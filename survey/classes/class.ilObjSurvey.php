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
* @author		Helmut Schottmüller <hschottm@tzi.de>
* @version $Id$
*
* @extends ilObject
* @package ilias-core
* @package survey
*/

require_once "./classes/class.ilObject.php";
require_once "./classes/class.ilMetaData.php";
require_once "./survey/classes/class.SurveyQuestion.php";
require_once "./survey/classes/class.SurveyNominalQuestionGUI.php";
require_once "./survey/classes/class.SurveyOrdinalQuestionGUI.php";
require_once "./survey/classes/class.SurveyTextQuestionGUI.php";
require_once "./survey/classes/class.SurveyMetricQuestionGUI.php";

define("STATUS_OFFLINE", 0);
define("STATUS_ONLINE", 1);

define("EVALUATION_ACCESS_OFF", 0);
define("EVALUATION_ACCESS_ON", 1);

define("INVITATION_OFF", 0);
define("INVITATION_ON", 1);

define("MODE_UNLIMITED", 0);
define("MODE_PREDEFINED_USERS", 1);

define("SURVEY_START_ALLOWED", 0);
define("SURVEY_START_START_DATE_NOT_REACHED", 1);
define("SURVEY_START_END_DATE_REACHED", 2);
define("SURVEY_START_OFFLINE", 3);

define("ANONYMIZE_OFF", 0);
define("ANONYMIZE_ON", 1);

define("QUESTIONTITLES_HIDDEN", 0);
define("QUESTIONTITLES_VISIBLE", 1);

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
* Defines if the surveyw will be places on users personal desktops
*
* Defines if the surveyw will be places on users personal desktops
*
* @var integer
*/
	var $invitation;

/**
* Defines the type of user invitation
*
* Defines the type of user invitation
*
* @var integer
*/
	var $invitation_mode;
	
/**
* Indicates the anonymization of the survey
*
* Indicates the anonymization of the survey
* @var integer
*/
	var $anonymize;

