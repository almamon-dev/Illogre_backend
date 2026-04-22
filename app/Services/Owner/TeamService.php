<?php

namespace App\Services\Owner;

use App\Models\User;
use App\Mail\TeamInvitationMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TeamService
{
    /**
     * Get team members and stats for a specific owner.
     */
    public function getTeamData($ownerId): array
    {
        $owner = User::findOrFail($ownerId);
        $members = $owner->members()->latest()->get();

        return [
            'stats' => [
                'total_members' => $members->count(),
                'online_now' => $members->where('last_active_at', '>', now()->subMinutes(5))->count(),
                'total_resolved' => 0, // Placeholder for future implementation
            ],
            'members' => $members
        ];
    }

    /**
     * Invite a new team member.
     */
    public function inviteMember(array $data, $ownerId)
    {
        $billingService = app(BillingService::class);
        if (!$billingService->checkLimit('member_limit', $ownerId)) {
            throw new \Exception('Team member limit reached for your current plan. Please upgrade to add more members.');
        }

        $name = 'Team Member'; // Default name until they update profile
        $password = $data['password'];
        
        $user = User::create([
            'parent_id' => $ownerId,
            'name' => $name,
            'email' => $data['email'],
            'password' => Hash::make($password),
            'user_type' => 'member',
            'role' => $data['role'],
            'status' => 'invited',
        ]);

        // Send Invitation Email
        $token = base64_encode($user->email);
        Mail::to($user->email)->send(new TeamInvitationMail($name, $user->email, $password, $user->role, $token));

        return $user;
    }

    /**
     * Update an existing team member's role.
     */
    public function updateMember($id, array $data, $ownerId)
    {
        $member = User::where('id', $id)
            ->where('parent_id', $ownerId)
            ->firstOrFail();

        $member->update([
            'role' => $data['role'],
        ]);

        return $member;
    }

    /**
     * Delete a team member.
     */
    public function deleteMember($id, $ownerId)
    {
        $member = User::where('id', $id)
            ->where('parent_id', $ownerId)
            ->firstOrFail();

        return $member->delete();
    }
}
