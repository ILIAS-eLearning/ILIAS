<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject.php";
require_once 'Modules/TestQuestionPool/classes/class.ilAssQuestionProcessLocker.php';

/**
 * Class ilObjAssessmentFolder
 *
 * @author	Helmut Schottmüller <hschottm@gmx.de>
 * @author	Björn Heyser <bheyser@databay.de>
 * 
 * @version	$Id$
 * 
 * @ingroup ModulesTest
 */
class ilObjAssessmentFolder extends ilObject
{
	const ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_DISABLED = 0;
	const ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_ENABLED = 1;

	const ASS_PROC_LOCK_MODE_NONE = 'none';
	const ASS_PROC_LOCK_MODE_FILE = 'file';
	const ASS_PROC_LOCK_MODE_DB = 'db';

	var $setting;
	
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function ilObjAssessmentFolder($a_id = 0,$a_call_by_reference = true)
	{
		include_once "./Services/Administration/classes/class.ilSetting.php";
		$this->setting = new ilSetting("assessment");
		$this->type = "assf";
		$this->ilObject($a_id,$a_call_by_reference);
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
	* enable assessment logging
	*/
	function _enableAssessmentLogging($a_enable)
	{
		$setting = new ilSetting("assessment");

		if ($a_enable)
		{
			$setting->set("assessment_logging", 1);
		}
		else
		{
			$setting->set("assessment_logging", 0);
		}
	}

	/**
	* set the log language
	*/
	function _setLogLanguage($a_language)
	{
		$setting = new ilSetting("assessment");

		$setting->set("assessment_log_language", $a_language);
	}

	/**
	* check wether assessment logging is enabled or not
	*/
	function _enabledAssessmentLogging()
	{
		$setting = new ilSetting("assessment");

		return (boolean) $setting->get("assessment_logging");
	}
	
	/**
	* Returns the forbidden questiontypes for ILIAS
	*/
	function _getForbiddenQuestionTypes()
	{
		$setting = new ilSetting("assessment");
		$types = $setting->get("forbidden_questiontypes");
		$result = array();
		if (strlen(trim($types)) == 0)
		{
			$result = array();
		}
		else
		{
			$result = unserialize($types);
		}
		return $result;
	}

	/**
	* Sets the forbidden questiontypes for ILIAS
	*
	* @param array $a_types An array containing the database ID's of the forbidden question types
	*/
	function _setForbiddenQuestionTypes($a_types)
	{
		$setting = new ilSetting("assessment");
		$types = "";
		if (is_array($a_types) && (count($a_types) > 0))
		{
			$types = serialize($a_types);
		}
		$setting->set("forbidden_questiontypes", $types);
	}
	
	/**
	* retrieve the log language for assessment logging
	*/
	function _getLogLanguage()
	{
		$setting = new ilSetting("assessment");

		$lang = $setting->get("assessment_log_language");
		if (strlen($lang) == 0)
		{
			$lang = "en";
		}
		return $lang;
	}
	
	/**
	 * Returns the fact wether manually scoreable
	 * question types exist or not
	 * 
	 * @static
	 * @access	public
	 * 
	 * @return	boolean		$mananuallyScoreableQuestionTypesExists
	 */
	public static function _mananuallyScoreableQuestionTypesExists()
	{
		if( count(self::_getManualScoring()) > 0 )
		{
			return true;
		}
		
		return false;
	}

	/**
	* Retrieve the manual scoring settings
	*/
	function _getManualScoring()
	{
		$setting = new ilSetting("assessment");

		$types = $setting->get("assessment_manual_scoring");
		return explode(",", $types);
	}

	/**
	* Retrieve the manual scoring settings as type strings
	*/
	function _getManualScoringTypes()
	{
		global $ilDB;
		
		$result = $ilDB->query("SELECT * FROM qpl_qst_type");
		$dbtypes = array();
		while ($row = $ilDB->fetchAssoc($result))
		{
			$dbtypes[$row["question_type_id"]] = $row["type_tag"];
		}
		$setting = new ilSetting("assessment");
		$types = $setting->get("assessment_manual_scoring");
		$ids = explode(",", $types);
		foreach ($ids as $key => $value)
		{
			$ids[$key] = $dbtypes[$value];
		}
		return $ids;
	}

	/**
	* Set the manual scoring settings
	*
	* @param array $type_ids An array containing the database ids of the question types which could be scored manually
	*/
	function _setManualScoring($type_ids)
	{
		$setting = new ilSetting("assessment");
		if ((!is_array($type_ids)) || (count($type_ids) == 0))
		{
			$setting->delete("assessment_manual_scoring");
		}
		else
		{
			$setting->set("assessment_manual_scoring", implode($type_ids, ","));
		}
	}

	public static function getScoringAdjustableQuestions()
	{
		$setting = new ilSetting("assessment");

		$types = $setting->get("assessment_scoring_adjustment");
		return explode(",", $types);
	}
	
	public static function setScoringAdjustableQuestions($type_ids)
	{
		$setting = new ilSetting("assessment");
		if ((!is_array($type_ids)) || (count($type_ids) == 0))
		{
			$setting->delete("assessment_scoring_adjustment");
		}
		else
		{
			$setting->set("assessment_scoring_adjustment", implode($type_ids, ","));
		}
	}

	public static function getScoringAdjustmentEnabled()
	{
		$setting = new ilSetting("assessment");
		return $setting->get('assessment_adjustments_enabled');
	}

	public static function setScoringAdjustmentEnabled($active)
	{
		$setting = new ilSetting('assessment');
		$setting->set('assessment_adjustments_enabled', (bool) $active);
	}

	/**
	* Add an assessment log entry
	*
	* @param integer $user_id The user id of the acting user
	* @param integer $object_id The database id of the modified test object
	* @param string $logtext The textual description for the log entry
	* @param integer $question_id The database id of a modified question (optional)
	* @param integer $original_id The database id of the original of a modified question (optional)
	* @return array Array containing the datasets between $ts_from and $ts_to for the test with the id $test_id
	*/
	function _addLog($user_id, $object_id, $logtext, $question_id = "", $original_id = "", $test_only = FALSE, $test_ref_id = NULL)
	{
		global $ilUser, $ilDB;
		if (strlen($question_id) == 0) $question_id = NULL;
		if (strlen($original_id) == 0) $original_id = NULL;
		if (strlen($test_ref_id) == 0) $test_ref_id = NULL;
		$only = ($test_only == TRUE) ? 1 : 0;
		$next_id = $ilDB->nextId('ass_log');
		$affectedRows = $ilDB->manipulateF("INSERT INTO ass_log (ass_log_id, user_fi, obj_fi, logtext, question_fi, original_fi, test_only, ref_id, tstamp) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)",
			array('integer', 'integer', 'integer', 'text', 'integer', 'integer', 'text', 'integer', 'integer'),
			array(
				$next_id,
				$user_id,
				$object_id,
				$logtext,
				$question_id,
				$original_id,
				$only,
				$test_ref_id,
				time()
			)
		);
	}
	
	/**
	* Retrieve assessment log datasets from the database
	*
	* @param string $ts_from Timestamp of the starting date/time period
	* @param string $ts_to Timestamp of the ending date/time period
	* @param integer $test_id Database id of the ILIAS test object
	* @return array Array containing the datasets between $ts_from and $ts_to for the test with the id $test_id
	*/
	function &getLog($ts_from, $ts_to, $test_id, $test_only = FALSE)
	{
		global $ilDB;
		
		$log = array();
		if ($test_only == TRUE)
		{
			$result = $ilDB->queryF("SELECT * FROM ass_log WHERE obj_fi = %s AND tstamp > %s AND tstamp < %s AND test_only = %s ORDER BY tstamp",
				array('integer','integer','integer','text'),
				array(
					$test_id,
					$ts_from,
					$ts_to,
					1
				)
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT * FROM ass_log WHERE obj_fi = %s AND tstamp > %s AND tstamp < %s ORDER BY tstamp",
				array('integer','integer','integer'),
				array(
					$test_id,
					$ts_from,
					$ts_to
				)
			);
		}
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (!array_key_exists($row["tstamp"], $log))
			{
				$log[$row["tstamp"]] = array();
			}
			array_push($log[$row["tstamp"]], $row);
		}
		krsort($log);
		// flatten array
		$log_array = array();
		foreach ($log as $key => $value)
		{
			foreach ($value as $index => $row)
			{
				array_push($log_array, $row);
			}
		}
		return $log_array;
	}
	
