<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup Services
 */
class ilTrSummaryTableGUI extends ilLPTableBaseGUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id, $a_print_mode = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng;

		$this->setId("trsmy");

		parent::__construct($a_parent_obj, $a_parent_cmd);

		if($a_print_mode)
		{
			$this->setPrintMode(true);
		}

		// $this->setTitle($lng->txt("tr_summary"));
		$this->setLimit(9999);
		$this->setShowTemplates(true);

		$this->addColumn($this->lng->txt("title"), "title");

		$labels = $this->getSelectableColumns();
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($labels[$c]["txt"], $c);
		}

		// $this->setExternalSorting(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setRowTemplate("tpl.trac_summary_row.html", "Services/Tracking");
		$this->initFilter($a_parent_obj->getObjId());

		$this->getItems($a_parent_obj->getObjId(), $a_ref_id);
	}

	function getSelectableColumns()
	{
		global $lng;

		$lng_map = array("first_access_min" => "trac_first_access",
			"last_access_max" => "trac_last_access", "mark" => "trac_mark", "status" => "trac_status",
			"spent_seconds_avg" => "trac_spent_seconds", "percentage_avg" => "trac_percentage",
			"read_count_sum" => "trac_read_count", "read_count_avg" => "trac_read_count"
			);

		$all = array("user_total", "country", "create_date_min", "create_date_max",
			"gender", "city", "language","read_count_sum", "read_count_avg", "first_access_min",
			"last_access_max", "spent_seconds_avg",	"status", "mark", "percentage_avg");
		
		$default = array("read_count_sum", "spent_seconds_avg", "status", "mark",
			"percentage_avg");

		$columns = array();
		foreach($all as $column)
		{
			$l = $column;
			
			$prefix = false;
			if(substr($l, -3) == "avg")
			{
				$prefix = "&#216; ";
			}
			else if(substr($l, -3) == "sum" || $l == "user_total")
			{
				$prefix = "&#8721; ";
			}
	
			if(isset($lng_map[$l]))
			{
				$l = $lng_map[$l];
			}
			
			$txt = $prefix.$lng->txt($l);

			$columns[$column] = array(
				"txt" => $txt,
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
		$this->filter["first_access"] = $item->getDate();

		$item = $this->addFilterItemByMetaType("trac_last_access", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["last_access"] = $item->getDate();
	}

	/**
	 * Build summary item rows for given object and filter(s
	 *
	 * @param	int		$a_object_id
	 * @param	int		$a_ref_id
	 */
	function getItems($a_object_id, $a_ref_id)
	{
		global $lng;

		include_once("./Services/Tracking/classes/class.ilTrQuery.php");

		$data = ilTrQuery::getObjectsSummaryForObject(
				$a_object_id,
				$a_ref_id,
				ilUtil::stripSlashes($this->getOrderField()),
				ilUtil::stripSlashes($this->getOrderDirection()),
				ilUtil::stripSlashes($this->getOffset()),
				ilUtil::stripSlashes($this->getLimit()),
				$this->getCurrentFilter(),
				$this->getSelectedColumns()
				);

		$rows = array();
		foreach($data["set"] as $idx => $result)
		{
			// sessions have no title
			if($result["title"] == "" && $result["type"] == "sess")
			{
				include_once "Modules/Session/classes/class.ilObjSession.php";
				$sess = new ilObjSession($result["obj_id"], false);
				$data["set"][$idx]["title"] = $sess->getFirstAppointment()->appointmentToString();
			}

			// percentages
			$users_no = $result["user_total"];
			$data["set"][$idx]["country"] = $this->getItemsPercentages($result["country"], $users_no);
			$data["set"][$idx]["gender"] = $this->getItemsPercentages($result["gender"], $users_no, array("m"=>$lng->txt("gender_m"), "f"=>$lng->txt("gender_f")));
			$data["set"][$idx]["city"] = $this->getItemsPercentages($result["city"], $users_no);

			$languages = array();
			foreach ($lng->getInstalledLanguages() as $lang_key)
			{
				$languages[$lang_key] = $lng->txt("lang_".$lang_key);
			}
			$data["set"][$idx]["language"] = $this->getItemsPercentages($result["language"], $users_no, $languages);

			include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
			include_once("./Services/Tracking/classes/class.ilLPStatus.php");
			$map = array();
			foreach(array(LP_STATUS_NOT_ATTEMPTED_NUM, LP_STATUS_IN_PROGRESS_NUM, LP_STATUS_COMPLETED_NUM, LP_STATUS_FAILED_NUM) as $status)
			{
				$path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
				$text = ilLearningProgressBaseGUI::_getStatusText($status);
				$map[$status] = ilUtil::img($path, $text);
			}
			$map[""] = $map[0];
			$data["set"][$idx]["status"] = $this->getItemsPercentages($result["status"], $users_no, $map);
			$data["set"][$idx]["mark"] = $this->getItemsPercentages($result["mark"], $users_no);

			if(!$this->isPercentageAvailable($result["obj_id"]))
			{
				$data["set"][$idx]["percentage_avg"] = NULL;
			}
		}

		$this->setMaxCount($data["cnt"]);
		$this->setData($data["set"]);
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
	protected function getItemsPercentages(array $data = NULL, $overall, array $value_map = NULL, $limit = 3)
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

	protected function parseValue($id, $value, $type)
	{
		global $lng;
		
		// get rid of aggregation
		$pos = strrpos($id, "_");
		if($pos !== false)
		{
			$function = strtoupper(substr($id, $pos+1));
			if(in_array($function, array("MIN", "MAX", "SUM", "AVG", "COUNT")))
			{
				$id = substr($id, 0, $pos);
			}
		}

		if(trim($value) == "")
		{
			if($id == "title")
			{
				return "--".$lng->txt("none")."--";
			}
			return "";
		}

		switch($id)
		{
			case "first_access":
			case "create_date":
				$value = ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_DATETIME));
				break;

			case "last_access":
				$value = ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_UNIX));
				break;

			case "spent_seconds":
				if(in_array($type, array("exc")))
				{
					$value = "-";
				}
				else
				{
					include_once("./classes/class.ilFormat.php");
					$value = ilFormat::_secondsToString($value);
				}
				break;

			case "percentage":
				/* :TODO:
				if(in_array(strtolower($this->status_class),
						  array("illpstatusmanual", "illpstatusscormpackage", "illpstatustestfinished")) ||
				$type == "exc"))
				*/
			    if(false)
				{
					$value = "-";
				}
				else
				{
					$value = $value."%";
				}
				break;

			case "mark":
				if(in_array($type, array("lm", "dbk")))
				{
					$value = "-";
				}
				break;
		}

		return $value;
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

		$this->tpl->setVariable("ICON", ilUtil::getTypeIconPath($a_set["type"], $a_set["id"], "tiny"));
		$this->tpl->setVariable("ICON_ALT", $lng->txt($a_set["type"]));
	    $this->tpl->setVariable("TITLE", $a_set["title"]);

		foreach ($this->getSelectedColumns() as $c)
		{
			switch($c)
			{
				case "country":
				case "gender":
				case "city":
				case "language":
				case "status":
				case "mark":
					$this->renderPercentages($c, $a_set[$c]);
					break;

				case "percentage_avg":
					if((int)$a_set[$c] === 0)
					{
						$this->tpl->setVariable(strtoupper($c), "");
						break;
					}
					
				default:
					$value = $this->parseValue($c, $a_set[$c], $a_set["type"]);
					$this->tpl->setVariable(strtoupper($c), $value);
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
	   else
	   {
		   $this->tpl->touchBlock($id);;
	   }
	}
}
?>
