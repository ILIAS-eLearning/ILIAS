# File System Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](../../docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
The File System is a special case as it is managing the storage of files which are given to it and obtained by other Services and Modules. Therefore most of the privacy relevant parts take part in these services and components which use the File System. Currently this is the ILIAS Resource Storage Service (IRSS) as well as many others, but in the future more Modules and Services will be migrated from the direct usage of the File Service to an indirect usage via the IRSS.

## Data being stored
- The File Service itself does not store any personal data.
- Services and Components which use the File Service might store personal data. This would be specified in their respective PRIVACY.md.

## Data being presented
- The File Service itself does not present any personal data.
- Services and Components which use the File Service might present personal data. This would be specified in their respective PRIVACY.md.

## Data being deleted
- The File Service does not delete personal data by itself.
- Services and Components which use the File Service might initiate the deletion of personal data. This would be specified in their respective PRIVACY.md.

## Data being exported
- The File Service itself does not export any personal data.
- Services and Components which use the File Service might export personal data. This would be specified in their respective PRIVACY.md.