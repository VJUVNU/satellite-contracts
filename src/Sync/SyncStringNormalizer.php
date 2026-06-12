<?php

namespace Vju\SatelliteContracts\Sync;

final class SyncStringNormalizer
{
    /**
     * Trim and NFC-normalize a string the same way fingerprints are computed,
     * so payloads pushed to satellites match the hashed representation.
     */
    public static function normalize(string $value): string
    {
        $value = trim($value);

        if (! class_exists(\Normalizer::class)) {
            return $value;
        }

        $normalized = \Normalizer::normalize($value, \Normalizer::FORM_C);

        return is_string($normalized) ? $normalized : $value;
    }
}
