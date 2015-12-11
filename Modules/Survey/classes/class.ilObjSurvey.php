<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObject.php";

/**
* Class ilObjSurvey
* 
* @author		Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @extends ilObject
* @defgroup ModulesSurvey Modules/Survey
*/
class ilObjSurvey extends ilObject
{
	const STATUS_OFFLINE = 0;
	const STATUS_ONLINE = 1;	
	
	const EVALUATION_ACCESS_OFF = 0;
	const EVALUATION_ACCESS_ALL = 1;
	const EVALUATION_ACCESS_PARTICIPANTS = 2;
	
	const INVITATION_OFF = 0;
	const INVITATION_ON = 1;
	
	const MODE_UNLIMITED = 0;
	const MODE_PREDEFINED_USERS = 1;
	
	const ANONYMIZE_OFF = 0; // personalized, no codes
	const ANONYMIZE_ON = 1; // anonymized, codes
	const ANONYMIZE_FREEACCESS = 2; // anonymized, no codes
	const ANONYMIZE_CODE_ALL = 3; // personalized, codes
	
	const QUESTIONTITLES_HIDDEN = 0;
	const QUESTIONTITLES_VISIBLE = 1;	
	
	/**
	* A unique positive numerical ID which identifies the survey.
	* This is the primary key from a database table.
	*
	* @var integer
	*/
	var $survey_id;

	/**
	* A text representation of the authors name. The name of the author must
	* not necessary be the name of the owner.
	*
	* @var string
	*/
	var $author;

	/**
	* A text representation of the surveys introduction.
	*
	* @var string
	*/
	var $introduction;

	/**
	* A text representation of the surveys outro.
	*
	* @var string
	*/
	var $outro;

	/**
	* Survey status (online/offline)
	*
	* @var integer
	*/
	var $status;

	/**
	* Indicates the evaluation access for learners
	*
	* @var string
	*/
	var $evaluation_access;

	/**
	* The start date of the survey
	*
	* @var string
	*/
	var $start_date;

	/**
	* The end date of the survey
	*
	* @var string
	*/
	var $end_date;

	/**
	* The questions contained in this survey
	*
	* @var array
	*/
	var $questions;

	/**
	* Defines if the survey will be places on users personal desktops
	*
	* @var integer
	*/
	var $invitation;

	/**
	* Defines the type of user invitation
	*
	* @var integer
	*/
	var $invitation_mode;
	
	/**
	* Indicates the anonymization of the survey
	* @var integer
	*/
	var $anonymize;

	/**
	* Indicates if the question titles are shown during a query
	* @var integer
	*/
	var $display_question_titles;

	/**
	 * Indicates if a survey code may be exported with the survey statistics
	 *
	 * @var boolean
	 **/
	var $surveyCodeSecurity;
	
	var $mailnotification;
	var $mailaddresses;
	var $mailparticipantdata;
	var $template_id;
	var $pool_usage;
	
	protected $activation_visibility;
	protected $activation_starting_time;
	protected $activation_ending_time;
	
	// 360° 
	protected $mode_360; // [bool]
	protected $mode_360_self_eval; // [bool]
	protected $mode_360_self_appr; // [bool]
	protected $mode_360_self_rate; // [bool]
	protected $mode_360_results; // [int]
	protected $mode_360_skill_service; // [bool]
	
	const RESULTS_360_NONE = 0;
	const RESULTS_360_OWN = 1;
	const RESULTS_360_ALL = 2;
	
	// reminder/notification	
	protected $reminder_status; // [bool]
	protected $reminder_start; // [ilDate]
	protected $reminder_end; // [ilDate]
	protected $reminder_frequency; // [int]
	protected $reminder_target; // [int]
	protected $reminder_last_sent; // [bool]
	protected $tutor_ntf_status; // [bool]
	protected $tutor_ntf_recipients; // [array]
	protected $tutor_ntf_target; // [int]
	
	protected $view_own_results; // [bool]
	protected $mail_own_results; // [bool]
	
	const NOTIFICATION_PARENT_COURSE = 1;
	const NOTIFICATION_INVITED_USERS = 2;
	

	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjSurvey($a_id = 0,$a_call_by_reference = true)
	{
		global $ilUser, $lng;
		
		$this->type = "svy";	
		$this->survey_id = -1;
		$this->introduction = "";
		$this->outro = $lng->txt("survey_finished");
		$this->author = $ilUser->getFullname();
		$this->status = self::STATUS_OFFLINE;
		$this->evaluation_access = self::EVALUATION_ACCESS_OFF;
		$this->questions = array();
		$this->invitation = self::INVITATION_OFF;
		$this->invitation_mode = self::MODE_PREDEFINED_USERS;
		$this->anonymize = self::ANONYMIZE_OFF;
		$this->display_question_titles = self::QUESTIONTITLES_VISIBLE;
		$this->surveyCodeSecurity = TRUE;
		$this->template_id = NULL;
		$this->pool_usage = true;
		
		parent::__construct($a_id,$a_call_by_reference);
	}

	/**
	* create survey object
	*/
	function create($a_upload = false)
	{
		parent::create();
		if(!$a_upload)
		{
			$this->createMetaData();
		}
	}

/**
* Create meta data entry
*
* @access public
*/
	function createMetaData()
	{
		parent::createMetaData();
		$this->saveAuthorToMetadata();
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

	function createReference() 
	{
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
	* Adds a question to the survey
	*
	* @param	integer	$question_id The question id of the question
	* @access	public
	*/
	function addQuestion($question_id)
	{
		array_push($this->questions, $question_id);
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

		$this->deleteMetaData();

		// Delete all survey questions, constraints and materials
		foreach ($this->questions as $question_id)
		{
			$this->removeQuestion($question_id);
		}
		$this->deleteSurveyRecord();
		
		ilUtil::delDir($this->getImportDirectory());
		return true;
	}
	
	/**
	* Deletes the survey from the database
	* 
	* @access	public
	*/
	function deleteSurveyRecord()
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_svy WHERE survey_id = %s",
			array('integer'),
			array($this->getSurveyId())
		);

		$result = $ilDB->queryF("SELECT questionblock_fi FROM svy_qblk_qst WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		$questionblocks = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($questionblocks, $row["questionblock_fi"]);
		}
		if (count($questionblocks))
		{
			$affectedRows = $ilDB->manipulate("DELETE FROM svy_qblk WHERE " . $ilDB->in('questionblock_id', $questionblocks, false, 'integer'));
		}
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qblk_qst WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		$this->deleteAllUserData();

		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_anonymous WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		
		// delete export files
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$svy_data_dir = ilUtil::getDataDir()."/svy_data";
		$directory = $svy_data_dir."/svy_".$this->getId();
		if (is_dir($directory))
		{
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			ilUtil::delDir($directory);
		}

		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		$mobs = ilObjMediaObject::_getMobsOfObject("svy:html", $this->getId());
		// remaining usages are not in text anymore -> delete them
		// and media objects (note: delete method of ilObjMediaObject
		// checks whether object is used in another context; if yes,
		// the object is not deleted!)
		foreach($mobs as $mob)
		{
			ilObjMediaObject::_removeUsage($mob, "svy:html", $this->getId());
			$mob_obj =& new ilObjMediaObject($mob);
			$mob_obj->delete();
		}
	}
	
	/**
	* Deletes all user data of a survey
	* 
	* @access	public
	*/
	function deleteAllUserData()
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT finished_id FROM svy_finished WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		$active_array = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($active_array, $row["finished_id"]);
		}

		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_finished WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);

		foreach ($active_array as $active_fi)
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_answer WHERE active_fi = %s",
				array('integer'),
				array($active_fi)
			);
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_times WHERE finished_fi = %s",
				array('integer'),
				array($active_fi)
			);
		}
	}
	
	/**
	* Deletes the user data of a given array of survey participants
	* 
	* @access	public
	*/
	function removeSelectedSurveyResults($finished_ids)
	{
		global $ilDB;
		
		foreach ($finished_ids as $finished_id)
		{
			$result = $ilDB->queryF("SELECT finished_id FROM svy_finished WHERE finished_id = %s",
				array('integer'),
				array($finished_id)
			);
			$row = $ilDB->fetchAssoc($result);

			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_answer WHERE active_fi = %s",
				array('integer'),
				array($row["finished_id"])
			);

			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_finished WHERE finished_id = %s",
				array('integer'),
				array($finished_id)
			);

			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_times WHERE finished_fi = %s",
				array('integer'),
				array($row["finished_id"])
			);
		}
	}
	
	function &getSurveyParticipants($finished_ids = null)
	{
		global $ilDB;
		
		$sql = "SELECT * FROM svy_finished".
			" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer");
		if($finished_ids)
		{
			$sql .= " AND ".$ilDB->in("finished_id", $finished_ids, "", "integer");
		}
		
		$result = $ilDB->query($sql);
		$participants = array();
		if ($result->numRows() > 0)
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$userdata = $this->getUserDataFromActiveId($row["finished_id"]);
				$userdata["finished"] = (bool)$row["state"];
				$userdata["finished_tstamp"] = $row["tstamp"];
				$participants[$userdata["sortname"] . $userdata["active_id"]] = $userdata;
			}
		}
		return $participants;
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
* Returns 1, if a survey is complete for use
*
* @return boolean 1, if the survey is complete for use, otherwise 0
* @access public
*/
	function isComplete()
	{
		if (($this->getTitle()) and (count($this->questions)))
		{
			return 1;
		} 
			else 
		{
			return 0;
		}
	}

/**
* Returns 1, if a survey is complete for use
*
* @return boolean 1, if the survey is complete for use, otherwise 0
* @access public
*/
	function _isComplete($obj_id)
	{
		$survey = new ilObjSurvey($obj_id, false);
		$survey->loadFromDb();
		if (($survey->getTitle()) and (count($survey->questions)))
		{
			return 1;
		} 
			else 
		{
			return 0;
		}
	}

/**
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
* @access public
*/
	function saveCompletionStatus() 
	{
		global $ilDB;
		
		$complete = 0;
		if ($this->isComplete()) 
		{
			$complete = 1;
		}
    if ($this->getSurveyId() > 0) 
		{
			$affectedRows = $ilDB->manipulateF("UPDATE svy_svy SET complete = %s, tstamp = %s WHERE survey_id = %s",
				array('text','integer','integer'),
				array($this->isComplete(), time(), $this->getSurveyId())
			);
		}
	}

/**
* Takes a question and creates a copy of the question for use in the survey
*
* @param integer $question_id The database id of the question
* @result integer The database id of the copied question
* @access public
*/
	function duplicateQuestionForSurvey($question_id, $a_force = false)
	{
		global $ilUser;
		
		$questiontype = $this->getQuestionType($question_id);
		$question_gui = $this->getQuestionGUI($questiontype, $question_id);

		// check if question is a pool question at all, if not do nothing
		if($this->getId() == $question_gui->object->getObjId() &&  !$a_force)
		{
			return $question_id;
		}

		$duplicate_id = $question_gui->object->duplicate(true);
		return $duplicate_id;
	}

/**
* Inserts a question in the survey and saves the relation to the database
*
* @access public
*/
	function insertQuestion($question_id) 
	{
		global $ilDB;
		
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		if (!SurveyQuestion::_isComplete($question_id))
		{
			return FALSE;
		}
		else
		{
			// get maximum sequence index in test
			$result = $ilDB->queryF("SELECT survey_question_id FROM svy_svy_qst WHERE survey_fi = %s",
				array('integer'),
				array($this->getSurveyId())
			);
			$sequence = $result->numRows();
			$duplicate_id = $this->duplicateQuestionForSurvey($question_id);
			$next_id = $ilDB->nextId('svy_svy_qst');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_svy_qst (survey_question_id, survey_fi, question_fi, sequence, tstamp) VALUES (%s, %s, %s, %s, %s)",
				array('integer', 'integer', 'integer', 'integer', 'integer'),
				array($next_id, $this->getSurveyId(), $duplicate_id, $sequence, time())
			);
			$this->loadQuestionsFromDb();
			return TRUE;
		}
	}


/**
* Inserts a questionblock in the survey and saves the relation to the database
*
* @access public
*/
	function insertQuestionblock($questionblock_id) 
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT svy_qblk.title, svy_qblk.show_questiontext, svy_qblk.show_blocktitle,".
			" svy_qblk_qst.question_fi FROM svy_qblk, svy_qblk_qst, svy_svy_qst".
			" WHERE svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi".
			" AND svy_svy_qst.question_fi = svy_qblk_qst.question_fi".
			" AND svy_qblk.questionblock_id = %s".
			" ORDER BY svy_svy_qst.sequence",
			array('integer'),
			array($questionblock_id)
		);
		$questions = array();
		$show_questiontext = 0;
		$show_blocktitle = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			$duplicate_id = $this->duplicateQuestionForSurvey($row["question_fi"]);
			array_push($questions, $duplicate_id);
			$title = $row["title"];
			$show_questiontext = $row["show_questiontext"];
			$show_blocktitle = $row["show_blocktitle"];
		}
		$this->createQuestionblock($title, $show_questiontext, $show_blocktitle, $questions);
	}
	
	/**
	* Returns the content of all RTE enabled text areas in the test
	*
	* @access private
	*/
	function getAllRTEContent()
	{
		$result = array();
		array_push($result, $this->getIntroduction());
		array_push($result, $this->getOutro());
		return $result;
	}
	
	/**
	* Cleans up the media objects for all text fields in a test which are using an RTE field
	*
	* @access private
	*/
	function cleanupMediaobjectUsage()
	{
		include_once("./Services/RTE/classes/class.ilRTE.php");
		$completecontent = "";
		foreach ($this->getAllRTEContent() as $content)
		{
			$completecontent .= $content;
		}
		ilRTE::_cleanupMediaObjectUsage($completecontent, $this->getType() . ":html",
			$this->getId());
	}
	
	public function saveUserSettings($usr_id, $key, $title, $value)
	{
		global $ilDB;
		
		$next_id = $ilDB->nextId('svy_settings');
		$affectedRows = $ilDB->insert("svy_settings", array(
			"settings_id" => array("integer", $next_id),
			"usr_id" => array("integer", $usr_id),
			"keyword" => array("text", $key),
			"title" => array("text", $title),
			"value" => array("clob", $value)
		));
	}
	
	public function deleteUserSettings($id)
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_settings WHERE settings_id = %s",
			array('integer'),
			array($id)
		);
		return $affectedRows;
	}
	
	public function getUserSettings($usr_id, $key)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM svy_settings WHERE usr_id = %s AND keyword = %s",
			array('integer', 'text'),
			array($usr_id, $key)
		);
		$found = array();
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$found[$row['settings_id']] = $row;
			}
		}
		return $found;
	}

/**
* Saves a survey object to a database
*
* @access public
*/
	function saveToDb()
	{
		global $ilDB;
		
		// date handling
		$rmd_start = $this->getReminderStart();
		if(is_object($rmd_start))
		{
			$rmd_start = $rmd_start->get(IL_CAL_DATE);
		}
		$rmd_end = $this->getReminderEnd();
		if(is_object($rmd_end))
		{
			$rmd_end = $rmd_end->get(IL_CAL_DATE);
		}
		
		include_once("./Services/RTE/classes/class.ilRTE.php");
		if ($this->getSurveyId() < 1)
		{
			$next_id = $ilDB->nextId('svy_svy');
			$affectedRows = $ilDB->insert("svy_svy", array(
				"survey_id" => array("integer", $next_id),
				"obj_fi" => array("integer", $this->getId()),
				"author" => array("text", $this->getAuthor()),
				"introduction" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 0)),
				"outro" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getOutro(), 0)),
				"status" => array("text", $this->getStatus()),
				"startdate" => array("text", $this->getStartDate()),
				"enddate" => array("text", $this->getEndDate()),
				"evaluation_access" => array("text", $this->getEvaluationAccess()),
				"invitation" => array("text", $this->getInvitation()),
				"invitation_mode" => array("text", $this->getInvitationMode()),
				"complete" => array("text", $this->isComplete()),
				"created" => array("integer", time()),
				"anonymize" => array("text", $this->getAnonymize()),
				"show_question_titles" => array("text", $this->getShowQuestionTitles()),
				"mailnotification" => array('integer', ($this->getMailNotification()) ? 1 : 0),
				"mailaddresses" => array('text', strlen($this->getMailAddresses()) ? $this->getMailAddresses() : NULL),
				"mailparticipantdata" => array('text', strlen($this->getMailParticipantData()) ? $this->getMailParticipantData() : NULL),
				"tstamp" => array("integer", time()),
				"template_id" => array("integer", $this->getTemplate()),
				"pool_usage" => array("integer", $this->getPoolUsage()),
				// 360°
				"mode_360" => array("integer", $this->get360Mode()),
				"mode_360_self_eval" => array("integer", $this->get360SelfEvaluation()),
				"mode_360_self_rate" => array("integer", $this->get360SelfRaters()),
				"mode_360_self_appr" => array("integer", $this->get360SelfAppraisee()),
				"mode_360_results" => array("integer", $this->get360Results()),
				"mode_360_skill_service" => array("integer", (int) $this->get360SkillService()),
				// reminder/notification
				"reminder_status" => array("integer", (int)$this->getReminderStatus()),
				"reminder_start" => array("datetime", $rmd_start),
				"reminder_end" => array("datetime", $rmd_end),
				"reminder_frequency" => array("integer", (int)$this->getReminderFrequency()),				
				"reminder_target" => array("integer", (int)$this->getReminderTarget()),
				"reminder_last_sent" => array("datetime", $this->getReminderLastSent()),
				"tutor_ntf_status" => array("integer", (int)$this->getTutorNotificationStatus()),
				"tutor_ntf_reci" => array("text", implode(";", (array)$this->getTutorNotificationRecipients())),
				"tutor_ntf_target" => array("integer", (int)$this->getTutorNotificationTarget()),
				"own_results_view" => array("integer", $this->hasViewOwnResults()),
				"own_results_mail" => array("integer", $this->hasMailOwnResults())
 			));
			$this->setSurveyId($next_id);
		}
		else
		{
			$affectedRows = $ilDB->update("svy_svy", array(
				"author" => array("text", $this->getAuthor()),
				"introduction" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 0)),
				"outro" => array("clob", ilRTE::_replaceMediaObjectImageSrc($this->getOutro(), 0)),
				"status" => array("text", $this->getStatus()),
				"startdate" => array("text", $this->getStartDate()),
				"enddate" => array("text", $this->getEndDate()),
				"evaluation_access" => array("text", $this->getEvaluationAccess()),
				"invitation" => array("text", $this->getInvitation()),
				"invitation_mode" => array("text", $this->getInvitationMode()),
				"complete" => array("text", $this->isComplete()),
				"anonymize" => array("text", $this->getAnonymize()),
				"show_question_titles" => array("text", $this->getShowQuestionTitles()),
				"mailnotification" => array('integer', ($this->getMailNotification()) ? 1 : 0),
				"mailaddresses" => array('text', strlen($this->getMailAddresses()) ? $this->getMailAddresses() : NULL),
				"mailparticipantdata" => array('text', strlen($this->getMailParticipantData()) ? $this->getMailParticipantData() : NULL),
				"tstamp" => array("integer", time()),
				"template_id" => array("integer", $this->getTemplate()),
				"pool_usage" => array("integer", $this->getPoolUsage()),
				// 360°
				"mode_360" => array("integer", $this->get360Mode()),
				"mode_360_self_eval" => array("integer", $this->get360SelfEvaluation()),
				"mode_360_self_rate" => array("integer", $this->get360SelfRaters()),
				"mode_360_self_appr" => array("integer", $this->get360SelfAppraisee()),
				"mode_360_results" => array("integer", $this->get360Results()),
				"mode_360_skill_service" => array("integer", (int) $this->get360SkillService()),
				// reminder/notification
				"reminder_status" => array("integer", $this->getReminderStatus()),
				"reminder_start" => array("datetime", $rmd_start),
				"reminder_end" => array("datetime", $rmd_end),
				"reminder_frequency" => array("integer", $this->getReminderFrequency()),
				"reminder_target" => array("integer", $this->getReminderTarget()),
				"reminder_last_sent" => array("datetime", $this->getReminderLastSent()),
				"tutor_ntf_status" => array("integer", $this->getTutorNotificationStatus()),
				"tutor_ntf_reci" => array("text", implode(";", (array)$this->getTutorNotificationRecipients())),
				"tutor_ntf_target" => array("integer", $this->getTutorNotificationTarget()),
				"own_results_view" => array("integer", $this->hasViewOwnResults()),
				"own_results_mail" => array("integer", $this->hasMailOwnResults())
			), array(
			"survey_id" => array("integer", $this->getSurveyId())
			));
		}
		if ($affectedRows)
		{
			// save questions to db
			$this->saveQuestionsToDb();
		}
		
		// moved activation to ilObjectActivation
		if($this->ref_id)
		{
			include_once "./Services/Object/classes/class.ilObjectActivation.php";		
			ilObjectActivation::getItem($this->ref_id);
			
			$item = new ilObjectActivation;			
			if(!$this->isActivationLimited())
			{
				$item->setTimingType(ilObjectActivation::TIMINGS_DEACTIVATED);
			}
			else
			{				
				$item->setTimingType(ilObjectActivation::TIMINGS_ACTIVATION);
				$item->setTimingStart($this->getActivationStartDate());
				$item->setTimingEnd($this->getActivationEndDate());
				$item->toggleVisible($this->getActivationVisibility());
			}						
			
			$item->update($this->ref_id);		
		}
	}

