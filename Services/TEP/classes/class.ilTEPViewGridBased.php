<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/TEP/classes/class.ilTEPView.php";

/**
 * TEP grid-based views base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
abstract class ilTEPViewGridBased extends ilTEPView
{	
	protected $all_tutors; // [array]
	protected $tutors; // [array]
	protected $filter; // [array]
		
	//
	// properties
	//
		
	/**
	 * Set filter
	 * 
	 * @param array $a_filter
	 */
	public function setFilter(array $a_filter = null)
	{
		$this->filter = $a_filter;
	}
	
	/**
	 * Get filter
	 * 
	 * @return array 
	 */	
	public function getFilter()
	{
		return $this->filter;
	}
	
			
	// 
	// request
	// 
	
	/**
	 * Is multi tutor view?
	 * 
	 * @return bool
	 */
	abstract protected function isMultiTutor();
	
	/**
	 * Has no tutor column?
	 * 
	 * @return bool
	 */
	abstract protected function hasNoTutorColumn();	
	
	protected function importRequest()
	{	
		parent::importRequest();
	
		// filter - incl. org unit[s] 
		
		if(sizeof($_POST["tepflt"]))
		{
			// keep "notutor"-filter even if not displayed
			if(!$this->hasNoTutorColumn())
			{
				$_POST["tepflt"]["notut"] = (bool)$_SESSION["tepflt"]["notut"];
			}			
			
			$_POST["tepflt"]["orgu"] = array(
				"ids" => is_array($_POST["tepflt_orgu"]) ? array_unique($_POST["tepflt_orgu"]) : array()
				,"rcrsv" => (bool)$_POST["tepflt_orgu_rcrsv"]
			);
			$_SESSION["tepflt"] = $_POST["tepflt"];			
		}
	
		// reload
		if(sizeof($_SESSION["tepflt"]))
		{
			$filter = array();
			$filter["etitle"] = trim($_SESSION["tepflt"]["etitle"]);
			$filter["eloc"] = trim($_SESSION["tepflt"]["eloc"]);
			$filter["etype"] = trim($_SESSION["tepflt"]["etype"]);		
			$filter["tutor"] = (int)$_SESSION["tepflt"]["tutor"];
			$filter["orgu"] = (array)$_SESSION["tepflt"]["orgu"];
			$filter["notut"] = (bool)$_SESSION["tepflt"]["notut"];
		}
		// default
		else
		{
			$filter = array(
				"orgu" => array("ids"=>array(), "rcrsv"=>true)
				,"notut" => true
			);
		}	
		
		$this->setFilter($filter);
	}	
	
	protected function initTutors()
	{		
		global $ilUser, $lng;
				
		// no other users
		if(!$this->getPermissions()->mayViewOthers())
		{
			return array($ilUser->getId());
		}
		else
		{
			require_once "Services/TEP/classes/class.ilTEP.php";
			
			$filter = $this->getFilter();
			
			$orgu_rcrsv = (bool)$filter["orgu"]["rcrsv"];
			$orgu_ids = (array)$filter["orgu"]["ids"];			
			if(!sizeof($orgu_ids))
			{
				$this->all_tutors = ilTEP::getViewableTutorNames($this->getPermissions(), null, $orgu_rcrsv);
			}
			else
			{
				$this->all_tutors = ilTEP::getViewableTutorNames($this->getPermissions(), $orgu_ids, $orgu_rcrsv);
			}						
			$tutors = array_keys($this->all_tutors);
			if(!$this->all_tutors)
			{
				ilUtil::sendFailure($lng->txt("tep_filter_tutor_empty"));
			}
			
			// single-tutor view 
			if(!$this->isMultiTutor())
			{			
				// restrict to selected user (if valid)
				if($filter["tutor"] && in_array($filter["tutor"], $this->all_tutors))
				{
					$tutors = array($filter["tutor"]);
				}
				// select 1st in alphabetical list
				else
				{					
					$tutors = array(array_shift($tutors));								
				}
			}
			
			return $tutors;
		}
	}
	
	public function getTutors()
	{		
		if($this->tutors === null)
		{
			$this->tutors = $this->initTutors();
		}
		
		return $this->tutors;
	}		
	
	/**
	 * Get currently selected navigation option
	 * 
	 * @return string
	 */
	abstract public function getCurrentNavigationOption();
		
	
	
	//
	// data
	// 
	
	/**
	 * Load/find entries
	 */
	public function loadData()
	{
		$period = $this->getPeriod();
		$from = $period[0];
		$to = $period[1];
		
		include_once "Services/TEP/classes/class.ilTEPEntries.php";
		$entries = new ilTEPEntries($from, $to, $this->getTutors(), $this->getFilter());
		
		$this->entries = $entries->getEntriesForPresentation();	
		
		return $this->hasData();
	}
	
	
	//
	// filter
	// 
	
		
	/**
	 * Render filter
	 * 
	 * @return string
	 */
	protected function renderFilter()
	{
		global $lng, $ilCtrl;

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
			
		$filter = $this->getFilter();

		$tpl = new ilTemplate("tpl.view_filter.html", true, true, "Services/TEP");
		
		$seed = new ilSelectInputGUI("", "seed");
		$seed->setOptions($this->getNavigationOptions());
		$seed->setValue($this->getCurrentNavigationOption());
		$tpl->setVariable("PERIOD_CAPTION", $lng->txt("tep_entry_period"));
		$tpl->setVariable("PERIOD_FIELD", $seed->getToolbarHTML());		

		$entry = new ilTextInputGUI("", "tepflt[etitle]");
		$entry->setSize(25);
		$entry->setValue($filter["etitle"]);
		$tpl->setVariable("ENTRY_TITLE_CAPTION", $lng->txt("title"));
		$tpl->setVariable("ENTRY_TITLE_FIELD", $entry->getToolbarHTML());

		$venue = new ilTextInputGUI("", "tepflt[eloc]");
		$venue->setSize(25);
		$venue->setValue($filter["eloc"]);
		$tpl->setVariable("VENUE_CAPTION", $lng->txt("tep_entry_location"));
		$tpl->setVariable("VENUE_FIELD", $venue->getToolbarHTML());

		require_once "Services/TEP/classes/class.ilCalEntryType.php";
		$options = array();
		foreach(ilCalEntryType::getListData() as $item)
		{
			$options[$item["id"]] = $item["title"];
		}		
		$etype = new ilSelectInputGUI("", "tepflt[etype]");
		$opts = array(""=>$lng->txt("tep_search_all"))+$options;
		$etype->setOptions($opts);
		$etype->setValue($filter["etype"]);
		$tpl->setVariable("ENTRY_TYPE_CAPTION", $lng->txt("tep_entry_type"));
		$tpl->setVariable("ENTRY_TYPE_FIELD", $etype->getToolbarHTML());
		
		if($this->hasNoTutorColumn())
		{
			$notut = new ilCheckboxInputGUI("", "tepflt[notut]");
			$notut->setChecked($filter["notut"]);
			$tpl->setVariable("NO_TUTOR_CAPTION", $lng->txt("tep_filter_no_tutor"));
			$tpl->setVariable("NO_TUTOR_FIELD", $notut->getToolbarHTML());
		}
		
		if($this->getPermissions()->mayViewOthers())
		{			
			// org unit(s)
			$orgs = ilTEP::getViewableOrgUnits($this->getPermissions());
			if($orgs)
			{
				require_once "Services/TEP/classes/class.ilTEPOrgUnitSelectionInputGUI.php";
				$ogrp = new ilTEPOrgUnitSelectionInputGUI($orgs, "tepflt_orgu", true);
				$ogrp->setValue($filter["orgu"]["ids"]);
				$ogrp->setRecursive($filter["orgu"]["rcrsv"]);
						
				$tpl->setVariable("ORGU_CAPTION", $lng->txt("objs_orgu"));
				$tpl->setVariable("ORGU_FIELD", $ogrp->getTableFilterHTML());	
			}		
			
			// tutor 
			if(!$this->isMultiTutor())
			{												
				$tutor = new ilSelectInputGUI("", "tepflt[tutor]");
				$tutor->setOptions($this->all_tutors);
				$tutor->setValue($filter["tutor"]);
				$tutor->addCustomAttribute(' onchange="this.form.submit();"');
				$tpl->setVariable("TUTOR_CAPTION", $lng->txt("tep_filter_tutor"));
				$tpl->setVariable("TUTOR_FIELD", $tutor->getToolbarHTML());		
			}
		}

		$tpl->setVariable("SUBMIT_CAPTION", $lng->txt("tep_filter_submit"));
		$tpl->setVariable("SUBMIT_CMD", "cmd[view]");

		$tpl->setVariable("FORM_CMD",
			$ilCtrl->getFormAction($this->getParentGUI(), "view"));

		return $tpl->get();
	}	
	
	
	//
	// presentation (navigation, legend)
	// 	
	
	/**
	 * Render navigation
	 * 
	 * @return string
	 */
	protected function renderNavigation()
	{
		global $ilCtrl;
		
		$options = $this->getNavigationOptions();		
		$curr = $this->getCurrentNavigationOption();	
		
		
		// parse
		
		$prev = $prev_prev = $prev_prev_prev = $next = $next_next
			= $next_next_next = $last = $last_last = $last_last_last = null;
					
		foreach(array_keys($options) as $opt)
		{
			if($curr == $opt)
			{
				if($last) 
				{
					$prev = array($last, $options[$last]);
				}
				if($last_last) 
				{
					$prev_prev = array($last_last, $options[$last_last]);
				}
				if($last_last_last) 
				{
					$prev_prev_prev = array($last_last_last, $options[$last_last_last]);
				}

			}
			if($curr == $last)
			{
				$next = array($opt, $options[$opt]);
			}

			if($curr == $last_last) 
			{
				$next_next = array($opt, $options[$opt]);
			}

			if($curr == $last_last_last) 
			{
				$next_next_next = array($opt, $options[$opt]);
			}

			$last_last_last = $last_last;
			$last_last = $last;
			$last = $opt;
		}		
		
		$curr = array($curr, $options[$curr]);
		
		
		// render

		$tpl = new ilTemplate("tpl.view_nav.html", true, true, "Services/TEP");
		
		$items = array(
			"prev" => $prev
			,"prev_prev" => $prev_prev
			,"prev_prev_prev" => $prev_prev_prev
			,"next" => $next
			,"next_next" => $next_next
			,"next_next_next" => $next_next_next
			,"current" => $curr
		);
		foreach($items as $tgt => $item)
		{
			if($item)
			{
				$var = strtoupper($tgt);
				$ilCtrl->setParameter($this->getParentGUI(), "seed", $item[0]);
				$url = $ilCtrl->getLinkTarget($this->getParentGUI(), "view");
				$tpl->setVariable($var."_CAPTION", $item[1]);
				$tpl->setVariable($var."_URL", $url);				
			}
		}
		
		$ilCtrl->setParameter($this->getParentGUI(), "seed", trim($_REQUEST["seed"]));		
		
		return $tpl->get();
	}

	/**
	 * Render legend
	 */
	protected function renderLegend()
	{
		global $lng;
		
		require_once "Services/TEP/classes/class.ilCalEntryType.php";
		require_once "Services/TEP/classes/class.ilTEPEntry.php";		
		$used = ilTEPEntry::getAllTypesInUse();		
		if($used)
		{
			$tep_tpl = new ilTemplate("tpl.view_legend.html", true, true, "Services/TEP");
			
			$counter = 0;
			foreach(ilCalEntryType::getListData($used) as $item)
			{				
				$counter++;

				$tep_tpl->setCurrentBlock("type_bl");
				$tep_tpl->setVariable("TYPE_COLOR", $item["bg_color"]);
				$tep_tpl->setVariable("TYPE_CAPTION", $item["title"]);
				$tep_tpl->parseCurrentBlock();				

				if($counter && !($counter%5))
				{
					$tep_tpl->setCurrentBlock("row_bl");
					$tep_tpl->parseCurrentBlock();
				}				
			}
			
			if($counter && !($counter%5))
			{
				$tep_tpl->setCurrentBlock("row_bl");
				$tep_tpl->parseCurrentBlock();
			}
			
			$tep_tpl->setVariable("TITLE", $lng->txt("tep_legend"));
			
			return $tep_tpl->get();
		}
	}	
	
	/**
	 * Get all navigation options
	 * 
	 * @return array
	 */
	abstract public function getNavigationOptions();
	
	
	
	// 
	// presentation (grid content)
	// 

	/**
	 * Handle events layout per day ("virtual columns")
	 * 
	 * @param array $a_events
	 * @param string $a_from (YYYY-MM-DD)
	 * @param string $a_to  (YYYY-MM-DD)
	 * @return array
	 */
	protected function layoutEvents(array $a_events, $a_from, $a_to)
	{
		$res = array();

		$columns = array();
		$last_event_ending = null;
		foreach($a_events as $event)
		{
			if(!($event["start"] >= $a_from && $event["end"] <= $a_to) &&
				!($event["start"] <= $a_from && $event["end"] >= $a_from) &&
				!($event["start"] <= $a_to && $event["end"] >= $a_to))
			{
				continue;
			}

			if($last_event_ending && $event["start"] > $last_event_ending)
			{
				$this->packEvents($res, $columns);
				$columns = array();
				$last_event_ending = null;
			}

			$placed = false;
			foreach($columns as $idx => $col)
			{
				$last = array_pop($col);
				if($last["end"] < $event["start"])
				{
					$columns[$idx][] = $event;
					$placed = true;
					break;
				}
			}

			if(!$placed)
			{
				$columns[] = array($event);
			}

			if($event["end"] > $last_event_ending)
			{
				$last_event_ending = $event["end"];
			}
		}

		$this->packEvents($res, $columns);

		return $res;
	}

	/**
	 * Update event entries with "virtual column" information
	 * 
	 * @param array &$result
	 * @param array $a_columns
	 */
	protected function packEvents(array &$result, array $a_columns)
	{
		$n = sizeof($a_columns);
		foreach ($a_columns as $idx => $col)
		{
			foreach ($col as $event)
			{
				$event["column"] = $idx;
				$event["counter"] = $n;
				$result[] = $event;
			}
		}
	}
	
	/**
	 * Find entries for given day and user
	 * 
	 * @param int $a_user_id
	 * @param string $a_date (YYYY-MM-DD)	
	 * @return array
	 */
	protected function getDayEntries($a_user_id, $a_date)
	{
		$res = array();			
		
		if(isset($this->entries[$a_user_id]))
		{
			foreach($this->entries[$a_user_id] as $entry)
			{
				if($entry["start"] <= $a_date && $entry["end"] >= $a_date)
				{
					$res[] = $entry;									
				}
			}
		}
		
		return $res;
	}
	
	/**
	 * Get HTML style tag for entry type
	 * 
	 * @param string $a_type
	 * @return string
	 */
	protected function getStyleForEntryType($a_type)
	{
		static $type_map;
		
		if(!is_array($type_map))
		{						
			require_once "Services/TEP/classes/class.ilCalEntryType.php";
			$type_map = array();
			foreach(ilCalEntryType::getListData() as $item)
			{
				$type_map[$item["id"]] = array(
					"title" => $item["title"]
					,"bg_color" => $item["bg_color"]
					,"font_color" => $item["font_color"] 
						? $item["font_color"] 
						: ilCalEntryType::getFontColorForBg($item["bg_color"])					
				);
			}		
		}
		
		if(array_key_exists($a_type, $type_map))
		{
			$style = $type_map[$a_type];
			return "background-color: #".$style["bg_color"].";".
				"color: #".$style["font_color"];
		}
	}
	
	/**
	 * Render entry details
	 * 
	 * @param ilTemplate $a_tpl
	 * @param array $a_entry
	 * @param string $a_style
	 * @return string
	 */
	protected function renderEntry(ilTemplate $a_tpl, $a_entry, $a_style)
	{		
		if($a_entry["url"])
		{
			$a_tpl->setCurrentBlock("title_link_bl");
			$a_tpl->setVariable("URL", $a_entry["url"]);
			$a_tpl->setVariable("URL_TITLE", $a_entry["title"]);
			$a_tpl->setVariable("URL_STYLE", $a_style);
			$a_tpl->parseCurrentBlock();
		}
		else
		{
			$a_tpl->setCurrentBlock("title_bl");
			$a_tpl->setVariable("TITLE", $a_entry["title"]);
			$a_tpl->setVariable("TITLE_STYLE", $a_style);
			$a_tpl->parseCurrentBlock();
		}
		
		if ($a_entry["subtitle"])
		{
			$a_tpl->setVariable("SUBTITLE", $a_entry["subtitle"]);
		}
		
		if($a_entry["description"])
		{
			$a_tpl->setVariable("DESCRIPTION", $a_entry["description"]);
		}
		
		/*
		if($a_entry["activation_online"] === "1"
		|| $a_entry["activation_online"] === "0")
		{
			if ($a_entry["activation_online"] == 1)
				$content .= "<br />".$lng->txt("online");
			else
				$content .= "<br />".$lng->txt("offline");
		}
		*/

		if($a_entry["location"])
		{
			$a_tpl->setVariable("LOCATION", $a_entry["location"]);			
		}

		if(!$a_entry["fullday"])
		{
			$start = new ilDateTime($a_entry["starta"], IL_CAL_DATETIME, "UTC");
			$end = new ilDateTime($a_entry["enda"], IL_CAL_DATETIME, "UTC");

			$a_tpl->setVariable("PERIOD", substr($start->get(IL_CAL_DATETIME), 11, 5).
				"-".substr($end->get(IL_CAL_DATETIME), 11, 5));
		}
	}
	
	/**
	 * Render day content
	 * 
	 * @param int $a_year
	 * @param int $a_month
	 * @param int $a_day
	 * @param int $a_user_id
	 * @param int $a_row_height
	 * @param int $a_col_width
	 * @return string
	 */
	protected function renderDayContent($a_year, $a_month, $a_day, $a_user_id, $a_row_height, $a_col_width)
	{	
		$res = array();
		
		$date = date("Y-m-d", mktime(12, 0, 1, $a_month, $a_day, $a_year));
		$last_day = date("d", mktime(12, 0, 1, $a_month+1, 0, $a_year));
		
		foreach($this->getDayEntries($a_user_id, $date) as $entry)
		{
			if($entry["start"] == $date || $a_day == 1)
			{				
				// restrict to current month
				$start = date_parse($entry["start"]);
				$end = date_parse($entry["end"]);
				if($start["month"] != $a_month)
				{
					$start = mktime(0, 0, 1, $a_month, 1, $a_year);
				}
				else
				{
					$start = mktime(0, 0, 1, $start["month"], $start["day"], $start["year"]);
				}
				if($end["month"] != $a_month)
				{
					$end = mktime(0, 0, 1, $a_month, $last_day, $a_year);
				}
				else
				{
					$end = mktime(0, 0, 1, $end["month"], $end["day"], $end["year"]);
				}
				
				// html dimensions
				$height = (((($end-$start)/(60*60*24))+1)*$a_row_height)-5 + ($end-$start)/(60*60*24) + 1;
				if($entry["counter"] > 1)
				{
					$width = floor($a_col_width/$entry["counter"]);
					$width -= 5; // padding, border
				}
				else
				{
					$width = $a_col_width-4;
				}
				$left = floor($a_col_width/$entry["counter"]) * $entry["column"];

				
				// render
				
				$id = "timesheet-events_".$a_user_id."_".$a_month."_".$entry["column"]."_".$entry["cal_id"];
				
				$style = $this->getStyleForEntryType($entry["entry_type"]);		
				
				$etpl = new ilTemplate("tpl.view_entry.html", true, true, "Services/TEP");
								
				$this->renderEntry($etpl, $entry, $style);
				
				$etpl->setVariable("ID", $id);
				$etpl->setVariable("HEIGHT", $height);
				$etpl->setVariable("WIDTH", $width);
				$etpl->setVariable("LEFT", $left);
				$etpl->setVariable("STYLE", $style);
								
				$res[] = $etpl->get();
			}						
		}

		return implode("\n", $res);
	}

	/**
	 * Render day actions
	 * 
	 * @param int $a_user_id
	 * @param int $a_id
	 * @return string
	 */
	protected function renderDayActions($a_user_id, $a_id)
	{
		global $ilUser, $ilCtrl, $lng;
				
		$may_create_entry = (($this->getPermissions()->isTutor() && $a_user_id == $ilUser->getId()) ||
			$this->getPermissions()->mayEditOthers());
	
		if($may_create_entry)
		{
			$url_event = $ilCtrl->getLinkTargetByClass("ilTEPEntryGUI", "createEntry");
			
			/* ilAdvancedSelectionListGUI
			include_once "Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php";
			$list = new ilAdvancedSelectionListGUI();
			$list->setId("ceal_".$a_id);
			
			$list->addItem($lng->txt("tep_add_new_entry"), "", $url_event);						
			
			return $list->getHTML();			 
			*/
			
			return $url_event;
		}
	}
		
	/**
	 * Render day for user
	 * 
	 * @param ilTemplate $a_tpl
	 * @param int $a_user_id
	 * @param int $a_year
	 * @param int $a_month
	 * @param int $a_day
	 * @param bool $a_is_holiday
	 * @param bool $a_force_empty
	 */
	protected function renderDayForUser($a_tpl, $a_user_id, $a_year, $a_month, $a_day, $a_is_holiday, $a_force_empty = false)
	{
		global $ilCtrl, $lng;
		
		$date_id = date("Y-m-d", mktime(12, 0, 1, $a_month, $a_day, $a_year));
		$unique_id = $a_user_id."_".$date_id;
		
		if($a_user_id)
		{						
			$ilCtrl->setParameterByClass("ilTEPEntryGUI", "euid", $a_user_id);
			$ilCtrl->setParameterByClass("ilTEPEntryGUI", "edt", $date_id);
					
			$actions = $this->renderDayActions($a_user_id, $unique_id);
			if($actions)
			{															
				$a_tpl->setCurrentBlock("col_actions_bl");
			
				/* ilAdvancedSelectionListGUI
				$a_tpl->setVariable("ACTION_ID", $unique_id);
				$a_tpl->setVariable("ACTION_LIST", $actions);
				*/
				
				$a_tpl->setVariable("ADD_URL", $actions);				
				$a_tpl->setVariable("ADD_ALT", $lng->txt("tep_add_new_entry"));				
				$a_tpl->setVariable("ADD_ICON", ilUtil::getImagePath("date_add.png"));		
				
				$a_tpl->parseCurrentBlock();
			}	
		}
					
		$a_tpl->setCurrentBlock("col_bl");
		$a_tpl->setVariable("WRAPPER_WIDTH", static::COL_WIDTH-20);				
		$a_tpl->setVariable("COL", (bool)$a_force_empty 
			? "&nbsp;"
			: $this->renderDayContent($a_year, $a_month, $a_day, $a_user_id, static::DAY_HEIGHT, static::COL_WIDTH-20));
		$a_tpl->setVariable("HOLIDAY_CLASS", $a_is_holiday ? "holiday" : "");
		$a_tpl->parseCurrentBlock();		
	}
	
	/**
	 * Get number of columns
	 * 
	 * @return int
	 */
	abstract protected function getNumberOfColumns();
	
	/**
	 * Render column headers
	 * 
	 * @param ilTemplate $a_tpl
	 */
	abstract protected function renderColumnHeaders(ilTemplate $a_tpl);	
			
	/**
	 * Render view content
	 * 
	 * @param ilTemplate $a_tpl
	 */
	abstract protected function renderContent(ilTemplate $a_tpl);	
		
	/**
	 * Render grid
	 * 
	 * @return string
	 */
	protected function renderGrid()
	{
		global $ilCtrl;
	
		$this->prepareDataForPresentation();						
		
		$tmpl = strtolower(substr(get_class($this), 9));
		$tpl = new ilTemplate("tpl.view_".$tmpl.".html", true, true, "Services/TEP");
		
		$width = ($this->getNumberOfColumns()*(static::COL_WIDTH+2))+100+150;
		$tpl->setVariable("SHEET_WIDTH", $width);
		
		$this->renderColumnHeaders($tpl);
		
		$this->renderContent($tpl);
				
		$ilCtrl->setParameterByClass("ilTEPEntryGUI", "edt", "");
		$ilCtrl->setParameterByClass("ilTEPEntryGUI", "euid", "");

		$tpl->setVariable("ACTION_OFFSET", static::COL_WIDTH-18);

		return $tpl->get();
	}
		
	public function render()
	{				
		global $tpl;
				
		$tpl->addCss(ilUtil::getStyleSheetLocation("filesystem", "tep.css", "Services/TEP"));
		$tpl->addJavaScript("Services/TEP/js/tep.js");
				
		$tep_tpl = new ilTemplate("tpl.view_grid.html", true, true, "Services/TEP");		
		$tep_tpl->setVariable("NAV", $this->renderNavigation());
		$tep_tpl->setVariable("FILTER", $this->renderFilter());								
		$tep_tpl->setVariable("VIEW", $this->renderGrid());		
		$tep_tpl->setVariable("LEGEND", $this->renderLegend());
		
		return $tep_tpl->get();
	}
}