import React, { useRef } from 'react';
import SettingsLayout from '../SettingsLayout';
import { User, Mail, Phone, Camera, Loader2, Save, Trash2, ShieldCheck } from 'lucide-react';
import { useForm, router } from '@inertiajs/react';
import { toast } from 'react-toastify';

export default function Profile({ user }) {
    const fileInputRef = useRef();

    const { data, setData, post, processing, errors, reset } = useForm({
        name: user?.name || '',
        email: user?.email || '',
        phone_number: user?.phone_number || '',
        designation: user?.designation || '',
        bio: user?.bio || '',
        profile_picture: null,
    });

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            setData('profile_picture', file);
        }
    };

    const triggerFileInput = () => {
        fileInputRef.current.click();
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('admin.settings.general.profile.update'), {
            onSuccess: () => {
                toast.success('Profile updated successfully!');
                setData('profile_picture', null);
            },
            preserveScroll: true
        });
    };

    const removePicture = () => {
        if (confirm('Are you sure you want to remove your profile picture?')) {
            router.post(route('admin.settings.general.profile.remove-picture'), {}, {
                onSuccess: () => toast.success('Profile picture removed.'),
            });
        }
    };

    return (
        <SettingsLayout
            title="Profile Management"
            subtitle="Manage your administrative credentials and public persona."
            breadcrumbs={["General", "Profile"]}
        >
            <div className="p-10 font-['Plus_Jakarta_Sans',sans-serif]">
                <form onSubmit={handleSubmit} className="max-w-8xl space-y-12">
                    {/* Avatar Master Section */}
                    <div className="flex items-start gap-10">
                        <div className="relative group">
                            <div className="w-32 h-32 rounded-md bg-slate-50 border-2 border-dashed border-slate-200 flex items-center justify-center text-slate-300 overflow-hidden transition-all group-hover:border-[#d9f196]/50">
                                {data.profile_picture ? (
                                    <img src={URL.createObjectURL(data.profile_picture)} alt="Preview" className="w-full h-full object-cover" />
                                ) : user.profile_picture ? (
                                    <img src={user.profile_picture} alt="Profile" className="w-full h-full object-cover" />
                                ) : (
                                    <User size={48} strokeWidth={1.5} />
                                )}
                            </div>
                            <input
                                type="file"
                                ref={fileInputRef}
                                onChange={handleFileChange}
                                className="hidden"
                                accept="image/*"
                            />
                            <button
                                type="button"
                                onClick={triggerFileInput}
                                className="absolute -bottom-3 -right-3 w-10 h-10 bg-[#d9f196] text-[#1a1c21] rounded-md flex items-center justify-center shadow-lg border-4 border-white hover:scale-110 transition-all active:scale-95"
                            >
                                <Camera size={18} strokeWidth={2.5} />
                            </button>
                        </div>
                        <div className="pt-2">
                            <h4 className="text-[18px] font-extrabold text-[#1a1c21]">Account Identifier</h4>
                            <p className="text-[13px] text-slate-500 mt-1 font-medium italic">High-resolution avatars recommended (min 400x400px)</p>
                            <div className="flex gap-4 mt-5">
                                <button
                                    type="button"
                                    onClick={triggerFileInput}
                                    className="px-4 py-2 bg-slate-900 text-white rounded-lg text-[12px] font-bold hover:bg-slate-800 transition-all shadow-md"
                                >
                                    Change Image
                                </button>
                                {(user.profile_picture || data.profile_picture) && (
                                    <button
                                        type="button"
                                        onClick={removePicture}
                                        className="px-4 py-2 bg-white border border-rose-100 text-rose-500 rounded-lg text-[12px] font-bold hover:bg-rose-50 transition-all"
                                    >
                                        Remove
                                    </button>
                                )}
                            </div>
                            {errors.profile_picture && <p className="text-rose-500 text-[12px] mt-2 font-bold">{errors.profile_picture}</p>}
                        </div>
                    </div>

                    {/* Information Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6 pt-6 border-t border-slate-50">
                        <div className="col-span-2 flex items-center gap-2 mb-2">
                            <ShieldCheck size={18} className="text-[#d9f196]" />
                            <h3 className="text-[14px] font-extrabold text-slate-400">Authentication & Info</h3>
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Display Name</label>
                            <input
                                type="text"
                                value={data.name}
                                onChange={e => setData('name', e.target.value)}
                                className={`w-full h-12 px-4 bg-slate-50/50 border ${errors.name ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all placeholder:text-slate-300`}
                                placeholder="Your full name"
                            />
                            {errors.name && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.name}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Email Connection</label>
                            <div className="relative group">
                                <Mail size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={e => setData('email', e.target.value)}
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.email ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all`}
                                />
                            </div>
                            {errors.email && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.email}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Contact Line</label>
                            <div className="relative group">
                                <Phone size={16} className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-[#1a1c21] transition-colors" />
                                <input
                                    type="text"
                                    value={data.phone_number}
                                    onChange={e => setData('phone_number', e.target.value)}
                                    placeholder="+1 (555) 000-0000"
                                    className={`w-full h-12 pl-11 pr-4 bg-slate-50/50 border ${errors.phone_number ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all`}
                                />
                            </div>
                            {errors.phone_number && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.phone_number}</p>}
                        </div>

                        <div className="space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Official Position</label>
                            <input
                                type="text"
                                value={data.designation}
                                onChange={e => setData('designation', e.target.value)}
                                className={`w-full h-12 px-4 bg-slate-50/50 border ${errors.designation ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all`}
                                placeholder="e.g. Senior Administrator"
                            />
                            {errors.designation && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.designation}</p>}
                        </div>

                        <div className="col-span-2 space-y-1.5">
                            <label className="text-[13px] font-bold text-slate-700 ml-1">Biographical Narrative</label>
                            <textarea
                                rows="4"
                                value={data.bio}
                                onChange={e => setData('bio', e.target.value)}
                                placeholder="Describe your background and role..."
                                className={`w-full p-4 bg-slate-50/50 border ${errors.bio ? 'border-rose-300' : 'border-slate-200'} rounded-md text-[14px] font-bold text-[#1a1c21] focus:outline-none focus:border-[#d9f196] focus:ring-4 focus:ring-[#d9f196]/10 transition-all resize-none leading-relaxed`}
                            ></textarea>
                            {errors.bio && <p className="text-rose-500 text-[12px] font-medium ml-1">{errors.bio}</p>}
                        </div>
                    </div>

                    {/* Footer Actions */}
                    <div className="border-slate-50 flex items-center justify-end gap-5">
                        <button
                            type="button"
                            onClick={() => reset()}
                            className="px-6 py-3 text-[14px] font-extrabold text-slate-400 bg-slate-100 rounded-md hover:text-slate-600 transition-colors"
                        >
                            Reset Fields
                        </button>
                        <button
                            type="submit"
                            disabled={processing}
                            className="bg-[#d9f196] text-[#1a1c21] px-10 py-3 rounded-md font-black text-[14px] hover:bg-[#b0d552] transition-all shadow-lg shadow-lime-200/50 flex items-center gap-2 disabled:opacity-70 active:scale-95"
                        >
                            {processing ? <Loader2 size={18} className="animate-spin" strokeWidth={3} /> : <Save size={18} strokeWidth={3} />}
                            Synchronize Profile
                        </button>
                    </div>
                </form>
            </div>
        </SettingsLayout>
    );
}
