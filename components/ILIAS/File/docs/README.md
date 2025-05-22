File Object
===========

# General Information

The file object is the files that were created in the magazine via ‘Add new
Object’. The file object uses many of the central file services and also offers
WOPI actions, for example.

# Capabilities

The file object uses an internal mechanism to handle all the ‘functions’ that a
user with a file object can perform. The capabilities currently check
authorisations, whether WOPI actions are available for a file and also certain
settings per object. The capabilities per user and file object are calculated
once per request and are then used to provide links to actions, create tabs and
also handle static URLs, for example. In particular, the capabilities are also
used to decide which action to open by default when clicking on a file object in
the magazine.
Here is a list (based on unit tests) of cases in which a default action is to be
expected:

<!-- START CAPABILITY_TABLE -->

| User's Permissions                            | WOPI View Action av. | WOPI Edit Action av. | Click-Setting | Expected Capability |
| --------------------------------------------- | -------------------- | -------------------- | ------------- | ------------------- |
| read                                          | Yes                  | Yes                  | Open          | DOWNLOAD            |
| write, read                                   | Yes                  | Yes                  | Open          | DOWNLOAD            |
| edit_file                                     | Yes                  | Yes                  | Open          | EDIT_EXTERNAL       |
| read, visible                                 | No                   | No                   | Info-Page     | FORCED_INFO_PAGE    |
| read, write, visible, edit_file, view_content | Yes                  | Yes                  | Info-Page     | FORCED_INFO_PAGE    |
| write, read                                   | Yes                  | Yes                  | Info-Page     | FORCED_INFO_PAGE    |
| visible                                       | Yes                  | Yes                  | Open          | INFO_PAGE           |
| write                                         | Yes                  | Yes                  | Open          | MANAGE_VERSIONS     |
| none                                          | Yes                  | Yes                  | Open          | NONE                |
| edit_file, view_content                       | Yes                  | Yes                  | Open          | VIEW_EXTERNAL       |
| read, write, visible, edit_file, view_content | Yes                  | Yes                  | Open          | VIEW_EXTERNAL       |

<!-- END CAPABILITY_TABLE -->
