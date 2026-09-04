<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use App\Services\RazorpayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function __construct(private RazorpayService $razorpay)
    {
    }

    public function index(): View
    {
        $razorpay = PaymentGateway::query()->where('code', 'razorpay')->first();
        $cod = PaymentGateway::query()->where('code', 'cod')->first();

        if ($razorpay) {
            $razorpay->makeVisible('credentials');
        }

        $keyPair = $this->currentKeyPair($razorpay);
        $credentialsValid = $keyPair
            ? $this->razorpay->testCredentials($keyPair['key_id'], $keyPair['key_secret'])
            : false;

        return view('admin.payments.index', [
            'razorpay' => $razorpay,
            'cod' => $cod,
            'hasCredentials' => $keyPair !== null,
            'credentialsValid' => $credentialsValid,
            'activeMethods' => $this->razorpay->enabledMethods(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cod_enabled' => ['nullable', 'boolean'],
            'razorpay_enabled' => ['nullable', 'boolean'],
            'razorpay_sandbox' => ['nullable', 'boolean'],
            'key_id' => ['nullable', 'string', 'max:120', 'regex:/^rzp_(test|live)_[A-Za-z0-9]+$/'],
            'key_secret' => ['nullable', 'string', 'min:10', 'max:255'],
            'upi_enabled' => ['nullable', 'boolean'],
            'netbanking_enabled' => ['nullable', 'boolean'],
            'card_enabled' => ['nullable', 'boolean'],
        ], [
            'key_id.regex' => 'Key ID must look like rzp_test_… or rzp_live_… from the Razorpay dashboard.',
            'key_secret.min' => 'Key Secret looks too short. Copy the full secret from Razorpay (it does not start with rzp_).',
        ]);

        PaymentGateway::query()->updateOrCreate(
            ['code' => 'cod'],
            [
                'name' => 'Cash on Delivery',
                'is_enabled' => $request->boolean('cod_enabled'),
            ]
        );

        $razorpay = PaymentGateway::query()->firstOrCreate(
            ['code' => 'razorpay'],
            ['name' => 'Razorpay', 'is_enabled' => false, 'is_sandbox' => true, 'credentials' => []]
        );

        $razorpay->makeVisible('credentials');
        $credentials = $razorpay->credentials ?? [];

        $nextKeyId = filled($data['key_id'] ?? null)
            ? trim($data['key_id'])
            : trim((string) ($credentials['key_id'] ?? ''));

        $nextKeySecret = filled($data['key_secret'] ?? null)
            ? trim($data['key_secret'])
            : trim((string) ($credentials['key_secret'] ?? ''));

        $keyIdChanged = filled($data['key_id'] ?? null);
        $keySecretChanged = filled($data['key_secret'] ?? null);

        if ($keyIdChanged && ! $keySecretChanged && $nextKeySecret === '') {
            return back()
                ->withInput()
                ->with('error', 'When changing Key ID, you must also enter the matching Key Secret.');
        }

        if ($keySecretChanged && ! $keyIdChanged && $nextKeyId === '') {
            return back()
                ->withInput()
                ->with('error', 'Enter the Key ID that matches this Key Secret.');
        }

        if ($request->boolean('razorpay_enabled')) {
            if ($nextKeyId === '' || $nextKeySecret === '') {
                return back()
                    ->withInput()
                    ->with('error', 'Razorpay requires both Key ID and Key Secret before it can be enabled.');
            }

            if (! $this->razorpay->testCredentials($nextKeyId, $nextKeySecret)) {
                return back()
                    ->withInput()
                    ->with('error', 'Razorpay authentication failed. Use a matching Key ID + Key Secret pair from Razorpay → Settings → API Keys. Test keys start with rzp_test_.');
            }

            $isTestKey = str_starts_with($nextKeyId, 'rzp_test_');
            if ($request->boolean('razorpay_sandbox') && ! $isTestKey) {
                return back()
                    ->withInput()
                    ->with('error', 'Sandbox mode is on but you entered a live Key ID (rzp_live_…). Use test keys or turn off sandbox mode.');
            }

            if (! $request->boolean('razorpay_sandbox') && $isTestKey) {
                return back()
                    ->withInput()
                    ->with('error', 'Live mode is on but you entered a test Key ID (rzp_test_…). Turn on sandbox mode or use live keys.');
            }
        } elseif ($keyIdChanged || $keySecretChanged) {
            if ($nextKeyId !== '' && $nextKeySecret !== '' && ! $this->razorpay->testCredentials($nextKeyId, $nextKeySecret)) {
                return back()
                    ->withInput()
                    ->with('error', 'Razorpay authentication failed. Keys were not saved — copy both Key ID and Key Secret together from the dashboard.');
            }
        }

        if (filled($data['key_id'] ?? null)) {
            $credentials['key_id'] = $nextKeyId;
        }

        if (filled($data['key_secret'] ?? null)) {
            $credentials['key_secret'] = $nextKeySecret;
        }

        $credentials['upi'] = $request->boolean('upi_enabled');
        $credentials['netbanking'] = $request->boolean('netbanking_enabled');
        $credentials['card'] = $request->boolean('card_enabled');

        $razorpay->update([
            'is_enabled' => $request->boolean('razorpay_enabled'),
            'is_sandbox' => $request->boolean('razorpay_sandbox'),
            'credentials' => $credentials,
        ]);

        return back()->with('success', 'Payment settings saved.');
    }

    /** @return array{key_id: string, key_secret: string}|null */
    protected function currentKeyPair(?PaymentGateway $razorpay): ?array
    {
        if (! $razorpay) {
            return null;
        }

        $keyId = trim((string) ($razorpay->credential('key_id') ?? ''));
        $keySecret = trim((string) ($razorpay->credential('key_secret') ?? ''));

        if ($keyId === '' || $keySecret === '') {
            return null;
        }

        return ['key_id' => $keyId, 'key_secret' => $keySecret];
    }
}
