<?php

namespace App\Http\Controllers\Api;

use App\AccessToken;
use App\LoginToken;
use App\Mail\LinkEmail;
use App\RefreshToken;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Mail;
use App\Services\GenerateTokenService;

class UserController
{
    /**
     * @param GenerateTokenService $generateTokenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function createAuthLink(GenerateTokenService $generateTokenService): JsonResponse
    {
        $now = Carbon::now()->timestamp;
        $data = $data ?? request()->all();
        $host = env('HOST');

        $loginTokenString = $generateTokenService->generate();
        $user = User::firstOrCreate($data);
        $loginTokens = LoginToken::where(['user_id' => $user->id, 'revoked' => false])->get();
        if ($loginTokens->count() > 0) {
            foreach ($loginTokens as $loginToken) {
                $loginToken->update(['revoked' => true]);
            }
        }

        LoginToken::create([
            'id' => $loginTokenString,
            'user_id' => $user->id,
            'expires_at' => (new \DateTime())->setTimestamp($now + 900)
        ]);

        $inviteUrl = $host . "/api/login?token=" . $loginTokenString;
        $emailObj = new LinkEmail($inviteUrl);
        \Mail::to($data['email'])->send($emailObj);

        return response()->json(['user' => $user], 200);
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function login(): JsonResponse
    {
        $token = request()->token;
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = LoginToken::find($token);
        $user = $token->user;
        if (!$token || !$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $loginTokens = LoginToken::where(['user_id' => $user->id, 'revoked' => false])->get();
        if ($loginTokens->count() > 0) {
            foreach ($loginTokens as $loginToken) {
                $loginToken->update(['revoked' => true]);
                if (strtotime($loginToken->expires_at) < Carbon::now()->timestamp) {
                    return response()->json(['error' => 'Unauthorized'], 401);
                }
            }
        }

        $accessTokens = AccessToken::where(['user_id' => $user->id, 'revoked' => false])->get();
        if ($accessTokens->count() > 0) {
            foreach ($accessTokens as $accessToken) {
                $accessToken->update(['revoked' => true]);
            }
        }

        $token->update(['revoked' => true]);
        $accessToken = $user->accessToken()->save(new AccessToken([
            'expires_at' => (new \DateTime())->setTimestamp(Carbon::now()->timestamp + 1800)
        ]));

        $refreshTokens = RefreshToken::where(['user_id' => $user->id, 'revoked' => false])->get();
        if ($refreshTokens->count() > 0) {
            foreach ($refreshTokens as $refreshToken) {
                $refreshToken->update(['revoked' => true]);
            }
        }
        $refreshToken = $user->refreshToken()->save(new RefreshToken([
            'expires_at' => (new \DateTime())->setTimestamp(Carbon::now()->timestamp + 86400)
        ]));

        return response()->json([
            'accessToken' => $accessToken->id,
            'refreshToken' => $refreshToken->id
        ], 200);
    }

    /**
     * @param GenerateTokenService $generateTokenService
     * @return JsonResponse
     * @throws \Exception
     */
    public function refreshAccessToken(GenerateTokenService $generateTokenService): JsonResponse
    {
        $refreshToken = request()->refreshToken;
        $refreshToken = RefreshToken::where([
            'id' => $refreshToken,
            'revoked' => false
        ])->first();
        if ($refreshToken == null || !$refreshToken->count() > 0 || strtotime($refreshToken->expires_at) < Carbon::now()->timestamp) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = $refreshToken->user;
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $refreshTokens = RefreshToken::where(['user_id' => $user->id, 'revoked' => false])->get();
        if ($refreshTokens->count() > 0) {
            foreach ($refreshTokens as $refreshToken) {
                $refreshToken->update(['revoked' => true]);
            }
        }

        $accessTokens = AccessToken::where(['user_id' => $user->id, 'revoked' => false])->get();
        if ($accessTokens->count() > 0) {
            foreach ($accessTokens as $accessToken) {
                $accessToken->update(['revoked' => true]);
            }
        }

        $accessToken = $user->accessToken()->save(new AccessToken([
            'id' => $generateTokenService->generate(),
            'expires_at' => (new \DateTime())->setTimestamp(Carbon::now()->timestamp + 1800)
        ]));

        $refreshToken = $user->refreshToken()->save(new RefreshToken([
            'id' => $generateTokenService->generate(),
            'expires_at' => (new \DateTime())->setTimestamp(Carbon::now()->timestamp + 1800)
        ]));

        return response()->json([
            'accessToken' => $accessToken->id,
            'refreshToken' => $refreshToken->id,
        ], 200);
    }

    /**
     * @return JsonResponse
     */
    public function getAllUsers(): JsonResponse
    {
        $users = User::get();
        if ($users->count() < 0) {
            return response()->json(['error' => 'Users not found'], 404);
        }

        return response()->json([
            'users' => $users
        ], 200);
    }

}
