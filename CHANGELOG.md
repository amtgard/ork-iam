# Changelog

## v1.4.0

### Added

- `ClaimBuilder` — fluent construction of claims from prefix, segments, and resource
- `PolicyBuilder` — `create()`, `from()`, `addOrn()`, `addClaim()`, `merge()`, `build()`
- `PolicyDocument` — JWT envelope with `fromOrnList()`, `fromJson()`, `fromVerifiedPayload()` / `fromVerifiedJwt()`, optional `integrator_document`
- `Policy::merge()`, `Policy::withClaims()`, `Policy::getClaims()` — union of claim sets with dedupe by canonical ORN string

### Changed

- `PolicyFactory::fromOrn()` delegates to `PolicyBuilder` (behavior unchanged)
