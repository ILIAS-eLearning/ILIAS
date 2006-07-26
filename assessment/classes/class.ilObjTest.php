<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilObjTest
*
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package assessment
*/

include_once "./classes/class.ilObject.php";
include_once "./assessment/classes/inc.AssessmentConstants.php";

class ilObjTest extends ilObject
{
/**
* The database id of the additional test data dataset
*
* The database id of the additional test data dataset
*
* @var integer
*/
  var $test_id;

/**
* Defines if the test will be placed on users personal desktops
*
* Defines if the test will be placed on users personal desktops
*
* @var integer
*/
	var $invitation = INVITATION_OFF;


/**
* Contains the name of the author
*
* A text representation of the authors name. The name of the author must
* not necessary be the name of the owner.
*
* @var string
*/
  var $author;

/**
* Contains the metadata of the test
*
* A reference to an IMS compatible matadata set
*
* @var object
*/
  var $metadata;

/**
* Contains the test questions
*
* An array which contains all the test questions
*
* @var array
*/
  var $questions;

/**
* A textual introduction for the test
*
* An introduction text to give users more information
* on the test.
*
* @var string
*/
  var $introduction;

/**
* Defines the mark schema
*
* Defines the mark schema
*
* @var object
*/
  var $mark_schema;

/**
* Defines the sequence settings for the test user
* 
* Defines the sequence settings for the test user. There are two values:
* TEST_FIXED_SEQUENCE (=0) and TEST_POSTPONE (=1). The default value is
* TEST_FIXED_SEQUENCE.
*
* @var integer
*/
  var $sequence_settings;
  
/**
* Defines the score reporting for the test
* 
* Defines the score reporting for the test. There are two values:
* REPORT_AFTER_QUESTION (=0), REPORT_AFTER_TEST (=1). The default
* value is REPORT_AFTER_QUESTION. If the score reporting is set to
* REPORT_AFTER_TEST, it is also possible to use the $reporting_date
* attribute to set a time/date for the earliest reporting time.
*
* @var integer
*/
  var $score_reporting;

/**
* A time/date value to set the earliest reporting time for the test score
* 
* A time/date value to set the earliest reporting time for the test score.
* If you set this attribute, the sequence settings will be set to REPORT_AFTER_TEST
* automatically. If $reporting_date is not set, the user will get a direct feedback.
* The reporting date is given in database TIMESTAMP notation (yyyymmddhhmmss).
*
* @var string
*/
  var $reporting_date;

/**
* Contains the evaluation data settings the tutor defines for the user
* 
* Contains the evaluation data settings the tutor defines for the user
*
* @var object
*/
  var $evaluation_data;

/**
* The test type
* 
* The test type
*
* @var integer
*/
  var $test_type;

/**
* Number of tries the user is allowed to do
* 
* Number of tries the user is allowed to do. If set to 0, the user has
* infinite tries.
*
* @var integer
*/
  var $nr_of_tries;

/**
* Tells ILIAS to hide the previous results of a learner in a later test pass
* 
* Tells ILIAS to hide the previous results of a learner in a later test pass
* The default is 0 which shows the previous results in the next pass.
*
* @var integer
*/
	var $hide_previous_results;

/**
* Tells ILIAS to hide the maximum points of a question in the question title
* 
* Tells ILIAS to hide the maximum points of a question in the question title
*
* @var integer
*/
  var $hide_title_points;

/**
* The maximum processing time as hh:mm:ss string
* 
* The maximum processing time as hh:mm:ss string the user is allowed to do.
*
* @var integer
*/
  var $processing_time;
	
/**
* The state of the processing time
* 
* Contains 0 if the processing time is disabled, 1 if the processing time is enabled
*
* @var integer
*/
	var $enable_processing_time;

/**
* The starting time of the test
* 
* The starting time in database timestamp format which defines the earliest starting time for the test
*
* @var string
*/
  var $starting_time;
  
/**
* The ending time of the test
* 
* The ending time in database timestamp format which defines the latest ending time for the test
*
* @var string
*/
  var $ending_time;

/**
* Indicates if ECTS grades will be used
* 
* Indicates if ECTS grades will be used
*
* @var integer
*/
  var $ects_output;

/**
* Contains the percentage of maximum points a failed user needs to get the FX ECTS grade
* 
* Contains the percentage of maximum points a failed user needs to get the FX ECTS grade
*
* @var float
*/
  var $ects_fx;

/**
* An array containing the different types of assessment tests
*
* The array contains the type strings of the test types
* which has been retrieved from the database. The array keys
* are identical with the database primary keys of the test
* type strings.
*
* @var array
*/
	var $test_types;

/**
* The percentiles of the ECTS grades for this test
* 
* The percentiles of the ECTS grades for this test
*
* @var array
*/
  var $ects_grades;

/**
* Indicates if the questions of the test are generated by a random generator
*
* Indicates if the questions of the test are generated by a random generator.
* If $random_test is 0, the questions are generated a the conventional way,
* if $random_test is 1 a random generator is used.
*
* @var integer
*/
	var $random_test;

/**
* Determines the number of questions which should be taken for a random test
*
* Determines the number of questions which should be taken for a random test
*
* @var integer
*/
	var $random_question_count;

/**
* Indicates if the points for answers are counted for partial solutions
* or only for correct solutions
*
* @var integer
*/
	var $count_system;
	
/**
* Indicates if the points unchecked multiple choice questions are given or not
*
* @var integer
*/
	var $mc_scoring;

/**
* Defines which pass should be used for scoring
*
* @var integer
*/
	var $pass_scoring;

/**
* Indicates if the questions in a test are shuffled before
* a user accesses the test
*
* @var boolean
*/
	var $shuffle_questions;

/**
* Determines wheather or not the solution details of the answers
* should be shown to the users
*
* @var boolean
*/
	var $show_solution_details;

/**
* Determines wheather or not a question summary is shown to the users
*
* @var boolean
*/
	var $show_summary;

/**
* Determines wheather or not the solution printview of the answers
* should be shown to the users
*
* @var boolean
*/
	var $show_solution_printview;

/**
* Determines if the score of every question should be cut at 0 points or the score of the complete test
*
* @var boolean
*/
	var $score_cutting;

/**
* Password access to enter the test
*
* @var string
*/
	var $password;

/**
* number of allowed users for the test
*
* @var int
*/
	var $allowedUsers;

/**
* inactivity time gap of the allowed users to let new users into the test
*
* @var int
*/
	var $allowedUsersTimeGap;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjTest($a_id = 0,$a_call_by_reference = true)
	{
		global $ilUser;
		$this->type = "tst";
		include_once "./assessment/classes/class.assMarkSchema.php";
		$this->mark_schema = new ASS_MarkSchema();
		//$this->ilObject($a_id, $a_call_by_reference);
		$this->retrieveTestTypes();
		$this->test_id = -1;
		$this->author = $ilUser->fullname;
		$this->introduction = "";
		$this->questions = array();
		$this->sequence_settings = TEST_FIXED_SEQUENCE;
		$this->score_reporting = REPORT_AFTER_TEST;
		$this->reporting_date = "";
		$this->nr_of_tries = 0;
		$this->hide_previous_results = 0;
		$this->hide_title_points = 0;
		$this->starting_time = "";
		$this->ending_time = "";
		$this->processing_time = "00:00:00";
		$this->enable_processing_time = "0";
		$this->test_type = TYPE_ASSESSMENT;
		$this->ects_output = 0;
		$this->ects_fx = "";
		$this->random_test = 0;
		$this->shuffle_questions = FALSE;
		$this->show_solution_details = TRUE;
		$this->show_summary = FALSE;
		$this->show_solution_printview = FALSE;
		$this->random_question_count = "";
		$this->count_system = COUNT_PARTIAL_SOLUTIONS;
		$this->mc_scoring = SCORE_ZERO_POINTS_WHEN_UNANSWERED;
		$this->score_cutting = SCORE_CUT_QUESTION;
		$this->pass_scoring = SCORE_LAST_PASS;
		$this->password = "";
		$this->allowedUsers = "";
		$this->allowedUsersTimeGap = "";
		global $lng;
		$lng->loadLanguageModule("assessment");
		$this->mark_schema->createSimpleSchema($lng->txt("failed_short"), $lng->txt("failed_official"), 0, 0, $lng->txt("passed_short"), $lng->txt("passed_official"), 50, 1);
		$this->ects_grades = array(
			"A" => 90,
			"B" => 65,
			"C" => 35,
			"D" => 10,
			"E" => 0
		);
		$this->ilObject($a_id, $a_call_by_reference);
	}

	/**
	* create test object
	*/
	function create($a_upload = false)
	{
		parent::create();
		
		// meta data will be created by
		// import parser
		if (!$a_upload)
		{
			$this->createMetaData();
		}
	}

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update()
	{
		$this->updateMetaData();
		if (!parent::update())
		{
			return false;
		}

		// put here object specific stuff
		
		return true;
	}
	
/**
* Creates a database reference id for the object
* 
* Creates a database reference id for the object (saves the object 
* to the database and creates a reference id in the database)
*
* @access public
*/
	function createReference() {
		$result = parent::createReference();
		$this->saveToDb();
		return $result;
	}
	
/**
	* read object data from db into object
	* @param	boolean
	* @access	public
	*/
	function read($a_force_db = false)
	{
		parent::read($a_force_db);
		$this->loadFromDb();
	}

/**
	* copy all entries of your object.
	*
	* @access	public
	* @param	integer	ref_id of parent object
	* @return	integer	new ref id
	*/
	function ilClone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent ilClone function first!!
		$new_ref_id = parent::ilClone($a_parent_ref);
		
		// get object instance of ilCloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");		

		// always destroy objects in ilClone method because ilClone() is recursive and creates instances for each object in subtree!
		//unset($newObj);

		// ... and finally always return new reference ID!!
		return $new_ref_id;
	}

	/**
	* delete object and all related data
	*
	* @access	public
	* @return	boolean	true if all object data were removed; false if only a references were removed
	*/
	function delete()
	{
		// always call parent delete function first!!
		if (!parent::delete())
		{
			return false;
		}

		// delet meta data
		$this->deleteMetaData();

		//put here your module specific stuff
		$this->deleteTest();

		return true;
	}

/**
* Deletes the test and all related objects, files and database entries
*
* Deletes the test and all related objects, files and database entries
*
* @access	public
*/
	function deleteTest()
	{
		global $ilDB;
		
		$query = sprintf("SELECT active_id FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		$active_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($active_array, $row["active_id"]);
		}

		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);

		if (count($active_array))
		{
			foreach ($active_array as $active_id)
			{
				$query = sprintf("DELETE FROM tst_times WHERE active_fi = %s",
					$ilDB->quote($active_id)
				);
				$result = $ilDB->query($query);
			}
		}

		$query = sprintf("DELETE FROM tst_mark WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);

		$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->removeQuestion($row->question_fi);
		}

		$query = sprintf("DELETE FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);

		$query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);

		$query = sprintf("DELETE FROM tst_test_random_question USING tst_test_random_question, tst_active WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_test_random_question.active_fi",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);

		$this->removeAllTestEditings();

		$query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);

		// delete export files
		include_once "./classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		$directory = $tst_data_dir."/tst_".$this->getId();
		if (is_dir($directory))
		{
			$directory = escapeshellarg($directory);
			exec("rm -rf $directory");
		}
		include_once("./content/classes/Media/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->getId());
		// remaining usages are not in text anymore -> delete them
		// and media objects (note: delete method of ilObjMediaObject
		// checks whether object is used in another context; if yes,
		// the object is not deleted!)
		foreach($mobs as $mob)
		{
			ilObjMediaObject::_removeUsage($mob, "tst:html", $this->getId());
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->delete();
		}
	}

	/**
	* init default roles settings
	*
	* If your module does not require any default roles, delete this method
	* (For an example how this method is used, look at ilObjForum)
	*
	* @access	public
	* @return	array	object IDs of created local roles.
	*/
	function initDefaultRoles()
	{
		global $rbacadmin;
		
		// create a local role folder
		//$rfoldObj = $this->createRoleFolder("Local roles","Role Folder of forum obj_no.".$this->getId());

		// create moderator role and assign role to rolefolder...
		//$roleObj = $rfoldObj->createRole("Moderator","Moderator of forum obj_no.".$this->getId());
		//$roles[] = $roleObj->getId();

		//unset($rfoldObj);
		//unset($roleObj);

		return $roles ? $roles : array();
	}

	/**
	* notifys an object about an event occured
	* Based on the event happend, each object may decide how it reacts.
	* 
	* If you are not required to handle any events related to your module, just delete this method.
	* (For an example how this method is used, look at ilObjGroup)
	* 
	* @access	public
	* @param	string	event
	* @param	integer	reference id of object where the event occured
	* @param	array	passes optional parameters if required
	* @return	boolean
	*/
	function notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params = 0)
	{
		global $tree;
		
		switch ($a_event)
		{
			case "link":
				
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by link event. Objects linked into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "cut":
				
				//echo "Module name ".$this->getRefId()." triggered by cut event. Objects are removed from target object ref_id: ".$a_ref_id;
				//exit;
				break;
				
			case "copy":
			
				//var_dump("<pre>",$a_params,"</pre>");
				//echo "Module name ".$this->getRefId()." triggered by copy event. Objects are copied into target object ref_id: ".$a_ref_id;
				//exit;
				break;

			case "paste":

				//echo "Module name ".$this->getRefId()." triggered by paste (cut) event. Objects are pasted into target object ref_id: ".$a_ref_id;
				//exit;
				break;
			
			case "new":
				
				//echo "Module name ".$this->getRefId()." triggered by paste (new) event. Objects are applied to target object ref_id: ".$a_ref_id;
				//exit;
				break;
		}
		
		// At the beginning of the recursive process it avoids second call of the notify function with the same parameter
		if ($a_node_id==$_GET["ref_id"])
		{	
			$parent_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_node_id);
			$parent_type = $parent_obj->getType();
			if($parent_type == $this->getType())
			{
				$a_node_id = (int) $tree->getParentId($a_node_id);
			}
		}
		
		parent::notify($a_event,$a_ref_id,$a_parent_non_rbac_id,$a_node_id,$a_params);
	}
	
	/**
	* creates data directory for export files
	* (data_dir/tst_data/tst_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		include_once "./classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);
		if(!is_writable($tst_data_dir))
		{
			$this->ilias->raiseError("Test Data Directory (".$tst_data_dir
				.") not writeable.",$this->ilias->error_obj->MESSAGE);
		}
		
		// create learning module directory (data_dir/lm_data/lm_<id>)
		$tst_dir = $tst_data_dir."/tst_".$this->getId();
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$this->ilias->raiseError("Creation of Test Directory failed.",$this->ilias->error_obj->MESSAGE);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		$export_dir = $tst_dir."/export";
		ilUtil::makeDir($export_dir);
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->MESSAGE);
		}
	}

/**
* Get the location of the export directory for the test
* 
* Get the location of the export directory for the test
*
* @access	public
*/
	function getExportDirectory()
	{
		include_once "./classes/class.ilUtil.php";
		$export_dir = ilUtil::getDataDir()."/tst_data"."/tst_".$this->getId()."/export";
		return $export_dir;
	}
	
/**
* Get a list of the already exported files in the export directory
* 
* Get a list of the already exported files in the export directory
*
* @return array A list of file names
* @access	public
*/
	function getExportFiles($dir)
	{
		// quit if import dir not available
		if (!@is_dir($dir) or
			!is_writeable($dir))
		{
			return array();
		}

		// open directory
		$dir = dir($dir);

		// initialize array
		$file = array();

		// get files and save the in the array
		while ($entry = $dir->read())
		{
			if ($entry != "." and
				$entry != ".." and
				//substr($entry, -4) == ".zip" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(test(__results)?__)*[0-9]+\.[a-z]{1,3}\$", $entry))
			{
				$file[] = $entry;
			}
		}

		// close import directory
		$dir->close();

		// sort files
		sort ($file);
		reset ($file);

		return $file;
	}

	
	/**
	* creates data directory for import files
	* (data_dir/tst_data/tst_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function _createImportDirectory()
	{
		global $ilias;
		
		include_once "./classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);
		
		if(!is_writable($tst_data_dir))
		{
			$ilias->raiseError("Test data directory (".$tst_data_dir
				.") not writeable.",$ilias->error_obj->FATAL);
		}

		// create test directory (data_dir/tst_data/tst_import)
		$tst_dir = $tst_data_dir."/tst_import";
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$ilias->raiseError("Creation of test import directory failed.",$ilias->error_obj->FATAL);
		}
	}

/**
* Get the import directory location of the test
* 
* Get the import directory location of the test
*
* @return string The location of the import directory or false if the directory doesn't exist
* @access	public
*/
	function _getImportDirectory()
	{
		include_once "./classes/class.ilUtil.php";
		$import_dir = ilUtil::getDataDir()."/tst_data/tst_import";
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}

	/**
	* creates data directory for import files
	* (data_dir/tst_data/tst_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createImportDirectory()
	{
		include_once "./classes/class.ilUtil.php";
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);
		
		if(!is_writable($tst_data_dir))
		{
			$this->ilias->raiseError("Test Data Directory (".$tst_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create test directory (data_dir/tst_data/tst_import)
		$tst_dir = $tst_data_dir."/tst_import";
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$ilias->raiseError("Creation of test import directory failed.",$ilias->error_obj->FATAL);
		}
	}

/**
* Get the import directory location of the test
* 
* Get the import directory location of the test
*
* @return string The location of the import directory or false if the directory doesn't exist
* @access	public
*/
	function getImportDirectory()
	{
		include_once "./classes/class.ilUtil.php";
		$import_dir = ilUtil::getDataDir()."/tst_data/tst_import";
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}

	
	/**
	* Retrieves the test types from the database
	*
	* Retrieves the test types from the database and sets the
	* test_types array to the corresponding values.
	*
	* @access private
	* @see $test_types
	*/
	function retrieveTestTypes()
	{
		global $ilDB;

		$this->test_types = array();
		$query = "SELECT * FROM tst_test_type ORDER BY test_type_id";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->test_types[$row->test_type_id] = $row->type_tag;
		}
	}
	
/**
* Returns TRUE if the test title exists in the database
* 
* Returns TRUE if the test title exists in the database
*
* @param string $title The title of the test
* @return boolean The result of the title check
* @access public
*/
  function testTitleExists($title) 
	{
		global $ilDB;
		
    $query = sprintf("SELECT * FROM object_data WHERE title = %s AND type = %s",
      $ilDB->quote($title),
			$ilDB->quote("tst")
    );
    $result = $ilDB->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      if ($result->numRows() == 1) {
        return TRUE;
      }
    }
    return FALSE;
  }
  
/**
* Duplicates the ilObjTest object
* 
* Duplicates the ilObjTest object
*
* @access public
*/
  function duplicate() 
	{
		global $ilDB;
		
    $clone = $this;
    $clone->setId(-1);
    $counter = 2;
    while ($this->testTitleExists($this->get_title() . " ($counter)")) {
      $counter++;
    }
    $clone->setTitle($this->get_title() . " ($counter)");
    $clone->setOwner($this->ilias->account->id);
    $clone->setAuthor($this->ilias->account->fullname);
    $clone->saveToDb($ilDB);
    // Duplicate questions
    $query = sprintf("SELECT * FROM tst_test_question WHERE test_fi = %s",
      $ilDB->quote($this->getId())
    );
    $result = $ilDB->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      $query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
        $ilDB->quote($clone->getId()),
        $ilDB->quote($data->question_fi),
        $ilDB->quote($data->sequence)
      );
      $insert_result = $ilDB->query($query);
    }
  }
  
/**
* Returns true, if a test is complete for use
*
* Returns true, if a test is complete for use
*
* @return boolean True, if the test is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if (($this->getTitle()) and ($this->author) and (count($this->mark_schema->mark_steps)) and (count($this->questions)))
		{
			return true;
		} 
			else 
		{
			if ($this->isRandomTest())
			{
				$arr = $this->getRandomQuestionpools();
				if (count($arr) && ($this->getRandomQuestionCount() > 0))
				{
					return true;
				}
				$count = 0;
				foreach ($arr as $array)
				{
					$count += $array["count"];
				}
				if ($count)
				{
					return true;
				}
			}
			return false;
		}
	}

/**
* Returns true, if a test is complete for use
*
* Returns true, if a test is complete for use
*
* @return boolean True, if the test is complete for use, otherwise false
* @access public
*/
	function _isComplete($obj_id)
	{
		$test = new ilObjTest($obj_id, false);
		$test->loadFromDb();
		return $test->isComplete();
	}

/**
* Saves the ECTS status (output of ECTS grades in a test) to the database
* 
* Saves the ECTS status (output of ECTS grades in a test) to the database
*
* @access public
*/
	function saveECTSStatus($ects_output = 0, $fx_support = "", $ects_a = 90, $ects_b = 65, $ects_c = 35, $ects_d = 10, $ects_e = 0) 
	{
    global $ilDB;
    if ($this->test_id > 0) {
			$fx_support = preg_replace("/,/", ".", $fx_support);
			if (preg_match("/\d+/", $fx_support))
			{
				$fx_support = $fx_support;
			}
			else
			{
				$fx_support = "NULL";
			}
      $query = sprintf("UPDATE tst_tests SET ects_output = %s, ects_a = %s, ects_b = %s, ects_c = %s, ects_d = %s, ects_e = %s, ects_fx = %s WHERE test_id = %s",
				$ilDB->quote("$ects_output"),
				$ilDB->quote($ects_a . ""),
				$ilDB->quote($ects_b . ""),
				$ilDB->quote($ects_c . ""),
				$ilDB->quote($ects_d . ""),
				$ilDB->quote($ects_e . ""),
        $fx_support,
				$this->getTestId()
      );
      $result = $ilDB->query($query);
			$this->ects_output = $ects_output;
			$this->ects_fx = $fx_support;
		}
	}

/**
* Checks if the test is complete and saves the status in the database
* 
* Checks if the test is complete and saves the status in the database
*
* @access public
*/
	function saveCompleteStatus() 
	{
    global $ilDB;
		
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
    if ($this->test_id > 0) {
      $query = sprintf("UPDATE tst_tests SET complete = %s WHERE test_id = %s",
				$ilDB->quote("$complete"),
        $ilDB->quote($this->test_id)
      );
      $result = $ilDB->query($query);
		}
	}

