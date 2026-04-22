<?php

namespace App\Http\Resources\API\Owner;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkspaceSettingsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'company_name' => $this->company_name,
            'support_email' => $this->getSetting('support_email'),
            'website_url' => $this->getSetting('website_url'),
            'brand_logo' => Helper::generateURL($this->getSetting('brand_logo')),
        ];
    }
}
