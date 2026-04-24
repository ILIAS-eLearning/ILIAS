# Course

This documentation is a work in progress, and will be updated with
more information.

### Certificates

If the Learning Progress is globally activated, then Course Certificates
will only be issued to users via their Learning Progress status: when
a user achieves the status 'Completed', they will receive a Certificate.
This is handled automatically by `ILIAS/Certificate`.

If the Learning Progress is globally deactivated, Courses will instead issue
Certificates to users when their status in the Course is set to 'Passed'.

### Start Objects

Start objects can be used to guide the learner through a starting sequence of objects before presenting the whole course. However this is only related to the presentation in the main course views. It is not a way to fully prevent access to other objects of the course, e.g. through search, since this is not an RBAC mechanism.

### Course Timings Notifications

The 'Course Timings Notifications' cron job sends notifications to members
of courses in 'Timings View'. Members are notified at the beginning
of the suggested timeframe for objects, and when they have exceeded it
without completing the material. 

A member of a 'Timings View' course is notified about the start of the
suggested timeframe for an object in the course under the following
conditions:

- This notification has not already been sent for this object.
- The start of the suggested timeframe is in the past at the time the
  cron job is executed, and the end is in the future, see [below](#date-handling) for details.
- Learning progress of the object is not deactivated, and the user 
  does not have the status 'Completed'.

A member of a 'Timings View' course is notified about exceeding the
suggested timeframe for an object in the course under the following
conditions:

- This notification has not already been sent for this object.
- The end of the suggested timeframe is in the past at the time the
  cron job is executed, see [below](#date-handling) for details.
- Learning progress of the object is not deactivated, and the user
  does not have the status 'Completed'.

#### Date Handling

When comparing dates, the time of day is also taken into account,
not only the date. For absolute dates, start and end of the suggested
timeframe are always understood as being at 00:00 UTC. For relative
dates on the other hand, start and end are based on the time the user
joined the course.
