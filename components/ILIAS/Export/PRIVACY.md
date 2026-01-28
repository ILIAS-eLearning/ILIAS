# Export
Disclaimer: This documentation does not guarantee completeness or correctness. Please report any missing or incorrect information using the ILIAS issue tracker or contribute a fix via Pull Request (docs/development/contributing.md#pull-request-to-the-repositories).
## General Information
This privacy.md exclusively covers the export in Export-tabs. It does not cover exporting data from tables outside the Export-tab.  
The Export creates XML file for download.a In some cases Microsoft Excel or Comma Separated Values are also offered. 
Exports are available for most object types in the Export tab. For Container objects one may select which objects are to be included or omitted. 
Exports are available for some services in the administration like user service or skill management.
OER-Harvester can create and publish exports of objects. Only export types without personal data can be set to public access. The components take care of this distinction.


## Integrated Components
The Export does use the [IRSS].

## Data being stored
### Export Service
The Export service itself does not store data. The data is stored in the IRSS. 

### Export Files
Once an Export file is created, it is stacked up in the Export tab of the very object.  
These object export files for objects do not comprise any personal data.
However, the Test is an exception to this and allows exporting participant results. 
A minor infringement are the pool objects of test and survey. They automatically add the first and last name of the author of a question. This personal data is included in the export file.
If an object type has metadata, they might contain personal data, which will be exported.   
The export files of user data comprise the full set of data configured on that platform but minimally first name and last name and login. (Should this become a part of user service?)

## Data being presented
The export service does not present any personal data. The export files may comprise personal data, please consult the respective privacy.md.

## Data being deleted
If the export files are not moved to the trash but immediately purged from the system.

## Data being exported
This export itself is the option to export. 
