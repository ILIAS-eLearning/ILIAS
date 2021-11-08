# HTML Learning Module Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).


## Data being stored

- The HTML Learning Module component itself does not store any personal data.
- The HTML Learning Module component employs the following services, please consult
  the respective privacy.mds
  - Reference to Learning Progress (Move to respective md: Learning Progress service
    stores data on access time speifically last time, number of accesses and the
    progress status specifically in progress, completed for each user accessing the
    object.)
  - Reference to Metadata (Move to respective md: Metadata  service contains two
    branches: LOM and custom metdata. The LOM offers storing personal dates like
    author. Custom metadata do contain user-created metadata sets which may contain
    personal dates, which mus be individually checked in the global administration.)
  - Reference to Permission (Move to respective md: The account which created the
    very object is stored as it's owner, creation and update timestamps for the
    object. The permission service stores which users / user roles have what kindo
    of access to the object.)


## Data being presented

- The HTML Learning Module component itself does not present any personal data.
- Reference to Learning Progress (Move to respective md: Depending on global
  settings Learning Progress service presents progress and access data to accounts
  having the "View learning progress" permission.
- Reference to Metadata (Move to respective md: Metadata service presents metadata
  to accounts with "view" permission on respective Info-tab).
- Reference to Permission (Move to respective md: The service presents the owner
  to accounts with "view" permission on respective Info-tab).


## Data being deleted

- The HTML Learning Module itself does not store or delete any personal data.
- Basic object, permission and learning progress data is deleted only, once the
  object is deleted from trash. User can empty the trash at Administration > System
  Settings an Maintenance > Repository Trash and Permissions.


## Data being exported 

- The HTML Learning Module component itself does not export any personal data.
- Reference to Learning Progress (Move to respective md: Personal data can be
  exported from the Learning Progress service.)
- Reference to Metadata (Move to respective md: Metadata  service exports metadata
  like author along with the compnent itself.)
