# HTML Learning Module Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The HTML Learning Module component employs the following services, please consult the respective privacy.mds
  - The **Learning Progress** service manages data on access time specifically last time, number of accesses and the progress status specifically in progress, completed for each user accessing the object.
  - The **Metadata** service contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
  - The **Object** service stores the account which created the
    object as it's owner and creation and update timestamps for the
    object.
  - [AccessControl](../../Services/AccessControl/PRIVACY.md)
  - [Info Screen Service](../../Services/InfoScreen/PRIVACY.md)

## Data being stored

The HTML Learning Module component itself does not store any personal data.

## Data being presented

The HTML Learning Module component itself does not present any personal data.

## Data being deleted

- The HTML Learning Module itself does not store or delete any personal data.
- Basic object, permission and learning progress data is deleted only, once the
  object is deleted from trash. User can empty the trash at Administration > System
  Settings an Maintenance > Repository Trash and Permissions.


## Data being exported 

- The HTML Learning Module component itself does not export any personal data.
- Personal data can be exported from the Learning Progress service.
- Metadata  service exports metadata like author along with the compnent itself.
