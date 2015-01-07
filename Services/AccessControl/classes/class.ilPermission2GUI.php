<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilPermissionGUI
* RBAC related output
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id: class.ilPermissionGUI.php 20310 2009-06-23 12:57:19Z smeyer $
*
*
* @ingroup	ServicesAccessControl
*/
class ilPermission2GUI
{
	protected $gui_obj = null;
	protected $ilErr = null;
	protected $ctrl = null;
	protected $lng = null;
	
	public function __construct($a_gui_obj)
	{
		global $ilias, $objDefinition, $tpl, $tree, $ilCtrl, $ilErr, $lng;
		
		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr =& $ilErr;
		}

		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->lng->loadLanguageModule("rbac");

		$this->ctrl =& $ilCtrl;

		$this->gui_obj = $a_gui_obj;
		
		$this->roles = array();
		$this->num_roles = 0;
	}
	


	

	// show owner sub tab
	function owner()
	{		
		$this->__initSubTabs("owner");
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this, "owner"));
		$form->setTitle($this->lng->txt("info_owner_of_object"));
		
		$login = new ilTextInputGUI($this->lng->txt("login"), "owner");
		$login->setDataSource($this->ctrl->getLinkTargetByClass(array(get_class($this),
			'ilRepositorySearchGUI'), 'doUserAutoComplete', '', true));		
		$login->setRequired(true);
		$login->setSize(50);
		$login->setInfo($this->lng->txt("chown_warning"));
		$login->setValue(ilObjUser::_lookupLogin($this->gui_obj->object->getOwner()));
		$form->addItem($login);
		
		$form->addCommandButton("changeOwner", $this->lng->txt("change_owner"));
		
		$this->tpl->setContent($form->getHTML());
	}
	
	function changeOwner()
	{
		global $rbacsystem,$ilObjDataCache;

		if(!$user_id = ilObjUser::_lookupId($_POST['owner']))
		{
			ilUtil::sendFailure($this->lng->txt('user_not_known'));
			$this->owner();
			return true;
		}
		
		// no need to change?
		if($user_id != $this->gui_obj->object->getOwner())
		{
			$this->gui_obj->object->setOwner($user_id);
			$this->gui_obj->object->updateOwner();
			$ilObjDataCache->deleteCachedEntry($this->gui_obj->object->getId());			

			include_once "Services/AccessControl/classes/class.ilRbacLog.php";
			if(ilRbacLog::isActive())
			{
				ilRbacLog::add(ilRbacLog::CHANGE_OWNER, $this->gui_obj->object->getRefId(), array($user_id));
			}
		}
		
		ilUtil::sendSuccess($this->lng->txt('owner_updated'),true);

		if (!$rbacsystem->checkAccess("edit_permission",$this->gui_obj->object->getRefId()))
		{
			$this->ctrl->redirect($this->gui_obj);
			return true;
		}

		$this->ctrl->redirect($this,'owner');
		return true;

	}
	
	// init sub tabs
	function __initSubTabs($a_cmd)
	{
		global $ilTabs;

		$perm = ($a_cmd == 'perm') ? true : false;
		$info = ($a_cmd == 'perminfo') ? true : false;
		$owner = ($a_cmd == 'owner') ? true : false;
		$log = ($a_cmd == 'log') ? true : false;

		$ilTabs->addSubTabTarget("permission_settings", $this->ctrl->getLinkTarget($this, "perm"),
								 "", "", "", $perm);
								 
		#$ilTabs->addSubTabTarget("permission_settings", $this->ctrl->getLinkTarget($this, "perm2"),
		#							 "", "", "", $perm);
								 
		$ilTabs->addSubTabTarget("info_status_info", $this->ctrl->getLinkTargetByClass(array(get_class($this),"ilobjectpermissionstatusgui"), "perminfo"),
								 "", "", "", $info);
		$ilTabs->addSubTabTarget("owner", $this->ctrl->getLinkTarget($this, "owner"),
								 "", "", "", $owner);

		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		if(ilRbacLog::isActive())
		{
			$ilTabs->addSubTabTarget("log", $this->ctrl->getLinkTarget($this, "log"),
									 "", "", "", $log);
		}
	}
	
	function log()
	{
		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		if(!ilRbacLog::isActive())
		{
			$this->ctrl->redirect($this, "perm");
		}

		$this->__initSubTabs("log");

		include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
		$table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
		$this->tpl->setContent($table->getHTML());
	}

	function applyLogFilter()
    {
		include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
		$table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
		$table->resetOffset();
		$table->writeFilterToSession();
		$this->log();
    }

	function resetLogFilter()
    {
		include_once "Services/AccessControl/classes/class.ilRbacLogTableGUI.php";
		$table = new ilRbacLogTableGUI($this, "log", $this->gui_obj->object->getRefId());
		$table->resetOffset();
		$table->resetFilter();
		$this->log();
    }

} // END class.ilPermissionGUI
?>
