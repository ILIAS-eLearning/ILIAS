<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Tracking user interface class.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesTracking
 */
class ilTrackingGUI
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		
	}
	
	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl;
	
		$nextClass = $ilCtrl->getNextClass();
		
		switch($nextClass)
		{
			default:
				$cmd = $ilCtrl->getCmd();
				$this->$cmd();
				break;
		}
		
	}
	
	/**
	 * Set object id
	 *
	 * @param	integer	object id
	 */
	function setObjectId($a_val)
	{
		$this->obj_id = $a_val;
	}
	
	/**
	 * Get object id
	 *
	 * @return	integer	object id
	 */
	function getObjectId()
	{
		return $this->obj_id;
	}
	
	/**
	 * For one object: List users (rows) and tracking properties in columns
	 * Prerequisite: object id is set
	 */
	function showObjectUsersProps()
	{
		global $tpl;
	
		include_once("./Services/Tracking/classes/class.ilTrObjectUsersPropsTableGUI.php");
		$table = new ilTrObjectUsersPropsTableGUI($this, "showObjectUsersProps", "troup".$this->getObjectId(),
			$this->getObjectId());
		$tpl->setContent($table->getHTML());
	}

	/**
	 *
	 */
	function showObjectSummary()
	{
		global $tpl;

		$data = array();
		$this->buildSummaryData($data, $this->getObjectId());

		include_once("./Services/Tracking/classes/class.ilTrSummaryTableGUI.php");
		$table = new ilTrSummaryTableGUI($this, "showObjectSummary");
		$table->setData($data);

		$tpl->setContent($table->getHTML());
	}

	protected function buildSummaryData(&$rows, $object_id)
	{
		global $lng;

		include_once("./Services/Tracking/classes/class.ilTrQuery.php");

		$a_users = ilTrQuery::getParticipantsForObject($object_id);
		$users_no = 0;
		if(is_array($a_users))
			$users_no = sizeof($a_users);

		$type = ilObject::_lookupType($object_id);

		$result = array();
		$result["id"] = $object_id;
		$result["title"] = ilObject::_lookupTitle($object_id);
		$result["type"] = $type;

		if($users_no > 0)
		{		
			$summary = ilTrQuery::getSummaryDataForObject($object_id);
		
			// user related

			$result["user_total"] = $users_no;

			$result["country"] = $this->buildSummaryPercentages($summary["countries"], $users_no);

			$result["registration_earliest"] = ilDatePresentation::formatDate(new ilDateTime($summary["first_registration"],IL_CAL_DATETIME));
			$result["registration_latest"] = ilDatePresentation::formatDate(new ilDateTime($summary["last_registration"],IL_CAL_DATETIME));

			$result["gender"] = $this->buildSummaryPercentages($summary["gender"], $users_no, array("m"=>$lng->txt("gender_m"), "f"=>$lng->txt("gender_f")));

			$result["city"] = $this->buildSummaryPercentages($summary["cities"], $users_no);

			$languages = array();
			foreach ($lng->getInstalledLanguages() as $lang_key)
			{
				$languages[$lang_key] = $lng->txt("lang_".$lang_key);
			}
			$result["language"] = $this->buildSummaryPercentages($summary["languages"], $users_no, $languages);


			// object related
			$result["access_total"] = $summary["sum_access"];
			$result["access_average"] = $summary["avg_access"];

			$result["activity_earliest"] = ilDatePresentation::formatDate(new ilDateTime($summary["first_access"],IL_CAL_DATETIME));
			$result["activity_latest"] = ilDatePresentation::formatDate(new ilDateTime($summary["last_access"],IL_CAL_UNIX));

			include_once("./classes/class.ilFormat.php");
			$result["time_average"] = ilFormat::_secondsToString($summary["avg_learn_time"]);

			include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
			include_once("./Services/Tracking/classes/class.ilLPStatus.php");
			$map = array();
			foreach(array(LP_STATUS_NOT_ATTEMPTED_NUM, LP_STATUS_IN_PROGRESS_NUM, LP_STATUS_COMPLETED_NUM, LP_STATUS_FAILED_NUM) as $status)
			{
				$path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
				$text = ilLearningProgressBaseGUI::_getStatusText($status);
				$map[$status] = ilUtil::img($path, $text);
			}
			$result["status"] = $this->buildSummaryPercentages($summary["status"], $users_no, $map);

			$result["mark"] = $this->buildSummaryPercentages($summary["mark"], $users_no);

			$result["completion_average"] = $summary["avg_completion"]."%";
		}

		$rows[] = $result;

		
		// --- child objects

		if($type != 'sahs_item' and
		   $type != 'objective' and
		   $type != 'event')
		{
			include_once 'Services/Tracking/classes/class.ilLPCollectionCache.php';
			foreach(ilLPCollectionCache::_getItems($object_id) as $child_ref_id)
			{
				$child_id = ilObject::_lookupObjId($child_ref_id);
				$this->buildSummaryData($rows, $child_id);
			}
		}
	}

	protected function buildSummaryPercentages(array $data, $overall, array $value_map = NULL, $limit = 3)
	{
		global $lng;

		$result = array();
		$counter = $others_counter = $others_sum = 0;
		foreach($data as $id => $count)
		{			
			$counter++;
			if($counter <= $limit)
			{
				$caption = $id;

				if($value_map && isset($value_map[$id]))
				{
					$caption = $value_map[$id];
				}

				if($caption == "")
				{
					$caption = $lng->txt("none");
				}

				$perc = round($count/$overall*100);
				$result[] = array(
					"caption" => $caption,
					"absolute" => $count." ".($count > 1 ? $lng->txt("users") : $lng->txt("user")),
					"percentage" => $perc
					);
			}
			else
			{
				$others_sum += $count;
				$others_counter++;
			}
		}

		if($others_counter)
		{
			$perc = round($others_sum/$overall*100);
			$result[] = array(
				"caption" => $otherss_counter."  ".$lng->txt("more"),
				"absolute" => $others_sum." ".($others_sum > 1 ? $lng->txt("users") : $lng->txt("user")),
				"percentage" => $perc
				);
		}

		return $result;
	}
}
?>