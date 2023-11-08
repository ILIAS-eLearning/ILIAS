ILIAS Maintenance
=================
The development of the ILIAS source code is coordinated and maintained by a coordination team within the ILIAS 
network. Besides the main responsibilities for the project, several developers and users are maintaining certain 
modules of ILIAS.

# Special Roles

* **Product Management**: [Matthias Kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
* **Technical Board**: [Michael Jansen](https://docu.ilias.de/goto_docu_usr_8784.html), [Stephan Kergomard](https://docu.ilias.de/goto_docu_usr_44474.html), [Richard Klees](https://docu.ilias.de/goto_docu_usr_34047.html), [Nico Roeser](https://docu.ilias.de/goto_docu_usr_72730.html), [Fabian Schmid](https://docu.ilias.de/goto_docu_usr_21087.html)
* **Testcase Management**: [Fabian Kruse](https://docu.ilias.de/goto_docu_usr_27631.html)
* **Release Management**: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
* **Technical Documentation**: [Ann-Christin Gruber](https://docu.ilias.de/goto_docu_usr_94205.html)
* **Online Help**: [Alexandra Tödt](https://docu.ilias.de/goto_docu_usr_3139.html)

# Maintainers
We highly appreciate to get new developers but we have to guarantee the sustainability and the quality of the ILIAS 
source code. The system is complex for new developers and they need to know the concepts of ILIAS that are described 
in the development guide.
 
Communication among developers that are working on a specific component needs to be assured. Final decision about 
getting write access to the ILIAS development system (Github) is handled by the product manager.
 
ILIAS is currently maintained by two types of maintainerships:

- **Classic Model** with First Maintainer and sometimes Second Maintainer
- **[Coordinator Model](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md)**
 
The following rules must be respected for everyone involved in the programming of ILIAS for all components having a 
listed component maintainer (see below):

1. Decisions on new features or feature removals are made by the responsible first maintainer and the product manager 
in the Jour Fixe meetings after an open discussion.
2. Components under the Classic Model have a first and sometimes second maintainer. Code changes are usually done by the first maintainer. The first 
maintainer may forward new implementations to the second maintainer.

Responsibilities of a component maintainer:

- Component maintainer must assure maintenance of their component for at least three years (approx. three ILIAS major 
releases).
- Component maintainers must agree to coordinate the development of their component with the product manager.
- Component maintainer are responsible for bug fixing of their component and get assigned related bugs automatically 
by the [Issue-Tracker](https://mantis.ilias.de).
- Component maintainers are responsible for Pull Requests to their component and get assigned related Pull Requests 
by the Technical Board according to the [Rules for Maintainers and Coordinators assigned to PRs[(Rules for Maintainers and Coordinators assigned to PRs)


## Becoming a Maintainer

Applications for maintainerships can be handed in to the product manager. The product manager together with the 
technical board decide on who becomes a maintainer. Maintainerships are listed with the name of the maintainer. In 
addition the company the maintainer is working for can be listed, too. In this second case, the company has the right to 
propose an alternative maintainer at any time. In particular, if the maintainer resigns from his maintenance, a proposal
for a new maintainer by the company of the old maintainer will be preferred, if the company recently invested 
substantially in the general condition of the component and the proposed maintainer meets the criteria.

## Implicit Maintainers
If a component is currently unmaintained, a developer can take responsibility for it without agreeing to give full support. 
An implicit maintainer will get assigned related bugs automatically and will keep the component working through the update cycle. 
S/he will not implement new features or develop the component further. If enhancements of the component are wanted, an
explicit maintainer or coordinator must be assigned.

## Additional Competences
A maintainer can pass certain of her/his competences to other people in the community. Currently these are:

* The **competence to handle pull requests** including the rights to merge or close them.
* The **competence to handle issues in Mantis** including the rights to relable, reassign, close, or reopen them.

If nobody is fulfilling the responsibilities of the component maintainer, the Product Manager together with the Technical Board 
can look for members of the community and assign these competences to them.

## Tracking Maintainerships
Maintainerships are tracked in maintenance.json files placed in the root of the corresponding components of ILIAS. The 
file containes the following fields:

* **maintenance_model**: Currently there are two possible entries for this field
	* "[Classic](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance.md#maintainers)"
	* "[Coordinator](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md#coordinator-model)".
* **"first_maintainer"**: One entry in the form `<username> (<userid>)` pointing to a valid user on 
	https://docu.ilias.de. Only relevant if **the maintenance_model** is set to "Classic".
* **"second_maintainer"**: One entry in the form `<username> (<userid>)` pointing to a valid user on https://docu.ilias.de. 
	Only relevant if **the maintenance_model** is set to "Classic".
* **"[implicit_maintainers](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance.md#implicit-maintainers)"**: 
    An array in the form [ `<username> (<userid>)` ] pointing to valid users on https://docu.ilias.de. Only relevant if 
    **the maintenance_model** is set to "Classic" **and** neither a first nor a second maintainers is set.
* **"coordinator"**: An array in the form [ `<username> (<userid>)` ] pointing to valid users on https://docu.ilias.de.
	Only relevant if **the maintenance_model** is set to "Coordinator".
* **"[pr_management](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance.md#additional-competences)"** : 
    An array in the form [ `<username> (<userid>)` ] pointing to valid users on https://docu.ilias.de.
* **"[issue_management](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance.md#additional-competences)"** :
  An array in the form [ `<username> (<userid>)` ] pointing to valid users on https://docu.ilias.de.
* **"tester"**: One entry in the form `<username> (<userid>)` pointing to a valid user on
  https://docu.ilias.de.
* **"testcase_writer"**: One entry in the form `<username> (<userid>)` pointing to a valid user on
  https://docu.ilias.de.

## Current Maintainerships

[//]: # (BEGIN ActiveRecord)

* **ActiveRecord**
	* Maintenance Model: Classic
    * 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END ActiveRecord)

[//]: # (BEGIN Administration)

* **Administration**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END Administration)

[//]: # (BEGIN AdministrativeNotifications)

* **Administrative Notifications**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END AdministrativeNotifications)

[//]: # (BEGIN BackgroundTasks)

* **BackgroundTasks**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END BackgroundTasks)

[//]: # (BEGIN Badges)

* **Badges**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)

[//]: # (END Badges)

[//]: # (BEGIN BibliographicListItem)

* **Bibliographic List Item**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)

[//]: # (END BibliographicListItem)

[//]: # (BEGIN Blog)

* **Blog**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [PaBer](https://docu.ilias.de/goto_docu_usr_33766.html)

[//]: # (END Blog)

[//]: # (BEGIN BookingTool)

* **Booking Tool**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [e.coroian](https://docu.ilias.de/goto_docu_usr_37215.html)
	* Tester: [wolfganghuebsch](https://docu.ilias.de/goto_docu_usr_18455.html)

[//]: # (END BookingTool)

[//]: # (BEGIN Calendar)

* **Calendar**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)

[//]: # (END Calendar)

[//]: # (BEGIN CategoryAndRepository)

* **Category and Repository**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)

[//]: # (END CategoryAndRepository)

[//]: # (BEGIN Certificate)

* **Certificate**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [m-gregory-m](https://docu.ilias.de/goto_docu_usr_51332.html)

[//]: # (END Certificate)

[//]: # (BEGIN Chat)

* **Chat**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)

[//]: # (END Chat)

[//]: # (BEGIN cmi5AndxAPIObject)

* **cmi5/xAPI Object**
	* Maintenance Model: Classic
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [EMok](https://docu.ilias.de/goto_docu_usr_80682.html)

[//]: # (END cmi5AndxAPIObject)

[//]: # (BEGIN Comments)

* **Comments**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)

[//]: # (END Comments)

[//]: # (BEGIN CompetenceManagement)

* **Competence Management**
	* Maintenance Model: Classic
	* 1st Maintainer: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [ioanna.mitroulaki](https://docu.ilias.de/goto_docu_usr_72564.html)

[//]: # (END CompetenceManagement)

[//]: # (BEGIN Component)

* **Component**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END Component)

[//]: # (BEGIN Contacts)

* **Contacts**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Contacts)

[//]: # (BEGIN ContentPage)

* **ContentPage**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END ContentPage)

[//]: # (BEGIN CourseManagement)

* **Course Management**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [lauener](https://docu.ilias.de/goto_docu_usr_8474.html)
	* Tester: [lauener](https://docu.ilias.de/goto_docu_usr_8474.html)
	  , [TESTER MISSING FOR LOC](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END CourseManagement)

[//]: # (BEGIN CronService)

* **Cron Service**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END CronService)

[//]: # (BEGIN CSSAndTemplates)

* **CSS / Templates**
	* Maintenance Model: Classic
	* 1st Maintainer: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)

[//]: # (END CSSAndTemplates)

[//]: # (BEGIN Dashboard)

* **Dashboard**
	* Maintenance Model: Classic
	* 1st Maintainer: [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html) and [lscharmer](https://docu.ilias.de/goto_docu_usr_87863.html)
	* 2nd Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [silvia.marine](https://docu.ilias.de/goto_docu_usr_71642.html)

[//]: # (END Dashboard)

[//]: # (BEGIN Data)

* **Data**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Data)

[//]: # (BEGIN DataCollection)

* **Data Collection**
	* Maintenance Model: Coordinator
	* Coordinators: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html) & [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	* Testcases: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* Tester: [mona.schliebs](https://docu.ilias.de/goto_docu_usr_60222.html)

[//]: # (END DataCollection)

[//]: # (BEGIN Database)

* **Database**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Database)

[//]: # (BEGIN DidacticTemplates)

* **Didactic Templates**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END DidacticTemplates)

[//]: # (BEGIN ECSInterface)

* **ECS Interface**
	* Maintenance Model: Classic
	* 1st Maintainer: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
	* 2nd Maintainer: N.A.
	* Testcases: [SIG CampusConnect und ECS(A)](https://docu.ilias.de/goto_docu_grp_7893.html)
	* Tester: [SIG CampusConnect und ECS(A)](https://docu.ilias.de/goto_docu_grp_7893.html)

[//]: # (END ECSInterface)

[//]: # (BEGIN EmployeeTalk)

* **EmployeeTalk**
	* Maintenance Model: Classic
	* 1st Maintainer: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
	* 2nd Maintainer: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
	* Testcases: N.A.
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)

[//]: # (END EmployeeTalk)

[//]: # (BEGIN Exercise)

* **Exercise**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [miriamwegener](https://docu.ilias.de/goto_docu_usr_23051.html)

[//]: # (END Exercise)

[//]: # (BEGIN Export)

* **Export**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

[//]: # (END Export)

[//]: # (BEGIN Favourites)

* **Favourites**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Favourites)

[//]: # (BEGIN File)

* **File**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [scarlino](https://docu.ilias.de/goto_docu_usr_56074.html)
	* Tester: Heinz Winter, CaT

[//]: # (END File)

[//]: # (BEGIN Forum)

* **Forum**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](https://docu.ilias.de/goto_docu_usr_14206.html)
	* Testcases: FH Aachen
	* Tester: [e.coroian](https://docu.ilias.de/goto_docu_usr_37215.html)
	  und [anna.s.vogel](https://docu.ilias.de/goto_docu_usr_71954.html)

[//]: # (END Forum)

[//]: # (BEGIN GeneralKiosk-Mode)

* **General Kiosk-Mode**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END GeneralKiosk-Mode)

[//]: # (BEGIN GlobalCache)

* **GlobalCache**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END GlobalCache)

[//]: # (BEGIN GlobalScreen)

* **GlobalScreen**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END GlobalScreen)

[//]: # (BEGIN Glossary)

* **Glossary**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)

[//]: # (END Glossary)

[//]: # (BEGIN Group)

* **Group**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)

[//]: # (END Group)

[//]: # (BEGIN HTTP-Request)

* **HTTP-Request**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END HTTP-Request)

[//]: # (BEGIN ILIASPageEditor)

* **ILIAS Page Editor**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: FH Aachen

[//]: # (END ILIASPageEditor)

[//]: # (BEGIN IndividualAssessment)

* **IndividualAssessment**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END IndividualAssessment)

[//]: # (BEGIN InfoPage)

* **Info Page**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

[//]: # (END InfoPage)

[//]: # (BEGIN InitialisationService)

* **Initialisation Service**
	* Maintenance Model: Coordinator
	* Coordinators: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END InitialisationService)

[//]: # (BEGIN ItemGroup)

* **ItemGroup**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)

[//]: # (END ItemGroup)

[//]: # (BEGIN LanguageHandling)

* **Language Handling**
	* Maintenance Model: Classic
	* 1st Maintainer: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* 2nd Maintainer: [katrin.grosskopf](https://docu.ilias.de/goto_docu_usr_68340.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END LanguageHandling)

[//]: # (BEGIN LearningHistory)

* **Learning History**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)

[//]: # (END LearningHistory)

[//]: # (BEGIN LearningModuleHTML)

* **Learning Module HTML**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END LearningModuleHTML)

[//]: # (BEGIN LearningModuleILIAS)

* **Learning Module ILIAS**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Balliel](https://docu.ilias.de/goto_docu_usr_18365.html)
	* Tester: [Balliel](https://docu.ilias.de/goto_docu_usr_18365.html)

[//]: # (END LearningModuleILIAS)

[//]: # (BEGIN LearningModuleSCORM)

* **Learning Module SCORM**
	* Maintenance Model: Classic
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: N.A.
	* Testcases: n.n., Qualitus
	* Tester: n.n., Qualitus

[//]: # (END LearningModuleSCORM)

[//]: # (BEGIN LearningSequence)

* **Learning Sequence**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [scarlino](https://docu.ilias.de/goto_docu_usr_56074.html)
	* Tester: [mglaubitz](https://docu.ilias.de/goto_docu_usr_28309.html)

[//]: # (END LearningSequence)

[//]: # (BEGIN Like)

* **Like**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Like)

[//]: # (BEGIN Logging)

* **Logging**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Logging)

[//]: # (BEGIN LoginAuthAndRegistration)

* **Login, Auth & Registration**
	* Maintenance Model: Coordinator
	* Coordinators: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html) & [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [vimotion](https://docu.ilias.de/goto_docu_usr_25105.html)
	  , [ILIAS_LM](https://docu.ilias.de/goto_docu_usr_14109.html) (OpenID)
	  , [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html) (Shibboleth), Alexander Grundkötter, Qualitus (SAML)

[//]: # (END LoginAuthAndRegistration)

[//]: # (BEGIN LTI)

* **LTI**
	* Maintenance Model: Classic
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [stv](https://docu.ilias.de/goto_docu_usr_45359.html)

[//]: # (END LTI)

[//]: # (BEGIN LTIConsumer)

* **LTI Consumer**
	* Maintenance Model: Classic
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)

[//]: # (END LTIConsumer)

[//]: # (BEGIN Mail)

* **Mail**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](https://docu.ilias.de/goto_docu_usr_14206.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: Till Lennart Vogt/Test-Team OWL

[//]: # (END Mail)

[//]: # (BEGIN MainMenu)

* **MainMenu**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END MainMenu)

[//]: # (BEGIN Maps)

* **Maps**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)

[//]: # (END Maps)

[//]: # (BEGIN MathJax)

* **MathJax**
	* Maintenance Model: Classic
	* 1st Maintainer: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
	* 2nd Maintainer: N.A.
	* Testcases: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
	* Tester: [resi](https://docu.ilias.de/goto_docu_usr_72790.html)

[//]: # (END MathJax)

[//]: # (BEGIN MediaObjects)

* **Media Objects**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)

[//]: # (END MediaObjects)

[//]: # (BEGIN MediaPool)

* **Media Pool**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)

[//]: # (END MediaPool)

[//]: # (BEGIN MediaCast)

* **MediaCast**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)

[//]: # (END MediaCast)

[//]: # (BEGIN Membership)

* **Membership**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Membership)

[//]: # (BEGIN Metadata)

* **Metadata**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
	* Testcases: [daniela.weber](https://docu.ilias.de/goto_docu_usr_40672.html)
	* Tester: [daniela.weber](https://docu.ilias.de/goto_docu_usr_40672.html)

[//]: # (END Metadata)

[//]: # (BEGIN News)

* **News**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)

[//]: # (END News)

[//]: # (BEGIN NotesAndComments)

* **Notes and Comments**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)

[//]: # (END NotesAndComments)

[//]: # (BEGIN Notifications)

* **Notifications**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Testcases: N.A.
	* Tester: N.A.

[//]: # (END Notifications)

[//]: # (BEGIN ObjectService)

* **Object Service**
	* Maintenance Model: Classic
	* 1st Maintainer: [fawinike](https://docu.ilias.de/goto_docu_usr_44474.html)
	* 2nd Maintainer: N.A.
	* Testcases: N.A.
	* Tester: N.A.

[//]: # (END ObjectService)

[//]: # (BEGIN OnlineHelp)

* **Online Help**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)

[//]: # (END OnlineHelp)

[//]: # (BEGIN OpenIdConect)

* **Open ID Connect**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: N.A.
	* Tester: N.A.

[//]: # (END OpenIdConect)

[//]: # (BEGIN OrganisationalUnits)

* **Organisational Units**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Testcases: [wischniak](https://docu.ilias.de/goto_docu_usr_21896.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)

[//]: # (END OrganisationalUnits)

[//]: # (BEGIN PDF)

* **PDF**
	* Maintenance Model: Classic
	* 1st Maintainer: [gvollbach](https://docu.ilias.de/goto_docu_usr_25234.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END PDF)

[//]: # (BEGIN PersonalAndSharedResources)

* **Personal and Shared Resources**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [scarlino](https://docu.ilias.de/goto_docu_usr_56074.html)

[//]: # (END PersonalAndSharedResources)

[//]: # (BEGIN Poll)

* **Poll**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [Qndrs](https://docu.ilias.de/goto_docu_usr_42611.html)

[//]: # (END Poll)

[//]: # (BEGIN Portfolio)

* **Portfolio**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases Portfolio: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
 	* Testcases Portfolio Template: N.A. 
	* Tester Portfolio: [KlausVorkauf](https://docu.ilias.de/goto_docu_usr_5890.html)
	* Tester Portfolio Template: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Portfolio)

[//]: # (BEGIN PreconditionHandling)

* **Precondition Handling**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [mkloes](https://docu.ilias.de/goto_docu_usr_22174.html)

[//]: # (END PreconditionHandling)

[//]: # (BEGIN Rating)

* **Rating**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

[//]: # (END Rating)

[//]: # (BEGIN RBAC)

* **RBAC / Access Control**
	* Maintenance Model: Classic
    * 1st Maintainer: [fawinike](https://docu.ilias.de/goto_docu_usr_44474.html)
	* Until ILIAS 8: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END RBAC)

[//]: # (BEGIN Refinery)

* **Refinery**
	* Maintenance Model: Coordinator
	* Coordinators: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Used in Directories: src/Refinery

[//]: # (END Refinery)

[//]: # (BEGIN SAML)

* **SAML**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: Alexander Grundkötter, Qualitus

[//]: # (END SAML)

[//]: # (BEGIN Search)

* **Search**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [Qndrs](https://docu.ilias.de/goto_docu_usr_42611.html)

[//]: # (END Search)

[//]: # (BEGIN Session)

* **Session**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)

[//]: # (END Session)

[//]: # (BEGIN Setup)

* **Setup**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [fwolf](https://docu.ilias.de/goto_docu_usr_29018.html)

[//]: # (END Setup)

[//]: # (BEGIN ShibbolethAuthentication)

* **Shibboleth Authentication**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)

[//]: # (END ShibbolethAuthentication)

[//]: # (BEGIN SOAP)

* **SOAP / Webservices**
	* Maintenance Model: Classic
	* 1st Maintainer: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
	* 2nd Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END SOAP)

[//]: # (BEGIN Staff)

* **Staff**
	* Maintenance Model: Classic
	* 1st Maintainer: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
	* 2nd Maintainer: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)

[//]: # (END Staff)

[//]: # (BEGIN StatisticsAndLearningProgress)

* **Statistics and Learning Progress**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [suittenpointner](https://docu.ilias.de/goto_docu_usr_3458.html)
	* Tester: [suittenpointner](https://docu.ilias.de/goto_docu_usr_3458.html)

[//]: # (END StatisticsAndLearningProgress)

[//]: # (BEGIN StudyProgramme)

* **Study Programme**
	* Maintenance Model: Classic
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: [shecken](https://docu.ilias.de/goto_docu_usr_45419.html)
	* Testcases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: Florence Seydoux, s+r

[//]: # (END StudyProgramme)

[//]: # (BEGIN Survey)

* **Survey**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)

[//]: # (END Survey)

[//]: # (BEGIN SystemCheck)

* **System Check**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END SystemCheck)

[//]: # (BEGIN Tagging)

* **Tagging**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* Testcases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)

[//]: # (END Tagging)

[//]: # (BEGIN Tasks)

* **Tasks**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Tasks)

[//]: # (BEGIN Taxonomy)

* **Taxonomy**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: Tested separately in each module that supports taxonomies
	* Tester: Tested separately in each module that supports taxonomies

[//]: # (END Taxonomy)

[//]: # (BEGIN TermsOfServices)

* **Terms of Services**
	* Maintenance Model: Classic
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: Stefania Akgül (CaT)
	* Tester: Heinz Winter (CaT)

[//]: # (END TermsOfServices)

[//]: # (BEGIN TestAndAssessment)

* **Test & Assessment**
	* Maintenance Model: Classic
	* 1st Maintainer: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
	* 2nd Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Testcases: SIG E-Assessment
	* Tester: Stefania Akgül (CaT), Stefanie Allmendinger (FAU)
	  , [dehling](https://docu.ilias.de/goto_docu_usr_12725.html)
          , [simon.lowe](https://docu.ilias.de/goto_docu_usr_79091.html)
	  , [rabah](https://docu.ilias.de/goto_docu_usr_40218.html)	

[//]: # (END TestAndAssessment)

[//]: # (BEGIN Tree)

* **Tree**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Tree)

[//]: # (BEGIN UserService)

* **User Service**
	* Maintenance Model: Classic
	* 1st Maintainer: [fawinike](https://docu.ilias.de/goto_docu_usr_44474.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)

[//]: # (END UserService)

[//]: # (BEGIN UICore)

* **UICore**
	* Maintenance Model: Classic
	* 1st Maintainer: [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
	* 2nd Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END UICore)

[//]: # (BEGIN UI-Service)

* **UI-Service**
	* Maintenance Model: Coordinator
	* Coordinators: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Test cases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
	* Tester: [kauerswald](https://docu.ilias.de/goto_docu_usr_70029.html)
	* Used in Directories: src/UI

[//]: # (END UI-Service)

[//]: # (BEGIN VirusScanner)

* **Virus Scanner**
	* Maintenance Model: Classic
	* 1st Maintainer: [rschenk](https://docu.ilias.de/goto_docu_usr_18065.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END VirusScanner)

[//]: # (BEGIN WebAccessChecker)

* **Web Access Checker**
	* Maintenance Model: Classic
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* Testcases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)

[//]: # (END WebAccessChecker)

[//]: # (BEGIN WebFeed)

* **Web Feed**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

[//]: # (END WebFeed)

[//]: # (BEGIN WebDAV)

* **WebDAV**
	* Maintenance Model: Classic
	* 1st Maintainer: [fawinike](https://docu.ilias.de/goto_docu_usr_44474.html)
	* 2nd Maintainer: [rheer](https://docu.ilias.de/goto_docu_usr_47872.html)
	* Testcases: [fawinike](https://docu.ilias.de/goto_docu_usr_44474.html)
	* Tester: [kauerswald](https://docu.ilias.de/goto_docu_usr_70029.html)

[//]: # (END WebDAV)

[//]: # (BEGIN Weblink)

* **Weblink**
	* Maintenance Model: Classic
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [nadine.bauser](https://docu.ilias.de/goto_docu_usr_34662.html)
	* Tester: [nadine.bauser](https://docu.ilias.de/goto_docu_usr_34662.html)

[//]: # (END Weblink)

[//]: # (BEGIN Webservices)

* **Webservices**
	* Maintenance Model: Classic
	* 1st Maintainer: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END Webservices)

[//]: # (BEGIN WhoIsOnline)

* **Who is online?**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)

[//]: # (END WhoIsOnline)

[//]: # (BEGIN Wiki)

* **Wiki**
	* Maintenance Model: Classic
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: N.N., Uni Köln
	* Tester: N.N., Uni Köln

[//]: # (END Wiki)

[//]: # (BEGIN WorkflowEngine)

* **Workflow Engine**
	* Maintenance Model: Classic
	* 1st Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* 2nd Maintainer: N.A.
	* Testcases: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Tester: [richtera](https://docu.ilias.de/goto_docu_usr_41247.html)

[//]: # (END WorkflowEngine)

[//]: # (BEGIN xAPIAndcmi5)

* **xAPI/cmi5**
	* Maintenance Model: Classic
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

[//]: # (END xAPIAndcmi5)

## Unmaintained Components

The following directories are currently unmaintained:

* Services/Context
* Services/CSV
* Services/EventHandling
* Services/Excel
* Services/QTI
* Services/Randomization
