import React, { useState } from 'react';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import {
    Plus, Edit, Trash2, Power, PowerOff, Check,
    Users, Truck, Euro, Calendar, Award
} from 'lucide-react';

export default function Index({ auth, plans = [] }) {
    const [billingCycle, setBillingCycle] = useState('monthly'); // 'monthly' or 'annual'

    const toggleActive = (planId) => {
        router.patch(route('admin.pricing-plans.toggle-active', planId));
    };

    const deletePlan = (planId) => {
        if (confirm('Are you sure you want to delete this pricing plan?')) {
            router.delete(route('admin.pricing-plans.destroy', planId));
        }
    };

    const getBillingPeriodLabel = (period) => {
        const labels = {
            trial: 'Free Trial',
            monthly: 'Monthly',
            quarterly: 'Quarterly',
            annual: 'Annual'
        };
        return labels[period] || period;
    };

    // Filter plans based on selected billing cycle
    const filteredPlans = plans.filter(plan => plan.billing_period === billingCycle);

    const PlanCard = ({ plan }) => (
        <div className={`bg-white rounded-lg border transition-all ${plan.is_popular
            ? 'border-[#c1e663] border-2 shadow-sm'
            : 'border-[#e5e7eb]'
            }`}>
            {/* Header */}
            <div className="p-6">
                <div className="flex items-start justify-between mb-4">
                    <h3 className="text-[18px] font-semibold text-[#2f3344]">{plan.name}</h3>
                </div>

                {/* Badges */}
                <div className="flex items-center gap-2 flex-wrap mb-4">
                    {plan.is_popular && (
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#c1e663]/20 text-[#1a1c21] rounded-md text-[11px] font-bold">
                            <Award size={12} />
                            Most Popular
                        </span>
                    )}
                    <span className={`inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold ${plan.is_active
                        ? 'bg-emerald-50 text-emerald-600'
                        : 'bg-rose-50 text-rose-600'
                        }`}>
                        {plan.is_active ? 'Active' : 'Inactive'}
                    </span>
                </div>

                {/* Price */}
                <div className="mb-4">
                    <div className="flex items-baseline gap-1">
                        <span className="text-[32px] font-semibold text-[#1a1c21]">
                            ${plan.price}
                        </span>
                        <span className="text-[14px] text-[#727586] font-medium uppercase tracking-wider">
                            /{getBillingPeriodLabel(plan.billing_period).toLowerCase()}
                        </span>
                    </div>
                </div>

                {/* Features */}
                <div className="mb-6">
                    <h4 className="text-[13px] font-semibold text-[#2f3344] mb-3">Features</h4>
                    <ul className="space-y-2.5">
                        {plan.features && plan.features.map((feature, index) => (
                            <li key={index} className="flex items-start gap-2 text-[13px] text-slate-600 font-medium">
                                <div className="w-4 h-4 rounded-full bg-emerald-500/10 flex items-center justify-center text-emerald-600 mt-0.5 flex-shrink-0">
                                    <Check size={10} strokeWidth={3} />
                                </div>
                                <span>{feature}</span>
                            </li>
                        ))}
                    </ul>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-2 pt-4 border-t border-slate-50">
                    <button
                        onClick={() => toggleActive(plan.id)}
                        className={`flex-1 flex items-center justify-center gap-1.5 px-3 py-2 rounded-md text-[12px] font-semibold transition-all ${plan.is_active
                            ? 'bg-emerald-50 text-emerald-600 hover:bg-emerald-100'
                            : 'bg-slate-50 text-slate-600 hover:bg-slate-100'
                            }`}
                    >
                        {plan.is_active ? <PowerOff size={14} /> : <Power size={14} />}
                        {plan.is_active ? 'Deactivate' : 'Activate'}
                    </button>
                    <Link
                        href={route('admin.pricing-plans.edit', plan.id)}
                        className="flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-50 text-slate-600 hover:bg-slate-100 rounded-md text-[12px] font-semibold transition-all"
                    >
                        <Edit size={14} />
                        Edit
                    </Link>
                    <button
                        onClick={() => deletePlan(plan.id)}
                        className="flex items-center justify-center px-3 py-2 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-md text-[12px] font-semibold transition-all"
                    >
                        <Trash2 size={14} />
                    </button>
                </div>
            </div>
        </div>
    );

    return (
        <AdminLayout user={auth.user}>
            <Head title="Pricing Plans" />

            <div className="min-h-screen bg-[#fafbfc]">
                <div className="max-w-8xl mx-auto px-6 py-8">
                    {/* Header */}
                    <div className="flex items-center justify-between mb-8">
                        <div>
                            <h1 className="text-[28px] font-semibold text-[#1a1c21] mb-1 tracking-tight">Pricing Plans</h1>
                            <p className="text-[14px] text-[#727586] font-medium">Manage and configure your subscription plans</p>
                        </div>
                        <div className="flex items-center gap-4">
                            {/* Toggle */}
                            <div className="flex items-center p-1 bg-slate-100 rounded-lg">
                                <button
                                    onClick={() => setBillingCycle('monthly')}
                                    className={`px-4 py-1.5 rounded-md text-xs font-bold transition-all ${billingCycle === 'monthly' ? 'bg-white text-[#1a1c21] shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
                                >
                                    Monthly
                                </button>
                                <button
                                    onClick={() => setBillingCycle('annual')}
                                    className={`px-4 py-1.5 rounded-md text-xs font-bold transition-all ${billingCycle === 'annual' ? 'bg-white text-[#1a1c21] shadow-sm' : 'text-slate-500 hover:text-slate-700'}`}
                                >
                                    Annual
                                </button>
                            </div>

                            <Link
                                href={route('admin.pricing-plans.create')}
                                className="inline-flex items-center gap-2 px-4 py-1.5 bg-[#c1e663] text-[#1a1c21] rounded-md text-[14px] font-extrabold transition-all"
                            >
                                <Plus size={18} strokeWidth={3} />
                                Create New Plan
                            </Link>
                        </div>
                    </div>

                    {/* All Plans */}
                    {filteredPlans.length > 0 ? (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            {filteredPlans.map((plan) => (
                                <PlanCard key={plan.id} plan={plan} />
                            ))}
                        </div>
                    ) : (
                        <div className="bg-white rounded-xl border border-slate-100 p-20 text-center shadow-sm">
                            <div className="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <Euro size={32} className="text-slate-300" />
                            </div>
                            <h3 className="text-[18px] font-semibold text-[#1a1c21] mb-2">No {billingCycle} plans</h3>
                            <p className="text-[14px] text-[#727586] mb-6 max-w-xs mx-auto">Create a new {billingCycle} subscription plan to get started.</p>
                            <Link
                                href={route('admin.pricing-plans.create')}
                                className="inline-flex items-center gap-2 px-6 py-2.5 bg-[#c1e663] text-[#1a1c21] hover:bg-[#b0d552] rounded-lg text-[14px] font-semibold transition-all"
                            >
                                <Plus size={18} />
                                Create Plan
                            </Link>
                        </div>
                    )}
                </div>
            </div>
        </AdminLayout>
    );
}
