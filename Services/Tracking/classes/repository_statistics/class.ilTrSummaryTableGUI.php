<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ilCtrl_Calls ilTrSummaryTableGUI: ilFormPropertyDispatchGUI
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

		$this->ref_id = $a_ref_id;
		$this->obj_id = ilObject::_lookupObjId($a_ref_id);
		$this->is_root = ($a_ref_id == ROOT_FOLDER_ID);
		
		if(!$this->is_root)
		{
			include_once './Services/Object/classes/class.ilObjectLP.php';
			$this->olp = ilObjectLP::getInstance($this->obj_id);		
		}

		parent::__construct($a_parent_obj, $a_parent_cmd);

		if($a_print_mode)
		{
			$this->setPrintMode(true);
		}

		$this->parseTitle($this->obj_id, "trac_summary");
		$this->setLimit(9999);
		$this->setShowTemplates(true);
		$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

		$this->addColumn($this->lng->txt("title"), "title");
		$this->setDefaultOrderField("title");
		
		$labels = $this->getSelectableColumns();
		foreach ($this->getSelectedColumns() as $c)
		{
			$this->addColumn($labels[$c]["txt"], $c);
		}

		if($this->is_root)
		{
			$this->addColumn($this->lng->txt("path"));
			$this->addColumn($this->lng->txt("action"));
		}
		$this->initFilter();

		// $this->setExternalSorting(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setRowTemplate("tpl.trac_summary_row.html", "Services/Tracking");
		
		$this->getItems($a_parent_obj->getObjId(), $a_ref_id);
	}

	function getSelectableColumns()
	{
		global $lng, $ilSetting;

		$lng_map = array("user_total" => "users", "first_access_min" => "trac_first_access",
			"last_access_max" => "trac_last_access", "mark" => "trac_mark", "status" => "trac_status",
			'status_changed_max' => 'trac_status_changed',
			"spent_seconds_avg" => "trac_spent_seconds", "percentage_avg" => "trac_percentage",
			"read_count_sum" => "trac_read_count", "read_count_avg" => "trac_read_count",
			"read_count_spent_seconds_avg" => "trac_read_count_spent_seconds"
			);

		
		$all = array("user_total");
		$default = array();

		// show only if extended data was activated in lp settings
		include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
		$tracking = new ilObjUserTracking();
		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_READ_COUNT))
		{
			$all[] = "read_count_sum";
			$all[] = "read_count_avg";
			$default[] = "read_count_sum";
		}
		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS))
		{
			$all[] = "spent_seconds_avg";
			$default[] = "spent_seconds_avg";
		}
		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_READ_COUNT) &&
			$tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS))
		{
			$all[] = "read_count_spent_seconds_avg";
			// $default[] = "read_count_spent_seconds_avg";
		}

		$all[] = "percentage_avg";
		
		// do not show status if learning progress is deactivated					
		if($this->is_root || $this->olp->isActive())
		{		
			$all[] = "status";
			$all[] = 'status_changed_max';
		}
		
		if($this->is_root || ilObject::_lookupType($this->obj_id) != "lm")
		{
			$all[] = "mark";
		}

		$privacy = array("gender", "city", "country", "sel_country");
		foreach($privacy as $field)
		{
			if($ilSetting->get("usr_settings_course_export_".$field))
			{
				$all[] = $field;
			}
		}
		
		$all[] = "language";

		$default[] = "percentage_avg";
		$default[] = "status";
		$default[] = "mark";
	
		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS))
		{
			$all[] = "first_access_min";
			$all[] = "last_access_max";
		}

		$all[] = "create_date_min";
		$all[] = "create_date_max";

		
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
			
			if(in_array($column, array("read_count_avg", "spent_seconds_avg", "percentage_avg")))
			{
				$txt .= " / ".$lng->txt("user");
			}

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
	function initFilter()
	{
		global $lng, $ilSetting;
		
		if($this->is_root)
		{
			return parent::initFilter(true, false);
		}
		
		// show only if extended data was activated in lp settings
		include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
		$tracking = new ilObjUserTracking();

		$item = $this->addFilterItemByMetaType("user_total", ilTable2GUI::FILTER_NUMBER_RANGE, true,
			"&#8721; ".$lng->txt("users"));
		$this->filter["user_total"] = $item->getValue();

		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_READ_COUNT))
		{
			$item = $this->addFilterItemByMetaType("read_count", ilTable2GUI::FILTER_NUMBER_RANGE, true,
				"&#8721; ".$lng->txt("trac_read_count"));
			$this->filter["read_count"] = $item->getValue();
		}

		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS))
		{
			$item = $this->addFilterItemByMetaType("spent_seconds", ilTable2GUI::FILTER_DURATION_RANGE,
				true, "&#216; ".$lng->txt("trac_spent_seconds")." / ".$lng->txt("user"));
			$this->filter["spent_seconds"]["from"] = $item->getCombinationItem("from")->getValueInSeconds();
			$this->filter["spent_seconds"]["to"] = $item->getCombinationItem("to")->getValueInSeconds();
		}

		$item = $this->addFilterItemByMetaType("percentage", ilTable2GUI::FILTER_NUMBER_RANGE, true,
			"&#216; ".$lng->txt("trac_percentage")." / ".$lng->txt("user"));
		$this->filter["percentage"] = $item->getValue();

		// do not show status if learning progress is deactivated				
		if($this->olp->isActive())
		{		
			include_once "Services/Tracking/classes/class.ilLPStatus.php";
			$item = $this->addFilterItemByMetaType("status", ilTable2GUI::FILTER_SELECT, true);
			$item->setOptions(array("" => $lng->txt("trac_all"),
				ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM+1 => $lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED),
				ilLPStatus::LP_STATUS_IN_PROGRESS_NUM+1 => $lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS),
				ilLPStatus::LP_STATUS_COMPLETED_NUM+1 => $lng->txt(ilLPStatus::LP_STATUS_COMPLETED),
				ilLPStatus::LP_STATUS_FAILED_NUM+1 => $lng->txt(ilLPStatus::LP_STATUS_FAILED)));
			$this->filter["status"] = $item->getValue();
			if($this->filter["status"])
			{
				$this->filter["status"]--;
			}

			$item = $this->addFilterItemByMetaType("trac_status_changed", ilTable2GUI::FILTER_DATE_RANGE, true);
			$this->filter["status_changed"] = $item->getDate();
		}

		if(ilObject::_lookupType($this->obj_id) != "lm")
		{
			$item = $this->addFilterItemByMetaType("mark", ilTable2GUI::FILTER_TEXT, true,
				$lng->txt("trac_mark"));
			$this->filter["mark"] = $item->getValue();
		}

		if($ilSetting->get("usr_settings_course_export_gender"))
		{
			$item = $this->addFilterItemByMetaType("gender", ilTable2GUI::FILTER_SELECT, true);
			$item->setOptions(array("" => $lng->txt("trac_all"), "m" => $lng->txt("gender_m"),
				"f" => $lng->txt("gender_f")));
			$this->filter["gender"] = $item->getValue();
		}

		if($ilSetting->get("usr_settings_course_export_city"))
		{
			$item = $this->addFilterItemByMetaType("city", ilTable2GUI::FILTER_TEXT, true);
			$this->filter["city"] = $item->getValue();
		}

		if($ilSetting->get("usr_settings_course_export_country"))
		{
			$item = $this->addFilterItemByMetaType("country", ilTable2GUI::FILTER_TEXT, true);
			$this->filter["country"] = $item->getValue();
		}

		if($ilSetting->get("usr_settings_course_export_sel_country"))
		{
			$item = $this->addFilterItemByMetaType("sel_country", ilTable2GUI::FILTER_SELECT, true);
			$item->setOptions(array("" => $lng->txt("trac_all"))+$this->getSelCountryCodes());
			$this->filter["sel_country"] = $item->getValue();
		}

		$item = $this->addFilterItemByMetaType("language", ilTable2GUI::FILTER_LANGUAGE, true);
		$this->filter["language"] = $item->getValue();

		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS))
		{
			$item = $this->addFilterItemByMetaType("trac_first_access", ilTable2GUI::FILTER_DATETIME_RANGE, true);
			$this->filter["first_access"] = $item->getDate();

			$item = $this->addFilterItemByMetaType("trac_last_access", ilTable2GUI::FILTER_DATETIME_RANGE, true);
			$this->filter["last_access"] = $item->getDate();
		}
		
		$item = $this->addFilterItemByMetaType("registration_filter", ilTable2GUI::FILTER_DATE_RANGE, true);
		$this->filter["registration"] = $item->getDate();
	}

	function getSelCountryCodes()
	{
		global $lng;
		
		include_once("./Services/Utilities/classes/class.ilCountry.php");
		$options = array();
		foreach (ilCountry::getCountryCodes() as $c)
		{
			$options[$c] = $lng->txt("meta_c_".$c);
		}
		asort($options);
		return $options;
	}

	/**
	 * Build summary item rows for given object and filter(s
	 *
	 * @param	int		$a_object_id
	 * @param	int		$a_ref_id
	 */
	function getItems($a_object_id, $a_ref_id)
	{
		global $lng, $rbacsystem;

		include_once("./Services/Tracking/classes/class.ilTrQuery.php");

		$preselected_obj_ids = $filter = NULL;
		if($this->is_root)
		{
			// using search to get all relevant objects
			// #8498/#8499: restrict to objects with at least "read_learning_progress" access
			$preselected_obj_ids = $this->searchObjects($this->getCurrentFilter(true), "read_learning_progress");
		}
		else
		{
			// using summary filters
			$filter = $this->getCurrentFilter();
		}

		$data = ilTrQuery::getObjectsSummaryForObject(
				$a_object_id,
				$a_ref_id,
				ilUtil::stripSlashes($this->getOrderField()),
				ilUtil::stripSlashes($this->getOrderDirection()),
				ilUtil::stripSlashes($this->getOffset()),
				ilUtil::stripSlashes($this->getLimit()),
				$filter,
				$this->getSelectedColumns(),
				$preselected_obj_ids
				);
		
		// build status to image map
		include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
		include_once("./Services/Tracking/classes/class.ilLPStatus.php");			
		$valid_status = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM, 
			ilLPStatus::LP_STATUS_IN_PROGRESS_NUM, 
			ilLPStatus::LP_STATUS_COMPLETED_NUM, 
			ilLPStatus::LP_STATUS_FAILED_NUM);
		$status_map = array();			
		foreach($valid_status as $status)
		{
			$path = ilLearningProgressBaseGUI::_getImagePathForStatus($status);
			$text = ilLearningProgressBaseGUI::_getStatusText($status);
			$status_map[$status] = ilUtil::img($path, $text);
		}
		
		// language map
		$lng->loadLanguageModule("meta");
		$languages = array();
		foreach ($lng->getInstalledLanguages() as $lang_key)
		{
			$languages[$lang_key] = $lng->txt("meta_l_".$lang_key);
		}

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

			$data["set"][$idx]["offline"] = ilLearningProgressBaseGUI::isObjectOffline($result["obj_id"], $result["type"]);
			
			// #13807
			if($result["ref_ids"])
			{
				$valid = false;
				foreach($result["ref_ids"] as $check_ref_id)
				{					
					if($rbacsystem->checkAccess("read_learning_progress", $check_ref_id))
					{
						$valid = true;
						break;
					}
				}
				if(!$valid)
				{					
					foreach(array_keys($data["set"][$idx]) as $col_id)
					{
						if(!in_array($col_id, array("type", "title", "obj_id", "ref_id", "offline")))
						{
							$data["set"][$idx][$col_id] = null;
						}
					}
					$data["set"][$idx]["privacy_conflict"] = true;
					continue;
				}			
			}
			
			// percentages
			$users_no = $result["user_total"];
			$data["set"][$idx]["country"] = $this->getItemsPercentages($result["country"], $users_no);
			$data["set"][$idx]["gender"] = $this->getItemsPercentages($result["gender"], $users_no, array("m"=>$lng->txt("gender_m"), "f"=>$lng->txt("gender_f")));
			$data["set"][$idx]["city"] = $this->getItemsPercentages($result["city"], $users_no);
			$data["set"][$idx]["sel_country"] = $this->getItemsPercentages($result["sel_country"], $users_no, $this->getSelCountryCodes());
			$data["set"][$idx]["mark"] = $this->getItemsPercentages($result["mark"], $users_no);
			$data["set"][$idx]["language"] = $this->getItemsPercentages($result["language"], $users_no, $languages);

			// if we encounter any invalid status codes, e.g. null, map them to not attempted instead
			foreach($result["status"] as $status_code => $status_counter)
			{
				// null is cast to ""
				if($status_code === "" || !in_array($status_code, $valid_status))
				{
					$result["status"][ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM] += $status_counter;
					unset($result["status"][$status_code]);
				}
			}
			$data["set"][$idx]["status"] = $this->getItemsPercentagesStatus($result["status"], $users_no, $status_map);

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

		if($data)
		{
			// if we have only 1 item more than the limit, "others" makes no sense
			if(sizeof($data) == $limit+1)
			{
				$limit++;
			}
			
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
						"absolute" => $count, // ." ".($count > 1 ? $lng->txt("users") : $lng->txt("user")),
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
					"caption" => $otherss_counter."  ".$lng->txt("trac_others"),
					"absolute" => $others_sum, // ." ".($others_sum > 1 ? $lng->txt("users") : $lng->txt("user")),
					"percentage" => $perc
					);
			}
		}

		return $result;
	}
	
	/**
	 * Render status data as needed for summary list (based on grouped values)
	 *
	 * @param	array	$data		rows data
	 * @param	int		$overall	overall number of entries
	 * @param	array	$value_map	labels for values
	 * @return	array
	 */
	protected function getItemsPercentagesStatus(array $data = NULL, $overall, array $value_map = NULL)
	{
		global $lng;

		$result = array();
		foreach($value_map as $id => $caption)
		{
			$count = 0;
			if(isset($data[$id]))
			{
				$count = $data[$id];
			}
			$perc = round($count/$overall*100);
			
			$result[] = array(
				"caption" => $caption,
				"absolute" => $count,
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
			case 'status_changed':
			case "first_access":
			case "create_date":
				$value = ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_DATETIME));
				break;

			case "last_access":
				$value = ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_UNIX));
				break;

			case "spent_seconds":
			case "read_count_spent_seconds":
				if(in_array($type, array("exc")))
				{
					$value = "-";
				}
				else
				{
					include_once("./Services/Utilities/classes/class.ilFormat.php");
					$value = ilFormat::_secondsToString($value, ($value < 3600 ? true : false)); // #14858
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
		global $lng, $ilCtrl;
		
		$this->tpl->setVariable("ICON", ilObject::_getIcon("", "tiny", $a_set["type"]));
		$this->tpl->setVariable("ICON_ALT", $lng->txt($a_set["type"]));
	    $this->tpl->setVariable("TITLE", $a_set["title"]);

		if($a_set["offline"] || $a_set["privacy_conflict"])
		{
			$mess = array();
			if($a_set["offline"])
			{
				$mess[] = $lng->txt("offline");
			}
			if($a_set["privacy_conflict"])
			{
				$mess[] = $lng->txt("status_no_permission");
			}
			$this->tpl->setCurrentBlock("status_bl");
			$this->tpl->setVariable("TEXT_STATUS", implode(", ", $mess));
			$this->tpl->parseCurrentBlock();
		}

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
				case "sel_country":
					$this->renderPercentages($c, $a_set[$c]);
					break;

				case "percentage_avg":
					if((int)$a_set[$c] === 0 || !$this->isPercentageAvailable($a_set["obj_id"]))
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
		
		if($this->is_root)
		{
			$path = $this->buildPath($a_set["ref_ids"], false, true);
			if($path)
			{
				$this->tpl->setCurrentBlock("item_path");
				foreach($path as $ref_id => $path_item)
				{
					$this->tpl->setVariable("PATH_ITEM", $path_item);
					
					if(!$this->anonymized)
					{
						$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', $ref_id);
						$this->tpl->setVariable("URL_DETAILS", $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), 'details'));
						$ilCtrl->setParameterByClass($ilCtrl->getCmdClass(), 'details_id', '');
						$this->tpl->setVariable("TXT_DETAILS", $lng->txt('trac_participants'));
					}
					else
					{
						$this->tpl->setVariable("URL_DETAILS", ilLink::_getLink($ref_id, $a_set["type"]));
						$this->tpl->setVariable("TXT_DETAILS", $lng->txt('view'));
					}
					
					$this->tpl->parseCurrentBlock();
				}
			}

			$this->tpl->setCurrentBlock("item_command");
			$ilCtrl->setParameterByClass(get_class($this),'hide', $a_set["obj_id"]);
			$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass(get_class($this),'hide'));
			$this->tpl->setVariable("TXT_COMMAND", $this->lng->txt('trac_hide'));
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->touchBlock("path_action");
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

	protected function isArrayColumn($a_name)
	{
		if(in_array($a_name, array("country", "gender", "city", "language", "status", "mark")))
		{
			return true;
		}
		return false;
	}
	
	public function numericOrdering($a_field)
	{
		$pos = strrpos($a_field, "_");
		if($pos !== false)
		{
			$function = strtoupper(substr($a_field, $pos+1));
			if(in_array($function, array("MIN", "MAX", "SUM", "AVG", "COUNT", "TOTAL")))
			{
				return true;
			}
		}
		return false;
	}

	protected function fillHeaderExcel($worksheet, &$a_row)
	{
		$worksheet->write($a_row, 0, $this->lng->txt("title"));

		$labels = $this->getSelectableColumns();
		$cnt = 1;
		foreach ($this->getSelectedColumns() as $c)
		{
			$label = $labels[$c]["txt"];
			$label = str_replace("&#216;", $this->lng->txt("trac_average"), $label);
			$label = str_replace("&#8721;", $this->lng->txt("trac_sum"), $label);
			
			if(!$this->isArrayColumn($c))
			{
				$worksheet->write($a_row, $cnt, $label);
				$cnt++;
			}
			else
			{
				if($c != "status")
				{
					$worksheet->write($a_row, $cnt, $label." #1");
					$worksheet->write($a_row, ++$cnt, $label." #1");
					$worksheet->write($a_row, ++$cnt, $label." #1 %");
					$worksheet->write($a_row, ++$cnt, $label." #2");
					$worksheet->write($a_row, ++$cnt, $label." #2");
					$worksheet->write($a_row, ++$cnt, $label." #2 %");
					$worksheet->write($a_row, ++$cnt, $label." #3");
					$worksheet->write($a_row, ++$cnt, $label." #3");
					$worksheet->write($a_row, ++$cnt, $label." #3 %");
					$worksheet->write($a_row, ++$cnt, $label." ".$this->lng->txt("trac_others"));
					$worksheet->write($a_row, ++$cnt, $label." ".$this->lng->txt("trac_others"));
					$worksheet->write($a_row, ++$cnt, $label." ".$this->lng->txt("trac_others")." %");
				}
				else
				{
					// build status to image map
					include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
					include_once("./Services/Tracking/classes/class.ilLPStatus.php");			
					$valid_status = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM, 
						ilLPStatus::LP_STATUS_IN_PROGRESS_NUM, 
						ilLPStatus::LP_STATUS_COMPLETED_NUM, 
						ilLPStatus::LP_STATUS_FAILED_NUM);			
					$cnt--;
					foreach($valid_status as $status)
					{
						$text = ilLearningProgressBaseGUI::_getStatusText($status);
						$worksheet->write($a_row, ++$cnt, $text);
						$worksheet->write($a_row, ++$cnt, $text." %");
					}
				}
				$cnt++;
			}
		}
	}

	protected function fillRowExcel($worksheet, &$a_row, $a_set)
	{
		$worksheet->write($a_row, 0, $a_set["title"]);

		$cnt = 1;
		foreach ($this->getSelectedColumns() as $c)
		{
			if(!$this->isArrayColumn($c))
			{
				$val = $this->parseValue($c, $a_set[$c], "user");
				$worksheet->write($a_row, $cnt, $val);
				$cnt++;
			}
			else
			{
				foreach($a_set[$c] as $idx => $value)
				{
					if($c == "status")
					{
						$worksheet->write($a_row, $cnt, (int)$value["absolute"]);
						$worksheet->write($a_row, ++$cnt, $value["percentage"]);
					}
					else
					{
						$worksheet->write($a_row, $cnt, $value["caption"]);
						$worksheet->write($a_row, ++$cnt, (int)$value["absolute"]);
						$worksheet->write($a_row, ++$cnt, $value["percentage"]);
					}
					$cnt++;
				}
				if(sizeof($a_set[$c]) < 4 && $c != "status")
				{
					for($loop = 4; $loop > sizeof($a_set[$c]); $loop--)
					{
						$worksheet->write($a_row, $cnt, "");
						$worksheet->write($a_row, ++$cnt, "");
						$worksheet->write($a_row, ++$cnt, "");
						$cnt++;
					}
				}
			}
		}
	}

	protected function fillHeaderCSV($a_csv)
	{
		$a_csv->addColumn($this->lng->txt("title"));

		$labels = $this->getSelectableColumns();
		foreach ($this->getSelectedColumns() as $c)
		{
			$label = $labels[$c]["txt"];
			$label = str_replace("&#216;", $this->lng->txt("trac_average"), $label);
			$label = str_replace("&#8721;", $this->lng->txt("trac_sum"), $label);

			if(!$this->isArrayColumn($c))
			{
				$a_csv->addColumn($label);
			}
			else
			{
				if($c != "status")
				{
					$a_csv->addColumn($label." #1");
					$a_csv->addColumn($label." #1");
					$a_csv->addColumn($label." #1 %");
					$a_csv->addColumn($label." #2");
					$a_csv->addColumn($label." #2");
					$a_csv->addColumn($label." #2 %");
					$a_csv->addColumn($label." #3");
					$a_csv->addColumn($label." #3");
					$a_csv->addColumn($label." #3 %");
					$a_csv->addColumn($label." ".$this->lng->txt("trac_others"));
					$a_csv->addColumn($label." ".$this->lng->txt("trac_others"));
					$a_csv->addColumn($label." ".$this->lng->txt("trac_others")." %");
				}
				else
				{
					// build status to image map
					include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
					include_once("./Services/Tracking/classes/class.ilLPStatus.php");			
					$valid_status = array(ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM, 
						ilLPStatus::LP_STATUS_IN_PROGRESS_NUM, 
						ilLPStatus::LP_STATUS_COMPLETED_NUM, 
						ilLPStatus::LP_STATUS_FAILED_NUM);			
					foreach($valid_status as $status)
					{
						$text = ilLearningProgressBaseGUI::_getStatusText($status);
						$a_csv->addColumn($text);
						$a_csv->addColumn($text." %");
					}
				}
			}
		}

		$a_csv->addRow();
	}

	protected function fillRowCSV($a_csv, $a_set)
	{
		$a_csv->addColumn($a_set["title"]);

		foreach ($this->getSelectedColumns() as $c)
		{
			if(!$this->isArrayColumn($c))
			{
				$val = $this->parseValue($c, $a_set[$c], "user");
				$a_csv->addColumn($val);
			}
			else
			{
				foreach($a_set[$c] as $idx => $value)
				{
					if($c != "status")
					{
						$a_csv->addColumn($value["caption"]);
					}
					$a_csv->addColumn((int)$value["absolute"]);
					$a_csv->addColumn($value["percentage"]);
				}
				if(sizeof($a_set[$c]) < 4 && $c != "status")
				{
					for($loop = 4; $loop > sizeof($a_set[$c]); $loop--)
					{
						$a_csv->addColumn("");
						$a_csv->addColumn("");
						$a_csv->addColumn("");
					}
				}
			}
		}

		$a_csv->addRow();
	}
}
?>
