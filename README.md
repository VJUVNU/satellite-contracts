# vju/satellite-contracts

Shared hub ↔ satellite contract code for the VJU Hub ecosystem. Installed by
**both** the hub (`vju/hub-satellite-sync`) and satellite apps
(`vju/satellite-client`) so the wire contract cannot drift.

## Contents

| Class | Purpose |
|---|---|
| `Vju\SatelliteContracts\Sync\CanonicalFingerprint` | Canonical SHA-256 payload hashing (contract §5.1). `hash()` for fingerprints, `canonicalize()` for normalizing payloads before push. |
| `Vju\SatelliteContracts\Sync\SyncStringNormalizer` | Trim + NFC unicode normalization used inside fingerprints and payloads. |
| `Vju\SatelliteContracts\Sync\EventKey` | Idempotency key builders for single and bulk (v1.2) envelopes. |
| `Vju\SatelliteContracts\Sync\EntityTypes` | `user`, `department` entity type identifiers. |
| `Vju\SatelliteContracts\Headers` | All `X-*` header names: webhook secret, hub origin, sync source, API token, on-behalf-of. |

## Contract stability

`CanonicalFingerprint` is **frozen**. Any change to its output is a breaking
change (major version) that forces a full re-sync of every entity on every
satellite. The pinned-vector test in `tests/CanonicalFingerprintTest.php`
guards this.

Requires `ext-intl` in practice — without it strings are only trimmed, which
breaks fingerprint parity for non-ASCII data.

## Docs

See `docs/integrations/vjuhub-satellite-user-sync-contract.md` in the VJUHub
repo for the authoritative sync contract (v1.2).
