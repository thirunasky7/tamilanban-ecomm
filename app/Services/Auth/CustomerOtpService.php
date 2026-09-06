<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class CustomerOtpService
{
    public const DUMMY_OTP = '123456';

    public function __construct(private SmsGatewayService $sms)
    {
    }

    public function sendOtp(string $mobile): array
    {
        $mobile = $this->normalizeMobile($mobile);

        OtpCode::query()->where('mobile', $mobile)->delete();

        $useDummy = $this->sms->useDummyOtp() || ! $this->sms->enabled();
        $code = $useDummy ? self::DUMMY_OTP : (string) random_int(100000, 999999);

        $otp = OtpCode::query()->create([
            'mobile' => $mobile,
            'code' => $code,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $smsSent = false;
        try {
            if ($this->sms->enabled()) {
                $template = $this->sms->template(
                    'otp',
                    'Your ShopEase OTP is {otp}. Valid for 10 minutes.'
                );
                $body = $this->sms->render($template, ['otp' => $code]);
                $smsSent = $this->sms->send($mobile, $body);
            }
        } catch (Throwable $exception) {
            Log::error('OTP SMS failed without blocking login.', [
                'mobile' => $mobile,
                'error' => $exception->getMessage(),
            ]);
            $smsSent = false;
        }

        $payload = [
            'mobile' => $mobile,
            'expires_at' => $otp->expires_at,
            'message' => $smsSent
                ? 'OTP sent to your mobile number.'
                : ($useDummy
                    ? 'OTP ready. Use 123456 (dummy OTP / SMS not enabled).'
                    : 'OTP generated. If you did not receive SMS, try again shortly.'),
            'sms_sent' => $smsSent,
        ];

        if ($useDummy) {
            $payload['dummy_otp'] = self::DUMMY_OTP;
        }

        return $payload;
    }

    public function verifyAndLogin(string $mobile, string $code, ?string $name = null): User
    {
        $mobile = $this->normalizeMobile($mobile);

        $otp = OtpCode::query()
            ->where('mobile', $mobile)
            ->latest()
            ->first();

        if (! $otp || ! $otp->isValid($code)) {
            abort(422, 'Invalid or expired OTP.');
        }

        $otp->update(['verified_at' => now()]);

        $user = User::query()->firstOrCreate(
            ['mobile' => $mobile],
            [
                'name' => $name ?: 'Customer '.substr($mobile, -4),
                'type' => 'customer',
                'is_active' => true,
                'mobile_verified_at' => now(),
            ]
        );

        if (! $user->mobile_verified_at) {
            $user->update(['mobile_verified_at' => now()]);
        }

        if ($name && blank($user->name)) {
            $user->update(['name' => $name]);
        }

        Auth::guard('web')->login($user, true);

        return $user;
    }

    public function normalizeMobile(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? '';

        if (strlen($digits) > 10 && str_starts_with($digits, '91')) {
            $digits = substr($digits, -10);
        }

        return $digits;
    }
}
