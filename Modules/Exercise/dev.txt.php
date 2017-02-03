<?php exit; ?>
## Main changes

New DB table exc_ass_file_order with columns id,assignment_id,filename,order_nr

- Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php
working with ilExAssignmentFileSystemGUI instead of ilFileSystemGUI


- Modules/Exercise/classes/class.ilExAssignment.php (create another class for this stuff could be fine)
Edit function saveAssOrderOfExercise -- now is static ( fixing another bug )
New function saveInstructionFilesOrderOfAssignment (db update)
New function instructionFileInsertOrder (db store)
New function instructionFileDeleteOrder (db delete)
New function renameInstructionFile (db delete/update)
New function instructionFileExistsInDb (db query)
New function instructionFileAddOrder: DB query and add order to an array previous to setData in the view rows. If the files doesn't have any previous order in the database
	this method will create it so we don't need any patch for the current installations and for the unziping files neither. See instructionFileGetFileOrderData method.

New function instructionFileOrderGetMax (db query, max order)
New function instructionFileRearrangeOrder rebuild the order after deletion. example: 10,30,50 will show 10,20,30
New function renameExecutables (names with extensions like php,php3,inc,lang... will be renamed (.sec) after store it in db) //have we another method that renames the extension files in db?
New function instructionFileGetFileOrderData (db query returns order values.)



- Services/FileSystem/classes/class.ilFileSystemGUI.php
Edit Construct commands available are now in the defineCommands method.
Edit ListFiles now can get class name as a param, to use one ilFileSystemTableGUI as always or another.
New function defineCommands define which commands are available.
New function getActionCommands  returns the commands array.


- Services/FileSystem/classes/class.ilFileSystemTableGUI.php
Edit contructor using the new method addColumns.
Edit function prepareOutput, take an array from getEntries and if the method instructionFileAddOrder exists then
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


## Things to take care.
If an assignment is deleted, we should delete the filenames from exc_ass_file_order table.
We are working with this files in the DB with "filename" instead of "id"


## 2/2/2017

*Modules/Exercise/classes/class.ilExSubmissionObjectGUI.php (User perspective)

	-(change) method "getOverviewContentPortfolio"
	Button create portfolio in assignment calls now "createPortfolioFromAssignmentObject" instead of getting the templates and createPortfolio.
	If this assignment has one previous submission, one new button are shown to unlink this portfolio/submission (method askUnlinkPortfolio).

	*** Here imo we should delete the button for portfolio selection. Because the portfolio is not defined here.

-(new) method "askUnlinkPortfolioObject"
	Confirmation for unlink portfolio/assignment.

-(new) method "unlinkPortfolioObject"
	Delete the portfolio from the assignment.

-(new) method "createPortfolioFromAssignmentObject"
	Check portfolio templates available and check if this assignment has port. template.
	Takes the values from the exercise, assignment, portfolio and portfolio template and set the proper parameters.
	Redirects to createPortfolioFromAssignment in ilObjPortfolioGUI.


*Modules/Portfolio/classes/class.ilObjPortfolioGUI.php  (assignment submission)

-(new) method "createPortfolioFromAssignment" to create portfolios from assignments without cross any form
	check again the templates
	getAllPortfolioPages and get blogs as well.
	create new portfolio and clone pages and blogs from the template.
	link the portfolio to the assignment.

-(new) method "linkPortfolioToAssignment"
	Add the portfolio to an assignment

-(change) method "createPortfolioFromTemplateProcess"
	now the part related with the portfolio assignment is in linkPortfolioToAssignment method.

	*** CARE WITH SKILLS I DON'T KNOW HOW THEY WORK. I DON'T KNOW IF I'M TAKING CARE OF THEM OR NOT.


*Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php

-(change) @ilCrl Calls also ilPropertyFormGUI class

-(change) method "executeCommand"
	new case "ilpropertyformgui" needed for ilRepositorySelector2 when selection of portfolio template is needed.

-(change) method "initAssignmentForm"
	new portfolio feature. radiobuttons + ilrepositoryselector2 to predefine a default portfolio template for this assignment.
	improved the order of the form elements + added section headers.

-(change) method "processForm"
	now gets the template input from the form.

-(change) method "importFormToAssignment"
	sets the portfolio template id.

-(change) method "getAssignmentValues"
	takes the portfolio template id

*Modules/Exercise/classes/class.ilExAssignmentGUI.php
just ordering ui elements

*Modules/Exercise/classes/class.ilExSubmissionFileGUI.
-(change) method "getOverviewContent"
list files one below another instead of separated by coma.
