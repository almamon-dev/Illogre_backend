import React from "react";
import {
    Activity,
    Facebook,
    Twitter,
    Instagram,
    Linkedin,
    Youtube,
    Mail,
    Phone,
    MapPin,
    Globe,
    ChevronDown,
} from "lucide-react";
import { usePage, Link } from "@inertiajs/react";
import { useLanguage } from "@/Contexts/LanguageContext";

/**
 * EXECUTIVE GLOBAL FOOTER (LINKEDIN LIGHT MODE SYNC)
 */

export default function Footer() {
    const { t } = useLanguage();
    const { settings } = usePage().props;

    const siteName = settings?.company_name || settings?.site_name || "EliteFleet";

    return (
        <footer className="footer bg-white border-t border-gray-200 py-10 lg:py-14 px-6 relative overflow-hidden font-sans">
            <div className="max-w-7xl mx-auto relative z-10">
                
                {/* --- TOP GRID: DIRECTORY ARCHITECTURE --- */}
                <div className="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-x-8 gap-y-10 mb-12">
                    
                    {/* Brand Identifier */}
                    <div className="col-span-2 lg:pr-10">
                        <Link href="/" className="flex items-center gap-2 mb-4">
                            {settings?.site_logo ? (
                                <img src={settings.site_logo} alt={siteName} className="h-6 w-auto" />
                            ) : (
                                <>
                                    <div className="bg-[#0a66c2] p-1.5 rounded-[4px] shadow-sm">
                                        <Activity className="text-white" size={16} />
                                    </div>
                                    <span className="text-[#000000e6] font-black text-[18px] tracking-tighter uppercase">
                                        {siteName}
                                    </span>
                                </>
                            )}
                        </Link>
                        <p className="text-[12px] leading-relaxed text-gray-500 font-medium mb-6">
                            {t.footer.about_desc}
                        </p>
                        <div className="flex gap-3">
                            {settings?.linkedin_url && <SocialButton Icon={Linkedin} href={settings.linkedin_url} hoverColor="#0A66C2" />}
                            {settings?.facebook_url && <SocialButton Icon={Facebook} href={settings.facebook_url} hoverColor="#1877F2" />}
                            {settings?.twitter_url && <SocialButton Icon={Twitter} href={settings.twitter_url} hoverColor="#000000" />}
                            {settings?.instagram_url && <SocialButton Icon={Instagram} href={settings.instagram_url} hoverColor="#E4405F" />}
                            {settings?.youtube_url && <SocialButton Icon={Youtube} href={settings.youtube_url} hoverColor="#FF0000" />}
                        </div>
                    </div>

                    {/* Directory Columns */}
                    <FooterColumn title={t.footer.quick_links} links={[
                        { label: t.nav.home, href: route('home') },
                        { label: t.nav.cars, href: route('car.list') },
                        { label: t.nav.about, href: route('about.index') },
                        { label: t.nav.contact, href: route('contact.index') }
                    ]} />
                    
                    {/* Contact Details Column */}
                    <div className="col-span-1">
                        <h4 className="text-gray-900 font-bold mb-4 text-[11px] uppercase tracking-[0.15em]">{t.nav.contact || "Contact"}</h4>
                        <ul className="space-y-3">
                            {settings?.company_phone && (
                                <li className="flex items-center gap-2 text-[12px] text-gray-500 font-bold group cursor-pointer">
                                    <div className="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100 group-hover:border-[#0a66c2]/30 group-hover:bg-blue-50 transition-all">
                                        <Phone size={14} className="text-gray-400 group-hover:text-[#0a66c2]" />
                                    </div>
                                    <span className="group-hover:text-gray-900 transition-colors">{settings.company_phone}</span>
                                </li>
                            )}
                            {settings?.company_email && (
                                <li className="flex items-center gap-2 text-[12px] text-gray-500 font-bold group cursor-pointer">
                                    <div className="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100 group-hover:border-[#0a66c2]/30 group-hover:bg-blue-50 transition-all">
                                        <Mail size={14} className="text-gray-400 group-hover:text-[#0a66c2]" />
                                    </div>
                                    <span className="group-hover:text-gray-900 transition-colors">{settings.company_email}</span>
                                </li>
                            )}
                            {settings?.company_address && (
                                <li className="flex items-start gap-2 text-[12px] text-gray-500 font-bold group cursor-pointer">
                                    <div className="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center border border-gray-100 group-hover:border-[#0a66c2]/30 group-hover:bg-blue-50 transition-all flex-shrink-0">
                                        <MapPin size={14} className="text-gray-400 group-hover:text-[#0a66c2]" />
                                    </div>
                                    <span className="group-hover:text-gray-900 transition-colors mt-1.5">{settings.company_address}</span>
                                </li>
                            )}
                        </ul>
                    </div>

                    {/* Support & Legal */}
                    <FooterColumn title={t.footer.support} links={[
                        { label: t.footer.help_center, href: route('help.index') },
                        { label: t.footer.terms, href: route('terms.index') },
                        { label: t.footer.privacy, href: route('privacy.index') },
                    ]} />
                    
                    {/* Control Panel / Language */}
                    <div className="col-span-1">
                         <h4 className="text-gray-900 font-bold mb-4 text-[11px] uppercase tracking-[0.15em]">{t.footer.rights}</h4>
                         <button className="w-full flex items-center justify-between px-3 py-1.5 border border-gray-300 rounded-[4px] text-[12px] font-bold text-gray-600 hover:bg-gray-50 transition-all mb-4">
                             <div className="flex items-center gap-2">
                                <Globe size={14} className="text-[#0a66c2]" />
                                <span>{t.nav.language}</span>
                             </div>
                             <ChevronDown size={14} />
                         </button>
                    </div>
                </div>

                {/* --- BOTTOM INTERFACE: LEGAL & DEPLOYMENT --- */}
                <div className="pt-8 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6">
                     <div className="flex items-center flex-wrap justify-center gap-x-6 gap-y-2">
                          <span className="text-[#000000e6] font-black text-[14px] tracking-tighter uppercase mr-2">
                             {siteName} <span className="text-gray-400 font-normal lowercase ml-1">© 2026</span>
                          </span>
                          <Link href={route('terms.index')} className="text-[11px] font-bold text-gray-500 hover:text-[#0a66c2] hover:underline transition-colors decoration-[#0a66c2] underline-offset-2">
                              {t.footer.terms}
                          </Link>
                          <Link href={route('privacy.index')} className="text-[11px] font-bold text-gray-500 hover:text-[#0a66c2] hover:underline transition-colors decoration-[#0a66c2] underline-offset-2">
                              {t.footer.privacy}
                          </Link>
                     </div>

                    {/* Verified Payment Grid (LinkedIn Style Grayscale) */}
                    <div className="flex items-center gap-6 opacity-30 grayscale hover:opacity-60 transition-all duration-500">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" className="h-2.5" />
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa" className="h-2" />
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" className="h-3.5" />
                    </div>
                </div>
            </div>
        </footer>
    );
}

const FooterColumn = ({ title, links }) => (
    <div>
        <h4 className="text-gray-900 font-bold mb-4 text-[11px] uppercase tracking-[0.15em]">{title}</h4>
        <ul className="space-y-2.5">
            {links.map((link, idx) => (
                <li key={idx}>
                    <Link href={link.href} className="text-[12px] text-gray-500 font-bold hover:text-[#0a66c2] hover:underline cursor-pointer transition-colors decoration-[#0a66c2] decoration-2 underline-offset-4">
                        {link.label}
                    </Link>
                </li>
            ))}
        </ul>
    </div>
);

const LegalLink = ({ label }) => (
    <span className="text-[11px] font-bold text-gray-500 hover:text-[#0a66c2] hover:underline cursor-pointer transition-colors decoration-[#0a66c2] underline-offset-2">
        {label}
    </span>
);

const SocialButton = ({ Icon, href }) => (
    <a href={href} target="_blank" rel="noopener noreferrer" className="text-gray-500 hover:text-[#0a66c2] transition-colors cursor-pointer p-0.5">
        <Icon size={18} />
    </a>
);
