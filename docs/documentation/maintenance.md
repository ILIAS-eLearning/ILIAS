ILIAS Maintenance
=================
The development of the ILIAS source code is coordinated and maintained by a coordination team within the ILIAS network. Besides the main responsibilities for the project, several developers and users are maintaining certain modules of ILIAS.

# Coordination Team

* **Product Management**: [Matthias Kunkel]
* **Technical Board**: [Alexander Killing], [Michael Jansen], [Fabian Schmid], [Timon Amstutz], [Richard Klees]
* **Testcase Management**: [Fabian Kruse]
* **Documentation**: [Florian Suittenpointner]
* **Online Help**: [Alexandra Tödt]

# Maintainers
We highly appreciate to get new developers but we have to guarantee the sustainability and the quality of the ILIAS source code. The system is complex for new developers and they need to know the concepts of ILIAS that are described in the development guide.
 
Communication among developers that are working on a specific module needs to be assured. Final decision about getting write access to the ILIAS development system (Github) is handled by the product manager.
 
The following rules must be respected for everyone involved in the programming of ILIAS:

1. Decisions on new features or feature removals are made by the responsible first maintainer and the product manager in the Jour Fixe meetings after an open discussion.
2. All components have a first and second maintainer. Code changes are usually done by the first maintainer. The first maintainer may forward new implementations to the second maintainer.

Responsibilities of a component maintainer:

- Component maintainer must assure maintenance of their component for at least three years (approx. three ILIAS major releases).
- Component maintainers must agree to coordinate the development of their component with the product manager.
- Component maintainer are responsible for bug fixing of their component and get assigned related bugs automatically by the [Issue-Tracker](http://mantis.ilias.de).

ILIAS is currently maintained by two types of Maintainerships:

- First Maintainer
- Second Maintainer

The code base is deviced in several components:
<!-- REMOVE -->
* **ActiveRecord**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Administration**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)

* **BackgroundTasks**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Badges**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)

