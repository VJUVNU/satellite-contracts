<?php

namespace Vju\SatelliteContracts\Signing;

/**
 * HMAC request signing shared by every machine-to-machine API between VJU apps
 * (payment gateway, e-invoice service, and their client packages).
 *
 * The formula is byte-for-byte the one already running between Enrollment
 * (`App\Support\Mirror\MirrorSignature`) and the Academic portal
 * (`App\Support\Sync\SyncSignature`), so those two can switch to this class
 * without changing anything on the wire.
 *
 * Signed string (lines joined by "\n"):
 *   METHOD
 *   /canonical/path?sorted=query
 *   unix timestamp
 *   nonce
 *   sha256(raw body)
 *
 * Signature header value: "sha256=" . hex(hmac_sha256(signed string, secret)).
 *
 * The output is FROZEN — the pinned vector in tests/RequestSignatureTest.php
 * guards it. Changing it is a major version.
 */
final class RequestSignature
{
    public const ALGORITHM = 'sha256';

    /** Caller id (which secret to verify with). */
    public const HEADER_KEY = 'X-Vju-Key';

    public const HEADER_TIMESTAMP = 'X-Vju-Timestamp';

    public const HEADER_NONCE = 'X-Vju-Nonce';

    public const HEADER_SIGNATURE = 'X-Vju-Signature';

    /** Default allowed clock skew, in seconds. */
    public const DEFAULT_WINDOW = 300;

    public const MAX_NONCE_LENGTH = 64;

    /**
     * @param  array<string, mixed>  $query
     */
    public static function canonicalPath(string $path, array $query = []): string
    {
        ksort($query);

        $encoded = http_build_query($query);

        return '/'.ltrim($path, '/').($encoded === '' ? '' : '?'.$encoded);
    }

    public static function stringToSign(string $method, string $canonicalPath, int $timestamp, string $nonce, string $body): string
    {
        return implode("\n", [
            strtoupper($method),
            $canonicalPath,
            (string) $timestamp,
            $nonce,
            hash(self::ALGORITHM, $body),
        ]);
    }

    public static function sign(string $secret, string $method, string $canonicalPath, int $timestamp, string $nonce, string $body): string
    {
        return self::ALGORITHM.'='.hash_hmac(
            self::ALGORITHM,
            self::stringToSign($method, $canonicalPath, $timestamp, $nonce, $body),
            $secret,
        );
    }

    /**
     * Constant-time check against one or more secrets (several during a key rotation).
     *
     * @param  string|list<string>  $secrets
     */
    public static function verify(string|array $secrets, string $signature, string $method, string $canonicalPath, int $timestamp, string $nonce, string $body): bool
    {
        if ($signature === '') {
            return false;
        }

        foreach ((array) $secrets as $secret) {
            if ($secret === '') {
                continue;
            }

            if (hash_equals(self::sign($secret, $method, $canonicalPath, $timestamp, $nonce, $body), $signature)) {
                return true;
            }
        }

        return false;
    }

    public static function withinWindow(int $timestamp, ?int $now = null, int $window = self::DEFAULT_WINDOW): bool
    {
        return $timestamp > 0 && abs(($now ?? time()) - $timestamp) <= $window;
    }

    public static function nonce(): string
    {
        return 'n-'.bin2hex(random_bytes(12));
    }

    /**
     * Headers for an outgoing request.
     *
     * @param  array<string, mixed>  $query
     * @return array<string, string>
     */
    public static function headers(string $keyId, string $secret, string $method, string $path, array $query, string $body, ?int $timestamp = null, ?string $nonce = null): array
    {
        $timestamp ??= time();
        $nonce ??= self::nonce();

        return [
            self::HEADER_KEY => $keyId,
            self::HEADER_TIMESTAMP => (string) $timestamp,
            self::HEADER_NONCE => $nonce,
            self::HEADER_SIGNATURE => self::sign($secret, $method, self::canonicalPath($path, $query), $timestamp, $nonce, $body),
        ];
    }
}
