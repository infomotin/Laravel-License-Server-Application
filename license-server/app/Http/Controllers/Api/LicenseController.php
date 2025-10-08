<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\License;
use App\Models\LicenseActivation;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LicenseController extends Controller
{
    protected $privateKeyPath;
    protected $publicKeyPath;
    protected $jwtAlgo = 'RS256';

    public function __construct()
    {
        $this->privateKeyPath = storage_path('app/keys/license_private.pem');
        $this->publicKeyPath  = storage_path('app/keys/license_public.pem');
    }

    // POST /api/license/activate
    public function activate(Request $req)
    {
        $req->validate(['license_key'=>'required|string', 'fingerprint'=>'required|string']);

        $license = License::where('key', $req->license_key)->first();
        if (!$license || $license->status !== 'active') {
            return response()->json(['error'=>'invalid_license'], 403);
        }

        // expiry check
        if ($license->expires_at && $license->expires_at->isPast()) {
            return response()->json(['error'=>'expired'], 403);
        }

        // bind fingerprint if not set
        if (!$license->bound_identifier) {
            $license->bound_identifier = $req->fingerprint;
            $license->save();
        } elseif ($license->bound_identifier !== $req->fingerprint) {
            return response()->json(['error'=>'bound_to_another'], 403);
        }

        // record activation
        LicenseActivation::updateOrCreate(
            ['license_id'=>$license->id, 'fingerprint'=>$req->fingerprint],
            ['ip'=>$req->ip(), 'user_agent'=>$req->userAgent(), 'last_seen'=>now()]
        );

        // prepare JWT payload (short-lived token, e.g. 30 days)
        $payload = [
            'iss' => url('/'),
            'iat' => time(),
            'exp' => time() + (60*60*24*30),
            'license_key' => $license->key,
            'fingerprint' => $req->fingerprint,
            'meta' => $license->meta ?? []
        ];

        $privateKey = file_get_contents($this->privateKeyPath);
        $jwt = JWT::encode($payload, $privateKey, $this->jwtAlgo);

        return response()->json(['token'=>$jwt, 'expires_in'=>60*60*24*30]);
    }

    // POST /api/license/validate
    public function validateLicense(Request $req)
    {
        $req->validate(['token'=>'required|string']);
        $token = $req->token;

        try {
            $publicKey = file_get_contents($this->publicKeyPath);
            $payload = JWT::decode($token, new Key($publicKey, $this->jwtAlgo));
            // check DB if still active
            $license = License::where('key', $payload->license_key)->first();
            if (!$license || $license->status !== 'active') {
                return response()->json(['valid'=>false,'reason'=>'inactive'], 403);
            }
            if ($license->bound_identifier && $license->bound_identifier !== $payload->fingerprint) {
                return response()->json(['valid'=>false,'reason'=>'bound_mismatch'], 403);
            }
            return response()->json(['valid'=>true,'payload'=>$payload]);
        } catch (\Exception $e) {
            return response()->json(['valid'=>false,'error'=>$e->getMessage()], 403);
        }
    }

    // POST /api/license/revoke
    public function revoke(Request $req)
    {
        $req->validate(['license_key'=>'required|string']);
        $license = License::where('key',$req->license_key)->first();
        if ($license) {
            $license->status = 'revoked';
            $license->save();
        }
        return response()->json(['ok'=>true]);
    }
}