/**
* Saves a ilObjTest object to a database
* 
* Saves a ilObjTest object to a database (experimental)
*
* @param object $db A pear DB object
* @access public
*/
  function saveToDb($properties_only = FALSE)
  {
    global $ilDB;
		
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
		$ects_fx = "NULL";
		if (preg_match("/\d+/", $this->ects_fx))
		{
			$ects_fx = $this->ects_fx;
		}
		$random_question_count = "NULL";
		if ($this->random_question_count > 0)
		{
			$random_question_count = $ilDB->quote($this->random_question_count . "");
		}
		$shuffle_questions = 0;
		if ($this->getShuffleQuestions())
		{
			$shuffle_questions = 1;
		}
		$show_solution_details = 0;
		if ($this->getShowSolutionDetails())
		{
			$show_solution_details = 1;
		}
		$show_summary = 0;
		if ($this->getShowSummary())
		{
			$show_summary = 1;
		}
		$show_solution_printview = 0;
		if ($this->getShowSolutionPrintview())
		{
			$show_solution_printview = 1;
		}
		$allowedUsers = $this->getAllowedUsers();
		if ($allowedUsers == 0)
		{
			$allowedUsers = "NULL";
		}
		else
		{
			$allowedUsers = $ilDB->quote($allowedUsers);
		}
		$allowedUsersTimeGap = $this->getAllowedUsersTimeGap();
		if ($allowedUsersTimeGap == 0)
		{
			$allowedUsersTimeGap = "NULL";
		}
		else
		{
			$allowedUsersTimeGap = $ilDB->quote($allowedUsersTimeGap);
		}

		// cleanup RTE images which are not inserted into the question text
		include_once("./Services/RTE/classes/class.ilRTE.php");
		ilRTE::_cleanupMediaObjectUsage($this->introduction, $this->getType() . ":html",
			$this->getId());

		include_once ("./classes/class.ilObjAssessmentFolder.php");
    if ($this->test_id == -1) 
		{
      // Create new dataset
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO tst_tests (test_id, obj_fi, author, test_type_fi, introduction, sequence_settings, score_reporting, nr_of_tries, hide_previous_results, hide_title_points, processing_time, enable_processing_time, reporting_date, starting_time, ending_time, complete, ects_output, ects_a, ects_b, ects_c, ects_d, ects_e, ects_fx, random_test, random_question_count, count_system, mc_scoring, score_cutting, pass_scoring, shuffle_questions, show_solution_details, show_summary, show_solution_printview, password, allowedUsers, allowedUsersTimeGap, created, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($this->getId() . ""),
				$ilDB->quote($this->author . ""),
				$ilDB->quote($this->test_type . ""),
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->introduction, 0)),
				$ilDB->quote($this->sequence_settings . ""),
				$ilDB->quote($this->score_reporting . ""),
				$ilDB->quote(sprintf("%d", $this->nr_of_tries) . ""),
				$ilDB->quote(sprintf("%d", $this->getHidePreviousResults() . "")),
				$ilDB->quote(sprintf("%d", $this->getHideTitlePoints() . "")),
				$ilDB->quote($this->processing_time . ""),
				$ilDB->quote("$this->enable_processing_time"),
				$ilDB->quote($this->reporting_date . ""),
				$ilDB->quote($this->starting_time . ""),
				$ilDB->quote($this->ending_time . ""),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->ects_output . ""),
				$ilDB->quote($this->ects_grades["A"] . ""),
				$ilDB->quote($this->ects_grades["B"] . ""),
				$ilDB->quote($this->ects_grades["C"] . ""),
				$ilDB->quote($this->ects_grades["D"] . ""),
				$ilDB->quote($this->ects_grades["E"] . ""),
				$ects_fx,
				$ilDB->quote(sprintf("%d", $this->random_test) . ""),
				$random_question_count,
				$ilDB->quote($this->count_system . ""),
				$ilDB->quote($this->mc_scoring . ""),
				$ilDB->quote($this->getScoreCutting() . ""),
				$ilDB->quote($this->getPassScoring() . ""),
				$ilDB->quote($shuffle_questions . ""),
				$ilDB->quote($show_solution_details . ""),
				$ilDB->quote($show_summary . ""),
				$ilDB->quote($show_solution_printview . ""),
				$ilDB->quote($this->getPassword() . ""),
				$allowedUsers,
				$allowedUsersTimeGap,
				$ilDB->quote($created)
      );
      
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_create_new_test", ilObjAssessmentFolder::_getLogLanguage()));
			}
      $result = $ilDB->query($query);
      if ($result == DB_OK) {
        $this->test_id = $ilDB->getLastInsertId();
      }
    } 
		else 
		{
      // Modify existing dataset
			$oldrow = array();
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
	        $ilDB->quote($this->test_id)
				);
				$result = $ilDB->query($query);
				if ($result->numRows() == 1)
				{
					$oldrow = $result->fetchRow(DB_FETCHMODE_ASSOC);
				}
			}
      $query = sprintf("UPDATE tst_tests SET author = %s, test_type_fi = %s, introduction = %s, sequence_settings = %s, score_reporting = %s, nr_of_tries = %s, hide_previous_results = %s, hide_title_points = %s, processing_time = %s, enable_processing_time = %s, reporting_date = %s, starting_time = %s, ending_time = %s, ects_output = %s, ects_a = %s, ects_b = %s, ects_c = %s, ects_d = %s, ects_e = %s, ects_fx = %s, random_test = %s, complete = %s, count_system = %s, mc_scoring = %s, score_cutting = %s, pass_scoring = %s, shuffle_questions = %s, show_solution_details = %s, show_summary = %s, show_solution_printview = %s, password = %s, allowedUsers = %s, allowedUsersTimeGap = %s WHERE test_id = %s",
        $ilDB->quote($this->author . ""), 
        $ilDB->quote($this->test_type . ""), 
				$ilDB->quote(ilRTE::_replaceMediaObjectImageSrc($this->introduction, 0)),
        $ilDB->quote($this->sequence_settings . ""), 
        $ilDB->quote($this->score_reporting . ""), 
        $ilDB->quote(sprintf("%d", $this->nr_of_tries) . ""),
				$ilDB->quote(sprintf("%d", $this->getHidePreviousResults() . "")),
				$ilDB->quote(sprintf("%d", $this->getHideTitlePoints() . "")),
        $ilDB->quote($this->processing_time . ""),
				$ilDB->quote("$this->enable_processing_time"),
        $ilDB->quote($this->reporting_date . ""), 
        $ilDB->quote($this->starting_time . ""), 
        $ilDB->quote($this->ending_time . ""), 
				$ilDB->quote($this->ects_output . ""),
				$ilDB->quote($this->ects_grades["A"] . ""),
				$ilDB->quote($this->ects_grades["B"] . ""),
				$ilDB->quote($this->ects_grades["C"] . ""),
				$ilDB->quote($this->ects_grades["D"] . ""),
				$ilDB->quote($this->ects_grades["E"] . ""),
				$ects_fx,
				$ilDB->quote(sprintf("%d", $this->random_test) . ""),
				$ilDB->quote("$complete"),
				$ilDB->quote($this->count_system . ""),
				$ilDB->quote($this->mc_scoring . ""),
				$ilDB->quote($this->getScoreCutting() . ""),
				$ilDB->quote($this->getPassScoring() . ""),
				$ilDB->quote($shuffle_questions . ""),
				$ilDB->quote($show_solution_details . ""),
				$ilDB->quote($show_summary . ""),
				$ilDB->quote($show_solution_printview . ""),
				$ilDB->quote($this->getPassword() . ""),
				$allowedUsers,
				$allowedUsersTimeGap,
        $ilDB->quote($this->test_id)
      );
	    $result = $ilDB->query($query);
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
	        $ilDB->quote($this->test_id)
				);
				$logresult = $ilDB->query($query);
				$newrow = array();
				if ($logresult->numRows() == 1)
				{
					$newrow = $logresult->fetchRow(DB_FETCHMODE_ASSOC);
				}
				$changed_fields = array();
				foreach ($oldrow as $key => $value)
				{
					if (strcmp($oldrow[$key], $newrow[$key]) != 0)
					{
						array_push($changed_fields, "$key: " . $oldrow[$key] . " => " . $newrow[$key]);
					}
				}
				$changes = join($changed_fields, ", ");
				if (count($changed_fields) == 0)
				{
					$changes = $this->lng->txtlng("assessment", "log_no_test_fields_changed", ilObjAssessmentFolder::_getLogLanguage());
				}
				$this->logAction($this->lng->txtlng("assessment", "log_modified_test", ilObjAssessmentFolder::_getLogLanguage()) . " [".$changes."]");
			}
    }
		if (!$properties_only)
		{
			if ($result == DB_OK) {
				if (!$this->isRandomTest())
				{
					$this->saveQuestionsToDb();
				}
				$this->mark_schema->saveToDb($this->test_id);
			}
		}
  }

/**
* Saves the test questions to the database
*
* Saves the test questions to the database
*
* @access public
* @see $questions
*/
	function saveQuestionsToDb() 
	{
		global $ilDB;
		
		$oldquestions = array();
		include_once "./classes/class.ilObjAssessmentFolder.php";
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
				$ilDB->quote($this->getTestId())
			);
			$result = $ilDB->query($query);
			if ($result->numRows() > 0)
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($oldquestions, $row["question_fi"]);
				}
			}
		}
		
		// delete existing category relations
    $query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		// create new category relations
		foreach ($this->questions as $key => $value) {
			$query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$ilDB->quote($this->getTestId() . ""),
				$ilDB->quote($value . ""),
				$ilDB->quote($key . "")
			);
			$result = $ilDB->query($query);
		}
		include_once ("./classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
				$ilDB->quote($this->getTestId())
			);
			$result = $ilDB->query($query);
			$newquestions = array();
			if ($result->numRows() > 0)
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					array_push($newquestions, $row["question_fi"]);
				}
			}
			foreach ($oldquestions as $index => $question_id)
			{
				if (strcmp($newquestions[$index], $question_id) != 0)
				{
					$pos = array_search($question_id, $newquestions);
					if ($pos === FALSE)
					{
						$this->logAction($this->lng->txtlng("assessment", "log_question_removed", ilObjAssessmentFolder::_getLogLanguage()), $question_id);							
					}
					else
					{
						$this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($index+1) . " => " . ($pos+1), $question_id);
					}
				}
			}
			foreach ($newquestions as $index => $question_id)
			{
				if (array_search($question_id, $oldquestions) === FALSE)
				{
					$this->logAction($this->lng->txtlng("assessment", "log_question_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($index+1), $question_id);
				}
			}
		}
	}

/**
* Saves a random question to the database
*
* Saves a random question to the database
*
* @access public
* @see $questions
*/
	function saveRandomQuestion($question_id, $pass = NULL) 
	{
		global $ilUser;
		global $ilDB;
		
		if (is_null($pass)) $pass = 0;
		$active = $this->getActiveTestUser($ilUser->getId());
		$query = sprintf("SELECT test_random_question_id FROM tst_test_random_question WHERE active_fi = %s AND pass = %s",
			$ilDB->quote($active->active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);

		$duplicate_id = $this->getRandomQuestionDuplicate($question_id, $active->active_id); 
		if ($duplicate_id === FALSE)
		{
			$duplicate_id = $this->duplicateQuestionForTest($question_id);
		}

		$query = sprintf("INSERT INTO tst_test_random_question (test_random_question_id, active_fi, question_fi, sequence, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
			$ilDB->quote($active->active_id . ""),
			$ilDB->quote($duplicate_id . ""),
			$ilDB->quote(($result->numRows()+1) . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
/*		
		$query = sprintf("INSERT INTO tst_test_random_question (test_random_question_id, active_fi, question_fi, sequence, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
			$ilDB->quote($active->active_id . ""),
			$ilDB->quote($question_id . ""),
			$ilDB->quote(($result->numRows()+1) . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
*/
	}
	
/**
* Returns the question id of the duplicate of a question which is already in use in a random test
*
* Returns the question id of the duplicate of a question which is already in use in a random test
*
* @param integer $question_id Question ID of the original question
* @param integer $active_id Active ID of the user
* @return mixed The question ID of the duplicate or FALSE if no duplicate was found
* @access public
* @see $questions
*/
	function getRandomQuestionDuplicate($question_id, $active_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT qpl_questions.question_id FROM qpl_questions, tst_test_random_question WHERE qpl_questions.original_id = %s AND tst_test_random_question.question_fi = qpl_questions.question_id AND tst_test_random_question.active_fi = %s",
			$ilDB->quote($question_id . ""),
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		$num = $result->numRows();
		if ($num > 0)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["question_id"];
		}
		else
		{
			return FALSE;
		}
	}
	
/**
* Calculates the number of user results for a specific test pass
*
* Calculates the number of user results for a specific test pass
*
* @access private
*/
	function getNrOfResultsForPass($active_id, $pass)
	{
		global $ilDB;
		
		$query = sprintf("SELECT test_result_id FROM tst_test_result WHERE active_fi = %s AND pass = %s",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		return $result->numRows();
	}
	
/**
* Generates new random questions for the active user
*
* Generates new random questions for the active user
*
* @access private
* @see $questions
*/
	function generateRandomQuestions($pass = NULL)
	{
		global $ilUser;
		$active = $this->getActiveTestUser($ilUser->getId());
		$this->loadQuestions($active->active_id, $pass);
		if (count($this->questions) > 0)
		{
			// Something went wrong. Maybe the user pressed the start button twice
			// Questions already exist so there is no need to create new questions
			return;
		}
		if ($pass > 0)
		{
			if ($this->getNrOfResultsForPass($active->active_id, $pass - 1) == 0)
			{
				// This means that someone maybe reloaded the test submission page
				// If there are no existing results for the previous test, it makes
				// no sense to create a new set of random questions
				return;
			}
		}
		if (!is_object($active)) $this->setActiveTestUser();
		if ($this->getRandomQuestionCount() > 0)
		{
			$qpls =& $this->getRandomQuestionpools();
			$rndquestions = $this->randomSelectQuestions($this->getRandomQuestionCount(), 0, 1, $qpls, $pass);
			$allquestions = array();
			foreach ($rndquestions as $question_id)
			{
				array_push($allquestions, $question_id);
			}
			srand ((float)microtime()*1000000);
			shuffle($allquestions);
			foreach ($allquestions as $question_id)
			{
				$this->saveRandomQuestion($question_id, $pass);
			}
		}
		else
		{
			$qpls =& $this->getRandomQuestionpools();
			$allquestions = array();
			foreach ($qpls as $key => $value)
			{
				if ($value["count"] > 0)
				{
					$rndquestions = $this->randomSelectQuestions($value["count"], $value["qpl"], 1, $pass);
					foreach ($rndquestions as $question_id)
					{
						array_push($allquestions, $question_id);
					}
				}
			}
			srand ((float)microtime()*1000000);
			shuffle($allquestions);
			foreach ($allquestions as $question_id)
			{
				$this->saveRandomQuestion($question_id, $pass);
			}
		}
		if (!is_object($active))
		{
			$active = $this->getActiveTestUser($ilUser->getId());
			$this->addQuestionSequence($active->active_id);
		}
		return;
	}

	/**
	* Saves the total amount of a tests random questions to the database
	*
	* Saves the total amount of a tests random questions to the database
	*
	* @param integer $total_questions The amount of random questions
	* @access public
	*/
	function saveRandomQuestionCount($total_questions = "NULL")
	{
		global $ilDB;
		
		if (strcmp($total_questions, "NULL") != 0)
		{
			$this->setRandomQuestionCount($total_questions);
			$total_questions = $ilDB->quote($total_questions);
		}
		$query = sprintf("UPDATE tst_tests SET random_question_count = %s WHERE test_id = %s",
			$total_questions,
			$ilDB->quote($this->getTestId() . "")
		);
		$result = $ilDB->query($query);
		include_once ("./classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			if (strcmp($total_questions, "NULL") == 0) $total_questions = '0';
			$this->logAction(sprintf($this->lng->txtlng("assessment", "log_total_amount_of_questions", ilObjAssessmentFolder::_getLogLanguage()), $total_questions));
		}
	}

/**
* Saves the question pools used for a random test
*
* Saves the question pools used for a random test
*
* @param array $qpl_array An array containing the questionpool id's
* @access public
* @see $questions
*/
	function saveRandomQuestionpools($qpl_array) 
	{
		global $ilDB;
		
		include_once ("./classes/class.ilObjAssessmentFolder.php");
		// delete existing random questionpools
    $query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction($this->lng->txtlng("assessment", "log_random_question_pool_deleted", ilObjAssessmentFolder::_getLogLanguage()));
		}
		// create new random questionpools
		foreach ($qpl_array as $key => $value) {
			if ($value["qpl"] > -1)
			{
				include_once "./assessment/classes/class.ilObjQuestionPool.php";
				$count = ilObjQuestionPool::_getQuestionCount($value["qpl"]);
				if ($value["count"] > $count)
				{
					$value["count"] = $count;
				}
				$query = sprintf("INSERT INTO tst_test_random (test_random_id, test_fi, questionpool_fi, num_of_q, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
					$ilDB->quote($this->getTestId() . ""),
					$ilDB->quote($value["qpl"] . ""),
					$ilDB->quote(sprintf("%d", $value["count"]) . "")
				);
				$result = $ilDB->query($query);
				if (ilObjAssessmentFolder::_enabledAssessmentLogging())
				{
					$this->logAction(sprintf($this->lng->txtlng("assessment", "log_random_question_pool_added", ilObjAssessmentFolder::_getLogLanguage()), $value["title"] . " (" . $value["qpl"] . ")", $value["count"]));
				}
			}
		}
	}

/**
* Returns an array containing the random questionpools saved to the database
*
* Returns an array containing the random questionpools saved to the database
*
* @access public
* @return array All saved random questionpools
* @see $questions
*/
	function &getRandomQuestionpools() 
	{
		global $ilDB;
		
		$qpls = array();
		$counter = 0;
		$query = sprintf("SELECT * FROM tst_test_random WHERE test_fi = %s ORDER BY test_random_id",
			$ilDB->quote($this->getTestId() . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$qpls[$counter] = array(
					"index" => $counter,
					"count" => $row["num_of_q"],
					"qpl"   => $row["questionpool_fi"]
				);
				$counter++;
			}
		}
		return $qpls;
	}

	/**
	* Loads a ilObjTest object from a database
	*
	* Loads a ilObjTest object from a database
	*
	* @param object $db A pear DB object
	* @param integer $test_id A unique key which defines the test in the database
	* @access public
	*/
	function loadFromDb()
	{
		global $ilDB;
		
		$query = sprintf("SELECT * FROM tst_tests WHERE obj_fi = %s",
		$ilDB->quote($this->getId())
			);
		$result = $ilDB->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->test_id = $data->test_id;
				$this->author = $data->author;
				$this->test_type = $data->test_type_fi;
				include_once("./Services/RTE/classes/class.ilRTE.php");
				$this->introduction = ilRTE::_replaceMediaObjectImageSrc($data->introduction, 1);
				$this->sequence_settings = $data->sequence_settings;
				$this->score_reporting = $data->score_reporting;
				$this->nr_of_tries = $data->nr_of_tries;
				$this->setHidePreviousResults($data->hide_previous_results);
				$this->setHideTitlePoints($data->hide_title_points);
				$this->processing_time = $data->processing_time;
				$this->enable_processing_time = $data->enable_processing_time;
				$this->reporting_date = $data->reporting_date;
				$this->setShuffleQuestions($data->shuffle_questions);
				$this->setShowSolutionDetails($data->show_solution_details);
				$this->setShowSummary($data->show_summary);
				$this->setShowSolutionPrintview($data->show_solution_printview);
				$this->starting_time = $data->starting_time;
				$this->ending_time = $data->ending_time;
				$this->ects_output = $data->ects_output;
				$this->ects_grades = array(
					"A" => $data->ects_a,
					"B" => $data->ects_b,
					"C" => $data->ects_c,
					"D" => $data->ects_d,
					"E" => $data->ects_e
				);
				$this->ects_fx = $data->ects_fx;
				$this->random_test = $data->random_test;
				$this->random_question_count = $data->random_question_count;
				$this->mark_schema->flush();
				$this->mark_schema->loadFromDb($this->test_id);
				$this->count_system = $data->count_system;
				$this->mc_scoring = $data->mc_scoring;
				$this->setScoreCutting($data->score_cutting);
				$this->setPassword($data->password);
				$this->setAllowedUsers($data->allowedUsers);
				$this->setAllowedUsersTimeGap($data->allowedUsersTimeGap);
				$this->setPassScoring($data->pass_scoring);
				$this->loadQuestions();
			}
		}
	}

/**
* Load the test question id's from the database 
* 
* Load the test question id's from the database 
*
* @param integer $user_id The user id of the test user (necessary for random tests)
* @access	public
*/
	function loadQuestions($active_id = "", $pass = NULL) 
	{
		global $ilUser;
		global $ilDB;
		
		$this->questions = array();
		if (strcmp($active_id, "") == 0)
		{
			$active = $this->getActiveTestUser($ilUser->getId());
			$active_id = $active->active_id;
		}
		if ($this->isRandomTest())
		{
			if (is_null($pass))
			{
				if ($this->getTestType() == TYPE_VARYING_RANDOMTEST)
				{
					$pass = $this->_getPass($active_id);
				}
				else
				{
					// normal random questions are created only once, for pass 0
					$pass = 0;
				}
			}
			$query = sprintf("SELECT tst_test_random_question.* FROM tst_test_random_question, qpl_questions WHERE tst_test_random_question.active_fi = %s AND qpl_questions.question_id = tst_test_random_question.question_fi AND tst_test_random_question.pass = %s ORDER BY sequence",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . "")
			);
		}
		else
		{
			$query = sprintf("SELECT tst_test_question.* FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND qpl_questions.question_id = tst_test_question.question_fi ORDER BY sequence",
				$ilDB->quote($this->test_id . "")
			);
		}
		$result = $ilDB->query($query);
		$index = 1;
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			$this->questions[$index++] = $data->question_fi;
		}
	}

/**
* Sets the authors name
* 
* Sets the authors name of the ilObjTest object
*
* @param string $author A string containing the name of the test author
* @access public
* @see $author
*/
  function setAuthor($author = "") 
	{
    $this->author = $author;
  }

/**
* Sets the introduction
* 
* Sets the introduction text of the ilObjTest object
*
* @param string $introduction An introduction string for the test
* @access public
* @see $introduction
*/
  function setIntroduction($introduction = "") 
	{
    $this->introduction = $introduction;
  }

/**
* Gets the authors name
* 
* Gets the authors name of the ilObjTest object
*
* @return string The string containing the name of the test author
* @access public
* @see $author
*/
  function getAuthor() 
	{
    return $this->author;
  }

/**
* Gets the status of the $random_test attribute
* 
* Gets the status of the $random_test attribute
*
* @return integer The random test status. 0 = normal, 1 = questions are generated with random generator
* @access public
* @see $random_test
*/
  function isRandomTest() 
	{
    return $this->random_test;
  }

/**
* Gets the number of random questions used for a random test
* 
* Gets the number of random questions used for a random test
*
* @return integer The number of random questions
* @access public
* @see $random_question_count
*/
  function getRandomQuestionCount() 
	{
    return $this->random_question_count;
  }

/**
* Gets the introduction
* 
* Gets the introduction text of the ilObjTest object
*
* @return string The introduction text of the test
* @access public
* @see $introduction
*/
  function getIntroduction() 
	{
    return $this->introduction;
  }

/**
* Gets the database id of the additional test data
* 
* Gets the database id of the additional test data
*
* @return integer The database id of the additional test data
* @access public
* @see $test_id
*/
  function getTestId() 
	{
    return $this->test_id;
  }

/**
* Sets the sequence settings
*
* Sets the sequence settings of the ilObjTest object
*
* @param integer $sequence_settings The sequence settings
* @access public
* @see $sequence_settings
*/
  function setSequenceSettings($sequence_settings = 0) 
	{
    $this->sequence_settings = $sequence_settings;
  }

/**
* Sets the test type
* 
* Sets the type of the ilObjTest object
*
* @param integer $type The test type value
* @access public
* @see $type
*/
  function setTestType($type = TYPE_ASSESSMENT) 
  {
    $this->test_type = $type;
  }

/**
* Sets the score reporting
* 
* Sets the score reporting of the ilObjTest object
*
* @param integer $score_reporting The score reporting
* @access public
* @see $score_reporting
*/
  function setScoreReporting($score_reporting = 0) 
	{
    $this->score_reporting = $score_reporting;
  }

/**
* Sets the random test indicator
* 
* Sets the random test indicator
*
* @param integer $a_random_test The random test indicator (0 = no random test, 1 = random test)
* @access public
* @see $random_test
*/
  function setRandomTest($a_random_test = 0) 
	{
    $this->random_test = $a_random_test;
  }

/**
* Sets the random question count
* 
* Sets the random question count
*
* @param integer $a_random_question_count The random question count
* @access public
* @see $random_question_count
*/
  function setRandomQuestionCount($a_random_question_count = "") 
	{
    $this->random_question_count = $a_random_question_count;
  }

