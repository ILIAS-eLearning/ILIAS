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
// ALTER  TABLE  `qpl_questions`  ADD  `original_id` INT AFTER  `created` ;

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

define("TEST_FIXED_SEQUENCE", 0);
define("TEST_POSTPONE", 1);

define("REPORT_AFTER_QUESTION", 0);
define("REPORT_AFTER_TEST", 1);

define("TEST_FORMAT_NEW", 1);
define("TEST_FORMAT_RESUME", 2);
define("TEST_FORMAT_REVIEW", 4);

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
* Contains the session settings of the test
* 
* Contains the session settings of the test
*
* @var object
*/
  var $session_sessings;
  
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
		$this->score_reporting = REPORT_AFTER_QUESTION;
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
		//$this->mark_schema = new ASS_MarkSchema();
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
		$this->updateMetaData();
		if (!parent::update())
		{			
			return false;
		}

		// put here object specific stuff
		
		return true;
	}
	
	function createReference() {
		parent::createReference();
		$this->saveToDb();
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
  function testTitleExists($title) {
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
  function duplicate() {
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
    // Zugeordnete Fragen duplizieren
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
	function saveCompleteStatus() {
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
  function saveToDb()
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
    if ($this->test_id == -1) {
      // Neuen Datensatz schreiben
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO tst_tests (test_id, obj_fi, author, test_type_fi, introduction, sequence_settings, score_reporting, nr_of_tries, processing_time, enable_processing_time, reporting_date, starting_time, ending_time, complete, ects_output, ects_a, ects_b, ects_c, ects_d, ects_e, ects_fx, created, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$db->quote($this->getId()),
        $db->quote($this->author),
        $db->quote($this->test_type),
        $db->quote($this->introduction), 
        $db->quote($this->sequence_settings),
        $db->quote($this->score_reporting),
        $db->quote(sprintf("%d", $this->nr_of_tries)),
        $db->quote($this->processing_time),
				$db->quote("$this->enable_processing_time"),
        $db->quote($this->reporting_date),
        $db->quote($this->starting_time),
        $db->quote($this->ending_time),
				$db->quote("$complete"),
				$db->quote($this->ects_output . ""),
				$db->quote($this->ects_grades["A"] . ""),
				$db->quote($this->ects_grades["B"] . ""),
				$db->quote($this->ects_grades["C"] . ""),
				$db->quote($this->ects_grades["D"] . ""),
				$db->quote($this->ects_grades["E"] . ""),
				$ects_fx,
        $db->quote($created)
      );
      $result = $db->query($query);
      if ($result == DB_OK) {
        $this->test_id = $this->ilias->db->getLastInsertId();
      }
    } else {
      // Vorhandenen Datensatz aktualisieren
      $query = sprintf("UPDATE tst_tests SET author = %s, test_type_fi = %s, introduction = %s, sequence_settings = %s, score_reporting = %s, nr_of_tries = %s, processing_time = %s, enable_processing_time = %s, reporting_date = %s, starting_time = %s, ending_time = %s, ects_output = %s, ects_a = %s, ects_b = %s, ects_c = %s, ects_d = %s, ects_e = %s, ects_fx = %s, complete = %s WHERE test_id = %s",
        $db->quote($this->author), 
        $db->quote($this->test_type), 
        $db->quote($this->introduction), 
        $db->quote($this->sequence_settings), 
        $db->quote($this->score_reporting), 
        $db->quote(sprintf("%d", $this->nr_of_tries)), 
        $db->quote($this->processing_time),
				$db->quote("$this->enable_processing_time"),
        $db->quote($this->reporting_date), 
        $db->quote($this->starting_time), 
        $db->quote($this->ending_time), 
				$db->quote($this->ects_output . ""),
				$db->quote($this->ects_grades["A"] . ""),
				$db->quote($this->ects_grades["B"] . ""),
				$db->quote($this->ects_grades["C"] . ""),
				$db->quote($this->ects_grades["D"] . ""),
				$db->quote($this->ects_grades["E"] . ""),
				$ects_fx,
				$db->quote("$complete"),
        $db->quote($this->test_id)
      );
      $result = $db->query($query);
    }
    if ($result == DB_OK) {
			$this->saveQuestionsToDb();
      $this->mark_schema->saveToDb($this->test_id);
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
	function saveQuestionsToDb() {
		// delete existing category relations
    $query = sprintf("DELETE FROM tst_test_question WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
		// create new category relations
		foreach ($this->questions as $key => $value) {
			$query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getTestId()),
				$this->ilias->db->quote($value),
				$this->ilias->db->quote($key)
			);
			$result = $this->ilias->db->query($query);
		}
	}


	/**
	* Loads a ilObjTest object from a database
	*
	* Loads a ilObjTest object from a database (experimental)
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
				$this->mark_schema->flush();
				$this->mark_schema->loadFromDb($this->test_id);
				$this->loadQuestions();
			}
		}
	}

	function loadQuestions() {
    $db = $this->ilias->db;
		$this->questions = array();
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
			$db->quote($this->test_id)
		);
		$result = $db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			$this->questions[$data->sequence] = $data->question_fi;
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
  function setAuthor($author = "") {
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
  function setIntroduction($introduction = "") {
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
  function getAuthor() {
    return $this->author;
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
  function getIntroduction() {
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
  function getTestId() {
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
  function setSequenceSettings($sequence_settings = 0) {
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
  function setTestType($type = TYPE_ASSESSMENT) {
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
  function setScoreReporting($score_reporting = 0) {
    $this->score_reporting = $score_reporting;
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
  function setReportingDate($reporting_date) {
    if (!$reporting_date) {
      $this->reporting_date = "";
    } else {
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
  function getSequenceSettings() {
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
  function getScoreReporting() {
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
  function getTestType() {
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
  function getReportingDate() {
    return $this->reporting_date;
  }

/**
* Sets the test formats
* 
* Sets the test formats of the ilObjTest object
*
* @param integer $test_formats A combination of the defined test formats TEST_FORMAT_NEW (=1), TEST_FORMAT_RESUME (=2) and TEST_FORMAT_REVIEW (=4)
* @access public
* @see $test_formats
*/
  function setTestFormats($test_formats = 7) {
    $this->test_formats = $test_formats;
  }

/**
* Gets the sequence settings
* 
* Gets the sequence settings
*
* @return integer The test formats of the ilObjTest object
* @access public
* @see $test_formats
*/
  function getTestFormats() {
    return $this->test_formats;
  }

/**
* Checks if the user can resume the test
* 
* Checks if the user can resume the test and returns TRUE if the user can resume the test.
* Otherwise the result is FALSE.
*
* @return integer The resume result
* @access public
* @see $test_formats
*/
  function canResume() {
    return (($this->test_formats & TEST_FORMAT_RESUME) == 1);
  }

/**
* Checks if the user can review the test
*
* Checks if the user can review the test and returns TRUE if the user can review the test.
* Otherwise the result is FALSE.
*
* @return integer The review result
* @access public
* @see $test_formats
*/
  function canReview() {
    return (($this->test_formats & TEST_FORMAT_REVIEW) == 1);
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
  function getNrOfTries() {
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
  function getProcessingTime() {
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
  function getEnableProcessingTime() {
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
  function getStartingTime() {
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
  function getEndingTime() {
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
  function setNrOfTries($nr_of_tries = 0) {
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
  function setProcessingTime($processing_time = "00:00:00") {
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
	function setEnableProcessingTime($enable = 0) {
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
  function setStartingTime($starting_time = "") {
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
  function setEndingTime($ending_time = "") {
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
	function removeQuestion($question_id) {
		$question = new ASS_Question();
		$question->delete($question_id);
		$this->removeAllTestEditings($question_id);
		$this->loadQuestions();
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
	function removeAllTestEditings($question_id = "") {
		// remove test_active entries, because test has changed
		$this->deleteActiveTests();
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
	}
	
/**
* Deletes all active references to this test
*
* Deletes all active references to this test. This is necessary, if the test has been changed to
* guarantee the same conditions for all users.
*
* @access public
*/
	function deleteActiveTests() {
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
		);
		$result = $this->ilias->db->query($query);
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
	function questionMoveUp($question_id) {
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
	function questionMoveDown($question_id) {
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
		if ($result->numRows() == 1) {
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
		// remove test_active entries, because test has changed
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->getTestId())
			);
		$result = $this->ilias->db->query($query);
		$this->loadQuestions();
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
	function getQuestionTitle($sequence) {
		$query = sprintf("SELECT title from qpl_questions WHERE question_id = %s",
			$this->ilias->db->quote($this->questions[$sequence])
		);
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
	function getQuestionDataset($question_id) {
		$query = sprintf("SELECT qpl_questions.*, qpl_question_type.type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
			$this->ilias->db->quote("$question_id")
		);
    $result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row;
	}
	
	function &get_qpl_titles() {
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
	
	function &getExistingQuestions() {
		$existing_questions = array();
		$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE tst_test_question.test_fi = %s AND tst_test_question.question_fi = qpl_questions.question_id",
			$this->ilias->db->quote($this->getTestId())
		);
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
  function getQuestionType($question_id) {
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
	
	function updateWorkingTime($times_id)
	{
		$q = sprintf("UPDATE tst_times SET finished = %s WHERE times_id = %s",
			$this->ilias->db->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$this->ilias->db->quote($times_id)
		);
		$result = $this->ilias->db->query($q);
	}
  
	function getQuestionCount ()
	{
		return count($this->questions);
	}
	
	function getQuestionIdFromActiveUserSequence($sequence) {
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
	function &getAllQuestionsForActiveUser() {
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
		$query = "SELECT qpl_questions.* FROM qpl_questions, tst_test_question WHERE tst_test_question.question_fi = qpl_questions.question_id AND qpl_questions.question_id IN (" . join($this->questions, ",") . ")";
		$result = $this->ilias->db->query($query);
		$result_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$result_array[$row["question_id"]] = $row;
		}
		return $result_array;
	}
	
	function getActiveTestUser($user_id = "") {
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
	
	function setActiveTestUser($lastindex = 1, $postpone = "", $addTries = false) {
		global $ilDB;
		global $ilUser;
		
		$db =& $ilDB->db;
		$old_active = $this->getActiveTestUser();
		if ($old_active) {
			$sequence = $old_active->sequence;
			$postponed = $old_active->postponed;
			if ($postpone) {
				$sequence_array = split(",", $sequence);
				$postpone = $sequence_array[$postpone-1];
				$sequence = preg_replace("/\D*$postpone/", "", $sequence) . ",$postpone";
				$sequence = preg_replace("/^,/", "", $sequence);
				$question_id = $this->questions[$postpone];
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
	function &getTestResult($user_id) {
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
    foreach ($sequence_array as $idx => $seq) {
			$value = $this->questions[$seq];
			$question =& ilObjTest::_instanciateQuestion($value);
      $max_points = $question->getMaximumPoints();
      $total_max_points += $max_points;
      $reached_points = $question->getReachedPoints($user_id, $this->getTestId());
      $total_reached_points += $reached_points;
			if ($max_points > 0) {
				$percentvalue = $reached_points / $max_points;
			} else {
				$percentvalue = 0;
			}
			$row = array(
				"nr" => "$key",
				"title" => "<a href=\"" . $_SERVER['PHP_SELF'] . "$add_parameter&evaluation=" . $question->getId() . "\">" . $question->getTitle() . "</a>",
				"max" => sprintf("%d", $max_points),
				"reached" => sprintf("%d", $reached_points),
				"percent" => sprintf("%2.2f ", ($percentvalue) * 100) . "%"
			);
			array_push($result_array, $row);
			$key++;
    }
		$result_array["test"]["total_max_points"] = $total_max_points;
		$result_array["test"]["total_reached_points"] = $total_reached_points;
		$result_array["test"]["test"] = $this;
		return $result_array;
	}
	
	/**
	* get description of content object
	*
	* @return	string		description
	*/
	function getDescription()
	{
//		return parent::getDescription();
		return $this->meta_data->getDescription();
	}

	/**
	* set description of content object
	*/
	function setDescription($a_description)
	{
		parent::setDescription($a_description);
		$this->meta_data->setDescription($a_description);
	}

	/**
	* get title of glossary object
	*
	* @return	string		title
	*/
	function getTitle()
	{
		//return $this->title;
		return $this->meta_data->getTitle();
	}

	/**
	* set title of glossary object
	*/
	function setTitle($a_title)
	{
		parent::setTitle($a_title);
		$this->meta_data->setTitle($a_title);
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
				if ($now < $epoch_time) {
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
		if (!$result->numRows()) {
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
		} else {
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
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$update = $row["eval_settings_id"];
		if (!$update) {
			$q = sprintf("INSERT INTO tst_eval_settings ".
					 "(eval_settings_id, user_fi, qworkedthrough, pworkedthrough, timeofwork, atimeofwork, firstvisit, " .
					 "lastvisit, resultspoints, resultsmarks, distancemedian, TIMESTAMP) VALUES " .
					 "(NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
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
		} else {
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
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
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
		$mark_obj = $test_result["test"]["test"]->mark_schema->get_matching_mark($percentage);
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
		$result_array = array(
			"qworkedthrough" => $worked_through_result->numRows(),
			"qmax" => count($test_result["test"]["test"]->questions),
			"pworkedthrough" => ($worked_through_result->numRows()) / count($test_result["test"]["test"]->questions),
			"timeofwork" => $max_time,
			"atimeofwork" => $atimeofwork,
			"firstvisit" => $first_date,
			"lastvisit" => $last_date,
			"resultspoints" => $test_result["test"]["total_reached_points"],
			"maxpoints" => $test_result["test"]["total_max_points"],
			"resultsmarks" => $mark_obj->get_short_name(),
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
* Returns a sorted array containing the total points of all users which participated the test
* 
* Returns a sorted array containing the total points of all users which participated the test.
* This array could be used to calculate the median.
*
* @return array The sorted total point values
* @access public
*/
	function &getMedianArray()
	{
		$median_array = array();
		$all_users =& $this->evalTotalPersonsArray();
		foreach ($all_users as $user_id => $user_name)
		{
			$test_result =& $this->getTestResult($user_id);
			array_push($median_array, $test_result["test"]["total_reached_points"]);
		}
		sort($median_array);
		reset($median_array);
		return $median_array;
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
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			$res =& $this->getTestResult($row->user_fi);
			if ((!$res["test"]["total_reached_points"]) or (!$res["test"]["total_max_points"])) {
				$percentage = 0.0;
			} else {
				$percentage = ($res["test"]["total_reached_points"] / $res["test"]["total_max_points"]) * 100.0;
			}
			$mark_obj = $res["test"]["test"]->mark_schema->get_matching_mark($percentage);
			$maximum_points = $res["test"]["total_max_points"];
			if ($mark_obj->get_passed()) {
				$passed_tests++;
				array_push($points, $res["test"]["total_reached_points"]);
			} else {
				$failed_tests++;
			}
		}
		$reached_points = 0;
		$counter = 0;
		foreach ($points as $key => $value) {
			$reached_points += $value;
			$counter++;
		}
		if ($counter) {
			$average_points = round($reached_points / $counter);
		} else {
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
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->started, $matches);
			$epoch_1 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			preg_match("/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/", $row->finished, $matches);
			$epoch_2 = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$times[$row->active_fi] += ($epoch_2 - $epoch_1);
		}
		$max_time = 0;
		$counter = 0;
		foreach ($times as $key => $value) {
			$max_time += $value;
			$counter++;
		}
		if ($counter) {
			$average_time = round($max_time / $counter);
		} else {
			$average_time = 0;
		}
		return $average_time;
	}
	
	function &getForbiddenQuestionpools()
	{
		global $rbacsystem;
		
		// get all available questionpools and remove the trashed questionspools
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
	function &getAvailableQuestionpools()
	{
		global $rbacsystem;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl'";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && ($this->_hasUntrashedReference($row->obj_id)))
			{
				$result_array[$row->ref_id] = $row->title;
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
	function getEstimatedWorkingTime() {
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
* @return array A random selection of questions
* @access public
*/
	function randomSelectQuestions($nr_of_questions, $questionpool)
	{
		global $rbacsystem;
		
		if ($questionpool != 0)
		{
			// retrieve object id
			$query = sprintf("SELECT obj_id FROM object_reference WHERE ref_id = %s",
				$this->ilias->db->quote("$questionpool")
			);
			$result = $this->ilias->db->query($query);
			$row = $result->fetchRow(DB_FETCHMODE_ARRAY);
			$questionpool = $row[0];
		}
		// get all questions in the test
		$query = sprintf("SELECT qpl_questions.original_id FROM qpl_questions, tst_test_question WHERE qpl_questions.question_id = tst_test_question.question_fi AND tst_test_question.test_fi = %s",
			$this->ilias->db->quote($this->getTestId() . "")
		);
		$result = $this->ilias->db->query($query);
		$original_ids = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ARRAY))
		{
			array_push($original_ids, $row[0]);
		}
		$original_clause = "";
		if (count($original_ids))
		{
			$original_clause = " AND ISNULL(qpl_questions.original_id) AND qpl_questions.question_id NOT IN (" . join($original_ids, ",") . ")";
		}

		$forbidden_pools =& $this->getForbiddenQuestionpools();
		$forbidden = "";
		if (count($forbidden_pools))
		{
			$forbidden = " AND qpl_questions.obj_fi NOT IN (" . join($forbidden_pools, ",") . ")";
		}
		$result_array = array();
		if ($questionpool == 0)
		{
			$query = "SELECT COUNT(question_id) FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$forbidden AND qpl_questions.complete = '1'$original_clause";
		}
			else
		{
			$query = sprintf("SELECT COUNT(question_id) FROM qpl_questions WHERE ISNULL(qpl_questions.original_id) AND obj_fi = %s$original_clause",
				$this->ilias->db->quote("$questionpool")
			);
		}
		$result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ARRAY);
		if (($row[0]-count($this->questions)) <= $nr_of_questions)
		{
			// take all available questions
			if ($questionpool == 0)
			{
				$query = "SELECT question_id FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$forbidden AND qpl_questions.complete = '1'$original_clause";
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
			$random_number = mt_rand(0, $row[0] - 1);
			while (count($result_array) < $nr_of_questions)
			{
				if ($questionpool == 0)
				{
					$query = "SELECT question_id FROM qpl_questions, object_data WHERE ISNULL(qpl_questions.original_id) AND object_data.type = 'qpl' AND object_data.obj_id = qpl_questions.obj_fi$forbidden AND qpl_questions.complete = '1'$original_clause LIMIT $random_number, 1";
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
			}
		}
		return $result_array;
	}

/**
* Returns the image path for web accessable images of a survey
*
* Returns the image path for web accessable images of a survey
* The image path is under the CLIENT_WEB_DIR in assessment/REFERENCE_ID_OF_SURVEY/images
*
* @access public
*/
	function getImagePath() {
		return CLIENT_WEB_DIR . "/assessment/" . $this->getId() . "/images/";
	}

/**
* Returns the web image path for web accessable images of a survey
*
* Returns the web image path for web accessable images of a survey
* The image path is under the web accessable data dir in assessment/REFERENCE_ID_OF_SURVEY/images
*
* @access public
*/
	function getImagePathWeb() {
		$webdir = CLIENT_WEB_DIR . "/assessment/" . $this->getId() . "/images/";
		return str_replace(ILIAS_ABSOLUTE_PATH, ILIAS_HTTP_PATH, $webdir);
	}

  function &createQuestionGUI($question_type, $question_id = -1) {
    if ((!$question_type) and ($question_id > 0)) {
			$question_type = $this->getQuestionType($question_id);
    }
    switch ($question_type) {
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
    }
		if ($question_id > 0)
		{
			$question->object->loadFromDb($question_id);
		}
		return $question;
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
  function &_instanciateQuestion($question_id) {
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
      }
      $question->loadFromDb($question_id);
			return $question;
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
		$this->questions = array_values(array_merge($part1, $move_questions, $part2));
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
			array_push($original_ids, $row[0]);
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
	
} // END class.ilObjTest
?>
