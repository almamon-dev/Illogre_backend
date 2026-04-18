import React from 'react';
import SettingsLayout from '../SettingsLayout';
import { useForm } from '@inertiajs/react';
import { Shield, Key, Lock, ShieldAlert, Save, RefreshCcw } from 'lucide-react';
import { toast } from 'react-toastify';

export default function Security() {
    const { data, setData, post, processing, errors, reset } = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.settings.general.security.update'), {
            onSuccess: () => {
                toast.success('Password updated successfully!');
                reset();
            },
            onError: () => toast.error('Check fields for errors.'),
        });
    };

    return (
        <SettingsLayout
            title="Security Configuration"
            subtitle="Strengthen your account security and manage authentication protocols."
            breadcrumbs={["General", "Security"]}
        >
            <div className="p-10 font-['Plus_Jakarta_Sans',sans-serif]">
                <form onSubmit={handleSubmit} className="max-w-8xl space-y-12">
                    {/* Information Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div className="col-span-2 flex items-center gap-2 mb-2">
                            <Lock size={18} className="text-[#d9f196]" />
                            <h3 className="text-[14px] font-extrabold text-slate-400 uppercase tracking-widest">Password Authentication</h3>
                        </div>

                        <div className="col-span-2 md:col-span-1 space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Current Secret Key</label>
                            <div className="relative group">
                                <Key size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="password"
                                    value={data.current_password}
                                    onChange={e => setData('current_password', e.target.value)}
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.current_password ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="Enter current password"
                                />
                            </div>
                            {errors.current_password && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.current_password}</p>}
                        </div>

                        <div className="hidden md:block"></div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">New Access Password</label>
                            <div className="relative group">
                                <Lock size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={e => setData('password', e.target.value)}
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.password ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="Minimum 8 characters"
                                />
                            </div>
                            {errors.password && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.password}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Verification Confirmation</label>
                            <div className="relative group">
                                <Lock size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="password"
                                    value={data.password_confirmation}
                                    onChange={e => setData('password_confirmation', e.target.value)}
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.password_confirmation ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="Re-type new password"
                                />
                            </div>
                            {errors.password_confirmation && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.password_confirmation}</p>}
                        </div>
                    </div>

                    {/* Footer Actions */}
                    <div className="pt-10 border-t border-slate-50 flex items-center justify-end gap-5">
                        <button
                            type="button"
                            onClick={() => reset()}
                            className="px-6 py-3 text-[14px] font-extrabold bg-slate-50/50 text-slate-400 hover:text-slate-600 transition-colors"
                        >
                            Reset Fields
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#d9f196] text-[#1a1c21] px-10 py-3 rounded-md font-black text-[14px] hover:bg-[#b0d552] transition-all shadow-lg shadow-lime-200/50 flex items-center gap-2 disabled:opacity-70 active:scale-95"
                        >
                            {processing ? <RefreshCcw size={18} className="animate-spin" strokeWidth={3} /> : <Save size={18} strokeWidth={3} />}
                            Revise Credentials
                        </button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    );
}
