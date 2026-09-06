<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderTimeline;
use App\Models\PaymentGateway;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\Error as RazorpayError;
use RuntimeException;

class RazorpayService
{
    protected ?PaymentGateway $gateway = null;

    public function gateway(): ?PaymentGateway
    {
        if ($this->gateway !== null) {
            return $this->gateway;
        }

        $this->gateway = PaymentGateway::query()
            ->where('code', 'razorpay')
            ->where('is_enabled', true)
            ->first();

        return $this->gateway;
    }

    public function settingsGateway(): ?PaymentGateway
    {
        return PaymentGateway::query()->where('code', 'razorpay')->first();
    }

    public function hasCredentials(): bool
    {
        return $this->resolveKeyPair() !== null;
    }

    public function isConfigured(): bool
    {
        return $this->gateway() !== null && $this->hasCredentials();
    }

    public function keyId(): ?string
    {
        return $this->resolveKeyPair()['key_id'] ?? null;
    }

    public function isMethodEnabled(string $method): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        return $this->gateway()->isMethodOn($method);
    }

    /** @return list<string> */
    public function enabledMethods(): array
    {
        if (! $this->isConfigured()) {
            return [];
        }

        $methods = [];
        foreach (['upi', 'netbanking', 'card'] as $method) {
            if ($this->isMethodEnabled($method)) {
                $methods[] = $method;
            }
        }

        return $methods;
    }

    /** @return list<string> */
    public function availableCheckoutMethods(): array
    {
        $methods = [];

        if ($this->isCodEnabled()) {
            $methods[] = 'cod';
        }

        return array_merge($methods, $this->enabledMethods());
    }

    public function isOnlineMethod(string $method): bool
    {
        return in_array($method, ['upi', 'netbanking', 'card'], true);
    }

    public function isCodEnabled(): bool
    {
        return (bool) PaymentGateway::query()
            ->where('code', 'cod')
            ->where('is_enabled', true)
            ->exists();
    }

    public function createOrder(Order $order): string
    {
        $api = $this->api();
        $amount = (int) round($order->total * 100);

        if ($amount < 100) {
            throw new RuntimeException('Order total must be at least ₹1 for online payment.');
        }

        try {
            $razorpayOrder = $api->order->create([
                'receipt' => $order->order_number,
                'amount' => $amount,
                'currency' => 'INR',
                'notes' => [
                    'order_number' => $order->order_number,
                ],
            ]);
        } catch (RazorpayError $exception) {
            throw new RuntimeException($this->friendlyError($exception), 0, $exception);
        }

        $order->update([
            'razorpay_order_id' => $razorpayOrder['id'],
        ]);

        return $razorpayOrder['id'];
    }

    public function verifyPayment(string $razorpayOrderId, string $paymentId, string $signature): bool
    {
        $api = $this->api();

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    public function markOrderPaid(Order $order, string $paymentId): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'razorpay_payment_id' => $paymentId,
            'status' => 'confirmed',
        ]);

        OrderTimeline::query()->create([
            'order_id' => $order->id,
            'status' => 'confirmed',
            'title' => 'Payment received',
            'note' => 'Payment completed via Razorpay.',
        ]);
    }

    public function testCredentials(string $keyId, string $keySecret): bool
    {
        $keyId = trim($keyId);
        $keySecret = trim($keySecret);

        if ($keyId === '' || $keySecret === '') {
            return false;
        }

        try {
            $api = new Api($keyId, $keySecret);
            $api->order->create([
                'receipt' => 'credential-test-'.time(),
                'amount' => 100,
                'currency' => 'INR',
            ]);

            return true;
        } catch (RazorpayError) {
            return false;
        }
    }

    public function checkoutConfig(string $method): array
    {
        $blocks = match ($method) {
            'upi' => [
                'upi' => [
                    'name' => 'Pay via UPI',
                    'instruments' => [['method' => 'upi']],
                ],
            ],
            'card' => [
                'card' => [
                    'name' => 'Pay with Card',
                    'instruments' => [['method' => 'card']],
                ],
            ],
            default => [
                'banks' => [
                    'name' => 'Pay via Net Banking',
                    'instruments' => [['method' => 'netbanking']],
                ],
            ],
        };

        $sequenceKey = array_key_first($blocks);

        return [
            'display' => [
                'blocks' => $blocks,
                'sequence' => ['block.'.$sequenceKey],
                'preferences' => [
                    'show_default_blocks' => false,
                ],
            ],
        ];
    }

    /** Payment payload for mobile / API clients. */
    public function mobileCheckoutPayload(Order $order): array
    {
        $needsPayment = $order->payment_status === 'pending'
            && $this->isOnlineMethod((string) $order->payment_method)
            && filled($order->razorpay_order_id);

        if (! $needsPayment) {
            return [
                'needsPayment' => false,
                'razorpayOrderId' => $order->razorpay_order_id,
                'razorpayKeyId' => null,
                'amountPaise' => (int) round($order->total * 100),
                'currency' => 'INR',
                'checkoutConfig' => null,
            ];
        }

        return [
            'needsPayment' => true,
            'razorpayOrderId' => $order->razorpay_order_id,
            'razorpayKeyId' => $this->keyId(),
            'amountPaise' => (int) round($order->total * 100),
            'currency' => 'INR',
            'checkoutConfig' => $this->checkoutConfig((string) $order->payment_method),
        ];
    }

    /** @return array{key_id: string, key_secret: string}|null */
    protected function resolveKeyPair(): ?array
    {
        $gateway = $this->gateway() ?? $this->settingsGateway();
        $keyId = trim((string) ($gateway?->credential('key_id') ?? ''));
        $keySecret = trim((string) ($gateway?->credential('key_secret') ?? ''));

        if ($keyId === '') {
            $keyId = trim((string) config('services.razorpay.key_id', ''));
        }

        if ($keySecret === '') {
            $keySecret = trim((string) config('services.razorpay.key_secret', ''));
        }

        if ($keyId === '' || $keySecret === '') {
            return null;
        }

        return [
            'key_id' => $keyId,
            'key_secret' => $keySecret,
        ];
    }

    protected function api(): Api
    {
        $keys = $this->resolveKeyPair();

        if (! $keys) {
            throw new RuntimeException('Razorpay is not configured. Add API keys in Admin → Payments.');
        }

        return new Api($keys['key_id'], $keys['key_secret']);
    }

    protected function friendlyError(RazorpayError $exception): string
    {
        $message = strtolower($exception->getMessage());

        if (str_contains($message, 'authentication failed')) {
            return 'Razorpay authentication failed. In Admin → Payments, re-enter the Key ID and Key Secret as a matching pair from the Razorpay dashboard (Settings → API Keys).';
        }

        return 'Razorpay error: '.$exception->getMessage();
    }
}
