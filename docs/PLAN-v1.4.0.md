# Implementation plan: v1.4.0 (1.x — claim composition)

**Branch:** `feature/1.x/claim-composition` → merge to `1.x` → tag `v1.4.0`  
**Base:** `v1.3.0` (`71ce4c7`)  
**Audience:** IDP server and 3rd-party integrators (only consumers today)  
**Goal:** Fluent APIs to build and compose policies without hand-authoring ORN strings; primary path matches IDP JWT shape.

---

## 1. Problem statement

Today integrators must:

- Hand-write ORN strings, or
- Call `PolicyFactory::fromOrn(array $orns)` only

Gaps:

- No fluent claim/policy construction
- No first-class “effective policy” from JWT payload (global + integrator lines)
- No typed envelope for integrator document/blob alongside ORN lines
- `Policy` has no merge / add helpers

Authorization semantics stay unchanged: **any claim in the policy that satisfies the requirement grants access** (OR across claims; prefix must match per requirement).

---

## 2. IDP integration model (drives design)

The IDP issues a **signed JWT** where policy content is always **ORK-namespace** ORN lines:

| JWT content | Owner | Integrator control |
|-------------|-------|------------------|
| Built-in policy lines (`OrkServices` enum prefixes) | IDP / global | None |
| Custom policy lines for integrator | IDP | Config only (IDP signs) |
| Custom document/blob | IDP | Config only (opaque to ORN engine) |

**Trust rule:** Clients must build `Policy` from **verified JWT claims**, not from integrator-supplied lines alone. The library does not enforce “global lines present” — JWT verification does.

**Primary library path:**

```php
$policy = PolicyDocument::fromVerifiedJwt($payload)->policy();
```

Not client-side merge of untrusted partial policies.

`merge()` remains useful for tests, server-side assembly of trusted fragments, and tooling.

---

## 3. Scope

### In scope (v1.4.0)

| Deliverable | Description |
|-------------|-------------|
| `ClaimBuilder` | Fluent construction: prefix → segments → resource → `Claim` |
| `PolicyBuilder` | `addClaim()`, `addOrn()`, `build()` |
| `Policy::withClaims()` / merge | Union of claim sets (dedupe by canonical ORN string) |
| `PolicyDocument` | Wrapper: ORN policy + optional integrator blob |
| `PolicyDocument::fromOrnList()` | Parse IDP ORN line list |
| `PolicyDocument::fromJson()` | Round-trip for JWT `policy_lines` JSON array |
| Tests | Unit + IDP-shaped fixtures (global + integrator lines) |
| README | JWT integration section, builder examples |

### Out of scope (defer)

- JWT signature verification (stays in IDP client / `firebase/php-jwt` etc.)
- Requirement builders
- Changes to authorization semantics
- 2.x API renames (`OrnPrefix`, `ServiceCatalog`, …)
- `ork-iam-orn-definitions` changes unless builders need new resource maps

---

## 4. Proposed API (1.x)

### 4.1 ClaimBuilder

```php
use Amtgard\IAM\Allowance\ClaimBuilder;
use Amtgard\IAM\OrkServices;

$claim = ClaimBuilder::forPrefix(OrkServices::Attendance)
    ->segment(OrkServices::Configuration, 1)
    ->segment(OrkServices::Kingdom, 42)
    ->segment('tenant-id', 7)              // custom label (1.3)
    ->resource('ORK', 'AddAttendance')
    ->build();                              // Claim via ClaimFactory / OrnClassMap
```

- Each setter returns `$this` (fluent).
- Unset segments → empty string in ORN (existing semantics).
- `build()` validates via existing `Claim` / `OrkResourceName` init.

### 4.2 PolicyBuilder

```php
use Amtgard\IAM\Allowance\PolicyBuilder;

$policy = PolicyBuilder::create()
    ->addOrn('ORK:1:::::*')
    ->addClaim($claim)
    ->build();

// From existing policy
$combined = PolicyBuilder::from($policyA)
    ->merge($policyB)
    ->build();
```

**Merge semantics:**

- Union of claims from both policies
- Dedupe by `buildOrn()` canonical string (align with `Policy::toJson()` / `Policy::is()`)
- Order not significant (existing `asort` behavior)

### 4.3 PolicyDocument (JWT envelope)

```php
use Amtgard\IAM\Allowance\PolicyDocument;

$doc = PolicyDocument::fromOrnList($jwt['policy_lines']);
$doc = PolicyDocument::fromJson($jwt['policy_json']);   // JSON array of ORN strings
$doc = PolicyDocument::fromVerifiedPayload([
    'policy_lines' => [...],
    'integrator_document' => [...],  // optional, opaque array/object
]);

$policy = $doc->policy();             // Policy for isAuthorized()
$blob = $doc->integratorDocument(); // ?array, application-defined
```

