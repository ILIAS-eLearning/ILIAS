# Roadmap

## Short Term

### Deletion of Talks

Currently, the deletion of talks bypasses the Trash manually. This
process should be brought more in line with the other ILIAS objects.
The simplest option would be to just allow Talks to be in the Trash.

### Legacy UI, Part II

The legacy table used for the 'Talks' list needs to be replaced by
a KS component. It should be investigated how to translate those view
to the KS, and take the opportunity to rework them. This effort should
be coordinated with Staff.

### Testcases

The testcases for Employee Talks on Testrail will be moved out of Staff
and into their own category. Information on initial setup and preconditions
should be made more precise.

### Removal of Unused Code

It will be investigated whether code/classes are unused and can be
deleted. From a first quick inspection, there seem to be some candidates
(see e.g. ilObjEmployeeTalkSeriesListGUI, copy and paste OrgUnit code,
etc.).

### Interface to OrgUnits

EmployeeTalk contains logic specific to OrgUnits (see 
ilObjEmployeeTalkAccess) which should be minimized by making use of
the OrgUnits interface more cleanly. It might be necessary to rename the
EmployeeTalk context in OrgUnits to make that work (since context is in
many places tied to the ref_id of objects, and one does not always have a
talk handy when one needs to check position access).<br>
Similarly, use of ilMyStaffAccess should be minimized.

## Mid Term

### Refactor Notification Service

Any changes to the content of notifications currently necessitates making
nearly identical modifications in four different places in the code. There
already is a centralized notification service, but it could be improved by
making its interface less concrete.

### Properly Implement RBAC Permissions for the Administration

Currently, effectively only administrators can access talk templates and
their administration view: even though the permissions needed look like
they can be configured for other roles, specifically read access does
not work at all (no templates are shown). This should be addressed by also
introducing permissions for talk templates, and removing the 'edit settings'
permission from the talk template administration. This would have the 
additional advantage that one could administer talk templates much more
granually.

### Interaction between Talks and Talk Series

There are a few occasions where information has to be propagated from talk
series to talks (and the other way around): this happens when changing the
title or changing the setting 'Lock editing for others', and when completing
the form in 'Change date of talk series'. This is implemented in different
ways, and in different places in the code, sometimes a bit hidden
(ilObjEmployeeTalkGUI::updateCustom and ilObjEmployeeTalkAppointmentGUI).
A centralized service fulfilling this function should be introduced, making
the flow of information more apparent in the structure of the code.

### Editing of Talks/Talk Series

Several aspects of the process of editing talks could be improved or
presented better:
- It could be explained better which settings are specific to individual
talks, and which are shared across the series. Currently this information
is only conveyed through bylines. It might be worthwhile to introduce 
separate tabs.
- Currently, when changing the date of a series, all talks of the series
except the talk where one has initiated the change are simply deleted, and
new talks are created (except for talks in the series for which the
date was individually changed, or which are already flagged as completed).
This should at least be explained in a byline. Improving this behavior
seems to require rather large changes, as the recurrence rule is not saved
anywhere.

## Long Term

### Unit tests

Improve the unit test coverage. This includes refactoring classes such
that they can be unit tested effectively.
