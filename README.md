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
