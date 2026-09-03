# Staff Privacy

**Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing
or incorrect information via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).**

## Integrated Components

The **Staff** component aggregates data from various ILIAS components. Please consult the respective privacy documentation:

- [OrgUnit](../OrgUnit/PRIVACY.md) provides information about accounts' assignment to **organisational units**,
  what accounts they have authority over, and which data this authority grants access to via 
  the **position access** mechanism. 
- [Skill](../Skill/PRIVACY.md) provides information about **achieved skill levels**, and a pre-built UI
  showing that information for a given account.
- **User** handles **account identification**, provides **personal profile data** and whether a profile
  is **published**, and provides a list of 'User Actions'.
- **Tree** is used together with **ILIASObject** to check the hierarchy of **organisational units**.
- **ILIASObject** is also used to retrieve **titles** of courses.
- [AccessControl](../AccessControl/PRIVACY.md) is used to check permissions to decide whether objects can be linked to.
- **Tracking** provides information about the **learning progress status** of accounts in courses.
- [Course](../Course/PRIVACY.md) together with **Membership** provides information about **enrolment status 
  in courses** of accounts.
- [Certificate](../Certificate/PRIVACY.md) provides information about **awarded certificates**,
  and a pre-built UI showing that information for a given account.

## General Information

What data is shown in **Staff** depends heavily on the position access configuration
of **OrgUnit**.

The component provides a number of reporting views to give accounts
an overview over the status of everyone they have authority over
(as defined via organisational units). This can include personal profile
data, enrolment and learning progress status in courses,
achieved skill levels, and awarded certificates.

Note that a view by [EmployeeTalk](../EmployeeTalk/PRIVACY.md) is offered in the same main
menu entry, see that component for the related privacy information.

## Data being stored

The component does not store any data itself, it only aggregates
data stored elsewhere.

## Data being presented

The component offers different views that show different data
of all accounts that the current account has authority over via the
organisational units.

Additionally, views that report individually on single accounts
under the authority of the current account are also available,
see [below](#account-specific-views).

All views are only available if 'Enable Main Menu Entry' is
enabled in the **OrgUnit** settings, and if the current account
has authority over at least one account.

### Staff List

The Staff List presents the following **personal profile** data of
all relevant accounts:

- **Profile Picture**
- **Login**
- All other **personal profile fields** of type 'Default'
  and set to 'Searchable' in the **User** profile administration.

Further, a selection of 'User Actions' is available,
which exposes the accounts' **user ID**.

### Course Memberships

'Course Memberships' is only available if the current account has the
'Manage Members' permission in courses over at least one account under
their authority, as granted by position access in the **OrgUnit**
administration. This permission can be set as a default per position,
but can also be overwritten locally in individual courses.

The view shows a table of those **course enrolments** of accounts where
the current account has the 'Manage Members' permission over that
account in that course. Included is the following data:

- **Title** of the course.
- **Login** of the account.
- **First Name**, **Last Name**, **E-Mail**, and **Organisational Units**
  of the account, only if the corresponding **personal profile field** is
  set to 'Searchable' via the **User** component.
- **Member Status** of the account in the course: 'Registered', 'Waiting List',
  'Requested'
- **Learning Progress** status of the account in the course. Only shown if
  learning progress is active on the installation, and the
  current account has the 'View learning progress of other users'
  position permission over the account in the course.

### Certificates

'Certificates' is only available if certificates are activated
on the installation. Also, the current account must have the
'View certificates of other users' permission in courses, exercises,
or tests over at least one account under their authority, as granted
by position access in the **OrgUnit** administration. This permission
can be set as a default per position, but can also be overwritten locally in
individual courses, exercises, and tests.

The view presents data related to certificates achieved by accounts in
courses, exercises, or tests. For a certificate to appear,
the current account must have the 'View certificates of other users'
permission over that account in that object.

A table of all such **certificates** of those accounts is shown,
with the following data:

- **Title** of the object in which the certificate was awarded.
- **Issued On:** Date on which the certificate was awarded.
- **Login** of the account to which the certificate was awarded.
- **First Name**, **Last Name**, **E-Mail**, and **Organisational Units**
  of the account, only if the corresponding **personal profile field** is
  set to 'Searchable' via the **User** component.

### Competences

'Competences' is only available if competence management is
activated on the installation. Also, the current account must have the
'View competences of other users' permission in courses, groups, surveys,
or tests over at least one account under their authority, as granted
by position access in the **OrgUnit** administration. This permission
can be set as a default per position, but can also be overwritten locally in
individual courses, groups, surveys, and tests.

It presents data related to competences achieved by accounts
in courses, groups, surveys, or tests. For a competence to appear,
the current account must have the 'View competences of other users'
permission over that account in that object.

A table of all such **certificates** of those accounts is shown,
with the following data:

- **Competence:** Title of the competence.
- **Competence Level:** Title of the achieved level in the competence.
- **Login** of the account.
- **First Name**, **Last Name**, **E-Mail**, and **Organisational Units**
  of the account, only if the corresponding **personal profile field** is
  set to 'Searchable' via the **User** component.

### Account-Specific Views

All account-specific views always show the **profile picture** and the **login**
of the selected account. They also show their **first name** and
**last name**, if the account's profile is published.

The following account-specific views are offered:

- **Course Memberships:** Same as the [Course Memberships](#course-memberships) overview,
  but without the fields **Login**, **First Name**, **Last Name**,
  **E-Mail**, and **Organisational Units**. Available under the same conditions.
- **Certificates:** All certificates of the account, regardless of position
  access, as presented to the user by the **Certificate** component
  via 'Achievements > Certificates'. Available under the same conditions as the
  [Certificates](#certificates) overview.
- **Competences:** All competences of the account, regardless of position
  access, as presented to the user by the **Skill** component 
  via 'Achievements > Competences > Selected Competences'.
  Available under the same conditions as the [Competences](#competences) overview.
- **Profile:** The profile of the account supplied by the **User** component. Only
  available if the account's profile is published.

Note that a view by [EmployeeTalk](../EmployeeTalk/PRIVACY.md) is also
offered here, see that component for the related privacy information.

## Data being deleted

The component does not store any data itself. Its behaviour when
deleting e.g. accounts or courses depends on the behaviour of the
related integrated component.

## Data being exported

Many of the tables offered in the component can be exported
in Excel or CSV format. The data being exported matches the 
data shown in the UI, see [above](#data-being-presented).

The following views contain exportable tables:

- Staff List
- Course Membership (both the overview, and the account-specific view)
- Certificates (overview only)
- Competences (overview only)

Additionally, in the 'Certificates' views (account-specific and
overview) the certificates awarded to accounts can be downloaded.
