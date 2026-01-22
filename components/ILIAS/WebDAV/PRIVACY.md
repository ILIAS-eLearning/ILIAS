# WebDAV Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services
- The WebDAV component employs the following services, please consult the respective privacy.mds
    - [Filesystem](../../ILIAS/Filesystem/PRIVACY.md)
    - [FileUpload](../../ILIAS/FileUpload/PRIVACY.md)

## Data being stored
- Personal data being stored regarding WebDAV locks:
  - Owner of the locked resource (user or client) as transmitted in the lock info
  - User ID of the owner of the ILIAS object which is affected by the lock
- Personal data being stored upon uploading a document with WebDAV mount instructions under "Administration" > "Extending ILIAS" > "WebDAV" > "Upload instructions":
  - User ID of the owner (as provided by the instruction file)
  - User ID of the person that last changed the instruction (as provided by the instruction file)
  - Creation timestamp (as provided by the instruction file)
  - Update timestamp (as provided by the instruction file)

## Data being presented
- WebDAV does not present any personal data associated with a lock.
- The following personal data associated with an instruction is shown to persons with "Edit Settings" permission on the WebDAV object:
  - Creation timestamp of the instruction file
  - Update timestamp of the instruction file

## Data being deleted
- The personal data associated with a lock is deleted when an unlock request is received for a WebDAV resource
- The personal data associated with an instruction is deleted upon the deletion of the instruction file under "Administration" > "Extending ILIAS" > "WebDAV" > "Upload instructions"

## Data being exported
- The WebDAV component does not export any personal data.
