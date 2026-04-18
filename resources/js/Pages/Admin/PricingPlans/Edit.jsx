import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { ChevronLeft, Plus, X, Save } from 'lucide-react';

export default function Edit({ auth, pricingPlan }) {
    const [formData, setFormData] = useState({
        name: pricingPlan.name || '',
        price: pricingPlan.price || '',
        billing_period: pricingPlan.billing_period || 'monthly',
        trial_days: pricingPlan.trial_days || 0,
        features: pricingPlan.features && pricingPlan.features.length > 0 ? pricingPlan.features : [''],
        is_active: pricingPlan.is_active ?? true,
        is_popular: pricingPlan.is_popular ?? false,
        order: pricingPlan.order || 0,
    });

    const [errors, setErrors] = useState({});

    const handleSubmit = (e) => {
        e.preventDefault();
        const cleanedFeatures = formData.features.filter(f => f.trim() !== '');
        router.put(route('admin.pricing-plans.update', pricingPlan.id), {
            ...formData,
            features: cleanedFeatures,
        }, {
            onError: (errors) => setErrors(errors),
        });
    };

    const addFeature = () => {
        setFormData({ ...formData, features: [...formData.features, ''] });
    };

    const removeFeature = (index) => {
        const newFeatures = formData.features.filter((_, i) => i !== index);
        setFormData({ ...formData, features: newFeatures.length > 0 ? newFeatures : [''] });
    };

    const updateFeature = (index, value) => {
        const newFeatures = [...formData.features];
        newFeatures[index] = value;
        setFormData({ ...formData, features: newFeatures });
    };

    return (
        <AdminLayout user={auth.user}>
            <Head title={`Edit ${pricingPlan.name}`} />

            <div className="min-h-screen bg-[#fafbfc] font-['Plus_Jakarta_Sans',sans-serif]">
                <div className="max-w-5xl mx-auto px-6 py-8">
                    {/* Header */}
                    <div className="flex items-center justify-between mb-8">
                        <div className="flex items-center gap-3">
                            <Link
                                 href={route('admin.pricing-plans.index')}
                                 className="w-10 h-10 flex items-center justify-center bg-white border border-slate-200 rounded-lg text-slate-500 hover:text-[#1a1c21] hover:border-slate-300 transition-all"
                            >
                                <ChevronLeft size={20} strokeWidth={2.5} />
                            </Link>
                            <div>
                                <h1 className="text-[24px] font-bold text-[#1a1c21]">Edit Plan: {pricingPlan.name}</h1>
                                <p className="text-[13px] text-slate-500 font-medium -mt-1">Update existing subscription tier details</p>
                            </div>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link
                                href={route('admin.pricing-plans.index')}
                                className="px-5 py-2.5 bg-white border border-slate-200 text-[#1a1c21] hover:bg-slate-50 rounded-lg text-[13px] font-bold transition-all shadow-sm"
                            >
                                Cancel
                            </Link>
                            <button
                                onClick={handleSubmit}
                                className="inline-flex items-center gap-2 px-6 py-2.5 bg-[#c1e663] text-[#1a1c21] hover:bg-[#b0d552] rounded-lg text-[13px] font-bold transition-all shadow-sm"
                            >
                                <Save size={16} strokeWidth={2.5} />
                                Update Plan
                            </button>
                        </div>
                    </div>

                    {/* Form Body */}
                    <div className="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                        <form onSubmit={handleSubmit} className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {/* Plan Name */}
                            <div className="col-span-1 space-y-1.5">
                                <label className="text-[13px] font-bold text-slate-700">Plan Name <span className="text-rose-500">*</span></label>
                                <input
                                    type="text"
                                    value={formData.name}
                                    onChange={(e) => setFormData({ ...formData, name: e.target.value })}
                                    className={`w-full h-11 px-4 bg-white border ${errors.name ? 'border-rose-300' : 'border-slate-200'} rounded-lg text-[14px] focus:outline-none focus:border-[#c1e663] focus:ring-1 focus:ring-[#c1e663] transition-all`}
                                    placeholder="Enter plan name"
                                />
                                {errors.name && <p className="text-[12px] text-rose-500 font-medium">{errors.name}</p>}
                            </div>

                            {/* Billing Period */}
                            <div className="col-span-1 space-y-1.5">
                                <label className="text-[13px] font-bold text-slate-700">Billing Period <span className="text-rose-500">*</span></label>
                                <select
                                    value={formData.billing_period}
                                    onChange={(e) => setFormData({ ...formData, billing_period: e.target.value })}
                                    className="w-full h-11 px-4 bg-white border border-slate-200 rounded-lg text-[14px] focus:outline-none focus:border-[#c1e663] focus:ring-1 focus:ring-[#c1e663] transition-all"
                                >
                                    <option value="monthly">Monthly</option>
                                    <option value="annual">Annual</option>
                                    <option value="quarterly">Quarterly</option>
                                    <option value="trial">Free Trial</option>
                                </select>
                            </div>

                            {/* Price */}
                            <div className="col-span-1 space-y-1.5">
                                <label className="text-[13px] font-bold text-slate-700">Price (USD) <span className="text-rose-500">*</span></label>
                                <div className="relative">
                                    <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-medium text-[14px]">$</span>
                                    <input
                                        type="number"
                                        step="0.01"
                                        value={formData.price}
                                        onChange={(e) => setFormData({ ...formData, price: e.target.value })}
                                        className={`w-full h-11 pl-8 pr-4 bg-white border ${errors.price ? 'border-rose-300' : 'border-slate-200'} rounded-lg text-[14px] focus:outline-none focus:border-[#c1e663] focus:ring-1 focus:ring-[#c1e663] transition-all`}
                                        placeholder="0.00"
                                    />
                                </div>
                                {errors.price && <p className="text-[12px] text-rose-500 font-medium">{errors.price}</p>}
                            </div>

                            {/* Order */}
                            <div className="col-span-1 space-y-1.5">
                                <label className="text-[13px] font-bold text-slate-700">Sort Order</label>
                                <input
                                    type="number"
                                    value={formData.order}
                                    onChange={(e) => setFormData({ ...formData, order: parseInt(e.target.value) || 0 })}
                                    className="w-full h-11 px-4 bg-white border border-slate-200 rounded-lg text-[14px] focus:outline-none focus:border-[#c1e663] focus:ring-1 focus:ring-[#c1e663] transition-all"
                                />
                            </div>

                            {/* Features */}
                            <div className="col-span-2 space-y-3 mt-2">
                                <div className="flex items-center justify-between">
                                    <label className="text-[13px] font-bold text-slate-700">Features</label>
                                    <button
                                        type="button"
                                        onClick={addFeature}
                                        className="text-[12px] font-bold text-[#1a1c21] hover:underline flex items-center gap-1"
                                    >
                                        <Plus size={14} /> Add Line
                                    </button>
                                </div>
                                <div className="space-y-2">
                                    {formData.features.map((feature, index) => (
                                        <div key={index} className="flex items-center gap-2">
                                            <input
                                                type="text"
                                                value={feature}
                                                onChange={(e) => updateFeature(index, e.target.value)}
                                                className="flex-1 h-10 px-3 bg-[#fafbfc] border border-slate-200 rounded-lg text-[13px] focus:outline-none focus:border-[#c1e663] transition-all"
                                                placeholder="e.g. Unlimited Access"
                                            />
                                            {formData.features.length > 1 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeFeature(index)}
                                                    className="w-10 h-10 flex items-center justify-center text-rose-500 hover:bg-rose-50 rounded-lg transition-all"
                                                >
                                                    <X size={16} />
                                                </button>
                                            )}
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Options */}
                            <div className="col-span-2 flex items-center gap-6 pt-2">
                                <label className="flex items-center gap-2 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        checked={formData.is_active}
                                        onChange={(e) => setFormData({ ...formData, is_active: e.target.checked })}
                                        className="w-4 h-4 text-[#c1e663] border-slate-300 rounded focus:ring-[#c1e663]"
                                    />
                                    <span className="text-[13px] font-bold text-slate-600 group-hover:text-slate-900">Active</span>
                                </label>

                                <label className="flex items-center gap-2 cursor-pointer group">
                                    <input
                                        type="checkbox"
                                        checked={formData.is_popular}
                                        onChange={(e) => setFormData({ ...formData, is_popular: e.target.checked })}
                                        className="w-4 h-4 text-[#c1e663] border-slate-300 rounded focus:ring-[#c1e663]"
                                    />
                                    <span className="text-[13px] font-bold text-slate-600 group-hover:text-slate-900">Most Popular</span>
                                </label>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <style dangerouslySetInnerHTML={{ __html: `
                @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
            `}} />
        </AdminLayout>
    );
}
