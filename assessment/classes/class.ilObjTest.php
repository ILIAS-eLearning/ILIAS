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
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package assessment
*/

require_once "./classes/class.ilObject.php";
require_once "./assessment/classes/class.assMarkSchema.php";
require_once "./classes/class.ilMetaData.php";
require_once "./assessment/classes/class.assQuestion.php";
require_once "./assessment/classes/class.assClozeTest.php";
require_once "./assessment/classes/class.assImagemapQuestion.php";
require_once "./assessment/classes/class.assJavaApplet.php";
require_once "./assessment/classes/class.assMatchingQuestion.php";
require_once "./assessment/classes/class.assMultipleChoice.php";
require_once "./assessment/classes/class.assOrderingQuestion.php";
require_once "./classes/class.ilObjAssessmentFolder.php";

define("TEST_FIXED_SEQUENCE", 0);
define("TEST_POSTPONE", 1);

define("REPORT_AFTER_QUESTION", 0);
define("REPORT_AFTER_TEST", 1);

define("TYPE_ASSESSMENT", "1");
define("TYPE_SELF_ASSESSMENT", "2");
define("TYPE_NAVIGATION_CONTROLLING", "3");

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
* Test formats
* 
* The test formats given for the user
*
* @var integer
*/
  var $test_formats;
  
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
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjTest($a_id = 0,$a_call_by_reference = true)
	{
		global $ilUser;
		$this->type = "tst";
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
		$this->starting_time = "";
		$this->ending_time = "";
		$this->processing_time = "00:00:00";
		$this->enable_processing_time = "0";
		$this->test_type = TYPE_ASSESSMENT;
		$this->test_formats = 7;
		$this->ects_output = 0;
		$this->ects_fx = "";
		$this->random_test = 0;
		$this->random_question_count = "";
		global $lng;
		$lng->loadLanguageModule("assessment");
		$this->mark_schema->create_simple_schema($lng->txt("failed_short"), $lng->txt("failed_official"), 0, 0, $lng->txt("passed_short"), $lng->txt("passed_official"), 50, 1);
		$this->ects_grades = array(
			"A" => 90,
			"B" => 65,
			"C" => 35,
			"D" => 10,
			"E" => 0
		);
		if ($a_id == 0)
		{
			$new_meta =& new ilMetaData();
			$this->assignMetaData($new_meta);
		}
		$this->ilObject($a_id, $a_call_by_reference);
	}

	/**
	* create test object
	*/
	function create($a_upload = false)
	{
		parent::create();
		if (!$a_upload)
		{
			$this->meta_data->setId($this->getId());
			$this->meta_data->setType($this->getType());
			$this->meta_data->setTitle($this->getTitle());
			$this->meta_data->setDescription($this->getDescription());
			$this->meta_data->setObject($this);
			$this->meta_data->create();
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
	* Returns the calling script of the class
	*
	* @access	public
	*/
	function getCallingScript()
	{
		return "test.php";
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
		$this->meta_data =& new ilMetaData($this->getType(), $this->getId());
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
		$query = sprintf("SELECT active_id FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		$active_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($active_array, $row["active_id"]);
		}
		
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);

		if (count($active_array))
		{
			foreach ($active_array as $active_id)
			{
				$query = sprintf("DELETE FROM tst_times WHERE active_fi = %s",
					$this->ilias->db->quote($active_id)
				);
				$result = $this->ilias->db->query($query);
			}
		}
		
		$query = sprintf("DELETE FROM tst_mark WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		
		$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->removeQuestion($row->question_fi);
		}
		
		$query = sprintf("DELETE FROM tst_tests WHERE test_id = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		
		$query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		
		$query = sprintf("DELETE FROM tst_test_random_question WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		
		$this->removeAllTestEditings();
		
		$query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		
		// delete export files
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		$directory = $tst_data_dir."/tst_".$this->getId();
		if (is_dir($directory))
		{
			$directory = escapeshellarg($directory);
			exec("rm -rf $directory");
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
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);
		if(!is_writable($tst_data_dir))
		{
			$this->ilias->raiseError("Test Data Directory (".$tst_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		
		// create learning module directory (data_dir/lm_data/lm_<id>)
		$tst_dir = $tst_data_dir."/tst_".$this->getId();
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$this->ilias->raiseError("Creation of Test Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		$export_dir = $tst_dir."/export";
		ilUtil::makeDir($export_dir);
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
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
				substr($entry, -4) == ".zip" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(test_)*[0-9]+\.zip\$", $entry))
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
	function createImportDirectory()
	{
		$tst_data_dir = ilUtil::getDataDir()."/tst_data";
		ilUtil::makeDir($tst_data_dir);
		
		if(!is_writable($tst_data_dir))
		{
			$this->ilias->raiseError("Test Data Directory (".$tst_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create test directory (data_dir/tst_data/tst_<id>)
		$tst_dir = $tst_data_dir."/tst_".$this->getId();
		ilUtil::makeDir($tst_dir);
		if(!@is_dir($tst_dir))
		{
			$this->ilias->raiseError("Creation of Test Directory failed.",$this->ilias->error_obj->FATAL);
		}

		// create import subdirectory (data_dir/tst_data/tst_<id>/import)
		$import_dir = $tst_dir."/import";
		ilUtil::makeDir($import_dir);
		if(!@is_dir($import_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed.",$this->ilias->error_obj->FATAL);
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
		$import_dir = ilUtil::getDataDir()."/tst_data".
			"/tst_".$this->getId()."/import";
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
    $query = sprintf("SELECT * FROM object_data WHERE title = %s AND type = %s",
      $this->ilias->db->quote($title),
			$this->ilias->db->quote("tst")
    );
    $result = $this->ilias->db->query($query);
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
    $clone = $this;
    $clone->set_id(-1);
    $counter = 2;
    while ($this->testTitleExists($this->get_title() . " ($counter)")) {
      $counter++;
    }
    $clone->set_title($this->get_title() . " ($counter)");
    $clone->set_owner($this->ilias->account->id);
    $clone->setAuthor($this->ilias->account->fullname);
    $clone->saveToDb($this->ilias->db);
    // Duplicate questions
    $query = sprintf("SELECT * FROM tst_test_question WHERE test_fi = %s",
      $this->ilias->db->quote($this->getId())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      $query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
        $this->ilias->db->quote($clone->getId()),
        $this->ilias->db->quote($data->question_fi),
        $this->ilias->db->quote($data->sequence)
      );
      $insert_result = $this->ilias->db->query($query);
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
		if (($test->getTitle()) and ($test->author) and (count($test->mark_schema->mark_steps)) and (count($test->questions)))
		{
			return true;
		} 
			else 
		{
			return false;
		}
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
    global $ilias;
		
    $db =& $ilias->db;
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
    if ($this->test_id > 0) {
      $query = sprintf("UPDATE tst_tests SET complete = %s WHERE test_id = %s",
				$db->quote("$complete"),
        $db->quote($this->test_id)
      );
      $result = $db->query($query);
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
  function saveToDb($properties_only = false)
  {
    global $ilias;
    $db =& $ilias->db;
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
			$random_question_count = $this->ilias->db->quote($this->random_question_count . "");
		}
    if ($this->test_id == -1) {
      // Create new dataset
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO tst_tests (test_id, obj_fi, author, test_type_fi, introduction, sequence_settings, score_reporting, nr_of_tries, processing_time, enable_processing_time, reporting_date, starting_time, ending_time, complete, ects_output, ects_a, ects_b, ects_c, ects_d, ects_e, ects_fx, random_test, random_question_count, created, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($this->getId() . ""),
        $db->quote($this->author . ""),
        $db->quote($this->test_type . ""),
        $db->quote($this->introduction . ""), 
        $db->quote($this->sequence_settings . ""),
        $db->quote($this->score_reporting . ""),
        $db->quote(sprintf("%d", $this->nr_of_tries) . ""),
        $db->quote($this->processing_time . ""),
				$db->quote("$this->enable_processing_time"),
        $db->quote($this->reporting_date . ""),
        $db->quote($this->starting_time . ""),
        $db->quote($this->ending_time . ""),
				$db->quote("$complete"),
				$db->quote($this->ects_output . ""),
				$db->quote($this->ects_grades["A"] . ""),
				$db->quote($this->ects_grades["B"] . ""),
				$db->quote($this->ects_grades["C"] . ""),
				$db->quote($this->ects_grades["D"] . ""),
				$db->quote($this->ects_grades["E"] . ""),
				$ects_fx,
				$db->quote(sprintf("%d", $this->random_test) . ""),
				$random_question_count,
        $db->quote($created)
      );
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txt("log_create_new_test"));
			}
      $result = $db->query($query);
      if ($result == DB_OK) {
        $this->test_id = $this->ilias->db->getLastInsertId();
      }
    } else {
      // Modify existing dataset
			$oldrow = array();
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
	        $db->quote($this->test_id)
				);
				$result = $db->query($query);
				if ($result->numRows() == 1)
				{
					$oldrow = $result->fetchRow(DB_FETCHMODE_ASSOC);
				}
			}
      $query = sprintf("UPDATE tst_tests SET author = %s, test_type_fi = %s, introduction = %s, sequence_settings = %s, score_reporting = %s, nr_of_tries = %s, processing_time = %s, enable_processing_time = %s, reporting_date = %s, starting_time = %s, ending_time = %s, ects_output = %s, ects_a = %s, ects_b = %s, ects_c = %s, ects_d = %s, ects_e = %s, ects_fx = %s, random_test = %s, complete = %s WHERE test_id = %s",
        $db->quote($this->author . ""), 
        $db->quote($this->test_type . ""), 
        $db->quote($this->introduction . ""), 
        $db->quote($this->sequence_settings . ""), 
        $db->quote($this->score_reporting . ""), 
        $db->quote(sprintf("%d", $this->nr_of_tries) . ""), 
        $db->quote($this->processing_time . ""),
				$db->quote("$this->enable_processing_time"),
        $db->quote($this->reporting_date . ""), 
        $db->quote($this->starting_time . ""), 
        $db->quote($this->ending_time . ""), 
				$db->quote($this->ects_output . ""),
				$db->quote($this->ects_grades["A"] . ""),
				$db->quote($this->ects_grades["B"] . ""),
				$db->quote($this->ects_grades["C"] . ""),
				$db->quote($this->ects_grades["D"] . ""),
				$db->quote($this->ects_grades["E"] . ""),
				$ects_fx,
				$db->quote(sprintf("%d", $this->random_test) . ""),
				$db->quote("$complete"),
        $db->quote($this->test_id)
      );
      $result = $db->query($query);
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$query = sprintf("SELECT * FROM tst_tests WHERE test_id = %s",
	        $db->quote($this->test_id)
				);
				$result = $db->query($query);
				$newrow = array();
				if ($result->numRows() == 1)
				{
					$newrow = $result->fetchRow(DB_FETCHMODE_ASSOC);
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
					$changes = $this->lng->txt("log_no_test_fields_changed");
				}
				$this->logAction($this->lng->txt("log_modified_test") . " [".$changes."]");
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
		$oldquestions = array();
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
				$this->ilias->db->quote($this->getTestId())
			);
			$result = $this->ilias->db->query($query);
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
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		// create new category relations
		foreach ($this->questions as $key => $value) {
			$query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getTestId() . ""),
				$this->ilias->db->quote($value . ""),
				$this->ilias->db->quote($key . "")
			);
			$result = $this->ilias->db->query($query);
		}
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$query = sprintf("SELECT question_fi FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
				$this->ilias->db->quote($this->getTestId())
			);
			$result = $this->ilias->db->query($query);
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
						$this->logAction($this->lng->txt("log_question_removed"), $question_id);							
					}
					else
					{
						$this->logAction($this->lng->txt("log_question_position_changed") . ": " . ($index+1) . " => " . ($pos+1), $question_id);
					}
				}
			}
			foreach ($newquestions as $index => $question_id)
			{
				if (array_search($question_id, $oldquestions) === FALSE)
				{
					$this->logAction($this->lng->txt("log_question_added") . ": " . ($index+1), $question_id);							
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
	function saveRandomQuestion($question_id) 
	{
		global $ilUser;
		
		$query = sprintf("SELECT test_random_question_id FROM tst_test_random_question WHERE test_fi = %s AND user_fi = %s",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($ilUser->id . "")
		);
		$result = $this->ilias->db->query($query);
		
		$query = sprintf("INSERT INTO tst_test_random_question (test_random_question_id, test_fi, user_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($ilUser->id . ""),
			$this->ilias->db->quote($question_id . ""),
			$this->ilias->db->quote(($result->numRows()+1) . "")
		);
		$result = $this->ilias->db->query($query);
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
		if (strcmp($total_questions, "NULL") != 0)
		{
			$total_questions = $this->ilias->db->quote($total_questions);
		}
		$query = sprintf("UPDATE tst_tests SET random_question_count = %s WHERE test_id = %s",
			$total_questions,
			$this->ilias->db->quote($this->getTestId() . "")
		);
		$result = $this->ilias->db->query($query);
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
		// delete existing random questionpools
    $query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		// create new random questionpools
		foreach ($qpl_array as $key => $value) {
			if ($value["qpl"] > -1)
			{
				$count = ilObjQuestionPool::_getQuestionCount($value["qpl"]);
				if ($value["count"] > $count)
				{
					$value["count"] = $count;
				}
				$query = sprintf("INSERT INTO tst_test_random (test_random_id, test_fi, questionpool_fi, num_of_q, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
					$this->ilias->db->quote($this->getTestId() . ""),
					$this->ilias->db->quote($value["qpl"] . ""),
					$this->ilias->db->quote(sprintf("%d", $value["count"]) . "")
				);
				$result = $this->ilias->db->query($query);
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
		$qpls = array();
		$counter = 0;
		$query = sprintf("SELECT * FROM tst_test_random WHERE test_fi = %s ORDER BY test_random_id",
			$this->ilias->db->quote($this->getTestId() . "")
		);
		$result = $this->ilias->db->query($query);
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
		$db = $this->ilias->db;

		$query = sprintf("SELECT * FROM tst_tests WHERE obj_fi = %s",
		$db->quote($this->getId())
			);
		$result = $db->query($query);
		if (strcmp(strtolower(get_class($result)), db_result) == 0)
		{
			if ($result->numRows() == 1)
			{
				$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->test_id = $data->test_id;
				$this->author = $data->author;
				$this->test_type = $data->test_type_fi;
				$this->introduction = $data->introduction;
				$this->sequence_settings = $data->sequence_settings;
				$this->score_reporting = $data->score_reporting;
				$this->nr_of_tries = $data->nr_of_tries;
				$this->processing_time = $data->processing_time;
				$this->enable_processing_time = $data->enable_processing_time;
				$this->reporting_date = $data->reporting_date;
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
	function loadQuestions($user_id = "") 
	{
		global $ilUser;
		
    $db = $this->ilias->db;
		$this->questions = array();
		if (strcmp($user_id, "") == 0)
		{
			$user_id = $ilUser->id;
		}
		if ($this->isRandomTest())
		{
			$query = sprintf("SELECT tst_test_random_question.* FROM tst_test_random_question, qpl_questions WHERE tst_test_random_question.test_fi = %s AND tst_test_random_question.user_fi = %s AND qpl_questions.question_id = tst_test_random_question.question_fi ORDER BY sequence",
				$db->quote($this->test_id . ""),
				$db->quote($user_id . "")
			);
		}
		else
		{
			$query = sprintf("SELECT tst_test_question.* FROM tst_test_question, qpl_questions WHERE tst_test_question.test_fi = %s AND qpl_questions.question_id = tst_test_question.question_fi ORDER BY sequence",
				$db->quote($this->test_id . "")
			);
		}
		$result = $db->query($query);
		$index = 1;
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
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
      $this->score_reporting = REPORT_AFTER_TEST;
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
		$question = new ASS_Question();
		if (ilObjAssessmentFolder::_enabledAssessmentLogging())
		{
			$this->logAction($this->lng->txt("log_question_removed"), $question_id);
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
		$query = sprintf("DELETE FROM tst_eval_users WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
	}
	
/**
* Removes all selected groups for the test evaluation
* 
* Removes all selected groups for the test evaluation
*
* @access public
*/
	function clearEvalSelectedGroups()
	{
		$query = sprintf("DELETE FROM tst_eval_groups WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
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
		// remove test_active entries, because test has changed
		$this->deleteActiveTests();
		// remove selected users/groups
		$this->clearEvalSelectedUsers();
		$this->clearEvalSelectedGroups();
		
		// remove the question from tst_solutions
		if ($question_id) {
			$query = sprintf("DELETE FROM tst_solutions WHERE test_fi = %s AND question_fi = %s",
				$this->ilias->db->quote($this->getTestId()),
				$this->ilias->db->quote($question_id)
			);
		} else {
			$query = sprintf("DELETE FROM tst_solutions WHERE test_fi = %s",
				$this->ilias->db->quote($this->getTestId())
			);
		}
		$result = $this->ilias->db->query($query);
		if ($this->isRandomTest())
		{
			$query = sprintf("DELETE FROM tst_test_random_question WHERE test_fi = %s",
				$this->ilias->db->quote($this->getTestId())
			);
			$result = $this->ilias->db->query($query);
		}
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
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
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
		if ($user_id) {
			$query = sprintf("DELETE FROM tst_solutions WHERE test_fi = %s AND user_fi = %s",
				$this->ilias->db->quote($this->getTestId()),
				$this->ilias->db->quote($user_id)
			);
			$result = $this->ilias->db->query($query);
			$sequence_arr = array_flip($this->questions);
			$sequence = join($sequence_arr, ",");
			$query = sprintf("UPDATE tst_active SET sequence = %s, lastindex = %s WHERE test_fi = %s and user_fi = %s",
				$this->ilias->db->quote($sequence),
				$this->ilias->db->quote("1"),
				$this->ilias->db->quote($this->getTestId()),
				$this->ilias->db->quote($user_id)
			);
			$result = $this->ilias->db->query($query);
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
		// Move a question up in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$this->ilias->db->quote($this->getTestId()),
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
		if ($data->sequence > 1) {
			// OK, it's not the top question, so move it up
			$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
				$this->ilias->db->quote($this->getTestId()),
				$this->ilias->db->quote($data->sequence - 1)
			);
			$result = $this->ilias->db->query($query);
			$data_previous = $result->fetchRow(DB_FETCHMODE_OBJECT);
			// change previous dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->quote($data->sequence),
				$this->ilias->db->quote($data_previous->test_question_id)
			);
			$result = $this->ilias->db->query($query);
			// move actual dataset up
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->quote($data->sequence - 1),
				$this->ilias->db->quote($data->test_question_id)
			);
			$result = $this->ilias->db->query($query);
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txt("log_question_position_changed") . ": " . ($data->sequence) . " => " . ($data->sequence-1), $question_id);
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
		// Move a question down in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$this->ilias->db->quote($this->getTestId()),
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
			$this->ilias->db->quote($this->getTestId()),
			$this->ilias->db->quote($data->sequence + 1)
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() == 1) 
		{
			// OK, it's not the last question, so move it down
			$data_next = $result->fetchRow(DB_FETCHMODE_OBJECT);
			// change next dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->quote($data->sequence),
				$this->ilias->db->quote($data_next->test_question_id)
			);
			$result = $this->ilias->db->query($query);
			// move actual dataset down
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->quote($data->sequence + 1),
				$this->ilias->db->quote($data->test_question_id)
			);
			$result = $this->ilias->db->query($query);
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txt("log_question_position_changed") . ": " . ($data->sequence) . " => " . ($data->sequence+1), $question_id);
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
		$duplicate_id = $this->duplicateQuestionForTest($question_id);

		// get maximum sequence index in test
		$query = sprintf("SELECT MAX(sequence) AS seq FROM tst_test_question WHERE test_fi=%s",
			$this->ilias->db->quote($this->getTestId())
			);
		$result = $this->ilias->db->query($query);
		$sequence = 1;

		if ($result->numRows() == 1)
		{
			$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
			$sequence = $data->seq + 1;
		}

		$query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$this->ilias->db->quote($this->getTestId()),
			$this->ilias->db->quote($duplicate_id),
			$this->ilias->db->quote($sequence)
			);
		$result = $this->ilias->db->query($query);
		if ($result != DB_OK)
		{
			// Error
		}
		else
		{
			if (ilObjAssessmentFolder::_enabledAssessmentLogging())
			{
				$this->logAction($this->lng->txt("log_question_added") . ": " . $sequence, $duplicate_id);
			}
		}
		// remove test_active entries, because test has changed
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
			);
		$result = $this->ilias->db->query($query);
		$this->loadQuestions();
		$this->saveCompleteStatus();
	}

/**
* Returns the title of a question with a given sequence number
*
* Returns the title of a question with a given sequence number
*
* @param integer $sequence The sequence number of the question
* @access public
* @see $questions
*/
	function getQuestionTitle($sequence) 
	{
		global $ilUser;
		if ($ilUser->id > 0)
		{
			$active = $this->getActiveTestUser($ilUser->id);
			$seq = split(",", $active->sequence);
			$query = sprintf("SELECT title from qpl_questions WHERE question_id = %s",
				$this->ilias->db->quote($this->questions[$seq[$sequence-1]])
			);
		}
		else
		{
			$query = sprintf("SELECT title from qpl_questions WHERE question_id = %s",
				$this->ilias->db->quote($this->questions[$sequence])
			);
		}
    $result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->title;
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
		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
			$this->ilias->db->quote("$question_id")
		);
    $result = $this->ilias->db->query($query);
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
		
		$qpl_titles = array();
		// get all available questionpools and remove the trashed questionspools
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl'";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && ($this->_hasUntrashedReference($row->obj_id)))
			{
				$qpl_titles["$row->obj_id"] = $row->title;
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
	function &getExistingQuestions() 
	{
		global $ilUser;
		$existing_questions = array();
		if ($this->isRandomTest())
		{
			$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_random_question WHERE tst_test_random_question.test_fi = %s AND tst_test_random_question.user_fi = %s AND tst_test_random_question.question_fi = qpl_questions.question_id",
				$this->ilias->db->quote($this->getTestId() . ""),
				$this->ilias->db->quote($ilUser->id . "")
			);
		}
		else
		{
			$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id",
				$this->ilias->db->quote($this->getTestId())
			);
		}
		$result = $this->ilias->db->query($query);
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
    if ($question_id < 1)
      return -1;
    $query = sprintf("SELECT type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
      $this->ilias->db->quote($question_id)
    );
    $result = $this->ilias->db->query($query);
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
	function startWorkingTime ($user_id) 
	{
		$result = "";
		if (!($result = $this->getActiveTestUser($user_id))) {
			$this->setActiveTestUser();
			$result = $this->getActiveTestUser($user_id);
		}
		$q = sprintf("INSERT INTO tst_times (times_id, active_fi, started, finished, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$this->ilias->db->quote($result->active_id),
			$this->ilias->db->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$this->ilias->db->quote(strftime("%Y-%m-%d %H:%M:%S"))
		);
		$result = $this->ilias->db->query($q);
		return $this->ilias->db->getLastInsertId();
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
		$q = sprintf("UPDATE tst_times SET finished = %s WHERE times_id = %s",
			$this->ilias->db->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$this->ilias->db->quote($times_id)
		);
		$result = $this->ilias->db->query($q);
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
		$worked_questions = &$this->getWorkedQuestions();
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
	
/**
* Gets the id's of all questions a user already worked through
* 
* Gets the id's of all questions a user already worked through
*
* @return array The question id's of the questions already worked through
* @access	public
*/
	function &getWorkedQuestions()
	{
		global $ilUser;
		$query = sprintf("SELECT * FROM tst_solutions WHERE user_fi = %s AND test_fi = %s GROUP BY question_fi",
			$this->ilias->db->quote($ilUser->id),
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
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
	function &getAllQuestions()
	{
		global $ilUser;
		
		if ($this->isRandomTest())
		{
			$query = sprintf("SELECT qpl_questions.* FROM qpl_questions, tst_test_random_question WHERE tst_test_random_question.question_fi = qpl_questions.question_id AND tst_test_random_question.user_fi = %s AND qpl_questions.question_id IN (" . join($this->questions, ",") . ")",
				$this->ilias->db->quote($ilUser->id . "")
			);
		}
		else
		{
			$query = "SELECT qpl_questions.* FROM qpl_questions, tst_test_question WHERE tst_test_question.question_fi = qpl_questions.question_id AND qpl_questions.question_id IN (" . join($this->questions, ",") . ")";
		}
		$result = $this->ilias->db->query($query);
		$result_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$result_array[$row["question_id"]] = $row;
		}
		return $result_array;
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
	function getActiveTestUser($user_id = "") 
	{
		global $ilDB;
		global $ilUser;
		
		$db =& $ilDB->db;
		if (!$user_id) {
			$user_id = $ilUser->id;
		}
		$query = sprintf("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
			$db->quote($user_id),
			$db->quote($this->test_id)
		);
		
		$result = $db->query($query);
		if ($result->numRows()) {
			return $result->fetchRow(DB_FETCHMODE_OBJECT);
		} else {
			return "";
		}
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
		
		$db =& $ilDB->db;
		if (!$user_id) {
			$user_id = $ilUser->id;
		}
		if (!$test_id)
		{
			return "";
		}
		$query = sprintf("SELECT * FROM tst_active WHERE user_fi = %s AND test_fi = %s",
			$db->quote($user_id),
			$db->quote($test_id)
		);
		
		$result = $db->query($query);
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
		
		$db =& $ilDB->db;
		$old_active = $this->getActiveTestUser();
		if ($old_active) {
			$sequence = $old_active->sequence;
			$postponed = $old_active->postponed;
			if ($postpone) {
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
			if ($addTries) {
				$tries++;
			}
			$query = sprintf("UPDATE tst_active SET lastindex = %s, sequence = %s, postponed = %s, tries = %s WHERE user_fi = %s AND test_fi = %s",
				$db->quote($lastindex),
				$db->quote($sequence),
				$db->quote($postponed),
				$db->quote($tries),
				$db->quote($ilUser->id),
				$db->quote($this->test_id)
			);
		} else {
			$sequence_arr = array_flip($this->questions);
			$sequence = join($sequence_arr, ",");
			$query = sprintf("INSERT INTO tst_active (active_id, user_fi, test_fi, sequence, postponed, lastindex, tries, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($ilUser->id),
				$db->quote($this->test_id),
				$db->quote($sequence),
				$db->quote(""),
				$db->quote($lastindex),
				$db->quote(0)
			);
		}
		$db->query($query);
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
	function &getTestResult($user_id)
	{
		//		global $ilBench;
		if ($this->isRandomTest())
		{
			$this->loadQuestions($user_id);
		}
		$add_parameter = "?ref_id=$this->ref_id&cmd=run";
		$total_max_points = 0;
		$total_reached_points = 0;
		
		// retrieve the active test dataset for the user containing
		// questions sequence and other information 
		$active = $this->getActiveTestUser($user_id);
		$sequence_array = split(",", $active->sequence);
		sort($sequence_array, SORT_NUMERIC);

		$key = 1;
		$result_array = array();
		foreach ($sequence_array as $idx => $seq)
		{
			$value = $this->questions[$seq];
//			$ilBench->start("getTestResult","instanciate question"); 
			$question =& ilObjTest::_instanciateQuestion($value);
//			$ilBench->stop("getTestResult","instanciate question"); 
			$max_points = $question->getMaximumPoints();
			$total_max_points += $max_points;
			$reached_points = $question->getReachedPoints($user_id, $this->getTestId());
			$total_reached_points += $reached_points;
			if ($max_points > 0)
			{
				$percentvalue = $reached_points / $max_points;
			}
			else
			{
				$percentvalue = 0;
			}
			if (is_object($question))
			{
				if (count($question->suggested_solutions) == 1)
				{
					$max_points = $question->getMaximumPoints();
					$total_max_points += $max_points;
					$reached_points = $question->getReachedPoints($user_id, $this->getTestId());
					$total_reached_points += $reached_points;
					if ($max_points > 0)
					{
						$percentvalue = $reached_points / $max_points;
					}
					else
					{
						$percentvalue = 0;
					}
					if (count($question->suggested_solutions) == 1)
					{
						$solution_array = $question->getSuggestedSolution(0);
						$href = ASS_Question::_getInternalLinkHref($solution_array["internal_link"]);
					}
					elseif (count($question->suggested_solutions) > 1)
					{
						$href = "see_details_for_further_information";
					}
					else
					{
						$href = "";
					}
					$row = array(
						"nr" => "$key",
						"title" => "<a href=\"" . $this->getCallingScript() . "$add_parameter&evaluation=" . $question->getId() . "\">" . $question->getTitle() . "</a>",
						"max" => sprintf("%d", $max_points),
						"reached" => sprintf("%d", $reached_points),
						"percent" => sprintf("%2.2f ", ($percentvalue) * 100) . "%",
						"solution" => $href,
						"type" => $question->getQuestionType(),
						"qid" => $question->getId()
					);
					array_push($result_array, $row);
					$key++;
				}
				elseif (count($question->suggested_solutions) > 1)
				{
					$href = "see_details_for_further_information";
				}
				else
				{
					$href = "";
				}
				$row = array(
					"nr" => "$key",
					"title" => "<a href=\"" . $this->getCallingScript() . "$add_parameter&evaluation=" . $question->getId() . "\">" . $question->getTitle() . "</a>",
					"max" => sprintf("%d", $max_points),
					"reached" => sprintf("%d", $reached_points),
					"percent" => sprintf("%2.2f ", ($percentvalue) * 100) . "%",
					"solution" => $href,
					"type" => $question->getQuestionType(),
					"qid" => $question->getId()
				);
				array_push($result_array, $row);
				$key++;
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
		}
		$mark_obj = $this->mark_schema->get_matching_mark($percentage);
		$passed = "";
		if ($mark_obj)
		{
			if ($mark_obj->get_passed())
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
* Calculates the results of a test for a given user
* 
* Calculates the results of a test for a given user
* and returns an array with all test results
*
* @return array An array containing the test results for the given user
* @access public
*/
	function &_getTestResult($user_id, $test_obj_id) 
	{
		$test = new ilObjTest($test_obj_id, false);
		$test->loadFromDb();
		$result =& $test->getTestResult($user_id);
		return $result;
	}

/**
* Returns a result for a condition check on the class
* 
* Calculates the results of a test for a given user
* and returns true if the user passed the test, else false
*
* @param integer $a_exc_id object id of the test object
* @param string $a_operator The operator which should be checked
* @param mixed $a_value ???
* @return boolean True if the test was passed, False otherwise
* @access public
*/
	function _checkCondition($a_exc_id,$a_operator,$a_value)
	{
		global $ilias;

		switch($a_operator)
		{
			case 'passed':
				$result = ilObjTest::_getTestResult($ilias->account->getId(), $a_exc_id);
				if ($result["test"]["passed"])
				{
					return true;
				}
				else
				{
					return false;
				}
				break;
			default:
				return true;
		}
		return true;
	}	
				
	
/**
* Returns the resulting mark of a test for a given user
* 
* Returns the resulting mark of a test for a given user
*
* @param integer $user_id Database id of the user
* @param integer $test_obj_id Object id of the test
* @return object The resulting mark object 
* @access public
*/
	function &_getMark($user_id, $test_obj_id) 
	{
		$test = new ilObjTest($test_obj_id, false);
		$test->loadFromDb();
		$result =& $test->getTestResult($user_id);
		if ($result["test"]["total_max_points"] == 0)
		{
			$pct = 0;
		}
			else
		{
			$pct = ($result["test"]["total_reached_points"] / $result["test"]["total_max_points"]) * 100.0;
		}
		$mark = $test->mark_schema->get_matching_mark($pct);
		return $mark;
	}

	/**
	* assign a meta data object to glossary object
	*
	* @param	object		$a_meta_data	meta data object
	*/
	function assignMetaData(&$a_meta_data)
	{
		$this->meta_data =& $a_meta_data;
	}

	/**
	* get meta data object of glossary object
	*
	* @return	object		meta data object
	*/
	function &getMetaData()
	{
		return $this->meta_data;
	}

	/**
	* init meta data object if needed
	*/
	function initMeta()
	{
		if (!is_object($this->meta_data))
		{
			if ($this->getId())
			{
				$new_meta =& new ilMetaData($this->getType(), $this->getId());
			}	
			else
			{
				$new_meta =& new ilMetaData();
			}
			$this->assignMetaData($new_meta);
		}
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
		$q = sprintf("SELECT COUNT(*) as total FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($q);
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
		if ($this->getTestType() == TYPE_ASSESSMENT)
		{
			if ($this->getReportingDate())
			{
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getReportingDate(), $matches);
				$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = mktime();
				if ($now < $epoch_time) 
				{
					$result = false;
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
		$q = sprintf("SELECT * FROM tst_eval_settings WHERE user_fi = %s",
			$this->ilias->db->quote("$user_id")
		);
		$result = $this->ilias->db->query($q);
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
		$q = sprintf("SELECT * FROM tst_eval_settings WHERE user_fi = %s",
			$this->ilias->db->quote("$user_id")
		);
		$result = $this->ilias->db->query($q);
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
				$this->ilias->db->quote("$user_id"),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["qworkedthrough"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["pworkedthrough"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["timeofwork"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["atimeofwork"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["firstvisit"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["lastvisit"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["resultspoints"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["resultsmarks"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["distancemedian"]))
			);
		} 
			else 
		{
			$q = sprintf("UPDATE tst_eval_settings SET ".
					 "qworkedthrough = %s, pworkedthrough = %s, timeofwork = %s, atimeofwork = %s, firstvisit = %s, " .
					 "lastvisit = %s, resultspoints = %s, resultsmarks = %s, distancemedian = %s " .
					 "WHERE eval_settings_id = %s",
				$this->ilias->db->quote(sprintf("%01d", $settings_array["qworkedthrough"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["pworkedthrough"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["timeofwork"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["atimeofwork"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["firstvisit"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["lastvisit"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["resultspoints"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["resultsmarks"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["distancemedian"])),
				$this->ilias->db->quote("$update")
			);
		}
		$result = $this->ilias->db->query($q);
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
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.user_fi = %s",
			$this->ilias->db->quote($this->getTestId()),
			$this->ilias->db->quote($user_id)
		);
		$result = $this->ilias->db->query($q);
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
	function &evalStatistical($user_id)
	{
//		global $ilBench;
		
		$test_result =& $this->getTestResult($user_id);
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.user_fi = %s",
			$this->ilias->db->quote($this->getTestId()),
			$this->ilias->db->quote($user_id)
		);
		$result = $this->ilias->db->query($q);
		$times = array();
		$first_visit = "";
		$last_visit = "";
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
		if ((!$test_result["test"]["total_reached_points"]) or (!$test_result["test"]["total_max_points"])) {
			$percentage = 0.0;
		} else {
			$percentage = ($test_result["test"]["total_reached_points"] / $test_result["test"]["total_max_points"]) * 100.0;
		}
		$mark_obj = $this->mark_schema->get_matching_mark($percentage);
		$first_date = getdate($first_visit);
		$last_date = getdate($last_visit);
		$qworkedthrough = 0;
		$query_worked_through = sprintf("SELECT DISTINCT(question_fi) FROM tst_solutions WHERE user_fi = %s AND test_fi = %s",
			$this->ilias->db->quote("$user_id"),
			$this->ilias->db->quote($this->getTestId())
		);
		$worked_through_result = $this->ilias->db->query($query_worked_through);
		if (!$worked_through_result->numRows())
		{
			$atimeofwork = 0;
		}
		else
		{
			$atimeofwork = $max_time / $worked_through_result->numRows();
		}
		$result_mark = "";
		$passed = "";
		if ($mark_obj)
		{
			$result_mark = $mark_obj->get_short_name();
			if ($mark_obj->get_passed())
			{
				$passed = 1;
			}
			else
			{
				$passed = 0;
			}
		}
		$result_array = array(
			"qworkedthrough" => $worked_through_result->numRows(),
			"qmax" => count($this->questions),
			"pworkedthrough" => ($worked_through_result->numRows()) / count($this->questions),
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
		$all_users =& $this->evalTotalPersonsArray();
		foreach ($all_users as $user_id => $user_name)
		{
			$test_result =& $this->getTestResult($user_id);
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
		$all_users =& $this->evalTotalPersonsArray();
		foreach ($all_users as $user_id => $user_name)
		{
			$test_result =& $this->getTestResult($user_id);
			$reached = $test_result["test"]["total_reached_points"];
			$total = $test_result["test"]["total_max_points"];
			$percentage = $reached/$total;
			$mark = $this->mark_schema->get_matching_mark($percentage*100.0);
			if ($mark)
			{
				if ($mark->get_passed())
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
	function &evalTotalPersonsArray()
	{
		$q = sprintf("SELECT tst_active.user_fi, usr_data.firstname, usr_data.lastname FROM tst_active, usr_data WHERE tst_active.test_fi = %s AND tst_active.user_fi = usr_data.usr_id", 
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($q);
		$persons_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$persons_array[$row->user_fi] = trim("$row->firstname $row->lastname");
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
		$q = sprintf("SELECT COUNT(*) as total FROM tst_active WHERE test_fi = %s AND tries > 0",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($q);
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
		$q = sprintf("SELECT * FROM tst_active WHERE test_fi = %s AND tries > 0",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($q);
		$points = array();
		$passed_tests = 0;
		$failed_tests = 0;
		$maximum_points = 0;
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) 
		{
			$res =& $this->getTestResult($row->user_fi);
			if ((!$res["test"]["total_reached_points"]) or (!$res["test"]["total_max_points"])) 
			{
				$percentage = 0.0;
			} 
				else 
			{
				$percentage = ($res["test"]["total_reached_points"] / $res["test"]["total_max_points"]) * 100.0;
			}
			$mark_obj = $this->mark_schema->get_matching_mark($percentage);
			$maximum_points = $res["test"]["total_max_points"];
			if ($mark_obj)
			{
				if ($mark_obj->get_passed()) {
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
			$average_points = round($reached_points / $counter);
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
		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.tries > 0 AND tst_active.active_id = tst_times.active_fi",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($q);
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
* Gets a list of all inaccessable question pools for the active user
* 
* Gets a list of all inaccessable question pools for the active user
*
* @return array An array containing the database id's of the inaccessable questionpools
* @access	public
*/
	function &getForbiddenQuestionpools()
	{
		global $rbacsystem;
		
		$forbidden_pools = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl'";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if (!$rbacsystem->checkAccess("write", $row->ref_id) || (!$this->_hasUntrashedReference($row->obj_id)))
			{
				array_push($forbidden_pools, $row->obj_id);
			}
		}
		return $forbidden_pools;
	}

/**
* Returns the available question pools for the active user
* 
* Returns the available question pools for the active user
*
* @return array The available question pools
* @access public
*/
	function &getAvailableQuestionpools($use_object_id = false)
	{
		global $rbacsystem;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl' ORDER BY object_data.title";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && ($this->_hasUntrashedReference($row->obj_id)))
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
		foreach ($this->questions as $question_id) {
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
	function randomSelectQuestions($nr_of_questions, $questionpool, $use_obj_id = 0, $qpls = "")
	{
		global $rbacsystem;
		if ($questionpool != 0)
		{
			// retrieve object id
			if (!$use_obj_id)
			{
				$query = sprintf("SELECT obj_id FROM object_reference WHERE ref_id = %s",
					$this->ilias->db->quote("$questionpool")
				);
				$result = $this->ilias->db->query($query);
				$row = $result->fetchRow(DB_FETCHMODE_ARRAY);
				$questionpool = $row[0];
			}
		}
		// get all questions in the test
		$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND tst_test_question.test_fi = %s",
			$this->ilias->db->quote($this->getTestId() . "")
		);
		$result = $this->ilias->db->query($query);
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

		$forbidden_pools =& $this->getForbiddenQuestionpools();
		$forbidden = "";
		$constraint_qpls = "";
		if (count($forbidden_pools))
		{
			$forbidden = " AND qpl_questions.obj_fi NOT IN (" . join($forbidden_pools, ",") . ")";
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
			$query = "SELECT COUNT(question_id) FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$forbidden$constraint_qpls AND qpl_questions.complete = '1'$original_clause";
		}
			else
		{
			$query = sprintf("SELECT COUNT(question_id) FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND obj_fi = %s$original_clause",
				$this->ilias->db->quote("$questionpool")
			);
		}
		$result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ARRAY);
		if (($row[0]) <= $nr_of_questions)
		{
			// take all available questions
			if ($questionpool == 0)
			{
				$query = "SELECT question_id FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$forbidden$constraint_qpls AND qpl_questions.complete = '1'$original_clause";
			}
				else
			{
				$query = sprintf("SELECT question_id FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND obj_fi = %s AND qpl_questions.complete = '1'$original_clause",
					$this->ilias->db->quote("$questionpool")
				);
			}
			$result = $this->ilias->db->query($query);
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
					$query = "SELECT question_id FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$forbidden$constraint_qpls AND qpl_questions.complete = '1'$original_clause LIMIT $random_number, 1";
				}
					else
				{
					$query = sprintf("SELECT question_id FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND obj_fi = %s AND qpl_questions.complete = '1'$original_clause LIMIT $random_number, 1",
						$this->ilias->db->quote("$questionpool")
					);
				}
				$result = $this->ilias->db->query($query);
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
    switch ($question_type) 
		{
      case "qt_multiple_choice_sr":
        $question =& new ASS_MultipleChoiceGUI();
        $question->object->set_response(RESPONSE_SINGLE);
        break;
      case "qt_multiple_choice_mr":
        $question =& new ASS_MultipleChoiceGUI();
        $question->object->set_response(RESPONSE_MULTIPLE);
        break;
      case "qt_cloze":
        $question =& new ASS_ClozeTestGUI();
        break;
      case "qt_matching":
        $question =& new ASS_MatchingQuestionGUI();
        break;
      case "qt_ordering":
        $question =& new ASS_OrderingQuestionGUI();
        break;
      case "qt_imagemap":
        $question =& new ASS_ImagemapQuestionGUI();
        break;
			case "qt_javaapplet":
				$question =& new ASS_JavaAppletGUI();
				break;
			case "qt_text":
				$question =& new ASS_TextQuestionGUI();
				break;
    }
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
			$question_type = ASS_Question::_getQuestionType($question_id);
			switch ($question_type) {
				case "qt_cloze":
					$question = new ASS_ClozeTest();
					break;
				case "qt_matching":
					$question = new ASS_MatchingQuestion();
					break;
				case "qt_ordering":
					$question = new ASS_OrderingQuestion();
					break;
				case "qt_imagemap":
					$question = new ASS_ImagemapQuestion();
					break;
				case "qt_multiple_choice_sr":
				case "qt_multiple_choice_mr":
					$question = new ASS_MultipleChoice();
					break;
				case "qt_javaapplet":
					$question = new ASS_JavaApplet();
					break;
				case "qt_text":
					$question = new ASS_TextQuestion();
					break;
			}
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
		if ($this->getTestType() == TYPE_ASSESSMENT) 
		{
			if ($this->getStartingTime()) 
			{
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getStartingTime(), $matches);
				$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = mktime();
				if ($now < $epoch_time) 
				{
					// starting time not reached
					return false;
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
		if ($this->getTestType() == TYPE_ASSESSMENT) 
		{
			if ($this->getEndingTime()) 
			{
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getEndingTime(), $matches);
				$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
				$now = mktime();
				if ($now > $epoch_time) 
				{
					// ending time reached
					return true;
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
		$where = "";
		if (strlen($filter_text) > 0) {
			switch($sel_filter_type) {
				case "title":
					$where = " AND qpl_questions.title LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "comment":
					$where = " AND qpl_questions.comment LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "author":
					$where = " AND qpl_questions.author LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
			}
		}
		
		if ($filter_question_type && (strcmp($filter_question_type, "all") != 0))
		{
			$where .= " AND qpl_question_type.type_tag = " . $this->ilias->db->quote($filter_question_type);
		}
		
		if ($filter_questionpool && (strcmp($filter_questionpool, "all") != 0))
		{
			$where .= " AND qpl_questions.obj_fi = $filter_questionpool";
		}
  
    // build sort order for sql query
		$order = "";
		$images = array();
    if (count($sortoptions)) {
      foreach ($sortoptions as $key => $value) {
        switch($key) {
          case "title":
            $order = " ORDER BY title $value";
            $images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "comment":
            $order = " ORDER BY comment $value";
            $images["comment"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "type":
            $order = " ORDER BY question_type_id $value";
            $images["type"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "author":
            $order = " ORDER BY author $value";
            $images["author"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "created":
            $order = " ORDER BY created $value";
            $images["created"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "updated":
            $order = " ORDER BY TIMESTAMP $value";
            $images["updated"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "qpl":
            $order = " ORDER BY obj_fi $value";
            $images["qpl"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
        }
      }
    }
		$maxentries = $ilUser->prefs["hits_per_page"];
		$forbidden_pools =& $this->getForbiddenQuestionpools();
		$forbidden = "";
		if (count($forbidden_pools))
		{
			$forbidden = " AND qpl_questions.obj_fi NOT IN (" . join($forbidden_pools, ",") . ")";
		}
		if ($completeonly)
		{
			$forbidden .= " AND qpl_questions.complete = " . $this->ilias->db->quote("1");
		}

		// get all questions in the test
		$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND tst_test_question.test_fi = %s",
			$this->ilias->db->quote($this->getTestId() . "")
		);
		$result = $this->ilias->db->query($query);
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

		$query = "SELECT qpl_questions.question_id FROM qpl_questions, qpl_question_type WHERE $original_clause$forbidden AND qpl_questions.question_type_fi = qpl_question_type.question_type_id $where$order$limit";
    $query_result = $this->ilias->db->query($query);
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
		$query = "SELECT qpl_questions.*, qpl_question_type.type_tag, object_reference.ref_id FROM qpl_questions, qpl_question_type, object_reference WHERE $original_clause AND qpl_questions.obj_fi = object_reference.obj_id$forbidden AND qpl_questions.question_type_fi = qpl_question_type.question_type_id $where$order$limit";
    $query_result = $this->ilias->db->query($query);
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
	function _getTestType($test_id)
	{
		global $ilDB;
		
		$result = "";
		$query = sprintf("SELECT tst_test_type.type_tag FROM tst_test_type, tst_tests WHERE tst_test_type.test_type_id = tst_tests.test_type_fi AND tst_tests.test_id = %s",
			$ilDB->quote($test_id)
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
	* Returns a QTI xml representation of the test
	*
	* Returns a QTI xml representation of the test
	*
	* @return string The QTI xml representation of the test
	* @access public
	*/
	function to_xml()
	{
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<questestinterop></questestinterop>\n";
		$domxml = domxml_open_mem($xml_header);
		$root = $domxml->document_element();
		// qti assessment
		$qtiAssessment = $domxml->create_element("assessment");
		$qtiAssessment->set_attribute("ident", "il_".IL_INST_ID."_tst_".$this->getTestId());
		$qtiAssessment->set_attribute("title", $this->getTitle());
		
		// add qti comment
		$qtiComment = $domxml->create_element("qticomment");
		$qtiCommentText = $domxml->create_text_node($this->getDescription());
		$qtiComment->append_child($qtiCommentText);
		$qtiAssessment->append_child($qtiComment);
		$qtiComment = $domxml->create_element("qticomment");
		$qtiCommentText = $domxml->create_text_node("ILIAS Version=".$this->ilias->getSetting("ilias_version"));
		$qtiComment->append_child($qtiCommentText);
		$qtiAssessment->append_child($qtiComment);
		$qtiComment = $domxml->create_element("qticomment");
		$qtiCommentText = $domxml->create_text_node("Author=".$this->getAuthor());
		$qtiComment->append_child($qtiCommentText);
		$qtiAssessment->append_child($qtiComment);
		// add qti duration
		if ($this->enable_processing_time)
		{
			$qtiDuration = $domxml->create_element("duration");
			preg_match("/(\d+):(\d+):(\d+)/", $this->processing_time, $matches);
			$qtiDurationText = $domxml->create_text_node(sprintf("P0Y0M0DT%dH%dM%dS", $matches[1], $matches[2], $matches[3]));
			$qtiDuration->append_child($qtiDurationText);
			$qtiAssessment->append_child($qtiDuration);
		}
		// add qti assessmentcontrol
		$qtiAssessmentcontrol = $domxml->create_element("assessmentcontrol");
		$qtiAssessmentcontrol->set_attribute("solutionswitch", sprintf("%d", $this->getScoreReporting()));
		$qtiAssessment->append_child($qtiAssessmentcontrol);
		// add qti objectives
		$qtiObjectives = $domxml->create_element("objectives");
		$qtiMaterial = $domxml->create_element("material");
		$qtiMaterial->set_attribute("label", "introduction");
		$qtiMatText = $domxml->create_element("mattext");
		$qtiMatTextText = $domxml->create_text_node($this->getIntroduction());
		$qtiMatText->append_child($qtiMatTextText);
		$qtiMaterial->append_child($qtiMatText);
		$qtiObjectives->append_child($qtiMaterial);
		$qtiAssessment->append_child($qtiObjectives);
		// add the rest of the preferences in qtimetadata tags, because there is no correspondent definition in QTI
		$qtiMetadata = $domxml->create_element("qtimetadata");
		// test type
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("test_type");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node(sprintf("%d", $this->getTestType()));
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// sequence settings
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("sequence_settings");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node(sprintf("%d", $this->getSequenceSettings()));
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// author
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("author");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node($this->getAuthor());
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// score reporting date
		if ($this->getReportingDate())
		{
			$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
			$qtiFieldLabel = $domxml->create_element("fieldlabel");
			$qtiFieldLabelText = $domxml->create_text_node("reporting_date");
			$qtiFieldLabel->append_child($qtiFieldLabelText);
			$qtiFieldEntry = $domxml->create_element("fieldentry");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->reporting_date, $matches);
			$qtiFieldEntryText = $domxml->create_text_node(sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$qtiFieldEntry->append_child($qtiFieldEntryText);
			$qtiMetadatafield->append_child($qtiFieldLabel);
			$qtiMetadatafield->append_child($qtiFieldEntry);
			$qtiMetadata->append_child($qtiMetadatafield);
		}
		// number of tries
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("nr_of_tries");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node(sprintf("%d", $this->getNrOfTries()));
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// random test
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("random_test");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node(sprintf("%d", $this->isRandomTest()));
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// random question count
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("random_question_count");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node(sprintf("%d", $this->getRandomQuestionCount()));
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// starting time
		if ($this->getStartingTime())
		{
			$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
			$qtiFieldLabel = $domxml->create_element("fieldlabel");
			$qtiFieldLabelText = $domxml->create_text_node("starting_time");
			$qtiFieldLabel->append_child($qtiFieldLabelText);
			$qtiFieldEntry = $domxml->create_element("fieldentry");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->starting_time, $matches);
			$qtiFieldEntryText = $domxml->create_text_node(sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$qtiFieldEntry->append_child($qtiFieldEntryText);
			$qtiMetadatafield->append_child($qtiFieldLabel);
			$qtiMetadatafield->append_child($qtiFieldEntry);
			$qtiMetadata->append_child($qtiMetadatafield);
		}
		// ending time
		if ($this->getEndingTime())
		{
			$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
			$qtiFieldLabel = $domxml->create_element("fieldlabel");
			$qtiFieldLabelText = $domxml->create_text_node("ending_time");
			$qtiFieldLabel->append_child($qtiFieldLabelText);
			$qtiFieldEntry = $domxml->create_element("fieldentry");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->ending_time, $matches);
			$qtiFieldEntryText = $domxml->create_text_node(sprintf("P%dY%dM%dDT%dH%dM%dS", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
			$qtiFieldEntry->append_child($qtiFieldEntryText);
			$qtiMetadatafield->append_child($qtiFieldLabel);
			$qtiMetadatafield->append_child($qtiFieldEntry);
			$qtiMetadata->append_child($qtiMetadatafield);
		}
		foreach ($this->mark_schema->mark_steps as $index => $mark)
		{
			// mark steps
			$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
			$qtiFieldLabel = $domxml->create_element("fieldlabel");
			$qtiFieldLabelText = $domxml->create_text_node("mark_step_$index");
			$qtiFieldLabel->append_child($qtiFieldLabelText);
			$qtiFieldEntry = $domxml->create_element("fieldentry");
			$qtiFieldEntryText = $domxml->create_text_node(sprintf("<short>%s</short><official>%s</official><percentage>%.2f</percentage><passed>%d</passed>", $mark->get_short_name(), $mark->get_official_name(), $mark->get_minimum_level(), $mark->get_passed()));
			$qtiFieldEntry->append_child($qtiFieldEntryText);
			$qtiMetadatafield->append_child($qtiFieldLabel);
			$qtiMetadatafield->append_child($qtiFieldEntry);
			$qtiMetadata->append_child($qtiMetadatafield);
		}
		$qtiAssessment->append_child($qtiMetadata);
		$root->append_child($qtiAssessment);
		$xml = $domxml->dump_mem(true);
		$domxml->free();
		foreach ($this->questions as $question_id) {
			$question =& ilObjTest::_instanciateQuestion($question_id);
			$qti_question = $question->to_xml(false);
			$qti_question = preg_replace("/<questestinterop>/", "", $qti_question);
			$qti_question = preg_replace("/<\/questestinterop>/", "", $qti_question);
			$xml = str_replace("</questestinterop>", "$qti_question</questestinterop>", $xml);
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
		$nested = new ilNestedSetXML();
		$nested->setParameterModifier($this, "modifyExportIdentifier");
		$a_xml_writer->appendXML($nested->export($this->getId(),
			$this->getType()));
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
		include_once("content/classes/Media/class.ilObjMediaObject.php");

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
		include_once("classes/class.ilObjFile.php");

		foreach ($this->file_ids as $file_id)
		{
			$expLog->write(date("[y-m-d H:i:s] ")."File Item ".$file_id);
			$file_obj = new ilObjFile($file_id, false);
			$file_obj->export($a_target_dir);
			unset($file_obj);
		}
	}

	/**
	* Imports the test properties from XML into the test object
	*
	* Imports the test properties from XML into the test object
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function from_xml($xml_text)
	{
		$result = false;
		$this->mark_schema->flush();
		$xml_text = preg_replace("/>\s*?</", "><", $xml_text);
		$domxml = domxml_open_mem($xml_text);
		if (!empty($domxml))
		{
			$root = $domxml->document_element();
			$this->setTitle($root->get_attribute("title"));
			$item = $root;
			$itemnodes = $item->child_nodes();
			foreach ($itemnodes as $index => $node)
			{
				switch ($node->node_name())
				{
					case "qticomment":
						$comment = $node->get_content();
						if (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$this->setAuthor($comment);
						}
						elseif (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						else
						{
							$this->setDescription($comment);
						}
						break;
					case "duration":
						$iso8601period = $node->get_content();
						if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
						{
							$this->enable_processing_time = 1;
							$this->setProcessingTime(sprintf("%02d:%02d:%02d", $matches[4], $matches[5], $matches[6]));
						}
						break;
					case "assessmentcontrol":
						$this->setScoreReporting($node->get_attribute("solutionswitch"));
						break;
					case "objectives":
						$material = $node->first_child();
						if (strcmp($material->get_attribute("label"), "introduction") == 0)
						{
							$mattext = $material->first_child();
							$this->setIntroduction($mattext->get_content());
						}
						break;
					case "qtimetadata":
						$metadata_fields = $node->child_nodes();
						foreach ($metadata_fields as $index => $metadata_field)
						{
							$fieldlabel = $metadata_field->first_child();
							$fieldentry = $fieldlabel->next_sibling();
							switch ($fieldlabel->get_content())
							{
								case "sequence_settings":
									$this->setSequenceSettings($fieldentry->get_content());
									break;
								case "author":
									$this->setAuthor($fieldentry->get_content());
									break;
								case "test_type":
									$this->setTestType($fieldentry->get_content());
									break;
								case "reporting_date":
									$iso8601period = $fieldentry->get_content();
									if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
									{
										$this->setReportingDate(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
									}
									break;
								case "nr_of_tries":
									$this->setNrOfTries($fieldentry->get_content());
									break;
								case "random_test":
									$this->setRandomTest($fieldentry->get_content());
									break;
								case "random_question_count":
									$this->setRandomQuestionCount($fieldentry->get_content());
									break;
								case "starting_time":
									$iso8601period = $fieldentry->get_content();
									if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
									{
										$this->setStartingTime(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
									}
									break;
								case "ending_time":
									$iso8601period = $fieldentry->get_content();
									if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
									{
										$this->setEndingTime(sprintf("%02d%02d%02d%02d%02d%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
									}
									break;
							}
							if (preg_match("/mark_step_\d+/", $fieldlabel->get_content()))
							{
								$xmlmark = $fieldentry->get_content();
								preg_match("/<short>(.*?)<\/short>/", $xmlmark, $matches);
								$mark_short = $matches[1];
								preg_match("/<official>(.*?)<\/official>/", $xmlmark, $matches);
								$mark_official = $matches[1];
								preg_match("/<percentage>(.*?)<\/percentage>/", $xmlmark, $matches);
								$mark_percentage = $matches[1];
								preg_match("/<passed>(.*?)<\/passed>/", $xmlmark, $matches);
								$mark_passed = $matches[1];
								$this->mark_schema->add_mark_step($mark_short, $mark_official, $mark_percentage, $mark_passed);
							}
						}
						break;
				}
			}
			$result = true;
		}
		return $result;
	}
	
	/**
	* Imports a test from XML into the ILIAS database
	*
	* Imports a test from XML into the ILIAS database
	*
	* @return array import results
	* @access public
	*/
	function importObject($source, $questionpool_id)
	{
		$ilias_version = "";
		$error = 0;
		if (($source == 'none') || (!$source) || $file_info["error"] > UPLOAD_ERR_OK)
		{
//			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
			$error = 1;
		}
		

		if (!$error)
		{
			// import file as a test
			$fh = fopen($source, "r");
			if (!$fh)
			{
//			$this->ilias->raiseError("Error opening the file!",$this->ilias->error_obj->MESSAGE);
				$error = 1;
				return $error;
			}

			$xml = fread($fh, filesize($source));
			$result = fclose($fh);
			if (!$result)
			{
//			$this->ilias->raiseError("Error closing the file!",$this->ilias->error_obj->MESSAGE);
				$error = 1;
				return $error;
			}

			$assessment_part = "";
			if (preg_match("/ILIAS Version\=(\d+)\.(\d+)\.?(\d?)/", $xml, $matches))
			{
				$major_version = $matches[1];
				unset($matches[0]);
				$ilias_version = join($matches, ".");
				if ($major_version == 2)
				{
					$this->setTestType(TYPE_SELF_ASSESSMENT);
					global $lng;
					$lng->loadLanguageModule("assessment");
					$this->setTitle($lng->txt("imported_test"));
					$assessment_part = $this->to_xml();
					if (preg_match("/(<assessment[^>]*>.*?<\/assessment>)/si", $assessment_part, $matches))
					{
						$assessment_part = $matches[1];
					}
				}
			}

			$found_assessment = preg_match("/(<assessment[^>]*>.*?<\/assessment>)/si", $xml, $matches);
			if ($found_assessment or $assessment_part)
			{
				if ((!$found_assessment) and ($assessment_part))
				{
					$matches[1] = $assessment_part;
				}
				// read test properties
				$succeeded = $this->from_xml($matches[1]);

				if (!$succeeded)
				{
//			$this->ilias->raiseError("The test properties do not contain proper values!",$this->ilias->error_obj->MESSAGE);
					$error = 1;
					return $error;
				}
			}
			else
			{
//			$this->ilias->raiseError("No test properties found. Cannot import test!",$this->ilias->error_obj->MESSAGE);
				$error = 1;
				return $error;
			}

			$question_counter = 1;
			
			$this->import_mapping = array();
			
			if (preg_match_all("/(<item[^>]*>.*?<\/item>)/si", $xml, $matches))
			{

				foreach ($matches[1] as $index => $item)
				{
					// get identifier
					if (preg_match("/(<item[^>]*>)/is", $item, $start_tag))
					{
						if (preg_match("/(ident=\"([^\"]*)\")/is", $start_tag[1], $ident))
						{
							$ident = $ident[2];
						}
					}
					
					$question = "";
					if (preg_match("/<qticomment>Questiontype\=(.*?)<\/qticomment>/is", $item, $questiontype))
					{
						switch ($questiontype[1])
						{
							case CLOZE_TEST_IDENTIFIER:
								$question = new ASS_ClozeTest();
								break;
							case IMAGEMAP_QUESTION_IDENTIFIER:
								$question = new ASS_ImagemapQuestion();
								break;
							case MATCHING_QUESTION_IDENTIFIER:
								$question = new ASS_MatchingQuestion();
								break;
							case MULTIPLE_CHOICE_QUESTION_IDENTIFIER:
								$question = new ASS_MultipleChoice();
								break;
							case ORDERING_QUESTION_IDENTIFIER:
								$question = new ASS_OrderingQuestion();
								break;
							case JAVAAPPLET_QUESTION_IDENTIFIER:
								$question = new ASS_JavaApplet();
								break;
							case TEXT_QUESTION_IDENTIFIER:
								$question = new ASS_TextQuestion();
								break;
						}
						if ($question)
						{
							$question->setObjId($questionpool_id);
							if ($question->from_xml("<questestinterop>$item</questestinterop>"))
							{
								$question->saveToDb();
								$q_1_id = $question->getId();
								$question_id = $question->duplicate(true);
								$this->questions[$question_counter++] = $question_id;
								$this->import_mapping[$ident] = array(
									"pool" => $q_1_id, "test" => $question_id);
							}
							else
							{
								$this->ilias->raiseError($this->lng->txt("error_importing_question"), $this->ilias->error_obj->MESSAGE);
							}
						}
					}
				}
			}
		}
//echo "<br>"; var_dump($this->import_mapping);
		$result = array(
			"error" => $error,
			"version" => $ilias_version
		);
		return $result;
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
	
	function getECTSGrade($reached_points, $max_points)
	{
		require_once "./classes/class.ilStatistics.php";
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
			if ($reached_points >= $ects_percentiles["A"])
			{
				return "A";
			}
			else if ($reached_points >= $ects_percentiles["B"])
			{
				return "B";
			}
			else if ($reached_points >= $ects_percentiles["C"])
			{
				return "C";
			}
			else if ($reached_points >= $ects_percentiles["D"])
			{
				return "D";
			}
			else if ($reached_points >= $ects_percentiles["E"])
			{
				return "E";
			}
			else if (strcmp($this->ects_fx, "") != 0)
			{
				if ($max_points > 0)
				{
					$percentage = ($reached_points / $max_points) * 100.0;
				}
				else
				{
					$percentage = 0.0;
				}
				if ($percentage >= $this->object->ects_fx)
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
	
	/**
	* Set the title and the description for the meta data
	*/
	function updateTitleAndDescription()
	{
		$this->initMeta();
		$this->meta_data->updateTitleAndDescription($this->getTitle(), $this->getDescription());
	}

	/**
	* update meta data only
	*/
	function updateMetaData()
	{
		$this->initMeta();
		$this->meta_data->update();
		if ($this->meta_data->section != "General")
		{
			$meta = $this->meta_data->getElement("Title", "General");
			$this->meta_data->setTitle($meta[0]["value"]);
			$meta = $this->meta_data->getElement("Description", "General");
			$this->meta_data->setDescription($meta[0]["value"]);
		}
		else
		{
			$this->setTitle($this->meta_data->getTitle());
			$this->setDescription($this->meta_data->getDescription());
		}
		parent::update();
	}
	
/**
* Returns the available question pools for the active user
* 
* Returns the available question pools for the active user
*
* @return array The available question pools
* @access public
*/
	function &_getAvailableTests($use_object_id = false)
	{
		global $rbacsystem;
		global $ilDB;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'tst' ORDER BY object_data.title";
		$result = $ilDB->query($query);
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
		if ($new_id > 0)
		{
			$query = sprintf("SELECT * FROM tst_test_random WHERE test_fi = %s",
				$this->ilias->db->quote($this->getTestId() . "")
			);
			$result = $this->ilias->db->query($query);
			if ($result->numRows())
			{
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$query = sprintf("INSERT INTO tst_test_random (test_random_id, test_fi, questionpool_fi, num_of_q, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
						$this->ilias->db->quote($new_id . ""),
						$this->ilias->db->quote($row["questionpool_fi"] . ""),
						$this->ilias->db->quote($row["num_of_q"] . "")
					);
					$insertresult = $this->ilias->db->query($query);
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
		$newObj->setTitle($original->getTitle());
		$newObj->setDescription($original->getDescription());
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
//		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());
		
		$newObj->$author = $original->getAuthor();
		$newObj->introduction = $original->getIntroduction();
		$newObj->mark_schema = $original->mark_schema;
		$newObj->sequence_settings = $original->getSequenceSettings();
		$newObj->score_reporting = $original->getScoreReporting();
		$newObj->reporting_date = $original->getReportingDate();
		$newObj->test_type = $original->getTestType();
		$newObj->test_formats = $original->getTestFormats();
		$newObj->nr_of_tries = $original->getNrOfTries();
		$newObj->processing_time = $original->getProcessingTime();
		$newObj->enable_processing_time = $original->getEnableProcessingTime();
		$newObj->starting_time = $original->getStartingTime();
		$newObj->ending_time = $original->getEndingTime();
		$newObj->ects_output = $original->ects_output;
		$newObj->ects_fx = $original->ects_fx;
		$newObj->ects_grades = $original->ects_grades;
		$newObj->random_test = $original->random_test;
		$newObj->random_question_count = $original->random_question_count;
		$newObj->saveToDb();		
		if ($original->isRandomTest())
		{
			$newObj->saveRandomQuestionCount($newObj->random_question_count);
			$original->cloneRandomQuestions($newObj->getTestId());
		}
		else
		{
			// clone the questions
			foreach ($original->questions as $key => $question_id)
			{
				$question = ilObjTest::_instanciateQuestion($question_id);
				$newObj->questions[$key] = $question->duplicate();
	//			$question->id = -1;
				$original_id = ASS_Question::_getOriginalId($question_id);
				$question = ilObjTest::_instanciateQuestion($newObj->questions[$key]);
				$question->saveToDb($original_id);
			}
		}
		
		$newObj->saveToDb();		

		// clone meta data
		$meta_data =& new ilMetaData($original->getType(), $original->getId());
		include_once("./classes/class.ilNestedSetXML.php");
		$nested = new ilNestedSetXML();
		$nested->dom = domxml_open_mem($meta_data->nested_obj->dom->dump_mem(0));
		$nodes = $nested->getDomContent("//MetaData/General", "Identifier");
		if (is_array($nodes))
		{
			$nodes[0]["Entry"] = "il__" . $newObj->getType() . "_" . $newObj->getId();
			$nested->updateDomContent("//MetaData/General", "Identifier", 0, $nodes[0]);
		}
		$xml = $nested->dom->dump_mem(0);
		$nested->import($xml, $newObj->getId(), $newObj->getType());
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
	
	function createRandomSolutionsForAllUsers()
	{
		global $ilDB;
		global $ilUser;
		
		$db =& $ilDB->db;
		$sequence_arr = array_flip($this->questions);
		$sequence = join($sequence_arr, ",");
		require_once("./classes/class.ilObjUser.php");
		$logins = ilObjUser::_getAllUserData(array("login"));

		foreach ($logins as $login)
		{
			$user_id = $login["usr_id"];
			$old_active = $this->getActiveTestUser($user_id);
			if ($old_active) {
				$query = sprintf("UPDATE tst_active SET lastindex = %s, sequence = %s, postponed = %s, tries = %s WHERE user_fi = %s AND test_fi = %s",
					$db->quote("0"),
					$db->quote($sequence),
					$db->quote(""),
					$db->quote("1"),
					$db->quote($user_id),
					$db->quote($this->getTestId())
				);
			} else {
				$sequence_arr = array_flip($this->questions);
				$sequence = join($sequence_arr, ",");
				$query = sprintf("INSERT INTO tst_active (active_id, user_fi, test_fi, sequence, postponed, lastindex, tries, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
					$db->quote($user_id),
					$db->quote($this->getTestId()),
					$db->quote($sequence),
					$db->quote(""),
					$db->quote("0"),
					$db->quote("1")
				);
			}
			$db->query($query);
		}
		foreach ($this->questions as $question_id) {
			$question =& ilObjTest::_instanciateQuestion($question_id);
			foreach ($logins as $login)
			{
				$question->createRandomSolution($this->getTestId(), $login["usr_id"]);
			}
		}
	}

/**
* Returns an array of users who are selected for a test evaluation of a given user
* 
* Returns an array of users who are selected for a test evaluation of a given user
*
* @access public
*/
	function &getEvaluationUsers($user_id)
	{
		$users = array();
		$query = sprintf("SELECT tst_eval_users.user_fi, usr_data.firstname, usr_data.lastname FROM tst_eval_users, usr_data WHERE tst_eval_users.test_fi = %s AND tst_eval_users.evaluator_fi = %s AND tst_eval_users.user_fi = usr_data.usr_id",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($user_id . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$users[$row["user_fi"]] = trim($row["firstname"] ." " . $row["lastname"]);
			}
		}
		return $users;
	}

/**
* Returns an array of groups who are selected for a test evaluation of a given user
* 
* Returns an array of groups who are selected for a test evaluation of a given user
*
* @access public
*/
	function &getEvaluationGroups($user_id)
	{
		$groups = array();
		$query = sprintf("SELECT group_fi FROM tst_eval_groups WHERE test_fi = %s AND evaluator_fi = %s",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($user_id . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$groups[$row["group_fi"]] = $row["group_fi"];
			}
		}
		return $groups;
	}
	
/**
* Disinvites a user from a survey
* 
* Disinvites a user from a survey
*
* @param integer $user_id The database id of the disinvited user
* @access public
*/
	function removeSelectedUser($user_id, $evaluator_id)
	{
		$query = sprintf("DELETE FROM tst_eval_users WHERE test_fi = %s AND user_fi = %s AND evaluator_fi = %s",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($user_id . ""),
			$this->ilias->db->quote($evaluator_id . "")
		);
		$result = $this->ilias->db->query($query);
	}

/**
* Invites a user to a survey
* 
* Invites a user to a survey
*
* @param integer $user_id The database id of the invited user
* @access public
*/
	function addSelectedUser($user_id, $evaluator_id)
	{
		$query = sprintf("SELECT user_fi FROM tst_eval_users WHERE test_fi = %s AND evaluator_fi = %s AND user_fi = %s",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($evaluator_id . ""),
			$this->ilias->db->quote($user_id . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() < 1)
		{
			$query = sprintf("INSERT INTO tst_eval_users (eval_users_id, test_fi, evaluator_fi, user_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getTestId() . ""),
				$this->ilias->db->quote($evaluator_id . ""),
				$this->ilias->db->quote($user_id . "")
			);
			$result = $this->ilias->db->query($query);
		}
	}

/**
* Disinvites a group from a survey
* 
* Disinvites a group from a survey
*
* @param integer $group_id The database id of the disinvited group
* @access public
*/
	function removeSelectedGroup($group_id, $evaluator_id)
	{
		$query = sprintf("DELETE FROM tst_eval_groups WHERE test_fi = %s AND group_fi = %s AND evaluator_fi = %s",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($group_id . ""),
			$this->ilias->db->quote($evaluator_id . "")
		);
		$result = $this->ilias->db->query($query);
	}

/**
* Invites a group to a survey
* 
* Invites a group to a survey
*
* @param integer $group_id The database id of the invited group
* @access public
*/
	function addSelectedGroup($group_id, $evaluator_id)
	{
		$query = sprintf("SELECT group_fi FROM tst_eval_groups WHERE test_fi = %s AND evaluator_fi = %s AND group_fi = %s",
			$this->ilias->db->quote($this->getTestId() . ""),
			$this->ilias->db->quote($evaluator_id . ""),
			$this->ilias->db->quote($group_id . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() < 1)
		{
			$query = sprintf("INSERT INTO tst_eval_groups (eval_groups_id, test_fi, evaluator_fi, group_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getTestId() . ""),
				$this->ilias->db->quote($evaluator_id . ""),
				$this->ilias->db->quote($group_id . "")
			);
			$result = $this->ilias->db->query($query);
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
* Redirect script to call a test with the test reference id
* 
* Redirect script to call a test with the test reference id
*
* @param integer $a_target The reference id of the test
* @access	public
*/
	function _goto($a_target)
	{
		global $rbacsystem, $ilErr, $lng;

		if ($rbacsystem->checkAccess("read", $a_target))
		{
			ilUtil::redirect("assessment/test.php?cmd=run&ref_id=$a_target");
		}
		else
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
		}
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
		// delete eventually set questions of a previous non-random test
		$this->removeAllTestEditings();
		$query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
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
		// delete eventually set random question pools of a previous random test
		$this->removeAllTestEditings();
		$query = sprintf("DELETE FROM tst_test_random WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
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
		if (strcmp($question_id, "") == 0)
		{
			$question_id = "NULL";
		}
		else
		{
			$original_id = ASS_Question::_getOriginalId($question_id);
			$question_id = $this->ilias->db->quote($question_id . "");
		}
		if (strcmp($original_id, "") == 0)
		{
			$original_id = "NULL";
		}
		else
		{
			$original_id = $this->ilias->db->quote($original_id . "");
		}
		$query = sprintf("INSERT INTO ass_log (ass_log_id, user_fi, obj_fi, logtext, question_fi, original_fi, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, NULL)",
			$this->ilias->db->quote($ilUser->id . ""),
			$this->ilias->db->quote($this->getId() . ""),
			$this->ilias->db->quote($logtext . ""),
			$question_id,
			$original_id
		);
		$result = $this->ilias->db->query($query);
	}
	
} // END class.ilObjTest
?>
