<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemLoan;
use App\Models\ItemLocation;
use App\Models\Room;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class PeminjamanController extends Controller
{
    /**
     * Role yang memeriksa stok, menyetujui/menolak, dan menerima pengembalian barang.
     *
     * @var array<int, string>
     */
    private const REVIEWER_ROLES = ['staff_accounting', 'staff_gudang', 'manager_accounting', 'manager_gudang'];

    public function index(Request $request): Response
    {
        $user = $request->user();
        $isKaryawan = $user->hasRole('karyawan');

        $query = ItemLoan::with(['item', 'fromBranch', 'fromRoom', 'toBranch', 'toRoom', 'borrower', 'reviewer'])
            ->latest();

        if ($isKaryawan) {
            $query->where('borrower_id', $user->id);
        }

        if ($request->filled('status') && $request->status !== 'ALL') {
            $query->where('status', $request->status);
        }

        if ($request->filled('branch_id') && $request->branch_id !== 'ALL') {
            $branchId = $request->branch_id;
            $query->where(function ($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                    ->orWhere('to_branch_id', $branchId);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_peminjaman', 'like', "%{$search}%")
                    ->orWhere('borrower_name', 'like', "%{$search}%")
                    ->orWhereHas('item', function ($iq) use ($search) {
                        $iq->where('nama_barang', 'like', "%{$search}%")
                            ->orWhere('kode_barang', 'like', "%{$search}%");
                    });
            });
        }

        $loans = $query->paginate(15)->withQueryString();

        $branches = Branch::with('rooms')->get();
        $items = Item::with('locations')->orderBy('nama_barang')->get();

        $allLoans = ItemLoan::query()
            ->when($isKaryawan, fn ($q) => $q->where('borrower_id', $user->id))
            ->get();

        $metrics = [
            'total_loans' => $allLoans->count(),
            'pending_loans' => $allLoans->where('status', 'DIAJUKAN')->count(),
            'active_loans' => $allLoans->where('status', 'DIPINJAM')->count(),
            'returned_loans' => $allLoans->where('status', 'SELESAI')->count(),
            'total_items_loaned' => $allLoans->where('status', 'DIPINJAM')->sum('jumlah'),
        ];

        return Inertia::render('Peminjaman/Index', [
            'loans' => $loans,
            'branches' => $branches,
            'items' => $items,
            'metrics' => $metrics,
            'filters' => $request->only(['status', 'branch_id', 'search']),
        ]);
    }

    /**
     * Karyawan mengajukan peminjaman. Stok belum berpindah sampai Staff menyetujui.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! $user->hasRole('karyawan')) {
            abort(403, 'Hanya Karyawan yang dapat mengajukan peminjaman barang.');
        }

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'from_branch_id' => ['required', 'exists:branches,id'],
            'from_room_id' => ['required', 'exists:rooms,id'],
            'to_branch_id' => ['required', 'exists:branches,id', 'different:from_branch_id'],
            'to_room_id' => ['nullable', 'exists:rooms,id'],
            'tanggal_pinjam' => ['required', 'date'],
            'tenggat_kembali' => ['nullable', 'date', 'after_or_equal:tanggal_pinjam'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'to_branch_id.different' => 'Cabang tujuan peminjaman harus berbeda dari cabang asal.',
            'tenggat_kembali.after_or_equal' => 'Tenggat kembali tidak boleh sebelum tanggal pinjam.',
        ]);

        $fromRoom = Room::findOrFail($validated['from_room_id']);
        if ((int) $fromRoom->branch_id !== (int) $validated['from_branch_id']) {
            throw ValidationException::withMessages([
                'from_room_id' => 'Ruangan asal yang dipilih tidak terdaftar pada cabang asal.',
            ]);
        }

        if (! empty($validated['to_room_id'])) {
            $toRoom = Room::findOrFail($validated['to_room_id']);
            if ((int) $toRoom->branch_id !== (int) $validated['to_branch_id']) {
                throw ValidationException::withMessages([
                    'to_room_id' => 'Ruangan tujuan yang dipilih tidak terdaftar pada cabang tujuan.',
                ]);
            }
        } else {
            $validated['to_room_id'] = Room::where('branch_id', $validated['to_branch_id'])->orderBy('id')->value('id');
        }

        DB::transaction(function () use ($validated, $user) {
            $year = date('Y');
            $latestLoan = ItemLoan::where('nomor_peminjaman', 'like', "LOAN-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->first();

            if ($latestLoan && preg_match('/LOAN-\d{4}-(\d+)/', $latestLoan->nomor_peminjaman, $matches)) {
                $count = ((int) $matches[1]) + 1;
            } else {
                $count = ItemLoan::whereYear('created_at', $year)->count() + 1;
            }
            $nomorPeminjaman = 'LOAN-'.$year.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);

            $loan = ItemLoan::create([
                ...$validated,
                'nomor_peminjaman' => $nomorPeminjaman,
                'borrower_id' => $user->id,
                'borrower_name' => $user->name,
                'status' => 'DIAJUKAN',
            ]);

            AuditLogService::log($user, 'LOAN_REQUESTED', 'ItemLoan', $loan->id, null, $loan->toArray());
        });

        return redirect()->back()->with('success', 'Pengajuan peminjaman berhasil dikirim dan menunggu pemeriksaan stok oleh Staff.');
    }

    /**
     * Staff memeriksa stok. Jika tersedia, peminjaman disetujui dan dicatat; jika tidak, ditolak.
     */
    public function setujui(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeReviewer($request);

        $result = DB::transaction(function () use ($id, $user) {
            $loan = ItemLoan::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'DIAJUKAN') {
                return ['success' => false, 'message' => 'Hanya pengajuan berstatus DIAJUKAN yang dapat diproses.'];
            }

            $oldValues = $loan->toArray();

            $fromLocation = ItemLocation::where('item_id', $loan->item_id)
                ->where('branch_id', $loan->from_branch_id)
                ->where('room_id', $loan->from_room_id)
                ->lockForUpdate()
                ->first();

            if (! $fromLocation || $fromLocation->jumlah < $loan->jumlah) {
                $tersedia = $fromLocation?->jumlah ?? 0;
                $loan->update([
                    'status' => 'DITOLAK',
                    'reviewed_by' => $user->id,
                    'reviewed_at' => now(),
                    'catatan_review' => "Barang tidak tersedia. Stok di lokasi asal hanya {$tersedia} unit, diminta {$loan->jumlah} unit.",
                ]);

                AuditLogService::log($user, 'LOAN_REJECTED_NO_STOCK', 'ItemLoan', $loan->id, $oldValues, $loan->fresh()->toArray());

                return ['success' => false, 'message' => "Stok tidak mencukupi. Peminjaman {$loan->nomor_peminjaman} otomatis ditolak dan informasi dikirim ke Karyawan."];
            }

            $fromLocation->decrement('jumlah', $loan->jumlah);

            $toRoomId = $loan->to_room_id ?: Room::where('branch_id', $loan->to_branch_id)->orderBy('id')->value('id');

            if ($toRoomId) {
                $toLocation = ItemLocation::firstOrCreate(
                    [
                        'item_id' => $loan->item_id,
                        'room_id' => $toRoomId,
                    ],
                    [
                        'branch_id' => $loan->to_branch_id,
                        'jumlah' => 0,
                        'kondisi' => 'Baik',
                        'keterangan' => 'Unit pinjaman antar cabang',
                    ]
                );
                $toLocation->increment('jumlah', $loan->jumlah);
            }

            $loan->update([
                'status' => 'DIPINJAM',
                'to_room_id' => $toRoomId,
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
            ]);

            AuditLogService::log($user, 'LOAN_APPROVED', 'ItemLoan', $loan->id, $oldValues, $loan->fresh()->toArray());

            return ['success' => true, 'message' => "Peminjaman {$loan->nomor_peminjaman} disetujui dan dicatat. Barang diserahkan ke cabang tujuan."];
        });

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    /**
     * Staff menolak pengajuan secara manual dengan alasan.
     */
    public function tolak(Request $request, int $id): RedirectResponse
    {
        $user = $request->user();
        $this->authorizeReviewer($request);

        $validated = $request->validate([
            'catatan_review' => ['required', 'string', 'max:1000'],
        ], [
            'catatan_review.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $result = DB::transaction(function () use ($id, $user, $validated) {
            $loan = ItemLoan::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'DIAJUKAN') {
                return ['success' => false, 'message' => 'Hanya pengajuan berstatus DIAJUKAN yang dapat ditolak.'];
            }

            $oldValues = $loan->toArray();

            $loan->update([
                'status' => 'DITOLAK',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'catatan_review' => $validated['catatan_review'],
            ]);

            AuditLogService::log($user, 'LOAN_REJECTED', 'ItemLoan', $loan->id, $oldValues, $loan->fresh()->toArray());

            return ['success' => true, 'message' => "Peminjaman {$loan->nomor_peminjaman} ditolak dan alasan diteruskan ke Karyawan."];
        });

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    public function kembalikan(Request $request, int $id): RedirectResponse
    {
        $this->authorizeReviewer($request);

        $result = DB::transaction(function () use ($id, $request) {
            $loan = ItemLoan::where('id', $id)->lockForUpdate()->firstOrFail();

            if ($loan->status !== 'DIPINJAM') {
                return ['success' => false, 'message' => 'Hanya peminjaman berstatus DIPINJAM yang dapat dikembalikan.'];
            }

            $oldValues = $loan->toArray();

            $toRoomId = $loan->to_room_id ?: Room::where('branch_id', $loan->to_branch_id)->orderBy('id')->value('id');

            if ($toRoomId) {
                $toLocation = ItemLocation::where('item_id', $loan->item_id)
                    ->where('room_id', $toRoomId)
                    ->lockForUpdate()
                    ->first();

                if (! $toLocation || $toLocation->jumlah < $loan->jumlah) {
                    $tersedia = $toLocation?->jumlah ?? 0;
                    throw ValidationException::withMessages([
                        'loan' => "Jumlah barang di lokasi tujuan ({$tersedia}) tidak mencukupi untuk dikembalikan ({$loan->jumlah} unit).",
                    ]);
                }

                $toLocation->decrement('jumlah', $loan->jumlah);
            }

            $fromLocation = ItemLocation::firstOrCreate(
                [
                    'item_id' => $loan->item_id,
                    'room_id' => $loan->from_room_id,
                ],
                [
                    'branch_id' => $loan->from_branch_id,
                    'jumlah' => 0,
                    'kondisi' => 'Baik',
                ]
            );
            $fromLocation->increment('jumlah', $loan->jumlah);

            $loan->update([
                'status' => 'SELESAI',
                'tanggal_kembali' => now()->format('Y-m-d'),
            ]);

            AuditLogService::log(
                $request->user(),
                'LOAN_RETURNED',
                'ItemLoan',
                $loan->id,
                $oldValues,
                $loan->fresh()->toArray()
            );

            return ['success' => true, 'message' => 'Barang pinjaman telah berhasil dikembalikan ke cabang dan ruangan semula.'];
        });

        if (! $result['success']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()->with('success', $result['message']);
    }

    private function authorizeReviewer(Request $request): void
    {
        if (! $request->user()->hasRole(self::REVIEWER_ROLES)) {
            abort(403, 'Hanya Staff atau Manager Gudang yang dapat memproses peminjaman.');
        }
    }
}