/**
* Sets the reporting date
* 
* Sets the reporting date of the ilObjTest object
*
* @param timestamp $reporting_date The date and time the score reporting is available
* @access public
* @see $reporting_date
*/
  function setReportingDate($reporting_date) 
  {
		if (!$reporting_date) 
		{
			$this->reporting_date = "";
			$this->ects_output = 0;
		}
		else 
		{
			$this->reporting_date = $reporting_date;
		}
  }

/**
* Gets the sequence settings
* 
* Gets the sequence settings of the ilObjTest object
*
* @return integer The sequence settings of the test
* @access public
* @see $sequence_settings
*/
  function getSequenceSettings() 
	{
    return $this->sequence_settings;
  }

/**
* Gets the score reporting
* 
* Gets the score reporting of the ilObjTest object
*
* @return integer The score reporting of the test
* @access public
* @see $score_reporting
*/
  function getScoreReporting() 
	{
    return $this->score_reporting;
  }

/**
* Gets the count system for the calculation of points
* 
* Gets the count system for the calculation of points
*
* @return integer The count system for the calculation of points
* @access public
* @see $count_system
*/
  function getCountSystem() 
	{
    return $this->count_system;
  }

/**
* Gets the count system for the calculation of points
* 
* Gets the count system for the calculation of points
*
* @return integer The count system for the calculation of points
* @access public
* @see $count_system
*/
  function _getCountSystem($active_id) 
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.count_system FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["count_system"];
		}
    return FALSE;
  }
	
/**
* Gets the scoring type for multiple choice questions
* 
* Gets the scoring type for multiple choice questions
*
* @return integer The scoring type for multiple choice questions
* @access public
* @see $mc_scoring
*/
  function getMCScoring() 
	{
    return $this->mc_scoring;
  }

/**
* Determines if the score of a question should be cut at 0 points or the score of the whole test
* 
* Determines if the score of a question should be cut at 0 points or the score of the whole test
*
* @return boolean The score cutting type. 0 for question cutting, 1 for test cutting
* @access public
* @see $score_cutting
*/
  function getScoreCutting() 
	{
    return $this->score_cutting;
  }

/**
* Returns the password for test access 
* 
* Returns the password for test access 
*
* @return striong  Password for test access
* @access public
* @see $password
*/
  function getPassword() 
	{
    return $this->password;
  }

/**
* Gets the pass scoring type
* 
* Gets the pass scoring type
*
* @return integer The pass scoring type
* @access public
* @see $pass_scoring
*/
  function getPassScoring() 
	{
    return $this->pass_scoring;
  }

/**
* Gets the pass scoring type
* 
* Gets the pass scoring type
*
* @return integer The pass scoring type
* @access public
* @see $pass_scoring
*/
  function _getPassScoring($active_id) 
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.pass_scoring FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["pass_scoring"];
		}
    return 0;
  }

/**
* Gets the scoring type for multiple choice questions
* 
* Gets the scoring type for multiple choice questions
*
* @return mixed The scoring type for multiple choice questions
* @access public
* @see $mc_scoring
*/
  function _getMCScoring($active_id) 
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.mc_scoring FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["mc_scoring"];
		}
    return FALSE;
  }

/**
* Determines if the score of a question should be cut at 0 points or the score of the whole test
* 
* Determines if the score of a question should be cut at 0 points or the score of the whole test
*
* @return boolean The score cutting type. 0 for question cutting, 1 for test cutting
* @access public
* @see $score_cutting
*/
  function _getScoreCutting($active_id) 
	{
		global $ilDB;
		$query = sprintf("SELECT tst_tests.score_cutting FROM tst_tests, tst_active WHERE tst_active.active_id = %s AND tst_tests.test_id = tst_active.test_fi",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["score_cutting"];
		}
    return FALSE;
  }

/**
* Gets the test type
* 
* Gets the test type
*
* @return integer The test type
* @access public
* @see $type
*/
  function getTestType() 
	{
    return $this->test_type;
  }

/**
* Gets the reporting date
* 
* Gets the reporting date of the ilObjTest object
*
* @return string The reporting date of the test of an empty string (=FALSE) if no reporting date is set
* @access public
* @see $reporting_date
*/
  function getReportingDate() 
	{
    return $this->reporting_date;
  }

/**
* Returns the nr of tries for the test
* 
* Returns the nr of tries for the test
*
* @return integer The maximum number of tries
* @access public
* @see $nr_of_tries
*/
  function getNrOfTries() 
	{
    return $this->nr_of_tries;
  }

/**
* Returns if the previous results should be hidden for a learner
* 
* Returns if the previous results should be hidden for a learner
*
* @return integer 1 if the previous results should be hidden, 0 otherwise
* @access public
* @see $hide_previous_results
*/
  function getHidePreviousResults() 
	{
    return $this->hide_previous_results;
  }

/**
* Returns true if the maximum points of a question should be hidden in the question title
* 
* Returns true if the maximum points of a question should be hidden in the question title
*
* @return integer 1 if the maximum points in the question title should be hidden
* @access public
* @see $hide_title_points
*/
  function getHideTitlePoints() 
	{
    return $this->hide_title_points;
  }

/**
* Returns true if the maximum points of a question should be hidden in the question title
* 
* Returns true if the maximum points of a question should be hidden in the question title
*
* @param integer The test id
* @return integer 1 if the maximum points in the question title should be hidden
* @access public
* @see $hide_title_points
*/
  function _getHideTitlePoints($active_id) 
	{
		global $ilDB;

		$query = sprintf("SELECT tst_tests.hide_title_points FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["hide_title_points"];
		}
		return 0;
  }

/**
* Returns if the previous results should be hidden for a learner
* 
* Returns if the previous results should be hidden for a learner
*
* @param integer $test_id The test id
* @param boolean $use_active_user_setting If true, the tst_hide_previous_results of the active user should be used as well
* @return integer 1 if the previous results should be hidden, 0 otherwise
* @access public
* @see $hide_previous_results
*/
  function _getHidePreviousResults($active_id, $user_active_user_setting = false) 
	{
		global $ilDB;
		global $ilUser;
		
		$user_hide_previous_results = 0;
		if ($user_active_user_setting)
		{
			if (array_key_exists("tst_hide_previous_results", $ilUser->prefs))
			{
				$user_hide_previous_results = $ilUser->prefs["tst_hide_previous_results"];
			}
		}
		$query = sprintf("SELECT tst_tests.hide_previous_results FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if ($row["hide_previous_results"] != 1)
			{
				return $row["hide_previous_results"] | $user_hide_previous_results;
			}
			else
			{
				return $row["hide_previous_results"];
			}
		}
		return 0;
  }

/**
* Returns the processing time for the test
* 
* Returns the processing time for the test
*
* @return integer The processing time for the test
* @access public
* @see $processing_time
*/
  function getProcessingTime() 
	{
    return $this->processing_time;
  }
	
/**
* Returns the processing time for the test in seconds
* 
* Returns the processing time for the test in seconds
*
* @return integer The processing time for the test in seconds
* @access public
* @see $processing_time
*/
	function getProcessingTimeInSeconds()
	{
		if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $this->getProcessingTime(), $matches))
		{
			return ($matches[1] * 3600) + ($matches[2] * 60) + $matches[3];
		}
			else
		{
			return 0;
		}
	}

/**
* Returns the state of the processing time (enabled/disabled)
* 
* Returns the state of the processing time (enabled/disabled)
*
* @return integer The processing time state (0 for disabled, 1 for enabled)
* @access public
* @see $processing_time
*/
  function getEnableProcessingTime() 
	{
    return $this->enable_processing_time;
  }

/**
* Returns the starting time of the test
* 
* Returns the starting time of the test
*
* @return string The starting time of the test
* @access public
* @see $starting_time
*/
  function getStartingTime() 
	{
    return $this->starting_time;
  }

/**
* Returns the ending time of the test
* 
* Returns the ending time of the test
*
* @return string The ending time of the test
* @access public
* @see $ending_time
*/
  function getEndingTime() 
	{
    return $this->ending_time;
  }

/**
* Sets the nr of tries for the test
* 
* Sets the nr of tries for the test
*
* @param integer $nr_of_tries The maximum number of tries for the test. 0 for infinite tries.
* @access public
* @see $nr_of_tries
*/
  function setNrOfTries($nr_of_tries = 0) 
	{
    $this->nr_of_tries = $nr_of_tries;
  }

/**
* Sets the status of the visibility of previous learner results
* 
* Sets the status of the visibility of previous learner results
**
* @param integer $hide_previous_results 1 if the previous results should be hidden.
* @access public
* @see $hide_previous_results
*/
  function setHidePreviousResults($hide_previous_results = 0) 
	{
		if ($hide_previous_results)
		{
			$this->hide_previous_results = 1;
		}
		else
		{
			$this->hide_previous_results = 0;
		}
  }

/**
* Sets the status of the visibility of the maximum points in the question title
* 
* Sets the status of the visibility of the maximum points in the question title
**
* @param integer $hide_title_points 1 if the maximum points should be hidden in the question title
* @access public
* @see $hide_title_points
*/
  function setHideTitlePoints($hide_title_points = 0) 
	{
		if ($hide_title_points)
		{
			$this->hide_title_points = 1;
		}
		else
		{
			$this->hide_title_points = 0;
		}
  }

/**
* Sets the processing time for the test
* 
* Sets the processing time for the test
*
* @param string $processing_time The maximum processing time for the test given in hh:mm:ss
* @access public
* @see $processing_time
*/
  function setProcessingTime($processing_time = "00:00:00") 
	{
    $this->processing_time = $processing_time;
  }
	
/**
* Sets the processing time enabled or disabled
* 
* Sets the processing time enabled or disabled
*
* @param integer $enable 0 to disable the processing time, 1 to enable the processing time
* @access public
* @see $processing_time
*/
	function setEnableProcessingTime($enable = 0) 
	{
		if ($enable) {
			$this->enable_processing_time = "1";
		} else {
			$this->enable_processing_time = "0";
		}
	}

/**
* Sets the starting time for the test
* 
* Sets the starting time in database timestamp format for the test
*
* @param string $starting_time The starting time for the test. Empty string for no starting time.
* @access public
* @see $starting_time
*/
  function setStartingTime($starting_time = "") 
	{
    $this->starting_time = $starting_time;
  }

/**
* Sets the ending time for the test
*
* Sets the ending time in database timestamp format for the test
*
* @param string $ending_time The ending time for the test. Empty string for no ending time.
* @access public
* @see $ending_time
*/
  function setEndingTime($ending_time = "") 
	{
    $this->ending_time = $ending_time;
  }
  
/**
* Sets the count system for the calculation of points
*
* Sets the count system for the calculation of points
*
* @param integer $a_count_system The count system for the calculation of points.
* @access public
* @see $count_system
*/
  function setCountSystem($a_count_system = COUNT_PARTIAL_SOLUTIONS) 
	{
    $this->count_system = $a_count_system;
  }
  
/**
* Sets the password for test access 
*
* Sets the password for test access 
*
* @param string $a_password The password for test access  
* @access public
* @see $password
*/
  function setPassword($a_password = "") 
	{
    $this->password = $a_password;
  }
  
/**
* Sets the type of score cutting
*
* Sets the type of score cutting
*
* @param integer $a_score_cutting The type of score cutting. 0 for cut questions, 1 for cut tests
* @access public
* @see $score_cutting
*/
  function setScoreCutting($a_score_cutting = SCORE_CUT_QUESTION) 
	{
    $this->score_cutting = $a_score_cutting;
  }
  
/**
* Sets the multiple choice scoring
*
* Sets the multiple choice scoring
*
* @param integer $a_mc_scoring The scoring for multiple choice questions
* @access public
* @see $mc_scoring
*/
  function setMCScoring($a_mc_scoring = SCORE_ZERO_POINTS_WHEN_UNANSWERED) 
	{
    $this->mc_scoring = $a_mc_scoring;
  }
  
/**
* Sets the pass scoring
*
* Sets the pass scoring
*
* @param integer $a_pass_scoring The pass scoring type
* @access public
* @see $pass_scoring
*/
  function setPassScoring($a_pass_scoring = SCORE_LAST_PASS) 
	{ 
		switch ($a_pass_scoring)
		{
			case SCORE_BEST_PASS:
				$this->pass_scoring = SCORE_BEST_PASS;
				break;
			default:
				$this->pass_scoring = SCORE_LAST_PASS;
				break;
		}
  }
  
/**
* Removes a question from the test object
* 
* Removes a question from the test object
*
* @param integer $question_id The database id of the question to be removed
* @access public
* @see $test_id
*/
	function removeQuestion($question_id) 
	{
		$question =& ilObjTest::_instanciateQuestion($question_id);
		include_once ("./classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction($this->lng->txtlng("assessment", "log_question_removed", ilObjAssessmentFolder::_getLogLanguage()), $question_id);
		}
		$question->delete($question_id);
		$this->removeAllTestEditings($question_id);
		$this->loadQuestions();
		$this->saveQuestionsToDb();
	}
	
/**
* Removes all selected users for the test evaluation
* 
* Removes all selected users for the test evaluation
*
* @access public
*/
	function clearEvalSelectedUsers()
	{
		global $ilDB;
		
		$query = sprintf("DELETE FROM tst_eval_users WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
	}
	
/**
* Removes all references to the question in executed tests in case the question has been changed
*
* Removes all references to the question in executed tests in case the question has been changed.
* If a question was changed it cannot be guaranteed that the content and the meaning of the question
* is the same as before. So we have to delete all already started or completed tests using that question.
* Therefore we have to delete all references to that question in tst_solutions and the tst_active
* entries which were created for the user and test in the tst_solutions entry.
*
* @access public
*/
	function removeAllTestEditings($question_id = "") 
	{
		global $ilDB;
		// remove test_active entries, because test has changed
		$this->deleteActiveTests();
		// remove selected users/groups
		$this->clearEvalSelectedUsers();
		
		// remove the question from tst_solutions
		if ($question_id) 
		{
			$query = sprintf("DELETE FROM tst_solutions USING tst_solutions, tst_active where tst_solutions.active_fi = tst_active.active_id AND tst_active.test_fi = %s AND tst_solutions.question_fi = %s",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($question_id)
			);			
			$query2 = sprintf("DELETE FROM tst_active_qst_sol_settings USING tst_active_qst_sol_settings, tst_active where tst_active_qst_sol_settings.active_fi = tst_active.active_id AND tst_active.test_fi = %s AND tst_active_qst_sol_settings.question_fi = %s",
				$ilDB->quote($this->getTestId()),				
				$ilDB->quote($question_id)
			);
			$query3 = sprintf("DELETE FROM tst_test_result USING tst_test_result, tst_active WHERE tst_active.test_fi = %s AND tst_test_result.question_fi = %s AND tst_active.active_id = tst_test_result.active_fi",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($question_id)
			);
		} else {
			$query = sprintf("DELETE FROM tst_solutions USING tst_solutions, tst_active where tst_solutions.active_fi = tst_active.active_id AND tst_active.test_fi = %s",
				$ilDB->quote($this->getTestId())
			);
			$query2 = sprintf("DELETE FROM tst_active_qst_sol_settings USING tst_active_qst_sol_settings, tst_active where tst_active_qst_sol_settings.active_fi = tst_active.active_id AND tst_active.test_fi = %s",
				$ilDB->quote($this->getTestId())							
			);			
			$query3 = sprintf("DELETE FROM tst_test_result USING tst_test_result, tst_active WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_test_result.active_fi",
				$ilDB->quote($this->getTestId())
			);
		}
		$result = $ilDB->query($query);
		$result = $ilDB->query($query2);
		$result = $ilDB->query($query3);

		if ($this->isRandomTest())
		{
			$query = sprintf("DELETE FROM tst_test_random_question USING tst_test_random_question, tst_active WHERE tst_active.test_fi = %s AND tst_test_random_question.active_fi = tst_active.active_id",
				$ilDB->quote($this->getTestId())
			);
			$result = $ilDB->query($query);
		}
		include_once ("./classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction($this->lng->txtlng("assessment", "log_user_data_removed", ilObjAssessmentFolder::_getLogLanguage()));
		}
	}
	
/**
* Removes all references to the question in executed tests in case the question has been changed
*
* Removes all references to the question in executed tests in case the question has been changed.
* If a question was changed it cannot be guaranteed that the content and the meaning of the question
* is the same as before. So we have to delete all already started or completed tests using that question.
* Therefore we have to delete all references to that question in tst_solutions and the tst_active
* entries which were created for the user and test in the tst_solutions entry.
*
* @access public
*/
	function removeSelectedTestResults($active_ids) 
	{
		global $ilDB;
		
		// remove selected users/groups
		$this->clearEvalSelectedUsers();
		
		// remove the question from tst_solutions
		foreach ($active_ids as $active_id)
		{
			$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$query2 = sprintf("DELETE FROM tst_active_qst_sol_settings WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);			
			$query3 = sprintf("DELETE FROM tst_test_result WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$result = $ilDB->query($query);
			$result = $ilDB->query($query2);
			$result = $ilDB->query($query3);
	
			if ($this->isRandomTest())
			{
				$query = sprintf("DELETE FROM tst_test_random_question WHERE active_fi = %s",
					$ilDB->quote($active_id . "")
				);
				$result = $ilDB->query($query);
			}

			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				include_once ("./classes/class.ilObjUser.php");
				$uname = ilObjUser::_lookupName($this->_getUserIdFromActiveId($active_id));
				$this->logAction(sprintf($this->lng->txtlng("assessment", "log_selected_user_data_removed", ilObjAssessmentFolder::_getLogLanguage()), trim($uname["title"] . " " . $uname["firstname"] . " " . $uname["lastname"] . " (" . $uname["user_id"] . ")")));
			}
		}

		// remove test_active entries of selected users
		foreach ($active_ids as $active_id)
		{
			$query = sprintf("DELETE FROM tst_active WHERE active_id = %s",
				$ilDB->quote($active_id . "")
			);
			$result = $ilDB->query($query);
		}
	}
	
/**
* Removes all references to the question in executed tests in case the question has been changed
*
* Removes all references to the question in executed tests in case the question has been changed.
* If a question was changed it cannot be guaranteed that the content and the meaning of the question
* is the same as before. So we have to delete all already started or completed tests using that question.
* Therefore we have to delete all references to that question in tst_solutions and the tst_active
* entries which were created for the user and test in the tst_solutions entry.
*
* @access public
*/
	function removeTestResultsForUser($user_id) 
	{
		global $ilDB;
		
		$active = $this->getActiveTestUser($user_id);
		$active_id = $active->active_id;

		// remove the question from tst_solutions
		$query = sprintf("DELETE FROM tst_solutions WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);
		$query2 = sprintf("DELETE FROM tst_active_qst_sol_settings WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);			
		$query3 = sprintf("DELETE FROM tst_test_result WHERE active_fi = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		$result = $ilDB->query($query2);
		$result = $ilDB->query($query3);

		if ($this->isRandomTest())
		{
			$query = sprintf("DELETE FROM tst_test_random_question WHERE active_fi = %s",
				$ilDB->quote($active_id . "")
			);
			$result = $ilDB->query($query);
		}

		include_once ("./classes/class.ilObjAssessmentFolder.php");
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			include_once ("./classes/class.ilObjUser.php");
			$uname = ilObjUser::_lookupName($this->_getUserIdFromActiveId($active_id));
			$this->logAction(sprintf($this->lng->txtlng("assessment", "log_selected_user_data_removed", ilObjAssessmentFolder::_getLogLanguage()), trim($uname["title"] . " " . $uname["firstname"] . " " . $uname["lastname"] . " (" . $uname["user_id"] . ")")));
		}

		// remove test_active entry
		$query = sprintf("DELETE FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
	}
	
/**
* Deletes all active references to this test
*
* Deletes all active references to this test. This is necessary, if the test has been changed to
* guarantee the same conditions for all users.
*
* @access public
*/
	function deleteActiveTests() 
	{
		global $ilDB;
		
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
	}
	
/**
* Removes all question solutions for a given user
*
* Removes all question solutions for a given user
* The tst_active table is not affected. Only the existing
* solutions for all questions the user answered will be removed.
* This resets the test to the default values
*
* @access public
*/
	function deleteResults($user_id = "") 
	{
		global $ilDB;
		
		if ($user_id) 
		{
			$active = $this->getActiveTestUser($user_id);
			$pass = $this->_getPass($active->active_id);
			$query = sprintf("DELETE FROM tst_solutions USING tst_solutions, tst_active where tst_solutions.active_fi = tst_active.active_id AND tst_active.test_fi = %s AND tst_active.user_fi = %s AND tst_solutions.pass = %s",
				$ilDB->quote($this->getTestId() . ""),
				$ilDB->quote($user_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			$query = sprintf("DELETE FROM tst_test_result WHERE active_fi = %s AND pass = %s",
				$ilDB->quote($active->active_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			$sequence_arr = array_flip($this->questions);
			$sequence = join($sequence_arr, ",");
			$query = sprintf("UPDATE tst_active SET sequence = %s, lastindex = %s WHERE test_fi = %s and user_fi = %s",
				$ilDB->quote($sequence),
				$ilDB->quote("1"),
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($user_id)
			);
			$result = $ilDB->query($query);
			
			$query = sprintf("DELETE FROM tst_active_qst_sol_settings USING tst_active_qst_sol_settings, tst_active where tst_active_qst_sol_settings.active_fi = tst_active.active_id AND tst_active.test_fi = %s AND tst_active.user_fi = %s",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($user_id)
			);
			$result = $ilDB->query($query);
		}
	}
	
/**
* Moves a question up in order
* 
* Moves a question up in order
*
* @param integer $question_id The database id of the question to be moved up
* @access public
* @see $test_id
*/
	function questionMoveUp($question_id) 
	{
		global $ilDB;
		
		// Move a question up in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
		if ($data->sequence > 1) {
			// OK, it's not the top question, so move it up
			$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
				$ilDB->quote($this->getTestId()),
				$ilDB->quote($data->sequence - 1)
			);
			$result = $ilDB->query($query);
			$data_previous = $result->fetchRow(DB_FETCHMODE_OBJECT);
			// change previous dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence),
				$ilDB->quote($data_previous->test_question_id)
			);
			$result = $ilDB->query($query);
			// move actual dataset up
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence - 1),
				$ilDB->quote($data->test_question_id)
			);
			$result = $ilDB->query($query);
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($data->sequence) . " => " . ($data->sequence-1), $question_id);
			}
		}
		$this->loadQuestions();
	}
	
/**
* Moves a question down in order
* 
* Moves a question down in order
*
* @param integer $question_id The database id of the question to be moved down
* @access public
* @see $test_id
*/
	function questionMoveDown($question_id) 
	{
		global $ilDB;
		
		// Move a question down in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($question_id)
		);
		$result = $ilDB->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($data->sequence + 1)
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1) 
		{
			// OK, it's not the last question, so move it down
			$data_next = $result->fetchRow(DB_FETCHMODE_OBJECT);
			// change next dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence),
				$ilDB->quote($data_next->test_question_id)
			);
			$result = $ilDB->query($query);
			// move actual dataset down
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$ilDB->quote($data->sequence + 1),
				$ilDB->quote($data->test_question_id)
			);
			$result = $ilDB->query($query);
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_question_position_changed", ilObjAssessmentFolder::_getLogLanguage()) . ": " . ($data->sequence) . " => " . ($data->sequence+1), $question_id);
			}
		}
		$this->loadQuestions();
	}
	
	/**
	* Takes a question and creates a copy of the question for use in the test
	*
	* Takes a question and creates a copy of the question for use in the test
	*
	* @param integer $question_id The database id of the question
	* @result integer The database id of the copied question
	* @access public
	*/
	function duplicateQuestionForTest($question_id)
	{
		global $ilUser;
		$question =& ilObjTest::_instanciateQuestion($question_id);
		$duplicate_id = $question->duplicate(true);

		return $duplicate_id;
	}

