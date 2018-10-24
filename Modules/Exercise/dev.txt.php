<?php exit; ?>

## Main changes 5.4

- Introduction of repo objects (wiki) as submission.
- Introduction of assignment type classes under AssignmentTypes

Current situation in ilExSubmission/exc_returned table
- PROBLEM: - exc_returned entries are used for text and blog/portfolios submissions, too!
           - filetitle is the wsp_id for blog/portfolios, the ref_id for wikis now!
           - getFiles() also returns entries for text
           -> This is confusing.
- FUTURE: exc_returned entries should be refactored in a more general concept "Submission Items" (files, text,
  wsp objects, repo objects, ...)


## Main changes 5.3

New DB table exc_ass_file_order with columns id,assignment_id,filename,order_nr

### File organisation 5.3
#### data/*client* directory

ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/0/									holds sample solution file (with original name)
ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/*USER_ID*/							holds evaluation/feedback files from tutors for learner *USER_ID*
ilExercise/X/exc_*EXC_ID*/subm_*ASS_ID*/*USER_ID*/*TIMESTAMP*_filename.pdf	holds file submissions (also blogs and porfilios, filename = obj_id)
ilExercise/X/exc_*EXC_ID*/peer_up_*ASS_ID*/*TAKER_ID*/*GIVER_ID*/*CRIT_ID*/	holds peer feedback file (original name)
ilExercise/X/exc_*EXC_ID*/mfb_up_*ASS_ID*/*UPLOADER_ID*/					hold multi-feedback zip file/structure from tutor *UPLOADER_ID*
ilExercise/X/exc_*EXC_ID*/tmp_*ASS_ID*/										temp dir for "download all assignments" process (creates random subdir before starting)

#### webdata/*client* directory

ilExercise/X/exc_*EXC_ID*/ass_*ASS_ID*/										directory holds all instruction files (with original names) !!! CHANGED in 5.3


### File organisation 5.2

#### data/*client* directory

ilExercise/X/exc_*EXC_ID*/ass_*ASS_ID*/										directory holds all instruction files (with original names)
ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/0/									holds sample solution file (with original name)
ilExercise/X/exc_*EXC_ID*/feedb_*ASS_ID*/*USER_ID*/							holds evaluation/feedback files from tutors for learner *USER_ID*
ilExercise/X/exc_*EXC_ID*/subm_*ASS_ID*/*USER_ID*/*TIMESTAMP*_filename.pdf	holds file submissions (also blogs and porfilios, filename = obj_id)
ilExercise/X/exc_*EXC_ID*/peer_up_*ASS_ID*/*TAKER_ID*/*GIVER_ID*/*CRIT_ID*/	holds peer feedback file (original name)
ilExercise/X/exc_*EXC_ID*/mfb_up_*ASS_ID*/*UPLOADER_ID*/					hold multi-feedback zip file/structure from tutor *UPLOADER_ID*
ilExercise/X/exc_*EXC_ID*/tmp_*ASS_ID*/										temp dir for "download all assignments" process (creates random subdir before starting)

#### webdata/*client* directory

not used in 5.2


#### 7/3/2017 INSTRUCTION FILES - DISPLAYED IN VIEW MODE

## MIGRATION
#### Instruction Files migration from outside ILIAS directory to ILIAS "data" directory

File -> **patch_exc_move_instruction_files.php**

I assume that all the files located in "ass_XXX" --> outside ilias /outside_data_directory/client_name/ilExercise/X/exc_XXX/ass_XXX/0
are instruction files.

We had doubts about where are the solution files located and this files are located in directories like this: feedb_xx/0/xxxx.xx

So The patch moves all the content in ass_ directories.


