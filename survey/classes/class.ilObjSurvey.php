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
* Class ilObjSurvey
* 
* @author Helmut Schottmüller <hschottm@tzi.de> 
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package survey
*/

require_once "classes/class.ilObject.php";
require_once "classes/class.ilMetaData.php";

define("STATUS_OFFLINE", 0);
define("STATUS_ONLINE", 1);

define("EVALUATION_ACCESS_OFF", 0);
define("EVALUATION_ACCESS_ON", 1);

class ilObjSurvey extends ilObject
{
/**
* Survey database id
*
* A unique positive numerical ID which identifies the survey.
* This is the primary key from a database table.
*
* @var integer
*/
  var $survey_id;

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
* Contains the introduction of the survey
*
* A text representation of the surveys introduction.
*
* @var string
*/
  var $introduction;

/**
* Survey status (online/offline)
*
* Survey status (online/offline)
*
* @var integer
*/
  var $status;

/**
* Indicates the evaluation access for learners
*
* Indicates the evaluation access for learners
*
* @var string
*/
  var $evaluation_access;

/**
* The start date of the survey
*
* The start date of the survey
*
* @var string
*/
  var $start_date;

/**
* Indicates if the start date is enabled
*
* Indicates if the start date is enabled
*
* @var boolean
*/
	var $startdate_enabled;

/**
* The end date of the survey
*
* The end date of the survey
*
* @var string
*/
  var $end_date;

/**
* Indicates if the end date is enabled
*
* Indicates if the end date is enabled
*
* @var boolean
*/
	var $enddate_enabled;

/**
* The questions containd in this survey
*
* The questions containd in this survey
*
* @var array
*/
	var $questions;

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSurvey($a_id = 0,$a_call_by_reference = true)
	{
		global $ilUser;
		$this->type = "svy";
		$this->ilObject($a_id,$a_call_by_reference);
		if ($a_id == 0)
		{
			$new_meta =& new ilMetaData();
			$this->assignMetaData($new_meta);
		}
		$this->survey_id = -1;
		$this->introduction = "";
		$this->author = $ilUser->fullname;
		$this->status = STATUS_OFFLINE;
		$this->evaluation_access = EVALUATION_ACCESS_OFF;
		$this->startdate_enabled = 0;
		$this->enddate_enabled = 0;
		$this->questions = array();
	}

