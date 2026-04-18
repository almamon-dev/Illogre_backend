import React, { useState } from 'react';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link } from '@inertiajs/react';
import { Check, ArrowRight, Truck, Users } from 'lucide-react';

export default function SelectPlan({ supplierPlans, customerPlans }) {
    const [selectedUserType, setSelectedUserType] = useState('customer');

    const plans = selectedUserType === 'supplier' ? supplierPlans : customerPlans;

    const getBillingPeriodLabel = (period) => {
        const labels = {
            trial: 'Free Trial',
            monthly: 'Monthly',
            quarterly: 'Quarterly',
            annual: 'Annual'
        };
        return labels[period] || period;
    };

    return (
        <GuestLayout>
            <Head title="Select Your Plan" />

            <div className="min-h-screen bg-gradient-to-br from-[#f8f9fa] to-[#e9ecef] py-12 px-4">
                <div className="max-w-7xl mx-auto">
                    {/* Header */}
                    <div className="text-center mb-12">
                        <h1 className="text-[36px] font-bold text-[#2f3344] mb-3">
                            Choose Your Plan
                        </h1>
                        <p className="text-[16px] text-[#727586] max-w-2xl mx-auto">
                            Select a subscription plan to get started with our transport management platform
                        </p>
                    </div>

                    {/* User Type Toggle */}
                    <div className="flex justify-center mb-12">
                        <div className="inline-flex bg-white rounded-xl p-1.5 shadow-sm border border-[#e5e7eb]">
                            <button
                                onClick={() => setSelectedUserType('customer')}
                                className={`flex items-center gap-2 px-6 py-3 rounded-lg text-[15px] font-semibold transition-all ${
                                    selectedUserType === 'customer'
                                        ? 'bg-[#3b82f6] text-white shadow-md'
                                        : 'text-[#727586] hover:text-[#2f3344]'
                                }`}
                            >
                                <Users size={18} />
                                I'm a Customer
                            </button>
                            <button
                                onClick={() => setSelectedUserType('supplier')}
                                className={`flex items-center gap-2 px-6 py-3 rounded-lg text-[15px] font-semibold transition-all ${
                                    selectedUserType === 'supplier'
                                        ? 'bg-[#673ab7] text-white shadow-md'
                                        : 'text-[#727586] hover:text-[#2f3344]'
                                }`}
                            >
                                <Truck size={18} />
                                I'm a Supplier
                            </button>
                        </div>
                    </div>

                    {/* Plans Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                        {plans.filter(plan => plan.is_active).map((plan) => (
                            <div
                                key={plan.id}
                                className={`bg-white rounded-xl border-2 transition-all hover:shadow-xl ${
                                    plan.is_popular
                                        ? 'border-[#673ab7] shadow-lg relative'
                                        : 'border-[#e5e7eb] hover:border-[#d1d5db]'
                                }`}
                            >
                                {plan.is_popular && (
                                    <div className="absolute -top-4 left-1/2 -translate-x-1/2">
                                        <span className="inline-flex items-center px-4 py-1.5 bg-[#673ab7] text-white rounded-full text-[12px] font-bold shadow-md">
                                            Most Popular
                                        </span>
                                    </div>
                                )}

                                <div className="p-8">
                                    {/* Plan Name */}
                                    <h3 className="text-[22px] font-bold text-[#2f3344] mb-2">
                                        {plan.name}
                                    </h3>

                                    {/* Price */}
                                    <div className="mb-6">
                                        {plan.billing_period === 'trial' ? (
                                            <div className="flex items-baseline gap-2">
                                                <span className="text-[48px] font-bold text-[#2f3344]">
                                                    {plan.trial_days}
                                                </span>
                                                <span className="text-[18px] text-[#727586]">days free</span>
                                            </div>
                                        ) : (
                                            <div className="flex items-baseline gap-1">
                                                <span className="text-[20px] text-[#673ab7] font-semibold">$</span>
                                                <span className="text-[48px] font-bold text-[#673ab7]">
                                                    {Math.floor(plan.price)}
                                                </span>
                                                <span className="text-[18px] text-[#727586]">
                                                    /{getBillingPeriodLabel(plan.billing_period).toLowerCase()}
                                                </span>
                                            </div>
                                        )}
                                    </div>

                                    {/* Features */}
                                    <ul className="space-y-3 mb-8">
                                        {plan.features && plan.features.map((feature, index) => (
                                            <li key={index} className="flex items-start gap-3 text-[14px] text-[#4a5568]">
                                                <Check size={18} className="text-[#10b981] mt-0.5 flex-shrink-0" strokeWidth={2.5} />
                                                <span className="leading-relaxed">{feature}</span>
                                            </li>
                                        ))}
                                    </ul>

                                    {/* Select Button */}
                                    <Link
                                        href={route('register', { plan: plan.id })}
                                        className={`w-full flex items-center justify-center gap-2 py-3.5 rounded-lg text-[15px] font-bold transition-all ${
                                            plan.is_popular
                                                ? 'bg-[#673ab7] text-white hover:bg-[#5e35b1] shadow-md'
                                                : 'bg-white text-[#2f3344] border-2 border-[#e5e7eb] hover:border-[#673ab7] hover:text-[#673ab7]'
                                        }`}
                                    >
                                        {plan.billing_period === 'trial' ? 'Start Free Trial' : 'Select Plan'}
                                        <ArrowRight size={18} />
                                    </Link>
                                </div>
                            </div>
                        ))}
                    </div>

                    {/* Already have account */}
                    <div className="text-center mt-12">
                        <p className="text-[14px] text-[#727586]">
                            Already have an account?{' '}
                            <Link href={route('login')} className="text-[#673ab7] font-semibold hover:underline">
                                Sign in
                            </Link>
                        </p>
                    </div>
                </div>
            </div>
        </GuestLayout>
    );
}
