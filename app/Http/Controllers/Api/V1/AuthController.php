<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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

        return response()->json([
            'message' => $result['message'],
            'mobile' => $result['mobile'],
            'dummy_otp' => $result['dummy_otp'] ?? null,
        ]);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'string', 'min:10', 'max:15'],
            'otp' => ['required', 'string', 'size:6'],
            'name' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $user = $this->otp->verifyAndLogin(
                $data['mobile'],
                $data['otp'],
                $data['name'] ?? null,
            );
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            throw ValidationException::withMessages([
                'otp' => [$e->getMessage()],
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'id' => (string) $user->id,
            'email' => (string) ($user->email ?? ''),
            'fullName' => (string) $user->name,
            'phone' => (string) ($user->mobile ?? ''),
            'avatarUrl' => MobileUrl::absolute(Media::url($user->avatar)),
            'token' => $token,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['user' => $this->userPayload($request->user())]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'fullName' => ['sometimes', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        $user->update([
            'name' => $data['fullName'] ?? $user->name,
            'email' => $data['email'] ?? $user->email,
            'mobile' => isset($data['phone'])
                ? $this->otp->normalizeMobile($data['phone'])
                : $user->mobile,
        ]);

        return $this->me($request);
    }

    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:5120'],
        ]);

        $user = $request->user();
        Media::delete($user->getRawOriginal('avatar'));
        $path = Media::store($request->file('avatar'), 'avatars');
        $user->update(['avatar' => $path]);

        return $this->me($request);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:DELETE'],
        ]);

        $user = $request->user();

        if ($user->isAdmin()) {
            throw ValidationException::withMessages([
                'confirmation' => ['Admin accounts cannot be deleted from the app.'],
            ]);
        }

        DB::transaction(function () use ($user) {
            Media::delete($user->getRawOriginal('avatar'));
            $user->tokens()->delete();
            $user->update([
                'name' => 'Deleted User',
                'email' => null,
                'mobile' => 'deleted_'.$user->id.'_'.time(),
                'avatar' => null,
                'is_active' => false,
                'password' => null,
            ]);
        });

        return response()->json(['message' => 'Account deleted successfully']);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out']);
    }

    private function userPayload($user): array
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
