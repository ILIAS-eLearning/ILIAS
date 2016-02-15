<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Badge/classes/class.ilBadgeHandler.php");

/**
 * Class ilBadgeManagementGUI
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @package ServicesBadge
 */
class ilBadgeManagementGUI
{	
	protected $parent_ref_id; // [int]
	protected $parent_obj_id; // [int]
	protected $parent_obj_type; // [string]
		
	/**
	 * Construct
	 * 
	 * @param int $a_parent_ref_id
	 * @param int $a_parent_obj_id
	 * @param string $a_parent_obj_type
	 * @return self
	 */
	public function __construct($a_parent_ref_id, $a_parent_obj_id = null, $a_parent_obj_type = null)
	{
		global $lng;
		
		$this->parent_ref_id = $a_parent_ref_id;
		$this->parent_obj_id = $a_parent_obj_id
			? $a_parent_obj_id
			: ilObject::_lookupObjId($a_parent_ref_id);
		$this->parent_obj_type = $a_parent_obj_type 
			? $a_parent_obj_type
			: ilObject::_lookupType($this->parent_obj_id);

		if(!ilBadgeHandler::getInstance()->isObjectActive($this->parent_obj_id))
		{
			throw new ilException("inactive object");
		}
		
		$lng->loadLanguageModule("badge");
	}
	
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listUsers");

		switch($next_class)
		{		
			default:	
				$this->$cmd();
				break;
		}
		
