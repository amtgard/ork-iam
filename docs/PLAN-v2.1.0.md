# Follow-up implementation plan: v2.1.0 (2.x — port claim composition)

**Prerequisite:** v1.4.0 released, IDP and integrator clients validated on `^1.4`  
**Branch:** `feature/2.x/claim-composition` → merge to `main` → tag `v2.1.0`  
**Base:** `v2.0.0` (`dff76a4` / `origin/main`)  
**Goal:** Port v1.4.0 claim-composition feature set to 2.0 ontology; migrate clients to `^2.0`; retire `1.x`.

---

## 1. When to start

Start v2.1.0 work only after:

- [ ] `v1.4.0` tagged and on Packagist
- [ ] IDP server updated and verified on `^1.4`
- [ ] All integrator clients verified on `^1.4`
- [ ] No outstanding P0/P1 bugs on `1.x` line

Then abandon `1.x` after successful `^2.0` migration (no further 1.x features except critical security fixes if needed).

---

## 2. What v2.1.0 is

**Not** a redesign. v2.1.0 is a **behavioral port** of v1.4.0 onto the 2.0 API:

| v1.4 (1.x) | v2.1 (2.x) |
|------------|------------|
| `ServiceIdentifier` / `getPrefix()` | `Orn\OrnPrefix` / `getPrefix()` |
| `OrkServices` | `Catalog\ServiceCatalog` |
| `getProviso()` / `getSegmentValue()` | `getSegment()` / `getValue()` |
| `Proviso` / `Grant` / `Condition` | `Orn\OrnSegment` / `Orn\Grant` / `Orn\Condition` |
| `ornSegmentSchema()` | `ornSegmentSchema()` (unchanged) |
| `ClaimBuilder::forPrefix(OrkServices::…)` | `ClaimBuilder::forPrefix(ServiceCatalog::…)` |
| `PolicyDocument` | Same concept, 2.x types internally |

Authorization semantics identical to v1.4.0.

---

## 3. Port strategy

### Option A — Cherry-pick + adapt (recommended)

1. Branch `feature/2.x/claim-composition` from `main`.
2. Cherry-pick v1.4 commits from `1.x` where files don’t overlap renamed types.
3. For conflicted files, port logic manually using [MIGRATION-2.0.md](MIGRATION-2.0.md) rename table.
4. Run full test suite on 2.x; add parallel tests using 2.0 type names.

### Option B — Reimplement from PLAN-v1.4.0

Use [PLAN-v1.4.0.md](PLAN-v1.4.0.md) as spec; implement directly on `main` with 2.0 names. Safer if v1.4 diff is large or entangled with 1.x-only code.

---

## 4. Scope (parity with v1.4.0)

| Deliverable | v2.1 notes |
|-------------|------------|
| `ClaimBuilder` | `OrnPrefix`, `OrnSegmentLabel`, `ServiceCatalog` |
| `PolicyBuilder` | Same fluent API |
| `Policy::merge()` | Same dedupe semantics |
| `PolicyDocument` | JWT envelope; 2.x `Policy` type |
| `PolicyFactory` | Delegate to builder (keep BC within 2.x) |
| Tests | Port all v1.4 IDP-shaped scenarios |
| README | 2.x examples only; link to MIGRATION-2.0 for upgraders |
| `docs/MIGRATION-2.0.md` | Add v1.4 → v2.1 claim-composition section |

### Also required for 2.x line

| Package | Action |
|---------|--------|
| `ork-iam-orn-definitions` | `main` at 2.0 API; pin `amtgard/ork-iam: ^2.1` |
| Packagist | Tag `v2.1.0` on `ork-iam` and definitions if needed |

---

## 5. API sketch (2.x)

```php
use Amtgard\IAM\Allowance\ClaimBuilder;
use Amtgard\IAM\Allowance\PolicyBuilder;
use Amtgard\IAM\Allowance\PolicyDocument;
use Amtgard\IAM\Catalog\ServiceCatalog;

$claim = ClaimBuilder::forPrefix(ServiceCatalog::Attendance)
    ->segment(ServiceCatalog::Configuration, 1)
    ->segment('tenant-id', 7)
    ->resource('ORK', 'AddAttendance')
    ->build();

$policy = PolicyDocument::fromVerifiedPayload($jwtPayload)->policy();

$effective = PolicyBuilder::from($globalPolicy)
    ->merge($integratorPolicy)
    ->build();
```

