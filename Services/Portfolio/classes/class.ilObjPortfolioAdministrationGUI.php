<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* Portfolio Administration Settings.
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id:$
*
* @ilCtrl_Calls ilObjPortfolioAdministrationGUI: ilPermissionGUI
* @ilCtrl_IsCalledBy ilObjPortfolioAdministrationGUI: ilAdministrationGUI
*
* @ingroup ServicesPortfolio
*/
class ilObjPortfolioAdministrationGUI extends ilObjectGUI
{
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "prfa";
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule("prtf");
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem,$ilErr,$ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

/*		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}
*/
		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSettings";
				}

				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		global $rbacsystem, $ilAccess;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "editSettings"),
				array("editSettings", "view"));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"),
				array(),'ilpermissiongui');
		}
	}

	
	/**
	* Edit settings.
	*/
	public function editSettings($a_form = null)
	{
		global $lng, $ilSetting;
		
		$this->tabs_gui->setTabActive('settings');	
		
		if ($ilSetting->get('user_portfolios'))
		{
			ilUtil::sendInfo($lng->txt("prtf_admin_toggle_info"));
		}
		else
		{
			ilUtil::sendInfo($lng->txt("prtf_admin_inactive_info"));
		}
		
		if(!$a_form)
		{
			$a_form = $this->initFormSettings();
		}		
		$this->tpl->setContent($a_form->getHTML());
		return true;
	}

	/**
	* Save settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;
		
		$this->checkPermission("write");
		
		$form = $this->initFormSettings();
		if($form->checkInput())
		{
			$banner = (bool)$form->getInput("banner");
			
			$prfa_set = new ilSetting("prfa");
			$prfa_set->set("banner", $banner);
			$prfa_set->set("banner_width", (int)$form->getInput("width"));
			$prfa_set->set("banner_height", (int)$form->getInput("height"));			
			
			ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
			$ilCtrl->redirect($this, "editSettings");
		}
		
		$form->setValuesByPost();
		$this->editSettings($form);
	}

	/**
	* Save settings
	*/
	public function cancel()
	{
		global $ilCtrl;
		
		$ilCtrl->redirect($this, "view");
	}
		
	/**
	 * Init settings property form
	 *
	 * @access protected
	 */
	protected function initFormSettings()
	{
	    global $lng;
		
		include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));
		$form->setTitle($this->lng->txt('prtf_settings'));
		$form->addCommandButton('saveSettings',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));

		$banner = new ilCheckboxInputGUI($lng->txt("prtf_preview_banner"), "banner");
		$banner->setInfo($lng->txt("prtf_preview_banner_info"));
		$form->addItem($banner);
		
		$width = new ilNumberInputGUI($lng->txt("prtf_preview_banner_width"), "width");
		$width->setRequired(true);
		$width->setSize(4);
		$banner->addSubItem($width);
		
		$height = new ilNumberInputGUI($lng->txt("prtf_preview_banner_height"), "height");
		$height->setRequired(true);
		$height->setSize(4);
		$banner->addSubItem($height);
		
		$prfa_set = new ilSetting("prfa");
		$banner->setChecked($prfa_set->get("banner"));		
		if($prfa_set->get("banner"))
		{
			$width->setValue($prfa_set->get("banner_width"));
			$height->setValue($prfa_set->get("banner_height"));
		}
		else
		{
			$width->setValue(880);
			$height->setValue(100);
		}

		return $form;
	}
}
?>