		return true;
	}
	
	protected function setTabs($a_active)
	{
		global $ilTabs, $lng, $ilCtrl;
		
		$ilTabs->addSubTab("users", 
			$lng->txt("users"),
			$ilCtrl->getLinkTarget($this, "listUsers"));
		
		$ilTabs->addSubTab("badges", 
			$lng->txt("obj_bdga"),
			$ilCtrl->getLinkTarget($this, "listBadges"));
		
		
		// :TODO: award badge(s)?
		
		$ilTabs->activateSubTab($a_active);
	}
	
	protected function hasWrite()
	{
		global $ilAccess;
		return $ilAccess->checkAccess("write", "", $this->parent_ref_id);
	}
	
	protected function listBadges()
	{
		global $ilToolbar, $lng, $ilCtrl, $tpl;
		
		$this->setTabs("badges");
		
		if($this->hasWrite())
		{
			$handler = ilBadgeHandler::getInstance();				
			$valid_types = $handler->getAvailableTypesForObjType($this->parent_obj_type);
			if($valid_types)
			{
				$options = array();
				foreach($valid_types as $id => $type)
				{
					$options[$id] = $type->getCaption();
				}
				asort($options);
				
				include_once "Services/Form/classes/class.ilSelectInputGUI.php";
				$drop = new ilSelectInputGUI($lng->txt("type"), "type");
				$drop->setOptions($options);
				$ilToolbar->addInputItem($drop, true);
				
				$ilToolbar->setFormAction($ilCtrl->getFormAction($this, "addBadge"));
				$ilToolbar->addFormButton($lng->txt("create"), "addBadge");
			}
		}
		
		include_once "Services/Badge/classes/class.ilBadgeTableGUI.php";
		$tbl = new ilBadgeTableGUI($this, "listBadges", $this->parent_obj_id, $this->hasWrite());
		$tpl->setContent($tbl->getHTML());
	}
	
	
	//
	// badge (CRUD)
	//
	
	protected function addBadge(ilPropertyFormGUI $a_form = null)
	{				
		global $ilCtrl, $tpl;
		
		$type_id = $_REQUEST["type"];
		if(!$type_id || 
			!$this->hasWrite())
		{
			$ilCtrl->redirect($this, "listBadges");
		}
		
		$ilCtrl->setParameter($this, "type", $type_id);
		
		$handler = ilBadgeHandler::getInstance();
		$type = $handler->getTypeInstanceByUniqueId($type_id);		
		if(!$type)
		{
			$ilCtrl->redirect($this, "listBadges");
		}
		
		if(!$a_form)
		{
			$a_form = $this->initBadgeForm("create", $type, $type_id);
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function initBadgeForm($a_mode, ilBadgeType $a_type, $a_type_unique_id)
	{
		global $lng, $ilCtrl;
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this, "saveBadge"));
		$form->setTitle($lng->txt("badge_badge").' "'.$a_type->getCaption().'"');
		
		$active = new ilCheckboxInputGUI($lng->txt("active"), "act");
		$form->addItem($active);
		
		$title = new ilTextInputGUI($lng->txt("title"), "title");
		$title->setRequired(true);
		$form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "desc");
		$desc->setRequired(true);
		$form->addItem($desc);
		
		if($a_mode == "create")
		{
			// upload
	
			$img_mode = new ilRadioGroupInputGUI($lng->txt("image"), "img_mode");			
			$img_mode->setRequired(true);
			$form->addItem($img_mode);			
	
			$img_mode_tmpl = new ilRadioOption($lng->txt("badge_image_from_template"), "tmpl");
			$img_mode->addOption($img_mode_tmpl);

			$img_mode_up = new ilRadioOption($lng->txt("badge_image_from_upload"), "up");
			$img_mode->addOption($img_mode_up);
			
			$img_upload = new ilImageFileInputGUI($lng->txt("file"), "img");
			$img_upload->setRequired(true);
			$img_mode_up->addSubItem($img_upload);

			// templates
			
			include_once "Services/Badge/classes/class.ilBadgeImageTemplate.php";
			$valid_templates = ilBadgeImageTemplate::getInstancesByType($a_type_unique_id);			
			if(sizeof($valid_templates))
			{
				$options = array();		
				$options[""] = $lng->txt("please_select");						
				foreach($valid_templates as $tmpl)
				{
					$options[$tmpl->getId()] = $tmpl->getTitle();
				}

				$tmpl = new ilSelectInputGUI($lng->txt("badge_image_template_form"), "tmpl");
				$tmpl->setRequired(true);
				$tmpl->setOptions($options);
				$img_mode_tmpl->addSubItem($tmpl);
			}
			else
			{
				// no templates, activate upload
				$img_mode_tmpl->setDisabled(true);
				$img_mode->setValue("up");
			}
		}
		else
		{
			$img_upload = new ilImageFileInputGUI($lng->txt("image"), "img");
			$img_upload->setALlowDeletion(false);
			$form->addItem($img_upload);
		}
		
		$custom = $a_type->getConfigGUIInstance();
		if($custom &&
			$custom instanceof ilBadgeTypeGUI)
		{
			$custom->initConfigForm($form);
		}
		
		// :TODO: valid date/period		
		
		if($a_mode == "create")
		{
			$form->addCommandButton("saveBadge", $lng->txt("save"));
		}
		else
		{
			$form->addCommandButton("updateBadge", $lng->txt("save"));
		}
		$form->addCommandButton("listBadges", $lng->txt("cancel"));
		
		return $form;
	}
	
	protected function saveBadge()
	{
		global $ilCtrl, $lng;
		
		$type_id = $_REQUEST["type"];
		if(!$type_id || 
			!$this->hasWrite())
		{
			$ilCtrl->redirect($this, "listBadges");
		}
		
		$ilCtrl->setParameter($this, "type", $type_id);
		
		$handler = ilBadgeHandler::getInstance();
		$type = $handler->getTypeInstanceByUniqueId($type_id);		
		if(!$type)
		{
			$ilCtrl->redirect($this, "listBadges");
		}
		
		$form = $this->initBadgeForm("create", $type, $type_id);		
		if($form->checkInput())
		{
			include_once "Services/Badge/classes/class.ilBadge.php";
			$badge = new ilBadge();
			$badge->setParentId($this->parent_obj_id); // :TODO: ref_id?
			$badge->setTypeId($type_id);
			$badge->setActive($form->getInput("act"));
			$badge->setTitle($form->getInput("title"));
			$badge->setDescription($form->getInput("desc"));
				
			$custom = $type->getConfigGUIInstance();
			if($custom &&
				$custom instanceof ilBadgeTypeGUI)
			{
				$badge->setConfiguration($custom->getConfigFromForm($form));
			}
						
			$badge->create();
			
			if($form->getInput("img_mode") == "up")
			{
				$badge->uploadImage($_FILES["img"]);
			}
			else
			{
				$tmpl = new ilBadgeImageTemplate($form->getInput("tmpl"));
				$badge->importImage($tmpl->getImage(), $tmpl->getImagePath());
			}
					
			ilUtil::sendInfo($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "listBadges");
		}
		
		$form->setValuesByPost();
		$this->addBadge($form);
	}
	
	protected function editBadge(ilPropertyFormGUI $a_form = null)
	{				
		global $ilCtrl, $tpl;
		
		$badge_id = $_REQUEST["bid"];
		if(!$badge_id || 
			!$this->hasWrite())
		{
			$ilCtrl->redirect($this, "listBadges");
		}
		
		$ilCtrl->setParameter($this, "bid", $badge_id);
		
		include_once "./Services/Badge/classes/class.ilBadge.php";
		$badge = new ilBadge($badge_id);
		
		if(!$a_form)
		{			
			$type = $badge->getTypeInstance();
			$a_form = $this->initBadgeForm("edit", $type, $badge->getTypeId());			
			$this->setBadgeFormValues($a_form, $badge, $type);
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	protected function setBadgeFormValues(ilPropertyFormGUI $a_form, ilBadge $a_badge, ilBadgeType $a_type)
	{
		$a_form->getItemByPostVar("act")->setChecked($a_badge->isActive());
		$a_form->getItemByPostVar("title")->setValue($a_badge->getTitle());
		$a_form->getItemByPostVar("desc")->setValue($a_badge->getDescription());
		$a_form->getItemByPostVar("img")->setValue($a_badge->getImage());
		$a_form->getItemByPostVar("img")->setImage($a_badge->getImagePath());
		
		$custom = $a_type->getConfigGUIInstance();
		if($custom &&
			$custom instanceof ilBadgeTypeGUI)
		{
			$custom->importConfigToForm($a_form, $a_badge->getConfiguration());
		}		
	}
	
	protected function updateBadge()
	{
		global $ilCtrl, $lng;
		
		$badge_id = $_REQUEST["bid"];
		if(!$badge_id || 
			!$this->hasWrite())
		{
			$ilCtrl->redirect($this, "listBadges");
		}
		
		$ilCtrl->setParameter($this, "bid", $badge_id);
		
		include_once "./Services/Badge/classes/class.ilBadge.php";
		$badge = new ilBadge($badge_id);
		$type = $badge->getTypeInstance();
		$form = $this->initBadgeForm("update", $type, $badge->getTypeId());		
		if($form->checkInput())
		{			
			$badge->setActive($form->getInput("act"));
			$badge->setTitle($form->getInput("title"));
			$badge->setDescription($form->getInput("desc"));
						
			$custom = $type->getConfigGUIInstance();
			if($custom &&
				$custom instanceof ilBadgeTypeGUI)
			{
				$badge->setConfiguration($custom->getConfigFromForm($form));
			}
						
			$badge->update();
			
			$badge->uploadImage($_FILES["img"]);
			
			ilUtil::sendInfo($lng->txt("settings_saved"), true);
			$ilCtrl->redirect($this, "listBadges");
		}
		
		$form->setValuesByPost();
		$this->editBadge($form);
	}
	
	
	//
	// users
	// 
	
	protected function listUsers()
	{
		$this->setTabs("users");
		
		
		
	}
	
}