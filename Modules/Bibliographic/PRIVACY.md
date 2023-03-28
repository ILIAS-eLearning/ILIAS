# Bibliograpghic Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- The Bibliographic module imports its data from a bibliography file. This data may or may not be personal in nature.
- The following sections regarding the storage, presentation, deletion and export of personal information are written with the assumption that personal data has been imported. Otherwise there would be no handling of privacy-relevant data to describe at all.

## Data being stored
- Potentially personal data from the imported bibliography file is stored as "value".

## Data being presented
- Persons with "Read" permission on the Bibliography object are presented with data which was stored as "value".
- This data is shown in the entries of the "Content" tab as well as in the "Detail View" of an entry, which can be opened by clicking on the entry.

## Data being deleted
- Persons with "Delete" permission on the Bibliography object can delete the Bibliography object and therefore data which was stored as "value".
  - If the Trash is deactivated, the Bibliography object and its data are deleted immediately. 
  - If the Trash is activated, deletion from the Repository merely pushes the Bibliography object to the Trash. 
  - In the latter case the Bibliography object and its data will only be deleted at last when:
    - Manually emptying the Trash at "Administration" > "System Settings and Maintenance" > "Repository Trash and Permissions" > "Trash". 
    - Or running a Cronjob for emtpying the Trash.
- Persons with "Edit Settings" permission on the Bibliography object can cause the deletion of data which was stored as "value" by using the "Override Entries" function.
  - This can be done by activating the "Override Entries" option in the "Settings" tab, selecting a bibliography file and clicking "Save".
  - The resulting update of the Bibliography object with data from the new bibliography file can lead to data being overwritten, which amounts to the same thing as this data being deleted.

## Data being exported
- Persons with "Edit Settings" permission on the Bibliography object can export data which was stored as "value" by using the export function in the "Export" tab.
- Persons with "Read" permission on the Bibliography object can export potentially personal data by using the "Download Original File" button in the "Content" tab, which downloads the bibliography file whose data was used in the creation of the Bibliography object.