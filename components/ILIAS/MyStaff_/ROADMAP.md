# Roadmap


## Short Term

### Remove Legacy UI, **Actions** Dropdown

The **Actions** dropdown (`ilAdvancedSelectionListGUI`), used in the different Staff views in the table, should be replaced by the corresponding KS element `Standard Dropdown`.

### Remove Legacy UI, Filter

The filters, used in the different Staff views in the toolbar, should be replaced by the corresponding KS element `Filter`.

### Replace UI element, User Avatars

The avatars of the users in the "Staff List" view, which are currently the KS element `Standard Image`, should be replaced by the KS element `Picture Avatar`.

### Remove Asynchronicity for Actions Dropdown

This is related to [Remove Legacy UI, **Actions** Dropdown](#remove-legacy-ui-actions-dropdown).<br>
The asynchronous **Actions** dropdown in the different Staff views was introduced to improve the performace, but it also brings along problems, e.g. when the entries within the dropdown also use Javascript.
The asynchronicity should be abolished and the performance should be improved in general, see [Refactoring of `ilMyStaffAccess`](#refactoring-of-ilmystaffaccess).

### Filter Field "Organisational Units"

In the different Staff views, the filter field "Organisational Units" is provided. It filters the table by entries which belong to the selected organisational unit and *only* to this one.
But organisational units can have a hierachy with sub-units. One can assume that entries of sub-units will also be shown in the table when one of their upper-units is selected in the filter field.
It should be discussed in a workshop which of these two behaviours is the correct one and should be established through the whole Staff Service.

### Consistency for the **Actions** Dropdown

The **Actions** dropdown is not consistent in the different Staff views. It shows either table-specific actions mixed with user-specific actions (Course Memberships), or only table-specific actions (Certificates, Talks) or only user-specific actions (Competences).
Futurely, it should be consitent, e.g. only show table-specific actions in all views.

### Move Entries from **Actions** Dropdown in **Course Memberships** View to Table Itself

This is related to [Consistency for the **Actions** Dropdown](#consistency-for-the-actions-dropdown).<br>
In the "Course Memberships" view, superiors can navigate to the employee's courses and organisational units using the **Actions** dropdown.
This is not really intuitive and the dropdown can theoretically have infinite entries, because it shows all organisational units the employee is member of.
A better approach could be to link the titles of the course and the organisational units directly in the table rows.

### Quoting in SQL Queries

In some sql queries, the `quote()` method is not used. This should be catched up.

## Mid Term

### Refactoring of `ilMyStaffAccess`

The `ilMyStaffAccess` class brings along performance issues. A refactoring of this class should reduce them. The used temporary sql tables should be replaced.
At the same time, it should be defined to which limit of organisational units (and also objects, employees,...) the Staff Service can ensure a relatively performant behaviour.

### Introduce Separate User Action Configuration for Staff

Currently, the Staff Service reuses the User Action Configuration from the Who-is-online-Tool.
A separate user action configuration for Staff itself should be introduced (with its own `ilUserActionContext`).
A new Mainbar entry *Administration > Organisation > Staff* may be necessary.

### Decoupling Staff Service

The Staff Service is used as a kind of "aggregator" to collect data from other services (Memberships/Courses, Competences, Certificates, Organisational Units) and deliver the information in a central view.
This idea should remain, but dependencies between Staff and those other components should be reduced and clarified:<br>
* Components could provide interfaces the Staff Service can use.
* The Staff Service could provide the aggregated data to other components and those components could prepare and present the data themselves.
Staff can still remain as a entry point to these views in the Mainbar.
* Components can directly work with the User Action provider and the Mainbar provider, cutting out the Staff Service entirely.
This does not necessarily have to lead to visual changes in the UI, one can just shift technical responsibility from one component to the other.
The Staff Service would be left in charge of the "Staff List" view and the configuration of the User Actions.

## Long Term

### Unit Tests

Unit tests should be introduced for the Staff Service. This requires a general refactoring of the classes to have a good starting point.