/**
* Saves the survey questions to the database
*
* @access public
* @see $questions
*/
	function saveQuestionsToDb() 
	{
		global $ilDB;
		// save old questions state
		$old_questions = array();
		$result = $ilDB->queryF("SELECT * FROM svy_svy_qst WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$old_questions[$row["question_fi"]] = $row;
			}
		}
		
		// delete existing question relations
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_svy_qst WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		// create new question relations
		foreach ($this->questions as $key => $value) 
		{
			$next_id = $ilDB->nextId('svy_svy_qst');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_svy_qst (survey_question_id, survey_fi, question_fi, heading, sequence, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
				array('integer','integer','integer','text','integer','integer'),
				array($next_id, $this->getSurveyId(), $value, (strlen($old_questions[$value]["heading"])) ? $old_questions[$value]["heading"] : NULL, $key, time())
			);
		}
	}

/**
* Checks for an anomyous survey id in the database an returns the id
*
* @param string $id A survey access code
* @result object Anonymous survey id if found, empty string otherwise
* @access public
*/
	function getAnonymousId($id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT anonymous_id FROM svy_finished WHERE anonymous_id = %s",
			array('text'),
			array($id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
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
* @result object Resulting question gui object
* @access public
*/
	function getQuestionGUI($questiontype, $question_id)
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestionGUI.php";
		return SurveyQuestionGUI::_getQuestionGUI($questiontype, $question_id);
	}
	
/**
* Returns the question type of a question with a given id
*
* @param integer $question_id The database id of the question
* @result string The question type string
* @access private
*/
	function getQuestionType($question_id) 
	{
		global $ilDB;
		if ($question_id < 1) return -1;
		$result = $ilDB->queryF("SELECT type_tag FROM svy_question, svy_qtype WHERE svy_question.question_id = %s AND " .
			"svy_question.questiontype_fi = svy_qtype.questiontype_id",
			array('integer'),
			array($question_id)
		);
		if ($result->numRows() == 1) 
		{
			$data = $ilDB->fetchAssoc($result);
			return $data["type_tag"];
		} 
		else 
		{
			return "";
		}
	}

/**
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
		switch ($a_anonymize)
		{
			case self::ANONYMIZE_OFF:
			case self::ANONYMIZE_ON:
			case self::ANONYMIZE_FREEACCESS:
			case self::ANONYMIZE_CODE_ALL:
				$this->anonymize = $a_anonymize;
				break;
			default:
				$this->anonymize = self::ANONYMIZE_OFF;
				break;
		}
	}

	/**
	* get anonymize status
	*
	* @return	integer status
	*/
	function getAnonymize()
	{
		return ($this->anonymize) ? $this->anonymize : 0;
	}
	
	/**
	* Checks if the survey is accessable without a survey code
	*
	* @return	boolean status
	*/
	function isAccessibleWithoutCode()
	{
		return ($this->getAnonymize() == self::ANONYMIZE_OFF || 
			$this->getAnonymize() == self::ANONYMIZE_FREEACCESS);		
	}
	
	/**
	* Checks if the survey results are to be anonymized
	*
	* @return	boolean status
	*/
	function hasAnonymizedResults()
	{
		return ($this->getAnonymize() == self::ANONYMIZE_ON || 
			$this->getAnonymize() == self::ANONYMIZE_FREEACCESS);
	}

/**
* Loads a survey object from a database
*
* @access public
*/
	function loadFromDb()
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT * FROM svy_svy WHERE obj_fi = %s",
			array('integer'),
			array($this->getId())
		);
		if ($result->numRows() == 1) 
		{
			$data = $ilDB->fetchAssoc($result);
			$this->setSurveyId($data["survey_id"]);
			$this->setAuthor($data["author"]);
			include_once("./Services/RTE/classes/class.ilRTE.php");
			$this->setIntroduction(ilRTE::_replaceMediaObjectImageSrc($data["introduction"], 1));
			if (strcmp($data["outro"], "survey_finished") == 0)
			{
				$this->setOutro($this->lng->txt("survey_finished"));
			}
			else
			{
				$this->setOutro(ilRTE::_replaceMediaObjectImageSrc($data["outro"], 1));
			}
			$this->setInvitation($data["invitation"]);
			$this->setInvitationMode($data["invitation_mode"]);
			$this->setShowQuestionTitles($data["show_question_titles"]);
			$this->setStartDate($data["startdate"]);
			$this->setEndDate($data["enddate"]);
			$this->setAnonymize($data["anonymize"]);
			$this->setEvaluationAccess($data["evaluation_access"]);
			$this->loadQuestionsFromDb();
			$this->setStatus($data["status"]);
			$this->setMailNotification($data['mailnotification']);
			$this->setMailAddresses($data['mailaddresses']);
			$this->setMailParticipantData($data['mailparticipantdata']);
			$this->setTemplate($data['template_id']);
			$this->setPoolUsage($data['pool_usage']);
			// 360°
			$this->set360Mode($data['mode_360']);
			$this->set360SelfEvaluation($data['mode_360_self_eval']);
			$this->set360SelfRaters($data['mode_360_self_rate']);
			$this->set360SelfAppraisee($data['mode_360_self_appr']);
			$this->set360Results($data['mode_360_results']);
			$this->set360SkillService($data['mode_360_skill_service']);
			// reminder/notification
			$this->setReminderStatus($data["reminder_status"]);
			$this->setReminderStart($data["reminder_start"] ? new ilDate($data["reminder_start"], IL_CAL_DATE) : null);
			$this->setReminderEnd($data["reminder_end"] ? new ilDate($data["reminder_end"], IL_CAL_DATE) : null);
			$this->setReminderFrequency($data["reminder_frequency"]);
			$this->setReminderTarget($data["reminder_target"]);
			$this->setReminderLastSent($data["reminder_last_sent"]);
			$this->setTutorNotificationStatus($data["tutor_ntf_status"]);
			$this->setTutorNotificationRecipients(explode(";", $data["tutor_ntf_reci"]));
			$this->setTutorNotificationTarget($data["tutor_ntf_target"]);
			
			$this->setViewOwnResults($data["own_results_view"]);
			$this->setMailOwnResults($data["own_results_mail"]);
		}
		
		// moved activation to ilObjectActivation
		if($this->ref_id)
		{
			include_once "./Services/Object/classes/class.ilObjectActivation.php";
			$activation = ilObjectActivation::getItem($this->ref_id);			
			switch($activation["timing_type"])
			{				
				case ilObjectActivation::TIMINGS_ACTIVATION:	
					$this->setActivationLimited(true);
					$this->setActivationStartDate($activation["timing_start"]);
					$this->setActivationEndDate($activation["timing_end"]);
					$this->setActivationVisibility($activation["visible"]);
					break;
				
				default:			
					$this->setActivationLimited(false);
					break;							
			}
		}
	}

/**
* Loads the survey questions from the database
*
* @access public
* @see $questions
*/
	function loadQuestionsFromDb() 
	{
		global $ilDB;
		$this->questions = array();
		$result = $ilDB->queryF("SELECT * FROM svy_svy_qst WHERE survey_fi = %s ORDER BY sequence",
			array('integer'),
			array($this->getSurveyId())
		);
		while ($data = $ilDB->fetchAssoc($result)) 
		{
			$this->questions[$data["sequence"]] = $data["question_fi"];
		}
	}

/**
* Sets the authors name of the ilObjSurvey object
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
* Saves an authors name into the lifecycle metadata if no lifecycle metadata exists
* This will only be called for conversion of "old" tests where the author hasn't been
* stored in the lifecycle metadata
*
* @param string $a_author A string containing the name of the test author
* @access private
* @see $author
*/
	function saveAuthorToMetadata($a_author = "")
	{
		$md =& new ilMD($this->getId(), 0, $this->getType());
		$md_life =& $md->getLifecycle();
		if (!$md_life)
		{
			if (strlen($a_author) == 0)
			{
				global $ilUser;
				$a_author = $ilUser->getFullname();
			}
			
			$md_life =& $md->addLifecycle();
			$md_life->save();
			$con =& $md_life->addContribute();
			$con->setRole("Author");
			$con->save();
			$ent =& $con->addEntity();
			$ent->setEntity($a_author);
			$ent->save();
		}
	}
	
/**
* Gets the authors name of the ilObjSurvey object
*
* @return string The string containing the name of the test author
* @access public
* @see $author
*/
  function getAuthor() 
	{
		$author = array();
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md =& new ilMD($this->getId(), 0, $this->getType());
		$md_life =& $md->getLifecycle();
		if ($md_life)
		{
			$ids =& $md_life->getContributeIds();
			foreach ($ids as $id)
			{
				$md_cont =& $md_life->getContribute($id);
				if (strcmp($md_cont->getRole(), "Author") == 0)
				{
					$entids =& $md_cont->getEntityIds();
					foreach ($entids as $entid)
					{
						$md_ent =& $md_cont->getEntity($entid);
						array_push($author, $md_ent->getEntity());
					}
				}
			}
		}
		return join($author, ",");
  }

/**
* Gets the status of the display_question_titles attribute
*
* @return integer The status of the display_question_titles attribute
* @see $display_question_titles
*/
	public function getShowQuestionTitles() 
	{
		return ($this->display_question_titles) ? 1 : 0;
	}

	/**
	* Sets the status of the display_question_titles attribute
	*
	* @param integer $a_show The status of the display_question_titles attribute
	* @see $display_question_titles
	*/
	public function setShowQuestionTitles($a_show) 
	{
		$this->display_question_titles = ($a_show) ? 1 : 0;
	}

/**
* Sets the question titles visible during the query
*
* @access public
* @see $display_question_titles
*/
	function showQuestionTitles() 
	{
		$this->display_question_titles = 1;
	}

/**
* Sets the question titles hidden during the query
*
* @access public
* @see $display_question_titles
*/
	function hideQuestionTitles() 
	{
		$this->display_question_titles = 0;
	}
	
/**
* Sets the invitation status
*
* @param integer $invitation The invitation status
* @access public
* @see $invitation
*/
	function setInvitation($invitation = 0) 
	{
		global $ilDB, $ilAccess;
		
		$this->invitation = $invitation;
		if ($invitation == self::INVITATION_OFF)
		{
			$this->disinviteAllUsers();
		}
		else if ($invitation == self::INVITATION_ON)
		{
			if ($this->getInvitationMode() == self::MODE_UNLIMITED)
			{
				$result = $ilDB->query("SELECT usr_id FROM usr_data");
				while ($row = $ilDB->fetchAssoc($result))
				{					
					if ($ilAccess->checkAccessOfUser($row["usr_id"], "read", "", $this->getRefId(), "svy", $this->getId()))
					{
						$this->inviteUser($row['usr_id']);
					}
				}
			}
		}
  }

/**
* Sets the invitation mode
*
* @param integer $invitation_mode The invitation mode
* @access public
* @see $invitation_mode
*/
  function setInvitationMode($invitation_mode = 0) 
	{
		$this->invitation_mode = $invitation_mode;
  }
	
/**
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
		$this->setInvitation($invitation);
	}

/**
* Sets the introduction text
*
* @param string $introduction A string containing the introduction
* @see $introduction
*/
	public function setIntroduction($introduction = "") 
	{
		$this->introduction = $introduction;
	}

/**
* Sets the outro text
*
* @param string $outro A string containing the outro
* @see $outro
*/
	public function setOutro($outro = "") 
	{
		$this->outro = $outro;
	}

/**
* Gets the invitation status
*
* @return integer The invitation status
* @access public
* @see $invitation
*/
	function getInvitation() 
	{
		return ($this->invitation) ? $this->invitation : self::INVITATION_OFF;
	}

/**
* Gets the invitation mode
*
* @return integer The invitation mode
* @access public
* @see $invitation
*/
	function getInvitationMode() 
	{
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$surveySetting = new ilSetting("survey");
		$unlimited_invitation = $surveySetting->get("unlimited_invitation");
		if (!$unlimited_invitation && $this->invitation_mode == self::MODE_UNLIMITED)
		{
			return self::MODE_PREDEFINED_USERS;
		}
		else
		{
			return ($this->invitation_mode) ? $this->invitation_mode : self::MODE_UNLIMITED;
		}
	}

/**
* Gets the survey status
*
* @return integer Survey status
* @access public
* @see $status
*/
	function getStatus() 
	{
		return ($this->status) ? $this->status : self::STATUS_OFFLINE;
	}

/**
* Gets the survey status
*
* @return integer true if status is online, false otherwise
* @access public
* @see $status
*/
	function isOnline() 
	{
		return ($this->status == self::STATUS_ONLINE) ? true : false;
	}

/**
* Gets the survey status
*
* @return integer true if status is online, false otherwise
* @access public
* @see $status
*/
	function isOffline() 
	{
		return ($this->status == self::STATUS_OFFLINE) ? true : false;
	}

/**
* Sets the survey status
*
* @param integer $status Survey status
* @return string An error message, if the status cannot be set, otherwise an empty string
* @access public
* @see $status
*/
	function setStatus($status = self::STATUS_OFFLINE) 
	{
		$result = "";
		if (($status == self::STATUS_ONLINE) && (count($this->questions) == 0))
		{
			$this->status = self::STATUS_OFFLINE;
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
* @return string Survey start date (YYYY-MM-DD)
* @access public
* @see $start_date
*/
	function getStartDate() 
	{
		return (strlen($this->start_date)) ? $this->start_date : NULL;
	}

/**
* Checks if the survey can be started
*
* @return array An array containing the following keys: result (boolean) and messages (array)
* @access public
*/
	function canStartSurvey($anonymous_id = NULL, $a_no_rbac = false)
	{
		global $ilAccess;
		
		$result = TRUE;
		$messages = array();
		$edit_settings = false;
		// check start date		
		if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getStartDate(), $matches))
		{			
			$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);		
			$now = mktime();
			if ($now < $epoch_time) 
			{		
				array_push($messages,$this->lng->txt('start_date_not_reached').' ('.
					ilDatePresentation::formatDate(new ilDateTime($this->getStartDate(), IL_CAL_TIMESTAMP)). ")");
				$result = FALSE;
				$edit_settings = true;
			}
		}				
		// check end date		
		if (preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getEndDate(), $matches))
		{
			$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
			$now = mktime();
			if ($now > $epoch_time) 
			{
				array_push($messages,$this->lng->txt('end_date_reached').' ('.
					ilDatePresentation::formatDate(new ilDateTime($this->getEndDate(), IL_CAL_TIMESTAMP)). ")");
				$result = FALSE;
				$edit_settings = true;
			}
		}
		
		// check online status
		if ($this->getStatus() == self::STATUS_OFFLINE)
		{
			array_push($messages, $this->lng->txt("survey_is_offline"));
			$result = FALSE;
			$edit_settings = true;
		}
		// check rbac permissions
		if (!$a_no_rbac && !$ilAccess->checkAccess("read", "", $this->ref_id))
		{
			array_push($messages, $this->lng->txt("cannot_participate_survey"));
			$result = FALSE;
		}
		/*
		// 2. check previous access
		if (!$result["error"])
		{
			global $ilUser;
			$survey_started = $this->isSurveyStarted($ilUser->getId(), $anonymous_id);
			if ($survey_started === 1)
			{
				array_push($messages, $this->lng->txt("already_completed_survey"));
				$result = FALSE;
			}
		}		 
		*/
		return array(
			"result" => $result,
			"messages" => $messages,
			"edit_settings" => $edit_settings
		);
	}

/**
* Sets the start date of the survey
*
* @param string $start_data Survey start date (YYYYMMDDHHMMSS)
* @access public
* @see $start_date
*/
	function setStartDate($start_date = "") 
	{
		$this->start_date = $start_date;
	}

	/**
	* Sets the start date of the survey
	*
	* @param string $start_date Survey start date (YYYY-MM-DD)
	* @param string $start_time Survey start time (HH:MM:SS)
	* @access public
	* @see $start_date
	*/
	function setStartDateAndTime($start_date = "", $start_time) 
	{
		$y = ''; $m = ''; $d = ''; $h = ''; $i = ''; $s = '';
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $start_date, $matches))
		{
			$y = $matches[1];
			$m = $matches[2];
			$d = $matches[3];
		}
		if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $start_time, $matches))
		{
			$h = $matches[1];
			$i = $matches[2];
			$s = $matches[3];
		}
		$this->start_date = sprintf('%04d%02d%02d%02d%02d%02d', $y, $m, $d, $h, $i, $s);
	}

/**
* Gets the end date of the survey
*
* @return string Survey end date (YYYY-MM-DD)
* @access public
* @see $end_date
*/
	function getEndDate() 
	{
		return (strlen($this->end_date)) ? $this->end_date : NULL;
	}

/**
* Sets the end date of the survey
*
* @param string $end_date Survey end date (YYYYMMDDHHMMSS)
* @access public
* @see $end_date
*/
	function setEndDate($end_date = "") 
	{
		$this->end_date = $end_date;
	}

	/**
	* Sets the end date of the survey
	*
	* @param string $end_date Survey end date (YYYY-MM-DD)
	* @param string $end_time Survey end time (HH:MM:SS)
	* @access public
	* @see $start_date
	*/
	function setEndDateAndTime($end_date = "", $end_time) 
	{
		$y = ''; $m = ''; $d = ''; $h = ''; $i = ''; $s = '';
		if (preg_match("/(\d{4})-(\d{2})-(\d{2})/", $end_date, $matches))
		{
			$y = $matches[1];
			$m = $matches[2];
			$d = $matches[3];
		}
		if (preg_match("/(\d{2}):(\d{2}):(\d{2})/", $end_time, $matches))
		{
			$h = $matches[1];
			$i = $matches[2];
			$s = $matches[3];
		}
		$this->end_date = sprintf('%04d%02d%02d%02d%02d%02d', $y, $m, $d, $h, $i, $s);
	}

/**
* Gets the learners evaluation access
*
* @return integer The evaluation access
* @access public
* @see $evaluation_access
*/
	function getEvaluationAccess() 
	{
		return ($this->evaluation_access) ? $this->evaluation_access : self::EVALUATION_ACCESS_OFF;
	}

/**
* Sets the learners evaluation access
*
* @param integer $evaluation_access The evaluation access
* @access public
* @see $evaluation_access
*/
	function setEvaluationAccess($evaluation_access = self::EVALUATION_ACCESS_OFF) 
	{
		$this->evaluation_access = ($evaluation_access) ? $evaluation_access : self::EVALUATION_ACCESS_OFF;
	}
	
	function setActivationVisibility($a_value)
	{
		$this->activation_visibility = (bool) $a_value;
	}
	
	function getActivationVisibility()
	{
		return $this->activation_visibility;
	}
	
	function isActivationLimited()
	{
	   return (bool)$this->activation_limited;
	}
	
	function setActivationLimited($a_value)
	{
	   $this->activation_limited = (bool)$a_value;
	}

/**
* Gets the introduction text
*
* @return string The introduction of the survey object
* @access public
* @see $introduction
*/
	function getIntroduction() 
	{
		return (strlen($this->introduction)) ? $this->introduction : NULL;
	}

/**
* Gets the outro text
*
* @return string The outro of the survey object
* @access public
* @see $outro
*/
	function getOutro() 
	{
		return (strlen($this->outro)) ? $this->outro : NULL;
	}

/**
* Gets the question id's of the questions which are already in the survey
*
* @return array The questions of the survey
* @access public
*/
	function &getExistingQuestions() 
	{
		global $ilDB;
		$existing_questions = array();
		$result = $ilDB->queryF("SELECT svy_question.original_id FROM svy_question, svy_svy_qst WHERE " .
			"svy_svy_qst.survey_fi = %s AND svy_svy_qst.question_fi = svy_question.question_id",
			array('integer'),
			array($this->getSurveyId())
		);
		while ($data = $ilDB->fetchAssoc($result)) 
		{
			if($data["original_id"])
			{
				array_push($existing_questions, $data["original_id"]);
			}
		}
		return $existing_questions;
	}

/**
* Get the titles of all available survey question pools
*
* @return array An array of survey question pool titles
* @access public
*/
	function &getQuestionpoolTitles($could_be_offline = FALSE, $showPath = FALSE) 
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		return ilObjSurveyQuestionPool::_getAvailableQuestionpools($use_object_id = TRUE, $could_be_offline, $showPath);
	}
	
/**
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
* @param integer $question_id The question id of the question which has to be moved down
*/
	public function moveDownQuestion($question_id)
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
						$this->deleteConstraint($constraint["id"]);
					}
				}
			}
		}
		$this->saveQuestionsToDb();
	}
	
/**
* Remove a question from the survey
*
* @param integer $question_id The database id of the question
* @access public
*/
	function removeQuestion($question_id)
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		$question =& $this->_instanciateQuestion($question_id);
		$question->delete($question_id);
		$this->removeConstraintsConcerningQuestion($question_id);
	}
	
/**
* Remove constraints concerning a question with a given question_id
*
* @param integer $question_id The database id of the question
* @access public
*/
	function removeConstraintsConcerningQuestion($question_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT constraint_fi FROM svy_qst_constraint WHERE question_fi = %s AND survey_fi = %s",
			array('integer','integer'),
			array($question_id, $this->getSurveyId())
		);
		if ($result->numRows() > 0)
		{
			$remove_constraints = array();
			while ($row = $ilDB->fetchAssoc($result))
			{
				array_push($remove_constraints, $row["constraint_fi"]);
			}
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qst_constraint WHERE question_fi = %s AND survey_fi = %s",
				array('integer','integer'),
				array($question_id, $this->getSurveyId())
			);
			foreach ($remove_constraints as $key => $constraint_id)
			{
				$affectedRows = $ilDB->manipulateF("DELETE FROM svy_constraint WHERE constraint_id = %s",
					array('integer'),
					array($constraint_id)
				);
			}
		}
	}
		
