import React, { useState } from 'react';
import SettingsLayout from '../SettingsLayout';
import { CreditCard, ShieldCheck, Save, Loader2, Eye, EyeOff, Copy, Check, Clock, RefreshCcw, Activity, Server, Database } from 'lucide-react';
import { useForm } from '@inertiajs/react';
import { toast } from 'react-toastify';

export default function FinancialGateway({ settings }) {
    const [showSecret, setShowSecret] = useState(false);
    const [showWebhook, setShowWebhook] = useState(false);
    const [copiedField, setCopiedField] = useState(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        stripe_mode: settings.stripe_mode || 'test',
        stripe_key: settings.stripe_key || '',
        stripe_secret: settings.stripe_secret || '',
        stripe_webhook_secret: settings.stripe_webhook_secret || '',
    });

    const handleCopy = (text, field) => {
        if (!text) return;
        navigator.clipboard.writeText(text);
        setCopiedField(field);
        toast.success(`${field.replace('_', ' ')} copied to clipboard!`);
        setTimeout(() => setCopiedField(null), 2000);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.settings.update'), {
            onSuccess: () => toast.success('Gateway configuration synchronized!'),
            onError: () => toast.error('Check your API credentials.'),
            preserveScroll: true,
        });
    };

    const toggleMode = () => {
        setData('stripe_mode', data.stripe_mode === 'live' ? 'test' : 'live');
    };

    return (
        <SettingsLayout
            title="Payment Gateways"
            subtitle="Connect and manage your global payment processors for automated transaction orchestration."
            breadcrumbs={["Financial", "Gateway"]}
        >
            <div className="p-8 font-['Plus_Jakarta_Sans',sans-serif]">
                <form onSubmit={handleSubmit} className="max-w-8xl space-y-10">

                    {/* Integration Card */}
                    <div className="flex items-center justify-between p-4 bg-white border border-slate-100 rounded-md shadow-sm">
                        <div className="flex items-center gap-4">
                            <div className="w-12 h-12 bg-slate-50 rounded-md border border-slate-100 flex items-center justify-center text-[#d9f196]">
                                <CreditCard size={24} />
                            </div>
                            <div>
                                <h4 className="text-[14px] font-extrabold text-[#1a1c21]">Stripe integration</h4>
                                <p className="text-[11px] text-slate-500 font-medium italic">Global credit card & local payment orchestration</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-4">
                            <span className={`text-[10px] font-bold px-3 py-1 rounded-md ${data.stripe_mode === 'live' ? 'text-green-600 bg-green-50' : 'text-orange-600 bg-orange-50'
                                }`}>
                                {data.stripe_mode === 'live' ? 'Production mode' : 'Sandbox (Test)'}
                            </span>
                            <button
                                type="button"
                                onClick={toggleMode}
                                className={`w-10 h-5 rounded-full relative transition-all duration-300 ${data.stripe_mode === 'live' ? 'bg-[#d9f196]' : 'bg-slate-200'
                                    }`}
                            >
                                <div className={`absolute top-0.5 w-4 h-4 bg-white rounded-full shadow-sm transition-all duration-300 ${data.stripe_mode === 'live' ? 'right-0.5' : 'left-0.5'
                                    }`}></div>
                            </button>
                        </div>
                    </div>

                    {/* API Section */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 pt-2">
                        <div className="col-span-2 flex items-center gap-2 mb-1">
                            <ShieldCheck size={16} className="text-[#d9f196]" />
                            <h3 className="text-[13px] font-extrabold text-slate-400">API credentials</h3>
                        </div>

                        <div className="col-span-2 space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Stripe publishable key</label>
                            <div className="relative group">
                                <Server size={14} className="absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="text"
                                    value={data.stripe_key}
                                    onChange={e => setData('stripe_key', e.target.value)}
                                    placeholder="pk_live_..."
                                    className={`w-full h-10 pl-10 pr-12 bg-slate-50/50 border ${errors.stripe_key ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[13px] font-mono font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                />
                                <button
                                    type="button"
                                    onClick={() => handleCopy(data.stripe_key, 'key')}
                                    className="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-[#1a1c21] transition-colors"
                                >
                                    {copiedField === 'key' ? <Check size={14} className="text-green-500" /> : <Copy size={14} />}
                                </button>
                            </div>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Stripe secret key</label>
                            <div className="relative group">
                                <section className="relative group">
                                    <input
                                        type={showSecret ? "text" : "password"}
                                        value={data.stripe_secret}
                                        onChange={e => setData('stripe_secret', e.target.value)}
                                        placeholder="sk_live_..."
                                        className={`w-full h-10 px-4 bg-slate-50/50 border ${errors.stripe_secret ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[13px] font-mono font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    />
                                    <div className="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center gap-3">
                                        <button
                                            type="button"
                                            onClick={() => handleCopy(data.stripe_secret, 'secret')}
                                            className="text-slate-300 hover:text-[#1a1c21] transition-colors"
                                        >
                                            {copiedField === 'secret' ? <Check size={14} className="text-green-500" /> : <Copy size={14} />}
                                        </button>
                                        <button
                                            type="button"
                                            onClick={() => setShowSecret(!showSecret)}
                                            className="text-slate-300 hover:text-[#1a1c21] transition-colors"
                                        >
                                            {showSecret ? <EyeOff size={16} /> : <Eye size={16} />}
                                        </button>
                                    </div>
                                </section>
                            </div>
                        </div>

                        <div className="space-y-1">
                            <label className="text-[12px] font-bold text-slate-700 ml-1">Webhook secret</label>
                            <div className="relative group">
                                <input
                                    type={showWebhook ? "text" : "password"}
                                    value={data.stripe_webhook_secret}
                                    onChange={e => setData('stripe_webhook_secret', e.target.value)}
                                    placeholder="whsec_..."
                                    className={`w-full h-10 px-4 bg-slate-50/50 border ${errors.stripe_webhook_secret ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[13px] font-mono font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                />
                                <div className="absolute right-3.5 top-1/2 -translate-y-1/2 flex items-center gap-3">
                                    <button
                                        type="button"
                                        onClick={() => handleCopy(data.stripe_webhook_secret, 'webhook')}
                                        className="text-slate-300 hover:text-[#1a1c21] transition-colors"
                                    >
                                        {copiedField === 'webhook' ? <Check size={14} className="text-green-500" /> : <Copy size={14} />}
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => setShowWebhook(!showWebhook)}
                                        className="text-slate-300 hover:text-[#1a1c21] transition-colors"
                                    >
                                        {showWebhook ? <EyeOff size={16} /> : <Eye size={16} />}
                                    </button>
                                </div>
                            </div>
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
                            Synchronize gateway
                        </button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    );
}
