<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Item;
use App\Models\ItemLoan;
use App\Models\ItemLocation;
use App\Models\Role;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $karyawan;

    private User $managerGudang;

    private User $staffGudang;

    private User $direkturSdm;

    private Branch $branchJaktim;

    private Branch $branchTambun;

    private Room $roomJaktim;

    private Room $roomTambun;

    private Item $itemBangku;

    protected function setUp(): void
    {
        parent::setUp();

        $roleKaryawan = Role::create(['name' => 'karyawan', 'display_name' => 'Karyawan']);
        $roleStaffGudang = Role::create(['name' => 'staff_gudang', 'display_name' => 'Staff Gudang']);
        $roleManagerGudang = Role::create(['name' => 'manager_gudang', 'display_name' => 'Manager Gudang']);
        $roleDirektur = Role::create(['name' => 'direktur_sdm', 'display_name' => 'Direktur SDM']);

        $this->karyawan = User::factory()->create([
            'role' => 'karyawan',
            'role_id' => $roleKaryawan->id,
            'name' => 'Karyawan User',
            'email' => 'karyawan.test@metinca.co.id',
        ]);

        $this->staffGudang = User::factory()->create([
            'role' => 'staff_gudang',
            'role_id' => $roleStaffGudang->id,
            'name' => 'Staff Gudang User',
            'email' => 'staff.gudang@metinca.co.id',
        ]);

        $this->managerGudang = User::factory()->create([
            'role' => 'manager_gudang',
            'role_id' => $roleManagerGudang->id,
            'name' => 'Manager Gudang User',
            'email' => 'manager.gudang@metinca.co.id',
        ]);

        $this->direkturSdm = User::factory()->create([
            'role' => 'direktur_sdm',
            'role_id' => $roleDirektur->id,
            'name' => 'Direktur SDM User',
            'email' => 'direktur.sdm@metinca.co.id',
        ]);

        $this->branchJaktim = Branch::create([
            'nama_cabang' => 'Cabang Jakarta Timur',
            'kode_cabang' => 'JKT',
            'alamat' => 'Pulogadung Jakarta Timur',
        ]);

        $this->branchTambun = Branch::create([
            'nama_cabang' => 'Cabang Tambun',
            'kode_cabang' => 'TMB',
            'alamat' => 'Tambun Selatan Bekasi',
        ]);

        $this->roomJaktim = Room::create([
            'branch_id' => $this->branchJaktim->id,
            'nama_ruangan' => 'Ruangan Produksi Jaktim',
            'kode_ruangan' => 'JKT-PRD',
        ]);

        $this->roomTambun = Room::create([
            'branch_id' => $this->branchTambun->id,
            'nama_ruangan' => 'Ruangan Operasional Tambun',
            'kode_ruangan' => 'TMB-OPS',
        ]);

        $this->itemBangku = Item::create([
            'kode_barang' => 'BRG-FRN-001',
            'nama_barang' => 'Bangku Putar Produksi Heavy Duty',
            'kategori' => 'Furniture & Perabot',
            'satuan' => 'Buah',
            'stok' => 75,
            'min_stok' => 10,
        ]);
    }

    /**
     * 1. Pengguna tanpa izin mendapat respons 403 saat mencoba mengelola akun,
     * mengganti role, atau mengubah inventaris.
     */
    public function test_unauthorized_user_cannot_manage_users_or_change_role_or_update_inventory(): void
    {
        // Karyawan cannot create user
        $this->actingAs($this->karyawan)
            ->post('/users', [
                'name' => 'User Baru',
                'email' => 'baru@metinca.co.id',
                'password' => 'password123',
                'role' => 'staff_gudang',
            ])
            ->assertForbidden();

        // Karyawan cannot update role
        $this->actingAs($this->karyawan)
            ->put("/users/{$this->karyawan->id}/role", [
                'role' => 'direktur_sdm',
            ])
            ->assertForbidden();

        // Karyawan cannot update room stock inventory
        $this->actingAs($this->karyawan)
            ->post('/barang/pendataan-ruangan', [
                'item_id' => $this->itemBangku->id,
                'branch_id' => $this->branchJaktim->id,
                'room_id' => $this->roomJaktim->id,
                'jumlah' => 10,
                'kondisi' => 'Baik',
            ])
            ->assertForbidden();

        // Non-director cannot promote to direktur_sdm
        $otherUser = User::factory()->create(['role' => 'karyawan']);
        $this->actingAs($this->managerGudang)
            ->put("/users/{$otherUser->id}/role", [
                'role' => 'direktur_sdm',
            ])
            ->assertForbidden();

        // User cannot change their own role even if manager
        $this->actingAs($this->managerGudang)
            ->put("/users/{$this->managerGudang->id}/role", [
                'role' => 'staff_gudang',
            ])
            ->assertForbidden();
    }

    /**
     * 2. Kombinasi cabang dan ruangan yang tidak cocok ditolak.
     */
    public function test_mismatched_branch_and_room_combinations_are_rejected(): void
    {
        // Room Jaktim used with Branch Tambun in pendataan-ruangan
        $this->actingAs($this->staffGudang)
            ->post('/barang/pendataan-ruangan', [
                'item_id' => $this->itemBangku->id,
                'branch_id' => $this->branchTambun->id,
                'room_id' => $this->roomJaktim->id, // Mismatched!
                'jumlah' => 10,
                'kondisi' => 'Baik',
            ])
            ->assertSessionHasErrors('room_id');

        // Mismatched origin room in loan creation
        $this->actingAs($this->karyawan)
            ->post('/peminjaman', [
                'item_id' => $this->itemBangku->id,
                'jumlah' => 5,
                'from_branch_id' => $this->branchJaktim->id,
                'from_room_id' => $this->roomTambun->id, // Mismatched!
                'to_branch_id' => $this->branchTambun->id,
                'to_room_id' => $this->roomTambun->id,
                'tanggal_pinjam' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('from_room_id');

        // Same origin and destination branch in loan creation
        $this->actingAs($this->karyawan)
            ->post('/peminjaman', [
                'item_id' => $this->itemBangku->id,
                'jumlah' => 5,
                'from_branch_id' => $this->branchJaktim->id,
                'from_room_id' => $this->roomJaktim->id,
                'to_branch_id' => $this->branchJaktim->id, // Same branch!
                'to_room_id' => $this->roomJaktim->id,
                'tanggal_pinjam' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('to_branch_id');
    }

    /**
     * 3. Penempatan melebihi stok yang diizinkan ditolak.
     */
    public function test_allocation_exceeding_master_stock_is_rejected(): void
    {
        // Master stock is 75
        // First allocate 50 in Jaktim
        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchJaktim->id,
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 50,
            'kondisi' => 'Baik',
        ]);

        // Attempt to allocate 30 in Tambun (50 + 30 = 80 > 75)
        $this->actingAs($this->staffGudang)
            ->post('/barang/pendataan-ruangan', [
                'item_id' => $this->itemBangku->id,
                'branch_id' => $this->branchTambun->id,
                'room_id' => $this->roomTambun->id,
                'jumlah' => 30,
                'kondisi' => 'Baik',
            ])
            ->assertSessionHasErrors('jumlah');

        // Attempt to adjust master stock below allocated room stock (50)
        $this->actingAs($this->staffGudang)
            ->post("/barang/{$this->itemBangku->id}/adjust-stock", [
                'type' => 'OUT',
                'jumlah' => 35, // 75 - 35 = 40 < 50
                'catatan' => 'Pengurangan stok',
            ])
            ->assertSessionHasErrors('jumlah');
    }

    /**
     * 4. Pengajuan dan penolakan tidak mengubah jumlah lokasi.
     */
    public function test_loan_application_and_rejection_do_not_modify_location_stocks(): void
    {
        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchJaktim->id,
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 45,
            'kondisi' => 'Baik',
        ]);

        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchTambun->id,
            'room_id' => $this->roomTambun->id,
            'jumlah' => 30,
            'kondisi' => 'Baik',
        ]);

        // Loan application
        $this->actingAs($this->karyawan)->post('/peminjaman', [
            'item_id' => $this->itemBangku->id,
            'jumlah' => 10,
            'from_branch_id' => $this->branchJaktim->id,
            'from_room_id' => $this->roomJaktim->id,
            'to_branch_id' => $this->branchTambun->id,
            'to_room_id' => $this->roomTambun->id,
            'tanggal_pinjam' => now()->format('Y-m-d'),
        ])->assertSessionHas('success');

        $loan = ItemLoan::where('status', 'DIAJUKAN')->firstOrFail();

        // Check stocks unchanged after application
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 45,
        ]);
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomTambun->id,
            'jumlah' => 30,
        ]);

        // Reject loan
        $this->actingAs($this->staffGudang)
            ->post("/peminjaman/{$loan->id}/tolak", [
                'catatan_review' => 'Stok sedang dipakai operasional',
            ])
            ->assertSessionHas('success');

        // Check stocks unchanged after rejection
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 45,
        ]);
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomTambun->id,
            'jumlah' => 30,
        ]);
    }

    /**
     * 5. Dengan saldo awal Jakarta Timur 45 bangku dan Tambun 30 bangku,
     * persetujuan pinjaman 10 bangku dari Jakarta Timur ke Tambun menghasilkan 35 dan 40.
     *
     * 6. Pengembalian menghasilkan saldo 45 dan 30 kembali.
     */
    public function test_loan_approval_and_return_stock_lifecycle(): void
    {
        // Saldo awal: Jaktim 45, Tambun 30
        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchJaktim->id,
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 45,
            'kondisi' => 'Baik',
        ]);

        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchTambun->id,
            'room_id' => $this->roomTambun->id,
            'jumlah' => 30,
            'kondisi' => 'Baik',
        ]);

        $loan = ItemLoan::create([
            'nomor_peminjaman' => 'LOAN-TEST-0001',
            'item_id' => $this->itemBangku->id,
            'jumlah' => 10,
            'from_branch_id' => $this->branchJaktim->id,
            'from_room_id' => $this->roomJaktim->id,
            'to_branch_id' => $this->branchTambun->id,
            'to_room_id' => $this->roomTambun->id,
            'borrower_id' => $this->karyawan->id,
            'borrower_name' => $this->karyawan->name,
            'tanggal_pinjam' => now()->format('Y-m-d'),
            'status' => 'DIAJUKAN',
        ]);

        // Persetujuan pinjaman 10 bangku dari Jakarta Timur ke Tambun
        $this->actingAs($this->staffGudang)
            ->post("/peminjaman/{$loan->id}/setujui")
            ->assertSessionHas('success');

        // Menghasilkan 35 di Jaktim dan 40 di Tambun
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 35,
        ]);
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomTambun->id,
            'jumlah' => 40,
        ]);

        // Pengembalian barang
        $this->actingAs($this->staffGudang)
            ->post("/peminjaman/{$loan->id}/kembalikan")
            ->assertSessionHas('success');

        // Menghasilkan saldo 45 dan 30 kembali
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 45,
        ]);
        $this->assertDatabaseHas('item_locations', [
            'room_id' => $this->roomTambun->id,
            'jumlah' => 30,
        ]);
    }

    /**
     * 7. Persetujuan atau pengembalian berulang tidak mengubah jumlah lagi (Idempotent).
     */
    public function test_repeated_approval_or_return_does_not_modify_stock_again(): void
    {
        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchJaktim->id,
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 45,
            'kondisi' => 'Baik',
        ]);

        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchTambun->id,
            'room_id' => $this->roomTambun->id,
            'jumlah' => 30,
            'kondisi' => 'Baik',
        ]);

        $loan = ItemLoan::create([
            'nomor_peminjaman' => 'LOAN-TEST-IDEMP',
            'item_id' => $this->itemBangku->id,
            'jumlah' => 10,
            'from_branch_id' => $this->branchJaktim->id,
            'from_room_id' => $this->roomJaktim->id,
            'to_branch_id' => $this->branchTambun->id,
            'to_room_id' => $this->roomTambun->id,
            'borrower_id' => $this->karyawan->id,
            'borrower_name' => $this->karyawan->name,
            'tanggal_pinjam' => now()->format('Y-m-d'),
            'status' => 'DIAJUKAN',
        ]);

        // Approve first time
        $this->actingAs($this->staffGudang)->post("/peminjaman/{$loan->id}/setujui")->assertSessionHas('success');
        $this->assertEquals(35, ItemLocation::where('room_id', $this->roomJaktim->id)->value('jumlah'));
        $this->assertEquals(40, ItemLocation::where('room_id', $this->roomTambun->id)->value('jumlah'));

        // Approve second time -> should be rejected and NOT reduce/increase stock again
        $this->actingAs($this->staffGudang)->post("/peminjaman/{$loan->id}/setujui")->assertSessionHas('error');
        $this->assertEquals(35, ItemLocation::where('room_id', $this->roomJaktim->id)->value('jumlah'));
        $this->assertEquals(40, ItemLocation::where('room_id', $this->roomTambun->id)->value('jumlah'));

        // Return first time
        $this->actingAs($this->staffGudang)->post("/peminjaman/{$loan->id}/kembalikan")->assertSessionHas('success');
        $this->assertEquals(45, ItemLocation::where('room_id', $this->roomJaktim->id)->value('jumlah'));
        $this->assertEquals(30, ItemLocation::where('room_id', $this->roomTambun->id)->value('jumlah'));

        // Return second time -> should be rejected and NOT alter stock again
        $this->actingAs($this->staffGudang)->post("/peminjaman/{$loan->id}/kembalikan")->assertSessionHas('error');
        $this->assertEquals(45, ItemLocation::where('room_id', $this->roomJaktim->id)->value('jumlah'));
        $this->assertEquals(30, ItemLocation::where('room_id', $this->roomTambun->id)->value('jumlah'));
    }

    /**
     * 8. Stok tidak mencukupi ditangani tanpa perpindahan barang.
     */
    public function test_insufficient_stock_handled_without_item_movement(): void
    {
        // Origin only has 5 units
        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchJaktim->id,
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 5,
            'kondisi' => 'Baik',
        ]);

        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchTambun->id,
            'room_id' => $this->roomTambun->id,
            'jumlah' => 30,
            'kondisi' => 'Baik',
        ]);

        // Loan requests 10 units
        $loan = ItemLoan::create([
            'nomor_peminjaman' => 'LOAN-TEST-INSUF',
            'item_id' => $this->itemBangku->id,
            'jumlah' => 10,
            'from_branch_id' => $this->branchJaktim->id,
            'from_room_id' => $this->roomJaktim->id,
            'to_branch_id' => $this->branchTambun->id,
            'to_room_id' => $this->roomTambun->id,
            'borrower_id' => $this->karyawan->id,
            'borrower_name' => $this->karyawan->name,
            'tanggal_pinjam' => now()->format('Y-m-d'),
            'status' => 'DIAJUKAN',
        ]);

        // Attempt approval
        $this->actingAs($this->staffGudang)
            ->post("/peminjaman/{$loan->id}/setujui")
            ->assertSessionHas('error');

        // Verify status marked DITOLAK or remain unchanged, and stocks not touched
        $loan->refresh();
        $this->assertEquals('DITOLAK', $loan->status);
        $this->assertEquals(5, ItemLocation::where('room_id', $this->roomJaktim->id)->value('jumlah'));
        $this->assertEquals(30, ItemLocation::where('room_id', $this->roomTambun->id)->value('jumlah'));
    }

    /**
     * 9. Filter pendataan dan tracking menghasilkan data yang sesuai.
     */
    public function test_filters_for_room_inventory_and_tracking_work_correctly(): void
    {
        ItemLocation::create([
            'item_id' => $this->itemBangku->id,
            'branch_id' => $this->branchJaktim->id,
            'room_id' => $this->roomJaktim->id,
            'jumlah' => 45,
            'kondisi' => 'Baik',
        ]);

        // Test room inventory filter by branch
        $response = $this->actingAs($this->staffGudang)
            ->get("/barang/pendataan-ruangan?branch_id={$this->branchJaktim->id}");
        $response->assertOk();

        // Test room inventory filter by room
        $responseRoom = $this->actingAs($this->staffGudang)
            ->get("/barang/pendataan-ruangan?room_id={$this->roomJaktim->id}");
        $responseRoom->assertOk();

        // Test tracking filter by branch
        $responseTracking = $this->actingAs($this->staffGudang)
            ->get("/barang/tracking?branch_id={$this->branchJaktim->id}");
        $responseTracking->assertOk();

        // Test tracking filter by search keyword
        $responseSearch = $this->actingAs($this->staffGudang)
            ->get('/barang/tracking?search=Bangku');
        $responseSearch->assertOk();
    }
}
