<?php

namespace Vju\SatelliteContracts\Sync;

/**
 * Entity type identifiers used in sync envelopes, event keys, webhook URL
 * config keys ({type}_sync_enabled, ...) and hash-check requests.
 */
final class EntityTypes
{
    public const USER = 'user';

    public const DEPARTMENT = 'department';
}
