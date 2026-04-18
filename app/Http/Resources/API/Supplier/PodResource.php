<?php

namespace App\Http\Resources\API\Supplier;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class PodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $statusMap = [
            'awaiting' => ['lbl' => 'Awaiting POD', 'clr' => 'warning', 'act' => 'Reupload', 'type' => 'upload'],
            'pending' => ['lbl' => 'Confirm POD', 'clr' => 'success', 'act' => 'View POD', 'type' => 'view'],
            'confirmed' => ['lbl' => 'Confirmed', 'clr' => 'info', 'act' => 'View POD', 'type' => 'view'],
            'rejected' => ['lbl' => 'Issued', 'clr' => 'danger', 'act' => 'Reupload', 'type' => 'upload'],
        ];

        $pod = $statusMap[$this->pod_status] ?? $statusMap['awaiting'];

        return [
            'id' => $this->id,
            'order_no' => $this->order_number,
            'client' => $this->customer?->name,
            'location' => $this->getLocationSummary(),
            'date' => $this->pickup_date ? Carbon::parse($this->pickup_date)->format('d M Y') : 'N/A',
            'status' => $pod['lbl'],
            'status_clr' => $pod['clr'],
            'price' => '€'.number_format($this->total_amount, 2),
            'action' => $pod['act'],
            'action_type' => $pod['type'],
            'file_url' => \App\Helpers\Helper::generateURL($this->proof_of_delivery),
        ];
    }

    private function getLocationSummary()
    {
        $pickup = explode(',', $this->pickup_address);
        $delivery = explode(',', $this->delivery_address);

        return trim(end($pickup)).' to '.trim(end($delivery));
    }
}
