# Checklist: 1.x / 2.x split validation and remediation

Use this checklist to confirm GitHub and Packagist are correctly set up for parallel **1.x** (maintenance) and **2.x** (current) development on `amtgard/ork-iam`.

**Target layout**

| Line | Git branch | Latest tag | Composer constraint |
|------|------------|------------|---------------------|
| 1.x | `1.x` | `v1.3.0` (→ `v1.4.0`, …) | `"amtgard/ork-iam": "^1.3"` |
| 2.x | `main` | `v2.0.0` (→ `v2.1.0`, …) | `"amtgard/ork-iam": "^2.0"` |

---

## 1. GitHub — branches

| # | Check | Expected | Remediation if failed |
|---|-------|----------|------------------------|
| 1.1 | `origin/main` exists and is default branch | Yes | Settings → Branches → Default: `main` |
| 1.2 | `origin/1.x` exists | Yes | `git push -u origin 1.x` from tag `v1.3.0` |
| 1.3 | `main` tip matches `v2.0.0` | `git rev-parse v2.0.0` == `origin/main` | Tag `v2.0.0` on merge commit or fast-forward `main` |
| 1.4 | `1.x` tip matches `v1.3.0` | `git rev-parse v1.3.0` == `origin/1.x` | Reset `1.x` to `v1.3.0` and force-push only if no releases were made from a wrong tip |
| 1.5 | `1.x` is an ancestor of `main` | `git merge-base --is-ancestor origin/1.x origin/main` | Normal; 2.x builds on 1.3 |
| 1.6 | Feature branch naming | `feature/1.x/<name>` → base `1.x`; `feature/<name>` → base `main` | Avoid `1.x/feature/...` (conflicts with branch ref `1.x`) |

**Verify locally**

```bash
git fetch origin --tags
git log --oneline -1 origin/main origin/1.x
git rev-parse v1.3.0 v2.0.0
git merge-base --is-ancestor origin/1.x origin/main && echo "OK: 1.x ancestor of main"
```

---

## 2. GitHub — tags and releases

| # | Check | Expected | Remediation |
|---|-------|----------|-------------|
| 2.1 | Tags `v1.3.0` and `v2.0.0` on remote | Present | `git push origin v1.3.0 v2.0.0` |
| 2.2 | GitHub Releases for both tags | Optional but recommended | Create releases with changelog links |
| 2.3 | `v1.0.0` tag consistency | Same commit, possibly different annotated tag object | Harmless fetch warning; see §6 |
| 2.4 | No `v2.x` tags on `1.x` branch only | 2.x tags point at `main` | Retag if misplaced |
| 2.5 | Stale feature branches | Merged branches can be deleted | Remove `feature/orn-segment-ontology-prep`, `feature/2.0-ontology`, etc. after merge |

---

## 3. GitHub — branch protection and workflow

| # | Check | Expected | Remediation |
|---|-------|----------|-------------|
| 3.1 | `main` protected | PR required, CI green | Branch protection rules |
| 3.2 | `1.x` protected | PR required, CI green | Same for maintenance line |
| 3.3 | CI runs on PRs to both branches | PHPUnit passes on `1.x` and `main` | Add / fix workflow `on: pull_request` branches |
| 3.4 | README states branching model | 1.x PRs → `1.x`; 2.x PRs → `main` | Add short “Branching” section |

---

## 4. Packagist — `amtgard/ork-iam`

| # | Check | Expected | Remediation |
|---|-------|----------|-------------|
| 4.1 | Single package, auto-update webhook | Push/tag triggers update | Packagist → package → set GitHub hook |
| 4.2 | `v1.3.0` indexed | `composer show amtgard/ork-iam:1.3.0` works | Manual “Update” on Packagist |
| 4.3 | `v2.0.0` indexed | `composer show amtgard/ork-iam:2.0.0` works | Same |
| 4.4 | `^1.3` resolves to 1.x tags only | No 2.0.x in range | Correct if only v1.3.0 is latest 1.x |
| 4.5 | `^2.0` resolves to 2.x tags only | v2.0.0+ | Correct after v2.0.0 indexed |
| 4.6 | Branch aliases (optional) | `dev-main` → `2.1-dev`, `dev-1.x` → `1.4-dev` | Add `extra.branch-alias` per branch (see §5) |

