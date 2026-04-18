import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Home, Search, Mail, Building2, Zap, CheckCircle2, Clock, Trash2 } from 'lucide-react';

export default function Index({ owners, auth }) {
    const [search, setSearch] = useState('');

    const handleSearch = (e) => {
        e.preventDefault();
        router.get(route('admin.owners.index'), { search }, { preserveState: true });
    };

    return (
        <AdminLayout>
            <Head title="Owners Management" />

            <div className="space-y-6 max-w-8xl mx-auto font-['Plus_Jakarta_Sans',sans-serif]">
                {/* Header Section */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <h1 className="text-[24px] font-bold text-[#2f3344] tracking-tight">Owners</h1>
                        <div className="flex items-center gap-2 text-[13px] text-[#727586] mt-1">
                            <Home size={16} className="text-[#727586]" />
                            <span className="text-[#c3c4ca]">-</span>
                            <span>Management</span>
                            <span className="text-[#c3c4ca]">-</span>
                            <span>Owners</span>
                        </div>
                    </div>
                </div>

                {/* Main Content Card */}
                <div className="bg-white rounded-[12px] border border-[#e3e4e8] shadow-sm overflow-hidden">
                    <div className="px-6 border-b border-[#e3e4e8]">
                        <div className="flex gap-10">
                            <button className="pt-5 pb-4 text-[14px] font-bold transition-all relative text-[#673ab7]">
                                All owners
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
                                placeholder="Search by name, email or company..."
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
                                    <th className="px-7 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Owner Info</th>
                                    <th className="px-5 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Company</th>
                                    <th className="px-5 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Plan</th>
                                    <th className="px-5 py-4 text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Status</th>
                                    <th className="px-7 py-4"></th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#f1f2f4]">
                                {owners.data.length > 0 ? (
                                    owners.data.map((owner) => (
                                        <tr key={owner.id} className="hover:bg-[#fafbfc] transition-colors group">
                                            <td className="px-7 py-5">
                                                <div className="flex items-center gap-3">
                                                    <div className="w-10 h-10 rounded-full bg-[#f4f0ff] flex items-center justify-center text-[#673ab7] font-bold">
                                                        {owner.name.charAt(0).toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div className="text-[14px] font-bold text-[#2f3344]">{owner.name}</div>
                                                        <div className="text-[12px] text-[#727586] flex items-center gap-1">
                                                            <Mail size={12} /> {owner.email}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td className="px-5 py-5 text-sm font-semibold text-[#2f3344]">
                                                <div className="flex items-center gap-2">
                                                    <Building2 size={16} className="text-[#c3c4ca]" />
                                                    {owner.company_name}
                                                </div>
                                            </td>
                                            <td className="px-5 py-5">
                                                <span className="inline-flex items-center px-3 py-1 bg-[#f4f0ff] text-[#673ab7] rounded-full text-[11px] font-bold border border-[#dcd0ff]">
                                                    <Zap size={12} className="mr-1" />
                                                    {owner.subscription?.plan?.name || 'No Plan'}
                                                </span>
                                            </td>
                                            <td className="px-5 py-5">
                                                {owner.email_verified_at ? (
                                                    <span className="text-emerald-600 font-bold text-[12px] flex items-center gap-1">
                                                        <CheckCircle2 size={14} /> Verified
                                                    </span>
                                                ) : (
                                                    <span className="text-amber-500 font-bold text-[12px] flex items-center gap-1">
                                                        <Clock size={14} /> Pending
                                                    </span>
                                                )}
                                            </td>
                                            <td className="px-7 py-5 text-right">
                                                <div className="flex items-center justify-end gap-3">
                                                    <Link
                                                        href="#"
                                                        className="h-[36px] inline-flex items-center bg-white border border-[#e3e4e8] text-[#2f3344] px-4 rounded-[6px] font-bold text-[13px] hover:border-[#673ab7] hover:text-[#673ab7] transition-all"
                                                    >
                                                        Details
                                                    </Link>
                                                    <button className="w-8 h-8 flex items-center justify-center text-[#727586] hover:bg-red-50 hover:text-red-500 rounded-lg transition-all">
                                                        <Trash2 size={18} />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="5" className="px-7 py-10 text-center text-[#727586]">No owners found.</td>
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
