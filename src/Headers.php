<?php

namespace Vju\SatelliteContracts;

/**
 * HTTP header names shared by the hub and satellite apps. Both sides MUST
 * reference these constants instead of string literals so the wire contract
 * cannot drift.
 */
final class Headers
{
    /** Shared secret sent with every hub -> satellite webhook/sync request. */
    public const WEBHOOK_SECRET = 'X-Workflow-Hook-Secret';

    /** Hub origin (scheme://host[:port]) so satellites can allowlist server-to-server calls. */
    public const HUB_ORIGIN = 'X-Vju-Hub-Origin';

    /** Marks hub -> satellite entity sync requests. Value is always {@see self::SYNC_SOURCE_VALUE}. */
    public const SYNC_SOURCE = 'X-Vju-Hub-Sync-Source';

    public const SYNC_SOURCE_VALUE = 'vju-hub';

    /** Satellite -> hub API token (alternative to Authorization: Bearer). */
    public const API_TOKEN = 'X-VJU-HUB-API-TOKEN';

    /** Satellite -> hub acting-user resolution, by email (preferred). */
    public const ON_BEHALF_OF_EMAIL = 'X-On-Behalf-Of-Email';

    /** Satellite -> hub acting-user resolution, by hub user id. */
    public const ON_BEHALF_OF_ID = 'X-On-Behalf-Of-Id';
}
