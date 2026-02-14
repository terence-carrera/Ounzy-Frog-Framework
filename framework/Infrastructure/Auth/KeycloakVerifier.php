<?php

namespace Frog\Infrastructure\Auth;

use Frog\Infrastructure\Cache\CacheInterface;
use RuntimeException;

class KeycloakVerifier
{
    public function __construct(private array $config, private ?CacheInterface $cache = null) {}

    public function verify(string $token): array
    {
        [$header, $payload, $signature, $signed] = Jwt::parse($token);

        if (($header['alg'] ?? '') !== 'RS256') {
            throw new RuntimeException('Unsupported JWT algorithm');
        }

        $issuer = $this->issuer();
        if ($issuer && ($payload['iss'] ?? '') !== $issuer) {
            throw new RuntimeException('Invalid token issuer');
        }

        $clientId = $this->config['client_id'] ?? '';
        if ($clientId !== '') {
            $aud = $payload['aud'] ?? null;
            $validAud = is_array($aud) ? in_array($clientId, $aud, true) : ($aud === $clientId);
            if (!$validAud) {
                throw new RuntimeException('Invalid token audience');
            }
        }

        if (isset($payload['exp']) && time() >= (int)$payload['exp']) {
            throw new RuntimeException('Token expired');
        }

        $kid = $header['kid'] ?? null;
        if (!$kid) {
            throw new RuntimeException('Missing key id');
        }

        $jwks = $this->getJwks();
        $jwk = $this->findKey($jwks, $kid);
        $pem = Jwt::jwkToPem($jwk);

        $key = openssl_pkey_get_public($pem);
        if ($key === false) {
            throw new RuntimeException('Invalid public key');
        }

        $ok = openssl_verify($signed, $signature, $key, OPENSSL_ALGO_SHA256);
        if ($ok !== 1) {
            throw new RuntimeException('Invalid token signature');
        }

        return $payload;
    }

    private function issuer(): string
    {
        if (!empty($this->config['issuer'])) {
            return rtrim($this->config['issuer'], '/');
        }
        $base = rtrim($this->config['base_url'] ?? '', '/');
        $realm = $this->config['realm'] ?? '';
        if ($base === '' || $realm === '') return '';
        return $base . '/realms/' . $realm;
    }

    private function getJwks(): array
    {
        $cacheKey = 'keycloak.jwks.' . ($this->config['realm'] ?? 'default');
        if ($this->cache && $this->cache->has($cacheKey)) {
            $cached = $this->cache->get($cacheKey);
            if (is_array($cached)) return $cached;
        }

        $base = rtrim($this->config['base_url'] ?? '', '/');
        $realm = $this->config['realm'] ?? '';
        if ($base === '' || $realm === '') {
            throw new RuntimeException('Keycloak not configured');
        }

        $url = $base . '/realms/' . $realm . '/protocol/openid-connect/certs';
        $raw = @file_get_contents($url);
        if ($raw === false) {
            throw new RuntimeException('Failed to fetch JWKS');
        }
        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data['keys'])) {
            throw new RuntimeException('Invalid JWKS response');
        }

        $ttl = (int)($this->config['jwks_cache_ttl'] ?? 3600);
        if ($this->cache) {
            $this->cache->set($cacheKey, $data['keys'], $ttl);
        }

        return $data['keys'];
    }

    private function findKey(array $jwks, string $kid): array
    {
        foreach ($jwks as $key) {
            if (($key['kid'] ?? '') === $kid) {
                return $key;
            }
        }
        throw new RuntimeException('Key not found in JWKS');
    }
}
