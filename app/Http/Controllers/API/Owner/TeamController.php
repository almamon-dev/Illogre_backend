<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Owner\InviteTeamMemberRequest;
use App\Http\Requests\API\Owner\UpdateTeamMemberRequest;
use App\Http\Resources\Owner\TeamResource;
use App\Services\Owner\TeamService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TeamController extends Controller
{
    use ApiResponse;

    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * Display a listing of team members and stats.
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->teamService->getTeamData(Auth::id());

            return $this->sendResponse([
                'stats' => $data['stats'],
                'members' => TeamResource::collection($data['members']),
            ], 'Team data fetched successfully.');

        } catch (Exception $e) {
            return $this->sendError('Failed to fetch team data.', [$e->getMessage()], 500);
        }
    }

    /**
     * Invite a new team member.
     */
    public function invite(InviteTeamMemberRequest $request): JsonResponse
    {
        try {
            $member = $this->teamService->inviteMember($request->validated(), Auth::id());

            return $this->sendResponse(
                new TeamResource($member),
                'Team member invited successfully.'
            );

        } catch (Exception $e) {
            return $this->sendError('Failed to invite team member.', [$e->getMessage()], 500);
        }
    }

    /**
     * Update an existing team member.
     */
    public function update(UpdateTeamMemberRequest $request, $id): JsonResponse
    {
        try {
            $member = $this->teamService->updateMember($id, $request->validated(), Auth::id());

            return $this->sendResponse(
                new TeamResource($member),
                'Team member updated successfully.'
            );

        } catch (Exception $e) {
            return $this->sendError('Failed to update team member.', [$e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified team member.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->teamService->deleteMember($id, Auth::id());

            return $this->sendResponse(null, 'Team member removed successfully.');

        } catch (Exception $e) {
            return $this->sendError('Failed to remove team member.', [$e->getMessage()], 500);
        }
    }
}
