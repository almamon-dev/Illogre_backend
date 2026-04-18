<?php

namespace App\Http\Resources\API\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplierAvailabilityListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $remaining = $this->capacity_limit - $this->capacity_used;
        $status_label = 'Available';
        $status_clr = 'success';

        if ($this->status === 'inactive') {
            $status_label = 'Closed';
            $status_clr = 'danger';
        } elseif ($remaining <= 5 && $remaining > 0) {
            $status_label = 'Limited';
            $status_clr = 'warning';
        } elseif ($remaining <= 0) {
            $status_label = 'Full';
            $status_clr = 'danger';
        }

        return [
            'id' => $this->id,
            'date' => $this->start_date?->format('d M Y'),
            'type' => $this->type === 'standard' ? 'Date-Based' : 'Route-Based',
            'route' => $this->route_name ?? ($this->pickup_region.' → '.$this->delivery_region),
            'pickup' => $this->pickup_region,
            'delivery' => $this->delivery_region,
            'trailer' => $this->trailer_type,
            'days' => $this->days_of_week,
            'total' => $this->capacity_limit ? $this->capacity_limit.' LDM' : '--',
            'used' => ($this->capacity_used ?: 0).' LDM',
            'remain' => $this->capacity_limit ? ($this->capacity_limit - $this->capacity_used).' LDM' : '----',
            'status' => $status_label,
            'status_clr' => $status_clr,
            'orders' => $this->capacity_used > 0 ? floor($this->capacity_used / 2) : 0,
            'note' => $this->notes ?? 'Available',
            'calendar_note' => $this->notes ?? 'At the moment, capacity is available.',
        ];
    }
}
