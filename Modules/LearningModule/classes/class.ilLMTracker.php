<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Track access to ILIAS learning modules
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesLearningModule
 */
class ilLMTracker
{
	const NOT_ATTEMPTED = 0;
	const IN_PROGRESS = 1;
	const COMPLETED = 2;
	const FAILED = 3;
	const CURRENT = 99;

	protected $lm_ref_id;
	protected $lm_obj_id;
	protected $lm_tree;
	protected $lm_obj_ids = array();
	protected $tree_arr = array();		// tree array
	protected $re_arr = array();		// read event data array
	protected $loaded_for_node = false;	// current node for that the tracking data has been loaded
	protected $dirty = false;
	protected $page_questions = array();
	protected $all_questions = array();
	protected $answer_status = array();
	protected $has_incorrect_answers = false;
	protected $current_page_id = 0;

	static $instances = array();
	static $instancesbyobj = array();

	////
	//// Constructing
	////

	/**
	 * Constructor
	 *
	 * @param ilObjLearningModule $a_lm learning module
	 */
	private function __construct($a_id, $a_by_obj_id = false, $a_user_id)
	{
		$this->user_id = $a_user_id;

		if ($a_by_obj_id)
		{
			$this->lm_ref_id = 0;
			$this->lm_obj_id = $a_id;
		}
		else
		{
			$this->lm_ref_id = $a_id;
			$this->lm_obj_id = ilObject::_lookupObjId($a_id);
		}

		include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
		$this->lm_tree = ilLMTree::getInstance($this->lm_obj_id);
	}

	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static function getInstance($a_ref_id, $a_user_id = 0)
	{
		global $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		if (!isset(self::$instances[$a_ref_id][$a_user_id]))
		{
			self::$instances[$a_ref_id][$a_user_id] = new ilLMTracker($a_ref_id, false, $a_user_id);
		}
		return self::$instances[$a_ref_id][$a_user_id];
	}

	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static function getInstanceByObjId($a_obj_id, $a_user_id = 0)
	{
		global $ilUser;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		if (!isset(self::$instancesbyobj[$a_obj_id][$a_user_id]))
		{
			self::$instancesbyobj[$a_obj_id][$a_user_id] = new ilLMTracker($a_obj_id, true, $a_user_id);
		}
		return self::$instancesbyobj[$a_obj_id][$a_user_id];
	}

	////
	//// Tracking
	////

	/**
	 * Track access to lm page
	 *
	 * @param int $a_page_id page id
	 */
	function trackAccess($a_page_id)
	{
		if ($this->lm_ref_id == 0)
		{
			die("ilLMTracker: No Ref Id given.");
		}

		// track page and chapter access
		$this->trackPageAndChapterAccess($a_page_id);

		// track last page access (must be done after calling trackPageAndChapterAccess())
		$this->trackLastPageAccess($this->user_id, $this->lm_ref_id, $a_page_id);

		// #9483
		// general learning module lp tracking
		include_once("./Services/Tracking/classes/class.ilLearningProgress.php");
		ilLearningProgress::_tracProgress($this->user_id, $this->lm_obj_id,
			$this->lm_ref_id, "lm");

		// obsolete?
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_updateStatus($this->lm_obj_id, $this->user_id);

		// mark currently loaded data as dirty to force reload if necessary
		$this->dirty = true;
	}

	/**
	 * Track last accessed page for a learning module
	 *
	 * @param int $usr_id user id
	 * @param int $lm_id learning module id
	 * @param int $obj_id page id
	 */
	function trackLastPageAccess($usr_id, $lm_id, $obj_id)
	{
		global $ilDB;

		// first check if an entry for this user and this lm already exist, when so, delete
		$q = "DELETE FROM lo_access ".
			"WHERE usr_id = ".$ilDB->quote((int) $usr_id, "integer")." ".
			"AND lm_id = ".$ilDB->quote((int) $lm_id, "integer");
		$ilDB->manipulate($q);

		$title = "";

		$q = "INSERT INTO lo_access ".
			"(timestamp,usr_id,lm_id,obj_id,lm_title) ".
			"VALUES ".
			"(".$ilDB->now().",".
			$ilDB->quote((int) $usr_id, "integer").",".
			$ilDB->quote((int) $lm_id, "integer").",".
			$ilDB->quote((int) $obj_id, "integer").",".
			$ilDB->quote($title, "text").")";
		$ilDB->manipulate($q);
	}


