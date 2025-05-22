#@ ILIAS Security Group

## Table of Contents
* [Reporting Security Issues](#reporting-security-issues)
* [Publishing Fixes for Security Issues](#publishing-fixes-for-security-issues)
* [Security Update Notifications](#security-update-notifications)
* [Contributors](#contributors)

## Reporting Security Issues
[//]: # (BEGIN Reporting)
Please make sure to understand, that treating security issues confidentially is
required to keep ILIAS installations as safe as possible until the issue is fixed.

Please follow the process described in detail below. You will receive an answer
from a member of the ILIAS security group about further steps.

**Do not file an issue in the bugtracker!**

1. Write an email to security@ilias.de about your discovery, containing a
description of the issue with the scenario in which the problem is triggered and
a description of its implications. Please provide all necessary steps to reproduce
the issue. We kindly ask you to withhold full disclosure of the issue until a fix
is ready and the new release has been build and made available to everyone
(full disclosure about 1 week after the new release is published).
2. The Security Group will assign an issue manager.
3. The issue manager will look into the issue and try and reproduce the problem.
4. The issue manager will contact you on behalf of the ILIAS e.V. by email.
We are grateful for any further help/information you can provide during the
analysis and bugfixing process.
5. Depending on the severity and impact of the issue at hand, the developers will
build a new release ASAP or continue with the default roadmap.
6. Optional: We are very interested in giving proper credit for your finding and
your support for the project. If you want to, we can include your name and/or
institution in our release notes. We will not publish your name or the name of
your institution without your consent.

[//]: # (END Reporting)

## Publishing Fixes for Security Issues
[//]: # (BEGIN Publishing)
We are delighted when solutions are offered together with the initial report.
Please follow the **Process for Fixing Security Issues** in order to not
unnecessarily endanger existing ILIAS installations. If you don't have access
to the corresponding issue in our internal security tracker please give access
to the corresponding patch files to the issue manager through a secure channel.

[//]: # (END Publishing)

## Process for Fixing Security Issues
[//]: # (BEGIN Fixing)
The following process MUST be followed to hand in a fix for a security issue:
* Create one patch file per supported branch needing the fix. The patch file MUST
be named "<ilias_version>_<title_of_fix>.patch" (e.g. "9_my-very-important-fix.patch").
To create the patch use `git format-patch` with the option `--stdout` from the branch in which you made the fixes
(e.g. `git format-patch release_9 --stdout > 9_my-very-important-fix.patch`)
to ensure that all commits are in a single patch file, if multiple commits are
needed to fix the issue. To make the application of the patches as easy as possible,
**fixes for security issues MUST not contain any unrelated changes**.
* Upload the patch files to the corresponding issue in the project "ILIAS Security"
on our issue tracker. If you would like to provide a patch for an issue, but do
not have access to the issue on our tracker please send the patch files to
the issue manager on a secure channel.
* Please provide a summary for the release notes and a few sentences describing
the fix either directly in the corresponding sections in the issue tracker or in
the communication.
* The fixes will be applied to the release branches by the release manager before
the release.

[//]: # (END Fixing)

## Security Update Notifications
[//]: # (BEGIN Notifications)

Please subscribe to our admin mailing list (ilias-admins@lists.ilias.de) to get
notifications about security updates, updates in general and announcements for
ILIAS server administrators. As a general rule ILIAS updates will be published
on the start of a week and will be announced in the middle of the previous week
on the mailing list.

[//]: # (END Notifications)

## Contributors
[//]: # (BEGIN Contributors)

* Robin Baumgartner, sr solutions ag, Burgdorf, Switzerland
* Tim Bongers, CaT Concepts and Training GmbH, Cologne, Germany
* Rob Falkenstein, University of Freiburg - IT Services, Germany
* Alex Hartwig, Qualitus GmbH, Cologne, Germany
* David Tokar, WEKA Media GmbH & Co. KG, Kissing, Germany

[//]: # (END Contributors)
