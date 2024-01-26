# File Object Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
The File Object encompasses only files which are created as an independent object. This means files which are created by "Add New Item" in the Repository or at "Personal Workspace" > "Personal and Shared Resources". This is NOT about files which are uploaded to Exercises, Tests etc. The File Object uses the ILIAS Resource Storage Service (IRSS) to manage the storing and retrieving of files. Data associated with the File Object such as its Owner is managed by the File Object itself.

## Services being used
- The File Object employs the following services, please consult the respective PRIVACY.mds:
  - [InfoScreen](../../Services/InfoScreen/PRIVACY.md)
  - [Metadata](../../Services/MetaData/Privacy.md)
  - [AccessControl](../../Services/AccessControl/PRIVACY.md)
  - ECS
  - [IRSS](../../src/ResourceStorage/PRIVACY.md)
  - Learning Progress
  - [News](../../Services/News/PRIVACY.md)
  - Object Service
  - [Rating](../../Services/Rating/PRIVACY.md)


## Data being stored
- User ID of the account that created the ILIAS File Object is stored as "Owner".
- Creation timestamp of the ILIAS File Object is stored.
- Update timestamp of the ILIAS File Object is stored.
- Resource ID for accessing the file through the ILIAS Ressource Storage Service is stored.

## Data being presented
- Persons with "Edit Settings" permission for the File Object are presented with the first and last name of the person who uploaded a version for each version entry on the tab "Version".

## Data being deleted
- Persons with "Delete" permission for the File Object can delete the File Object.
- If the Trash is deactivated, the File Object is deleted immediately. 
- If the Trash is activated, deleting the File Object from the Repository merely pushes the File Object, its permissions, Learning Progress and Metadata to the Trash. 
- In the latter case the File Object will only be finally deleted when:
  - Manually emptying the Trash at "Administration" > "System Settings and Maintenance" > "Repository Trash and Permissions" > "Trash".
  - Or running a Cronjob for emtpying the Trash.

## Data being exported
- Exports of the File Object contain the User ID for each version of the File Object.