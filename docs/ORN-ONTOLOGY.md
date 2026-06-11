# ORN ontology

An ORN decomposes into a fixed vocabulary. This document is the canonical naming model; 2.0.0 aligns the public API with these terms.

## Glossary

| Term | Meaning | Example |
|------|---------|---------|
| **Prefix** | Leading segment — who owns this ORN | `YourService`, `Attendance` |
| **Schema** | Ordered list of segment labels for a claim/requirement type | `[Configuration, tenant-id, org unit]` |
| **Label** | Name of one middle segment in the schema | `tenant-id` |
| **Offset** | Zero-based index of a label in the schema | `1` for `tenant-id` in the schema above |
| **Value** | Parsed ID in one segment (`int`, `*`, or empty) | `42` |
| **Binding** | Label + value on a parsed claim or requirement | `tenant-id = 42` |
| **Catalog** | Built-in registry of known prefix and label names | `OrkServices` enum today |

```
YourService : 1    : 42       : 7        : Widget/Read
[prefix]      [val0] [val1]     [val2]     [resource]
              └──────── schema labels (by offset) ─────┘
```

## 1.x API (current, non-breaking)

Legacy names remain available. Prefer the **Preferred (1.x)** column in new code.

| Concept | Preferred (1.x) | Legacy (deprecated 2.0) |
|---------|-----------------|---------------------------|
| Prefix type | `ServiceIdentifier` | — |
| Prefix accessor | `getPrefix()` | `getServiceIdentifier()` |
| Label type | `Orn\OrnSegmentLabel` | `ProvisoSlot` |
| Schema method | `ornSegmentSchema()` | `serviceFormat()` |
| Labels resolver | `ornSegmentLabels()` | `provisoSlots()` |
| Offset lookup | `segmentOffset($label)` | — |
| Binding type | `Proviso\Proviso` | — |
| Binding accessors | `getSegment()`, `getSegments()`, `setSegment()` | `getProviso()`, `getProvisos()`, `setProviso()` |
| Label on binding | `getSegmentLabel()` | `getSlot()` |
| Value on binding | `getSegmentValue()` | `getId()` |
| Catalog | `OrkServices` | — |

## 2.0 API (planned, breaking)

See [MIGRATION-2.0.md](MIGRATION-2.0.md) on the `feature/2.0-ontology` branch.

| Concept | 2.0 type / method |
|---------|-------------------|
| Prefix | `Orn\OrnPrefix` |
| Schema | `ornSegmentSchema()` |
| Label | `Orn\OrnSegmentLabel` |
| Offset | `OrnSegmentLabel::offsetIn()`, `segmentOffset()` |
| Value | `getValue()` on `Orn\OrnSegment` |
| Binding | `Orn\OrnSegment` (`Grant`, `Condition`) |
| Catalog | `Catalog\ServiceCatalog` (renamed from `OrkServices`) |
