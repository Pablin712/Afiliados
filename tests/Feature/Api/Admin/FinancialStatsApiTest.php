<?php

namespace Tests\Feature\Api\Admin;

use App\Models\DailyFinancialStat;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialStatsApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $token = 'test-internal-token';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('affiliates.internal_api_token', $this->token);
    }

    public function test_it_rejects_requests_without_internal_token(): void
    {
        $response = $this->postJson('/api/admin/financial-stats/register-today');

        $response->assertStatus(401);
    }

    public function test_it_registers_today_stats_with_internal_token(): void
    {
        $response = $this
            ->withHeaders(['X-Internal-Token' => $this->token])
            ->postJson('/api/admin/financial-stats/register-today');

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'meta' => ['requested_date'],
            'data',
        ]);

        $this->assertDatabaseCount('daily_financial_stats', 1);
    }

    public function test_it_registers_range_stats(): void
    {
        $response = $this
            ->withHeaders(['X-Internal-Token' => $this->token])
            ->postJson('/api/admin/financial-stats/register-range', [
                'from' => '2026-03-10',
                'to' => '2026-03-12',
            ]);

        $response->assertOk();
        $response->assertJsonPath('meta.days', 3);
        $response->assertJsonPath('meta.registered_rows', 3);

        $this->assertDatabaseCount('daily_financial_stats', 3);
    }

    public function test_it_returns_stats_by_date(): void
    {
        $row = DailyFinancialStat::query()->create([
            'stat_date' => '2026-03-20',
            'incomes_total' => 120.50,
            'expenses_total' => 20.10,
            'net_profit' => 100.40,
            'new_users_count' => 4,
            'new_customers_count' => 2,
            'approved_payments_count' => 3,
            'pending_profits_total' => 10,
            'profits_paid_total' => 5,
        ]);

        $response = $this
            ->withHeaders(['X-Internal-Token' => $this->token])
            ->getJson('/api/admin/financial-stats/2026-03-20');

        $response->assertOk();
        $response->assertJsonPath('data.id', $row->id);
        $response->assertJsonPath('meta.requested_date', '2026-03-20');
    }
}
