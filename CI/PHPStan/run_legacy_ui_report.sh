#!/bin/bash

RE="(Services|Modules)"
CONFIG=CI/PHPStan/legacy_ui.neon
MEMORY_LIMIT=2G
REPORT_FORMAT=csv
REPORT_DIRECTORY=Reports

# Create the report directory if it doesn't exist
mkdir -p ${REPORT_DIRECTORY}

# Check for Directory as Script-Parameter
if [ -d "$1" ]; then
    REPORT_DIRECTORIES=($1)
else
    # Find all directories matching the regex (depth 2)
    REPORT_DIRECTORIES=($(find . -type d -maxdepth 2 -print0 |
    xargs -0I % echo "%" |
    grep -oE "${RE}\/.*" |
    tr "\n" "\0" |
    xargs -0))
fi

# Run PHPStan for each directory
for i in "${REPORT_DIRECTORIES[@]}";
do
  echo "Running LUI-Report on ${i}"
  php -dxdebug.mode=off libs/composer/vendor/bin/phpstan analyse -c "${CONFIG}" -a libs/composer/vendor/autoload.php --no-progress --no-interaction --memory-limit=${MEMORY_LIMIT} --error-format=${REPORT_FORMAT} "$i" > "${REPORT_DIRECTORY}/${i//\//_}.csv" || true;
done

cat ${REPORT_DIRECTORY}/*.csv | awk '!a[$0]++' > "${REPORT_DIRECTORY}/Summary.csv"
