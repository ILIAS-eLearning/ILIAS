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
* @author Helmut Schottmüller <hschottm@tzi.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package assessment
*/

require_once "classes/class.ilObject.php";
require_once "class.assMarkSchema.php";
require_once("classes/class.ilMetaData.php");

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
* The maximum processing time in seconds
* 
* The maximum processing time in seconds the user is allowed to do. If set to 0, the user has
* no time limitations.
*
* @var integer
*/
  var $processing_time;

/**
* The starting time of the test
* 
* The starting time in database timestamp format which defines the earliest starting time for the test
*
* @var string
*/
  var $starting_time;
  
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
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjTest($a_id = 0,$a_call_by_reference = true)
	{ 
		$this->type = "tst";
		$this->ilObject($a_id, $a_call_by_reference);
		$this->retrieve_test_types();
		$this->test_id = -1;
    $this->author = $this->ilias->account->fullname;
    $this->introduction = "";
    $this->questions = array();
    $this->sequence_settings = TEST_FIXED_SEQUENCE;
    $this->score_reporting = REPORT_AFTER_QUESTION;
    $this->reporting_date = "";
    $this->nr_of_tries = 0;
    $this->starting_time = "";
    $this->processing_time = 0;
    $this->test_type = TYPE_ASSESSMENT;
    $this->test_formats = 7;
    $this->mark_schema = new ASS_MarkSchema();
		if ($a_id == 0)
		{
			$new_meta =& new ilMetaData();
			$this->assignMetaData($new_meta);
		}
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
		$this->save_to_db();
	}

/**
	* read object data from db into object
	* @param	boolean
	* @access	public
	*/
	function read($a_force_db = false)
	{
		parent::read($a_force_db);
		$this->load_from_db();
		$this->meta_data =& new ilMetaData($this->getType(), $this->getId());
	}
	
