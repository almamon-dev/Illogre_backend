<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    /**
     * Show the profile settings page
     */
    public function index()
    {
        $user = Auth::user();
        
        return Inertia::render('Admin/Settings/General/Profile', [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'designation' => $user->designation,
                'bio' => $user->bio,
                'profile_picture' => Helper::generateURL($user->profile_picture),
            ]
        ]);
    }

    /**
     * Update the profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:255',
            'bio' => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048', // 2MB max
        ]);

        $data = $request->only(['name', 'email', 'phone_number', 'designation', 'bio']);

        if ($request->hasFile('profile_picture')) {
            // Delete old picture if exists
            if ($user->profile_picture) {
                Helper::deleteFile($user->profile_picture);
            }
            
            // Upload new picture
            $path = Helper::uploadFile('profiles', $request->file('profile_picture'));
            if ($path) {
                $data['profile_picture'] = $path;
            }
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Remove profile picture
     */
    public function removePicture()
    {
        $user = Auth::user();
        
        if ($user->profile_picture) {
            Helper::deleteFile($user->profile_picture);
            $user->update(['profile_picture' => null]);
        }

        return redirect()->back()->with('success', 'Profile picture removed.');
    }
}
