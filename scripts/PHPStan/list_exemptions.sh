#!/bin/bash

# Lists every rule-violation exemption granted in the code base, with the reason,
# the ILIAS major it was granted for, and whether it still counts.
#
# Exemptions expire with the next major (see scripts/PHPStan/README.md), so this is
# the list to walk through when the version is bumped.
#
# Usage:
#   scripts/PHPStan/list_exemptions.sh                      # all components
#   scripts/PHPStan/list_exemptions.sh components/ILIAS/Form
#   scripts/PHPStan/list_exemptions.sh --version=13         # what expires in ILIAS 13
#
# Exits non-zero when an exemption has expired or carries no version.

php -dxdebug.mode=off scripts/PHPStan/list_exemptions.php "$@"
