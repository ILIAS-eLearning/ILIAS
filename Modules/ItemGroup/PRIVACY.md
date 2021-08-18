# Item Group Privacy

This documentation comes with no guarantee of completeness or correctness. Please report any issues (missing or wrong information) in the ILIAS issue tracker.

## Data being stored

- The Item Group module itself does not store any personal data.
- Like all repository objects, it uses the basic Object service which stores the owner, creation and update timestamps for the object.
- Like all repository objects it uses the Permission service which stores information about which users / user roles have what kind of access to the object.

## Data presentation

- The Item group itself does not present any personal data.
- Like all repository objects it integrates screens of the Permission service which present information about which users / user roles have what kind of access to the object.

## Data Deletion

- The Item group itself does not store or delete any personal data.
- Basic object and permission data is deleted once the object is "finally" deleted (removed from trash).