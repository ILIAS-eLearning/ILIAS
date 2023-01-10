# ILIAS Resource Storage Service (IRSS) Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
The IRSS is a special case as it is managing the storage of resources which are given to it and obtained by other Services and Modules. Therefore most of the privacy relevant parts take part in these services and components which use the IRSS. Currently these are the File-Object and the MainMenu, but in the future more Modules and Services will be migrated from the old storage to the IRSS.

## Services being used
- The Resource Storage employs the following services, please consult the respective PRIVACY.mds:
	- [FileSystem](../../src/Filesystem/PRIVACY.md)
	- [FileUpload](../../src/FileUpload/PRIVACY.md)

## Data being stored
- The User-ID of the account that created the resource is stored as "owner_id".
- The timestamp of the resource creation is stored.

## Data being presented
- The IRSS itself does not present any personal data.
- Services and Components which use the IRSS might present personal data. This would be specified in their respective PRIVACY.md.

## Data being deleted
- The IRSS does not delete personal data by itself.
- Services and Components which use the IRSS might initiate the deletion of personal data. This would be specified in their respective PRIVACY.md.

## Data being exported
- The IRSS itself does not export any personal data.
- Services and Components which use the IRSS might export personal data. This would be specified in their respective PRIVACY.md.