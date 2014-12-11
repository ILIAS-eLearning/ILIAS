<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * TEP view base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesTEP
 */
abstract class ilTEPView
{
	protected $parent_gui; // [ilTEPGUI]
	protected $permissions; // [ilTEPPermissions]
	protected $seed; // [ilDate] 
	protected $entries; // [array] 

	
	const TYPE_MONTH = 1;
	const TYPE_HALFYEAR = 2;
	const TYPE_LIST = 3;
	
	/**
	 * Constructor
	 * 
	 * @param ilTEPGUI $a_parent_gui
	 * @param ilTEPPermissions $a_permissions
	 * @return self
	 */
	protected function __construct(ilTEPGUI $a_parent_gui, ilTEPPermissions $a_permissions)
	{
		$this->setParentGUI($a_parent_gui);
		$this->setPermissions($a_permissions);
		$this->importRequest();
	}
	
	/**
	 * Factory
	 * 	 
	 * @throws ilException
	 * @param int $a_type
	 * @param ilTEPGUI $a_parent_gui
	 * @param ilTEPPermissions $a_permissions
	 * @return ilTEPView
	 */
	public static function getInstance($a_type, ilTEPGUI $a_parent_gui, ilTEPPermissions $a_permissions)
	{
		if(self::isValidType($a_type))
		{
			switch($a_type)
			{
				case self::TYPE_MONTH:
					require_once "Services/TEP/classes/class.ilTEPViewMonth.php";
					return new ilTEPViewMonth($a_parent_gui, $a_permissions);
					
				case self::TYPE_HALFYEAR:
					require_once "Services/TEP/classes/class.ilTEPViewHalfYear.php";
					return new ilTEPViewHalfYear($a_parent_gui, $a_permissions);
						
				case self::TYPE_LIST:
					require_once "Services/TEP/classes/class.ilTEPViewList.php";
					return new ilTEPViewList($a_parent_gui, $a_permissions);				
			}
		}
		
		throw new ilException("ilTEPView - invalid type");
	}
	
	/**
	 * Is given type valid?
	 * 
	 * @param int $a_type
	 * @return bool
	 */
	protected static function isValidType($a_type)
	{
		return in_array($a_type, array(self::TYPE_MONTH, self::TYPE_HALFYEAR, self::TYPE_LIST));
	}
	
	
	// 
	// properties
	//
	
	/**
	 * Set parent gui
	 * 
	 * @param ilTEPGUI $a_gui
	 */
	protected function setParentGUI(ilTEPGUI $a_gui)
	{
		$this->parent_gui = $a_gui;
	}
	
	/**
	 * Get parent gui
	 * 
	 * @return ilTEPGUI 
	 */	
	protected function getParentGUI()
	{
		return $this->parent_gui;
	}
	
	/**
	 * Set permissions
	 * 
	 * @param ilTEPPermissions $a_perms
	 */
	protected function setPermissions(ilTEPPermissions $a_perms)
	{
		$this->permissions = $a_perms;
	}
	
	/**
	 * Get permissions
	 * 
	 * @return ilTEPPermissions 
	 */	
	protected function getPermissions()
	{
		return $this->permissions;
	}
	
	/**
	 * Set seed
	 * 
	 * @param string $a_value
	 */
	public function setSeed($a_value)
	{
		$seed = new ilDate($a_value, IL_CAL_DATE);
		$seed = $this->normalizeSeed($seed);
		$this->seed = $seed;
	}
	
	/**
	 * Get seed
	 * 
	 * @return string
	 */
	public function getSeed()
	{
		return $this->seed;
	}
	
	// 
	// request
	// 
		
	/**
	 * Import request data
	 */
	protected function importRequest()
	{		
		$seed = trim($_REQUEST["seed"]);
		if(!$seed)
		{
			$seed = date("Y-m-d");
		}				
		$this->setSeed($seed);		
	}	
	
	/**
	 * Adapt seed to current view
	 * 
	 * @param ilDate $a_value
	 */
	abstract protected function normalizeSeed(ilDate $a_value);
	
	/**
	 * Get currently selected period
	 * 
	 * @return array(ilDate, ilDate)
	 */
	abstract public function getPeriod();
	
	/**
	 * Get (selected) tutor ids 
	 *
	 * @return array
	 */
	abstract public function getTutors();
	
	
	//
	// data
	// 
	
	/**
	 * Load/find entries
	 * 
	 * @return bool
	 */
	abstract public function loadData();	
	
	/**
	 * Has entries?
	 * 
	 * @return bool
	 */
	public function hasData()
	{
		return (bool)sizeof($this->entries);
	}
	
	//
	// presentation
	// 
		
