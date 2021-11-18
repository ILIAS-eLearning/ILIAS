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
source code. The system is complex for new developers, and they need to know the concepts of ILIAS that are described 
in the development guide. 
 
Communication among developers that are working on a specific component needs to be assured. Final decision about 
getting write access to the ILIAS development system (Github) is handled by the product manager. A maintainer must be
responsive to queries of the PM and Technical Board. If Maintainers are unresponsive for an extended period of time, 
the Technical Board along with the Product Manager will take according measures.

## Competencies
There is a set of competencies that attaches to role maintainer to a developer if she or he claims one of them for a 
specific components. Those competencies come without duties and responsibilities other than being responsive towards the
Product Manager and the Technical Board. Maintainers can not be forced into taking action, they provide contributions as
a voluntary contribution towards the sustainability and the quality of the ILIAS source code. 

The following competencies must be claimed by at least one maintainer, otherwise the component can not be sustained in 
future versions of ILIAS:
* Merge and Close PRs
* Relable, Reassign, Close, Reopen Mantis Tickets.

It is possible that a specific component only features a maintainer for these two sets of competencies. In this case
not feature can be added to the component. Maintainers willing to claim further competencies in this component must
contact the Product Manager.

The following competencies must be claimed by at least one maintainer if future features should be added to a component:
* Decision upon new Features with PM in JF
* Approve New Test Cases
* Distribute Competencies for a Component

If at least one maintainer for each competencies is listed, new features can be introduced according the development
process.

There are further competencies that can be freely assigned by the maintainer that has the according distribution claim:
* Commit Changes to Repo in Component

## Becoming a Maintainer

Applications for maintainerships, meaning claiming one competencies for one of the above component directly be sent to 
the maintainer listed as claiming the competencies to "Distribute Competencies for a Component". If no maintainer is listed
for this competency, then to the application can be handed in to the product manager. The product manager together with 
the technical board decide on who becomes a maintainer claiming a specific competency. 

Maintainerships are listed with the name of the maintainer for a specific competency in a specific module. In
addition the company the maintainer is working for can be listed, too. In this second case, the company has the right to
propose an alternative maintainer at any time. In particular, if the maintainer resigns from his claim for a specific 
competency, a proposal for a new maintainer by the company of the old maintainer will be preferred, if the company recently invested
substantially in the general condition of the component and the proposed maintainer meets the criteria.

## Maintenance Models
The listing of maintainers for specific competencies generates a great amount of flexibilities and allows people to perform
work in their area of expertise but might be somewhat overwhelming for newcomers. For this reason we encourage maintainers
to use and list one of the following models of how this competencies can be used and distributed. 

### Classic Model
This is the classical way of handling a component. Also referred to as "Component Maintainer" Model. 
One maintainer claims all the competencies for a specific component. In most cases a second maintainer is listed for 
each competency except "Distribute Competencies for a Component", who can take responsibility in case of absence of the 
first maintainer. All PRs, Mantis Issue, JF decisions and extensions are primarily handled by the first and second maintainer. 
See:"[Classic Model](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance.md#maintainers)" for
a detailed description.

### Coordinator Model
One or multiple coordinator share the competencies listed above but focus their processes towards contributions from
other developers. See "[Coordinator Model](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md) ""
for a detailed description.

### Stay Alive Model
Formally referred to as "Implicit Maintainer". In this model at least one maintainer claims to competencies involved for 
handling bugs and handling mantis issues. It merely allows the component to stay alive in future version but prevents 
any future extension.

### Custom
Some custom distribution of competencies around maintainers allowing to adapt for specific needs. Maintainers choosing
this model are encourage to list their specific workflows and ways to contribute in some readme of the component.


## Tracking Maintainerships
Maintainerships are tracked in maintenance.json files placed in the root of the corresponding components of ILIAS. The 
file contains the following fields:

