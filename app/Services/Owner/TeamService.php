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
        $owner = User::with(['members' => function($query) {
            $query->withCount([
                'tickets',
                'tickets as resolved_tickets_count' => function ($query) {
                    $query->where('status', 'Resolved');
                }
            ])->latest();
        }])->findOrFail($ownerId);

        $members = $owner->members;

        // Calculate total resolved for the whole team
        $ownerResolvedCount = \App\Models\Ticket::where('owner_id', $ownerId)->where('status', 'Resolved')->count();
        $teamResolvedCount = $members->sum('resolved_tickets_count') + $ownerResolvedCount;

        return [
            'stats' => [
                'total_members' => $members->count(),
                'online_now' => $members->filter(function ($member) {
                    return $member->last_active_at && $member->last_active_at->gt(now()->subMinutes(5));
                })->count(),
                'total_resolved' => $teamResolvedCount,
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
