<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Ecdsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        //
    ];

    public function boot(): void
    {
        $privateKeyPath = config('services.apple.private_key');

        if (!$privateKeyPath || !is_string($privateKeyPath) || !file_exists($privateKeyPath)) {
            logger("Apple private key not found at: " . $privateKeyPath);
            return;
        }

        $privateKeyContents = file_get_contents($privateKeyPath);

        if (empty($privateKeyContents)) {
            logger("Apple private key file exists but is empty: " . $privateKeyPath);
            return;
        }

        $this->app->bind(Configuration::class, static function () use ($privateKeyContents) {
            return Configuration::forAsymmetricSigner(
                new Sha256(),
                InMemory::plainText($privateKeyContents),
                InMemory::plainText(' ') // just pass a dummy public key, like a space
            );
        });
    }
}
