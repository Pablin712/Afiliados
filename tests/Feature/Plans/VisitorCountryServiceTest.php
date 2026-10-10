<?php

namespace Tests\Feature\Plans;

use App\Services\VisitorCountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VisitorCountryServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['affiliates.geo.providers' => [
            'https://first.example/{ip}',
            'https://second.example/country/{ip}',
        ]]);
    }

    public function test_it_falls_back_to_the_next_provider_when_one_fails(): void
    {
        Http::fake([
            'first.example/*' => Http::response('Service Unavailable', 503),
            'second.example/*' => Http::response("EC\n"),
        ]);

        $this->assertSame('EC', $this->detect('190.152.1.1'));
    }

    public function test_it_reads_json_answers_and_ignores_codes_that_are_not_countries(): void
    {
        Http::fake([
            'first.example/*' => Http::response(['country' => 'XX']),
            'second.example/*' => Http::response(['country_code' => 'us']),
        ]);

        $this->assertSame('US', $this->detect('8.8.8.8'));
    }

    public function test_each_address_is_looked_up_once(): void
    {
        Http::fake(fn ($request) => Http::response([
            'country' => str_contains($request->url(), '190.152.1.1') ? 'EC' : 'US',
        ]));

        $this->assertSame('US', $this->detect('8.8.8.8'));
        $this->assertSame('US', $this->detect('8.8.8.8'));

        Http::assertSentCount(1);

        // A VPN means a new address, so it is located on its own.
        $this->assertSame('EC', $this->detect('190.152.1.1'));
        Http::assertSentCount(2);
    }

    public function test_private_addresses_are_not_looked_up(): void
    {
        Http::fake();

        $this->assertNull($this->detect('127.0.0.1'));
        $this->assertNull($this->detect('192.168.1.20'));

        Http::assertNothingSent();
    }

    public function test_the_debug_country_is_ignored_outside_local(): void
    {
        config(['affiliates.geo.debug_country' => 'US']);
        Http::fake();

        // The test suite runs as "testing", not "local".
        $this->assertNull($this->detect('127.0.0.1'));
    }

    private function detect(string $ip): ?string
    {
        $request = Request::create('/plans', 'GET', server: ['REMOTE_ADDR' => $ip]);

        return app(VisitorCountryService::class)->detect($request);
    }
}
