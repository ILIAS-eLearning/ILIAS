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
authorised to assign and deassign other people to the authorities of a component.
They are the only ones allowed to modify the authority entries of a component in
this document.

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
Authorities are tracked directly in this document: every
[component](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/components-and-directories.md)
of ILIAS has a block in the listing below containing the following fields:

* **"Authority to Sign off on Conceptual Changes"**:
    An array in the form [ `[<github-username>](<link-to-docu-profile>)` ]
    linking the GitHub username to the corresponding profile on https://docu.ilias.de.
* **"Authority to Sign off on Code Changes"**:
    An array in the form [ `[<github-username>](<link-to-docu-profile>)` ]
    linking the GitHub username to the corresponding profile on https://docu.ilias.de.
* **"Authority to Curate Test Cases"**:
    An array in the form [ `[<github-username>](<link-to-docu-profile>)` ]
    linking the GitHub username to the corresponding profile on https://docu.ilias.de.
* **"Authority to (De-)Assign Authorities"**:
    An array in the form [ `[<github-username>](<link-to-docu-profile>)` ]
    linking the GitHub username to the corresponding profile on https://docu.ilias.de.

* **"Assignee for Issues"**:
    A string in the form `[<github-username>](<link-to-docu-profile>)`
    linking the GitHub username to the corresponding profile on https://docu.ilias.de.
* **"Assignee for Security Reports"**:
    A string in the form `[<github-username>](<link-to-docu-profile>)`
    linking the GitHub username to the corresponding profile on https://docu.ilias.de.

## Current Maintainerships

Components are listed alphabetically by their folder name in `components/ILIAS/`.

