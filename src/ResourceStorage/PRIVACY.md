# ILIAS Resource Storage Service (IRSS) Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
The IRSS is an abstraction layer on top of the Filesystem for accessing and storing file-related data.
Services and components which use the IRSS might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Services being used
- The IRSS employs the following services, please consult the respective PRIVACY.mds:
	- [Filesystem](../../src/Filesystem/PRIVACY.md)
	- [FileUpload](../../src/FileUpload/PRIVACY.md)

## Data being stored
- The User ID of the person who created the resource is stored as "owner_id".
- The timestamp of the resource creation is stored.

## Data being presented
- The IRSS does not present any personal data.

## Data being deleted
- The IRSS does not delete any personal data.

## Data being exported
- The IRSS does not export any personal data.