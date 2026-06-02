<?php

namespace App\Http\Controllers;

use App\Models\MembershipType;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $customerType = MembershipType::query()
            ->where('name', 'customer')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'first_payment_cost' => ['required', 'numeric', 'min:0'],
            'renewal_cost' => ['required', 'numeric', 'min:0'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Program::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => $validated['first_payment_cost'],
            'renewal_cost' => $validated['renewal_cost'],
            'duration_months' => $validated['duration_months'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', __('messages.plans.program_created'));
    }

    public function update(Request $request, Program $program): RedirectResponse
    {
        $customerType = MembershipType::query()
            ->where('name', 'customer')
            ->firstOrFail();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'first_payment_cost' => ['required', 'numeric', 'min:0'],
            'renewal_cost' => ['required', 'numeric', 'min:0'],
            'duration_months' => ['required', 'integer', 'min:1', 'max:24'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $program->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => $validated['first_payment_cost'],
            'renewal_cost' => $validated['renewal_cost'],
            'duration_months' => $validated['duration_months'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', __('messages.plans.program_updated'));
    }
}
