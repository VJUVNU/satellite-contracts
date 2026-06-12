<?php

namespace Vju\SatelliteContracts\Sync;

/**
 * Idempotency event keys carried in sync envelopes. Satellites treat keys as
 * opaque strings (contract v1.1+) — only the hub builds them, but satellites
 * reuse this class in tests to assert replay behavior.
 */
final class EventKey
{
    public static function forEntity(string $entityType, int $entityId, string $fingerprint, string $eventType): string
    {
        return sprintf(
            '%s:%d:%s:%s',
            $entityType,
            $entityId,
            $fingerprint,
            str_replace('.', '-', $eventType),
        );
    }

    /**
     * Key for `{entity}.bulk-upserted` / `{entity}.bulk-deleted` envelopes,
     * derived from the per-item keys (or ids for deletes).
     *
     * @param  list<string>  $parts
     */
    public static function forBulk(string $kind, string $entityType, array $parts): string
    {
        return sprintf('%s:%s:%s', $kind, $entityType, hash('sha256', implode('|', $parts)));
    }
}
