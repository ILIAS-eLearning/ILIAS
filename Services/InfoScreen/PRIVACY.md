# Info Screen Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

- The Info Screen component employs the following services, please consult the respective privacy.mds
  - The **Learning Progress** service manages data on access time specifically last time, number of accesses and the progress status specifically in progress, completed for each user accessing the object.
  - The **Metadata** service contains two branches: LOM and custom metdata. The LOM offers storing person dates like author. Custom metadata do contain user-created metadata sets which may contain personal data, which must be individually checked in the global administration.)
  - [Notes Service](../../Services/Notes/PRIVACY.md)
  - The **Object** service stores the account which created the
    object as it's owner and creation and update timestamps for the
    object.
  - [Tagging Service](../../Services/Tagging/PRIVACY.md)
  - WebDav Service

## Configuration

- **Repository Object Context**
  - Almost all repository objects allow to present the info screen. Access is controlled by the "Visible" permission. Some object types allow to activate/deactivate the Info Screen in their settings.

## Data being stored

- The Info Screen component itself does not store any data. All data being stored is handled by the integrated services.

## Data being presented

Since the component itself does not store any data, all data being presented is provided by the integrated services. So this is just an overview on personal data being presented, the privacy information of the services may contain detailed information.

- Notes and comments, see [Notes Service](../../Services/Notes/PRIVACY.md)
- (Personal) learning progress status
- Metadata, incl. author and contributor information
- Object creation date and owner (this presentation requires "Edit" permission)
- Personal Tags, see [Tagging Service](../../Services/Tagging/PRIVACY.md)
- Locking user (if WebDav is activated)

## Data being deleted

No data stored by the component itself.

## Data being exported

No data stored by the component itself.