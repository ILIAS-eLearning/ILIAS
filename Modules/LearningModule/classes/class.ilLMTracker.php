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
	protected $lm;
	protected $lm_tree;

	/**
	 * Constructor
	 *
	 * @param ilObjLearningModule $a_lm learning module
	 */
	function __construct(ilObjLearningModule $a_lm)
	{
		$this->lm = $a_lm;

		include_once("./Modules/LearningModule/classes/class.ilLMTree.php");
		$this->lm_tree = ilLMTree::getInstance($this->lm->getId());
	}

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
		$this->trackLastPageAccess($ilUser->getId(), $this->lm->getRefId(), $a_page_id);

		// #9483
		// general learning module lp tracking
		include_once("./Services/Tracking/classes/class.ilLearningProgress.php");
		ilLearningProgress::_tracProgress($ilUser->getId(), $this->lm->getId(),
			$this->lm->getRefId(), $this->lm->getType());

		// obsolete?
		include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
		ilLPStatusWrapper::_updateStatus($this->lm->getId(), $ilUser->getId());

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

		$title = (is_object($this->lm))?$this->lm->getTitle():"- no title -";

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
			" read_count = read_count + ".$ilDB->quote($read_diff, "integer").
			" , last_access = ".$ilDB->quote($now, "integer").
			" WHERE obj_id = ".$ilDB->quote($a_page_id, "integer").
			" AND usr_id = ".$ilDB->quote($ilUser->getId(), "integer"));


		//
		// 2. Chapter access: based on last page accessed
		//

		// get last accessed page
		$set = $ilDB->query("SELECT * FROM lo_access WHERE ".
			"usr_id = ".$ilDB->quote($ilUser->getId(), "integer")." AND ".
			"lm_id = ".$ilDB->quote($this->lm->getRefId(), "integer"));
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

}

?>