	/**
	* Retrieve assessment log datasets from the database
	*
	* @param string $ts_from Timestamp of the starting date/time period
	* @param string $ts_to Timestamp of the ending date/time period
	* @param integer $test_id Database id of the ILIAS test object
	* @return array Array containing the datasets between $ts_from and $ts_to for the test with the id $test_id
	*/
	function &_getLog($ts_from, $ts_to, $test_id, $test_only = FALSE)
	{
		global $ilDB;
		
		$log = array();
		if ($test_only == TRUE)
		{
			$result = $ilDB->queryF("SELECT * FROM ass_log WHERE obj_fi = %s AND tstamp > %s AND tstamp < %s AND test_only = %s ORDER BY tstamp",
				array('integer', 'integer', 'integer', 'text'),
				array($test_id, $ts_from, $ts_to, 1)
			);
		}
		else
		{
			$result = $ilDB->queryF("SELECT * FROM ass_log WHERE obj_fi = %s AND tstamp > %s AND tstamp < %s ORDER BY tstamp",
				array('integer', 'integer', 'integer'),
				array($test_id, $ts_from, $ts_to)
			);
		}
		while ($row = $ilDB->fetchAssoc($result))
		{
			if (!array_key_exists($row["tstamp"], $log))
			{
				$log[$row["tstamp"]] = array();
			}
			$type_href = "";
			if (array_key_exists("ref_id", $row))
			{
				if ($row["ref_id"] > 0)
				{
					$type = ilObject::_lookupType($row['ref_id'], true);
					switch ($type)
					{
						case "tst":
							$type_href = sprintf("goto.php?target=tst_%s&amp;client_id=" . CLIENT_ID, $row["ref_id"]);
							break;
						case "cat":
							$type_href = sprintf("goto.php?target=cat_%s&amp;client_id=" . CLIENT_ID, $row["ref_id"]);
							break;
					}
				}
			}
			$row["href"] = $type_href;
			array_push($log[$row["tstamp"]], $row);
		}
		krsort($log);
		// flatten array
		$log_array = array();
		foreach ($log as $key => $value)
		{
			foreach ($value as $index => $row)
			{
				array_push($log_array, $row);
			}
		}
		return $log_array;
	}
	
