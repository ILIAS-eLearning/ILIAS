# EmployeeTalk

_Talks_ are used to organise one-on-one meetings between ILIAS users. Superiors
can schedule _Talks_ with their employees (with those _Positions_ being derived
from _Organisational Units_) by creating them from previously configured
_Talk Templates_.<br>
Employees recieve _Notifications_ about talk appointments,
and can keep track of them via the _Calendar_. The superior can keep minutes
of the appointments with the use of _Custom Metadata_.

* [Talk Template Administration](#talk-template-administration)
  * [Access to Administration](#access-to-administration)
* [Talk Templates](#talk-templates)
  * [Metadata](#metadata)
* [Talks](#talks)
  * [Editing Talks](#editing-talks)
  * [Talks in Staff](#talks-in-staff)
  * [Calendar Appointments](#calendar-appointments)
  * [Notifications](#notifications)
  * [Access to Talks](#access-to-talks)


## Talk Template Administration

To activate _Talks_, open the settings of _Organisational Units_. Activate
**Positions in Employee Talk** and **Enable ‘Staff’**, and make sure the
_Positions_ have the relevant permissions (see 
[Access to Administration](#access-to-administration) and
[Access to Talks](#access-to-talks)).<br>
The _Talk Templates_ available to _Superiors_ can be managed in the
_Talk Template_ administration, found under **Administration** > 
**Organisation** > **Talk Templates**. There a list of all templates is
shown, and the option to add new templates is given.<br>
Templates can also be deleted via the dropdowns in the list. Deleting
templates does not delete the _Talks_ derived from them. 

### Access to Administration

Access to the _Talk Template_ administration and its functions is restricted
using _Role_-specific permissions. Currently, only users with the _Administrator_
role have access.

## Talk Templates

_Talk Templates_ can be created in the _Talk Template_ administration. They
consist of a **Title** and **Description**, and can be toggled **Online**. Further,
previously set up _Metadata_ sets can be activated individually for the
template. All of these options can be configured in the **Settings** tab
of _Talk Templates_, and their info tab shows all selected **Metadata** fields
in a disabled form.

### Metadata

To be able to activate _Custom Metadata_ sets, they first need to be created
in the _Metadata_ administration, and fields to be used for minuting the 
_Talks_ should be added to the sets. Lastly, the sets need to be set to
**Data set needs activation** for _Talk Templates_.<br>
Changing the sets back to **Data set not used** only affects _Talk Templates_
but not already created _Talks_, but changing their fields also changes the 
fields available in the _Talks_.

## Talks

_Talks_ can be created in **Organisation** > **Talks**. To do so, first a _Talk
Template_ has to be chosen, from which the _Talks_ inherit their assigned
_Custom Metadata_. _Talk Templates_ which are not set **Online** are not
available. Then one can set **Title**, **Description** and **Location**
for the _Talks_, their **Date** and/or **Time** as well as a **Recurrence**,
should one want to create _Talks_ in a series, and an **Employee**. The
field **Employee** autofills from all available users.<br>
In the **Settings** tab of the created _Talks_ one can find and edit the
aforementioned fields, as well as **Completed** and **Lock editing for 
others** checkboxes. Further, two buttons are offered to change the date of
the individual _Talk_, and to change the dates of all _Talks_ in the same 
series.<br>
The users involved in the _Talk_ are shown in the **Settings** tab - the 
**Employee** set during creation, and as the **Superior** the user that
created the _Talk_ - but can not be changed.<br>
The fields of the assigned _Metadata_ sets can be edited in the
**Content** tab.

### Editing Talks

* When editing a _Talk_, changing the **Title** and **Lock editing for others**
also applies those changes to all other talks in the same series. All other
options are specific to the individual _Talk_, including the _Metadata_.
* When **Lock editing for others** is checked, only the user listed as 
**Superior** is allowed to edit the talk.
* When changing the dates of a series of _Talks_, all _Talks_ but the one
the change was initiated from are deleted, and new _Talks_ are created
according to the chosen **Recurrence**. This process explicitely excludes 
_Talks_ for which **Completed** is checked, and _Talks_ where the individual
date was changed previously.
* When a user is not allowed to edit a talk but can still access its **Content**
and **Settings** tabs, the buttons to edit dates disappear, and all fields in the forms
of both tabs are disabled, though their values remain visible. This includes the
_Metadata_.

### Talks in Staff

A list of all talks the user is allowed to see can be found in **Organisation** >
**Talks**. This is primarily intended to be used by **Superiors** to manage,
edit and create _Talks_: A **Add new Talk** button is offered, and by clicking
on the titles of _Talks_ or via their **Action** dropdown one can access their
**Content** and **Settings** tab, respectively. The latter option is either
titled **Edit** or **View**, depending on the permissions of the user. The
**Action** dropdown also offers the option to **Delete** individual _Talks_.<br>
Further, a similar list of _Talks_ limited to individual **Employees**
is available. It can be reached either by clicking on the user or on **Talks**
in the user's **Actions** dropdown in **Organisation** > **Staff List**,
as long as one has permission to view the user's _Talks_.
The same **Talks** option in the **Actions** dropdown of users is also
offered in other contexts, e.g. **Communication** > **Contacts** > **Gallery**
or in the **Members Gallery** of _Courses_ and _Groups_. Note that the option
can be disabled in the **Administration**.<br>
In the lists of _Talks_, it is not explicitely denoted which _Talks_ are
in a series, except for their shared title.

### Calendar Appointments

_Talks_ where the user is either **Employee** and **Superior** can be found
as appointments in their _Calendar_. For **Employees**, this is intended to
be the only way to access their _Talks_. Appointments list the **Title**
of the _Talk_, the participating **Superior** and **Employee**, as well as
the date and time of the last update of the appointment. Further, a button
linking to the _Talk_ is offered.<br>
The calendar can be filtered for _Talks_ in a specific series.

### Notifications

Any time _Talks_ are created, edited, or deleted, a _Notification_ is sent
out to the **Employee** with the **Superior** in CC, informing them about
the change. It contains the **Title** of the _Talk(s)_ and a permanent link to
the first one, its **Description** and **Location**, the name of the **Superior**,
and a list of the dates of the affected talks. Note that the list does not
contain unchanged _Talks_, even if they are in the same series.<br>
The _Notification_ further has attached an `.ics` file such that the _Talk_
can be imported as an appointment in external calendars.

### Access to Talks

Access to _Talks_ and related function is restricted using _Position_-specific
permissions as given by **Organisational Units**:

* **Read:** User can see those _Talks_ in lists and view their content, over
whose **Superior** and **Employee** they have authority. 
* **Create:** User can create _Talks_ with users as **Employee**
over which they have authority.
* **Edit:** User can edit _Talks_ in which they have authority over the
**Employee**, except when **Lock editing for others** is checked.

These permission are overwritten in some cases: users always have **Read** access
to _Talks_ in which they are **Superior** or **Employee**, and can always
edit _Talks_ in which they are **Superior**, but they can never edit _Talks_
in which they are **Employee**. A _Talk_ can only be deleted by the
**Superior**, or an _Administrator_.<br>
To have access to **Organisation** and **Organisation** > **Talks**, a user
needs to have at least one of the three permissions above.