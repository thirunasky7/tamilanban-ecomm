<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\CustomerOtpService;
use App\Support\Media;
use App\Support\MobileUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(private CustomerOtpService $otp)
    {
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'string', 'min:10', 'max:15'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        $result = $this->otp->sendOtp($data['mobile']);

        $payload = [
            'message' => $result['message'],
            'mobile' => $result['mobile'],
            'sms_sent' => (bool) ($result['sms_sent'] ?? false),
        ];

        if (! empty($result['dummy_otp'])) {
            $payload['dummy_otp'] = $result['dummy_otp'];
        }

        return response()->json($payload);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'string', 'min:10', 'max:15'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        $mobile = $this->otp->normalizeMobile($data['mobile']);

        $otp = $this->otp->findValidOtp($mobile, $data['otp']);

        if (! $otp) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP.'],
            ]);
        }

        $otp->update(['verified_at' => now()]);

        $user = User::query()->firstOrCreate(
            ['mobile' => $mobile],
            [
                'name' => ($data['name'] ?? null) ?: 'Customer '.substr($mobile, -4),
                'type' => 'customer',
                'is_active' => true,
                'mobile_verified_at' => now(),
            ]
        );

        if (! $user->mobile_verified_at) {
            $user->update(['mobile_verified_at' => now()]);
        }

        if (! empty($data['name']) && blank($user->name)) {
            $user->update(['name' => $data['name']]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json($this->tokenPayload($user, $token));
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $data = $request->validate([
            'fullName' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();
        $user->update([
            'name' => $data['fullName'],
            'email' => $data['email'] ?? $user->email,
            'mobile' => filled($data['phone'] ?? null)
                ? $this->otp->normalizeMobile($data['phone'])
                : $user->mobile,
        ]);

        return response()->json([
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        Media::delete($user->getRawOriginal('avatar') ?? $user->avatar);
        $path = Media::store($data['avatar'], 'avatars');
        $user->update(['avatar' => $path]);

        return response()->json([
            'user' => $this->userPayload($user->fresh()),
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $data = $request->validate([
            'confirmation' => ['required', 'in:DELETE'],
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            $user->tokens()->delete();
            Media::delete($user->getRawOriginal('avatar') ?? null);
            $user->delete();
        });

        return response()->json(['message' => 'Account deleted.']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    protected function tokenPayload(User $user, string $token): array
    {
        return [
            'id' => (string) $user->id,
            'email' => (string) ($user->email ?? ''),
            'fullName' => (string) $user->name,
            'phone' => (string) ($user->mobile ?? ''),
            'token' => $token,
            'avatarUrl' => MobileUrl::absolute(Media::url($user->avatar)),
        ];
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => (string) $user->id,
            'fullName' => (string) $user->name,
            'email' => (string) ($user->email ?? ''),
            'phone' => (string) ($user->mobile ?? ''),
            'avatarUrl' => MobileUrl::absolute(Media::url($user->avatar)),
            'createdAt' => optional($user->created_at)?->toIso8601String(),
        ];
    }
}
