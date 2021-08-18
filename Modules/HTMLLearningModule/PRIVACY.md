# HTML Learning Module Privacy

This documentation comes with no guarantee of completeness or correctness. Please report any issues (missing or wrong information) in the ILIAS issue tracker.

## Data being stored

- The HTML Learning Module component itself do not store any personal data.
- Like all repository objects, it uses the basic Object service which stores the owner, creation and update timestamps for the object.
- Like all repository objects it uses the Permission service which stores information about which users / user roles have what kind of access to the object.
- The module integrates the Learning Progress service, which stores data on access time (last time, number of accesses) and the progress status (in progress, completed) for each user accessing the object.

## Data presentation

- The HTML Learning Module component itself does not present any personal data.
- Like all repository objects it integrates screens of the Permission service which present information about which users / user roles have what kind of access to the object.
- The module integrates the Info Screen service which reveals owner and creation date of the object.
- The module integrates the Learning Progress service, which presents (depending on global settings) progress and access data to users having the "View learning progress" permission.

## Data Deletion

- The Item group itself does not store or delete any personal data.
- Basic object, permission and learning progress data is deleted once the object is "finally" deleted (removed from trash).