	/**
	* Returns the number of log entries for a given test id
	*
	* @param integer $test_obj_id Database id of the ILIAS test object
	* @return integer The number of log entries for the test object
	*/
	function getNrOfLogEntries($test_obj_id)
	{
		global $ilDB;
		$result = $ilDB->queryF("SELECT COUNT(obj_fi) logcount FROM ass_log WHERE obj_fi = %s",
			array('integer'),
			array($test_obj_id)
		);
		if ($result->numRows())
		{
			$row = $ilDB->fetchAssoc($result);
			return $row["logcount"];
		}
		else
		{
			return 0;
		}
	}
	
	/**
	* Returns the full path output of an object
	*
	* @param integer $ref_id The reference id of the object
	* @return string The full path with hyperlinks to the path elements
	*/
	function getFullPath($ref_id)
	{
		global $tree;
		$path = $tree->getPathFull($ref_id);
		$pathelements = array();
		foreach ($path as $id => $data)
		{
			if ($id == 0)
			{
				array_push($pathelements, ilUtil::prepareFormOutput($this->lng->txt("repository")));
			}
			else
			{
				array_push($pathelements, "<a href=\"./goto.php?target=" . $data["type"] . "_" . $data["ref_id"] . "&amp;client=" . CLIENT_ID . "\">" .
					ilUtil::prepareFormOutput($data["title"]) . "</a>");
			}
		}
		return implode("&nbsp;&gt;&nbsp;", $pathelements);
	}
	
	/**
	* Deletes the log entries for a given array of test object IDs
	*
	* @param array $a_array An array containing the object IDs of the tests
	*/
	function deleteLogEntries($a_array)
	{
		global $ilDB;
		global $ilUser;
		
		foreach ($a_array as $object_id)
		{
			$affectedRows = $ilDB->manipulateF("DELETE FROM ass_log WHERE obj_fi = %s",
				array('integer'),
				array($object_id)
			);
			$this->_addLog($ilUser->getId(), $object_id, $this->lng->txt("assessment_log_deleted"));
		}
	}
	
	/**
	 * returns the fact wether content editing with ilias page editor is enabled for questions or not
	 * 
	 * @global ilSetting $ilSetting
	 * @return bool $isPageEditorEnabled
	 */
	public static function isAdditionalQuestionContentEditingModePageObjectEnabled()
	{
		require_once 'Modules/TestQuestionPool/classes/class.assQuestion.php';
		
		global $ilSetting;
		
		$isPageEditorEnabled = $ilSetting->get(
				'enable_tst_page_edit', self::ADDITIONAL_QUESTION_CONTENT_EDITING_MODE_PAGE_OBJECT_DISABLED
		);
		
		return $isPageEditorEnabled;
	}
	
	public function getAssessmentProcessLockMode()
	{
		return $this->setting->get('ass_process_lock_mode', self::ASS_PROC_LOCK_MODE_NONE);
	}

	public function setAssessmentProcessLockMode($lockMode)
	{
		$this->setting->set('ass_process_lock_mode', $lockMode);
	}
	
	public static function getValidAssessmentProcessLockModes()
	{
		return array(self::ASS_PROC_LOCK_MODE_NONE, self::ASS_PROC_LOCK_MODE_FILE, self::ASS_PROC_LOCK_MODE_DB);
	}
}
