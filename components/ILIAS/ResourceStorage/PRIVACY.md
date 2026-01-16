# ILIAS Resource Storage Service (IRSS) Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- The IRSS is an abstraction layer on top of the Filesystem for accessing and storing file-related data.
- Components which use the IRSS might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Integrated Services
- The IRSS employs the following services, please consult the respective PRIVACY.mds:
	- [Filesystem](../Filesystem/PRIVACY.md)
	- [FileUpload](../FileUpload/PRIVACY.md)

## Data being stored
- The User ID of the person who created the resource is stored as "owner_id".
- The timestamp of the resource creation is stored.
- The User ID of the person who created a revision of the resource is stored as "owner_id".

## Data being presented
- Persons with "Read" permission for the FileServices object are presented with the date and time of the resource creation in the Resource Overview.
  - The Resource Overview is located under "Administration" > "System Settings and Maintenance" > "File Services" > "Resource Overview".

## Data being deleted
- Personal data that is stored regarding resources or revisions is deleted by the IRSS when directed to do so by the component who created the resource or revision.

## Data being exported
- The IRSS does not export any personal data.
