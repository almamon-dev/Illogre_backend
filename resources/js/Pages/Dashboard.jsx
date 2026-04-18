import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import {
    Ticket,
    Bot,
    Clock,
    TrendingUp,
    ChevronDown,
    Search,
    MessageSquare,
    ShoppingBag,
    Mail,
} from 'lucide-react';
import {
    LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer,
    BarChart, Bar, Cell
} from 'recharts';

export default function Dashboard({ auth, stats, ticket_volume, resolution_rate, recent_tickets }) {

    const statCards = [
        {
            label: 'Total Tickets',
            value: stats?.total_tickets?.value || '0',
            trend: stats?.total_tickets?.trend,
            trendLabel: stats?.total_tickets?.trend_label,
            icon: <Ticket size={20} />,
            color: 'text-lime-600',
            bg: 'bg-lime-100',
            type: stats?.total_tickets?.type
        },
        {
            label: 'AI Resolved',
            value: stats?.ai_resolved?.value || '0',
            trend: stats?.ai_resolved?.trend,
            trendLabel: stats?.ai_resolved?.trend_label,
            icon: <Bot size={20} />,
            color: 'text-emerald-600',
            bg: 'bg-emerald-100',
            type: stats?.ai_resolved?.type
        },
        {
            label: 'Waiting Approval',
            value: stats?.waiting_approval?.value || '0',
            trend: stats?.waiting_approval?.trend,
            trendLabel: stats?.waiting_approval?.trend_label,
            icon: <Clock size={20} />,
            color: 'text-orange-600',
            bg: 'bg-orange-100',
            type: stats?.waiting_approval?.type
        },
        {
            label: 'Success Rate',
            value: stats?.success_rate?.value || '0',
            trend: stats?.success_rate?.trend,
            trendLabel: stats?.success_rate?.trend_label,
            icon: <TrendingUp size={20} />,
            color: 'text-indigo-600',
            bg: 'bg-indigo-100',
            type: stats?.success_rate?.type
        },
        {
            label: 'Time Saved',
            value: stats?.time_saved?.value || '0',
            trend: null,
            trendLabel: stats?.time_saved?.trend_label,
            icon: <Clock size={20} />,
            color: 'text-amber-600',
            bg: 'bg-amber-100',
            type: stats?.time_saved?.type
        },
    ];

    const getSourceIcon = (source) => {
        switch (source?.toLowerCase()) {
            case 'chat': return <MessageSquare size={14} className="text-slate-500" />;
            case 'shopify': return <ShoppingBag size={14} className="text-slate-500" />;
            case 'email': return <Mail size={14} className="text-slate-500" />;
            default: return <MessageSquare size={14} className="text-slate-500" />;
        }
    };

    return (
        <AdminLayout user={auth?.user}>
            <Head title="Admin Dashboard" />

            <div className="p-8 bg-[#f8f9fa] min-h-screen font-['Plus_Jakarta_Sans',sans-serif]">
                <div className="flex justify-between items-center mb-10">
                    <div>
                        <h1 className="text-3xl font-semibold text-[#1a1c21]">Welcome back, {auth.user.name}</h1>
                        <p className="text-[#727586] mt-1 text-sm font-medium">Here are your dashboard overview</p>
                    </div>
                </div>

                {/* Stat Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 mb-8">
                    {statCards.map((card, idx) => (
                        <div key={idx} className="bg-white p-6 rounded-md border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)]">
                            <div className="flex justify-between items-start mb-4">
                                <p className="text-[14px] font-semibold text-[#727586]">{card.label}</p>
                                <div className={`${card.bg} ${card.color} p-2 rounded-lg`}>
                                    {card.icon}
                                </div>
                            </div>
                            <h2 className="text-3xl font-semibold text-[#1a1c21] mb-2">{card.value}</h2>
                            <div className="flex items-center gap-1.5 overflow-hidden">
                                {card.trend && (
                                    <span className={`text-[12px] font-semibold whitespace-nowrap ${card.type === 'up' ? 'text-emerald-500' : 'text-rose-500'}`}>
                                        {card.trend}
                                    </span>
                                )}
                                <span className="text-[11px] text-slate-400 font-medium truncate italic">
                                    {card.trendLabel}
                                </span>
                            </div>
                        </div>
                    ))}
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                    <div className="bg-white p-8 rounded-xl border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)]">
                        <div className="flex justify-between items-center mb-10">
                            <h3 className="text-xl font-semibold text-[#1a1c21]">Ticket volume</h3>
                        </div>
                        <div className="h-[300px]">
                            <ResponsiveContainer width="100%" height="100%">
                                <LineChart data={ticket_volume}>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f0f0f0" />
                                    <XAxis dataKey="day" axisLine={false} tickLine={false} tick={{ fill: '#adb5bd', fontSize: 11 }} />
                                    <YAxis axisLine={false} tickLine={false} tick={{ fill: '#adb5bd', fontSize: 11 }} />
                                    <Tooltip />
                                    <Line type="monotone" dataKey="tickets" stroke="#94e394" strokeWidth={3} dot={{ r: 4 }} />
                                </LineChart>
                            </ResponsiveContainer>
                        </div>
                    </div>

                    <div className="bg-white p-8 rounded-xl border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)]">
                        <div className="mb-10">
                            <h3 className="text-xl font-semibold text-[#1a1c21]">AI Resolution Rate</h3>
                        </div>
                        <div className="h-[300px]">
                            <ResponsiveContainer width="100%" height="100%">
                                <BarChart data={resolution_rate}>
                                    <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f0f0f0" />
                                    <XAxis dataKey="name" axisLine={false} tickLine={false} />
                                    <YAxis axisLine={false} tickLine={false} />
                                    <Tooltip />
                                    <Bar dataKey="resolved" fill="#e9ecef" radius={[6, 6, 0, 0]} />
                                </BarChart>
                            </ResponsiveContainer>
                        </div>
                    </div>
                </div>

                <div className="bg-white rounded-xl border border-slate-100 shadow-[0_2px_15px_-3px_rgba(0,0,0,0.07)]">
                    <div className="p-8">
                        <h3 className="text-xl font-semibold text-[#1a1c21]">Recent Tickets</h3>
                    </div>
                    <div className="overflow-x-auto px-4 pb-6">
                        <table className="w-full text-left">
                            <thead className="bg-[#f2f7f2] text-[#727586] text-[12px] font-semibold uppercase">
                                <tr>
                                    <th className="px-6 py-4">Ticket ID</th>
                                    <th className="px-6 py-4">Customer</th>
                                    <th className="px-6 py-4">Subject</th>
                                    <th className="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {recent_tickets?.map((ticket, idx) => (
                                    <tr key={idx} className="hover:bg-slate-50 transition-all border-b border-slate-50">
                                        <td className="px-6 py-5 text-sm font-semibold text-[#2f3344]">{ticket.id}</td>
                                        <td className="px-6 py-5 text-sm font-semibold">{ticket.customer.name}</td>
                                        <td className="px-6 py-5 text-sm">{ticket.subject.title}</td>
                                        <td className="px-6 py-5">
                                            <span className="px-4 py-1.5 rounded-lg text-[11px] font-semibold bg-emerald-50 text-emerald-600">
                                                {ticket.status}
                                            </span>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <style dangerouslySetInnerHTML={{
                __html: `
                @import url('https://fonts.googleapis.com/css2?family=Plus_Jakarta_Sans:wght@400;500;600;700;800&display=swap');
            `}} />
        </AdminLayout>
    );
}
