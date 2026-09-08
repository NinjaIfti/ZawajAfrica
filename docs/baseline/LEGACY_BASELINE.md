# Protected Legacy Baseline

**Baseline commit:** `e5d1e7a419d16882fb1a3abe41a08bda5481d606`  
**Protected tag:** `pre-commercial-transformation-20260908`  
**Implementation branch:** `feat/commercial-release-rebuild`

## Purpose

This baseline preserves the last GitHub-published ZawajAfrica source before the worldwide commercial transformation. It is a recovery point, not a release candidate. The later release-candidate implementation described in project reports was not published and is therefore treated as an acceptance target rather than recoverable source.

## Protected product data

The transformation must preserve existing accounts, authentication state, profiles, profile extensions, preferences, ordered photos, primary-photo state, photo privacy settings, likes, matches, messages, notifications, reports, verification history, subscription history, payment history, and administration data.

Historical migrations remain part of the supported upgrade path. Compatibility edits may make a historical migration safe on a fresh install, but migrations must not be deleted or repurposed to destroy existing data. Data-bearing cleanup uses additive migrations and a tested database backup.

## Baseline recovery

Source recovery is available through the protected Git tag:

```bash
git fetch --tags origin
git switch --detach pre-commercial-transformation-20260908
```

To create a recoverable source archive:

```bash
git archive --format=tar.gz \
  --output=/secure/path/ZawajAfrica-e5d1e7a.tar.gz \
  pre-commercial-transformation-20260908
sha256sum /secure/path/ZawajAfrica-e5d1e7a.tar.gz
```

Database backup and restore are provided by `scripts/database-backup.php` and `scripts/database-restore.php`. Backups must be stored outside the public web root and outside release archives.

## Rollback policy

Application-code rollback uses a tagged release or the protected baseline. Database rollback for any data-bearing or destructive wave uses a verified pre-change backup. Additive compatibility migrations may intentionally use roll-forward recovery instead of dropping columns that could contain legacy data.

Before each removal wave:

1. Create and checksum a database backup.
2. Record the current application commit and migration status.
3. Restore the backup into an isolated database.
4. Run the full protected-core regression suite against the restored copy.
5. Apply the proposed upgrade and verify row counts and invariants.
6. Retain the prior application package until the post-upgrade observation window closes.

## Evidence boundary

The following are documented targets but are not present in this baseline commit: the complete `/api/v1` implementation, Expo mobile client, Stripe adapter, role-based administration, worldwide product configuration, and the expanded release-candidate test suite. They must be reconstructed and reverified before being claimed as delivered.
