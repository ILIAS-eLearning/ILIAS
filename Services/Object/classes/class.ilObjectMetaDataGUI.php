<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilObjectMetaDataGUI
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
* 
* @ilCtrl_Calls ilObjectMetaDataGUI: ilMDEditorGUI, ilAdvancedMDSettingsGUI
*/
class ilObjectMetaDataGUI
{
	protected $obj_id; // [int]
	protected $obj_type; // [string]
	protected $sub_type; // [string]
	
	/**
	 * Construct
	 * 
	 * @param int $a_obj_id
	 * @param string $a_obj_type
	 * @param string $a_sub_type
	 * @return self
	 */
	public function __construct($a_obj_id, $a_obj_type, $a_sub_type = null, $a_sub_id = null)
	{
		global $lng;
		
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->sub_type = $a_sub_type;
		$this->sub_id = $a_sub_id;
		
		if(!$this->sub_type)
		{
			$this->sub_type = "-";
		}
		
		$lng->loadLanguageModule("meta");
	}
	
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("edit");
		
		switch($next_class)
		{			
			case 'ilmdeditorgui':										
				$this->setSubTabs("lom");		
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';
				$md_gui = new ilMDEditorGUI($this->obj_id, 0, $this->obj_type);
				$md_gui->addObserver($this->object,'MDUpdateListener','General');
				$ilCtrl->forwardCommand($md_gui);				
				break;
				
			case 'iladvancedmdsettingsgui':	
				$this->setSubTabs("advmddef");	
				include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDSettingsGUI.php';
				$advmdgui = new ilAdvancedMDSettingsGUI($this->obj_id, $this->obj_type, $this->sub_type);				
				$ilCtrl->forwardCommand($advmdgui);							
				break;				
			
			default:
				$this->setSubTabs("advmd");	
				$this->$cmd();
				break;
		}
	}	
	
	protected function isLOMAvailable()
	{
		// no sub-type supported
		return ($this->obj_id &&
			!$this->sub_id && 
			in_array($this->obj_type, array("crs", "glo")));
	}
	
	protected function hasAdvancedMDSettings()
	{					
		if($this->sub_id)
		{
			return false;
		}
		
		include_once 'Services/Container/classes/class.ilContainer.php';
		include_once 'Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
		
		return ilContainer::_lookupContainerSetting(
			$this->obj_id,
			ilObjectServiceSettingsGUI::CUSTOM_METADATA,
			false);	
	}		
	
	protected function hasActiveRecords()
	{
		include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php';
		return (bool)sizeof(ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type));
	}
	
	protected function canEdit()
	{
		if($this->hasActiveRecords() &&
			$this->obj_id)
		{
			if($this->sub_type == "-" ||
				$this->sub_id)
			{
				return true;
			}
		}
		return false;
	}
	
	public function getTab()
	{
		global $ilCtrl;
		
		$link = null;
		if($this->isLOMAvailable())
		{
			$link = $ilCtrl->getLinkTargetByClass(array("ilobjectmetadatagui", "ilmdeditorgui"), "listSection");
		}
		else if($this->canEdit())
		{
			$link = $ilCtrl->getLinkTarget($this, "edit");
		}
		else if($this->hasAdvancedMDSettings())
		{
			$link = $ilCtrl->getLinkTargetByClass(array("ilobjectmetadatagui", "iladvancedmdsettingsgui"), "showRecords");
		}		
		return $link;
	}

	protected function setSubTabs($a_active)
	{
		global $ilTabs, $lng, $ilCtrl;
				
		if($this->isLOMAvailable())
		{
			$ilTabs->addSubTab("lom",			
				$lng->txt("meta_tab_lom"),
				$ilCtrl->getLinkTargetByClass("ilmdeditorgui", "listSection")
			);
		}
		
		if($this->canEdit())
		{
			$ilTabs->addSubTab("advmd",
				$lng->txt("meta_tab_advmd"),
				$ilCtrl->getLinkTarget($this, "edit"));
		}
				
		if($this->hasAdvancedMDSettings())
		{
			$ilTabs->addSubTab("advmddef",
				$lng->txt("meta_tab_advmd_def"),
				$ilCtrl->getLinkTargetByClass("iladvancedmdsettingsgui", "showRecords"));
		}		
		
		$ilTabs->activateSubTab($a_active);
	}
	
	protected function edit()
	{
		
		
	}
}

?>