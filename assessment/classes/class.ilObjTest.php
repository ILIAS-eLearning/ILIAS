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

require_once "classes/class.ilObjectGUI.php";
require_once "class.assMarkSchema.php";

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
		$this->ilObject($a_id,$a_call_by_reference);
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
    
    if ($this->test_id == -1) {
      // Neuen Datensatz schreiben
      $id = $db->nextId('tst_tests');
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO tst_tests (test_id, ref_fi, author, test_type_fi, introduction, sequence_settings, score_reporting, nr_of_tries, processing_time, starting_time, created, TIMESTAMP) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $db->quote($id),
				$db->quote($this->getRefId()),
        $db->quote($this->author), 
        $db->quote($this->test_type),
        $db->quote($this->introduction), 
        $db->quote($this->sequence_settings),
        $db->quote($this->score_reporting),
        $db->quote(sprintf("%d", $this->nr_of_tries)),
        $db->quote(sprintf("%d", $this->processing_time)),
        $db->quote($this->starting_time),
        $db->quote($created)
      );
      $result = $db->query($query);
      if ($result == DB_OK) {
        $this->test_id = $id;
      }
    } else {
      // Vorhandenen Datensatz aktualisieren
      $query = sprintf("UPDATE tst_tests SET author = %s, test_type_fi = %s, introduction = %s, sequence_settings = %s, score_reporting = %s, nr_of_tries = %s, processing_time = %s, starting_time = %s WHERE test_id = %s",
        $db->quote($this->author), 
        $db->quote($this->test_type), 
        $db->quote($this->introduction), 
        $db->quote($this->sequence_settings), 
        $db->quote($this->score_reporting), 
        $db->quote(sprintf("%d", $this->nr_of_tries)), 
        $db->quote(sprintf("%d", $this->processing_time)), 
        $db->quote($this->starting_time), 
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
  function set_test_type($type = ASS_ASSESSMENT) {
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
* @param integer $year The year of the reporting date
* @access public
* @see $reporting_date
*/
  function set_reporting_date($year, $month, $day, $hour, $minute, $second) {
    if (($year | $month | $day | $hour | $minute | $second) == 0) {
      $this->reporting_date = "";
    } else {
      $this->reporting_date = sprintf("%04d%02d%02d%02d%02d%02d", $year, $month, $day, $hour, $minute, $second);
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
    $query = sprintf("SELECT type_tag FROM dum_assessment_questions, dum_question_type WHERE dum_assessment_questions.question_id = %s AND dum_assessment_questions.question_type_id = dum_question_type.question_type_id",
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
  
  function get_resume_index() {
    if ($this->test_type == TYPE_SELF_ASSESSMENT) {
      $query = sprintf("SELECT * FROM dum_assessment_solutions WHERE user_fi = %s AND test_fi = %s",
        $this->ilias->db->db->quote($this->ilias->account->id),
        $this->ilias->db->db->quote($this->get_id())
      );
      $result = $this->ilias->db->query($query);
      $index = -1;
      while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
        $found = array_search($data->question_fi, $this->questions);
        if ($found > $index) $index = $found;
      }
      if (($index > 0) and ($index < count($this->questions))) {
        $index++;
      }
      return $index;
    } else {
      return -1;
    }
  }

  function check_tries($question_id) {
    if ($this->get_nr_of_tries() == 0) return TRUE;
    $query = sprintf("SELECT * FROM dum_assessment_solution_order WHERE user_fi = %s AND test_fi = %s AND question_fi = %s",
      $this->ilias->db->db->quote($this->ilias->account->id),
      $this->ilias->db->db->quote($_GET["test"]),
      $this->ilias->db->db->quote($question_id)
    );
    $result = $this->ilias->db->query($query);
    $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
    if ($data->tries < $this->get_nr_of_tries()) {
      return TRUE;
    } else {
      return FALSE;
    }
  }
  
  function get_first_question_id() {
    foreach ($this->questions as $key => $value) {
      if ($this->check_tries($value)) {
        return $value;
      }
    }
    return 0;
  }
  
  function get_next_question_id($question_id) {
    $start_key = array_search($question_id, $this->questions);
    for ($i = $start_key + 1; $i < count($this->questions) + 1; $i++) {
      if ($this->check_tries($this->questions[$i])) {
        return $this->questions[$i];
      }
    }
    return 0;
  }

  function get_previous_question_id($question_id) {
    $start_key = array_search($question_id, $this->questions);
    for ($i = $start_key - 1; $i > 0; $i--) {
      if ($this->check_tries($this->questions[$i])) {
        return $this->questions[$i];
      }
    }
    return 0;
  }
} // END class.ilObjTest
?>