### 4.4 PolicyFactory (backward compatible)

Keep `PolicyFactory::fromOrn()`; optionally delegate to `PolicyBuilder` internally.

---

## 5. File plan

| File | Action |
|------|--------|
| `src/Allowance/ClaimBuilder.php` | New |
| `src/Allowance/PolicyBuilder.php` | New |
| `src/Allowance/PolicyDocument.php` | New |
| `src/Allowance/Policy.php` | Add `merge()`, `getClaims()`, optional `fromBuilder` hook |
| `src/PolicyFactory.php` | Thin wrapper / delegate to builder |
| `tests/Allowance/ClaimBuilderTest.php` | New |
| `tests/Allowance/PolicyBuilderTest.php` | New |
| `tests/Allowance/PolicyDocumentTest.php` | New |
| `tests/Fixtures/IdpJwtPolicyFixture.php` | Global + integrator ORN lines |
| `README.md` | IDP / JWT / builder section |

---

## 6. Implementation phases

### Phase 1 — PolicyBuilder + merge (1–2 days)

- [ ] `PolicyBuilder` with `create()`, `from()`, `addOrn()`, `addClaim()`, `merge()`, `build()`
- [ ] `Policy::merge()` or package-private merge helper
- [ ] Dedupe by sorted ORN string
- [ ] Tests: merge global + integrator ORN lists into one effective policy
- [ ] Test: `isAuthorized()` succeeds if either global or integrator claim matches

### Phase 2 — ClaimBuilder (1–2 days)

- [ ] Fluent segment API (`OrkServices|string|ProvisoSlot`)
- [ ] Resource helper → `buildOrn()` → `ClaimFactory::createOrn()`
- [ ] Tests: built-in + custom segment labels
- [ ] Test: round-trip `build()` ORN equals hand-written ORN

### Phase 3 — PolicyDocument (1 day)

- [ ] `fromOrnList`, `fromJson`, optional `integrator_document`
- [ ] Tests mirroring IDP JWT payload shape
- [ ] Document that blob is not used in `isAuthorized()` unless app logic applies it

### Phase 4 — Docs and release (0.5 day)

- [ ] README examples
- [ ] CHANGELOG entry
- [ ] Coverage ≥ 95% on new code
- [ ] Merge PR to `1.x`
- [ ] Tag `v1.4.0`, Packagist update
- [ ] Update IDP + integrator clients on `^1.4`

---

## 7. Test scenarios (IDP-shaped)

| # | Scenario | Assert |
|---|----------|--------|
| T1 | JWT `policy_lines` = global ORK lines only | Policy authorizes ORK requirement |
| T2 | JWT lines = global + integrator-specific ORK lines | Same policy; either line can satisfy |
| T3 | Integrator line wrong prefix for resource | Global line still authorizes; integrator line ignored for that requirement |
| T4 | `merge(global, integrator)` equals `fromOrnList(combined)` | Same `toJson()` |
| T5 | Duplicate ORN in merge | Single claim in policy |
| T6 | `integrator_document` present | Exposed via getter; does not affect `isAuthorized()` |
| T7 | `ClaimBuilder` matches `ClaimFactory::createOrn()` | Same segments and resource |

---

## 8. Consumer migration (your rollout)

1. Release `v1.4.0` on Packagist.
2. IDP: no change if it only **emits** ORN strings; optional use of `PolicyDocument` in reference client.
3. Integrator services: replace hand-built ORN arrays with `PolicyDocument::fromVerifiedJwt()` after signature verify.
4. Run integrator test suite against `^1.4`.
5. Confirm before any 2.x port.

---

## 9. Release checklist

- [ ] PR `feature/1.x/claim-composition` → `1.x`
- [ ] All tests pass; coverage threshold met
- [ ] `composer.json` branch-alias `dev-1.x` → `1.4-dev` (optional)
- [ ] Tag `v1.4.0` on merge commit
- [ ] GitHub Release notes
- [ ] Packagist refresh
- [ ] IDP + integrators bumped to `^1.4`

---

## 10. Open decisions

| Decision | Recommendation |
|----------|----------------|
| Dedupe on merge | Yes, by canonical ORN string |
| `PolicyDocument` class name | `PolicyDocument` or `JwtPolicyEnvelope` — confirm with IDP claim names |
| JSON shape | Array of ORN strings (matches current `Policy::toJson()`) |
| Integrator blob type | `?array` (decoded JSON) in v1.4 |

Resolve JWT field names with IDP payload before Phase 3.
