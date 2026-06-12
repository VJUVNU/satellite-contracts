<?php

namespace Vju\SatelliteContracts\Sync;

/**
 * Contract §5.2: global fingerprint over an entity set, used by hash-check
 * `mode=global` to short-circuit reconciliation.
 *
 * sha256 of the JSON-encoded `"<id>:<fingerprint>"` list ordered by id
 * ascending, encoded with unescaped unicode/slashes.
 */
final class GlobalFingerprint
{
    /**
     * @param  array<int, string>  $fingerprintsById  entity id => fingerprint
     */
    public static function fromMap(array $fingerprintsById): string
    {
        ksort($fingerprintsById);

        $items = [];
        foreach ($fingerprintsById as $entityId => $fingerprint) {
            $items[] = sprintf('%d:%s', $entityId, $fingerprint);
        }

        return hash('sha256', json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
    }
}
