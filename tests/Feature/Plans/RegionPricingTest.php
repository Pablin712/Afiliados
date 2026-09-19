<?php

namespace Tests\Feature\Plans;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Region pricing decides what a buyer is actually charged, so these cover the
 * amount that ends up on the payment record - not just what the page shows.
 */
class RegionPricingTest extends TestCase
{
    use RefreshDatabase;

    /** Phones are unique and required by middleware, so each user gets one. */
    private int $phoneCounter = 0;

    private function uniquePhone(): string
    {
        return '+5939000'.str_pad((string) ++$this->phoneCounter, 4, '0', STR_PAD_LEFT);
    }

    public function test_bank_transfer_charges_the_price_of_the_chosen_region(): void
    {
        Storage::fake('public');

        $program = $this->programWithRegions();
        $latam = $program->regionPrices()->where('region', 'LATAM')->firstOrFail();
        $user = $this->freeUser();

        $response = $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'region_price_id' => $latam->id,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-1',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $response->assertSessionHasNoErrors();

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('120.00', (string) $payment->amount);
        $this->assertSame('LATAM', $payment->region_label);
    }

    public function test_another_region_on_the_same_plan_charges_its_own_price(): void
    {
        Storage::fake('public');

        $program = $this->programWithRegions();
        $usa = $program->regionPrices()->where('region', 'EE.UU.')->firstOrFail();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'region_price_id' => $usa->id,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-2',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertSessionHasNoErrors();

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('200.00', (string) $payment->amount);
        $this->assertSame('EE.UU.', $payment->region_label);
    }

    public function test_a_plan_that_charges_by_region_rejects_a_payment_without_one(): void
    {
        Storage::fake('public');

        $program = $this->programWithRegions();
        $user = $this->freeUser();

        $response = $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-3',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $response->assertSessionHas('error', __('messages.plans.region_required'));
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_region_belonging_to_another_plan_is_rejected(): void
    {
        Storage::fake('public');

        $program = $this->programWithRegions();
        $otherProgram = $this->programWithRegions('Otro plan');
        $foreignRegion = $otherProgram->regionPrices()->first();
        $user = $this->freeUser();

        $response = $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'region_price_id' => $foreignRegion->id,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-4',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ]);

        $response->assertSessionHas('error', __('messages.plans.region_required'));
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_the_amount_posted_by_the_form_is_ignored(): void
    {
        Storage::fake('public');

        $program = $this->programWithRegions();
        $latam = $program->regionPrices()->where('region', 'LATAM')->firstOrFail();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'region_price_id' => $latam->id,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-5',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
            'amount' => '1.00',
        ])->assertSessionHasNoErrors();

        $this->assertSame('120.00', (string) Payment::query()->firstOrFail()->amount);
    }

    public function test_a_plan_without_regions_keeps_charging_its_own_price(): void
    {
        Storage::fake('public');

        $program = $this->baseProgram();
        $user = $this->freeUser();

        $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-6',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertSessionHasNoErrors();

        $payment = Payment::query()->firstOrFail();

        $this->assertSame('147.00', (string) $payment->amount);
        $this->assertNull($payment->region_label);
    }