/**
* Insert a question in the list of questions 
* 
* Insert a question in the list of questions 
*
* @param integer $question_id The database id of the inserted question
* @access	public
*/
	function insertQuestion($question_id)
	{
		global $ilDB;
		
		$duplicate_id = $this->duplicateQuestionForTest($question_id);

		// get maximum sequence index in test
		$query = sprintf("SELECT MAX(sequence) AS seq FROM tst_test_question WHERE test_fi=%s",
			$ilDB->quote($this->getTestId())
			);
		$result = $ilDB->query($query);
		$sequence = 1;

		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
			$sequence = $data->seq + 1;
		}

		$query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($duplicate_id),
			$ilDB->quote($sequence)
			);
		$result = $ilDB->query($query);
		if ($result != DB_OK)
		{
			// Error
		}
		else
		{
			include_once ("./classes/class.ilObjAssessmentFolder.php");
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txtlng("assessment", "log_question_added", ilObjAssessmentFolder::_getLogLanguage()) . ": " . $sequence, $duplicate_id);
			}
		}
		// remove test_active entries, because test has changed
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
			);
		$result = $ilDB->query($query);
		$this->loadQuestions();
		$this->saveCompleteStatus();
	}

/**
* Returns the titles of the test questions in question sequence
*
* Returns the titles of the test questions in question sequence
*
* @return array The question titles
* @access public
* @see $questions
*/
	function &getQuestionTitles() 
	{
		$titles = array();
		if (!$this->isRandomTest())
		{
			global $ilDB;
			$query = sprintf("SELECT qpl_questions.title FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY tst_test_question.sequence",
				$ilDB->quote($this->getTestId() . "")
			);
			$result = $ilDB->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($titles, $row["title"]);
			}
		}
		return $titles;
	}
	
/**
* Returns the dataset for a given question id
* 
* Returns the dataset for a given question id
*
* @param integer $question_id The database id of the question
* @return object Question dataset
* @access public
* @see $questions
*/
	function getQuestionDataset($question_id) 
	{
		global $ilDB;
		
		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
			$ilDB->quote("$question_id")
		);
    $result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row;
	}
	
/**
* Get the titles of all available questionpools for the current user
* 
* Get the titles of all available questionpools for the current user
*
* @return array An array containing the questionpool titles as values and the questionpool id's as keys
* @access	public
*/
	function &get_qpl_titles() 
	{
		global $rbacsystem;
		global $ilDB;
		
		$qpl_titles = array();
		// get all available questionpools and remove the trashed questionspools
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl' ORDER BY object_data.title";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && ($this->_hasUntrashedReference($row->obj_id)))
			{
				include_once("./assessment/classes/class.ilObjQuestionPool.php");
				if (ilObjQuestionPool::_lookupOnline($row->obj_id))
				{
					$qpl_titles["$row->obj_id"] = $row->title;
				}
			}
		}
		return $qpl_titles;
	}
	
/**
* Get the id's of the questions which are already part of the test
* 
* Get the id's of the questions which are already part of the test
*
* @return array An array containing the already existing questions
* @access	public
*/
	function &getExistingQuestions($pass = NULL) 
	{
		global $ilUser;
		global $ilDB;
		
		$existing_questions = array();
		$active = $this->getActiveTestUser($ilUser->getId());
		if ($this->isRandomTest())
		{
			if (is_null($pass)) $pass = 0;
			$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_random_question WHERE tst_test_random_question.active_fi = %s AND tst_test_random_question.question_fi = qpl_questions.question_id AND tst_test_random_question.pass = %s",
				$ilDB->quote($active->active_id . ""),
				$ilDB->quote($pass . "")
			);
		}
		else
		{
			$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id",
				$ilDB->quote($this->getTestId())
			);
		}
		$result = $ilDB->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			array_push($existing_questions, $data->original_id);
		}
		return $existing_questions;
	}
	
/**
* Returns the question type of a question with a given id
* 
* Returns the question type of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question type string
* @access private
*/
  function getQuestionType($question_id) 
	{
		global $ilDB;
		
    if ($question_id < 1)
      return -1;
    $query = sprintf("SELECT type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
      $ilDB->quote($question_id)
    );
    $result = $ilDB->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      return $data->type_tag;
    } else {
      return "";
    }
  }
	
/**
* Write the initial entry for the tests working time to the database
* 
* Write the initial entry for the tests working time to the database
*
* @param integer $user_id The database id of the user working with the test
* @access	public
*/
	function startWorkingTime($user_id) 
	{
		global $ilDB;
		
		$result = "";
		if (!($result = $this->getActiveTestUser($user_id))) {
			$this->setActiveTestUser();
			$result = $this->getActiveTestUser($user_id);
		}
		$q = sprintf("INSERT INTO tst_times (times_id, active_fi, started, finished, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$ilDB->quote($result->active_id),
			$ilDB->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$ilDB->quote(strftime("%Y-%m-%d %H:%M:%S"))
		);
		$result = $ilDB->query($q);
		return $ilDB->getLastInsertId();
	}
	
/**
* Update the working time of a test when a question is answered
* 
* Update the working time of a test when a question is answered
*
* @param integer $times_id The database id of a working time entry
* @access	public
*/
	function updateWorkingTime($times_id)
	{
		global $ilDB;
		
		$q = sprintf("UPDATE tst_times SET finished = %s WHERE times_id = %s",
			$ilDB->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$ilDB->quote($times_id)
		);
		$result = $ilDB->query($q);
	}
  
/**
* Calculate the question id from a test sequence number
* 
* Calculate the question id from a test sequence number
*
* @param integer $sequence The sequence number of the question
* @return integer The question id of the question with the given sequence number
* @access	public
*/
	function getQuestionIdFromActiveUserSequence($sequence) 
	{
		global $ilUser;

		$active = $this->getActiveTestUser();
		$sequence_array = split(",", $active->sequence);
		return $this->questions[$sequence_array[$sequence-1]];
	}
	
/**
* Returns all questions of a test in users order
* 
* Returns all questions of a test in users order
*
* @return array An array containing the id's and the titles of the questions
* @access public
*/
	function &getAllQuestionsForActiveUser() 
	{
		$result_array = array();
		$active = $this->getActiveTestUser();
		$sequence_array = split(",", $active->sequence);
		$all_questions = &$this->getAllQuestions();
		$worked_questions = &$this->getWorkedQuestions($active->active_id);
		foreach ($sequence_array as $sequence)
		{
			if (in_array($this->questions[$sequence], $worked_questions))
			{
				$all_questions[$this->questions[$sequence]]["worked"] = 1;
			}
			else
			{
				$all_questions[$this->questions[$sequence]]["worked"] = 0;
			}
			array_push($result_array, $all_questions[$this->questions[$sequence]]);
		}
		return $result_array;
	}

	function getWrongAnsweredQuestions()
	{
		global $ilUser;

		$active = $this->getActiveTestUser($ilUser->getId());
		foreach($all_questions =& $this->getAllQuestionsForActiveUser() as $question)
		{
			foreach($this->getTestResult($active->active_id) as $result)
			{
				if($result['qid'] == $question['question_id'])
				{
					if($result['max'] != $result['reached'])
					{
						$wrong[] = $question;
					}
				}
			}
		}
		return $wrong ? $wrong : array();
	}


	function getFirstSequence()
	{
		global $ilUser;

		$active = $this->getActiveTestUser($ilUser->getId());
		$results = $this->getTestResult($active->active_id);

		for($i = 1; $i <= $this->getQuestionCount(); $i++)
		{
			$qid = $this->getQuestionIdFromActiveUserSequence($i);

			foreach($results as $result)
			{
				if($qid == $result['qid'])
				{
					if(!$result['max'] or $result['max'] != $result['reached'])
					{
						return $i;
					}
				}
			}
		}
		return 0;
	}

	
/**
* Gets the id's of all questions a user already worked through
* 
* Gets the id's of all questions a user already worked through
*
* @return array The question id's of the questions already worked through
* @access	public
*/
	function &getWorkedQuestions($active_id, $pass = NULL)
	{
		global $ilUser;
		global $ilDB;
		
		if (is_null($pass))
		{
			$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND pass = 0 GROUP BY question_fi",
				$ilDB->quote($active_id . "")
			);
		}
		else
		{
			$query = sprintf("SELECT * FROM tst_solutions WHERE active_fi = %s AND pass = %s GROUP BY question_fi",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($pass . "")
			);
		}
		$result = $ilDB->query($query);
		$result_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($result_array, $row->question_fi);
		}
		return $result_array;
	}
	
/**
* Returns all questions of a test in test order
* 
* Returns all questions of a test in test order
*
* @return array An array containing the id's as keys and the database row objects as values
* @access public
*/
	function &getAllQuestions($pass = NULL)
	{
		global $ilUser;
		global $ilDB;

		if ($this->isRandomTest())
		{
			$active = $this->getActiveTestUser($ilUser->getId());
			if (is_null($pass)) $pass = 0;
			$query = sprintf("SELECT qpl_questions.* FROM qpl_questions, tst_test_random_question WHERE tst_test_random_question.question_fi = qpl_questions.question_id AND tst_test_random_question.active_fi = %s AND tst_test_random_question.pass = %s AND qpl_questions.question_id IN (" . join($this->questions, ",") . ")",
				$ilDB->quote($active->active_id . ""),
				$ilDB->quote($pass . "")
			);
		}
		else
		{
			$query = "SELECT qpl_questions.* FROM qpl_questions, tst_test_question WHERE tst_test_question.question_fi = qpl_questions.question_id AND qpl_questions.question_id IN (" . join($this->questions, ",") . ")";
		}
		$result = $ilDB->query($query);
		$result_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$result_array[$row["question_id"]] = $row;
		}
		return $result_array;
	}
	
	function &getActiveTestUserFromActiveId($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		$row = NULL;
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		}
		return $row;
	}
	
/**
* Gets the database row of the tst_active table for the active user
* 
* Gets the database row of the tst_active table for the active user
*
* @param integer $user_id The database id of the user
* @return object The database row of the tst_active table
* @access	public
*/
	function getActiveTestUser($user_id = "", $anonymous_id = "") 
	{
		global $ilDB;
		global $ilUser;

		if (!$user_id) 
		{
			$user_id = $ilUser->id;
		}
		if (($_SESSION["AccountId"] == ANONYMOUS_USER_ID) && (strlen($_SESSION["tst_access_code"][$this->getTestId()])))
		{
			$query = sprintf("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
				$ilDB->quote($user_id),
				$ilDB->quote($this->test_id),
				$ilDB->quote($_SESSION["tst_access_code"][$this->getTestId()])
			);
		}
		else if (strlen($anonymous_id))
		{
			$query = sprintf("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s AND anonymous_id = %s",
				$ilDB->quote($user_id),
				$ilDB->quote($this->test_id),
				$ilDB->quote($anonymous_id)
			);
		}
		else
		{
			if ($_SESSION["AccountId"] == ANONYMOUS_USER_ID)
			{
				return NULL;
			}
			$query = sprintf("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
				$ilDB->quote($user_id),
				$ilDB->quote($this->test_id)
			);
		}
		$result = $ilDB->query($query);
		if ($result->numRows()) 
		{
			$this->active = $result->fetchRow(DB_FETCHMODE_OBJECT);
		} 
		else 
		{
			$this->active = null;
		}
		return $this->active;
	}
	
/**
* Gets the database row of the tst_active table for the active user
* 
* Gets the database row of the tst_active table for the active user
*
* @param integer $user_id The database id of the user
* @param integer $test_id The database id of the test
* @return object The database row of the tst_active table
* @access	public
*/
	function _getActiveTestUser($user_id = "", $test_id = "") {
		global $ilDB;
		global $ilUser;
		
		if (!$user_id) {
			$user_id = $ilUser->id;
		}
		if (!$test_id)
		{
			return "";
		}
		$query = sprintf("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
			$ilDB->quote($user_id),
			$ilDB->quote($test_id)
		);
		
		$result = $ilDB->query($query);
		if ($result->numRows()) {
			return $result->fetchRow(DB_FETCHMODE_OBJECT);
		} else {
			return "";
		}
	}
	
/**
* Update the data of the tst_active table for the current user
* 
* Update the data of the tst_active table for the current user
* The table saves the state of the active user in the test (sequence position,
* postponed questions etc.)
*
* @param integer $lastindex The sequence position of the question the user last visited
* @param integer $postpone The sequence position of a question which should be postponed
* @param boolean $addTries Adds 1 to the number of test completions if set to true
* @access	public
*/
	function setActiveTestUser($lastindex = 1, $postpone = "", $addTries = false) 
	{
		global $ilDB;
		global $ilUser;
		
		if ($lastindex < 1) $lastindex = 1;

		$old_active = $this->getActiveTestUser();
		if ($old_active) 
		{
			$sequence = $old_active->sequence;
			$postponed = $old_active->postponed;
			if ($postpone) 
			{
				$sequence_array = split(",", $sequence);
				$postpone_sequence = $sequence_array[$postpone-1];
				$question_id = $this->questions[$postpone_sequence];
				unset($sequence_array[$postpone-1]);
				array_push($sequence_array, $postpone_sequence);
				$sequence = join(",", $sequence_array);
				$postponed .= ",$question_id";
				$postponed = preg_replace("/^,/", "", $postponed);
			}
			$tries = $old_active->tries;
			if ($addTries && ($this->getNrOfResultsForPass($old_active->active_id, $old_active->tries) > 0)) 
			{
				// only add the number of tries if there are ANY results for the current
				// test pass. Otherwise something must be wrong (doubleclick, reload etc.)
				$tries++;
			}
			$query = sprintf("UPDATE tst_active SET lastindex = %s, sequence = %s, postponed = %s, tries = %s WHERE user_fi = %s AND test_fi = %s",
				$ilDB->quote($lastindex),
				$ilDB->quote($sequence),
				$ilDB->quote($postponed),
				$ilDB->quote($tries),
				$ilDB->quote($ilUser->id),
				$ilDB->quote($this->test_id)
			);
		}
		else 
		{
			$sequence_arr = array_flip($this->questions);
			if ($this->getShuffleQuestions())
			{
				$sequence_arr = array_values($sequence_arr);
				$sequence_arr = $this->pcArrayShuffle($sequence_arr);
			}
			$sequence = join($sequence_arr, ",");
			if ($_SESSION["tst_access_code"][$this->getTestId()])
			{
				$anonymous_id = $ilDB->quote($_SESSION["tst_access_code"][$this->getTestId()]);
			}
			else
			{
				$anonymous_id = "NULL";
			}
			$query = sprintf("INSERT INTO tst_active (active_id, user_fi, test_fi, anonymous_id, sequence, postponed, lastindex, tries, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($ilUser->id),
				$ilDB->quote($this->test_id),
				$anonymous_id,
				$ilDB->quote($sequence),
				$ilDB->quote(""),
				$ilDB->quote($lastindex),
				$ilDB->quote(0)
			);
		}
		$ilDB->query($query);
	}
	
	/**
	* Adds the sequence of questions for a random test to an existing active dataset
	*
	* Adds the sequence of questions for a random test to an existing active dataset
	* This is called when the initial question sequence for a random test has to be
	* created. The generation of the questions depends on the active id of the user
	* which means that the active dataset has to be created before the question sequence
	* exists.
	*
	* @param int $active_id The active id of the user
	* @access private
	*/
	function addQuestionSequence($active_id)
	{
		if ($this->isRandomTest())
		{
			global $ilUser;
			global $ilDB;
			
			$this->loadQuestions($active_id, 0);
			$sequence_arr = array_flip($this->questions);
			if ($this->getShuffleQuestions())
			{
				$sequence_arr = array_values($sequence_arr);
				$sequence_arr = $this->pcArrayShuffle($sequence_arr);
			}
			$sequence = join($sequence_arr, ",");
			$query = sprintf("UPDATE tst_active SET sequence = %s WHERE active_id = %s",
				$ilDB->quote($sequence . ""),
				$ilDB->quote($active_id . "")
			);
			$ilDB->query($query);
		}
	}
	
	/**
	* Shuffles the values of a given array
	*
	* Shuffles the values of a given array
	*
	* @param array $array An array which should be shuffled
	* @access public
	*/
	function pcArrayShuffle($array)
	{
		mt_srand((double)microtime()*1000000);
		$i = count($array);
		if ($i > 0)
		{
			while(--$i)
			{
				$j = mt_rand(0, $i);
				if ($i != $j)
				{
					// swap elements
					$tmp = $array[$j];
					$array[$j] = $array[$i];
					$array[$i] = $tmp;
				}
			}
		}
		return $array;
	}
	
	/**
	* Calculates the results of a test for a given user
	* 
	* Calculates the results of a test for a given user
	* and returns an array with all test results
	*
	* @return array An array containing the test results for the given user
	* @access public
	*/
	function &getTestResult($active_id, $pass = NULL)
	{
		//		global $ilBench;
		if ($this->isRandomTest())
		{
			$this->loadQuestions($active_id, $pass);
		}
		$total_max_points = 0;
		$total_reached_points = 0;
		
		$key = 1;
		$result_array = array();
		include_once "./assessment/classes/class.assQuestion.php";
		$workedthrough = 0;
		$active_object = $this->getActiveTestUserFromActiveId($active_id);
		$user_sequence = split(",", $active_object->sequence);
		foreach ($user_sequence as $questionindex)
		{
			$value = $this->questions[$questionindex];
			$max_points = assQuestion::_getMaximumPoints($value);
			$total_max_points += $max_points;
			$reached_points = assQuestion::_getReachedPoints($active_id, $value, $pass);
			if (assQuestion::_isWorkedThrough($active_id, $value, $pass))
			{
				$workedthrough = 1;
			}
			else
			{
				$workedthrough = 0;
			}
			$total_reached_points += $reached_points;
			if ($max_points > 0)
			{
				$percentvalue = $reached_points / $max_points;
			}
			else
			{
				$percentvalue = 0;
			}
			if ($percentvalue < 0) $percentvalue = 0.0;
			if (assQuestion::_getSuggestedSolutionCount($value) == 1)
			{
				$solution_array =& assQuestion::_getSuggestedSolution($value, 0);
				$href = assQuestion::_getInternalLinkHref($solution_array["internal_link"]);
			}
			elseif (assQuestion::_getSuggestedSolutionCount($value) > 1)
			{
				$href = "see_details_for_further_information";
			}
			else
			{
				$href = "";
			}
			$info =& assQuestion::_getQuestionInfo($value);
			include_once "./classes/class.ilUtil.php";
			$row = array(
				"nr" => "$key",
				"title" => ilUtil::prepareFormOutput($info["title"]),
				"max" => sprintf("%d", $max_points),
				"reached" => sprintf("%d", $reached_points),
				"percent" => sprintf("%2.2f ", ($percentvalue) * 100) . "%",
				"solution" => $href,
				"type" => $info["type_tag"],
				"qid" => $value,
				"workedthrough" => $workedthrough
			);
			array_push($result_array, $row);
			$key++;
		}
		if ($this->getScoreCutting() == 1)
		{
			if ($total_reached_points < 0)
			{
				$total_reached_points = 0;
			}
		}
		$result_array["test"]["total_max_points"] = $total_max_points;
		$result_array["test"]["total_reached_points"] = $total_reached_points;
		if ((!$total_reached_points) or (!$total_max_points))
		{
			$percentage = 0.0;
		}
		else
		{
			$percentage = ($total_reached_points / $total_max_points) * 100.0;
			if ($percentage < 0) $percentage = 0.0;
		}
		$mark_obj = $this->mark_schema->getMatchingMark($percentage);
		$passed = "";
		if ($mark_obj)
		{
			if ($mark_obj->getPassed())
			{
				$passed = 1;
			}
			else
			{
				$passed = 0;
			}
		}
		$result_array["test"]["passed"] = $passed;
		return $result_array;
	}


	/**
	* Calculates the overview of a test for a given user
	* 
	* and returns an array with all test questions
	*
	* @return array An array containing the test overview for the given user
	* @access public
	*/
	function &getTestSummary($active_id, $pass = NULL)
	{
		global $ilDB;
		if ($this->isRandomTest())
		{
			$this->loadQuestions($active_id, $pass);
		}
		
		$key = 1;
		$result_array = array();

		$active = $this->getActiveTestUserFromActiveId($active_id);
		$postponed = explode(",", $active->postponed);
		$solved_questions = ilObjTest::_getSolvedQuestions($active_id);
		include_once "./classes/class.ilObjUser.php";
	 	$user = new ilObjUser($user_id);
		$sequence_array = split(",", $active->sequence);
		foreach ($sequence_array as $question_index)
		{
			$val = $this->questions[$question_index];
			$question =& ilObjTest::_instanciateQuestion($val);
			if (is_object($question))
			{
				$worked_through = $question->_isWorkedThrough($active_id, $question->getId(), $pass);
				$solved  = 0;
				if (array_key_exists($question->getId(),$solved_questions)) 
				{
					$solved =  $solved_questions[$question->getId()]->solved; 
				}
				$is_postponed = FALSE;
				if (in_array($question->getId(), $postponed))
				{
					$is_postponed = TRUE;
				}
				
				$row = array(
					"nr" => "$key",					
					"title" => $question->getTitle(),
					"qid" => $question->getId(),
					"visited" => $worked_through,
					"solved" => (($solved)?"1":"0"),
					"description" => $question->getComment(),
					"points" => $question->getMaximumPoints(),
					"worked_through" => $worked_through,
					"postponed" => $is_postponed
				);
				array_push($result_array, $row);
				$key++;
			}			
		}
		
		return $result_array;
	}

	
/**
* Returns the number of persons who started the test
*
* Returns the number of persons who started the test
*
* @return integer The number of persons who started the test
* @access public
*/
	function evalTotalPersons()
	{
		global $ilDB;
		
		$q = sprintf("SELECT COUNT(*) as total FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->total;
	}
	
/**
* Returns true, if the test results can be viewed
* 
* Returns true, if the test results can be viewed
*
* @return boolean True, if the test results can be viewed, else false
* @access public
*/
	function canViewResults()
	{
		$result = true;
		if ($this->getTestType() != TYPE_SELF_ASSESSMENT)
		{
			if ($this->getReportingDate())
			{
				if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getReportingDate(), $matches))
				{
					$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
					$now = mktime();
					if ($now < $epoch_time) 
					{
						$result = false;
					}
				}
			}
		}
		return $result;
	}

/**
* Retrieves the user settings for the statistical evaluation tool
* 
* Retrieves the user settings for the statistical evaluation tool
*
* @return array An array containing the user settings
* @access public
*/
	function evalLoadStatisticalSettings($user_id)
	{
		global $ilDB;
		
		$q = sprintf("SELECT * FROM tst_eval_settings WHERE user_fi = %s",
			$ilDB->quote("$user_id")
		);
		$result = $ilDB->query($q);
		if (!$result->numRows()) 
		{
			$row = array(
				"qworkedthrough" => "1",
				"pworkedthrough" => "1",
				"timeofwork" => "1",
				"atimeofwork" => "1",
				"firstvisit" => "1",
				"lastvisit" => "1",
				"resultspoints" => "1",
				"resultsmarks" => "1",
				"distancemedian" => "1"
			);
		} 
			else 
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			unset($row["eval_settings_id"]);
		}
		return $row;		
	}
	
