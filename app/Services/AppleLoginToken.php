<?php

namespace App\Services;

use Carbon\Carbon;
use Lcobucci\JWT\Configuration;

class AppleLoginToken
{
    public function generate(): string
    {
        $config = app(Configuration::class);
        $now = Carbon::now()->toDateTimeImmutable();
        $expiresAt = Carbon::now()->addMinutes(5)->toDateTimeImmutable();

        $token = $config->builder()
            ->issuedBy(config('services.apple.team_id'))                     // iss
            ->issuedAt($now)                                                 // iat
            ->expiresAt($expiresAt)                                          // exp
            ->permittedFor('https://appleid.apple.com')                      // ✅ aud
            ->withHeader('kid', config('services.apple.key_id'))             // kid
            ->relatedTo(config('services.apple.client_id'))                  // ✅ sub
            ->getToken($config->signer(), $config->signingKey());

        return $token->toString();
    }

}
