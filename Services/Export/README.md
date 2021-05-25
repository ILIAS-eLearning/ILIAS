# Export/Import Service

## General Information
The Export Service provides several classes for the XML-based im/export of repository objects.

### Upload from directory

To avoid HTTP-Post-Limit restrictions for the import of repository objects, ILIAS 
offers the possibility to directly import files from a predefined upload directory.
Files which are located in the upload directory can be accessed directly by specific users, without
the requirement to upload the files via HTTP.

#### Export Files
Export files for repository objects must be located in the directory
 
`{PATH_TO_EXTERN_DATA_OF_CLIENT}/upload/export`

Files located in this directory must be named with the original export file name: e.g

`1605001786__13243__fold_319.zip` for folder objects
`1604568820__12654__cat_3191.zip` for category objects

The file names must match the regular expression
  
`/[0-9]{10}__[0-9]{1,6}__([a-z]{1,4})_[0-9]{2,9}.zip`

The "Add new Item" dialogue will only show export files with names matching the object 
type ("Category" files are only presented in the category import form).
All files located in this directory will be shown to all users. 
To restrict the "Upload from directory" service to specific users only, create 
subdirectories with user ids and copy the export files to this location:

Example for category export files only for "Root" User (ID 6):
`{PATH_TO_EXTERN_DATA_OF_CLIENT}/upload/export/6/1604568820__12654__cat_3191.zip`

#### Scorm and HTLMs
The configuration of an upload directory has been removed in Release 7.
The fixed location is: 

`{PATH_TO_EXTERN_DATA_OF_CLIENT}/upload/learningModule`

All files stored in this directory will be available in the SCORM-upload 
and the HTLM-File-Browser. Only users with "write" permission to the administration
can access the files.

#### Media Objects
The configuration of an upload directory has been removed in Release 7.
The fixed location is: 

`{PATH_TO_EXTERN_DATA_OF_CLIENT}/upload/mob`

All files stored in this directory will be accessible in the media pool management. 
Only users with "write" permission to the administration can access the files.
