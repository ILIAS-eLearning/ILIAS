# How to add dependencies with composer in ILIAS

**New dependencies need to be approved by the Jour Fixe of the ILIAS society.**

## Dependencies for production
- Comment all lines in libs/.gitignore
- Add a new library using composer, e.g. "composer require filp/whoops"
- Documents the usage and your wrapper class in composer.josn, e.g.:
```json
"filp/whoops" : {
  "source" : "github.com/filp/whoops",
  "used_version" : "v2.1.0",
  "wrapped_by" : null,
  "added_by" : "Denis Klöpfer <denis.kloepfer@concepts-and-training.de>",
  "last_update" : "2016-03-22",
  "last_update_by" : "Jörg Lützenkirchen <luetzenkirchen@leifos.com>"
},
```

- Run "composer install --no-dev"
- Add all files to ILIAS git-repository and commit
- Run "composer install" to reinstall dev-dependencies and build autoload.php
- ignore all dependencies installed now (which are dev-requirements only)
- add and commit changes in libs/.gitgnore and autoload.php etc.

## Dependencies for development
- Add a new library using composer, e.g. "composer require --dev phpunit/phpunit" 
- Ignore all directories which are added by installation (uncomment existing)
- commit changes in autoload.php etc.

## Update a single dependency
- Search the name of dependency you like to update.
- Update by using "composer update --no-dev <DEPENDENCIE_NAME>"
- Commit all changes in composer.lock and the vendor folder

# Updating Class-Map
The composer classmap holds information (besides the PHP-dependencies) on all ILIAS-classnames and 
provied autoloading for them. There is no need to require_once or include_once ILIAS-Classes 
anymore.
Whenever you introduce a new ILIAS-Class in /Services\/* or /Modules\/*, the classmap should be 
updated using 
```bash
$ composer dump-autoload
```