<?php

namespace Tests\Feature\Api\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TelegramRegistrationApiTest extends TestCase
{
    use RefreshDatabase;

    private const TOKEN = 'test-internal-token';

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('affiliates.internal_api_token', self::TOKEN);
        config()->set('affiliates.telegram.bot_token', 'fake-bot-token');
    }

    public function test_group_chat_messages_are_ignored_without_sending_any_message(): void
    {
        Http::fake();

        $user = User::factory()->create(['telegram_chat_id' => null]);

        $response = $this->withHeaders(['X-Internal-Token' => self::TOKEN])
            ->postJson('/api/admin/telegram/register-chat-id', [
                'chat_id' => -1003914721960,
                'code'    => $user->telegram_code,
            ]);

        $response->assertOk();
        $response->assertJson(['registered' => false]);

        $this->assertNull($user->fresh()->telegram_chat_id);
        Http::assertNothingSent();
    }

    public function test_ordinary_chat_text_is_ignored_without_sending_any_message(): void
    {
        Http::fake();

        $response = $this->withHeaders(['X-Internal-Token' => self::TOKEN])
            ->postJson('/api/admin/telegram/register-chat-id', [
                'chat_id' => 123456789,
                'code'    => 'hola, buenos dias!',
            ]);

        $response->assertOk();
        $response->assertJson(['registered' => false]);
        Http::assertNothingSent();
    }

    public function test_missing_text_updates_are_ignored_not_rejected(): void
    {
        Http::fake();

        $response = $this->withHeaders(['X-Internal-Token' => self::TOKEN])
            ->postJson('/api/admin/telegram/register-chat-id', [
                'chat_id' => 123456789,
            ]);

        $response->assertOk();
        $response->assertJson(['registered' => false]);
        Http::assertNothingSent();
    }

    public function test_valid_code_in_private_chat_still_registers_the_chat_id(): void
    {
        Http::fake();

        $user = User::factory()->create(['telegram_chat_id' => null]);

        $response = $this->withHeaders(['X-Internal-Token' => self::TOKEN])
            ->postJson('/api/admin/telegram/register-chat-id', [
                'chat_id' => 123456789,
                'code'    => $user->telegram_code,
            ]);

        $response->assertCreated();
        $response->assertJson(['registered' => true]);

        $this->assertSame(123456789, $user->fresh()->telegram_chat_id);
        Http::assertSentCount(1);
    }

    public function test_well_formed_but_unknown_code_still_returns_400(): void
    {
        Http::fake();

        $response = $this->withHeaders(['X-Internal-Token' => self::TOKEN])
            ->postJson('/api/admin/telegram/register-chat-id', [
                'chat_id' => 123456789,
                'code'    => 'ZZZZZZZZZZ',
            ]);

        $response->assertStatus(400);
        Http::assertNothingSent();
    }
}
