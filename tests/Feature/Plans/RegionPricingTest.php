<?php

namespace Tests\Feature\Plans;

use App\Models\Bank;
use App\Models\Membership;
use App\Models\MembershipType;
use App\Models\Payment;
use App\Models\Program;
use App\Models\User;
use App\Services\DatafastService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The buyer's region is decided by the country they connect from, never by
 * them, and it decides what they are charged - so these cover the amount that
 * ends up on the payment record, not just what the page shows.
 */
class RegionPricingTest extends TestCase
{
    use RefreshDatabase;

    /** Any public address: the geolocation answer is faked per test. */
    private const VISITOR_IP = '8.8.8.8';

    /** Phones are unique and required by middleware, so each user gets one. */
    private int $phoneCounter = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Requests from the test client come from 127.0.0.1, which cannot be
        // located; a public address makes the lookup happen.
        $this->withServerVariables(['REMOTE_ADDR' => self::VISITOR_IP]);
    }

    private function uniquePhone(): string
    {
        return '+5939000'.str_pad((string) ++$this->phoneCounter, 4, '0', STR_PAD_LEFT);
    }

    public function test_a_visitor_from_the_united_states_is_charged_the_us_price(): void
    {
        Storage::fake('public');
        $this->connectFrom('US');

        $program = $this->programWithRegions();
        $usa = $program->regionPrices->firstWhere('region', 'EE.UU.');
        $user = $this->freeUser();

        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, $usa->id))
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('error');

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('200.00', (string) $payment->amount);
        $this->assertSame('EE.UU.', $payment->region_label);
        $this->assertSame('US', $payment->country_code);
    }

    public function test_a_visitor_from_a_country_in_a_group_is_charged_that_group_price(): void
    {
        Storage::fake('public');
        $this->connectFrom('CO');

        $program = $this->programWithRegions();
        $latam = $program->regionPrices->firstWhere('region', 'LATAM');
        $user = $this->freeUser();

        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, $latam->id))
            ->assertSessionMissing('error');

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('120.00', (string) $payment->amount);
        $this->assertSame('LATAM', $payment->region_label);
        $this->assertSame('CO', $payment->country_code);
    }

    public function test_the_buyer_cannot_pick_a_cheaper_region_than_their_own(): void
    {
        Storage::fake('public');
        $this->connectFrom('US');

        $program = $this->programWithRegions();
        $latam = $program->regionPrices->firstWhere('region', 'LATAM');
        $user = $this->freeUser();

        // Connected from the US but posting the LATAM row, by hand or because
        // the page was loaded before switching a VPN on.
        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, $latam->id))
            ->assertSessionHas('error', __('messages.plans.region_changed'));

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_a_visitor_outside_every_region_pays_the_plan_base_price(): void
    {
        Storage::fake('public');
        $this->connectFrom('JP');

        $program = $this->programWithRegions();
        $user = $this->freeUser();

        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, null))
            ->assertSessionMissing('error');

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('147.00', (string) $payment->amount);
        $this->assertNull($payment->region_label);
        $this->assertSame('JP', $payment->country_code);
    }

    public function test_an_unlocatable_visitor_pays_the_plan_base_price(): void
    {
        Storage::fake('public');
        Http::fake(['*' => Http::response('', 500)]);

        $program = $this->programWithRegions();
        $user = $this->freeUser();

        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, null))
            ->assertSessionMissing('error');

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('147.00', (string) $payment->amount);
        $this->assertNull($payment->country_code);
    }

    public function test_the_region_with_fewer_countries_wins_when_two_list_the_same_one(): void
    {
        Storage::fake('public');
        $this->connectFrom('EC');

        $program = $this->programWithRegions();
        $ecuador = $program->regionPrices()->create([
            'region' => 'Ecuador',
            'countries' => ['EC'],
            'first_payment_cost' => 99,
            'renewal_cost' => 30,
            'sort_order' => 5,
        ]);
        $user = $this->freeUser();

        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, $ecuador->id))
            ->assertSessionMissing('error');

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('99.00', (string) $payment->amount);
        $this->assertSame('Ecuador', $payment->region_label);
    }

    public function test_the_amount_posted_by_the_form_is_ignored(): void
    {
        Storage::fake('public');
        $this->connectFrom('EC');

        $program = $this->programWithRegions();
        $latam = $program->regionPrices->firstWhere('region', 'LATAM');
        $user = $this->freeUser();

        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, $latam->id) + ['amount' => '1.00'])
            ->assertSessionMissing('error');

        $this->assertSame('120.00', (string) Payment::query()->firstOrFail()->amount);
    }

    public function test_renewal_uses_the_renewal_price_of_the_visitor_region(): void
    {
        Storage::fake('public');
        $this->connectFrom('EC');

        $program = $this->programWithRegions();
        $latam = $program->regionPrices->firstWhere('region', 'LATAM');
        $user = $this->freeUser();

        // An earlier approved payment turns the next one into a renewal.
        Payment::query()->create([
            'user_id' => $user->id,
            'program_id' => $program->id,
            'number' => 'OLD',
            'amount' => 120,
            'state' => 'approved',
        ]);

        $this->actingAs($user)
            ->post(route('plans.payment.store'), ['number' => 'TRX-RENEW'] + $this->transferForm($program, $latam->id))
            ->assertSessionMissing('error');

        $this->assertSame('40.00', (string) Payment::query()->where('number', 'TRX-RENEW')->firstOrFail()->amount);
    }

    public function test_a_plan_without_regions_charges_its_own_price_without_locating_anyone(): void
    {
        Storage::fake('public');
        Http::fake();

        $program = $this->baseProgram();
        $user = $this->freeUser();

        $this->actingAs($user)
            ->post(route('plans.payment.store'), $this->transferForm($program, null))
            ->assertSessionMissing('error');

        $payment = Payment::query()->firstOrFail();

        $this->assertSame('147.00', (string) $payment->amount);
        $this->assertNull($payment->region_label);
        $this->assertNull($payment->country_code);
        // The pending-payment webhook still goes out; no geolocation lookup does.
        Http::assertNotSent(fn ($request) => str_contains($request->url(), self::VISITOR_IP));
    }

    public function test_card_checkout_charges_the_card_price_of_the_visitor_region(): void
    {
        config(['affiliates.datafast.enabled' => true]);
        $this->connectFrom('US');

        $datafast = Mockery::mock(DatafastService::class);
        $datafast->shouldReceive('initiateCheckout')
            ->once()
            ->withArgs(fn (float $amount) => $amount === 230.0)
            ->andReturn('CHECKOUT-1');
        $this->app->instance(DatafastService::class, $datafast);

        $program = $this->programWithRegions();
        $usa = $program->regionPrices->firstWhere('region', 'EE.UU.');
        $user = $this->freeUser();
        $user->forceFill(['address' => 'Av. Siempre Viva 123', 'identification' => '0102030405'])->save();

        $this->actingAs($user)
            ->post(route('plans.card-checkout'), [
                'program_id' => $program->id,
                'region_price_id' => $usa->id,
            ])
            ->assertRedirect();

        $payment = Payment::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertSame('230.00', (string) $payment->amount);
        $this->assertSame('card', $payment->payment_method);
        $this->assertSame('US', $payment->country_code);
    }

    public function test_the_plans_page_shows_the_visitor_price_and_no_region_picker(): void
    {
        $this->connectFrom('US');

        $program = $this->programWithRegions();
        $usa = $program->regionPrices->firstWhere('region', 'EE.UU.');
        $user = $this->freeUser();

        $response = $this->actingAs($user)
            ->get(route('plans.index'));

        $response->assertOk()
            ->assertSee('$200.00')
            ->assertDontSee('$120.00')
            ->assertSee('name="region_price_id" value="'.$usa->id.'"', false)
            ->assertDontSee('<select id="region_price_', false);
    }

    public function test_the_plans_page_shows_the_base_price_when_the_location_is_unknown(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $this->programWithRegions();
        $user = $this->freeUser();

        $this->actingAs($user)
            ->get(route('plans.index'))
            ->assertOk()
            ->assertSee('$147.00');
    }

    public function test_admin_can_save_and_replace_the_region_table_of_a_plan(): void
    {
        $admin = $this->admin();
        $program = $this->baseProgram();

        $this->actingAs($admin)->put(route('plans.programs.update', $program), $this->planForm([
            ['region' => 'EE.UU.', 'countries' => 'us, ca', 'first_payment_cost' => '200', 'card_first_payment_cost' => '230', 'renewal_cost' => '70', 'card_renewal_cost' => '80'],
            ['region' => 'LATAM', 'countries' => 'Latam', 'first_payment_cost' => '120', 'card_first_payment_cost' => '', 'renewal_cost' => '40', 'card_renewal_cost' => ''],
            ['region' => '', 'countries' => '', 'first_payment_cost' => '', 'card_first_payment_cost' => '', 'renewal_cost' => '', 'card_renewal_cost' => ''],
        ]))->assertSessionHasNoErrors();

        $program->refresh()->load('regionPrices');

        // The trailing blank row of the editor is dropped, not saved.
        $this->assertCount(2, $program->regionPrices);
        $this->assertTrue($program->chargesByRegion());
        $this->assertSame(['US', 'CA'], $program->regionPrices->firstWhere('region', 'EE.UU.')->countries);

        $latam = $program->regionPrices->firstWhere('region', 'LATAM');
        $this->assertSame(['LATAM'], $latam->countries);
        $this->assertTrue($latam->covers('EC'));
        $this->assertSame('120.00', (string) $latam->first_payment_cost);
        $this->assertNull($latam->card_first_payment_cost);

        // Saving again replaces the whole table.
        $this->actingAs($admin)->put(route('plans.programs.update', $program), $this->planForm([
            ['region' => 'Europa', 'countries' => 'EUROPA', 'first_payment_cost' => '180', 'renewal_cost' => '60'],
        ]))->assertSessionHasNoErrors();

        $program->refresh()->load('regionPrices');

        $this->assertCount(1, $program->regionPrices);
        $this->assertSame('Europa', $program->regionPrices->first()->region);
    }

    public function test_a_region_needs_countries_the_system_recognises(): void
    {
        $admin = $this->admin();
        $program = $this->baseProgram();

        $this->actingAs($admin)->put(route('plans.programs.update', $program), $this->planForm([
            ['region' => 'EE.UU.', 'countries' => 'USA', 'first_payment_cost' => '200', 'renewal_cost' => '70'],
            ['region' => 'Sin países', 'countries' => '', 'first_payment_cost' => '100', 'renewal_cost' => '30'],
        ]))->assertSessionHasErrors(['region_prices.0.countries', 'region_prices.1.countries']);

        $this->assertCount(0, $program->refresh()->regionPrices);
    }

    public function test_the_editor_keeps_what_the_admin_typed_and_says_what_is_wrong(): void
    {
        $admin = $this->admin();
        $program = $this->programWithRegions();

        $this->actingAs($admin)
            ->from(route('plans.index'))
            ->followingRedirects()
            ->put(route('plans.programs.update', $program), $this->planForm([
                ['region' => 'Brasil y Chile', 'countries' => 'BR, CHL', 'first_payment_cost' => '130', 'renewal_cost' => '45'],
            ]) + ['_region_form' => 'program-'.$program->id])
            ->assertOk()
            ->assertSee('Brasil y Chile', false)
            ->assertSee('Not recognised: CHL', false);
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

    /** Every geolocation service answers that the visitor is in $country. */
    private function connectFrom(string $country): void
    {
        Http::fake(['*' => Http::response(['ip' => self::VISITOR_IP, 'country' => $country])]);
    }

    /** @return array<string, mixed> */
    private function transferForm(Program $program, ?int $shownRegionId): array
    {
        return [
            'program_id' => $program->id,
            'region_price_id' => $shownRegionId,
            'bank_id' => $this->bank()->id,
            'number' => 'TRX-'.uniqid(),
            'photo' => UploadedFile::fake()->image('receipt.jpg'),
        ];
    }

    /**
     * @param  list<array<string, string>>  $regionPrices
     * @return array<string, mixed>
     */
    private function planForm(array $regionPrices): array
    {
        return [
            'name' => 'Plan editado',
            'first_payment_cost' => 147,
            'renewal_cost' => 47,
            'duration_months' => 2,
            'is_active' => '1',
            'region_prices' => $regionPrices,
        ];
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
                'countries' => ['US'],
                'first_payment_cost' => 200,
                'card_first_payment_cost' => 230,
                'renewal_cost' => 70,
                'card_renewal_cost' => 80,
                'sort_order' => 0,
            ],
            [
                'region' => 'LATAM',
                'countries' => ['LATAM'],
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
