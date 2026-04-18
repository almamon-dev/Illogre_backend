import React, { useState } from 'react';
import SettingsLayout from '../SettingsLayout';
import { Mail, Server, Key, Send, Save, Globe2, User, Eye, EyeOff, ShieldCheck, RefreshCcw } from 'lucide-react';
import { useForm } from '@inertiajs/react';
import { toast } from 'react-toastify';

export default function EmailSettings({ settings }) {
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        mail_mailer: settings.mail_mailer || 'smtp',
        mail_host: settings.mail_host || '',
        mail_port: settings.mail_port || '',
        mail_username: settings.mail_username || '',
        mail_password: settings.mail_password || '',
        mail_encryption: settings.mail_encryption || 'tls',
        mail_from_address: settings.mail_from_address || '',
        mail_from_name: settings.mail_from_name || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.settings.system.email.update'), {
            onSuccess: () => toast.success('Protocol configuration synchronized!'),
            onError: () => toast.error('Configuration update failed.'),
            preserveScroll: true,
        });
    };

    return (
        <SettingsLayout
            title="Communication Protocol"
            subtitle="Configure SMTP servers and automated email gateways. Changes directly reflect in platform environment."
            breadcrumbs={["System", "Email"]}
        >
            <div className="p-8 font-['Plus_Jakarta_Sans',sans-serif]">
                <form onSubmit={handleSubmit} className="max-w-8xl space-y-10">

                    {/* Identification Section */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 pt-2">
                        <div className="col-span-2 flex items-center gap-2 mb-1">
                            <Server size={16} className="text-[#d9f196]" />
                            <h3 className="text-[13px] font-extrabold text-slate-400">SMTP Server config</h3>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Mail driver</label>
                            <select
                                value={data.mail_mailer}
                                onChange={e => setData('mail_mailer', e.target.value)}
                                className="w-full h-10 px-4 bg-slate-50/50 border border-slate-200 rounded-md text-[13px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all cursor-pointer"
                            >
                                <option value="smtp">SMTP Relay</option>
                                <option value="mailgun">Mailgun Protocol</option>
                                <option value="postmark">Postmark Bridge</option>
                                <option value="ses">Amazon SES Gateway</option>
                            </select>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Encryption protocol</label>
                            <div className="flex gap-2">
                                {['tls', 'ssl', 'none'].map((enc) => (
                                    <button
                                        key={enc}
                                        type="button"
                                        onClick={() => setData('mail_encryption', enc)}
                                        className={`flex-1 h-10 rounded-md border text-[12px] font-extrabold transition-all active:scale-95 ${data.mail_encryption === enc
                                            ? 'bg-[#d9f196] border-[#d9f196] text-[#1a1c21]'
                                            : 'bg-white border-slate-200 text-slate-400 hover:border-slate-300'
                                            }`}
                                    >
                                        {enc.toUpperCase()}
                                    </button>
                                ))}
                            </div>
                        </div>

                        <div className="md:col-span-1.5 space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">SMTP host address</label>
                            <div className="relative group">
                                <Globe2 size={14} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="text"
                                    value={data.mail_host}
                                    onChange={e => setData('mail_host', e.target.value)}
                                    placeholder="e.g. smtp.gmail.com"
                                    className={`w-full h-10 pl-10 pr-4 bg-slate-50/50 border ${errors.mail_host ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[13px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                />
                            </div>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Service port</label>
                            <input
                                type="text"
                                value={data.mail_port}
                                onChange={e => setData('mail_port', e.target.value)}
                                placeholder="587"
                                className={`w-full h-10 px-4 bg-slate-50/50 border ${errors.mail_port ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[13px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                            />
                        </div>
                    </div>

                    {/* Authentication Section */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 pt-8 border-t border-slate-100">
                        <div className="col-span-2 flex items-center gap-2 mb-1">
                            <Key size={16} className="text-[#d9f196]" />
                            <h3 className="text-[13px] font-extrabold text-slate-400">Authentication credentials</h3>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Username / email</label>
                            <div className="relative group">
                                <Mail size={14} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="text"
                                    value={data.mail_username}
                                    onChange={e => setData('mail_username', e.target.value)}
                                    className={`w-full h-10 pl-10 pr-4 bg-slate-50/50 border ${errors.mail_username ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[13px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="SMTP login name"
                                />
                            </div>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Access password</label>
                            <div className="relative group">
                                <Key size={14} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type={showPassword ? "text" : "password"}
                                    value={data.mail_password}
                                    onChange={e => setData('mail_password', e.target.value)}
                                    className={`w-full h-10 pl-10 pr-12 bg-slate-50/50 border ${errors.mail_password ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[13px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="••••••••••••"
                                />
                                <button
                                    type="button"
                                    onClick={() => setShowPassword(!showPassword)}
                                    className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-[#1a1c21] transition-colors"
                                >
                                    {showPassword ? <EyeOff size={16} /> : <Eye size={16} />}
                                </button>
                            </div>
                        </div>
                    </div>

                    {/* Sender Identity */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 pt-8 border-t border-slate-100">
                        <div className="col-span-2 flex items-center gap-2 mb-1">
                            <User size={16} className="text-[#d9f196]" />
                            <h3 className="text-[13px] font-extrabold text-slate-400">Sender profile</h3>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">From address</label>
                            <input
                                type="email"
                                value={data.mail_from_address}
                                onChange={e => setData('mail_from_address', e.target.value)}
                                className={`w-full h-10 px-4 bg-slate-50/50 border ${errors.mail_from_address ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                placeholder="noreply@subscriptionlab.com"
                            />
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">From name</label>
                            <input
                                type="text"
                                value={data.mail_from_name}
                                onChange={e => setData('mail_from_name', e.target.value)}
                                className={`w-full h-10 px-4 bg-slate-50/50 border ${errors.mail_from_name ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                placeholder="Platform Notification"
                            />
                        </div>
                    </div>

                    {/* Footer Actions */}
                    <div className="pt-8 border-t border-slate-50 flex items-center justify-end gap-4">
                        <button
                            type="button"
                            onClick={() => reset()}
                            className="px-5 py-2 text-[12px] font-bold text-slate-400 bg-slate-50 rounded-md hover:text-slate-600 transition-colors"
                        >
                            Reset
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#d9f196] text-[#1a1c21] px-8 py-2.5 rounded-md font-bold text-[13px] hover:bg-[#c9e186] transition-all shadow-md shadow-lime-200/30 flex items-center gap-2 disabled:opacity-70 active:scale-95"
                        >
                            {processing ? <RefreshCcw size={16} className="animate-spin" /> : <Save size={16} />}
                            Synchronize configurations
                        </button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    );
}

