# Baseline Verification Record

**Verification date:** 8 September 2026  
**Legacy source commit:** `e5d1e7a419d16882fb1a3abe41a08bda5481d606`

## Verification results

| Control | Result |
|---|---|
| Protected source tag | `pre-commercial-transformation-20260908` resolves to the legacy source commit. |
| Source, migration, and route manifests | Generated under `docs/baseline/` with SHA-256 checksums. |
| SQLite clean migration | All historical migrations plus the reconciliation migration completed successfully. |
| Database backup | SQLite backup and SHA-256 checksum were created outside the repository. |
| Database restore | The backup restored to an isolated SQLite database and matched the source byte-for-byte. |
| Laravel regression suite | 25 tests passed with 62 assertions. |
| Frontend production build | Vite production build completed successfully in 6.55 seconds. |

## Compatibility repairs

The legacy repository contained repair migrations dated before the tables they modify. Fresh SQLite installation failed before reaching the matrimonial schema. The historical migrations were retained and made conditional on table and column availability. A late additive reconciliation migration now applies the intended fields and indexes after all legacy tables exist.

SQLite also requires index names to be database-global, while the historical index names assumed MySQL table-local naming. The compatibility helpers retain the established names on MySQL/MariaDB and use table-prefixed names on SQLite. Equivalent existing indexes are detected by their columns to avoid duplicate indexes during upgrades.

The legacy password-reset controller bypassed Laravel's password broker and coupled account recovery directly to one mail provider. It now uses the framework password broker, preserving buyer-configurable mail delivery and allowing notification tests. Existing registration and root-route tests were aligned with actual onboarding behavior, and feature tests disable Vite asset resolution because the production frontend build is validated separately.

## Scope

This verification establishes a safe reconstruction starting point. It does not claim that the commercial API, mobile application, provider-neutral billing, role-based administration, excluded-module removal, or CodeCanyon packaging are complete.
