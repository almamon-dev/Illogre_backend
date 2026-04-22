<?php

namespace App\Http\Resources\API\Owner;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalDetailsResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'avatar_url' => Helper::generateURL($this->getSetting('avatar_url')),
        ];
    }
}
