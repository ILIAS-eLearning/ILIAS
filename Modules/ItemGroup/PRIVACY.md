# Item Group Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Item Group component employs the following services, please consult the respective privacy.mds
  - The **Object** service stores the account which created the
    object as it's owner and creation and update timestamps for the
    object.
  - [AccessControl](../../Services/AccessControl/PRIVACY.md)


## Data being stored

- The Item Group module itself does not store any personal data.


## Data presentation

- The Item group itself does not present any personal data.


## Data Deletion

- The Item group itself does not store or delete any personal data.
- Basic object and permission data is deleted once the object is "finally" deleted
  (removed from trash).