/**
	* copy all entries of your object.
	* 
	* @access	public
	* @param	integer	ref_id of parent object
	* @return	integer	new ref id
	*/
	function clone($a_parent_ref)
	{		
		global $rbacadmin;

		// always call parent clone function first!!
		$new_ref_id = parent::clone($a_parent_ref);
		
		// get object instance of cloned object
		//$newObj =& $this->ilias->obj_factory->getInstanceByRefId($new_ref_id);

		// create a local role folder & default roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "n");		

		// always destroy objects in clone method because clone() is recursive and creates instances for each object in subtree!
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
		
		return true;
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
	function retrieve_test_types() {
		$this->test_types = array();
		$query = "SELECT * FROM tst_test_type ORDER BY test_type_id";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
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
  function test_title_exists($title) {
    $query = sprintf("SELECT * FROM object_data WHERE title = %s AND type = %s",
      $this->ilias->db->db->quote($title),
			$this->ilias->db->db->quote("tst")
    );
    $result = $this->ilias->db->query($query);
    if (strcmp(get_class($result), db_result) == 0) {
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
    while ($this->test_title_exists($this->get_title() . " ($counter)")) {
      $counter++;
    }
    $clone->set_title($this->get_title() . " ($counter)");
    $clone->set_owner($this->ilias->account->id);
    $clone->set_author($this->ilias->account->fullname);
    $clone->save_to_db($this->ilias->db->db);
    // Zugeordnete Fragen duplizieren
    $query = sprintf("SELECT * FROM tst_test_question WHERE test_fi = %s",
      $this->ilias->db->db->quote($this->get_id())
    );
    $result = $this->ilias->db->query($query);
    while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
      $query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
        $this->ilias->db->db->quote($clone->get_id()),
        $this->ilias->db->db->quote($data->question_fi),
        $this->ilias->db->db->quote($data->sequence)
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
* Saves a ilObjTest object to a database
* 
* Saves a ilObjTest object to a database (experimental)
*
* @param object $db A pear DB object
* @access public
*/
  function save_to_db()
  {
    global $ilias;
    $db =& $ilias->db->db;
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
    if ($this->test_id == -1) {
      // Neuen Datensatz schreiben
      $id = $db->nextId('tst_tests');
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO tst_tests (test_id, ref_fi, author, test_type_fi, introduction, sequence_settings, score_reporting, nr_of_tries, processing_time, reporting_date, starting_time, complete, created, TIMESTAMP) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $db->quote($id),
				$db->quote($this->getRefId()),
        $db->quote($this->author), 
        $db->quote($this->test_type),
        $db->quote($this->introduction), 
        $db->quote($this->sequence_settings),
        $db->quote($this->score_reporting),
        $db->quote(sprintf("%d", $this->nr_of_tries)),
        $db->quote(sprintf("%d", $this->processing_time)),
        $db->quote($this->reporting_date),
        $db->quote($this->starting_time),
				$db->quote("$complete"),
        $db->quote($created)
      );
      $result = $db->query($query);
      if ($result == DB_OK) {
        $this->test_id = $id;
      }
    } else {
      // Vorhandenen Datensatz aktualisieren
      $query = sprintf("UPDATE tst_tests SET author = %s, test_type_fi = %s, introduction = %s, sequence_settings = %s, score_reporting = %s, nr_of_tries = %s, processing_time = %s, reporting_date = %s, starting_time = %s, complete = %s WHERE test_id = %s",
        $db->quote($this->author), 
        $db->quote($this->test_type), 
        $db->quote($this->introduction), 
        $db->quote($this->sequence_settings), 
        $db->quote($this->score_reporting), 
        $db->quote(sprintf("%d", $this->nr_of_tries)), 
        $db->quote(sprintf("%d", $this->processing_time)), 
        $db->quote($this->reporting_date), 
        $db->quote($this->starting_time), 
				$db->quote("$complete"),
        $db->quote($this->test_id) 
      );
      $result = $db->query($query);
    }
    if ($result == DB_OK) {
      $this->mark_schema->save_to_db($this->test_id);
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
  function load_from_db()
  {
    $db = $this->ilias->db->db;
    
    $query = sprintf("SELECT * FROM tst_tests WHERE ref_fi = %s",
      $db->quote($this->getRefId())
    );
    $result = $db->query($query);
    if (strcmp(get_class($result), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->test_id = $data->test_id;
        $this->author = $data->author;
        $this->test_type = $data->test_type_fi;
        $this->introduction = $data->introduction;
        $this->sequence_settings = $data->sequence_settings;
        $this->score_reporting = $data->score_reporting;
        $this->nr_of_tries = $data->nr_of_tries;
        $this->processing_time = $data->processing_time;
				$this->reporting_date = $data->reporting_date;
        $this->starting_time = $data->starting_time;

				$this->mark_schema->load_from_db($this->test_id);
				$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi = %s ORDER BY sequence",
					$db->quote($this->test_id)
				);
				$result = $db->query($query);
				while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
					$this->questions[$data->sequence] = $data->question_fi;
				}
      }
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
  function set_author($author = "") {
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
  function set_introduction($introduction = "") {
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
  function get_author() {
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
  function get_introduction() {
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
  function get_test_id() {
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
  function set_sequence_settings($sequence_settings = 0) {
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
  function set_test_type($type = TYPE_ASSESSMENT) {
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
  function set_score_reporting($score_reporting = 0) {
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
  function set_reporting_date($reporting_date) {
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
  function get_sequence_settings() {
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
  function get_score_reporting() {
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
  function get_test_type() {
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
  function get_reporting_date() {
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
  function set_test_formats($test_formats = 7) {
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
  function get_test_formats() {
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
  function can_resume() {
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
  function can_review() {
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
  function get_nr_of_tries() {
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
  function get_processing_time() {
    return $this->processing_time;
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
  function get_starting_time() {
    return $this->starting_time;
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
  function set_nr_of_tries($nr_of_tries = 0) {
    $this->nr_of_tries = $nr_of_tries;
  }

/**
* Sets the processing time for the test
* 
* Sets the processing time in seconds for the test
*
* @param integer $processing_time The maximum processing time for the test. 0 for no time limitation.
* @access public
* @see $processing_time
*/
  function set_processing_time($processing_time = 0) {
    $this->processing_time = $processing_time;
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
  function set_starting_time($starting_time = "") {
    $this->starting_time = $starting_time;
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
	function remove_question($question_id) {
		if (!$question_id)
			return;
		$query = sprintf("DELETE FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$this->ilias->db->db->quote($this->get_test_id()),
			$this->ilias->db->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		
		// renumber sequence of remaining questions
		$query = sprintf("SELECT test_question_id FROM tst_test_question WHERE test_fi=%s ORDER BY sequence ASC",
			$this->ilias->db->quote($this->get_test_id())
		);
		$result = $this->ilias->db->query($query);
		$test_question_id_arr = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			array_push($test_question_id_arr, $row->test_question_id);
		}		
		$counter = 1;
		foreach ($test_question_id_arr as $key => $value) {
			$query = sprintf("UPDATE tst_test_question SET sequence = %s WHERE test_question_id = %s",
				$this->ilias->db->quote($counter),
				$this->ilias->db->quote($value)
			);
			$result = $this->ilias->db->query($query);
			$counter++;
		}
		
		// remove test_active entries, because test has changed
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->get_test_id())
		);
		$result = $this->ilias->db->query($query);
		// remove the question from tst_solutions
		$query = sprintf("DELETE FROM tst_solutions WHERE test_fi = %s AND question_fi = %s",
			$this->ilias->db->quote($this->get_test_id()),
			$this->ilias->db->quote($question_id)
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
	function question_move_up($question_id) {
		// Move a question up in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$this->ilias->db->db->quote($this->get_test_id()),
			$this->ilias->db->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
		if ($data->sequence > 1) {
			// OK, it's not the top question, so move it up
			$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
				$this->ilias->db->db->quote($this->get_test_id()),
				$this->ilias->db->db->quote($data->sequence - 1)
			);
			$result = $this->ilias->db->query($query);
			$data_previous = $result->fetchRow(DB_FETCHMODE_OBJECT);
			// change previous dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->db->quote($data->sequence),
				$this->ilias->db->db->quote($data_previous->test_question_id)
			);
			$result = $this->ilias->db->query($query);
			// move actual dataset up
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->db->quote($data->sequence - 1),
				$this->ilias->db->db->quote($data->test_question_id)
			);
			$result = $this->ilias->db->query($query);
		}
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
	function question_move_down($question_id) {
		// Move a question down in sequence
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND question_fi=%s",
			$this->ilias->db->db->quote($this->get_test_id()),
			$this->ilias->db->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		$data = $result->fetchRow(DB_FETCHMODE_OBJECT);
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi=%s AND sequence=%s",
			$this->ilias->db->db->quote($this->get_test_id()),
			$this->ilias->db->db->quote($data->sequence + 1)
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() == 1) {
			// OK, it's not the last question, so move it down
			$data_next = $result->fetchRow(DB_FETCHMODE_OBJECT);
			// change next dataset
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->db->quote($data->sequence),
				$this->ilias->db->db->quote($data_next->test_question_id)
			);
			$result = $this->ilias->db->query($query);
			// move actual dataset down
			$query = sprintf("UPDATE tst_test_question SET sequence=%s WHERE test_question_id=%s",
				$this->ilias->db->db->quote($data->sequence + 1),
				$this->ilias->db->db->quote($data->test_question_id)
			);
			$result = $this->ilias->db->query($query);
		}
	}
	
	function insert_question($question_id) {
    // get maximum sequence index in test
    $query = sprintf("SELECT MAX(sequence) AS seq FROM tst_test_question WHERE test_fi=%s",
      $this->ilias->db->db->quote($this->get_test_id())
    );
    $result = $this->ilias->db->query($query);
    $sequence = 1;
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      $sequence = $data->seq + 1;
    }
    $query = sprintf("INSERT INTO tst_test_question (test_question_id, test_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
      $this->ilias->db->db->quote($this->get_test_id()),
      $this->ilias->db->db->quote($question_id),
      $this->ilias->db->db->quote($sequence)
    );
    $result = $this->ilias->db->query($query);
    if ($result != DB_OK) {
      // Error
    }
		// remove test_active entries, because test has changed
		$query = sprintf("DELETE FROM tst_active WHERE test_fi = %s",
			$this->ilias->db->quote($this->get_test_id())
		);
		$result = $this->ilias->db->query($query);
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
	function get_question_title($sequence) {
		$query = sprintf("SELECT title from qpl_questions WHERE question_id = %s",
			$this->ilias->db->quote($this->questions[$sequence])
		);
    $result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
		return $row->title;
	}
	
	function &get_qpl_titles() {
		$qpl_titles = array();
		$query = sprintf("SELECT object_data.title, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = %s",
			$this->ilias->db->db->quote("qpl")
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			$qpl_titles["$data->ref_id"] = $data->title;
		}
		return $qpl_titles;
	}
	
	function &get_existing_questions() {
		$existing_questions = array();
		$query = sprintf("SELECT * FROM tst_test_question WHERE test_fi = %s",
			$this->ilias->db->db->quote($this->get_test_id())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			array_push($existing_questions, $data->question_fi);
		}
		return $existing_questions;
	}
	
  function get_question_type($question_id) {
    if ($question_id < 1)
      return -1;
    $query = sprintf("SELECT type_tag FROM qpl_questions, qpl_question_type WHERE qpl_questions.question_id = %s AND qpl_questions.question_type_fi = qpl_question_type.question_type_id",
      $this->ilias->db->db->quote($question_id)
    );
    $result = $this->ilias->db->db->query($query);
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      return $data->type_tag;
    } else {
      return "";
    }
  }
	
	function start_working_time ($user_id) 
	{
		$result = "";
		if (!($result = $this->get_active_test_user($user_id))) {
			$this->set_active_test_user();
			$result = $this->get_active_test_user($user_id);
		}
		$q = sprintf("INSERT INTO tst_times (times_id, active_fi, started, finished, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
			$this->ilias->db->quote($result->active_id),
			$this->ilias->db->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$this->ilias->db->quote(strftime("%Y-%m-%d %H:%M:%S"))
		);
		$result = $this->ilias->db->query($q);
		return $this->ilias->db->getLastInsertId();
	}
	
	function update_working_time($times_id)
	{
		$q = sprintf("UPDATE tst_times SET finished = %s WHERE times_id = %s",
			$this->ilias->db->quote(strftime("%Y-%m-%d %H:%M:%S")),
			$this->ilias->db->quote($times_id)
		);
		$result = $this->ilias->db->query($q);
	}
  
	function get_question_count ()
	{
		return count($this->questions);
	}
	
	function get_question_id_from_active_user_sequence($sequence) {
		$active = $this->get_active_test_user();
		$sequence_array = split(",", $active->sequence);
		return $this->questions[$sequence_array[$sequence-1]];
	}
	
	function get_active_test_user($user_id = "") {
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
	
	function set_active_test_user($lastindex = 1, $postpone = "", $addTries = false) {
		global $ilDB;
		global $ilUser;
		
		$db =& $ilDB->db;
		$old_active = $this->get_active_test_user();
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
	
	function &get_test_result($user_id) {
		$add_parameter = "?ref_id=$this->ref_id&cmd=run";
		$total_max_points = 0;
		$total_reached_points = 0;
		$active = $this->get_active_test_user($user_id);
		$sequence_array = split(",", $active->sequence);
		$key = 1;
		$result_array = array();
    foreach ($sequence_array as $idx => $seq) {
			$value = $this->questions[$seq];
      $question_type = $this->get_question_type($value);
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
      }
      $question->load_from_db($value);
      $max_points = $question->get_maximum_points();
      $total_max_points += $max_points;
      $reached_points = $question->get_reached_points($user_id, $this->get_test_id());
      $total_reached_points += $reached_points;
			$row = array(
				"nr" => "$key",
				"title" => "<a href=\"" . $_SERVER['PHP_SELF'] . "$add_parameter&evaluation=" . $question->get_id() . "\">" . $question->get_title() . "</a>",
				"max" => sprintf("%d", $max_points),
				"reached" => sprintf("%d", $reached_points),
				"percent" => sprintf("%2.2f ", ($reached_points / $max_points) * 100) . "%"
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
	* update meta data only
	*/
	function updateMetaData()
	{
		$this->meta_data->update();
		$this->setTitle($this->meta_data->getTitle());
		$this->setDescription($this->meta_data->getDescription());
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
			$this->ilias->db->quote($this->get_test_id())
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
		if ($this->get_test_type() == TYPE_ASSESSMENT)
		{
			if ($this->get_reporting_date())
			{
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->get_reporting_date(), $matches);
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
				"distancemean" => "1",
				"distancequintile" => "1"
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
					 "lastvisit, resultspoints, resultsmarks, distancemean, distancequintile, TIMESTAMP) VALUES " .
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
				$this->ilias->db->quote(sprintf("%01d", $settings_array["distancemean"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["distancequintile"]))
			);
		} else {
			$q = sprintf("UPDATE tst_eval_settings SET ".
					 "qworkedthrough = %s, pworkedthrough = %s, timeofwork = %s, atimeofwork = %s, firstvisit = %s, " .
					 "lastvisit = %s, resultspoints = %s, resultsmarks = %s, distancemean = %s, distancequintile = %s " .
					 "WHERE eval_settings_id = %s",
				$this->ilias->db->quote(sprintf("%01d", $settings_array["qworkedthrough"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["pworkedthrough"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["timeofwork"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["atimeofwork"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["firstvisit"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["lastvisit"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["resultspoints"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["resultsmarks"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["distancemean"])),
				$this->ilias->db->quote(sprintf("%01d", $settings_array["distancequintile"])),
				$this->ilias->db->quote("$update")
			);
		}
		$result = $this->ilias->db->query($q);
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
		$test_result =& $this->get_test_result($user_id);

		$q = sprintf("SELECT tst_times.* FROM tst_active, tst_times WHERE tst_active.test_fi = %s AND tst_active.active_id = tst_times.active_fi AND tst_active.user_fi = %s",
			$this->ilias->db->quote($this->get_test_id()),
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
		if (!$test_result["test"]["total_reached_points"]) {
			$percentage = 0.0;
		} else {
			$percentage = ($test_result["test"]["total_reached_points"] / $test_result["test"]["total_max_points"]) * 100.0;
		}
		$mark_obj = $test_result["test"]["test"]->mark_schema->get_matching_mark($percentage);
		$first_date = getdate($first_visit);
		$last_date = getdate($last_visit);
		$result_array = array(
			"qworkedthrough" => (count($test_result) - 1),
			"qmax" => count($test_result["test"]["test"]->questions),
			"pworkedthrough" => (count($test_result) - 1) / count($test_result["test"]["test"]->questions),
			"timeofwork" => $max_time,
			"atimeofwork" => $max_time / (count($test_result) - 1),
			"firstvisit" => $first_date,
			"lastvisit" => $last_date,
			"resultspoints" => $test_result["test"]["total_reached_points"],
			"maxpoints" => $test_result["test"]["total_max_points"],
			"resultsmarks" => $mark_obj->get_short_name(),
			"distancemean" => "0",
			"distancequintile" => "0"
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
			$this->ilias->db->quote($this->get_test_id())
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
			$this->ilias->db->quote($this->get_test_id())
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
			$this->ilias->db->quote($this->get_test_id())
		);
		$result = $this->ilias->db->query($q);
		$points = array();
		$passed_tests = 0;
		$failed_tests = 0;
		$maximum_points = 0;
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			$res =& $this->get_test_result($row->user_fi);
			if (!$res["test"]["total_reached_points"]) {
				$percentage = 0.0;
			} else {
				$percentage = ($res["test"]["total_max_points"] / $res["test"]["total_reached_points"]) * 100.0;
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
			$this->ilias->db->quote($this->get_test_id())
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
		$query = "SELECT object_data.*, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'qpl'";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess('read', $row->ref_id))
			{
				$result_array[$row->ref_id] = $row->title;
			}
		}
		return $result_array;
	}

} // END class.ilObjTest
?>
