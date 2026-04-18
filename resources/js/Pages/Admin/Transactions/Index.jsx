import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Home, Search, DollarSign, CreditCard, Zap, Calendar, CheckCircle2, XCircle, Eye } from 'lucide-react';

export default function Index({ transactions, stats, auth }) {
    const [search, setSearch] = useState('');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.transactions.index'), { search }, { preserveState: true });
    };

    return (
        <AdminLayout>
            <Head title="Transaction Management" />

            <div className="space-y-6 max-w-8xl mx-auto font-['Plus_Jakarta_Sans',sans-serif]">
                {/* Header Section */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <h1 className="text-[24px] font-bold text-[#2f3344] tracking-tight">Transactions</h1>
                        <div className="flex items-center gap-2 text-[13px] text-[#727586] mt-1">
                            <Home size={16} className="text-[#727586]" />
                            <span className="text-[#c3c4ca]">-</span>
                            <span>Financial</span>
                            <span className="text-[#c3c4ca]">-</span>
                            <span>Transactions</span>
                        </div>
                    </div>
                    {/* Revenue Summary Badge */}
                    <div className="bg-[#f4f0ff] border border-[#dcd0ff] px-4 py-2 rounded-[10px] flex items-center gap-3 shadow-sm">
                        <div className="bg-[#673ab7] text-white p-1.5 rounded-lg">
                            <DollarSign size={16} />
                        </div>
                        <div>
                            <p className="text-[10px] font-bold text-[#673ab7] uppercase tracking-wider">Total Revenue</p>
                            <p className="text-[16px] font-bold text-[#2f3344]">${stats.total_revenue}</p>
                        </div>
                    </div>
                </div>

                {/* Main Content Card */}
                <div className="bg-white rounded-[12px] border border-[#e3e4e8] shadow-sm overflow-hidden">
                    <div className="px-6 border-b border-[#e3e4e8]">
                        <div className="flex gap-10">
                            <button className="pt-5 pb-4 text-[14px] font-bold transition-all relative text-[#673ab7]">
                                All Transactions
                                <div className="absolute bottom-0 left-0 right-0 h-[3px] bg-[#673ab7] rounded-t-full"></div>
                            </button>
                        </div>
                    </div>

                    {/* Search Bar */}
                    <div className="p-6 border-b border-[#f1f2f4]">
                        <form onSubmit={handleSearch} className="relative max-w-[400px]">
                            <Search className="absolute left-4 top-1/2 -translate-y-1/2 text-[#727586]" size={18} />
                            <input
                                type="text"
                                placeholder="Search by Transaction ID or User..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                className="w-full h-[45px] pl-11 pr-4 bg-[#f8f9fa] border-none rounded-[8px] text-[14px] text-[#2f3344] focus:ring-2 focus:ring-[#673ab7]/20 transition-all outline-none"
                            />
                        </form>
                    </div>

                    {/* Table Area */}
                    <div className="overflow-x-auto">
                        <table className="w-full text-left">
                            <thead className="bg-[#fafbfc] border-b border-[#f1f2f4]">
                                <tr>
                                    <th className="px-7 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider whitespace-nowrap">Transaction ID</th>
                                    <th className="px-5 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">User</th>
                                    <th className="px-5 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Amount</th>
                                    <th className="px-5 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Date</th>
                                    <th className="px-5 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Status</th>
                                    <th className="px-7 py-4"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f2f4]">
                                {transactions.data.length > 0 ? (
                                    transactions.data.map((tx) => (
                                        <tr key={tx.id} className="hover:bg-[#fafbfc] transition-colors group">
                                            <td className="px-7 py-5">
                                                <div className="flex items-center gap-2 font-mono text-[13px] font-bold text-[#673ab7] whitespace-nowrap">
                                                    <CreditCard size={14} className="text-[#c3c4ca]" />
                                                    {tx.external_payment_id || 'N/A'}
                                                </div>
                                            </td>
                                            <td className="px-5 py-5">
                                                <div className="text-[14px] font-bold text-[#2f3344]">{tx.user?.name}</div>
                                                <span className="inline-flex items-center px-2.5 py-0.5 bg-[#f4f0ff] text-[#673ab7] rounded-full text-[10px] font-bold border border-[#dcd0ff] mt-1 uppercase">
                                                    <Zap size={10} className="mr-1" />
                                                    {tx.plan?.name || 'Manual'}
                                                </span>
                                            </td>

                                            <td className="px-5 py-5">
                                                <div className="flex items-baseline gap-1">
                                                    <span className="text-[15px] font-bold text-[#2f3344]">${tx.amount}</span>
                                                    <span className="text-[10px] font-bold text-[#c3c4ca] uppercase">{tx.currency}</span>
                                                </div>
                                            </td>
                                            <td className="px-5 py-5">
                                                <div className="flex items-center gap-2 text-[#727586] text-[13px]">
                                                    <Calendar size={14} className="text-[#c3c4ca]" />
                                                    {new Date(tx.created_at).toLocaleDateString()}
                                                </div>
                                            </td>
                                            <td className="px-5 py-5">
                                                {tx.status === 'completed' ? (
                                                    <span className="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-600 rounded-full text-[10px] font-bold uppercase border border-emerald-100">
                                                        <CheckCircle2 size={12} className="mr-1" /> Succeeded
                                                    </span>
                                                ) : (
                                                    <span className="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-600 rounded-full text-[10px] font-bold uppercase border border-rose-100">
                                                        <XCircle size={12} className="mr-1" /> Failed
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-7 py-5 text-right">
                                                <div className="flex items-center justify-end gap-3 transition-all">
                                                    <Link
                                                        href={route('admin.transactions.show', tx.id)}
                                                        className="h-[36px] inline-flex items-center bg-white border border-[#e3e4e8] text-[#2f3344] px-4 rounded-[6px] font-bold text-[13px] hover:border-[#673ab7] hover:text-[#673ab7] transition-all"
                                                    >
                                                        Details
                                                    </Link>

                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6" className="px-7 py-10 text-center text-[#727586]">No transactions found.</td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