/**
* Remove questions from the survey
*
* @param array $remove_questions An array with the question id's of the questions to remove
* @param array $remove_questionblocks An array with the questionblock id's of the questions blocks to remove
* @access public
*/
	function removeQuestions($remove_questions, $remove_questionblocks)
	{
		global $ilDB;

		$block_sizes = array();
		foreach ($this->getSurveyQuestions() as $question_id => $data)
		{
			if (in_array($question_id, $remove_questions) or in_array($data["questionblock_id"], $remove_questionblocks))
			{
				unset($this->questions[array_search($question_id, $this->questions)]);
			    $this->removeQuestion($question_id);
			}
			else if($data["questionblock_id"])
			{
				$block_sizes[$data["questionblock_id"]]++;
			}
		}
		
		// blocks with just 1 question need to be deleted
		foreach($block_sizes as $block_id => $size)
		{
			if($size < 2)
			{
				$remove_questionblocks[] = $block_id;
			}
		}

		foreach (array_unique($remove_questionblocks) as $questionblock_id)
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qblk WHERE questionblock_id = %s",
				array('integer'),
				array($questionblock_id)
			);
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qblk_qst WHERE questionblock_fi = %s AND survey_fi = %s",
				array('integer','integer'),
				array($questionblock_id, $this->getSurveyId())
			);
		}
		
		$this->questions = array_values($this->questions);
		$this->saveQuestionsToDb();
	}
		
/**
* Unfolds question blocks of a question pool
*
* @param array $questionblocks An array of question block id's
* @access public
*/
	function unfoldQuestionblocks($questionblocks)
	{
		global $ilDB;
		foreach ($questionblocks as $index)
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qblk WHERE questionblock_id = %s",
				array('integer'),
				array($index)
			);
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qblk_qst WHERE questionblock_fi = %s AND survey_fi = %s",
				array('integer','integer'),
				array($index, $this->getSurveyId())
			);
		}
	}

	function removeQuestionFromBlock($question_id, $questionblock_id)
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qblk_qst WHERE questionblock_fi = %s AND survey_fi = %s AND question_fi = %s",
			array('integer','integer','integer'),
			array($questionblock_id, $this->getSurveyId(), $question_id)
		);
	}

	function addQuestionToBlock($question_id, $questionblock_id)
	{
		global $ilDB;


		$next_id = $ilDB->nextId('svy_qblk_qst');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_qblk_qst (qblk_qst_id, survey_fi, questionblock_fi, " .
			"question_fi) VALUES (%s, %s, %s, %s)",
			array('integer','integer','integer','integer'),
			array($next_id, $this->getSurveyId(), $questionblock_id, $question_id)
		);
		
		$this->deleteConstraints($question_id); // #13713
	}

/**
* Returns the question titles of all questions of a question block
*
* @result array The titles of the the question block questions
* @access public
*/
	function &getQuestionblockQuestions($questionblock_id)
	{
		global $ilDB;
		$titles = array();
		$result = $ilDB->queryF("SELECT svy_question.title, svy_qblk_qst.question_fi, svy_qblk_qst.survey_fi FROM ".
			"svy_qblk, svy_qblk_qst, svy_question WHERE svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND " .
			"svy_question.question_id = svy_qblk_qst.question_fi AND svy_qblk.questionblock_id = %s",
			array('integer'),
			array($questionblock_id)
		);
		$survey_id = "";
		while ($row = $ilDB->fetchAssoc($result))
		{
			$titles[$row["question_fi"]] = $row["title"];
			$survey_id = $row["survey_fi"];
		}
		$result = $ilDB->queryF("SELECT question_fi, sequence FROM svy_svy_qst WHERE survey_fi = %s ORDER BY sequence",
			array('integer'),
			array($survey_id)
		);
		$resultarray = array();
		$counter = 1;
		while ($row = $ilDB->fetchAssoc($result))
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
* @result array The id's of the the question block questions
* @access public
*/
	function &getQuestionblockQuestionIds($questionblock_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT question_fi FROM svy_qblk_qst WHERE questionblock_fi = %s",
			array("integer"),
			array($questionblock_id)
		);
		$ids = array();
		if ($result->numRows())
		{
			while ($data = $ilDB->fetchAssoc($result))
			{
				array_push($ids, $data['question_fi']);
			}
		}
		return $ids;
	}
	
/**
* Returns the database row for a given question block
*
* @param integer $questionblock_id The database id of the question block
* @result array The database row of the question block
* @access public
*/
	function getQuestionblock($questionblock_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT * FROM svy_qblk WHERE questionblock_id = %s",
			array('integer'),
			array($questionblock_id)
		);
		return $ilDB->fetchAssoc($result);
	}
	
/**
* Returns the database row for a given question block
*
* @param integer $questionblock_id The database id of the question block
* @result array The database row of the question block
* @access public
*/
	function _getQuestionblock($questionblock_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT * FROM svy_qblk WHERE questionblock_id = %s",
			array('integer'),
			array($questionblock_id)
		);
		$row = $ilDB->fetchAssoc($result);
		return $row;
	}

/**
* Adds a questionblock to the database
*
* @param string $title The questionblock title
* @param integer $owner The database id of the owner
* @return integer The database id of the newly created questionblock
* @access public
*/
	function _addQuestionblock($title = "", $owner = 0,  $show_questiontext = true, $show_blocktitle = false)
	{		
		global $ilDB;
		$next_id = $ilDB->nextId('svy_qblk');
		$ilDB->manipulateF("INSERT INTO svy_qblk (questionblock_id, title, show_questiontext,".
			" show_blocktitle, owner_fi, tstamp) " .
			"VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer','text','integer','integer','integer','integer'),
			array($next_id, $title, $show_questiontext, $show_blocktitle, $owner, time())
		);
		return $next_id;
	}
	
/**
* Creates a question block for the survey
*
* @param string $title The title of the question block
* @param array $questions An array with the database id's of the question block questions
* @access public
*/
	function createQuestionblock($title, $show_questiontext, $show_blocktitle, $questions)
	{
		global $ilDB;
		
		// if the selected questions are not in a continous selection, move all questions of the
		// questionblock at the position of the first selected question
		$this->moveQuestions($questions, $questions[0], 0);
		
		// now save the question block
		global $ilUser;
		$next_id = $ilDB->nextId('svy_qblk');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_qblk (questionblock_id, title, show_questiontext,".
			" show_blocktitle, owner_fi, tstamp) VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer','text','text','text','integer','integer'),
			array($next_id, $title, $show_questiontext, $show_blocktitle, $ilUser->getId(), time())
		);
		if ($affectedRows)
		{
			$questionblock_id = $next_id;
			foreach ($questions as $index)
			{
				$next_id = $ilDB->nextId('svy_qblk_qst');
				$affectedRows = $ilDB->manipulateF("INSERT INTO svy_qblk_qst (qblk_qst_id, survey_fi, questionblock_fi, " .
					"question_fi) VALUES (%s, %s, %s, %s)",
					array('integer','integer','integer','integer'),
					array($next_id, $this->getSurveyId(), $questionblock_id, $index)
				);
				$this->deleteConstraints($index);
			}
		}
	}
	
/**
* Modifies a question block
*
* @param integer $questionblock_id The database id of the question block
* @param string $title The title of the question block
* @access public
*/
	function modifyQuestionblock($questionblock_id, $title, $show_questiontext, $show_blocktitle)
	{
		global $ilDB;
		$affectedRows = $ilDB->manipulateF("UPDATE svy_qblk SET title = %s, show_questiontext = %s,".
			" show_blocktitle = %s WHERE questionblock_id = %s",
			array('text','text','text','integer'),
			array($title, $show_questiontext, $show_blocktitle, $questionblock_id)
		);
	}
	
/**
* Deletes the constraints for a question
*
* @param integer $question_id The database id of the question
* @access public
*/
	function deleteConstraints($question_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT constraint_fi FROM svy_qst_constraint WHERE question_fi = %s AND survey_fi = %s",
			array('integer','integer'),
			array($question_id, $this->getSurveyId())
		);
		$constraints = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($constraints, $row["constraint_fi"]);
		}
		foreach ($constraints as $constraint_id)
		{
			$this->deleteConstraint($constraint_id);
		}
	}

/**
* Deletes a constraint of a question
*
* @param integer $constraint_id The database id of the constraint
* @param integer $question_id The database id of the question
* @access public
*/
	function deleteConstraint($constraint_id)
	{
		global $ilDB;
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_constraint WHERE constraint_id = %s",
			array('integer'),
			array($constraint_id)
		);
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qst_constraint WHERE constraint_fi = %s",
			array('integer'),
			array($constraint_id)
		);
	}

/**
* Returns the survey questions and questionblocks in an array
*
* @access public
*/
	public function &getSurveyQuestions($with_answers = false)
	{
		global $ilDB;
		$obligatory_states =& $this->getObligatoryStates();
		// get questionblocks
		$all_questions = array();
		$result = $ilDB->queryF("SELECT svy_qtype.type_tag, svy_qtype.plugin, svy_question.question_id, ".
			"svy_svy_qst.heading FROM svy_qtype, svy_question, svy_svy_qst WHERE svy_svy_qst.survey_fi = %s AND " .
			"svy_svy_qst.question_fi = svy_question.question_id AND svy_question.questiontype_fi = svy_qtype.questiontype_id " .
			"ORDER BY svy_svy_qst.sequence",
			array('integer'),
			array($this->getSurveyId())
		);
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		while ($row = $ilDB->fetchAssoc($result))
		{
			$add = true;
			if ($row["plugin"])
			{
				if (!$this->isPluginActive($row["type_tag"]))
				{
					$add = false;
				}
			}
			if ($add)
			{
				$question =& $this->_instanciateQuestion($row["question_id"]);
				$questionrow = $question->_getQuestionDataArray($row["question_id"]);
				foreach ($row as $key => $value)
				{
					$questionrow[$key] = $value;
				}
				$all_questions[$row["question_id"]] = $questionrow;
				$all_questions[$row["question_id"]]["usableForPrecondition"] = $question->usableForPrecondition();
				$all_questions[$row["question_id"]]["availableRelations"] = $question->getAvailableRelations();
				if (array_key_exists($row["question_id"], $obligatory_states))
				{
					$all_questions[$row["question_id"]]["obligatory"] = $obligatory_states[$row["question_id"]];
				}
			}
		}
		// get all questionblocks
		$questionblocks = array();
		if (count($all_questions))
		{
			$result = $ilDB->queryF("SELECT svy_qblk.*, svy_qblk_qst.question_fi FROM svy_qblk, svy_qblk_qst WHERE " .
				"svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND svy_qblk_qst.survey_fi = %s " .
				"AND " . $ilDB->in('svy_qblk_qst.question_fi', array_keys($all_questions), false, 'integer'),
				array('integer'),
				array($this->getSurveyId())
			);
			while ($row = $ilDB->fetchAssoc($result))
			{
				$questionblocks[$row['question_fi']] = $row;
			}
		}
		
		foreach ($all_questions as $question_id => $row)
		{
			$constraints = $this->getConstraints($question_id);
			if (isset($questionblocks[$question_id]))
			{
				$all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]['title'];
				$all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]['questionblock_id'];
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
				$result = $ilDB->queryF("SELECT svy_variable.*, svy_category.title FROM svy_variable, svy_category " .
					"WHERE svy_variable.question_fi = %s AND svy_variable.category_fi = svy_category.category_id ".
					"ORDER BY sequence ASC",
					array('integer'),
					array($question_id)
				);
				if ($result->numRows() > 0) 
				{
					while ($data = $ilDB->fetchAssoc($result)) 
					{
						array_push($answers, $data["title"]);
					}
				}
				$all_questions[$question_id]["answers"] = $answers;
			}
		}
		return $all_questions;
	}
	
/**
* Sets the obligatory states for questions in a survey from the questions form
*
* @param array $obligatory_questions The questions which should be set obligatory from the questions form, the remaining questions should be setted not obligatory
* @access public
*/
	function setObligatoryStates($obligatory_questions)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT * FROM svy_svy_qst WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				if (!array_key_exists($row["question_fi"], $obligatory_questions))
				{
					$obligatory_questions[$row["question_fi"]] = 0;
				}
			}
		}

	  // set the obligatory states in the database
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_qst_oblig WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);

	  // set the obligatory states in the database
		foreach ($obligatory_questions as $question_fi => $obligatory)
		{
			$next_id = $ilDB->nextId('svy_qst_oblig');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_qst_oblig (question_obligatory_id, survey_fi, question_fi, " .
				"obligatory, tstamp) VALUES (%s, %s, %s, %s, %s)",
				array('integer','integer','integer','text','integer'),
				array($next_id, $this->getSurveyId(), $question_fi, (strlen($obligatory)) ? $obligatory : 0, time())
			);
			
			// #12420
			$ilDB->manipulate("UPDATE svy_question".
				" SET obligatory = ".$ilDB->quote($obligatory, "integer").
				" WHERE question_id = ".$ilDB->quote($question_fi, "integer"));
		}
	}
	
/**
* Gets specific obligatory states of the survey
*
* @return array An array containing the obligatory states for every question found in the database
* @access public
*/
	function &getObligatoryStates()
	{
		global $ilDB;
		$obligatory_states = array();
		$result = $ilDB->queryF("SELECT * FROM svy_qst_oblig WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				$obligatory_states[$row["question_fi"]] = $row["obligatory"];
			}
		}
		return $obligatory_states;
	}
	
/**
* Returns the survey pages in an array (a page contains one or more questions)
*
* @access public
*/
	function &getSurveyPages()
	{
		global $ilDB;
		$obligatory_states =& $this->getObligatoryStates();
		// get questionblocks
		$all_questions = array();
		$result = $ilDB->queryF("SELECT svy_question.*, svy_qtype.type_tag, svy_svy_qst.heading FROM " . 
			"svy_question, svy_qtype, svy_svy_qst WHERE svy_svy_qst.survey_fi = %s AND " .
			"svy_svy_qst.question_fi = svy_question.question_id AND svy_question.questiontype_fi = svy_qtype.questiontype_id ".
			"ORDER BY svy_svy_qst.sequence",
			array('integer'),
			array($this->getSurveyId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$all_questions[$row["question_id"]] = $row;
		}
		// get all questionblocks
		$questionblocks = array();
		if (count($all_questions))
		{
			$result = $ilDB->queryF("SELECT svy_qblk.*, svy_qblk_qst.question_fi FROM svy_qblk, svy_qblk_qst ".
				"WHERE svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND svy_qblk_qst.survey_fi = %s ".
				"AND " . $ilDB->in('svy_qblk_qst.question_fi', array_keys($all_questions), false, 'integer'),
				array('integer'),
				array($this->getSurveyId())
			);
			while ($row = $ilDB->fetchAssoc($result))
			{
				$questionblocks[$row['question_fi']] = $row;
			}
		}
		
		$all_pages = array();
		$pageindex = -1;
		$currentblock = "";
		foreach ($all_questions as $question_id => $row)
		{
			if (array_key_exists($question_id, $obligatory_states))
			{
				$all_questions[$question_id]["obligatory"] = $obligatory_states[$question_id];
			}
			$constraints = array();
			if (isset($questionblocks[$question_id]))
			{
				if (!$currentblock or ($currentblock != $questionblocks[$question_id]['questionblock_id']))
				{
					$pageindex++;
				}
				$all_questions[$question_id]['page'] = $pageindex;
				$all_questions[$question_id]["questionblock_title"] = $questionblocks[$question_id]['title'];
				$all_questions[$question_id]["questionblock_id"] = $questionblocks[$question_id]['questionblock_id'];
				$all_questions[$question_id]["questionblock_show_questiontext"] = $questionblocks[$question_id]['show_questiontext'];
				$all_questions[$question_id]["questionblock_show_blocktitle"] = $questionblocks[$question_id]['show_blocktitle'];
				$currentblock = $questionblocks[$question_id]['questionblock_id'];
				$constraints = $this->getConstraints($question_id);
				$all_questions[$question_id]["constraints"] = $constraints;
			}
			else
			{
				$pageindex++;
				$all_questions[$question_id]['page'] = $pageindex;
				$all_questions[$question_id]["questionblock_title"] = "";
				$all_questions[$question_id]["questionblock_id"] = "";
				$all_questions[$question_id]["questionblock_show_questiontext"] = 1;
				$all_questions[$question_id]["questionblock_show_blocktitle"] = 1;
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
				$all_pages[$index][$blockindex]["position"] = $counter / $max;
			}
			$counter++;
		}
		return $all_pages;
	}
	
/**
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
* @return array The available question pools
* @access public
*/
	function &getAvailableQuestionpools($use_obj_id = false, $could_be_offline = false, $showPath = FALSE, $permission = "read")
	{
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		return ilObjSurveyQuestionPool::_getAvailableQuestionpools($use_obj_id, $could_be_offline, $showPath, $permission);
	}
	
	/**
	* Returns a precondition with a given id
	*
	* @access public
	*/
	function getPrecondition($id)
	{
		global $ilDB;
		
		$result_array = array();
		$result = $ilDB->queryF("SELECT svy_constraint.*, svy_relation.*, svy_qst_constraint.question_fi ref_question_fi FROM svy_qst_constraint, svy_constraint, ".
			"svy_relation WHERE svy_constraint.relation_fi = svy_relation.relation_id AND ".
			"svy_qst_constraint.constraint_fi = svy_constraint.constraint_id AND svy_constraint.constraint_id = %s",
			array('integer'),
			array($id)
		);
		$pc = array();
		if ($result->numRows())
		{
			$pc = $ilDB->fetchAssoc($result);
		}
		return $pc;
	}
	
/**
* Returns the constraints to a given question or questionblock
*
* @access public
*/
	function getConstraints($question_id)
 	{
		global $ilDB;
		
		$result_array = array();
		$result = $ilDB->queryF("SELECT svy_constraint.*, svy_relation.* FROM svy_qst_constraint, svy_constraint, svy_relation ".
			"WHERE svy_constraint.relation_fi = svy_relation.relation_id AND ".
			"svy_qst_constraint.constraint_fi = svy_constraint.constraint_id AND svy_qst_constraint.question_fi = %s ".
			"AND svy_qst_constraint.survey_fi = %s",
			array('integer','integer'),
			array($question_id, $this->getSurveyId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{	
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question_type = SurveyQuestion::_getQuestionType($row["question_fi"]);
			SurveyQuestion::_includeClass($question_type);
			$question = new $question_type();
			$question->loadFromDb($row["question_fi"]);
			$valueoutput = $question->getPreconditionValueOutput($row["value"]);
			array_push($result_array, array("id" => $row["constraint_id"], "question" => $row["question_fi"], "short" => $row["shortname"], "long" => $row["longname"], "value" => $row["value"], "conjunction" => $row["conjunction"], "valueoutput" => $valueoutput));
		}
		return $result_array;
	}

/**
* Returns the constraints to a given question or questionblock
*
* @access public
*/
	function _getConstraints($survey_id)
 	{
		global $ilDB;
		$result_array = array();
		$result = $ilDB->queryF("SELECT svy_qst_constraint.question_fi as for_question, svy_constraint.*, svy_relation.* ".
			"FROM svy_qst_constraint, svy_constraint, svy_relation WHERE svy_constraint.relation_fi = svy_relation.relation_id ".
			"AND svy_qst_constraint.constraint_fi = svy_constraint.constraint_id AND svy_qst_constraint.survey_fi = %s",
			array('integer'),
			array($survey_id)
		);
		while ($row = $ilDB->fetchAssoc($result))
		{		
			array_push($result_array, array("id" => $row["constraint_id"], "for_question" => $row["for_question"], "question" => $row["question_fi"], "short" => $row["shortname"], "long" => $row["longname"], "relation_id" => $row["relation_id"], "value" => $row["value"], 'conjunction' => $row['conjunction']));
		}
		return $result_array;
	}


/**
* Returns all variables of a question
*
* @access public
*/
	function &getVariables($question_id)
	{
		global $ilDB;
		
		$result_array = array();
		$result = $ilDB->queryF("SELECT svy_variable.*, svy_category.title FROM svy_variable LEFT JOIN ".
			"svy_category ON svy_variable.category_fi = svy_category.category_id WHERE svy_variable.question_fi = %s ".
			"ORDER BY svy_variable.sequence",
			array('integer'),
			array($question_id)
		);
		while ($row = $ilDB->fetchObject($result))
		{
			$result_array[$row->sequence] = $row;
		}
		return $result_array;
	}
	
	/**
	* Adds a constraint
	*
	* @param integer $if_question_id The question id of the question which defines a precondition
	* @param integer $relation The database id of the relation
	* @param mixed $value The value compared with the relation
	* @access public
	*/
	function addConstraint($if_question_id, $relation, $value, $conjunction)
	{
		global $ilDB;

		$next_id = $ilDB->nextId('svy_constraint');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_constraint (constraint_id, question_fi, relation_fi, value, conjunction) VALUES ".
			"(%s, %s, %s, %s, %s)",
			array('integer','integer','integer','float', 'integer'),
			array($next_id, $if_question_id, $relation, $value, $conjunction)
		);
		if ($affectedRows)
		{
			return $next_id;
		}
		else
		{
			return null;
		}
	}


/**
* Adds a constraint to a question
*
* @param integer $to_question_id The question id of the question where to add the constraint
* @param integer $constraint_id The id of the constraint
*/
	public function addConstraintToQuestion($to_question_id, $constraint_id)
	{
		global $ilDB;
		
		$next_id = $ilDB->nextId('svy_qst_constraint');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_qst_constraint (question_constraint_id, survey_fi, question_fi, ".
			"constraint_fi) VALUES (%s, %s, %s, %s)",
			array('integer','integer','integer','integer'),
			array($next_id, $this->getSurveyId(), $to_question_id, $constraint_id)
		);
	}
	
	/**
	* Updates a precondition
	*
	* @param integer $precondition_id The id of the original precondition
	* @param integer $to_question_id The question id of the question where to add the constraint
	* @param integer $if_question_id The question id of the question which defines a precondition
	* @param integer $relation The database id of the relation
	* @param mixed $value The value compared with the relation
	* @access public
	*/
	function updateConstraint($precondition_id, $if_question_id, $relation, $value, $conjunction)
	{
		global $ilDB;
		$affectedRows = $ilDB->manipulateF("UPDATE svy_constraint SET question_fi = %s, relation_fi = %s, value = %s, conjunction = %s ".
			"WHERE constraint_id = %s",
			array('integer','integer','float','integer','integer'),
			array($if_question_id, $relation, $value, $conjunction, $precondition_id)
		);
	}
		
	public function updateConjunctionForQuestions($questions, $conjunction)
	{
		global $ilDB;
		foreach ($questions as $question_id)
		{
			$affectedRows = $ilDB->manipulateF("UPDATE svy_constraint SET conjunction = %s ".
				"WHERE constraint_id IN (SELECT constraint_fi FROM svy_qst_constraint WHERE svy_qst_constraint.question_fi = %s)",
				array('integer','integer'),
				array($conjunction, $question_id)
			);
		}
	}

/**
* Returns all available relations
*
* @access public
*/
	function getAllRelations($short_as_key = false)
 	{
		global $ilDB;
		
		// #7987
		$custom_order = array("equal", "not_equal", "less", "less_or_equal", "more", "more_or_equal");
		$custom_order = array_flip($custom_order);
		
		$result_array = array();
		$result = $ilDB->query("SELECT * FROM svy_relation");
		while ($row = $ilDB->fetchAssoc($result))
		{
			if ($short_as_key)
			{
				$result_array[$row["shortname"]] = array("short" => $row["shortname"], "long" => $row["longname"], "id" => $row["relation_id"], "order" => $custom_order[$row["longname"]]);
			}
			else
			{
				$result_array[$row["relation_id"]] = array("short" => $row["shortname"], "long" => $row["longname"], "order" => $custom_order[$row["longname"]]);
			}
		}		
		
		$result_array = ilUtil::sortArray($result_array, "order", "ASC", true, true);
		foreach($result_array as $idx => $item)
		{
			unset($result_array[$idx]["order"]);
		}
		
		return $result_array;
	}

	/**
	* Disinvite all users
	*/
	public function disinviteAllUsers()
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT user_fi FROM svy_inv_usr WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			$this->disinviteUser($row['user_fi']);
		}
	}

