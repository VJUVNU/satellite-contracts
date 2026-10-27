<?php

namespace Vju\SatelliteContracts\Tests;

use PHPUnit\Framework\TestCase;
use Vju\SatelliteContracts\Signing\RequestSignature;

final class RequestSignatureTest extends TestCase
{
    public function test_canonical_path_sorts_query_and_adds_leading_slash(): void
    {
        $this->assertSame('/api/v1/orders', RequestSignature::canonicalPath('api/v1/orders'));
        $this->assertSame('/api/v1/payments?cursor=5&limit=10', RequestSignature::canonicalPath('/api/v1/payments', ['limit' => 10, 'cursor' => 5]));
    }

    public function test_pinned_vector(): void
    {
        // Enrollment's MirrorSignature::ky('secret', 'POST', '/api/v1/orders', 1761523200, 'n-abc', '{"a":1}') gives the same value.
        $this->assertSame(
            'sha256=5524c845886df30630676f9cb1f5281fc766b4830f752ff36ef5a0a4e31b5b42',
            RequestSignature::sign('secret', 'post', '/api/v1/orders', 1761523200, 'n-abc', '{"a":1}'),
        );
    }

    public function test_verify_accepts_any_rotation_secret_and_rejects_tampering(): void
    {
        $sig = RequestSignature::sign('new', 'GET', '/a', 100, 'n-1', '');

        $this->assertTrue(RequestSignature::verify(['old', 'new'], $sig, 'GET', '/a', 100, 'n-1', ''));
        $this->assertFalse(RequestSignature::verify(['old'], $sig, 'GET', '/a', 100, 'n-1', ''));
        $this->assertFalse(RequestSignature::verify('new', $sig, 'GET', '/a', 100, 'n-1', 'x'));
        $this->assertFalse(RequestSignature::verify('new', '', 'GET', '/a', 100, 'n-1', ''));
        $this->assertFalse(RequestSignature::verify(['', ''], $sig, 'GET', '/a', 100, 'n-1', ''));
    }

    public function test_window(): void
    {
        $this->assertTrue(RequestSignature::withinWindow(1000, 1300));
        $this->assertFalse(RequestSignature::withinWindow(1000, 1301));
        $this->assertFalse(RequestSignature::withinWindow(0, 0));
    }

    public function test_headers_round_trip_through_verify(): void
    {
        $h = RequestSignature::headers('enrollment', 's3', 'PATCH', 'api/v1/orders/HP1', ['b' => 2, 'a' => 1], '{}', 500, 'n-x');

        $this->assertSame('enrollment', $h[RequestSignature::HEADER_KEY]);
        $this->assertTrue(RequestSignature::verify('s3', $h[RequestSignature::HEADER_SIGNATURE], 'PATCH', '/api/v1/orders/HP1?a=1&b=2', 500, 'n-x', '{}'));
    }
}
