import React, { useState, useEffect, useRef } from "react";
import { Link, usePage } from "@inertiajs/react";
import { AnimatePresence, motion } from "framer-motion";
import { 
    Car, Menu, X, Search, Globe, ChevronDown, Check,
    Home, Users, Bell, Grid, UserCircle, Briefcase, 
    MessageSquare, Settings, LogOut, LayoutDashboard,
    User, HelpCircle, ShieldCheck, Mail, Target, LayoutGrid,
    Heart, CreditCard, ClipboardList, Package, BarChart2, MapPin
} from "lucide-react";


import { useLanguage } from "@/Contexts/LanguageContext";
import { languageNames } from "@/Locales/index";

// --- Sub-components ---

const LanguageDropdown = () => {
    const { locale, toggleLanguage, setLanguage, t } = useLanguage();
    const [isOpen, setIsOpen] = useState(false);
    const containerRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (e) => {
            if (containerRef.current && !containerRef.current.contains(e.target)) setIsOpen(false);
        };
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    const languages = Object.entries(languageNames).map(([code, label]) => ({
        code,
        label
    }));

    return (
        <div className="relative h-full flex flex-col justify-center" ref={containerRef}>
            <button 
                onClick={() => setIsOpen(!isOpen)}
                className={`flex flex-col items-center justify-between h-full pt-2 pb-1.5 min-w-[64px] cursor-pointer transition-all border-b-[2px] ${
                    isOpen ? "border-gray-900 text-gray-900" : "border-transparent text-gray-500 hover:text-gray-900"
                }`}
            >
                <div className="flex flex-col items-center justify-center flex-1">
                    <Globe size={22} className={isOpen ? "text-gray-900" : "text-gray-500"} strokeWidth={1.5} />
                    <span className="text-[12px] font-medium uppercase tracking-tighter leading-none mt-1">
                        {locale.toUpperCase().slice(0, 2)} 
                        <ChevronDown size={11} className={`inline-block ml-0.5 transition-transform duration-300 ${isOpen ? "rotate-180" : ""}`} />
                    </span>
                </div>
            </button>

            <AnimatePresence>
                {isOpen && (
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95, y: 5 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.95, y: 5 }}
                        transition={{ duration: 0.1 }}
                        className="absolute top-full right-0 w-40 bg-white rounded-lg shadow-[0_12px_32px_rgba(0,0,0,0.15)] border border-gray-200 overflow-hidden py-1.5 z-[100] origin-top-right mt-1"
                    >
                        {languages.map(({ code, label }) => (
                            <button
                                key={code}
                                onClick={() => {
                                    setLanguage(code);
                                    setIsOpen(false);
                                }}
                                className={`w-full text-left px-4 py-2 text-[13px] hover:bg-gray-50 flex justify-between items-center transition-colors ${
                                    locale === code ? "bg-blue-50/50 font-black text-[#0a66c2]" : "text-gray-700 font-bold"
                                }`}
                            >
                                {label}
                                {locale === code && <Check size={14} strokeWidth={4} />}
                            </button>
                        ))}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
};

const ProfileDropdown = ({ user }) => {
    const { t } = useLanguage();
    const [isOpen, setIsOpen] = useState(false);
    const containerRef = useRef(null);

    useEffect(() => {
        const handleClickOutside = (e) => {
            if (containerRef.current && !containerRef.current.contains(e.target)) setIsOpen(false);
        };
        document.addEventListener("mousedown", handleClickOutside);
        return () => document.removeEventListener("mousedown", handleClickOutside);
    }, []);

    return (
        <div className="relative h-full flex flex-col justify-center" ref={containerRef}>
            <button 
                onClick={() => setIsOpen(!isOpen)}
                className={`flex flex-col items-center justify-between h-full pt-2 pb-1.5 min-w-[64px] cursor-pointer transition-all border-b-[2px] ${
                    isOpen ? "border-gray-900 text-gray-900" : "border-transparent text-gray-500 hover:text-gray-900"
                }`}
            >
                <div className="flex flex-col items-center justify-center flex-1">
                    <div className={`w-6 h-6 rounded-full overflow-hidden border transition-all duration-300 ${
                        isOpen ? "border-gray-900 scale-110" : "border-gray-200"
                    }`}>
                        <img 
                            src={user.profile_photo_url || `https://ui-avatars.com/api/?name=${user.name}&background=f3f4f6&color=6b7280&size=64`} 
                            alt={user.name} 
                            className="w-full h-full object-cover" 
                        />
                    </div>
                    <span className="flex items-center text-[12px] font-medium tracking-tighter leading-none mt-1">
                        Me <ChevronDown size={11} className={`ml-0.5 transition-transform duration-300 ${isOpen ? "rotate-180" : ""}`} />
                    </span>
                </div>
            </button>

            <AnimatePresence>
                {isOpen && (
                    <motion.div
                        initial={{ opacity: 0, scale: 0.95, y: 5 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.95, y: 5 }}
                        transition={{ duration: 0.1 }}
                        className="absolute top-full right-0 w-[280px] bg-white rounded-lg shadow-[0_12px_44px_rgba(0,0,0,0.2)] border border-gray-200 overflow-hidden z-[100] origin-top-right mt-1"
                    >
                        <div className="p-3 bg-white">
                            <div className="flex gap-2.5 items-center">
                                <div className="w-11 h-11 rounded-full overflow-hidden bg-gray-50 border border-gray-100 flex-shrink-0 shadow-sm">
                                    <img src={user.profile_photo_url || `https://ui-avatars.com/api/?name=${user.name}`} alt={user.name} className="w-full h-full object-cover" />
                                </div>
                                <div className="flex flex-col min-w-0 pr-1">
                                    <span className="text-[14px] font-bold text-gray-900 truncate tracking-tight">{user.name}</span>
                                    <span className="text-[11px] text-gray-500 truncate font-medium">
                                        {user.is_admin ? t.dashboard.administrator : (t.nav.member_status || 'Verified Member')}
                                    </span>
                                </div>
                            </div>
                            <Link 
                                href="/profile" 
                                onClick={() => setIsOpen(false)}
                                className="block w-full text-center py-1.5 text-[14px] font-black text-[#0a66c2] border-2 border-[#0a66c2] rounded-full hover:bg-blue-50 transition-all mt-4"
                            >
                                {t.nav.view_profile}
                            </Link>
                        </div>

                        {/* Divider */}
                        <div className="h-[1px] bg-gray-100" />

                        {/* Account Section */}
                        <div className="py-2">
                             <div className="px-4 py-1 text-[11px] font-black text-gray-400 uppercase tracking-widest">{t.nav.account}</div>
                             <DropdownItem href={route('profile.edit')} icon={Settings} onClick={() => setIsOpen(false)}>{t.nav.settings_privacy}</DropdownItem>
                             <DropdownItem href={route('user.payments.index')} icon={CreditCard} onClick={() => setIsOpen(false)}>{t.nav.payments || 'Payments'}</DropdownItem>
                             <DropdownItem href="/security" icon={ShieldCheck} onClick={() => setIsOpen(false)}>{t.nav.security_protocol}</DropdownItem>
                        </div>

                        {/* Divider */}
                        <div className="h-[1px] bg-gray-100" />

                        {/* Manage Section */}
                        <div className="py-2">
                             <div className="px-4 py-1 text-[11px] font-black text-gray-400 uppercase tracking-widest">{t.nav.manage}</div>
                             {user.is_admin ? (
                                 <>
                                     <DropdownItem href={route('admin.cars.index')} icon={LayoutDashboard} onClick={() => setIsOpen(false)}>{t.nav.admin_dashboard || 'Admin Dashboard'}</DropdownItem>
                                     <DropdownItem href={route('admin.cars.index')} icon={Package} onClick={() => setIsOpen(false)}>{t.nav.manage_cars || 'Manage Cars'}</DropdownItem>
                                     <DropdownItem href={route('admin.brands.index')} icon={Target} onClick={() => setIsOpen(false)}>{t.nav.manage_brands || 'Manage Brands'}</DropdownItem>
                                     <DropdownItem href={route('admin.category.index')} icon={Grid} onClick={() => setIsOpen(false)}>{t.nav.manage_categories || 'Manage Categories'}</DropdownItem>
                                     <DropdownItem href={route('admin.locations.index')} icon={MapPin} onClick={() => setIsOpen(false)}>{t.nav.manage_locations || 'Manage Hubs'}</DropdownItem>
                                 </>
                             ) : (
                                 <>
                                     <DropdownItem href={route('dashboard')} icon={LayoutDashboard} onClick={() => setIsOpen(false)}>{t.nav.dashboard}</DropdownItem>
                                     <DropdownItem href={route('user.bookings.index')} icon={ClipboardList} onClick={() => setIsOpen(false)}>{t.nav.bookings}</DropdownItem>
                                     <DropdownItem href={route('user.favorites.index')} icon={Heart} onClick={() => setIsOpen(false)}>{t.nav.favorites || 'Favorites'}</DropdownItem>
                                 </>
                             )}
                        </div>

                        {/* Footer Section */}
                        <div className="bg-gray-50/50 py-1.5">
                             <Link 
                                href={route('logout')} 
                                method="post" 
                                as="button"
                                className="w-full text-left px-4 py-2.5 text-[14px] font-bold text-gray-500 hover:text-red-600 transition-colors flex items-center gap-2 group/logout"
                            >
                                <LogOut size={16} className="group-hover/logout:translate-x-0.5 transition-transform" />
                                {t.nav.logout}
                            </Link>
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
};

const DropdownItem = ({ children, icon: Icon, href, onClick }) => (
    <Link 
        href={href} 
        onClick={onClick}
        className="flex items-center gap-3 px-4 py-3 text-[14px] text-gray-700 hover:text-gray-900 hover:bg-gray-50 transition-colors group"
    >
        <Icon size={20} className="text-gray-500 group-hover:text-gray-900 transition-colors" />
        <span className="text-[15px] font-semibold text-gray-700 group-hover:text-gray-900">{children}</span>
    </Link>
);

const MobileMenu = ({ links, isActive, auth, onClose }) => {
    const { locale, toggleLanguage, setLanguage, t } = useLanguage();
    
    return (
        <>
            <motion.div
                initial={{ opacity: 0 }}
                animate={{ opacity: 1 }}
                exit={{ opacity: 0 }}
                transition={{ duration: 0.2 }}
                className="fixed inset-0 bg-black/60 z-[200] backdrop-blur-[4px]"
                onClick={onClose}
            />
            
            <motion.div
                initial={{ x: "-100%" }}
                animate={{ x: 0 }}
                exit={{ x: "-100%" }}
                transition={{ type: "spring", stiffness: 350, damping: 35 }}
                className="fixed inset-y-0 left-0 w-[300px] bg-white z-[210] flex flex-col shadow-2xl overflow-hidden"
            >
                {/* Mobile Header */}
                <div className="flex flex-col p-6 bg-[#1d2226] text-white relative">
                    <button 
                        onClick={onClose} 
                        className="absolute top-4 right-4 w-9 h-9 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/20 transition-colors"
                    >
                       <X size={20} />
                    </button>

                    <div className="flex flex-col">
                        <div className="w-16 h-16 rounded-full bg-white/10 border-[3px] border-white/20 shadow-xl overflow-hidden mb-4 flex items-center justify-center">
                            {auth && auth.user ? (
                                <img src={auth.user.profile_photo_url || `https://ui-avatars.com/api/?name=${auth.user.name}`} alt={auth.user.name} className="w-full h-full object-cover" />
                            ) : (
                                <UserCircle size={64} className="text-white/30" />
                            )}
                        </div>
                        
                        {auth && auth.user ? (
                            <>
                                <span className="text-[20px] font-black leading-tight tracking-tight mb-1">{auth.user.name}</span>
                                <Link href="/profile" onClick={onClose} className="text-[13px] font-bold text-blue-400 hover:underline flex items-center gap-1 uppercase tracking-widest">
                                    {t.nav.view_profile}
                                </Link>
                            </>
                        ) : (
                            <>
                                <Link href="/login" onClick={onClose} className="text-[22px] font-black leading-tight hover:underline transition-all">
                                    {t.nav.login}
                                </Link>
                                <Link href="/register" onClick={onClose} className="text-[14px] font-bold text-blue-400 mt-1 hover:underline tracking-wider uppercase">
                                    {t.nav.register}
                                </Link>
                            </>
                        )}
                    </div>
                </div>

                {/* Mobile Nav Links */}
                <div className="flex-1 overflow-y-auto pt-2">
                    <nav className="space-y-0">
                        {links.map((link) => (
                            <Link
                                key={link.name}
                                href={route(link.routeName)}
                                onClick={onClose}
                                className={`flex items-center gap-4 px-6 py-4 text-[15px] font-black transition-all border-l-[4px] ${
                                    isActive(link.routeName)
                                        ? "bg-blue-50/50 text-[#0a66c2] border-[#0a66c2]"
                                        : "bg-transparent text-gray-700 border-transparent hover:bg-gray-50"
                                }`}
                            >
                                <link.icon size={22} strokeWidth={isActive(link.routeName) ? 2.5 : 2} />
                                {link.name}
                            </Link>
                        ))}
                    </nav>

                    <hr className="border-gray-100 my-4 mx-4"/>

                    <div className="px-6 py-2">
                        <h3 className="text-[11px] font-black text-gray-400 uppercase tracking-widest mb-4">{t.nav.language}</h3>
                            <div className="flex flex-wrap gap-2">
                            {Object.entries(languageNames).map(([code, label]) => (
                                <button
                                    key={code}
                                    onClick={() => {
                                        setLanguage(code);
                                    }}
                                    className={`flex-1 py-3 rounded-xl text-[13px] font-black border transition-all min-w-[30%] ${
                                        locale === code 
                                            ? "border-[#0a66c2] text-[#0a66c2] bg-blue-50 shadow-sm ring-1 ring-[#0a66c2]/20" 
                                            : "border-gray-200 text-gray-500 hover:border-gray-300 bg-gray-50"
                                    }`}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>
                    </div>
                </div>

                {/* Mobile Footer */}
                  <div className="p-6 border-t border-gray-100 bg-gray-50/80">
                    <div className="flex items-center justify-between">
                         {auth && auth.user ? (
                             <Link 
                                href={route('logout')} 
                                method="post" 
                                as="button" 
                                onClick={onClose} 
                                className="flex items-center gap-2 text-[14px] font-black text-gray-600 hover:text-red-600 transition-colors py-2 px-4 bg-white rounded-full border border-gray-200 shadow-sm"
                             >
                                 <LogOut size={16} />
                                 <span>{t.nav.logout}</span>
                             </Link>
                         ) : (
                             <Link href="/settings" onClick={onClose} className="flex items-center gap-2 text-[12px] font-black text-gray-500 hover:text-[#0a66c2] uppercase tracking-widest">
                                 <Settings size={18} />
                                 <span>{t.nav.settings}</span>
                             </Link>
                         )}
                         <div className="text-[10px] font-black text-gray-300 uppercase tracking-[0.2em]">LP-v1.8</div>
                    </div>
                </div>
            </motion.div>
        </>
    );
};

// --- Main Header Component ---

const Header = () => {
    const { url, props } = usePage();
    const auth = props.auth;
    const { locale, toggleLanguage, t } = useLanguage();
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [scrolled, setScrolled] = useState(false);
    const [isSearchFocused, setIsSearchFocused] = useState(false);

    useEffect(() => {
        const handleScroll = () => setScrolled(window.scrollY > 0);
        window.addEventListener("scroll", handleScroll);
        return () => window.removeEventListener("scroll", handleScroll);
    }, []);

    const navLinks = [
        { name: t.nav.home, routeName: 'home', icon: Home },
        { name: t.nav.cars, routeName: 'car.list', icon: Car },
        { name: t.nav.brands, routeName: 'brands.index', icon: Briefcase },
        { name: t.nav.categories, routeName: 'categories.index', icon: Users },
        { name: t.nav.contact, routeName: 'contact.index', icon: MessageSquare },
    ];

    const isActive = (routeName) => route().current(routeName);

    return (
        <>
            <header 
                className={`fixed top-0 left-0 w-full z-[100] transition-all duration-300 ${
                    scrolled 
                        ? "bg-white/95 backdrop-blur-md shadow-[0_2px_15px_rgba(0,0,0,0.06)] border-b border-gray-200" 
                        : "bg-white border-b border-gray-100"
                }`}
            >
                <div className="max-w-7xl mx-auto px-4 sm:px-6 h-full">
                    <div className="flex h-[52px] sm:h-[62px] items-center justify-between">
                        
                        {/* --- LEFT SECTION --- */}
                        <div className="flex items-center gap-2 sm:gap-4 md:gap-4">
                            <Link href="/" className="flex-shrink-0 group relative">
                                {props.settings?.site_logo ? (
                                    <div className="flex items-center justify-center h-8 sm:h-9 w-auto">
                                        <img 
                                            src={props.settings.site_logo} 
                                            alt="Logo" 
                                            className="h-full w-auto object-contain"
                                        />
                                    </div>
                                ) : (
                                    <div className="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 rounded-[4px] bg-[#0a66c2] text-white shadow-sm transition-all group-hover:scale-105 active:scale-95">
                                         <Car size={20} strokeWidth={2.5} className="sm:w-6 sm:h-6" />
                                    </div>
                                )}
                            </Link>
                            
                            {/* Search */}
                            <div className={`hidden md:flex items-center px-4 py-1.5 rounded-[4px] transition-all duration-300 ease-in-out ${
                                isSearchFocused 
                                    ? "w-[360px] bg-white ring-2 ring-[#0a66c2] shadow-xl" 
                                    : "w-[260px] bg-[#edf3f8] hover:bg-[#e1eaf3]"
                            }`}>
                                <Search 
                                    size={16} 
                                    className={`mr-3 transition-colors ${isSearchFocused ? "text-[#0a66c2]" : "text-gray-600"}`} 
                                    strokeWidth={3}
                                />
                                <input 
                                    type="text" 
                                    placeholder={t.nav.search_placeholder} 
                                    onFocus={() => setIsSearchFocused(true)}
                                    onBlur={() => setIsSearchFocused(false)}
                                    className="bg-transparent border-none outline-none text-[14px] font-medium text-gray-900 placeholder:text-gray-500 w-full p-0 focus:ring-0"
                                />
                            </div>
                        </div>

                        {/* --- CENTER SECTION --- */}
                        <nav className="hidden lg:flex items-center ml-auto h-full gap-0 sm:gap-1">
                            {navLinks.map((link) => {
                                const active = isActive(link.routeName);
                                const Icon = link.icon;
                                return (
                                    <Link
                                        key={link.name}
                                        href={route(link.routeName)}
                                        className={`group relative flex flex-col items-center justify-between h-full pt-2 pb-1.5 min-w-[70px] sm:min-w-[80px] cursor-pointer transition-all border-b-[2px] ${
                                            active 
                                                ? "border-gray-900 text-gray-900" 
                                                : "border-transparent text-gray-500 hover:text-gray-900"
                                        }`}
                                    >
                                        <div className="flex flex-col items-center justify-center flex-1">
                                            <Icon 
                                                size={22} 
                                                strokeWidth={active ? 2.5 : 1.75}
                                                className={`mb-1 transition-transform group-hover:scale-110 ${active ? "fill-gray-900" : ""}`} 
                                            />
                                            <span className={`text-[12px] leading-3 tracking-tighter transition-colors ${
                                                active ? "font-bold" : "font-medium"
                                            }`}>
                                                {link.name}
                                            </span>
                                        </div>
                                    </Link>
                                );
                            })}
                        </nav>

                        {/* --- RIGHT SECTION --- */}
                        <div className="flex items-center h-full border-l border-gray-100 ml-2">
                            
                            {/* Language */}
                            <div className="hidden sm:flex items-center h-full">
                                <LanguageDropdown />
                            </div>
                            
                            {/* Subtle Separator */}
                            <div className="hidden sm:block w-[1px] h-8 bg-gray-100 mx-1" />

                            {/* Auth / Profile */}
                            <div className="hidden sm:flex items-center h-full">
                                {auth && auth.user ? (
                                    <ProfileDropdown user={auth.user} />
                                ) : (
                                    <div className="flex items-center gap-2 pl-4">
                                        <Link 
                                            href="/register" 
                                            className="text-[13px] font-bold text-gray-500 hover:text-gray-900 transition-all uppercase tracking-widest px-4 py-2 hover:bg-gray-50 rounded"
                                        >
                                            {t.nav.register}
                                        </Link>
                                        <Link 
                                            href="/login"
                                            className="text-[13px] font-bold text-[#0a66c2] border-2 border-[#0a66c2] px-6 py-1.5 rounded-full hover:bg-[#0a66c2] hover:text-white transition-all shadow-sm active:scale-95 uppercase tracking-tighter"
                                        >
                                            {t.nav.login}
                                        </Link>
                                    </div>
                                )}
                            </div>

                            {/* Mobile Triggers */}
                            <div className="lg:hidden flex items-center gap-1 pl-2">
                                <button className="text-gray-500 w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 active:scale-90 transition-all">
                                    <Search size={22} strokeWidth={2.5} />
                                </button>
                                <button 
                                    onClick={() => setIsMenuOpen(true)}
                                    className="text-gray-900 w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 active:scale-90 transition-all"
                                >
                                    <Menu size={24} strokeWidth={2.5} />
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            
            {/* Removed the Spacer <div className="h-[52px] sm:h-[62px] w-full" /> as requested to fix unwanted gap */}

            <AnimatePresence>
                {isMenuOpen && (
                    <MobileMenu 
                        links={navLinks} 
                        isActive={isActive} 
                        auth={auth}
                        onClose={() => setIsMenuOpen(false)}
                    />
                )}
            </AnimatePresence>
        </>
    );
};

export default Header;
