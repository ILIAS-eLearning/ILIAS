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
require_once "class.SurveyNominalQuestionGUI.php";
require_once "class.SurveyOrdinalQuestionGUI.php";
require_once "class.SurveyTextQuestionGUI.php";
require_once "class.SurveyMetricQuestionGUI.php";

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
    $query = sprintf("SELECT survey_question_id FROM survey_survey_question WHERE survey_fi = %s",
      $this->ilias->db->quote($this->getSurveyId())
    );
    $result = $this->ilias->db->query($query);
    $sequence = $result->numRows();
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
			$this->saveQuestionsToDb();
    }
  }

/**
* Saves the survey questions to the database
*
* Saves the survey questions to the database
*
* @access public
* @see $questions
*/
	function saveQuestionsToDb() {
		// delete existing category relations
    $query = sprintf("DELETE FROM survey_survey_question WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		// create new category relations
		foreach ($this->questions as $key => $value) {
			$query = sprintf("INSERT INTO survey_survey_question (survey_question_id, survey_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getSurveyId()),
				$this->ilias->db->quote($value),
				$this->ilias->db->quote($key)
			);
			$result = $this->ilias->db->query($query);
		}
	}

/**
* Returns a question gui object to a given questiontype and question id
* 
* Returns a question gui object to a given questiontype and question id
*
* @result object Resulting question gui object
* @access public
*/
	function getQuestionGUI($questiontype, $question_id)
	{
		switch ($questiontype)
		{
			case "qt_nominal":
				$question = new SurveyNominalQuestionGUI();
				break;
			case "qt_ordinal":
				$question = new SurveyOrdinalQuestionGUI();
				break;
			case "qt_metric":
				$question = new SurveyMetricQuestionGUI();
				break;
			case "qt_text":
				$question = new SurveyTextQuestionGUI();
				break;
		}
		$question->object->loadFromDb($question_id);
		return $question;
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
* Remove questions from the survey
*
* Remove questions from the survey
*
* @param array $remove_questions An array with the question id's of the questions to remove
* @param array $remove_questionblocks An array with the questionblock id's of the questions blocks to remove
* @access public
*/
	function removeQuestions($remove_questions, $remove_questionblocks)
	{
		$questions =& $this->getSurveyQuestions();
		foreach ($questions as $question_id => $data)
		{
			if (in_array($question_id, $remove_questions) or in_array($data["questionblock_id"], $remove_questionblocks))
			{
				unset($this->questions[array_search($question_id, $this->questions)]);
				$this->deleteConstraints($question_id);
			}
		}
		foreach ($remove_questionblocks as $questionblock_id)
		{
			$query = sprintf("DELETE FROM survey_questionblock WHERE questionblock_id = %s",
				$this->ilias->db->quote($questionblock_id)
			);
			$result = $this->ilias->db->query($query);
			$query = sprintf("DELETE FROM survey_questionblock_question WHERE questionblock_fi = %s",
				$this->ilias->db->quote($questionblock_id)
			);
			$result = $this->ilias->db->query($query);
		}
		$this->questions = array_values($this->questions);
		$this->saveQuestionsToDb();
	}
		
/**
* Unfolds question blocks of a question pool
* 
* Unfolds question blocks of a question pool
*
* @param array $questionblocks An array of question block id's
* @access public
*/
	function unfoldQuestionblocks($questionblocks)
	{
		foreach ($questionblocks as $index)
		{
			$query = sprintf("DELETE FROM survey_questionblock WHERE questionblock_id = %s",
				$this->ilias->db->quote($index)
			);
			$result = $this->ilias->db->query($query);
			$query = sprintf("DELETE FROM survey_questionblock_question WHERE questionblock_fi = %s",
				$this->ilias->db->quote($index)
			);
			$result = $this->ilias->db->query($query);
		}
	}
	
/**
* Returns the titles of all question blocks of the question pool
* 
* Returns the titles of all question blocks of the question pool
*
* @result array The titles of the the question blocks
* @access public
*/
	function &getQuestionblockTitles()
	{
		$titles = array();
		$query = sprintf("SELECT survey_questionblock.* FROM survey_questionblock, survey_question, survey_questionblock_question WHERE survey_questionblock_question.question_fi = survey_question.question_id AND survey_question.ref_fi = %s",
			$this->ilias->db->quote($this->getRefId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$titles[$row->questionblock_id] = $row->title;
		}
		return $titles;
	}
	
/**
* Creates a question block for the question pool
* 
* Creates a question block for the question pool
*
* @param string $title The title of the question block
* @param array $questions An array with the database id's of the question block questions
* @access public
*/
	function createQuestionblock($title, $questions)
	{
		// if the selected questions are not in a continous selection, move all questions of the
		// questionblock at the position of the first selected question
		$this->moveQuestions($questions, $questions[0], 0);
		
		// now save the question block
		global $ilUser;
		$query = sprintf("INSERT INTO survey_questionblock (questionblock_id, title, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, NULL)",
			$this->ilias->db->quote($title),
			$this->ilias->db->quote($ilUser->id)
		);
		$result = $this->ilias->db->query($query);
		if ($result == DB_OK) {
			$questionblock_id = $this->ilias->db->getLastInsertId();
			foreach ($questions as $index)
			{
				$query = sprintf("INSERT INTO survey_questionblock_question (questionblock_question_id, questionblock_fi, question_fi) VALUES (NULL, %s, %s)",
					$this->ilias->db->quote($questionblock_id),
					$this->ilias->db->quote($index)
				);
				$result = $this->ilias->db->query($query);
				$this->deleteConstraints($index);
			}
		}
	}
	
/**
* Deletes the constraints for a question
* 
* Deletes the constraints for a question
*
* @param integer $question_id The database id of the question
* @access public
*/
	function deleteConstraints($question_id)
	{
		$query = sprintf("SELECT * FROM survey_question_constraint WHERE question_fi = %s AND survey_fi = %s",
			$this->ilias->db->quote($question_id),
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$query = sprintf("DELETE FROM survey_constraint WHERE constraint_id = %s",
				$this->ilias->db->quote($row->constraint_fi)
			);
			$delresult = $this->ilias->db->query($query);
		}
		$query = sprintf("DELETE FROM survey_question_constraint WHERE question_fi = %s AND survey_fi = %s",
			$this->ilias->db->quote($question_id),
			$this->ilias->db->quote($this->getId())
		);
		$delresult = $this->ilias->db->query($query);
	}

/**
* Deletes a constraint of a question
* 
* Deletes a constraint of a question
*
* @param integer $constraint_id The database id of the constraint
* @param integer $question_id The database id of the question
* @access public
*/
	function deleteConstraint($constraint_id, $question_id)
	{
		$query = sprintf("DELETE FROM survey_constraint WHERE constraint_id = %s",
			$this->ilias->db->quote($constraint_id)
		);
		$delresult = $this->ilias->db->query($query);
		$query = sprintf("DELETE FROM survey_question_constraint WHERE constraint_fi = %s AND question_fi = %s AND survey_fi = %s",
			$this->ilias->db->quote($constraint_id),
			$this->ilias->db->quote($question_id),
			$this->ilias->db->quote($this->getId())
		);
		$delresult = $this->ilias->db->query($query);
	}

/**
* Returns the survey questions and questionblocks in an array
* 
* Returns the survey questions and questionblocks in an array
*
* @access public
*/
	function &getSurveyQuestions()
	{
		// get questionblocks
		$all_questions = array();
		$query = sprintf("SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype, survey_survey_question WHERE survey_survey_question.survey_fi = %s AND survey_survey_question.question_fi = survey_question.question_id AND survey_question.questiontype_fi = survey_questiontype.questiontype_id ORDER BY survey_survey_question.sequence",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$all_questions[$row["question_id"]] = $row;
		}
		// get all questionblocks
		$questionblocks = array();
		$in = join(array_keys($all_questions), ",");
		if ($in)
		{
			$query = "SELECT survey_questionblock.*, survey_questionblock_question.question_fi FROM survey_questionblock, survey_questionblock_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_questionblock_question.question_fi IN ($in)";
			$result = $this->ilias->db->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$questionblocks[$row->question_fi] = $row;
			}			
		}
		
		foreach ($all_questions as $question_id => $row)
		{
			if (isset($questionblocks[$question_id]))
			{
				$all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]->title;
				$all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]->questionblock_id;
			}
			else
			{
				$all_questions[$question_id]["questionblock_title"] = "";
				$all_questions[$question_id]["questionblock_id"] = "";
			}
		}
		return $all_questions;
	}
	
