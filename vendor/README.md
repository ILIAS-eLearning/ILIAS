# Third-Party-Libraries and Frameworks

Two mechanisams to integrate external libraries/dependencies are used in ILIAS:
* External dependencies of the PHP code are managed through [composer](https://getcomposer.org/),
stored under `./libs/composer/vendor`, and defined in the file `composer.json`
in the webroot of ILIAS. For more details on composer see [composer-readme](composer/README.md).
* External dependencies of the Javascript code are managed through [npm](https://www.npmjs.com/),
stored under `npm_modules`, and defined in the file `package.json` in the
webroot of ILIAS. For more details on npm see [npm-readme](../docs/development/js-libraries.md).
* External dependencies of the Java code are managed through [maven](https://maven.apache.org).
They are only used in `Services\WebServices\RPC` and are in the full and sole
responsibility of the corresponding maintainer.


No other external dependencies are allowed in ILIAS. Should you find an older
dependency in the folder of your [component](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/components-and-directories.md)
you MUST remove it immediately and either replace it by the corresponding version
from one of the officially allowed dependeny managers (subject to approval by
the Jour Fix, see below) or provide the corresponding functionality yourself.

## Adding a New Dependency to ILIAS
Adding new dependencies to ILIAS depends on the approval of the Jour Fix and
all dependencies MUST be reaproved for every new ILIAS version.
If you want to add a new dependency to ILIAS:
* Provide a PR against the corresponding file (`composer.json`, `package.json`) in
the trunk-branch of ILIAS. Make sure to fill in all required information (see below).
* Tag the PR with the tags "jour fixe" and "dependencies".
* You will need to make the case at the next Jour Fixe why the corresponding
functionality is best integrated through this dependency and why we can trust
this dependency to be properly maintained in the future.

## Criteria for Accepting Dependencies
* The corresponding functionality is needed.
* The corresponding functionality can not be easily implemented without the
use of a external dependency/library.
* The corresponding functionality should be maintained and used in a more
extensive community than possible inside ILIAS.
* The dependency is maintained and used by a more extensive community than
available inside ILIAS.
* The dependency is actively maintained.
* The most current version of the dependency can be and is used.
* The dependency ist pinned to a major version. Minor version updates will happen
automatically on each minor update of ILIAS.

## Re-evaluating Dependencies on Major Updates of ILIAS
All external dependencies are re-evaluated on every major update of ILIAS. The
[Criteria for Accepting Dependencies](#criteria-for-accepting-dependencies) apply.

### Process for Re-Evaluation of Dependencies
* As soon as the beta branch of ILIAS has been split from `trunk` two files
`composer_new.json` and `packages_new.json` are created in `trunk` containing a
skeleton json.
* Each ILIAS maintainer creates a PR against `composer_new.json` and/or
`packages_new.json` for the dependencies s/he will need and take responsibility
for. S/he also adds an entry to the corresponding section of the agenda for the
corresponding Jour Fixe (see next point). The entry contains the name of the
dependency, the used version, an explanation specifying why the dependency is
needed, and her/his name. A template is provided in the corresponding agenda.
* The second Jour Fixe after the branch is split up is mostly dedicated to
reviewing the list of dependencies. The Technical Board is responsible for
organising it.
* If a previously used dependency is either not added to the list anymore, or if
nobody can be found to take care of a dependency, or if a dependency does not
conform to the [Criteria for Accepting Dependencies](#criteria-for-accepting-dependencies)
the dependency is removed.
* The Technical Board merges the accepted PRs after the corresponding Jour Fixe.
* Maintainers are asked to find solutions for the dependencies that where removed
until four weeks after the dependencies have been discussed at the Jour Fixe.
* Four weeks after the dependencies have been discussed at the Jour Fixe the
Technical Board replaces the `composer.json` and `packages.json` file in the
`trunk` branch with the corresponding `composer_new.json` and `packages_new.json`.

## Deprecated Locations

- **Deprecated**: PHP libraries installad manually, located in some /Services/\*/libs and /Modules/\*/libs directories
- **Deprecated**: JS- and client-side libraries installed using bower, see [bower-readme](bower/README.md)
- **Deprecated**: JS- and client-side libraries installed manually, currently also located in /Services\/* and /Modules\/*
