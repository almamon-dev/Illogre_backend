import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head } from '@inertiajs/react';
import { 
    Home, Settings, ChevronDown, Check, 
    AlertCircle, Info, Save, RotateCcw
} from 'lucide-react';

export default function SettingsPlaceholder({ title }) {
    const [showPromo, setShowPromo] = useState(true);

    return (
        <AdminLayout>
            <Head title={title} />

            <div className="space-y-6 max-w-[1240px] mx-auto pb-20 px-4 sm:px-6">
                {/* Top Header - Matching Categories Design */}
                <div className="flex items-center justify-between pt-2">
                    <div className="flex items-center gap-4">
                        <h1 className="text-[24px] font-bold text-[#2f3344] tracking-tight">
                            {title}
                        </h1>
                        <div className="flex items-center gap-2 text-[13px] text-[#727586] mt-1">
                            <Home size={16} className="text-[#727586]" />
                            <span className="text-[#c3c4ca]">-</span>
                            <span>Settings</span>
                            <span className="text-[#c3c4ca]">-</span>
                            <span className="text-[#673ab7] font-medium">{title}</span>
                        </div>
                    </div>
                    <div className="flex items-center gap-3">
                         <button className="inline-flex items-center bg-white border border-[#e3e4e8] text-[#2f3344] px-5 py-[10px] rounded-[8px] font-bold text-[14px] hover:bg-slate-50 transition-all shadow-sm">
                            <RotateCcw size={18} className="mr-2" />
                            Reset
                        </button>
                        <button className="inline-flex items-center bg-[#673ab7] text-white px-6 py-[10px] rounded-[8px] font-bold text-[14px] hover:bg-[#5e35b1] transition-all shadow-sm">
                            <Save size={18} className="mr-2" />
                            Save Changes
                        </button>
                    </div>
                </div>

                {/* Promo/Info Banner - Matching Categories Design */}
                {showPromo && (
                    <div className="relative bg-[#f4f0ff] rounded-[12px] p-6 border border-[#e9e3ff] overflow-hidden flex items-center justify-between">
                        <div className="flex-1">
                            <h2 className="text-[18px] font-bold text-[#2f3344] mb-1">
                                Configuration Mode: {title}
                            </h2>
                            <p className="text-[14px] text-[#727586]">
                                Update your system preferences carefully. Some changes might require a page reload to take effect.
                            </p>
                        </div>
                        <div className="flex items-center gap-4">
                            <div className="w-[100px] h-[60px] relative hidden md:block">
                                <div className="absolute right-0 top-0 text-[#673ab7] opacity-20 transform rotate-12">
                                    <Settings size={40} />
                                </div>
                                <div className="absolute right-10 bottom-0 text-[#673ab7] opacity-20">
                                    <Check size={30} />
                                </div>
                            </div>
                            <button 
                                onClick={() => setShowPromo(false)}
                                className="w-8 h-8 flex items-center justify-center bg-white rounded-lg border border-[#e3e4e8] text-[#727586] hover:bg-slate-50 transition-all"
                            >
                                <ChevronDown size={18} />
                            </button>
                        </div>
                        <div className="absolute right-0 top-0 bottom-0 w-24 bg-gradient-to-l from-[#673ab7]/5 to-transparent pointer-events-none"></div>
                    </div>
                )}

                {/* Main Content Card - Matching Categories Design */}
                <div className="bg-white rounded-[12px] border border-[#e3e4e8] shadow-sm overflow-hidden">
                    {/* Header Tabs Style */}
                    <div className="px-8 border-b border-[#e3e4e8]">
                        <div className="flex gap-10">
                            <button className="pt-5 pb-4 text-[14px] font-bold transition-all relative text-[#673ab7]">
                                General Configuration
                                <div className="absolute bottom-0 left-0 right-0 h-[3px] bg-[#673ab7] rounded-t-full"></div>
                            </button>
                            <button className="pt-5 pb-4 text-[14px] font-bold transition-all relative text-[#727586] hover:text-[#2f3344]">
                                Advanced
                            </button>
                            <button className="pt-5 pb-4 text-[14px] font-bold transition-all relative text-[#727586] hover:text-[#2f3344]">
                                Logs
                            </button>
                        </div>
                    </div>

                    <div className="p-8">
                        <div className="max-w-3xl space-y-8">
                            {/* Section 1 */}
                            <div>
                                <h3 className="text-[16px] font-bold text-[#2f3344] mb-4 flex items-center gap-2">
                                    <Info size={18} className="text-[#673ab7]" />
                                    Basic Information
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-2">
                                        <label className="text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Display Name</label>
                                        <input 
                                            type="text" 
                                            placeholder="Enter name"
                                            className="w-full h-[48px] px-4 bg-white border border-[#e3e4e8] rounded-[8px] text-[14px] focus:outline-none focus:border-[#673ab7] focus:ring-1 focus:ring-[#673ab7] transition-all"
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <label className="text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Status</label>
                                        <div className="relative">
                                            <select className="w-full h-[48px] px-4 bg-white border border-[#e3e4e8] rounded-[8px] text-[14px] appearance-none focus:outline-none focus:border-[#673ab7] focus:ring-1 focus:ring-[#673ab7] transition-all cursor-pointer">
                                                <option>Enabled</option>
                                                <option>Disabled</option>
                                                <option>Maintenance Mode</option>
                                            </select>
                                            <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-[#727586]">
                                                <ChevronDown size={16} />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {/* Section 2 */}
                            <div className="pt-6 border-t border-[#f1f2f4]">
                                <h3 className="text-[16px] font-bold text-[#2f3344] mb-4 flex items-center gap-2">
                                    <Settings size={18} className="text-[#673ab7]" />
                                    Detailed Preferences
                                </h3>
                                <div className="space-y-6">
                                    <div className="space-y-2">
                                        <label className="text-[13px] font-bold text-[#2f3344] uppercase tracking-wider">Extended Description</label>
                                        <textarea 
                                            rows="4" 
                                            placeholder="Provide more context for this setting category..."
                                            className="w-full p-4 bg-white border border-[#e3e4e8] rounded-[8px] text-[14px] focus:outline-none focus:border-[#673ab7] focus:ring-1 focus:ring-[#673ab7] transition-all resize-none"
                                        ></textarea>
                                    </div>
                                    
                                    <div className="flex items-center justify-between p-4 bg-[#f8f9fa] rounded-[8px] border border-[#e3e4e8]">
                                        <div className="flex items-center gap-3">
                                            <div className="w-10 h-10 rounded-full bg-white border border-[#e3e4e8] flex items-center justify-center text-[#673ab7]">
                                                <AlertCircle size={20} />
                                            </div>
                                            <div>
                                                <p className="text-[14px] font-bold text-[#2f3344]">Email Notifications</p>
                                                <p className="text-[12px] text-[#727586]">Send automated alerts when this setting is modified</p>
                                            </div>
                                        </div>
                                        <div className="w-12 h-6 bg-[#673ab7] rounded-full relative cursor-pointer">
                                            <div className="absolute right-1 top-1 w-4 h-4 bg-white rounded-full shadow-sm"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Footer Area - Matching Table Pagination Feel */}
                    <div className="px-8 py-5 border-t border-[#e3e4e8] bg-[#fafbfc] flex items-center justify-between">
                        <p className="text-[13px] text-[#727586]">
                            Last updated: <span className="font-bold text-[#2f3344]">January 29, 2026</span>
                        </p>
                        <div className="flex gap-4">
                            <button className="text-[13px] font-bold text-[#727586] hover:text-[#2f3344] transition-colors">
                                View History
                            </button>
                            <button className="text-[13px] font-bold text-[#673ab7] hover:underline transition-colors">
                                Documentation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
