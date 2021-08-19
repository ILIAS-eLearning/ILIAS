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

* **ActiveRecord**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Administration**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **BackgroundTasks**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Badges**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)

* **Bibliographic List Item**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)

* **Blog**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [PaBer](https://docu.ilias.de/goto_docu_usr_33766.html)

* **Booking Tool**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [e.coroian](https://docu.ilias.de/goto_docu_usr_37215.html)
	* Tester: [wolfganghuebsch](https://docu.ilias.de/goto_docu_usr_18455.html)

* **Bookmarks**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)

* **Calendar**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)

* **Category and Repository**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)

* **Certificate**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [m-gregory-m](https://docu.ilias.de/goto_docu_usr_51332.html)

* **Chat**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)

* **Cloud Object**
	* 1st Maintainer: [ttruffer](https://docu.ilias.de/goto_docu_usr_42894.html)
	* 2nd Maintainer: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	* Testcases: [ttruffer](https://docu.ilias.de/goto_docu_usr_42894.html)
	* Tester: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)

* **cmi5/xAPI Object**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: [bheyser](https://docu.ilias.de/goto_docu_usr_14300.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [EMok](https://docu.ilias.de/goto_docu_usr_80682.html)

* **Comments**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)

* **Competence Management**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [ioanna.mitroulaki](https://docu.ilias.de/goto_docu_usr_72564.html)

* **Contacts**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **ContentPage**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Course Management**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [lauener](https://docu.ilias.de/goto_docu_usr_8474.html)
	* Tester: [lauener](https://docu.ilias.de/goto_docu_usr_8474.html), [TESTER MISSING FOR LOC](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Cron Service**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **CSS / Templates**
	* 1st Maintainer: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)

* **Dashboard**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [silvia.marine](https://docu.ilias.de/goto_docu_usr_71642.html)

* **Data**
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Data Collection**
	* 1st Maintainer: [ttruffer](https://docu.ilias.de/goto_docu_usr_42894.html)
	* 2nd Maintainer: N.A.
	* Testcases: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* Tester: [mona.schliebs](https://docu.ilias.de/goto_docu_usr_60222.html)

* **Database**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Didactic Templates**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **ECS Interface**
	* 1st Maintainer: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Exercise**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [miriamwegener](https://docu.ilias.de/goto_docu_usr_23051.html)

* **Export**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

* **File**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [scarlino](https://docu.ilias.de/goto_docu_usr_56074.html)
	* Tester: Heinz Winter, CaT

* **Forum**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](https://docu.ilias.de/goto_docu_usr_14206.html)
	* Testcases: FH Aachen
	* Tester: [e.coroian](https://docu.ilias.de/goto_docu_usr_37215.html) und [anna.s.vogel](https://docu.ilias.de/goto_docu_usr_71954.html) 

* **General Kiosk-Mode**
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **GlobalCache**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **GlobalScreen**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Glossary**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)

* **Group**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)

* **HTTP-Request**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **ILIAS Page Editor**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: FH Aachen

* **IndividualAssessment**
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Info Page**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

* **ItemGroup**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)

* **Language Handling**
	* 1st Maintainer: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* 2nd Maintainer: [katrin.grosskopf](https://docu.ilias.de/goto_docu_usr_68340.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **Learning History**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)

* **Learning Module HTML**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: n.n., Qualitus - for ILIAS 7: [bgoch](https://docu.ilias.de/goto_docu_usr_79405.html)

* **Learning Module ILIAS**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Balliel](https://docu.ilias.de/goto_docu_usr_18365.html)
	* Tester: [Balliel](https://docu.ilias.de/goto_docu_usr_18365.html)

* **Learning Module SCORM**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: N.A.
	* Testcases: n.n., Qualitus
	* Tester: n.n., Qualitus

* **Learning Sequence**
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [scarlino](https://docu.ilias.de/goto_docu_usr_56074.html)
	* Tester: [mglaubitz](https://docu.ilias.de/goto_docu_usr_28309.html)

* **Like**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Logging**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Login, Auth & Registration**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [bheyser](https://docu.ilias.de/goto_docu_usr_14300.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [vimotion](https://docu.ilias.de/goto_docu_usr_25105.html), [ILIAS_LM](https://docu.ilias.de/goto_docu_usr_14109.html) (OpenID)

* **LTI**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [stv](https://docu.ilias.de/goto_docu_usr_45359.html)

* **LTI Consumer**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: [bheyser](https://docu.ilias.de/goto_docu_usr_14300.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)

* **Mail**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](https://docu.ilias.de/goto_docu_usr_14206.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **MainMenu**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **Maps**
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: N.A.
	* Testcases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)

* **MathJax**
	* 1st Maintainer: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
	* 2nd Maintainer: N.A.
	* Testcases: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
	* Tester: [resi](https://docu.ilias.de/goto_docu_usr_72790.html)

* **Media Objects**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)

* **Media Pool**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)

* **MediaCast**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)

* **Membership**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Metadata**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [daniela.weber](https://docu.ilias.de/goto_docu_usr_40672.html)
	* Tester: [daniela.weber](https://docu.ilias.de/goto_docu_usr_40672.html)

* **News**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)

* **Notes and Comments**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)

* **Online Help**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)

* **Organisational Units**
	* 1st Maintainer: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* 2nd Maintainer: [bheyser](https://docu.ilias.de/goto_docu_usr_14300.html)
	* Testcases: [wischniak](https://docu.ilias.de/goto_docu_usr_21896.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)

* **Personal Profile**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

* **Plugin Slots**
	* 1st Maintainer: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* 2nd Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **Poll**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [Qndrs](https://docu.ilias.de/goto_docu_usr_42611.html)

* **Portfolio**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [KlausVorkauf](https://docu.ilias.de/goto_docu_usr_5890.html)
	* Tester: [KlausVorkauf](https://docu.ilias.de/goto_docu_usr_5890.html) and [ob1013](https://docu.ilias.de/goto_docu_usr_71172.html) (for Portfolio Template)

* **Precondition Handling**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [mkloes](https://docu.ilias.de/goto_docu_usr_22174.html)

* **Rating**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

* **RBAC**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **Rating**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: MISSING
	* Testcases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)

* **SAML**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: Alexander Grundkötter, Qualitus

* **SCORM Offline Player**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: [sschneider](https://docu.ilias.de/goto_docu_usr_21741.html)
	* Testcases: [sschneider](https://docu.ilias.de/goto_docu_usr_21741.html)
	* Tester: [sschneider](https://docu.ilias.de/goto_docu_usr_21741.html)

* **SCORM Online Editor**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [Hester](https://docu.ilias.de/goto_docu_usr_31687.html)

* **Search**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [Qndrs](https://docu.ilias.de/goto_docu_usr_42611.html)

* **Session**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)

* **Setup**
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [fwolf](https://docu.ilias.de/goto_docu_usr_29018.html)

* **Shibboleth Authentication**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)

* **SOAP**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Staff**
	* 1st Maintainer: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* 2nd Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)

* **Statistics and Learning Progress**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [suittenpointner](https://docu.ilias.de/goto_docu_usr_3458.html)
	* Tester: [suittenpointner](https://docu.ilias.de/goto_docu_usr_3458.html)

* **Study Programme**
	* 1st Maintainer: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* 2nd Maintainer: [shecken](https://docu.ilias.de/goto_docu_usr_45419.html)
	* Testcases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: Florence Seydoux, s+r

* **Survey**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)

* **System Check**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Tagging**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: [mstuder](https://docu.ilias.de/goto_docu_usr_8473.html)
	* Testcases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)

* **Tasks**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Taxonomy**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: Tested separately in each module that supports taxonomies
	* Tester: Tested separately in each module that supports taxonomies

* **Test & Assessment**
	* 1st Maintainer (comm.): [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
	* 2nd Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Testcases: SIG E-Assessment
	* Tester: Stefania Akgül (CaT), Stefanie Allmendinger (FAU), [dehling](https://docu.ilias.de/goto_docu_usr_12725.html), [kderr](https://docu.ilias.de/goto_docu_usr_28900.html), [sdittebrand](https://docu.ilias.de/goto_docu_usr_77841.html), [ioanna.mitroulaki](https://docu.ilias.de/goto_docu_usr_72564.html), [rabah](https://docu.ilias.de/goto_docu_usr_40218.html), [vreuschen](https://docu.ilias.de/goto_docu_usr_14382.html)

* **Tree**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **User Service**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)

* **VirusScanner**
	* 1st Maintainer: [rschenk](https://docu.ilias.de/goto_docu_usr_18065.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Web Access Checker**
	* 1st Maintainer: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* 2nd Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* Testcases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)

* **Web Feed**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)

* **WebDAV**
	* 1st Maintainer: [fawinike](https://docu.ilias.de/goto_docu_usr_44474.html)
	* 2nd Maintainer: [rheer](https://docu.ilias.de/goto_docu_usr_47872.html)
	* Testcases: [fawinike](https://docu.ilias.de/goto_docu_usr_44474.html)
	* Tester: [kauerswald](https://docu.ilias.de/goto_docu_usr_70029.html)

* **Weblink**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [nadine.bauser](https://docu.ilias.de/goto_docu_usr_34662.html)
	* Tester: [nadine.bauser](https://docu.ilias.de/goto_docu_usr_34662.html)

* **Webservices**
	* 1st Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Who is online?**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
	* Tester: [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)

* **Wiki**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [abaulig1](https://docu.ilias.de/goto_docu_usr_44386.html)
	* Tester: [abaulig1](https://docu.ilias.de/goto_docu_usr_44386.html)

* **Workflow Engine**
	* 1st Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* 2nd Maintainer: N.A.
	* Testcases: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Tester: [richtera](https://docu.ilias.de/goto_docu_usr_41247.html)


Components in the Coordinator Model [Coordinator Model](maintenance-coordinator.md):

* **Refinery**
	* Coordinators: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html), [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Used in Directories: src/Refinery

* **UI-Service**
	* Coordinators: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html), [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Test cases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Tester: [kauerswald](https://docu.ilias.de/goto_docu_usr_70029.html)
    * Used in Directories: src/UI


The following directories are currently maintained under the [Coordinator Model](maintenance-coordinator.md):

* src/Refinery
* src/UI


The following directories are currently unmaintained:

* Services/DiskQuota
* Services/Membership
* Services/OpenIdConnect
* Services/PHPUnit
* Services/QTI
* Services/Randomization
