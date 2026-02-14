<?php

namespace Frog\Infrastructure\Auth;

use RuntimeException;

class Jwt
{
    public static function parse(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new RuntimeException('Invalid JWT format');
        }
        [$h, $p, $s] = $parts;
        $header = json_decode(self::base64UrlDecode($h), true);
        $payload = json_decode(self::base64UrlDecode($p), true);
        if (!is_array($header) || !is_array($payload)) {
            throw new RuntimeException('Invalid JWT payload');
        }
        $signature = self::base64UrlDecode($s);
        return [$header, $payload, $signature, "$h.$p"];
    }

    public static function base64UrlDecode(string $data): string
    {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        $data = strtr($data, '-_', '+/');
        $decoded = base64_decode($data, true);
        if ($decoded === false) {
            throw new RuntimeException('Invalid base64url encoding');
        }
        return $decoded;
    }

    public static function jwkToPem(array $jwk): string
    {
        if (!isset($jwk['kty'], $jwk['n'], $jwk['e']) || $jwk['kty'] !== 'RSA') {
            throw new RuntimeException('Unsupported JWK');
        }

        $modulus = self::base64UrlDecode($jwk['n']);
        $exponent = self::base64UrlDecode($jwk['e']);

        $modulus = "\x02" . self::encodeLength(strlen($modulus)) . $modulus;
        $exponent = "\x02" . self::encodeLength(strlen($exponent)) . $exponent;
        $sequence = "\x30" . self::encodeLength(strlen($modulus . $exponent)) . $modulus . $exponent;
        $bitString = "\x03" . self::encodeLength(strlen($sequence) + 1) . "\x00" . $sequence;

        $rsaOid = "\x30\x0D\x06\x09\x2A\x86\x48\x86\xF7\x0D\x01\x01\x01\x05\x00";
        $pubKey = "\x30" . self::encodeLength(strlen($rsaOid . $bitString)) . $rsaOid . $bitString;

        $pem = "-----BEGIN PUBLIC KEY-----\n";
        $pem .= chunk_split(base64_encode($pubKey), 64, "\n");
        $pem .= "-----END PUBLIC KEY-----\n";
        return $pem;
    }

    private static function encodeLength(int $length): string
    {
        if ($length <= 0x7F) {
            return chr($length);
        }
        $temp = ltrim(pack('N', $length), "\x00");
        return chr(0x80 | strlen($temp)) . $temp;
    }
}
