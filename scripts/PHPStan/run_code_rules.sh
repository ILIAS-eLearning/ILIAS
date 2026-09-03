#!/bin/bash

# Runs the ILIAS custom code rules (policy gate) via PHPStan.
#
# Hard gate: exits non-zero on any violation, so CI fails the pull request.
# Genuinely unavoidable cases are exempted in the code (AllowRuleViolation
# attribute / inline @phpstan-ignore), not via a baseline.
#
# Environment variables:
#   ERROR_FORMAT   PHPStan --error-format to use. Default: "table" (human
#                  readable). CI sets "github" so violations show up as inline
#                  annotations on the pull request. Any PHPStan format works
#                  (table, github, json, checkstyle, ...).

# Never truncate the table output — with a large number of findings the table
# formatter otherwise collapses the list ("... and N more errors").
export PHPSTAN_TABLE_ERROR_FORMATTER_FORCE_SHOW_ALL_ERRORS=1

CONFIG=scripts/PHPStan/code_rules.neon
TMP_DIR=scripts/PHPStan/.cache/code-rules
MEMORY_LIMIT=4G
ERROR_FORMAT="${ERROR_FORMAT:-table}"

# Rule-violation allowances are granted per ILIAS major version, and the reported
# error identifiers carry that major. The result cache is keyed by file contents
# only, so a version bump would otherwise serve stale findings from the previous
# release. Drop the cache whenever the major changes.
ILIAS_MAJOR="$(sed -n 's/.*ILIAS_VERSION_NUMERIC[^0-9]*\([0-9]\{1,\}\).*/\1/p' ilias_version.php | head -1)"
STAMP="${TMP_DIR}/.ilias-major"
if [ ! -f "${STAMP}" ] || [ "$(cat "${STAMP}")" != "${ILIAS_MAJOR}" ]; then
    rm -rf "${TMP_DIR}"
    mkdir -p "${TMP_DIR}"
    printf '%s' "${ILIAS_MAJOR}" > "${STAMP}"
fi

# Target directory: explicit script parameter, or all ILIAS components at once.
if [ -d "$1" ]; then
    TARGET="$1"
else
    TARGET="components/ILIAS"
fi

# Informational line goes to stderr so `ERROR_FORMAT=json ... > out.json` stays pure JSON.
echo "Running ILIAS code rules on ${TARGET} (error-format: ${ERROR_FORMAT})" >&2
php -dxdebug.mode=off vendor/composer/vendor/bin/phpstan analyse \
    -c "${CONFIG}" \
    -a vendor/composer/vendor/autoload.php \
    --no-progress \
    --no-interaction \
    --memory-limit=${MEMORY_LIMIT} \
    --error-format="${ERROR_FORMAT}" \
    "${TARGET}"
