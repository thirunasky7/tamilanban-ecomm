<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\Sms\SmsGatewayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsSettingsController extends Controller
{
    public function __construct(private SmsGatewayService $sms)
    {
    }

    public function index(): View
    {
        return view('admin.sms.index', [
            'sms_enabled' => filter_var(Setting::getValue('sms_enabled', '0'), FILTER_VALIDATE_BOOLEAN),
            'sms_use_dummy_otp' => filter_var(Setting::getValue('sms_use_dummy_otp', '1'), FILTER_VALIDATE_BOOLEAN),
            'sms_base_url' => Setting::getValue('sms_base_url', 'https://sms.zennexs.com/api/v1'),
            'sms_api_key' => Setting::getValue('sms_api_key', ''),
            'sms_api_secret_set' => filled(Setting::getValue('sms_api_secret', '')),
            'sms_country_code' => Setting::getValue('sms_country_code', '91'),
            'tpl_otp' => Setting::getValue('sms_tpl_otp', 'Your ShopEase OTP is {otp}. Valid for 10 minutes.'),
            'tpl_order_confirmed' => Setting::getValue(
                'sms_tpl_order_confirmed',
                'Order #{order} confirmed. Thank you for your order. Total: Rs.{total}.'
            ),
            'tpl_order_paid' => Setting::getValue(
                'sms_tpl_order_paid',
                'Payment received for order #{order}. Total: Rs.{total}. Thank you!'
            ),
            'tpl_order_shipped' => Setting::getValue(
                'sms_tpl_order_shipped',
                'Order #{order} has been shipped. Track it in ShopEase.'
            ),
            'tpl_order_delivered' => Setting::getValue(
                'sms_tpl_order_delivered',
                'Order #{order} delivered. Thank you for shopping with ShopEase!'
            ),
            'gateway_ready' => $this->sms->enabled(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'sms_enabled' => ['nullable', 'boolean'],
            'sms_use_dummy_otp' => ['nullable', 'boolean'],
            'sms_base_url' => ['required', 'url', 'max:255'],
            'sms_api_key' => ['nullable', 'string', 'max:255'],
            'sms_api_secret' => ['nullable', 'string', 'max:255'],
            'sms_country_code' => ['required', 'string', 'max:5'],
            'tpl_otp' => ['required', 'string', 'max:500'],
            'tpl_order_confirmed' => ['required', 'string', 'max:500'],
            'tpl_order_paid' => ['required', 'string', 'max:500'],
            'tpl_order_shipped' => ['required', 'string', 'max:500'],
            'tpl_order_delivered' => ['required', 'string', 'max:500'],
        ]);

        Setting::setValue('sms_enabled', $request->boolean('sms_enabled') ? '1' : '0', 'sms', 'boolean');
        Setting::setValue('sms_use_dummy_otp', $request->boolean('sms_use_dummy_otp') ? '1' : '0', 'sms', 'boolean');
        Setting::setValue('sms_base_url', rtrim($data['sms_base_url'], '/'), 'sms');
        Setting::setValue('sms_country_code', preg_replace('/\D+/', '', $data['sms_country_code']) ?: '91', 'sms');

        if (filled($data['sms_api_key'] ?? null)) {
            Setting::setValue('sms_api_key', trim($data['sms_api_key']), 'sms');
        }

        if (filled($data['sms_api_secret'] ?? null)) {
            Setting::setValue('sms_api_secret', trim($data['sms_api_secret']), 'sms');
        }

        Setting::setValue('sms_tpl_otp', $data['tpl_otp'], 'sms');
        Setting::setValue('sms_tpl_order_confirmed', $data['tpl_order_confirmed'], 'sms');
        Setting::setValue('sms_tpl_order_paid', $data['tpl_order_paid'], 'sms');
        Setting::setValue('sms_tpl_order_shipped', $data['tpl_order_shipped'], 'sms');
        Setting::setValue('sms_tpl_order_delivered', $data['tpl_order_delivered'], 'sms');

        return back()->with('success', 'SMS settings saved.');
    }

    public function test(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'test_mobile' => ['required', 'string', 'min:10', 'max:15'],
        ]);

        $sent = $this->sms->send(
            $data['test_mobile'],
            'ShopEase SMS test message. Gateway is working.'
        );

        return back()->with(
            $sent ? 'success' : 'error',
            $sent
                ? 'Test SMS sent successfully.'
                : 'Test SMS failed. Check credentials, enable SMS, and review laravel.log.'
        );
    }
}