	/**
	* create survey object
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
* Returns true, if a survey is complete for use
*
* Returns true, if a survey is complete for use
*
* @return boolean True, if the survey is complete for use, otherwise false
* @access public
*/
	function isComplete()
	{
		if (($this->getTitle()) and ($this->author) and (count($this->questions)))
		{
			return true;
		} 
			else 
		{
			return false;
		}
	}

/**
* Saves the completion status of the survey
*
* Saves the completion status of the survey
*
* @access public
*/
	function saveCompletionStatus() {
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
    if ($this->survey_id > 0) {
      $query = sprintf("UPDATE survey_survey SET complete = %s WHERE survey_id = %s",
				$this->ilias->db->quote("$complete"),
        $this->ilias->db->quote($this->survey_id) 
      );
      $result = $this->ilias->db->query($query);
		}
	}

/**
* Inserts a question in the survey and saves the relation to the database
*
* Inserts a question in the survey and saves the relation to the database
*
* @access public
*/
	function insertQuestion($question_id) {
    // get maximum sequence index in test
    $query = sprintf("SELECT MAX(sequence) AS seq FROM survey_survey_question WHERE survey_fi = %s",
      $this->ilias->db->quote($this->getSurveyId())
    );
    $result = $this->ilias->db->query($query);
    $sequence = 1;
    if ($result->numRows() == 1) {
      $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
      $sequence = $data->seq + 1;
    }
    $query = sprintf("INSERT INTO survey_survey_question (survey_question_id, survey_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
      $this->ilias->db->quote($this->getSurveyId()),
      $this->ilias->db->quote($question_id),
      $this->ilias->db->quote($sequence)
    );
    $result = $this->ilias->db->query($query);
    if ($result != DB_OK) {
      // Error
    }
		$this->loadQuestionsFromDb();
	}
	
/**
* Saves a survey object to a database
*
* Saves a survey object to a database
*
* @access public
*/
  function saveToDb()
  {
		$complete = 0;
		if ($this->isComplete()) {
			$complete = 1;
		}
		$startdate = $this->getStartDate();
		if (!$startdate or !$this->startdate_enabled)
		{
			$startdate = "NULL";
		}
		else
		{
			$startdate = $this->ilias->db->quote($startdate);
		}
		$enddate = $this->getEndDate();
		if (!$enddate or !$this->enddate_enabled)
		{
			$enddate = "NULL";
		}
		else
		{
			$enddate = $this->ilias->db->quote($enddate);
		}
    if ($this->survey_id == -1) {
      // Write new dataset
      $now = getdate();
      $created = sprintf("%04d%02d%02d%02d%02d%02d", $now['year'], $now['mon'], $now['mday'], $now['hours'], $now['minutes'], $now['seconds']);
      $query = sprintf("INSERT INTO survey_survey (survey_id, ref_fi, author, introduction, status, startdate, enddate, evaluation_access, complete, created, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $this->ilias->db->quote($this->ref_id),
        $this->ilias->db->quote($this->author),
        $this->ilias->db->quote($this->introduction),
        $this->ilias->db->quote($this->status),
        $startdate,
				$enddate,
        $this->ilias->db->quote($this->evaluation_access),
				$this->ilias->db->quote("$complete"),
        $this->ilias->db->quote($created)
      );
      $result = $this->ilias->db->query($query);
      if ($result == DB_OK) {
        $this->survey_id = $this->ilias->db->getLastInsertId();
      }
    } else {
      // update existing dataset
      $query = sprintf("UPDATE survey_survey SET author = %s, introduction = %s, status = %s, startdate = %s, enddate = %s, evaluation_access = %s, complete = %s WHERE survey_id = %s",
        $this->ilias->db->quote($this->author),
        $this->ilias->db->quote($this->introduction),
        $this->ilias->db->quote($this->status),
        $startdate,
				$enddate,
        $this->ilias->db->quote($this->evaluation_access),
				$this->ilias->db->quote("$complete"),
        $this->ilias->db->quote($this->survey_id)
      );
      $result = $this->ilias->db->query($query);
    }
    if ($result == DB_OK) {
			// save questions to db
			// delete existing category relations
/*      $query = sprintf("DELETE FROM survey_variable WHERE question_fi = %s",
        $this->ilias->db->quote($this->id)
      );
      $result = $this->ilias->db->query($query);
      // create new category relations
      foreach ($this->categories as $key => $value) {
				$category_id = $this->saveCategoryToDb($value);
        $query = sprintf("INSERT INTO survey_variable (variable_id, category_fi, question_fi, value1, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
					$this->ilias->db->quote($category_id),
          $this->ilias->db->quote($this->id),
          $this->ilias->db->quote(($key + 1)),
          $this->ilias->db->quote($key)
        );
        $answer_result = $this->ilias->db->query($query);
      }*/
    }
  }

/**
* Returns the survey database id
* 
* Returns the survey database id
*
* @result integer survey database id
* @access public
*/
	function getSurveyId()
	{
		return $this->survey_id;
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
* Loads a survey object from a database
* 
* Loads a survey object from a database
*
* @access public
*/
  function loadFromDb()
  {
    $query = sprintf("SELECT * FROM survey_survey WHERE ref_fi = %s",
      $this->ilias->db->quote($this->getRefId())
    );
    $result = $this->ilias->db->query($query);
    if (strcmp(get_class($result), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->survey_id = $data->survey_id;
        $this->author = $data->author;
        $this->introduction = $data->introduction;
        $this->status = $data->status;
        $this->start_date = $data->startdate;
				if (!$data->startdate)
				{
					$this->startdate_enabled = 0;
				}
				else
				{
					$this->startdate_enabled = 1;
				}
        $this->end_date = $data->enddate;
				if (!$data->enddate)
				{
					$this->enddate_enabled = 0;
				}
				else
				{
					$this->enddate_enabled = 1;
				}
        $this->evaluation_access = $data->evaluation_access;
				$this->loadQuestionsFromDb();
      }
    }
	}

/**
* Loads the survey questions from the database
*
* Loads the survey questions from the database
*
* @access public
* @see $questions
*/
	function loadQuestionsFromDb() {
		$this->questions = array();
		$query = sprintf("SELECT * FROM survey_survey_question WHERE survey_fi = %s ORDER BY sequence",
			$this->ilias->db->quote($this->survey_id)
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			$this->questions[$data->sequence] = $data->question_fi;
		}
	}

/**
* Sets the enabled state of the start date
*
* Sets the enabled state of the start date
*
* @param boolean $enabled True to enable the start date, false to disable the start date
* @access public
* @see $start_date
*/
	function setStartDateEnabled($enabled = false)
	{
		if ($enabled)
		{
			$this->startdate_enabled = 1;
		}
		else
		{
			$this->startdate_enabled = 0;
		}
	}
	
/**
* Gets the enabled state of the start date
*
* Gets the enabled state of the start date
*
* @result boolean True for an enabled end date, false otherwise
* @access public
* @see $start_date
*/
	function getStartDateEnabled()
	{
		return $this->startdate_enabled;
	}

/**
* Sets the enabled state of the end date
*
* Sets the enabled state of the end date
*
* @param boolean $enabled True to enable the end date, false to disable the end date
* @access public
* @see $end_date
*/
	function setEndDateEnabled($enabled = false)
	{
		if ($enabled)
		{
			$this->enddate_enabled = 1;
		}
		else
		{
			$this->enddate_enabled = 0;
		}
	}
	
/**
* Gets the enabled state of the end date
*
* Gets the enabled state of the end date
*
* @result boolean True for an enabled end date, false otherwise
* @access public
* @see $end_date
*/
	function getEndDateEnabled()
	{
		return $this->enddate_enabled;
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
* Sets the authors name
*
* Sets the authors name of the SurveyQuestion object
*
* @param string $author A string containing the name of the questions author
* @access public
* @see $author
*/
  function setAuthor($author = "") {
    if (!$author) {
      $author = $this->ilias->account->fullname;
    }
    $this->author = $author;
  }

/**
* Sets the introduction text
*
* Sets the introduction text
*
* @param string $introduction A string containing the introduction
* @access public
* @see $introduction
*/
  function setIntroduction($introduction = "") {
    $this->introduction = $introduction;
  }

/**
* Gets the authors name
*
* Gets the authors name of the SurveyQuestion object
*
* @return string The string containing the name of the questions author
* @access public
* @see $author
*/
  function getAuthor() {
    return $this->author;
  }

/**
* Gets the survey status
*
* Gets the survey status
*
* @return integer Survey status
* @access public
* @see $status
*/
  function getStatus() {
    return $this->status;
  }

/**
* Sets the survey status
*
* Sets the survey status
*
* @param integer $status Survey status
* @access public
* @see $status
*/
  function setStatus($status = STATUS_OFFLINE) {
    $this->status = $status;
  }

/**
* Gets the start date of the survey
*
* Gets the start date of the survey
*
* @return string Survey start date (YYYY-MM-DD)
* @access public
* @see $start_date
*/
  function getStartDate() {
    return $this->start_date;
  }

/**
* Sets the start date of the survey
*
* Sets the start date of the survey
*
* @param string $start_data Survey start date (YYYY-MM-DD)
* @access public
* @see $start_date
*/
  function setStartDate($start_date = "") {
    $this->start_date = $start_date;
  }

/**
* Gets the start month of the survey
*
* Gets the start month of the survey
*
* @return string Survey start month
* @access public
* @see $start_date
*/
  function getStartMonth() {
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $this->start_date, $matches))
		{
			return $matches[2];
		}
		else
		{
			return "";
		}
  }

/**
* Gets the start day of the survey
*
* Gets the start day of the survey
*
* @return string Survey start day
* @access public
* @see $start_date
*/
  function getStartDay() {
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $this->start_date, $matches))
		{
			return $matches[3];
		}
		else
		{
			return "";
		}
  }