/**
* Saves the user settings for the statistical evaluation tool
* 
* Saves the user settings for the statistical evaluation tool
*
* @param array $settings_array An array containing the user settings
* @access public
*/
	function evalSaveStatisticalSettings($settings_array, $user_id)
	{
		global $ilDB;
		
		$q = sprintf("SELECT * FROM tst_eval_settings WHERE user_fi = %s",
			$ilDB->quote("$user_id")
		);
		$result = $ilDB->query($q);
		if ($result->numRows() > 0)
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		}
		$update = $row["eval_settings_id"];
		if (!$update) {
			$q = sprintf("INSERT INTO tst_eval_settings ".
					 "(eval_settings_id, user_fi, qworkedthrough, pworkedthrough, timeofwork, atimeofwork, firstvisit, " .
					 "lastvisit, resultspoints, resultsmarks, distancemedian, TIMESTAMP) VALUES " .
					 "(NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote("$user_id"),
				$ilDB->quote(sprintf("%01d", $settings_array["qworkedthrough"])),
				$ilDB->quote(sprintf("%01d", $settings_array["pworkedthrough"])),
				$ilDB->quote(sprintf("%01d", $settings_array["timeofwork"])),
				$ilDB->quote(sprintf("%01d", $settings_array["atimeofwork"])),
				$ilDB->quote(sprintf("%01d", $settings_array["firstvisit"])),
				$ilDB->quote(sprintf("%01d", $settings_array["lastvisit"])),
				$ilDB->quote(sprintf("%01d", $settings_array["resultspoints"])),
				$ilDB->quote(sprintf("%01d", $settings_array["resultsmarks"])),
				$ilDB->quote(sprintf("%01d", $settings_array["distancemedian"]))
			);
		} 
			else 
		{
			$q = sprintf("UPDATE tst_eval_settings SET ".
					 "qworkedthrough = %s, pworkedthrough = %s, timeofwork = %s, atimeofwork = %s, firstvisit = %s, " .
					 "lastvisit = %s, resultspoints = %s, resultsmarks = %s, distancemedian = %s " .
					 "WHERE eval_settings_id = %s",
				$ilDB->quote(sprintf("%01d", $settings_array["qworkedthrough"])),
				$ilDB->quote(sprintf("%01d", $settings_array["pworkedthrough"])),
				$ilDB->quote(sprintf("%01d", $settings_array["timeofwork"])),
				$ilDB->quote(sprintf("%01d", $settings_array["atimeofwork"])),
				$ilDB->quote(sprintf("%01d", $settings_array["firstvisit"])),
				$ilDB->quote(sprintf("%01d", $settings_array["lastvisit"])),
				$ilDB->quote(sprintf("%01d", $settings_array["resultspoints"])),
				$ilDB->quote(sprintf("%01d", $settings_array["resultsmarks"])),
				$ilDB->quote(sprintf("%01d", $settings_array["distancemedian"])),
				$ilDB->quote("$update")
			);
		}
		$result = $ilDB->query($q);
	}

/**
* Returns the complete working time in seconds a user worked on the test
* 
* Returns the complete working time in seconds a user worked on the test
*
* @return integer The working time in seconds
* @access public
*/
	function getCompleteWorkingTime($user_id)
	{
		global $ilDB;
		
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.user_fi = %s",
			$ilDB->quote($this->getTestId()),
			$ilDB->quote($user_id)
		);
		$result = $ilDB->query($q);
		$time = 0;
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$time += ($epoch_2 - $epoch_1);
		}
		return $time;
	}
	
/**
* Returns the statistical evaluation of the test for a specified user
* 
* Returns the statistical evaluation of the test for a specified user
*
* @return arrary The statistical evaluation array of the test
* @access public
*/
	function &evalStatistical($active_id)
	{
		global $ilDB;
//		global $ilBench;
		$pass = ilObjTest::_getResultPass($active_id);
		$test_result =& $this->getTestResult($active_id, $pass);
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.active_id = %s AND tst_active.active_id = tst_times.active_fi",
			$ilDB->quote($active_id)
		);
		$result = $ilDB->query($q);
		$times = array();
		$first_visit = 0;
		$last_visit = 0;
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if (!$first_visit) {
				$first_visit = $epoch_1;
			}
			if ($epoch_1 < $first_visit) {
				$first_visit = $epoch_1;
			}
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			if (!$last_visit) {
				$last_visit = $epoch_2;
			}
			if ($epoch_2 > $last_visit) {
				$last_visit = $epoch_2;
			}
			$times[$row->active_fi] += ($epoch_2 - $epoch_1);
		}
		$max_time = 0;
		foreach ($times as $key => $value) {
			$max_time += $value;
		}
		if ((!$test_result["test"]["total_reached_points"]) or (!$test_result["test"]["total_max_points"])) 
		{
			$percentage = 0.0;
		} 
		else 
		{
			$percentage = ($test_result["test"]["total_reached_points"] / $test_result["test"]["total_max_points"]) * 100.0;
			if ($percentage < 0) $percentage = 0.0;
		}
		$mark_obj = $this->mark_schema->getMatchingMark($percentage);
		$first_date = getdate($first_visit);
		$last_date = getdate($last_visit);
		$qworkedthrough = 0;
		foreach ($test_result as $key => $value)
		{
			if (preg_match("/\d+/", $key))
			{
				$qworkedthrough += $value["workedthrough"];
			}
		}
		if (!qworkedthrough)
		{
			$atimeofwork = 0;
		}
		else
		{
			$atimeofwork = $max_time / $qworkedthrough;
		}
		$result_mark = "";
		$passed = "";
		if ($mark_obj)
		{
			$result_mark = $mark_obj->getShortName();
			if ($mark_obj->getPassed())
			{
				$passed = 1;
			}
			else
			{
				$passed = 0;
			}
		}
		$result_array = array(
			"qworkedthrough" => $qworkedthrough,
			"qmax" => count($this->questions),
			"pworkedthrough" => $qworkedthrough / count($this->questions),
			"timeofwork" => $max_time,
			"atimeofwork" => $atimeofwork,
			"firstvisit" => $first_date,
			"lastvisit" => $last_date,
			"resultspoints" => $test_result["test"]["total_reached_points"],
			"maxpoints" => $test_result["test"]["total_max_points"],
			"resultsmarks" => $result_mark,
			"passed" => $passed,
			"distancemedian" => "0"
		);
		foreach ($test_result as $key => $value)
		{
			if (preg_match("/\d+/", $key))
			{
				$result_array[$key] = $value;
			}
		}
		return $result_array;
	}

/**
* Returns an array with the total points of all users who participated the test
* 
* Returns an array with the total points of all users who participated the test
* This array could be used for statistics
*
* @return array The total point values
* @access public
*/
	function &getTotalPointsArray()
	{
		$totalpoints_array = array();
		$all_users =& $this->evalTotalParticipantsArray();
		foreach ($all_users as $active_id => $user_name)
		{
			$test_result =& $this->getTestResult($active_id);
			array_push($totalpoints_array, $test_result["test"]["total_reached_points"]);
		}
		return $totalpoints_array;
	}
	
/**
* Returns an array with the total points of all users who passed the test
* 
* Returns an array with the total points of all users who passed the test
* This array could be used for statistics
*
* @return array The total point values
* @access public
*/
	function &getTotalPointsPassedArray()
	{
		$totalpoints_array = array();
		$all_users =& $this->evalTotalParticipantsArray();
		foreach ($all_users as $active_id => $user_name)
		{
			$test_result =& $this->getTestResult($active_id);
			$reached = $test_result["test"]["total_reached_points"];
			$total = $test_result["test"]["total_max_points"];
			$percentage = $reached/$total;
			$mark = $this->mark_schema->getMatchingMark($percentage*100.0);
			if ($mark)
			{
				if ($mark->getPassed())
				{
					array_push($totalpoints_array, $test_result["test"]["total_reached_points"]);
				}
			}
		}
		return $totalpoints_array;
	}

/**
* Returns all persons who started the test
* 
* Returns all persons who started the test
*
* @return arrary The user id's and names of the persons who started the test
* @access public
*/
	function &evalTotalPersonsArray($name_sort_order = "asc")
	{
		global $ilDB;
		
		$q = sprintf("SELECT tst_active.user_fi, usr_data.firstname, usr_data.lastname, usr_data.title FROM tst_active LEFT JOIN usr_data ON tst_active.user_fi = usr_data.usr_id WHERE tst_active.test_fi = %s ORDER BY usr_data.lastname " . strtoupper($name_sort_order),
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$persons_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if (strlen($row->firstname.$row->lastname.$row->title) == 0)
			{
				$persons_array[$row->user_fi] = $this->lng->txt("deleted_user");
			}
			else
			{
				$persons_array[$row->user_fi] = trim("$row->lastname, $row->firstname $row->title");
			}
		}
		return $persons_array;
	}
	
/**
* Returns all participants who started the test
* 
* Returns all participants who started the test
*
* @return arrary The active user id's and names of the persons who started the test
* @access public
*/
	function &evalTotalParticipantsArray($name_sort_order = "asc")
	{
		global $ilDB;
		$q = sprintf("SELECT tst_active.active_id, tst_active.user_fi, usr_data.firstname, usr_data.lastname FROM tst_active, usr_data WHERE tst_active.test_fi = %s AND tst_active.user_fi = usr_data.usr_id ORDER BY usr_data.lastname " . strtoupper($name_sort_order), 
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$persons_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row["user_fi"] == ANONYMOUS_USER_ID)
			{
				$persons_array[$row["active_id"]] = $row["lastname"];
			}
			else
			{
				$persons_array[$row["active_id"]] = trim($row["lastname"] . ", " . $row["firstname"] . " " .  $row["title"]);
			}
		}
		return $persons_array;
	}
	
/**
* Returns the number of total finished tests
* 
* Returns the number of total finished tests
*
* @return integer The number of total finished tests
* @access public
*/
	function evalTotalFinished()
	{
		global $ilDB;
		
		$q = sprintf("SELECT COUNT(*) as total FROM tst_active WHERE test_fi = %s AND tries > 0",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->total;
	}
	
/**
* Returns the total passed tests and the average reached points
* 
* Returns the total passed tests and the average reached points
*
* @return array The total passed tests and the avarage reached points. 
* array("total_passed" => VALUE, "total_failed" => VALUE, "average_points" => VALUE, "maximum_points" => VALUE)
* @access public
*/
	function evalTotalFinishedPassed()
	{
		global $ilDB;
		
		$q = sprintf("SELECT * FROM tst_active WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$points = array();
		$passed_tests = 0;
		$failed_tests = 0;
		$maximum_points = 0;
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			$pass = ilObjTest::_getResultPass($row->active_id);
			$res =& $this->getTestResult($row->active_id, $pass);
			if ((!$res["test"]["total_reached_points"]) or (!$res["test"]["total_max_points"])) 
			{
				$percentage = 0.0;
			} 
				else 
			{
				$percentage = ($res["test"]["total_reached_points"] / $res["test"]["total_max_points"]) * 100.0;
				if ($percentage < 0) $percentage = 0.0;
			}
			$mark_obj = $this->mark_schema->getMatchingMark($percentage);
			$maximum_points = $res["test"]["total_max_points"];
			if ($mark_obj)
			{
				if ($mark_obj->getPassed()) {
					$passed_tests++;
					array_push($points, $res["test"]["total_reached_points"]);
				} 
					else 
				{
					$failed_tests++;
				}
			}
		}
		$reached_points = 0;
		$counter = 0;
		foreach ($points as $key => $value) 
		{
			$reached_points += $value;
			$counter++;
		}
		if ($counter) 
		{
			$average_points = ($reached_points / $counter);
		} 
			else 
		{
			$average_points = 0;
		}
		return array(
			"total_passed" => $passed_tests,
			"total_failed" => $failed_tests,
			"average_points" => $average_points,
			"maximum_points" => $maximum_points
		);
	}
	
/**
* Returns the average processing time for total finished tests
* 
* Returns the average processing time for total finished tests
*
* @return integer The average processing time for total finished tests
* @access public
*/
	function evalTotalFinishedAverageTime()
	{
		global $ilDB;
		
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.tries > 0 AND tst_active.active_id = tst_times.active_fi",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$times = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$times[$row->active_fi] += ($epoch_2 - $epoch_1);
		}
		$max_time = 0;
		$counter = 0;
		foreach ($times as $key => $value) 
		{
			$max_time += $value;
			$counter++;
		}
		if ($counter) 
		{
			$average_time = round($max_time / $counter);
		} 
			else 
		{
			$average_time = 0;
		}
		return $average_time;
	}
	
/**
* Returns the average processing time for all started tests
* 
* Returns the average processing time for all started tests
*
* @return integer The average processing time for all started tests
* @access public
*/
	function evalTotalStartedAverageTime()
	{
		global $ilDB;
		
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$times = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$times[$row->active_fi] += ($epoch_2 - $epoch_1);
		}
		$max_time = 0;
		$counter = 0;
		foreach ($times as $key => $value) 
		{
			$max_time += $value;
			$counter++;
		}
		if ($counter) 
		{
			$average_time = round($max_time / $counter);
		} 
			else 
		{
			$average_time = 0;
		}
		return $average_time;
	}
	
/**
* Returns the average processing time for all passed tests
* 
* Returns the average processing time for all passed tests
*
* @return integer The average processing time for all passed tests
* @access public
*/
	function evalTotalPassedAverageTime()
	{
		global $ilDB;
		
		include_once "./assessment/classes/class.ilObjTestAccess.php";
		$passed_users =& ilObjTest::_getPassedUsers($this->getId());
		$q = sprintf("SELECT tst_times.*, tst_active.active_id FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($q);
		$times = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			if (in_array($row->active_id, $passed_users))
			{
				preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
				$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
				$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$times[$row->active_id] += ($epoch_2 - $epoch_1);
			}
		}
		$max_time = 0;
		$counter = 0;
		foreach ($times as $key => $value) 
		{
			$max_time += $value;
			$counter++;
		}
		if ($counter) 
		{
			$average_time = round($max_time / $counter);
		} 
			else 
		{
			$average_time = 0;
		}
		return $average_time;
	}
	
/**
* Returns the object id's of the available question pools for the active user
* 
* Returns the object id's of the available question pools for the active user
*
* @return array The available question pool id's
* @access public
*/
	function &getAvailableQuestionpoolIDs()
	{
		global $rbacsystem;
		global $ilDB;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl'";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && ($this->_hasUntrashedReference($row->obj_id)))
			{
				include_once("./assessment/classes/class.ilObjQuestionPool.php");
				if (ilObjQuestionPool::_lookupOnline($row->obj_id))
				{
					array_push($result_array, $row->obj_id);
				}
			}
		}
		return $result_array;
	}

/**
* Returns the available question pools for the active user
* 
* Returns the available question pools for the active user
*
* @return array The available question pools
* @access public
*/
	function &getAvailableQuestionpools($use_object_id = false, $equal_points = false, $could_be_offline = false)
	{
		global $rbacsystem;
		global $ilDB;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl' ORDER BY object_data.title";
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && ($this->_hasUntrashedReference($row->obj_id)))
			{
				include_once("./assessment/classes/class.ilObjQuestionPool.php");
				if (ilObjQuestionPool::_lookupOnline($row->obj_id) || $could_be_offline)
				{
					if ((!$equal_points) || (($equal_points) && (ilObjQuestionPool::_hasEqualPoints($row->obj_id))))
					{
						if ($use_object_id)
						{
							$result_array[$row->obj_id] = $row->title;
						}
						else
						{
							$result_array[$row->ref_id] = $row->title;
						}
					}
				}
			}
		}
		return $result_array;
	}

/**
* Returns the estimated working time for the test calculated from the working time of the contained questions
* 
* Returns the estimated working time for the test calculated from the working time of the contained questions
*
* @return array An associative array containing the working time. array["h"] = hours, array["m"] = minutes, array["s"] = seconds
* @access public
*/
	function getEstimatedWorkingTime() 
	{
		$time_in_seconds = 0;
		foreach ($this->questions as $question_id) 
		{
			$question =& ilObjTest::_instanciateQuestion($question_id);
			$est_time = $question->getEstimatedWorkingTime();
			$time_in_seconds += $est_time["h"] * 3600 + $est_time["m"] * 60 + $est_time["s"];
		}
		$hours = (int)($time_in_seconds / 3600)	;
		$time_in_seconds = $time_in_seconds - ($hours * 3600);
		$minutes = (int)($time_in_seconds / 60);
		$time_in_seconds = $time_in_seconds - ($minutes * 60);
		$result = array("h" => $hours, "m" => $minutes, "s" => $time_in_seconds);
		return $result;
	}
	
/**
* Returns a random selection of questions
* 
* Returns a random selection of questions
*
* @param integer $nr_of_questions Number of questions to return
* @param integer $questionpool ID of questionpool to choose the questions from (0 = all available questionpools)
* @param boolean $user_obj_id Use the object id instead of the reference id when set to true
* @param array $qpls An array of questionpool id's if the random questions should only be chose from the contained questionpools
* @return array A random selection of questions
* @access public
*/
	function randomSelectQuestions($nr_of_questions, $questionpool, $use_obj_id = 0, $qpls = "", $pass = NULL)
	{
		global $rbacsystem;
		global $ilDB;
		// get the questionpool id if a questionpool ref id was entered
		if ($questionpool != 0)
		{
			// retrieve object id
			if (!$use_obj_id)
			{
				$query = sprintf("SELECT obj_id FROM object_reference WHERE ref_id = %s",
					$ilDB->quote("$questionpool")
				);
				$result = $ilDB->query($query);
				$row = $result->fetchRow(DB_FETCHMODE_ARRAY);
				$questionpool = $row[0];
			}
		}
		
		// get all existing questions in the test
		$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND tst_test_question.test_fi = %s",
			$ilDB->quote($this->getTestId() . "")
		);
		$result = $ilDB->query($query);
		$original_ids = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ARRAY))
		{
			if (strcmp($row[0], "") != 0)
			{
				array_push($original_ids, $row[0]);
			}
		}
		$original_clause = "";
		if (count($original_ids))
		{
			$original_clause = " AND ISNULL(qpl_questions.original_id) AND qpl_questions.question_id NOT IN (" . join($original_ids, ",") . ")";
		}
		
		// get a list of questionpools which are not allowed for the test (only for random selection of questions in test questions editor)
		if (($questionpool == 0) && (!is_array($qpls)))
		{
			$available_pools =& $this->getAvailableQuestionpoolIDs();
			$available = "";
			$constraint_qpls = "";
			if (count($available_pools))
			{
				$available = " AND qpl_questions.obj_fi IN (" . join($available_pools, ",") . ")";
			}
			else
			{
				return array();
			}
		}
		
		$result_array = array();
		if ($questionpool == 0)
		{
			if (is_array($qpls))
			{
				if (count($qpls) > 0)
				{
					$qplidx = array();
					foreach ($qpls as $idx => $arr)
					{
						array_push($qplidx, $arr["qpl"]);
					}
					$constraint_qpls = " AND qpl_questions.obj_fi IN (" . join($qplidx, ",") . ")";
				}
			}
			$query = "SELECT COUNT(question_id) FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$available$constraint_qpls AND qpl_questions.complete = '1'$original_clause";
		}
			else
		{
			$query = sprintf("SELECT COUNT(question_id) FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND obj_fi = %s$original_clause",
				$ilDB->quote("$questionpool")
			);
		}
		$result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ARRAY);
		if (($row[0]) <= $nr_of_questions)
		{
			// take all available questions
			if ($questionpool == 0)
			{
				$query = "SELECT question_id FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$available$constraint_qpls AND qpl_questions.complete = '1'$original_clause";
			}
				else
			{
				$query = sprintf("SELECT question_id FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND obj_fi = %s AND qpl_questions.complete = '1'$original_clause",
					$ilDB->quote("$questionpool")
				);
			}
			$result = $ilDB->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_ARRAY))
			{
				if ((!in_array($row[0], $this->questions)) && (strcmp($row[0], "") != 0))
				{
					$result_array[$row[0]] = $row[0];
				}
			}
		}
			else
		{
			// select a random number out of the maximum number of questions
			mt_srand((double)microtime()*1000000);
			$random_number = mt_rand(0, $row[0] - 1);
			$securitycounter = 500;
			while ((count($result_array) < $nr_of_questions) && ($securitycounter > 0))
			{
				if ($questionpool == 0)
				{
					$query = "SELECT question_id FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$available$constraint_qpls AND qpl_questions.complete = '1'$original_clause LIMIT $random_number, 1";
				}
					else
				{
					$query = sprintf("SELECT question_id FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND obj_fi = %s AND qpl_questions.complete = '1'$original_clause LIMIT $random_number, 1",
						$ilDB->quote("$questionpool")
					);
				}
				$result = $ilDB->query($query);
				$result_row = $result->fetchRow(DB_FETCHMODE_ARRAY);
				if ((!in_array($result_row[0], $this->questions)) && (strcmp($result_row[0], "") != 0))
				{
					$result_array[$result_row[0]] = $result_row[0];
				}
				$random_number = mt_rand(0, $row[0] - 1);
				$securitycounter--;
			}
		}
		return $result_array;
	}

/**
* Returns the image path for web accessable images of a test
*
* Returns the image path for web accessable images of a test
* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_TEST/images
*
* @access public
*/
	function getImagePath() 
	{
		return CLIENT_WEB_DIR . "/assessment/" . $this->getId() . "/images/";
	}

/**
* Returns the web image path for web accessable images of a test
*
* Returns the web image path for web accessable images of a test
* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_TEST/images
*
* @access public
*/
	function getImagePathWeb() 
	{
		include_once "./classes/class.ilUtil.php";
		$webdir = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR) . "/assessment/" . $this->getId() . "/images/";
		return str_replace(ilUtil::removeTrailingPathSeparators(ILIAS_ABSOLUTE_PATH), ilUtil::removeTrailingPathSeparators(ILIAS_HTTP_PATH), $webdir);
	}

/**
* Creates a question GUI instance of a given question type
* 
* Creates a question GUI instance of a given question type
*
* @param integer $question_type The question type of the question
* @param integer $question_id The question id of the question, if available
* @return object The question GUI instance
* @access	public
*/
  function &createQuestionGUI($question_type, $question_id = -1) 
	{
    if ((!$question_type) and ($question_id > 0)) 
		{
			$question_type = $this->getQuestionType($question_id);
    }
		include_once "./assessment/classes/class.".$question_type."GUI.php";
		$question_type_gui = $question_type . "GUI";
		$question =& new $question_type_gui();
		if ($question_id > 0)
		{
			$question->object->loadFromDb($question_id);
		}
		return $question;
  }

/**
* Creates an instance of a question with a given question id
*
* Creates an instance of a question with a given question id
*
* @param integer $question_id The question id
* @return object The question instance
* @access public
*/
  function &_instanciateQuestion($question_id) 
	{
		if (strcmp($question_id, "") != 0)
		{
			include_once "./assessment/classes/class.assQuestion.php";
			$question_type = assQuestion::_getQuestionType($question_id);

			include_once "./assessment/classes/class.".$question_type.".php";
			$question = new $question_type();

			$question->loadFromDb($question_id);
			return $question;
		}
  }

/**
* Move questions to another position
*
* Move questions to another position
*
* @param array $move_questions An array with the question id's of the questions to move
* @param integer $target_index The question id of the target position
* @param integer $insert_mode 0, if insert before the target position, 1 if insert after the target position
* @access public
*/
	function moveQuestions($move_questions, $target_index, $insert_mode)
	{
		$this->questions = array_values($this->questions);
		$array_pos = array_search($target_index, $this->questions);
		if ($insert_mode == 0)
		{
			$part1 = array_slice($this->questions, 0, $array_pos);
			$part2 = array_slice($this->questions, $array_pos);
		}
		else if ($insert_mode == 1)
		{
			$part1 = array_slice($this->questions, 0, $array_pos + 1);
			$part2 = array_slice($this->questions, $array_pos + 1);
		}
		foreach ($move_questions as $question_id)
		{
			if (!(array_search($question_id, $part1) === FALSE))
			{
				unset($part1[array_search($question_id, $part1)]);
			}
			if (!(array_search($question_id, $part2) === FALSE))
			{
				unset($part2[array_search($question_id, $part2)]);
			}
		}
		$part1 = array_values($part1);
		$part2 = array_values($part2);
		$new_array = array_values(array_merge($part1, $move_questions, $part2));
		$this->questions = array();
		$counter = 1;
		foreach ($new_array as $question_id)
		{
			$this->questions[$counter] = $question_id;
			$counter++;
		}
		$this->saveQuestionsToDb();
	}

	
