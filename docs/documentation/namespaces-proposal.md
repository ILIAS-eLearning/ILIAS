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
* Newly created interfaces MUST be suffixed by "*Interface*".
* Newly created abstract classes MUST be prefixed by "*Abstract*".
* Newly created traits MUST be suffixed by "*Trait*".
* Existing interfaces, abstract classes and traits SHOULD be renamed accordingly.
* Every Module and Service folder MUST have a subdirectory for every type of asset 
existing in this Module / Service. Possible asset types:
    * js
    * css
    * templates
    * images
    * docs
* The classes of each Module and Service SHOULD be *structured by feature*, i.e. multiple classes implementing the same feature/view should be located
 in the same subdirectory.
* Only classes and interfaces which provide functionality to other Components SHOULD be located in the Component's top level directory.

*Examples:*
* Hypothetical Folder Structure:

		src/
		  Modules/
		    Course/
		      Object/
		        ObjCourse.php
		        ObjCourseGUI.php
		        ObjCourseAccess.php
		      Administration/
		        CourseAdministration.php
		        CourseAdministrationAccess.php
		        CourseAdministrationGUI.php
		      Participant/
		        Member/
		          CourseMember.php
		          CourseMembers.php
		          CourseMemberGUI.php
		          CourseMemberTableGUI.php
	            Tutor/
	              CourseTutor.php
	              CourseTutors.php
	              CourseTutorGUI.php
	              CourseTutorTableGUI.php
	            AbstractCourseParticipantGUI.php
	            AbstractCourseParticipantTableGUI.php  
	            AbstractCourseParticipant.php
		      CourseParticipantFactory.php
	          CourseParticipantInterface.php
	          CourseProvider.php
		      ...
		      js/
		        participants.js
		        ...
		      css/
		        participants_table.css
		        ...
		      templates/
		        tpl.participant_row.html
		        ...
* The class *ilObjCourse* would be renamed *ObjCourse*.
* The file *class.ilObjCourse.php* would be renamed *ObjCourse.php*.
* The file would be moved to *src/Modules/Course/Object*.
* The namespace would be *ILIAS\Modules\Course\Object*.
* Any JavaScript files used in the Course Module would be located at *src/Modules/Course/js/*.
* All classes which are used to handle the participants would be located at *src/Modules/Course/Participants*. 
* A (hypothetical) class *CourseParticipantsFactory* - which can be used by other Components - should be located at *src/Modules/Course*.

### Autoloading & Class Imports

* The composer autoloader (located at vendor/autoload.php) MUST be used for autoloading.
* The autoloader MUST be included in every entry point class (index.php, ilias.php, ...).
* Include statements (i.e. "require", "require_once", "include", "include_once") are thereby unnecessary and MUST NOT be used anywhere outside 
of the entry point classes.
* Existing include statements outside of the entry point classes MUST be removed.
* The required dependencies of a class (outside of its own namespace) MUST be imported via '*use*' statements at 
the beginning of the class definition. This helps to get a good overview over all dependencies of a class.
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

The migration can be divided into three phases:

#### 1: Move Library-Like Components

The *./src* directory is currently used for library-like components only. This will change and to keep a good structure, all these components must be moved to a new subdirectory: *./src/libs*

After creating this new subdirectory, the following steps must be done for each component:

* Move the whole component from *./src* to *./src/libs*
* Change each namespace of the component from *ILIAS\\[component]\\[possible_subnamespaces]* to *ILIAS\\libs\\[component]\\[possible_subnamespaces]*
* Search for all occurrences of the namespaces and adapt these as well

##### Procedure

Each maintainer of these components must migrate their component(s), but can do this at any given time. After this is done, a Pull Request must be provided. Since the Pull Request is likely to contain changes in other maintainers components, these maintainers must be notified and have to review the changes as soon as possible.

#### 2: General Revision

As second step, the central Services must be adapted to work with migrated components as well as with components still to be migrated. This allows a coexistence of the current and the future state and therefore supports a step-by-step migration of the Modules and Services. 

The Revision consists of the following steps:

* Revise ilCtrl, ilObjectFactory, ilObjectDefinition & ilRepositoryGUI to work both with namespaces and with the old structure
* Move composer.json (and all corresponding files) to the root directory and adjust paths inside the file accordingly.
* Include the autoloader in all entry point classes.

##### Procedure

The General Revision must be done in a single Pull Request, together with the migration of one Module or Service (see below) to test this revision. It must then be thoroughly tested before starting the third phase.

#### 3: Migration of Modules and Services

After the general revision, each Module and Service must be migrated. 

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

##### Procedure
    
Since the General Revision supports both the old and the new structure, maintainers can decide for themselves when they want to migrate their component. After a component is migrated, the maintainer provides a single Pull Request with all changes. Since these Pull Requests are likely to contain changes in other maintainers components, the providing maintainer must notify the affected maintainers which in return will review the changes as soon as possible.

To avoid conflicts, the maintainers must inform the community about the intention to migrate a component at the Jour Fixe. Strongly connected components can thereby be coordinated in such a way that they are not migrated at the same time.
    
### Possible Problems

* The renaming of classes and replacing of include statements must be done and tested thoroughly in order to not break
any dependencies.
* The structure of the whole project will be changed, i.e. cherry-picking a commit to older versions will not work, providing plugins with multi version compatibility will be harder, etc. These problems will disappear with the release of a next version, though.