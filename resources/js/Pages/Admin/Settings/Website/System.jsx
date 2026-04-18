import React from 'react';
import SettingsLayout from '../SettingsLayout';
import { Head, useForm } from '@inertiajs/react';
import {
    Layout, Globe, Search, Cpu,
    Save, RefreshCcw, Activity, Globe2,
    Smartphone, Database
} from 'lucide-react';
import { toast } from 'react-toastify';

export default function WebsiteSystem({ settings }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        site_name: settings.site_name || '',
        site_url: settings.site_url || '',
        title_prefix: settings.title_prefix || '',
        meta_description: settings.meta_description || '',
        keywords: settings.keywords || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.settings.update'), {
            onSuccess: () => toast.success('Platform configuration synchronized!'),
            onError: () => toast.error('Configuration update failed.'),
            preserveScroll: true,
        });
    };


    return (
        <SettingsLayout
            title="System Architecture"
            subtitle="Manage your platform's core configuration, SEO metadata, and server-level protocols."
            breadcrumbs={["Website", "System"]}
        >
            <div className="p-10 font-['Plus_Jakarta_Sans',sans-serif]">
                <form onSubmit={handleSubmit} className="max-w-8xl space-y-12">
                    {/* Form Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 pt-6 ">
                        <div className="col-span-2 flex items-center gap-2 mb-2">
                            <Database size={18} className="text-[#d9f196]" />
                            <h3 className="text-[14px] font-extrabold text-slate-400">Global Variables</h3>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Platform Name</label>
                            <div className="relative group">
                                <Smartphone size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="text"
                                    value={data.site_name}
                                    onChange={e => setData('site_name', e.target.value)}
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.site_name ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="Enter platform display name"
                                />
                            </div>
                            {errors.site_name && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.site_name}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Canonical URL</label>
                            <div className="relative group">
                                <Globe2 size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="text"
                                    value={data.site_url}
                                    onChange={e => setData('site_url', e.target.value)}
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.site_url ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="https://yourdomain.com"
                                />
                            </div>
                            {errors.site_url && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.site_url}</p>}
                        </div>

                        <div className="col-span-2 space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">SEO Title Prefix</label>
                            <div className="relative group">
                                <Search size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="text"
                                    value={data.title_prefix}
                                    onChange={e => setData('title_prefix', e.target.value)}
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.title_prefix ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                    placeholder="e.g. | Premium SaaS Solutions"
                                />
                            </div>
                        </div>


                    </div>

                    {/* Footer Actions */}
                    <div className="flex items-center justify-end gap-5">
                        <button
                            type="button"
                            onClick={() => reset()}
                            className="px-6 py-3 text-[14px] font-extrabold text-slate-400 bg-slate-100 rounded-md hover:text-slate-600 transition-colors"
                        >
                            Reset Parameters
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#d9f196] text-[#1a1c21] px-10 py-3 rounded-md font-black text-[14px] hover:bg-[#c9e186] transition-all shadow-lg shadow-lime-200/50 flex items-center gap-2 disabled:opacity-70 active:scale-95"
                        >
                            {processing ? <RefreshCcw size={18} className="animate-spin" strokeWidth={3} /> : <Save size={18} strokeWidth={3} />}
                            Synchronize Backend
                        </button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    );
}
