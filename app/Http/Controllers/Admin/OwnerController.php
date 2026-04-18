<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OwnerController extends Controller
{
    public function index(Request $request)
    {
        $owners = User::where('user_type', 'owner')
            ->with(['subscription.plan'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Owners/Index', [
            'owners' => $owners
        ]);
    }
}