/**
* Disinvites a user from a survey
*
* @param integer $user_id The database id of the disinvited user
*/
	public function disinviteUser($user_id)
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_inv_usr WHERE survey_fi = %s AND user_fi = %s",
			array('integer','integer'),
			array($this->getSurveyId(), $user_id)
		);
		include_once './Services/User/classes/class.ilObjUser.php';
		ilObjUser::_dropDesktopItem($user_id, $this->getRefId(), "svy");
	}

/**
* Invites a user to a survey
*
* @param integer $user_id The database id of the invited user
* @access public
*/
	function inviteUser($user_id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT user_fi FROM svy_inv_usr WHERE user_fi = %s AND survey_fi = %s",
			array('integer','integer'),
			array($user_id, $this->getSurveyId())
		);
		if ($result->numRows() < 1)
		{
			$next_id = $ilDB->nextId('svy_inv_usr');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_inv_usr (invited_user_id, survey_fi, user_fi, tstamp) " .
				"VALUES (%s, %s, %s, %s)",
				array('integer','integer','integer','integer'),
				array($next_id, $this->getSurveyId(), $user_id, time())
			);
		}
		if ($this->getInvitation() == self::INVITATION_ON)
		{
			include_once './Services/User/classes/class.ilObjUser.php';
			ilObjUser::_addDesktopItem($user_id, $this->getRefId(), "svy");
		}
	}

	/**
	* Invites a group to a survey
	*
	* @param integer $group_id The database id of the invited group
	* @access public
	*/
		function inviteGroup($group_id)
		{
			global $ilAccess;
			$invited = 0;
			include_once "./Modules/Group/classes/class.ilObjGroup.php";
			$group = new ilObjGroup($group_id);
			$members = $group->getGroupMemberIds();
			foreach ($members as $user_id)
			{
				if ($ilAccess->checkAccessOfUser($user_id, "read", "", $this->getRefId(), "svy", $this->getId()))
				{
					$this->inviteUser($user_id);
					if ($this->getInvitation() == self::INVITATION_ON)
					{
						include_once './Services/User/classes/class.ilObjUser.php';
						ilObjUser::_addDesktopItem($user_id, $this->getRefId(), "svy");
					}
				}
			}
			return $invited;
		}

	/**
	* Invites a role to a survey
	*
	* @param integer $role_id The database id of the invited role
	* @access public
	*/
		function inviteRole($role_id)
		{
			global $rbacreview;
			global $ilAccess;
			$invited = 0;
			$members = $rbacreview->assignedUsers($role_id);
			foreach ($members as $user_id)
			{
				if ($ilAccess->checkAccessOfUser($user_id, "read", "", $this->getRefId(), "svy", $this->getId()))
				{
					$this->inviteUser($user_id);
					if ($this->getInvitation() == self::INVITATION_ON)
					{
						include_once './Services/User/classes/class.ilObjUser.php';
						ilObjUser::_addDesktopItem($user_id, $this->getRefId(), "svy");
					}
				}
			}
			return $invited;
		}

/**
* Returns a list of all invited users in a survey
*
* @return array The user id's of the invited users
* @access public
*/
	function &getInvitedUsers()
	{
		global $ilDB;
		
		$result_array = array();
		$result = $ilDB->queryF("SELECT user_fi FROM svy_inv_usr WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($result_array, $row["user_fi"]);
		}
		return $result_array;
	}

/**
* Deletes the working data of a question in the database
*
* @param integer $question_id The database id of the question
* @param integer $active_id The active id of the user who worked through the question
* @access public
*/
	function deleteWorkingData($question_id, $active_id)
	{
		global $ilDB;
		
		$affectedRows = $ilDB->manipulateF("DELETE FROM svy_answer WHERE question_fi = %s AND active_fi = %s",
			array('integer','integer'),
			array($question_id, $active_id)
		);
	}
	
/**
* Gets the working data of question from the database
*
* @param integer $question_id The database id of the question
* @param integer $active_id The active id of the user who worked through the question
* @return array The resulting database dataset as an array
* @access public
*/
	function loadWorkingData($question_id, $active_id)
	{
		global $ilDB;
		$result_array = array();
		$result = $ilDB->queryF("SELECT * FROM svy_answer WHERE question_fi = %s AND active_fi = %s",
			array('integer','integer'),
			array($question_id, $active_id)
		);
		if ($result->numRows() >= 1)
		{
			while ($row = $ilDB->fetchAssoc($result))
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
	* Fills a survey randomly with data for a given user.
	*
	* @param integer $user_id The database id of the user. If empty an anonymous user will be taken
	* @access public
	*/
	function fillSurveyForUser($user_id = ANONYMOUS_USER_ID)
	{
		global $ilDB;
		// create an anonymous key
		$anonymous_id = $this->createNewAccessCode();
		$this->saveUserAccessCode($user_id, $anonymous_id);
		// create the survey_finished dataset and set the survey finished already
		$active_id = $ilDB->nextId('svy_finished');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_finished (finished_id, survey_fi, user_fi, anonymous_id, state, tstamp) ".
			"VALUES (%s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','text','text','integer'),
			array($active_id, $this->getSurveyId(), $user_id, $anonymous_id, 1, time())
		);
		// fill the questions randomly
		$pages =& $this->getSurveyPages();
		foreach ($pages as $key => $question_array)
		{
			foreach ($question_array as $question)
			{
				// instanciate question
				require_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
				$question =& SurveyQuestion::_instanciateQuestion($question["question_id"]);
				$question->saveRandomData($active_id);
			}
		}		
	}

/**
* Starts the survey creating an entry in the database
*
* @param integer $user_id The database id of the user who starts the survey
* @access public
*/
	function startSurvey($user_id, $anonymous_id, $appraisee_id)
	{
		global $ilDB;
		
		if ($this->getAnonymize() && (strlen($anonymous_id) == 0)) return;

		if (strcmp($user_id, "") == 0)
		{
			if ($user_id == ANONYMOUS_USER_ID)
			{
				$user_id = 0;
			}
		}
		$next_id = $ilDB->nextId('svy_finished');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_finished (finished_id, survey_fi, user_fi, anonymous_id, state, tstamp, appr_id) ".
			"VALUES (%s, %s, %s, %s, %s, %s, %s)",
			array('integer','integer','integer','text','text','integer','integer'),
			array($next_id, $this->getSurveyId(), $user_id, $anonymous_id, 0, time(), $appraisee_id)
		);
		return $next_id;
	}

/**
* Finishes the survey creating an entry in the database
*
* @param integer $user_id The database id of the user who finishes the survey
* @access public
*/
	function finishSurvey($finished_id)
	{
		global $ilDB;
		
		$ilDB->manipulateF("UPDATE svy_finished SET state = %s, tstamp = %s".
			" WHERE survey_fi = %s AND finished_id = %s",
			array('text','integer','integer','integer'),
			array(1, time(), $this->getSurveyId(), $finished_id)
		);			
		
		$this->checkTutorNotification();
	}

	/**
	* Sets the number of the active survey page
	*
	* @param integer $finished_id The database id of the active user
	* @param integer $page_id The index of the page
	* @access public
	*/
	function setPage($finished_id, $page_id)
	{
		global $ilDB;

		$affectedRows = $ilDB->manipulateF("UPDATE svy_finished SET lastpage = %s WHERE finished_id = %s",
			array('integer','integer'),
			array(($page_id) ? $page_id : 0, $finished_id)
		);
	}

	function sendNotificationMail($user_id, $anonymize_id, $appr_id)
	{		
		include_once "./Services/User/classes/class.ilObjUser.php";
		include_once "./Services/User/classes/class.ilUserUtil.php";	
		
		// #12755
		$placeholders = array(
			"FIRST_NAME" => "firstname",
			"LAST_NAME" => "lastname",		
			"LOGIN" => "login",		
			// old style
			"firstname" => "firstname"
		);		

		$recipients = preg_split('/,/', $this->mailaddresses);
		foreach ($recipients as $recipient)
		{						
			// #11298		
			include_once "./Services/Notification/classes/class.ilSystemNotification.php";
			$ntf = new ilSystemNotification();
			$ntf->setLangModules(array("survey"));
			$ntf->setRefId($this->getRefId());
			$ntf->setSubjectLangId('finished_mail_subject');
								
			$messagetext = $this->mailparticipantdata;
			if(trim($messagetext))
			{				
				if (!$this->hasAnonymizedResults())
				{
					$data = ilObjUser::_getUserData(array($user_id));
					$data = $data[0];
				}
				foreach ($placeholders as $key => $mapping)
				{									
					if ($this->hasAnonymizedResults()) // #16480
					{
						$messagetext = str_replace('[' . $key . ']', '', $messagetext);
					}
					else
					{									
						$messagetext = str_replace('[' . $key . ']', trim($data[$mapping]), $messagetext);
					}
				}		
				$ntf->setIntroductionDirect($messagetext);
			}
			else
			{
				$ntf->setIntroductionLangId('survey_notification_finished_introduction');
			}
						
			// 360°? add appraisee data
			if($appr_id)
			{										
				$ntf->addAdditionalInfo('survey_360_appraisee', 
					ilUserUtil::getNamePresentation($appr_id));
			}
			
			$active_id = $this->getActiveID($user_id, $anonymize_id, $appr_id);
			$ntf->addAdditionalInfo('results', 
				$this->getParticipantTextResults($active_id), true);
									
			$ntf->setGotoLangId('survey_notification_tutor_link');				
			$ntf->setReasonLangId('survey_notification_finished_reason');	

			$ntf->sendMail(array($recipient), null, null);		
		}															
	}

	protected function getParticipantTextResults($active_id)
	{
		$textresult = "";
		$userResults =& $this->getUserSpecificResults(array($active_id));
		$questions =& $this->getSurveyQuestions(true);
		$questioncounter = 1;
		foreach ($questions as $question_id => $question_data)
		{
			$textresult .= $questioncounter++ . ". " . $question_data["title"] . "\n";
			$found = $userResults[$question_id][$active_id];
			$text = "";
			if (is_array($found))
			{
				$text = implode("\n", $found);
			}
			else
			{
				$text = $found;
			}
			if (strlen($text) == 0) $text = self::getSurveySkippedValue();
			$text = str_replace("<br />", "\n", $text);
			$textresult .= $text . "\n\n";
		}
		return $textresult;
	}

	function getDetailedParticipantResultsAsText()
	{
		$counter = 0;
		$questions =& $this->getSurveyQuestions();
		$counter++;
		foreach ($questions as $data)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question = SurveyQuestion::_instanciateQuestion($data["question_id"]);

			$eval = $this->getCumulatedResults($question);
		}
	}
	
	/**
	* Checks if a user is allowed to take multiple survey
	*
	* @param int $userid user id of the user
	* @return boolean TRUE if the user is allowed to take the survey more than once, FALSE otherwise
	* @access public
	*/
	function isAllowedToTakeMultipleSurveys($userid = "")
	{		
		// #7927: special users are deprecated
		return false;
		
		/*
		$result = FALSE;
		if ($this->getAnonymize())
		{
			if ($this->isAccessibleWithoutCode())
			{
				if (strlen($username) == 0)
				{
					global $ilUser;
					$userid = $ilUser->getId();
				}
				global $ilSetting;
				$surveysetting = new ilSetting("survey");
				$allowedUsers = strlen($surveysetting->get("multiple_survey_users")) ? explode(",",$surveysetting->get("multiple_survey_users")) : array();
				if (in_array($userid, $allowedUsers))
				{
					$result = TRUE;
				}
			}
		}
		return $result;		 
		*/
	}
	
/**
* Checks if a user already started a survey
*
* @param integer $user_id The database id of the user
* @return mixed false, if the user has not started the survey, 0 if the user has started the survey but not finished it, 1 if the user has finished the survey
* @access public
*/
	function isSurveyStarted($user_id, $anonymize_id, $appr_id = 0)
	{
		global $ilDB;

		// #15031 - should not matter if code was used by registered or anonymous (each code must be unique)
		if($anonymize_id)
		{
			$result = $ilDB->queryF("SELECT * FROM svy_finished".
				" WHERE survey_fi = %s AND anonymous_id = %s AND appr_id = %s",
				array('integer','text','integer'),
				array($this->getSurveyId(), $anonymize_id, $appr_id)
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT * FROM svy_finished".
				" WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
				array('integer','integer','integer'),
				array($this->getSurveyId(), $user_id, $appr_id)
			);
		}
		if ($result->numRows() == 0)
		{
			return false;
		}			
		else
		{
			$row = $ilDB->fetchAssoc($result);
			
			// yes, we are doing it this way
			$_SESSION["finished_id"][$this->getId()] = $row["finished_id"];
			
			return (int)$row["state"];
		}

		/*
		if ($this->getAnonymize())
		{
			if ((($user_id != ANONYMOUS_USER_ID) && sizeof($anonymize_id)) && (!($this->isAccessibleWithoutCode() && $this->isAllowedToTakeMultipleSurveys())))
			{
				$result = $ilDB->queryF("SELECT * FROM svy_finished".
					" WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
					array('integer','integer','integer'),
					array($this->getSurveyId(), $user_id, $appr_id)
				);
			}
			else
			{
				$result = $ilDB->queryF("SELECT * FROM svy_finished".
					" WHERE survey_fi = %s AND anonymous_id = %s AND appr_id = %s",
					array('integer','text','integer'),
					array($this->getSurveyId(), $anonymize_id, $appr_id)
				);
			}
		}
		else
		{
			$result = $ilDB->queryF("SELECT * FROM svy_finished".
				" WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
				array('integer','integer','integer'),
				array($this->getSurveyId(), $user_id, $appr_id)
			);
		}
		if ($result->numRows() == 0)
		{
			return false;
		}			
		else
		{
			$row = $ilDB->fetchAssoc($result);
			$_SESSION["finished_id"][$this->getId()] = $row["finished_id"];
			return (int)$row["state"];
		}		
		*/
	}

	/**
	* Checks if a user already started a survey
	*
	* @param integer $user_id The database id of the user
	* @return mixed false, if the user has not started the survey, 0 if the user has started the survey but not finished it, 1 if the user has finished the survey
	* @access public
	*/
	function getActiveID($user_id, $anonymize_id, $appr_id)
	{
		global $ilDB;

		// see self::isSurveyStarted()
		
		// #15031 - should not matter if code was used by registered or anonymous (each code must be unique)
		if($anonymize_id)
		{
			$result = $ilDB->queryF("SELECT finished_id FROM svy_finished".
				" WHERE survey_fi = %s AND anonymous_id = %s AND appr_id = %s",
				array('integer','text','integer'),
				array($this->getSurveyId(), $anonymize_id, $appr_id)
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT finished_id FROM svy_finished".
				" WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
				array('integer','integer','integer'),
				array($this->getSurveyId(), $user_id, $appr_id)
			);
		}
		if ($result->numRows() == 0)
		{
			return false;
		}			
		else
		{
			$row = $ilDB->fetchAssoc($result);			
			return $row["finished_id"];
		}	

		/*
		if ($this->getAnonymize())
		{
			if ((($user_id != ANONYMOUS_USER_ID) && (strlen($anonymize_id) == 0)) && (!($this->isAccessibleWithoutCode() && $this->isAllowedToTakeMultipleSurveys())))
			{
				$result = $ilDB->queryF("SELECT finished_id FROM svy_finished".
					" WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
					array('integer','integer','integer'),
					array($this->getSurveyId(), $user_id, $appr_id)
				);
			}
			else
			{
				$result = $ilDB->queryF("SELECT finished_id FROM svy_finished".
					" WHERE survey_fi = %s AND anonymous_id = %s AND appr_id = %s",
					array('integer','text','integer'),
					array($this->getSurveyId(), $anonymize_id, $appr_id)
				);
			}
		}
		else
		{
			$result = $ilDB->queryF("SELECT finished_id FROM svy_finished".
				" WHERE survey_fi = %s AND user_fi = %s AND appr_id = %s",
				array('integer','integer','integer'),
				array($this->getSurveyId(), $user_id, $appr_id)
			);
		}
		if ($result->numRows() == 0)
		{
			return false;
		}			
		else
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["finished_id"];
		}		 
		*/
	}
	
/**
* Returns the question id of the last active page a user visited in a survey
*
* @param integer $active_id The active id of the user
* @return mixed Empty string if the user has not worked through a page, question id of the last page otherwise
* @access public
*/
	function getLastActivePage($active_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT lastpage FROM svy_finished WHERE finished_id = %s",
			array('integer'),
			array($active_id)
		);
		if ($result->numRows() == 0)
		{
			return "";
		}
		else
		{
			$row = $ilDB->fetchAssoc($result);
			return ($row["lastpage"]) ? $row["lastpage"] : '';
		}
	}

/**
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
		
		$found = false;
		foreach ($working_data as $data)
		{
			switch ($constraint_data["short"])
			{
				case "<":
					if ($data["value"] < $constraint_data["value"])
					{
						$found = true;
					}
					break;
					
				case "<=":
					if ($data["value"] <= $constraint_data["value"])
					{
						$found = true;
					}
					break;
				
				case "=":
					if ($data["value"] == $constraint_data["value"])
					{
						$found = true;
					}
					break;
																			
				case "<>":
					if ($data["value"] <> $constraint_data["value"])
					{
						$found = true;
					}					
					break;
					
				case ">=":					
					if ($data["value"] >= $constraint_data["value"])
					{
						$found = true;
					}				
					break;
					
				case ">":
					if ($data["value"] > $constraint_data["value"])
					{
						$found = true;
					}
					break;
			}					
			if ($found)
			{
				break;
			}
		}
		
		return (int)$found;
	}
	
	function _hasDatasets($survey_id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT finished_id FROM svy_finished WHERE survey_fi = %s",
			array('integer'),
			array($survey_id)
		);
		return ($result->numRows()) ? true : false;
	}

	/**
	* Get the finished id's of all survey participants
	*
	* @return array An array containing finished_id's of all survey participants
	* @access public
	*/
	function &getSurveyFinishedIds()
	{
		global $ilDB, $ilLog;
		
		$users = array();
		$result = $ilDB->queryF("SELECT * FROM svy_finished WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		if ($result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				array_push($users, $row["finished_id"]);
			}
		}
		return $users;
	}
	
