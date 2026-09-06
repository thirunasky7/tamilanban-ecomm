<?php

namespace App\Services\Sms;

use App\Models\Order;
use Throwable;

class OrderSmsNotifier
{
    public function __construct(private SmsGatewayService $sms)
    {
    }

    public function orderConfirmed(Order $order): void
    {
        $this->safeSend($order, function () use ($order) {
            $template = $this->sms->template(
                'order_confirmed',
                'Order #{order} confirmed. Thank you for your order. Total: Rs.{total}. Delivery soon.'
            );

            return $this->sms->render($template, $this->vars($order));
        });
    }

    public function paymentReceived(Order $order): void
    {
        $this->safeSend($order, function () use ($order) {
            $template = $this->sms->template(
                'order_paid',
                'Payment received for order #{order}. Total: Rs.{total}. Thank you!'
            );

            return $this->sms->render($template, $this->vars($order));
        });
    }

    public function statusUpdated(Order $order): void
    {
        $status = (string) $order->status;

        $defaults = [
            'shipped' => 'Order #{order} has been shipped. Track it in the ShopEase app.',
            'delivered' => 'Order #{order} delivered. Thank you for shopping with ShopEase!',
            'cancelled' => 'Order #{order} was cancelled. Contact support if you need help.',
            'processing' => 'Order #{order} is being processed.',
            'confirmed' => 'Order #{order} confirmed. Total: Rs.{total}.',
        ];

        if (! isset($defaults[$status])) {
            return;
        }

        $this->safeSend($order, function () use ($order, $status, $defaults) {
            $template = $this->sms->template('order_'.$status, $defaults[$status]);

            return $this->sms->render($template, $this->vars($order));
        });
    }

    protected function vars(Order $order): array
    {
        return [
            'order' => $order->order_number,
            'total' => number_format((float) $order->total, 0, '.', ''),
            'status' => (string) $order->status,
            'name' => (string) (($order->shipping_address['name'] ?? null) ?: $order->guest_name ?: 'Customer'),
        ];
    }

    protected function mobileFor(Order $order): ?string
    {
        $mobile = $order->shipping_address['mobile']
            ?? $order->guest_mobile
            ?? optional($order->user)->mobile;

        return filled($mobile) ? (string) $mobile : null;
    }

    protected function safeSend(Order $order, callable $bodyBuilder): void
    {
        try {
            $mobile = $this->mobileFor($order);
            if (! $mobile) {
                return;
            }

            $body = $bodyBuilder();
            if (! is_string($body) || trim($body) === '') {
                return;
            }

            $this->sms->send($mobile, $body);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
