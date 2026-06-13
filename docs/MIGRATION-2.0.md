# Migrating to ork-iam 2.0.0

2.0.0 aligns the public API with the [ORN ontology](ORN-ONTOLOGY.md). All 1.x names listed below are removed.

## Type renames

| 1.x | 2.0 |
|-----|-----|
| `Amtgard\IAM\OrkServices` | `Amtgard\IAM\Catalog\ServiceCatalog` |
| `Amtgard\IAM\ServiceIdentifier` | `Amtgard\IAM\ORN\OrnPrefix` |
| `Amtgard\IAM\ProvisoSlot` | *(removed)* → `Orn\OrnSegmentLabel` |
| `Amtgard\IAM\Proviso\Proviso` | `Amtgard\IAM\ORN\OrnSegment` |
| `Amtgard\IAM\Proviso\Grant` | `Amtgard\IAM\ORN\Grant` |
| `Amtgard\IAM\Proviso\Condition` | `Amtgard\IAM\ORN\Condition` |

## Method renames

| 1.x | 2.0 |
|-----|-----|
| `getServiceIdentifier()` | `getPrefix()` |
| `serviceFormat()` | `ornSegmentSchema()` |
| `provisoSlots()` | `ornSegmentLabels()` |
| `getProviso()` / `getProvisos()` / `setProviso()` | `getSegment()` / `getSegments()` / `setSegment()` |
| `buildProviso()` | `buildSegment()` |
| `getSlot()` / `setSlot()` | `getLabel()` / `setLabel()` |
| `getId()` / `setId()` | `getValue()` / `setValue()` |
| `getSegmentLabel()` | `getLabel()` |
| `getSegmentValue()` | `getValue()` |
| `toOrkServices()` | `toCatalogEntry()` |
| `OrnClassMap::validateCustomServiceName()` | `OrnClassMap::validateCustomPrefix()` |

## Claim / requirement authors

```php
// 1.x
protected function serviceFormat(): array
{
    return [OrkServices::Configuration, 'tenant-id'];
}

// 2.0
protected function ornSegmentSchema(): array
{
    return [ServiceCatalog::Configuration, 'tenant-id'];
}
```

## `ork-iam-orn-definitions` companion release

Release `amtgard/ork-iam-orn-definitions` 2.0.0 alongside `ork-iam` 2.0.0:

- Replace `OrkServices` with `ServiceCatalog`
- Rename `serviceFormat()` to `ornSegmentSchema()` on all `*Format` classes
- Update `register.php` enum references

## Branch layout

| Branch | Base | Purpose |
|--------|------|---------|
| `feature/orn-segment-ontology-prep` | `main` | 1.x non-breaking aliases + custom segment labels |
| `feature/2.0-ontology` | `feature/orn-segment-ontology-prep` | Breaking 2.0.0 rename (this document) |

Merge order: prep branch → `main` (1.3.x), then 2.0 branch when `ork-iam-orn-definitions` 2.0 is ready.

## Upgrading from 1.4 to 2.1 (claim composition)

v2.1.0 ports the v1.4.0 claim-composition APIs onto the 2.0 ontology. Authorization semantics are unchanged; only type names differ.

```php
// Prefix / catalog
OrkServices::Attendance           → ServiceCatalog::Attendance
ServiceIdentifier::from($s)       → OrnPrefix::from($s)
$claim->getServiceIdentifier()     → $claim->getPrefix()

// Segments
$claim->getProviso($label)        → $claim->getSegment($label)
$proviso->getId()                  → $segment->getValue()
$proviso->getSegmentLabel()        → $segment->getLabel()

// composer.json
"amtgard/ork-iam": "^1.4"         → "^2.1"
"amtgard/ork-iam-orn-definitions": "^1.x" → "^2.0"
```

`ClaimBuilder`, `PolicyBuilder`, and `PolicyDocument` method names are unchanged. `PolicyDocument::fromVerifiedJwt()` and `fromVerifiedPayload()` behave the same as on 1.4.
