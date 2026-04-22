<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Owner\UpdateAISettingsRequest;
use App\Http\Requests\API\Owner\UpdateNotificationSettingsRequest;
use App\Http\Requests\API\Owner\UpdatePersonalDetailsRequest;
use App\Http\Requests\API\Owner\UpdateSecurityRequest;
use App\Http\Requests\API\Owner\UpdateWorkspaceSettingsRequest;
use App\Http\Resources\API\Owner\AISettingsResource;
use App\Http\Resources\API\Owner\NotificationSettingsResource;
use App\Http\Resources\API\Owner\PersonalDetailsResource;
use App\Http\Resources\API\Owner\SettingsResource;
use App\Http\Resources\API\Owner\WorkspaceSettingsResource;
use App\Services\Owner\SettingsService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsApiController extends Controller
{
    use ApiResponse;

    protected $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Get all current settings.
     */
    public function index(Request $request): JsonResponse
    {
        return $this->sendResponse(
            new SettingsResource($request->user()->load('settings')),
            'Settings data retrieved successfully.'
        );
    }

    /**
     * General Tab: Update Personal Details.
     */
    public function updateGeneral(UpdatePersonalDetailsRequest $request): JsonResponse
    {
        try {
            $user = $this->settingsService->updateSettings($request->user(), $request->validated());

            return $this->sendResponse(new PersonalDetailsResource($user), 'General details updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update general details.', [$e->getMessage()]);
        }
    }

    /**
     * Workspace Tab: Update Company Information.
     */
    public function updateWorkspace(UpdateWorkspaceSettingsRequest $request): JsonResponse
    {
        try {
            $user = $this->settingsService->updateSettings($request->user(), $request->validated());

            return $this->sendResponse(new WorkspaceSettingsResource($user), 'Workspace settings updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update workspace settings.', [$e->getMessage()]);
        }
    }

    /**
     * AI Settings Tab.
     */
    public function updateAI(UpdateAISettingsRequest $request): JsonResponse
    {
        try {
            $user = $this->settingsService->updateSettings($request->user(), $request->validated());

            return $this->sendResponse(new AISettingsResource($user), 'AI settings updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update AI settings.', [$e->getMessage()]);
        }
    }

    /**
     * Notifications Tab.
     */
    public function updateNotifications(UpdateNotificationSettingsRequest $request): JsonResponse
    {
        try {
            $user = $this->settingsService->updateSettings($request->user(), $request->validated());

            return $this->sendResponse(new NotificationSettingsResource($user), 'Notification preferences updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Failed to update notification settings.', [$e->getMessage()]);
        }
    }

    /**
     * Security Tab.
     */
    public function updateSecurity(UpdateSecurityRequest $request): JsonResponse
    {
        try {
            $this->settingsService->updateSecurity($request->user(), $request->validated());

            return $this->sendResponse([], 'Password updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), [], $e->getCode() ?: 500);
        }
    }
}
