import { Link } from '@inertiajs/react';

export default function Pagination({ links, from, to, total, className = '' }) {
    if (!links || links.length <= 3) return null;

    return (
        <div
            className={`flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-4 sm:px-6 py-4 border-t border-slate-100 bg-white ${className}`}
        >
            <div className="text-xs text-slate-500">
                {total > 0 ? (
                    <span>
                        Menampilkan <strong className="font-bold text-slate-700">{from}</strong> -{' '}
                        <strong className="font-bold text-slate-700">{to}</strong> dari{' '}
                        <strong className="font-bold text-slate-700">{total}</strong> data
                    </span>
                ) : (
                    <span>Tidak ada data</span>
                )}
            </div>

            <div className="flex items-center gap-1 flex-wrap">
                {links.map((link, index) => {
                    if (!link.url) {
                        return (
                            <span
                                key={index}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                                className="px-3 py-1.5 text-xs text-slate-300 font-semibold rounded-lg select-none cursor-not-allowed"
                            />
                        );
                    }

                    return (
                        <Link
                            key={index}
                            href={link.url}
                            preserveScroll
                            preserveState
                            dangerouslySetInnerHTML={{ __html: link.label }}
                            className={`px-3 py-1.5 text-xs font-semibold rounded-lg transition ${
                                link.active
                                    ? 'bg-emerald-600 text-white font-bold shadow-xs'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-slate-200 bg-white'
                            }`}
                        />
                    );
                })}
            </div>
        </div>
    );
}
