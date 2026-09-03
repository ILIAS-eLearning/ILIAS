#!/bin/bash

CONFIG=scripts/PHPStan/legacy_ui.neon
MEMORY_LIMIT=4G
REPORT_DIRECTORY=Reports

# Create the report directory if it doesn't exist
mkdir -p ${REPORT_DIRECTORY}

# Target directory: explicit script parameter, or all ILIAS components at once.
# A single PHPStan invocation analyses the whole tree in one process instead of
# booting PHPStan once per component (179 cold starts) — the CSV error format
# already groups every finding by component, so per-directory runs are redundant.
if [ -d "$1" ]; then
    TARGET="$1"
else
    TARGET="components/ILIAS"
fi

echo "Running LUI-Report on ${TARGET}"
php -dxdebug.mode=off vendor/composer/vendor/bin/phpstan analyse \
    -c "${CONFIG}" \
    -a vendor/composer/vendor/autoload.php \
    --no-progress \
    --no-interaction \
    --memory-limit=${MEMORY_LIMIT} \
    --error-format=csv \
    "${TARGET}" > "${REPORT_DIRECTORY}/Summary.csv" || true
