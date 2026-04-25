<?php

namespace App\Http\Controllers\API\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\Owner\CustomerDetailsResource;
use App\Http\Resources\API\Owner\CustomerResource;
use App\Models\Customer;
use App\Services\Owner\CustomerService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    use ApiResponse;

    protected $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Display a listing of customers.
     */
    public function index(): JsonResponse
    {
        try {
            $ownerId = Auth::user()->getTeamOwnerId();
            $customers = $this->customerService->getCustomers($ownerId);

            return $this->sendResponse(
                CustomerResource::collection($customers),
                'Customers fetched successfully.'
            );

        } catch (Exception $e) {
            return $this->sendError('Failed to fetch customers.', [$e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created customer in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $ownerId = Auth::user()->getTeamOwnerId();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('customers')->where(function ($query) use ($ownerId) {
                    return $query->where('owner_id', $ownerId);
                }),
            ],
            'country' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:New,Returning,VIP',
        ]);

        try {
            $customer = $this->customerService->createCustomer($ownerId, $validated);

            return $this->sendResponse(
                new CustomerResource($customer),
                'Customer created successfully.'
            );

        } catch (Exception $e) {
            return $this->sendError('Failed to create customer.', [$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified customer.
     */
    public function show($id): JsonResponse
    {
        try {
            $ownerId = Auth::user()->getTeamOwnerId();
            $data = $this->customerService->getCustomerDetails($ownerId, $id);

            return $this->sendResponse(
                new CustomerDetailsResource($data),
                'Customer details fetched successfully.'
            );

        } catch (Exception $e) {
            return $this->sendError('Failed to fetch customer details.', [$e->getMessage()], 500);
        }
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $ownerId = Auth::user()->getTeamOwnerId();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => [
                'sometimes',
                'email',
                Rule::unique('customers')->where(function ($query) use ($ownerId) {
                    return $query->where('owner_id', $ownerId);
                })->ignore($id),
            ],
            'country' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:New,Returning,VIP',
        ]);

        try {
            $customer = $this->customerService->updateCustomer($ownerId, $id, $validated);

            return $this->sendResponse(
                new CustomerResource($customer),
                'Customer updated successfully.'
            );

        } catch (Exception $e) {
            return $this->sendError('Failed to update customer.', [$e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified customer from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $ownerId = Auth::user()->getTeamOwnerId();

            $customer = Customer::where('owner_id', $ownerId)->find($id);

            if (! $customer) {
                return $this->sendError('Customer not found.', [], 404);
            }

            $this->customerService->deleteCustomer($ownerId, $id);

            return $this->sendResponse([], 'Customer deleted successfully.');

        } catch (Exception $e) {
            return $this->sendError('Failed to delete customer.', [$e->getMessage()], 500);
        }
    }
}
