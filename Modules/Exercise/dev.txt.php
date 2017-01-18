<?php exit; ?>
## Main changes

New DB table exc_ass_file_order with tables id,assignment_id,filename,order_nr

- Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php
working with ilExAssignmentFileSystemGUI instead of ilFileSystemGUI


- Modules/Exercise/classes/class.ilExAssignment.php
Edit function saveAssOrderOfExercise -- now is static ( fixing another bug )
New function saveInstructionFilesOrderOfAssignment (db update)
New function insertOrder (db store)
New function deleteOrder (db delete)
New function renameInstructionFile (db delete/update)
New function InstructionFileExistsInDb (db query)
New function addOrderValues(db query and add the order to an array previous to setData in the view rows)


- Services/FileSystem/classes/class.ilFileSystemGUI.php
Edit Construct commands available are now in the defineCommands method.
Edit ListFiles now can get class name as a param, to use one ilFileSystemTableGUI as always or another.
New function defineCommands define which commands are available.
New function getActionCommands  returns the commands array.


- Services/FileSystem/classes/class.ilFileSystemTableGUI.php
Edit contructor using the new method addColumns.
Edit function prepareOutput, take an array from getEntries and if the method addOrderValues exists then
the order values are added to the array.
Edit function getEntries, now doesn't setData, only returns the array to work with.
New function addColumns, check if the property add_order_column from a child class exists and add the proper columns.
Edit function fillRow, check if the property add_order_column from a child class exists and add set the tpl vars.



- Services/FileSystem/templates/default/tpl.directory_row.html
New block: Order

- (NEW FILE) Modules/Exercise/classes/class.ilExAssignmentFileSystemGUI.php
Extends Services/FileSystem/classes/class.ilFileSystemGUI.php
- (NEW FILE) Modules/Exercise/classes/class.ilExAssignmentFileSystemTableGUI.php
Extends Services/FileSystem/classes/class.ilFileSystemTableGUI.php




## Last commit changes:

- No new params in the contructors

- No new params in the methods to define the redirected view.

- No new table row template.

- Methods which works with DB are not in the GUI files.


## Things to take care.

If an assignment is deleted, we should delete the filenames from exc_ass_file_order table.
In the documentation at (ilFileSystemGUI prepareOutput) there is a comment with a suggerstion from where the code should be placed.
Unzip doesn't work properly yet. Do we maintain this feature for this kind of files?
After deletion, the items needs to be reordered (Check when renaming with the same other file name.
We are working with this files in the DB with "filename" instead of "id"
files php and py. How are they being uploading. And stored as .sec in the db.
without extension doesn't store the file in the directory but stored in the database. ( probably I should delete all the records for this assignment id and then store it again)
so delete and insert instead of update.




