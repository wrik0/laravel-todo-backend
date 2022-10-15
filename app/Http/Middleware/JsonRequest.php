<?php

namespace App\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Response;

class JsonRequest
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
        if ($request->header('Accept') != 'application/json' || $request->header('Content-Type') != 'application/json')
            return response()->json([
                'msg' => 'Accept and Content-Type must be of type application/json'
            ], Response::HTTP_UNSUPPORTED_MEDIA_TYPE);
        return $next($request);
    }
}
