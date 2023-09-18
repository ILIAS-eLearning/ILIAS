# MyStaff (aka Staff)

This document describes the general concepts and structures of the Staff Service. 

The Staff Service is primarily used by companies to give superiors a centralized view to have an overview over their employees.
The Staff Service can be used by superiors as a kind of entry point, which gives them information to various things (course memberships, competences, certificates, talks) of their employees.


* [Permissions](#permissions)
* [Staff List](#staff-list)
* [Course Memberships](#course-memberships)
* [Cerificates](#certificates)
* [Competences](#competences)
* [Talks](#talks)


## Permissions

To have access to user-specific data of their employees, superiors need the required permissions to do that.
These permissions have to be set in *Administration > Organisation > Organisational Units > Positions > Actions > Edit > Access Control by Organisation Unit Positions*.
The exactly needed permissions are context-specific and will be mentioned in the next paragraphs.


## Staff List

*Organisation > Staff List*

The "Staff List" view lists all users (employees) over whom the logged-in user (superior) has authority.<br>
Superiors can decide themselves which table columns respectively user information are shown in the table. All standard user data fields, which can be set as "Searchable", are potential columns in this table (see *Administration > Users and Roles > User Management > Settings > Standard Fields*). Custom user data fields are currently not supported.<br>
An **Actions** dropdown is shown per employee. It provides multiple actions.
Depending on the given permissions, superiors can access **Course Memberships**, **Certificates**, **Competences** and **Talks**, filtered only for this one employee.
Additionally, there are more possible actions in the dropdown. Which exact actions are shown, varies widely from the configuration of the ILIAS installation and the personal settings of the employees themselves.
These further actions can be: 
- Send a mail to the employee
- open the profile of the employee
- view the shared personal resources of the employee
- invite the employee to the public chatroom
- start a private conversation with the employee
- add an employee to a group

**Needed permissions:**
- Having a position in an organisational unit, which has at least authority over one other user.


## Course Memberships

*Organisation > Course Memberships*

The "Course Memberships" view lists all course-user-connections for all users (employees) over whom the logged-in user (superior) has authority.<br>
The potential table columns are limited to some basic user information (login, first name, last name, e-mail, organisational units) on the one hand. 
On the other hand, the title of the course, the employee's member status in the course and (possibly) the employee's learning progress in the course are represented in the table.<br>
An **Actions** dropdown is shown per course-user-connection. The first entry in the dropdown shows the title of the course, with which the superior can navigate directly to it. The following entries show all organisational units the employee is member of.
The remaining entries in the dropdown are the user-specific actions, which are already listed in [Staff List](#staff-list).

**Needed permissions (for course memberships):** 
- Course > View Course Memberships

**Needed permissions (for learning progress):** 
- Course > View learning progress of other users (also the permission above is required to have general access to "Course Memberships" view)


## Certificates

*Organisation > Certificates*

The "Certificates" view lists all certificate-user-connections for all users (employees) over whom the logged-in user (superior) has authority.<br>
The potential table columns are limited to some basic user information (login, first name, last name, e-mail, organisational units) on the one hand.
On the other hand, the title of the object (course, test, exercise,...) the certificate is coming from, is shown. The date the certificate was issued, is also displayed in the table.<br>
An **Actions** dropdown is shown per certificate-user-connection. It only has one entry **Download Certificate**, which allows the superior to download the certificate the employee has achieved in the shown object.

**Needed permissions:**
- Course > View certificates of other users
- **and/or** Exercise > View certificates of other users
- **and/or** Test > View certificates of other users


## Competences

*Organisation > Competences*

The "Competences" view lists all competence-user-connections for all users (employees) over whom the logged-in user (superior) has authority.
The potential table columns are limited to some basic user information (login, first name, last name, e-mail, organisational units) on the one hand.
On the other hand, the title of the competence and the level the employee achieved for this competence, are shown.<br>
An **Actions** dropdown is shown per competence-user-connection. The entries in the dropdown are the user-specific actions, which are already listed in [Staff List](#staff-list).

**Needed permissions:**
- Course > View competences of other users
- **and/or** Group > View competences of other users
- **and/or** Survey > View competences of other users
- **and/or** Test > View competences of other users


## Talks

*Organisation > Talks*

see [Talks in Staff](../../Modules/EmployeeTalk/README.md#talks-in-staff)

**Needed permissions:**
- Employee Talk > Read access talk appointments
- **and/or** Employee Talk > Create talk appointments / edit talk appointments that you have created yourself
- **and/or** Employee Talk > Edit Talk appointments