	/**
	 * Prepare data for output (permissions, links)
	 */
	protected function prepareDataForPresentation()
	{
		global $ilAccess, $ilUser, $ilCtrl;
		
		include_once "Services/Link/classes/class.ilLink.php";
		
		$may_create_own = $this->getPermissions()->isTutor();
		$may_create_other = $this->getPermissions()->mayEditOthers();
		$may_view_other = $this->getPermissions()->mayViewOthers();
		$user_cat = ilTEP::getPersonalCalendarId($ilUser->getId());
				
		foreach($this->entries as $user_id => $entries)
		{
			foreach($entries as $idx => $entry)
			{
				$entry_id = $entry["cal_id"];
								
				$url = "";
				if($entry["course_ref_id"])
				{
					// gev-patch start
					if ($ilAccess->checkAccess("write_reduced_settings", "", $entry["course_ref_id"])) {
						$ilCtrl->setParameterByClass(array("gevDesktopGUI", "gevDecentralTrainingGUI"), "ref_id", $entry["course_ref_id"]);
						$url = $ilCtrl->getLinkTargetByClass(array("gevDesktopGUI", "gevDecentralTrainingGUI"), "modifySettings");
						$ilCtrl->setParameterByClass(array("gevDesktopGUI", "gevDecentralTrainingGUI"), "ref_id", null);
					}
					else 
					// gev-patch end
					if($ilAccess->checkAccess("read", "", $entry["course_ref_id"]))
					{
						$url = ilLink::_getStaticLink($entry["course_ref_id"]);
					}
				}
				else
				{									
					// if owner or may edit others
					$entry_editable = (($may_create_own && $entry["cat_id"] == $user_cat) ||
						$may_create_other);
					
					// #156 - if user has derived entry he may view, too
					$drv_viewable = false;
					if(!$entry_editable && !$may_view_other && $entry["derived_id"])
					{
						$drv_entry = new ilCalDerivedEntry($entry["derived_id"]);
						$drv_viewable = ($drv_entry->getCategoryId() == $user_cat);
					}
					
					// view_others should always be set in TEP, making extra sure
					if($entry_editable || $may_view_other || $drv_viewable)
					{					
						if($entry_editable)
						{	
							$cmd = "editEntry";
						}
						else 
						{											
							$cmd = "showEntry";							
						}									
						$ilCtrl->setParameterByClass("ilTEPEntryGUI", "eid", $entry_id);
						$url = $ilCtrl->getLinkTargetByClass("ilTEPEntryGUI", $cmd);						
						$ilCtrl->setParameterByClass("ilTEPEntryGUI", "eid", "");		
					}			
				}	
				$this->entries[$user_id][$idx]["url"] = $url;				
			}			
		}		
	}
	
	/**
	 * Render view
	 * 
	 * @return string
	 */
	abstract public function render();		
	
	
	//
	// export
	// 
	
	/**
	 * Export current entries as XLS
	 */
	public function exportXLS()
	{
		global $lng;
		
		if(!$this->hasData())
		{
			return;
		}
				
		require_once "Services/TEP/classes/class.ilCalEntryType.php";
		$type_map = array();
		foreach(ilCalEntryType::getListData() as $item)
		{
			$type_map[$item["id"]] = $item["title"];
		}
		
		require_once "Services/User/classes/class.ilUserUtil.php";
		require_once "Services/Excel/classes/class.ilExcelUtils.php";
		require_once "Services/Excel/classes/class.ilExcelWriterAdapter.php";
		
		$adapter = new ilExcelWriterAdapter("TEP.xls", true); 
		$workbook = $adapter->getWorkbook();
		$worksheet = $workbook->addWorksheet();
		$worksheet->setLandscape();

		$worksheet->setColumn(0, 0, 30);
		$worksheet->setColumn(1, 1, 40);
		$worksheet->setColumn(2, 2, 30);
		$worksheet->setColumn(3, 3, 30);
		$worksheet->setColumn(4, 4, 40);
		$worksheet->setColumn(5, 5, 30);

		$format_bold = $workbook->addFormat(array("bold" => 1));
		$worksheet->writeString(0, 0, $lng->txt("tep_entry_owner"), $format_bold);
		$worksheet->writeString(0, 1, $lng->txt("tep_entry_period"), $format_bold);
		$worksheet->writeString(0, 2, $lng->txt("tep_entry_type"), $format_bold);
		$worksheet->writeString(0, 3, $lng->txt("tep_entry_title"), $format_bold);
		$worksheet->writeString(0, 4, $lng->txt("description"), $format_bold);
		$worksheet->writeString(0, 5, $lng->txt("tep_entry_location"), $format_bold);

		$format_wrap = $workbook->addFormat();
		$format_wrap->setTextWrap();

		ilDatePresentation::setUseRelativeDates(false);
		
		$row = 1;
		foreach($this->entries as $tutor_id => $items)
		{			
			foreach($items as $item)
			{
				if($tutor_id)
				{
					$name = ilUserUtil::getNamePresentation($tutor_id);
				}
				else
				{
					$name = $lng->txt("column_no_tutor");
				}
				$worksheet->write($row, 0, $name);

				if($item["fullday"])
				{
					$start = new ilDate($item["start"], IL_CAL_DATE);
					$end = new ilDate($item["end"], IL_CAL_DATE);
				}
				else
				{
					// #4034
					$start = new ilDateTime($item["starta"], IL_CAL_DATETIME, "UTC");
					$end = new ilDateTime($item["enda"], IL_CAL_DATETIME, "UTC");
				}
				$worksheet->write($row, 1, ilDatePresentation::formatPeriod($start, $end, true), $format_wrap);

				$worksheet->write($row, 2, $type_map[$item["entry_type"]], $format_wrap);
				$worksheet->write($row, 3, $item["title"], $format_wrap);
				$worksheet->write($row, 4, $item["description"], $format_wrap);
				$worksheet->write($row, 5, $item["location"], $format_wrap);

				$row++;
			}
		}

		$workbook->close();		
	}
}