/**
* Returns the survey pages in an array (a page contains one or more questions)
* 
* Returns the survey pages in an array (a page contains one or more questions)
*
* @access public
*/
	function &getSurveyPages()
	{
		// get questionblocks
		$all_questions = array();
		$query = sprintf("SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype, survey_survey_question WHERE survey_survey_question.survey_fi = %s AND survey_survey_question.question_fi = survey_question.question_id AND survey_question.questiontype_fi = survey_questiontype.questiontype_id ORDER BY survey_survey_question.sequence",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$all_questions[$row["question_id"]] = $row;
		}
		// get all questionblocks
		$questionblocks = array();
		$in = join(array_keys($all_questions), ",");
		if ($in)
		{
			$query = "SELECT survey_questionblock.*, survey_questionblock_question.question_fi FROM survey_questionblock, survey_questionblock_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_questionblock_question.question_fi IN ($in)";
			$result = $this->ilias->db->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$questionblocks[$row->question_fi] = $row;
			}			
		}
		
		$all_pages = array();
		$pageindex = -1;
		$currentblock = "";
		foreach ($all_questions as $question_id => $row)
		{
			if (isset($questionblocks[$question_id]))
			{
				if (!$currentblock or ($currentblock != $questionblocks[$question_id]->questionblock_id))
				{
					$pageindex++;
				}
				$all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]->title;
				$all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]->questionblock_id;
				$currentblock = $questionblocks[$question_id]->questionblock_id;
			}
			else
			{
				$pageindex++;
				$all_questions[$question_id]["questionblock_title"] = "";
				$all_questions[$question_id]["questionblock_id"] = "";
				$currentblock = "";
			}
			if (!isset($all_pages[$pageindex]))
			{
				$all_pages[$pageindex] = array();
			}
			array_push($all_pages[$pageindex], $all_questions[$question_id]);
		}
		return $all_pages;
	}
	
