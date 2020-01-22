<?php

namespace App\Http\Middleware;

use App\AccessToken;
use Carbon\Carbon;
use Closure;

class TokenCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $accessTokens = AccessToken::where([
            'id' => $request->header('authorization'),
            'revoked' => false
        ])->get();

        if ($accessTokens->count() <= 0) {
            return response(['error' => 'Unauthorized'], 401);
        }

        foreach ($accessTokens as $accessToken){
            if(strtotime($accessToken->expires_at) < Carbon::now()->timestamp){
                $accessToken->update(['revoked' => true]);
                return response(['error' => 'Unauthorized'], 401);
            }
        }

        return $next($request);
    }
}
