<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function privacy(): JsonResponse
    {
        return response()->json([
            'title' => 'Privacy Policy',
            'updatedAt' => now()->toDateString(),
            'sections' => [
                [
                    'title' => 'Information We Collect',
                    'body' => "Account details: name, mobile number, email address, and delivery addresses.\nOrder information: products purchased, payment method, transaction references, and order history.\nTechnical data: IP address, browser type, device information, and cookies used to improve your experience.\nCommunications: messages you send via our contact form, reviews, or customer support.",
                ],
                [
                    'title' => 'How We Use Your Information',
                    'body' => "To process and deliver your orders, including sharing delivery details with logistics partners.\nTo verify your identity via OTP login and prevent fraud.\nTo send order updates, promotional offers (with your consent), and service notifications.\nTo improve our products, website performance, and customer support.\nTo comply with applicable laws and respond to legal requests.",
                ],
                [
                    'title' => 'Payment Information',
                    'body' => 'Online payments are processed securely through Razorpay. We do not store your full card or UPI credentials on our servers. Payment data is handled according to Razorpay\'s privacy and security standards.',
                ],
                [
                    'title' => 'Sharing of Information',
                    'body' => 'We may share limited information with trusted third parties such as payment gateways, courier partners, and technology providers who assist in operating our store. We do not sell your personal data to third parties.',
                ],
                [
                    'title' => 'Data Retention',
                    'body' => 'We retain your information for as long as your account is active or as needed to fulfil orders, resolve disputes, and meet legal obligations.',
                ],
                [
                    'title' => 'Your Rights',
                    'body' => 'You may request access, correction, or deletion of your personal data by contacting us or using Delete Account in the app. You may also opt out of marketing communications at any time.',
                ],
                [
                    'title' => 'Cookies',
                    'body' => 'We use cookies and similar technologies to remember your preferences, keep you logged in, and analyse site traffic. You can control cookies through your browser settings.',
                ],
                [
                    'title' => 'Security',
                    'body' => 'We implement reasonable technical and organisational measures to protect your data. However, no method of transmission over the internet is completely secure.',
                ],
                [
                    'title' => 'Contact Us',
                    'body' => 'For privacy-related questions, contact ShopEase support through the Contact page on our website or Help & Support in the app.',
                ],
            ],
        ]);
    }

    public function terms(): JsonResponse
    {
        return response()->json([
            'title' => 'Terms & Conditions',
            'updatedAt' => now()->toDateString(),
            'sections' => [
                [
                    'title' => 'Acceptance of Terms',
                    'body' => 'By accessing ShopEase website or mobile app, you agree to these Terms & Conditions and our Privacy Policy. If you do not agree, please do not use our services.',
                ],
                [
                    'title' => 'Account & Eligibility',
                    'body' => 'You must provide accurate information when creating an account. You are responsible for keeping your login credentials confidential and for all activity under your account.',
                ],
                [
                    'title' => 'Orders & Pricing',
                    'body' => 'All prices are listed in INR and may change without notice. Placing an order constitutes an offer to purchase. We reserve the right to cancel orders due to pricing errors, stock unavailability, or suspected fraud.',
                ],
                [
                    'title' => 'Payments',
                    'body' => 'We accept Cash on Delivery and online payments via enabled gateways. Orders are confirmed once payment is authorized or COD is accepted.',
                ],
                [
                    'title' => 'Shipping & Delivery',
                    'body' => 'Delivery timelines are estimates. Risk of loss transfers upon delivery to the address provided. Please ensure your contact details are accurate.',
                ],
                [
                    'title' => 'Returns & Refunds',
                    'body' => 'Returns and refunds are governed by our Refund & Cancellation Policy. Some products may be non-returnable for hygiene or perishable reasons.',
                ],
                [
                    'title' => 'Prohibited Use',
                    'body' => 'You agree not to misuse the platform, attempt unauthorized access, scrape data, or engage in fraudulent transactions.',
                ],
                [
                    'title' => 'Limitation of Liability',
                    'body' => 'To the maximum extent permitted by law, ShopEase is not liable for indirect, incidental, or consequential damages arising from use of the service.',
                ],
                [
                    'title' => 'Contact',
                    'body' => 'Questions about these terms can be sent through our Contact page.',
                ],
            ],
        ]);
    }
}
