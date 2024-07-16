# Course

This documentation is a work in progress, and will be updated with
more information.

### Certificates

If the Learning Progress is globally activated, then Course Certificates
will only be issued to users via their Learning Progress status: when
a user achieves the status 'Completed', they will recieve a Certificate.
This is handled automatically by `Services/Certificate`.

If the Learning Progress is globally deactivated, Courses will instead issue
Certificates to users when their status in the Course is set to 'Passed'.

### Start Objects

Start objects can be used to guide the learner through a starting sequence of objects before presenting the whole course. However this is only related to the presentation in the main course views. It is not a way to fully prevent access to other objects of the course, e.g. through search, since this is not an RBAC mechanism.