/**
* Gets the start year of the survey
*
* Gets the start year of the survey
*
* @return string Survey start year
* @access public
* @see $start_date
*/
  function getStartYear() {
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $this->start_date, $matches))
		{
			return $matches[1];
		}
		else
		{
			return "";
		}
  }

/**
* Gets the end date of the survey
*
* Gets the end date of the survey
*
* @return string Survey end date (YYYY-MM-DD)
* @access public
* @see $end_date
*/
  function getEndDate() {
    return $this->end_date;
  }

/**
* Sets the end date of the survey
*
* Sets the end date of the survey
*
* @param string $end_date Survey end date (YYYY-MM-DD)
* @access public
* @see $end_date
*/
  function setEndDate($end_date = "") {
    $this->end_date = $end_date;
  }

/**
* Gets the end month of the survey
*
* Gets the end month of the survey
*
* @return string Survey end month
* @access public
* @see $end_date
*/
  function getEndMonth() {
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $this->end_date, $matches))
		{
			return $matches[2];
		}
		else
		{
			return "";
		}
  }

/**
* Gets the end day of the survey
*
* Gets the end day of the survey
*
* @return string Survey end day
* @access public
* @see $end_date
*/
  function getEndDay() {
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $this->end_date, $matches))
		{
			return $matches[3];
		}
		else
		{
			return "";
		}
  }

