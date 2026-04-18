<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->price,
            'billing_period' => $this->billing_period,
            'trial_days' => $this->trial_days,
            'features' => $this->features,
            'is_popular' => $this->is_popular,
            'user_type' => $this->user_type,
            'order' => $this->order,
        ];
    }
}
