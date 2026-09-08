# Upgrade and Rollback Guide

## Supported starting point

The initial protected upgrade baseline is Git commit `e5d1e7a419d16882fb1a3abe41a08bda5481d606`, tagged as `pre-commercial-transformation-20260908`.

## Before every upgrade

1. Put the application into maintenance mode.
2. Record `git rev-parse HEAD` and `php artisan migrate:status`.
3. Create a database backup with `php scripts/database-backup.php --output=/secure/path`.
4. Copy user uploads and private storage to encrypted backup storage.
5. Verify the generated SHA-256 checksum.
6. Restore the backup into an isolated database and run the test suite against it.
7. Retain the previous application package and database backup until verification is complete.

## Upgrade sequence

```bash
composer install --no-dev --classmap-authoritative
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Queue workers must be restarted after deployment. Scheduler configuration must invoke `php artisan schedule:run` once per minute where scheduled jobs are enabled.

## Rollback policy

Code-only changes may be rolled back to the prior tagged release. Additive database changes remain in place unless their migration has a proven non-destructive `down()` method. Any removal wave or data transformation requires restoring the verified pre-upgrade database and storage backup instead of relying on destructive reverse migrations.

Use the restore command only after confirming the destination database:

```bash
php scripts/database-restore.php \
  --input=/secure/path/zawajafrica-backup \
  --confirm=RESTORE
```

After restoration, deploy the matching application tag, clear caches, restart workers, and run smoke tests.

## Legacy migration compatibility

Some historical repair migrations predate the tables they modify. They are retained for upgrade history but guarded on fresh installations. A later additive reconciliation migration applies the intended columns and indexes after the legacy tables exist. This avoids deleting migration history while permitting clean installations and legacy upgrades.
