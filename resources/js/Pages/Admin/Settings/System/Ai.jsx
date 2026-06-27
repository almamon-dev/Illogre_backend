import React, { useState } from 'react';
import SettingsLayout from '../SettingsLayout';
import { Server, Key, Save, Cpu, RefreshCcw, EyeOff, Eye, Globe } from 'lucide-react';
import { useForm } from '@inertiajs/react';
import { toast } from 'react-toastify';

export default function AiSettings({ settings }) {
    const [showPassword, setShowPassword] = useState(false);
    const { data, setData, post, processing, errors, reset } = useForm({
        ai_provider: settings.ai_provider || 'openai',
        openai_api_key: settings.openai_api_key || '',
        ai_model: settings.ai_model || 'gpt-4o',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.settings.system.ai.update'), {
            onSuccess: () => toast.success('AI configuration synchronized!'),
            onError: () => toast.error('Configuration update failed.'),
            preserveScroll: true,
        });
    };

    return (
        <SettingsLayout
            title="Global AI Configuration"
            subtitle="Configure artificial intelligence features globally. Once enabled, business owners can use AI without needing their own API keys."
            breadcrumbs={["System", "AI Configuration"]}
        >
            <div className="p-6 font-['Plus_Jakarta_Sans',sans-serif]">
                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div className="lg:col-span-2">
                        <form onSubmit={handleSubmit} className="space-y-6">

                            <div className="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                                <div className="px-5 py-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Cpu size={15} className="text-[#1a1c21]" />
                                        <h3 className="text-[13px] font-extrabold text-slate-800">AI Service Details</h3>
                                    </div>
                                </div>

                                <div className="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    {/* Provider */}
                                    <div className="space-y-1.5">
                                        <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Provider</label>
                                        <div className="relative group">
                                            <Server size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                            <select
                                                value={data.ai_provider}
                                                onChange={e => setData('ai_provider', e.target.value)}
                                                className="w-full h-9 pl-9 pr-4 bg-slate-50/50 border border-slate-200 rounded-md text-[13px] font-semibold text-[#1a1c21] focus:outline-none focus:ring-2 focus:ring-[#d9f196] focus:border-transparent transition-all cursor-pointer appearance-none"
                                            >
                                                <option value="openai">OpenAI</option>
                                            </select>
                                        </div>
                                    </div>

                                    {/* Default Model */}
                                    <div className="space-y-1.5">
                                        <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Default Model</label>
                                        <input
                                            type="text"
                                            value={data.ai_model}
                                            onChange={e => setData('ai_model', e.target.value)}
                                            placeholder="e.g. gpt-4o"
                                            className="w-full h-9 px-3 bg-slate-50/50 border border-slate-200 rounded-md text-[13px] font-semibold text-[#1a1c21] focus:outline-none focus:ring-2 focus:ring-[#d9f196] focus:border-transparent transition-all placeholder:text-slate-400"
                                        />
                                    </div>

                                    {/* API Key */}
                                    <div className="col-span-2 space-y-1.5 pt-2">
                                        <label className="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Master API Key</label>
                                        <div className="relative group">
                                            <Key size={14} className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                            <input
                                                type={showPassword ? "text" : "password"}
                                                value={data.openai_api_key}
                                                onChange={e => setData('openai_api_key', e.target.value)}
                                                className={`w-full h-9 pl-9 pr-10 bg-slate-50/50 border ${errors.openai_api_key ? 'border-red-400 focus:ring-red-200' : 'border-slate-200 focus:ring-[#d9f196]'} rounded-md text-[13px] font-semibold text-[#1a1c21] focus:outline-none focus:ring-2 focus:border-transparent transition-all placeholder:text-slate-400`}
                                                placeholder="sk-..."
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setShowPassword(!showPassword)}
                                                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 transition-colors"
                                            >
                                                {showPassword ? <EyeOff size={14} /> : <Eye size={14} />}
                                            </button>
                                        </div>
                                        <p className="text-[11px] text-slate-400">This key automatically authenticates AI requests for all active business owners.</p>
                                        {errors.openai_api_key && (
                                            <p className="text-[11px] text-red-500 font-semibold">{errors.openai_api_key}</p>
                                        )}
                                    </div>
                                </div>
                            </div>

                            {/* Footer Actions */}
                            <div className="flex items-center justify-end gap-3 pt-2">
                                <button
                                    type="button"
                                    onClick={() => reset()}
                                    className="px-4 py-2 text-[12px] font-bold text-slate-500 hover:text-slate-800 transition-colors bg-white border border-slate-200 rounded-md shadow-sm"
                                >
                                    Discard Changes
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-[#1a1c21] text-white px-5 py-2 rounded-md font-bold text-[12px] hover:bg-black transition-all flex items-center gap-2 shadow-sm disabled:opacity-70 active:scale-95"
                                >
                                    {processing ? <RefreshCcw size={14} className="animate-spin text-[#d9f196]" /> : <Save size={14} className="text-[#d9f196]" />}
                                    Save Configuration
                                </button>
                            </div>
                        </form>
                    </div>

                    {/* Right side info panel */}
                    <div className="lg:col-span-1">
                        <div className="bg-slate-50/50 border border-slate-200 rounded-lg p-5 sticky top-6">
                            <h4 className="text-[13px] font-extrabold text-slate-800 flex items-center gap-2 mb-4">
                                <Globe size={15} className="text-[#1a1c21]" />
                                Global AI Configuration Guide
                            </h4>
                            
                            <div className="space-y-4 text-[12px] text-slate-600 leading-relaxed">
                                <div>
                                    <span className="font-bold text-slate-800 block mb-1">Supported Providers</span>
                                    The platform currently integrates with OpenAI's conversational and generative models. Make sure your API key has billing enabled.
                                </div>
                                
                                <div>
                                    <span className="font-bold text-slate-800 block mb-1">Model Selection</span>
                                    We recommend <code className="bg-white px-1.5 py-0.5 rounded border border-slate-200 text-[#1a1c21] font-semibold text-[11px]">gpt-4o</code> for the best balance of speed, cost, and reasoning quality in customer support scenarios.
                                </div>
                                
                                <div className="bg-blue-50/50 p-3 rounded-md border border-blue-100">
                                    <span className="font-bold text-blue-800 block mb-1">How it affects owners</span>
                                    When you configure this global API key, business owners will no longer be prompted to provide their own OpenAI keys. The platform will route all their automated replies and AI-assisted tasks through this central key.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </SettingsLayout>
    );
}

