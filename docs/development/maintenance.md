
ILIAS Maintenance
=================
The development of the ILIAS source code is coordinated and maintained by a coordination team within the ILIAS
network. Besides the main responsibilities for the project, several developers and users are maintaining certain
modules of ILIAS.

# Special Roles

* **Product Management**: [Matthias Kunkel]
* **Technical Board**: [Timon Amstutz], [Michael Jansen], [Richard Klees], [Fabian Schmid], [Stephan Winiker]
* **Testcase Management**: [Fabian Kruse]
* **Documentation**: N.A.
* **Online Help**: [Alexandra Tödt]

# Maintainers
We highly appreciate to get new developers but we have to guarantee the sustainability and the quality of the ILIAS
source code. The system is complex for new developers and they need to know the concepts of ILIAS that are described
in the development guide.

Communication among developers that are working on a specific component needs to be assured. Final decision about
getting write access to the ILIAS development system (Github) is handled by the product manager.

ILIAS is currently maintained by three types of Maintainerships:

- First Component Maintainer
- Second Component Maintainer
- [Coordinator Model](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md)

The following rules must be respected for everyone involved in the programming of ILIAS for all components having a
listed component maintainer (see below):

1. Decisions on new features or feature removals are made by the responsible first maintainer and the product manager
in the Jour Fixe meetings after an open discussion.
2. All components have a first and second maintainer. Code changes are usually done by the first maintainer. The first
maintainer may forward new implementations to the second maintainer.

Responsibilities of a component maintainer:

- Component maintainer must assure maintenance of their component for at least three years (approx. three ILIAS major
releases).
- Component maintainers must agree to coordinate the development of their component with the product manager.
- Component maintainer are responsible for bug fixing of their component and get assigned related bugs automatically
by the [Issue-Tracker](https://mantis.ilias.de).


## Becoming a Maintainer

Applications for maintainerships can be handed in to the product manager. The product manager together with the
technical board decide on who becomes a maintainer. Maintainerships are listed with the name of the maintainer. In
addition the company the maintainer is working for can be listed, too. In this second case, the company has the right to
propose an alternative maintainer at any time. In particular, if the maintainer resigns from his maintenance, a proposal
for a new maintainer by the company of the old maintainer will be preferred, if the company recently invested
substantially in the general condition of the component and the proposed maintainer meets the criteria.


## Current Maintainerships

<!-- BEGIN ActiveRecord -->
* **ActiveRecord**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END ActiveRecord -->

<!-- BEGIN Administration -->
* **Administration**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END Administration -->

<!-- BEGIN BackgroundTasks -->
* **BackgroundTasks**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END BackgroundTasks -->

<!-- BEGIN Badges -->
* **Badges**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)
<!-- END Badges -->