    public function test_renewal_uses_the_renewal_price_of_the_chosen_region(): void
    {
        Storage::fake('public');

        $program = $this->programWithRegions();
        $latam = $program->regionPrices()->where('region', 'LATAM')->firstOrFail();
        $user = $this->freeUser();

        // An earlier approved payment turns the next one into a renewal.
        Payment::query()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'number' => 'OLD',
            'amount' => 120,
            'state' => 'approved',
        ]);

        $this->actingAs($user)->post(route('plans.payment.store'), [
            'program_id' => $program->id,
            'region_price_id' => $latam->id,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-7',
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ])->assertSessionHasNoErrors();

        $payment = Payment::query()->where('number', 'TRX-7')->firstOrFail();

        $this->assertSame('40.00', (string) $payment->amount);
    }

    public function test_admin_can_save_and_replace_the_region_table_of_a_plan(): void
    {
        $admin = $this->admin();
        $program = $this->baseProgram();

        $this->actingAs($admin)->put(route('plans.programs.update', $program), [
            'name' => 'Plan editado',
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 2,
            'is_active' => '1',
            'region_prices' => [
                ['region' => 'EE.UU.', 'first_payment_cost' => '200', 'card_first_payment_cost' => '230', 'renewal_cost' => '70', 'card_renewal_cost' => '80'],
                ['region' => 'LATAM', 'first_payment_cost' => '120', 'card_first_payment_cost' => '', 'renewal_cost' => '40', 'card_renewal_cost' => ''],
                ['region' => '', 'first_payment_cost' => '', 'card_first_payment_cost' => '', 'renewal_cost' => '', 'card_renewal_cost' => ''],
            ],
        ])->assertSessionHasNoErrors();

        $program->refresh()->load('regionPrices');

        // The trailing blank row of the editor is dropped, not saved.
        $this->assertCount(2, $program->regionPrices);
        $this->assertTrue($program->chargesByRegion());

        $latam = $program->regionPrices->firstWhere('region', 'LATAM');
        $this->assertSame('120.00', (string) $latam->first_payment_cost);
        $this->assertNull($latam->card_first_payment_cost);

        // Saving again replaces the whole table.
        $this->actingAs($admin)->put(route('plans.programs.update', $program), [
            'name' => 'Plan editado',
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 2,
            'is_active' => '1',
            'region_prices' => [
                ['region' => 'Europa', 'first_payment_cost' => '180', 'renewal_cost' => '60'],
            ],
        ])->assertSessionHasNoErrors();

        $program->refresh()->load('regionPrices');

        $this->assertCount(1, $program->regionPrices);
        $this->assertSame('Europa', $program->regionPrices->first()->region);
    }

    public function test_a_non_admin_cannot_edit_plan_prices(): void
    {
        $program = $this->baseProgram();
        $user = $this->freeUser();

        $this->actingAs($user)->put(route('plans.programs.update', $program), [
            'name' => 'Intento',
            'first_payment_cost' => 1,
            'renewal_cost' => 1,
            'duration_months' => 1,
        ])->assertForbidden();

        $this->assertSame('Plan base', $program->refresh()->name);
    }

    private function customerType(): MembershipType
    {
        return MembershipType::query()->firstOrCreate(
            ['name' => 'customer'],
            ['affiliates_required' => 0, 'cost' => 97, 'profit' => 0],
        );
    }

    private function baseProgram(string $name = 'Plan base'): Program
    {
        return Program::query()->create([
            'name' => $name,
            'description' => 'Plan de prueba',
            'membership_type_id' => $this->customerType()->id,
            'first_payment_cost' => 147,
            'card_first_payment_cost' => 167,
            'renewal_cost' => 47,
            'card_renewal_cost' => 57,
            'duration_months' => 2,
            'is_active' => true,
        ]);
    }

    private function programWithRegions(string $name = 'Plan con regiones'): Program
    {
        $program = $this->baseProgram($name);

        $program->regionPrices()->createMany([
            [
                'region' => 'EE.UU.',
                'first_payment_cost' => 200,
                'card_first_payment_cost' => 230,
                'renewal_cost' => 70,
                'card_renewal_cost' => 80,
                'sort_order' => 0,
            ],
            [
                'region' => 'LATAM',
                'first_payment_cost' => 120,
                'card_first_payment_cost' => null,
                'renewal_cost' => 40,
                'card_renewal_cost' => null,
                'sort_order' => 1,
            ],
        ]);

        return $program->load('regionPrices');
    }

    private function bank(): Bank
    {
        return Bank::query()->firstOrCreate(
            ['name' => 'Banco de prueba'],
            ['owner' => 'AET', 'identification' => '9999999999', 'number' => '1234567890', 'amount' => 0],
        );
    }

    private function freeUser(): User
    {
        $user = User::factory()->create(['phone' => $this->uniquePhone()]);

        Membership::query()->create([
            'user_id' => $user->id,
            'membership_type_id' => $this->customerType()->id,
            'status' => 'active',
            'started_at' => now()->subMonth(),
            'expires_at' => now()->addDays(7),
        ]);

        return $user;
    }

    private function admin(): User
    {
        $role = Role::query()->firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $role->givePermissionTo(Permission::query()->firstOrCreate(['name' => 'edit landing_content', 'guard_name' => 'web']));

        $user = User::factory()->create(['phone' => $this->uniquePhone()]);
        $user->assignRole($role);

        return $user;
    }
}
