<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class CustomerOtpService
{
    public const DUMMY_OTP = '123456';

    public function sendOtp(string $mobile): array
    {
        $mobile = $this->normalizeMobile($mobile);

        OtpCode::query()->where('mobile', $mobile)->delete();

        $otp = OtpCode::query()->create([
            'mobile' => $mobile,
            'code' => self::DUMMY_OTP,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);

        return [
            'mobile' => $mobile,
            'expires_at' => $otp->expires_at,
            'message' => 'OTP sent. Use 123456 for now (dummy OTP).',
            'dummy_otp' => self::DUMMY_OTP,
        ];
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
