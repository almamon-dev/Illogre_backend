import React from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    CreditCard,
    Calendar,
    User,
    Mail,
    CheckCircle2,
    XCircle,
    DollarSign,
    Zap,
    Download,
    FileText
} from 'lucide-react';

export default function Show({ transaction, auth }) {
    return (
        <AdminLayout>
            <Head title={`Transaction - ${transaction.external_payment_id}`} />

            <div className="space-y-6 max-w-8xl mx-auto font-['Plus_Jakarta_Sans',sans-serif]">
                {/* Header Section */}
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-4">
                        <Link
                            href={route('admin.transactions.index')}
                            className="bg-white p-2 rounded-lg border border-[#e3e4e8] hover:border-[#673ab7] hover:text-[#673ab7] transition-all"
                        >
                            <ArrowLeft size={18} />
                        </Link>
                        <div>
                            <h1 className="text-[24px] font-bold text-[#2f3344] tracking-tight">Transaction Details</h1>
                            <p className="text-[13px] text-[#727586]">Receipt and payment data for ID: {transaction.external_payment_id}</p>
                        </div>
                    </div>
                    <button className="h-[40px] px-5 bg-[#673ab7] text-white rounded-[8px] font-bold text-[13px] hover:bg-[#5e35a6] transition-all flex items-center gap-2 shadow-sm shadow-[#673ab7]/20">
                        <Download size={16} /> Export Receipt
                    </button>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Payment Summary Table Style */}
                    <div className="lg:col-span-2 space-y-6">
                        <div className="bg-white rounded-[12px] border border-[#e3e4e8] shadow-sm overflow-hidden">
                            <div className="px-6 py-5 border-b border-[#e3e4e8] bg-[#fafbfc]">
                                <h3 className="text-[15px] font-bold text-[#2f3344]">Payment Overview</h3>
                            </div>
                            <div className="p-6">
                                <div className="space-y-4">
                                    <div className="flex justify-between items-center py-2 border-b border-[#f1f2f4]">
                                        <span className="text-[13px] text-[#727586] font-medium">Stripe Payment ID</span>
                                        <span className="text-[14px] font-bold text-[#673ab7] font-mono">{transaction.external_payment_id}</span>
                                    </div>
                                    <div className="flex justify-between items-center py-2 border-b border-[#f1f2f4]">
                                        <span className="text-[13px] text-[#727586] font-medium">Plan Purchased</span>
                                        <span className="inline-flex items-center px-2.5 py-1 rounded bg-[#f4f0ff] text-[#673ab7] font-bold text-[11px] uppercase border border-[#dcd0ff]">
                                            <Zap size={12} className="mr-1" /> {transaction.plan?.name || 'Manual'}
                                        </span>
                                    </div>
                                    <div className="flex justify-between items-center py-2 border-b border-[#f1f2f4]">
                                        <span className="text-[13px] text-[#727586] font-medium">Payment Method</span>
                                        <div className="flex items-center gap-2 text-[14px] font-bold text-[#2f3344]">
                                            <CreditCard size={16} className="text-[#c3c4ca]" />
                                            {transaction.payment_method?.toUpperCase()}
                                        </div>
                                    </div>
                                    <div className="flex justify-between items-center py-3">
                                        <span className="text-[14px] text-[#2f3344] font-bold uppercase">Total Amount Paid</span>
                                        <span className="text-[24px] font-extrabold text-[#673ab7]">${transaction.amount} <span className="text-[12px] text-[#c3c4ca]">{transaction.currency}</span></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Customer Sidebar Card */}
                    <div className="space-y-6">
                        <div className="bg-white rounded-[12px] border border-[#e3e4e8] shadow-sm overflow-hidden">
                            <div className="px-6 py-5 border-b border-[#e3e4e8] bg-[#fafbfc]">
                                <h3 className="text-[15px] font-bold text-[#2f3344]">Owner Info</h3>
                            </div>
                            <div className="p-6">
                                <div className="flex flex-col items-center text-center pb-6 border-b border-[#f1f2f4]">
                                    <div className="w-16 h-16 rounded-full bg-[#f4f0ff] flex items-center justify-center text-[#673ab7] font-bold text-xl mb-4">
                                        {transaction.user?.name.charAt(0).toUpperCase()}
                                    </div>
                                    <h4 className="text-[16px] font-bold text-[#2f3344]">{transaction.user?.name}</h4>
                                    <div className="flex items-center gap-1 text-[12px] text-[#727586] font-medium">
                                        <Mail size={12} /> {transaction.user?.email}
                                    </div>
                                </div>

                                <div className="pt-6 space-y-4">
                                    <div className="flex flex-col gap-1">
                                        <span className="text-[11px] font-bold text-[#c3c4ca] uppercase">Verification Status</span>
                                        {transaction.user?.email_verified_at ? (
                                            <span className="text-[13px] font-bold text-emerald-600 flex items-center gap-1.5">
                                                <CheckCircle2 size={16} /> Account Verified
                                            </span>
                                        ) : (
                                            <span className="text-[13px] font-bold text-amber-500 flex items-center gap-1.5">
                                                <XCircle size={16} /> Unverified
                                            </span>
                                        )}
                                    </div>
                                    <div className="flex flex-col gap-1">
                                        <span className="text-[11px] font-bold text-[#c3c4ca] uppercase">Payment Date</span>
                                        <div className="text-[13px] font-bold text-[#2f3344] flex items-center gap-1.5">
                                            <Calendar size={16} className="text-[#c3c4ca]" />
                                            {new Date(transaction.created_at).toLocaleString()}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