**Verify in a clean project**

```bash
composer require amtgard/ork-iam:^1.3 --no-install 2>&1 | grep versions
composer require amtgard/ork-iam:^2.0 --no-install 2>&1 | grep versions
```

---

## 5. Composer — branch aliases (recommended)

Add on each branch before tagging next minors:

**On `1.x` (`composer.json`):**

```json
"extra": {
  "branch-alias": {
    "dev-1.x": "1.4-dev"
  }
}
```

**On `main`:**

```json
"extra": {
  "branch-alias": {
    "dev-main": "2.1-dev"
  }
}
```

---

## 6. Known issue — `v1.0.0` fetch clobber

**Symptom:** `git fetch origin --tags` reports `would clobber existing tag v1.0.0`.

**Cause:** Local and remote annotated tags point at the same commit (`e24be9b`) but have different tag object SHAs.

**Impact:** None for 1.x/2.x split work. Branches and newer tags fetch normally.

**Remediation (optional):**

```bash
git tag -d v1.0.0
git fetch origin tag v1.0.0
```

Or ignore the warning.

---

## 7. Companion package — `amtgard/ork-iam-orn-definitions`

| # | Check | Expected | Remediation |
|---|-------|----------|-------------|
| 7.1 | `1.x` branch exists | Mirror `ork-iam` at v1.3-era API | Branch from tag/commit compatible with `ork-iam` 1.3 |
| 7.2 | `main` at 2.0 API | `ServiceCatalog`, `ornSegmentSchema()` | Merge 2.0 ontology port |
| 7.3 | Version constraints aligned | `ork-iam ^1.3` + definitions `^1.x`; `ork-iam ^2.0` + definitions `^2.0` | Pin in each package’s `composer.json` |
| 7.4 | Packagist tags for definitions | Matching `v1.x` / `v2.x` tags | Tag and push |

**Current gap (as of validation):** definitions repo may only have `main`. Create and push `1.x` before consumer validation.

---

## 8. Consumer validation matrix

| Consumer | Current constraint | Validate on | Pass criteria |
|----------|-------------------|-------------|---------------|
| IDP server | `^1.3` | `v1.3.0` / `1.x` | Auth flows, JWT policy lines |
| 3rd-party integrators | `^1.3` | `v1.4.0` after release | ORN eval with global + custom lines |
| (future) all clients | `^2.0` | `v2.1.0` on `main` | Port from 1.4 feature set |

---

## 9. Remediation priority order

If anything is wrong, fix in this order:

1. **Confirm tag ↔ branch tips** (`v1.3.0` = `1.x`, `v2.0.0` = `main`).
2. **Push missing remote branch** (`1.x` if absent).
3. **Packagist update** — refresh package, confirm both tags install.
4. **Branch protection + CI** on `1.x` and `main`.
5. **Definitions repo** — add `1.x` branch and matching tags.
6. **Branch aliases** in `composer.json` on each line.
7. **Clean up** merged feature branches and document branching in README.
8. **Optional** — align `v1.0.0` tag object, GitHub Releases, homepage URL in `composer.json` (currently points at unrelated repo).

---

## 10. Sign-off checklist

- [ ] `origin/1.x` at `v1.3.0`
- [ ] `origin/main` at `v2.0.0`
- [ ] Packagist installs `^1.3` and `^2.0` correctly
- [ ] CI green on both branches
- [ ] `ork-iam-orn-definitions` has matching `1.x` / `main` lines
- [ ] README documents branch + release policy
- [ ] IDP / integrator clients confirmed on `^1.3` before `v1.4.0` work merges

**Owner:** _______________  
**Validated date:** _______________