[//]: # (BEGIN AccessControl)

#### [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/AccessControl)

*Belongs to:* [RBAC](https://docu.ilias.de/go/wiki/wpage_2_1357)

* Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
* Assignee for Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
* Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)

[//]: # (END AccessControl)


[//]: # (BEGIN Accessibility)

#### [Accessibility](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Accessibility)

**Status:** Unmaintained / NONE

*Belongs to:* [Accessibility](https://docu.ilias.de/go/wiki/wpage_30_1357)

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Accessibility)


[//]: # (BEGIN Accordion)

#### [Accordion](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Accordion)

**Status:** Unmaintained / NONE

*Belongs to:* Accordion

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Accordion)


[//]: # (BEGIN ActiveRecord)

#### [ActiveRecord](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ActiveRecord)

*Belongs to:* ActiveRecord

* Authority to Sign off on Conceptual Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
* Authority to Sign off on Code Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
* Authority to Curate Test Cases: MISSING
* Authority to (De-)Assign Authorities: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
* Assignee for Issues: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
* Assignee for Security Reports: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)

[//]: # (END ActiveRecord)


[//]: # (BEGIN Administration)

#### [Administration](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Administration)

*Belongs to:* [Administration](https://docu.ilias.de/go/wiki/wpage_246_1357)

* Authority to Sign off on Conceptual Changes: [fneumann](https://docu.ilias.de/go/usr/1560)
* Authority to Sign off on Code Changes: [fneumann](https://docu.ilias.de/go/usr/1560), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Authority to Curate Test Cases: [fneumann](https://docu.ilias.de/go/usr/1560), [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [fneumann (Databay AG)](https://docu.ilias.de/go/usr/1560), [lscharmer (Databay AG)](https://docu.ilias.de/go/usr/87863)
* Assignee for Issues: [fneumann](https://docu.ilias.de/go/usr/1560)
* Assignee for Security Reports: [fneumann](https://docu.ilias.de/go/usr/1560)

[//]: # (END Administration)


[//]: # (BEGIN AdministrativeNotification)

#### [AdministrativeNotification](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/AdministrativeNotification)

*Belongs to:* [Administrative Notifications](https://docu.ilias.de/go/wiki/wpage_7290_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END AdministrativeNotification)


[//]: # (BEGIN ADT)

#### [ADT](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ADT)

*Belongs to:* [Metadata](https://docu.ilias.de/go/wiki/wpage_973_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Curate Test Cases: [Alexandra Tödt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END ADT)


[//]: # (BEGIN AdvancedMetaData)

#### [AdvancedMetaData](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/AdvancedMetaData)

*Belongs to:* [Metadata](https://docu.ilias.de/go/wiki/wpage_973_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Curate Test Cases: [Alexandra Tödt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END AdvancedMetaData)


[//]: # (BEGIN App)

#### [App](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/App)

**Status:** Unmaintained / NONE

*Belongs to:* App

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END App)


[//]: # (BEGIN AuthApache)

#### [AuthApache](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/AuthApache)

*Belongs to:* [Login, Auth & Registration](https://docu.ilias.de/go/wiki/wpage_19_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END AuthApache)


[//]: # (BEGIN Authentication)

#### [Authentication](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Authentication)

*Belongs to:* [Login, Auth & Registration](https://docu.ilias.de/go/wiki/wpage_19_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Authentication)


[//]: # (BEGIN AuthShibboleth)

#### [AuthShibboleth](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/AuthShibboleth)

*Belongs to:* [Login, Auth & Registration](https://docu.ilias.de/go/wiki/wpage_19_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END AuthShibboleth)


[//]: # (BEGIN AuthSOAP)

#### [AuthSOAP](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/AuthSOAP)

*Belongs to:* SOAP

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to (De-)Assign Authorities: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)

[//]: # (END AuthSOAP)


[//]: # (BEGIN Awareness)

#### [Awareness](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Awareness)

*Belongs to:* [Who is online?](https://docu.ilias.de/go/wiki/wpage_293_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Awareness)


[//]: # (BEGIN BackgroundTasks)

#### [BackgroundTasks](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/BackgroundTasks)

*Belongs to:* [Background Tasks](https://docu.ilias.de/go/wiki/wpage_4383_1357)

* Authority to Sign off on Conceptual Changes: [thojou](https://docu.ilias.de/go/usr/103745), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [thojou](https://docu.ilias.de/go/usr/103745), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: MISSING
* Authority to (De-)Assign Authorities: [thojou (Databay AG)](https://docu.ilias.de/go/usr/103745)
* Assignee for Issues: [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Security Reports: [thojou](https://docu.ilias.de/go/usr/103745)

[//]: # (END BackgroundTasks)


[//]: # (BEGIN BackgroundTasks_)

#### [BackgroundTasks_](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/BackgroundTasks_)

*Belongs to:* [Background Tasks](https://docu.ilias.de/go/wiki/wpage_4383_1357)

* Authority to Sign off on Conceptual Changes: [thojou](https://docu.ilias.de/go/usr/103745), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [thojou](https://docu.ilias.de/go/usr/103745), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: MISSING
* Authority to (De-)Assign Authorities: [thojou (Databay AG)](https://docu.ilias.de/go/usr/103745)
* Assignee for Issues: [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Security Reports: [thojou](https://docu.ilias.de/go/usr/103745)

[//]: # (END BackgroundTasks_)


[//]: # (BEGIN Badge)

#### [Badge](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Badge)

*Belongs to:* [Badges](https://docu.ilias.de/go/wiki/wpage_4203_1357)

* Authority to Sign off on Conceptual Changes: [fhelfer](https://docu.ilias.de/go/usr/93367)
* Authority to Sign off on Code Changes: [fhelfer](https://docu.ilias.de/go/usr/93367), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [fhelfer](https://docu.ilias.de/go/usr/93367)
* Assignee for Security Reports: [fhelfer](https://docu.ilias.de/go/usr/93367)

[//]: # (END Badge)


[//]: # (BEGIN Benchmark)

#### [Benchmark](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Benchmark)

*Belongs to:* Benchmark

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END Benchmark)


[//]: # (BEGIN Bibliographic)

#### [Bibliographic](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Bibliographic)

*Belongs to:* [Bibliographic List Item](https://docu.ilias.de/go/wiki/wpage_2553_1357)

* Authority to Sign off on Conceptual Changes: [lschmidt-tf](https://docu.ilias.de/go/usr/120143)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [maalers](https://docu.ilias.de/go/usr/119188), [mhomann-tf](https://docu.ilias.de/go/usr/120211)
* Authority to Curate Test Cases: [maalers](https://docu.ilias.de/go/usr/119188), [mhomann-tf](https://docu.ilias.de/go/usr/120211)
* Authority to (De-)Assign Authorities: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Issues: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Security Reports: [maalers](https://docu.ilias.de/go/usr/119188)

[//]: # (END Bibliographic)


[//]: # (BEGIN Blog)

#### [Blog](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Blog)

*Belongs to:* [Blog](https://docu.ilias.de/go/wiki/wpage_1448_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Blog)


[//]: # (BEGIN BookingManager)

#### [BookingManager](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/BookingManager)

*Belongs to:* [Booking Pool](https://docu.ilias.de/go/wiki/wpage_133_1357)

* Authority to Sign off on Conceptual Changes: [simon.lowe](https://docu.ilias.de/go/usr/79091), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [simon.lowe](https://docu.ilias.de/go/usr/79091), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to (De-)Assign Authorities: [simon.lowe (Databay AG)](https://docu.ilias.de/go/usr/79091), [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Security Reports: [thojou](https://docu.ilias.de/go/usr/103745)

[//]: # (END BookingManager)


[//]: # (BEGIN Cache)

#### [Cache](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Cache)

*Belongs to:* [Global Cache](https://docu.ilias.de/go/wiki/wpage_6435_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END Cache)


[//]: # (BEGIN Calendar)

#### [Calendar](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Calendar)

*Belongs to:* [Calendar](https://docu.ilias.de/go/wiki/wpage_23_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Calendar)


[//]: # (BEGIN Category)

#### [Category](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Category)

*Belongs to:* [Category and Repository](https://docu.ilias.de/go/wiki/wpage_106_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Category)


[//]: # (BEGIN CategoryReference)

#### [CategoryReference](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/CategoryReference)

*Belongs to:* [Category and Repository](https://docu.ilias.de/go/wiki/wpage_106_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END CategoryReference)


[//]: # (BEGIN Certificate)

#### [Certificate](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Certificate)

*Belongs to:* [Certificate](https://docu.ilias.de/go/wiki/wpage_66_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784), [ChrisPotter](https://docu.ilias.de/go/usr/90855)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Certificate)


[//]: # (BEGIN Chart)

#### [Chart](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Chart)

**Status:** Unmaintained / NONE

*Belongs to:* Chart

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Chart)


[//]: # (BEGIN Chatroom)

#### [Chatroom](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Chatroom)

*Belongs to:* Chatroom

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Chatroom)



[//]: # (BEGIN CmiXapi)

#### [CmiXapi](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/CmiXapi)

*Belongs to:* [xAPI](https://docu.ilias.de/go/wiki/wpage_2921_1357)

* Authority to Sign off on Conceptual Changes: [ukohnle](https://docu.ilias.de/go/usr/21855)
* Authority to Sign off on Code Changes: [ukohnle](https://docu.ilias.de/go/usr/21855)
* Authority to Curate Test Cases: [ukohnle](https://docu.ilias.de/go/usr/21855)
* Authority to (De-)Assign Authorities: [ukohnle](https://docu.ilias.de/go/usr/21855)
* Assignee for Issues: [ukohnle](https://docu.ilias.de/go/usr/21855)
* Assignee for Security Reports: [ukohnle](https://docu.ilias.de/go/usr/21855)

[//]: # (END CmiXapi)


[//]: # (BEGIN Component)

#### [Component](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Component)

*Belongs to:* Component

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087), [tfuhrer](https://docu.ilias.de/go/usr/81947)

[//]: # (END Component)


[//]: # (BEGIN Conditions)

#### [Conditions](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Conditions)

*Belongs to:* Conditions

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Conditions)


[//]: # (BEGIN Contact)

#### [Contact](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Contact)

*Belongs to:* [Contacts](https://docu.ilias.de/go/wiki/wpage_3740_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Contact)


[//]: # (BEGIN Container)

#### [Container](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Container)

*Belongs to:* [Category and Repository](https://docu.ilias.de/go/wiki/wpage_106_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Container)


[//]: # (BEGIN ContainerReference)

#### [ContainerReference](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ContainerReference)

*Belongs to:* [Category and Repository](https://docu.ilias.de/go/wiki/wpage_106_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END ContainerReference)


[//]: # (BEGIN ContentPage)

#### [ContentPage](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ContentPage)

*Belongs to:* [Content Page](https://docu.ilias.de/go/wiki/wpage_5369_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END ContentPage)



[//]: # (BEGIN COPage)

#### [COPage](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/COPage)

*Belongs to:* [ILIAS Page Editor](https://docu.ilias.de/go/wiki/wpage_2141_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END COPage)


[//]: # (BEGIN Course)

#### [Course](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Course)

*Belongs to:* [Course Management](https://docu.ilias.de/go/wiki/wpage_13_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Course)


[//]: # (BEGIN CourseReference)

#### [CourseReference](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/CourseReference)

*Belongs to:* [Course Management](https://docu.ilias.de/go/wiki/wpage_13_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END CourseReference)


[//]: # (BEGIN Cron)

#### [Cron](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Cron)

*Belongs to:* [Cron Service](https://docu.ilias.de/go/wiki/wpage_2357_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Cron)


[//]: # (BEGIN CSV)

#### [CSV](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/CSV)

**Status:** Unmaintained / NONE

*Belongs to:* CSV

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END CSV)


[//]: # (BEGIN Dashboard)

#### [Dashboard](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Dashboard)

*Belongs to:* [Dashboard](https://docu.ilias.de/go/wiki/wpage_6092_1357)

* Authority to Sign off on Conceptual Changes: [iszmais](https://docu.ilias.de/go/usr/65630), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/go/usr/65630), [lscharmer](https://docu.ilias.de/go/usr/87863), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [iszmais (Databay AG)](https://docu.ilias.de/go/usr/65630), [lscharmer (Databay AG)](https://docu.ilias.de/go/usr/87863)
* Assignee for Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
* Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)

[//]: # (END Dashboard)


[//]: # (BEGIN Data)

#### [Data](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Data)

*Belongs to:* Data

* Authority to Sign off on Conceptual Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Data)


[//]: # (BEGIN Database)

#### [Database](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Database)

*Belongs to:* [Database](https://docu.ilias.de/go/wiki/wpage_12_1357)

* Authority to Sign off on Conceptual Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [lscharmer](https://docu.ilias.de/go/usr/87863), [mjansen](https://docu.ilias.de/go/usr/8784), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: MISSING
* Authority to (De-)Assign Authorities: [lscharmer](https://docu.ilias.de/go/usr/87863)
* Assignee for Issues: [lscharmer](https://docu.ilias.de/go/usr/87863)
* Assignee for Security Reports: [lscharmer](https://docu.ilias.de/go/usr/87863)

[//]: # (END Database)


[//]: # (BEGIN DataCollection)

#### [DataCollection](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/DataCollection)

*Belongs to:* [Data Collection](https://docu.ilias.de/go/wiki/wpage_2340_1357)

* Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [iszmais](https://docu.ilias.de/go/usr/65630)
* Authority to Curate Test Cases: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [iszmais](https://docu.ilias.de/go/usr/65630)
* Assignee for Security Reports: [iszmais](https://docu.ilias.de/go/usr/65630)

[//]: # (END DataCollection)


[//]: # (BEGIN DataProtection)

#### [DataProtection](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/DataProtection)

*Belongs to:* [Privacy, Terms of Service and Data Protection (incl. Terms of Service)](https://docu.ilias.de/go/wiki/wpage_4995_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END DataProtection)


[//]: # (BEGIN DI)

#### [DI](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/DI)

**Status:** Unmaintained / NONE

*Belongs to:* DI

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END DI)


[//]: # (BEGIN DidacticTemplate)

#### [DidacticTemplate](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/DidacticTemplate)

*Belongs to:* Didactic Templates

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END DidacticTemplate)


[//]: # (BEGIN EmployeeTalk)

#### [EmployeeTalk](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/EmployeeTalk)

*Belongs to:* EmployeeTalk

* Authority to Sign off on Conceptual Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Sign off on Code Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Curate Test Cases: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to (De-)Assign Authorities: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Assignee for Issues: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Assignee for Security Reports: [tschmitz](https://docu.ilias.de/go/usr/92591)

[//]: # (END EmployeeTalk)


[//]: # (BEGIN Environment)

#### [Environment](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Environment)

*Belongs to:* Environment

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Environment)


[//]: # (BEGIN EventHandling)

#### [EventHandling](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/EventHandling)

*Belongs to:* EventHandling

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END EventHandling)


[//]: # (BEGIN Excel)

#### [Excel](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Excel)

*Belongs to:* Excel

* Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
* Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/goto_docu_usr_44474.html)
* Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
* Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
* Assignee for Issues: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)
* Assignee for Security Reports: [dstrassner](https://docu.ilias.de/goto_docu_usr_48931.html)

[//]: # (END Excel)


[//]: # (BEGIN Exceptions)

#### [Exceptions](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Exceptions)

**Status:** Unmaintained / NONE

*Belongs to:* Exceptions

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Exceptions)


[//]: # (BEGIN Exercise)

#### [Exercise](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Exercise)

*Belongs to:* [Exercise](https://docu.ilias.de/go/wiki/wpage_28_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Exercise)


[//]: # (BEGIN Export)

#### [Export](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Export)

*Belongs to:* [Export](https://docu.ilias.de/go/wiki/wpage_91_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/go/usr/27631)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Export)


[//]: # (BEGIN Feeds)

#### [Feeds](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Feeds)

*Belongs to:* [News - RSS - Webfeeds](https://docu.ilias.de/go/wiki/wpage_38_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Feeds)


[//]: # (BEGIN File)

#### [File](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/File)

*Belongs to:* [File](https://docu.ilias.de/go/wiki/wpage_4_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END File)


[//]: # (BEGIN FileDelivery)

#### [FileDelivery](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/FileDelivery)

*Belongs to:* [File](https://docu.ilias.de/go/wiki/wpage_4_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END FileDelivery)


[//]: # (BEGIN FileServices)

#### [FileServices](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/FileServices)

*Belongs to:* [File](https://docu.ilias.de/go/wiki/wpage_4_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END FileServices)


[//]: # (BEGIN Filesystem)

#### [Filesystem](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Filesystem)

*Belongs to:* Filesystem

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END Filesystem)


[//]: # (BEGIN FileUpload)

#### [FileUpload](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/FileUpload)

*Belongs to:* [File](https://docu.ilias.de/go/wiki/wpage_4_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END FileUpload)


[//]: # (BEGIN Folder)

#### [Folder](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Folder)

*Belongs to:* [Category and Repository](https://docu.ilias.de/go/wiki/wpage_106_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Folder)


[//]: # (BEGIN Form)

#### [Form](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Form)

**Status:** Unmaintained / NONE

*Belongs to:* Form

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Form)


[//]: # (BEGIN Forum)

#### [Forum](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Forum)

*Belongs to:* [Forum](https://docu.ilias.de/go/wiki/wpage_35_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: FH Aachen
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Forum)


[//]: # (BEGIN GlobalScreen)

#### [GlobalScreen](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/GlobalScreen)

*Belongs to:* [Global Screen Service](https://docu.ilias.de/go/wiki/wpage_6079_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END GlobalScreen)


[//]: # (BEGIN Glossary)

#### [Glossary](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Glossary)

*Belongs to:* [Glossary](https://docu.ilias.de/go/wiki/wpage_121_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Glossary)


[//]: # (BEGIN Group)

#### [Group](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Group)

*Belongs to:* [Group and Group Reference](https://docu.ilias.de/go/wiki/wpage_39_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Group)


[//]: # (BEGIN GroupReference)

#### [GroupReference](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/GroupReference)

*Belongs to:* [Group](https://docu.ilias.de/go/wiki/wpage_39_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END GroupReference)


[//]: # (BEGIN Help)

#### [Help](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Help)

*Belongs to:* [Online Help](https://docu.ilias.de/go/wiki/wpage_415_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Help)


[//]: # (BEGIN History)

#### [History](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/History)

**Status:** Unmaintained / NONE

*Belongs to:* History

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END History)


[//]: # (BEGIN Html)

#### [Html](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Html)

*Belongs to:* [Security (incl. Web Access Checker)](https://docu.ilias.de/go/wiki/wpage_866_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: FH Aachen
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Html)


[//]: # (BEGIN HTMLLearningModule)

#### [HTMLLearningModule](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/HTMLLearningModule)

*Belongs to:* [Learning Module HTML](https://docu.ilias.de/go/wiki/wpage_135_1357)

* Authority to Sign off on Conceptual Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to Sign off on Code Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to Curate Test Cases: [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to (De-)Assign Authorities: [mbecker](https://docu.ilias.de/go/usr/27266)
* Assignee for Issues: [mbecker](https://docu.ilias.de/go/usr/27266)
* Assignee for Security Reports: [mbecker](https://docu.ilias.de/go/usr/27266)

[//]: # (END HTMLLearningModule)


[//]: # (BEGIN HTTP)

#### [HTTP](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/HTTP)

*Belongs to:* HTTP-Request

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END HTTP)


[//]: # (BEGIN Http_)

#### [Http_](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Http_)

**Status:** Unmaintained / NONE

*Belongs to:* Http_

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Http_)


[//]: # (BEGIN ILIASObject)

#### [ILIASObject](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ILIASObject)

*Belongs to:* ILIASObject

* Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
* Assignee for Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
* Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)

[//]: # (END ILIASObject)


[//]: # (BEGIN Imprint)

#### [Imprint](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Imprint)

**Status:** Unmaintained / NONE

*Belongs to:* Imprint

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Imprint)


[//]: # (BEGIN IndividualAssessment)

#### [IndividualAssessment](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/IndividualAssessment)

*Belongs to:* [Individual Assessment](https://docu.ilias.de/go/wiki/wpage_4226_1357)

* Authority to Sign off on Conceptual Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to Sign off on Code Changes: [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to Curate Test Cases: [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to (De-)Assign Authorities: [mbecker](https://docu.ilias.de/go/usr/27266)
* Assignee for Issues: [mbecker](https://docu.ilias.de/go/usr/27266)
* Assignee for Security Reports: [mbecker](https://docu.ilias.de/go/usr/27266)

[//]: # (END IndividualAssessment)


[//]: # (BEGIN InfoScreen)

#### [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/InfoScreen)

*Belongs to:* [Info Page](https://docu.ilias.de/go/wiki/wpage_2095_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END InfoScreen)


[//]: # (BEGIN Init)

#### [Init](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Init)

*Belongs to:* Initialisation Service

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Init)


[//]: # (BEGIN ItemGroup)

#### [ItemGroup](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ItemGroup)

*Belongs to:* ItemGroup

* Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [oliver.samoila](https://docu.ilias.de/go/usr/26160), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Security Reports: [thojou](https://docu.ilias.de/go/usr/103745)

[//]: # (END ItemGroup)


[//]: # (BEGIN JavaScript)

#### [JavaScript](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/JavaScript)

**Status:** Unmaintained / NONE

*Belongs to:* JavaScript

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END JavaScript)


[//]: # (BEGIN jQuery)

#### [jQuery](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/jQuery)

**Status:** Unmaintained / NONE

*Belongs to:* jQuery

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END jQuery)


[//]: # (BEGIN KeyValueStorage)

#### [KeyValueStorage](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/KeyValueStorage)

*Belongs to:* KeyValueStorage

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784), [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784), [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784), [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END KeyValueStorage)


[//]: # (BEGIN KioskMode)

#### [KioskMode](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/KioskMode)

*Belongs to:* General Kiosk-Mode

* Authority to Sign off on Conceptual Changes: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Authority to Sign off on Code Changes: [keven.clausen](https://docu.ilias.de/go/usr/100316), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [jeanine.auerbach](https://docu.ilias.de/go/usr/101332), [cknof](https://docu.ilias.de/go/usr/90890), [dkippKPG](https://docu.ilias.de/go/usr/120714)
* Authority to Curate Test Cases: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
* Authority to (De-)Assign Authorities: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Assignee for Issues: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Assignee for Security Reports: [keven.clausen](https://docu.ilias.de/go/usr/100316)

[//]: # (END KioskMode)


[//]: # (BEGIN KioskMode_)

#### [KioskMode_](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/KioskMode_)

*Belongs to:* General Kiosk-Mode

* Authority to Sign off on Conceptual Changes: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Authority to Sign off on Code Changes: [keven.clausen](https://docu.ilias.de/go/usr/100316), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [jeanine.auerbach](https://docu.ilias.de/go/usr/101332), [cknof](https://docu.ilias.de/go/usr/90890), [dkippKPG](https://docu.ilias.de/go/usr/120714)
* Authority to Curate Test Cases: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
* Authority to (De-)Assign Authorities: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Assignee for Issues: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Assignee for Security Reports: [keven.clausen](https://docu.ilias.de/go/usr/100316)

[//]: # (END KioskMode_)


[//]: # (BEGIN Language)

#### [Language](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Language)

*Belongs to:* Language

* Authority to Sign off on Conceptual Changes: [mkunkel](https://docu.ilias.de/go/usr/115)
* Authority to Sign off on Code Changes: [mkunkel](https://docu.ilias.de/go/usr/115), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [ChrisPotter](https://docu.ilias.de/go/usr/90855), [keven.clausen](https://docu.ilias.de/go/usr/100316), [cknof](https://docu.ilias.de/go/usr/90890), [dkippKPG](https://docu.ilias.de/go/usr/120714)
* Authority to Curate Test Cases: [ChrisPotter](https://docu.ilias.de/go/usr/90855)
* Authority to (De-)Assign Authorities: [mkunkel](https://docu.ilias.de/go/usr/115)
* Assignee for Issues: [mkunkel](https://docu.ilias.de/go/usr/115)
* Assignee for Security Reports: [mkunkel](https://docu.ilias.de/go/usr/115)

[//]: # (END Language)


[//]: # (BEGIN LDAP)

#### [LDAP](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LDAP)

*Belongs to:* [Login, Auth & Registration](https://docu.ilias.de/go/wiki/wpage_19_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END LDAP)


[//]: # (BEGIN LearningHistory)

#### [LearningHistory](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LearningHistory)

*Belongs to:* [Learning History](https://docu.ilias.de/go/wiki/wpage_5454_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END LearningHistory)


[//]: # (BEGIN LearningModule)

#### [LearningModule](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LearningModule)

*Belongs to:* [Learning Module ILIAS](https://docu.ilias.de/go/wiki/wpage_33_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [Balliel](https://docu.ilias.de/go/usr/18365)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END LearningModule)


[//]: # (BEGIN LearningSequence)

#### [LearningSequence](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LearningSequence)

*Belongs to:* [Learning Sequence](https://docu.ilias.de/go/wiki/wpage_5557_1357)

* Authority to Sign off on Conceptual Changes: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Authority to Sign off on Code Changes: [keven.clausen](https://docu.ilias.de/go/usr/100316), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [jeanine.auerbach](https://docu.ilias.de/go/usr/101332), [dkippKPG](https://docu.ilias.de/go/usr/120714)
* Authority to Curate Test Cases: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
* Authority to (De-)Assign Authorities: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Assignee for Issues: [katrin.grosskopf](https://docu.ilias.de/go/usr/68340)
* Assignee for Security Reports: [keven.clausen](https://docu.ilias.de/go/usr/100316)

[//]: # (END LearningSequence)


[//]: # (BEGIN LegalDocuments)

#### [LegalDocuments](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LegalDocuments)

*Belongs to:* Legal Documents

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/34047)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END LegalDocuments)


[//]: # (BEGIN Like)

#### [Like](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Like)

*Belongs to:* Like

* Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [fhelfer](https://docu.ilias.de/go/usr/93367), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [fhelfer](https://docu.ilias.de/go/usr/93367), [thojou](https://docu.ilias.de/go/usr/103745), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [fhelfer](https://docu.ilias.de/go/usr/93367)
* Assignee for Security Reports: [fhelfer](https://docu.ilias.de/go/usr/93367)

[//]: # (END Like)


[//]: # (BEGIN Link)

#### [Link](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Link)

**Status:** Unmaintained / NONE

*Belongs to:* Link

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Link)


[//]: # (BEGIN Locator)

#### [Locator](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Locator)

**Status:** Unmaintained / NONE

*Belongs to:* Locator

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Locator)


[//]: # (BEGIN Logging)

#### [Logging](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Logging)

*Belongs to:* [Logging](https://docu.ilias.de/go/wiki/wpage_148_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Logging)


[//]: # (BEGIN LTIConsumer)

#### [LTIConsumer](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LTIConsumer)

*Belongs to:* [LTI](https://docu.ilias.de/go/wiki/wpage_4335_1357)

* Authority to Sign off on Conceptual Changes: [Saaweel](https://docu.ilias.de/go/usr/105654)
* Authority to Sign off on Code Changes: [Zallax](https://docu.ilias.de/go/usr/101102), [Saaweel](https://docu.ilias.de/go/usr/105654)
* Authority to Curate Test Cases: [jcopado](https://docu.ilias.de/go/usr/30511)
* Authority to (De-)Assign Authorities: [jcopado](https://docu.ilias.de/go/usr/30511)
* Assignee for Issues: [jcopado](https://docu.ilias.de/go/usr/30511)
* Assignee for Security Reports: [jcopado](https://docu.ilias.de/go/usr/30511)

[//]: # (END LTIConsumer)


[//]: # (BEGIN LTIProvider)

#### [LTIProvider](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LTIProvider)

*Belongs to:* [LTI](https://docu.ilias.de/go/wiki/wpage_4335_1357)

* Authority to Sign off on Conceptual Changes: [Saaweel](https://docu.ilias.de/go/usr/105654)
* Authority to Sign off on Code Changes: [Zallax](https://docu.ilias.de/go/usr/101102), [Saaweel](https://docu.ilias.de/go/usr/105654), [smeyer](https://docu.ilias.de/goto_docu_usr_191.html)
* Authority to Curate Test Cases: [jcopado](https://docu.ilias.de/go/usr/30511)
* Authority to (De-)Assign Authorities: [jcopado](https://docu.ilias.de/go/usr/30511)
* Assignee for Issues: [jcopado](https://docu.ilias.de/go/usr/30511)
* Assignee for Security Reports: [jcopado](https://docu.ilias.de/go/usr/30511)

[//]: # (END LTIProvider)


[//]: # (BEGIN Mail)

#### [Mail](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Mail)

*Belongs to:* [Mail](https://docu.ilias.de/go/wiki/wpage_36_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Mail)


[//]: # (BEGIN MainMenu)

#### [MainMenu](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/MainMenu)

*Belongs to:* MainMenu

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END MainMenu)


[//]: # (BEGIN Maps)

#### [Maps](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Maps)

*Belongs to:* [Maps](https://docu.ilias.de/go/wiki/wpage_2909_1357)

* Authority to Sign off on Conceptual Changes: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
* Authority to Sign off on Code Changes: [keven.clausen](https://docu.ilias.de/go/usr/100316), [katrin.grosskopf](https://docu.ilias.de/go/usr/68340), [jeanine.auerbach](https://docu.ilias.de/go/usr/101332), [dkippKPG](https://docu.ilias.de/go/usr/120714)
* Authority to Curate Test Cases: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
* Authority to (De-)Assign Authorities: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
* Assignee for Issues: [jeanine.auerbach](https://docu.ilias.de/go/usr/101332)
* Assignee for Security Reports: [keven.clausen](https://docu.ilias.de/go/usr/100316)

[//]: # (END Maps)


[//]: # (BEGIN Math)

#### [Math](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Math)

**Status:** Unmaintained / NONE

*Belongs to:* Math

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Math)


[//]: # (BEGIN MediaCast)

#### [MediaCast](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/MediaCast)

*Belongs to:* MediaCast

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [berggold](https://docu.ilias.de/go/usr/22199)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END MediaCast)


[//]: # (BEGIN MediaObjects)

#### [MediaObjects](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/MediaObjects)

*Belongs to:* [Media Pools and Media Objects](https://docu.ilias.de/go/wiki/wpage_83_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END MediaObjects)


[//]: # (BEGIN MediaPool)

#### [MediaPool](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/MediaPool)

*Belongs to:* [Media Pools and Media Objects](https://docu.ilias.de/go/wiki/wpage_83_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END MediaPool)


[//]: # (BEGIN Membership)

#### [Membership](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Membership)

*Belongs to:* Membership

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Membership)


[//]: # (BEGIN MetaData)

#### [MetaData](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/MetaData)

*Belongs to:* [Metadata](https://docu.ilias.de/go/wiki/wpage_973_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Curate Test Cases: [Alexandra Tödt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END MetaData)


[//]: # (BEGIN Migration)

#### [Migration](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Migration)

**Status:** Unmaintained / NONE

*Belongs to:* Migration

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Migration)


[//]: # (BEGIN Multilingualism)

#### [Multilingualism](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Multilingualism)

**Status:** Unmaintained / NONE

*Belongs to:* Multilingualism

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Multilingualism)


[//]: # (BEGIN MyStaff)

#### [MyStaff](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/MyStaff)

*Belongs to:* [Staff](https://docu.ilias.de/go/wiki/wpage_4829_1357)

* Authority to Sign off on Conceptual Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Sign off on Code Changes: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Curate Test Cases: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to (De-)Assign Authorities: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Assignee for Issues: [tschmitz](https://docu.ilias.de/go/usr/92591)
* Assignee for Security Reports: [tschmitz](https://docu.ilias.de/go/usr/92591)

[//]: # (END MyStaff)


[//]: # (BEGIN News)

#### [News](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/News)

*Belongs to:* [News - RSS - Webfeeds](https://docu.ilias.de/go/wiki/wpage_38_1357)

* Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [thojou](https://docu.ilias.de/go/usr/103745), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Security Reports: [thojou](https://docu.ilias.de/go/usr/103745)

[//]: # (END News)


[//]: # (BEGIN Notes)

#### [Notes](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Notes)

*Belongs to:* [Notes and Comments](https://docu.ilias.de/go/wiki/wpage_31_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Notes)


[//]: # (BEGIN Notification)

#### [Notification](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Notification)

*Belongs to:* Notification

* Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html), [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html), [oliver.samoila](https://docu.ilias.de/go/usr/26160), [iszmais](https://docu.ilias.de/goto_docu_usr_65630.html)
* Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/goto_docu_usr_8784.html)

[//]: # (END Notification)


[//]: # (BEGIN Notifications)

#### [Notifications](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Notifications)

*Belongs to:* [Notifications](https://docu.ilias.de/go/wiki/wpage_1754_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [iszmais](https://docu.ilias.de/go/usr/65630)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [iszmais](https://docu.ilias.de/go/usr/65630)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784), [iszmais](https://docu.ilias.de/go/usr/65630)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Notifications)


[//]: # (BEGIN OnScreenChat)

#### [OnScreenChat](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/OnScreenChat)

*Belongs to:* [Chat](https://docu.ilias.de/go/wiki/wpage_37_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [mbecker](https://docu.ilias.de/go/usr/27266)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END OnScreenChat)


[//]: # (BEGIN OpenIdConnect)

#### [OpenIdConnect](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/OpenIdConnect)

*Belongs to:* [Login, Auth & Registration](https://docu.ilias.de/go/wiki/wpage_19_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to (De-)Assign Authorities: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)

[//]: # (END OpenIdConnect)


[//]: # (BEGIN OrgUnit)

#### [OrgUnit](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/OrgUnit)

*Belongs to:* [Organisational Units](https://docu.ilias.de/go/wiki/wpage_2265_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [lschmidt-tf](https://docu.ilias.de/go/usr/120143)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087), [maalers](https://docu.ilias.de/go/usr/119188), [mhomann-tf](https://docu.ilias.de/go/usr/120211)
* Authority to Curate Test Cases: [wischniak](https://docu.ilias.de/go/usr/21896)
* Authority to (De-)Assign Authorities: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Issues: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Security Reports: [maalers](https://docu.ilias.de/go/usr/119188)

[//]: # (END OrgUnit)


[//]: # (BEGIN Password)

#### [Password](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Password)

*Belongs to:* Password

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Password)


[//]: # (BEGIN PermanentLink)

#### [PermanentLink](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/PermanentLink)

**Status:** Unmaintained / NONE

*Belongs to:* PermanentLink

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END PermanentLink)


[//]: # (BEGIN PersonalWorkspace)

#### [PersonalWorkspace](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/PersonalWorkspace)

*Belongs to:* [Personal and Shared Resources](https://docu.ilias.de/go/wiki/wpage_1338_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END PersonalWorkspace)


[//]: # (BEGIN Poll)

#### [Poll](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Poll)

*Belongs to:* [Poll](https://docu.ilias.de/go/wiki/wpage_2590_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191), [tschmitz](https://docu.ilias.de/go/usr/92591)
* Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Poll)


[//]: # (BEGIN Portfolio)

#### [Portfolio](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Portfolio)

*Belongs to:* [Portfolio](https://docu.ilias.de/go/wiki/wpage_353_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [ezenzen](https://docu.ilias.de/go/usr/42910)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Portfolio)


[//]: # (BEGIN PrivacySecurity)

#### [PrivacySecurity](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/PrivacySecurity)

**Status:** Unmaintained / NONE

*Belongs to:* [Privacy, Terms of Service and Data Protection (incl. Terms of Service)](https://docu.ilias.de/go/wiki/wpage_4995_1357)

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END PrivacySecurity)


[//]: # (BEGIN Rating)

#### [Rating](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Rating)

*Belongs to:* [Rating](https://docu.ilias.de/go/wiki/wpage_2784_1357)

* Authority to Sign off on Conceptual Changes: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [fhelfer](https://docu.ilias.de/go/usr/93367)
* Authority to Curate Test Cases: [fhelfer](https://docu.ilias.de/go/usr/93367), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to (De-)Assign Authorities: [oliver.samoila (Databay AG)](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [fhelfer](https://docu.ilias.de/go/usr/93367)
* Assignee for Security Reports: [fhelfer](https://docu.ilias.de/go/usr/93367)

[//]: # (END Rating)


[//]: # (BEGIN Refinery)

#### [Refinery](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Refinery)

*Belongs to:* Refinery

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)

[//]: # (END Refinery)


[//]: # (BEGIN Registration)

#### [Registration](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Registration)

*Belongs to:* [Login, Auth & Registration](https://docu.ilias.de/go/wiki/wpage_19_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Registration)


[//]: # (BEGIN RemoteCategory)

#### [RemoteCategory](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteCategory)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteCategory)


[//]: # (BEGIN RemoteCourse)

#### [RemoteCourse](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteCourse)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteCourse)


[//]: # (BEGIN RemoteFile)

#### [RemoteFile](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteFile)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteFile)


[//]: # (BEGIN RemoteGlossary)

#### [RemoteGlossary](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteGlossary)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteGlossary)


[//]: # (BEGIN RemoteGroup)

#### [RemoteGroup](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteGroup)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteGroup)


[//]: # (BEGIN RemoteLearningModule)

#### [RemoteLearningModule](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteLearningModule)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteLearningModule)


[//]: # (BEGIN RemoteTest)

#### [RemoteTest](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteTest)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteTest)


[//]: # (BEGIN RemoteWiki)

#### [RemoteWiki](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RemoteWiki)

*Belongs to:* [ECS Interface](https://docu.ilias.de/go/wiki/wpage_1132_1357)

* Authority to Sign off on Conceptual Changes: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Authority to Sign off on Code Changes: [sdyhr](https://docu.ilias.de/go/usr/102107), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [jheim](https://docu.ilias.de/go/usr/40167), [SIG CampusConnect und ECS(A)](https://docu.ilias.de/go/grp/7893)
* Authority to (De-)Assign Authorities: [bogen](https://docu.ilias.de/go/usr/13815), [mglaubitz](https://docu.ilias.de/go/usr/28309)
* Assignee for Issues: [sdyhr](https://docu.ilias.de/go/usr/102107)
* Assignee for Security Reports: [sdyhr](https://docu.ilias.de/go/usr/102107)

[//]: # (END RemoteWiki)


[//]: # (BEGIN Repository)

#### [Repository](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Repository)

*Belongs to:* [Category and Repository](https://docu.ilias.de/go/wiki/wpage_106_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Repository)


[//]: # (BEGIN ResourceStorage)

#### [ResourceStorage](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ResourceStorage)

*Belongs to:* [ILIAS Resource Storage Service](https://docu.ilias.de/go/wiki/wpage_6729_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END ResourceStorage)


[//]: # (BEGIN RootFolder)

#### [RootFolder](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RootFolder)

*Belongs to:* [Category and Repository](https://docu.ilias.de/go/wiki/wpage_106_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149), [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END RootFolder)


[//]: # (BEGIN RTE)

#### [RTE](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/RTE)

**Status:** Unmaintained / NONE

*Belongs to:* RTE

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END RTE)


[//]: # (BEGIN Saml)

#### [Saml](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Saml)

*Belongs to:* [Login, Auth & Registration](https://docu.ilias.de/go/wiki/wpage_19_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Curate Test Cases: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END Saml)


[//]: # (BEGIN Scorm2004)

#### [Scorm2004](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Scorm2004)

*Belongs to:* [Learning Module SCORM](https://docu.ilias.de/go/wiki/wpage_32_1357)

* Authority to Sign off on Conceptual Changes: [wischniak](https://docu.ilias.de/go/usr/21896)
* Authority to Sign off on Code Changes: [qualitus.dahme](https://docu.ilias.de/go/usr/99160), [qualitus.hartwig](https://docu.ilias.de/go/usr/104063)
* Authority to Curate Test Cases: [emix](https://docu.ilias.de/go/usr/57311)
* Authority to (De-)Assign Authorities: [wischniak](https://docu.ilias.de/go/usr/21896)
* Assignee for Issues: [wischniak](https://docu.ilias.de/go/usr/21896)
* Assignee for Security Reports: [wischniak](https://docu.ilias.de/go/usr/21896)

[//]: # (END Scorm2004)


[//]: # (BEGIN ScormAicc)

#### [ScormAicc](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/ScormAicc)

*Belongs to:* [Learning Module SCORM](https://docu.ilias.de/go/wiki/wpage_32_1357)

* Authority to Sign off on Conceptual Changes: [wischniak](https://docu.ilias.de/go/usr/21896)
* Authority to Sign off on Code Changes: [qualitus.dahme](https://docu.ilias.de/go/usr/99160), [qualitus.hartwig](https://docu.ilias.de/go/usr/104063)
* Authority to Curate Test Cases: [emix](https://docu.ilias.de/go/usr/57311)
* Authority to (De-)Assign Authorities: [wischniak](https://docu.ilias.de/go/usr/21896)
* Assignee for Issues: [wischniak](https://docu.ilias.de/go/usr/21896)
* Assignee for Security Reports: [wischniak](https://docu.ilias.de/go/usr/21896)

[//]: # (END ScormAicc)


[//]: # (BEGIN Search)

#### [Search](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Search)

*Belongs to:* [Search](https://docu.ilias.de/go/wiki/wpage_11_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Search)


[//]: # (BEGIN Session)

#### [Session](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Session)

*Belongs to:* [Session (Course & Group)](https://docu.ilias.de/go/wiki/wpage_2172_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [MISSING]
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Session)


[//]: # (BEGIN Setup)

#### [Setup](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Setup)

*Belongs to:* [Setup](https://docu.ilias.de/go/wiki/wpage_40_1357)

* Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [kunkel](https://docu.ilias.de/go/usr/115)
* Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END Setup)



[//]: # (BEGIN Skill)

#### [Skill](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Skill)

*Belongs to:* [Competence Management](https://docu.ilias.de/go/wiki/wpage_1161_1357)

* Authority to Sign off on Conceptual Changes: [cludolf](https://docu.ilias.de/go/usr/97658)
* Authority to Sign off on Code Changes: [cludolf](https://docu.ilias.de/go/usr/97658), [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [atoedt](https://docu.ilias.de/go/usr/3139)
* Authority to (De-)Assign Authorities: [cludolf](https://docu.ilias.de/go/usr/97658)
* Assignee for Issues: [cludolf](https://docu.ilias.de/go/usr/97658)
* Assignee for Security Reports: [cludolf](https://docu.ilias.de/go/usr/97658)

[//]: # (END Skill)


[//]: # (BEGIN soap)

#### [soap](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/soap)

*Belongs to:* [Web Services Overview: SOAP, REST, ...](https://docu.ilias.de/go/wiki/wpage_186_1357)

* Authority to Sign off on Conceptual Changes: [githamo](https://docu.ilias.de/go/usr/115389)
* Authority to Sign off on Code Changes: [githamo](https://docu.ilias.de/go/usr/115389), [sKarki999](https://docu.ilias.de/go/usr/112949)
* Authority to Curate Test Cases: [sKarki999](https://docu.ilias.de/go/usr/112949)
* Authority to (De-)Assign Authorities: [TimoScheuer](https://docu.ilias.de/go/usr/102976)
* Assignee for Issues: [sKarki999](https://docu.ilias.de/go/usr/112949)
* Assignee for Security Reports: [sKarki999](https://docu.ilias.de/go/usr/112949)

[//]: # (END soap)


[//]: # (BEGIN StaticURL)

#### [StaticURL](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/StaticURL)

**Status:** Unmaintained / NONE

*Belongs to:* StaticURL

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END StaticURL)


[//]: # (BEGIN StudyProgramme)

#### [StudyProgramme](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/StudyProgramme)

*Belongs to:* [Study Programme](https://docu.ilias.de/go/wiki/wpage_3391_1357)

* Authority to Sign off on Conceptual Changes: [lschmidt-tf](https://docu.ilias.de/go/usr/120143)
* Authority to Sign off on Code Changes: [maalers](https://docu.ilias.de/go/usr/119188), [mhomann-tf](https://docu.ilias.de/go/usr/120211)
* Authority to Curate Test Cases: [maalers](https://docu.ilias.de/go/usr/119188), [mhomann-tf](https://docu.ilias.de/go/usr/120211)
* Authority to (De-)Assign Authorities: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Issues: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Security Reports: [maalers](https://docu.ilias.de/go/usr/119188)

[//]: # (END StudyProgramme)


[//]: # (BEGIN StudyProgrammeReference)

#### [StudyProgrammeReference](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/StudyProgrammeReference)

*Belongs to:* [Study Programme](https://docu.ilias.de/go/wiki/wpage_3391_1357)

* Authority to Sign off on Conceptual Changes: [lschmidt-tf](https://docu.ilias.de/go/usr/120143)
* Authority to Sign off on Code Changes: [maalers](https://docu.ilias.de/go/usr/119188), [mhomann-tf](https://docu.ilias.de/go/usr/120211)
* Authority to Curate Test Cases: [maalers](https://docu.ilias.de/go/usr/119188), [mhomann-tf](https://docu.ilias.de/go/usr/120211)
* Authority to (De-)Assign Authorities: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Issues: [maalers](https://docu.ilias.de/go/usr/119188)
* Assignee for Security Reports: [maalers](https://docu.ilias.de/go/usr/119188)

[//]: # (END StudyProgrammeReference)


[//]: # (BEGIN Style)

#### [Style](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Style)

*Belongs to:* CSS / Templates

* Authority to Sign off on Conceptual Changes: [BettyFromHH](https://docu.ilias.de/go/usr/96573), [alinaseibt](https://docu.ilias.de/go/usr/70225)
* Authority to Sign off on Code Changes: [BettyFromHH](https://docu.ilias.de/go/usr/96573), [rotegras](https://docu.ilias.de/go/usr/88399), [padvincenzo](https://docu.ilias.de/go/usr/87189)
* Authority to Curate Test Cases: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
* Authority to (De-)Assign Authorities: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
* Assignee for Issues: [BettyFromHH](https://docu.ilias.de/go/usr/96573)
* Assignee for Security Reports: [BettyFromHH](https://docu.ilias.de/go/usr/96573)

[//]: # (END Style)


[//]: # (BEGIN Survey)

#### [Survey](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Survey)

*Belongs to:* [Survey](https://docu.ilias.de/go/wiki/wpage_27_1357)

* Authority to Sign off on Conceptual Changes: [jcopado](https://docu.ilias.de/go/usr/30511)
* Authority to Sign off on Code Changes: [abrahammordev](https://docu.ilias.de/go/usr/110909), [juanma1331](https://docu.ilias.de/go/usr/107249)
* Authority to Curate Test Cases: [jcopado](https://docu.ilias.de/go/usr/30511)
* Authority to (De-)Assign Authorities: [jcopado](https://docu.ilias.de/go/usr/30511)
* Assignee for Issues: [abrahammordev](https://docu.ilias.de/go/usr/110909)
* Assignee for Security Reports: [jcopado](https://docu.ilias.de/go/usr/30511)

[//]: # (END Survey)


[//]: # (BEGIN SurveyQuestionPool)

#### [SurveyQuestionPool](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/SurveyQuestionPool)

*Belongs to:* [Survey](https://docu.ilias.de/go/wiki/wpage_27_1357)

* Authority to Sign off on Conceptual Changes: [jcopado](https://docu.ilias.de/go/usr/30511)
* Authority to Sign off on Code Changes: [abrahammordev](https://docu.ilias.de/go/usr/110909), [juanma1331](https://docu.ilias.de/go/usr/107249)
* Authority to Curate Test Cases: [jcopado](https://docu.ilias.de/go/usr/30511)
* Authority to (De-)Assign Authorities: [jcopado](https://docu.ilias.de/go/usr/30511)
* Assignee for Issues: [abrahammordev](https://docu.ilias.de/go/usr/110909)
* Assignee for Security Reports: [jcopado](https://docu.ilias.de/go/usr/30511)

[//]: # (END SurveyQuestionPool)


[//]: # (BEGIN SystemCheck)

#### [SystemCheck](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/SystemCheck)

*Belongs to:* [System Check](https://docu.ilias.de/go/wiki/wpage_2093_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END SystemCheck)



[//]: # (BEGIN Table)

#### [Table](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Table)

**Status:** Unmaintained / NONE

*Belongs to:* Table

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Table)


[//]: # (BEGIN Tagging)

#### [Tagging](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Tagging)

*Belongs to:* [Tagging](https://docu.ilias.de/go/wiki/wpage_140_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [skaiser](https://docu.ilias.de/go/usr/17260)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Tagging)


[//]: # (BEGIN Tasks)

#### [Tasks](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Tasks)

*Belongs to:* Tasks

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Tasks)


[//]: # (BEGIN Taxonomy)

#### [Taxonomy](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Taxonomy)

*Belongs to:* [Taxonomy Service](https://docu.ilias.de/go/wiki/wpage_2304_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: Tested separately in each module that supports taxonomies
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Taxonomy)


[//]: # (BEGIN TermsOfService)

#### [TermsOfService](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/TermsOfService)

*Belongs to:* [Privacy, Terms of Service and Data Protection (incl. Terms of Service)](https://docu.ilias.de/go/wiki/wpage_4995_1357)

* Authority to Sign off on Conceptual Changes: [mjansen](https://docu.ilias.de/go/usr/8784)
* Authority to Sign off on Code Changes: [mjansen](https://docu.ilias.de/go/usr/8784), [lscharmer](https://docu.ilias.de/go/usr/87863)
* Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
* Authority to (De-)Assign Authorities: [mjansen (Databay AG)](https://docu.ilias.de/go/usr/8784)
* Assignee for Issues: [mjansen](https://docu.ilias.de/go/usr/8784)
* Assignee for Security Reports: [mjansen](https://docu.ilias.de/go/usr/8784)

[//]: # (END TermsOfService)


[//]: # (BEGIN Test)

#### [Test](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Test)

*Belongs to:* [Test & Assessment](https://docu.ilias.de/go/wiki/wpage_26_1357)

* Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474), [dstrassner](https://docu.ilias.de/go/usr/48931), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Assignee for Issues: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Assignee for Security Reports: [dstrassner](https://docu.ilias.de/go/usr/48931)

[//]: # (END Test)


[//]: # (BEGIN TestQuestionPool)

#### [TestQuestionPool](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/TestQuestionPool)

*Belongs to:* [Test & Assessment](https://docu.ilias.de/go/wiki/wpage_26_1357)

* Authority to Sign off on Conceptual Changes: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474), [dstrassner](https://docu.ilias.de/go/usr/48931), [thojou](https://docu.ilias.de/go/usr/103745)
* Authority to Curate Test Cases: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Authority to (De-)Assign Authorities: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Assignee for Issues: [dstrassner](https://docu.ilias.de/go/usr/48931)
* Assignee for Security Reports: [dstrassner](https://docu.ilias.de/go/usr/48931)

[//]: # (END TestQuestionPool)


[//]: # (BEGIN Tracking)

#### [Tracking](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Tracking)

*Belongs to:* [Statistics and Learning Progress](https://docu.ilias.de/go/wiki/wpage_189_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END Tracking)


[//]: # (BEGIN Tree)

#### [Tree](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Tree)

*Belongs to:* Tree

* Authority to Sign off on Conceptual Changes: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
* Authority to Sign off on Code Changes: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
* Authority to Curate Test Cases: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
* Authority to (De-)Assign Authorities: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
* Assignee for Issues: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)
* Assignee for Security Reports: [Fabian Wolf](https://docu.ilias.de/go/usr/29018)

[//]: # (END Tree)


[//]: # (BEGIN UI)

#### [UI](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/UI)

*Belongs to:* UI-Service

* Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Authority to Curate Test Cases: [Fabian](https://docu.ilias.de/go/usr/27631)
* Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/go/usr/81947), [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Assignee for Issues: [oliver.samoila](https://docu.ilias.de/go/usr/26160)
* Assignee for Security Reports: [oliver.samoila](https://docu.ilias.de/go/usr/26160)

[//]: # (END UI)


[//]: # (BEGIN UI_)

#### [UI_](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/UI_)

**Status:** Unmaintained / NONE

*Belongs to:* UI_

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END UI_)


[//]: # (BEGIN UIComponent)

#### [UIComponent](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/UIComponent)

**Status:** Unmaintained / NONE

*Belongs to:* UIComponent

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END UIComponent)


[//]: # (BEGIN UICore)

#### [UICore](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/UICore)

*Belongs to:* UICore

* Authority to Sign off on Conceptual Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Authority to Sign off on Code Changes: [tfuhrer](https://docu.ilias.de/go/usr/81947), [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Authority to (De-)Assign Authorities: [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Assignee for Issues: [tfuhrer](https://docu.ilias.de/go/usr/81947)
* Assignee for Security Reports: [tfuhrer](https://docu.ilias.de/go/usr/81947)

[//]: # (END UICore)


[//]: # (BEGIN User)

#### [User](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/User)

*Belongs to:* [User Service](https://docu.ilias.de/go/wiki/wpage_332_1357)

* Authority to Sign off on Conceptual Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to Sign off on Code Changes: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to Curate Test Cases: [skergomard](https://docu.ilias.de/go/usr/44474)
* Authority to (De-)Assign Authorities: [skergomard](https://docu.ilias.de/go/usr/44474)
* Assignee for Issues: [skergomard](https://docu.ilias.de/go/usr/44474)
* Assignee for Security Reports: [skergomard](https://docu.ilias.de/go/usr/44474)

[//]: # (END User)


[//]: # (BEGIN Utilities)

#### [Utilities](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Utilities)

**Status:** Unmaintained / NONE

*Belongs to:* Utilities

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Utilities)


[//]: # (BEGIN Verification)

#### [Verification](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Verification)

**Status:** Unmaintained / NONE

*Belongs to:* Verification

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Verification)


[//]: # (BEGIN VirusScanner)

#### [VirusScanner](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/VirusScanner)

**Status:** Unmaintained / NONE

*Belongs to:* Virus Scanner

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END VirusScanner)


[//]: # (BEGIN WebAccessChecker)

#### [WebAccessChecker](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/WebAccessChecker)

*Belongs to:* [Security (incl. Web Access Checker)](https://docu.ilias.de/go/wiki/wpage_866_1357)

* Authority to Sign off on Conceptual Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
* Authority to Sign off on Code Changes: [fwolf-ilias](https://docu.ilias.de/go/usr/29018), [ukohnle](https://docu.ilias.de/go/usr/21855)
* Authority to Curate Test Cases: [AUTHOR MISSING](https://docu.ilias.de/go/pg/64423_4793)
* Authority to (De-)Assign Authorities: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
* Assignee for Issues: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)
* Assignee for Security Reports: [fwolf-ilias](https://docu.ilias.de/go/usr/29018)

[//]: # (END WebAccessChecker)


[//]: # (BEGIN WebDAV)

#### [WebDAV](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/WebDAV)

*Belongs to:* [WebDAV](https://docu.ilias.de/go/wiki/wpage_5484_1357)

* Authority to Sign off on Conceptual Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Sign off on Code Changes: [fschmid](https://docu.ilias.de/go/usr/21087)
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Issues: [fschmid](https://docu.ilias.de/go/usr/21087)
* Assignee for Security Reports: [fschmid](https://docu.ilias.de/go/usr/21087)

[//]: # (END WebDAV)


[//]: # (BEGIN WebResource)

#### [WebResource](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/WebResource)

*Belongs to:* [Weblink](https://docu.ilias.de/go/wiki/wpage_1420_1357)

* Authority to Sign off on Conceptual Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Sign off on Code Changes: [smeyer](https://docu.ilias.de/go/usr/191)
* Authority to Curate Test Cases: [nadine.bauser](https://docu.ilias.de/go/usr/34662)
* Authority to (De-)Assign Authorities: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Issues: [smeyer](https://docu.ilias.de/go/usr/191)
* Assignee for Security Reports: [smeyer](https://docu.ilias.de/go/usr/191)

[//]: # (END WebResource)


[//]: # (BEGIN WebServices)

#### [WebServices](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/WebServices)

*Belongs to:* [Web Services Overview: SOAP, REST, ...](https://docu.ilias.de/go/wiki/wpage_186_1357)

* Authority to Sign off on Conceptual Changes: [githamo](https://docu.ilias.de/go/usr/115389)
* Authority to Sign off on Code Changes: [githamo](https://docu.ilias.de/go/usr/115389), [sKarki999](https://docu.ilias.de/go/usr/112949)
* Authority to Curate Test Cases: [sKarki999](https://docu.ilias.de/go/usr/112949)
* Authority to (De-)Assign Authorities: [TimoScheuer](https://docu.ilias.de/go/usr/102976)
* Assignee for Issues: [sKarki999](https://docu.ilias.de/go/usr/112949)
* Assignee for Security Reports: [sKarki999](https://docu.ilias.de/go/usr/112949)

[//]: # (END WebServices)


[//]: # (BEGIN Wiki)

#### [Wiki](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Wiki)

*Belongs to:* [Wiki](https://docu.ilias.de/go/wiki/wpage_34_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: n.n., Uni Köln
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END Wiki)


[//]: # (BEGIN WOPI)

#### [WOPI](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/WOPI)

*Belongs to:* WOPI

* Authority to Sign off on Conceptual Changes: fschmid
* Authority to Sign off on Code Changes: fschmid
* Authority to Curate Test Cases: fschmid
* Authority to (De-)Assign Authorities: fschmid
* Assignee for Issues: fschmid
* Assignee for Security Reports: fschmid

[//]: # (END WOPI)


[//]: # (BEGIN WorkspaceFolder)

#### [WorkspaceFolder](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/WorkspaceFolder)

*Belongs to:* [Personal and Shared Resources](https://docu.ilias.de/go/wiki/wpage_1338_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END WorkspaceFolder)


[//]: # (BEGIN WorkspaceRootFolder)

#### [WorkspaceRootFolder](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/WorkspaceRootFolder)

*Belongs to:* [Personal and Shared Resources](https://docu.ilias.de/go/wiki/wpage_1338_1357)

* Authority to Sign off on Conceptual Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Sign off on Code Changes: [akill](https://docu.ilias.de/go/usr/149)
* Authority to Curate Test Cases: [akill](https://docu.ilias.de/go/usr/149)
* Authority to (De-)Assign Authorities: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Issues: [akill](https://docu.ilias.de/go/usr/149)
* Assignee for Security Reports: [akill](https://docu.ilias.de/go/usr/149)

[//]: # (END WorkspaceRootFolder)


[//]: # (BEGIN Xml)

#### [Xml](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/Xml)

**Status:** Unmaintained / NONE

*Belongs to:* Xml

* Authority to Sign off on Conceptual Changes: NONE
* Authority to Sign off on Code Changes: NONE
* Authority to Curate Test Cases: NONE
* Authority to (De-)Assign Authorities: NONE
* Assignee for Issues: NONE
* Assignee for Security Reports: NONE

[//]: # (END Xml)

