<?php

namespace Tests\Unit;

use App\Services\Sms\SmsGatewayService;
use Tests\TestCase;

class DummyOtpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.sms.dummy_otp' => '123456',
            'services.sms.dummy_otp_mobiles' => '',
            'services.sms.allow_dummy_in_production' => false,
        ]);
    }

    public function test_dummy_otp_is_disabled_in_production(): void
    {
        app()['env'] = 'production';

        $service = app(SmsGatewayService::class);

        $this->assertFalse($service->dummyOtpEnabled());
        $this->assertFalse($service->dummyOtpAllowedFor('9999999999'));
    }

    public function test_dummy_otp_stays_disabled_in_production_without_an_allowlist(): void
    {
        app()['env'] = 'production';
        config(['services.sms.allow_dummy_in_production' => true]);

        $this->assertFalse(app(SmsGatewayService::class)->dummyOtpEnabled());
    }

    public function test_allowlisted_number_can_use_the_fixed_otp_in_production(): void
    {
        app()['env'] = 'production';
        config([
            'services.sms.allow_dummy_in_production' => true,
            'services.sms.dummy_otp_mobiles' => '6381673242',
        ]);

        $service = app(SmsGatewayService::class);

        $this->assertTrue($service->dummyOtpEnabled());
        $this->assertTrue($service->dummyOtpAllowedFor('6381673242'));
        $this->assertTrue($service->dummyOtpAllowedFor('916381673242'));
        $this->assertFalse($service->dummyOtpAllowedFor('9876543210'));
    }

    public function test_dummy_otp_is_enabled_outside_production(): void
    {
        $service = app(SmsGatewayService::class);

        $this->assertTrue($service->dummyOtpEnabled());
        $this->assertTrue($service->dummyOtpAllowedFor('9999999999'));
        $this->assertSame('123456', $service->dummyOtpCode());
    }

    public function test_dummy_otp_is_off_when_not_configured(): void
    {
        config(['services.sms.dummy_otp' => null]);

        $service = app(SmsGatewayService::class);

        $this->assertNull($service->dummyOtpCode());
        $this->assertFalse($service->dummyOtpAllowedFor('9999999999'));
    }

    public function test_allowlist_limits_which_numbers_may_use_the_fixed_otp(): void
    {
        config(['services.sms.dummy_otp_mobiles' => '9999999999, 9123456780']);

        $service = app(SmsGatewayService::class);

        $this->assertTrue($service->dummyOtpAllowedFor('9999999999'));
        $this->assertTrue($service->dummyOtpAllowedFor('+919999999999'));
        $this->assertTrue($service->dummyOtpAllowedFor('9123456780'));
        $this->assertFalse($service->dummyOtpAllowedFor('9876543210'));
        $this->assertFalse($service->dummyOtpAllowedFor(null));
    }
}