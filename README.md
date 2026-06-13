# ork-iam

IAM implementation for ORNs — the policy engine for parsing ORNs, evaluating claims against requirements, and authorizing policies.

## Design

https://docs.google.com/document/d/1iv8trAWMf21VFtsKGLCrheVzUeiQVlZYRGB2rfmtUq0/edit?tab=t.0#heading=h.zfr9cypk79t7

## Installation

Requirements:
- PHP ^8.3
- ext-json

**Policy engine only** (bring your own ORN definitions):

```bash
composer require amtgard/ork-iam
```

**With standard Amtgard ORN definitions** (Attendance, ORK):

```bash
composer require amtgard/ork-iam amtgard/ork-iam-orn-definitions
```

`ork-iam` provides the IAM engine. It does not ship ORN definition classes itself. Use the companion package [amtgard/ork-iam-orn-definitions](https://github.com/amtgard/ork-iam-orn-definitions) for the standard set, or register your own (see below).

For local development:

```bash
composer install
```

### Version lines

| Line | Branch | Composer constraint | Latest tag |
|------|--------|---------------------|------------|
| 1.x (maintenance) | `1.x` | `"amtgard/ork-iam": "^1.4"` | `v1.4.0` |
| 2.x (current) | `main` | `"amtgard/ork-iam": "^2.0"` | `v2.0.0` |

Pin `^1.4` for IDP and existing integrators on the 1.x API. Use `^2.0` for the current ontology API (`ServiceCatalog`, `ornSegmentSchema()`, …).

### Branching

- **1.x fixes and minors** — branch from `1.x`, name `feature/1.x/<name>`, open PRs against `1.x`. Tag releases `v1.4.0`, `v1.5.0`, … on `1.x`.
- **2.x features** — branch from `main`, name `feature/<name>`, open PRs against `main`. Tag releases `v2.1.0`, `v2.2.0`, … on `main`.

Do not use `1.x/feature/...` — it conflicts with the `1.x` branch ref.

## Introduction

ORK IAM is a policy document system that allows for testing `claims` against `requirements`. `claims` may be bundled into `policies`. If any claim in a policy is accepted by the requirement on a given object, then the policy is accepted by the requirement; otherwise, it is rejected.

A claim and a policy are not self-enforcing, in the sense that any validly constructed claim can be compared to a requirement; it is up to the system making the comparison to validate that the given claim originated from a trusted source.

A typical method of doing this would be a cryptographic signature of the policy from the trusted source, or a cryptographic signature of an envelope containing the policy, such as embedding the policy in a JWT.

## ORN definitions

`ClaimFactory` and `RequirementFactory` resolve ORN strings to concrete classes via `OrnClassMap`. **Services must be registered before use** — otherwise factories throw `InvalidArgumentException`.

### Standard definitions (optional)

Install [amtgard/ork-iam-orn-definitions](https://github.com/amtgard/ork-iam-orn-definitions). That package registers Attendance and ORK claim/requirement classes with `OrnClassMap` automatically on Composer autoload.

Currently defined there:

| Service | Claim class | Requirement class |
|---------|-------------|-------------------|
| Attendance | `Amtgard\IAM\Definitions\ORN\AttendanceClaim` | `Amtgard\IAM\Definitions\ORN\AttendanceRequirement` |
| ORK | `Amtgard\IAM\Definitions\ORN\OrkClaim` | `Amtgard\IAM\Definitions\ORN\OrkRequirement` |

### Custom definitions

Define your own ORN classes by extending the framework types:

- `Amtgard\IAM\ORNFormat` — proviso layout and valid resources for a service
- `Amtgard\IAM\Allowance\Claim` — claim ORN for a service
- `Amtgard\IAM\Requirement\Requirement` — requirement ORN for a service

Register them with `OrnClassMap` before calling the factories:

```php
use Amtgard\IAM\ORN\OrnClassMap;
use Amtgard\IAM\OrkServices;
use MyApp\IAM\AttendanceClaim;
use MyApp\IAM\AttendanceRequirement;

OrnClassMap::registerClaim(OrkServices::Attendance, AttendanceClaim::class);
OrnClassMap::registerRequirement(OrkServices::Attendance, AttendanceRequirement::class);
```

A typical approach is a bootstrap file loaded via Composer autoload `files`, the same pattern used by `ork-iam-orn-definitions`.

### ORN ontology

ORK IAM uses a consistent vocabulary for ORN structure. See [docs/ORN-ONTOLOGY.md](docs/ORN-ONTOLOGY.md) for the full glossary and the planned 2.0.0 rename.

| Term | 1.x API | Meaning |
|------|---------|---------|
| **Prefix** | `ServiceIdentifier`, `getPrefix()` | Leading segment — who owns the ORN |
| **Schema** | `ornSegmentSchema()` | Ordered segment labels for a claim/requirement type |
| **Label** | `Orn\OrnSegmentLabel` | Name of one middle segment in the schema |
| **Offset** | `segmentOffset()`, `OrnSegmentLabel::offsetIn()` | Zero-based index of a label in the schema |
| **Value** | `getSegmentValue()` | Parsed ID in one segment (`int`, `*`, or empty) |
| **Binding** | `Proviso` (`Grant` / `Condition`) | Label + value on a parsed claim or requirement |
| **Catalog** | `OrkServices` | Built-in registry of known prefix and label names |

Legacy names (`serviceFormat()`, `getProviso()`, `ProvisoSlot`, …) remain available and are deprecated for removal in 2.0.0.

### Custom service identifiers

An ORN has two distinct naming layers at the prefix and schema level:

1. **Prefix** — the leading segment (`Attendance` in `Attendance:1:2:…`, or any custom name like `YourService` in `YourService:1:Widget/Read`). This identifies which integrator or product owns the ORN.
2. **Schema labels** — the ordered middle segments returned by `ornSegmentSchema()` (`Configuration`, `Game`, `Kingdom`, …). Each label names one ORN value segment, not the value itself.

Built-in prefixes (`Attendance`, `ORK`, …) normalize to `OrkServices` cases. Integrators may also register **custom prefixes** that are not enum members, as long as they match `/^[A-Z][A-Za-z0-9]*$/`. Each prefix maps to its own claim/requirement classes and `serviceFormat()` layout.

```php
use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\ORN\OrnClassMap;
use MyApp\IAM\YourServiceClaim;
use MyApp\IAM\YourServiceRequirement;

OrnClassMap::registerClaim('YourService', YourServiceClaim::class);
OrnClassMap::registerRequirement('YourService', YourServiceRequirement::class);

$claim = ClaimFactory::createOrn('YourService:1:Widget/Read');
```

Built-in `OrkServices` names cannot be registered via a custom string key — use the enum overload (`OrnClassMap::registerClaim(OrkServices::Attendance, …)`). `OrnClassMap::validateCustomServiceName()` checks that a proposed custom name does not collide with a built-in identifier.

Use `getServiceIdentifier()` on claims and requirements for the prefix string. `getService()` remains available when the prefix maps to a built-in `OrkServices` case.

#### Custom segment labels

`ornSegmentSchema()` (or legacy `serviceFormat()`) may return `OrkServices` cases, arbitrary strings, or `OrnSegmentLabel` instances. Unlike prefixes, custom label names are not restricted — integrators can use any non-empty string (`tenant-id`, `org unit`, and so on). Strings that exactly match a built-in catalog entry normalize to that `OrkServices` case.

```php
protected function serviceFormat(): array
{
    return [OrkServices::Configuration, 'tenant-id', 'org unit'];
}

// equivalent preferred form:
protected function ornSegmentSchema(): array
{
    return [OrkServices::Configuration, 'tenant-id', 'org unit'];
}
```

For an ORN `YourService:1:42:7:Widget/Read`, the values `1`, `42`, and `7` map to `Configuration`, `tenant-id`, and `org unit` by offset. Use `getSegment('tenant-id')` or `getSegment(OrnSegmentLabel::from('tenant-id'))` to read a binding. `getSegmentLabel()` and `getSegmentValue()` are the preferred accessors on binding objects.

#### Alternative: `Application` prefix

`OrkServices::Application` exists as a catch-all enum case. An integrator could use `Application:…` ORNs instead of custom string prefixes, but every integrator would share one prefix and therefore one `serviceFormat()` validator — strong per-service ORN validation is not possible. Custom string prefixes are preferred so each integrator can register its own claim/requirement classes and proviso layout.

## Usage

Examples below assume `amtgard/ork-iam-orn-definitions` is installed. Adjust namespaces if you use custom definition classes.

_Single claim_

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\Definitions\ORN\AttendanceRequirement;
use Amtgard\IAM\Definitions\ORN\AttendanceClaim;
use Amtgard\IAM\OrkServices;
use Amtgard\IAM\Resource;

// Create a Claim instance from an ORN string
$claim = ClaimFactory::createOrn('Attendance:*::::::ORK/AddAttendance'); // => AttendanceClaim

// Define a Requirement for Attendance on the same ORN pattern
$requirement = new AttendanceRequirement(OrkServices::Attendance, 'Attendance:1:2:3:4:5:6:ORK/AddAttendance');

// Evaluate permission
if ($requirement->allows($claim)) {
    // The claim satisfies the requirement
}
```

_Policy_

```php
use Amtgard\IAM\Allowance\Policy;
use Amtgard\IAM\Definitions\ORN\OrkClaim;
use Amtgard\IAM\Definitions\ORN\OrkRequirement;
use Amtgard\IAM\OrkServices;

$claim1 = new OrkClaim(OrkServices::ORK, "ORK:1:::::*");
$claim2 = new OrkClaim(OrkServices::ORK, "ORK:2:::::*");
$claim3 = new OrkClaim(OrkServices::ORK, "ORK::3::::*");
$claim4 = new OrkClaim(OrkServices::ORK, "ORK:::::4:*");

$policy = new Policy([$claim1, $claim2, $claim3, $claim4]);
$requirement = new OrkRequirement(OrkServices::ORK, "ORK:1:7:8:9:10:ORK/AddKingdom");

if ($policy->isAuthorized($requirement)) {
    // Policy satisfies the requirement
}
```

### Claim and policy composition (v1.4.0)

Build claims fluently instead of hand-authoring ORN strings:

```php
use Amtgard\IAM\Allowance\ClaimBuilder;
use Amtgard\IAM\OrkServices;

$claim = ClaimBuilder::forPrefix(OrkServices::Attendance)
    ->segment(OrkServices::Configuration, 1)
    ->segment(OrkServices::Kingdom, 42)
    ->resource('ORK', 'AddAttendance')
    ->build();
```

Compose policies from ORN lines or claims; merge dedupes by canonical ORN string:

```php
use Amtgard\IAM\Allowance\PolicyBuilder;

$policy = PolicyBuilder::create()
    ->addOrn('ORK:1:::::*')
    ->addClaim($claim)
    ->build();

$combined = PolicyBuilder::from($policyA)->merge($policyB)->build();
```

`PolicyFactory::fromOrn()` remains available and delegates to `PolicyBuilder` internally.

### IDP / JWT integration

After JWT signature verification, build the effective policy from signed claims — do not merge untrusted partial policies client-side:

```php
use Amtgard\IAM\Allowance\PolicyDocument;

$doc = PolicyDocument::fromVerifiedJwt($verifiedPayload);
$policy = $doc->policy();

if ($policy->isAuthorized($requirement)) {
    // authorized
}

$blob = $doc->integratorDocument(); // optional opaque integrator config; not used by isAuthorized()
```

Expected JWT payload shape:

| Field | Type | Description |
|-------|------|-------------|
| `policy_lines` | `string[]` | ORN lines (global + integrator-specific), all ORK-namespace |
| `integrator_document` | `array` (optional) | Application-defined blob; exposed via getter only |

`PolicyDocument::fromOrnList()`, `fromJson()`, and `fromVerifiedPayload()` are also available for tests and server-side assembly of trusted fragments.

## Service identifiers

`OrkServices` enumerates known service names used in ORN provisos and registration. These are identifiers within the IAM model — not all have ORN definition classes yet.

### ORK services

- ORK
- Configuration
- Mundane
- Attendance
- Kingdom
- Park
- Unit
- Game
- Event
- EventInstance
- Awards
- Audit
- Cache
- Tenant
- Officer
- Recommendations
- Tournament

### Planned applications

- Idp
- Documents
- Forums
- Media
- Errata

ORN definition classes for additional services will be added in `amtgard/ork-iam-orn-definitions` (or your own registration) as schemas are finalized.

## Testing

Run the test suite:

```bash
vendor/bin/phpunit
```

Run with code coverage:

```bash
XDEBUG_MODE=coverage vendor/bin/phpunit -c phpunit.coverage.xml.dist --coverage-text
```