/**
* Returns true if the starting time of a test is reached
*
* Returns true if the starting time of a test is reached
* A starting time is not available for self assessment tests
*
* @return boolean true if the starting time is reached, otherwise false
* @access public
*/
	function startingTimeReached()
	{
		if ($this->getTestType() == TYPE_ASSESSMENT || $this->getTestType() == TYPE_ONLINE_TEST || $this->getTestType() == TYPE_VARYING_RANDOMTEST) 
		{
			if ($this->getStartingTime()) 
			{
				if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getStartingTime(), $matches))
				{
					$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
					$now = mktime();
					if ($now < $epoch_time) 
					{
						// starting time not reached
						return false;
					}
				}
			}
		}
		return true;
	}		
	
/**
* Returns true if the ending time of a test is reached
*
* Returns true if the ending time of a test is reached
* An ending time is not available for self assessment tests
*
* @return boolean true if the ending time is reached, otherwise false
* @access public
*/
	function endingTimeReached()
	{
		if ($this->getTestType() == TYPE_ASSESSMENT || $this->getTestType() == TYPE_ONLINE_TEST || $this->getTestType() == TYPE_VARYING_RANDOMTEST) 
		{
			if ($this->getEndingTime()) 
			{
				if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getEndingTime(), $matches))
				{
					$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
					$now = mktime();				
					if ($now > $epoch_time) 
					{
						// ending time reached
						return true;
					}
				}
			}
		}
		return false;
	}		
	
/**
* Calculates the data for the output of the questionpool
*
* Calculates the data for the output of the questionpool
*
* @access public
*/
	function getQuestionsTable($sortoptions, $filter_text, $sel_filter_type, $startrow = 0, $completeonly = 0, $filter_question_type = "", $filter_questionpool = "")
	{
		global $ilUser;
		global $ilDB;
		
		$where = "";
		if (strlen($filter_text) > 0) {
			switch($sel_filter_type) {
				case "title":
					$where = " AND qpl_questions.title LIKE " . $ilDB->quote("%" . $filter_text . "%");
					break;
				case "comment":
					$where = " AND qpl_questions.comment LIKE " . $ilDB->quote("%" . $filter_text . "%");
					break;
				case "author":
					$where = " AND qpl_questions.author LIKE " . $ilDB->quote("%" . $filter_text . "%");
					break;
			}
		}
		
		if ($filter_question_type && (strcmp($filter_question_type, "all") != 0))
		{
			$where .= " AND qpl_question_type.type_tag = " . $ilDB->quote($filter_question_type);
		}
		
		if ($filter_questionpool && (strcmp($filter_questionpool, "all") != 0))
		{
			$where .= " AND qpl_questions.obj_fi = $filter_questionpool";
		}
  
    // build sort order for sql query
		$order = "";
		$images = array();
    if (count($sortoptions)) 
		{
			include_once "./classes/class.ilUtil.php";
      foreach ($sortoptions as $key => $value) 
			{
        switch($key) {
          case "title":
            $order = " ORDER BY title $value";
            $images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
            break;
          case "comment":
            $order = " ORDER BY comment $value";
            $images["comment"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
            break;
          case "type":
            $order = " ORDER BY question_type_id $value";
            $images["type"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
            break;
          case "author":
            $order = " ORDER BY author $value";
            $images["author"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
            break;
          case "created":
            $order = " ORDER BY created $value";
            $images["created"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
            break;
          case "updated":
            $order = " ORDER BY TIMESTAMP14 $value";
            $images["updated"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
            break;
          case "qpl":
            $order = " ORDER BY obj_fi $value";
            $images["qpl"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . $this->lng->txt(strtolower($value) . "ending_order")."\" />";
            break;
        }
      }
    }
		$maxentries = $ilUser->prefs["hits_per_page"];
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}
		$available_pools =& $this->getAvailableQuestionpoolIDs();
		$available = "";
		if (count($available_pools))
		{
			$available = " AND qpl_questions.obj_fi IN (" . join($available_pools, ",") . ")";
		}
		else
		{
			return array();
		}
		if ($completeonly)
		{
			$available .= " AND qpl_questions.complete = " . $ilDB->quote("1");
		}

		// get all questions in the test
		$query = sprintf("SELECT qpl_questions.original_id, qpl_questions.TIMESTAMP + 0 AS TIMESTAMP14 FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND tst_test_question.test_fi = %s",
			$ilDB->quote($this->getTestId() . "")
		);
		$result = $ilDB->query($query);
		$original_ids = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ARRAY))
		{
			if (strcmp($row[0], "") != 0)
			{
				array_push($original_ids, $row[0]);
			}
		}
		$original_clause = " ISNULL(qpl_questions.original_id)";
		if (count($original_ids))
		{
			$original_clause = " ISNULL(qpl_questions.original_id) AND qpl_questions.question_id NOT IN (" . join($original_ids, ",") . ")";
		}

		$query = "SELECT qpl_questions.question_id, qpl_questions.TIMESTAMP + 0 AS TIMESTAMP14 FROM qpl_questions, qpl_question_type WHERE $original_clause$available AND qpl_questions.question_type_fi = qpl_question_type.question_type_id $where$order$limit";
    $query_result = $ilDB->query($query);
		$max = $query_result->numRows();
		if ($startrow > $max -1)
		{
			$startrow = $max - ($max % $maxentries);
		}
		else if ($startrow < 0)
		{
			$startrow = 0;
		}
		$limit = " LIMIT $startrow, $maxentries";
		$query = "SELECT qpl_questions.*, qpl_questions.TIMESTAMP + 0 AS TIMESTAMP14, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE $original_clause $available AND qpl_questions.question_type_fi = qpl_question_type.question_type_id $where$order$limit";
    $query_result = $ilDB->query($query);
		$rows = array();
		if ($query_result->numRows())
		{
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($rows, $row);
			}
		}
		$nextrow = $startrow + $maxentries;
		if ($nextrow > $max - 1)
		{
			$nextrow = $startrow;
		}
		$prevrow = $startrow - $maxentries;
		if ($prevrow < 0)
		{
			$prevrow = 0;
		}
		return array(
			"rows" => $rows,
			"images" => $images,
			"startrow" => $startrow,
			"nextrow" => $nextrow,
			"prevrow" => $prevrow,
			"step" => $maxentries,
			"rowcount" => $max
		);
	}
	
/**
* Returns the test type for a given test id
* 
* Returns the test type for a given test id
*
* @param integer $test_id The database id of the test
* @return integer The test type of the test
* @access	public
*/
	function _getTestType($active_id)
	{
		global $ilDB;
		
		$result = "";
		$query = sprintf("SELECT tst_test_type.type_tag FROM tst_test_type, tst_tests, tst_active WHERE tst_test_type.test_type_id = tst_tests.test_type_fi AND tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$query_result = $ilDB->query($query);
		if ($query_result->numRows())
		{
			$row = $query_result->fetchRow(DB_FETCHMODE_ASSOC);
			$result = $row["type_tag"];
		}
		return $result;
	}
	
	/**
	* Creates a list of all available question types
	*
	* Creates a list of all available question types
	*
	* @return array An array containing the available questiontypes
	* @access public
	*/
	function &_getQuestiontypes()
	{
		global $ilDB;
		
		$questiontypes = array();
		$query = "SELECT * FROM qpl_question_type ORDER BY type_tag";
		$query_result = $ilDB->query($query);
		while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questiontypes, $row["type_tag"]);
		}
		return $questiontypes;
	}
	
	/**
	* Creates a test from a QTI file
	*
	* Receives parameters from a QTI parser and creates a valid ILIAS test object
	*
	* @param object $assessment The QTI assessment object
	* @access public
	*/
	function fromXML(&$assessment)
	{
		unset($_SESSION["import_mob_xhtml"]);
		
		$this->setDescription($assessment->getComment());
		$this->setTitle($assessment->getTitle());

		foreach ($assessment->objectives as $objectives)
		{
			foreach ($objectives->materials as $material)
			{
				$this->setIntroduction($this->QTIMaterialToString($material));
			}
		}
		foreach ($assessment->assessmentcontrol as $assessmentcontrol)
		{
			switch ($assessmentcontrol->getSolutionswitch())
			{
				case "Yes":
					$this->setScoreReporting(1);
					break;
				default:
					$this->setScoreReporting(0);
					break;
			}
		}
		
		foreach ($assessment->qtimetadata as $metadata)
		{
			switch ($metadata["label"])
			{
				case "test_type":
					$this->setTestType($metadata["entry"]);
					break;
				case "sequence_settings":
					$this->setSequenceSettings($metadata["entry"]);
					break;
				case "author":
					$this->setAuthor($metadata["entry"]);
					break;
				case "nr_of_tries":
					$this->setNrOfTries($metadata["entry"]);
					break;
				case "hide_previous_results":
					$this->setHidePreviousResults($metadata["entry"]);
					break;
				case "hide_title_points":
					$this->setHideTitlePoints($metadata["entry"]);
					break;
				case "random_test":
					$this->setRandomTest($metadata["entry"]);
					break;
				case "random_question_count":
					$this->setRandomQuestionCount($metadata["entry"]);
					break;
				case "show_solution_details":
					$this->setShowSolutionDetails($metadata["entry"]);
					break;
				case "show_solution_printview":
					$this->setShowSolutionPrintview($metadata["entry"]);
					break;
				case "score_reporting":
					$this->setScoreReporting($metadata["entry"]);
					break;
				case "shuffle_questions":
					$this->setShuffleQuestions($metadata["entry"]);
					break;
				case "count_system":
					$this->setCountSystem($metadata["entry"]);
					break;
				case "mc_scoring":
					$this->setMCScoring($metadata["entry"]);
					break;
				case "score_cutting":
					$this->setScoreCutting($metadata["entry"]);
					break;
				case "password":
					$this->setPassword($metadata["entry"]);
					break;
				case "allowedUsers":
					$this->setAllowedUsers($metadata["entry"]);
					break;
				case "allowedUsersTimeGap":
					$this->setAllowedUsersTimeGap($metadata["entry"]);
					break;
				case "pass_scoring":
					$this->setPassScoring($metadata["entry"]);
					break;
				case "show_summary":
					$this->setShowSummary($metadata["entry"]);
					break;
				case "reporting_date":
					$iso8601period = $metadata["entry"];
					if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
					{
						$this->setReportingDate(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
					}
					break;
				case "starting_time":
					$iso8601period = $metadata["entry"];
					if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
					{
						$this->setStartingTime(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
					}
					break;
				case "ending_time":
					$iso8601period = $metadata["entry"];
					if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
					{
						$this->setEndingTime(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
					}
					break;
			}
			if (preg_match("/mark_step_\d+/", $metadata["label"]))
			{
				$xmlmark = $metadata["entry"];
				preg_match("/<short>(.*?)<\/short>/", $xmlmark, $matches);
				$mark_short = $matches[1];
				preg_match("/<official>(.*?)<\/official>/", $xmlmark, $matches);
				$mark_official = $matches[1];
				preg_match("/<percentage>(.*?)<\/percentage>/", $xmlmark, $matches);
				$mark_percentage = $matches[1];
				preg_match("/<passed>(.*?)<\/passed>/", $xmlmark, $matches);
				$mark_passed = $matches[1];
				$this->mark_schema->addMarkStep($mark_short, $mark_official, $mark_percentage, $mark_passed);
			}
		}
		// handle the import of media objects in XHTML code
		if (is_array($_SESSION["import_mob_xhtml"]))
		{
			include_once "./content/classes/Media/class.ilObjMediaObject.php";
			include_once "./Services/RTE/classes/class.ilRTE.php";
			include_once "./assessment/classes/class.ilObjQuestionPool.php";
			foreach ($_SESSION["import_mob_xhtml"] as $mob)
			{
				$importfile = $this->getImportDirectory() . "/" . $_SESSION["tst_import_subdir"] . "/" . $mob["uri"];
				if (file_exists($importfile))
				{
					$media_object =& ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
					ilObjMediaObject::_saveUsage($media_object->getId(), "tst:html", $this->getId());
					$this->setIntroduction(ilRTE::_replaceMediaObjectImageSrc(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->getIntroduction()), 1));
				}
				else
				{
					global $ilLog;
					$ilLog->write("Error: Could not open XHTML mob file for test introduction during test import. File $importfile does not exist!");
				}
			}
			$this->saveToDb();
		}
	}
	
	/**
	* Returns a QTI xml representation of the test
	*
	* Returns a QTI xml representation of the test
	*
	* @return string The QTI xml representation of the test
	* @access public
	*/
	function to_xml()
	{
		include_once("./classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$a_xml_writer->xmlStartTag("questestinterop");

		$attrs = array(
			"ident" => "il_".IL_INST_ID."_tst_".$this->getTestId(),
			"title" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("assessment", $attrs);
		// add qti comment
		$a_xml_writer->xmlElement("qticomment", NULL, $this->getDescription());

		// add qti duration
		if ($this->enable_processing_time)
		{
			preg_match("/(\d+):(\d+):(\d+)/", $this->processing_time, $matches);
			$a_xml_writer->xmlElement("duration", NULL, sprintf("P0Y0M0DT%dH%dM%dS", $matches[1], $matches[2], $matches[3]));
		}

		// add the rest of the preferences in qtimetadata tags, because there is no correspondent definition in QTI
		$a_xml_writer->xmlStartTag("qtimetadata");
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "ILIAS_VERSION");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->ilias->getSetting("ilias_version"));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// test type
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "test_type");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getTestType());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// sequence settings
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "sequence_settings");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getSequenceSettings());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// author
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "author");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAuthor());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// count system
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "count_system");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getCountSystem());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// multiple choice scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "mc_scoring");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getMCScoring());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// multiple choice scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "score_cutting");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getScoreCutting());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// multiple choice scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "password");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getPassword());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// allowed users
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "allowedUsers");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAllowedUsers());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// allowed users time gap
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "allowedUsersTimeGap");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getAllowedUsersTimeGap());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// pass scoring
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "pass_scoring");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getPassScoring());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// score reporting date
		if ($this->getReportingDate())
		{
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "reporting_date");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->reporting_date, $matches);
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		// number of tries
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "nr_of_tries");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getNrOfTries()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// hide previous results
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "hide_previous_results");
		$a_xml_writer->xmlElement("fieldentry", NULL, $this->getHidePreviousResults());
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// hide title points
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "hide_title_points");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getHideTitlePoints()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// random test
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "random_test");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->isRandomTest()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// random question count
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "random_question_count");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getRandomQuestionCount()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// solution details
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "show_solution_details");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getShowSolutionDetails()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// solution details
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "show_summary");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getShowSummary()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// solution details
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "score_reporting");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getScoreReporting()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// solution printview
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "show_solution_printview");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getShowSolutionPrintview()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// shuffle questions
		$a_xml_writer->xmlStartTag("qtimetadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "shuffle_questions");
		$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("%d", $this->getShuffleQuestions()));
		$a_xml_writer->xmlEndTag("qtimetadatafield");

		// starting time
		if ($this->getStartingTime())
		{
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "starting_time");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->starting_time, $matches);
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		// ending time
		if ($this->getEndingTime())
		{
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "ending_time");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->ending_time, $matches);
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		foreach ($this->mark_schema->mark_steps as $index => $mark)
		{
			// mark steps
			$a_xml_writer->xmlStartTag("qtimetadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, "mark_step_$index");
			$a_xml_writer->xmlElement("fieldentry", NULL, sprintf("<short>%s</short><official>%s</official><percentage>%.2f</percentage><passed>%d</passed>", $mark->getShortName(), $mark->getOfficialName(), $mark->getMinimumLevel(), $mark->getPassed()));
			$a_xml_writer->xmlEndTag("qtimetadatafield");
		}
		$a_xml_writer->xmlEndTag("qtimetadata");
		
		// add qti objectives
		$a_xml_writer->xmlStartTag("objectives");
		$this->addQTIMaterial($a_xml_writer, $this->getIntroduction());
		$a_xml_writer->xmlEndTag("objectives");

		// add qti assessmentcontrol
		$a_xml_writer->xmlElement("assessmentcontrol", NULL, NULL);

		$attrs = array(
			"ident" => "1"
		);
		$a_xml_writer->xmlElement("section", $attrs, NULL);
		$a_xml_writer->xmlEndTag("assessment");
		$a_xml_writer->xmlEndTag("questestinterop");

		$xml = $a_xml_writer->xmlDumpMem(FALSE);

		foreach ($this->questions as $question_id) 
		{
			$question =& ilObjTest::_instanciateQuestion($question_id);
			$qti_question = $question->to_xml(false);
			$qti_question = preg_replace("/<questestinterop>/", "", $qti_question);
			$qti_question = preg_replace("/<\/questestinterop>/", "", $qti_question);
			if (strpos($xml, "</section>") !== false)
			{
				$xml = str_replace("</section>", "$qti_question</section>", $xml);
			}
			else
			{
				$xml = str_replace("<section ident=\"1\"/>", "<section ident=\"1\">\n$qti_question</section>", $xml);
			}
		}
		return $xml;
	}
	
	/**
	* export pages of test to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportPagesXML(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		global $ilBench;
		
		$this->mob_ids = array();
		$this->file_ids = array();

		$attrs = array();
		$attrs["Type"] = "Test";
		$a_xml_writer->xmlStartTag("ContentObject", $attrs);

		// MetaData
		$this->exportXMLMetaData($a_xml_writer);

		// PageObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Page Objects");
		$ilBench->start("ContentObjectExport", "exportPageObjects");
		$this->exportXMLPageObjects($a_xml_writer, $a_inst, $expLog);
		$ilBench->stop("ContentObjectExport", "exportPageObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Page Objects");

		// MediaObjects
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export Media Objects");
		$ilBench->start("ContentObjectExport", "exportMediaObjects");
		$this->exportXMLMediaObjects($a_xml_writer, $a_inst, $a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportMediaObjects");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export Media Objects");

		// FileItems
		$expLog->write(date("[y-m-d H:i:s] ")."Start Export File Items");
		$ilBench->start("ContentObjectExport", "exportFileItems");
		$this->exportFileItems($a_target_dir, $expLog);
		$ilBench->stop("ContentObjectExport", "exportFileItems");
		$expLog->write(date("[y-m-d H:i:s] ")."Finished Export File Items");

		$a_xml_writer->xmlEndTag("ContentObject");
	}

	/**
	* export content objects meta data to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMetaData(&$a_xml_writer)
	{
		include_once "./Services/MetaData/classes/class.ilMD2XML.php";
		$md2xml = new ilMD2XML($this->getId(), 0, $this->getType());
		$md2xml->setExportMode(true);
		$md2xml->startExport();
		$a_xml_writer->appendXML($md2xml->getXML());
	}

/**
* Returns the installation id for a given identifier
*
* Returns the installation id for a given identifier
*
* @access	private
*/
	function modifyExportIdentifier($a_tag, $a_param, $a_value)
	{
		if ($a_tag == "Identifier" && $a_param == "Entry")
		{
			include_once "./classes/class.ilUtil.php";
			$a_value = ilUtil::insertInstIntoID($a_value);
		}

		return $a_value;
	}


	/**
	* export page objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLPageObjects(&$a_xml_writer, $a_inst, &$expLog)
	{
		global $ilBench;

		include_once "./content/classes/class.ilLMPageObject.php";

		foreach ($this->questions as $question_id)
		{
			$ilBench->start("ContentObjectExport", "exportPageObject");
			$expLog->write(date("[y-m-d H:i:s] ")."Page Object ".$question_id);

			$attrs = array();
			$a_xml_writer->xmlStartTag("PageObject", $attrs);

			
			// export xml to writer object
			$ilBench->start("ContentObjectExport", "exportPageObject_XML");
			$page_object = new ilPageObject("qpl", $question_id);
			$page_object->buildDom();
			$page_object->insertInstIntoIDs($a_inst);
			$mob_ids = $page_object->collectMediaObjects(false);
			$file_ids = $page_object->collectFileItems();
			$xml = $page_object->getXMLFromDom(false, false, false, "", true);
			$xml = str_replace("&","&amp;", $xml);
			$a_xml_writer->appendXML($xml);
			$page_object->freeDom();
			unset ($page_object);
			
			$ilBench->stop("ContentObjectExport", "exportPageObject_XML");

			// collect media objects
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectMedia");
			//$mob_ids = $page_obj->getMediaObjectIDs();
			foreach($mob_ids as $mob_id)
			{
				$this->mob_ids[$mob_id] = $mob_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectMedia");

			// collect all file items
			$ilBench->start("ContentObjectExport", "exportPageObject_CollectFileItems");
			//$file_ids = $page_obj->getFileItemIds();
			foreach($file_ids as $file_id)
			{
				$this->file_ids[$file_id] = $file_id;
			}
			$ilBench->stop("ContentObjectExport", "exportPageObject_CollectFileItems");
			
			$a_xml_writer->xmlEndTag("PageObject");
			//unset($page_obj);

			$ilBench->stop("ContentObjectExport", "exportPageObject");
			

		}
	}

	/**
	* export media objects to xml (see ilias_co.dtd)
	*
	* @param	object		$a_xml_writer	ilXmlWriter object that receives the
	*										xml data
	*/
	function exportXMLMediaObjects(&$a_xml_writer, $a_inst, $a_target_dir, &$expLog)
	{
		include_once "./content/classes/Media/class.ilObjMediaObject.php";

		foreach ($this->mob_ids as $mob_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."Media Object ".$mob_id);
			$media_obj = new ilObjMediaObject($mob_id);
			$media_obj->exportXML($a_xml_writer, $a_inst);
			$media_obj->exportFiles($a_target_dir);
			unset($media_obj);
		}
	}

	/**
	* export files of file itmes
	*
	*/
	function exportFileItems($a_target_dir, &$expLog)
	{
		include_once "./classes/class.ilObjFile.php";

		foreach ($this->file_ids as $file_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
			$file_obj = new ilObjFile($file_id, false);
			$file_obj->export($a_target_dir);
			unset($file_obj);
		}
	}

	/**
	* get array of (two) new created questions for
	* import id
	*/
	function getImportMapping()
	{
		if (!is_array($this->import_mapping))
		{
			return array();
		}
		else
		{
			return $this->import_mapping;
		}
	}
	
/**
* Returns the ECTS grade for a number of reached points
* 
* Returns the ECTS grade for a number of reached points
*
* @param double $reached_points The points reached in the test
* @param double $max_points The maximum number of points for the test
* @return string The ECTS grade short description
* @access public
*/
	function getECTSGrade($reached_points, $max_points)
	{
		include_once "./classes/class.ilStatistics.php";
		// calculate the median
		$passed_statistics = new ilStatistics();
		$passed_array =& $this->getTotalPointsPassedArray();
		$passed_statistics->setData($passed_array);
		$ects_percentiles = array
			(
				"A" => $passed_statistics->quantile($this->ects_grades["A"]),
				"B" => $passed_statistics->quantile($this->ects_grades["B"]),
				"C" => $passed_statistics->quantile($this->ects_grades["C"]),
				"D" => $passed_statistics->quantile($this->ects_grades["D"]),
				"E" => $passed_statistics->quantile($this->ects_grades["E"])
			);
			if (count($passed_array) && ($reached_points >= $ects_percentiles["A"]))
			{
				return "A";
			}
			else if (count($passed_array) && ($reached_points >= $ects_percentiles["B"]))
			{
				return "B";
			}
			else if (count($passed_array) && ($reached_points >= $ects_percentiles["C"]))
			{
				return "C";
			}
			else if (count($passed_array) && ($reached_points >= $ects_percentiles["D"]))
			{
				return "D";
			}
			else if (count($passed_array) && ($reached_points >= $ects_percentiles["E"]))
			{
				return "E";
			}
			else if (strcmp($this->ects_fx, "") != 0)
			{
				if ($max_points > 0)
				{
					$percentage = ($reached_points / $max_points) * 100.0;
					if ($percentage < 0) $percentage = 0.0;
				}
				else
				{
					$percentage = 0.0;
				}
				if ($percentage >= $this->ects_fx)
				{
					return "FX";
				}
				else
				{
					return "F";
				}
			}
			else
			{
				return "F";
			}
	}
	
	function checkMarks()
	{
		return $this->mark_schema->checkMarks();
	}
	
	function updateMetaData()
	{
		global $ilUser;
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md =& new ilMD($this->getId(), 0, $this->getType());
		$md_gen =& $md->getGeneral();
		if ($md_gen == false)
		{
			include_once "./Services/MetaData/classes/class.ilMDCreator.php";
			$md_creator = new ilMDCreator($this->getId(),0,$this->getType());
			$md_creator->setTitle($this->getTitle());
			$md_creator->setTitleLanguage($ilUser->getPref('language'));
			$md_creator->create();
		}
		parent::updateMetaData();
	}
	
