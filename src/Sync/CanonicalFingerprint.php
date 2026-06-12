<?php

namespace Vju\SatelliteContracts\Sync;

/**
 * Canonical payload hashing shared verbatim by the hub and every satellite.
 *
 * The fingerprint is the SHA-256 of the canonical JSON form of a payload:
 * strings trimmed + NFC-normalized, associative keys sorted recursively,
 * list arrays re-indexed, encoded with unescaped unicode/slashes.
 *
 * Any change here is a breaking contract change: every stored fingerprint on
 * both sides becomes stale and a full re-sync of all entities is triggered.
 */
final class CanonicalFingerprint
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function hash(array $payload): string
    {
        $canonical = self::canonicalize(self::sortKeysRecursively($payload));

        return hash('sha256', json_encode($canonical, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    /**
     * Trim + NFC-normalize every string in the payload, recursively, without
     * reordering keys. Used to keep pushed payloads byte-identical to the
     * representation that was hashed.
     *
     * @param  array<int|string, mixed>  $payload
     * @return array<int|string, mixed>
     */
    public static function canonicalize(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_string($value)) {
                $payload[$key] = SyncStringNormalizer::normalize($value);

                continue;
            }

            if (is_array($value)) {
                $payload[$key] = self::canonicalize($value);
            }
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function sortKeysRecursively(array $payload): array
    {
        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                /** @var mixed $value */
                $payload[$key] = self::isAssoc($value)
                    ? self::sortKeysRecursively($value)
                    : array_values($value);
            }
        }

        ksort($payload);

        return $payload;
    }

    /**
     * @param  array<int|string, mixed>  $value
     */
    private static function isAssoc(array $value): bool
    {
        return array_keys($value) !== range(0, count($value) - 1);
    }
}
