ILIAS Maintenance
=================
The development of ILIAS is coordinated by the Product Manager and the
Technical Board. Many decisions are taken at the biweekly Jour Fixe, which is
open for participation to everyone. The source code is maintained by a growing
group of people, ranging from devoted maintainers to regular or even one-time
contributors.

# Special Roles

* **Product Management**: [Matthias Kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
* **Technical Board**: [Michael Jansen](https://docu.ilias.de/goto_docu_usr_8784.html), [Stephan Kergomard](https://docu.ilias.de/goto_docu_usr_44474.html), [Richard Klees](https://docu.ilias.de/goto_docu_usr_34047.html), [Nico Roeser](https://docu.ilias.de/goto_docu_usr_72730.html), [Fabian Schmid](https://docu.ilias.de/goto_docu_usr_21087.html)
* **Testcase Management**: [Fabian Kruse](https://docu.ilias.de/goto_docu_usr_27631.html)
* **Release Management**: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
* **Technical Documentation**: [Ann-Christin Gruber](https://docu.ilias.de/goto_docu_usr_94205.html)
* **Online Help**: [Alexandra Tödt](https://docu.ilias.de/goto_docu_usr_3139.html)

# Authorities
The ILIAS community strives to create and maintain a secure, reliable, and
adaptable learning management. We foster participation by a diverse set of
developers, designers, testers and other contributors, but we also have to
guarantee the sustainability and the quality of the ILIAS source code.

To make sure people with diverse backgrounds and capabilities can participate
in our community and contribute to the development of ILIAS and its code base,
we split the code into units (often called components, even though the term
is hard to define) and we define a set of authorities community members can have
concerning these units of code. We understand an authority as the counterpart of
a responsibility: the people having the authorities to do something in a unit of
code also assume the responsibility for the corresponding functions.

For the context of ILIAS, we define **four** different authorities:

1. **Authority to Sign off on Conceptual Changes**: The people listed here are
authorised to decide on the future course of the component. Depending on the
social organisation, this decision is taken collectively or individually. In any
case a close coordination with the people holding *the Authority to Sign off on
Code Changes* will be necessary. The people listed here are authorised to
set the checked and attendance flag for features to be discussed at the Jour Fixe.
They should be contacted first for changes to the functionality of a component.
2. **Authority to Sign off on Code Changes**: The people listed here are
authorised to contribute directly to the code base of the ILIAS core. They are
authorised to commit directly to the codebase of the ILIAS core and to merge
Pull Requests. They are the ones deciding on the structure and quality of the
code of a component.
3. **Authority to Curate Test Cases**: The people listed here are
authorised to modify and delete existing test cases. They also have the final
say on new test cases and can ask for modifications. They will be the ones
contacted if there are questions concerning the test cases for a component.
4. **Authority to (De-)Assign Authorities**: The people listed here are
authorised to assign and deassign other people to the authorities of a component
They are the only ones allowed to modify the `maintanance.json` of a component.

Each of these authorities can be held by a different set of people. This means
that the social organisation of different groups working on different parts of
the code of ILIAS can be different.
Right now ILIAS knows a few different social structures for the maintenance of
units in the code of ILIAS:

* In the **"Classic Model"** all authorities are concentrated in one person and
this person works mostly alone.
* In the **Coordinator Model** all authorities are concentrated in one or more
people and they work together with other developers in the community to improve
the code.
* In the **"Test and Assessment Model"** the authorities **to Sign off on Conceptual
Changes**, **to Curate Test Cases**, and **to (De-)Assign Authorities**
lie with one person and the **Authority to Sign off on Code Changes** with two
others.

More will surely emerge as the optimal solution for each unit is found.

# Responsibilites
Independently of the social organisation, for each [component](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/components-and-directories.md) the following
responsibilites need to be assumed:

* All people holding an authority must agree to coordinate the development
of their [component](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/components-and-directories.md)
with the Product Manager and with the people maintaining other units of code.
* One of the people holding either the **Authority to Sign off on Code Changes** or
the **Authority to Sign off on Conceptual Changes** gets assigned related bugs
automatically by the [Issue-Tracker](https://mantis.ilias.de). S/he is responsible
to make sure all issues receive a response within the defined time frame and are
either fixed in a timely manner or postponed/closed with a solid explanation.
* The people holding the **Authority to Sign off on Code Changes** are responsible
for pull requests to their component and get assigned related pull requests
according to the [Rules for Maintainers and Coordinators
assigned to PRs](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#rules-for-community-members-assigned-to-prs).
* The person/people holding the **Authority to (De-)Assign Authorities**
coordinate assignments of authorities with the Product Manager and the Technical
Board, who hold a vetoing power over these decisions.

# Additional Rules and Guidelines
* Although the first decision on new features or feature removals in a unit of
code lie with the person/people holding the **Authority to Sign off on Conceptual
Changes** the final decisions are made by them together with the Product
Manager during the Jour Fixe meetings after an open discussion.
* If nobody holds the **Authority to (De-)Assign Authorities** for a
[component](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/components-and-directories.md),
it defaults to the Technical Board.
* Final decision about getting write access to the ILIAS development system
(GitHub) is handled by the Product Manager together with the Technical Board.
* Authorities are listed with the name of the person holding the authority. In
addition the company the person is working for can be listed, too.
* If a company is listed for the last assignee of the **Authority to (De-)Assign
Authorities** the company can propose a prioritized candidate for the
succession.

## Process to Change Authorities
* To apply for an `Authority` of a `Component` that currently has a holder of the
`Authority to (De-)Assign Authorities`, it is recommended to contact this person
before taking the next step.
* Please provide a pull request against the `trunk`-branch of the [official ILIAS Repository](https://github.com/ILIAS-eLearning/ILIAS)
to change assignments to `Authorities` for some `Component`. Please explain in
the comment of the pull request why this change should be made. Also shortly
report your exchange with the person holding the `Authority to (De-)Assign
Authorities`, if you are not this person. Add the tags `authorities` and
`documentation`.
* The PR will be assigned to all persons with `Authorities to (De-)Assign Authority`.
These persons are asked to document in the PR if they accept the new assignment
or not. If they accept the assignment, they should also add the tag `technical board`.
* The Product Manager and the Technical Board will discuss the request as quickly
as possible. Depending on the `Authority`, the `Component`, and their role in the
community, the new assignees might be invited for a short talk to get to know them
and their plans for the `Component` better.
* If the Product Manager and the Technical Board do not veto the new assignment,
they take the pull request for the next Jour Fixe for an announcement and merge it
afterwards.
* If you want to give up an `Authority` for a `Component`, please contact all persons
with the `Authority to (De-)Assign Authorities` in that `Component`. If you are the
last person holding the `Authority to (De-)Assign Authorities`, please contact
the Product Manager and the Technical Board per email instead.
* If the person with `Authority to (De-)Assign Authorities` for a `Component` wants
to remove someone from an assignment to an `Authority` in said `Component`, she should
open a PR against the `trunk`-branch of the [official ILIAS Repository](https://github.com/ILIAS-eLearning/ILIAS)
and tag it with `authorities`, `documentation` and `jour fixe`. The change will
then be announced on the next Jour Fixe.
* If a `Component` lacks an `Authority to Sign off on Code Changes` or if the holder
of the last `Authority to Sign off on Code Chagnes` would like to pass the
responsibility over to somebody else, the `Component` is added to the agenda of
the Jour Fixe by the Product Manager.


## How Authority Assignments are Stored
Authorities are tracked in `maintenance.json` files placed in the root of the
corresponding [component](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/components-and-directories.md)
of ILIAS. The file contains the following fields:

* **"Authority to Sign off on Conceptual Changes"**:
    An array in the form [ `<username> (<userid>, <company> (<company_page>)` ]
    pointing to valid users on https://docu.ilias.de.
* **"Authority to Sign off on Code Changes"**:
    An array in the form [ `<username> (<userid>), <company> (<company_page>)` ]
    pointing to valid users/companies on https://docu.ilias.de.
* **"Authority to Curate Test Cases"**:
    An array in the form [ `<username> (<userid>), <company> (<company_page>)` ]
    pointing to valid users on https://docu.ilias.de.
* **"Authority to (De-)Assign Authorities"**:
    An array in the form [ `<username> (<userid>), <company> (<company_page>)` ]
    pointing to valid users on https://docu.ilias.de.
* **"Tester"**:
    An array in the form [ `<username> (<userid>), <company> (<company_page>)` ]
    pointing to valid users on https://docu.ilias.de.
* **"Assignee for Issues"**:
    A string in the form `<username> (<userid>), <company> (<company_page>)`
    pointing to valid users on https://docu.ilias.de.
* **"Assignee for Security Reports"**:
    A string in the form `<username> (<userid>), <company> (<company_page>)`
    pointing to valid users on https://docu.ilias.de.
* **"Unit-specific Guidelines, Rules, and Regulations"**:
    Link to a file `COMMUNITY.md` in the root of the unity in the trunk branch on
    GitHub specifying the guidelines, rules, and regulations for collaboration.

## Current Maintainerships

[//]: # (BEGIN ActiveRecord)

* **ActiveRecord**
	* Authority to Sign off on Conceptual Changes: [[fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases:[fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Testcases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ActiveRecord)

[//]: # (BEGIN Administration)

* **Administration**
    * Authority to Sign off on Conceptual Changes: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
	* Authority to Sign off on Code Changes: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
        , [lscharmer](https://docu.ilias.de/goto_docu_usr_87863.html)
    * Authority to Curate Test Cases: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
        , [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [fneumann (Databay AG)](https://docu.ilias.de/goto_docu_usr_1560.html)
        , [lscharmer (Databay AG)](https://docu.ilias.de/goto_docu_usr_87863.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
    * Assignee for Security Issues: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Administration)

[//]: # (BEGIN AdministrativeNotifications)

* **Administrative Notifications**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END AdministrativeNotifications)

[//]: # (BEGIN BackgroundTasks)

* **BackgroundTasks**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BackgroundTasks)

[//]: # (BEGIN Badges)

* **Badges**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Badges)

[//]: # (BEGIN BibliographicListItem)

* **Bibliographic List Item**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BibliographicListItem)

[//]: # (BEGIN Blog)

* **Blog**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [PaBer](https://docu.ilias.de/goto_docu_usr_33766.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Blog)

[//]: # (BEGIN BookingTool)

* **Booking Tool**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [wolfganghuebsch](https://docu.ilias.de/goto_docu_usr_18455.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BookingTool)

[//]: # (BEGIN Calendar)

* **Calendar**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
        , [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Calendar)

[//]: # (BEGIN CategoryAndRepository)

* **Category and Repository**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
        ,  [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CategoryAndRepository)

[//]: # (BEGIN Certificate)

* **Certificate**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [m-gregory-m](https://docu.ilias.de/goto_docu_usr_51332.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Certificate)

[//]: # (BEGIN Chat)

* **Chat**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Chat)

[//]: # (BEGIN cmi5AndxAPIObject)

* **cmi5/xAPI Object**
    * Authority to Sign off on Conceptual Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Sign off on Code Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Curate Test Cases: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to (De-)Assign Authorities: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* Tester: [EMok](https://docu.ilias.de/goto_docu_usr_80682.html)
    * Assignee for Security Reports: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Assignee for Security Issues: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END cmi5AndxAPIObject)

[//]: # (BEGIN Comments)
* **Comments**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Comments)

[//]: # (BEGIN CompetenceManagement)

* **Competence Management**
    * Authority to Sign off on Conceptual Changes: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Authority to Sign off on Code Changes: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
        , [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
    * Authority to (De-)Assign Authorities: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
	* Tester: [ioanna.mitroulaki](https://docu.ilias.de/goto_docu_usr_72564.html)
    * Assignee for Security Reports: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Assignee for Security Issues: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CompetenceManagement)

[//]: # (BEGIN Component)

* **Component**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
        ,  [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Component)

[//]: # (BEGIN Contacts)

* **Contacts**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Contacts)

[//]: # (BEGIN ContentPage)

* **ContentPage**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ContentPage)

[//]: # (BEGIN CourseManagement)

* **Course Management**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
        , [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [lauener](https://docu.ilias.de/goto_docu_usr_8474.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [lauener](https://docu.ilias.de/goto_docu_usr_8474.html)
	  , [TESTER MISSING FOR LOC](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CourseManagement)

[//]: # (BEGIN CronService)

* **Cron Service**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CronService)

[//]: # (BEGIN CSSAndTemplates)

* **CSS / Templates**
    * Authority to Sign off on Conceptual Changes: [yvseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
      , [catenglaender](https://docu.ilias.de/goto_docu_usr_79291.html)
    * Authority to Sign off on Code Changes: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
      , [catenglaender](https://docu.ilias.de/goto_docu_usr_79291.html)
    * Authority to Curate Test Cases: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
      , [yvseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Authority to (De-)Assign Authorities: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	* Tester: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Reports: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
    * Assignee for Security Issues: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CSSAndTemplates)

[//]: # (BEGIN Dashboard)

* **Dashboard**
    * Authority to Sign off on Conceptual Changes: [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
        , [lscharmer](https://docu.ilias.de/goto_docu_usr_87863.html)
    * Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
        , [lscharmer](https://docu.ilias.de/goto_docu_usr_87863.html)
        , [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [iszmais (Databay AG)](https://docu.ilias.de/goto_docu_usr_65630.html)
        , [lscharmer (Databay AG)](https://docu.ilias.de/goto_docu_usr_87863.html)
	* Tester: [silvia.marine](https://docu.ilias.de/goto_docu_usr_71642.html)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Assignee for Security Issues: [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Dashboard)

[//]: # (BEGIN Data)

* **Data**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Data)

[//]: # (BEGIN DataCollection)

* **Data Collection**
    * Authority to Sign off on Conceptual Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html), [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)
    * Authority to Sign off on Code Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
        , [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Authority to Curate Test Cases: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html), [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)
    * Authority to (De-)Assign Authorities: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html), [oliver.samoila (Databay AG)](https://docu.ilias.de/goto_docu_usr_26160.html)
	* Tester: [mona.schliebs](https://docu.ilias.de/goto_docu_usr_60222.html)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Assignee for Security Issues: [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DataCollection)

[//]: # (BEGIN DataProtection)

* **Data Protection**
	* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [lscharmer](https://docu.ilias.de/goto_docu_usr_87863.html)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [[TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DataProtection)

[//]: # (BEGIN Database)

* **Database**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
        , [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Database)

[//]: # (BEGIN DidacticTemplates)

* **Didactic Templates**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DidacticTemplates)

[//]: # (BEGIN ECSInterface)

* **ECS Interface**
    * Authority to Sign off on Conceptual Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Authority to Sign off on Code Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Authority to Curate Test Cases: [SIG CampusConnect und ECS(A)](https://docu.ilias.de/goto_docu_grp_7893.html)
    * Authority to (De-)Assign Authorities: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
	* Tester: [SIG CampusConnect und ECS(A)](https://docu.ilias.de/goto_docu_grp_7893.html)
    * Assignee for Security Reports: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Assignee for Security Issues: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ECSInterface)

[//]: # (BEGIN EmployeeTalk)

* **EmployeeTalk**
    * Authority to Sign off on Conceptual Changes: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
    * Authority to Sign off on Code Changes: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
        , [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Authority to Curate Test Cases: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
    * Authority to (De-)Assign Authorities: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)
    * Assignee for Security Reports: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
    * Assignee for Security Issues: [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END EmployeeTalk)

[//]: # (BEGIN Exercise)

* **Exercise**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [miriamwegener](https://docu.ilias.de/goto_docu_usr_23051.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Exercise)

[//]: # (BEGIN Export)

* **Export**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Export)

[//]: # (BEGIN Favourites)

* **Favourites**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Favourites)

[//]: # (BEGIN File)

* **File**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: Heinz Winter, CaT
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END File)

[//]: # (BEGIN Forum)

* **Forum**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [nadia](https://docu.ilias.de/goto_docu_usr_14206.html)
    * Authority to Curate Test Cases: FH Aachen
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester:[anna.s.vogel](https://docu.ilias.de/goto_docu_usr_71954.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Forum)

[//]: # (BEGIN GeneralKiosk-Mode)

* **General Kiosk-Mode**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GeneralKiosk-Mode)

[//]: # (BEGIN GlobalCache)

* **GlobalCache**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GlobalCache)

[//]: # (BEGIN GlobalScreen)

* **GlobalScreen**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GlobalScreen)

[//]: # (BEGIN Glossary)

* **Glossary**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
        , [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Glossary)

[//]: # (BEGIN Group)

* **Group**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
        , [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Group)

[//]: # (BEGIN HTTP-Request)

* **HTTP-Request**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END HTTP-Request)

[//]: # (BEGIN ILIASPageEditor)

* **ILIAS Page Editor**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: FH Aachen
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ILIASPageEditor)

[//]: # (BEGIN IndividualAssessment)

* **IndividualAssessment**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END IndividualAssessment)

[//]: # (BEGIN InfoPage)

* **Info Page**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
        , [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END InfoPage)

[//]: # (BEGIN InitialisationService)

* **Initialisation Service**
    * Authority to Sign off on Conceptual Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Authority to Sign off on Code Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
        , [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Authority to (De-)Assign Authorities: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Assignee for Security Issues: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END InitialisationService)

[//]: # (BEGIN ItemGroup)

* **ItemGroup**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ItemGroup)

[//]: # (BEGIN LanguageHandling)

* **Language Handling**
    * Authority to Sign off on Conceptual Changes: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to Sign off on Code Changes: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
        , [katrin.grosskopf](https://docu.ilias.de/goto_docu_usr_68340.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Issues: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LanguageHandling)

[//]: # (BEGIN LearningHistory)

* **Learning History**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningHistory)

[//]: # (BEGIN LearningModuleHTML)

* **Learning Module HTML**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleHTML)

[//]: # (BEGIN LearningModuleILIAS)

* **Learning Module ILIAS**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [Balliel](https://docu.ilias.de/goto_docu_usr_18365.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [Balliel](https://docu.ilias.de/goto_docu_usr_18365.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleILIAS)

[//]: # (BEGIN LearningModuleSCORM)

* **Learning Module SCORM**
    * Authority to Sign off on Conceptual Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Sign off on Code Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Curate Test Cases: n.n., Qualitus
    * Authority to (De-)Assign Authorities: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* Tester: n.n., Qualitus
    * Assignee for Security Reports: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Assignee for Security Issues: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleSCORM)

[//]: # (BEGIN LearningSequence)

* **Learning Sequence**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [mglaubitz](https://docu.ilias.de/goto_docu_usr_28309.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningSequence)

[//]: # (BEGIN LegalDocuments)

* **Legal Documents**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [lscharmer](https://docu.ilias.de/goto_docu_usr_87863.html)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [[TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LegalDocuments)

[//]: # (BEGIN Like)

* **Like**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
        , [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Like)

[//]: # (BEGIN Logging)

* **Logging**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Logging)

[//]: # (BEGIN LoginAuthAndRegistration)

* **Login, Auth & Registration**
    * Authority to Sign off on Conceptual Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
        , [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
        , [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
        , [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to (De-)Assign Authorities: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
        , [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [vimotion](https://docu.ilias.de/goto_docu_usr_25105.html)
	  , [ILIAS_LM](https://docu.ilias.de/goto_docu_usr_14109.html) (OpenID)
	  , [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html) (Shibboleth), Alexander Grundkötter, Qualitus (SAML)
    * Assignee for Security Reports: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Assignee for Security Issues: [PerPascalSeeland](https://docu.ilias.de/goto_docu_usr_31492.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LoginAuthAndRegistration)

[//]: # (BEGIN LTI)

* **LTI**
    * Authority to Sign off on Conceptual Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Sign off on Code Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
        , [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to (De-)Assign Authorities: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* Tester: [stv](https://docu.ilias.de/goto_docu_usr_45359.html)
    * Assignee for Security Reports: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Assignee for Security Issues: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LTI)

[//]: # (BEGIN LTIConsumer)

* **LTI Consumer**
    * Authority to Sign off on Conceptual Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Sign off on Code Changes: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Curate Test Cases: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to (De-)Assign Authorities: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)
    * Assignee for Security Reports: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Assignee for Security Issues: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LTIConsumer)

[//]: # (BEGIN Mail)

* **Mail**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [nadia](https://docu.ilias.de/goto_docu_usr_14206.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: Till Lennart Vogt/Test-Team OWL
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Mail)

[//]: # (BEGIN MainMenu)

* **MainMenu**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MainMenu)

[//]: # (BEGIN Maps)

* **Maps**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [miriamhoelscher](https://docu.ilias.de/goto_docu_usr_25370.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Maps)

[//]: # (BEGIN MathJax)

* **MathJax**
    * Authority to Sign off on Conceptual Changes: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
    * Authority to Sign off on Code Changes: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
    * Authority to Curate Test Cases: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
    * Authority to (De-)Assign Authorities: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
	* Tester: [resi](https://docu.ilias.de/goto_docu_usr_72790.html)
    * Assignee for Security Reports: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
    * Assignee for Security Issues: [fneumann](https://docu.ilias.de/goto_docu_usr_1560.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MathJax)

[//]: # (BEGIN MediaObjects)

* **Media Objects**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaObjects)

[//]: # (BEGIN MediaPool)

* **Media Pool**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [kiegel](https://docu.ilias.de/goto_docu_usr_20646.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaPool)

[//]: # (BEGIN MediaCast)

* **MediaCast**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaCast)

[//]: # (BEGIN Membership)

* **Membership**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Membership)

[//]: # (BEGIN Metadata)

* **Metadata**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
        , [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
    * Authority to Curate Test Cases: [daniela.weber](https://docu.ilias.de/goto_docu_usr_40672.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [daniela.weber](https://docu.ilias.de/goto_docu_usr_40672.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Metadata)

[//]: # (BEGIN News)

* **News**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/goto_docu_usr_38330.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END News)

[//]: # (BEGIN NotesAndComments)

* **Notes and Comments**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END NotesAndComments)

[//]: # (BEGIN Notifications)

* **Notifications**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
        , [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Notifications)

[//]: # (BEGIN ObjectService)

* **Object Service**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Assignee for Security Issues: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ObjectService)

[//]: # (BEGIN OnlineHelp)

* **Online Help**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
        , [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OnlineHelp)

[//]: # (BEGIN OpenIdConect)

* **Open ID Connect**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OpenIdConect)

[//]: # (BEGIN OrganisationalUnits)

* **Organisational Units**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
        , [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [wischniak](https://docu.ilias.de/goto_docu_usr_21896.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OrganisationalUnits)

[//]: # (BEGIN PersonalAndSharedResources)

* **Personal and Shared Resources**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [scarlino](https://docu.ilias.de/goto_docu_usr_56074.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END PersonalAndSharedResources)

[//]: # (BEGIN Poll)

* **Poll**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
        , [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [Qndrs](https://docu.ilias.de/goto_docu_usr_42611.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Poll)

[//]: # (BEGIN Portfolio)

* **Portfolio**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [KlausVorkauf](https://docu.ilias.de/goto_docu_usr_5890.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Portfolio)

[//]: # (BEGIN PreconditionHandling)

* **Precondition Handling**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [mkloes](https://docu.ilias.de/goto_docu_usr_22174.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END PreconditionHandling)

[//]: # (BEGIN Rating)

* **Rating**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Rating)

[//]: # (BEGIN RBAC)

* **RBAC / Access Control**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Assignee for Security Issues: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END RBAC)

[//]: # (BEGIN Refinery)

* **Refinery**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Refinery)

[//]: # (BEGIN SAML)

* **SAML**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: Alexander Grundkötter, Qualitus
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END SAML)

[//]: # (BEGIN Search)

* **Search**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [Qndrs](https://docu.ilias.de/goto_docu_usr_42611.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Search)

[//]: # (BEGIN Session)

* **Session**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [yseiler](https://docu.ilias.de/goto_docu_usr_17694.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Session)

[//]: # (BEGIN Setup)

* **Setup**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: [fwolf](https://docu.ilias.de/goto_docu_usr_29018.html)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Setup)

[//]: # (BEGIN ShibbolethAuthentication)

* **Shibboleth Authentication**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ShibbolethAuthentication)

[//]: # (BEGIN Staff)

* **Staff**
    * Authority to Sign off on Conceptual Changes: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Authority to Sign off on Code Changes: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
        , [tschmitz](https://docu.ilias.de/goto_docu_usr_92591.html)
    * Authority to Curate Test Cases: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Authority to (De-)Assign Authorities: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
	* Tester: [qualitus.morgunova](https://docu.ilias.de/goto_docu_usr_69410.html)
    * Assignee for Security Reports: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Assignee for Security Issues: [tfamula](https://docu.ilias.de/goto_docu_usr_58959.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Staff)

[//]: # (BEGIN StatisticsAndLearningProgress)

* **Statistics and Learning Progress**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [suittenpointner](https://docu.ilias.de/goto_docu_usr_3458.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [suittenpointner](https://docu.ilias.de/goto_docu_usr_3458.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END StatisticsAndLearningProgress)

[//]: # (BEGIN StudyProgramme)

* **Study Programme**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
        , [shecken](https://docu.ilias.de/goto_docu_usr_45419.html)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
	* Tester: Florence Seydoux, s+r
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END StudyProgramme)

[//]: # (BEGIN Survey)

* **Survey**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/goto_docu_usr_42910.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Survey)

[//]: # (BEGIN SystemCheck)

* **System Check**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END SystemCheck)

[//]: # (BEGIN Tagging)

* **Tagging**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [skaiser](https://docu.ilias.de/goto_docu_usr_17260.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tagging)

[//]: # (BEGIN Tasks)

* **Tasks**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tasks)

[//]: # (BEGIN Taxonomy)

* **Taxonomy**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: Tested separately in each module that supports taxonomies
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: Tested separately in each module that supports taxonomies
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Taxonomy)

[//]: # (BEGIN TermsOfService)

* **Terms of Services**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html),
        [lscharmer](https://docu.ilias.de/goto_docu_usr_87863.html)
    * Authority to Curate Test Cases: Stefania Akgül (CaT)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/goto_docu_usr_8784.html)
	* Tester: Heinz Winter (CaT)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END TermsOfService)

[//]: # (BEGIN TestAndAssessment)

* **Test & Assessment**
	* Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Authority to Sign off on Code Changes: [mbecker](https://docu.ilias.de/goto_docu_usr_27266.html)
        , [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
        , [tjoussen](https://docu.ilias.de/goto_docu_usr_103745.html)
    * Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
	* Testcases: SIG E-Assessment
	* Tester: [dehling](https://docu.ilias.de/goto_docu_usr_12725.html)
        , [NDJ1508](https://docu.ilias.de/goto_docu_usr_93043.html)
        , [ksgrie](https://docu.ilias.de/goto_docu_usr_95947.html)
        , [simon.lowe](https://docu.ilias.de/goto_docu_usr_79091.html)
        , [rabah](https://docu.ilias.de/goto_docu_usr_40218.html)
        , Testteam Uni Hohenheim
        , Testteam Kröpelin
    * Assignee for Security Reports: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Assignee for Security Issues: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END TestAndAssessment)

[//]: # (BEGIN Tree)

* **Tree**
    * Authority to Sign off on Conceptual Changes: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
    * Authority to Sign off on Code Changes: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
    * Authority to Curate Test Cases: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
    * Authority to (De-)Assign Authorities: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
    * Assignee for Security Issues: [Fabian Wolf](https://docu.ilias.de/goto_docu_usr_29018.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tree)

[//]: # (BEGIN UserService)

* **User Service**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
	* Tester: [elena](https://docu.ilias.de/goto_docu_usr_49160.html)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Assignee for Security Issues: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UserService)

[//]: # (BEGIN UICore)

* **UICore**
    * Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
    * Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
        , [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Curate Test Cases: [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
    * Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
	* Tester: [AUTHOR MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
    * Assignee for Security Issues: [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UICore)

[//]: # (BEGIN UI-Service)

* **UI-Service**
    * Authority to Sign off on Conceptual Changes: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
      , [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
    * Authority to Sign off on Code Changes: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
      , [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Authority to (De-)Assign Authorities: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
	  , [rklees](https://docu.ilias.de/goto_docu_usr_34047.html)
      , [tfuhrer](https://docu.ilias.de/goto_docu_usr_81947.html)
	* Tester: [kauerswald](https://docu.ilias.de/goto_docu_usr_70029.html)
    * Assignee for Security Reports: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
    * Assignee for Security Issues: [amstutz](https://docu.ilias.de/goto_docu_usr_26468.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UI-Service)

[//]: # (BEGIN VirusScanner)

* **Virus Scanner**
    * Authority to Sign off on Conceptual Changes: [rschenk](https://docu.ilias.de/goto_docu_usr_18065.html)
    * Authority to Sign off on Code Changes: [rschenk](https://docu.ilias.de/goto_docu_usr_18065.html)
    * Authority to Curate Test Cases: [rschenk](https://docu.ilias.de/goto_docu_usr_18065.html)
    * Authority to (De-)Assign Authorities: [rschenk (Databay AG)](https://docu.ilias.de/goto_docu_usr_18065.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [rschenk](https://docu.ilias.de/goto_docu_usr_18065.html)
    * Assignee for Security Issues: [rschenk](https://docu.ilias.de/goto_docu_usr_18065.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END VirusScanner)

[//]: # (BEGIN WebAccessChecker)

* **Web Access Checker**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
        , [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Authority to Curate Test Cases: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Tester: [berggold](https://docu.ilias.de/goto_docu_usr_22199.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebAccessChecker)

[//]: # (BEGIN WebFeed)

* **Web Feed**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [kunkel](https://docu.ilias.de/goto_docu_usr_115.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebFeed)

[//]: # (BEGIN WebDAV)

* **WebDAV**
	* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to Sign off Testcase Changes: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
	* Testcases: [fschmid](https://docu.ilias.de/goto_docu_usr_70029.html)
	* Tester: [kauerswald](https://docu.ilias.de/goto_docu_usr_70029.html)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/goto_docu_usr_21087.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebDAV)

[//]: # (BEGIN Weblink)

* **Weblink**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [nadine.bauser](https://docu.ilias.de/goto_docu_usr_34662.html)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
	* Tester: [nadine.bauser](https://docu.ilias.de/goto_docu_usr_34662.html)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Weblink)

[//]: # (BEGIN Webservices)

* **Webservices**
    * Authority to Sign off on Conceptual Changes: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
    * Authority to Sign off on Code Changes: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
    * Authority to Curate Test Cases: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
    * Authority to (De-)Assign Authorities: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
    * Assignee for Security Issues: [Jephte](https://docu.ilias.de/goto_docu_usr_70542.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Webservices)

[//]: # (BEGIN WhoIsOnline)

* **Who is online?**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/goto_docu_usr_3139.html)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: [oliver.samoila](https://docu.ilias.de/goto_docu_usr_26160.html)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WhoIsOnline)

[//]: # (BEGIN Wiki)

* **Wiki**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Authority to Curate Test Cases: n.n., Uni Köln
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
	* Tester: n.n., Uni Köln
    * Assignee for Security Reports: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/goto_docu_usr_149.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Wiki)

[//]: # (BEGIN xAPIAndcmi5)

* **xAPI/cmi5**
    * Authority to Sign off on Conceptual Changes: [[ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)]
    * Authority to Sign off on Code Changes: [[ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)]
    * Authority to Curate Test Cases: [[ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)]
    * Authority to (De-)Assign Authorities: [[ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)]
	* Tester: [TESTER MISSING](https://docu.ilias.de/goto_docu_pg_64423_4793.html)
    * Assignee for Security Reports: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Assignee for Security Issues: [ukohnle](https://docu.ilias.de/goto_docu_usr_21855.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END xAPIAndcmi5)

## Unmaintained Components

The following directories are currently unmaintained:

* Services/Context
* Services/CSV
* Services/EventHandling
* Services/Excel
* Services/QTI
* Services/Randomization
