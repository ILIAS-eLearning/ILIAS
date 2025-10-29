# FileServices Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- The FileServices component provides various services regarding the handling of files.
- Most of these services do not handle or have no awareness of any personal data.
- The exception to that is the upload policy, which is used for definig custom limits for the size of file uploads and whose handling of personal data will be described in the following sections.

## Data being stored
- The FileServices' upload policies store the following data:
  - User ID of the account that created the upload policy is stored as "Owner".
  - Creation date and time of the upload policy is stored.
  - Update date and time of the upload policy is stored.

## Data being presented
- The personal data which is stored by the upload policies is never being presented.

## Data being deleted
- Persons with "Edit Settings" permission for the FileServices object can delete upload policies and the associated personal data.

## Data being exported
- The FileServices component does not export any personal data.
