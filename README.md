# ork-iam

IAM Implementation for ORNs

## Design

https://docs.google.com/document/d/1iv8trAWMf21VFtsKGLCrheVzUeiQVlZYRGB2rfmtUq0/edit?tab=t.0#heading=h.zfr9cypk79t7

## Installation

```bash
composer require amtgard/ork-iam
```

Requirements:
- PHP ^8.3
- ext-json

For local development:
```bash
composer install
```

## Introduction
ORK IAM is a policy document system that allows for testing `claims` against `requirements`. `claims` may be bundled into `policies`. If any claim in a policy is accepted by the requirement on a given object, then the policy is accepted by the requirement; otherwise, it is rejected.

A claim and a policy are not self-enforcing, in the sense that any validly constructed claim can be compared to a requirement; it is up to the system making the comparison to validate that the given claim originated from a trusted source.

A typical method of doing this would be a cryptographic signature of the policy from the trusted source, or a cryptographic signature of an envelope containing the policy, such as embedding the policy in a JWT.

## Usage

Basic example showing how to parse an ORN into a Claim, define a Requirement, and evaluate it. Also shows simple `Resource` usage.

_Single Claim_
```php
<?php
require __DIR__ . '/vendor/autoload.php';

use Amtgard\IAM\ClaimFactory;
use Amtgard\IAM\ORN\Definitions\AttendanceRequirement;
use Amtgard\IAM\ORN\Definitions\AttendanceClaim;
use Amtgard\IAM\OrkService;
use Amtgard\IAM\Resource;

// Create a Claim instance from an ORN string
$claim = ClaimFactory::createOrn('Attendance:*::::::ORK/AddAttendance'); // => AttendanceClaim

// Define a Requirement for Attendance on the same ORN pattern
$requirement = new AttendanceRequirement(OrkService::Attendance, 'Attendance:1:2:3:4:5:6:ORK/AddAttendance');

// Evaluate permission
if ($requirement->allows($claim)) {
    // The claim satisfies the requirement
}
```

_Policy_
```php
$claim1 = new OrkClaim(OrkService::ORK, "ORK:1:::::*");
$claim2 = new OrkClaim(OrkService::ORK, "ORK:2:::::*");
$claim3 = new OrkClaim(OrkService::ORK, "ORK::3::::*");
$claim4 = new OrkClaim(OrkService::ORK, "ORK:::::4:*");

$policy = new Policy([$claim1, $claim2, $claim3, $claim4]);
$requirement = new OrkRequirement(OrkService::ORK, "ORK:1:7:8:9:10:ORK/AddKingdom");

if ($policy->grants($requirement)) {
    // Policy satisfies the requirement
}
```

## Supported Services

The following services are supported (from `src/OrkService.php`):

### ORK Services
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

### Planned Applications
- Idp
- Documents
- Forums
- Media
- Errata

## Testing

Run the test suite:
```bash
vendor/bin/phpunit
```

