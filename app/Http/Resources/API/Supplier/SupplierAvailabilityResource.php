<?php

namespace App\Http\Resources\API\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierAvailabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array (Full Detail).
     */
    public function toArray(Request $request): array
    {
        $limit = (float) $this->capacity_limit;
        $used = (float) $this->capacity_used;
        $remaining = $limit - $used;

        $status_label = 'Available';
        $status_clr = 'success';

        if ($this->status === 'inactive') {
            $status_label = 'Closed';
            $status_clr = 'danger';
        } elseif ($this->type === 'standard') {
            if ($remaining <= 5 && $remaining > 0) {
                $status_label = 'Limited';
                $status_clr = 'warning';
            } elseif ($remaining <= 0) {
                $status_label = 'Full';
                $status_clr = 'danger';
            }
        }

        return [
            'id' => $this->id,
            'date' => $this->start_date?->format('d M Y'),
            'type' => $this->type === 'standard' ? 'Date-Based' : 'Route-Based',
            'route_name' => $this->route_name,
            'route' => $this->route_name ?? ($this->pickup_region.' → '.$this->delivery_region),
            'pickup' => $this->pickup_region,
            'delivery' => $this->delivery_region,
            'trailer' => $this->trailer_type,
            'days' => $this->days_of_week,

            // Capacity
            'total' => $limit > 0 ? $limit.' LDM' : '--',
            'used' => $used.' LDM',
            'remain' => $limit > 0 ? $remaining.' LDM' : '----',

            // Raw/Detail Fields
            'time_start' => $this->start_time ?? 'N/A',
            'time_end' => $this->end_time ?? 'N/A',
            'min_weight' => $this->min_weight ?? 0,
            'max_weight' => $this->max_weight ?? 0,
            'price' => '€'.number_format($this->price, 2),

            // UI Status
            'status' => $status_label,
            'clr' => $status_clr,
            'note' => $this->notes ?? 'Available',
            'created_at' => $this->created_at?->format('d M Y, h:i A'),
        ];
    }
}
