<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Tracking/classes/class.ilLPTableBaseGUI.php");

/**
 * Learning progress table: One object, rows: users, columns: properties
 * Example: A course, rows: members, columns: name, status, mark, ...
 *
 * PD, Personal Learning Progress -> UserObjectsProps
 * PD, Learning Progress of Users -> UserAggObjectsProps
 * Crs, Learnign Progress of Participants -> ObjectUsersProps
 * Details -> UserObjectsProps
 *
 * More:
 * PropUsersObjects (Grading Overview in Course)
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilTrObjectUsersPropsTableGUI: ilFormPropertyDispatchGUI
 * @ingroup ServicesTracking
 */
class ilTrObjectUsersPropsTableGUI extends ilLPTableBaseGUI
{
	protected $user_fields; // array
	protected $filter; // array
	protected $in_course; // int
	
	/**
	* Constructor
	*/
	function __construct($a_parent_obj, $a_parent_cmd, $a_obj_id, $a_ref_id, $a_print_view = false)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $rbacsystem, $tree;
		
		$this->setId("troup");
		$this->obj_id = $a_obj_id;
		$this->ref_id = $a_ref_id;
		$this->type = ilObject::_lookupType($a_obj_id);

		$this->in_course = $tree->checkForParentType($this->ref_id, "crs");
		if($this->in_course)
		{
			$this->in_course = ilObject::_lookupObjId($this->in_course);
		}
	
		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->parseTitle($a_obj_id, "trac_participants");

		if($a_print_view)
		{
			$this->setPrintMode(true);
		}

		$labels = $this->getSelectableColumns();
		foreach ($this->getSelectedColumns() as $c)
		{
			$first = $c;
			
			// list cannot be sorted by udf fields (separate query)
			$sort_id = (substr($c, 0, 4) == "udf_") ? "" : $c;
			
			$this->addColumn($labels[$c]["txt"], $sort_id);
		}
		
		if(!$this->getPrintMode())
		{
			$this->addColumn($this->lng->txt("actions"), "");
		}

