# File Object Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
The file object encompasses only files which were created as an independent object. This means that files which are created by adding a new item of the type file to the repository, a course, category etc. are file objects while files which are uploaded to exercises, tests etc. are not. The file object uses the ILIAS Resource Storage Service (IRSS) to manage the storing and retrieving of its files while the data which is associated with the files (ownership etc.) is managed by the file object itself.

## Services being used
- The File Object employs the following services, please consult the respective privacy.mds:
  - [Info Screen](../../Services/InfoScreen/PRIVACY.md)
  - [Metadata](../../Services/MetaData/Privacy.md)
  - [AccessControl](../../Services/AccessControl/PRIVACY.md)
  - ECS
  - [IRSS](../../src/ResourceStorage/PRIVACY.md)
  - Learning Progress
  - [News](../../Services/News/PRIVACY.md)
  - Object Service
  - [Rating](../../Services/Rating/PRIVACY.md)


## Data being stored
- UserID of the account that created the ILIAS file object is stored as "owner". 
- Creation Timestamp of the ILIAS file object. 
- Update Timestamp  of the ILIAS file object. 
- Ressource-Identifier for accessing the file through the Ressource Storage Service.

## Data being presented
- Persons with "edit settings" permission for the file object are presented with the first and last name of the user who uploaded a version for each version entry on the version tab.

## Data being deleted
- Persons with "delete" permission for the file object can delete the file object. If no trash is activated then the data is deleted at once.  
- If the trash is activated then the basic object, permission and learning progress and metadata data are deleted only, once the object is deleted from trash. User can empty the trash at Administration > System Settings an Maintenance > Repository Trash and Permissions.

## Data being exported
- Exports of the file object contain the owner data and UserID for each version of the file object.