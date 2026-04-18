import React, { useState, useRef, useEffect } from "react";
import { router, usePage, Link } from "@inertiajs/react";
import { 
    Search, Bell, Menu, Settings, Maximize, 
    Mail, Globe, Monitor, Plus, LogOut, 
    ChevronDown, CreditCard, Home 
} from "lucide-react";

const Header = ({ onMenuClick }) => {
    const { auth } = usePage().props;
    const [open, setOpen] = useState(false);
    const [notifOpen, setNotifOpen] = useState(false);
    const dropdownRef = useRef(null);
    const notificationRef = useRef(null);

    const handleLogout = () => {
        router.post(route("logout"));
    };

    // Close dropdown on outside click
    useEffect(() => {
        const handleClickOutside = (e) => {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setOpen(false);
            }
            if (notificationRef.current && !notificationRef.current.contains(e.target)) {
                setNotifOpen(false);
            }
        };
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    return (
        <header className="h-[70px] bg-white/80 backdrop-blur-md sticky top-0 z-[50] flex items-center px-4 md:px-8 border-b border-slate-100 shadow-sm transition-all duration-300">
            
            {/* LEFT: Toggle & Mobile Logo */}
            <div className="flex items-center gap-4">
                <button
                    onClick={onMenuClick}
                    className="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-colors"
                >
                    <Menu size={20} />
                </button>
            </div>

            {/* CENTER: Spacer (since search is removed) */}
            <div className="flex-1" />

            {/* RIGHT: Actions & Profile */}
            <div className="flex items-center gap-2 md:gap-4 ml-auto">
                
                {/* Utility Icons */}
                <div className="flex items-center gap-1 border-r border-slate-100 pr-2 mr-2">
                    <Link 
                        href="/" 
                        className="w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-50 hover:text-[#0a66c2] rounded-xl transition-all" 
                        title="Frontend"
                    >
                        <Home size={20} strokeWidth={1.5} />
                    </Link>
                    
                    <div className="relative" ref={notificationRef}>
                        <button 
                            onClick={() => setNotifOpen(!notifOpen)}
                            className="w-10 h-10 flex items-center justify-center text-slate-500 hover:bg-slate-50 rounded-xl relative" 
                            title="Notifications"
                        >
                            <Bell size={20} strokeWidth={1.5} />
                            {auth?.user?.unread_notifications_count > 0 && (
                                <span className="absolute top-2.5 right-2.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center ring-2 ring-white">
                                    {auth.user.unread_notifications_count > 9 ? '9+' : auth.user.unread_notifications_count}
                                </span>
                            )}
                        </button>

                        {/* Notifications Dropdown */}
                        {notifOpen && (
                            <div className="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
                                <div className="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                                    <h3 className="text-sm font-bold text-slate-900">Notifications</h3>
                                    {auth?.user?.unread_notifications_count > 0 && (
                                        <button className="text-[11px] text-[#0a66c2] font-semibold hover:underline">Mark all as read</button>
                                    )}
                                </div>
                                <div className="max-h-[300px] overflow-y-auto">
                                    {auth?.user?.notifications?.length > 0 ? (
                                        auth.user.notifications.map((notif) => (
                                            <div key={notif.id} className="p-4 hover:bg-slate-50 border-b border-slate-50 last:border-0 transition-colors cursor-pointer group">
                                                <div className="flex gap-3">
                                                    <div className="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                                                        <CreditCard size={16} />
                                                    </div>
                                                    <div>
                                                        <p className="text-[13px] text-slate-800 font-medium group-hover:text-[#0a66c2] transition-colors leading-snug">
                                                            {notif.data.message}
                                                        </p>
                                                        <p className="text-[11px] text-slate-400 mt-1">
                                                            {new Date(notif.created_at).toLocaleDateString()}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        ))
                                    ) : (
                                        <div className="p-8 text-center">
                                            <div className="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                                <Bell size={20} className="text-slate-300" />
                                            </div>
                                            <p className="text-[13px] text-slate-500">No new notifications</p>
                                        </div>
                                    )}
                                </div>
                                <div className="p-3 bg-slate-50/30 border-t border-slate-100 text-center">
                                    <Link className="text-[12px] text-[#0a66c2] font-bold hover:underline">View All Notifications</Link>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                {/* User Profile Dropdown */}
                <div className="relative" ref={dropdownRef}>
                    <button 
                        onClick={() => setOpen(!open)}
                        className="flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-50 transition-colors border border-transparent hover:border-slate-100"
                    >
                        <img
                            src={auth?.user?.profile_picture || `https://ui-avatars.com/api/?name=${auth?.user?.name || 'Admin'}&background=673ab7&color=fff`}
                            alt="User"
                            className="w-9 h-9 rounded-lg object-cover shadow-sm"
                        />
                        <div className="hidden lg:block text-left leading-tight">
                            <p className="text-sm font-bold text-slate-700 block truncate">{auth?.user?.name || "User"}</p>
                            <p className="text-[11px] text-slate-400 font-medium">{auth?.user?.designation || "Administrator"}</p>
                        </div>
                        <ChevronDown size={14} className={`text-slate-400 transition-transform duration-300 ${open ? 'rotate-180' : ''}`} />
                    </button>

                    {/* Dropdown Menu */}
                    {open && (
                        <div className="absolute right-0 mt-3 w-64 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-200">
                            <div className="p-4 bg-slate-50/50 border-b border-slate-100 flex items-center gap-3">
                                <img
                                    src={auth?.user?.profile_picture || `https://ui-avatars.com/api/?name=${auth?.user?.name || 'Admin'}&background=673ab7&color=fff`}
                                    className="w-10 h-10 rounded-lg object-cover"
                                    alt="Avatar"
                                />
                                <div className="overflow-hidden">
                                    <p className="text-sm font-bold text-slate-900 truncate">{auth?.user?.name || "Admin"}</p>
                                    <Link href={route("profile.edit")} className="text-[11px] text-[#0a66c2] font-semibold hover:underline">Manage Account</Link>
                                </div>
                            </div>

                            <div className="p-2">
                                <DropdownLink icon={Settings} label="System Settings" href={route('admin.settings.website.system')} />
                            </div>

                            <div className="p-2 border-t border-slate-50 bg-slate-50/30">
                                <button
                                    onClick={handleLogout}
                                    className="w-full text-left px-3 py-2.5 text-[13px] text-red-500 hover:bg-red-50 font-bold flex items-center gap-3 rounded-xl transition-colors"
                                >
                                    <LogOut size={16} />
                                    <span>Sign Out</span>
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
};

const DropdownLink = ({ icon: Icon, label, href }) => (
    <Link 
        href={href}
        className="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13px] text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-all font-semibold group"
    >
        <Icon size={17} className="text-slate-400 group-hover:text-[#0a66c2] transition-colors" />
        <span>{label}</span>
    </Link>
);

export default Header;
