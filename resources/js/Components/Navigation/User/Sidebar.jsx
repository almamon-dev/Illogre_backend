import React from "react";
import { Link } from "@inertiajs/react";
import { useLanguage } from "@/Contexts/LanguageContext";
import { 
    Bookmark, 
    History, 
    LayoutDashboard, 
    CreditCard, 
    Activity, 
    Compass, 
    ChevronRight,
    Settings,
    User,
    Car
} from "lucide-react";

export default function UserSidebar({ user, stats = {} }) {
    const { t } = useLanguage();
    
    return (
        <aside className="hidden lg:block col-span-3 space-y-3">
            {/* Main Profile Card */}
            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm">
                {/* Visual Cover / Image Placeholder */}
                <div className="h-16 bg-gradient-to-r from-[#004182] to-[#0a66c2] relative">
                    <div className="absolute -bottom-6 left-4">
                        <div className="w-14 h-14 rounded-full border-2 border-white overflow-hidden bg-white shadow-md">
                            <img 
                                src={user.profile_photo_url || `https://ui-avatars.com/api/?name=${user.name}&background=f3f4f6&color=0a66c2&size=128`} 
                                alt={user.name} 
                                className="w-full h-full object-cover"
                            />
                        </div>
                    </div>
                </div>
                
                <div className="pt-8 pb-3 px-4">
                    <h2 className="text-[16px] font-semibold text-gray-900 leading-tight hover:text-[#0a66c2] cursor-pointer">
                        {user.name}
                    </h2>
                    <p className="text-[12px] text-gray-500 mt-0.5 lowercase">
                        {user.email}
                    </p>
                </div>
                
                <div className="py-1 border-t border-gray-50">
                    <div className="px-4 py-2 flex justify-between items-center hover:bg-gray-50 transition-colors cursor-pointer group">
                        <span className="text-[12px] font-medium text-gray-500">{t.dashboard.total_bookings}</span>
                        <span className="text-[12px] font-semibold text-[#0a66c2]">{stats?.total_bookings || 0}</span>
                    </div>
                    <div className="px-4 py-2 flex justify-between items-center hover:bg-gray-50 transition-colors cursor-pointer group border-t border-gray-50">
                        <span className="text-[12px] font-medium text-gray-500">{t.dashboard.active_rentals}</span>
                        <span className="text-[12px] font-semibold text-[#0a66c2]">{stats?.active_bookings || 0}</span>
                    </div>
                </div>
            </div>

            {/* Navigation Menu */}
            <div className="bg-white rounded-lg border border-gray-200 overflow-hidden shadow-sm pt-1 pb-1">
                <SidebarLink 
                    href={route('dashboard')} 
                    icon={LayoutDashboard} 
                    label={t.nav.dashboard} 
                    active={route().current('dashboard')} 
                />
                <SidebarLink 
                    href={route('user.favorites.index')} 
                    icon={Bookmark} 
                    label={t.dashboard.saved_assets} 
                    active={route().current('user.favorites.index')} 
                />
                <SidebarLink 
                    href={route('user.bookings.index')} 
                    icon={History} 
                    label={t.dashboard.my_reservations} 
                    active={route().current('user.bookings.index')} 
                />
                <SidebarLink 
                    href={route('user.payments.index')} 
                    icon={CreditCard} 
                    label={t.dashboard.billing_details} 
                    active={route().current('user.payments.index')} 
                />
                <SidebarLink 
                    href={route('profile.edit')} 
                    icon={Settings} 
                    label={t.dashboard.settings_privacy} 
                    active={route().current('profile.edit')} 
                />
            </div>

            {/* Discovery Section */}
            <div className="bg-white rounded-lg border border-gray-200 p-4 shadow-sm text-center">
                <p className="text-[11px] text-gray-400 font-semibold mb-2 uppercase tracking-wide">Premium Access</p>
                <div className="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-[#0a66c2] mx-auto mb-2 border border-blue-100 italic">
                    <Car size={24} />
                </div>
                <h4 className="text-[13px] font-semibold text-gray-800 leading-tight">Elite Fleet 2026</h4>
                <button className="mt-3 w-full py-1.5 text-[12px] font-semibold text-[#0a66c2] border border-[#0a66c2] rounded-full hover:bg-blue-50 transition-all">
                    Upgrade Logistics
                </button>
            </div>
        </aside>
    );
}

const SidebarLink = ({ href, icon: Icon, label, active }) => (
    <Link 
        href={href}
        className={`px-5 py-2.5 flex items-center gap-3.5 hover:bg-slate-50 transition-all border-l-[4px] ${
            active 
                ? "bg-blue-50/40 border-[#0a66c2] text-[#0a66c2]" 
                : "border-transparent text-slate-600 hover:text-slate-900"
        }`}
    >
        <Icon size={20} strokeWidth={active ? 2 : 1.5} className={active ? "text-[#0a66c2]" : "text-slate-400"} />
        <span className={`text-[14px] ${active ? "font-bold" : "font-medium"}`}>{label}</span>
    </Link>
);
