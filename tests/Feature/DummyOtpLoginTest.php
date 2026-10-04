<?php

namespace Tests\Feature;

use App\Models\OtpCode;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DummyOtpLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config([
            'services.sms.enabled' => false,
            'services.sms.dummy_otp' => '123456',
            'services.sms.dummy_otp_mobiles' => '',
        ]);
    }

    private function enableSmsGateway(): void
    {
        Setting::setValue('sms_enabled', '1', 'sms');
        Setting::setValue('sms_api_key', 'test-key', 'sms');
        Setting::setValue('sms_api_secret', 'test-secret', 'sms');
    }

    public function test_api_issues_and_accepts_the_fixed_otp_for_an_allowlisted_number(): void
    {
        config(['services.sms.dummy_otp_mobiles' => '9999999999']);

        $this->postJson('/api/v1/auth/otp/send', [
            'mobile' => '9999999999',
            'name' => 'Koru QA',
        ])
            ->assertOk()
            ->assertJsonPath('mobile', '9999999999')
            ->assertJsonPath('dummy_otp', '123456');

        $this->assertSame('123456', OtpCode::where('mobile', '9999999999')->value('code'));

        // No "name" here on purpose: the Flutter app omits it on first login.
        $this->postJson('/api/v1/auth/otp/verify', [
            'mobile' => '9999999999',
            'otp' => '123456',
        ])
            ->assertOk()
            ->assertJsonStructure(['token', 'id', 'fullName', 'phone', 'avatarUrl']);

        $this->assertDatabaseHas('users', [
            'mobile' => '9999999999',
            'type' => 'customer',
        ]);
    }

    public function test_send_fails_loudly_when_no_gateway_and_the_number_is_not_allowlisted(): void
    {
        config(['services.sms.dummy_otp_mobiles' => '9999999999']);

        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('mobile');

        $this->assertDatabaseCount('otp_codes', 0);
    }

    public function test_send_fails_loudly_when_no_gateway_and_no_dummy_otp_is_configured(): void
    {
        config(['services.sms.dummy_otp' => null]);

        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9999999999'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('mobile');

        $this->assertDatabaseCount('otp_codes', 0);
    }

    public function test_real_gateway_path_issues_a_random_code_that_is_not_the_fixed_otp(): void
    {
        Http::fake();

        $this->enableSmsGateway();
        config(['services.sms.dummy_otp_mobiles' => '9999999999']);

        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9876543210'])
            ->assertOk()
            ->assertJsonPath('sms_sent', true)
            ->assertJsonMissingPath('dummy_otp');

        $code = OtpCode::where('mobile', '9876543210')->value('code');

        $this->assertNotSame('123456', $code);

        $this->postJson('/api/v1/auth/otp/verify', [
            'mobile' => '9876543210',
            'otp' => '123456',
        ])->assertStatus(422)->assertJsonValidationErrors('otp');

        $this->postJson('/api/v1/auth/otp/verify', [
            'mobile' => '9876543210',
            'otp' => $code,
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_api_rejects_a_wrong_otp_even_in_dummy_mode(): void
    {
        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9999999999'])->assertOk();

        $this->postJson('/api/v1/auth/otp/verify', [
            'mobile' => '9999999999',
            'otp' => '654321',
        ])->assertStatus(422)->assertJsonValidationErrors('otp');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fixed_otp_is_never_issued_in_production(): void
    {
        app()['env'] = 'production';

        config(['services.sms.dummy_otp_mobiles' => '9999999999']);

        $this->postJson('/api/v1/auth/otp/send', ['mobile' => '9999999999'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('mobile');

        $this->assertDatabaseCount('otp_codes', 0);
    }
}