* **maintenance_model**: Currently there are the above for mentioned models possible entries for this field
	* [Classic Model](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance.md#maintainers)
	* [Coordinator](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md#coordinator-model)
	* Stay Alive
	* Custom
* **"Distribute Competencies for a Component"**: One entry in the form "\<username> (\<userid>)" pointing to a valid user on
  https://docu.ilias.de.
* **"Merge and Close PRs"**: One or multiple entries in the form "\<username> (\<userid>)" pointing to a valid user on 
	https://docu.ilias.de. 
* **"Relable, Reassign, Close, Reopen Mantis Tickets"**: One or multiple entries in the form "\<username> (\<userid>)" pointing to a valid user on
	  https://docu.ilias.de.
* **"Decision upon new Features with PM in JF"**: One or multiple entries in the form "\<username> (\<userid>)" pointing to a valid user on
  https://docu.ilias.de.
* **"Approve New Test Cases"**: One or multiple entries in the form "\<username> (\<userid>)" pointing to a valid user on
  https://docu.ilias.de.
* **"Commit Changes to Repo in Component"**: One or multiple entries in the form "\<username> (\<userid>)" pointing to a valid user on
  https://docu.ilias.de.
* **"tester"**: One entry in the form "<username> (<userid>)" pointing to a valid user on 
	https://docu.ilias.de.
* **"testcase_writer"**: One entry in the form "<username> (<userid>)" pointing to a valid user on 
	https://docu.ilias.de.

## Current Maintainerships (Todo)

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
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html); from v.9 [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
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
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [EMok](https://docu.ilias.de/goto_docu_usr_80682.html)

* **Comments**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)

* **Competence Management**
	* 1st Maintainer: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
	* 2nd Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
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

* **Favourites**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

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
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [vimotion](https://docu.ilias.de/goto_docu_usr_25105.html), [ILIAS_LM](https://docu.ilias.de/goto_docu_usr_14109.html) (OpenID), [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html) (Shibboleth), Alexander Grundkötter, Qualitus (SAML)

* **LTI**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [stv](https://docu.ilias.de/goto_docu_usr_45359.html)

* **LTI Consumer**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)

* **Mail**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: [nadia](https://docu.ilias.de/goto_docu_usr_14206.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: Till Lennart Vogt/Test-Team OWL

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
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html), [wolfganghuebsch](https://docu.ilias.de/goto_docu_usr_18455.html) (for ILIAS 7)

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
	* 2nd Maintainer: N.A.
	* Testcases: [wischniak](https://docu.ilias.de/goto_docu_usr_21896.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)

* **PDF**
	* 1st Maintainer: [gvollbach](https://docu.ilias.de/goto_docu_usr_25234.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)

* **Personal and Shared Resources**
	* 1st Maintainer: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [scarlino](https://docu.ilias.de/goto_docu_usr_56074.html)

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

* **Terms of Services**
	* 1st Maintainer: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	* 2nd Maintainer: N.A.
	* Testcases: Stefania Akgül (CaT)
	* Tester: Heinz Winter (CaT)

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

* **Virus Scanner**
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
	* Testcases: N.N., Uni Köln
	* Tester: N.N., Uni Köln

* **Workflow Engine**
	* 1st Maintainer: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* 2nd Maintainer: N.A.
	* Testcases: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
	* Tester: [richtera](https://docu.ilias.de/goto_docu_usr_41247.html)

* **xAPI/cmi5**
	* 1st Maintainer: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* 2nd Maintainer: N.A.
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)


Components in the Coordinator Model [Coordinator Model](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md):

* **Refinery**
	* Coordinators: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html), [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Used in Directories: src/Refinery

* **UI-Service**
	* Coordinators: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html), [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Test cases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Tester: [kauerswald](https://docu.ilias.de/goto_docu_usr_70029.html)
    * Used in Directories: src/UI


The following directories are currently maintained under the [Coordinator Model](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/maintenance-coordinator.md):

* src/Refinery
* src/UI


The following directories are currently unmaintained:

* Services/DiskQuota
* Services/Membership
* Services/OpenIdConnect
* Services/PHPUnit
* Services/QTI
* Services/Randomization
* src/ArtifactBuilder
