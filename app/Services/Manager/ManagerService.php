<?php

namespace App\Services\Manager;

use App\Mail\AgentInvitationMail;
use App\Models\User;
use App\Repositories\RegistrationRepository;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class ManagerService
{
    protected $registrationRepo;

    public function __construct(RegistrationRepository $registrationRepo)
    {
        $this->registrationRepo = $registrationRepo;
    }

    /**
     * Send an invitation to a new agent (Stored in Cache).
     */
    public function sendInvitation(array $data, $managerId)
    {
        // Check if user already exists in DB
        if (User::where('email', $data['email'])->exists()) {
            throw new Exception('A user with this email already exists.');
        }

        $manager = User::findOrFail($managerId);
        $password = $data['temporary_password'];

        $invitationData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password, // Store plain password to hash later or hash now
            'parent_id' => $managerId,
            'company_name' => $manager->company_name,
            'user_type' => 'member',
            'role' => 'Support Agent',
        ];

        // Store in Cache instead of DB
        $this->registrationRepo->storeCachedRegistration($data['email'], $invitationData);

        // Generate Accept URL (token is the email)
        $token = base64_encode($data['email']);
        $acceptUrl = config('app.frontend_url').'/auth/accept-agent-invitation?token='.$token;

        Mail::to($data['email'])->send(new AgentInvitationMail($manager->name, $acceptUrl, $password, $data['email']));

        return $invitationData;
    }

    /**
     * Accept an invitation and save it to the Database.
     */
    public function acceptInvitation(string $token)
    {
        $email = base64_decode($token);

        // Retrieve from Cache
        $invitationData = $this->registrationRepo->getCachedRegistration($email);

        if (! $invitationData) {
            throw new Exception('Invitation expired or invalid.');
        }

        return DB::transaction(function () use ($invitationData) {
            $user = User::create([
                'name' => $invitationData['name'],
                'email' => $invitationData['email'],
                'password' => Hash::make($invitationData['password']),
                'parent_id' => $invitationData['parent_id'],
                'company_name' => $invitationData['company_name'],
                'user_type' => $invitationData['user_type'],
                'role' => $invitationData['role'],
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            // Clear Cache after successful save
            Cache::forget("reg_{$invitationData['email']}");

            return $user;
        });
    }

    /**
     * Get agents and dashboard stats managed by the manager.
     */
    public function getDashboardData($managerId)
    {
        $agents = User::where('parent_id', $managerId)
            ->where('role', 'Support Agent')
            ->withCount([
                'tickets',
                'tickets as resolved_tickets_count' => function ($query) {
                    $query->where('status', 'Resolved');
                }
            ])
            ->latest()
            ->get();

        $managerResolvedCount = \App\Models\Ticket::where('owner_id', $managerId)->where('status', 'Resolved')->count();
        $totalResolved = $agents->sum('resolved_tickets_count') + $managerResolvedCount;

        return [
            'stats' => [
                'total_members' => $agents->count(),
                'online_now' => $agents->filter(function ($agent) {
                    return $agent->last_active_at && $agent->last_active_at->gt(now()->subMinutes(5));
                })->count(),
                'total_resolved' => $totalResolved,
            ],
            'agents' => $agents,
        ];
    }

    /**
     * Update an agent's details.
     */
    public function updateAgent($id, array $data, $managerId)
    {
        $agent = User::where('id', $id)
            ->where('parent_id', $managerId)
            ->where('role', 'Support Agent')
            ->firstOrFail();

        $agent->update([
            'name' => $data['name'] ?? $agent->name,
            'role' => $data['role'] ?? $agent->role,
        ]);

        return $agent;
    }

    /**
     * Delete an agent.
     */
    public function deleteAgent($id, $managerId)
    {
        $agent = User::where('id', $id)
            ->where('parent_id', $managerId)
            ->where('role', 'Support Agent')
            ->firstOrFail();

        return $agent->delete();
    }
}
