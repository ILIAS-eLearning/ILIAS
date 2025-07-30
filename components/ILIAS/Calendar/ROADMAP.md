# Calendar

## Short Term

### Analyze indices of cal_entries

Currently, the database table `cal_entries` has an index for the field
`last_update`. It should be investigated whether this index can be dropped
or optimized.

### Autocomplete for User Notification Input

When autocomplete is supported by KS tag input fields, it should
be implemented with `ilCalendarAppointmentGUI::doUserAutoComplete` in 
`ilCalendarAppointmentGUI::initForm`.

## Mid Term

### Turn ilCalendarRecurrence Into a Proper Data Object

Currently, `ilCalendarRecurrence` also has methods `read`, `delete`, etc.
related to the database. This functionality should be separated out of
the class, so that it can function solely as a data object.

Classes that inherit from `ilCalendarRecurrence` empty out those methods
anyways.

## Long Term

### API for Calendar

The Calendar should offer its services to other components through a
dedicated API, and not via a mixture of static calls and manually
instantiated classes.

Among other things, recurrence inputs should be offered there through
their factory.
