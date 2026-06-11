# ORN ontology

An ORN decomposes into a fixed vocabulary used throughout ork-iam 2.0.

## Glossary

| Term | Type / method | Meaning |
|------|---------------|---------|
| **Prefix** | `Orn\OrnPrefix`, `getPrefix()` | Leading segment — who owns the ORN |
| **Schema** | `ornSegmentSchema()` | Ordered segment labels for a claim/requirement type |
| **Label** | `Orn\OrnSegmentLabel` | Name of one middle segment in the schema |
| **Offset** | `segmentOffset()`, `OrnSegmentLabel::offsetIn()` | Zero-based index of a label in the schema |
| **Value** | `getValue()` on `Orn\OrnSegment` | Parsed ID in one segment (`int`, `*`, or empty) |
| **Binding** | `Orn\OrnSegment` (`Grant`, `Condition`) | Label + value on a parsed claim or requirement |
| **Catalog** | `Catalog\ServiceCatalog` | Built-in registry of known prefix and label names |

```
YourService : 1    : 42       : 7        : Widget/Read
[prefix]      [val0] [val1]     [val2]     [resource]
              └──────── schema labels (by offset) ─────┘
```

## Example

```php
protected function ornSegmentSchema(): array
{
    return [ServiceCatalog::Configuration, 'tenant-id', 'org unit'];
}

$claim->getSegment('tenant-id')->getValue(); // 42
$claim->segmentOffset('tenant-id');          // 1
```

## Migrating from 1.x

See [MIGRATION-2.0.md](MIGRATION-2.0.md).
