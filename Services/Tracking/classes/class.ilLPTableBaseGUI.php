<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");
include_once  './Services/Search/classes/class.ilSearchSettings.php';

/**
* TableGUI class for learning progress
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesTracking
*/
class ilLPTableBaseGUI extends ilTable2GUI
{
	protected $filter; // array

	public function __construct($a_parent_obj, $a_parent_cmd = "", $a_template_context = "")
	{
		parent::__construct($a_parent_obj, $a_parent_cmd, $a_template_context);

		// country names
		$this->lng->loadLanguageModule("meta");
		
		include_once("./Services/Tracking/classes/class.ilLPObjSettings.php");
	}

	public function executeCommand()
	{
		global $ilCtrl;

		$this->determineSelectedFilters();

		if(!$ilCtrl->getNextClass($this))
		{
			$to_hide = false;

			switch($ilCtrl->getCmd())
			{
				case "applyFilter":
					$this->resetOffset();
					$this->writeFilterToSession();
					break;

				case "resetFilter":
					$this->resetOffset();
					$this->resetFilter();
					break;

				case "hideSelected":
					$to_hide = $_POST["item_id"];
					break;

				case "hide":
					$to_hide = array((int)$_GET["hide"]);
					break;
				
				// page selector
				default:
					$this->determineOffsetAndOrder();
					$this->storeNavParameter();
					break;
			}

			if($to_hide)
			{
				$obj = $this->getFilterItemByPostVar("hide");
				$value = array_unique(array_merge((array)$obj->getValue(), $to_hide));
				$obj->setValue($value);
				$obj->writeToSession();
			}

			if(isset($_POST["tbltplcrt"]))
			{
				$ilCtrl->setParameter($this->parent_obj, "tbltplcrt", $_POST["tbltplcrt"]);
			}
			if(isset($_POST["tbltpldel"]))
			{
				$ilCtrl->setParameter($this->parent_obj, "tbltpldel", $_POST["tbltpldel"]);
			}

			$ilCtrl->redirect($this->parent_obj, $this->parent_cmd);
		}
        else
		{
			// e.g. repository selector
			return parent::executeCommand();
		}
	}

	/**
	 * Search objects that match current filters
	 *
	 * @param	array	$filter
	 * @param	string	$permission
	 * @return	array
	 */
	protected function searchObjects(array $filter, $permission, array $preset_obj_ids = null)
	{
		global $ilObjDataCache;
				
		/* for performance issues: fast search WITHOUT any permission checks
		include_once "Services/Tracking/classes/class.ilTrQuery.php";
		return ilTrQuery::searchObjects($filter["type"], $filter["query"], 
			$filter["area"], $filter["hide"], $preset_obj_ids);
		*/

		include_once './Services/Search/classes/class.ilQueryParser.php';

		$query_parser =& new ilQueryParser($filter["query"]);
		$query_parser->setMinWordLength(0);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			// echo $query_parser->getMessage();
			return false;
		}

		if($filter["type"] == "lres")
		{
			$filter["type"] = array('lm','sahs','htlm','dbk');
		}
		else
		{
			$filter["type"] = array($filter["type"]);
		}

		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search =& new ilLikeObjectSearch($query_parser);
		$object_search->setFilter($filter["type"]);
		if($preset_obj_ids)
		{
			$object_search->setIdFilter($preset_obj_ids);
		}		
		$res =& $object_search->performSearch();
		$res->setRequiredPermission($permission);
		$res->setMaxHits(1000);
		$res->addObserver($this, "searchFilterListener");

		if(!$this->filter["area"])
		{
			$res->filter(ROOT_FOLDER_ID, false);
		}
		else
		{
			$res->filter($this->filter["area"], false);
		}

		$objects = array();
		foreach($res->getResults() as $obj_data)
		{
			$objects[$obj_data['obj_id']][] = $obj_data['ref_id'];
		}

		// Check if search max hits is reached
		$this->limit_reached = $res->isLimitReached();

