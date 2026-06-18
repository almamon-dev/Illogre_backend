<?php

namespace App\Services\Owner;

use App\Models\Customer;
use App\Models\Ticket;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * Get all customers for an owner with stats and pagination.
     */
    public function getCustomers($ownerId, $perPage = 10, $search = null)
    {
        $query = Customer::where('owner_id', $ownerId)
            ->withCount(['tickets'])
            ->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get detailed information for a single customer.
     */
    public function getCustomerDetails($ownerId, $customerId)
    {
        $customer = Customer::where('owner_id', $ownerId)
            ->where('id', $customerId)
            ->firstOrFail();

        // Get tickets
        $tickets = Ticket::where('customer_email', $customer->email)
            ->where('owner_id', $ownerId)
            ->latest()
            ->get();

        // Get actual order history
        $orders = Order::where('customer_id', $customer->id)
            ->where('owner_id', $ownerId)
            ->latest()
            ->get();

        return [
            'customer' => $customer,
            'tickets' => [
                'open' => $tickets->whereIn('status', ['Pending', 'Open', 'In Progress']),
                'closed' => $tickets->whereIn('status', ['Resolved', 'Closed']),
            ],
            'order_history' => $orders,
        ];
    }

    /**
     * Manually create a customer.
     */
    public function createCustomer($ownerId, array $data)
    {
        $data['owner_id'] = $ownerId;
        
        // Use manual status if provided, otherwise calculate it
        if (!isset($data['status'])) {
            $data['status'] = $this->calculateStatus($data['total_orders'] ?? 0, $data['total_spent'] ?? 0);
        }

        return Customer::create($data);
    }

    /**
     * Update customer details.
     */
    public function updateCustomer($ownerId, $customerId, array $data)
    {
        $customer = Customer::where('owner_id', $ownerId)
            ->where('id', $customerId)
            ->firstOrFail();

        if (!isset($data['status']) && (isset($data['total_orders']) || isset($data['total_spent']))) {
            $data['status'] = $this->calculateStatus(
                $data['total_orders'] ?? $customer->total_orders,
                $data['total_spent'] ?? $customer->total_spent
            );
        }

        $customer->update($data);

        return $customer;
    }

    /**
     * Delete a customer.
     */
    public function deleteCustomer($ownerId, $customerId)
    {
        $customer = Customer::where('owner_id', $ownerId)
            ->where('id', $customerId)
            ->firstOrFail();

        return $customer->delete();
    }

    /**
     * Calculate customer status based on orders and spending.
     */
    protected function calculateStatus($orders, $spent)
    {
        // Simple logic: New (1), Returning (2+), VIP (Threshold)
        // VIP threshold could be dynamic, let's use 500 for now.
        $vipThreshold = 500;

        if ($spent >= $vipThreshold) {
            return 'VIP';
        }

        if ($orders >= 2) {
            return 'Returning';
        }

        return 'New';
    }
}
