#!/usr/bin/env bash
set -uo pipefail
repo_root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$repo_root"
mode="${1:-native}"
if [[ "$mode" != native && "$mode" != --docker ]]; then
    echo "Usage: bash scripts/check.sh [--docker]" >&2
    exit 2
fi

frontend() {
    if [[ "$mode" == --docker ]]; then
        docker run --rm -v "$repo_root/frontend:/frontend" -w /frontend node:14.21.3-buster npm "$@"
    else
        (cd frontend && npm "$@")
    fi
}
backend() {
    if [[ "$mode" == --docker ]]; then
        docker compose exec -T backend php "$@"
    else
        (cd backend && php "$@")
    fi
}

backend_lint() {
    if [[ "$mode" == --docker ]]; then
        docker compose exec -T backend sh -c 'find app config database routes scripts tests -name "*.php" -print0 | xargs -0 -n1 php -l >/dev/null'
    else
        (cd backend && find app config database routes scripts tests -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null)
    fi
}

status=0
run() {
    printf '\nRunning: %s\n' "$*"
    if ! "$@"; then status=1; fi
}

# Remove only summary inputs so failed runs cannot display old coverage totals.
rm -f frontend/coverage/coverage-summary.json backend/coverage/clover.xml || exit 1
run python3 scripts/test-ci.py
run frontend run typecheck
run backend_lint
run backend vendor/bin/phpstan analyse --no-progress --memory-limit=512M
run frontend run test:coverage
run backend vendor/bin/phpunit --coverage-text --coverage-html coverage/html --coverage-clover coverage/clover.xml
run python3 scripts/coverage-summary.py
exit "$status"
