<?php

namespace Vju\SatelliteContracts\Tests;

use PHPUnit\Framework\TestCase;
use Vju\SatelliteContracts\Sync\CanonicalFingerprint;
use Vju\SatelliteContracts\Sync\EventKey;
use Vju\SatelliteContracts\Sync\SyncStringNormalizer;

class CanonicalFingerprintTest extends TestCase
{
    /**
     * Pinned vector captured from the hub before extraction (VJUHub
     * SatelliteEntitySyncService::fingerprint, 2026-06-12). If this test
     * fails the sync contract is broken and every satellite will re-sync.
     */
    public function test_fingerprint_matches_pinned_hub_vector(): void
    {
        $payload = ['b' => 1, 'a' => ' Ä  ', 'nested' => ['z' => ['x', 'y'], 'a' => 'Việt Nam']];

        $this->assertSame(
            '41da3d87c593b8a91aaa46171aeaaea471add24b69f15f3a78894a43676f6988',
            CanonicalFingerprint::hash($payload),
        );
    }

    public function test_fingerprint_is_key_order_independent(): void
    {
        $a = CanonicalFingerprint::hash(['x' => 1, 'y' => ['b' => 2, 'a' => 3]]);
        $b = CanonicalFingerprint::hash(['y' => ['a' => 3, 'b' => 2], 'x' => 1]);

        $this->assertSame($a, $b);
    }

    public function test_fingerprint_normalizes_unicode_to_nfc(): void
    {
        // "Ä" composed (U+00C4) vs decomposed (A + U+0308)
        $composed = CanonicalFingerprint::hash(['name' => "\u{00C4}"]);
        $decomposed = CanonicalFingerprint::hash(['name' => "A\u{0308}"]);

        $this->assertSame($composed, $decomposed);
    }

    public function test_fingerprint_trims_strings(): void
    {
        $this->assertSame(
            CanonicalFingerprint::hash(['name' => 'Alex']),
            CanonicalFingerprint::hash(['name' => '  Alex  ']),
        );
    }

    public function test_canonicalize_preserves_key_order(): void
    {
        $this->assertSame(
            ['b' => 'x', 'a' => 'y'],
            CanonicalFingerprint::canonicalize(['b' => ' x ', 'a' => 'y']),
        );
    }

    public function test_normalizer_trims_and_normalizes(): void
    {
        $this->assertSame('Ä', SyncStringNormalizer::normalize(" A\u{0308} "));
    }

    public function test_entity_event_key_format(): void
    {
        $this->assertSame(
            'user:7:abc123:user-updated',
            EventKey::forEntity('user', 7, 'abc123', 'user.updated'),
        );
    }

    public function test_bulk_event_key_format(): void
    {
        $this->assertSame(
            'bulk:user:'.hash('sha256', 'k1|k2'),
            EventKey::forBulk('bulk', 'user', ['k1', 'k2']),
        );
    }
}
