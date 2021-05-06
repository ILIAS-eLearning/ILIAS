# Accessibility Process

The ILIAS community aims to provide a system that is usable for everyone, including
users with special needs. This document clarifies how we want to move our software
towards this goal incrementally, who is involved, which tools the community uses and
how different activities are incorporated in our release cycle.

**Table of Contents**
* [Goals](#goals)
* [Participants](#participants)
* [Tools](#tools)
* [Activities in Release Cycle](#activities)
* [Future Development](#future-development)

<a name="goals"></a>
## Goals

While the general goal is to provide a system that is usable to for everyone, that
goal needs to be elaborated to some more concrete goals to become effective. These
concrete goals are:

* Targets for incremental improvements are identified regularly, be it concrete
areas of the system itself or processes, tools and approaches used to work towards
a better accessibility of ILIAS.
* An [expert group](./expert-groups.md) for UI, UX and accessibility is appointed.
The group is a central point of contact for all questions related to ILIAS' user
interface and accessibility.
* External accessibility experts are consulted regularly to improve the general
competence of the ILIAS community as well as to identify concrete problems or provide
concrete solutions.
* Effective and efficient tools are provided to the community that help to meet the
targets and goals that are set in this document and in the accessibilty guidelines.
* Regular tests of components of ILIAS make sure that there is no regression in
accessibility properties of the system.
* The general knowledge and awareness for accessibility in the ILIAS community is
improved.
* This process regularly evaluated, improved and adapted to new insights and new
situations.


<a name="participants"></a>
## Participants

To achieve these goals, various people need to participate in different roles during
the release cycle. This section should help to clarify the expectations towards these
participants. Roles central to the process are listed first.

* [UI/UX/A11y Expert Group](../development/expert-groups.md)
    * supports maintainers and developers on general and specific questions regarding
      feature requests, issue tickets and implementations
    * works on processes and strategies to improve processes
    * sends members to the SIG Accessibility and takes part in the discussions there
    * works with UI-Coordinators to improve components and processes
    * analyses accessibility reports
    * reviews and improves test suites
    * provides feedback on feature requests before Jour Fixe
    * sends member to take part in Jour Fixe to answers questions on issues and features
    * carries out an annual review of this process 
    * provides a point of contact for inquiries regarding user interface, user experience
      and accessibility
    * is responsible for this document

* [Maintainers and Developers](../development/maintenance.md)
    * develop new components and features in accordance with the accessibility guidelines
    * apply the accessibility checklist to their own projects and review their own code
      with focus on accessibility
    * acquire a sound understanding of accessibility
    * get in contact with the expert group to improve their understanding of accessibility
      issues or our guidelines

* [UI-Coordinators](../development/maintenance-coordinator.md)
    * triage objectively testable accessibility bugs for UI components
    * checks if formal requirements are met and code is at the correct location 
    * acquire a sound understanding of accessibility
    * get in contact with the expert group to improve their understanding of accessibility

* [Community Tester for Accessibility](https://docu.ilias.de/goto_docu_pg_9809_42.html)
    * carries out test cases of the accessibility test suite in the beta phase 
    * uses a simple diagnostic browser extensions like WAVE or Lighthouse to
      automate a part of testing
    * provides feedback on quality and coverage of the accessibility test suite to the
      Test Case Author for Accessibility 

* Test Case Author for Accessibility
    * prepares, organises and writes test cases of the Accessibility Test Suite
      according to rules in KS-entries, FW-entries and objectively testable
      requirements in accessibility guidelines
    * supports Community Tester for Accessibility, improves cases upon feedback
      from community tester or developer
    * analyses Accessibility Assessment Reports. Reviews Test Suite in the light
      of said report and makes changes accordingly
    * has sound understanding of accessibility
    * is recruited and appointed by the UI/UX/A11y expert group
    * works in close coordination with or is member of the UI/UX/A11y expert group

* [Chair SIG Accessibility](https://docu.ilias.de/goto_docu_grp_6949.html)
    * Role requirements are described in [Rules of Procedure](https://docu.ilias.de/goto_docu_cat_3773.html)
      for Special Interest Groups.
    * advocates accessibility to community i.e. ensures reports are prepared and
      presented on DevConfs
    * organizes or initiates or delegates the fundraising

* Accessibility Contractor
    * is contracted to perform audits and run accessibility trainings for developers
      and community members
    * is contracted to advise and consults on major UI Developments
    * Accessibility contractors should be tendered and selected from BITV-Test-
      Prüfverband or similar.

* Managing Director of ILIAS society
    * contracts external Beta Accessibility Assessment with dedicated agency

* Product Manager
    * ensures accessibility is aptly considered in feature requests in Jour Fixe

* [Technical Board](https://docu.ilias.de/goto_docu_grp_5089.html)
    * appoints UI/UX/A11y expert group, makes sure that the group works as intended
      and takes responsibility, if the group does not function as intended
    * is responsible for the general form and direction of the accessibility process

* Community Test Manager
    * coordinates accessibility testing


<a name="tools"></a>
## Tools

To support the work in the various stages of the release cycle the community
uses different tools. This section clarifies the purpose and usage of these tools.

* [Accessibility Guidelines](./accessibility.md)
    * The Accessibility Guidelines inform the development of new features, KS-entries
      etc. and determine potential improvements of existing features, KS-entries etc.
    * The Accessibility Guidelines are based on the WCAG and put these guidelines
      in the context of ILIAS. The guidelines are supplemented with:
        * specific rules in KS entries which interpret guidelines for UI-elements
        * the Accessibility Checklist, that interprets guidelines to make them
          objectively testable wherever possible
    * We accept that there is ambivalence in the rules that are not objectively
      testable. We strive to shrink this ambivalence.
    * Accessibility guidelines are kept in synch with WCAG developments.
    * The Accessibility Guidelines refer to resources on specific accessibility
      issues for all consumers to look up and self-educate.

* [Accessibility Checklist](./accessibility.md#Checklist)
    * The Accessibility Checklist is documented as md-file along with the
      Accessibility Guidelines.
    * Maintainers and Developers use the Accessibility Checklist to assess
      accessibility of their ongoing implementation projects.

* [Feature Wiki](https://docu.ilias.de/goto_docu_wiki_wpage_1_1357.html)
    * The Maintainer has to fill in the section "Accessibility Implications" and
      complete it before the article can be finally decided on the Jour Fixe.
      He may contact the UI/UX/A11y Expert Group for support.
    * The UI/UX/A11y Expert Group reviews Feature Wiki entries at their own discretion
      and provides feedback about potential issues as early as possible in the design
      of new features.
    * The section "Accessibility Implications" is located in the section "User
      Interface Modifications" and contains this text: "If the proposal contains
      potential accessibility issues that are neither covered by existing UI
      components nor clarified by guidelines, please list them here. For every
      potential issue please either propose a solution or write down a short risk
      assessment about potential fallout if there would be no solution for the issue."

* [Testrail](https://testrail.ilias.de)
    * A [test suite dedicated to Accessibility](https://testrail.ilias.de/index.php?/runs/view/566&group_by=cases:section_id&group_order=asc)
      is available.
    * Test cases are well groomed, easy to carry out and understand.
    * Test cases refer to resource on specific accessibility issues for all
      consumers to look up and self-educate.
    * Test Cases are provided that use simple diagnostical browser extensions like
      lighthouse, HTML Validator or Wave browser extension.
    * All KS-Entries are tested to make sure they comply with their stated
      accessibility rules and the Accessibility Guidelines.

* [Issue Tracker](https://mantis.ilias.de)
    * Issues can be reported on automatically testable violations of guidelines.
    * Issues can be reported on violations of accessibility rules listed in a KS
      entry, but also the on the rules themselves if they need improvement.
      The KS entry and implementation are amended via the normal KS process.
    * Issues can be reported against soft / non-objective parts of the Accessibility
      Guidelines. These require discussion and decisions. Accessibility Guidelines
      are to be amended or clarified to become more objective. Tickets of this kind
      are assigned to the UI/UX/A11y expert group.

* [Jour Fixe](https://docu.ilias.de/goto.php?target=wiki_1357_Jour_Fixe_Agendas)
    * Maintainers can put forward their accessibility issues that came up during
      implementation as "Development Issues".
    * The Jour Fix can be contacted for resolving discussions/deciding on issues
      among different stakeholders on accessibility questions.

* [UI Components](../..//src/UI/README.md)
    * The documentation of UI Components encompases a section on accessibility rules.
    * The documentation can be used to lookup of the mechanism of specific parts of
      the UI


<a name="activities"></a>
## Activities in Release Cycle
The goals state above are not achievable in a single step. They need to be tackled
continuously in an iterative process aiming to push closer towards achieving those
goals with each iteration. This section shows how we understand our release cycle
as one iteration in the process of enhancing accessibility in ILIAS. The main
responsibility for following those steps listed in this cycle resides by the
Technical Board supported by the SIG Accessibility and the UI/UX/A11y Expert Group.

### Contracted External Beta Accessibility Assessment - November
* Early in the beta phase a formative Accessibility Assessment is carried out by a
registered auditor.
* Those audits have the main goal of evaluating the accessibility of an aspect of
ILIAS (e.g. new Features, status of a given service module or component or guidelines).
* Those audits provide feedback on the quality of the tested aspect. 
* Certification of any kind or generating lists for issue trackers are NOT
goals of that assessment.
* The Product Manager signs the contract with the registered auditor. The SIG may
help in providing the funding.
* The ILIAS society provides test installation.

### Large Project Identification Jour Fixe / Yearly Project
* During the Large Project Identification Meeting we try to identify large UI heavy
components to make sure accessibility issues are addressed early and continuously.
* Huge UI heavy components may choose to contract external accessibility testing
before merging.

### After-Action Review Accessibility - March
* After the release the UI/UX/A11y Expert Group organizes an After-Action Review.
* The is a yearly meeting organized by UI/UX/A11y expert group and open to all
community members.
* After publishing the stable release, the former season is analysed with respect
to accessibility.
* The UI/UX/A11y expert group draws up a plan for the upcoming season.
* The Main Question is: How well did our current process and guidelines hold water
during this release?
* During this phase the UI/UX/A11y expert group reviews und updates the Accessibility
Guidelines according to possible new guidelines from the WCAG or other major
developments outside the ILIAS Community.
* The meeting serves as trigger for the Test Case Author for Accessibility to adapt
the test suite.
* During this phase the  UI/UX/A11y expert group reviews processes and roles that
manage accessibility.

### Feature Freeze - April
* Features for Accessibility Improvements are to be handed in on time.
* Features for Accessibility Improvements may be derived from the After-Action Review.

### Feature Development 
* Feature must adhere to the Accessibility Guidelines
* Maintainers go through the Accessibility Checklist (Todo, provide Link) during
implementation

### Community Testing - November
* In Beta Phase the new release is specifically tested with regard to accessibility.


<a name="future-development"></a>
## Future Development

There are some developments we expect, or at least hope, to happen sometime soon.
Since these things are not there yet, we list them in this separate section.
Please make sure to understand, that these things are no promisses. Their implementation
heavily relies on resources from the community, where labor is even scarcer and
more important than funding. If you want to see some of these things implemented,
consider offering your resources to the [Technical Board](mailto:tb@lists.ilias.de) for
further advice:

* Development Tools in CI
    * detect issues very early on and highlight the importance of accessibility
      during development.
    * Reports are prepared with Continuous Integration and reported to Jour Fixe.

* Documentation of local Development Tools
    * All developers are encouraged to make use of simple diagnostical browser
      extensions like lighthouse, HTML Validator or Wave browser extension
    * A document as a common entry point to our accessibility tooling for new
      developers and designers exists.
    * IILIAS developers and designers can readily use accessibility tooling on
      their local machines by help of the documentation.

* Forum as Point of Contact
    * Inquiries regarding user interface, user experience and accessibility can
      be made via this forum to the UI/UX/A11y Expert Group.
    * The results to this inquiries are public.
    * The Point of Contact is unified with the already existing UI-clinic.