/**
* Returns the available tests for the active user
*
* Returns the available tests for the active user
*
* @return array The available tests
* @access public
*/
	function &_getAvailableTests($use_object_id = false)
	{
		global $rbacsystem;
		global $ilDB;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'tst' ORDER BY object_data.title";
		$result = $ilDB->query($query);
		include_once "./classes/class.ilObject.php";
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && (ilObject::_hasUntrashedReference($row->obj_id)))
			{
				if ($use_object_id)
				{
					$result_array[$row->obj_id] = $row->title;
				}
				else
				{
					$result_array[$row->ref_id] = $row->title;
				}
			}
		}
		return $result_array;
	}

/**
* Duplicates the source random questionpools for another test
* 
* Duplicates the source random questionpools for another test
*
* @param integer $new_id Test id of the new test which should take the random questionpools
* @access public
*/
	function cloneRandomQuestions($new_id)
	{
		global $ilDB;
		
		if ($new_id > 0)
		{
			$query = sprintf("SELECT * FROM tst_test_random WHERE test_fi = %s",
				$ilDB->quote($this->getTestId() . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$query = sprintf("INSERT INTO tst_test_random (test_random_id, test_fi, questionpool_fi, num_of_q, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
						$ilDB->quote($new_id . ""),
						$ilDB->quote($row["questionpool_fi"] . ""),
						$ilDB->quote($row["num_of_q"] . "")
					);
					$insertresult = $ilDB->query($query);
				}
			}
		}
	}
	
	
/**
* Creates a 1:1 copy of the object and places the copy in a given repository
* 
* Creates a 1:1 copy of the object and places the copy in a given repository
*
* @access public
*/
	function _clone($obj_id)
	{
		$original = new ilObjTest($obj_id, false);
		$original->loadFromDb();
		
		$newObj = new ilObjTest();
		$newObj->setType("tst");
    $counter = 2;
    while ($newObj->testTitleExists($newObj->getTitle() . " ($counter)")) 
		{
      $counter++;
    }
		$newObj->setAuthor($original->getAuthor());
		$newObj->setHideTitlePoints($original->getHideTitlePoints());
		$newObj->setPassScoring($original->getPassScoring());
		$newObj->setTitle($original->getTitle() . " ($counter)");
		$newObj->setDescription($original->getDescription());
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->author = $original->getAuthor();
		$newObj->introduction = $original->getIntroduction();
		$newObj->mark_schema = $original->mark_schema;
		$newObj->sequence_settings = $original->getSequenceSettings();
		$newObj->score_reporting = $original->getScoreReporting();
		$newObj->reporting_date = $original->getReportingDate();
		$newObj->test_type = $original->getTestType();
		$newObj->nr_of_tries = $original->getNrOfTries();
		$newObj->setHidePreviousResults($original->getHidePreviousResults());
		$newObj->processing_time = $original->getProcessingTime();
		$newObj->enable_processing_time = $original->getEnableProcessingTime();
		$newObj->starting_time = $original->getStartingTime();
		$newObj->ending_time = $original->getEndingTime();
		$newObj->ects_output = $original->ects_output;
		$newObj->ects_fx = $original->ects_fx;
		$newObj->ects_grades = $original->ects_grades;
		$newObj->random_test = $original->random_test;
		$newObj->random_question_count = $original->random_question_count;
		$newObj->setCountSystem($original->getCountSystem());
		$newObj->setMCScoring($original->getMCScoring());
		$newObj->saveToDb();		
		if ($original->isRandomTest())
		{
			$newObj->saveRandomQuestionCount($newObj->random_question_count);
			$original->cloneRandomQuestions($newObj->getTestId());
		}
		else
		{
			// clone the questions
			include_once "./assessment/classes/class.assQuestion.php";
			foreach ($original->questions as $key => $question_id)
			{
				$question = ilObjTest::_instanciateQuestion($question_id);
				$newObj->questions[$key] = $question->duplicate();
	//			$question->id = -1;
				$original_id = assQuestion::_getOriginalId($question_id);
				$question = ilObjTest::_instanciateQuestion($newObj->questions[$key]);
				$question->saveToDb($original_id);
			}
		}
		
		$newObj->saveToDb();

		// clone meta data
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md = new ilMD($original->getId(),0,$original->getType());
		$new_md =& $md->cloneMD($newObj->getId(),0,$newObj->getType());
		return $newObj->getRefId();
	}

	function _getRefIdFromObjId($obj_id)
	{
		global $ilDB;

		$query = sprintf("SELECT ref_id FROM object_reference WHERE obj_id=%s",
			$ilDB->quote($obj_id)
			
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["ref_id"];
		}
		return 0;
	}
	
/**
* Returns an array of users who are selected for a test evaluation of a given user
* 
* Returns an array of users who are selected for a test evaluation of a given user
*
* @access public
*/
	function &getEvaluationUsers($user_id, $sort_name_option = "asc")
	{
		global $ilDB;
		
		$users = array();
		$query = sprintf("SELECT tst_eval_users.user_fi, usr_data.firstname, usr_data.lastname FROM tst_eval_users, usr_data WHERE tst_eval_users.test_fi = %s AND tst_eval_users.evaluator_fi = %s AND tst_eval_users.user_fi = usr_data.usr_id ORDER BY usr_data.lastname " . strtoupper($sort_name_option),
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($user_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$users[$row["user_fi"]] = trim($row["lastname"] . ", " . $row["firstname"] ." " . $row["title"]);
			}
		}
		return $users;
	}

/**
* Returns an array of users who are selected for a test evaluation of a given user
* 
* Returns an array of users who are selected for a test evaluation of a given user
*
* @access public
*/
	function &getEvaluationParticipants($user_id, $sort_name_option = "asc")
	{
		global $ilDB;
		
		$users = array();
		$query = sprintf("SELECT tst_eval_users.user_fi, usr_data.firstname, usr_data.lastname FROM tst_eval_users, usr_data WHERE tst_eval_users.test_fi = %s AND tst_eval_users.evaluator_fi = %s AND tst_eval_users.user_fi = usr_data.usr_id ORDER BY usr_data.lastname " . strtoupper($sort_name_option),
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($user_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$active = $this->getActiveTestUser($row["user_fi"]);
				if (is_object($active))
				{
					$users[$active->active_id] = trim($row["lastname"] . ", " . $row["firstname"] ." " . $row["title"]);
				}
			}
		}
		return $users;
	}

/**
* Disinvites a user from a evaluation
* 
* Disinvites a user from a evaluation
*
* @param integer $user_id The database id of the disinvited user
* @access public
*/
	function removeSelectedUser($user_id, $evaluator_id)
	{
		global $ilDB;
		
		$query = sprintf("DELETE FROM tst_eval_users WHERE test_fi = %s AND user_fi = %s AND evaluator_fi = %s",
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($user_id . ""),
			$ilDB->quote($evaluator_id . "")
		);
		$result = $ilDB->query($query);
	}

/**
* Invites a user to a evaluation
* 
* Invites a user to a evaluation
*
* @param integer $user_id The database id of the invited user
* @access public
*/
	function addSelectedUser($user_id, $evaluator_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT user_fi FROM tst_active WHERE test_fi = %s AND user_fi = %s",
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($user_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() == 1)
		{
			$query = sprintf("REPLACE INTO tst_eval_users (test_fi, evaluator_fi, user_fi) VALUES (%s, %s, %s)",
				$ilDB->quote($this->getTestId() . ""),
				$ilDB->quote($evaluator_id . ""),
				$ilDB->quote($user_id . "")
			);
			$result = $ilDB->query($query);
		}
	}

/**
* Invites a group to a evaluation
* 
* Invites a group to a evaluation
*
* @param integer $group_id The database id of the invited group
* @access public
*/
	function addSelectedGroup($group_id, $evaluator_id)
	{
		include_once "./classes/class.ilObjGroup.php";
		$group = new ilObjGroup($group_id);
		$members = $group->getGroupMemberIds();
		foreach ($members as $user_id)
		{
			$this->addSelectedUser($user_id, $evaluator_id);
		}		
	}
	
/**
* Adds a role to a evaluation
* 
* Adds a role to a evaluation
*
* @param integer $role_id The database id of the role to add
* @access public
*/
	function addSelectedRole($role_id, $evaluator_id)
	{
		global $rbacreview;
		$members =  $rbacreview->assignedUsers($role_id,"usr_id");
		foreach ($members as $user_id)
		{
			$this->addSelectedUser($user_id, $evaluator_id);
		}		
	}
	
/**
* Returns the number of questions in the test
* 
* Returns the number of questions in the test
*
* @return integer The number of questions
* @access	public
*/
	function getQuestionCount()
	{
		$num = 0;
		
		if ($this->isRandomTest())
		{
			if ($this->getRandomQuestionCount())
			{
				$num = $this->getRandomQuestionCount();
			}
				else
			{
				$qpls =& $this->getRandomQuestionpools();
				foreach ($qpls as $data)
				{
					$num += $data["count"];
				}
			}
		}
			else
		{
			$num = count($this->questions);
		}
		return $num;
	}

/**
* Returns the number of questions in the test for a given user
* 
* Returns the number of questions in the test for a given user
*
* @return integer The number of questions
* @access	public
*/
	function _getQuestionCount($test_id, $user_id)
	{
		global $ilDB;
		
		$num = 0;
		
		$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		if (!$result->numRows())
		{
			return 0;
		}
		$test = $result->fetchRow(DB_FETCHMODE_ASSOC);
		
		if ($test["random_test"] == 1)
		{
			$active = ilObjTest::_getActiveTestUser($user_id, $test_id);
			$query = sprintf("SELECT test_random_question_id FROM tst_test_random_question WHERE active_fi = %s AND pass = 0",
				$ilDB->quote($active->active_id . "")
			);
			$result = $ilDB->query($query);
			$num = $result->numRows();
		}
		else
		{
			$query = sprintf("SELECT test_question_id FROM tst_test_question WHERE test_fi = %s",
				$ilDB->quote($test_id . "")
			);
			$result = $ilDB->query($query);
			$num = $result->numRows();
		}
		return $num;
	}
	

/**
* Removes all test data of a non random test when a test was set to random test
* 
* Removes all test data of a non random test when a test was set to random test
*
* @access	private
*/
	function removeNonRandomTestData()
	{
		global $ilDB;
		
		// delete eventually set questions of a previous non-random test
		$this->removeAllTestEditings();
		$query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		$this->questions = array();
		$this->saveCompleteStatus();
	}
	
/**
* Removes all test data of a random test when a test was set to non random test
* 
* Removes all test data of a random test when a test was set to non random test
*
* @access	private
*/
	function removeRandomTestData()
	{
		global $ilDB;
		// delete eventually set random question pools of a previous random test
		$this->removeAllTestEditings();
		$query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$ilDB->quote($this->getTestId())
		);
		$result = $ilDB->query($query);
		$this->questions = array();
		$this->saveCompleteStatus();
	}
	
/**
* Logs an action into the Test&Assessment log
* 
* Logs an action into the Test&Assessment log
*
* @param string $logtext The log text
* @param integer $question_id If given, saves the question id to the database
* @access public
*/
	function logAction($logtext = "", $question_id = "")
	{
		global $ilUser;

		$original_id = "";
		if (strcmp($question_id, "") != 0)
		{
			include_once "./assessment/classes/class.assQuestion.php";
			$original_id = assQuestion::_getOriginalId($question_id);
		}
		include_once "./classes/class.ilObjAssessmentFolder.php";
		ilObjAssessmentFolder::_addLog($ilUser->id, $this->getId(), $logtext, $question_id, $original_id, TRUE, $this->getRefId());
	}
	
