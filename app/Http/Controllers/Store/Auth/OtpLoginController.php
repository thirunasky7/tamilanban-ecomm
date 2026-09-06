<?php

namespace App\Http\Controllers\Store\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\CustomerOtpService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OtpLoginController extends Controller
{
    public function __construct(private CustomerOtpService $otp)
    {
    }

    public function show(): View
    {
        return view('store.auth.login');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'string', 'min:10', 'max:15'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        $result = $this->otp->sendOtp($data['mobile']);

        session([
            'otp.mobile' => $result['mobile'],
            'otp.name' => $data['name'] ?? null,
        ]);

        $message = $result['message'];
        if (! empty($result['dummy_otp'])) {
            $message .= ' Demo OTP: '.$result['dummy_otp'];
        }

        return redirect()->route('login.verify')
            ->with('success', $message);
    }

    public function showVerify(): View|RedirectResponse
    {
        if (! session('otp.mobile')) {
            return redirect()->route('login');
        }

        return view('store.auth.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'size:6'],
        ]);

        $mobile = session('otp.mobile');
        if (! $mobile) {
            return redirect()->route('login');
        }

        try {
            $this->otp->verifyAndLogin($mobile, $data['code'], session('otp.name'));
        } catch (\Throwable $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        }

        session()->forget(['otp.mobile', 'otp.name']);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
