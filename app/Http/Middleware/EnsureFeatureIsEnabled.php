<?php

namespace App\Http\Middleware;

use App\Domain\Plans\Services\FeatureGateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureIsEnabled
{
    public function __construct(private FeatureGateService $features) {}

    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless($request->user()?->notary, 403, 'A notary is required.');
        $this->features->assertAllowed($request->user()->notary, $feature);

        return $next($request);
    }
}
