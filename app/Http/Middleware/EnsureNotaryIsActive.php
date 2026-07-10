<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotaryIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_if($user?->notary_id !== null && ! $user->notary?->is_active, 403, 'The notary is inactive.');

        return $next($request);
    }
}
