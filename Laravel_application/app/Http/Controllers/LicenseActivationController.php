<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LicenseService;
use Illuminate\Support\Facades\File;

class LicenseActivationController extends Controller
{
    protected $svc;
    public function __construct(LicenseService $svc) { $this->svc = $svc; }

    public function show() {
        return view('license.activate');
    }

    public function activate(Request $req)
    {
        $req->validate(['license_key'=>'required']);
        $licenseKey = $req->license_key;
        // create fingerprint (simple): host + app url + server ip
        $fingerprint = $this->makeFingerprint();
        $resp = $this->svc->activateRemote($licenseKey, $fingerprint);
        if (!$resp || !isset($resp['token'])) {
            return back()->withErrors(['msg'=>'Activation failed: server error or invalid key.']);
        }
        // store token locally
        File::put(config('license.token_path'), $resp['token']);
        return back()->with('success','Activated successfully.');
    }

    protected function makeFingerprint()
    {
        $fqdn = gethostname();
        $url = config('app.url');
        $ip = gethostbyname(gethostname());
        return substr(sha1($fqdn . '|' . $url . '|' . $ip), 0, 40);
    }
}
