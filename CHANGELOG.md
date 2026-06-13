# Changelog

## v2.1.0

### Added

- `ClaimBuilder` — fluent construction of claims from prefix, segments, and resource (2.x types: `OrnPrefix`, `ServiceCatalog`, `OrnSegmentLabel`)
- `PolicyBuilder` — `create()`, `from()`, `addOrn()`, `addClaim()`, `merge()`, `build()`
- `PolicyDocument` — JWT envelope with `fromOrnList()`, `fromJson()`, `fromVerifiedPayload()` / `fromVerifiedJwt()`, optional `integrator_document`
- `Policy::merge()`, `Policy::withClaims()`, `Policy::getClaims()` — union of claim sets with dedupe by canonical ORN string

### Changed

- `PolicyFactory::fromOrn()` delegates to `PolicyBuilder` (behavior unchanged)

Behavioral parity with v1.4.0 on the 2.0 ontology. See [MIGRATION-2.0.md](docs/MIGRATION-2.0.md#upgrading-from-14-to-21-claim-composition) for client migration.
