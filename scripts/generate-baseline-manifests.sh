#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
BASELINE_REF="${1:-pre-commercial-transformation-20260908}"
OUT_DIR="${ROOT}/docs/baseline"

mkdir -p "${OUT_DIR}"

{
  echo "ZawajAfrica protected baseline source manifest"
  echo "Reference: ${BASELINE_REF}"
  echo "Commit: $(git -C "${ROOT}" rev-parse "${BASELINE_REF}^{commit}")"
  echo
  git -C "${ROOT}" ls-tree -r --long "${BASELINE_REF}"
} > "${OUT_DIR}/SOURCE_MANIFEST.txt"

{
  echo "ZawajAfrica protected baseline migration manifest"
  echo "Reference: ${BASELINE_REF}"
  echo
  git -C "${ROOT}" ls-tree -r --name-only "${BASELINE_REF}" -- database/migrations
} > "${OUT_DIR}/MIGRATION_MANIFEST.txt"

(
  cd "${ROOT}"
  CACHE_STORE=array SESSION_DRIVER=array QUEUE_CONNECTION=sync \
    php artisan route:list --json
) > "${OUT_DIR}/ROUTE_MANIFEST.json"

sha256sum \
  "${OUT_DIR}/SOURCE_MANIFEST.txt" \
  "${OUT_DIR}/MIGRATION_MANIFEST.txt" \
  "${OUT_DIR}/ROUTE_MANIFEST.json" \
  > "${OUT_DIR}/SHA256SUMS"

printf 'Generated baseline manifests in %s\n' "${OUT_DIR}"