/**
* Indicates if the question titles are shown during a query
*
* Indicates if the question titles are shown during a query
* @var integer
*/
	var $display_question_titles;

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
		$this->invitation = INVITATION_OFF;
		$this->invitation_mode = MODE_PREDEFINED_USERS;
		$this->anonymize = ANONYMIZE_OFF;
		$this->display_question_titles = QUESTIONTITLES_VISIBLE;
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
		$remove = parent::delete();
		// always call parent delete function first!!
		if (!$remove)
		{
			return false;
		}
		
		// Delete all survey questions, constraints and materials
		foreach ($this->questions as $question_id)
		{
			$this->removeQuestion($question_id);
		}
		$this->deleteSurveyRecord();
		
		return true;
	}
	
	/**
	* Deletes the survey from the database
	* 
	* Deletes the survey from the database
	* 
	* @access	public
	*/
	function deleteSurveyRecord()
	{
		$query = sprintf("DELETE FROM survey_survey WHERE survey_id = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("SELECT questionblock_fi FROM survey_questionblock_question WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		$questionblocks = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questionblocks, $row["questionblock_fi"]);
		}
		if (count($questionblocks))
		{
			$query = "DELETE FROM survey_questionblock WHERE questionblock_id IN (" . join($questionblocks, ",") . ")";
			$result = $this->ilias->db->query($query);
		}
		$query = sprintf("DELETE FROM survey_questionblock_question WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		
		$this->deleteAllUserData();

		// delete export files
		$svy_data_dir = ilUtil::getDataDir()."/svy_data";
		$directory = $svy_data_dir."/svy_".$this->getId();
		if (is_dir($directory))
		{
			$directory = escapeshellarg($directory);
			exec("rm -rf $directory");
		}
	}
	
	/**
	* Deletes all user data of a survey
	* 
	* Deletes all user data of a survey
	* 
	* @access	public
	*/
	function deleteAllUserData()
	{
		$query = sprintf("SELECT user_fi FROM survey_invited_user WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->disinviteUser($row["user_fi"]);
		}

		$query = sprintf("SELECT group_fi FROM survey_invited_group WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->disinviteGroup($row["group_fi"]);
		}

		$query = sprintf("DELETE FROM survey_finished WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("DELETE FROM survey_answer WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);

		$query = sprintf("DELETE FROM survey_anonymous WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
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
* Returns true, if a survey is complete for use
*
* Returns true, if a survey is complete for use
*
* @return boolean True, if the survey is complete for use, otherwise false
* @access public
*/
	function _isComplete($obj_id)
	{
		$survey = new ilObjSurvey($obj_id, false);
		$survey->loadFromDb();
		if (($survey->getTitle()) and ($survey->author) and (count($survey->questions)))
		{
			return true;
		} 
			else 
		{
			return false;
		}
	}

/**
* Returns an array with data needed in the repository, personal desktop or courses
*
* Returns an array with data needed in the repository, personal desktop or courses
*
* @return array resulting array
* @access public
*/
	function &_getGlobalSurveyData($obj_id)
	{
		$survey = new ilObjSurvey($obj_id, false);
		$survey->loadFromDb();
		$result = array();
		if (($survey->getTitle()) and ($survey->author) and (count($survey->questions)))
		{
			$result["complete"] = true;
		} 
			else 
		{
			$result["complete"] = false;
		}
		$result["evaluation_access"] = $survey->getEvaluationAccess();
		return $result;
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
* Takes a question and creates a copy of the question for use in the survey
*
* Takes a question and creates a copy of the question for use in the survey
*
* @param integer $question_id The database id of the question
* @result integer The database id of the copied question
* @access public
*/
	function duplicateQuestionForSurvey($question_id)
	{
		global $ilUser;
		
		$questiontype = $this->getQuestionType($question_id);
		$question_gui = $this->getQuestionGUI($questiontype, $question_id);
		$duplicate_id = $question_gui->object->duplicate(true);
		return $duplicate_id;
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
		$duplicate_id = $this->duplicateQuestionForSurvey($question_id);
    $query = sprintf("INSERT INTO survey_survey_question (survey_question_id, survey_fi, question_fi, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
      $this->ilias->db->quote($this->getSurveyId()),
      $this->ilias->db->quote($duplicate_id),
      $this->ilias->db->quote($sequence)
    );
    $result = $this->ilias->db->query($query);
    if ($result != DB_OK) {
      // Error
    }
		$this->loadQuestionsFromDb();
	}


	
/**
* Inserts a questionblock in the survey and saves the relation to the database
*
* Inserts a questionblock in the survey and saves the relation to the database
*
* @access public
*/
	function insertQuestionblock($questionblock_id) {
		$query = sprintf("SELECT survey_questionblock.*, survey_survey.obj_fi, survey_question.title AS questiontitle, survey_survey_question.sequence, object_data.title as surveytitle, survey_question.question_id FROM object_reference, object_data, survey_questionblock, survey_questionblock_question, survey_survey, survey_question, survey_survey_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_survey.survey_id = survey_questionblock_question.survey_fi AND survey_questionblock_question.question_fi = survey_question.question_id AND survey_survey.obj_fi = object_reference.obj_id AND object_reference.obj_id = object_data.obj_id AND survey_survey_question.survey_fi = survey_survey.survey_id AND survey_survey_question.question_fi = survey_question.question_id AND survey_questionblock.questionblock_id =%s ORDER BY survey_survey_question.sequence",
			$this->ilias->db->quote($questionblock_id)
		);
		$result = $this->ilias->db->query($query);
		$questions = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questions, $row["question_id"]);
			$title = $row["title"];
		}
		$this->createQuestionblock($title, $questions);
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
      $query = sprintf("INSERT INTO survey_survey (survey_id, obj_fi, author, introduction, status, startdate, enddate, evaluation_access, invitation, invitation_mode, complete, created, anonymize, show_question_titles, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, NULL)",
        $this->ilias->db->quote($this->getId()),
        $this->ilias->db->quote($this->author . ""),
        $this->ilias->db->quote($this->introduction . ""),
        $this->ilias->db->quote($this->status . ""),
        $startdate,
				$enddate,
        $this->ilias->db->quote($this->evaluation_access . ""),
				$this->ilias->db->quote($this->invitation . ""),
				$this->ilias->db->quote($this->invitation_mode . ""),
				$this->ilias->db->quote($complete . ""),
				$this->ilias->db->quote($this->getAnonymize() . ""),
				$this->ilias->db->quote($this->getShowQuestionTitles() . ""),
        $this->ilias->db->quote($created)
      );
      $result = $this->ilias->db->query($query);
      if ($result == DB_OK) {
        $this->survey_id = $this->ilias->db->getLastInsertId();
      }
    } else {
      // update existing dataset
      $query = sprintf("UPDATE survey_survey SET author = %s, introduction = %s, status = %s, startdate = %s, enddate = %s, evaluation_access = %s, invitation = %s, invitation_mode = %s, complete = %s, anonymize = %s, show_question_titles = %s WHERE survey_id = %s",
        $this->ilias->db->quote($this->author . ""),
        $this->ilias->db->quote($this->introduction . ""),
        $this->ilias->db->quote($this->status . ""),
        $startdate,
				$enddate,
        $this->ilias->db->quote($this->evaluation_access . ""),
				$this->ilias->db->quote($this->invitation . ""),
				$this->ilias->db->quote($this->invitation_mode . ""),
				$this->ilias->db->quote($complete . ""),
				$this->ilias->db->quote($this->getAnonymize() . ""),
				$this->ilias->db->quote($this->getShowQuestionTitles() . ""),
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
		// save old questions state
		$old_questions = array();
		$query = sprintf("SELECT * FROM survey_survey_question WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$old_questions[$row["question_fi"]] = $row;
			}
		}
		
		// delete existing question relations
    $query = sprintf("DELETE FROM survey_survey_question WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		// create new question relations
		foreach ($this->questions as $key => $value) {
			$query = sprintf("INSERT INTO survey_survey_question (survey_question_id, survey_fi, question_fi, heading, sequence, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($value . ""),
				$this->ilias->db->quote($old_questions[$value]["heading"]),
				$this->ilias->db->quote($key . "")
			);
			$result = $this->ilias->db->query($query);
		}
	}

/**
* Checks for an anomyous survey id in the database an returns the id
* 
* Checks for an anomyous survey id in the database an returns the id
*
* @param string $id A 32 digit MD5 key
* @result object Anonymous survey id if found, empty string otherwise
* @access public
*/
	function getAnonymousId($id)
	{
		$query = sprintf("SELECT anonymous_id FROM survey_answer WHERE anonymous_id = %s",
			$this->ilias->db->quote($id)
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["anonymous_id"];
		}
		else
		{
			return "";
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
    $query = sprintf("SELECT type_tag FROM survey_question, survey_questiontype WHERE survey_question.question_id = %s AND survey_question.questiontype_fi = survey_questiontype.questiontype_id",
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
	* set anonymize status
	*/
	function setAnonymize($a_anonymize)
	{
		$this->anonymize = $a_anonymize;
	}

	/**
	* get anonymize status
	*
	* @return	integer status
	*/
	function getAnonymize()
	{
		return $this->anonymize;
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
* Loads a survey object from a database
* 
* Loads a survey object from a database
*
* @access public
*/
  function loadFromDb()
  {
    $query = sprintf("SELECT * FROM survey_survey WHERE obj_fi = %s",
      $this->ilias->db->quote($this->getId())
    );
    $result = $this->ilias->db->query($query);
    if (strcmp(strtolower(get_class($result)), db_result) == 0) {
      if ($result->numRows() == 1) {
        $data = $result->fetchRow(DB_FETCHMODE_OBJECT);
				$this->survey_id = $data->survey_id;
        $this->author = $data->author;
        $this->introduction = $data->introduction;
        $this->status = $data->status;
				$this->invitation = $data->invitation;
				$this->invitation_mode = $data->invitation_mode;
				$this->display_question_titles = $data->show_question_titles;
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
				if (!$data->anonymize)
				{
					$this->setAnonymize(ANONYMIZE_OFF);
				}
				else
				{
					$this->setAnonymize(ANONYMIZE_ON);
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
* Gets the status of the display_question_titles attribute
*
* Gets the status of the display_question_titles attribute
*
* @return integer The status of the display_question_titles attribute
* @access public
* @see $display_question_titles
*/
  function getShowQuestionTitles() {
		return $this->display_question_titles;
  }

/**
* Sets the question titles visible during the query
*
* Sets the question titles visible during the query
*
* @access public
* @see $display_question_titles
*/
  function showQuestionTitles() {
		$this->display_question_titles = QUESTIONTITLES_VISIBLE;
  }

/**
* Sets the question titles hidden during the query
*
* Sets the question titles hidden during the query
*
* @access public
* @see $display_question_titles
*/
  function hideQuestionTitles() {
		$this->display_question_titles = QUESTIONTITLES_HIDDEN;
  }
	
/**
* Sets the invitation status
*
* Sets the invitation status
*
* @param integer $invitation The invitation status
* @access public
* @see $invitation
*/
  function setInvitation($invitation = 0) {
    $this->invitation = $invitation;
		// remove the survey from the personal desktops
		$query = sprintf("DELETE FROM desktop_item WHERE type = %s AND item_id = %s",
			$this->ilias->db->quote("svy"),
			$this->ilias->db->quote($this->getRefId())
		);
		$result = $this->ilias->db->query($query);
		if ($invitation == INVITATION_OFF)
		{
			// already removed prior
		}
		else if ($invitation == INVITATION_ON)
		{
			if ($this->getInvitationMode() == MODE_UNLIMITED)
			{
				$query = "SELECT usr_id FROM usr_data";
				$result = $this->ilias->db->query($query);
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$query = sprintf("INSERT INTO desktop_item (user_id, item_id, type, parameters) VALUES (%s, %s, %s, NULL)",
						$this->ilias->db->quote($row["usr_id"]),
						$this->ilias->db->quote($this->getRefId()),
						$this->ilias->db->quote("svy")
					);
					$insertresult = $this->ilias->db->query($query);
				}
			}
			else
			{
				$query = sprintf("SELECT user_fi FROM survey_invited_user WHERE survey_fi = %s",
					$this->ilias->db->quote($this->getSurveyId())
				);
				$result = $this->ilias->db->query($query);
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$query = sprintf("INSERT INTO desktop_item (user_id, item_id, type, parameters) VALUES (%s, %s, %s, NULL)",
						$this->ilias->db->quote($row["user_fi"]),
						$this->ilias->db->quote($this->getRefId()),
						$this->ilias->db->quote("svy")
					);
					$insertresult = $this->ilias->db->query($query);
				}
				$query = sprintf("SELECT group_fi FROM survey_invited_group WHERE survey_fi = %s",
					$this->ilias->db->quote($this->getSurveyId())
				);
				$result = $this->ilias->db->query($query);
				while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
				{
					$group = new ilObjGroup($row["group_fi"]);
					$members = $group->getGroupMemberIds();
					foreach ($members as $user_id)
					{
						$user = new ilObjUser($user_id);
						$user->addDesktopItem($this->getRefId(), "svy");
					}
				}
			}
		}
  }

/**
* Sets the invitation mode
*
* Sets the invitation mode
*
* @param integer $invitation_mode The invitation mode
* @access public
* @see $invitation_mode
*/
  function setInvitationMode($invitation_mode = 0) {
    $this->invitation_mode = $invitation_mode;
		if ($invitation_mode == MODE_UNLIMITED)
		{
			$query = sprintf("DELETE FROM survey_invited_group WHERE survey_fi = %s",
				$this->ilias->db->quote($this->getSurveyId())
			);
			$result = $this->ilias->db->query($query);
			$query = sprintf("DELETE FROM survey_invited_user WHERE survey_fi = %s",
				$this->ilias->db->quote($this->getSurveyId())
			);
			$result = $this->ilias->db->query($query);
		}
		// add/remove the survey from personal desktops -> calling getInvitation with the same value makes all changes for the new invitation mode
		$this->setInvitation($this->getInvitation());
  }
	
/**
* Sets the invitation status and mode (a more performant solution if you change both)
*
* Sets the invitation status and mode (a more performant solution if you change both)
*
* @param integer $invitation The invitation status
* @param integer $invitation_mode The invitation mode
* @access public
* @see $invitation_mode
*/
	function setInvitationAndMode($invitation = 0, $invitation_mode = 0)
	{
    $this->invitation_mode = $invitation_mode;
		if ($invitation_mode == MODE_UNLIMITED)
		{
			$query = sprintf("DELETE FROM survey_invited_group WHERE survey_fi = %s",
				$this->ilias->db->quote($this->getSurveyId())
			);
			$result = $this->ilias->db->query($query);
			$query = sprintf("DELETE FROM survey_invited_user WHERE survey_fi = %s",
				$this->ilias->db->quote($this->getSurveyId())
			);
			$result = $this->ilias->db->query($query);
		}
		// add/remove the survey from personal desktops -> calling getInvitation with the same value makes all changes for the new invitation mode
		$this->setInvitation($invitation);
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
* Gets the invitation status
*
* Gets the invitation status
*
* @return integer The invitation status
* @access public
* @see $invitation
*/
  function getInvitation() {
    return $this->invitation;
  }

/**
* Gets the invitation mode
*
* Gets the invitation mode
*
* @return integer The invitation mode
* @access public
* @see $invitation
*/
  function getInvitationMode() {
    return $this->invitation_mode;
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
* Gets the survey status
*
* Gets the survey status
*
* @return integer true if status is online, false otherwise
* @access public
* @see $status
*/
  function isOnline() 
	{
    if ($this->status == STATUS_ONLINE)
		{
			return true;
		}
		else
		{
			return false;
		}
  }

/**
* Gets the survey status
*
* Gets the survey status
*
* @return integer true if status is online, false otherwise
* @access public
* @see $status
*/
  function isOffline() 
	{
    if ($this->status == STATUS_OFFLINE)
		{
			return true;
		}
		else
		{
			return false;
		}
  }

/**
* Sets the survey status
*
* Sets the survey status
*
* @param integer $status Survey status
* @return string An error message, if the status cannot be set, otherwise an empty string
* @access public
* @see $status
*/
  function setStatus($status = STATUS_OFFLINE) {
		$result = "";
		if (($status == STATUS_ONLINE) && (count($this->questions) == 0))
		{
    	$this->status = STATUS_OFFLINE;
			$result = $this->lng->txt("cannot_switch_to_online_no_questions");
		}
		else
		{
    	$this->status = $status;
		}
		return $result;
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
* Checks if the survey can be started
* 
* Checks if the survey can be started
*
* @return integer 
* @access public
*/
	function canStartSurvey()
	{
		$result = 0;
		if ($this->getStartDateEnabled())
		{
			$epoch_time = mktime(0, 0, 0, $this->getStartMonth(), $this->getStartDay(), $this->getStartYear());
			$now = mktime();
			if ($now < $epoch_time) {
				$result = SURVEY_START_START_DATE_NOT_REACHED;
			}
		}
		if ($this->getEndDateEnabled())
		{
			$epoch_time = mktime(0, 0, 0, $this->getEndMonth(), $this->getEndDay(), $this->getEndYear());
			$now = mktime();
			if ($now > $epoch_time) {
				$result = SURVEY_START_END_DATE_REACHED;
			}
		}
		if ($this->getStatus() == STATUS_OFFLINE)
		{
			$result = SURVEY_START_OFFLINE;
		}
		return $result;
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
		$query = sprintf("SELECT survey_question.original_id FROM survey_question, survey_survey_question WHERE survey_survey_question.survey_fi = %s AND survey_survey_question.question_fi = survey_question.question_id",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
			array_push($existing_questions, $data->original_id);
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
		global $rbacsystem;
		
		$qpl_titles = array();
		// get all available questionpools and remove the trashed questionspools
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'spl'";
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
* Moves a question up in the list of survey questions
*
* Moves a question up in the list of survey questions
*
* @param integer $question_id The question id of the question which has to be moved up
* @access public
*/
	function moveUpQuestion($question_id)
	{
		$move_questions = array($question_id);
		$pages =& $this->getSurveyPages();
		$pageindex = -1;
		foreach ($pages as $idx => $page)
		{
			if ($page[0]["question_id"] == $question_id)
			{
				$pageindex = $idx;
			}
		}
		if ($pageindex > 0)
		{
			$this->moveQuestions($move_questions, $pages[$pageindex-1][0]["question_id"], 0);
		}
		else
		{
			// move up a question in a questionblock
			$questions = $this->getSurveyQuestions();
			$questions = array_keys($questions);
			$index = array_search($question_id, $questions);
			if (($index !== FALSE) && ($index > 0))
			{
				$this->moveQuestions($move_questions, $questions[$index-1], 0);
			}
		}
	}
	
/**
* Moves a question down in the list of survey questions
*
* Moves a question down in the list of survey questions
*
* @param integer $question_id The question id of the question which has to be moved down
* @access public
*/
	function moveDownQuestion($question_id)
	{
		$move_questions = array($question_id);
		$pages =& $this->getSurveyPages();
		$pageindex = -1;
		foreach ($pages as $idx => $page)
		{
			if (($page[0]["question_id"] == $question_id) && (strcmp($page[0]["questionblock_id"], "") == 0))
			{
				$pageindex = $idx;
			}
		}
		if (($pageindex < count($pages)-1) && ($pageindex >= 0))
		{
			$this->moveQuestions($move_questions, $pages[$pageindex+1][count($pages[$pageindex+1])-1]["question_id"], 1);
		}
		else
		{
			// move down a question in a questionblock
			$questions = $this->getSurveyQuestions();
			$questions = array_keys($questions);
			$index = array_search($question_id, $questions);
			if (($index !== FALSE) && ($index < count($questions)-1))
			{
				$this->moveQuestions($move_questions, $questions[$index+1], 1);
			}
		}
	}
	
/**
* Moves a questionblock up in the list of survey questions
*
* Moves a questionblock up in the list of survey questions
*
* @param integer $questionblock_id The questionblock id of the questionblock which has to be moved up
* @access public
*/
	function moveUpQuestionblock($questionblock_id)
	{
		$pages =& $this->getSurveyPages();
		$move_questions = array();
		$pageindex = -1;
		foreach ($pages as $idx => $page)
		{
			if ($page[0]["questionblock_id"] == $questionblock_id)
			{
				foreach ($page as $pageidx => $question)
				{
					array_push($move_questions, $question["question_id"]);
				}
				$pageindex = $idx;
			}
		}
		if ($pageindex > 0)
		{
			$this->moveQuestions($move_questions, $pages[$pageindex-1][0]["question_id"], 0);
		}
	}
	
/**
* Moves a questionblock down in the list of survey questions
*
* Moves a questionblock down in the list of survey questions
*
* @param integer $questionblock_id The questionblock id of the questionblock which has to be moved down
* @access public
*/
	function moveDownQuestionblock($questionblock_id)
	{
		$pages =& $this->getSurveyPages();
		$move_questions = array();
		$pageindex = -1;
		foreach ($pages as $idx => $page)
		{
			if ($page[0]["questionblock_id"] == $questionblock_id)
			{
				foreach ($page as $pageidx => $question)
				{
					array_push($move_questions, $question["question_id"]);
				}
				$pageindex = $idx;
			}
		}
		if ($pageindex < count($pages)-1)
		{
			$this->moveQuestions($move_questions, $pages[$pageindex+1][count($pages[$pageindex+1])-1]["question_id"], 1);
		}
	}
	
/**
* Move questions and/or questionblocks to another position
*
* Move questions and/or questionblocks to another position
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
		foreach ($move_questions as $question_id)
		{
			$constraints = $this->getConstraints($question_id);
			foreach ($constraints as $idx => $constraint)
			{
				foreach ($part2 as $next_question_id)
				{
					if ($constraint["question"] == $next_question_id)
					{
						// constraint concerning a question that follows -> delete constraint
						$this->deleteConstraint($constraint["id"], $question_id);
					}
				}
			}
		}
		$this->saveQuestionsToDb();
	}
	
/**
* Remove a question from the survey
*
* Remove a question from the survey
*
* @param integer $question_id The database id of the question
* @access public
*/
	function removeQuestion($question_id)
	{
		$question = new SurveyQuestion();
		$question->delete($question_id);
		$this->removeConstraintsConcerningQuestion($question_id);
	}
	
/**
* Remove constraints concerning a question with a given question_id
*
* Remove constraints concerning a question with a given question_id
*
* @param integer $question_id The database id of the question
* @access public
*/
	function removeConstraintsConcerningQuestion($question_id)
	{
		$query = sprintf("SELECT constraint_fi FROM survey_question_constraint WHERE question_fi = %s AND survey_fi = %s",
			$this->ilias->db->quote($question_id . ""),
			$this->ilias->db->quote($this->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() > 0)
		{
			$remove_constraints = array();
			while ($row = $result->fetchRow(DB_FETCHMODE_HASHREF))
			{
				array_push($remove_constraints, $row["constraint_fi"]);
			}
			$query = sprintf("DELETE FROM survey_question_constraint WHERE question_fi = %s AND survey_fi = %s",
				$this->ilias->db->quote($question_id . ""),
				$this->ilias->db->quote($this->getSurveyId() . "")
			);
			$result = $this->ilias->db->query($query);
			foreach ($remove_constraints as $key => $constraint_id)
			{
				$query = sprintf("DELETE FROM survey_constraint WHERE constraint_id = %s",
					$this->ilias->db->quote($constraint_id . "")
				);
				$result = $this->ilias->db->query($query);
			}
		}
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
				$this->removeQuestion($question_id);
			}
		}
		foreach ($remove_questionblocks as $questionblock_id)
		{
			$query = sprintf("DELETE FROM survey_questionblock WHERE questionblock_id = %s",
				$this->ilias->db->quote($questionblock_id)
			);
			$result = $this->ilias->db->query($query);
			$query = sprintf("DELETE FROM survey_questionblock_question WHERE questionblock_fi = %s AND survey_fi = %s",
				$this->ilias->db->quote($questionblock_id),
				$this->ilias->db->quote($this->getSurveyId())
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
			$query = sprintf("DELETE FROM survey_questionblock_question WHERE questionblock_fi = %s AND survey_fi = %s",
				$this->ilias->db->quote($index),
				$this->ilias->db->quote($this->getSurveyId())
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
		$query = sprintf("SELECT survey_questionblock.* FROM survey_questionblock, survey_question, survey_questionblock_question WHERE survey_questionblock_question.question_fi = survey_question.question_id AND survey_question.obj_fi = %s",
			$this->ilias->db->quote($this->getId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$titles[$row->questionblock_id] = $row->title;
		}
		return $titles;
	}
	
/**
* Returns the question titles of all questions of a question block
* 
* Returns the question titles of all questions of a question block
*
* @result array The titles of the the question block questions
* @access public
*/
	function &getQuestionblockQuestions($questionblock_id)
	{
		$titles = array();
		$query = sprintf("SELECT survey_question.title, survey_questionblock_question.question_fi, survey_questionblock_question.survey_fi FROM survey_questionblock, survey_questionblock_question, survey_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_question.question_id = survey_questionblock_question.question_fi AND survey_questionblock.questionblock_id = %s",
			$this->ilias->db->quote($questionblock_id)
		);
		$result = $this->ilias->db->query($query);
		$survey_id = "";
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$titles[$row["question_fi"]] = $row["title"];
			$survey_id = $row["survey_fi"];
		}
		$query = sprintf("SELECT question_fi, sequence FROM survey_survey_question WHERE survey_fi = %s ORDER BY sequence",
			$this->ilias->db->quote($survey_id . "")
		);
		$result = $this->ilias->db->query($query);
		$resultarray = array();
		$counter = 1;
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (array_key_exists($row["question_fi"], $titles))
			{
				$resultarray[$counter++] = $titles[$row["question_fi"]];
			}
		}
		return $resultarray;
	}
	
/**
* Returns the question id's of all questions of a question block
* 
* Returns the question id's of all questions of a question block
*
* @result array The id's of the the question block questions
* @access public
*/
	function &getQuestionblockQuestionIds($questionblock_id)
	{
		$ids = array();
		$query = sprintf("SELECT survey_questionblock.*, survey_survey.obj_fi, survey_question.question_id AS questiontitle, survey_survey_question.sequence, object_data.title as surveytitle, survey_question.question_id FROM object_reference, object_data, survey_questionblock, survey_questionblock_question, survey_survey, survey_question, survey_survey_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_survey.survey_id = survey_questionblock_question.survey_fi AND survey_questionblock_question.question_fi = survey_question.question_id AND survey_survey.obj_fi = object_reference.obj_id AND object_reference.obj_id = object_data.obj_id AND survey_survey_question.survey_fi = survey_survey.survey_id AND survey_survey_question.question_fi = survey_question.question_id AND survey_survey.obj_fi = %s AND survey_questionblock.questionblock_id = %s ORDER BY survey_survey_question.sequence ASC",
			$this->ilias->db->quote($this->getId()),
			$this->ilias->db->quote($questionblock_id)
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($ids, $row->question_id);
		}
		return $ids;
	}
	
/**
* Returns the database row for a given question block
* 
* Returns the database row for a given question block
*
* @param integer $questionblock_id The database id of the question block
* @result array The database row of the question block
* @access public
*/
	function getQuestionblock($questionblock_id)
	{
		$query = sprintf("SELECT * FROM survey_questionblock WHERE questionblock_id = %s",
			$this->ilias->db->quote($questionblock_id)
		);
		$result = $this->ilias->db->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		return $row;
	}
	
/**
* Returns the database row for a given question block
* 
* Returns the database row for a given question block
*
* @param integer $questionblock_id The database id of the question block
* @result array The database row of the question block
* @access public
*/
	function _getQuestionblock($questionblock_id)
	{
		global $ilDB;
		$query = sprintf("SELECT * FROM survey_questionblock WHERE questionblock_id = %s",
			$ilDB->quote($questionblock_id)
		);
		$result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		return $row;
	}

/**
* Adds a questionblock to the database
* 
* Adds a questionblock to the database
*
* @param string $title The questionblock title
* @param integer $owner The database id of the owner
* @return integer The database id of the newly created questionblock
* @access public
*/
	function _addQuestionblock($title = "", $owner = 0)
	{
		global $ilDB;
		$query = sprintf("INSERT INTO survey_questionblock (questionblock_id, title, owner_fi, TIMESTAMP) VALUES (NULL, %s, %s, NULL)",
			$ilDB->quote($title . ""),
			$ilDB->quote($owner . "")
		);
		$result = $ilDB->query($query);
		return $ilDB->getLastInsertId();
	}
	
/**
* Creates a question block for the survey
* 
* Creates a question block for the survey
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
				$query = sprintf("INSERT INTO survey_questionblock_question (questionblock_question_id, survey_fi, questionblock_fi, question_fi) VALUES (NULL, %s, %s, %s)",
					$this->ilias->db->quote($this->getSurveyId()),
					$this->ilias->db->quote($questionblock_id),
					$this->ilias->db->quote($index)
				);
				$result = $this->ilias->db->query($query);
				$this->deleteConstraints($index);
			}
		}
	}
	
/**
* Modifies a question block
* 
* Modifies a question block
*
* @param integer $questionblock_id The database id of the question block
* @param string $title The title of the question block
* @access public
*/
	function modifyQuestionblock($questionblock_id, $title)
	{
		$query = sprintf("UPDATE survey_questionblock SET title = %s WHERE questionblock_id = %s",
			$this->ilias->db->quote($title),
			$this->ilias->db->quote($questionblock_id)
		);
		$result = $this->ilias->db->query($query);
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
			$this->ilias->db->quote($this->getSurveyId())
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
			$this->ilias->db->quote($this->getSurveyId())
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
			$this->ilias->db->quote($this->getSurveyId())
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
	function &getSurveyQuestions($with_answers = false)
	{
		$obligatory_states =& $this->getObligatoryStates();
		// get questionblocks
		$all_questions = array();
		$query = sprintf("SELECT survey_question.*, survey_questiontype.type_tag, survey_survey_question.heading FROM survey_question, survey_questiontype, survey_survey_question WHERE survey_survey_question.survey_fi = %s AND survey_survey_question.question_fi = survey_question.question_id AND survey_question.questiontype_fi = survey_questiontype.questiontype_id ORDER BY survey_survey_question.sequence",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$all_questions[$row["question_id"]] = $row;
			if (array_key_exists($row["question_id"], $obligatory_states))
			{
				$all_questions[$row["question_id"]]["obligatory"] = $obligatory_states[$row["question_id"]];
			}
		}
		// get all questionblocks
		$questionblocks = array();
		$in = join(array_keys($all_questions), ",");
		if ($in)
		{
			$query = sprintf("SELECT survey_questionblock.*, survey_questionblock_question.question_fi FROM survey_questionblock, survey_questionblock_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_questionblock_question.survey_fi = %s AND survey_questionblock_question.question_fi IN ($in)",
				$this->ilias->db->quote($this->getSurveyId())
			);
			$result = $this->ilias->db->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$questionblocks[$row->question_fi] = $row;
			}			
		}
		
		foreach ($all_questions as $question_id => $row)
		{
			$constraints = $this->getConstraints($question_id);
			if (isset($questionblocks[$question_id]))
			{
				$all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]->title;
				$all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]->questionblock_id;
				$all_questions[$question_id]["constraints"] = $constraints;
			}
			else
			{
				$all_questions[$question_id]["questionblock_title"] = "";
				$all_questions[$question_id]["questionblock_id"] = "";
				$all_questions[$question_id]["constraints"] = $constraints;
			}
			if ($with_answers)
			{
				$answers = array();
				$query = sprintf("SELECT survey_variable.*, survey_category.title FROM survey_variable, survey_category WHERE survey_variable.question_fi = %s AND survey_variable.category_fi = survey_category.category_id ORDER BY sequence ASC",
					$this->ilias->db->quote($question_id . "")
				);
				$result = $this->ilias->db->query($query);
				if (strcmp(strtolower(get_class($result)), db_result) == 0) {
					while ($data = $result->fetchRow(DB_FETCHMODE_OBJECT)) {
						array_push($answers, $data->title);
					}
				}
				$all_questions[$question_id]["answers"] = $answers;				
			}
		}
		return $all_questions;
	}
	
/**
* Returns an array with all existing question types
* 
* Returns an array with all existing question types
*
* @result array An array containing the question types
* @access public
*/
	function &getQuestiontypes()
	{
		$query = "SELECT type_tag FROM survey_questiontype";
		$result = $this->ilias->db->query($query);
		$result_array = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($result_array, $row->type_tag);
		}
		return $result_array;
	}

/**
* Sets the obligatory states for questions in a survey from the questions form
* 
* Sets the obligatory states for questions in a survey from the questions form
*
* @param array $obligatory_questions The questions which should be set obligatory from the questions form, the remaining questions should be setted not obligatory
* @access public
*/
	function setObligatoryStates($obligatory_questions)
	{
		$query = sprintf("SELECT * FROM survey_survey_question WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				if (!array_key_exists($row["question_fi"], $obligatory_questions))
				{
					$obligatory_questions[$row["question_fi"]] = 0;
				}
			}
		}

	  // set the obligatory states in the database
		$query = sprintf("DELETE FROM survey_question_obligatory WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);

	  // set the obligatory states in the database
		foreach ($obligatory_questions as $question_fi => $obligatory)
		{
			$query = sprintf("INSERT INTO survey_question_obligatory (question_obligatory_id, survey_fi, question_fi, obligatory, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($question_fi . ""),
				$this->ilias->db->quote($obligatory . "")
			);
			$result = $this->ilias->db->query($query);
		}
	}
	
/**
* Gets specific obligatory states of the survey
* 
* Gets specific obligatory states of the survey
*
* @return array An array containing the obligatory states for every question found in the database
* @access public
*/
	function &getObligatoryStates()
	{
		$obligatory_states = array();
		$query = sprintf("SELECT * FROM survey_question_obligatory WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$obligatory_states[$row["question_fi"]] = $row["obligatory"];
			}
		}
		return $obligatory_states;
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
		$obligatory_states =& $this->getObligatoryStates();
		// get questionblocks
		$all_questions = array();
		$query = sprintf("SELECT survey_question.*, survey_questiontype.type_tag, survey_survey_question.heading FROM survey_question, survey_questiontype, survey_survey_question WHERE survey_survey_question.survey_fi = %s AND survey_survey_question.question_fi = survey_question.question_id AND survey_question.questiontype_fi = survey_questiontype.questiontype_id ORDER BY survey_survey_question.sequence",
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
			$query = sprintf("SELECT survey_questionblock.*, survey_questionblock_question.question_fi FROM survey_questionblock, survey_questionblock_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_questionblock_question.survey_fi = %s AND survey_questionblock_question.question_fi IN ($in)",
				$this->ilias->db->quote($this->getSurveyId())
			);
			$result = $this->ilias->db->query($query);
			while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
			{
				$questionblocks["$row->question_fi"] = $row;
			}			
		}
		
		$all_pages = array();
		$pageindex = -1;
		$currentblock = "";
		foreach ($all_questions as $question_id => $row)
		{
			if (array_key_exists($question_id, $obligatory_states))
			{
				$all_questions["$question_id"]["obligatory"] = $obligatory_states["$question_id"];
			}
			$constraints = array();
			if (isset($questionblocks[$question_id]))
			{
				if (!$currentblock or ($currentblock != $questionblocks[$question_id]->questionblock_id))
				{
					$pageindex++;
				}
				$all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]->title;
				$all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]->questionblock_id;
				$currentblock = $questionblocks[$question_id]->questionblock_id;
				$constraints = $this->getConstraints($question_id);
				$all_questions[$question_id]["constraints"] = $constraints;
			}
			else
			{
				$pageindex++;
				$all_questions[$question_id]["questionblock_title"] = "";
				$all_questions[$question_id]["questionblock_id"] = "";
				$currentblock = "";
				$constraints = $this->getConstraints($question_id);
				$all_questions[$question_id]["constraints"] = $constraints;
			}
			if (!isset($all_pages[$pageindex]))
			{
				$all_pages[$pageindex] = array();
			}
			array_push($all_pages[$pageindex], $all_questions[$question_id]);
		}
		// calculate position percentage for every page
		$max = count($all_pages);
		$counter = 1;
		foreach ($all_pages as $index => $block)
		{
			foreach ($block as $blockindex => $question)
			{
				$all_pages[$index][$blockindex][position] = $counter / $max;
			}
			$counter++;
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
	function &getAvailableQuestionpools($use_obj_id = false)
	{
		global $rbacsystem;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'spl'";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			if ($rbacsystem->checkAccess("write", $row->ref_id) && ($this->_hasUntrashedReference($row->obj_id)))
			{
				if ($use_obj_id)
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
* Returns the constraints to a given question or questionblock
* 
* Returns the constraints to a given question or questionblock
*
* @access public
*/
	function getConstraints($question_id)
 	{
		$result_array = array();
		$query = sprintf("SELECT survey_constraint.*, survey_relation.* FROM survey_question_constraint, survey_constraint, survey_relation WHERE survey_constraint.relation_fi = survey_relation.relation_id AND survey_question_constraint.constraint_fi = survey_constraint.constraint_id AND survey_question_constraint.question_fi = %s AND survey_question_constraint.survey_fi = %s",
			$this->ilias->db->quote($question_id),
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			array_push($result_array, array("id" => $row->constraint_id, "question" => $row->question_fi, "short" => $row->shortname, "long" => $row->longname, "value" => $row->value));
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
	function _getConstraints($survey_id)
 	{
		global $ilDB;
		$result_array = array();
		$query = sprintf("SELECT survey_question_constraint.question_fi as for_question, survey_constraint.*, survey_relation.* FROM survey_question_constraint, survey_constraint, survey_relation WHERE survey_constraint.relation_fi = survey_relation.relation_id AND survey_question_constraint.constraint_fi = survey_constraint.constraint_id AND survey_question_constraint.survey_fi = %s",
			$ilDB->quote($survey_id . "")
		);
		$result = $ilDB->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{		
			array_push($result_array, array("id" => $row->constraint_id, "for_question" => $row->for_question, "question" => $row->question_fi, "short" => $row->shortname, "long" => $row->longname, "relation_id" => $row->relation_id, "value" => $row->value));
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
		$query = sprintf("SELECT survey_variable.*, survey_category.title FROM survey_variable LEFT JOIN survey_category ON survey_variable.category_fi = survey_category.category_id WHERE survey_variable.question_fi = %s ORDER BY survey_variable.sequence",
			$this->ilias->db->quote($question_id)
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$result_array[$row->sequence] = $row;
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
				$this->ilias->db->quote($this->getSurveyId()),
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
	function getAllRelations($short_as_key = false)
 	{
		$result_array = array();
		$query = "SELECT * FROM survey_relation";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			if ($short_as_key)
			{
				$result_array[$row->shortname] = array("short" => $row->shortname, "long" => $row->longname, "id" => $row->relation_id);
			}
			else
			{
				$result_array[$row->relation_id] = array("short" => $row->shortname, "long" => $row->longname);
			}
		}
		return $result_array;
	}

/**
* Disinvites a user from a survey
* 
* Disinvites a user from a survey
*
* @param integer $user_id The database id of the disinvited user
* @access public
*/
	function disinviteUser($user_id)
	{
		$query = sprintf("DELETE FROM survey_invited_user WHERE survey_fi = %s AND user_fi = %s",
			$this->ilias->db->quote($this->getSurveyId()),
			$this->ilias->db->quote($user_id)
		);
		$result = $this->ilias->db->query($query);
		if ($this->getInvitation() == INVITATION_ON)
		{
			$userObj = new ilObjUser($user_id);
			$userObj->dropDesktopItem($this->getRefId(), "svy");
		}
	}

/**
* Invites a user to a survey
* 
* Invites a user to a survey
*
* @param integer $user_id The database id of the invited user
* @access public
*/
	function inviteUser($user_id)
	{
		$query = sprintf("SELECT user_fi FROM survey_invited_user WHERE user_fi = %s AND survey_fi = %s",
			$this->ilias->db->quote($user_id),
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() < 1)
		{
			$query = sprintf("INSERT INTO survey_invited_user (invited_user_id, survey_fi, user_fi, TIMESTAMP) VALUES (NULL, %s, %s, NULL)",
				$this->ilias->db->quote($this->getSurveyId()),
				$this->ilias->db->quote($user_id)
			);
			$result = $this->ilias->db->query($query);
		}
		if ($this->getInvitation() == INVITATION_ON)
		{
			$userObj = new ilObjUser($user_id);
			$userObj->addDesktopItem($this->getRefId(), "svy");
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
	function disinviteGroup($group_id)
	{
		$query = sprintf("DELETE FROM survey_invited_group WHERE survey_fi = %s AND group_fi = %s",
			$this->ilias->db->quote($this->getSurveyId()),
			$this->ilias->db->quote($group_id)
		);
		$result = $this->ilias->db->query($query);
		if ($this->getInvitation() == INVITATION_ON)
		{
			$group = new ilObjGroup($group_id);
			$members = $group->getGroupMemberIds();
			foreach ($members as $user_id)
			{
				$userObj = new ilObjUser($user_id);
				$userObj->dropDesktopItem($this->getRefId(), "svy");
			}
		}
	}

/**
* Invites a group to a survey
* 
* Invites a group to a survey
*
* @param integer $group_id The database id of the invited group
* @access public
*/
	function inviteGroup($group_id)
	{
		$query = sprintf("SELECT group_fi FROM survey_invited_group WHERE group_fi = %s AND survey_fi = %s",
			$this->ilias->db->quote($group_id),
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() < 1)
		{
			$query = sprintf("INSERT INTO survey_invited_group (invited_group_id, survey_fi, group_fi, TIMESTAMP) VALUES (NULL, %s, %s, NULL)",
				$this->ilias->db->quote($this->getSurveyId()),
				$this->ilias->db->quote($group_id)
			);
			$result = $this->ilias->db->query($query);
		}
		
		if ($this->getInvitation() == INVITATION_ON)
		{
			$group = new ilObjGroup($group_id);
			$members = $group->getGroupMemberIds();
			foreach ($members as $user_id)
			{
				$userObj = new ilObjUser($user_id);
				$userObj->addDesktopItem($this->getRefId(), "svy");
			}
		}
	}
	
/**
* Returns a list of all invited users in a survey
* 
* Returns a list of all invited users in a survey
*
* @return array The user id's of the invited users
* @access public
*/
	function &getInvitedUsers()
	{
		$result_array = array();
		$query = sprintf("SELECT user_fi FROM survey_invited_user WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($result_array, $row->user_fi);
		}
		return $result_array;
	}

/**
* Returns a list of all invited groups in a survey
* 
* Returns a list of all invited groups in a survey
*
* @return array The group id's of the invited groups
* @access public
*/
	function &getInvitedGroups()
	{
		$result_array = array();
		$query = sprintf("SELECT group_fi FROM survey_invited_group WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			array_push($result_array, $row->group_fi);
		}
		return $result_array;
	}

/**
* Deletes the working data of a question in the database
*
* Deletes the working data of a question in the database
*
* @param integer $question_id The database id of the question
* @param integer $user_id The database id of the user who worked through the question
* @access public
*/
	function deleteWorkingData($question_id, $user_id)
	{
		$query = "";
		if ($this->getAnonymize())
		{
			$query = sprintf("DELETE FROM survey_answer WHERE survey_fi = %s AND question_fi = %s AND anonymous_id = %s",
				$this->ilias->db->quote($this->getSurveyId()),
				$this->ilias->db->quote($question_id),
				$this->ilias->db->quote($_SESSION["anonymous_id"])
			);
		}
		else
		{
			$query = sprintf("DELETE FROM survey_answer WHERE survey_fi = %s AND question_fi = %s AND user_fi = %s",
				$this->ilias->db->quote($this->getSurveyId()),
				$this->ilias->db->quote($question_id),
				$this->ilias->db->quote($user_id)
			);
		}
		$result = $this->ilias->db->query($query);
	}
	
/**
* Saves the working data of a question to the database
*
* Saves the working data of a question to the database
*
* @param integer $question_id The database id of the question
* @param integer $user_id The database id of the user who worked through the question
* @param mixed $value The value the user entered for the question
* @param string $text The answer text of a text question
* @access public
*/
	function saveWorkingData($question_id, $user_id, $anonymize_id, $value = "", $text = "")
	{
		if ($this->isSurveyStarted($user_id, $anonymize_id) === false)
		{
			$this->startSurvey($user_id, $anonymize_id);
		}
		if (strcmp($value, "") == 0)
		{
			$value = "NULL";
		}
		else
		{
			$value = $this->ilias->db->quote($value);
		}
		if (strcmp($text, "") == 0)
		{
			$text = "NULL";
		}
		else
		{
			$text = $this->ilias->db->quote($text);
		}
		if ($this->getAnonymize())
		{
			$user_id = 0;
		}
		$query = sprintf("INSERT INTO survey_answer (answer_id, survey_fi, question_fi, user_fi, anonymous_id, value, textanswer, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
			$this->ilias->db->quote($this->getSurveyId() . ""),
			$this->ilias->db->quote($question_id . ""),
			$this->ilias->db->quote($user_id . ""),
			$this->ilias->db->quote($anonymize_id),
			$value,
			$text
		);
		$result = $this->ilias->db->query($query);
	}
	
/**
* Gets the working data of question from the database
*
* Gets the working data of question from the database
*
* @param integer $question_id The database id of the question
* @param integer $user_id The database id of the user who worked through the question
* @return array The resulting database dataset as an array
* @access public
*/
	function loadWorkingData($question_id, $user_id)
	{
		$result_array = array();
		$query = "";
		if ($this->getAnonymize())
		{
			$query = sprintf("SELECT * FROM survey_answer WHERE survey_fi = %s AND question_fi = %s AND anonymous_id = %s",
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($question_id. ""),
				$this->ilias->db->quote($_SESSION["anonymous_id"])
			);
		}
		else
		{
			$query = sprintf("SELECT * FROM survey_answer WHERE survey_fi = %s AND question_fi = %s AND user_fi = %s",
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($question_id . ""),
				$this->ilias->db->quote($user_id . "")
			);
		}
		$result = $this->ilias->db->query($query);
		if ($result->numRows() >= 1)
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($result_array, $row);
			}
			return $result_array;
		}
		else
		{
			return $result_array;
		}
	}

/**
* Starts the survey creating an entry in the database
*
* Starts the survey creating an entry in the database
*
* @param integer $user_id The database id of the user who starts the survey
* @access public
*/
	function startSurvey($user_id, $anonymous_id)
	{
		global $ilUser;
		
		if (strcmp($user_id, "") == 0)
		{
			$user_id = 0;
		}
		if ($this->getAnonymize())
		{
			$user_id = 0;
		}
		$query = sprintf("INSERT INTO survey_finished (finished_id, survey_fi, user_fi, anonymous_id, state, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, NULL)",
			$this->ilias->db->quote($this->getSurveyId() . ""),
			$this->ilias->db->quote($user_id . ""),
			$this->ilias->db->quote($anonymous_id . ""),
			$this->ilias->db->quote(0 . "")
		);
		$result = $this->ilias->db->query($query);
		if ($this->getAnonymize())
		{
			if (strcmp($ilUser->login, "anonymous") != 0)
			{
				require_once "./include/inc.mail.php";
				require_once "./classes/class.ilFormatMail.php";
				require_once "./classes/class.ilMailbox.php";
				$subject = sprintf($this->lng->txt("subject_mail_survey_id"), $this->getTitle());
				$message = sprintf($this->lng->txt("message_mail_survey_id"), $this->getTitle(), $_SESSION["anonymous_id"]);
				$umail = new ilFormatMail($ilUser->id);
				$f_message = $umail->formatLinebreakMessage($message);
				$umail->setSaveInSentbox(true);
				if($error_message = $umail->sendMail($ilUser->getLogin(),"",
													 "",$subject,$f_message,
													 "",array("normal")))
				{
					sendInfo($error_message);
				}
			}
		}
	}
			
/**
* Finishes the survey creating an entry in the database
*
* Finishes the survey creating an entry in the database
*
* @param integer $user_id The database id of the user who finishes the survey
* @access public
*/
	function finishSurvey($user_id, $anonymize_id)
	{
		if ($this->getAnonymize())
		{
			$user_id = 0;
			$query = sprintf("UPDATE survey_finished SET state = %s WHERE survey_fi = %s AND anonymous_id = %s",
				$this->ilias->db->quote("1"),
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($anonymize_id . "")
			);
		}
		else
		{
			$query = sprintf("UPDATE survey_finished SET state = %s WHERE survey_fi = %s AND user_fi = %s",
				$this->ilias->db->quote("1"),
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($user_id . "")
			);
		}
		$result = $this->ilias->db->query($query);
	}
	
/**
* Checks if a user already started a survey
*
* Checks if a user already started a survey
*
* @param integer $user_id The database id of the user
* @return mixed false, if the user has not started the survey, 0 if the user has started the survey but not finished it, 1 if the user has finished the survey
* @access public
*/
	function isSurveyStarted($user_id, $anonymize_id)
	{
		if ($this->getAnonymize())
		{
			$query = sprintf("SELECT state FROM survey_finished WHERE survey_fi = %s AND anonymous_id = %s",
				$this->ilias->db->quote($this->getSurveyId()),
				$this->ilias->db->quote($anonymize_id)
			);
		}
		else
		{
			$query = sprintf("SELECT state FROM survey_finished WHERE survey_fi = %s AND user_fi = %s",
				$this->ilias->db->quote($this->getSurveyId()),
				$this->ilias->db->quote($user_id)
			);
		}
		$result = $this->ilias->db->query($query);
		if ($result->numRows() == 0)
		{
			return false;
		}			
		else
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return (int)$row["state"];
		}
	}
	
/**
* Returns the question id of the last active page a user visited in a survey
*
* Returns the question id of the last active page a user visited in a survey
*
* @param integer $user_id The database id of the user
* @return mixed Empty string if the user has not worked through a page, question id of the last page otherwise
* @access public
*/
	function getLastActivePage($user_id)
	{
		$query = "";
		if ($this->getAnonymize())
		{
			$query = sprintf("SELECT question_fi, TIMESTAMP+0 AS TIMESTAMP14 FROM survey_answer WHERE survey_fi = %s AND anonymous_id = %s ORDER BY TIMESTAMP14 DESC",
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($_SESSION["anonymous_id"])
			);
		}
		else
		{
			$query = sprintf("SELECT question_fi, TIMESTAMP+0 AS TIMESTAMP14 FROM survey_answer WHERE survey_fi = %s AND user_fi = %s ORDER BY TIMESTAMP14 DESC",
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($user_id . "")
			);
		}
		$result = $this->ilias->db->query($query);
		if ($result->numRows() == 0)
		{
			return "";
		}
		else
		{
			$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
			return $row["question_fi"];
		}
	}

/**
* Checks if a constraint is valid
*
* Checks if a constraint is valid
*
* @param array $constraint_data The database row containing the constraint data
* @param array $working_data The user input of the related question
* @return boolean true if the constraint is valid, otherwise false
* @access public
*/
	function checkConstraint($constraint_data, $working_data)
	{
		if (count($working_data) == 0)
		{
			return 0;
		}
		
		if ((count($working_data) == 1) and (strcmp($working_data[0]["value"], "") == 0))
		{
			return 0;
		}
		
		foreach ($working_data as $data)
		{
			switch ($constraint_data["short"])
			{
				case "<":
					if ($data["value"] < $constraint_data["value"])
					{
						return 1;
					}
					break;
				case "<=":
					if ($data["value"] <= $constraint_data["value"])
					{
						return 1;
					}
					break;
				case "=":
					if ($data["value"] == $constraint_data["value"])
					{
						return 1;
					}
					break;
				case "<>":
					if ($data["value"] != $constraint_data["value"])
					{
						return 1;
					}
					break;
				case ">=":
					if ($data["value"] >= $constraint_data["value"])
					{
						return 1;
					}
					break;
				case ">":
					if ($data["value"] > $constraint_data["value"])
					{
						return 1;
					}
					break;
			}
		}
		return 0;
	}

	function &getEvaluationForAllUsers()
	{
		$users = array();
		$query = sprintf("SELECT * FROM survey_finished WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows())
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($users, $row);
			}
		}
		$evaluation = array();
		$questions =& $this->getSurveyQuestions();
		foreach ($users as $row)
		{
			if ($row["user_fi"] > 0)
			{
				$evaluation[$row["user_fi"]] = $this->getEvaluationByUser($questions, $row["user_fi"], $row["anonymous_id"]);
			}
			else
			{
				$evaluation[$row["anonymous_id"]] = $this->getEvaluationByUser($questions, $row["user_fi"], $row["anonymous_id"]);
			}
		}
		return $evaluation;
	}
	
/**
* Calculates the evaluation data for a given user or anonymous id
*
* Calculates the evaluation data for a given user or anonymous id
*
* @param array $questions An array containing all relevant information on the survey's questions
* @param integer $user_id The database id of the user
* @param string $anonymous_id The unique anonymous id for an anonymous survey
* @return array An array containing the evaluation parameters for the user
* @access public
*/
	function &getEvaluationByUser($questions, $user_id, $anonymous_id = "")
	{
		$wherecond = "";
		$wherevalue = "";
		if (strcmp($anonymous_id, "") != 0)
		{
			$wherecond = "anonymous_id = %s";
			$wherevalue = $anonymous_id;
		}
		else
		{
			$wherecond = "user_fi = %s";
			$wherevalue = $user_id;
		}
		// collect all answers
		$answers = array();
		$query = sprintf("SELECT * FROM survey_answer WHERE $wherecond AND survey_fi = %s",
			$this->ilias->db->quote($wherevalue),
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if (!is_array($answers[$row["question_fi"]]))
			{
				$answers[$row["question_fi"]] = array();
			}
			array_push($answers[$row["question_fi"]], $row);
		}
		$username = "";
		if ($user_id > 0)
		{
			$user = new ilObjUser($user_id);
			$username = $user->getFullname();
		}
		$resultset = array(
			"name" => $username,
			"answers" => array()
		);
		foreach ($questions as $key => $question)
		{
			if (array_key_exists($key, $answers))
			{
				$resultset["answers"][$key] = $answers[$key];
			}
			else
			{
				$resultset["answers"][$key] = array();
			}
			sort($resultset["answers"][$key]);
		}
		return $resultset;
	}
	
/**
* Calculates the evaluation data for a question
*
* Calculates the evaluation data for a question
*
* @param integer $question_id The database id of the question
* @param integer $user_id The database id of the user
* @return array An array containing the evaluation parameters for the question
* @access public
*/
	function getEvaluation($question_id)
	{
		$questions =& $this->getSurveyQuestions();
		$result_array = array();
		$query = sprintf("SELECT finished_id FROM survey_finished WHERE survey_fi = %s",
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		$nr_of_users = $result->numRows();
				
		$query = sprintf("SELECT * FROM survey_answer WHERE question_fi = %s AND survey_fi = %s",
			$this->ilias->db->quote($question_id),
			$this->ilias->db->quote($this->getSurveyId())
		);
		$result = $this->ilias->db->query($query);
		$cumulated = array();
		$textvalues = array();
		while ($row = $result->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$cumulated["$row->value"]++;
			array_push($textvalues, $row->textanswer);
		}
		asort($cumulated, SORT_NUMERIC);
		end($cumulated);
		$numrows = $result->numRows();
		if ($questions[$question_id]["subtype"] == SUBTYPE_MCMR)
		{
			if ($this->getAnonymize())
			{
				$query = sprintf("SELECT answer_id, concat( question_fi,  \"_\", anonymous_id )  AS groupval FROM `survey_answer` WHERE question_fi = %s AND survey_fi = %s GROUP BY groupval",
					$this->ilias->db->quote($question_id),
					$this->ilias->db->quote($this->getSurveyId())
				);
			}
			else
			{
				$query = sprintf("SELECT answer_id, concat( question_fi,  \"_\", user_fi )  AS groupval FROM `survey_answer` WHERE question_fi = %s AND survey_fi = %s GROUP BY groupval",
					$this->ilias->db->quote($question_id),
					$this->ilias->db->quote($this->getSurveyId())
				);
			}
			$mcmr_result = $this->ilias->db->query($query);
			$result_array["USERS_ANSWERED"] = $mcmr_result->numRows();
			$result_array["USERS_SKIPPED"] = $nr_of_users - $mcmr_result->numRows();
			$numrows = $mcmr_result->numRows();
		}
		else
		{
			$result_array["USERS_ANSWERED"] = $result->numRows();
			$result_array["USERS_SKIPPED"] = $nr_of_users - $result->numRows();
		}
		$variables =& $this->getVariables($question_id);
		switch ($questions[$question_id]["type_tag"])
		{
			case "qt_nominal":
				$result_array["MEDIAN"] = "";
				$result_array["ARITHMETIC_MEAN"] = "";
				$prefix = "";
				if (strcmp(key($cumulated), "") != 0)
				{
					$prefix = (key($cumulated)+1) . " - ";
				}
				$result_array["MODE"] =  $prefix . $variables[key($cumulated)]->title;
				$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
				$result_array["QUESTION_TYPE"] = $questions[$question_id]["type_tag"];
				$maxvalues = 0;
				foreach ($variables as $key => $value)
				{
					$maxvalues += $cumulated[$key];
				}
				foreach ($variables as $key => $value)
				{
					$percentage = 0;
					if ($numrows > 0)
					{
						if ($questions[$question_id]["subtype"] == SUBTYPE_MCMR)
						{
							if ($maxvalues > 0)
							{
								$percentage = (float)((int)$cumulated[$key]/$maxvalues);
							}
						}
						else
						{
							$percentage = (float)((int)$cumulated[$key]/$numrows);
						}
					}
					$result_array["variables"][$key] = array("title" => $value->title, "selected" => (int)$cumulated[$key], "percentage" => $percentage);
				}
				break;
			case "qt_ordinal":
				$prefix = "";
				if (strcmp(key($cumulated), "") != 0)
				{
					$prefix = (key($cumulated)+1) . " - ";
				}
				$result_array["MODE"] =  $prefix . $variables[key($cumulated)]->title;
				$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
				foreach ($variables as $key => $value)
				{
					$percentage = 0;
					if ($numrows > 0)
					{
						$percentage = (float)((int)$cumulated[$key]/$numrows);
					}
					$result_array["variables"][$key] = array("title" => $value->title, "selected" => (int)$cumulated[$key], "percentage" => $percentage);
				}
				ksort($cumulated, SORT_NUMERIC);
				$median = array();
				$total = 0;
				foreach ($cumulated as $value => $key)
				{
					$total += $key;
					for ($i = 0; $i < $key; $i++)
					{
						array_push($median, $value+1);
					}
				}
				if ($total > 0)
				{
					if (($total % 2) == 0)
					{
						$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
						if (round($median_value) != $median_value)
						{
							$median_value = $median_value . "<br />" . "(" . $this->lng->txt("median_between") . " " . (floor($median_value)) . "-" . $variables[floor($median_value)-1]->title . " " . $this->lng->txt("and") . " " . (ceil($median_value)) . "-" . $variables[ceil($median_value)-1]->title . ")";
						}
					}
					else
					{
						$median_value = $median[(($total+1)/2)-1];
					}
				}
				else
				{
					$median_value = "";
				}
				$result_array["ARITHMETIC_MEAN"] = "";
				$result_array["MEDIAN"] = $median_value;
				$result_array["QUESTION_TYPE"] = $questions[$question_id]["type_tag"];
				break;
			case "qt_metric":
				$result_array["MODE"] = key($cumulated);
				$result_array["MODE_NR_OF_SELECTIONS"] = $cumulated[key($cumulated)];
				ksort($cumulated, SORT_NUMERIC);
				$counter = 0;
				foreach ($cumulated as $value => $nr_of_users)
				{
					$percentage = 0;
					if ($numrows > 0)
					{
						$percentage = (float)($nr_of_users/$numrows);
					}
					$result_array["values"][$counter++] = array("value" => $value, "selected" => (int)$nr_of_users, "percentage" => $percentage);
				}
				$median = array();
				$total = 0;
				$x_i = 0;
				$p_i = 1;
				$x_i_inv = 0;
				$sum_part_zero = false;
				foreach ($cumulated as $value => $key)
				{
					$total += $key;
					for ($i = 0; $i < $key; $i++)
					{
						array_push($median, $value);
						$x_i += $value;
						$p_i *= $value;
						if ($value != 0)
						{
							$sum_part_zero = true;
							$x_i_inv += 1/$value;
						}
					}
				}
				if ($total > 0)
				{
					if (($total % 2) == 0)
					{
						$median_value = 0.5 * ($median[($total/2)-1] + $median[($total/2)]);
					}
					else
					{
						$median_value = $median[(($total+1)/2)-1];
					}
				}
				else
				{
					$median_value = "";
				}
				if ($total > 0)
				{
					if (($x_i/$total) == (int)($x_i/$total))
					{
						$result_array["ARITHMETIC_MEAN"] = $x_i/$total;
					}
					else
					{
						$result_array["ARITHMETIC_MEAN"] = sprintf("%.2f", $x_i/$total);
					}
				}
				else
				{
					$result_array["ARITHMETIC_MEAN"] = "";
				}
				$result_array["MEDIAN"] = $median_value;
				$result_array["QUESTION_TYPE"] = $questions[$question_id]["type_tag"];
				break;
			case "qt_text":
				$result_array["ARITHMETIC_MEAN"] = "";
				$result_array["MEDIAN"] = "";
				$result_array["MODE"] = "";
				$result_array["MODE_NR_OF_SELECTIONS"] = "";
				$result_array["QUESTION_TYPE"] = $questions[$question_id]["type_tag"];
				$result_array["textvalues"] = $textvalues;
				break;
		}
		return $result_array;
	}

	function &getQuestions($question_ids)
	{
		$result_array = array();
		$query = "SELECT survey_question.*, survey_questiontype.type_tag FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id AND survey_question.question_id IN (" . join($question_ids, ",") . ")";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($result_array, $row);
		}
		return $result_array;
	}
	
	function &getQuestionblocks($questionblock_ids)
	{
		$result_array = array();
    $query = "SELECT survey_questionblock.*, survey_survey.obj_fi, survey_question.title AS questiontitle, survey_survey_question.sequence, object_data.title as surveytitle, survey_question.question_id FROM object_reference, object_data, survey_questionblock, survey_questionblock_question, survey_survey, survey_question, survey_survey_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_survey.survey_id = survey_questionblock_question.survey_fi AND survey_questionblock_question.question_fi = survey_question.question_id AND survey_survey.obj_fi = object_reference.obj_id AND object_reference.obj_id = object_data.obj_id AND survey_survey_question.survey_fi = survey_survey.survey_id AND survey_survey_question.question_fi = survey_question.question_id AND survey_questionblock.questionblock_id IN (" . join($questionblock_ids, ",") . ") ORDER BY survey_survey.survey_id, survey_survey_question.sequence";
		$result = $this->ilias->db->query($query);
		while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			if ($row["questionblock_id"] != $qbid)
			{
				$sequence = 1;
			}
			$row["sequence"] = $sequence++;
			$result_array[$row["questionblock_id"]][$row["question_id"]] = $row;
			$qbid = $row["questionblock_id"];
		}
		return $result_array;
	}

	function &getForbiddenQuestionpools()
	{
		global $rbacsystem;
		
		// get all available questionpools and remove the trashed questionspools
		$forbidden_pools = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'spl'";
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
* Calculates the data for the output of the question browser
*
* Calculates the data for the output of the question browser
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
					$where = " AND survey_question.title LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "description":
					$where = " AND survey_question.description LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
				case "author":
					$where = " AND survey_question.author LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
			}
		}
  
		if ($filter_question_type && (strcmp($filter_question_type, "all") != 0))
		{
			$where .= " AND survey_questiontype.type_tag = " . $this->ilias->db->quote($filter_question_type);
		}
		
		if ($filter_questionpool && (strcmp($filter_questionpool, "all") != 0))
		{
			$where .= " AND survey_question.obj_fi = $filter_questionpool";
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
          case "description":
            $order = " ORDER BY description $value";
            $images["description"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
          case "type":
            $order = " ORDER BY questiontype_id $value";
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
            $order = " ORDER BY TIMESTAMP14 $value";
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
		if ($maxentries < 1)
		{
			$maxentries = 9999;
		}

		$forbidden_pools =& $this->getForbiddenQuestionpools();
		$forbidden = "";
		if (count($forbidden_pools))
		{
			$forbidden = " AND survey_question.obj_fi NOT IN (" . join($forbidden_pools, ",") . ")";
		}
		if ($completeonly)
		{
			$forbidden .= " AND survey_question.complete = " . $this->ilias->db->quote("1");
		}

		$existing = "";
		$existing_questions =& $this->getExistingQuestions();
		if (count($existing_questions))
		{
			$existing = " AND survey_question.question_id NOT IN (" . join($existing_questions, ",") . ")";
		}
	  $query = "SELECT survey_question.question_id, survey_question.TIMESTAMP+0 AS TIMESTAMP14 FROM survey_question, survey_questiontype WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id$forbidden$existing AND ISNULL(survey_question.original_id) " . " $where$order$limit";
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
	  $query = "SELECT survey_question.*, survey_question.TIMESTAMP+0 AS TIMESTAMP14, survey_questiontype.type_tag, object_reference.ref_id FROM survey_question, survey_questiontype, object_reference WHERE survey_question.questiontype_fi = survey_questiontype.questiontype_id$forbidden$existing AND survey_question.obj_fi = object_reference.obj_id AND ISNULL(survey_question.original_id) " . " $where$order$limit";
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
* Calculates the data for the output of the questionblock browser
*
* Calculates the data for the output of the questionblock browser
*
* @access public
*/
	function getQuestionblocksTable($sortoptions, $filter_text, $sel_filter_type, $startrow = 0)
	{
		global $ilUser;
		$where = "";
		if (strlen($filter_text) > 0) {
			switch($sel_filter_type) {
				case "title":
					$where = " AND survey_questionblock.title LIKE " . $this->ilias->db->quote("%" . $filter_text . "%");
					break;
			}
		}
  
    // build sort order for sql query
		$order = "";
		$images = array();
    if (count($sortoptions)) {
      foreach ($sortoptions as $key => $value) {
        switch($key) {
          case "title":
						$order = " ORDER BY survey_questionblock.title $value";
            $images["title"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
            break;
					case "svy":
						$order = " ORDER BY survey_survey_question.survey_fi $value";
            $images["svy"] = " <img src=\"" . ilUtil::getImagePath(strtolower($value) . "_order.png", true) . "\" alt=\"" . strtolower($value) . "ending order\" />";
						break;
        }
      }
    }
		$maxentries = $ilUser->prefs["hits_per_page"];
		if ($order)
		{
			$order .=  ",survey_survey_question.sequence ASC";
		}
		else
		{
			$order = " ORDER BY survey_survey_question.sequence ASC";
		}
		$query = "SELECT survey_questionblock.questionblock_id FROM object_reference, object_data, survey_questionblock, survey_questionblock_question, survey_survey, survey_question, survey_survey_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_survey.survey_id = survey_questionblock_question.survey_fi AND survey_questionblock_question.question_fi = survey_question.question_id AND survey_survey.obj_fi = object_reference.obj_id AND object_reference.obj_id = object_data.obj_id AND survey_survey_question.survey_fi = survey_survey.survey_id AND survey_survey_question.question_fi = survey_question.question_id$where GROUP BY survey_questionblock.questionblock_id$order$limit";
    $query_result = $this->ilias->db->query($query);
		$questionblock_ids = array();
		if ($query_result->numRows())
		{
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($questionblock_ids, $row["questionblock_id"]);
			}
		}
		
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
		$query = "SELECT survey_questionblock.*, object_data.title as surveytitle FROM object_reference, object_data, survey_questionblock, survey_questionblock_question, survey_survey, survey_question, survey_survey_question WHERE survey_questionblock.questionblock_id = survey_questionblock_question.questionblock_fi AND survey_survey.survey_id = survey_questionblock_question.survey_fi AND survey_questionblock_question.question_fi = survey_question.question_id AND survey_survey.obj_fi = object_reference.obj_id AND object_reference.obj_id = object_data.obj_id AND survey_survey_question.survey_fi = survey_survey.survey_id AND survey_survey_question.question_fi = survey_question.question_id$where GROUP BY survey_questionblock.questionblock_id$order$limit";
    $query_result = $this->ilias->db->query($query);
		$rows = array();
		if ($query_result->numRows())
		{
			while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$questions_array =& $this->getQuestionblockQuestions($row["questionblock_id"]);
				$counter = 1;
				foreach ($questions_array as $key => $value)
				{
					$questions_array[$key] = "$counter. $value";
					$counter++;
				}
				$rows[$row["questionblock_id"]] = array(
					"questionblock_id" => $row["questionblock_id"],
					"title" => $row["title"], 
					"surveytitle" => $row["surveytitle"], 
					"questions" => join($questions_array, ", "),
					"owner" => $row["owner_fi"]
				);
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
		$query = "SELECT * FROM survey_questiontype ORDER BY type_tag";
		$query_result = $ilDB->query($query);
		while ($row = $query_result->fetchRow(DB_FETCHMODE_ASSOC))
		{
			array_push($questiontypes, $row["type_tag"]);
		}
		return $questiontypes;
	}
		
	/**
	* Returns a QTI xml representation of the survey
	*
	* Returns a QTI xml representation of the survey
	*
	* @return string The QTI xml representation of the survey
	* @access public
	*/
	function to_xml()
	{
		$xml_header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<questestinterop></questestinterop>\n";
		$domxml = domxml_open_mem($xml_header);
		$root = $domxml->document_element();
		// qti assessment
		$qtiSurvey = $domxml->create_element("survey");
		$qtiSurvey->set_attribute("ident", $this->getSurveyId());
		$qtiSurvey->set_attribute("title", $this->getTitle());
		
		// add qti comment
		$qtiComment = $domxml->create_element("qticomment");
		$qtiCommentText = $domxml->create_text_node($this->getDescription());
		$qtiComment->append_child($qtiCommentText);
		$qtiSurvey->append_child($qtiComment);
		$qtiComment = $domxml->create_element("qticomment");
		$qtiCommentText = $domxml->create_text_node("ILIAS Version=".$this->ilias->getSetting("ilias_version"));
		$qtiComment->append_child($qtiCommentText);
		$qtiSurvey->append_child($qtiComment);
		$qtiComment = $domxml->create_element("qticomment");
		$qtiCommentText = $domxml->create_text_node("Author=".$this->getAuthor());
		$qtiComment->append_child($qtiCommentText);
		$qtiSurvey->append_child($qtiComment);
		// add qti objectives
		$qtiObjectives = $domxml->create_element("objectives");
		$qtiMaterial = $domxml->create_element("material");
		$qtiMaterial->set_attribute("label", "introduction");
		$qtiMatText = $domxml->create_element("mattext");
		$qtiMatTextText = $domxml->create_text_node($this->getIntroduction());
		$qtiMatText->append_child($qtiMatTextText);
		$qtiMaterial->append_child($qtiMatText);
		$qtiObjectives->append_child($qtiMaterial);
		$qtiSurvey->append_child($qtiObjectives);
		// add the rest of the preferences in qtimetadata tags, because there is no correspondent definition in QTI
		$qtiMetadata = $domxml->create_element("qtimetadata");
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
		// description
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("description");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node($this->getDescription());
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// evaluation access
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("evaluation_access");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node($this->getEvaluationAccess());
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// anonymization
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("anonymize");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node($this->getAnonymize());
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// status
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("status");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node($this->getStatus());
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// start date
		if ($this->getStartDateEnabled())
		{
			$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
			$qtiFieldLabel = $domxml->create_element("fieldlabel");
			$qtiFieldLabelText = $domxml->create_text_node("startdate");
			$qtiFieldLabel->append_child($qtiFieldLabelText);
			$qtiFieldEntry = $domxml->create_element("fieldentry");
			$qtiFieldEntryText = $domxml->create_text_node(sprintf("P%dY%dM%dDT0H0M0S", $this->getStartYear(), $this->getStartMonth(), $this->getStartDay()));
			$qtiFieldEntry->append_child($qtiFieldEntryText);
			$qtiMetadatafield->append_child($qtiFieldLabel);
			$qtiMetadatafield->append_child($qtiFieldEntry);
			$qtiMetadata->append_child($qtiMetadatafield);
		}
		// end date
		if ($this->getEndDateEnabled())
		{
			$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
			$qtiFieldLabel = $domxml->create_element("fieldlabel");
			$qtiFieldLabelText = $domxml->create_text_node("enddate");
			$qtiFieldLabel->append_child($qtiFieldLabelText);
			$qtiFieldEntry = $domxml->create_element("fieldentry");
			$qtiFieldEntryText = $domxml->create_text_node(sprintf("P%dY%dM%dDT0H0M0S", $this->getEndYear(), $this->getEndMonth(), $this->getEndDay()));
			$qtiFieldEntry->append_child($qtiFieldEntryText);
			$qtiMetadatafield->append_child($qtiFieldLabel);
			$qtiMetadatafield->append_child($qtiFieldEntry);
			$qtiMetadata->append_child($qtiMetadatafield);
		}
		// show question titles
		$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
		$qtiFieldLabel = $domxml->create_element("fieldlabel");
		$qtiFieldLabelText = $domxml->create_text_node("display_question_titles");
		$qtiFieldLabel->append_child($qtiFieldLabelText);
		$qtiFieldEntry = $domxml->create_element("fieldentry");
		$qtiFieldEntryText = $domxml->create_text_node($this->getShowQuestionTitles());
		$qtiFieldEntry->append_child($qtiFieldEntryText);
		$qtiMetadatafield->append_child($qtiFieldLabel);
		$qtiMetadatafield->append_child($qtiFieldEntry);
		$qtiMetadata->append_child($qtiMetadatafield);
		// add questionblock descriptions
		$pages =& $this->getSurveyPages();
		foreach ($pages as $question_array)
		{
			if (count($question_array) > 1)
			{
				$question_ids = array();
				// found a questionblock
				foreach ($question_array as $question)
				{
					array_push($question_ids, $question["question_id"]);
				}
				$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
				$qtiFieldLabel = $domxml->create_element("fieldlabel");
				$qtiFieldLabelText = $domxml->create_text_node("questionblock_" . $question_array[0]["questionblock_id"]);
				$qtiFieldLabel->append_child($qtiFieldLabelText);
				$qtiFieldEntry = $domxml->create_element("fieldentry");
				$qtiFieldEntryText = $domxml->create_text_node("<title>" . $question["questionblock_title"]. "</title><questions>" . join($question_ids, ",") . "</questions>");
				$qtiFieldEntry->append_child($qtiFieldEntryText);
				$qtiMetadatafield->append_child($qtiFieldLabel);
				$qtiMetadatafield->append_child($qtiFieldEntry);
				$qtiMetadata->append_child($qtiMetadatafield);				
			}
		}
		// add constraints
		foreach ($pages as $question_array)
		{
			foreach ($question_array as $question)
			{
				if (count($question["constraints"]))
				{
					// found constraints
					foreach ($question["constraints"] as $constraint)
					{
						$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
						$qtiFieldLabel = $domxml->create_element("fieldlabel");
						$qtiFieldLabelText = $domxml->create_text_node("constraint_" . $question["question_id"]);
						$qtiFieldLabel->append_child($qtiFieldLabelText);
						$qtiFieldEntry = $domxml->create_element("fieldentry");
						$qtiFieldEntryText = $domxml->create_text_node($constraint["question"] . "," . $constraint["short"] . "," . $constraint["value"]);
						$qtiFieldEntry->append_child($qtiFieldEntryText);
						$qtiMetadatafield->append_child($qtiFieldLabel);
						$qtiMetadatafield->append_child($qtiFieldEntry);
						$qtiMetadata->append_child($qtiMetadatafield);				
					}
				}
			}
		}
		// add headings
		foreach ($pages as $question_array)
		{
			foreach ($question_array as $question)
			{
				if ($question["heading"])
				{
					$qtiMetadatafield = $domxml->create_element("qtimetadatafield");
					$qtiFieldLabel = $domxml->create_element("fieldlabel");
					$qtiFieldLabelText = $domxml->create_text_node("heading_" . $question["question_id"]);
					$qtiFieldLabel->append_child($qtiFieldLabelText);
					$qtiFieldEntry = $domxml->create_element("fieldentry");
					$qtiFieldEntryText = $domxml->create_text_node($question["heading"]);
					$qtiFieldEntry->append_child($qtiFieldEntryText);
					$qtiMetadatafield->append_child($qtiFieldLabel);
					$qtiMetadatafield->append_child($qtiFieldEntry);
					$qtiMetadata->append_child($qtiMetadatafield);				
				}
			}
		}
		$qtiSurvey->append_child($qtiMetadata);
		$root->append_child($qtiSurvey);
		$xml = $domxml->dump_mem(true);
		$domxml->free();
		$obligatory_states =& $this->getObligatoryStates();
		foreach ($this->questions as $question_id) {
			$question =& $this->_instanciateQuestion($question_id);
			$qti_question = $question->to_xml(false, $obligatory_states[$question_id]);
			$qti_question = preg_replace("/<questestinterop>/", "", $qti_question);
			$qti_question = preg_replace("/<\/questestinterop>/", "", $qti_question);
			$xml = str_replace("</questestinterop>", "$qti_question</questestinterop>", $xml);
		}
		return $xml;
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
  function &_instanciateQuestion($question_id) {
      $question_type = SurveyQuestion::_getQuestionType($question_id);
      switch ($question_type) {
				case "qt_nominal":
					$question = new SurveyNominalQuestion();
					break;
				case "qt_ordinal":
					$question = new SurveyOrdinalQuestion();
					break;
				case "qt_metric":
					$question = new SurveyMetricQuestion();
					break;
				case "qt_text":
					$question = new SurveyTextQuestion();
					break;
			}
      $question->loadFromDb($question_id);
			return $question;
  }

	/**
	* Imports a survey from XML into the ILIAS database
	*
	* Imports a survey from XML into the ILIAS database
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function importObject($file_info, $survey_questionpool_id)
	{
		// check if file was uploaded
		$source = $file_info["tmp_name"];
		$error = 0;
		if (($source == 'none') || (!$source) || $file_info["error"] > UPLOAD_ERR_OK)
		{
			$this->ilias->raiseError($this->lng->txt("import_no_file_selected"),$this->ilias->error_obj->MESSAGE);
			$error = 1;
		}
		// check correct file type
		if (!((strcmp($file_info["type"], "text/xml") == 0) || (strcmp($file_info["type"], "application/xml") == 0)))
		{
			$this->ilias->raiseError($this->lng->txt("import_wrong_file_type"),$this->ilias->error_obj->MESSAGE);
			$error = 1;
		}
		if (!$error)
		{
			// import file as a survey
			$import_dir = $this->getImportDirectory();
			$importfile = tempnam($import_dir, "survey_import");
			//move_uploaded_file($source, $importfile);
			ilUtil::moveUploadedFile($source, "survey_import", $importfile);
			$fh = fopen($importfile, "r");
			if (!$fh)
			{
				$this->ilias->raiseError($this->lng->txt("import_error_opening_file"),$this->ilias->error_obj->MESSAGE);
				$error = 1;
				return $error;
			}
			$xml = fread($fh, filesize($importfile));
			$result = fclose($fh);
			unlink($importfile);
			if (!$result)
			{
				$this->ilias->raiseError($this->lng->txt("import_error_closing_file"),$this->ilias->error_obj->MESSAGE);
				$error = 1;
				return $error;
			}
			if (preg_match("/(<survey[^>]*>.*?<\/survey>)/si", $xml, $matches))
			{
				// read survey properties
				$import_results = $this->from_xml($matches[1]);
				if ($import_results === false)
				{
					$this->ilias->raiseError($this->lng->txt("import_error_survey_no_proper_values"),$this->ilias->error_obj->MESSAGE);
					$error = 1;
					return $error;
				}
			}
			else
			{
				$this->ilias->raiseError($this->lng->txt("import_error_survey_no_properties"),$this->ilias->error_obj->MESSAGE);
				$error = 1;
				return $error;
			}
			$question_counter = 0;
			$new_question_ids = array();
			if (preg_match_all("/(<item[^>]*>.*?<\/item>)/si", $xml, $matches))
			{
				foreach ($matches[1] as $index => $item)
				{
					$question = "";
					if (preg_match("/<qticomment>Questiontype\=(.*?)<\/qticomment>/is", $item, $questiontype))
					{
						switch ($questiontype[1])
						{
							case NOMINAL_QUESTION_IDENTIFIER:
								$question = new SurveyNominalQuestion();
								break;
							case ORDINAL_QUESTION_IDENTIFIER:
								$question = new SurveyOrdinalQuestion();
								break;
							case METRIC_QUESTION_IDENTIFIER:
								$question = new SurveyMetricQuestion();
								break;
							case TEXT_QUESTION_IDENTIFIER:
								$question = new SurveyTextQuestion();
								break;
						}
						if ($question)
						{
							$question->from_xml("<questestinterop>$item</questestinterop>");
							if ($import_results !== false)
							{
								$question->setObjId($survey_questionpool_id);
								$question->saveToDb();
								$question_id = $question->duplicate(true);
								$this->questions[$question_counter++] = $question_id;
								if (preg_match("/<item\s+ident\=\"(\d+)\"/", $item, $matches))
								{
									$original_question_id = $matches[1];
									$new_question_ids[$original_question_id] = $question_id;
								}
							}
							else
							{
								$this->ilias->raiseError($this->lng->txt("error_importing_question"), $this->ilias->error_obj->MESSAGE);
							}
						}
					}
				}
			}

			$this->saveToDb();
			// add question blocks
			foreach ($import_results["questionblocks"] as $questionblock)
			{
				foreach ($questionblock["questions"] as $key => $value)
				{
					$questionblock["questions"][$key] = $new_question_ids[$value];
				}
				$this->createQuestionblock($questionblock["title"], $questionblock["questions"]);
			}
			// add constraints
			$relations = $this->getAllRelations(true);
			foreach ($import_results["constraints"] as $constraint)
			{
				$this->addConstraint($new_question_ids[$constraint["for"]], $new_question_ids[$constraint["question"]], $relations[$constraint["relation"]]["id"], $constraint["value"]);
			}
			foreach ($import_results["headings"] as $qid => $heading)
			{
				$this->saveHeading($heading, $new_question_ids[$qid]);
			}
		}
		return $error;
	}

	/**
	* Imports the survey properties from XML into the survey object
	*
	* Imports the survey properties from XML into the survey object
	*
	* @return mixed An array containing the constraints and questionblocks, false otherwise
	* @access public
	*/
	function from_xml($xml_text)
	{
		$result = false;
		$xml_text = preg_replace("/>\s*?</", "><", $xml_text);
		$domxml = domxml_open_mem($xml_text);
		$constraints = array();
		$headings = array();
		$questionblocks = array();
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
						if (strpos($comment, "ILIAS Version=") !== false)
						{
						}
						elseif (strpos($comment, "Questiontype=") !== false)
						{
						}
						elseif (strpos($comment, "Author=") !== false)
						{
							$comment = str_replace("Author=", "", $comment);
							$this->setAuthor($comment);
						}
						else
						{
							$this->setDescription($comment);
						}
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
								case "evaluation_access":
									$this->setEvaluationAccess($fieldentry->get_content());
									break;
								case "author":
									$this->setAuthor($fieldentry->get_content());
									break;
								case "description":
									$this->setDescription($fieldentry->get_content());
									break;
								case "anonymize":
									$this->setAnonymize($fieldentry->get_content());
									break;
								case "startdate":
									$iso8601period = $fieldentry->get_content();
									if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
									{
										$this->setStartDateEnabled(true);
										$this->setStartDate(sprintf("%04d-%02d-%02d", $matches[1], $matches[2], $matches[3]));
									}
									break;
								case "enddate":
									$iso8601period = $fieldentry->get_content();
									if (preg_match("/P(\d+)Y(\d+)M(\d+)DT(\d+)H(\d+)M(\d+)S/", $iso8601period, $matches))
									{
										$this->setEndDateEnabled(true);
										$this->setEndDate(sprintf("%04d-%02d-%02d", $matches[1], $matches[2], $matches[3]));
									}
									break;
								case "status":
									$this->setStatus($fieldentry->get_content());
									break;
								case "display_question_titles":
									if ($fieldentry->get_content() == QUESTIONTITLES_HIDDEN)
									{
										$this->hideQuestionTitles();
									}
									else
									{
										$this->showQuestionTitles();
									}
							}
							if (preg_match("/questionblock_\d+/", $fieldlabel->get_content()))
							{
								$qb = $fieldentry->get_content();
								preg_match("/<title>(.*?)<\/title>/", $qb, $matches);
								$qb_title = $matches[1];
								preg_match("/<questions>(.*?)<\/questions>/", $qb, $matches);
								$qb_questions = $matches[1];
								$qb_questions_array = explode(",", $qb_questions);
								array_push($questionblocks, array(
									"title" => $qb_title,
									"questions" => $qb_questions_array
								));
							}
							if (preg_match("/constraint_(\d+)/", $fieldlabel->get_content(), $matches))
							{
								$constraint = $fieldentry->get_content();
								$constraint_array = explode(",", $constraint);
								if (count($constraint_array) == 3)
								{
									array_push($constraints, array(
										"for"      => $matches[1], 
										"question" => $constraint_array[0],
										"relation" => $constraint_array[1],
										"value"    => $constraint_array[2]
									));
								}
							}
							if (preg_match("/heading_(\d+)/", $fieldlabel->get_content(), $matches))
							{
								$heading = $fieldentry->get_content();
								$headings[$matches[1]] = $heading;
							}
						}
						break;
				}
			}
			$result["questionblocks"] = $questionblocks;
			$result["constraints"] = $constraints;
			$result["headings"] = $headings;
		}
		return $result;
	}
	
	/**
	* Set the title and the description of the meta data
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
* Returns the available surveys for the active user
* 
* Returns the available surveys for the active user
*
* @return array The available surveys
* @access public
*/
	function &_getAvailableSurveys($use_object_id = false)
	{
		global $rbacsystem;
		global $ilDB;
		
		$result_array = array();
		$query = "SELECT object_data.*, object_data.obj_id, object_reference.ref_id FROM object_data, object_reference WHERE object_data.obj_id = object_reference.obj_id AND object_data.type = 'svy' ORDER BY object_data.title";
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
* Creates a 1:1 copy of the object and places the copy in a given repository
* 
* Creates a 1:1 copy of the object and places the copy in a given repository
*
* @access public
*/
	function _clone($obj_id)
	{
		global $ilDB;
		
		$original = new ilObjSurvey($obj_id, false);
		$original->loadFromDb();
		
		$newObj = new ilObjSurvey();
		$newObj->setType("svy");
		$newObj->setTitle($original->getTitle());
		$newObj->setDescription($original->getDescription());
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
//		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());
		
		$newObj->$author = $original->getAuthor();
		$newObj->introduction = $original->getIntroduction();
		$newObj->status = $original->getStatus();
		$newObj->evaluation_access = $original->getEvaluationAccess();
		$newObj->start_date = $original->getStartDate();
		$newObj->startdate_enabled = $original->getStartDateEnabled();
		$newObj->end_date = $original->getEndDate();
		$newObj->enddate_enabled = $original->getEndDateEnabled();
		$newObj->invitation = $original->getInvitation();
		$newObj->invitation_mode = $original->getInvitationMode();
		$newObj->anonymize = $original->getAnonymize();

		$question_pointer = array();
		// clone the questions
		foreach ($original->questions as $key => $question_id)
		{
			$question = ilObjSurvey::_instanciateQuestion($question_id);
			$question->id = -1;
			$original_id = SurveyQuestion::_getOriginalId($question_id);
			$question->saveToDb($original_id);
			$newObj->questions[$key] = $question->getId();
			$question_pointer[$question_id] = $question->getId();
		}

		$newObj->saveToDb();		

		// clone the questionblocks
		$questionblocks = array();
		$questionblock_questions = array();
		$query = sprintf("SELECT * FROM survey_questionblock_question WHERE survey_fi = %s",
			$this->ilias->db->quote($original->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() > 0)
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($questionblock_questions, $row);
				$questionblocks[$row["questionblock_fi"]] = $row["questionblock_fi"];
			}
		}
		// create new questionblocks
		foreach ($questionblocks as $key => $value)
		{
			$questionblock = ilObjSurvey::_getQuestionblock($key);
			$questionblock_id = ilObjSurvey::_addQuestionblock($questionblock["title"], $questionblock["owner_fi"]);
			$questionblocks[$key] = $questionblock_id;
		}
		// create new questionblock questions
		foreach ($questionblock_questions as $key => $value)
		{
			$clonequery = sprintf("INSERT INTO survey_questionblock_question (questionblock_question_id, survey_fi, questionblock_fi, question_fi) VALUES (NULL, %s, %s, %s)",
				$ilDB->quote($newObj->getSurveyId() . ""),
				$ilDB->quote($questionblocks[$value["questionblock_fi"]] . ""),
				$ilDB->quote($question_pointer[$value["question_fi"]] . "")
			);
			$cloneresult = $this->ilias->db->query($clonequery);
		}
		
		// clone the constraints
		$constraints = ilObjSurvey::_getConstraints($original->getSurveyId());
		foreach ($constraints as $key => $constraint)
		{
			$newObj->addConstraint($question_pointer[$constraint["for_question"]], $question_pointer[$constraint["question"]], $constraint["relation_id"], $constraint["value"]);
		}
		
		// clone the obligatory states
		$query = sprintf("SELECT * FROM survey_question_obligatory WHERE survey_fi = %s",
			$this->ilias->db->quote($original->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() > 0)
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				$clonequery = sprintf("INSERT INTO survey_question_obligatory (question_obligatory_id, survey_fi, question_fi, obligatory, TIMESTAMP) VALUES (NULL, %s, %s, %s, NULL)",
					$this->ilias->db->quote($newObj->getSurveyId() . ""),
					$this->ilias->db->quote($question_pointer[$row["question_fi"]] . ""),
					$this->ilias->db->quote($row["obligatory"])
				);
				$cloneresult = $this->ilias->db->query($clonequery);
			}
		}

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
	
	/**
	* creates data directory for export files
	* (data_dir/svy_data/svy_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		$svy_data_dir = ilUtil::getDataDir()."/svy_data";
		ilUtil::makeDir($svy_data_dir);
		if(!is_writable($svy_data_dir))
		{
			$this->ilias->raiseError("Survey Data Directory (".$svy_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}
		
		// create learning module directory (data_dir/lm_data/lm_<id>)
		$svy_dir = $svy_data_dir."/svy_".$this->getId();
		ilUtil::makeDir($svy_dir);
		if(!@is_dir($svy_dir))
		{
			$this->ilias->raiseError("Creation of Survey Directory failed.",$this->ilias->error_obj->FATAL);
		}
		// create Export subdirectory (data_dir/lm_data/lm_<id>/Export)
		$export_dir = $svy_dir."/export";
		ilUtil::makeDir($export_dir);
		if(!@is_dir($export_dir))
		{
			$this->ilias->raiseError("Creation of Export Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get export directory of survey
	*/
	function getExportDirectory()
	{
		$export_dir = ilUtil::getDataDir()."/svy_data"."/svy_".$this->getId()."/export";

		return $export_dir;
	}
	
	/**
	* get export files
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
				substr($entry, -4) == ".xml" and
				ereg("^[0-9]{10}_{2}[0-9]+_{2}(survey__)*[0-9]+\.xml\$", $entry))
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
	* (data_dir/svy_data/svy_<id>/import, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createImportDirectory()
	{
		$svy_data_dir = ilUtil::getDataDir()."/svy_data";
		ilUtil::makeDir($svy_data_dir);
		
		if(!is_writable($svy_data_dir))
		{
			$this->ilias->raiseError("Survey Data Directory (".$svy_data_dir
				.") not writeable.",$this->ilias->error_obj->FATAL);
		}

		// create test directory (data_dir/svy_data/svy_<id>)
		$svy_dir = $svy_data_dir."/svy_".$this->getId();
		ilUtil::makeDir($svy_dir);
		if(!@is_dir($svy_dir))
		{
			$this->ilias->raiseError("Creation of Survey Directory failed.",$this->ilias->error_obj->FATAL);
		}

		// create import subdirectory (data_dir/svy_data/svy_<id>/import)
		$import_dir = $svy_dir."/import";
		ilUtil::makeDir($import_dir);
		if(!@is_dir($import_dir))
		{
			$this->ilias->raiseError("Creation of Import Directory failed.",$this->ilias->error_obj->FATAL);
		}
	}

	/**
	* get import directory of survey
	*/
	function getImportDirectory()
	{
		$import_dir = ilUtil::getDataDir()."/svy_data".
			"/svy_".$this->getId()."/import";
		if (!is_dir($import_dir))
		{
			ilUtil::makeDirParents($import_dir);
		}
		if(@is_dir($import_dir))
		{
			return $import_dir;
		}
		else
		{
			return false;
		}
	}
	
	function saveHeading($heading = "", $insertbefore)
	{
		if ($heading)
		{
			$query = sprintf("UPDATE survey_survey_question SET heading=%s WHERE survey_fi=%s AND question_fi=%s",
				$this->ilias->db->quote($heading),
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($insertbefore)
			);
		}
		else
		{
			$query = sprintf("UPDATE survey_survey_question SET heading=NULL WHERE survey_fi=%s AND question_fi=%s",
				$this->ilias->db->quote($this->getSurveyId() . ""),
				$this->ilias->db->quote($insertbefore)
			);
		}
		$this->ilias->db->query($query);
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
	
	function isAnonymousKey($key)
	{
		$query = sprintf("SELECT anonymous_id FROM survey_anonymous WHERE survey_key = %s AND survey_fi = %s",
			$this->ilias->db->quote($key . ""),
			$this->ilias->db->quote($this->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		if ($result->numRows() == 1)
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
	function getUserSurveyCode()
	{
		global $ilUser;
		return md5($ilUser->id . $this->getSurveyId());
	}
	
	function checkSurveyCode($code)
	{
		global $ilUser;
		// check for the correct survey code
		if (strcmp($ilUser->login, "anonymous") != 0)
		{
			$anonymize_key = $this->getUserSurveyCode();
			if (strcmp(strtolower($anonymize_key), strtolower($code)) == 0)
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			if ($this->isAnonymousKey($code))
			{
				if ($this->isSurveyStarted("", $code) == 1)
				{
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				return false;
			}
		}
		return false;
	}

	function &getSurveyCodes()
	{
		$codes = array();
		$query = sprintf("SELECT survey_anonymous.anonymous_id, survey_anonymous.survey_key, survey_anonymous.survey_fi, survey_anonymous.TIMESTAMP+0 AS TIMESTAMP14, survey_finished.state FROM survey_anonymous LEFT JOIN survey_finished ON survey_anonymous.survey_key = survey_finished.anonymous_id WHERE survey_anonymous.survey_fi = %s ORDER BY TIMESTAMP14",
			$this->ilias->db->quote($this->getSurveyId() . "")
		);
		$result = $this->ilias->db->query($query);
		
		if ($result->numRows() > 0)
		{
			while ($row = $result->fetchRow(DB_FETCHMODE_ASSOC))
			{
				array_push($codes, $row);
			}
		}
		return $codes;
	}
	
	function createSurveyCodes($nrOfCodes)
	{
		for ($i = 0; $i < $nrOfCodes; $i++)
		{
			$anonymize_key = md5((time() + ($i*$nrOfCodes)) . $this->getSurveyId());
			$query = sprintf("INSERT INTO survey_anonymous (anonymous_id, survey_key, survey_fi, TIMESTAMP) VALUES (NULL, %s, %s, NULL)",
				$this->ilias->db->quote($anonymize_key . ""),
				$this->ilias->db->quote($this->getSurveyId() . "")
			);
			$result = $this->ilias->db->query($query);
		}
	}
	
	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target)
	{
		global $rbacsystem, $ilErr, $lng;

		if ($rbacsystem->checkAccess("read", $a_target))
		{
			ilUtil::redirect("survey/survey.php?cmd=run&ref_id=$a_target");
		}
		else
		{
			$ilErr->raiseError($lng->txt("msg_no_perm_read_lm"), $ilErr->FATAL);
		}
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
} // END class.ilObjSurvey
?>
