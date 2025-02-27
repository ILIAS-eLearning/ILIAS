ILIAS Maintenance
=================
The development of ILIAS is coordinated by the Product Manager and the
Technical Board. Many decisions are taken at the biweekly Jour Fixe, which is
open for participation to everyone. The source code is maintained by a growing
group of people, ranging from devoted maintainers to regular or even one-time
contributors.

# Special Roles

* **Product Management**: [Matthias Kunkel](https://docu.ilias.de/go/usr/115)
* **Technical Board**: [Michael Jansen](https://docu.ilias.de/go/usr/8784), [Stephan Kergomard](https://docu.ilias.de/go/usr/44474), [Richard Klees](https://docu.ilias.de/go/usr/34047), [Nico Roeser](https://docu.ilias.de/go/usr/72730), [Fabian Schmid](https://docu.ilias.de/go/usr/21087)
* **Testcase Management**: [Fabian Kruse](https://docu.ilias.de/go/usr/27631)
* **Release Management**: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
* **Technical Documentation**: [Ann-Christin Gruber](https://docu.ilias.de/go/usr/94025)
* **Online Help**: [Alexandra Tödt](https://docu.ilias.de/go/usr/3139)

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
	* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases:[fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ActiveRecord)

[//]: # (BEGIN Administration)

* **Administration**
    * Authority to Sign off on Conceptual Changes: [fneumann](https://docu.ilias.de/go/usr/1560)
	* Authority to Sign off on Code Changes: [fneumann](https://docu.ilias.de/go/usr/1560)
        , [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [fneumann](https://docu.ilias.de/go/usr/1560)
        , [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [fneumann (Databay AG)](https://docu.ilias.de/go/usr/1560)
        , [lscharmer (Databay AG)](https://docu.ilias.de/go/usr/87863)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Assignee for Security Issues: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Administration)

[//]: # (BEGIN AdministrativeNotifications)

* **Administrative Notifications**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END AdministrativeNotifications)

[//]: # (BEGIN BackgroundTasks)

* **BackgroundTasks**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BackgroundTasks)

[//]: # (BEGIN Badges)

* **Badges**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/go/usr/38330)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Badges)

[//]: # (BEGIN BibliographicListItem)

* **Bibliographic List Item**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [miriamhoelscher](https://docu.ilias.de/go/usr/25370)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BibliographicListItem)

[//]: # (BEGIN Blog)

* **Blog**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [PaBer](https://docu.ilias.de/go/usr/33766)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Blog)

[//]: # (BEGIN BookingTool)

* **Booking Tool**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [wolfganghuebsch](https://docu.ilias.de/go/usr/18455)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BookingTool)

[//]: # (BEGIN Calendar)

* **Calendar**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [yseiler](https://docu.ilias.de/go/usr/17694)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [yseiler](https://docu.ilias.de/go/usr/17694)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Calendar)

[//]: # (BEGIN CategoryAndRepository)

* **Category and Repository**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        ,  [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [miriamhoelscher](https://docu.ilias.de/go/usr/25370)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CategoryAndRepository)

[//]: # (BEGIN Certificate)

* **Certificate**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
  * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784), [ChrisPotter](https://docu.ilias.de/go/usr/90855)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [m-gregory-m](https://docu.ilias.de/go/usr/51332)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Certificate)

[//]: # (BEGIN Chat)

* **Chat**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Chat)


[//]: # (BEGIN Comments)
* **Comments**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Comments)

[//]: # (BEGIN CompetenceManagement)

* **Competence Management**
    * Authority to Sign off on Conceptual Changes: [tfamula](https://docu.ilias.de/go/usr/58959)
    * Authority to Sign off on Code Changes: [tfamula](https://docu.ilias.de/go/usr/58959)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [tfamula](https://docu.ilias.de/go/usr/58959)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [tfamula](https://docu.ilias.de/go/usr/58959)
    * Assignee for Security Issues: [tfamula](https://docu.ilias.de/go/usr/58959)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CompetenceManagement)

[//]: # (BEGIN Component)

* **Component**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
        ,  [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Component)

[//]: # (BEGIN Contacts)

* **Contacts**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Contacts)

[//]: # (BEGIN ContentPage)

* **ContentPage**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ContentPage)

[//]: # (BEGIN Context)

* **Context**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Context)

[//]: # (BEGIN CourseManagement)

* **Course Management**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [lauener](https://docu.ilias.de/go/usr/8474)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [lauener](https://docu.ilias.de/go/usr/8474)
	  , [TESTER MISSING FOR LOC](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CourseManagement)

[//]: # (BEGIN CronService)

* **Cron Service**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CronService)

[//]: # (BEGIN CSSAndTemplates)

* **CSS / Templates**
    * Authority to Sign off on Conceptual Changes: [yvseiler](https://docu.ilias.de/go/usr/17694)
      , [catenglaender](https://docu.ilias.de/go/usr/79291)
    * Authority to Sign off on Code Changes: [amstutz](https://docu.ilias.de/go/usr/26468)
      , [catenglaender](https://docu.ilias.de/go/usr/79291)
    * Authority to Curate Test Cases: [amstutz](https://docu.ilias.de/go/usr/26468)
      , [yvseiler](https://docu.ilias.de/go/usr/17694)
    * Authority to (De-)Assign Authorities: [amstutz](https://docu.ilias.de/go/usr/26468)
	* Tester: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [amstutz](https://docu.ilias.de/go/usr/26468)
    * Assignee for Security Issues: [amstutz](https://docu.ilias.de/go/usr/26468)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CSSAndTemplates)

[//]: # (BEGIN Dashboard)

* **Dashboard**
    * Authority to Sign off on Conceptual Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
        , [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
        , [lscharmer](https://docu.ilias.de/go/usr/87863)
        , [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [iszmais (Databay AG)](https://docu.ilias.de/go/usr/65630)
        , [lscharmer (Databay AG)](https://docu.ilias.de/go/usr/87863)
	* Tester: [silvia.marine](https://docu.ilias.de/go/usr/71642)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Assignee for Security Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Dashboard)

[//]: # (BEGIN Data)

* **Data**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Data)

[//]: # (BEGIN DataCollection)

* **Data Collection**
    * Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Curate Test Cases: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Assignee for Security Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DataCollection)

[//]: # (BEGIN DataProtection)

* **Data Protection**
	* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DataProtection)

[//]: # (BEGIN Database)

* **Database**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
        , [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Database)

[//]: # (BEGIN DidacticTemplates)

* **Didactic Templates**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DidacticTemplates)

[//]: # (BEGIN ECSInterface)

* **ECS Interface**
    * Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
    * Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107)
    * Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
    * Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
	* Tester: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
    * Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)
    * Assignee for Security Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ECSInterface)

[//]: # (BEGIN EmployeeTalk)

* **EmployeeTalk**
    * Authority to Sign off on Conceptual Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Sign off on Code Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
        , [tfamula](https://docu.ilias.de/go/usr/58959)
    * Authority to Curate Test Cases: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to (De-)Assign Authorities: [tschmitz](https://docu.ilias.de/go/usr/92591)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Assignee for Security Issues: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END EmployeeTalk)

[//]: # (BEGIN EventHandling)

* **EventHandling**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END EventHandling)

[//]: # (BEGIN Excel)

* **Excel**
    * Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Tester: Tested separately in each module that supports Excel.
    * Assignee for Security Reports: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Assignee for Security Issues: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Excel)

[//]: # (BEGIN Exercise)

* **Exercise**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [miriamwegener](https://docu.ilias.de/go/usr/23051)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Exercise)

[//]: # (BEGIN Export)

* **Export**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Export)

[//]: # (BEGIN Favourites)

* **Favourites**
    * Authority to Sign off on Conceptual Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Curate Test Cases: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to (De-)Assign Authorities: [iszmais](https://docu.ilias.de/go/usr/65630)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Assignee for Security Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Favourites)

[//]: # (BEGIN File)

* **File**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: Heinz Winter, CaT
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END File)

[//]: # (BEGIN Forum)

* **Forum**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: FH Aachen
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester:[anna.s.vogel](https://docu.ilias.de/go/usr/71954)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Forum)

[//]: # (BEGIN GeneralKiosk-Mode)

* **General Kiosk-Mode**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GeneralKiosk-Mode)

[//]: # (BEGIN GlobalCache)

* **GlobalCache**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GlobalCache)

[//]: # (BEGIN GlobalScreen)

* **GlobalScreen**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GlobalScreen)

[//]: # (BEGIN Glossary)

* **Glossary**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        , [tfamula](https://docu.ilias.de/go/usr/58959)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Glossary)

[//]: # (BEGIN Group)

* **Group**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [yseiler](https://docu.ilias.de/go/usr/17694)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [yseiler](https://docu.ilias.de/go/usr/17694)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Group)

[//]: # (BEGIN HTTP-Request)

* **HTTP-Request**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END HTTP-Request)

[//]: # (BEGIN ILIASPageEditor)

* **ILIAS Page Editor**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: FH Aachen
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ILIASPageEditor)

[//]: # (BEGIN IndividualAssessment)

* **IndividualAssessment**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END IndividualAssessment)

[//]: # (BEGIN InfoPage)

* **Info Page**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        , [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END InfoPage)

[//]: # (BEGIN InitialisationService)

* **Initialisation Service**
    * Authority to Sign off on Conceptual Changes: [PerPascalSeeland](https://docu.ilias.de/go/usr/31492)
    * Authority to Sign off on Code Changes: [PerPascalSeeland](https://docu.ilias.de/go/usr/31492)
        , [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [PerPascalSeeland](https://docu.ilias.de/go/usr/31492)
    * Authority to (De-)Assign Authorities: [PerPascalSeeland](https://docu.ilias.de/go/usr/31492)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [PerPascalSeeland](https://docu.ilias.de/go/usr/31492)
    * Assignee for Security Issues: [PerPascalSeeland](https://docu.ilias.de/go/usr/31492)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END InitialisationService)

[//]: # (BEGIN ItemGroup)

* **ItemGroup**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [berggold](https://docu.ilias.de/go/usr/22199)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [berggold](https://docu.ilias.de/go/usr/22199)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ItemGroup)

[//]: # (BEGIN LanguageHandling)

* **Language Handling**
    * Authority to Sign off on Conceptual Changes: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to Sign off on Code Changes: [kunkel](https://docu.ilias.de/go/usr/115)
        , [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
    * Authority to Curate Test Cases: [ChrisPotter](https://docu.ilias.de/go/usr/90855)
    * Authority to (De-)Assign Authorities: [kunkel](https://docu.ilias.de/go/usr/115)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Issues: [kunkel](https://docu.ilias.de/go/usr/115)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LanguageHandling)

[//]: # (BEGIN LearningHistory)

* **Learning History**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningHistory)

[//]: # (BEGIN LearningModuleHTML)

* **Learning Module HTML**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleHTML)

[//]: # (BEGIN LearningModuleILIAS)

* **Learning Module ILIAS**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [Balliel](https://docu.ilias.de/go/usr/18365)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [Balliel](https://docu.ilias.de/go/usr/18365)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleILIAS)

[//]: # (BEGIN LearningModuleSCORM)

* **Learning Module SCORM (1.2 and 2004)**
    * Authority to Sign off on Conceptual Changes: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Authority to Sign off on Code Changes: [qualitus.dahme](https://docu.ilias.de/go/usr/99160)
    * Authority to Curate Test Cases: [tim.fehske](https://docu.ilias.de/go/usr/101255)
    * Authority to (De-)Assign Authorities: [wischniak](https://docu.ilias.de/go/usr/21896)
	* Tester: n.n., Qualitus
    * Assignee for Security Reports: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Assignee for Security Issues: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleSCORM)

[//]: # (BEGIN LearningSequence)

* **Learning Sequence**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [mglaubitz](https://docu.ilias.de/go/usr/28309)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningSequence)

[//]: # (BEGIN LegalDocuments)

* **Legal Documents**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/34047)
	* Tester: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LegalDocuments)

[//]: # (BEGIN Like)

* **Like**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        , [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Like)

[//]: # (BEGIN Logging)

* **Logging**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Logging)

[//]: # (BEGIN LoginAuthAndRegistration)

* **Login, Auth & Registration**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: vimotion
	  , [ILIAS_LM](https://docu.ilias.de/go/usr/14109) (OpenID)
	  , [fschmid](https://docu.ilias.de/go/usr/21087) (Shibboleth)
      , Alexander Grundkötter, Qualitus (SAML)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LoginAuthAndRegistration)

[//]: # (BEGIN LTIConsumer)

* **LTI Consumer**
    * Authority to Sign off on Conceptual Changes: [jcop](https://docu.ilias.de/go/usr/30511)
    * Authority to Sign off on Code Changes: [Zallax](https://docu.ilias.de/go/usr/101102), [sdiaz](https://docu.ilias.de/go/usr/105654)
    * Authority to Curate Test Cases: [jcop](https://docu.ilias.de/go/usr/30511)
    * Authority to (De-)Assign Authorities: [jcop](https://docu.ilias.de/go/usr/30511)
	* Tester: [Fabian Kruse](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Assignee for Security Reports: [jcop](https://docu.ilias.de/go/usr/30511)
    * Assignee for Security Issues: [jcop](https://docu.ilias.de/go/usr/30511)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LTIConsumer)

[//]: # (BEGIN LTI)

* **LTI Provider**
    * Authority to Sign off on Conceptual Changes: [jcop](https://docu.ilias.de/go/usr/30511)
    * Authority to Sign off on Code Changes: [Zallax](https://docu.ilias.de/go/usr/101102), [sdiaz](https://docu.ilias.de/go/usr/105654), [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [jcop](https://docu.ilias.de/go/usr/30511)
    * Authority to (De-)Assign Authorities: [jcop](https://docu.ilias.de/go/usr/30511)
	* Tester: [Fabian Kruse](https://docu.ilias.de/goto_docu_usr_27631.html)
    * Assignee for Security Reports: [jcop](https://docu.ilias.de/go/usr/30511)
    * Assignee for Security Issues: [jcop](https://docu.ilias.de/go/usr/30511)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LTI)

[//]: # (BEGIN Mail)

* **Mail**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: Till Lennart Vogt/Test-Team OWL
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Mail)

[//]: # (BEGIN MainMenu)

* **MainMenu**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MainMenu)

[//]: # (BEGIN Maps)

* **Maps**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [miriamhoelscher](https://docu.ilias.de/go/usr/25370)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Maps)

[//]: # (BEGIN MathJax)

* **MathJax**
    * Authority to Sign off on Conceptual Changes: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Authority to Sign off on Code Changes: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Authority to Curate Test Cases: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Authority to (De-)Assign Authorities: [fneumann](https://docu.ilias.de/go/usr/1560)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Assignee for Security Issues: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MathJax)

[//]: # (BEGIN MediaObjects)

* **Media Objects**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [kiegel](https://docu.ilias.de/go/usr/20646)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaObjects)

[//]: # (BEGIN MediaPool)

* **Media Pool**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [kiegel](https://docu.ilias.de/go/usr/20646)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaPool)

[//]: # (BEGIN MediaCast)

* **MediaCast**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [berggold](https://docu.ilias.de/go/usr/22199)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [berggold](https://docu.ilias.de/go/usr/22199)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaCast)

[//]: # (BEGIN Membership)

* **Membership**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Membership)

[//]: # (BEGIN Metadata)

* **Metadata**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Curate Test Cases: [daniela.weber](https://docu.ilias.de/go/usr/40672)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Metadata)

[//]: # (BEGIN News)

* **News**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [Thomas.schroeder](https://docu.ilias.de/go/usr/38330)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [Thomas.schroeder](https://docu.ilias.de/go/usr/38330)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END News)

[//]: # (BEGIN NotesAndComments)

* **Notes and Comments**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END NotesAndComments)

[//]: # (BEGIN Notifications)

* **Notifications**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Notifications)

[//]: # (BEGIN ObjectService)

* **Object Service**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Security Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ObjectService)

[//]: # (BEGIN OnlineHelp)

* **Online Help**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        , [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OnlineHelp)

[//]: # (BEGIN OpenIdConect)

* **Open ID Connect**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OpenIdConect)

[//]: # (BEGIN OrganisationalUnits)

* **Organisational Units**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
        , [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OrganisationalUnits)

[//]: # (BEGIN PersonalAndSharedResources)

* **Personal and Shared Resources**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END PersonalAndSharedResources)

[//]: # (BEGIN Poll)

* **Poll**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [Qndrs](https://docu.ilias.de/go/usr/42611)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Poll)

[//]: # (BEGIN Portfolio)

* **Portfolio**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [KlausVorkauf](https://docu.ilias.de/go/usr/5890)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Portfolio)

[//]: # (BEGIN PreconditionHandling)

* **Precondition Handling**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [mkloes](https://docu.ilias.de/go/usr/22174)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END PreconditionHandling)

[//]: # (BEGIN Rating)

* **Rating**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Rating)

[//]: # (BEGIN RBAC)

* **RBAC / Access Control**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Security Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END RBAC)

[//]: # (BEGIN Refinery)

* **Refinery**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
	  , [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
	  , [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
	  , [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	  , [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Refinery)

[//]: # (BEGIN SAML)

* **SAML**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: Alexander Grundkötter, Qualitus
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END SAML)

[//]: # (BEGIN Search)

* **Search**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [Qndrs](https://docu.ilias.de/go/usr/42611)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Search)

[//]: # (BEGIN Session)

* **Session**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [yseiler](https://docu.ilias.de/go/usr/17694)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [yseiler](https://docu.ilias.de/go/usr/17694)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Session)

[//]: # (BEGIN Setup)

* **Setup**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [fwolf](https://docu.ilias.de/go/usr/29018)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Setup)

[//]: # (BEGIN ShibbolethAuthentication)

* **Shibboleth Authentication**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ShibbolethAuthentication)

[//]: # (BEGIN Staff)

* **Staff**
    * Authority to Sign off on Conceptual Changes: [tfamula](https://docu.ilias.de/go/usr/58959)
    * Authority to Sign off on Code Changes: [tfamula](https://docu.ilias.de/go/usr/58959)
        , [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Curate Test Cases: [tfamula](https://docu.ilias.de/go/usr/58959)
    * Authority to (De-)Assign Authorities: [tfamula](https://docu.ilias.de/go/usr/58959)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [tfamula](https://docu.ilias.de/go/usr/58959)
    * Assignee for Security Issues: [tfamula](https://docu.ilias.de/go/usr/58959)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Staff)

[//]: # (BEGIN StatisticsAndLearningProgress)

* **Statistics and Learning Progress**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END StatisticsAndLearningProgress)

[//]: # (BEGIN StudyProgramme)

* **Study Programme**
    * Authority to Sign off on Conceptual Changes: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to Sign off on Code Changes: [rklees](https://docu.ilias.de/go/usr/34047)
        , [shecken](https://docu.ilias.de/go/usr/45419)
    * Authority to Curate Test Cases: [rklees](https://docu.ilias.de/go/usr/34047)
    * Authority to (De-)Assign Authorities: [rklees](https://docu.ilias.de/go/usr/34047)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rklees](https://docu.ilias.de/go/usr/34047)
    * Assignee for Security Issues: [rklees](https://docu.ilias.de/go/usr/34047)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END StudyProgramme)

[//]: # (BEGIN Survey)

* **Survey**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Survey)

[//]: # (BEGIN SystemCheck)

* **System Check**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END SystemCheck)

[//]: # (BEGIN Tagging)

* **Tagging**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tagging)

[//]: # (BEGIN Tasks)

* **Tasks**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tasks)

[//]: # (BEGIN Taxonomy)

* **Taxonomy**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: Tested separately in each module that supports taxonomies
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: Tested separately in each module that supports taxonomies
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Taxonomy)

[//]: # (BEGIN TermsOfService)

* **Terms of Services**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784),
        [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
	* Tester: Heinz Winter (CaT)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END TermsOfService)

[//]: # (BEGIN TestAndAssessment)

* **Test & Assessment**
	* Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Authority to Sign off on Code Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
        , [skergomard](https://docu.ilias.de/go/usr/44474)
        , [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/go/usr/48931)
	* Testcases: SIG E-Assessment
	* Tester: [dehling](https://docu.ilias.de/go/usr/12725)
        , [NDJ1508](https://docu.ilias.de/go/usr/93043)
        , [ksgrie](https://docu.ilias.de/go/usr/95947)
        , [simon.lowe](https://docu.ilias.de/go/usr/79091)
        , [rabah](https://docu.ilias.de/go/usr/40218)
        , Testteam Kröpelin
    * Assignee for Security Reports: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Assignee for Security Issues: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END TestAndAssessment)

[//]: # (BEGIN Tree)

* **Tree**
    * Authority to Sign off on Conceptual Changes: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Authority to Sign off on Code Changes: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Authority to Curate Test Cases: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Authority to (De-)Assign Authorities: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Assignee for Security Issues: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tree)

[//]: # (BEGIN UserService)

* **User Service**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Security Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UserService)

[//]: # (BEGIN UICore)

* **UICore**
    * Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947)
        , [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/go/usr/81947)
	* Tester: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Assignee for Security Issues: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UICore)

[//]: # (BEGIN UI-Service)

* **UI-Service**
    * Authority to Sign off on Conceptual Changes: [amstutz](https://docu.ilias.de/go/usr/26468)
	  , [rklees](https://docu.ilias.de/go/usr/34047)
      , [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to Sign off on Code Changes: [amstutz](https://docu.ilias.de/go/usr/26468)
	  , [rklees](https://docu.ilias.de/go/usr/34047)
      , [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Authority to (De-)Assign Authorities: [amstutz](https://docu.ilias.de/go/usr/26468)
	  , [rklees](https://docu.ilias.de/go/usr/34047)
      , [tfuhrer](https://docu.ilias.de/go/usr/81947)
	* Tester: [kauerswald](https://docu.ilias.de/go/usr/70029)
    * Assignee for Security Reports: [amstutz](https://docu.ilias.de/go/usr/26468)
    * Assignee for Security Issues: [amstutz](https://docu.ilias.de/go/usr/26468)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UI-Service)

[//]: # (BEGIN VirusScanner)

* **Virus Scanner**
    * Authority to Sign off on Conceptual Changes: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Authority to Sign off on Code Changes: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Authority to Curate Test Cases: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Authority to (De-)Assign Authorities: [rschenk (Databay AG)](https://docu.ilias.de/go/usr/18065)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Assignee for Security Issues: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END VirusScanner)

[//]: # (BEGIN WebAccessChecker)

* **Web Access Checker**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
        , [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Tester: [berggold](https://docu.ilias.de/go/usr/22199)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebAccessChecker)

[//]: # (BEGIN WebFeed)

* **Web Feed**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [kunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebFeed)

[//]: # (BEGIN WebDAV)

* **WebDAV**
	* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off Testcase Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
	* Testcases: [fschmid](https://docu.ilias.de/go/usr/70029)
	* Tester: [kauerswald](https://docu.ilias.de/go/usr/70029)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebDAV)

[//]: # (BEGIN Weblink)

* **Weblink**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [nadine.bauser](https://docu.ilias.de/go/usr/34662)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
	* Tester: [nadine.bauser](https://docu.ilias.de/go/usr/34662)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Weblink)

[//]: # (BEGIN Webservices)

* **Webservices**
    * Authority to Sign off on Conceptual Changes: [Jephte](https://docu.ilias.de/go/usr/70542)
    * Authority to Sign off on Code Changes: [Jephte](https://docu.ilias.de/go/usr/70542)
    * Authority to Curate Test Cases: [Jephte](https://docu.ilias.de/go/usr/70542)
    * Authority to (De-)Assign Authorities: [Jephte](https://docu.ilias.de/go/usr/70542)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [Jephte](https://docu.ilias.de/go/usr/70542)
    * Assignee for Security Issues: [Jephte](https://docu.ilias.de/go/usr/70542)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Webservices)

[//]: # (BEGIN WhoIsOnline)

* **Who is online?**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WhoIsOnline)

[//]: # (BEGIN Wiki)

* **Wiki**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: n.n., Uni Köln
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
	* Tester: n.n., Uni Köln
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Wiki)

[//]: # (BEGIN xAPIAndcmi5)

* **xAPI/cmi5**
    * Authority to Sign off on Conceptual Changes: [[ukohnle](https://docu.ilias.de/go/usr/21855)]
    * Authority to Sign off on Code Changes: [[ukohnle](https://docu.ilias.de/go/usr/21855)]
    * Authority to Curate Test Cases: [[ukohnle](https://docu.ilias.de/go/usr/21855)]
    * Authority to (De-)Assign Authorities: [[ukohnle](https://docu.ilias.de/go/usr/21855)]
	* Tester: [TESTER MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Assignee for Security Reports: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Assignee for Security Issues: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END xAPIAndcmi5)

## Unmaintained Components

The following directories are currently unmaintained:

* ILIAS/Context
* ILIAS/CSV
* ILIAS/EventHandling
* ILIAS/QTI
* ILIAS/Randomization