* **Bibliographic List Item**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [marko.glaubitz](http://www.ilias.de/docu/goto_docu_usr_28309.html)

* **Blog**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Booking Tool**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [e.coroian](http://www.ilias.de/docu/goto_docu_usr_37215.html)
	* Tester: [wolfganghuebsch](http://www.ilias.de/docu/goto_docu_usr_18455.html)

* **Bookmarks**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [miriamhoelscher](http://www.ilias.de/docu/goto_docu_usr_25370.html)

* **CSS / Templates**
	* 1st Maintainer: [braun](http://www.ilias.de/docu/goto_docu_usr_27123.html)
	* 2nd Maintainer: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)

* **Calendar**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)

* **Category and Repository**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [miriamhoelscher](http://www.ilias.de/docu/goto_docu_usr_25370.html)

* **Certificate**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [christian.hueser](http://www.ilias.de/docu/goto_docu_usr_41129.html)

* **Chat**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [AntoniaWeber](http://www.ilias.de/docu/goto_docu_usr_36934.html)

* **Cloud Object**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html)

* **Competence Management**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [wolfganghuebsch](http://www.ilias.de/docu/goto_docu_usr_18455.html)

* **Contacts**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: MISSING
	* Testcases: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
	* Tester: [AntoniaWeber](http://www.ilias.de/docu/goto_docu_usr_36934.html)

* **Course Management**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern

* **Cron Service**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Data**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Data Collection**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Tester: [kim.schmidt](http://www.ilias.de/docu/goto_docu_usr_28720.html)

* **Database**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **ECS Interface**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [bogen](http://www.ilias.de/docu/goto_docu_usr_13815.html)
	* Tester: [bogen](http://www.ilias.de/docu/goto_docu_usr_13815.html)

* **Exercise**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)
	* Tester: [miriamwegener](http://www.ilias.de/docu/goto_docu_usr_23051.html)

* **Export**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)

* **File**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [tloewen](http://www.ilias.de/docu/goto_docu_usr_41553.html)
	* Tester: [tloewen](http://www.ilias.de/docu/goto_docu_usr_41553.html)

* **Forum**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](http://www.ilias.de/docu/goto_docu_usr_14206.html)
	* Testcases: FH Aachen
	* Tester: [e.paulmann](http://www.ilias.de/docu/goto_docu_usr_8645.html)

* **GlobalCache**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Glossary**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)

* **Group**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern

* **HTTP**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **HTTP-Request**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **ILIAS Page Editor**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: FH Aachen

* **IndividualAssessment**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [dkloepfer](http://www.ilias.de/docu/goto_docu_usr_42712.html)
	* Testcases: [dkloepfer](http://www.ilias.de/docu/goto_docu_usr_42712.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)

* **Info Page**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Language Handling**
	* 1st Maintainer: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* 2nd Maintainer: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)

* **Learning Module HTML**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
	* Tester: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)

* **Learning Module ILIAS**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Balliel](http://www.ilias.de/docu/goto_docu_usr_18365.html)
	* Tester: [Balliel](http://www.ilias.de/docu/goto_docu_usr_18365.html)

* **Learning Module SCORM**
	* 1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* 2nd Maintainer: MISSING
	* Testcases: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)
	* Tester: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)

* **Login, Auth & Registration**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: FH Aachen
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Mail**
	* 1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](http://www.ilias.de/docu/goto_docu_usr_14206.html)
	* Testcases: [amersch](http://www.ilias.de/docu/goto_docu_usr_15114.html)
	* Tester: [amersch](http://www.ilias.de/docu/goto_docu_usr_15114.html)

* **Maps**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [dkloepfer](http://www.ilias.de/docu/goto_docu_usr_42712.html)
	* Testcases: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Tester: [miriamhoelscher](http://www.ilias.de/docu/goto_docu_usr_25370.html)

* **MathJax**
	* 1st Maintainer: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* 2nd Maintainer: MISSING
	* Testcases: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html)
	* Tester: [claudio.fischer](http://www.ilias.de/docu/goto_docu_usr_41113.html)

* **Media Pool**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)

* **MediaCast**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)
	* Tester: [berggold](http://www.ilias.de/docu/goto_docu_usr_22199.html)

* **Metadata**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [AntoniaWeber](http://www.ilias.de/docu/goto_docu_usr_36934.html)
	* Tester: [AntoniaWeber](http://www.ilias.de/docu/goto_docu_usr_36934.html)

* **My Workspace**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)

* **News**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)
	* Tester: [Thomas.schroeder](http://www.ilias.de/docu/goto_docu_usr_38330.html)

* **Notes and Comments**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)

* **Online Help**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Organisational Units**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* Testcases: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)
	* Tester: [wischniak](http://www.ilias.de/docu/goto_docu_usr_21896.html)

* **Personal Desktop**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Personal Profile**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)

* **Plugin Slots**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [PerPascalGrube](http://www.ilias.de/docu/goto_docu_usr_31492.html)

* **Poll**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Portfolio**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)
	* Tester: [KlausVorkauf](http://www.ilias.de/docu/goto_docu_usr_5890.html)

* **Precondition Handling**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **RBAC**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)

* **Rating**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)

* **SCORM Offline Player**
	* 1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* 2nd Maintainer: [sschneider](http://www.ilias.de/docu/goto_docu_usr_21741.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **SCORM Online Editor**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: pellemanns

* **SOAP**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Search**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: FH Aachen
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Session**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern

* **Setup**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Shibboleth Authentication**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: MISSING
	* Testcases: iLUB Universität Bern
	* Tester: iLUB Universität Bern

* **Statistics and Learning Progress**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [bromberger](http://www.ilias.de/docu/goto_docu_usr_198.html)
	* Tester: [suittenpointner](http://www.ilias.de/docu/goto_docu_usr_3458.html)

* **Study Programme**
	* 1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* 2nd Maintainer: [shecken](http://www.ilias.de/docu/goto_docu_usr_45419.html)
	* Testcases: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html)
	* Tester: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)

* **Survey**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [e.coroian](http://www.ilias.de/docu/goto_docu_usr_37215.html)

* **System Check**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Tagging**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: [mstuder](http://www.ilias.de/docu/goto_docu_usr_8473.html)
	* Testcases: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)
	* Tester: [skaiser](http://www.ilias.de/docu/goto_docu_usr_17260.html)

* **Test & Assessment**
	* 1st Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html)
	* 2nd Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* Testcases: [Fabian](http://www.ilias.de/docu/goto_docu_usr_27631.html)
	* Tester: SIG EA

* **User Service**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Web Access Checker**
	* 1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html)
	* 2nd Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html)
	* Testcases: [ttruffer](http://www.ilias.de/docu/goto_docu_usr_42894.html)
	* Tester: iLUB Universität Bern

* **Web Feed**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)
	* Tester: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html)

* **WebDAV**
	* 1st Maintainer: [fawinike](http://www.ilias.de/docu/goto_docu_usr_44474.html)
	* 2nd Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Weblink**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [nadine.bauser](http://www.ilias.de/docu/goto_docu_usr_34662.html)
	* Tester: [nadine.bauser](http://www.ilias.de/docu/goto_docu_usr_34662.html)

* **Webservices**
	* 1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html)
	* 2nd Maintainer: MISSING
	* Testcases: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Who is online?**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [atoedt](http://www.ilias.de/docu/goto_docu_usr_3139.html)
	* Tester: [amersch](http://www.ilias.de/docu/goto_docu_usr_15114.html)

* **Wiki**
	* 1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [abaulig1](http://www.ilias.de/docu/goto_docu_usr_44386.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)

* **Workflow Engine**
	* 1st Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* 2nd Maintainer: MISSING
	* Testcases: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html)
	* Tester: [TESTERS MISSING](http://www.ilias.de/docu/goto_docu_pg_64423_4793.html)


Components in the Service-Maintenance-Model:
* **UI-Service**
	* Coordinators: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html) 
	* Used in Directories: src/UI, 


The following directories are currently maintained unter the Classic-Maintenace-Model:
* Modules/Bibliographic
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/Blog
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/BookingManager
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/Category
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/CategoryReference
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/Chatroom
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Modules/Cloud
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/Course
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/CourseReference
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/DataCollection
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/Exercise
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/ExternalFeed
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/File
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/Forum
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Modules/Glossary
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/Group
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/HTMLLearningModule
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/IndividualAssessment
 (1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html))
* Modules/LearningModule
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/MediaCast
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/MediaPool
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/OrgUnit
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Modules/Poll
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/Portfolio
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/RemoteCategory
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RemoteCourse
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RemoteFile
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RemoteGlossary
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RemoteGroup
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RemoteLearningModule
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RemoteTest
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RemoteWiki
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/RootFolder
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/Scorm2004
 (1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html))
* Modules/ScormAicc
 (1st Maintainer: [ukohnle](http://www.ilias.de/docu/goto_docu_usr_21855.html))
* Modules/Session
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/StudyProgramme
 (1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html))
* Modules/Survey
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/SurveyQuestionPool
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/SystemFolder
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/Test
 (1st Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html))
* Modules/TestQuestionPool
 (1st Maintainer: [bheyser](http://www.ilias.de/docu/goto_docu_usr_14300.html))
* Modules/WebResource
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Modules/Wiki
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/WorkspaceFolder
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Modules/WorkspaceRootFolder
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/ADT
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/AccessControl
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/ActiveRecord
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/AdvancedMetaData
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/AuthApache
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/AuthShibboleth
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/Authentication
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/BackgroundTask
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/CAS
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/COPage
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Cache
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Calendar
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Certificate
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Services/Contact
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Services/Cron
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Services/Database
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/DiskQuota
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/Excel
 (1st Maintainer: MISSING)
* Services/Export
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Feeds
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/FileDelivery
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/FileSystem
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/FileUpload
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/Form
 (1st Maintainer: MISSING)
* Services/GlobalCache
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/Help
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Html
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Services/InfoScreen
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Init
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Language
 (1st Maintainer: [kunkel](http://www.ilias.de/docu/goto_docu_usr_115.html))
* Services/Mail
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Services/MainMenu
 (1st Maintainer: MISSING)
* Services/Maps
 (1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html))
* Services/MathJax
 (1st Maintainer: [fneumann](http://www.ilias.de/docu/goto_docu_usr_1560.html))
* Services/MetaData
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/News
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Notes
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Notifications
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Services/OnScreenChat
 (1st Maintainer: [mjansen](http://www.ilias.de/docu/goto_docu_usr_8784.html))
* Services/PHPUnit
 (1st Maintainer: MISSING)
* Services/PersonalDesktop
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/PersonalWorkspace
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Preview
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/RTE
 (1st Maintainer: MISSING)
* Services/Rating
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Repository
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/SOAPAuth
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Search
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Style
 (1st Maintainer: [braun](http://www.ilias.de/docu/goto_docu_usr_27123.html))
* Services/Survey
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/SystemCheck
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Tagging
 (1st Maintainer: [akill](http://www.ilias.de/docu/goto_docu_usr_149.html))
* Services/Tracking
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/UIComponent
 (1st Maintainer: MISSING)
* Services/UICore
 (1st Maintainer: MISSING)
* Services/User
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/Utilities
 (1st Maintainer: MISSING)
* Services/VirusScanner
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/WebAccessChecker
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))
* Services/WebDAV
 (1st Maintainer: [fawinike](http://www.ilias.de/docu/goto_docu_usr_44474.html))
* Services/WebServices
 (1st Maintainer: [smeyer](http://www.ilias.de/docu/goto_docu_usr_191.html))
* Services/WorkflowEngine
 (1st Maintainer: [mbecker](http://www.ilias.de/docu/goto_docu_usr_27266.html))
* src/DI
 (1st Maintainer: MISSING)
* src/Data
 (1st Maintainer: [rklees](http://www.ilias.de/docu/goto_docu_usr_34047.html))
* src/HTTP
 (1st Maintainer: [fschmid](http://www.ilias.de/docu/goto_docu_usr_21087.html))


The following directories are currently maintained unter the Service-Maintenace-Model:
* src/UI
 (Coordinator: [amstutz](http://www.ilias.de/docu/goto_docu_usr_26468.html))


The following directories are currently unmaintained:
* Modules/Folder
* Modules/GroupReference
* Modules/ItemGroup
* Services/Accessibility
* Services/Accordion
* Services/Administration
* Services/AdvancedEditing
* Services/Awareness
* Services/Badge
* Services/Block
* Services/Booking
* Services/Bookmarks
* Services/Captcha
* Services/Chart
* Services/Classification
* Services/Clipboard
* Services/Component
* Services/Container
* Services/ContainerReference
* Services/Context
* Services/CopyWizard
* Services/DataSet
* Services/DidacticTemplate
* Services/Dom
* Services/Environment
* Services/EventHandling
* Services/Exceptions
* Services/Frameset
* Services/History
* Services/Http
* Services/Imprint
* Services/JSON
* Services/JavaScript
* Services/LDAP
* Services/License
* Services/Link
* Services/LinkChecker
* Services/Locator
* Services/Logging
* Services/Math
* Services/MediaObjects
* Services/Membership
* Services/Migration
* Services/Multilingualism
* Services/Navigation
* Services/Notification
* Services/Object
* Services/PDFGeneration
* Services/Password
* Services/PermanentLink
* Services/PrivacySecurity
* Services/QTI
* Services/Radius
* Services/Randomization
* Services/Registration
* Services/Skill
* Services/Table
* Services/Taxonomy
* Services/TermsOfService
* Services/Transformation
* Services/Tree
* Services/Verification
* Services/XHTMLPage
* Services/XHTMLValidator
* Services/Xml
* Services/YUI
* Services/jQuery