---

## 6. Implementation phases

### Phase 0 — Prep (0.5 day)

- [ ] Confirm `main` at `v2.0.0`
- [ ] Create `feature/2.x/claim-composition` from `main`
- [ ] Diff `1.x` `v1.4.0` against `v1.3.0` — list files to port
- [ ] Ensure `docs/MIGRATION-2.0.md` exists on `main` (restore from 2.0 merge if missing)

### Phase 1 — Core port (1–2 days)

- [ ] Port `PolicyBuilder`, merge, dedupe
- [ ] Port `ClaimBuilder` with 2.x segment types
- [ ] Port `PolicyDocument`
- [ ] Update `Policy`, `PolicyFactory`

### Phase 2 — Tests (1 day)

- [ ] Port `ClaimBuilderTest`, `PolicyBuilderTest`, `PolicyDocumentTest`
- [ ] Port IDP JWT fixtures
- [ ] Full suite green; coverage ≥ 95% on new code

### Phase 3 — Definitions + docs (0.5 day)

- [ ] Verify `ork-iam-orn-definitions` on `main` works with builders
- [ ] README 2.x JWT section
- [ ] MIGRATION-2.0: “Upgrading from 1.4 to 2.1” snippet

### Phase 4 — Release and client migration (your timeline)

- [ ] Merge to `main`, tag `v2.1.0`
- [ ] Packagist update
- [ ] Migrate IDP to `^2.1`
- [ ] Migrate integrators to `^2.1`
- [ ] Archive `1.x` branch (read-only) after migration sign-off

---

## 7. v1.4 → v2.1 client migration cheatsheet

```php
// Prefix / catalog
OrkServices::Attendance        → ServiceCatalog::Attendance
ServiceIdentifier::from($s)  → OrnPrefix::from($s)
$claim->getServiceIdentifier() → $claim->getPrefix()

// Segments
$claim->getProviso($label)     → $claim->getSegment($label)
$proviso->getId()              → $segment->getValue()
$proviso->getSegmentLabel()    → $segment->getLabel()

// composer.json
"amtgard/ork-iam": "^1.4"     → "^2.1"
"amtgard/ork-iam-orn-definitions": "^1.x" → "^2.0"
```

`PolicyDocument` / `PolicyBuilder` / `ClaimBuilder` method names stay the same where possible.

---

## 8. Test parity matrix

Every test in [PLAN-v1.4.0.md §7](PLAN-v1.4.0.md#7-test-scenarios-idp-shaped) must have a 2.x equivalent (T1–T7).

Additional 2.x-only checks:

| # | Scenario | Assert |
|---|----------|--------|
| T8 | Builder uses `ServiceCatalog` enum | Compiles, same ORN output as 1.x |
| T9 | `OrnPrefix` custom integrator prefix | Still works with custom registration |
| T10 | Cross-version `Policy::toJson()` | 1.4 and 2.1 produce identical JSON for same ORN inputs |

---

## 9. Release checklist

- [ ] v1.4 consumers signed off
- [ ] PR `feature/2.x/claim-composition` → `main`
- [ ] CI green on `main`
- [ ] Tag `v2.1.0`
- [ ] GitHub Release with migration notes
- [ ] Packagist refresh
- [ ] `ork-iam-orn-definitions` tag if bumped
- [ ] IDP + integrators on `^2.1`
- [ ] `1.x` branch marked maintenance-only / archived

---

## 10. Timeline (suggested)

| Milestone | Depends on |
|-----------|------------|
| v1.4.0 complete | This plan’s 1.x work |
| Client validation (1.x) | v1.4.0 + your IDP/integrator testing |
| v2.1.0 development start | Client validation green |
| v2.1.0 release | Port + CI |
| 1.x retirement | All clients on `^2.1` |

**Do not** parallelize v1.4 and v2.1 implementation — port only after 1.x behavior is proven.

---

## 11. Reference documents

- [CHECKLIST-1X-2X-SPLIT.md](CHECKLIST-1X-2X-SPLIT.md) — branch/tag/Packagist validation
- [PLAN-v1.4.0.md](PLAN-v1.4.0.md) — feature spec and IDP model
- [ORN-ONTOLOGY.md](ORN-ONTOLOGY.md) — vocabulary (on `1.x`; extended on `main`)
- `MIGRATION-2.0.md` — on `main` only; type rename reference for port
