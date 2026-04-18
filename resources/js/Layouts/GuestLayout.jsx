import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';

export default function GuestLayout({ children }) {
    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-[#f4f5f9] dark:bg-gray-900 p-4">
            

            <div className="w-full sm:max-w-[450px] bg-white dark:bg-gray-800 px-8 py-10 shadow-[0_10px_40px_-15px_rgba(0,0,0,0.1)] rounded-xl border border-gray-100 dark:border-gray-700">
                {children}
            </div>
        </div>
    );
}
