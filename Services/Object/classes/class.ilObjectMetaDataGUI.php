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
	protected $object; // [ilObject]
	protected $obj_id; // [int]
	protected $obj_type; // [string]
	protected $sub_type; // [string]
	protected $sub_id; // [int]
	protected $md_observers; // [array]
	
	/**
	 * Construct
	 * 
	 * @param ilObject $a_object
	 * @param string $a_sub_type
	 * @return self
	 */
	public function __construct(ilObject $a_object = null, $a_sub_type = null, $a_sub_id = null)
	{
		global $lng;
		
		if($a_object)
		{
			$this->object = $a_object;
			$this->obj_id = $a_object->getId();
			$this->obj_type = $a_object->getType();
		}
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
				$md_gui = new ilMDEditorGUI((int)$this->obj_id, (int)$this->sub_id, $this->getLOMType());	
				// custom observers?
				if(is_array($this->md_observers))
				{
					foreach($this->md_observers as $observer)
					{
						$md_gui->addObserver($observer["class"], $observer["method"], $observer["section"]);
					}
				}
				// "default" repository object observer
				else if(!$this->sub_id && 
					$this->object)
				{
					$md_gui->addObserver($this->object, 'MDUpdateListener', 'General');
				}
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
	
	public function addMDObserver($a_class, $a_method, $a_section)
	{
		$this->md_observers[] = array(
			"class" => $a_class,
			"method" => $a_method,
			"section" => $a_section				
		);
	}
	
	protected function getLOMType()
	{
		if($this->sub_type != "-" &&
			$this->sub_id)
		{
			return $this->sub_type;
		}
		else
		{
			return $this->obj_type;
		}
	}
	
	protected function isAdvMDAvailable()
	{
		include_once 'Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php';
		foreach(ilAdvancedMDRecord::_getAssignableObjectTypes(false) as $item)
		{			
			if($item["obj_type"] == $this->obj_type)
			{
				return ((!$item["sub_type"] && $this->sub_type == "-") ||
					($item["sub_type"] == $this->sub_type));
			}
		}
		return false;
	}
	
	protected function isLOMAvailable()
	{						
		$type = $this->getLOMType();
		if($type == $this->sub_type)
		{
			$type = $this->obj_type.":".$type;
		}
		
		return (($this->obj_id || !$this->obj_type) &&
			in_array($type, array(
				"crs", 
				"file", 
				"glo", "glo:gdf", 
				"svy", "spl", 
				"tst", "qpl", 
				":mob", 
				"webr", 
				"htlm", 
				"lm", "lm:st", "lm:pg",
				"sahs", "sahs:sco", "sahs:page"
		)));
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
	
	public function getTab($a_base_class = null)
	{
		global $ilCtrl;
		
		if(!$a_base_class)
		{
			$path = array();
		}
		else
		{
			$path = array($a_base_class);
		}
		$path[] = "ilobjectmetadatagui";
		
		$link = null;
		if($this->isLOMAvailable())
		{
			$path[] = "ilmdeditorgui";
			$link = $ilCtrl->getLinkTargetByClass($path, "listSection");
		}
		else if($this->isAdvMDAvailable())
		{	
			if($this->canEdit())
			{
				$link = $ilCtrl->getLinkTarget($this, "edit");
			}
			else if($this->hasAdvancedMDSettings())
			{
				$path[] = "iladvancedmdsettingsgui";
				$link = $ilCtrl->getLinkTargetByClass($path, "showRecords");
			}	
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
		
		if($this->isAdvMDAvailable())
		{		
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
								
				$ilTabs->addSubTab("md_adv_file_list",
					$lng->txt("md_adv_file_list"),
					$ilCtrl->getLinkTargetByClass("iladvancedmdsettingsgui", "showFiles"));
			}				
		}
		
		$ilTabs->activateSubTab($a_active);
	}
	
	
	//
	// (VALUES) EDITOR
	// 
	
	protected function initEditForm()
	{
		global $ilCtrl, $lng;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "update"));				
		$form->setTitle($lng->txt("meta_tab_advmd"));
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$this->record_gui = new ilAdvancedMDRecordGUI(
			ilAdvancedMDRecordGUI::MODE_EDITOR, 
			$this->obj_type, 
			$this->obj_id, 
			$this->sub_type, 
			$this->sub_id
		);
		$this->record_gui->setPropertyForm($form);
		$this->record_gui->parse();
		
		$form->addCommandButton("update", $lng->txt("save"));
		
		return $form;
	}
	
	protected function edit(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;
		
		if(!$a_form)
		{
			$a_form = $this->initEditForm();
		}
		
		$tpl->setContent($a_form->getHTML());		
	}
	
	protected function update()
	{
		global $lng, $ilCtrl;
		
		$form = $this->initEditForm();
		if($form->checkInput() &&
			$this->record_gui->importEditFormPostValues())
		{
			$this->record_gui->writeEditForm();
			
			// Update ECS content
			if($this->obj_type == "crs")
			{
				include_once "Modules/Course/classes/class.ilECSCourseSettings.php";
				$ecs = new ilECSCourseSettings($this->object);
				$ecs->handleContentUpdate();
			}
			
			ilUtil::sendSuccess($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "edit");
		}
		
		$form->setValuesByPost();
		$this->edit($form);
	}
	
	
	//
	// BLOCK
	// 
	
	public function getBlockHTML(array $a_cmds = null, $a_callback = null)
	{
		global $lng;
		
		$html = "";
		
		include_once "Services/Object/classes/class.ilObjectMetaDataBlockGUI.php";	
		include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
		include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php";
		foreach(ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type) as $record)			
		{				
			$block = new ilObjectMetaDataBlockGUI($record, $a_callback);
			$block->setValues(new ilAdvancedMDValues($record->getRecordId(), $this->obj_id, $this->sub_type, $this->sub_id));			
			if($a_cmds)
			{
				foreach($a_cmds as $caption => $url)
				{
					$block->addBlockCommand($url, $lng->txt($caption), "_top");		
				}
			}
			$html.= $block->getHTML();
		}		
		
		return $html;
	}


	//
	// Key/value list
	//


	public function getKeyValueList()
	{
		$html = "";
		$sep = "";

		$old_dt = ilDatePresentation::useRelativeDates();
		ilDatePresentation::setUseRelativeDates(false);

		include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDRecord.php";
		include_once "Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php";
		foreach(ilAdvancedMDRecord::_getSelectedRecordsByObject($this->obj_type, $this->obj_id, $this->sub_type) as $record)
		{
			$vals = new ilAdvancedMDValues($record->getRecordId(), $this->obj_id, $this->sub_type, $this->sub_id);


			include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDValues.php');
			include_once('Services/ADT/classes/class.ilADTFactory.php');

			// this correctly binds group and definitions
			$vals->read();

			$defs = $vals->getDefinitions();
			foreach ($vals->getADTGroup()->getElements() as $element_id => $element)
			{
				if($element instanceof ilADTLocation)
				{
					continue;
				}

				$html.= $sep.$defs[$element_id]->getTitle().": ";

				if($element->isNull())
				{
					$value = "-";
				}
				else
				{
					$value = ilADTFactory::getInstance()->getPresentationBridgeForInstance($element);

					$value = $value->getHTML();
				}
				$html.= $value;
				$sep = ",&nbsp;&nbsp;&nbsp; ";
			}

		}

		ilDatePresentation::setUseRelativeDates($old_dt);

		return $html;
	}

}

?>