/**
* Returns the ILIAS test object id for a given test id
* 
* Returns the ILIAS test object id for a given test id
*
* @param integer $test_id The test id
* @return mixed The ILIAS test object id or FALSE if the query was not successful
* @access public
*/
	function _getObjectIDFromTestID($test_id)
	{
		global $ilDB;
		$object_id = FALSE;
		$query = sprintf("SELECT obj_fi FROM tst_tests WHERE test_id = %s",
			$ilDB->quote($test_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$object_id = $row["obj_fi"];
		}
		return $object_id;
	}

/**
* Returns the ILIAS test object id for a given active id
* 
* Returns the ILIAS test object id for a given active id
*
* @param integer $active_id The active id
* @return mixed The ILIAS test object id or FALSE if the query was not successful
* @access public
*/
	function _getObjectIDFromActiveID($active_id)
	{
		global $ilDB;
		$object_id = FALSE;
		$query = sprintf("SELECT tst_tests.obj_fi FROM tst_tests, tst_active WHERE tst_tests.test_id = tst_active.test_fi AND tst_active.active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$object_id = $row["obj_fi"];
		}
		return $object_id;
	}

/**
* Returns the ILIAS test id for a given object id
* 
* Returns the ILIAS test id for a given object id
*
* @param integer $object_id The object id
* @return mixed The ILIAS test id or FALSE if the query was not successful
* @access public
*/
	function _getTestIDFromObjectID($object_id)
	{
		global $ilDB;
		$test_id = FALSE;
		$query = sprintf("SELECT test_id FROM tst_tests WHERE obj_fi = %s",
			$ilDB->quote($object_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			$test_id = $row["test_id"];
		}
		return $test_id;
	}

/**
* Returns the text answer of a given user for a given question
* 
* Returns the text answer of a given user for a given question
*
* @param integer $user_id The user id
* @param integer $question_id The question id
* @return string The answer text
* @access public
*/
	function getTextAnswer($active_id, $question_id, $pass = NULL)
	{
		global $ilDB;
		
		$res = "";
		if (($active_id) && ($question_id))
		{
			if (is_null($pass))
			{
				include_once "./assessment/classes/class.assQuestion.php";
				$pass = assQuestion::_getSolutionMaxPass($question_id, $active_id);
			}
			$query = sprintf("SELECT value1 FROM tst_solutions WHERE active_fi = %s AND question_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($question_id . ""),
				$ilDB->quote($pass . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows() == 1)
			{
				$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
				$res = $row["value1"];
			}
		}
		return $res;
	}
	
/**
* Returns the question text for a given question
* 
* Returns the question text for a given question
*
* @param integer $question_id The question id
* @return string The question text
* @access public
*/
	function getQuestiontext($question_id)
	{
		global $ilDB;
		
		$res = "";
		if ($question_id)
		{
			$query = sprintf("SELECT question_text FROM qpl_questions WHERE question_id = %s",
				$ilDB->quote($question_id . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows() == 1)
			{
				$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
				$res = $row["question_text"];
			}
		}
		return $res;		
	}
	
/**
* Returns a list of all invited users in a test
* 
* Returns a list of all invited users in a test
*
* @return array The user id's of the invited users
* @access public
*/
	function &getInvitedUsers($user_id="", $order="login, lastname, firstname")
	{
		global $ilDB;
		
		$result_array = array();

		if (is_numeric($user_id))
			$query = sprintf("SELECT usr_id, login, lastname, firstname, t.clientip, test.submitted as test_finished, matriculation, IF(test.active_id IS NULL,0,1) as test_started " .
							 "FROM tst_invited_user t, usr_data ".
							 "LEFT JOIN tst_active test ON test.user_fi=usr_id AND test.test_fi=t.test_fi ".
							 "WHERE t.test_fi = %s and t.user_fi=usr_id AND usr_id=%s ".
							 "ORDER BY %s",
				$ilDB->quote($this->test_id),
				$user_id,
				$order
			);
		else 
		{
			$query = sprintf("SELECT usr_id, login, lastname, firstname, t.clientip, test.submitted as test_finished, matriculation, IF(test.active_id IS NULL,0,1) as test_started " .							 				
							 "FROM tst_invited_user t, usr_data ".
							 "LEFT JOIN tst_active test ON test.user_fi=usr_id AND test.test_fi=t.test_fi ".
							 "WHERE t.test_fi = %s and t.user_fi=usr_id ".
							 "ORDER BY %s",
				$ilDB->quote($this->test_id),
				$order
			);
		}
		
		return $this->getArrayData($query, "usr_id");
	}
	
/**
* Returns a data of all users specified by id list
* 
* Returns a data of all users specified by id list
*
* @param $usr_ids kommaseparated list of ids
* @return array The user data "usr_id, login, lastname, firstname, clientip" of the users with id as key
* @access public
*/
	function &getUserData($ids)
	{
		if (!is_array($ids) || count($ids) ==0)
			return array();
			
		$result_array = array();
			
		$query = sprintf("SELECT usr_id, login, lastname, firstname, client_ip as clientip FROM usr_data WHERE usr_id IN (%s) ORDER BY login",			
			join ($ids,",")
		);
				
		return $this->getArrayData ($query, "usr_id");		
	}
	
/**
* Returns a data as id key list
* 
* Returns a data as id key list
*
* @param $query
* @param $id_field index for array 
* @return array with data with id as key
* @access private
*/
	function &getArrayData($query, $id_field)
	{	
		return ilObjTest::_getArrayData ($query, $id_field);
	}
	
	function &_getArrayData($query, $id_field)
	{	
		global $ilDB;
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_array[$row->$id_field]= $row;
		}
		return ($result_array)?$result_array:array();
	}

	function &getGroupData($ids)
	{
		if (!is_array($ids) || count($ids) ==0)
			return array();
			
		$result_array = array();
			
		$query = sprintf("SELECT ref_id, title, description FROM `grp_data` g, object_data o, object_reference r WHERE o.obj_id=grp_id AND o.obj_id = r.obj_id AND ref_id IN (%s)",			
			join ($ids,",")
		);
		
		return $this->getArrayData ($query, "ref_id");		
	}
	
	function &getRoleData($ids)
	{
		if (!is_array($ids) || count($ids) ==0)
			return array();
			
		$result_array = array();
			
		$query = sprintf("SELECT obj_id, description, title FROM role_data, object_data o WHERE o.obj_id=role_id AND role_id IN (%s)",			
			join ($ids,",")
		);
		
		return $this->getArrayData ($query, "obj_id");		
	}


/**
* Invites all users of a group to a test
* 
* Invites all users of a group to a test
*
* @param integer $group_id The database id of the invited group
* @access public
*/
	function inviteGroup($group_id)
	{			
		include_once "./classes/class.ilObjGroup.php";
		$group = new ilObjGroup($group_id);
		$members = $group->getGroupMemberIds();
		include_once "./classes/class.ilObjUser.php";
		foreach ($members as $user_id)
		{
			$this->inviteUser($user_id, ilObjUser::_lookupClientIP($user_id));
		}		
	}
	
/**
* Invites all users of a role to a test
* 
* Invites all users of a role to a test
*
* @param integer $group_id The database id of the invited group
* @access public
*/
	function inviteRole($role_id)
	{			
		global $rbacreview;
		$members =  $rbacreview->assignedUsers($role_id,"usr_id");
		include_once "./classes/class.ilObjUser.php";
		foreach ($members as $user_id)
		{
			$this->inviteUser($user_id, ilObjUser::_lookupClientIP($user_id));
		}		
	}
	
	
	
/**
* Disinvites a user from a test
* 
* Disinvites a user from a test
*
* @param integer $user_id The database id of the disinvited user
* @access public
*/
	function disinviteUser($user_id)
	{
		global $ilDB;
		
		$query = sprintf("DELETE FROM tst_invited_user WHERE test_fi = %s AND user_fi = %s",
			$ilDB->quote($this->test_id),
			$ilDB->quote($user_id)
		);
		$result = $ilDB->query($query);	
	}

/**
* Invites a user to a test
* 
* Invites a user to a test
*
* @param integer $user_id The database id of the invited user
* @access public
*/
	function inviteUser($user_id, $client_ip="")
	{
		global $ilDB;
		
		$query = sprintf("INSERT IGNORE INTO tst_invited_user (test_fi, user_fi, clientip) VALUES (%s, %s, %s)",
			$ilDB->quote($this->test_id),
			$ilDB->quote($user_id),
			$ilDB->quote($client_ip)
		);
		
		$result = $ilDB->query($query);
	}
	
		
	function setClientIP($user_id, $client_ip) 
	{
		global $ilDB;

		$query = sprintf("UPDATE tst_invited_user SET clientip=%s WHERE test_fi=%s and user_fi=%s",
				$ilDB->quote($client_ip),
				$ilDB->quote($this->test_id),
				$ilDB->quote($user_id)
		);
		$insertresult = $ilDB->query($query);
	}
	
	/**
	 * gets TestType equals Online Test
	 * 
	 * @return	boolean true, if test type equals Online Test otherwise false
	 */
	
	function isOnlineTest() 
	{
		return $this->getTestType()==TYPE_ONLINE_TEST;
	}
	
	/**
	 * get solved questions
	 * 
	 * @return array of int containing all question ids which have been set solved for the given user and test
	 */
	function _getSolvedQuestions($active_id, $question_fi = null) 
	{
		global $ilDB;
		if (is_numeric($question_fi))
			$query = sprintf("SELECT question_fi, solved FROM tst_active_qst_sol_settings " .
						 "WHERE active_fi = %s AND question_fi=%s",
							$ilDB->quote($active_id),
							$question_fi
			);
		else $query = sprintf("SELECT question_fi, solved FROM tst_active_qst_sol_settings " .
						 "WHERE active_fi = %s",
			$ilDB->quote($active_id)
		);
		return ilObjTest::_getArrayData ($query, "question_fi");		
	}
	
	
	/**
	 * sets question solved state to value for given user_id
	 */
	function setQuestionSetSolved($value, $question_id, $user_id) 
	{
		global $ilDB;
		
		$active = $this->getActiveTestUser($user_id);
		$query = sprintf("REPLACE INTO tst_active_qst_sol_settings SET solved=%s, question_fi=%s, active_fi = %s",
			$ilDB->quote($value),
			$ilDB->quote($question_id),
			$ilDB->quote($active->active_id)
		);
		
		$ilDB->query($query);				
	}
	
	
	/**
	 * submits active test for user user_id
	 */
	function setActiveTestSubmitted($user_id) 
	{
		global $ilDB;
		
		$query = sprintf("UPDATE tst_active SET submitted=1, tries=1, submittimestamp=NOW() WHERE test_fi=%s AND user_fi=%s",
			$ilDB->quote($this->test_id),
			$ilDB->quote($user_id)
		);		
		$ilDB->query($query);				
		
	}
	
	/**
	 * returns if the active for user_id has been submitted
	 */
	function isActiveTestSubmitted($user_id = null) 
	{
		global $ilUser;
		global $ilDB;
		
		if (!is_numeric($user_id))
			$user_id = $ilUser->getId();
			
		$query = sprintf("SELECT submitted FROM tst_active WHERE test_fi=%s AND user_fi=%s AND submitted=1",
			$ilDB->quote($this->test_id),
			$ilDB->quote($user_id)
		);		
		$result = $ilDB->query($query);		
		
		return 	$result->numRows() == 1;
		
	}
	/**
	 * returns if the numbers of tries have to be checked
	 */
	function hasNrOfTriesRestriction() 
	{
		return $this->getNrOfTries() != 0;
	}
	
	
	/**
	 * returns if number of tries are reached
	 */
	
	function isNrOfTriesReached($tries) 
	{
		return $tries >= (int) $this->getNrOfTries();
	}
	
	
	/**
	 * returns all test results for all participants
	 */
	function getAllTestResults() 
	{
		$participants = $this->getInvitedUsers("matriculation");
		$results = array();		
		$row = array("matriculation" =>  $this->lng->txt("matriculation"),
					"lastname" =>  $this->lng->txt("lastname"),
					"firstname" => $this->lng->txt("firstname"),					
					"reached_points" => $this->lng->txt("tst_reached_points"),
					"max_points" => $this->lng->txt("tst_maximum_points"),
					"percent_value" => $this->lng->txt("tst_percent_solved"),
					"mark" => $this->lng->txt("tst_mark"),
					"ects" => $this->lng->txt("ects_grade"));
		
		$results[] = $row;
		foreach ($participants as $user_id => $user_rec) 
		{
			$row = array();		
			$active = $this->getActiveTestUser($user_id);
			$reached_points = 0;
			$max_points = 0;					
			
			foreach ($this->questions as $value)
			{
			//$value = $this->questions[$seq];
//				$ilBench->start("getTestResult","instanciate question"); 
				$question =& ilObjTest::_instanciateQuestion($value);
//				$ilBench->stop("getTestResult","instanciate question"); 
				if (is_object($question))
				{
					$max_points += $question->getMaximumPoints();
					$reached_points += $question->getReachedPoints($active->active_id); 
				}
			}
			
			if ($max_points > 0)
			{
				$percentvalue = $reached_points / $max_points;
				if ($percentvalue < 0) $percentvalue = 0.0;
			}
			else
			{
				$percentvalue = 0;
			}			
			$mark_obj = $this->mark_schema->getMatchingMark($percentvalue * 100);
			$passed = "";	
			if ($mark_obj)
			{
				$mark = $mark_obj->getOfficialName();
				$ects_mark = $this->getECTSGrade($reached_points, $max_points);
			}

			$row = array(
					"matriculation" =>  $user_rec->matriculation,
					"lastname" =>  $user_rec->lastname,
					"firstname" => $user_rec->firstname,
					"reached_points" => $reached_points,
					"max_points" => $max_points,
					"percent_value" => $percentvalue,
					"mark" => $mark,
					"ects" => $ects_mark);
			$results[] = $this->processCSVRow ($row, true);
		} 								
		return $results;
	}
	
/**
* Convertes an array for CSV usage
* 
* Processes an array as a CSV row and converts the array values to correct CSV
* values. The "converted" array is returned
*
* @param array $row The array containing the values for a CSV row
* @param string $quoteAll Indicates to quote every value (=TRUE) or only values containing quotes and separators (=FALSE, default)
* @param string $separator The value separator in the CSV row (used for quoting) (; = default)
* @return array The converted array ready for CSV use
* @access public
*/
	function &processCSVRow($row, $quoteAll = FALSE, $separator = ";")
	{
		$resultarray = array();
		foreach ($row as $rowindex => $entry)
		{
			$surround = FALSE;
			if ($quoteAll)
			{
				$surround = TRUE;
			}
			if (strpos($entry, "\"") !== FALSE)
			{
				$entry = str_replace("\"", "\"\"", $entry);
				$surround = TRUE;
			}
			if (strpos($entry, $separator) !== FALSE)
			{
				$surround = TRUE;
			}
			// replace all CR LF with LF (for Excel for Windows compatibility
			$entry = str_replace(chr(13).chr(10), chr(10), $entry);
			if ($surround)
			{
				$resultarray[$rowindex] = utf8_decode("\"" . $entry . "\"");
			}
			else
			{
				$resultarray[$rowindex] = utf8_decode($entry);
			}
		}
		return $resultarray;
	}
	
/**
* Retrieves the actual pass of a given user for a given test
* 
* Retrieves the actual pass of a given user for a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @return integer The pass of the user for the given test
* @access public
*/
	function _getPass($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT tries FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["tries"];
		}
		else
		{
			return 0;
		}
	}
	
/**
* Retrieves the best pass of a given user for a given test
* 
* Retrieves the best pass of a given user for a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @return integer The best pass of the user for the given test
* @access public
*/
	function _getBestPass($active_id)
	{
		global $ilDB;
		$lastpass = ilObjTest::_getPass($active_id);
		$bestpass = 0;
		$maxpoints = 0;
		for ($i = 0; $i <= $lastpass; $i++)
		{
			$query = sprintf("SELECT SUM(points) AS maxpoints FROM tst_test_result WHERE active_fi = %s AND pass = %s",
				$ilDB->quote($active_id . ""),
				$ilDB->quote($i . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows())
			{
				$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
				if ($row["maxpoints"] > $maxpoints)
				{
					$maxpoints = $row["maxpoints"];
					$bestpass = $i;
				}
			}
		}
		return $bestpass;
	}
	
/**
* Retrieves the pass number that should be counted for a given user
* 
* Retrieves the pass number that should be counted for a given user
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @return integer The result pass of the user for the given test
* @access public
*/
	function _getResultPass($active_id)
	{
		$counted_pass = NULL;
		if (strcmp(ilObjTest::_getTestType($active_id), "tt_varying_randomtest") == 0)
		{
			if (ilObjTest::_getPassScoring($active_id) == SCORE_BEST_PASS)
			{
				$counted_pass = ilObjTest::_getBestPass($active_id);
			}
			else
			{
				$counted_pass = ilObjTest::_getPass($active_id);
				global $ilDB;
				$query = sprintf("SELECT test_result_id FROM tst_test_result WHERE active_fi = %s AND pass = %s",
					$ilDB->quote($active_id . ""),
					$ilDB->quote($counted_pass . "")
				);
				$result = $ilDB->query($query);
				if ($result->numRows() == 0)
				{
					// There was no answer answered in the actual pass, so the last pass is
					// $counted_pass - 1
					$counted_pass -= 1;
				}
				if ($counted_pass < 0) $counted_pass = 0;
			}
		}
		return $counted_pass;
	}
	
/**
* Retrieves the number of answered questions for a given user in a given test
* 
* Retrieves the number of answered questions for a given user in a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @param integer $pass The pass of the test (optional)
* @return integer The number of answered questions
* @access public
*/
	function getAnsweredQuestionCount($active_id, $pass = NULL)
	{
		if ($this->isRandomTest())
		{
			$this->loadQuestions($active_id, $pass);
		}
		include_once "./assessment/classes/class.assQuestion.php";
		$workedthrough = 0;
		foreach ($this->questions as $value)
		{
			if (assQuestion::_isWorkedThrough($active_id, $value, $pass))
			{
				$workedthrough += 1;
			}
		}
		return $workedthrough;
	}

/**
* Retrieves the number of answered questions for a given user in a given test
* 
* Retrieves the number of answered questions for a given user in a given test
*
* @param integer $user_id The user id
* @param integer $test_id The test id
* @param integer $pass The pass of the test
* @return timestamp The SQL timestamp of the finished pass
* @access public
*/
	function getPassFinishDate($active_id, $pass)
	{
		global $ilDB;
		if (is_null($pass)) $pass = 0;
		$query = sprintf("SELECT tst_test_result.TIMESTAMP + 0 AS TIMESTAMP14 FROM tst_test_result WHERE active_fi = %s AND pass = %s ORDER BY tst_test_result.TIMESTAMP DESC",
			$ilDB->quote($active_id . ""),
			$ilDB->quote($pass . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["TIMESTAMP14"];
		}
		else
		{
			return 0;
		}
	}
	
/**
* Checks if the test is executable by the given user
* 
* Checks if the test is executable by the given user
*
* @param integer $user_id The user id
* @return array Result array
* @access public
*/
	function isExecutable($user_id)
	{
		$result = array(
			"executable" => true,
			"errormessage" => ""
		);
		if (!$this->startingTimeReached())
		{
			$result["executable"] = false;
			$result["errormessage"] = sprintf($this->lng->txt("detail_starting_time_not_reached"), ilFormat::ftimestamp2datetimeDB($this->getStartingTime()));
			return $result;
		}
		if ($this->endingTimeReached())
		{
			$result["executable"] = false;
			$result["errormessage"] = sprintf($this->lng->txt("detail_ending_time_reached"), ilFormat::ftimestamp2datetimeDB($this->getEndingTime()));
			return $result;
		}
		if ($this->getEnableProcessingTime())
		{
			$starting_time = $this->getStartingTimeOfUser($user_id);
			if ($starting_time !== FALSE)
			{
				if ($this->isMaxProcessingTimeReached($starting_time))
				{
					$result["executable"] = false;
					$result["errormessage"] = $this->lng->txt("detail_max_processing_time_reached");
					return $result;
				}
			}
		}

		$active = $this->getActiveTestUser($user_id);
		if ($this->hasNrOfTriesRestriction() && is_object($active) && $this->isNrOfTriesReached($active->tries))
		{
			$result["executable"] = false;
			if ($this->isOnlineTest())
			{
				// don't display an errormessage for online exams. It could confuse users
				// because they will get either a finish test button or a print test button
				$result["errormessage"] = "";
			}
			else
			{
				$result["errormessage"] = $this->lng->txt("maximum_nr_of_tries_reached");
			}
			return $result;
		}
		
		// TODO: max. processing time
		
		return $result;
	}

	function canShowTestResults($user_id) 
	{
		$active = $this->getActiveTestUser($user_id);
		$starting_time = $this->getStartingTimeOfUser($user_id);
		$notimeleft = FALSE;
		if ($starting_time !== FALSE)
		{
			if ($this->isMaxProcessingTimeReached($starting_time))
			{
				$notimeleft = TRUE;
			}
		}
		$result = $this->canViewResults();
		if (($active->tries == 0) && ($this->getScoreReporting() == REPORT_AFTER_TEST))
		{
			$result = FALSE;
		}
		if (($this->endingTimeReached()) || $notimeleft) $result = TRUE;
		if ($this->getTestType() == TYPE_ONLINE_TEST)
		{
			return $result && $this->isActiveTestSubmitted();
		}
		return $result;
	}
	
	function canEditMarks()
	{
		$total = $this->evalTotalPersons();
		if ($total > 0)
		{
			if ($this->getReportingDate())
			{
				if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getReportingDate(), $matches))
				{
					$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
					$now = mktime();
					if ($now < $epoch_time) 
					{
						return true;
					}
				}
			}
			return false;
		}
		else
		{
			return true;
		}
	}
	
/**
* Returns the unix timestamp of the time a user started a test
* 
* Returns the unix timestamp of the time a user started a test
*
* @param integer $user_id The user id
* @return mixed The unix timestamp if the user started the test, FALSE otherwise
* @access public
*/
	function getStartingTimeOfUser($user_id)
	{
		global $ilDB;
		
		if ($user_id < 1) return FALSE;
		$query = sprintf("SELECT tst_times.started FROM tst_times, tst_active WHERE tst_active.user_fi = %s AND tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi ORDER BY tst_times.started",
			$ilDB->quote($user_id . ""),
			$ilDB->quote($this->getTestId() . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			if (preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row["started"], $matches))
			{
				return mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
/**
* Returns wheather the maximum processing time for a test is reached or not
* 
* Returns wheather the maximum processing time for a test is reached or not
*
* @param long $starting_time The unix timestamp of the starting time of the test
* @return boolean TRUE if the maxium processing time is reached, FALSE if the
*					maximum processing time is not reached or no maximum processing time is given
* @access public
*/
	function isMaxProcessingTimeReached($starting_time)
	{
		if ($this->getEnableProcessingTime())
		{
			$processing_time = $this->getProcessingTimeInSeconds();
			$now = mktime();
			if ($now > ($starting_time + $processing_time))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return FALSE;
		}
	}
	
	function &getTestQuestions()
	{
		global $ilDB;
		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type, tst_test_question WHERE qpl_questions.question_type_fi = qpl_question_type.question_type_id AND tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id ORDER BY sequence",
			$ilDB->quote($this->getTestId() . "")
		);
		$query_result = $ilDB->query($query);
		$removableQuestions = array();
		while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($removableQuestions, $row);
		}
		return $removableQuestions;
	}
	
/**
* Returns the status of the shuffle_questions variable
* 
* Returns the status of the shuffle_questions variable
*
* @return boolean FALSE if the test questions are not shuffled, TRUE if the test questions are shuffled
* @access public
*/
	function getShuffleQuestions()
	{
		return $this->shuffle_questions;
	}
	
/**
* Sets the status of the shuffle_questions variable
* 
* Sets the status of the shuffle_questions variable
*
* @param boolean $a_shuffle FALSE if the test questions are not shuffled, TRUE if the test questions are shuffled
* @access public
*/
	function setShuffleQuestions($a_shuffle)
	{
		if ($a_shuffle)
		{
			$this->shuffle_questions = TRUE;
		}
		else
		{
			$this->shuffle_questions = FALSE;
		}
	}

/**
* Returns if the solution details should be presented to the user or not
* 
* Returns if the solution details should be presented to the user or not
*
* @return boolean TRUE if the solution details should be presented, FALSE otherwise
* @access public
*/
	function getShowSolutionDetails()
	{
		return $this->show_solution_details;
	}
	
/**
* Returns if the question summary should be presented to the user or not
* 
* Returns if the question summary should be presented to the user or not
*
* @return boolean TRUE if the question summary should be presented, FALSE otherwise
* @access public
*/
	function getShowSummary()
	{
		return $this->show_summary;
	}
	
/**
* Returns if the solution printview should be presented to the user or not
* 
* Returns if the solution printview should be presented to the user or not
*
* @return boolean TRUE if the solution printview should be presented, FALSE otherwise
* @access public
*/
	function getShowSolutionPrintview()
	{
		return $this->show_solution_printview;
	}
	
/**
* Sets if the the solution details should be presented to the user or not
* 
* Sets if the the solution details should be presented to the user or not
*
* @param boolean $a_details TRUE if the solution details should be presented, FALSE otherwise
* @access public
*/
	function setShowSolutionDetails($a_details = TRUE)
	{
		if ($a_details)
		{
			$this->show_solution_details = TRUE;
		}
		else
		{
			$this->show_solution_details = FALSE;
		}
	}

/**
* Sets if the the question summary should be presented to the user or not
* 
* Sets if the the question summary should be presented to the user or not
*
* @param boolean $a_details TRUE if the question_summary should be presented, FALSE otherwise
* @access public
*/
	function setShowSummary($a_summary = TRUE)
	{
		if ($a_summary)
		{
			$this->show_summary = TRUE;
		}
		else
		{
			$this->show_summary = FALSE;
		}
	}

/**
* Calculates if a user may see the solution printview of his/her test results
* 
* Calculates if a user may see the solution printview of his/her test results
*
* @return boolean TRUE if the user may see the printview, FALSE otherwise
* @access public
*/
	function canShowSolutionPrintview($user_id = NULL) 
	{
		global $ilDB;
		
		if (!is_numeric($user_id))
		{
			global $ilUser;
			$user_id = $ilUser->getId();
		}
		
		if ($this->isOnlineTest())
		{
			$query = sprintf("SELECT submitted FROM tst_active WHERE test_fi=%s AND user_fi=%s AND submitted=1",
				$ilDB->quote($this->getTestId() . ""),
				$ilDB->quote($user_id . "")
			);		
			$result = $ilDB->query($query);		
			return $result->numRows() == 1;
		}
		else
		{
			if (($this->canShowTestResults($user_id)) && ($this->getShowSolutionPrintview()))
			{
				return TRUE;
			}
			else
			{
				return FALSE;
			}
		}
		return FALSE;
	}
	
/**
* Sets if the the solution printview should be presented to the user or not
* 
* Sets if the the solution printview should be presented to the user or not
*
* @param boolean $a_details TRUE if the solution printview should be presented, FALSE otherwise
* @access public
*/
	function setShowSolutionPrintview($a_printview = TRUE)
	{
		if ($a_printview)
		{
			$this->show_solution_printview = TRUE;
		}
		else
		{
			$this->show_solution_printview = FALSE;
		}
	}
	
/**
* Returns an array containing the user ids of all users who passed the test
*
* Returns an array containing the user ids of all users who passed the test,
* regardless if they fnished the test with the finish test button or not. Only
* the reached points are counted
*
* @param integer $test_id Test id of the test
* @return array An array containing the user ids of the users who passed the test
* @access public
*/
	function &_getPassedUsers($a_obj_id)
	{
		global $ilDB;
		
		$passed_users = array();
		$query = sprintf("SELECT tst_active.* FROM tst_active, tst_tests ".
						 "WHERE tst_tests.obj_fi = %s ".
						 "AND tst_active.test_fi = tst_tests.test_id",
			$ilDB->quote($a_obj_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$active_id = $row["active_id"];
				$pass = ilObjTest::_getResultPass($active_id);
				include_once "./assessment/classes/class.ilObjTestAccess.php";
				$testres =& ilObjTestAccess::_getTestResult($active_id, $pass);
				if ((bool) $testres['passed'])
				{
					array_push($passed_users, $active_id);
				}
			}
		}
		return $passed_users;
	}
	
	/**
	* Returns a new, unused test access code
	*
	* @return	string A new test access code
	*/
	function createNewAccessCode()
	{
		// create a 5 character code
		$codestring = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		mt_srand();
		$code = "";
		for ($i = 1; $i <=5; $i++)
		{
			$index = mt_rand(0, strlen($codestring)-1);
			$code .= substr($codestring, $index, 1);
		}
		// verify it against the database
		while ($this->isAccessCodeUsed($code))
		{
			$code = $this->createNewAccessCode();
		}
		return $code;
	}
	
	function isAccessCodeUsed($code)
	{
		global $ilDB;
		
		$query = sprintf("SELECT anonymous_id FROM tst_active WHERE test_fi = %s AND anonymous_id = %s",
			$ilDB->quote($this->getTestId() . ""),
			$ilDB->quote($code . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows() > 0)
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	function _getUserIdFromActiveId($active_id)
	{
		global $ilDB;
		$query = sprintf("SELECT user_fi FROM tst_active WHERE active_id = %s",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["user_fi"];
		}
		else
		{
			return -1;
		}
	}
	
	function getAccessCodeSession()
	{
		$id = $this->getTestId();
		if (!is_array($_SESSION["tst_access_code"]))
		{
			return "";
		}
		else
		{
			return $_SESSION["tst_access_code"]["$id"];
		}
	}
	
	function setAccessCodeSession($access_code)
	{
		$id = $this->getTestId();
		if (!is_array($_SESSION["tst_access_code"]))
		{
			$_SESSION["tst_access_code"] = array();
		}
		$_SESSION["tst_access_code"]["$id"] = $access_code;
	}
	
	function unsetAccessCodeSession()
	{
		$id = $this->getTestId();
		unset($_SESSION["tst_access_code"]["$id"]);
	}
	
	function getAllowedUsers()
	{
		return $this->allowedUsers;
	}
	
	function setAllowedUsers($a_allowed_users)
	{
		$this->allowedUsers = $a_allowed_users;
	}
	
	function getAllowedUsersTimeGap()
	{
		return $this->allowedUsersTimeGap;
	}
	
	function setAllowedUsersTimeGap($a_allowed_users_time_gap)
	{
		$this->allowedUsersTimeGap = $a_allowed_users_time_gap;
	}
	
	function checkMaximumAllowedUsers()
	{
		global $ilDB;
		
		$nr_of_users = $this->getAllowedUsers();
		$time_gap = $this->getAllowedUsersTimeGap();
		if (($nr_of_users > 0) && ($time_gap > 0))
		{
			$now = mktime();
			$time_border = $now - $time_gap;
			$str_time_border = strftime("%Y%m%d%H%M%S", $time_border);
			$query = sprintf("SELECT DISTINCT tst_times.active_fi, tst_times.times_id FROM tst_times, tst_active WHERE tst_times.TIMESTAMP > %s AND tst_times.active_fi = tst_active.active_id AND tst_active.test_fi = %s",
				$ilDB->quote($str_time_border),
				$ilDB->quote($this->getTestId() . "")
			);
			$result = $ilDB->query($query);
			if ($result->numRows() >= $nr_of_users)
			{
				if (ilObjAssessmentFolder::_enabledAssessmentLogging())
				{
					include_once ("./classes/class.ilObjAssessmentFolder.php");
					$this->logAction($this->lng->txtlng("assessment", "log_could_not_enter_test_due_to_simultaneous_users", ilObjAssessmentFolder::_getLogLanguage()));
				}
				return FALSE;
			}
			else
			{
				return TRUE;
			}
		}
		return TRUE;
	}
	
	function _getLastAccess($active_id)
	{
		global $ilDB;
		
		$query = sprintf("SELECT finished FROM tst_times WHERE active_fi = %s ORDER BY finished DESC",
			$ilDB->quote($active_id . "")
		);
		$result = $ilDB->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["finished"];
		}
		return "";
	}

	/**
	* Checks if a given string contains HTML or not
	*
	* @param string $a_text Text which should be checked
	
	* @return boolean 
	* @access public
	*/
	function isHTML($a_text)
	{
		if (preg_match("/<[^>]*?>/", $a_text))
		{
			return TRUE;
		}
		else
		{
			return FALSE; 
		}
	}
	
	/**
	* Reads an QTI material tag an creates a text string
	*
	* @param string $a_material QTI material tag
	* @return string text or xhtml string
	* @access public
	*/
	function QTIMaterialToString($a_material)
	{
		$result = "";
		for ($i = 0; $i < $a_material->getMaterialCount(); $i++)
		{
			$material = $a_material->getMaterial($i);
			if (strcmp($material["type"], "mattext") == 0)
			{
				$result .= $material["material"]->getContent();
			}
			if (strcmp($material["type"], "matimage") == 0)
			{
				$matimage = $material["material"];
				if (preg_match("/(il_([0-9]+)_mob_([0-9]+))/", $matimage->getLabel(), $matches))
				{
					// import an mediaobject which was inserted using tiny mce
					if (!is_array($_SESSION["import_mob_xhtml"])) $_SESSION["import_mob_xhtml"] = array();
					array_push($_SESSION["import_mob_xhtml"], array("mob" => $matimage->getLabel(), "uri" => $matimage->getUri()));
				}
			}
		}
		return $result;
	}
	
	/**
	* Creates a QTI material tag from a plain text or xhtml text
	*
	* @param object $a_xml_writer Reference to the ILIAS XML writer
	* @param string $a_material plain text or html text containing the material
	* @return string QTI material tag
	* @access public
	*/
	function addQTIMaterial(&$a_xml_writer, $a_material)
	{
		include_once "./Services/RTE/classes/class.ilRTE.php";
		include_once("./content/classes/Media/class.ilObjMediaObject.php");

		$a_xml_writer->xmlStartTag("material");
		$attrs = array(
			"texttype" => "text/plain"
		);
		if ($this->isHTML($a_material))
		{
			$attrs["texttype"] = "text/xhtml";
		}
		$a_xml_writer->xmlElement("mattext", $attrs, ilRTE::_replaceMediaObjectImageSrc($a_material, 0));

		$mobs = ilObjMediaObject::_getMobsOfObject("tst:html", $this->getId());
		foreach ($mobs as $mob)
		{
			$mob_obj =& new ilObjMediaObject($mob);
			$imgattrs = array(
				"label" => "il_" . IL_INST_ID . "_mob_" . $mob,
				"uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle()
			);
			$a_xml_writer->xmlElement("matimage", $imgattrs, NULL);
		}
		$a_xml_writer->xmlEndTag("material");
	}
} // END class.ilObjTest

?>
