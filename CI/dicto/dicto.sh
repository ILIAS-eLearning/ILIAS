#!/bin/bash

# Dicto
#
# Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE
#
# Author Laura Herzog <laura.herzog@concepts-and-training.de>
#
# dicto.php helps you to maintain or enforce architectural rules in you
# software project. Take for example an application that should implement
# the MVC pattern. This tool uses dicto to check the ILIAS code with a
# certain ruleset and parses the results in a csv. This will be send to
# the ILIAS CI Results repository and can be viewed there.

# Travis mandatory packages:
# hhvm, sqlite

# Import the important stuff
source ./CI/Import/Functions.sh
source ./CI/Import/Variables.sh

# move to CI folder
cd CI/dicto

# clone the dicto-php
git clone https://github.com/lechimp-p/dicto.php.git dicto-php
cd dicto-php && composer install && cd ..

# clone the CI project because we need the old database of dicto
git clone https://github.com/ILIAS-eLearning/CI-Results.git ci-results
if [[ ! -f "ci-results/data/dicto_latest.sqlite" ]];
then
  cp "ci-results/data/dicto_latest.sqlite" "results/results.sqlite"
fi

# Run the analyser
php dicto-php/dicto.php analyze config.yaml

# Convert to CSV
sqlite3 results/results.sqlite <<!
.headers on
.mode csv
.output results/result_rules.csv
SELECT * FROM "main"."rules";
!

sqlite3 results/results.sqlite <<!
.headers on
.mode csv
.output results/result_runs.csv
SELECT * FROM "main"."runs";
!

sqlite3 results/results.sqlite <<!
.headers on
.mode csv
.output results/result_variables.csv
SELECT * FROM "main"."variables";
!

sqlite3 results/results.sqlite <<!
.headers on
.mode csv
.output results/result_violation_locations.csv
SELECT * FROM "main"."violation_locations";
!

sqlite3 results/results.sqlite <<!
.headers on
.mode csv
.output results/result_violations.csv
SELECT * FROM "main"."violations";
!

# merge into dicto_latest.csv
if [[ ! -f "results/dicto_latest.csv" ]]
then
  touch "results/dicto_latest.csv"
else
  rm "results/dicto_latest.csv"
  touch "results/dicto_latest.csv"
fi

# prepare the results and move them to the global results
cat results/result_*.csv > "results/dicto_latest.csv"
cp results/dicto_latest.csv "$RESULTS_DATA_DIRECTORY_DICTO_CSV"
cp results/results.sqlite "$RESULTS_DATA_DIRECTORY_DICTO_DB"
