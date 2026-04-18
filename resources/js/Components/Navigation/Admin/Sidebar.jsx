import React, { useState, useRef, useEffect } from "react";
import { Link, usePage } from "@inertiajs/react";
import {
    Home, Globe, LayoutGrid, Waves, Mail,
    Cloud, CreditCard, Store, ChevronRight, ChevronsLeft,
    Settings, ShieldCheck, DollarSign, Cog, Users, FolderTree,
    Smartphone, Monitor, CircleDollarSign, Hexagon, LogOut, User,
    Truck, UserCircle, AlertCircle
} from "lucide-react";

const Sidebar = ({ isCollapsed, toggleCollapse }) => {
    const { url, props } = usePage();
    const { sidebarCategories = [], auth } = props;
    const currentPath = url.split("?")[0];

    const menuGroups = [
        {
            title: "Management",
            items: [
                {
                    label: "Owners",
                    path: route('admin.owners.index'),
                    icon: <Users />,
                    route: "admin.owners.index"
                },
                {
                    label: "Transactions",
                    path: route('admin.transactions.index'),
                    icon: <CreditCard />,
                    route: "admin.transactions.index"
                },
            ]
        },
        {
            title: "Pricing",

            items: [
                {
                    label: "Pricing Plans",
                    path: route('admin.pricing-plans.index'),
                    icon: <CircleDollarSign />,
                    route: "admin.pricing-plans.index"
                },
            ]
        },

        {
            title: "Settings",
            items: [
                {
                    label: "General Settings",
                    icon: <Settings />,
                    key: "general",
                    children: [
                        { label: "Profile", path: route('admin.settings.general.profile'), route: 'admin.settings.general.profile' },
                        { label: "Security", path: route('admin.settings.general.security'), route: 'admin.settings.general.security' },

                    ]
                },
                {
                    label: "Website Settings",
                    icon: <Globe />,
                    key: "website",
                    children: [
                        { label: "General", path: route('admin.settings.website.system'), route: 'admin.settings.website.system' },
                        { label: "Email SMTP", path: route('admin.settings.system.email'), route: 'admin.settings.system.email' },
                    ]
                },
                {
                    label: "Financial Settings",
                    icon: <CircleDollarSign />,
                    key: "financial",
                    children: [
                        { label: "Payment Gateway", path: route('admin.settings.financial.gateway'), route: 'admin.settings.financial.gateway' },
                    ]
                },

                { label: "Logout", path: "/logout", icon: <LogOut />, method: "post" },
            ]
        }
    ];

    const legacyMenuItems = [
        { label: "Overview", path: "/dashboard", icon: <LayoutGrid />, route: "dashboard" },
    ];

    const checkActive = (item) => {
        if (typeof route !== 'undefined' && item.route) {
            if (route().current(item.route)) return true;
        }
        if (currentPath === item.path) return true;
        
        // Check if any child is active
        if (item.children) {
            return item.children.some(child => (
                currentPath === child.path || (child.route && typeof route !== 'undefined' && route().current(child.route))
            ));
        }
        
        return false;
    };

    const [openMenus, setOpenMenus] = useState(() => {
        const initialState = {};
        menuGroups.forEach(group => {
            group.items.forEach(item => {
                if (item.children && checkActive(item)) {
                    initialState[item.key] = true;
                }
            });
        });
        return initialState;
    });

    const renderMenuItem = (item) => {
        const active = checkActive(item);
        const isOpen = openMenus[item.key];
        const isLogout = item.label === "Logout";

        const content = (
            <>
                {/* Active Indicator or Just Background */}
                {/* Icon */}
                <div className={`${isCollapsed ? 'mb-1' : 'mr-3'} transition-transform duration-200 group-hover:scale-110 ${active || isOpen ? 'text-[#1a1c21]' : 'text-slate-400 group-hover:text-[#1a1c21]'}`}>
                    {React.cloneElement(item.icon, {
                        size: isCollapsed ? 24 : 18,
                        strokeWidth: active || isOpen ? 2.5 : 1.5
                    })}
                </div>

                {/* Label */}
                {!isCollapsed && (
                    <span className={`font-semibold leading-tight transition-all duration-300 text-[14px] flex-1 text-left
                        ${active || isOpen ? 'text-[#1a1c21]' : 'text-slate-600'}`}>
                        {item.label}
                    </span>
                )}

                {/* Chevron for expandable */}
                {!isCollapsed && !isLogout && (
                    <ChevronRight
                        size={14}
                        className={`transition-all duration-200 ${active || isOpen ? 'text-[#1a1c21]' : 'text-slate-300 group-hover:text-[#1a1c21]'} ${isOpen ? 'rotate-90' : ''} ${item.children ? '' : 'opacity-0'}`}
                    />
                )}
            </>
        );

        if (item.children) {
            return (
                <div key={item.label} className="px-3">
                    <button
                        onClick={() => setOpenMenus(prev => ({ ...prev, [item.key]: !prev[item.key] }))}
                        className={`w-full flex transition-all duration-300 group relative rounded-l-none rounded-r-full
                            ${isCollapsed
                                ? 'flex-col items-center justify-center py-4 px-1'
                                : 'flex-row items-center py-3 pl-5 pr-6'}
                            ${active || isOpen
                                ? 'bg-[#d9f196] shadow-md shadow-lime-200/50'
                                : 'text-slate-500 hover:bg-slate-50'}`}
                    >
                        {content}
                    </button>

                    {/* Sub-menu items */}
                    {!isCollapsed && isOpen && (
                        <div className="mt-1 space-y-1">
                            {item.children.map(child => (
                                <Link
                                    key={child.label}
                                    href={child.path}
                                    className={`flex items-center gap-3 py-2 pl-9 pr-6 rounded-l-none rounded-r-full text-[13px] font-medium transition-all
                                        ${checkActive(child)
                                            ? 'bg-[#d9f196] text-[#1a1c21] shadow-sm'
                                            : 'text-slate-500 hover:bg-slate-50 hover:text-[#1a1c21]'}`}
                                >
                                    {child.icon ? (
                                        React.cloneElement(child.icon, { size: 14 })
                                    ) : (
                                        <div className={`w-1.5 h-1.5 rounded-full transition-colors ${checkActive(child) ? 'bg-[#1a1c21]' : 'bg-slate-300 group-hover:bg-slate-400'}`} />
                                    )}
                                    <span>{child.label}</span>
                                </Link>
                            ))}
                        </div>
                    )}
                </div>
            );
        }

        if (item.method === "post") {
            return (
                <div key={item.label} className="px-3">
                    <Link
                        href={item.path}
                        method="post"
                        as="button"
                        className={`w-full flex transition-all duration-300 group relative rounded-l-none rounded-r-full
                            ${isCollapsed
                                ? 'flex-col items-center justify-center py-4 px-1'
                                : 'flex-row items-center py-3 pl-5 pr-6'}
                            ${active
                                ? 'bg-[#d9f196] shadow-md shadow-lime-200/50'
                                : 'text-slate-500 hover:bg-slate-50'}`}
                    >
                        {content}
                    </Link>
                </div>
            );
        }

        return (
            <div key={item.label} className="px-3">
                <Link
                    href={item.path}
                    className={`w-full flex transition-all duration-300 group relative rounded-l-none rounded-r-full
                        ${isCollapsed
                            ? 'flex-col items-center justify-center py-4 px-1'
                            : 'flex-row items-center py-3 pl-5 pr-6'}
                        ${active
                            ? 'bg-[#d9f196] shadow-md shadow-lime-200/50'
                            : 'text-slate-500 hover:bg-slate-50'}`}
                >
                    {content}
                </Link>
            </div>
        );
    };

    return (
        <div className="flex flex-col h-full bg-white relative">
            {/* Collapse Toggle Button */}
            <button
                onClick={toggleCollapse}
                className="absolute -right-3.5 top-5 z-50 w-7 h-7 bg-white border border-slate-200 rounded-full flex items-center justify-center text-slate-500 hover:text-[#0a66c2] shadow-sm transition-transform duration-300"
                style={{ transform: isCollapsed ? 'rotate(180deg)' : 'rotate(0deg)' }}
            >
                <ChevronsLeft size={14} strokeWidth={3} />
            </button>

            {/* Logo Section */}
            <div className={`h-[70px] flex items-center px-6 transition-all duration-300 ${isCollapsed ? 'justify-center px-0' : 'justify-start'}`}>
                <div className="min-w-[35px] w-[35px] h-[35px] bg-[#0a66c2] rounded-lg flex items-center justify-center text-white shadow-sm">
                    <Cloud size={20} fill="currentColor" />
                </div>
                {!isCollapsed && (
                    <span className="ml-3 font-bold text-slate-800 text-lg tracking-tight animate-in fade-in duration-500">
                        Admin<span className="text-slate-400 font-normal">Panel</span>
                    </span>
                )}
            </div>

            {/* Navigation */}
            <nav className="flex-1 flex flex-col pt-4 overflow-y-auto no-scrollbar">
                {/* Legacy Items */}
                <div className="space-y-1 mb-2">
                    {legacyMenuItems.map((item) => renderMenuItem(item))}
                </div>

                {/* Sectioned Navigation */}
                {menuGroups.map((group) => (
                    <div key={group.title} className="mb-2">
                        {!isCollapsed && (
                            <div className="px-6 py-2 mb-1">
                                <h3 className="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                    {group.title}
                                </h3>
                            </div>
                        )}
                        <div className="space-y-1">
                            {group.items.map((item) => renderMenuItem(item))}
                        </div>
                    </div>
                ))}
            </nav>
        </div>
    );
};

export default Sidebar;
