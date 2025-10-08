<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\License;
use Illuminate\Support\Str;

class CreateLicense extends Command
{
    protected $signature = 'license:create {--key=} {--days=365}';
    protected $description = 'Create a new license key';

    public function handle()
    {
        $key = $this->option('key') ?: strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
        $days = (int)$this->option('days');

        $license = License::create([
            'key' => $key,
            'expires_at' => now()->addDays($days),
            'meta' => ['seats'=>1],
        ]);

        $this->info("Created license: {$license->key} (expires: {$license->expires_at})");
    }
}