## FEATURE IMPLEMENTATION
Save and show instruction files located inside root directory instead of outside data directory.

	- (edit) **include/inc.ilias_version.php** change ILIAS version

			define("ILIAS_VERSION", "5.3.0 2017-02-07");
			define("ILIAS_VERSION_NUMERIC", "5.3.0");

	- (new class) **Modules/Exercise/classes/class.ilFSWebStorageExercise.php** extending ilFileSystemStorage class.
	Stores the files inside ILIAS data directory.
	important to know, in the construct:

		parent::__construct(self::STORAGE_WEB,true,$a_container_id);


	- (edit) **Modules/Exercise/classes/class.ilExerciseExporter.php**

		- (edit)**getValidSchemaVersions()** method: Add new ILIAS version

				"5.2.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_2",
				"xsd_file" => "ilias_exc_5_2.xsd",
				"uses_dataset" => true,
				"min" => "5.2.0",
				"max" => "5.2.99"),
				"5.3.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Exercise/exc/5_3",
				"xsd_file" => "ilias_exc_5_3.xsd",
				"uses_dataset" => true,
				"min" => "5.3.0",
				"max" => "")

	- (edit) **Modules/Exercise/classes/class.ilExerciseDataSet.php**

		- (edit) **getSupportedVersions()** method: Add new ILIAS version

				return array("4.1.0", "4.4.0", "5.0.0", "5.1.0", "5.2.0", "5.3.0");

		- (edit) **getTypes()** method: Add new ILIAS version, with same code as 5.2

				if ($a_entity == "exc")
				{
					switch ($a_version)
					{
						...
						case "5.2.0":
						case "5.3.0":
						...
						...
						...


				if ($a_entity == "exc_assignment")
				{
					switch ($a_version)
					{
						case "5.3.0": //same as 5.2.0 + add WebDataDir
							return array(
								...

								"WebDataDir" => "directory"

								...
							);
					...

		- (edit) **readData()** method: Add the ILIAS version. Same code as 5.2

				...
				case "5.2.0":
				case "5.3.0":
				...

		- (edit) **getXmlRecord** method: store the setWebDataDir path.

				//now the instruction files inside the root directory
				include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
				$fswebstorage = new ilFSWebStorageExercise($a_set['ExerciseId'], $a_set['Id']);
				$a_set['WebDataDir'] = $fswebstorage->getPath();

		- (edit) **importRecord()** method: instruction files into web data dir., all the others are stored as always.
			we were talking about if $a_rec["WebDataDir"] use one class, else the other one. But both are needed.
			- ilFSWebStorageExercise for instruction files.
			- ilFSStorageExercise for all the other files.

				// (5.3) assignment files inside ILIAS
				include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
				$fwebstorage = new ilFSWebStorageExercise($exc_id, $ass->getId());
				$fwebstorage->create();
				$dir = str_replace("..", "", $a_rec["WebDataDir"]);
				if ($dir != "" && $this->getImportDirectory() != "")
				{
					$source_dir = $this->getImportDirectory()."/".$dir;
					$target_dir = $fwebstorage->getPath();
					ilUtil::rCopy($source_dir, $target_dir);
				}

	- (edit) **Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php**

		- (edit) **executeCommand** method: case ilfilesystemgui: use ilFSWebStorageExercise instead of ilFSStorageExercise

				include_once("./Modules/Exercise/classes/class.ilFSWebStorageExercise.php");
				$fWebStorage = new ilFSWebStorageExercise($this->exercise_id, $this->assignment->getId());
				$fWebStorage->create();

	- (edit) **Modules/Exercise/classes/class.ilExAssignment.php**

		- (edit) **getFiles()** method: ilFSWebStorageExercise instead of ilFSStorageExercise

		- (edit) **uploadAssignmentFiles()** method: ilFSWebStorageExercise instead of ilFSStorageExercise


	- (edit) **Modules/Exercise/classes/class.ilExAssignmentGUI.php**

		- (edit) **addFiles()** method: Represent the files depending of its type

		- (edit) **addSubmissionFeedback()** method: include the new class.

				include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");



## MANTIS BUG :0019795
It is not possible to remove files from a peer feedback from a exercise.


The problem seems the file path creation and affects both feedback with and without criteria.

Example:
User ID who did the exercise: 310
User ID who provide feedback: 6
Feedback file: feedback.txt
Criteria ID = 10

Without criteria the uploaded files are stored outside the final path. The name of the file is also affected.

data/client/ilExercise/3/exc_343/peer_up_15/310/6/ [empty directory]
data/client/ilExercise/3/exc_343/peer_up_15/310/6feedback.txt

After patch:

data/client/ilExercise/3/exc_343/peer_up_15/310/6/feedback.txt


With criteria, the final directory name is userid+criteriaid instead of criteria id.

data/client/ilExercise/3/exc_343/peer_up_15/310/610/feedback.txt

After patch:

data/client/ilExercise/3/exc_343/peer_up_15/310/6/10/feedback.txt

## We need to take a look at how to proceed with the migration of the old directories/files.



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

-(change) initCreatePortfolioFormTemplate
	all the skills stuff moved to getSkillsToPortfolioAssignment method.

-(new) method getSkillsToPortfolioAssignment
	returns the skills to be added in the assignment.

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



##28/2/17

## ilTextareaInput - MIN and MAX CHARACTER LIMITATIONS.

Textarea input now can limit the number of characters allowed. The limitations are not mandatory, for instance we can limit only min or max.

## Info to extend this feature to another elements taking exercise assignments as an example.

#### DATABASE

	update table [X] with 2 new columns "min_char_limit" and "max_char_limit" both integer and length 4.

	if($ilDB->tableExists("[table_name]"))
	{
		if(!$ilDB->tableColumnExists('[table_name]','min_char_limit'))
		{
			$ilDB->addTableColumn("[table_name]", "min_char_limit", array("type" => "integer", "length" => 4));
		}
			if(!$ilDB->tableColumnExists('[table_name]','max_char_limit'))
		{
			$ilDB->addTableColumn("[table_name]", "max_char_limit", array("type" => "integer", "length" => 4));
		}
	}

e.g. table used in exercise assignments -> "exc_assignment"


#### FORM (admin - creation/configuration)

Getting exercise assignments as an example (Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php)

Add the fields - method -> initAssignmentForm()

	$rb_limit_chars = new ilCheckboxInputGUI($lng->txt("exc_limit_characters"),"limit_characters");

	$min_char_limit = new ilNumberInputGUI($lng->txt("exc_min_char_limit"), "min_char_limit");
	$min_char_limit->allowDecimals(false);
	$min_char_limit->setSize(3);

	$max_char_limit = new ilNumberInputGUI($lng->txt("exc_max_char_limit"), "max_char_limit");
	$max_char_limit->allowDecimals(false);
	$max_char_limit->setMinValue($_POST['min_char_limit'] + 1);

	$max_char_limit->setSize(3);

	$rb_limit_chars->addSubItem($min_char_limit);
	$rb_limit_chars->addSubItem($max_char_limit);

	$form->addItem($rb_limit_chars);


Manage data/inputs

method -> processForm()

	...
	// text limitations
	if($a_form->getInput("limit_characters"))
	{
	$res['limit_characters'] = $a_form->getInput("limit_characters");
	}
	if($a_form->getInput("limit_characters") && $a_form->getInput("max_char_limit"))
	{
	$res['max_char_limit'] = $a_form->getInput("max_char_limit");
	}
	if($a_form->getInput("limit_characters") && $a_form->getInput("min_char_limit"))
	{
	$res['min_char_limit'] = $a_form->getInput("min_char_limit");
	
	}
	...


method -> importFormToAssignment()

	$a_ass->setMinCharLimit($a_input['min_char_limit']);
	$a_ass->setMaxCharLimit($a_input['max_char_limit']);


method -> getAssignmentValues()

	if($this->assignment->getMinCharLimit())
	{
		$values['limit_characters'] = 1;
		$values['min_char_limit'] = $this->assignment->getMinCharLimit();
	}
	if($this->assignment->getMaxCharLimit())
	{
		$values['limit_characters'] = 1;
		$values['max_char_limit'] = $this->assignment->getMaxCharLimit();
	}


#### MODEL class(admin - creation/configuration)

Getting exercise assignments as an example (Modules/Exercise/classes/class.ilExAssignment.php)

properties

	protected $min_char_limit;
	protected $max_char_limit;

method -> initFromDB()

	...
	$this->setMinCharLimit($a_set["min_char_limit"]);
	$this->setMaxCharLimit($a_set["max_char_limit"]);

method -> save()

	$ilDB->insert("[table_name", array(...
		"min_char_limit" => array("integer", $this->getMinCharLimit()),
		"max_char_limit" => array("integer", $this->getMaxCharLimit())

method -> update()

	$ilDB->update("[table_name",array(...
		"min_char_limit" => array("integer", $this->getMinCharLimit()),
		"max_char_limit" => array("integer", $this->getMaxCharLimit())


setters and getters

	/**
	* Set limit minimum characters
	*
	* @param	int	minim limit
	*/
	function setMinCharLimit($a_val)
	{
		$this->min_char_limit = $a_val;
	}

	/**
	* Get limit minimum characters
	*
	* @return	int minimum limit
	*/
	function getMinCharLimit()
	{
		return $this->min_char_limit;
	}

	/**
	* Set limit maximum characters
	* @param int max limit
	*/
	function setMaxCharLimit($a_val)
	{
		$this->max_char_limit = $a_val;
	}

	/**
	* get limit maximum characters
	* return int max limit
	*/
	function getMaxCharLimit()
	{
		return $this->max_char_limit;
	}


#### Public user part. (users doing exercises etc.)

getting exercise submission as an example (Modules/Exercise/classes/class.ilExSubmissionTextGUI.php)

Init the form - method -> initAssignmentTextForm()
Set max and min limit values
add a setInfo element with the limits explanation.

	...
	if(!$a_read_only)
	{...

$text->setMaxNumOfChars($this->assignment->getMaxCharLimit());
$text->setMinNumOfChars($this->assignment->getMinCharLimit());

	if ($text->isCharLimited())
	{
	$char_msg = $lng->txt("exc_min_char_limit").": ".$this->assignment->getMinCharLimit().
	" ".$lng->txt("exc_max_char_limit").": ".$this->assignment->getMaxCharLimit();

	$text->setInfo($char_msg);
	}...

if you want to remove the "HTML" button in your TinyMCE, just add "code" in this array.

	$text->disableButtons(array(
	'charmap',
	'undo',
	'redo',
	'justifyleft',
	'justifycenter',
	'justifyright',
	'justifyfull',
	'anchor',
	'fullscreen',
	'cut',
	'copy',
	'paste',
	'pastetext',
	'code',
	// 'formatselect' #13234
	));