<!-- BEGIN BibliographicListItem -->
* **Bibliographic List Item**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [miriamhoelscher](http://www.ilias.de/docu/goto_docu_usr_25370.html)
<!-- END BibliographicListItem -->

<!-- BEGIN Blog -->
* **Blog**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)
	* Tester: [PaBer](http://www.ilias.de/docu/goto_docu_usr_33766.html)
<!-- END Blog -->

<!-- BEGIN BookingTool -->
* **Booking Tool**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [e.coroian](http://www.ilias.de/docu/goto_docu_usr_37215.html)
	* Tester: [wolfganghuebsch](http://www.ilias.de/docu/goto_docu_usr_18455.html)
<!-- END BookingTool -->

<!-- BEGIN Bookmarks -->
* **Bookmarks**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [miriamhoelscher](http://www.ilias.de/docu/goto_docu_usr_25370.html)
<!-- END Bookmarks -->

<!-- BEGIN CSSAndTemplates -->
* **CSS / Templates**
	* 1st Maintainer: [braun](http://www.ilias.de/docu/goto_docu_usr_27123.html)
	* 2nd Maintainer: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
<!-- END CSSAndTemplates -->

<!-- BEGIN Calendar -->
* **Calendar**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern
<!-- END Calendar -->

<!-- BEGIN CategoryAndRepository -->
* **Category and Repository**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [miriamhoelscher](http://www.ilias.de/docu/goto_docu_usr_25370.html)
<!-- END CategoryAndRepository -->

<!-- BEGIN Certificate -->
* **Certificate**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: [christian.hueser](http://www.ilias.de/docu/goto_docu_usr_41129.html)
	* Tester: [christian.hueser](http://www.ilias.de/docu/goto_docu_usr_41129.html)
<!-- END Certificate -->

<!-- BEGIN Chat -->
* **Chat**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [elenak](http://www.ilias.de/docu/goto_docu_usr_49160.html)
<!-- END Chat -->

<!-- BEGIN CloudObject -->
* **Cloud Object**
	* 1st Maintainer: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* 2nd Maintainer: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
<!-- END CloudObject -->

<!-- BEGIN CompetenceManagement -->
* **Competence Management**
	* 1st Maintainer: [tfamula](http://www.ilias.de/docu/goto_docu_usr_58959.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [wolfganghuebsch](http://www.ilias.de/docu/goto_docu_usr_18455.html)
<!-- END CompetenceManagement -->

<!-- BEGIN Contacts -->
* **Contacts**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
	* Tester: [abaulig1](http://www.ilias.de/docu/goto_docu_usr_44386.html)
<!-- END Contacts -->

<!-- BEGIN ContentPage -->
* **ContentPage**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END ContentPage -->

<!-- BEGIN CourseManagement -->
* **Course Management**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern
<!-- END CourseManagement -->

<!-- BEGIN CronService -->
* **Cron Service**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END CronService -->

<!-- BEGIN Dashboard -->
* **Dashboard**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Dashboard -->

<!-- BEGIN Data -->
* **Data**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Data -->

<!-- BEGIN DataCollection -->
* **Data Collection**
	* 1st Maintainer: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [kim.herms](http://www.ilias.de/docu/goto_docu_usr_28720.html)
<!-- END DataCollection -->

<!-- BEGIN Database -->
* **Database**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Database -->

<!-- BEGIN DidacticTemplates -->
* **Didactic Templates**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END DidacticTemplates -->

<!-- BEGIN ECSInterface -->
* **ECS Interface**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: Christian Bogen and Kristina Haase
	* Tester: [bogen](http://www.ilias.de/docu/goto_docu_usr_13815.html)
<!-- END ECSInterface -->

<!-- BEGIN Exercise -->
* **Exercise**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [miriamwegener](http://www.ilias.de/docu/goto_docu_usr_23051.html)
<!-- END Exercise -->

<!-- BEGIN Export -->
* **Export**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
<!-- END Export -->

<!-- BEGIN File -->
* **File**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [daniwe4](http://www.ilias.de/docu/goto_docu_usr_54500.html)
	* Tester: [daniwe4](http://www.ilias.de/docu/goto_docu_usr_54500.html)
<!-- END File -->

<!-- BEGIN Forum -->
* **Forum**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](http://www.ilias.de/docu/goto_docu_usr_14206.html)
	* Testcases: FH Aachen
	* Tester: [e.coroian](http://www.ilias.de/docu/goto_docu_usr_37215.html)
<!-- END Forum -->

<!-- BEGIN GeneralKiosk-Mode -->
* **General Kiosk-Mode**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END GeneralKiosk-Mode -->

<!-- BEGIN GlobalCache -->
* **GlobalCache**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END GlobalCache -->

<!-- BEGIN GlobalScreen -->
* **GlobalScreen**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END GlobalScreen -->

<!-- BEGIN Glossary -->
* **Glossary**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
<!-- END Glossary -->

<!-- BEGIN Group -->
* **Group**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern
<!-- END Group -->

<!-- BEGIN HTTP-Request -->
* **HTTP-Request**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END HTTP-Request -->

<!-- BEGIN ILIASPageEditor -->
* **ILIAS Page Editor**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: FH Aachen
<!-- END ILIASPageEditor -->

<!-- BEGIN IndividualAssessment -->
* **IndividualAssessment**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [dkloepfer](http://www.ilias.de/docu/goto_docu_usr_42712.html)
	* Testcases: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END IndividualAssessment -->

<!-- BEGIN InfoPage -->
* **Info Page**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END InfoPage -->

<!-- BEGIN ItemGroup -->
* **ItemGroup**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
	* Tester: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
<!-- END ItemGroup -->

<!-- BEGIN LTI -->
* **LTI**
	* 1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
<!-- END LTI -->

<!-- BEGIN LTIConsumer -->
* **LTI Consumer**
	* 1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END LTIConsumer -->

<!-- BEGIN LanguageHandling -->
* **Language Handling**
	* 1st Maintainer: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* 2nd Maintainer: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END LanguageHandling -->

<!-- BEGIN LearningHistory -->
* **Learning History**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [oliver.samoila](http://www.ilias.de/docu/goto_docu_usr_26160.html)
<!-- END LearningHistory -->

<!-- BEGIN LearningModuleHTML -->
* **Learning Module HTML**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
	* Tester: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
<!-- END LearningModuleHTML -->

<!-- BEGIN LearningModuleILIAS -->
* **Learning Module ILIAS**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Balliel](http://www.ilias.de/docu/goto_docu_usr_18365.html)
	* Tester: [Balliel](http://www.ilias.de/docu/goto_docu_usr_18365.html)
<!-- END LearningModuleILIAS -->

<!-- BEGIN LearningModuleSCORM -->
* **Learning Module SCORM**
	* 1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* 2nd Maintainer: MISSING
	* Testcases: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
	* Tester: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
<!-- END LearningModuleSCORM -->

<!-- BEGIN LearningSequence -->
* **Learning Sequence**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: MISSING
	* Testcases: [scarlino](http://www.ilias.de/docu/goto_docu_usr_56074.html)
	* Tester: [mglaubitz](http://www.ilias.de/docu/goto_docu_usr_28309.html)
<!-- END LearningSequence -->

<!-- BEGIN Like -->
* **Like**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Like -->

<!-- BEGIN Logging -->
* **Logging**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Logging -->

<!-- BEGIN LoginAuthRegistration -->
* **Login, Auth & Registration**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: FH Aachen
	* Tester: [vimotion](http://www.ilias.de/docu/goto_docu_usr_25105.html)
<!-- END LoginAuthRegistration -->

<!-- BEGIN Mail -->
* **Mail**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](http://www.ilias.de/docu/goto_docu_usr_14206.html)
	* Testcases: [amersch](http://www.ilias.de/docu/goto_docu_usr_15114.html)
	* Tester: [amersch](http://www.ilias.de/docu/goto_docu_usr_15114.html)
<!-- END Mail -->

<!-- BEGIN MainMenu -->
* **MainMenu**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END MainMenu -->

<!-- BEGIN Maps -->
* **Maps**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [dkloepfer](http://www.ilias.de/docu/goto_docu_usr_42712.html)
	* Testcases: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Tester: [miriamhoelscher](http://www.ilias.de/docu/goto_docu_usr_25370.html)
<!-- END Maps -->

<!-- BEGIN MathJax -->
* **MathJax**
	* 1st Maintainer: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* 2nd Maintainer: MISSING
	* Testcases: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* Tester: [claudio.fischer](http://www.ilias.de/docu/goto_docu_usr_41113.html)
<!-- END MathJax -->

<!-- BEGIN MediaObjects -->
* **Media Objects**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END MediaObjects -->

<!-- BEGIN MediaPool -->
* **Media Pool**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END MediaPool -->

<!-- BEGIN MediaCast -->
* **MediaCast**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
	* Tester: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
<!-- END MediaCast -->

<!-- BEGIN Membership -->
* **Membership**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Membership -->

<!-- BEGIN Metadata -->
* **Metadata**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [daniela.weber](http://www.ilias.de/docu/goto_docu_usr_40672.html)
	* Tester: [daniela.weber](http://www.ilias.de/docu/goto_docu_usr_40672.html)
<!-- END Metadata -->

<!-- BEGIN MyWorkspace -->
* **My Workspace**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)
<!-- END MyWorkspace -->

<!-- BEGIN News -->
* **News**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)
	* Tester: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)
<!-- END News -->

<!-- BEGIN NotesAndComments -->
* **Notes and Comments**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
<!-- END NotesAndComments -->

<!-- BEGIN OnlineHelp -->
* **Online Help**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
<!-- END OnlineHelp -->

<!-- BEGIN OrganisationalUnits -->
* **Organisational Units**
	* 1st Maintainer: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)
	* Tester: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)
<!-- END OrganisationalUnits -->

<!-- BEGIN PersonalDesktop -->
* **Personal Desktop**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END PersonalDesktop -->

<!-- BEGIN PersonalProfile -->
* **Personal Profile**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
<!-- END PersonalProfile -->

<!-- BEGIN PluginSlots -->
* **Plugin Slots**
	* 1st Maintainer: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* 2nd Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: Future Learning
<!-- END PluginSlots -->

<!-- BEGIN Poll -->
* **Poll**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: Future Learning
<!-- END Poll -->

<!-- BEGIN Portfolio -->
* **Portfolio**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: KlausVorkauf and Oliver Samoila (Portfolio Template)
	* Tester: KlausVorkauf and Oliver Samoila (Portfolio Template)
<!-- END Portfolio -->

<!-- BEGIN PreconditionHandling -->
* **Precondition Handling**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [mkloes](http://www.ilias.de/docu/goto_docu_usr_22174.html)
<!-- END PreconditionHandling -->

<!-- BEGIN RBAC -->
* **RBAC**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END RBAC -->

<!-- BEGIN Rating -->
* **Rating**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
<!-- END Rating -->

<!-- BEGIN SAML -->
* **SAML**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END SAML -->

<!-- BEGIN SCORMOfflinePlayer -->
* **SCORM Offline Player**
	* 1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* 2nd Maintainer: [sschneider](http://www.ilias.de/docu/goto_docu_usr_21741.html)
	* Testcases: [sschneider](http://www.ilias.de/docu/goto_docu_usr_21741.html)
	* Tester: [sschneider](http://www.ilias.de/docu/goto_docu_usr_21741.html)
<!-- END SCORMOfflinePlayer -->

<!-- BEGIN SCORMOnlineEditor -->
* **SCORM Online Editor**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [Hester](http://www.ilias.de/docu/goto_docu_usr_31687.html)
<!-- END SCORMOnlineEditor -->

<!-- BEGIN SOAP -->
* **SOAP**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END SOAP -->

<!-- BEGIN Search -->
* **Search**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: FH Aachen
	* Tester: Future Learning
<!-- END Search -->

<!-- BEGIN Session -->
* **Session**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern
<!-- END Session -->

<!-- BEGIN Setup -->
* **Setup**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [aarsenij](http://www.ilias.de/docu/goto_docu_usr_41159.html)
<!-- END Setup -->

<!-- BEGIN ShibbolethAuthentication -->
* **Shibboleth Authentication**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern
<!-- END ShibbolethAuthentication -->

<!-- BEGIN Staff -->
* **Staff**
	* 1st Maintainer: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* 2nd Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Staff -->

<!-- BEGIN StatisticsAndLearningProgress -->
* **Statistics and Learning Progress**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [bromberger](http://www.ilias.de/docu/goto_docu_usr_198.html)
	* Tester: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
<!-- END StatisticsAndLearningProgress -->

<!-- BEGIN StudyProgramme -->
* **Study Programme**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [shecken](http://www.ilias.de/docu/goto_docu_usr_45419.html)
	* Testcases: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Tester: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
<!-- END StudyProgramme -->

<!-- BEGIN Survey -->
* **Survey**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [Xus](http://www.ilias.de/docu/goto_docu_usr_50418.html)
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: ILIAS open source e-Learning e.V.
<!-- END Survey -->

<!-- BEGIN SystemCheck -->
* **System Check**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END SystemCheck -->

<!-- BEGIN Tagging -->
* **Tagging**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
<!-- END Tagging -->

<!-- BEGIN Tasks -->
* **Tasks**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Tasks -->

<!-- BEGIN Taxonomy -->
* **Taxonomy**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: Tested separately in each module that supports taxonomies
	* Tester: Tested separately in each module that supports taxonomies
<!-- END Taxonomy -->

<!-- BEGIN TestAssessment -->
* **Test & Assessment**
	* 1st Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* 2nd Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* Testcases: Stefanie Zepf and SIG EA
	* Tester: Claudia Dehling, SIG EA, et al.
<!-- END TestAssessment -->

<!-- BEGIN Tree -->
* **Tree**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Tree -->

<!-- BEGIN UICore -->
* **UICore**
	* 1st Maintainer: tfuhrer
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END UICore -->

<!-- BEGIN UserService -->
* **User Service**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END UserService -->

<!-- BEGIN WebAccessChecker -->
* **Web Access Checker**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
<!-- END WebAccessChecker -->

<!-- BEGIN WebFeed -->
* **Web Feed**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
<!-- END WebFeed -->

<!-- BEGIN WebDAV -->
* **WebDAV**
	* 1st Maintainer: [fawinike](http://www.ilias.de/docu/goto_docu_usr_44474.html)
	* 2nd Maintainer: [rheer](http://www.ilias.de/docu/goto_docu_usr_47872.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END WebDAV -->

<!-- BEGIN Weblink -->
* **Weblink**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [nadine.bauser](http://www.ilias.de/docu/goto_docu_usr_34662.html)
	* Tester: [nadine.bauser](http://www.ilias.de/docu/goto_docu_usr_34662.html)
<!-- END Weblink -->

<!-- BEGIN Webservices -->
* **Webservices**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Webservices -->

<!-- BEGIN WhoIsOnline -->
* **Who is online?**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [amersch](http://www.ilias.de/docu/goto_docu_usr_15114.html)
<!-- END WhoIsOnline -->

<!-- BEGIN Wiki -->
* **Wiki**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [abaulig1](http://www.ilias.de/docu/goto_docu_usr_44386.html)
	* Tester: [abaulig1](http://www.ilias.de/docu/goto_docu_usr_44386.html)
<!-- END Wiki -->

<!-- BEGIN WorkflowEngine -->
* **Workflow Engine**
	* 1st Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* Tester: [richtera](http://www.ilias.de/docu/goto_docu_usr_41247.html)
<!-- END WorkflowEngine -->

<!-- BEGIN Cmi5AndxAPIObject -->
* **cmi5/xAPI Object**
	* 1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
<!-- END Cmi5AndxAPIObject -->


Components in the Coordinator Model [Coordinator Model](maintenance-coordinator.md):

* **Refinery**
	* Coordinators: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html), [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Used in Directories: src/Refinery
* **UI-Service**
	* Coordinators: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html), [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Used in Directories: src/UI


The following directories are currently maintained under the [Coordinator Model](maintenance-coordinator.md):

* src/Refinery
* src/UI


The following directories are currently unmaintained:

* Services/Membership
* Services/OpenIdConnect
* Services/PHPUnit
* Modules/StudyProgrammeReference
* Services/AdministrativeNotification
* Services/FileServices
* Services/ResourceStorage
* Services/UI
* src/ResourceStorage
* src/Setup
