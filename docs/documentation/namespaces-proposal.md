# Namespaces Proposal

This document contains a proposal for a guideline on how to expand the usage of 
php namespaces to ILIAS modules and services. The suggested approach will elaborate 
the following aspects:
* naming conventions
* integration of composer and autoloading
* handling of subdirectories and interfaces
* migration of the current structure
* possible problems

## Guideline

### Naming & Structuring

* The naming and structuring of all Namespaces MUST follow the PHP Standards Recommendation PSR-4: https://www.php-fig.org/psr/psr-4/.
* The base prefix namespace is *ILIAS* and is pointing at the subdirectory */src*. Therefore, all Modules and Services MUST be located in the directory */src*. 
* The subdirectory */classes* for Modules and Services will not be needed anymore and MUST be removed.
* The prefixes "*class.*" and "*il*" for ILIAS classes will not be needed anymore and MUST be removed.
* Newly created interfaces, abstract classes and traits MUST follow the PSR Naming Conventions: https://www.php-fig.org/bylaws/psr-naming-conventions. Already existing interfaces, abstract classes and traits SHOULD be renamed.
* Every Module and Service folder MUST have a subdirectory for every type of asset (js, css, templates, images, docs) 
existing in this Module / Service.

*Examples:*

* The class *ilObjCourse* would be renamed *ObjCourse*.
* The file *class.ilObjCourse.php* would be renamed *ObjCourse.php*.
* The file would be moved to *src/Modules/Course/*.
* The namespace would be *ILIAS\Modules\Course*.
* Any JavaScript files used in the Course Module would be located at *src/Modules/Course/js/*.

### Autoloading & Class Imports

* The composer autoloader (located at vendor/autoload.php) MUST be used for autoloading.
* The autoloader MUST be included in every entry point class (index.php, ilias.php, ...).
* Include statements (i.e. "require", "require_once", "include", "include_once") MUST NOT be used anywhere outside 
of the entry point classes.
* Existing include statements outside of the entry point classes MUST be removed.
* The required dependencies of a class (outside of its own namespace) MUST be imported via '*use*' statements at 
the beginning of the class definition.
* The '*use*' statements SHOULD always import the absolute namespace of a file.
* Fully-qualified names to call a class MUST never be used. 
* Ambiguous classes MUST be imported with aliases.
 
*Examples:*

* Correct "*use*" statement: 
    * *use ILIAS\Modules\Course\ObjCourse;*
* Incorrect "*use*" statement: 
    * *use ILIAS\Modules\Course;*
* Import ambiguous classes:
    * *use Pimple\Container as PimpleContainer;*
    * *use ILIAS\DI\Container as DIContainer;*
* Not allowed: 
    * *$objCourse = new \ILIAS\Modules\Course\ObjCourse();*

### Composer & Libraries

* The composer.json file MUST be located in the ILIAS root directory.
* All external libraries SHOULD be imported via composer.

*Examples:*
None required.


## Approach
 
### Migration

#### General

* Revise ilCtrl, ilObjectFactory & ilObjectDefinition to work both with namespaces and with the old structure (so that the migration of Modules and Services can be done step-by-step).
* Move composer.json (and all corresponding files) to the root directory and adjust paths inside the file accordingly.
* Include the autoloader in all entry point classes.

#### Migration of Modules and Services
The following steps must be done for each Module and Service:

* Move everything to */src* (or to the correspondent subdirectory, respectively)
* Remove the *classes* folder
* For each class:
    * rename class (remove prefixes from classes and class files)
    * add namespace 
    * search ILIAS for all occurrences of the class..
        * remove include statements and replace by *use* statements at the beginning of the class definition
        * rename all other occurrences to match the new class name 
    * move all assets to the specified asset type folder and correct the paths for these assets in code occurrences.
    
### Possible Problems

* The renaming of classes and replacing of include statements must be done and tested thoroughly in order to not break
any dependencies.
* The structure of the whole project will be changed, i.e. cherry-picking a commit to older versions will not work, providing plugins with multi version compatibility will be harder, etc. These problems will disappear with the release of a next version, though.