/**
* Returns the next "page" of a running test
* 
* Returns the next "page" of a running test
*
* @param integer $active_page_question_id The database id of one of the questions on that page
* @param integer $direction The direction of the next page (-1 = previous page, 1 = next page)
* @return mixed An array containing the question id's of the questions on the next page if there is a next page, 0 if the next page is before the start page, 1 if the next page is after the last page
* @access public
*/
	function getNextPage($active_page_question_id, $direction)
	{
		$foundpage = -1;
		$pages =& $this->getSurveyPages();
		if (strcmp($active_page_question_id, "") == 0)
		{
			return $pages[0];
		}
		
		foreach ($pages as $key => $question_array)
		{
			foreach ($question_array as $question)
			{
				if ($active_page_question_id == $question["question_id"])
				{
					$foundpage = $key;
				}
			}
		}
		if ($foundpage == -1)
		{
			// error: page not found
		}
		else
		{
			$foundpage += $direction;
			if ($foundpage < 0)
			{
				return 0;
			}
			if ($foundpage >= count($pages))
			{
				return 1;
			}
			return $pages[$foundpage];
		}
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
		$query = "SELECT object_data.*, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'spl'";
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
	
/**
* Returns the constraints to a given question or questionblock
* 
* Returns the constraints to a given question or questionblock
*
* @access public
*/
	function getConstraints($question_id)
 	{
		$result_array = array();
		$query = sprintf("SELECT survey_constraint.*, survey_relation.* FROM survey_question_constraint, survey_constraint, survey_relation WHERE survey_constraint.relation_fi = survey_relation.relation_id AND survey_question_constraint.constraint_fi = survey_constraint.constraint_id AND survey_question_constraint.question_fi = %s",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			array_push($result_array, array("id" => $row->constraint_id, "question" => $row->question_fi, "short" => $row->short, "long" => $row->long, "value" => $row->value));
		}
		return $result_array;
	}

/**
* Returns all variables of a question
* 
* Returns all variables of a question
*
* @access public
*/
	function &getVariables($question_id)
	{
		$result_array = array();
		$query = sprintf("SELECT survey_variable.*, survey_category.title FROM survey_variable, survey_category WHERE survey_variable.category_fi = survey_category.category_id AND survey_variable.question_fi = %s ORDER BY survey_variable.sequence",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_array[$row->sequence] = $row->title;
		}
		return $result_array;
	}
	
/**
* Adds a constraint to a question
* 
* Adds a constraint to a question
*
* @param integer $to_question_id The question id of the question where to add the constraint
* @param integer $if_question_id The question id of the question which defines a precondition
* @param integer $relation The database id of the relation
* @param mixed $value The value compared with the relation
* @access public
*/
	function addConstraint($to_question_id, $if_question_id, $relation, $value)
	{
		$query = sprintf("INSERT INTO survey_constraint (constraint_id, question_fi, relation_fi, value) VALUES (NULL, %s, %s, %s)",
			$this->ilias->db->quote($if_question_id),
			$this->ilias->db->quote($relation),
			$this->ilias->db->quote($value)
		);
		$result = $this->ilias->db->query($query);
		if ($result == DB_OK) {
			$constraint_id = $this->ilias->db->getLastInsertId();
			$query = sprintf("INSERT INTO survey_question_constraint (question_constraint_id, survey_fi, question_fi, constraint_fi) VALUES (NULL, %s, %s, %s)",
				$this->ilias->db->quote($this->getId()),
				$this->ilias->db->quote($to_question_id),
				$this->ilias->db->quote($constraint_id)
			);
			$result = $this->ilias->db->query($query);
		}
	}
	
/**
* Returns all available relations
* 
* Returns all available relations
*
* @access public
*/
	function getAllRelations()
 	{
		$result_array = array();
		$query = "SELECT * FROM survey_relation";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_array[$row->relation_id] = array("short" => $row->short, "long" => $row->long);
		}
		return $result_array;
	}

} // END class.ilObjSurvey
?>