		return $objects ? $objects : array();
	}

	/**
	 * Listener for SearchResultFilter
	 * Checks wheather the object is hidden and mode is not LP_MODE_DEACTIVATED
	 * @access public
	 */
	public function searchFilterListener($a_ref_id, $a_data)
	{
		if(is_array($this->filter["hide"]) && in_array($a_data["obj_id"], $this->filter["hide"]))
		{
			return false;
		}
		// :TODO: mode does not have to be set in db
		if(ilLPObjSettings::_lookupMode($a_data["obj_id"]) == LP_MODE_DEACTIVATED)
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Init filter
	 *
	 * @param bool $a_split_learning_resources
	 */
	public function initFilter($a_split_learning_resources = false, $a_include_no_status_filter = true)
	{
		global $lng, $ilObjDataCache;
		
		$this->setDisableFilterHiding(true);
		
		// object type selection
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($this->lng->txt("obj_type"), "type");
		$si->setOptions($this->getPossibleTypes($a_split_learning_resources));
		$this->addFilterItem($si);
		$si->readFromSession();
		if(!$si->getValue())
		{
			$si->setValue("crs");
		}
		$this->filter["type"] = $si->getValue();

		// hidden items
		include_once("./Services/Form/classes/class.ilMultiSelectInputGUI.php");
		$msi = new ilMultiSelectInputGUI($lng->txt("trac_filter_hidden"), "hide");
		$this->addFilterItem($msi);
		$msi->readFromSession();
		$this->filter["hide"] = $msi->getValue();
		if($this->filter["hide"])
		{
			// create options from current value
			$types = $this->getCurrentFilter(true);			
			$type = $types["type"];
			$options = array();
			if($type == 'lres')
			{
				$type = array('lm','sahs','htlm','dbk');
			}
			else
			{
				$type = array($type);
			}
			foreach($this->filter["hide"] as $obj_id)
			{
				if(in_array($ilObjDataCache->lookupType($obj_id), $type))
				{		
					$options[$obj_id] = $ilObjDataCache->lookupTitle($obj_id);
				}
			}
			$msi->setOptions($options);
		}

		// title/description
		include_once("./Services/Form/classes/class.ilTextInputGUI.php");
		$ti = new ilTextInputGUI($lng->txt("trac_title_description"), "query");
		$ti->setMaxLength(64);
		$ti->setSize(20);
		$this->addFilterItem($ti);
		$ti->readFromSession();
		$this->filter["query"] = $ti->getValue();
		
		// repository area selection
		include_once("./Services/Form/classes/class.ilRepositorySelectorInputGUI.php");
		$rs = new ilRepositorySelectorInputGUI($lng->txt("trac_filter_area"), "area");
		$rs->setSelectText($lng->txt("trac_select_area"));
		$this->addFilterItem($rs);
		$rs->readFromSession();
		$this->filter["area"] = $rs->getValue();
		
		// hide "not started yet"
		if($a_include_no_status_filter)
		{
			include_once("./Services/Form/classes/class.ilCheckboxInputGUI.php");
			$cb = new ilCheckboxInputGUI($lng->txt("trac_filter_has_status"), "status");
			$this->addFilterItem($cb);
			$cb->readFromSession();
			$this->filter["status"] = $cb->getChecked();
		}
	}

    /**
 	 * Build path with deep-link
	 *
	 * @param	array	$ref_ids
	 * @return	array 
	 */
	protected function buildPath($ref_ids)
	{
		global $tree, $ilCtrl;

		include_once './Services/Link/classes/class.ilLink.php';
		
		if(!count($ref_ids))
		{
			return false;
		}
		foreach($ref_ids as $ref_id)
		{
			$path = "...";
			$counter = 0;
			$path_full = $tree->getPathFull($ref_id);
			foreach($path_full as $data)
			{
				if(++$counter < (count($path_full)-1))
				{
					continue;
				}
				$path .= " &raquo; ";
				if($ref_id != $data['ref_id'])
				{
					$path .= $data['title'];
				}
				else
				{
					$path .= ('<a target="_top" href="'.
							  ilLink::_getLink($data['ref_id'],$data['type']).'">'.
							  $data['title'].'</a>');
				}
			}

			$result[$ref_id] = $path;
		}
		return $result;
	}

	/**
 	 * Get possible subtypes
	 *
	 * @param bool $a_split_learning_resources
	 * @param bool $a_include_digilib
	 * @param bool $a_allow_undefined_lp
	 */
	protected function getPossibleTypes($a_split_learning_resources = false, $a_include_digilib = false, $a_allow_undefined_lp = false)
	{
		global $lng, $ilPluginAdmin;

		$options = array();

		if($a_split_learning_resources)
		{
			$options['lm'] = $lng->txt('objs_lm');
			$options['sahs'] = $lng->txt('objs_sahs');
			$options['htlm'] = $lng->txt('objs_htlm');
			
			if($a_include_digilib)
			{
				$options['dbk'] = $lng->txt('objs_dbk');
			}
		}
		else
		{
			$options['lres'] = $lng->txt('learning_resources');
		}

		$options['crs'] = $lng->txt('objs_crs');
		$options['grp'] = $lng->txt('objs_grp');
		$options['exc'] = $lng->txt('objs_exc');
		$options['tst'] = $lng->txt('objs_tst');		
		
		if($a_allow_undefined_lp)
		{
			$options["file"] = $lng->txt("objs_file");
			$options["webr"] = $lng->txt("objs_webr");
			$options["wiki"] = $lng->txt("objs_wiki");
		}
		
		// repository plugins (currently only active)
		include_once 'Services/Repository/classes/class.ilRepositoryObjectPluginSlot.php';	
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "Repository", "robj");
		foreach ($pl_names as $pl)
		{
			$pl_id = $ilPluginAdmin->getId(IL_COMP_SERVICE, "Repository", "robj", $pl);
			if(ilRepositoryObjectPluginSlot::isTypePluginWithLP($pl_id))
			{
				$options[$pl_id] = ilPlugin::lookupTxt("rep_robj", $pl_id, "objs_".$pl_id);
			}
		}
		
		asort($options);
		return $options;
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

		if(trim($value) == "" && $id != "status")
		{
			if($id == "title" && get_class($this) != "ilTrObjectUsersPropsTableGUI")
			{
				return "--".$lng->txt("none")."--";
			}
			return " ";
		}

		switch($id)
		{
			case "first_access":
			case "create_date":
			case 'status_changed':
				$value = ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_DATETIME));
				break;

			case "last_access":
				$value = ilDatePresentation::formatDate(new ilDateTime($value, IL_CAL_UNIX));
				break;

			case "birthday":
				$value = ilDatePresentation::formatDate(new ilDate($value, IL_CAL_DATE));
				break;

			case "spent_seconds":
				if(in_array($type, array("exc")))
				{
					$value = "-";
				}
				else
				{
					include_once("./Services/Utilities/classes/class.ilFormat.php");
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

			case "gender":
				$value = $lng->txt("gender_".$value);
				break;

			case "status":
				include_once("./Services/Tracking/classes/class.ilLearningProgressBaseGUI.php");
				$path = ilLearningProgressBaseGUI::_getImagePathForStatus($value);
				$text = ilLearningProgressBaseGUI::_getStatusText($value);
				$value = ilUtil::img($path, $text);
				break;

			case "language":
				$lng->loadLanguageModule("meta");
				$value = $lng->txt("meta_l_".$value);
				break;

			case "sel_country":
				$value = $lng->txt("meta_c_".$value);
				break;
		}

		return $value;
	}

	public function getCurrentFilter($as_query = false)
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
				case "login":
				case "firstname":
				case "lastname":
				case "mark":
				case "u_comment":
				case "institution":
				case "department":
				case "title":
				case "street":
				case "zipcode":
				case "email":
				case "matriculation":
				case "sel_country":
				case "query":
				case "type":
				case "area":
					if($value)
					{
						$result[$id] = $value;
					}
					break;

				case "status":
					if($value !== false)
					{
						$result[$id] = $value;
					}
					break;

				case "user_total":
				case "read_count":
				case "percentage":
				case "hide":
				case "spent_seconds":
					if(is_array($value) && implode("", $value))
					{
						$result[$id] = $value;
					}
					break;

				 case "registration":
				 case "create_date":
				 case "first_access":
				 case "last_access":				
				 case 'status_changed':
					 if($value)
					 {
						 if($value["from"])
						 {
							 $result[$id]["from"] = $value["from"]->get(IL_CAL_DATETIME);
						 }
						 if($value["to"])
						 {
							 $result[$id]["to"] = $value["to"]->get(IL_CAL_DATETIME);
						 }
					 }
					 break;
					 
				 case "birthday":
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

	protected function isPercentageAvailable($a_obj_id)
	{
		include_once("./Services/Tracking/classes/class.ilLPObjSettings.php");
		$mode = ilLPObjSettings::_lookupMode($a_obj_id);
		if(in_array($mode, array(LP_MODE_TLT, LP_MODE_VISITS, LP_MODE_OBJECTIVES, LP_MODE_SCORM,
			LP_MODE_TEST_PASSED)))
		{
			return true;
		}
		return false;
	}

	protected function parseTitle($a_obj_id, $action, $a_user_id = false)
	{
		global $lng, $ilObjDataCache, $ilUser;

		$user = "";
		if($a_user_id)
		{
			if($a_user_id != $ilUser->getId())
			{
				$a_user = ilObjectFactory::getInstanceByObjId($a_user_id);
			}
			else
			{
				$a_user = $ilUser;
			}
			$user .= ", ".$a_user->getFullName(); // " [".$a_user->getLogin()."]";
		}

		if($a_obj_id != ROOT_FOLDER_ID)
		{
			$this->setTitle($lng->txt($action).": ".$ilObjDataCache->lookupTitle($a_obj_id).$user);
			$this->setDescription($this->lng->txt('trac_mode').": ".ilLPObjSettings::_mode2Text(ilLPObjSettings::_lookupMode($a_obj_id)));
		}
		else
		{
			$this->setTitle($lng->txt($action));
		}
	}

	/**
	 * Build export meta data
	 *
	 * @return array 
	 */
	protected function getExportMeta()
	{
		global $lng, $ilObjDataCache, $ilUser, $ilClientIniFile;

		/* see spec
			Name of installation
			Name of the course
			Permalink to course
			Owner of course object
			Date of report generation
			Reporting period
			Name of person who generated the report.
		*/

	    ilDatePresentation::setUseRelativeDates(false);
		include_once './Services/Link/classes/class.ilLink.php';
		
		$data = array(
			$lng->txt("trac_name_of_installation") => $ilClientIniFile->readVariable('client', 'name'),
			$lng->txt("trac_object_name") => $ilObjDataCache->lookupTitle($this->obj_id),
			$lng->txt("trac_object_link") => ilLink::_getLink($this->ref_id, ilObject::_lookupType($this->obj_id)),
			$lng->txt("trac_object_owner") => ilObjUser::_lookupFullname(ilObject::_lookupOwner($this->obj_id)),
			$lng->txt("trac_report_date") =>
				ilDatePresentation::formatDate(new ilDateTime(time(), IL_CAL_UNIX), IL_CAL_DATETIME),
			$lng->txt("trac_report_owner") => $ilUser->getFullName()
			);
		return $data;
	}

	protected function fillMetaExcel($worksheet, &$a_row)
	{
		foreach($this->getExportMeta() as $caption => $value)
		{
			$worksheet->write($a_row, 0, $caption);
			$worksheet->write($a_row, 1, $value);
			$a_row++;
		}
		$a_row++;
	}
	
	protected function fillMetaCSV($a_csv)
	{
		foreach($this->getExportMeta() as $caption => $value)
		{
			$a_csv->addColumn(strip_tags($caption));
			$a_csv->addColumn(strip_tags($value));
			$a_csv->addRow();
		}
		$a_csv->addRow();
	}

	protected function showTimingsWarning($a_ref_id, $a_user_id)
	{
		include_once 'Modules/Course/classes/Timings/class.ilTimingCache.php';
		if(ilTimingCache::_showWarning($a_ref_id, $a_user_id))
		{
			$timings = ilTimingCache::_getTimings($a_ref_id);
			if($timings['item']['changeable'] and $timings['user'][$a_usr_id]['end'])
			{
				$end = $timings['user'][$a_usr_id]['end'];
			}
			else if ($timings['item']['suggestion_end'])
			{
				$end = $timings['item']['suggestion_end'];
			}
			else
			{
				$end = true;
			}
			return $end;
		}
	}
	
	protected function formatSeconds($seconds, $a_shorten_zero = false)
	{
		$seconds = $seconds ? $seconds : 0;
		if($a_shorten_zero && !$seconds)
		{
			return "-";
		}
		
		$hours = floor($seconds / 3600);		
		$rest = $seconds % 3600;
		
		$minutes = floor($rest / 60);
		$rest = $rest % 60;
		
		if($rest)
		{
			$minutes++;
		}
		
		return sprintf("%dh%02dm",$hours,$minutes);
	}
	
	protected function anonymizeValue($a_value, $a_force_number = false)
	{
		// currently inactive
		return $a_value;
		
		if(is_numeric($a_value))
		{
			$threshold = 3;		
			$a_value = (int)$a_value;
			if($a_value <= $threshold)
			{
				if(!$a_force_number)
				{
					return "0-".$threshold;
				}
				else
				{
					return $threshold;
				}
			}
		}
		return $a_value;
	}
	
	protected function buildValueScale($a_max_value, $a_anonymize = false, $a_format_seconds = false)
	{
		$step = 0;
		if($a_max_value)
		{
			$step = $a_max_value / 10;
			$base = ceil(log($step, 10));		
			$fac = ceil($step / pow(10, ($base - 1)));
			$step = pow(10, $base - 1) * $fac;
		}
		if ($step <= 1)
		{
			$step = 1;
		}
		$ticks = range(0, $a_max_value+$step, $step);
		
		$value_ticks = array(0 => 0);
		foreach($ticks as $tick)
		{
			$value = $tvalue = $tick;
			if($a_anonymize)
			{
				$value = $this->anonymizeValue($value, true);
				$tvalue = $this->anonymizeValue($tvalue);
			}
			if($a_format_seconds)
			{
				$tvalue = $this->formatSeconds($value);
			}
			$value_ticks[$value] = $tvalue;				
		}	
	
		return $value_ticks;
	}
	
	protected function getMonthsFilter($a_short = false)
	{
		global $lng;
		
		$options = array();
		for($loop = 0; $loop < 10; $loop++)
		{
			$year = date("Y")-$loop;
			$options[$year] = $year;
			for($loop2 = 12; $loop2 > 0; $loop2--)
			{
				$month = str_pad($loop2, 2, "0", STR_PAD_LEFT);
				if($year.$month <= date("Ym"))
				{
					if(!$a_short)
					{
						$caption = $year." / ".$lng->txt("month_".$month."_long");
					}
					else
					{
						$caption = $year."/".$month;
					}
					$options[$year."-".$month] = $caption;
				}
			}
		}
		return $options;
	}
	
	protected function getMonthsYear($a_year = null, $a_short = false)
	{
		global $lng;
		
		if(!$a_year)
		{
			$a_year = date("Y");
		}
		
		$all = array();
		for($loop = 1; $loop<13; $loop++)
		{
			$month = str_pad($loop, 2, "0", STR_PAD_LEFT);
			if($a_year."-".$month <= date("Y-m"))
			{
				if(!$a_short)
				{
					$caption = $lng->txt("month_".$month."_long");
				}
				else
				{
					$caption = $lng->txt("month_".$month."_short");
				}			
				$all[$a_year."-".$month] = $caption;
			}
		}
		return $all;
	}
}

?>