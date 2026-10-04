<?php

namespace App\Services\Sms;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SmsGatewayService
{
    public function enabled(): bool
    {
        return filter_var(Setting::getValue('sms_enabled', '0'), FILTER_VALIDATE_BOOLEAN)
            && filled($this->apiKey())
            && filled($this->apiSecret());
    }

    public function apiKey(): string
    {
        return trim((string) Setting::getValue('sms_api_key', config('services.sms.api_key', '')));
    }

    public function apiSecret(): string
    {
        return trim((string) Setting::getValue('sms_api_secret', config('services.sms.api_secret', '')));
    }

    public function baseUrl(): string
    {
        return rtrim((string) Setting::getValue(
            'sms_base_url',
            config('services.sms.base_url', 'https://sms.zennexs.com/api/v1')
        ), '/');
    }

    public function countryCode(): string
    {
        return preg_replace('/\D+/', '', (string) Setting::getValue(
            'sms_country_code',
            config('services.sms.country_code', '91')
        )) ?: '91';
    }

    public function useDummyOtp(): bool
    {
        return $this->dummyOtpEnabled();
    }

    /**
     * The fixed OTP configured through SMS_DUMMY_OTP, if any.
     */
    public function dummyOtpCode(): ?string
    {
        $code = trim((string) config('services.sms.dummy_otp', ''));

        return $code === '' ? null : $code;
    }

    /**
     * Whether the fixed OTP is active at all.
     *
     * Hard-blocked in production: a shared OTP must never work on a live
     * store, no matter how the setting or env is configured.
     */
    public function dummyOtpEnabled(): bool
    {
        if (app()->environment('production')) {
            return false;
        }

        if ($this->dummyOtpCode() !== null) {
            return true;
        }

        // Admin toggle from the SMS settings screen. Defaults to off.
        return filter_var(
            Setting::getValue('sms_use_dummy_otp', '0'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * Whether the fixed OTP may be used for this mobile number.
     *
     * With SMS_DUMMY_OTP_MOBILES set, only those numbers may use it, which
     * keeps reviewer/test access narrow instead of opening a global bypass.
     */
    public function dummyOtpAllowedFor(?string $mobile = null): bool
    {
        if (! $this->dummyOtpEnabled()) {
            return false;
        }

        $allowlist = array_filter(array_map(
            'trim',
            explode(',', (string) config('services.sms.dummy_otp_mobiles', ''))
        ));

        if ($allowlist === []) {
            return true;
        }

        if ($mobile === null) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $mobile) ?? '';

        foreach ($allowlist as $allowed) {
            $allowedDigits = preg_replace('/\D+/', '', $allowed) ?? '';

            if ($allowedDigits !== '' && str_ends_with($digits, $allowedDigits)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Send SMS. Never throws — failures are logged and return false.
     */
    public function send(string $mobile, string $body): bool
    {
        if (! $this->enabled()) {
            Log::info('SMS skipped (disabled or missing credentials).', [
                'mobile' => $mobile,
            ]);

            return false;
        }

        $to = $this->toE164($mobile);
        $body = trim($body);

        if ($to === '' || $body === '') {
            return false;
        }

        try {
            $response = Http::timeout(12)
                ->acceptJson()
                ->withHeaders([
                    'X-API-Key' => $this->apiKey(),
                    'X-API-Secret' => $this->apiSecret(),
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl().'/messages/send', [
                    'to' => $to,
                    'body' => $body,
                    'device_id' => null,
                ]);

            if (! $response->successful()) {
                Log::warning('SMS gateway rejected message.', [
                    'mobile' => $to,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            Log::error('SMS gateway request failed.', [
                'mobile' => $to,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    public function toE164(string $mobile): string
    {
        $digits = preg_replace('/\D+/', '', $mobile) ?? '';

        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '0')) {
            $digits = ltrim($digits, '0');
        }

        $cc = $this->countryCode();

        if (strlen($digits) === 10) {
            $digits = $cc.$digits;
        } elseif (strlen($digits) > 10 && ! str_starts_with($digits, $cc)) {
            // keep as-is if already international-looking
        }

        return '+'.$digits;
    }

    public function template(string $key, string $default): string
    {
        $value = Setting::getValue('sms_tpl_'.$key);

        return filled($value) ? (string) $value : $default;
    }

    public function render(string $template, array $vars): string
    {
        $replacements = [];
        foreach ($vars as $key => $value) {
            $replacements['{'.$key.'}'] = (string) $value;
        }

        return strtr($template, $replacements);
    }
}