	/**
	 * Track page and chapter access
	 */
	protected function trackPageAndChapterAccess($a_page_id)
	{
		global $ilDB;

		$now = time();

		//
		// 1. Page access: current page
		//
		$set = $ilDB->query("SELECT obj_id FROM lm_read_event".
			" WHERE obj_id = ".$ilDB->quote($a_page_id, "integer").
			" AND usr_id = ".$ilDB->quote($this->user_id, "integer"));
		if (!$ilDB->fetchAssoc($set))
		{
			$fields = array(
				"obj_id" => array("integer", $a_page_id),
				"usr_id" => array("integer", $this->user_id)
			);									
			// $ilDB->insert("lm_read_event", $fields);
			$ilDB->replace("lm_read_event", $fields, array()); // #15144
		}

		// update all parent chapters
		$ilDB->manipulate("UPDATE lm_read_event SET".
			" read_count = read_count + 1 ".
			" , last_access = ".$ilDB->quote($now, "integer").
			" WHERE obj_id = ".$ilDB->quote($a_page_id, "integer").
			" AND usr_id = ".$ilDB->quote($this->user_id, "integer"));


		//
		// 2. Chapter access: based on last page accessed
		//

		// get last accessed page
		$set = $ilDB->query("SELECT * FROM lo_access WHERE ".
			"usr_id = ".$ilDB->quote($this->user_id, "integer")." AND ".
			"lm_id = ".$ilDB->quote($this->lm_ref_id, "integer"));
		$res = $ilDB->fetchAssoc($set);
		if($res["obj_id"])
		{
			include_once('Services/Tracking/classes/class.ilObjUserTracking.php');
			$valid_timespan = ilObjUserTracking::_getValidTimeSpan();

			$pg_ts = new ilDateTime($res["timestamp"], IL_CAL_DATETIME);
			$pg_ts = $pg_ts->get(IL_CAL_UNIX);
			$pg_id = $res["obj_id"];
			if(!$this->lm_tree->isInTree($pg_id))
			{
				return;
			}
			
			$time_diff = $read_diff = 0;

			// spent_seconds or read_count ?
			if (($now-$pg_ts) <= $valid_timespan)
			{
				$time_diff = $now-$pg_ts;
			}
			else
			{
				$read_diff = 1;
			}

			// find parent chapter(s) for that page
			$parent_st_ids = array();
			foreach($this->lm_tree->getPathFull($pg_id) as $item)
			{
				if($item["type"] == "st")
				{
					$parent_st_ids[] = $item["obj_id"];
				}
			}

			if($parent_st_ids && ($time_diff || $read_diff))
			{
				// get existing chapter entries
				$ex_st = array();
				$set = $ilDB->query("SELECT obj_id FROM lm_read_event".
					" WHERE ".$ilDB->in("obj_id", $parent_st_ids, "", "integer").
					" AND usr_id = ".$ilDB->quote($this->user_id, "integer"));
				while($row = $ilDB->fetchAssoc($set))
				{
					$ex_st[] = $row["obj_id"];
				}

				// add missing chapter entries
				$missing_st = array_diff($parent_st_ids, $ex_st);
				if(sizeof($missing_st))
				{
					foreach($missing_st as $st_id)
					{
						$fields = array(
							"obj_id" => array("integer", $st_id),
							"usr_id" => array("integer", $this->user_id)
						);
						// $ilDB->insert("lm_read_event", $fields);
						$ilDB->replace("lm_read_event", $fields, array()); // #15144
					}
				}

				// update all parent chapters
				$ilDB->manipulate("UPDATE lm_read_event SET".
					" read_count = read_count + ".$ilDB->quote($read_diff, "integer").
					" , spent_seconds = spent_seconds + ".$ilDB->quote($time_diff, "integer").
					" , last_access = ".$ilDB->quote($now, "integer").
					" WHERE ".$ilDB->in("obj_id", $parent_st_ids, "", "integer").
					" AND usr_id = ".$ilDB->quote($this->user_id, "integer"));
			}
		}
	}


