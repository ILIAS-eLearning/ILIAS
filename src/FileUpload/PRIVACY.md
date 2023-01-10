# File Upload Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
The FileUpload is a special case as it is only processing the upload of files. Therefore the privacy relevant parts take part in other Services and Components like the File System, the Resource Storage or the File Object.

## Services being used
- The File Upload employs the following services, please consult the respective PRIVACY.mds:
	- [FileSystem](../../src/Filesystem/PRIVACY.md)

## Data being stored
- The File Upload itself does not store any personal data.

## Data being presented
- The File Upload itself does not present any personal data.
- Services and Components which store and use the uploaded files might present personal data. This would be specified in their respective PRIVACY.md.

## Data being deleted
- The File Upload itself does not delete any personal data.
- Services and Components which store and use the uploaded files might delete personal data. This would be specified in their respective PRIVACY.md.

## Data being exported
- The File Upload itself does not export any personal data.
- Services and Components which store and use the uploaded files might export personal data. This would be specified in their respective PRIVACY.md.
