ILIAS Maintenance
=================
The development of ILIAS is coordinated by the Product Manager and the
Technical Board. Many decisions are taken at the biweekly Jour Fixe, which is
open for participation to everyone. The source code is maintained by a growing
group of people, ranging from devoted maintainers to regular or even one-time
contributors.

# Special Roles

* **Product Management**: [Matthias Kunkel](https://docu.ilias.de/go/usr/115)
* **Technical Board**: [Rob Falkenstein](https://docu.ilias.de/go/usr/63946), [Marvin Hackfort](https://docu.ilias.de/go/usr/50523), [Michael Jansen](https://docu.ilias.de/go/usr/8784), [Franziska Wandelmaier](https://docu.ilias.de/go/usr/33833), [Maximilian Becker](https://docu.ilias.de/go/usr/27266)
* **Testcase Management**: [Fabian Kruse](https://docu.ilias.de/go/usr/27631)
* **Release Management**: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
* **Technical Documentation**: [Ann-Christin Gruber](https://docu.ilias.de/go/usr/94025)
* **Online Help**: [Alexandra Tödt](https://docu.ilias.de/go/usr/3139)

[//]: # (BEGIN Authorities)
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
They are the only ones allowed to modify the `maintenance.json` of a component.

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

[//]: # (END Authorities)

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
* If the person holding the **Authority to (De-)Assign Authorities** assigns a new **Authority to Curate Test Cases** the Testcase Management MUST be informed about the change.

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
of the last `Authority to Sign off on Code Changes` would like to pass the
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
* **"Assignee for Issues"**:
    A string in the form `<username> (<userid>), <company> (<company_page>)`
    pointing to valid users on https://docu.ilias.de.
* **"Assignee for Security Reports"**:
    A string in the form `<username> (<userid>), <company> (<company_page>)`
    pointing to valid users on https://docu.ilias.de.
* **"Unit-specific Guidelines, Rules, and Regulations"**:
    Link to a file `COMMUNITY.md` in the root of the unity in the trunk branch on
    GitHub specifying the guidelines, rules, and regulations for collaboration.

# Components and Related Authorities

[//]: # (BEGIN ActiveRecord)

* **ActiveRecord**
    * Authority to Sign off on Conceptual Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Authority to Sign off on Code Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Authority to Curate Test Cases: MISSING
    * Authority to (De-)Assign Authorities: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Assignee for Issues: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Assignee for Security Reports: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
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
    * Assignee for Issues: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Assignee for Security Reports: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Administration)

[//]: # (BEGIN AdministrativeNotifications)

* **Administrative Notifications**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END AdministrativeNotifications)

[//]: # (BEGIN BackgroundTasks)

* **BackgroundTasks**
    * Authority to Sign off on Conceptual Changes: [tjoussen](https://docu.ilias.de/go/usr/103745), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [tjoussen](https://docu.ilias.de/go/usr/103745), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: MISSING
    * Authority to (De-)Assign Authorities: [tjoussen (Databay AG)](https://docu.ilias.de/go/usr/103745)
    * Assignee for Issues: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Assignee for Security Reports: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BackgroundTasks)

[//]: # (BEGIN Badges)

* **Badges**
    * Authority to Sign off on Conceptual Changes: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Authority to Sign off on Code Changes: [fhelfer](https://docu.ilias.de/go/usr/93367), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Assignee for Security Reports: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Badges)

[//]: # (BEGIN Benchmark)

* **Benchmark**
  * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
  * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    , [smeyer](https://docu.ilias.de/go/usr/191)
  * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
  * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
  * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
  * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
  * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Benchmark)

[//]: # (BEGIN BibliographicListItem)

* **Bibliographic List Item**
    * Authority to Sign off on Conceptual Changes: [lschmidt-tf](https://docu.ilias.de/go/usr/120143)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [maalers](https://docu.ilias.de/go/usr/119188)
    * Authority to Curate Test Cases: [maalers](https://docu.ilias.de/go/usr/119188)
    * Authority to (De-)Assign Authorities: [maalers](https://docu.ilias.de/go/usr/119188)
    * Assignee for Issues: [maalers](https://docu.ilias.de/go/usr/119188)
    * Assignee for Security Reports: [maalers](https://docu.ilias.de/go/usr/119188)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BibliographicListItem)

[//]: # (BEGIN Blog)

* **Blog**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Blog)

[//]: # (BEGIN BookingPool)

* **Booking Pool**
    * Authority to Sign off on Conceptual Changes: [simon.lowe](https://docu.ilias.de/go/usr/79091), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Curate Test Cases: [simon.lowe](https://docu.ilias.de/go/usr/79091), [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to (De-)Assign Authorities: [simon.lowe (Databay AG)](https://docu.ilias.de/go/usr/79091), [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Assignee for Security Reports: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END BookingPool)

[//]: # (BEGIN Calendar)

* **Calendar**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [MISSING]
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Calendar)

[//]: # (BEGIN CategoryAndRepository)

* **Category, Category Reference and Repository**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        ,  [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CategoryAndRepository)

[//]: # (BEGIN Certificate)

* **Certificate**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
  * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784), [ChrisPotter](https://docu.ilias.de/go/usr/90855)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Certificate)

[//]: # (BEGIN Chat)

* **Chatroom**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Chat)


[//]: # (BEGIN Comments)
* **Comments**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Comments)

[//]: # (BEGIN CompetenceManagement)

* **Competence Management**
    * Authority to Sign off on Conceptual Changes: [cludolf](https://docu.ilias.de/go/usr/97658)
    * Authority to Sign off on Code Changes: [cludolf](https://docu.ilias.de/go/usr/97658)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [cludolf](https://docu.ilias.de/go/usr/97658)
    * Assignee for Issues: [cludolf](https://docu.ilias.de/go/usr/97658)
    * Assignee for Security Reports: [cludolf](https://docu.ilias.de/go/usr/97658)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CompetenceManagement)

[//]: # (BEGIN Component)

* **Component**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to Curate Test Cases: [MISSING]
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Component)

[//]: # (BEGIN Contacts)

* **Contacts**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Contacts)

[//]: # (BEGIN ContentPage)

* **ContentPage**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ContentPage)

[//]: # (BEGIN ILIASPageEditor)

* **COPage (aka ILIAS Page Editor)**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ILIASPageEditor)

[//]: # (BEGIN CourseManagement)

* **Course and Course Reference**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [MISSING]
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CourseManagement)

[//]: # (BEGIN CronService)

* **Cron Service**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END CronService)

[//]: # (BEGIN CSSAndTemplates)

* **CSS / Templates**
    * Authority to Sign off on Conceptual Changes: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
    * Authority to Sign off on Code Changes: [BettyFromHH](https://docu.ilias.de/go/usr/96573), [rotegras](https://docu.ilias.de/go/usr/88399), [padvincenzo](https://docu.ilias.de/go/usr/87189)
    * Authority to Curate Test Cases: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
    * Authority to (De-)Assign Authorities: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
    * Assignee for Issues: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
    * Assignee for Security Reports: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
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
    * Assignee for Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Dashboard)

[//]: # (BEGIN Data)

* **Data**
    * Authority to Sign off on Conceptual Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [MISSING]
    * Authority to (De-)Assign Authorities: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Data)

[//]: # (BEGIN DataCollection)

* **Data Collection**
    * Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Curate Test Cases: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DataCollection)

[//]: # (BEGIN DataProtection)

* **Data Protection**
	* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DataProtection)

[//]: # (BEGIN Database)

* **Database**
    * Authority to Sign off on Conceptual Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
        , [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: MISSING
    * Authority to (De-)Assign Authorities: [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Assignee for Issues: [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Assignee for Security Reports: [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Database)

[//]: # (BEGIN DidacticTemplates)

* **Didactic Templates**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END DidacticTemplates)

[//]: # (BEGIN ECSInterface)

* **ECS Interface**
    * Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
    * Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107)
    * Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
    * Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
    * Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
    * Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ECSInterface)

[//]: # (BEGIN EmployeeTalk)

* **EmployeeTalk**
    * Authority to Sign off on Conceptual Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Sign off on Code Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Curate Test Cases: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to (De-)Assign Authorities: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Assignee for Issues: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Assignee for Security Reports: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END EmployeeTalk)

[//]: # (BEGIN EventHandling)

* **EventHandling**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END EventHandling)

[//]: # (BEGIN Excel)

* **Excel**
    * Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
    * Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Assignee for Issues: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Assignee for Security Reports: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Excel)

[//]: # (BEGIN Exercise)

* **Exercise**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Exercise)

[//]: # (BEGIN Export)

* **Export**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Export)

[//]: # (BEGIN Favourites)

* **Favourites**
    * Authority to Sign off on Conceptual Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Curate Test Cases: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to (De-)Assign Authorities: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Assignee for Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Favourites)

[//]: # (BEGIN WebFeed)

* **Feed (aka Web Feeds)**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebFeed)

[//]: # (BEGIN File)

* **File**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END File)

[//]: # (BEGIN Forum)

* **Forum**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: FH Aachen
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Forum)

[//]: # (BEGIN GlobalCache)

* **GlobalCache**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GlobalCache)

[//]: # (BEGIN GlobalScreen)

* **GlobalScreen**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GlobalScreen)

[//]: # (BEGIN Glossary)

* **Glossary**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Glossary)

[//]: # (BEGIN Group)

* **Group and Group Reference**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [MISSING]
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Group)

[//]: # (BEGIN OnlineHelp)

* **Help (aka Online Help)**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        , [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OnlineHelp)

[//]: # (BEGIN HTTP-Request)

* **HTTP-Request**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END HTTP-Request)

[//]: # (BEGIN IndividualAssessment)

* **IndividualAssessment**
    * Authority to Sign off on Conceptual Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to Sign off on Code Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to Curate Test Cases: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to (De-)Assign Authorities: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Assignee for Issues: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Assignee for Security Reports: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END IndividualAssessment)

[//]: # (BEGIN InfoPage)

* **InfoScreen (aka Info Page)**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
        , [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END InfoPage)

[//]: # (BEGIN InitialisationService)

* **Init (aka Initialisation Service)**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END InitialisationService)

[//]: # (BEGIN ItemGroup)

* **ItemGroup**
    * Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Curate Test Cases: [oliver.samoila](https://docu.ilias.de/go/usr/26160), [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Assignee for Security Reports: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ItemGroup)

[//]: # (BEGIN GeneralKiosk-Mode)

* **KioskMode (aka General Kiosk Mode)**
    * Authority to Sign off on Conceptual Changes: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
    * Authority to Sign off on Code Changes: [keven.clausen](https://docu.ilias.de/go/usr/100316), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [jeanine.auerbach](https://docu.ilias.de/go/usr/101332),[cknof](https://docu.ilias.de/go/usr/90890)
    * Authority to Curate Test Cases: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Authority to (De-)Assign Authorities: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
    * Assignee for Issues: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
    * Assignee for Security Reports: [keven.clausen](https://docu.ilias.de/go/usr/100316)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END GeneralKiosk-Mode)

[//]: # (BEGIN LanguageHandling)

* **Language**
    * Authority to Sign off on Conceptual Changes: [mkunkel](https://docu.ilias.de/go/usr/115)
    * Authority to Sign off on Code Changes: [mkunkel](https://docu.ilias.de/go/usr/115), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [ChrisPotter](https://docu.ilias.de/go/usr/90855), [keven.clausen](https://docu.ilias.de/go/usr/100316), [cknof](https://docu.ilias.de/go/usr/90890) 
    * Authority to Curate Test Cases: [ChrisPotter](https://docu.ilias.de/go/usr/90855)
    * Authority to (De-)Assign Authorities: [mkunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Issues: [mkunkel](https://docu.ilias.de/go/usr/115)
    * Assignee for Security Reports: [mkunkel](https://docu.ilias.de/go/usr/115)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LanguageHandling)

[//]: # (BEGIN LearningHistory)

* **Learning History**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningHistory)

[//]: # (BEGIN LearningModuleHTML)

* **Learning Module HTML**
    * Authority to Sign off on Conceptual Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to Sign off on Code Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to Curate Test Cases: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Authority to (De-)Assign Authorities: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Assignee for Issues: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Assignee for Security Reports: [mbecker](https://docu.ilias.de/go/usr/27266)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleHTML)

[//]: # (BEGIN LearningModuleILIAS)

* **Learning Module ILIAS**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [Balliel](https://docu.ilias.de/go/usr/18365)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleILIAS)

[//]: # (BEGIN LearningSequence)

* **Learning Sequence**
    * Authority to Sign off on Conceptual Changes: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
    * Authority to Sign off on Code Changes: [keven.clausen](https://docu.ilias.de/go/usr/100316), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Authority to Curate Test Cases: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Authority to (De-)Assign Authorities: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
    * Assignee for Issues: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
    * Assignee for Security Reports: [keven.clausen](https://docu.ilias.de/go/usr/100316)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningSequence)

[//]: # (BEGIN LegalDocuments)

* **Legal Documents**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/34047)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LegalDocuments)

[//]: # (BEGIN Like)

* **Like**
    * Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [fhelfer](https://docu.ilias.de/go/usr/93367), [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Curate Test Cases: [fhelfer](https://docu.ilias.de/go/usr/93367), [tjoussen](https://docu.ilias.de/go/usr/103745), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Assignee for Security Reports: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Like)

[//]: # (BEGIN Logging)

* **Logging**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
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
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LoginAuthAndRegistration)

[//]: # (BEGIN LTIConsumer)

* **LTI Consumer**
    * Authority to Sign off on Conceptual Changes: [Saaweel](https://docu.ilias.de/go/usr/105654)
    * Authority to Sign off on Code Changes: [Zallax](https://docu.ilias.de/go/usr/101102), [Saaweel](https://docu.ilias.de/go/usr/105654)
    * Authority to Curate Test Cases: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Authority to (De-)Assign Authorities: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Assignee for Issues: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Assignee for Security Reports: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LTIConsumer)

[//]: # (BEGIN LTI)

* **LTI Provider**
    * Authority to Sign off on Conceptual Changes: [Saaweel](https://docu.ilias.de/go/usr/105654)
    * Authority to Sign off on Code Changes: [Zallax](https://docu.ilias.de/go/usr/101102), [Saaweel](https://docu.ilias.de/go/usr/105654), [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
    * Authority to Curate Test Cases: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Authority to (De-)Assign Authorities: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Assignee for Issues: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Assignee for Security Reports: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LTI)

[//]: # (BEGIN Mail)

* **Mail**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Mail)

[//]: # (BEGIN MainMenu)

* **MainMenu**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MainMenu)

[//]: # (BEGIN Maps)

* **Maps**
    * Authority to Sign off on Conceptual Changes: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Authority to Sign off on Code Changes: [keven.clausen](https://docu.ilias.de/go/usr/100316), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Authority to Curate Test Cases: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Authority to (De-)Assign Authorities: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Assignee for Issues: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
    * Assignee for Security Reports: [keven.clausen](https://docu.ilias.de/go/usr/100316)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Maps)

[//]: # (BEGIN MathJax)

* **MathJax**
    * Authority to Sign off on Conceptual Changes: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Authority to Sign off on Code Changes: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Authority to Curate Test Cases: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Authority to (De-)Assign Authorities: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Assignee for Issues: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Assignee for Security Reports: [fneumann](https://docu.ilias.de/go/usr/1560)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MathJax)

[//]: # (BEGIN MediaObjects)

* **Media Objects**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaObjects)

[//]: # (BEGIN MediaPool)

* **Media Pool**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaPool)

[//]: # (BEGIN MediaCast)

* **MediaCast**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [berggold](https://docu.ilias.de/go/usr/22199)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END MediaCast)

[//]: # (BEGIN Membership)

* **Membership**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Membership)

[//]: # (BEGIN Metadata)

* **Metadata**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Curate Test Cases: [Alexandra Tödt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Metadata)

[//]: # (BEGIN News)

* **News**
    * Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Curate Test Cases: [tjoussen](https://docu.ilias.de/go/usr/103745), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Assignee for Security Reports: [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END News)

[//]: # (BEGIN NotesAndComments)

* **Notes (aka Notes and Comments)**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END NotesAndComments)

[//]: # (BEGIN Notification)

* **Notification**
    * Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html), [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html), [oliver.samoila](https://docu.ilias.de/go/usr/26160), [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
    * Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Notification)

[//]: # (BEGIN Notifications)

* **Notifications**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
        , [iszmais](https://docu.ilias.de/go/usr/65630)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Notifications)

[//]: # (BEGIN ObjectService)

* **Object Service**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ObjectService)

[//]: # (BEGIN OpenIdConect)

* **Open ID Connect**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OpenIdConect)

[//]: # (BEGIN OrganisationalUnits)

* **Organisational Units**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [lschmidt-tf](https://docu.ilias.de/go/usr/120143)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [maalers](https://docu.ilias.de/go/usr/119188)
    * Authority to Curate Test Cases: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Authority to (De-)Assign Authorities: [maalers](https://docu.ilias.de/go/usr/119188)
    * Assignee for Issues: [maalers](https://docu.ilias.de/go/usr/119188) 
	* Assignee for Security Reports: [maalers](https://docu.ilias.de/go/usr/119188)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END OrganisationalUnits)

[//]: # (BEGIN PersonalAndSharedResources)

* **Personal and Shared Resources**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END PersonalAndSharedResources)

[//]: # (BEGIN Poll)

* **Poll**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
        , [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Poll)

[//]: # (BEGIN Portfolio)

* **Portfolio**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Portfolio)

[//]: # (BEGIN PreconditionHandling)

* **Precondition Handling**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END PreconditionHandling)

[//]: # (BEGIN Rating)

* **Rating**
    * Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Authority to Curate Test Cases: [fhelfer](https://docu.ilias.de/go/usr/93367), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Assignee for Security Reports: [fhelfer](https://docu.ilias.de/go/usr/93367)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Rating)

[//]: # (BEGIN RBAC)

* **RBAC / Access Control**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END RBAC)

[//]: # (BEGIN Refinery)

* **Refinery**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Refinery)

[//]: # (BEGIN SAML)

* **SAML**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END SAML)

[//]: # (BEGIN LearningModuleSCORM)

* **Scorm (aka Learning Module SCORM 1.2 and 2004)**
    * Authority to Sign off on Conceptual Changes: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Authority to Sign off on Code Changes: [qualitus.dahme](https://docu.ilias.de/go/usr/99160), [qualitus.hartwig](https://docu.ilias.de/go/usr/104063)
    * Authority to Curate Test Cases: [emix](https://docu.ilias.de/go/usr/57311)
    * Authority to (De-)Assign Authorities: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Assignee for Issues: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Assignee for Security Reports: [wischniak](https://docu.ilias.de/go/usr/21896)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END LearningModuleSCORM)

[//]: # (BEGIN Search)

* **Search**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Search)

[//]: # (BEGIN Session)

* **Session**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [MISSING]
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Session)

[//]: # (BEGIN Setup)

* **Setup**
    * Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
    * Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Setup)

[//]: # (BEGIN ShibbolethAuthentication)

* **Shibboleth Authentication**
    * Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END ShibbolethAuthentication)

[//]: # (BEGIN Staff)

* **Staff**
    * Authority to Sign off on Conceptual Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Sign off on Code Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to Curate Test Cases: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Authority to (De-)Assign Authorities: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Assignee for Issues: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Assignee for Security Reports: [tschmitz](https://docu.ilias.de/go/usr/92591)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Staff)

[//]: # (BEGIN StatisticsAndLearningProgress)

* **Statistics and Learning Progress**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END StatisticsAndLearningProgress)

[//]: # (BEGIN StudyProgramme)

* **Study Programme**
    * Authority to Sign off on Conceptual Changes: [lschmidt-tf](https://docu.ilias.de/go/usr/120143)
    * Authority to Sign off on Code Changes: [maalers](https://docu.ilias.de/go/usr/119188)
    * Authority to Curate Test Cases: [maalers](https://docu.ilias.de/go/usr/119188)
    * Authority to (De-)Assign Authorities: [maalers](https://docu.ilias.de/go/usr/119188)
    * Assignee for Issues: [maalers](https://docu.ilias.de/go/usr/119188)
    * Assignee for Security Reports: [maalers](https://docu.ilias.de/go/usr/119188)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END StudyProgramme)

[//]: # (BEGIN Survey)

* **Survey**
    * Authority to Sign off on Conceptual Changes: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Authority to Sign off on Code Changes: [abrahammordev](https://docu.ilias.de/go/usr/110909), [juanma1331](https://docu.ilias.de/go/usr/107249)
    * Authority to Curate Test Cases: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Authority to (De-)Assign Authorities: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Assignee for Issues: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Assignee for Security Reports: [jcopado](https://docu.ilias.de/go/usr/30511)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Survey)

[//]: # (BEGIN SystemCheck)

* **System Check**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END SystemCheck)

[//]: # (BEGIN Tagging)

* **Tagging**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tagging)

[//]: # (BEGIN Tasks)

* **Tasks**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tasks)

[//]: # (BEGIN Taxonomy)

* **Taxonomy**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: Tested separately in each module that supports taxonomies
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Taxonomy)

[//]: # (BEGIN TermsOfService)

* **TermsOfService (aka Terms of Services)**
    * Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784),
        [lscharmer](https://docu.ilias.de/go/usr/87863)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
    * Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END TermsOfService)

[//]: # (BEGIN TestAndAssessment)

* **Test and TestQuestionPool (aka Test & Assessment)**
	* Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Authority to Sign off on Code Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
        , [skergomard](https://docu.ilias.de/go/usr/44474)
        , [dstrassner](https://docu.ilias.de/go/usr/48931)
        , [tjoussen](https://docu.ilias.de/go/usr/103745)
    * Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Assignee for Issues: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Assignee for Security Reports: [dstrassner](https://docu.ilias.de/go/usr/48931)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END TestAndAssessment)

[//]: # (BEGIN Tree)

* **Tree**
    * Authority to Sign off on Conceptual Changes: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Authority to Sign off on Code Changes: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Authority to Curate Test Cases: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Authority to (De-)Assign Authorities: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Assignee for Issues: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Assignee for Security Reports: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Tree)

[//]: # (BEGIN UserService)

* **User (aka User Service)**
    * Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UserService)

[//]: # (BEGIN UICore)

* **UICore**
    * Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947)
        , [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Curate Test Cases: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Assignee for Issues: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Assignee for Security Reports: [tfuhrer](https://docu.ilias.de/go/usr/81947)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UICore)

[//]: # (BEGIN UI-Service)

* **UI-Service**
    * Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/go/usr/27631)
    * Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/go/usr/81947), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Assignee for Issues: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Assignee for Security Reports: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END UI-Service)

[//]: # (BEGIN VirusScanner)

* **Virus Scanner**
    * Authority to Sign off on Conceptual Changes: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Authority to Sign off on Code Changes: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Authority to Curate Test Cases: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Authority to (De-)Assign Authorities: [rschenk (Databay AG)](https://docu.ilias.de/go/usr/18065)
    * Assignee for Issues: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Assignee for Security Reports: [rschenk](https://docu.ilias.de/go/usr/18065)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END VirusScanner)

[//]: # (BEGIN WebAccessChecker)

* **Web Access Checker**
    * Authority to Sign off on Conceptual Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Authority to Sign off on Code Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018), [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
    * Authority to (De-)Assign Authorities: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Assignee for Issues: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Assignee for Security Reports: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebAccessChecker)

[//]: # (BEGIN WebDAV)

* **WebDAV**
	* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to Sign off Testcase Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WebDAV)

[//]: # (BEGIN Weblink)

* **Weblink**
    * Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
    * Authority to Curate Test Cases: [nadine.bauser](https://docu.ilias.de/go/usr/34662)
    * Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
    * Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Weblink)

[//]: # (BEGIN Webservices)

* **Webservices**
    * Authority to Sign off on Conceptual Changes: [githamo](https://docu.ilias.de/go/usr/115389)
    * Authority to Sign off on Code Changes: [githamo](https://docu.ilias.de/go/usr/115389), [sKarki999](https://docu.ilias.de/go/usr/112949)
    * Authority to Curate Test Cases: [sKarki999](https://docu.ilias.de/go/usr/112949)
    * Authority to (De-)Assign Authorities: [TimoScheuer](https://docu.ilias.de/go/usr/102976)
    * Assignee for Issues: [sKarki999](https://docu.ilias.de/go/usr/112949)
    * Assignee for Security Reports: [sKarki999](https://docu.ilias.de/go/usr/112949)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Webservices)

[//]: # (BEGIN WhoIsOnline)

* **Who is online?**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END WhoIsOnline)

[//]: # (BEGIN Wiki)

* **Wiki**
    * Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
    * Authority to Curate Test Cases: n.n., Uni Köln
    * Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
    * Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END Wiki)

[//]: # (BEGIN xAPIAndcmi5)

* **xAPI/cmi5**
    * Authority to Sign off on Conceptual Changes: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Authority to Sign off on Code Changes: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Authority to Curate Test Cases: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Authority to (De-)Assign Authorities: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Assignee for Issues: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Assignee for Security Reports: [ukohnle](https://docu.ilias.de/go/usr/21855)
    * Unit-specific Guidelines, Rules, and Regulations: [LINK MISSING]('')

[//]: # (END xAPIAndcmi5)

## Unmaintained Components

The following directories are currently unmaintained:

* ILIAS/CSV
* ILIAS/EventHandling