	////
	//// Tracking
	////

	/**
	 * Set current page
	 *
	 * @param id $a_val current page id
	 */
	public function setCurrentPage($a_val)
	{
		$this->current_page_id = $a_val;
	}

	/**
	 * Get current page
	 *
	 * @return id current page id
	 */
	public function getCurrentPage()
	{
		return $this->current_page_id;
	}

	/**
	 * Load LM tracking data. Loaded when needed.
	 *
	 * @param
	 * @return
	 */
	protected function loadLMTrackingData()
	{
		global $ilDB;

		// we must prevent loading tracking data multiple times during a request where possible
		// please note that the dirty flag works only to a certain limit
		// e.g. if questions are answered the flag is not set (yet)
		// or if pages/chapter are added/deleted the flag is not set
		if ($this->loaded_for_node === (int) $this->getCurrentPage() && !$this->dirty)
		{
			return;
		}

		$this->loaded_for_node = (int) $this->getCurrentPage();
		$this->dirty = false;

		// load lm tree in array
		$this->tree_arr = array();
		$nodes = $this->lm_tree->getSubTree($this->lm_tree->getNodeData($this->lm_tree->readRootId()));
		foreach ($nodes as $node)
		{
			$this->tree_arr["childs"][$node["parent"]][] = $node;
			$this->tree_arr["parent"][$node["child"]] = $node["parent"];
			$this->tree_arr["nodes"][$node["child"]] = $node;
		}

		// load all lm obj ids of learning module
		include_once("./Modules/LearningModule/classes/class.ilLMObject.php");
		$this->lm_obj_ids = ilLMObject::_getAllLMObjectsOfLM($this->lm_obj_id);

		// load read event data
		$this->re_arr = array();
		$set = $ilDB->query("SELECT * FROM lm_read_event ".
			" WHERE ".$ilDB->in("obj_id", $this->lm_obj_ids, false, "integer"));
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->re_arr[$rec["obj_id"]] = $rec;
		}

		// load question/pages information
		$this->page_questions = array();
		$this->all_questions = array();
		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		$q = ilLMPageObject::queryQuestionsOfLearningModule($this->lm_obj_id, "", "", 0, 0);
		foreach ($q["set"] as $quest)
		{
			$this->page_questions[$quest["page_id"]][] = $quest["question_id"];
			$this->all_questions[] = $quest["question_id"];
		}

		// load question answer information
		include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
		$this->answer_status = ilPageQuestionProcessor::getAnswerStatus($this->all_questions, $this->user_id);

		$this->has_incorrect_answers = false;

		$has_pred_incorrect_answers = false;
		$has_pred_incorrect_not_unlocked_answers = false;
		$this->determineProgressStatus($this->lm_tree->readRootId(), $has_pred_incorrect_answers, $has_pred_incorrect_not_unlocked_answers);

