<?php
namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class LicenseService
{
    protected $publicKey;
    public function __construct()
    {
        $this->publicKey = file_get_contents(config('license.public_key_path'));
    }

    public function verifyTokenLocal($token)
    {
        try {
            $payload = JWT::decode($token, new Key($this->publicKey, 'RS256'));
            return $payload;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function activateRemote($license_key, $fingerprint)
    {
        $resp = Http::timeout(5)->post(config('license.server_url').'/api/license/activate', [
            'license_key' => $license_key,
            'fingerprint' => $fingerprint
        ]);
        if ($resp->ok()) {
            return $resp->json();
        }
        return null;
    }

    public function validateRemoteToken($token)
    {
        try {
            $resp = Http::timeout(5)->post(config('license.server_url').'/api/license/validate', ['token'=>$token]);
            return $resp->ok() ? $resp->json() : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
