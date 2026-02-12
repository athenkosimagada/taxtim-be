<?php

namespace App\Utils;

class JwtHelper
{
    private const SECRET = 'CHANGE_THIS_SECRET_KEY';
    private const EXPIRY_SECONDS = 3600; // 1 hour

    public static function generate(array $payload): string
    {
        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));

        $payload['exp'] = time() + self::EXPIRY_SECONDS;
        $payloadEncoded = base64_encode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            "$header.$payloadEncoded",
            self::SECRET,
            true
        );

        return "$header.$payloadEncoded." . base64_encode($signature);
    }

    public static function validate(string $token): array
    {
        [$header, $payload, $signature] = explode('.', $token);

        $expected = base64_encode(hash_hmac(
            'sha256',
            "$header.$payload",
            self::SECRET,
            true
        ));

        if (!hash_equals($expected, $signature)) {
            throw new \Exception('Invalid token');
        }

        $data = json_decode(base64_decode($payload), true);

        if ($data['exp'] < time()) {
            throw new \Exception('Token expired');
        }

        return $data;
    }
}
