import React, { useState } from "react";
import Header from "../Components/Navigation/Admin/Header";
import Sidebar from "../Components/Navigation/Admin/Sidebar";

export default function AdminLayout({ children }) {
    const [isMobileOpen, setIsMobileOpen] = useState(false);
    const [isCollapsed, setIsCollapsed] = useState(false); // Initially open/expanded

    // Constants for width
    const expandedWidth = "260px";
    const collapsedWidth = "90px";

    return (
        <div className="flex h-screen bg-[#f8fafc] overflow-hidden">
            {/* Mobile Overlay */}
            {isMobileOpen && (
                <div
                    className="fixed inset-0 bg-slate-900/20 z-[155] lg:hidden backdrop-blur-sm"
                    onClick={() => setIsMobileOpen(false)}
                />
            )}

            {/* Dynamic Sidebar Container */}
            <aside
                style={{ width: isCollapsed ? collapsedWidth : expandedWidth }}
                className={`fixed inset-y-0 left-0 z-[160] bg-white transition-all duration-300 ease-in-out border-r border-slate-200/60
                    ${isMobileOpen ? "translate-x-0" : "-translate-x-full lg:translate-x-0"}`}
            >
                <Sidebar
                    isCollapsed={isCollapsed}
                    toggleCollapse={() => setIsCollapsed(!isCollapsed)}
                />
            </aside>

            {/* Dynamic Content Wrapper */}
            <div
                className="flex-1 flex flex-col min-w-0 transition-all duration-300 ease-in-out"
                style={{ marginLeft: isCollapsed ? collapsedWidth : expandedWidth }}
            >
                <Header onMenuClick={() => setIsMobileOpen(true)} />

                <main className="flex-1 overflow-y-auto">
                    <div className="p-6 md:p-8 max-w-9xl mx-auto">
                        {children}
                    </div>
                </main>
            </div>
        </div>
    );
}