/**
* Calculates the evaluation data for the user specific results
*
* @return array An array containing the user specific results
* @access public
*/
	function &getUserSpecificResults($finished_ids)
	{
		global $ilDB;
		
		$evaluation = array();
		$questions =& $this->getSurveyQuestions();
		foreach ($questions as $question_id => $question_data)
		{
			include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
			$question_type = SurveyQuestion::_getQuestionType($question_id);
			SurveyQuestion::_includeClass($question_type);
			$question = new $question_type();
			$question->loadFromDb($question_id);
			$data =& $question->getUserAnswers($this->getSurveyId(), $finished_ids);
			$evaluation[$question_id] = $data;
		}
		return $evaluation;
	}
	
	/**
	* Returns the user information from an active_id (survey_finished.finished_id)
	*
	* @param integer $active_id The active id of the user
	* @return array An array containing the user data
	* @access public
	*/
	function getUserDataFromActiveId($active_id)
	{
		global $ilDB;

		$surveySetting = new ilSetting("survey");
		$use_anonymous_id = array_key_exists("use_anonymous_id", $_GET) ? $_GET["use_anonymous_id"] : $surveySetting->get("use_anonymous_id");
		$result = $ilDB->queryF("SELECT * FROM svy_finished WHERE finished_id = %s",
			array('integer'),
			array($active_id)
		);
		$row = array();
		$foundrows = $result->numRows();
		if ($foundrows)
		{
			$row = $ilDB->fetchAssoc($result);
		}
		$name = ($use_anonymous_id) ? $row["anonymous_id"] : $this->lng->txt("anonymous");
		$userdata = array(
			"fullname" => $name,
			"sortname" => $name,
			"firstname" => "",
			"lastname" => "",
			"login" => "",
			"gender" => "",
			"active_id" => "$active_id"
		);
		if ($foundrows)
		{
			if (($row["user_fi"] > 0) && ($row["user_fi"] != ANONYMOUS_USER_ID) && 				
				!$this->hasAnonymizedResults() &&
				!$this->get360Mode()) // 360° uses ANONYMIZE_CODE_ALL which is wrong - see ilObjSurveyGUI::afterSave()
			{
				include_once './Services/User/classes/class.ilObjUser.php';
				if (strlen(ilObjUser::_lookupLogin($row["user_fi"])) == 0)
				{
					$userdata["fullname"] = $userdata["sortname"] = $this->lng->txt("deleted_user");
				}
				else
				{
					$user = new ilObjUser($row["user_fi"]);
					$userdata["fullname"] = $user->getFullname();
					$gender = $user->getGender();
					if (strlen($gender) == 1) $gender = $this->lng->txt("gender_$gender");
					$userdata["gender"] = $gender;
					$userdata["firstname"] = $user->getFirstname();
					$userdata["lastname"] = $user->getLastname();
					$userdata["sortname"] = $user->getLastname() . ", " . $user->getFirstname();
					$userdata["login"] = $user->getLogin();
				}
			}
		}
		return $userdata;
	}
	
/**
* Calculates the evaluation data for a given user or anonymous id
*
* @param array $questions An array containing all relevant information on the survey's questions
* @param integer $user_id The database id of the user
* @param string $anonymous_id The unique anonymous id for an anonymous survey
* @return array An array containing the evaluation parameters for the user
* @access public
*/
	function &getEvaluationByUser($questions, $active_id)
	{
		global $ilDB;
		
		// collect all answers
		$answers = array();
		$result = $ilDB->queryF("SELECT * FROM svy_answer WHERE active_fi = %s",
			array('integer'),
			array($active_id)
		);
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (!is_array($answers[$row["question_fi"]]))
			{
				$answers[$row["question_fi"]] = array();
			}
			array_push($answers[$row["question_fi"]], $row);
		}
		$userdata = $this->getUserDataFromActiveId($active_id);
		$resultset = array(
			"name" => $userdata["fullname"],
			"firstname" => $userdata["firstname"],
			"lastname" => $userdata["lastname"],
			"login" => $userdata["login"],
			"gender" => $userdata["gender"],
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
* @param integer $question_id The database id of the question
* @param integer $user_id The database id of the user
* @return array An array containing the evaluation parameters for the question
* @access public
*/
	function getCumulatedResults(&$question, $finished_ids)
	{
		global $ilDB;
		
		if(!$finished_ids)
		{
			$result = $ilDB->queryF("SELECT finished_id FROM svy_finished WHERE survey_fi = %s",
				array('integer'),
				array($this->getSurveyId())
			);
			$nr_of_users = $result->numRows();
		}
		else
		{
			$nr_of_users = sizeof($finished_ids);
		}
		
		$result_array =& $question->getCumulatedResults($this->getSurveyId(), $nr_of_users, $finished_ids);
		return $result_array;
	}

/**
* Returns the number of participants for a survey
*
* @param integer $survey_id The database ID of the survey
* @return integer The number of participants
* @access public
*/
	function _getNrOfParticipants($survey_id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT finished_id FROM svy_finished WHERE survey_fi = %s",
			array('integer'),
			array($survey_id)
		);
		return $result->numRows();
	}

	function &getQuestions($question_ids)
	{
		global $ilDB;
		
		$result_array = array();
		$result = $ilDB->query("SELECT svy_question.*, svy_qtype.type_tag FROM svy_question, svy_qtype WHERE ".
			"svy_question.questiontype_fi = svy_qtype.questiontype_id AND svy_question.tstamp > 0 AND ".
			$ilDB->in('svy_question.question_id', $question_ids, false, 'integer'));
		while ($row = $ilDB->fetchAssoc($result))
		{
			array_push($result_array, $row);
		}
		return $result_array;
	}
	
/**
* Calculates the data for the output of the question browser
*
* @access public
*/
	function getQuestionsTable($arrFilter)
	{
		global $ilUser;
		global $ilDB;
		$where = "";
		if (is_array($arrFilter))
		{
			if (array_key_exists('title', $arrFilter) && strlen($arrFilter['title']))
			{
				$where .= " AND " . $ilDB->like('svy_question.title', 'text', "%%" . $arrFilter['title'] . "%%");
			}
			if (array_key_exists('description', $arrFilter) && strlen($arrFilter['description']))
			{
				$where .= " AND " . $ilDB->like('svy_question.description', 'text', "%%" . $arrFilter['description'] . "%%");
			}
			if (array_key_exists('author', $arrFilter) && strlen($arrFilter['author']))
			{
				$where .= " AND " . $ilDB->like('svy_question.author', 'text', "%%" . $arrFilter['author'] . "%%");
			}
			if (array_key_exists('type', $arrFilter) && strlen($arrFilter['type']))
			{
				$where .= " AND svy_qtype.type_tag = " . $ilDB->quote($arrFilter['type'], 'text');
			}
			if (array_key_exists('spl', $arrFilter) && strlen($arrFilter['spl']))
			{
				$where .= " AND svy_question.obj_fi = " . $ilDB->quote($arrFilter['spl'], 'integer');
			}		
		}
		
		$spls =& $this->getAvailableQuestionpools($use_obj_id = TRUE, $could_be_offline = FALSE, $showPath = FALSE);
		$forbidden = "";
		$forbidden = " AND " . $ilDB->in('svy_question.obj_fi', array_keys($spls), false, 'integer');
		$forbidden .= " AND svy_question.complete = " . $ilDB->quote("1", 'text');
		$existing = "";
		$existing_questions =& $this->getExistingQuestions();
		if (count($existing_questions))
		{
			$existing = " AND " . $ilDB->in('svy_question.question_id', $existing_questions, true, 'integer');
		}
		
		include_once "./Modules/SurveyQuestionPool/classes/class.ilObjSurveyQuestionPool.php";
		$trans = ilObjSurveyQuestionPool::_getQuestionTypeTranslations();
		
		$query_result = $ilDB->query("SELECT svy_question.*, svy_qtype.type_tag, svy_qtype.plugin, object_reference.ref_id".
			" FROM svy_question, svy_qtype, object_reference".
			" WHERE svy_question.original_id IS NULL".$forbidden.$existing.
			" AND svy_question.obj_fi = object_reference.obj_id AND svy_question.tstamp > 0".
			" AND svy_question.questiontype_fi = svy_qtype.questiontype_id " . $where);

		$rows = array();
		if ($query_result->numRows())
		{
			while ($row = $ilDB->fetchAssoc($query_result))
			{
				if (array_key_exists('spl_txt', $arrFilter) && strlen($arrFilter['spl_txt']))
				{
					if(!stristr($spls[$row["obj_fi"]], $arrFilter['spl_txt']))
					{
						continue;
					}
				}
				
				$row['ttype'] = $trans[$row['type_tag']];
				if ($row["plugin"])
				{
					if ($this->isPluginActive($row["type_tag"]))
					{
						array_push($rows, $row);
					}
				}
				else
				{
					array_push($rows, $row);
				}
			}
		}
		return $rows;
	}

/**
* Calculates the data for the output of the questionblock browser
*
* @access public
*/
	function getQuestionblocksTable($arrFilter)
	{
		global $ilUser, $ilDB;
		
		$where = "";
		if (is_array($arrFilter))
		{
			if (array_key_exists('title', $arrFilter) && strlen($arrFilter['title']))
			{
				$where .= " AND " . $ilDB->like('svy_qblk.title', 'text', "%%" . $arrFilter['title'] . "%%");
			}
		}
  
		$query_result = $ilDB->query("SELECT svy_qblk.*, svy_svy.obj_fi FROM svy_qblk , svy_qblk_qst, svy_svy WHERE ".
			"svy_qblk.questionblock_id = svy_qblk_qst.questionblock_fi AND svy_svy.survey_id = svy_qblk_qst.survey_fi ".
			"$where GROUP BY svy_qblk.questionblock_id, svy_qblk.title, svy_qblk.show_questiontext,  svy_qblk.show_blocktitle, ".
			"svy_qblk.owner_fi, svy_qblk.tstamp, svy_svy.obj_fi");
		$rows = array();
		if ($query_result->numRows())
		{
			$survey_ref_ids = ilUtil::_getObjectsByOperations("svy", "write");
			$surveytitles = array();
			foreach ($survey_ref_ids as $survey_ref_id)
			{
				$survey_id = ilObject::_lookupObjId($survey_ref_id);
				$surveytitles[$survey_id] = ilObject::_lookupTitle($survey_id);				
			}
			while ($row = $ilDB->fetchAssoc($query_result))
			{
				$questions_array =& $this->getQuestionblockQuestions($row["questionblock_id"]);
				$counter = 1;
				foreach ($questions_array as $key => $value)
				{
					$questions_array[$key] = "$counter. $value";
					$counter++;
				}
				if (strlen($surveytitles[$row["obj_fi"]])) // only questionpools which are not in trash
				{
					$rows[$row["questionblock_id"]] = array(
						"questionblock_id" => $row["questionblock_id"],
						"title" => $row["title"], 
						"svy" => $surveytitles[$row["obj_fi"]], 
						"contains" => join($questions_array, ", "),
						"owner" => $row["owner_fi"]
					);
				}
			}
		}
		return $rows;
	}

	/**
	* Returns a QTI xml representation of the survey
	*
	* @return string The QTI xml representation of the survey
	* @access public
	*/
	function toXML()
	{
		include_once("./Services/Xml/classes/class.ilXmlWriter.php");
		$a_xml_writer = new ilXmlWriter;
		// set xml header
		$a_xml_writer->xmlHeader();
		$attrs = array(
			"xmlns:xsi" => "http://www.w3.org/2001/XMLSchema-instance",
			"xsi:noNamespaceSchemaLocation" => "http://www.ilias.de/download/xsd/ilias_survey_4_2.xsd"
		);
		$a_xml_writer->xmlStartTag("surveyobject", $attrs);
		$attrs = array(
			"id" => $this->getSurveyId(),
			"title" => $this->getTitle()
		);
		$a_xml_writer->xmlStartTag("survey", $attrs);
		
		$a_xml_writer->xmlElement("description", NULL, $this->getDescription());
		$a_xml_writer->xmlElement("author", NULL, $this->getAuthor());
		$a_xml_writer->xmlStartTag("objectives");
		$attrs = array(
			"label" => "introduction"
		);
		$this->addMaterialTag($a_xml_writer, $this->getIntroduction(), TRUE, TRUE, $attrs);
		$attrs = array(
			"label" => "outro"
		);
		$this->addMaterialTag($a_xml_writer, $this->getOutro(), TRUE, TRUE, $attrs);
		$a_xml_writer->xmlEndTag("objectives");

		if ($this->getAnonymize())
		{
			$attribs = array("enabled" => "1");
		}
		else
		{
			$attribs = array("enabled" => "0");
		}
		$a_xml_writer->xmlElement("anonymisation", $attribs);
		$a_xml_writer->xmlStartTag("restrictions");
		if ($this->getAnonymize() == 2)
		{
			$attribs = array("type" => "free");
		}
		else
		{
			$attribs = array("type" => "restricted");
		}
		$a_xml_writer->xmlElement("access", $attribs);
		if ($this->getStartDate())
		{
			$attrs = array("type" => "date");			
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getStartDate(), $matches);
			$a_xml_writer->xmlElement("startingtime", $attrs, sprintf("%04d-%02d-%02dT%02d:%02d:00", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
		}
		if ($this->getEndDate())
		{
			$attrs = array("type" => "date");
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $this->getEndDate(), $matches);
			$a_xml_writer->xmlElement("endingtime", $attrs, sprintf("%04d-%02d-%02dT%02d:%02d:00", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));

		}
		$a_xml_writer->xmlEndTag("restrictions");
		
		// constraints
		$pages =& $this->getSurveyPages();
		$hasconstraints = FALSE;
		foreach ($pages as $question_array)
		{
			foreach ($question_array as $question)
			{
				if (count($question["constraints"]))
				{
					$hasconstraints = TRUE;
				}
			}
		}
		
		if ($hasconstraints)
		{
			$a_xml_writer->xmlStartTag("constraints");
			foreach ($pages as $question_array)
			{
				foreach ($question_array as $question)
				{
					if (count($question["constraints"]))
					{
						// found constraints
						foreach ($question["constraints"] as $constraint)
						{							
							$attribs = array(
								"sourceref" => $question["question_id"],
								"destref" => $constraint["question"],
								"relation" => $constraint["short"],
								"value" => $constraint["value"],
								"conjunction" => $constraint["conjunction"]
							);
							$a_xml_writer->xmlElement("constraint", $attribs);
						}
					}
				}
			}
			$a_xml_writer->xmlEndTag("constraints");
		}
		
		// add the rest of the preferences in qtimetadata tags, because there is no correspondent definition in QTI
		$a_xml_writer->xmlStartTag("metadata");

		$custom_properties = array();
		$custom_properties["evaluation_access"] = $this->getEvaluationAccess();
		$custom_properties["status"] = $this->getStatus();
		$custom_properties["display_question_titles"] = $this->getShowQuestionTitles();
		$custom_properties["pool_usage"] = (int)$this->getPoolUsage();
		
		$custom_properties["own_results_view"] = (int)$this->hasViewOwnResults();
		$custom_properties["own_results_mail"] = (int)$this->hasMailOwnResults();
		
		$custom_properties["mode_360"] = (int)$this->get360Mode();
		$custom_properties["mode_360_self_eval"] = (int)$this->get360SelfEvaluation();
		$custom_properties["mode_360_self_rate"] = (int)$this->get360SelfRaters();
		$custom_properties["mode_360_self_appr"] = (int)$this->get360SelfAppraisee();
		$custom_properties["mode_360_results"] = $this->get360Results();
		$custom_properties["mode_360_skill_service"] = (int)$this->get360SkillService();
		
		
		// :TODO: skills?
				
		// reminder/tutor notification are (currently?) not exportable
		
		foreach($custom_properties as $label => $value)
		{
			$a_xml_writer->xmlStartTag("metadatafield");
			$a_xml_writer->xmlElement("fieldlabel", NULL, $label);
			$a_xml_writer->xmlElement("fieldentry", NULL, $value);
			$a_xml_writer->xmlEndTag("metadatafield");
		}

		$a_xml_writer->xmlStartTag("metadatafield");
		$a_xml_writer->xmlElement("fieldlabel", NULL, "SCORM");
		include_once "./Services/MetaData/classes/class.ilMD.php";
		$md = new ilMD($this->getId(),0, $this->getType());
		$writer = new ilXmlWriter();
		$md->toXml($writer);
		$metadata = $writer->xmlDumpMem();
		$a_xml_writer->xmlElement("fieldentry", NULL, $metadata);
		$a_xml_writer->xmlEndTag("metadatafield");
		
		$a_xml_writer->xmlEndTag("metadata");
		$a_xml_writer->xmlEndTag("survey");

		$attribs = array("id" => $this->getId());
		$a_xml_writer->xmlStartTag("surveyquestions", $attribs);
		// add questionblock descriptions
		$obligatory_states =& $this->getObligatoryStates();
		foreach ($pages as $question_array)
		{
			if (count($question_array) > 1)
			{
				$attribs = array("id" => $question_array[0]["question_id"]);
				$attribs = array("showQuestiontext" => $question_array[0]["questionblock_show_questiontext"],
					"showBlocktitle" => $question_array[0]["questionblock_show_blocktitle"]);
				$a_xml_writer->xmlStartTag("questionblock", $attribs);
				if (strlen($question_array[0]["questionblock_title"]))
				{
					$a_xml_writer->xmlElement("questionblocktitle", NULL, $question_array[0]["questionblock_title"]);
				}
			}
			foreach ($question_array as $question)
			{
				if (strlen($question["heading"]))
				{
					$a_xml_writer->xmlElement("textblock", NULL, $question["heading"]);
				}
				$questionObject =& $this->_instanciateQuestion($question["question_id"]);
				if ($questionObject !== FALSE) $questionObject->insertXML($a_xml_writer, FALSE, $obligatory_states[$question["question_id"]]);
			}
			if (count($question_array) > 1)
			{
				$a_xml_writer->xmlEndTag("questionblock");
			}
		}

		$a_xml_writer->xmlEndTag("surveyquestions");
		$a_xml_writer->xmlEndTag("surveyobject");
		$xml = $a_xml_writer->xmlDumpMem(FALSE);
		return $xml;
	}
	