/**
* Gets the end year of the survey
*
* Gets the end year of the survey
*
* @return string Survey end year
* @access public
* @see $end_date
*/
  function getEndYear() {
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $this->end_date, $matches))
		{
			return $matches[1];
		}
		else
		{
			return "";
		}
  }

/**
* Gets the learners evaluation access
*
* Gets the learners evaluation access
*
* @return integer The evaluation access
* @access public
* @see $evaluation_access
*/
  function getEvaluationAccess() {
    return $this->evaluation_access;
  }

/**
* Sets the learners evaluation access
*
* Sets the learners evaluation access
*
* @param integer $evaluation_access The evaluation access
* @access public
* @see $evaluation_access
*/
  function setEvaluationAccess($evaluation_access = EVALUATION_ACCESS_OFF) {
    $this->evaluation_access = $evaluation_access;
  }

/**
* Gets the introduction text
*
* Gets the introduction text
*
* @return string The introduction of the survey object
* @access public
* @see $introduction
*/
  function getIntroduction() {
    return $this->introduction;
  }

/**
* Gets the question id's of the questions which are already in the survey
*
* Gets the question id's of the questions which are already in the survey
*
* @return array The questions of the survey
* @access public
*/
	function &getExistingQuestions() {
		$existing_questions = array();
		$query = sprintf("SELECT * FROM survey_survey_question WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			array_push($existing_questions, $data->question_fi);
		}
		return $existing_questions;
	}

/**
* Get the titles of all available survey question pools
*
* Get the titles of all available survey question pools
*
* @return array An array of survey question pool titles
* @access public
*/
	function &getQuestionpoolTitles() {
		global $tree;
		$qpl_titles = array();
		$query = sprintf("SELECT object_data.title, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = %s",
			$this->ilias->db->quote("spl")
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			$qpl_titles["$data->ref_id"] = $data->title;
		}
		return $qpl_titles;
	}
	
} // END class.ilObjSurvey
?>
