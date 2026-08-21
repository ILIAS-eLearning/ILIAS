# ILIAS Security Policy

## Table of Contents
* [About this Document](#about-this-document)
* [Responsibilities](#responsibilities)
* [Reporting a Security Issue](#reporting-a-security-issue)
* [Addressing a Security Issue](#addressing-a-security-issue)
* [Process for Fixing Security Issues](#process-for-fixing-security-issues)
* [Security Update Notifications](#security-update-notifications)
* [Security Goals](#security-goals)
* [Security Measures](#security-measures)
* [Contributors](#contributors)

## About this Document
[//]: # (BEGIN about)
This document describes the cybersecurity policy for the core of the open source 
learning management system ILIAS. The document provides information on how security 
issues and incidents should be reported and how they are handled by the responsible 
team, the ILIAS Security Group.

This document is not a guideline on how to set up and operate an ILIAS installation 
securely. Such instructions can be found in [docs/configuration/secure.md](../configuration/secure.md).

[//]: # (END about)

## Responsibilities
[//]: # (BEGIN responsibilities)
ILIAS is published as an open source software by the non-commercial organisation
ILIAS open source e-Learning e.V. Under the Cyber Resilience Act (CRA), the association 
regards itself as an open source software steward. The association’s registered office 
is in Cologne, Germany. Accordingly, the relevant CSIRT is the German Federal Office 
for Information Security (BSI) and the market surveillance authority is the 
Bundesnetzagentur with whom the ILIAS association is cooperating.

The ILIAS Security Group is the responsible team for receiving and processing all 
reports about incidents and security issues in ILIAS. E-mail contact is security@ilias.de. 
The ILIAS Security Group is reporting vulnerabilities and incidents to the single reporting 
platform of ENISA.

The ILIAS Release Manager is responsible for packaging and publishing security bugfix releases
in coordination with the Security Group. Once a security release has been published, the
release manager sends a related notification to the admin mailing list.

[//]: # (END responsibilities)

## Reporting a Security Issue
[//]: # (BEGIN reporting)
The following process describes what to do when you have found a security issue in 
ILIAS or observed a related security incident. Please make sure to understand, that 
treating security issues confidentially is required to keep ILIAS installations as 
safe as possible until the issue is fixed.

Please follow the process described in detail below. You will receive an answer
from a member of the ILIAS Security Group about further steps.

Important: **Never file a security issue in the bugtracker of ILIAS!**

1. Write an e-mail to security@ilias.de about your discovery. This e-mail has to 
contain a description of the issue with the scenario in which the problem is triggered 
and a description of its implications. Please let us know if the vulnerability has 
been actively exploited and/or a severe incident has happened. Please provide all 
necessary steps to reproduce the issue. We kindly ask you to withhold full disclosure 
of the issue until a fix is ready and the new release has been built and made available 
to everyone (full disclosure about one week after the new release is published).
2. Your e-mail creates a new ticket in our internal ticket system for security issues.
You will receive an automatic e-mail as confirmation of this.
3. In the next step, the ILIAS Security Group will assign an issue manager.
4. The issue manager will look into the issue and try to reproduce the problem.
5. In accordance with the CRA's guidelines, the issue manager gives an early warning 
about an actively exploited vulnerability and/or severe incident to ENISA's single 
reporting platform within 24 hours of becoming aware of it.
6. In case of questions, the issue manager will contact you on behalf of the ILIAS 
association by email. We are grateful for any further help/information you can 
provide during the analysis and bugfixing process.

Optional: We are very interested in giving proper credit for your finding and
your support for the project. If you want to, we can include your name and/or
institution in our release notes. We will not publish your name or the name of
your institution without your consent.

Please note: If you already have a solution in the form of a bugfix for the 
reported issue, we would highly appreciate to get it. In this case, please follow 
the **Process for Fixing Security Issues** in order to not unnecessarily endanger 
existing ILIAS installations. If you don't have access to the corresponding issue 
in our internal security tracker please give access to the corresponding patch 
files to the issue manager through a secure channel.

[//]: # (END reporting)

## Addressing a Security Issue
[//]: # (BEGIN addressing)
1. As soon as the issue manager has looked at the issue, the manager assigns 
a report to the responsible developer in our internal security issues tracking 
system and notifies this developer about the issue.
2. The developer analyses the problem and provides a security fix as soon as 
possible.
3. Within 72 hours of becoming aware of the vulnerability/incident the Security Group
provides general information and an initial assessment to ENISA.
4. Depending on the severity and impact of the reported and fixed issues, the 
ILIAS release manager will build a new release or continue with the default roadmap.
5. In case of a vulnerability that has been reported to ENISA, the Security Group
provides a final report to ENISA no later than 14 days after the security bugfix 
release has been made available.
6. In case of a reported severe incident, the Security Group will provide a final report
to ENISA within one month after the severe incident notification.

[//]: # (END addressing)


## Process for Fixing Security Issues
[//]: # (BEGIN fixing)
The following process MUST be followed to hand in a fix for a security issue. These 
rules apply to code authorities as well as to all other developers and contributors.
* Create one patch file per supported branch needing the fix. The patch file MUST
be named "<ilias_version>_<title_of_fix>.patch" (e.g. "11_my-very-important-fix.patch").
To create the patch use `git format-patch` with the option `--stdout` from the branch 
in which you made the fixes
(e.g. `git format-patch release_11 --stdout > 11_my-very-important-fix.patch`)
to ensure that all commits are in a single patch file, if multiple commits are
needed to fix the issue. To make the application of the patches as easy as possible,
**fixes for security issues MUST not contain any unrelated changes**.
* Upload the patch files to the corresponding issue in the project ‘ILIAS Security’
on our issue tracker. If you would like to provide a patch for an issue, but do
not have access to the issue on our tracker please send the patch files to
the issue manager on a secure channel.
* Please provide a summary for the release notes and a few sentences describing
the fix either directly in the corresponding sections in the issue tracker or in
the communication.
* The fixes will be applied to the release branches by the release manager before
the release.

[//]: # (END fixing)

### Regressions
[//]: # (BEGIN regressions)

In exceptional cases, security fixes may introduce regressions that negatively impact functionality
or system stability. To address such situations promptly and effectively, the following procedure
shall be followed:

1. Identification
  * Any regression resulting from a security fix must be reported immediately in the issue tracker.
  * All relevant details (affected components, impact, steps to reproduce) must be provided.
  * The assigned component authority shall inform both the Technical Board and the Product Manager without delay.
2. Evaluation
  * The Technical Board and the Product Manager jointly assess the severity and impact of the regression
    and decide if the regression will be fixed.
  * If the regression is to be fixed, a decision will be made on whether an urgent fix (a hotfix) is needed
    or whether the issue can be deferred to the next scheduled security release.
3. Coordination with Release Management
  * If a hotfix is necessary:
    * A corrective patch shall be prepared by the responsible authority who introduced the regression
      and reviewed with priority.
    * The corresponding patch file must be attached to the issue.
    * The Release Manager shall be involved at an early stage to coordinate the release process.
  * If the issue can be deferred:
    * The fix will be included in the next scheduled security release under the supervision of the Release Manager.
4. Communication
  * In case of a hotfix, the Release Manager shall inform stakeholders via the appropriate mailing lists
    and Discord channels.

[//]: # (END regressions)

## Security Update Notifications
[//]: # (BEGIN notifications)

Please subscribe to our admin mailing list (ilias-admins@lists.ilias.de) to get
notifications about security updates, updates in general and announcements for
ILIAS server administrators. As a general rule, ILIAS updates will be published
on the start of a week and will be announced in the middle of the previous week
on the mailing list.

[//]: # (END notifications)

## Security Goals
[//]: # (BEGIN goals)

* All ILIAS security issues should be kept confidential until patches for them are integrated into an official release.
* Security issues must be fixed in ALL currently supported and affected versions.
* ILIAS releases which contain patches for security issues should be released as soon as possible.
* All fixed security issues should be documented publicly.

[//]: # (END goals)

## Security Measures
[//]: # (BEGIN measures)

* All incoming issues (via security@ilias.de) are handled by the Security Group in an internal 
ticket system (hosted by the ILIAS open source e-Learning e.V.). It allows for sending encrypted 
and digitally signed e-mails to security reporters.
* Security issues are entered into a restricted part of the official Mantis platform for ILIAS 
by the Security Group and then assigned to the authority which is responsible for the affected 
component.
* The Release Manager is in direct contact with the Security Group to ensure that releases for 
all affected versions can be made available in a short timespan, ideally on the same day.
* Releases with security fixes are accompanied by an entry in our security blog (at docu.ilias.de)
which provides more details on affected and fixed versions of ILIAS.

[//]: # (END measures)

## Contributors
[//]: # (BEGIN contributors)

* Robin Baumgartner, sr solutions ag, Burgdorf, Switzerland
* Tim Bongers, CaT Concepts and Training GmbH, Cologne, Germany
* Alex Hartwig, Qualitus GmbH, Cologne, Germany
* Matthias Kunkel, ILIAS open source e-Learning e.V., Cologne, Germany
* André Schweigert, FAU Kompetenzzentrum Lehre, Fürth, Germany
* Lukas Scharmer, Databay AG, Würselen, Germany
* David Tokar, WEKA Media GmbH & Co. KG, Kissing, Germany
* Guido Vollbach, Databay AG, Würselen, Germany

[//]: # (END contributors)
