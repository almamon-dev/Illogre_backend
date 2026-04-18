import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import {
    Home, Settings, ChevronDown, Save, RotateCcw
} from 'lucide-react';

export default function SettingsLayout({ title, subtitle, children, breadcrumbs = [] }) {
    const [showPromo, setShowPromo] = useState(true);

    return (
        <AdminLayout>
            <Head title={title} />

            <div className="space-y-6 max-w-8xl mx-auto pb-20 px-4 sm:px-6 font-['Plus_Jakarta_Sans',sans-serif]">
                {/* Header */}
                <div className="flex items-center justify-between pt-4">
                    <div className="flex items-center gap-4">
                        <h1 className="text-[26px] font-extrabold text-[#1a1c21] tracking-tight">
                            {title}
                        </h1>
                        <div className="flex items-center gap-2 text-[13px] text-slate-500 mt-1">
                            <Home size={15} className="text-slate-400" />
                            <span className="text-slate-200">-</span>
                            <span className="font-medium text-slate-400">Settings</span>
                            {breadcrumbs.map((crumb, index) => (
                                <React.Fragment key={index}>
                                    <span className="text-slate-200">-</span>
                                    <span className={index === breadcrumbs.length - 1 ? "text-[#1a1c21] font-bold" : "font-medium text-slate-400"}>
                                        {crumb}
                                    </span>
                                </React.Fragment>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Info Banner */}
                {showPromo && (
                    <div className="relative bg-[#fafbfc] rounded-xl p-8 border border-slate-200 overflow-hidden flex items-center justify-between shadow-sm">
                        <div className="flex-1 relative z-10">
                            <div className="flex items-center gap-3 mb-2">
                                <div className="w-2 h-2 bg-[#c1e663] rounded-full animate-pulse" />
                                <h2 className="text-[18px] font-bold text-[#1a1c21]">
                                    {title}
                                </h2>
                            </div>
                            <p className="text-[14px] text-slate-500 leading-relaxed max-w-2xl">
                                {subtitle || "Configure your preferences and system settings below."}
                            </p>
                        </div>
                        <div className="flex items-center gap-4 relative z-10">
                            <button
                                onClick={() => setShowPromo(false)}
                                className="w-9 h-9 flex items-center justify-center bg-white rounded-lg border border-slate-200 text-slate-400 hover:text-slate-900 transition-all shadow-sm"
                            >
                                <ChevronDown size={18} />
                            </button>
                        </div>
                        {/* Decorative Element */}
                        <div className="absolute right-[-20px] top-[-20px] opacity-[0.03] pointer-events-none">
                            <Settings size={180} />
                        </div>
                    </div>
                )}

                {/* Main Content */}
                <div className="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    {children}
                </div>
            </div>

            <style dangerouslySetInnerHTML={{
                __html: `
                @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
            `}} />
        </AdminLayout>
    );
}