		$this->has_incorrect_answers = $has_pred_incorrect_answers;
	}

	/**
	 * Have all questoins been answered correctly (and questions exist)?
	 *
	 * @return bool true, if learning module contains any question and all questions (in the chapter structure) have been answered correctly
	 */
	function getAllQuestionsCorrect()
	{
		$this->loadLMTrackingData();
		if (count($this->all_questions) > 0 && !$this->has_incorrect_answers)
		{
			return true;
		}
		return false;
	}


	/**
	 * Determine progress status of nodes
	 *
	 * @param int $a_obj_id lm object id
	 * @return int status
	 */
	protected function determineProgressStatus($a_obj_id, &$a_has_pred_incorrect_answers, $a_has_pred_incorrect_not_unlocked_answers)
	{
		$status = ilLMTracker::NOT_ATTEMPTED;

		if (isset($this->tree_arr["nodes"][$a_obj_id]))
		{
			$this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"] = $a_has_pred_incorrect_answers;
			$this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_not_unlocked_answers"] = $a_has_pred_incorrect_not_unlocked_answers;

			if (is_array($this->tree_arr["childs"][$a_obj_id]))
			{
				// sort childs in correct order
				$this->tree_arr["childs"][$a_obj_id] = ilUtil::sortArray($this->tree_arr["childs"][$a_obj_id], "lft", "asc", true);

				$cnt_completed = 0;
				foreach ($this->tree_arr["childs"][$a_obj_id] as $c)
				{
					// if child is not activated/displayed count child as implicitly completed
					// rationale: everything that is visible for the learner determines the status
					// see also bug #14642
					if (!self::_isNodeVisible($c))
					{
						$cnt_completed++;
						continue;
					}

					$c_stat = $this->determineProgressStatus($c["child"], $a_has_pred_incorrect_answers,
						$a_has_pred_incorrect_not_unlocked_answers);
					if ($status != ilLMTracker::FAILED)
					{
						if ($c_stat == ilLMTracker::FAILED)
						{
							$status = ilLMTracker::IN_PROGRESS;
						}
						else if ($c_stat == ilLMTracker::IN_PROGRESS)
						{
							$status = ilLMTracker::IN_PROGRESS;
						}
						else if ($c_stat == ilLMTracker::COMPLETED || $c_stat == ilLMTracker::CURRENT)
						{
							$status = ilLMTracker::IN_PROGRESS;
							$cnt_completed++;
						}
					}
					if ($c_stat == ilLMTracker::FAILED || $c_stat == ilLMTracker::IN_PROGRESS)
					{
						$a_has_pred_incorrect_answers = true;
						if (!$this->tree_arr["nodes"][$c["child"]]["unlocked"])
						{
							$a_has_pred_incorrect_not_unlocked_answers = true;
						}
					}
				}
				if ($cnt_completed == count($this->tree_arr["childs"][$a_obj_id]))
				{
					$status = ilLMTracker::COMPLETED;
				}
			}
			else if ($this->tree_arr["nodes"][$a_obj_id]["type"] == "pg")
			{
				// check read event data
				if (isset($this->re_arr[$a_obj_id]) && $this->re_arr[$a_obj_id]["read_count"] > 0)
				{
					$status = ilLMTracker::COMPLETED;
				}
				else if ($a_obj_id == $this->getCurrentPage())
				{
					$status = ilLMTracker::CURRENT;
				}

				$unlocked = false;
				if (is_array($this->page_questions[$a_obj_id]))
				{
					// check questions, if one is failed -> failed
					$unlocked = true;
					foreach ($this->page_questions[$a_obj_id] as $q_id)
					{
						if (is_array($this->answer_status[$q_id])
							&& $this->answer_status[$q_id]["try"] > 0
							&& !$this->answer_status[$q_id]["passed"])
						{
							$status = ilLMTracker::FAILED;
							if (!$this->answer_status[$q_id]["unlocked"])
							{
								$unlocked = false;
							}
						}
					}

					// check questions, if one is not answered -> in progress
					if ($status != ilLMTracker::FAILED)
					{
						foreach ($this->page_questions[$a_obj_id] as $q_id)
						{
							if (!is_array($this->answer_status[$q_id])
								|| $this->answer_status[$q_id]["try"] == 0)
							{
								$status = ilLMTracker::IN_PROGRESS;
							}
						}
						$unlocked = false;
					}
				}
				$this->tree_arr["nodes"][$a_obj_id]["unlocked"] = $unlocked;
				$this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"] = $a_has_pred_incorrect_answers;
				$this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_not_unlocked_answers"] = $a_has_pred_incorrect_not_unlocked_answers;
			}
		}
		else	// free pages (currently not called, since only walking through tree structure)
		{

		}
		$this->tree_arr["nodes"][$a_obj_id]["status"] = $status;

		return $status;
	}


	/**
	 * Get icon for lm object
	 *
	 * @param array $a_node node array
	 * @param int $a_highlighted_node current node id
	 * @return string image path
	 */
	public function getIconForLMObject($a_node, $a_highlighted_node = 0)
	{
		$this->loadLMTrackingData();
		if ($a_node["child"] == $a_highlighted_node)
		{
			return ilUtil::getImagePath('scorm/running.svg');
		}
		if (isset($this->tree_arr["nodes"][$a_node["child"]]))
		{
			switch ($this->tree_arr["nodes"][$a_node["child"]]["status"])
			{
				case ilLMTracker::IN_PROGRESS:
					return ilUtil::getImagePath('scorm/incomplete.svg');

				case ilLMTracker::FAILED:
					return ilUtil::getImagePath('scorm/failed.svg');

				case ilLMTracker::COMPLETED:
					return ilUtil::getImagePath('scorm/completed.svg');
			}
		}
		return ilUtil::getImagePath('scorm/not_attempted.svg');
	}

	/**
	 * Has predecessing incorrect answers
	 *
	 * @param int $a_obj_id
	 * @return bool true if incorrect/unsanswered questions exist in predecessing pages
	 */
	function hasPredIncorrectAnswers($a_obj_id, $a_ignore_unlock = false)
	{
		$this->loadLMTrackingData();
		$ret = false;
		if (is_array($this->tree_arr["nodes"][$a_obj_id]))
		{
			if ($a_ignore_unlock)
			{
				$ret = $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"];
			}
			else
			{
				$ret = $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_not_unlocked_answers"];
			}
		}

		return $ret;
	}

	////
	//// Blocked Users
	////

	/**
	 * Get blocked users information
	 *
	 * @param
	 * @return
	 */
	function getBlockedUsersInformation()
	{
		global $ilDB, $lng, $ilPluginAdmin, $ilUser;

		$blocked_users = array();

		// load question/pages information
		$this->page_questions = array();
		$this->all_questions = array();
		$page_for_question = array();
		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		$q = ilLMPageObject::queryQuestionsOfLearningModule($this->lm_obj_id, "", "", 0, 0);
		foreach ($q["set"] as $quest)
		{
			$this->page_questions[$quest["page_id"]][] = $quest["question_id"];
			$this->all_questions[] = $quest["question_id"];
			$page_for_question[$quest["question_id"]] = $quest["page_id"];
		}

		// get question information
		include_once("./Modules/TestQuestionPool/classes/class.ilAssQuestionList.php");
		$qlist = new ilAssQuestionList($ilDB, $lng, $ilPluginAdmin, 0);
		$qlist->addFieldFilter("question_id", $this->all_questions);
		$qlist->load();
		$qdata = $qlist->getQuestionDataArray();

		// load question answer information
		include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
		$this->answer_status = ilPageQuestionProcessor::getAnswerStatus($this->all_questions);

		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		foreach ($this->answer_status as $as)
		{
			if ($as["try"] >= $qdata[$as["qst_id"]]["nr_of_tries"] && $qdata[$as["qst_id"]]["nr_of_tries"] > 0 && !$as["passed"])
			{
		//var_dump($qdata[$as["qst_id"]]);
				$name = ilObjUser::_lookupName($as["user_id"]);
				$as["user_name"] = $name["lastname"].", ".$name["firstname"]." [".$name["login"]."]";
				$as["question_text"] = $qdata[$as["qst_id"]]["question_text"];
				$as["page_id"] = $page_for_question[$as["qst_id"]];
				$as["page_title"] = ilLMPageObject::_lookupTitle($as["page_id"]);
				$blocked_users[] = $as;
			}
		}

		return $blocked_users;
	}

	/**
	 * Is node visible for the learner
	 *
	 * @param mixed $a_node node object/array
	 * @return boolean node visible true/false
	 */
	static function _isNodeVisible($a_node)
	{
		include_once("./Services/COPage/classes/class.ilPageObject.php");

		if ($a_node["type"] != "pg")
		{
			return true;
		}

		$lm_set = new ilSetting("lm");
		$active = ilPageObject::_lookupActive($a_node["child"], "lm",
			$lm_set->get("time_scheduled_page_activation"));

		if(!$active)
		{
			$act_data = ilPageObject::_lookupActivationData((int) $a_node["child"], "lm");
			if ($act_data["show_activation_info"] &&
				(ilUtil::now() < $act_data["activation_start"]))
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
			return true;
		}
	}


}

?>