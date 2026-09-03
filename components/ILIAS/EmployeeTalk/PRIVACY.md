# EmployeeTalk Privacy

**Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing
or incorrect information via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).**

## Integrated Components

The **EmployeeTalk** component integrates various ILIAS components. Please consult the respective privacy documentation:

- [OrgUnit](../OrgUnit/PRIVACY.md) provides information about accounts' assignment to **organisational units**,
  what accounts they have authority over, and which permissions this authority grants them via 
  the **position access** mechanism.
- [AdvancedMetadata](../AdvancedMetaData/PRIVACY.md) provides **custom metadata sets** that can be
  attached to talk templates, and filled out for talks.
- **Calendar** is used to add talks as appointments to personal calendars.
- [Mail](../Mail/PRIVACY.md) is used to send notifications about talks.
- **User** handles **account identification**, provides names, logins, and email addresses from the **personal profile data** of accounts,
  as well as their **personal preferences**, and is used to register a 'User action'.
- **ILIASObject**, [Container](../Container/PRIVACY.md), and **Tree** are used
  to handle the hierarchy of template administration, templates, talk series,
  and talks, as well as their owners, titles, and descriptions.
- [AccessControl](../AccessControl/PRIVACY.md) is used to check role-based permissions.
- [Staff](../MyStaff/PRIVACY.md) is used to check access via **OrgUnit** positions to the talk views within
  the 'Organisation' main menu entry.
- [InfoScreen](../InfoScreen/PRIVACY.md) to offer an 'Info'-tab in templates,
  the template administration, and in talks.

## General Information

Talks carry a 'location' field (handled by **EmployeeTalk** itself),
a title and description (handled by **ILIASObject**),
as well as further fields from attached **Custom Metadata**
sets (if configured). The latter are intended to be used as minutes for
the talks. All of these fields are filled out manually, but are likely
to contain personal data of at least the employee involved in the talk.

Note that talks share their title with the series they are a part of.
The title is stored redundantly, again by **ILIASObject**.

### Access to Talks

Access in **EmployeeTalk** depends mostly on the position access configuration
of **OrgUnit**, and its concept of authority. The corresponding permissions in the
**OrgUnit** position administration are 'Read access talk appointments',
'Create talk appointments / edit talk appointments that you have created yourself',
and 'Edit Talk appointments', and relate to access to talks as follows:

- **Create:** The current account has the 'Create talk appointments / edit talk appointments that you have created yourself'
  permission over the employee. Access to the creation dialogue is granted if
  the account has any position with that permission. 
- **Read:** Does not affect whether a talk is shown in the [talk list](#talk-list).
  The current account is either superior or employee of the talk, or they have the
  'Read access talk appointments' permission over the employee.
- **Edit:** The current account is either superior of the talk, or they have the
  'Edit Talk appointments' permission over the employee. If the setting
  'Lock the editing of all appointments in this series' is enabled in
  the series, only the superior can edit.
- **Delete:** The current account is superior of the talk and has read-access to the talk template
  administration via RBAC (AccessControl), or they have the global 'Administrator' role.

Note that the root account can always access every talk, and can always create
a talk. If position access for 'Employee Talks' is deactivated, only the root
account can access and create talks.

## Data being stored

**EmployeeTalk** itself stores the following data for each talk:

- **Superior** and **Employee** of the talk, by their user ID.
- **Start Date** and **End Date** of the talk, including whether it is an all day event.
- **Location** of the talk.
- Whether the talk has been **completed** already.

## Data being presented

### Talk List

A list of talks is offered under the same main menu entry as the various
views of the **Staff** component, and is available
under similar conditions: 'Enable Main Menu Entry' must be
enabled in the **OrgUnit** settings, and the current account must have
at least one of the 'Employee Talk' position access permissions over at least one account under their authority.

The list contains all talks where the current account, or an account they
have the 'Read access talk appointments' permission over, is superior
or employee. The following data is shown for every talk:

- **Title** of the talk.
- **Superior** and **Employee** of the talk, by their login. If the corresponding
  user ID doesn't exist, for example because the account was deleted, 'Unknown User'
  is shown instead 
- **Start Date** and **End Date** of the talk, with time if applicable.
- Completion **Status** of the talk.

Aside from the main talk list, an account-specific talk list is
offered. It is identical to the main list (in content and conditions of
access), except that it only contains talks where the selected
account is employee. It is only available under the additional condition
that the current user has authority over the selected user.

From the talk lists, talks can be created if the current account
has [create access](#access-to-talks). There, an autocomplete
is offered when typing in the employee. Suggestions are only shown
after at least three letters are already entered, and only
accounts which the current account can [create talks for](#access-to-talks)
are shown.

### Talk

In the talks themselves, in addition what is included in the
[talk list](#talk-list), the following data is shown:

- **Title** and **Description** of the talk.
- **Location** of the talk.
- The attached **Custom Metadata** fields.
- The **Info**-tab of the talk. There the superior of the talk
  is listed as its **owner**, but only to accounts with the global
  'Administrator' role (and the superior themselves). They are identified via their **login**.
  If their personal profile is published via the User component,
  their **first name**, **last name**, and a **link to their profile** are
  also shown.

This data is available to accounts with [read or edit access](#access-to-talks)
to the talk. Accounts with the latter can also edit these fields
(except for **Superior** and **Employee**, which can't be changed at all).

Additionally, editing the talk allows enabling the setting
'Lock the editing of all appointments in this series', with which
one can prevent editing of talks in the same series by anyone
but the superior themselves.

### Calendar Appointments

The employee and superior of a talk have the talk as an appointment
in their personal calendar, only accessible by them, with the following data:

- **Superior** and **Employee** of the talk, by first and last name.
  If the account's profile is published via the **User** component,
  their personal profile is linked.
- **Date and Time of the last change** of the talk's date and time.

Additionally, the [talk](#talk) itself is linked.

### Notifications

When a talk (series) is created, deleted, or its settings or dates changed,
a system notification is sent to the employee, with the superior in CC.
This notification includes the following data:

- **Title** and **Description** of the talks.
- **Location** of the talks.
- **Superior** of the talks by full name, including title if available,
  as given by the **User** component.
- **Start Date** and **End Date** of the talks, with time if applicable.

The notification also includes a link to the first [talk](#talk) in the series. Attached
to the notification is an ics-file, which includes in addition to the above:

- **Employee** of the talks by full name, including title if available,
  as given by the **User** component.

With the ics-file, the talk series can be imported as events into external
calendar applications.

## Data being deleted

Talks can be deleted via the [talk list](#talk-list) (both main
and account-specific), by accounts with [delete access](#access-to-talks).

Talks circumvent the trash, they are always removed permanently
from the system. When a talk is deleted, the [data stored](#data-being-stored)
by the component itself is deleted, as well as the corresponding
data handled by **ILIASObject**, and the appointments in personal
calendars.

When the last talk in a series is deleted, the series is deleted 
along with it.

Note that deletion of an account does not trigger the deletion of
talks with that account as superior or employee. Their user ID
is stored with those talks until the talks are deleted.

## Data being exported

[Notifications](#notifications) about talks include ics-files as
attachments, see above for details.
