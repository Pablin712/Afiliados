<?php

namespace App\Http\Controllers;

use App\Http\Requests\Programs\ProgramRequest;
use App\Models\MembershipType;
use App\Models\Program;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class ProgramController extends Controller
{
    public function store(ProgramRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $program = Program::create($this->payloadFrom($request));

            $this->syncRegionPrices($program, $request->input('region_prices', []));
        });

        return back()->with('status', __('messages.plans.program_created'));
    }

    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        DB::transaction(function () use ($request, $program): void {
            $program->update($this->payloadFrom($request));

            $this->syncRegionPrices($program, $request->input('region_prices', []));
        });

        return back()->with('status', __('messages.plans.program_updated'));
    }

    public function destroy(Program $program): RedirectResponse
    {
        abort_unless(request()->user()?->hasRole('admin'), 403);

        // Region prices go with it through the cascading foreign key.
        $program->delete();

        return back()->with('status', __('messages.plans.program_deleted'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFrom(ProgramRequest $request): array
    {
        $customerType = MembershipType::query()
            ->where('name', 'customer')
            ->firstOrFail();

        return [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'membership_type_id' => $customerType->id,
            'first_payment_cost' => $request->input('first_payment_cost'),
            'card_first_payment_cost' => $request->input('card_first_payment_cost'),
            'renewal_cost' => $request->input('renewal_cost'),
            'card_renewal_cost' => $request->input('card_renewal_cost'),
            'duration_months' => $request->input('duration_months'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    /**
     * The editor posts the whole region table on every save, so the stored
     * rows are replaced wholesale.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function syncRegionPrices(Program $program, array $rows): void
    {
        $program->regionPrices()->delete();

        foreach (array_values($rows) as $index => $row) {
            $program->regionPrices()->create([
                'region' => $row['region'],
                'countries' => $row['countries'],
                'first_payment_cost' => $row['first_payment_cost'],
                'card_first_payment_cost' => $row['card_first_payment_cost'] ?? null,
                'renewal_cost' => $row['renewal_cost'],
                'card_renewal_cost' => $row['card_renewal_cost'] ?? null,
                'sort_order' => $index,
            ]);
        }
    }
}
