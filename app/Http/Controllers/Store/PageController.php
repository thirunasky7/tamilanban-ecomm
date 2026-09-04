<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function privacy(): View
    {
        return view('store.pages.privacy');
    }

    public function terms(): View
    {
        return view('store.pages.terms');
    }

    public function refund(): View
    {
        return view('store.pages.refund');
    }

    public function contact(): View
    {
        return view('store.pages.contact', [
            'supportEmail' => Setting::getValue('support_email', 'support@shopease.test'),
            'supportPhone' => Setting::getValue('support_phone', '+91 98765 43210'),
        ]);
    }

    public function contactSubmit(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'subject' => ['required', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        return back()->with('success', 'Thank you for contacting us. We will respond within 24–48 hours.');
    }
}
