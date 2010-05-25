<?php

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTrSummaryTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId("tr_summary");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setTitle($lng->txt("tr_summary"));
		$this->setLimit(9999);
		$this->setShowTemplates(true);

		$this->addColumn($this->lng->txt("title"));

		// re-use caption from learners list
		$this->lng_map = array("activity_earliest" => "trac_first_access", "activity_latest" => "trac_last_access",
			"mark" => "trac_mark", "status" => "trac_status", "time_average" => "trac_spent_seconds",
			"access_total" => "trac_read_count", "completion_average" => "trac_percentage"
			);

		foreach ($this->getSelectedColumns() as $c)
		{
			$l = $c;
			if(isset($this->lng_map[$l]))
			{
				$l = $this->lng_map[$l];
			}
			$this->addColumn($this->lng->txt($l), $c);
		}

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "applyFilter"));
		$this->setRowTemplate("tpl.trac_summary_row.html", "Services/Tracking");
		$this->setFilterCommand("applyFilterSummary");
		$this->setResetCommand("resetFilterSummary");
		$this->initFilter($a_parent_obj->getObjectId());

		$data = array();
		$this->getItems($data, $a_parent_obj->getObjectId(), $this->getCurrentFilter());
		$this->setData($data);

		// $this->addCommandButton("", $lng->txt(""));
	}

	function getSelectableColumns()
	{
		global $lng;

		$all = array("user_total", "country", "registration_earliest", "registration_latest",
			"gender", "city", "language", "access_total", "access_average", "activity_earliest",
			"activity_latest", "time_average", "status", "mark", "completion_average");
		
		$default = array("user_total", "access_total", "access_average", "time_average", "status", "mark", "completion_average");

		$columns = array();
		foreach($all as $column)
		{
			$l = $column;
			if(isset($this->lng_map[$l]))
			{
				$l = $this->lng_map[$l];
			}
			$columns[$column] = array(
				"txt" => $lng->txt($l),
				"default" => (in_array($column, $default) ? true :false)
			);
		}
		return $columns;
	}

	/**
	* Init filter
	*/
	function initFilter($a_obj_id)
	{
		global $lng;

		$item = $this->addFilterItemByMetaType("country", ilTable2GUI::FILTER_TEXT, true);
		$this->filter["country"] = $item->getValue();

		$item = $this->addFilterItemByMetaType("registration_filter", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["registration"] = $item->getDate();

		$item = $this->addFilterItemByMetaType("gender", ilTable2GUI::FILTER_SELECT, true);
		$item->setOptions(array("" => $lng->txt("all"), "m" => $lng->txt("gender_m"), "f" => $lng->txt("gender_f")));
		$this->filter["gender"] = $item->getValue();

        $item = $this->addFilterItemByMetaType("city", ilTable2GUI::FILTER_TEXT, true);
		$this->filter["city"] = $item->getValue();
		
        $item = $this->addFilterItemByMetaType("language", ilTable2GUI::FILTER_LANGUAGE, true);
		$this->filter["language"] = $item->getValue();

		$item = $this->addFilterItemByMetaType("user_total", ilTable2GUI::FILTER_NUMBER_RANGE, true);
		$this->filter["user_total"] = $item->getValue();

		$item = $this->addFilterItemByMetaType("trac_first_access", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["activity_earliest"] = $item->getDate();

		$item = $this->addFilterItemByMetaType("trac_last_access", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["activity_latest"] = $item->getDate();
	}

	/**
	 * Build summary item rows for given object and filter(s
	 *
	 * @param	array	&$rows
	 * @param	int		$object_id
	 * @param	array	$filter
	 */
	function getItems(&$rows, $object_id, array $filter = NULL)
	{
		global $lng;

		include_once("./Services/Tracking/classes/class.ilTrQuery.php");

		$type = ilObject::_lookupType($object_id);

		$result = array();
		$result["id"] = $object_id;
		$result["title"] = ilObject::_lookupTitle($object_id);
		$result["type"] = $type;

		$summary = ilTrQuery::getSummaryDataForObject($object_id, $filter);
		$users_no = $summary["cnt"];
		$summary = $summary["set"];
		if(sizeof($summary))
		{
			// sessions have no title
			if($result["title"] == "" && $type == "sess")
			{
				include_once "modules/Session/classes/class.ilObjSession.php";
				$sess = new ilObjSession($object_id, false);
				$result["title"] = $sess->getFirstAppointment()->appointmentToString();
			}


			// user related

			$result["user_total"] = $users_no;

			$result["country"] = $this->getItemsPercentages($summary["countries"], $users_no);

			$result["registration_earliest"] = ilDatePresentation::formatDate(new ilDateTime($summary["first_registration"],IL_CAL_DATETIME));
			$result["registration_latest"] = ilDatePresentation::formatDate(new ilDateTime($summary["last_registration"],IL_CAL_DATETIME));

			$result["gender"] = $this->getItemsPercentages($summary["gender"], $users_no, array("m"=>$lng->txt("gender_m"), "f"=>$lng->txt("gender_f")));

			$result["city"] = $this->getItemsPercentages($summary["cities"], $users_no);

			$languages = array();
			foreach ($lng->getInstalledLanguages() as $lang_key)
			{
				$languages[$lang_key] = $lng->txt("lang_".$lang_key);
			}
			$result["language"] = $this->getItemsPercentages($summary["languages"], $users_no, $languages);


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
			$result["status"] = $this->getItemsPercentages($summary["status"], $users_no, $map);

			$result["mark"] = $this->getItemsPercentages($summary["mark"], $users_no);

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
				$this->getItems($rows, $child_id, $filter);
			}
		}
	}

	/**
	 * Render data as needed for summary list (based on grouped values)
	 *
	 * @param	array	$data		rows data
	 * @param	int		$overall	overall number of entries
	 * @param	array	$value_map	labels for values
	 * @param	int		$limit		summarize all entries beyond limit
	 * @return	array
	 */
	protected function getItemsPercentages(array $data, $overall, array $value_map = NULL, $limit = 3)
	{
		global $lng;

		if(!$overall)
		{
			return false;
		}

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

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		if(!$a_set["title"])
		{
			$a_set["title"] = "--".$lng->txt("none")."--";
		}

		$this->tpl->setVariable("ICON", ilUtil::getTypeIconPath($a_set["type"], $a_set["id"], "small"));
	    $this->tpl->setVariable("TITLE", $a_set["title"]);

		foreach ($this->getSelectedColumns() as $c)
		{
			switch($c)
			{
				case "title":
				case "user_total":
				case "registration_earliest":
				case "registration_latest":
				case "access_total":
				case "access_average":
				case "activity_earliest":
				case "activity_latest":
				case "completion_average":
				case "time_average":
					$this->tpl->setVariable(strtoupper($c), $a_set[$c]);
					break;

				case "country":
				case "gender":
				case "city":
				case "language":
				case "status":
				case "mark":
					$this->renderPercentages($c, $a_set[$c]);
					break;
			}
		}
	}

	protected function renderPercentages($id, $data)
	{
	  if($data)
	  {		  
		  foreach($data as $item)
		  {
			$this->tpl->setCurrentBlock($id."_row");
			$this->tpl->setVariable("CAPTION", $item["caption"]);
			$this->tpl->setVariable("ABSOLUTE", $item["absolute"]);
			$this->tpl->setVariable("PERCENTAGE", $item["percentage"]);
			$this->tpl->parseCurrentBlock();
		  }
	   }
	}

	public function getCurrentFilter()
	{
		$result = array();
		foreach($this->filter as $id => $value)
		{
		  $item = $this->getFilterItemByPostVar($id);
		  switch($id)
		  {
			 case "title":
			 case "country":
			 case "gender":
			 case "city":
			 case "language":
			     if($value)
				 {
					 $result[$id] = $value;
				 }
				 break;

			case "user_total":
				if(is_array($value) && implode("", $value))
				{
					$result[$id] = $value;
				}
				break;

			 case "registration":
			 case "activity_earliest":
			 case "activity_latest":
				 if($value)
				 {
					 if($value["from"])
					 {
						 $result[$id]["from"] = $value["from"]->get(IL_CAL_DATETIME);
						 $result[$id]["from"] = substr($result[$id]["from"], 0, -8)."00:00:00";
					 }
					 if($value["to"])
					 {
						 $result[$id]["to"] = $value["to"]->get(IL_CAL_DATETIME);
						 $result[$id]["to"] = substr($result[$id]["to"], 0, -8)."23:59:59";
					 }
				 }
				 break;
		  }
		}
		return $result;
	}
}
?>