		$this->setExternalSorting(true);
		$this->setExternalSegmentation(true);
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormActionByClass(get_class($this)));
		$this->setRowTemplate("tpl.object_users_props_row.html", "Services/Tracking");
		$this->setEnableTitle(true);
		$this->setShowTemplates(true);
		$this->setExportFormats(array(self::EXPORT_CSV, self::EXPORT_EXCEL));

		if($first)
		{
			$this->setDefaultOrderField($first);
			$this->setDefaultOrderDirection("asc");
		}
		
		$this->initFilter();

		$this->getItems();
	}
	
	/**
	 * Get selectable columns
	 *
	 * @param
	 * @return
	 */
	function getSelectableColumns()
	{
		global $lng, $ilSetting;

		if($this->selectable_columns)
		{
			return $this->selectable_columns;
		}

		$anonymized_object = false;
		include_once './Modules/Test/classes/class.ilObjTest.php';
		if(ilObjTest::_lookupAnonymity($this->obj_id))
		{
			$anonymized_object = true;
		}
		
		include_once("./Services/User/classes/class.ilUserProfile.php");
		$up = new ilUserProfile();
		$up->skipGroup("preferences");
		$up->skipGroup("settings");
		$ufs = $up->getStandardFields();

		// default fields
		$cols = array();
		$cols["login"] = array(
			"txt" => $lng->txt("login"),
			"default" => true);

		if(!$anonymized_object)
		{
			$cols["firstname"] = array(
				"txt" => $lng->txt("firstname"),
				"default" => true);
			$cols["lastname"] = array(
				"txt" => $lng->txt("lastname"),
				"default" => true);
		}

		// show only if extended data was activated in lp settings
		include_once 'Services/Tracking/classes/class.ilObjUserTracking.php';
		$tracking = new ilObjUserTracking();
		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_LAST_ACCESS))
		{
			$cols["first_access"] = array(
				"txt" => $lng->txt("trac_first_access"),
				"default" => true);
			$cols["last_access"] = array(
				"txt" => $lng->txt("trac_last_access"),
				"default" => true);
		}
		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_READ_COUNT))
		{
			$cols["read_count"] = array(
				"txt" => $lng->txt("trac_read_count"),
				"default" => true);
		}
		if($tracking->hasExtendedData(ilObjUserTracking::EXTENDED_DATA_SPENT_SECONDS))
		{
			$cols["spent_seconds"] = array(
				"txt" => $lng->txt("trac_spent_seconds"),
				"default" => true);
		}

		if($this->isPercentageAvailable($this->obj_id))
		{
			$cols["percentage"] = array(
				"txt" => $lng->txt("trac_percentage"),
				"default" => true);
		}

		// do not show status if learning progress is deactivated
		$mode = ilLPObjSettings::_lookupMode($this->obj_id);
		if($mode != LP_MODE_DEACTIVATED && $mode != LP_MODE_LP_MODE_UNDEFINED)
		{
			$cols["status"] = array(
				"txt" => $lng->txt("trac_status"),
				"default" => true);

			$cols['status_changed'] = array(
				'txt' => $lng->txt('trac_status_changed'),
				'default' => false);
		}

		if($this->type != "lm")
		{
			$cols["mark"] = array(
				"txt" => $lng->txt("trac_mark"),
				"default" => true);
		}

		$cols["u_comment"] = array(
			"txt" => $lng->txt("trac_comment"),
			"default" => false);

		$cols["create_date"] = array(
			"txt" => $lng->txt("create_date"),
			"default" => false);
		$cols["language"] = array(
			"txt" => $lng->txt("language"),
			"default" => false);

	    // add user data only if object is [part of] course
		if($this->in_course && !$anonymized_object)
		{
			$this->user_fields = array();

			// other user profile fields
			foreach ($ufs as $f => $fd)
			{
				if (!isset($cols[$f]) && $f != "username" && !$fd["lists_hide"]  && ($fd["course_export_fix_value"] || $ilSetting->get("usr_settings_course_export_".$f)))
				{
					$cols[$f] = array(
						"txt" => $lng->txt($f),
						"default" => false);

					$this->user_fields[] = $f;
				}
			}

			// additional defined user data fields
			include_once './Services/User/classes/class.ilUserDefinedFields.php';
			$user_defined_fields = ilUserDefinedFields::_getInstance();
			foreach($user_defined_fields->getVisibleDefinitions() as $field_id => $definition)
			{
				if($definition["field_type"] != UDF_TYPE_WYSIWYG && $definition["course_export"])
				{
					$f = "udf_".$definition["field_id"];
					$cols[$f] = array(
							"txt" => $definition["field_name"],
							"default" => false);

					$this->user_fields[] = $f;
				}
			}
		}

		$this->selectable_columns = $cols;

		return $cols;
	}
	
	/**
	* Get user items
	*/
	function getItems()
	{
		global $lng, $tree;

		$this->determineOffsetAndOrder();
		
		include_once("./Services/Tracking/classes/class.ilTrQuery.php");
		
		$additional_fields = $this->getSelectedColumns();

	    // only if object is [part of] course
		$check_agreement = false;
		if($this->in_course)
		{
			// privacy (if course agreement is activated)
			include_once "Services/PrivacySecurity/classes/class.ilPrivacySettings.php";
			$privacy = ilPrivacySettings::_getInstance();
		    if($privacy->courseConfirmationRequired())
			{
				$check_agreement = $this->in_course;
			}
		}

		$tr_data = ilTrQuery::getUserDataForObject(
			$this->ref_id,
			ilUtil::stripSlashes($this->getOrderField()),
			ilUtil::stripSlashes($this->getOrderDirection()),
			ilUtil::stripSlashes($this->getOffset()),
			ilUtil::stripSlashes($this->getLimit()),
			$this->getCurrentFilter(),
			$additional_fields,
			$check_agreement,
			$this->user_fields
			);
			
		if (count($tr_data["set"]) == 0 && $this->getOffset() > 0)
		{
			$this->resetOffset();
			$tr_data = ilTrQuery::getUserDataForObject(
				$this->ref_id,
				ilUtil::stripSlashes($this->getOrderField()),
				ilUtil::stripSlashes($this->getOrderDirection()),
				ilUtil::stripSlashes($this->getOffset()),
				ilUtil::stripSlashes($this->getLimit()),
				$this->getCurrentFilter(),
				$additional_fields,
				$check_agreement,
				$this->user_fields
				);
		}

		$this->setMaxCount($tr_data["cnt"]);
		$this->setData($tr_data["set"]);
	}
	
	
	/**
	* Init filter
	*/
	function initFilter()
	{
		global $lng;

		foreach($this->getSelectableColumns() as $column => $meta)
		{
			// no udf!
			switch($column)
			{
				case "firstname":
				case "lastname":
				case "mark":
				case "u_comment":
				case "institution":
				case "department":
				case "title":
				case "street":
				case "zipcode":
				case "city":
				case "country":
				case "email":
				case "matriculation":
				case "login":
					$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_TEXT, true, $meta["txt"]);
					$this->filter[$column] = $item->getValue();
					break;

				case "first_access":
				case "last_access":
				case "create_date":				
				case 'status_changed':
					$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_DATETIME_RANGE, true, $meta["txt"]);
					$this->filter[$column] = $item->getDate();
					break;
				
				case "birthday":
					$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_DATE_RANGE, true, $meta["txt"]);
					$this->filter[$column] = $item->getDate();
					break;

				case "read_count":
				case "percentage":
					$item = $this->addFilterItemByMetaType($column, ilTable2GUI::FILTER_NUMBER_RANGE, true, $meta["txt"]);
					$this->filter[$column] = $item->getValue();
					break;

				case "gender":
					$item = $this->addFilterItemByMetaType("gender", ilTable2GUI::FILTER_SELECT, true, $meta["txt"]);
					$item->setOptions(array("" => $lng->txt("trac_all"), "m" => $lng->txt("gender_m"), "f" => $lng->txt("gender_f")));
					$this->filter["gender"] = $item->getValue();
					break;

				case "sel_country":
					$item = $this->addFilterItemByMetaType("sel_country", ilTable2GUI::FILTER_SELECT, true, $meta["txt"]);

					$options = array();
					include_once("./Services/Utilities/classes/class.ilCountry.php");
					foreach (ilCountry::getCountryCodes() as $c)
					{
						$options[$c] = $lng->txt("meta_c_".$c);
					}
					asort($options);
					$item->setOptions(array("" => $lng->txt("trac_all"))+$options);

					$this->filter["sel_country"] = $item->getValue();
					break;

				case "status":
					include_once "Services/Tracking/classes/class.ilLPStatus.php";
					$item = $this->addFilterItemByMetaType("status", ilTable2GUI::FILTER_SELECT, true, $meta["txt"]);
					$item->setOptions(array("" => $lng->txt("trac_all"),
						LP_STATUS_NOT_ATTEMPTED_NUM+1 => $lng->txt(LP_STATUS_NOT_ATTEMPTED),
						LP_STATUS_IN_PROGRESS_NUM+1 => $lng->txt(LP_STATUS_IN_PROGRESS),
						LP_STATUS_COMPLETED_NUM+1 => $lng->txt(LP_STATUS_COMPLETED),
						LP_STATUS_FAILED_NUM+1 => $lng->txt(LP_STATUS_FAILED)));
					$this->filter["status"] = $item->getValue();
					if($this->filter["status"])
					{
						$this->filter["status"]--;
					}
					break;

				case "language":
					$item = $this->addFilterItemByMetaType("language", ilTable2GUI::FILTER_LANGUAGE, true);
					$this->filter["language"] = $item->getValue();
					break;

				case "spent_seconds":
					$item = $this->addFilterItemByMetaType("spent_seconds", ilTable2GUI::FILTER_DURATION_RANGE, true, $meta["txt"]);
					$this->filter["spent_seconds"]["from"] = $item->getCombinationItem("from")->getValueInSeconds();
					$this->filter["spent_seconds"]["to"] = $item->getCombinationItem("to")->getValueInSeconds();
					break;
			}
		}
	}
	
	/**
	* Fill table row
	*/
	protected function fillRow($data)
	{
		global $ilCtrl, $lng;

		foreach ($this->getSelectedColumns() as $c)
		{
			if($c == 'status' && $data[$c] != LP_STATUS_COMPLETED_NUM)
			{
				$timing = $this->showTimingsWarning($this->ref_id, $data["usr_id"]);
				if($timing)
				{
					if($timing !== true)
					{
						$timing = ": ".ilDatePresentation::formatDate(new ilDate($timing, IL_CAL_UNIX));
					}
					else
					{
						$timing = "";
					}
					$this->tpl->setCurrentBlock('warning_img');
					$this->tpl->setVariable('WARNING_IMG', ilUtil::getImagePath('time_warn.png'));
					$this->tpl->setVariable('WARNING_ALT', $this->lng->txt('trac_time_passed').$timing);
					$this->tpl->parseCurrentBlock();
				}
			}

			$this->tpl->setCurrentBlock("user_field");
			$val = $this->parseValue($c, $data[$c], "user");
			$this->tpl->setVariable("VAL_UF", $val);
			$this->tpl->parseCurrentBlock();
		}
		
		$ilCtrl->setParameterByClass("illplistofobjectsgui", "user_id", $data["usr_id"]);
		
		if(!$this->getPrintMode())
		{
			if(in_array($this->type, array("crs", "grp", "cat", "fold")))
			{
				$this->tpl->setCurrentBlock("item_command");
				$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass("illplistofobjectsgui", "userdetails"));
				$this->tpl->setVariable("TXT_COMMAND", $lng->txt('details'));
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("item_command");
			$this->tpl->setVariable("HREF_COMMAND", $ilCtrl->getLinkTargetByClass("illplistofobjectsgui", "edituser"));
			$this->tpl->setVariable("TXT_COMMAND", $lng->txt('edit'));
			$this->tpl->parseCurrentBlock();
		}

		$ilCtrl->setParameterByClass("illplistofobjectsgui", 'user_id', '');
	}

	protected function fillHeaderExcel($worksheet, &$a_row)
	{
		$labels = $this->getSelectableColumns();
		$cnt = 0;
		foreach ($this->getSelectedColumns() as $c)
		{
			$worksheet->write($a_row, $cnt, $labels[$c]["txt"]);
			$cnt++;
		}
	}

	protected function fillRowExcel($worksheet, &$a_row, $a_set)
	{
		$cnt = 0;
		foreach ($this->getSelectedColumns() as $c)
		{
			if($c != 'status')
			{
				$val = $this->parseValue($c, $a_set[$c], "user");
			}
			else
			{
				$val = ilLearningProgressBaseGUI::_getStatusText((int)$a_set[$c]);
			}
			$worksheet->write($a_row, $cnt, $val);
			$cnt++;
		}
	}

	protected function fillHeaderCSV($a_csv)
	{
		$labels = $this->getSelectableColumns();
		foreach ($this->getSelectedColumns() as $c)
		{
			$a_csv->addColumn($labels[$c]["txt"]);
		}

		$a_csv->addRow();
	}

	protected function fillRowCSV($a_csv, $a_set)
	{
		foreach ($this->getSelectedColumns() as $c)
		{
			if($c != 'status')
			{
				$val = $this->parseValue($c, $a_set[$c], "user");
			}
			else
			{
				$val = ilLearningProgressBaseGUI::_getStatusText((int)$a_set[$c]);
			}
			$a_csv->addColumn($val);
		}
		
		$a_csv->addRow();
	}
}
?>