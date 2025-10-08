<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use App\Services\LicenseService;
use Illuminate\Support\Facades\File;

class CheckLicense
{
    protected $svc;
    public function __construct(LicenseService $svc) { $this->svc = $svc; }

    public function handle($request, Closure $next)
    {
        $tokenPath = config('license.token_path');

        if (!File::exists($tokenPath)) {
            return response('License missing. Activate the application.', 403);
        }

        $token = File::get($tokenPath);
        $payload = $this->svc->verifyTokenLocal($token);

        if (!$payload) {
            return response('Invalid license token.', 403);
        }

        // expiry handling: if token expired, allow offline grace but require remote validation soon
        $now = time();
        $expiresAt = property_exists($payload, 'exp') ? $payload->exp : null;
        if ($expiresAt && $expiresAt < $now) {
            $graceKey = 'license_grace_'.md5(config('app.url'));
            $grace = Cache::get($graceKey, 0);
            if ($grace > config('license.offline_grace_days')) {
                return response('License expired and offline grace exceeded.', 403);
            }
            Cache::increment($graceKey);
        }

        // Periodic remote validation (cached)
        $cacheKey = 'license_valid_'.md5($payload->license_key);
        $valid = Cache::get($cacheKey);
        if ($valid === null) {
            // try online validation
            $resp = $this->svc->validateRemoteToken($token);
            if ($resp && isset($resp['valid']) && $resp['valid'] === true) {
                Cache::put($cacheKey, true, now()->addMinutes(config('license.validate_every_minutes')));
            } else {
                // could be offline or server invalid -> allow short-lived local fallback
                Cache::put($cacheKey, true, now()->addMinutes(30));
            }
        } elseif ($valid === false) {
            return response('License revoked or invalid.', 403);
        }

        // all checks passed
        return $next($request);
    }
}
