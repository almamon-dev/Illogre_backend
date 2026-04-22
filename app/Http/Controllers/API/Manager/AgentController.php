<?php

namespace App\Http\Controllers\API\Manager;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Manager\StoreAgentRequest;
use App\Http\Resources\API\Manager\AgentResource;
use App\Services\Manager\ManagerService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;

class AgentController extends Controller
{
    use ApiResponse;

    protected $managerService;

    public function __construct(ManagerService $managerService)
    {
        $this->managerService = $managerService;
    }

    /**
     * List all agents managed by the current Support Manager.
     */
    public function index(): JsonResponse
    {
        try {
            $data = $this->managerService->getDashboardData(auth()->id());

            return $this->sendResponse([
                'stats' => $data['stats'],
                'agents' => AgentResource::collection($data['agents']),
            ], 'Dashboard data fetched successfully.');
        } catch (Exception $e) {
            return $this->sendError('Failed to fetch agents.', [$e->getMessage()]);
        }
    }

    /**
     * Invite a new Support Agent (Sends Email).
     */
    public function store(StoreAgentRequest $request): JsonResponse
    {
        try {
            $invitation = $this->managerService->sendInvitation($request->validated(), auth()->id());

            return $this->sendResponse(
                $invitation,
                'Invitation sent successfully to '.$request->email
            );
        } catch (Exception $e) {
            return $this->sendError('Failed to send invitation.', [$e->getMessage()]);
        }
    }

    /**
     * Update an existing Support Agent.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $agent = $this->managerService->updateAgent($id, $request->all(), auth()->id());

            return $this->sendResponse(
                new AgentResource($agent),
                'Support Agent updated successfully.'
            );
        } catch (Exception $e) {
            return $this->sendError('Failed to update agent.', [$e->getMessage()]);
        }
    }

    /**
     * Remove a Support Agent.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $this->managerService->deleteAgent($id, auth()->id());

            return $this->sendResponse(
                [],
                'Support Agent deleted successfully.'
            );
        } catch (Exception $e) {
            return $this->sendError('Failed to delete agent.', [$e->getMessage()]);
        }
    }
}
