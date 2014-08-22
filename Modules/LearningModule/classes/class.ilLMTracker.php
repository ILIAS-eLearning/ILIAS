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

	static $instances = array();

	////
	//// Constructing
	////

	/**
	 * Constructor
	 *
	 * @param ilObjLearningModule $a_lm learning module
	 */
	private function __construct($a_ref_id)
	{
		$this->lm_ref_id = $a_ref_id;
		$this->lm_obj_id = ilObject::_lookupObjId($a_ref_id);

		include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
		$this->lm_tree = ilLMTree::getInstance($this->lm_obj_id);
	}

	/**
	 * Get instance
	 *
	 * @param
	 * @return
	 */
	static function getInstance($a_ref_id)
	{
		if (!isset(self::$instances[$a_ref_id]))
		{
			self::$instances[$a_ref_id] = new ilLMTracker($a_ref_id);
		}
		return self::$instances[$a_ref_id];
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
		global $ilUser;

		// track page and chapter access
		$this->trackPageAndChapterAccess($a_page_id);

		// track last page access (must be done after calling trackPageAndChapterAccess())
		$this->trackLastPageAccess($ilUser->getId(), $this->lm_ref_id, $a_page_id);

		// #9483
		// general learning module lp tracking
		include_once("./Services/Tracking/classes/class.ilLearningProgress.php");
		ilLearningProgress::_tracProgress($ilUser->getId(), $this->lm_obj_id,
			$this->lm_ref_id, "lm");

		// obsolete?
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_updateStatus($this->lm_obj_id, $ilUser->getId());

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
		global $ilDB, $ilUser;


		//
		// 1. Page access: current page
		//
		$set = $ilDB->query("SELECT obj_id FROM lm_read_event".
			" WHERE obj_id = ".$ilDB->quote($a_page_id, "integer").
			" AND usr_id = ".$ilDB->quote($ilUser->getId(), "integer"));
		if (!$ilDB->fetchAssoc($set))
		{
			$fields = array(
				"obj_id" => array("integer", $a_page_id),
				"usr_id" => array("integer", $ilUser->getId())
			);
			$ilDB->insert("lm_read_event", $fields);
		}

		// update all parent chapters
		$ilDB->manipulate("UPDATE lm_read_event SET".
			" read_count = read_count + 1 ".
			" , last_access = ".$ilDB->quote($now, "integer").
			" WHERE obj_id = ".$ilDB->quote($a_page_id, "integer").
			" AND usr_id = ".$ilDB->quote($ilUser->getId(), "integer"));


		//
		// 2. Chapter access: based on last page accessed
		//

		// get last accessed page
		$set = $ilDB->query("SELECT * FROM lo_access WHERE ".
			"usr_id = ".$ilDB->quote($ilUser->getId(), "integer")." AND ".
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

			$now = time();
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
					" AND usr_id = ".$ilDB->quote($ilUser->getId(), "integer"));
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
							"usr_id" => array("integer", $ilUser->getId())
						);
						$ilDB->insert("lm_read_event", $fields);
					}
				}

				// update all parent chapters
				$ilDB->manipulate("UPDATE lm_read_event SET".
					" read_count = read_count + ".$ilDB->quote($read_diff, "integer").
					" , spent_seconds = spent_seconds + ".$ilDB->quote($time_diff, "integer").
					" , last_access = ".$ilDB->quote($now, "integer").
					" WHERE ".$ilDB->in("obj_id", $parent_st_ids, "", "integer").
					" AND usr_id = ".$ilDB->quote($ilUser->getId(), "integer"));
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
		global $ilDB, $ilUser;

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
		$set = $ilDB->query("SELECT * FROM lm_read_event ".
			" WHERE ".$ilDB->in("obj_id", $this->lm_obj_ids, false, "integer"));
		while ($rec = $ilDB->fetchAssoc($set))
		{
			$this->re_arr[$rec["obj_id"]] = $rec;
		}

		// load question/pages information
		include_once("./Modules/LearningModule/classes/class.ilLMPageObject.php");
		$q = ilLMPageObject::queryQuestionsOfLearningModule($this->lm_obj_id, "", "", 0, 0);
		foreach ($q["set"] as $quest)
		{
			$this->page_questions[$quest["page_id"]][] = $quest["question_id"];
			$this->all_questions[] = $quest["question_id"];
		}

		// load question answer information
		include_once("./Services/COPage/classes/class.ilPageQuestionProcessor.php");
		$this->answer_status = ilPageQuestionProcessor::getAnswerStatus($this->all_questions, $ilUser->getId());

		$has_pred_incorrect_answers = false;
		$this->determineProgressStatus($this->lm_tree->readRootId(), $has_pred_incorrect_answers);
	}

	/**
	 * Determine progress status of nodes
	 *
	 * @param int $a_obj_id lm object id
	 * @return int status
	 */
	protected function determineProgressStatus($a_obj_id, &$a_has_pred_incorrect_answers)
	{
		$status = ilLMTracker::NOT_ATTEMPTED;

		if (isset($this->tree_arr["nodes"][$a_obj_id]))
		{
			if (is_array($this->tree_arr["childs"][$a_obj_id]))
			{
				$this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"] = $a_has_pred_incorrect_answers;
				// sort childs in correct order
				$this->tree_arr["childs"][$a_obj_id] = ilUtil::sortArray($this->tree_arr["childs"][$a_obj_id], "lft", "asc", true);

				$cnt_completed = 0;
				foreach ($this->tree_arr["childs"][$a_obj_id] as $c)
				{
					$c_stat = $this->determineProgressStatus($c["child"], $a_has_pred_incorrect_answers);
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

				if (is_array($this->page_questions[$a_obj_id]))
				{
					// check questions, if one is failed -> failed
					foreach ($this->page_questions[$a_obj_id] as $q_id)
					{
						if (is_array($this->answer_status[$q_id])
							&& $this->answer_status[$q_id]["try"] > 0
							&& !$this->answer_status[$q_id]["passed"])
						{
							$status = ilLMTracker::FAILED;
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
					}
				}
				$this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"] = $a_has_pred_incorrect_answers;
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
			return ilUtil::getImagePath('scorm/running.png');
		}
		if (isset($this->tree_arr["nodes"][$a_node["child"]]))
		{
			switch ($this->tree_arr["nodes"][$a_node["child"]]["status"])
			{
				case ilLMTracker::IN_PROGRESS:
					return ilUtil::getImagePath('scorm/incomplete.png');

				case ilLMTracker::FAILED:
					return ilUtil::getImagePath('scorm/failed.png');

				case ilLMTracker::COMPLETED:
					return ilUtil::getImagePath('scorm/completed.png');
			}
		}
		return ilUtil::getImagePath('scorm/not_attempted.png');
	}

	/**
	 * Has predecessing incorrect answers
	 *
	 * @param int $a_obj_id
	 * @return bool true if incorrect/unsanswered questions exist in predecessing pages
	 */
	function hasPredIncorrectAnswers($a_obj_id)
	{
		$this->loadLMTrackingData();
		if (is_array($this->tree_arr["nodes"][$a_obj_id]))
		{
			return $this->tree_arr["nodes"][$a_obj_id]["has_pred_incorrect_answers"];
		}
		return false;
	}


}

?>