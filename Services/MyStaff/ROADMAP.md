# Roadmap


## Short Term

### Remove Legacy UI, pt. 1

The **Actions** dropdown (`ilAdvancedSelectionListGUI`) should be replaced by the corresponding KS element *Standard Dropdown*.

### Remove Asynchronicity for Actions Dropdown

This is related to [Remove Legacy UI, pt. 1](#remove-legacy-ui-pt-1).<br>
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

### Introduce APIs for Staff Service

Currently, the Staff Service has dependencies to other components, especially with regard to database tables. This always bears risks when changes are made in the components.
The dependencies should be dissolved by introducing APIs in the corresponding components themselves, which the Staff Service can consume.
It must be discussed how exactly these interfaces will look like.

## Long Term

### Unit Tests

Unit tests should be introduced for the Staff Service. This requires a general refactoring of the classes to have a good starting point.