/**
* Creates an instance of a question with a given question id
*
* @param integer $question_id The question id
* @return object The question instance
* @access public
*/
  function &_instanciateQuestion($question_id) 
	{
		if ($question_id < 1) return FALSE;
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		$question_type = SurveyQuestion::_getQuestionType($question_id);
		if (strlen($question_type) == 0) return FALSE;
		SurveyQuestion::_includeClass($question_type);
		$question = new $question_type();
		$question->loadFromDb($question_id);
		return $question;
  }

	/**
	* Locates the import directory and the xml file in a directory with an unzipped import file
	*
	* @return array An associative array containing "dir" (import directory) and "xml" (xml file)
	* @access private
	*/
	function locateImportFiles($a_dir)
	{
		if (!is_dir($a_dir) || is_int(strpos($a_dir, "..")))
		{
			return;
		}
		$importDirectory = "";
		$xmlFile = "";

		$current_dir = opendir($a_dir);
		$files = array();
		while($entryname = readdir($current_dir))
		{
			$files[] = $entryname;
		}

		foreach($files as $file)
		{
			if(is_dir($a_dir."/".$file) and ($file != "." and $file!=".."))
			{
				// found directory created by zip
				$importDirectory = $a_dir."/".$file;
			}
		}
		closedir($current_dir);
		if (strlen($importDirectory))
		{
			// find the xml file
			$current_dir = opendir($importDirectory);
			$files = array();
			while($entryname = readdir($current_dir))
			{
				$files[] = $entryname;
			}
			foreach($files as $file)
			{
				if(@is_file($importDirectory."/".$file) && ($file != "." && $file!="..") && (ereg("^[0-9]{10}_{2}[0-9]+_{2}(svy_)*[0-9]+\.[a-z]{1,3}\$", $file) || ereg("^[0-9]{10}_{2}[0-9]+_{2}(survey__)*[0-9]+\.[a-z]{1,3}\$", $file)))
				{
					// found xml file
					$xmlFile = $importDirectory."/".$file;
				}
			}
		}
		return array("dir" => $importDirectory, "xml" => $xmlFile);
	}

	/**
	* Imports a survey from XML into the ILIAS database
	*
	* @return boolean True, if the import succeeds, false otherwise
	* @access public
	*/
	function importObject($file_info, $svy_qpl_id)
	{
		if ($svy_qpl_id < 1) $svy_qpl_id = -1;
		// check if file was uploaded
		$source = $file_info["tmp_name"];
		$error = "";
		if (($source == 'none') || (!$source) || $file_info["error"] > UPLOAD_ERR_OK)
		{
			$error = $this->lng->txt("import_no_file_selected");
		}
		// check correct file type
		$isXml = FALSE;
		$isZip = FALSE;
		if ((strcmp($file_info["type"], "text/xml") == 0) || (strcmp($file_info["type"], "application/xml") == 0))
		{
			$isXml = TRUE;
		}
		// too many different mime-types, so we use the suffix
		$suffix = pathinfo($file_info["name"]);
		if (strcmp(strtolower($suffix["extension"]), "zip") == 0)
		{
			$isZip = TRUE;
		}
		if (!$isXml && !$isZip)
		{
			$error = $this->lng->txt("import_wrong_file_type");
			global $ilLog;
			$ilLog->write("Survey: Import error. Filetype was \"" . $file_info["type"] ."\"");
		}
		if (strlen($error) == 0)
		{
			// import file as a survey
			$import_dir = $this->getImportDirectory();
			$import_subdir = "";
			$importfile = "";
			include_once "./Services/Utilities/classes/class.ilUtil.php";
			if ($isZip)
			{
				$importfile = $import_dir."/".$file_info["name"];
				ilUtil::moveUploadedFile($source, $file_info["name"], $importfile);
				ilUtil::unzip($importfile);
				$found = $this->locateImportFiles($import_dir);
				if (!((strlen($found["dir"]) > 0) && (strlen($found["xml"]) > 0)))
				{
					$error = $this->lng->txt("wrong_import_file_structure");
					return $error;
				}
				$importfile = $found["xml"];
				$import_subdir = $found["dir"];
			}
			else
			{
				$importfile = tempnam($import_dir, "survey_import");
				ilUtil::moveUploadedFile($source, $file_info["name"], $importfile);
			}
			$fh = fopen($importfile, "r");
			if (!$fh)
			{
				$error = $this->lng->txt("import_error_opening_file");
				return $error;
			}
			$xml = fread($fh, filesize($importfile));
			$result = fclose($fh);
			if (!$result)
			{
				$error = $this->lng->txt("import_error_closing_file");
				return $error;
			}

			unset($_SESSION["import_mob_xhtml"]);
			if (strpos($xml, "questestinterop"))
			{
				include_once "./Services/Survey/classes/class.SurveyImportParserPre38.php";
				$import = new SurveyImportParserPre38($svy_qpl_id, "", TRUE);
				$import->setSurveyObject($this);
				$import->setXMLContent($xml);
				$import->startParsing();
			}
			else
			{
				include_once "./Services/Survey/classes/class.SurveyImportParser.php";
				$import = new SurveyImportParser($svy_qpl_id, "", TRUE);
				$import->setSurveyObject($this);
				$import->setXMLContent($xml);
				$import->startParsing();
			}

			if (is_array($_SESSION["import_mob_xhtml"]))
			{
				include_once "./Services/MediaObjects/classes/class.ilObjMediaObject.php";
				include_once "./Services/RTE/classes/class.ilRTE.php";
				include_once "./Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php";
				foreach ($_SESSION["import_mob_xhtml"] as $mob)
				{
					$importfile = $import_subdir . "/" . $mob["uri"];
					if (file_exists($importfile))
					{						
						if (!$mob["type"])
						{
							$mob["type"] = "svy:html";
						}
						
						$media_object = ilObjMediaObject::_saveTempFileAsMediaObject(basename($importfile), $importfile, FALSE);
						
						// survey mob
						if ($mob["type"] == "svy:html")
						{													
							ilObjMediaObject::_saveUsage($media_object->getId(), "svy:html", $this->getId());
							$this->setIntroduction(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->getIntroduction()));
							$this->setOutro(str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $this->getOutro()));
						}
						// question mob
						else if($import->questions[$mob["id"]])
						{
							$new_qid = $import->questions[$mob["id"]];							
							ilObjMediaObject::_saveUsage($media_object->getId(), $mob["type"], $new_qid);						
							$new_question = SurveyQuestion::_instanciateQuestion($new_qid);			
							$qtext = $new_question->getQuestiontext();
							$qtext = ilRTE::_replaceMediaObjectImageSrc($qtext, 0);							
							$qtext = str_replace("src=\"" . $mob["mob"] . "\"", "src=\"" . "il_" . IL_INST_ID . "_mob_" . $media_object->getId() . "\"", $qtext);							
							$qtext = ilRTE::_replaceMediaObjectImageSrc($qtext, 1);									
							$new_question->setQuestiontext($qtext);
							$new_question->saveToDb();		
							
							// also fix existing original in pool
							if($new_question->getOriginalId())
							{
								$pool_question = SurveyQuestion::_instanciateQuestion($new_question->getOriginalId());
								$pool_question->setQuestiontext($qtext);
								$pool_question->saveToDb();		
							}
						}						
					}
					else
					{
						global $ilLog;
						$ilLog->write("Error: Could not open XHTML mob file for test introduction during test import. File $importfile does not exist!");
					}
				}
				$this->setIntroduction(ilRTE::_replaceMediaObjectImageSrc($this->getIntroduction(), 1));
				$this->setOutro(ilRTE::_replaceMediaObjectImageSrc($this->getOutro(), 1));
				$this->saveToDb();
			}

			// delete import directory
			ilUtil::delDir($this->getImportDirectory());
		}
		return $error;
	}

	/**
	 * Clone object
	 *
	 * @access public
	 * @param int ref_id of target container
	 * @param int copy id
	 * @return object new svy object
	 */
	public function cloneObject($a_target_id,$a_copy_id = 0)
	{
		global $ilDB;
		
		$this->loadFromDb();
		
		// Copy settings
		$newObj = parent::cloneObject($a_target_id,$a_copy_id);
		$this->cloneMetaData($newObj);
		$newObj->updateMetaData();
	 	
		$newObj->setAuthor($this->getAuthor());
		$newObj->setIntroduction($this->getIntroduction());
		$newObj->setOutro($this->getOutro());
		$newObj->setEvaluationAccess($this->getEvaluationAccess());
		$newObj->setStartDate($this->getStartDate());
		$newObj->setEndDate($this->getEndDate());
		$newObj->setInvitation($this->getInvitation());
		$newObj->setInvitationMode($this->getInvitationMode());
		$newObj->setAnonymize($this->getAnonymize());
		$newObj->setShowQuestionTitles($this->getShowQuestionTitles());
		$newObj->setTemplate($this->getTemplate());
		$newObj->setPoolUsage($this->getPoolUsage());
		$newObj->setViewOwnResults($this->hasViewOwnResults());
		$newObj->setMailOwnResults($this->hasMailOwnResults());
		
		// #12661
		if($this->get360Mode())
		{
			$newObj->set360Mode(true);
			$newObj->set360SelfEvaluation($this->get360SelfEvaluation());
			$newObj->set360SelfAppraisee($this->get360SelfAppraisee());
			$newObj->set360SelfRaters($this->get360SelfRaters());
			$newObj->set360Results($this->get360Results());
			$newObj->set360SkillService($this->get360SkillService());			
		}
				
		// reminder/notification
		$newObj->setReminderStatus($this->getReminderStatus());
		$newObj->setReminderStart($this->getReminderStart());
		$newObj->setReminderEnd($this->getReminderEnd());
		$newObj->setReminderFrequency($this->getReminderFrequency());
		$newObj->setReminderTarget($this->getReminderTarget());
		// reminder_last_sent must not be copied!
		$newObj->setTutorNotificationStatus($this->getTutorNotificationStatus());
		$newObj->setTutorNotificationRecipients($this->getTutorNotificationRecipients());
		$newObj->setTutorNotificationTarget($this->getTutorNotificationTarget());

		$question_pointer = array();
		// clone the questions
		$mapping = array();
		include_once "./Modules/SurveyQuestionPool/classes/class.SurveyQuestion.php";
		
		foreach ($this->questions as $key => $question_id)
		{
			$question = ilObjSurvey::_instanciateQuestion($question_id);
			if($question) // #10824
			{
				$question->id = -1;
				$original_id = SurveyQuestion::_getOriginalId($question_id, false);
				$question->saveToDb($original_id);
				$newObj->questions[$key] = $question->getId();
				$question_pointer[$question_id] = $question->getId();
				$mapping[$question_id] = $question->getId();				
			}
		}

		//copy online status if object is not the root copy object
		$cp_options = ilCopyWizardOptions::_getInstance($a_copy_id);

		if(!$cp_options->isRootNode($this->getRefId()))
		{
			$newObj->setStatus($this->isOnline()?self::STATUS_ONLINE: self::STATUS_OFFLINE);
		}

		$newObj->saveToDb();		
		$newObj->cloneTextblocks($mapping);
		
		// #14929
		if($this->get360Mode() &&
			$this->get360SkillService())
		{
			include_once "./Modules/Survey/classes/class.ilSurveySkill.php";
			$src_skills = new ilSurveySkill($this);
			$tgt_skills = new ilSurveySkill($newObj);
			
			foreach($mapping as $src_qst_id => $tgt_qst_id)
			{
				$qst_skill = $src_skills->getSkillForQuestion($src_qst_id);
				if($qst_skill)
				{
					$tgt_skills->addQuestionSkillAssignment($tgt_qst_id, $qst_skill["base_skill_id"], $qst_skill["tref_id"]);
				}
			}
		}

		// clone the questionblocks
		$questionblocks = array();
		$questionblock_questions = array();
		$result = $ilDB->queryF("SELECT * FROM svy_qblk_qst WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		if ($result->numRows() > 0)
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				array_push($questionblock_questions, $row);
				$questionblocks[$row["questionblock_fi"]] = $row["questionblock_fi"];
			}
		}
		// create new questionblocks
		foreach ($questionblocks as $key => $value)
		{
			$questionblock = ilObjSurvey::_getQuestionblock($key); 
			$questionblock_id = ilObjSurvey::_addQuestionblock($questionblock["title"], $questionblock["owner_fi"], $questionblock["show_questiontext"], $questionblock["show_blocktitle"]);
			$questionblocks[$key] = $questionblock_id;
		}
		// create new questionblock questions
		foreach ($questionblock_questions as $key => $value)
		{
			if($questionblocks[$value["questionblock_fi"]] &&
				$question_pointer[$value["question_fi"]])
			{
				$next_id = $ilDB->nextId('svy_qblk_qst');
				$affectedRows = $ilDB->manipulateF("INSERT INTO svy_qblk_qst (qblk_qst_id, survey_fi, questionblock_fi, question_fi) ".
					"VALUES (%s, %s, %s, %s)",
					array('integer','integer','integer','integer'),
					array($next_id, $newObj->getSurveyId(), $questionblocks[$value["questionblock_fi"]], $question_pointer[$value["question_fi"]])
				);
			}
		}
		
		// clone the constraints
		$constraints = ilObjSurvey::_getConstraints($this->getSurveyId());
		$newConstraints = array();
		foreach ($constraints as $key => $constraint)
		{
			if ($question_pointer[$constraint["for_question"]] &&
				$question_pointer[$constraint["question"]])
			{
				if (!array_key_exists($constraint['id'], $newConstraints))
				{
					$constraint_id = $newObj->addConstraint($question_pointer[$constraint["question"]], $constraint["relation_id"], $constraint["value"], $constraint['conjunction']);
					$newConstraints[$constraint['id']] = $constraint_id;
				}
				$newObj->addConstraintToQuestion($question_pointer[$constraint["for_question"]], $newConstraints[$constraint['id']]);
			}
		}
		
		// clone the obligatory states
		$result = $ilDB->queryF("SELECT * FROM svy_qst_oblig WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		if ($result->numRows() > 0)
		{
			while ($row = $ilDB->fetchAssoc($result))
			{
				if($question_pointer[$row["question_fi"]])
				{
					$next_id = $ilDB->nextId('svy_qst_oblig');
					$affectedRows = $ilDB->manipulateF("INSERT INTO svy_qst_oblig (question_obligatory_id, survey_fi, question_fi, ".
						"obligatory, tstamp) VALUES (%s, %s, %s, %s, %s)",
						array('integer','integer','integer','text','integer'),
						array($next_id, $newObj->getSurveyId(), $question_pointer[$row["question_fi"]], $row["obligatory"], time())
					);
				}
			}
		}
		return $newObj;
	}
	
	function getTextblock($question_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT * FROM svy_svy_qst WHERE question_fi = %s",
			array('integer'),
			array($question_id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["heading"];
		}
		else
		{
			return "";
		}
	}

	/**
	* Clones the textblocks of survey questions
	*
	* @access public
	*/
	function cloneTextblocks($mapping)
	{
		foreach ($mapping as $original_id => $new_id)
		{
			$textblock = $this->getTextblock($original_id);
			include_once "./Services/AdvancedEditing/classes/class.ilObjAdvancedEditing.php";
			$this->saveHeading(ilUtil::stripSlashes($textblock, TRUE, ilObjAdvancedEditing::_getUsedHTMLTagsAsString("survey")), $new_id);
		}
	}

	/**
	* creates data directory for export files
	* (data_dir/svy_data/svy_<id>/export, depending on data
	* directory that is set in ILIAS setup/ini)
	*/
	function createExportDirectory()
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
			if ($entry != "." && $entry != ".." && ereg("^[0-9]{10}_{2}[0-9]+_{2}(svy_)*[0-9]+\.[a-z]{1,3}\$", $entry))
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
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
		include_once "./Services/Utilities/classes/class.ilUtil.php";
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
		global $ilDB;
		if ($heading)
		{
			$affectedRows = $ilDB->manipulateF("UPDATE svy_svy_qst SET heading=%s WHERE survey_fi=%s AND question_fi=%s",
				array('text','integer','integer'),
				array($heading, $this->getSurveyId(), $insertbefore)
			);
		}
		else
		{
			$affectedRows = $ilDB->manipulateF("UPDATE svy_svy_qst SET heading=%s WHERE survey_fi=%s AND question_fi=%s",
				array('text','integer','integer'),
				array(NULL, $this->getSurveyId(), $insertbefore)
			);
		}
	}

	function isAnonymousKey($key)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT anonymous_id FROM svy_anonymous WHERE survey_key = %s AND survey_fi = %s",
			array('text','integer'),
			array($key, $this->getSurveyId())
		);
		return ($result->numRows() == 1) ? true : false;
	}
	
	function getUserSurveyCode($user_id)
	{
		global $ilDB;
		
		if (($user_id == ANONYMOUS_USER_ID) || (($this->isAccessibleWithoutCode() && $this->isAllowedToTakeMultipleSurveys()))) return "";
		$result = $ilDB->queryF("SELECT anonymous_id FROM svy_finished WHERE survey_fi = %s AND user_fi = %s",
			array('integer','integer'),
			array($this->getSurveyId(), $user_id)
		);
		if ($result->numRows() == 1)
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["anonymous_id"];
		}
		else
		{
			return "";
		}
	}
	
	function bindSurveyCodeToUser($user_id, $code)
	{
		global $ilDB;
		
		if($user_id == ANONYMOUS_USER_ID)
		{
			return;
		}
		
		if($this->checkSurveyCode($code))
		{		
			$ilDB->manipulate("UPDATE svy_anonymous".
				" SET user_key = ".$ilDB->quote(md5($user_id), "text").
				" WHERE survey_key = ".$ilDB->quote($code, "text"));
		}
	}
	
	function isAnonymizedParticipant($key)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT finished_id FROM svy_finished WHERE anonymous_id = %s AND survey_fi = %s",
			array('text','integer'),
			array($key, $this->getSurveyId())
		);
		return ($result->numRows() == 1) ? true : false;
	}
	
	function checkSurveyCode($code)
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

	/**
	* Returns the number of generated survey codes for the survey
	*
	* @return integer The number of generated survey codes
	* @access public
	*/
	function getSurveyCodesCount()
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT anonymous_id FROM svy_anonymous WHERE survey_fi = %s AND user_key IS NULL",
			array('integer'),
			array($this->getSurveyId())
		);
		return $result->numRows();
	}
	
	/**
	* Returns a list of survey codes for file export
	*
	* @param array $a_array An array of all survey codes that should be exported
	* @return string A comma separated list of survey codes an URLs for file export
	* @access public
	*/
	function getSurveyCodesForExport(array $a_codes = null, array $a_ids = null)
	{
		global $ilDB, $ilUser, $lng;
		
		include_once "./Services/Link/classes/class.ilLink.php";

		$sql = "SELECT svy_anonymous.*, svy_finished.state".
			" FROM svy_anonymous".
			" LEFT JOIN svy_finished ON (svy_anonymous.survey_key = svy_finished.anonymous_id)".
			" WHERE svy_anonymous.survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND svy_anonymous.user_key IS NULL";
		
		if($a_codes)
		{
			$sql .= " AND ".$ilDB->in("svy_anonymous.survey_key", $a_codes, "", "text");
		}
		else if($a_ids)
		{
			$sql .= " AND ".$ilDB->in("svy_anonymous.anonymous_id", $a_ids, "", "text");
		}
		
		$export = array();
		
		// #14905
		$titles = array();
		$titles[] = '"'.$lng->txt("survey_code").'"';
		$titles[] = '"'.$lng->txt("email").'"';
		$titles[] = '"'.$lng->txt("lastname").'"';
		$titles[] = '"'.$lng->txt("firstname").'"';
		$titles[] = '"'.$lng->txt("create_date").'"';
		$titles[] = '"'.$lng->txt("used").'"';
		$titles[] = '"'.$lng->txt("mail_sent_short").'"';
		$titles[] = '"'.$lng->txt("survey_code_url").'"';
		$export[] = implode(";", $titles);
				
		$result = $ilDB->query($sql);		
		$default_lang = $ilUser->getPref("survey_code_language");
		while ($row = $ilDB->fetchAssoc($result))
		{			
			$item = array();						
			$item[] = $row["survey_key"];
			
			if($row["externaldata"])
			{
				$ext = unserialize($row["externaldata"]);
				$item[] = $ext["email"];
				$item[] = $ext["lastname"];
				$item[] = $ext["firstname"];				
			}
			else
			{
				$item[] = "";
				$item[] = "";
				$item[] = "";
			}
						
			// No relative (today, tomorrow...) dates in export.
			$date = new ilDateTime($row['tstamp'],IL_CAL_UNIX);
			$item[] = $date->get(IL_CAL_DATETIME);
			
			$item[] = ($this->isSurveyCodeUsed($row["survey_key"])) ? 1 : 0;
			$item[] = ($row["sent"]) ? 1 : 0;
			
			$params = array("accesscode" => $row["survey_key"]);
			if ($default_lang) 
			{
				$params["lang"] = $default_lang;
			}
			$item[] = ilLink::_getLink($this->getRefId(), "svy", $params);		
				
			$export[] = '"'.implode('";"', $item).'"';
		}
		return implode("\n", $export);
	}
	
	/**
	* Fetches the data for the survey codes table
	*
	* @param string $lang Language for the survey code URL
	* @return array The requested data
	* @access public
	*/
	public function getSurveyCodesTableData(array $ids = null, $lang = null)
	{
		global $ilDB;
		
		include_once "./Services/Link/classes/class.ilLink.php";
		
		$codes = array();

		$sql = "SELECT svy_anonymous.*, svy_finished.state".
			" FROM svy_anonymous".
			" LEFT JOIN svy_finished ON (svy_anonymous.survey_key = svy_finished.anonymous_id)".
			" WHERE svy_anonymous.survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer") /*.
			" AND svy_anonymous.user_key IS NULL" */; // #15860
		
		if($ids)
		{
			$sql .= " AND ".$ilDB->in("svy_anonymous.anonymous_id", $ids, "", "integer");
		}
		
		$sql .= " ORDER BY tstamp, survey_key ASC";			
		$result = $ilDB->query($sql);				
		if ($result->numRows() > 0)
		{
			while ($row = $ilDB->fetchAssoc($result))
			{				
				$href = "";				
				$used = false;
				if ($this->isSurveyCodeUsed($row["survey_key"]))
				{
					$used = true;
				}
				else
				{
					$params = array("accesscode" => $row["survey_key"]);
					if ($lang)
					{
						$params["lang"] = $lang;
					}
					$href = ilLink::_getLink($this->getRefId(), "svy", $params);					
				}
				
				
				$item = array(
					'id' => $row["anonymous_id"],
					'code' => $row["survey_key"],
					'date' => $row["tstamp"], 
					'used' => $used, 
					'sent' => $row['sent'],
					'href' => $href,
					'email' => '',
					'last_name' => '',
					'first_name' => ''
				);
				
				if($row["externaldata"])
				{
					$ext = unserialize($row["externaldata"]);
					$item['email'] = $ext['email'];
					$item['last_name'] = $ext['lastname'];
					$item['first_name'] = $ext['firstname'];
				}				
												
				array_push($codes, $item);
			}
		}
		return $codes;
	}

	function isSurveyCodeUsed($code)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT finished_id FROM svy_finished WHERE survey_fi = %s AND anonymous_id = %s",
			array('integer','text'),
			array($this->getSurveyId(), $code)
		);
		return ($result->numRows() > 0) ? true : false;
	}
	
	function isSurveyCodeUnique($code)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT anonymous_id FROM svy_anonymous WHERE survey_fi = %s AND survey_key = %s",
			array('integer','text'),
			array($this->getSurveyId(), $code)
		);
		return ($result->numRows() > 0) ? false : true;
	}
	
	function createSurveyCodes($nrOfCodes)
	{
		global $ilDB;
		
		$res = array();
		
		for ($i = 0; $i < $nrOfCodes; $i++)
		{
			$next_id = $ilDB->nextId('svy_anonymous');
			$ilDB->manipulateF("INSERT INTO svy_anonymous (anonymous_id, survey_key, survey_fi, tstamp) ".
				"VALUES (%s, %s, %s, %s)",
				array('integer','text','integer','integer'),
				array($next_id, $this->createNewAccessCode(), $this->getSurveyId(), time())
			);
			$res[] = $next_id;
		}
		
		return $res;
	}
	
	function importSurveyCode($a_anonymize_key, $a_created, $a_data)
	{
		global $ilDB;
		
		$next_id = $ilDB->nextId('svy_anonymous');
		$ilDB->manipulateF("INSERT INTO svy_anonymous (anonymous_id, survey_key, survey_fi, externaldata, tstamp) ".
			"VALUES (%s, %s, %s, %s, %s)",
			array('integer','text','integer','text','integer'),
			array($next_id, $a_anonymize_key, $this->getSurveyId(), serialize($a_data), $a_created)
		);
	}

	function createSurveyCodesForExternalData($data)
	{
		global $ilDB;
		
		$ids = array();
		foreach ($data as $dataset)
		{
			$anonymize_key = $this->createNewAccessCode();
			$next_id = $ilDB->nextId('svy_anonymous');
			$affectedRows = $ilDB->manipulateF("INSERT INTO svy_anonymous (anonymous_id, survey_key, survey_fi, externaldata, tstamp) ".
				"VALUES (%s, %s, %s, %s, %s)",
				array('integer','text','integer','text','integer'),
				array($next_id, $anonymize_key, $this->getSurveyId(), serialize($dataset), time())
			);
			$ids[] = $next_id;
		}
		return $ids;
	}

	function sendCodes($not_sent, $subject, $message, $lang)
	{
		/*
		 * 0 = all
		 * 1 = not sent
		 * 2 = finished
		 * 3 = not finished
		 */		
		$check_finished = ($not_sent > 1);
		
		include_once "./Services/Mail/classes/class.ilMail.php";
		include_once "./Services/Link/classes/class.ilLink.php";
		$user_id = $this->getOwner();
		$mail = new ilMail($user_id);
		$recipients = $this->getExternalCodeRecipients($check_finished);
		foreach ($recipients as $data)
		{
			if($data['email'] && $data['code'])
			{				
				$do_send = false;
				switch ((int)$not_sent)
				{
					case 1:
						$do_send = !(bool)$data['sent'];
						break;
					
					case 2:
						$do_send = $data['finished'];
						break;
					
					case 3:
						$do_send = !$data['finished'];
						break;
					
					default:
						$do_send = true;
						break;									
				}				
				if ($do_send)
				{			
					// build text
					$messagetext = $message;
					$url = ilLink::_getLink($this->getRefId(), "svy",
						array(
							"accesscode" => $data["code"],
							"lang" => $lang
						));				
					$messagetext = str_replace('[url]', "<" . $url . ">", $messagetext);
					foreach ($data as $key => $value)
					{
						$messagetext = str_replace('[' . $key . ']', $value, $messagetext);
					}
					
					// send mail
					$mail->sendMail(
						$data['email'], // to
						"", // cc
						"", // bcc
						$subject, // subject
						$messagetext, // message
						array(), // attachments
						array('normal') // type
					);	
				}
			}
		}

		global $ilDB;
		$ilDB->manipulateF("UPDATE svy_anonymous SET sent = %s WHERE survey_fi = %s AND externaldata IS NOT NULL",
			array('integer','integer'),
			array(1, $this->getSurveyId())
		);
	}

	function getExternalCodeRecipients($a_check_finished = false)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT survey_key code, externaldata, sent FROM svy_anonymous WHERE survey_fi = %s",
			array('integer'),
			array($this->getSurveyId())
		);
		$res = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			if(!$row['externaldata'])
			{
				continue;				
			}
			
			$externaldata = unserialize($row['externaldata']);
			if(!$externaldata['email'])
			{
				continue;
			}
			
			$externaldata['code'] = $row['code'];
			$externaldata['sent'] = $row['sent'];
			
			if($a_check_finished)
			{				
				$externaldata['finished'] =  $this->isSurveyCodeUsed($row['code']);
			}
			
			array_push($res, $externaldata);
		}
		return $res;
	}
	
	/**
	* Deletes a given survey access code
	*
	* @param string $survey_code	The survey code that should be deleted
	*/
	function deleteSurveyCode($survey_code)
	{
		global $ilDB;
		
		if (strlen($survey_code) > 0)
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM svy_anonymous WHERE survey_fi = %s AND survey_key = %s",
				array('integer', 'text'),
				array($this->getSurveyId(), $survey_code)
			);
		}
	}
	
	/**
	* Returns a survey access code that was saved for a registered user
	*
	* @param int $user_id	The database id of the user
	* @return string The survey access code of the user
	*/
	function getUserAccessCode($user_id)
	{
		global $ilDB;
		$access_code = "";
		$result = $ilDB->queryF("SELECT survey_key FROM svy_anonymous WHERE survey_fi = %s AND user_key = %s",
			array('integer','text'),
			array($this->getSurveyId(), md5($user_id))
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			$access_code = $row["survey_key"];
		}
		return $access_code;
	}
	
	/**
	* Saves a survey access code for a registered user to the database
	*
	* @param int $user_id	The database id of the user
	* @param string $access_code The survey access code
	*/
	function saveUserAccessCode($user_id, $access_code)
	{
		global $ilDB;
		
		// not really sure what to do about ANONYMOUS_USER_ID
		
		$next_id = $ilDB->nextId('svy_anonymous');
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_anonymous (anonymous_id, survey_key, survey_fi, user_key, tstamp) ".
			"VALUES (%s, %s, %s, %s, %s)",
			array('integer','text', 'integer', 'text', 'integer'),
			array($next_id, $access_code, $this->getSurveyId(), md5($user_id), time())
		);
	}
	
	/**
	* Returns a new, unused survey access code
	*
	* @return	string A new survey access code
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
		while (!$this->isSurveyCodeUnique($code))
		{
			$code = $this->createNewAccessCode();
		}
		return $code;
	}
	

/**
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
			if(is_array($entry))
			{
				$entry = implode("/", $entry);
			}			
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

	function _getLastAccess($finished_id)
	{
		global $ilDB;
		
		$result = $ilDB->queryF("SELECT tstamp FROM svy_answer WHERE active_fi = %s ORDER BY tstamp DESC",
			array('integer'),
			array($finished_id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["tstamp"];
		}
		else
		{
			$result = $ilDB->queryF("SELECT tstamp FROM svy_finished WHERE finished_id = %s",
				array('integer'),
				array($finished_id)
			);
			if ($result->numRows())
			{
				$row = $ilDB->fetchAssoc($result);
				return $row["tstamp"];
			}
		}
		return "";
	}

	/**
	* Prepares a string for a text area output in surveys
	*
	* @param string $txt_output String which should be prepared for output
	* @access public
	*/
	function prepareTextareaOutput($txt_output)
	{
		include_once "./Services/Utilities/classes/class.ilUtil.php";
		return ilUtil::prepareTextareaOutput($txt_output, $prepare_for_latex_output);
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
	* Creates an XML material tag from a plain text or xhtml text
	*
	* @param object $a_xml_writer Reference to the ILIAS XML writer
	* @param string $a_material plain text or html text containing the material
	* @return string XML material tag
	* @access public
	*/
	function addMaterialTag(&$a_xml_writer, $a_material, $close_material_tag = TRUE, $add_mobs = TRUE, $attribs = NULL)
	{
		include_once "./Services/RTE/classes/class.ilRTE.php";
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");

		$a_xml_writer->xmlStartTag("material", $attribs);
		$attrs = array(
			"type" => "text/plain"
		);
		if ($this->isHTML($a_material))
		{
			$attrs["type"] = "text/xhtml";
		}
		$mattext = ilRTE::_replaceMediaObjectImageSrc($a_material, 0);
		$a_xml_writer->xmlElement("mattext", $attrs, $mattext);

		if ($add_mobs)
		{
			$mobs = ilObjMediaObject::_getMobsOfObject("svy:html", $this->getId());
			foreach ($mobs as $mob)
			{
				$mob_id = "il_" . IL_INST_ID . "_mob_" . $mob;
				if (strpos($mattext, $mob_id) !== FALSE)
				{
					$mob_obj =& new ilObjMediaObject($mob);
					$imgattrs = array(
						"label" => $mob_id,
						"uri" => "objects/" . "il_" . IL_INST_ID . "_mob_" . $mob . "/" . $mob_obj->getTitle(),
						"type" => "svy:html",
						"id" => $this->getId()
					);
					$a_xml_writer->xmlElement("matimage", $imgattrs, NULL);
				}
			}
		}		
		if ($close_material_tag) $a_xml_writer->xmlEndTag("material");
	}

	/**
	 * Checks if the survey code can be exported with the survey evaluation. In some cases this may be
	 * necessary but usually you should prevent it because people who sent the survey codes could connect
	 * real people with the survey code in the evaluation and undermine the anonymity
	 *
	 * @return boolean TRUE if the survey is anonymized and the survey code may be shown in the export file
	 * @author Helmut Schottmüller
	 **/
	function canExportSurveyCode()
	{
		if ($this->getAnonymize() != self::ANONYMIZE_OFF)
		{
			if ($this->surveyCodeSecurity == FALSE)
			{
				return TRUE;
			}
		}
		return FALSE;
	}

	/**
	* Convert a print output to XSL-FO
	*
	* @param string $print_output The print output
	* @return string XSL-FO code
	* @access public
	*/
	function processPrintoutput2FO($print_output)
	{
		global $ilLog; 
		
		if (extension_loaded("tidy"))
		{
			$config = array(
				"indent"         => false,
				"output-xml"     => true,
				"numeric-entities" => true
			);
			$tidy = new tidy();
			$tidy->parseString($print_output, $config, 'utf8');
			$tidy->cleanRepair();
			$print_output = tidy_get_output($tidy);
			$print_output = preg_replace("/^.*?(<html)/", "\\1", $print_output);
		}
		else
		{
			$print_output = str_replace("&nbsp;", "&#160;", $print_output);
			$print_output = str_replace("&otimes;", "X", $print_output);
			
			// #17680 - metric questions use &#160; in print view
			$print_output = str_replace("&gt;", ">", $print_output);
			$print_output = str_replace("&lt;", "<", $print_output);
			$print_output = str_replace("&#160;", "~|nbsp|~", $print_output);
			$print_output = preg_replace('/&(?!amp)/', '&amp;', $print_output);
			$print_output = str_replace("~|nbsp|~", "&#160;", $print_output);			
		}
		$xsl = file_get_contents("./Modules/Survey/xml/question2fo.xsl");

		// additional font support
		$xsl = str_replace(
				'font-family="Helvetica, unifont"',
				'font-family="'.$GLOBALS['ilSetting']->get('rpc_pdf_font','Helvetica, unifont').'"',
				$xsl
		);
		
		$args = array( '/_xml' => $print_output, '/_xsl' => $xsl );
		$xh = xslt_create();
		$params = array();
		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, $params);
		xslt_error($xh);
		xslt_free($xh);
		$ilLog->write($output);
		return $output;
	}
	
	/**
	* Delivers a PDF file from a XSL-FO string
	*
	* @param string $fo The XSL-FO string
	* @access public
	*/
	function deliverPDFfromFO($fo)
	{
		global $ilLog;

		include_once "./Services/Utilities/classes/class.ilUtil.php";
		$fo_file = ilUtil::ilTempnam() . ".fo";
		$fp = fopen($fo_file, "w"); fwrite($fp, $fo); fclose($fp);

		include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
		try
		{
			$pdf_base64 = ilRpcClientFactory::factory('RPCTransformationHandler')->ilFO2PDF($fo);
			ilUtil::deliverData($pdf_base64->scalar, ilUtil::getASCIIFilename($this->getTitle()) . ".pdf", "application/pdf");
			return true;
		}
		catch(XML_RPC2_FaultException $e)
		{
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			return false;
		}
		catch(Exception $e)
		{
			$ilLog->write(__METHOD__.': '.$e->getMessage());
			return false;
		}

		/*
		include_once "./Services/Transformation/classes/class.ilFO2PDF.php";
		$fo2pdf = new ilFO2PDF();
		$fo2pdf->setFOString($fo);
		$result = $fo2pdf->send();
		ilUtil::deliverData($result, ilUtil::getASCIIFilename($this->getTitle()) . ".pdf", "application/pdf");
		*/
	}

	/**
	* Checks whether or not a question plugin with a given name is active
	*
	* @param string $a_pname The plugin name
	* @access public
	*/
	function isPluginActive($a_pname)
	{
		global $ilPluginAdmin;
		if ($ilPluginAdmin->isActive(IL_COMP_MODULE, "SurveyQuestionPool", "svyq", $a_pname))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}
	
	/**
	* Sets the survey id
	*
	* @param integer $survey_id The survey id
	*/
	public function setSurveyId($survey_id)
	{
		$this->survey_id = $survey_id;
	}

	/**
	* Returns a data of all users specified by id list
	*
	* @param $ids array of user id's
	* @return array The user data "usr_id, login, lastname, firstname, clientip" of the users with id as key
	*/
	public function &getUserData($ids)
	{
		global $ilDB;

		if (!is_array($ids) || count($ids) ==0) return array();

		$result = $ilDB->query("SELECT usr_id, login, lastname, firstname FROM usr_data WHERE " . $ilDB->in('usr_id', $ids, false, 'integer') . " ORDER BY login");
		$result_array = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$result_array[$row["usr_id"]]= $row;
		}
		return $result_array;
	}

	function &getGroupData($ids)
	{
		if (!is_array($ids) || count($ids) ==0) return array();
		$result = array();
		foreach ($ids as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$result[$ref_id] = array("ref_id" => $ref_id, "title" => ilObject::_lookupTitle($obj_id), "description" => ilObject::_lookupDescription($obj_id));
		}
		return $result;
	}

	function &getRoleData($ids)
	{
		if (!is_array($ids) || count($ids) ==0) return array();
		$result = array();
		foreach ($ids as $obj_id)
		{
			$result[$obj_id] = array("obj_id" => $obj_id, "title" => ilObject::_lookupTitle($obj_id), "description" => ilObject::_lookupDescription($obj_id));
		}
		return $result;
	}
	
	function getMailNotification()
	{
		return $this->mailnotification;
	}
	
	function setMailNotification($a_notification)
	{
		$this->mailnotification = ($a_notification) ? true : false;
	}
	
	function getMailAddresses()
	{
		return $this->mailaddresses;
	}
	
	function setMailAddresses($a_addresses)
	{
		$this->mailaddresses = $a_addresses;
	}
	
	function getMailParticipantData()
	{
		return $this->mailparticipantdata;
	}
	
	function setMailParticipantData($a_data)
	{
		$this->mailparticipantdata = $a_data;
	}
	
	public function getSurveyTimes()
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM svy_times, svy_finished WHERE svy_finished.survey_fi = %s",
			array('integer'),
			array($this->getId())
		);
		$times = array();;
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (strlen($row['left_page']) && strlen($row['entered_page']))
				$times[$row['finished_fi']] += ($row['left_page']-$row['entered_page']);
		}
		return $times;
	}
	
	function setStartTime($finished_id, $first_question)
	{
		global $ilDB;
		$time = time();
		$_SESSION['svy_entered_page'] = $time;
		$affectedRows = $ilDB->manipulateF("INSERT INTO svy_times (finished_fi, entered_page, left_page, first_question) VALUES (%s, %s, %s, %s)",
			array('integer', 'integer', 'integer', 'integer'),
			array($finished_id, $time, NULL, $first_question)
		);
	}
	
	function setEndTime($finished_id)
	{
		global $ilDB;
		$time = time();
		$affectedRows = $ilDB->manipulateF("UPDATE svy_times SET left_page = %s WHERE finished_fi = %s AND entered_page = %s",
			array('integer', 'integer', 'integer'),
			array($time, $finished_id, $_SESSION['svy_entered_page'])
		);
		unset($_SESSION['svy_entered_page']);
	}
	
	function getWorkingtimeForParticipant($finished_id)
	{
		global $ilDB;

		$result = $ilDB->queryF("SELECT * FROM svy_times WHERE finished_fi = %s",
			array('integer'),
			array($finished_id)
		);
		$total = 0;
		while ($row = $ilDB->fetchAssoc($result))
		{
			if ($row['left_page'] > 0 && $row['entered_page'] > 0)
				$total += $row['left_page'] - $row['entered_page'];
		}
		return $total;
	}

	function setTemplate($template_id)
	{
		$this->template_id = (int)$template_id;
	}

	function getTemplate()
	{
		return $this->template_id;
	}

	function updateOrder(array $a_order)
	{
		if(sizeof($this->questions) == sizeof($a_order))
		{
			$this->questions = array_flip($a_order);
			$this->saveQuestionsToDB();
		}		
	}

	function getPoolUsage()
	{
		return $this->pool_usage;
	}

	function setPoolUsage($a_value)
	{
		$this->pool_usage = (bool)$a_value;
	}

	/**
	 * Get current pool status
	 *
	 * @return bool
	 */
	function isPoolActive()
	{
		$use_pool = (bool)$this->getPoolUsage();
		$template_settings = $this->getTemplate();
		if($template_settings)
		{
			include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
			$template_settings = new ilSettingsTemplate($template_settings);
			$template_settings = $template_settings->getSettings();
			$template_settings = $template_settings["use_pool"];
			if($template_settings && $template_settings["hide"])
			{
				$use_pool = (bool)$template_settings["value"];
			}
		}
		return $use_pool;
	}
	
	/**
	 * Apply settings template
	 * 
	 * @param int $template_id
	 */
	function applySettingsTemplate($template_id)
	{
		if(!$template_id)
		{
			return;
		}
		
		include_once "Services/Administration/classes/class.ilSettingsTemplate.php";
		$template = new ilSettingsTemplate($template_id);
		$template_settings = $template->getSettings();
		if($template_settings)
		{
			if($template_settings["show_question_titles"] !== NULL)
			{
				if($template_settings["show_question_titles"]["value"])
				{
					$this->setShowQuestionTitles(true);
				}
				else
				{
					$this->setShowQuestionTitles(false);
				}
			}

			if($template_settings["use_pool"] !== NULL)
			{
				if($template_settings["use_pool"]["value"])
				{
					$this->setPoolUsage(true);
				}
				else
				{
					$this->setPoolUsage(false);
				}
			}

			if($template_settings["anonymization_options"]["value"])
			{
				$anon_map = array('personalized' => self::ANONYMIZE_OFF,
					'anonymize_with_code' => self::ANONYMIZE_ON,
					'anonymize_without_code' => self::ANONYMIZE_FREEACCESS);
				$this->setAnonymize($anon_map[$template_settings["anonymization_options"]["value"]]);
			}

			/* other settings: not needed here
			 * - enabled_end_date
			 * - enabled_start_date
			 * - rte_switch
			 */
		}

		$this->setTemplate($template_id);
		$this->saveToDb();
	}
	
	public function updateCode($a_id, $a_email, $a_last_name, $a_first_name, $a_sent)
	{
		global $ilDB;
		
		$a_email = trim($a_email);
		
		// :TODO:
		if($a_email && !ilUtil::is_email($a_email))
		{
			return;
		}
		
		$data = array("email" => $a_email,
			"lastname" => trim($a_last_name),
			"firstname" => trim($a_first_name));
		
		$fields = array(
			"externaldata" => array("text", serialize($data)),
			"sent" => array("integer", $a_sent)			
		);
		
		$ilDB->update("svy_anonymous", $fields,
			array("anonymous_id" => array("integer", $a_id)));							
	}
	
	
	//
	// 360° 
	// 
	
	public function set360Mode($a_value)
	{
		$this->mode_360 = (bool)$a_value;
	}
	
	public function get360Mode()
	{
		return (bool)$this->mode_360;
	}
	
	public function set360SelfEvaluation($a_value)
	{
		$this->mode_360_self_eval = (bool)$a_value;
	}
	
	public function get360SelfEvaluation()
	{
		return (bool)$this->mode_360_self_eval;
	}
	
	public function set360SelfAppraisee($a_value)
	{
		$this->mode_360_self_appr = (bool)$a_value;
	}
	
	public function get360SelfAppraisee()
	{
		return (bool)$this->mode_360_self_appr;
	}
	
	public function set360SelfRaters($a_value)
	{
		$this->mode_360_self_rate = (bool)$a_value;
	}
	
	public function get360SelfRaters()
	{
		return (bool)$this->mode_360_self_rate;
	}
	
	public function set360Results($a_value)
	{
		$this->mode_360_results = (int)$a_value;
	}
	
	public function get360Results()
	{
		return (int)$this->mode_360_results;
	}
	
	public function addAppraisee($a_user_id)
	{
		global $ilDB;
		
		if(!$this->isAppraisee($a_user_id) && 
			$a_user_id != ANONYMOUS_USER_ID)
		{
			$fields = array(
				"obj_id" => array("integer", $this->getSurveyId()),
				"user_id" => array("integer", $a_user_id)
			);			
			$ilDB->insert("svy_360_appr", $fields);			
		}				
	}
	
	public function isAppraisee($a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT user_id".
			" FROM svy_360_appr".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer"));		
		return (bool)$ilDB->numRows($set);		
	}
	
	public function isAppraiseeClosed($a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT has_closed".
			" FROM svy_360_appr".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer"));		
		$row = $ilDB->fetchAssoc($set);
		return $row["has_closed"];				
	}
	
	public function deleteAppraisee($a_user_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM svy_360_appr".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer"));		
				
		$set = $ilDB->query("SELECT user_id".
			" FROM svy_360_rater".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").		
			" AND appr_id = ".$ilDB->quote($a_user_id, "integer"));		
		while($row = $ilDB->fetchAssoc($set))
		{			
			$this->deleteRater($a_user_id, $row["user_id"]);
		}		
		// appraisee will not be part of raters table
		if($this->get360SelfEvaluation())
		{
			$this->deleteRater($a_user_id, $a_user_id);
		}
	}
	
	public function getAppraiseesData()
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM svy_360_appr".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer"));		
		while($row = $ilDB->fetchAssoc($set))
		{		
			$name = ilObjUser::_lookupName($row["user_id"]);
			$name["email"] = ilObjUser::_lookupEmail($row["user_id"]);
			$res[$row["user_id"]] = $name;
			
			$finished = 0;
			$raters = $this->getRatersData($row["user_id"]);	
			foreach($raters as $rater)
			{
				if($rater["finished"])
				{
					$finished++;
				}
			}
			$res[$row["user_id"]]["finished"] = $finished."/".sizeof($raters);
			$res[$row["user_id"]]["closed"] = $row["has_closed"];			
		}
		
		return $res;
	}
	
	public function addRater($a_appraisee_id, $a_user_id, $a_anonymous_id = 0)
	{
		global $ilDB;
		
		if($this->isAppraisee($a_appraisee_id) &&
			!$this->isRater($a_appraisee_id, $a_user_id, $a_anonymous_id))
		{
			$fields = array(
				"obj_id" => array("integer", $this->getSurveyId()),
				"appr_id" => array("integer", $a_appraisee_id),
				"user_id" => array("integer", $a_user_id),
				"anonymous_id" => array("integer", $a_anonymous_id)
			);			
			$ilDB->insert("svy_360_rater", $fields);			
		}				
	}
	
	public function isRater($a_appraisee_id, $a_user_id, $a_anonymous_id = 0)
	{
		global $ilDB;
		
		// user is rater if already appraisee and active self-evaluation
		if($this->isAppraisee($a_user_id) &&
			$this->get360SelfEvaluation() && 
			(!$a_appraisee_id || $a_appraisee_id == $a_user_id))
		{
			return true;
		}
				
		// :TODO: should we get rid of code as well?
		
		$sql = "SELECT user_id".
			" FROM svy_360_rater".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").			
			" AND user_id = ".$ilDB->quote($a_user_id, "integer").		
			" AND anonymous_id = ".$ilDB->quote($a_anonymous_id, "integer");		
		if($a_appraisee_id)
		{
			$sql .= " AND appr_id = ".$ilDB->quote($a_appraisee_id, "integer");
		}		
		$set = $ilDB->query($sql);		
		return (bool)$ilDB->numRows($set);		
	}
	
	public function deleteRater($a_appraisee_id, $a_user_id, $a_anonymous_id = 0)
	{
		global $ilDB;
		
		$finished_id = $this->getFinishedIdForAppraiseeIdAndRaterId($a_appraisee_id, $a_user_id);
		if($finished_id)
		{
			$this->removeSelectedSurveyResults(array($finished_id));		
		}
		
		$ilDB->manipulate("DELETE FROM svy_360_rater".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND appr_id = ".$ilDB->quote($a_appraisee_id, "integer").		
			" AND user_id = ".$ilDB->quote($a_user_id, "integer").		
			" AND anonymous_id = ".$ilDB->quote($a_anonymous_id, "integer"));			
	}
	
	public function getRatersData($a_appraisee_id)
	{
		global $ilDB;
		
		$res = $anonymous_ids = array();
		
		$set = $ilDB->query("SELECT * FROM svy_360_rater".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").		
			" AND appr_id = ".$ilDB->quote($a_appraisee_id, "integer"));		
		while($row = $ilDB->fetchAssoc($set))
		{										
			if($row["anonymous_id"])
			{				
				$res["a".$row["anonymous_id"]] = array(
					"lastname" => "unknown code ".$row["anonymous_id"],
					"sent" => $row["mail_sent"],
					"finished" => null
				);
				$anonymous_ids[] = $row["anonymous_id"];
			}
			else
			{			
				$name = ilObjUser::_lookupName($row["user_id"]);
				$name["user_id"] = "u".$name["user_id"];
				$name["email"] = ilObjUser::_lookupEmail($row["user_id"]);
				$name["sent"] = $row["mail_sent"];
				$name["finished"] = (bool)$this->is360SurveyStarted($a_appraisee_id, $row["user_id"]);
				$res["u".$row["user_id"]] = $name;
			}		
		}
		
		if(sizeof($anonymous_ids))
		{
			$data = $this->getSurveyCodesTableData($anonymous_ids);
			foreach($data as $item)
			{
				if(isset($res["a".$item["id"]]))
				{				
					$res["a".$item["id"]] = array(
						"user_id" => "a".$item["id"],
						"lastname" => $item["last_name"],
						"firstname" => $item["first_name"],						
						"login" => "",
						"email" => $item["email"],
						"code" => $item["code"],
						"href" => $item["href"],
						"sent" => $res["a".$item["id"]]["sent"],
						"finished" => (bool)$this->is360SurveyStarted($a_appraisee_id, null, $item["code"])
					);					
				}
			}
		}
		
		return $res;
	}
	
	public function getAppraiseesToRate($a_user_id, $a_anonymous_id = null)
	{
		global $ilDB;
	
		$res = array();
		
		$sql = "SELECT appr_id FROM svy_360_rater".
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer");
		
		if($a_user_id)
		{
			$sql .= " AND user_id = ".$ilDB->quote($a_user_id, "integer");		
		}
		else
		{
			$sql .= " AND anonymous_id = ".$ilDB->quote($a_anonymous_id, "integer");		
		}	
				
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{		
			$res[] = $row["appr_id"];
		}
				
		// user may evaluate himself if already appraisee
		if($this->get360SelfEvaluation() && 
			$this->isAppraisee($a_user_id) &&
			!in_array($a_user_id, $res))
		{
			$res[] = $a_user_id;					
		}
		
		return $res;
	}
	
	public function getAnonymousIdByCode($a_code)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT anonymous_id FROM svy_anonymous".
				" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").		
				" AND survey_key = ".$ilDB->quote($a_code, "text"));	
		$res = $ilDB->fetchAssoc($set);
		return $res["anonymous_id"];
	}
	
	function is360SurveyStarted($appr_id, $user_id, $anonymous_code = null)
	{
		global $ilDB;

		$sql = "SELECT * FROM svy_finished".
			" WHERE survey_fi =".$ilDB->quote($this->getSurveyId(), "integer").
			" AND appr_id = ".$ilDB->quote($appr_id, "integer");					
		if ($user_id)
		{			
			$sql .= " AND user_fi = ".$ilDB->quote($user_id, "integer");
		}
		else
		{
			$sql .= " AND anonymous_id = ".$ilDB->quote($anonymous_code, "text");
		}
		$result = $ilDB->query($sql);
		if ($result->numRows() == 0)
		{
			return false;
		}			
		else
		{
			$row = $ilDB->fetchAssoc($result);			
			return (int)$row["state"];
		}
	}
	
	function getUserSurveyExecutionStatus($a_code = null)
	{
		global $ilUser, $ilDB;
			
		$user_id = $ilUser->getId();
				
		// code is obligatory?
		if(!$this->isAccessibleWithoutCode())
		{
			if(!$a_code)
			{
				// registered raters do not need code
				if($this->get360Mode() &&
					$user_id != ANONYMOUS_USER_ID && 
					$this->isRater(0, $user_id))
				{
					// auto-generate code
					$a_code = $this->createNewAccessCode();		
					$this->saveUserAccessCode($user_id, $a_code);	
				}
				else
				{
					return null;
				}
			}
		}		
		else if($user_id == ANONYMOUS_USER_ID ||
			$this->getAnonymize() == self::ANONYMIZE_FREEACCESS)
		{
			if(!$a_code)
			{			
				// auto-generate code
				$a_code = $this->createNewAccessCode();		
				$this->saveUserAccessCode($user_id, $a_code);					
			}
		}
		else
		{
			$a_code = null;
		}
			
		$res = array();
								
		$sql = "SELECT * FROM svy_finished".
			" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer");		
		// if proper user id is given, use it or current code
		if($user_id != ANONYMOUS_USER_ID)
		{
			$sql .= " AND (user_fi = ".$ilDB->quote($user_id, "integer").
				" OR anonymous_id = ".$ilDB->quote($a_code, "text").")";		
		}
		// use anonymous code to find finished id(s)
		else
		{
			$sql .= " AND anonymous_id = ".$ilDB->quote($a_code, "text");	
		}
		$set = $ilDB->query($sql);
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["finished_id"]] = array("appr_id" => $row["appr_id"],
				"user_id" => $row["user_fi"],
				"code" => $row["anonymous_id"],
				"finished" => (bool)$row["state"]);
		}
		
		return array("code"=>$a_code, "runs"=>$res);
	}
	
	function findCodeForUser($a_user_id)
	{		
		global $ilDB;
		
		if($a_user_id != ANONYMOUS_USER_ID)
		{
			$set = $ilDB->query("SELECT sf.anonymous_id FROM svy_finished sf".				
				" WHERE sf.survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").
				" AND sf.user_fi = ".$ilDB->quote($a_user_id, "integer"));
			$a_code = $ilDB->fetchAssoc($set);
			return $a_code["anonymous_id"];
		}
	}
	
	function isUnusedCode($a_code, $a_user_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT user_fi FROM svy_finished".
			" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND anonymous_id = ".$ilDB->quote($a_code, "text"));
		$user_id = $ilDB->fetchAssoc($set);
		$user_id = $user_id["user_fi"];
		
		if($user_id && ($user_id != $a_user_id || $user_id == ANONYMOUS_USER_ID))
		{
			return false;
		}
		return true;
	}
	
	function getFinishedIdsForAppraiseeId($a_appr_id, $a_exclude_appraisee = false)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT finished_id, user_fi FROM svy_finished".
			" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND appr_id = ".$ilDB->quote($a_appr_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			if ($a_exclude_appraisee && $row["user_fi"] == $a_appr_id)
			{
				continue;
			}
			$res[] = $row["finished_id"];
		}
		
		return $res;
	}
	
	/**
	 * Get finished id for an appraisee and a rater
	 *
	 * @param int $a_appraisee_id appraisee id
	 * @param int $a_rater_id rater id
	 * @return int finished id
	 */
	function getFinishedIdForAppraiseeIdAndRaterId($a_appr_id, $a_rat_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT finished_id, user_fi FROM svy_finished".
			" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND appr_id = ".$ilDB->quote($a_appr_id, "integer").
			" AND user_fi = ".$ilDB->quote($a_rat_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		return $row["finished_id"];
	}
	
	
	// 360° using competence/skill service
	
	/**
	 * Set skill service
	 *
	 * @param bool $a_val activate skill service	
	 */
	function set360SkillService($a_val)
	{
		$this->mode_360_skill_service = $a_val;
	}
	
	/**
	 * Get skill service
	 *
	 * @return bool activate skill service
	 */
	function get360SkillService()
	{
		return $this->mode_360_skill_service;
	}
	
	function set360RaterSent($a_appraisee_id, $a_user_id, $a_anonymous_id, $a_tstamp = null)
	{
		global $ilDB;
		
		if(!$a_tstamp)
		{
			$a_tstamp = time();
		}
		
		$ilDB->manipulate("UPDATE svy_360_rater".
			" SET mail_sent = ".$ilDB->quote($a_tstamp, "integer").
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND appr_id = ".$ilDB->quote($a_appraisee_id, "integer").		
			" AND user_id = ".$ilDB->quote($a_user_id, "integer").		
			" AND anonymous_id = ".$ilDB->quote($a_anonymous_id, "integer"));	
	}
	
	function closeAppraisee($a_user_id)
	{
		global $ilDB;
		
		// close the appraisee
		$ilDB->manipulate("UPDATE svy_360_appr".
			" SET has_closed = ".$ilDB->quote(time(), "integer").
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer").
			" AND user_id = ".$ilDB->quote($a_user_id, "integer"));
		
		// write competences
		include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
		$skmg_set = new ilSkillManagementSettings();
		if ($this->get360SkillService() && $skmg_set->isActivated())
		{
			include_once("./Modules/Survey/classes/class.ilSurveySkill.php");
			$sskill = new ilSurveySkill($this);
			$sskill->writeAppraiseeSkills($a_user_id);
		}
	}
	
	function openAllAppraisees()
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE svy_360_appr".
			" SET has_closed = ".$ilDB->quote(null, "integer").
			" WHERE obj_id = ".$ilDB->quote($this->getSurveyId(), "integer"));
	}
	
	public static function validateExternalRaterCode($a_ref_id, $a_code)
	{				
		if(!isset($_SESSION["360_extrtr"][$a_ref_id]))
		{			
			$svy = new self($a_ref_id);
			$svy->loadFromDB();
			
			if($svy->canStartSurvey(null, true) &&
				$svy->get360Mode() &&
				$svy->isAnonymousKey($a_code))
			{				
				$anonymous_id = $svy->getAnonymousIdByCode($a_code);
				if($anonymous_id)
				{					
					if(sizeof($svy->getAppraiseesToRate(null, $anonymous_id)))
					{						
						$_SESSION["360_extrtr"][$a_ref_id] = true;
						return true;
					}
				}			
			}

			$_SESSION["360_extrtr"][$a_ref_id] = false;	
			return false;
		}
		
		return $_SESSION["360_extrtr"][$a_ref_id];
	}
	
	
	//
	// reminder/notification
	// 
	
	public function getReminderStatus()
	{
		return (bool)$this->reminder_status;
	}
	
	public function setReminderStatus($a_value)
	{
		$this->reminder_status = (bool)$a_value;
	}
	
	public function getReminderStart()
	{
		return $this->reminder_start;
	}
	
	public function setReminderStart(ilDate $a_value = null)
	{
		$this->reminder_start = $a_value;
	}
	
	public function getReminderEnd()
	{
		return $this->reminder_end;
	}
	
	public function setReminderEnd(ilDate $a_value = null)
	{
		$this->reminder_end = $a_value;
	}
	
	public function getReminderFrequency()
	{
		return $this->reminder_frequency;
	}
	
	public function setReminderFrequency($a_value)
	{
		$this->reminder_frequency = (int)$a_value;
	}
	
	public function getReminderTarget()
	{
		return $this->reminder_target;
	}
	
	public function setReminderTarget($a_value)
	{
		$this->reminder_target = (int)$a_value;
	}
	
	public function getReminderLastSent()
	{
		return $this->reminder_last_sent;
	}
	
	public function setReminderLastSent($a_value)
	{
		$this->reminder_last_sent = $a_value;
	}
	
	public function getTutorNotificationStatus()
	{
		return (bool)$this->tutor_ntf_status;
	}
	
	public function setTutorNotificationStatus($a_value)
	{
		$this->tutor_ntf_status = (bool)$a_value;
	}
	
	public function getTutorNotificationRecipients()
	{
		return $this->tutor_ntf_recipients;
	}
	
	public function setTutorNotificationRecipients(array $a_value)
	{
		$this->tutor_ntf_recipients = $a_value;			
	}
	
	public function getTutorNotificationTarget()
	{
		return $this->tutor_ntf_target;
	}	
	
	public function setTutorNotificationTarget($a_value)
	{
		$this->tutor_ntf_target = (int)$a_value;
	}	
	
	protected function checkTutorNotification()
	{
		global $ilDB;
		
		if($this->getTutorNotificationStatus())
		{
			$user_ids = $this->getNotificationTargetUserIds(($this->getTutorNotificationTarget() == self::NOTIFICATION_INVITED_USERS));
			if($user_ids)
			{
				$set = $ilDB->query("SELECT COUNT(*) numall FROM svy_finished".
					" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").
					" AND state = ".$ilDB->quote(1, "integer").
					" AND ".$ilDB->in("user_fi", $user_ids, "", "integer"));
				$row = $ilDB->fetchAssoc($set);
				if($row["numall"] == sizeof($user_ids))
				{
					$this->sendTutorNotification();
				}
			}			
		}
	}
	
	protected function getNotificationTargetUserIds($a_use_invited)
	{
		global $tree;
		
		if((bool)$a_use_invited)
		{
			$user_ids = $this->getInvitedUsers();				
		}
		else
		{
			$parent_grp_ref_id = $tree->checkForParentType($this->getRefId(), "grp");
			if($parent_grp_ref_id)
			{
				include_once "Modules/Group/classes/class.ilGroupParticipants.php";
				$part = new ilGroupParticipants(ilObject::_lookupObjId($parent_grp_ref_id));
				$user_ids = $part->getMembers();					
			}
			else
			{
				$parent_crs_ref_id = $tree->checkForParentType($this->getRefId(), "crs");
				if($parent_crs_ref_id)
				{
					include_once "Modules/Course/classes/class.ilCourseParticipants.php";
					$part = new ilCourseParticipants(ilObject::_lookupObjId($parent_crs_ref_id));
					$user_ids = $part->getMembers();					
				}				
			}
		}
		return $user_ids;
	}
	
	protected function sendTutorNotification()
	{		
		include_once "./Services/Mail/classes/class.ilMail.php";
		include_once "./Services/User/classes/class.ilObjUser.php";
		include_once "./Services/Language/classes/class.ilLanguageFactory.php";
		include_once "./Services/User/classes/class.ilUserUtil.php";		
		include_once "./Services/Link/classes/class.ilLink.php";
		$link = ilLink::_getStaticLink($this->getRefId(), "svy");
			
		foreach($this->getTutorNotificationRecipients() as $user_id)
		{																
			// use language of recipient to compose message
			$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
			$ulng->loadLanguageModule('survey');

			$subject = sprintf($ulng->txt('survey_notification_tutor_subject'), $this->getTitle());
			$message = sprintf($ulng->txt('survey_notification_tutor_salutation'), ilObjUser::_lookupFullname($user_id))."\n\n";

			$message .= $ulng->txt('survey_notification_tutor_body').":\n\n";
			$message .= $ulng->txt('obj_svy').": ". $this->getTitle()."\n";			
			$message .= "\n".$ulng->txt('survey_notification_tutor_link').": ".$link;				

			$mail_obj = new ilMail(ANONYMOUS_USER_ID);
			$mail_obj->appendInstallationSignature(true);
			$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id),
				"", "", $subject, $message, array(), array("system"));
		}
	}
	
	public function checkReminder()
	{
		global $ilDB, $ilAccess;
		
		$now = time();		
		$today = date("Y-m-d");
		
		// object settings / participation period
		if($this->isOffline() ||
			!$this->getReminderStatus() ||
			($this->getStartDate() && $now < $this->getStartDate()) ||
			($this->getEndDate() && $now > $this->getEndDate()))
		{
			return false;
		}
						
		// reminder period
		$start = $this->getReminderStart();
		if($start)
		{
			$start = $start->get(IL_CAL_DATE);
		}
		$end = $this->getReminderEnd();
		if($end)
		{
			$end = $end->get(IL_CAL_DATE);
		}		
		if($today < $start ||
			($end && $today > $end))
		{
			return false;
		}
		
		// object access period
		include_once "Services/Object/classes/class.ilObjectActivation.php";	
		$item_data = ilObjectActivation::getItem($this->getRefId());				
		if($item_data["timing_type"] == ilObjectActivation::TIMINGS_ACTIVATION &&
			($now < $item_data["timing_start"] ||
			$now > $item_data["timing_end"]))
		{
			return false;
		}
		
		// check frequency
		$cut = new ilDate($today, IL_CAL_DATE);
		$cut->increment(IL_CAL_DAY, $this->getReminderFrequency()*-1);
		if(!$this->getReminderLastSent() ||
			$cut->get(IL_CAL_DATE) >= substr($this->getReminderLastSent(), 0, 10))					
		{	
			$missing_ids = array();
				
			// #16871
			$user_ids = $this->getNotificationTargetUserIds(($this->getReminderTarget() == self::NOTIFICATION_INVITED_USERS));					
			if($user_ids)
			{				
				// gather participants who already finished
				$finished_ids = array();
				$set = $ilDB->query("SELECT user_fi FROM svy_finished".
				" WHERE survey_fi = ".$ilDB->quote($this->getSurveyId(), "integer").
				" AND state = ".$ilDB->quote(1, "text").
				" AND ".$ilDB->in("user_fi", $user_ids, "", "integer"));
				while($row = $ilDB->fetchAssoc($set))
				{
					$finished_ids[] = $row["user_fi"];						
				}

				// some users missing out?
				$missing_ids = array_diff($user_ids, $finished_ids);
				if($missing_ids)
				{
					foreach($missing_ids as $idx => $user_id)
					{
						// should be able to participate
						if(!$ilAccess->checkAccessOfUser($user_id, "read", "", $this->getRefId(), "svy", $this->getId()))
						{
							unset($missing_ids[$idx]);
						}
					}
				}								
				if($missing_ids)
				{
					$this->sentReminder($missing_ids);
				}
			}

			$this->setReminderLastSent($today);
			$this->saveToDb();

			return sizeof($missing_ids);
		}
		
		return false;
	}
	
	protected function sentReminder(array $a_recipient_ids)
	{
		include_once "./Services/Mail/classes/class.ilMail.php";
		include_once "./Services/User/classes/class.ilObjUser.php";
		include_once "./Services/Language/classes/class.ilLanguageFactory.php";
		include_once "./Services/User/classes/class.ilUserUtil.php";		
		include_once "./Services/Link/classes/class.ilLink.php";
		$link = ilLink::_getStaticLink($this->getRefId(), "svy");
			
		foreach($a_recipient_ids as $user_id)
		{																
			// use language of recipient to compose message
			$ulng = ilLanguageFactory::_getLanguageOfUser($user_id);
			$ulng->loadLanguageModule('survey');

			$subject = sprintf($ulng->txt('survey_reminder_subject'), $this->getTitle());
			$message = sprintf($ulng->txt('survey_reminder_salutation'), ilObjUser::_lookupFullname($user_id))."\n\n";

			$message .= $ulng->txt('survey_reminder_body').":\n\n";
			$message .= $ulng->txt('obj_svy').": ". $this->getTitle()."\n";			
			$message .= "\n".$ulng->txt('survey_reminder_link').": ".$link;				

			$mail_obj = new ilMail(ANONYMOUS_USER_ID);
			$mail_obj->appendInstallationSignature(true);
			$mail_obj->sendMail(ilObjUser::_lookupLogin($user_id),
				"", "", $subject, $message, array(), array("system"));
		}					
	}
	
	function setActivationStartDate($starting_time = NULL)
	{
		$this->activation_starting_time = $starting_time;
	}

	function setActivationEndDate($ending_time = NULL)
	{
		$this->activation_ending_time = $ending_time;
	}
	
	function getActivationStartDate()
	{
		return (strlen($this->activation_starting_time)) ? $this->activation_starting_time : NULL;
	}

	function getActivationEndDate()
	{
		return (strlen($this->activation_ending_time)) ? $this->activation_ending_time : NULL;
	}
	
	function setViewOwnResults($a_value)
	{
		$this->view_own_results = (bool)$a_value;
	}
	
	function hasViewOwnResults()
	{
		return $this->view_own_results;
	}
	
	function setMailOwnResults($a_value)
	{
		$this->mail_own_results = (bool)$a_value;
	}
	
	function hasMailOwnResults()
	{
		return $this->mail_own_results;
	}
	
	public static function getSurveySkippedValue()
	{		
		global $lng;
		
		// #13541
		
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$surveySetting = new ilSetting("survey");
		if(!$surveySetting->get("skipped_is_custom", false))
		{
			return $lng->txt("skipped");
		}
		else
		{
			return $surveySetting->get("skipped_custom_value", "");
		}
	}
	
} // END class.ilObjSurvey

?>