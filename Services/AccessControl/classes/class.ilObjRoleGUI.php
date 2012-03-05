<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Object/classes/class.ilObjectGUI.php";
include_once './Services/AccessControl/classes/class.ilObjRole.php';

/**
* Class ilObjRoleGUI
*
* @author Stefan Meyer <smeyer@ilias@gmx.de>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilObjRoleGUI: ilRepositorySearchGUI
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleGUI extends ilObjectGUI
{
	const MODE_GLOBAL_UPDATE = 1;
	const MODE_GLOBAL_CREATE = 2;
	const MODE_LOCAL_UPDATE = 3;
	const MODE_LOCAL_CREATE = 4;

	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* rolefolder ref_id where role is assigned to
	* @var		string
	* @access	public
	*/
	var $rolf_ref_id;
	
	protected $obj_ref_id = 0;
	protected $obj_obj_id = 0;
	protected $obj_obj_type = '';
	protected $container_type = '';


	var $ctrl;
 
	/**
	* Constructor
	* @access public
	*/
	function __construct($a_data,$a_id,$a_call_by_reference = false,$a_prepare_output = true)
	{
		global $tree,$lng;
		
		$lng->loadLanguageModule('rbac');

		//TODO: move this to class.ilias.php
		define("USER_FOLDER_ID",7);
		
		if($_GET['rolf_ref_id'] != '')
		{
			$this->rolf_ref_id = $_GET['rolf_ref_id'];
		}
		else
		{
			$this->rolf_ref_id = $_GET['ref_id'];
		}
		// Add ref_id of object that contains this role folder
		$this->obj_ref_id = $tree->getParentId($this->rolf_ref_id);
		$this->obj_obj_id = ilObject::_lookupObjId($this->getParentRefId());
		$this->obj_obj_type = ilObject::_lookupType($this->getParentObjId());
		
		$this->container_type = ilObject::_lookupType(ilObject::_lookupObjId($this->obj_ref_id));

		$this->type = "role";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		$this->ctrl->saveParameter($this, array("obj_id", "rolf_ref_id"));
	}


	function &executeCommand()
	{
		global $rbacsystem;

		$this->prepareOutput();
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setTitle($this->lng->txt('role_add_user'));
				$rep_search->setCallback($this,'addUserObject');

				// Set tabs
				$this->tabs_gui->setTabActive('user_assignment');
				$this->ctrl->setReturn($this,'userassignment');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				break;

			default:
				if(!$cmd)
				{
					if($this->showDefaultPermissionSettings())
					{
						$cmd = "perm";
					}
					else
					{
						$cmd = 'userassignment';
					}
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}

		return true;
	}
	
	/**
	 * Get ref id of current object (not role folder id)
	 * @return 
	 */
	public function getParentRefId()
	{
		return $this->obj_ref_id;
	}
	
	/**
	 * Get obj_id of current object
	 * @return 
	 */
	public function getParentObjId()
	{
		return $this->obj_obj_id;
	}
	
	/**
	 * get type of current object (not role folder)
	 * @return 
	 */
	public function getParentType()
	{
		return $this->obj_obj_type;
	}
	
	/**
	* set back tab target
	*/
	function setBackTarget($a_text, $a_link)
	{
		$this->back_target = array("text" => $a_text,
			"link" => $a_link);
	}
	
	public function getBackTarget()
	{
		return $this->back_target ? $this->back_target : array();
	}
	
	/**
	* admin and normal tabs are equal for roles
	*/
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	/**
	 * Get type of role container
	 * @return 
	 */
	protected function getContainerType()
	{
		return $this->container_type;
	}
	
	/**
	 * check if default permissions are shown or not
	 * @return 
	 */
	protected function showDefaultPermissionSettings()
	{
		global $objDefinition;
		
		return $objDefinition->isContainer($this->getContainerType());
	}


	function listDesktopItemsObject()
	{
		global $rbacsystem,$rbacreview,$tree;


		#if(!$rbacsystem->checkAccess('edit_permission', $this->rolf_ref_id))
		/*
		if(!$this->checkAccess('edit_permission'))
		{
			ilUtil::sendFailure()
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		*/
		if(!$rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id) &&
			$this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			ilUtil::sendInfo($this->lng->txt('role_no_users_no_desk_items'));
			return true;
		}


		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';
		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());

		if($rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->__showButton('selectDesktopItem',$this->lng->txt('role_desk_add'));
		}
		if(!count($items = $role_desk_item_obj->getAll()))
		{
			ilUtil::sendInfo($this->lng->txt('role_desk_none_created'));
			return true;
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_desktop_item_list.html", "Services/AccessControl");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_role.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_role'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('role_assigned_desk_items').' ('.$this->object->getTitle().')');
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));

		$counter = 0;

		foreach($items as $role_item_id => $item)
		{
			$tmp_obj = ilObjectFactory::getInstanceByRefId($item['item_id']);
			
			if(strlen($desc = $tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_DESK",$desc);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("desk_row");
			$this->tpl->setVariable("DESK_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_DESK",ilUtil::formCheckBox(0,'del_desk_item[]',$role_item_id));
			$this->tpl->setVariable("TXT_PATH",$this->lng->txt('path').':');
			$this->tpl->setVariable("PATH",$this->__formatPath($tree->getPathFull($item['item_id'])));
			$this->tpl->parseCurrentBlock();
		}

		return true;
	}

	function askDeleteDesktopItemObject()
	{
		global $rbacsystem;
		
		
		#if(!$rbacsystem->checkAccess('edit_permission', $this->rolf_ref_id))
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['del_desk_item']))
		{
			ilUtil::sendFailure($this->lng->txt('role_select_one_item'));

			$this->listDesktopItemsObject();

			return true;
		}
		ilUtil::sendQuestion($this->lng->txt('role_sure_delete_desk_items'));
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_ask_delete_desktop_item.html", "Services/AccessControl");
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_role.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('obj_role'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('role_assigned_desk_items').' ('.$this->object->getTitle().')');
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));

		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());

		$counter = 0;

		foreach($_POST['del_desk_item'] as $role_item_id)
		{
			$item_data = $role_desk_item_obj->getItem($role_item_id);
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($item_data['item_id']);

			if(strlen($desc = $tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_DESK",$desc);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("desk_row");
			$this->tpl->setVariable("DESK_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}

		$_SESSION['role_del_desk_items'] = $_POST['del_desk_item'];

		return true;
	}

	function deleteDesktopItemsObject()
	{
		global $rbacsystem;
		
		#if (!$rbacsystem->checkAccess('edit_permission', $this->rolf_ref_id))
		if(!$this->checkAccess('edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if (!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		if (!count($_SESSION['role_del_desk_items']))
		{
			ilUtil::sendFailure($this->lng->txt('role_select_one_item'));

			$this->listDesktopItemsObject();

			return true;
		}

		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());

		foreach ($_SESSION['role_del_desk_items'] as $role_item_id)
		{
			$role_desk_item_obj->delete($role_item_id);
		}

		ilUtil::sendSuccess($this->lng->txt('role_deleted_desktop_items'));
		$this->listDesktopItemsObject();

		return true;
	}


	function selectDesktopItemObject()
	{
		global $rbacsystem,$tree;

		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItemSelector.php';
		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';

		if(!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			#$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			ilUtil::sendFailure($this->lng->txt('permission_denied'));
			$this->listDesktopItemsObject();
			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_desktop_item_selector.html", "Services/AccessControl");
		$this->__showButton('listDesktopItems',$this->lng->txt('back'));

		ilUtil::sendInfo($this->lng->txt("role_select_desktop_item"));
		
		$exp = new ilRoleDesktopItemSelector($this->ctrl->getLinkTarget($this,'selectDesktopItem'),
											 new ilRoleDesktopItem($this->object->getId()));
		$exp->setExpand($_GET["role_desk_item_link_expand"] ? $_GET["role_desk_item_link_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'selectDesktopItem'));
		
		$exp->setOutput(0);
		
		$output = $exp->getOutput();
		$this->tpl->setVariable("EXPLORER",$output);
		//$this->tpl->setVariable("EXPLORER", $exp->getOutput());

		return true;
	}

	function assignDesktopItemObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess('push_desktop_items',USER_FOLDER_ID))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
			return false;
		}
	

		if (!isset($_GET['item_id']))
		{
			ilUtil::sendFailure($this->lng->txt('role_no_item_selected'));
			$this->selectDesktopItemObject();

			return false;
		}

		include_once 'Services/AccessControl/classes/class.ilRoleDesktopItem.php';

		$role_desk_item_obj =& new ilRoleDesktopItem($this->object->getId());
		$role_desk_item_obj->add((int) $_GET['item_id'],ilObject::_lookupType((int) $_GET['item_id'],true));

		ilUtil::sendSuccess($this->lng->txt('role_assigned_desktop_item'));

		$this->ctrl->redirect($this,'listDesktopItems');
		return true;
	}
	
	/**
	 * Create role prperty form
	 * @return 
	 * @param int $a_mode
	 */
	protected function initFormRoleProperties($a_mode)
	{
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();

		if($this->creation_mode)
		{
			$this->ctrl->setParameter($this, "new_type", 'role');
		}
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	
		switch($a_mode)
		{
			case self::MODE_GLOBAL_CREATE:
				$this->form->setTitle($this->lng->txt('role_new'));
				$this->form->addCommandButton('save',$this->lng->txt('role_new'));
				break;
				
			case self::MODE_GLOBAL_UPDATE:
				$this->form->setTitle($this->lng->txt('role_edit'));
				$this->form->addCommandButton('update', $this->lng->txt('save'));
				break;
				
			case self::MODE_LOCAL_CREATE:
			case self::MODE_LOCAL_UPDATE:
		}
		// Fix cancel
		$this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
		
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		if(ilObjRole::isAutoGenerated($this->object->getId()))
		{
			$title->setDisabled(true);
		}
		$title->setValidationRegexp('/^(?!il_).*$/');
		$title->setValidationFailureMessage($this->lng->txt('msg_role_reserved_prefix'));
		$title->setSize(40);
		$title->setMaxLength(70);
		$title->setRequired(true);
		$this->form->addItem($title);
		
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		if(ilObjRole::isAutoGenerated($this->object->getId()))
		{
			$desc->setDisabled(true);
		}
		$desc->setCols(40);
		$desc->setRows(3);
		$this->form->addItem($desc);
		
		if($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$reg = new ilCheckboxInputGUI($this->lng->txt('allow_register'),'reg');
			$reg->setValue(1);
			#$reg->setInfo($this->lng->txt('rbac_new_acc_reg_info'));
			$this->form->addItem($reg);
			
			$la = new ilCheckboxInputGUI($this->lng->txt('allow_assign_users'),'la');
			$la->setValue(1);
			#$la->setInfo($this->lng->txt('rbac_local_admin_info'));
			$this->form->addItem($la);
		}
		
		$pro = new ilCheckboxInputGUI($this->lng->txt('role_protect_permissions'),'pro');
		$pro->setValue(1);
		#$pro->setInfo($this->lng->txt('role_protext_permission_info'));
		$this->form->addItem($pro);
		
		include_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		if(ilDiskQuotaActivationChecker::_isActive())
		{
			$quo = new ilNumberInputGUI($this->lng->txt('disk_quota'),'disk_quota');
			$quo->setMinValue(0);
			$quo->setSize(4);
			$quo->setInfo($this->lng->txt('enter_in_mb_desc').'<br />'.$this->lng->txt('disk_quota_on_role_desc'));
			$this->form->addItem($quo);
		}
			
		return true;
	}
	
	/**
	 * Store form input in role object
	 * @return 
	 * @param object $role
	 */
	protected function loadRoleProperties(ilObjRole $role)
	{
		$role->setTitle($this->form->getInput('title'));
		$role->setDescription($this->form->getInput('desc'));
		$role->setAllowRegister($this->form->getInput('reg'));
		$role->toggleAssignUsersStatus($this->form->getInput('la'));
		$role->setDiskQuota($this->form->getInput('disk_quota') * pow(ilFormat::_getSizeMagnitude(),2));
		return true;
	}
	
	/**
	 * Read role properties and write them to form
	 * @return 
	 * @param object $role
	 */
	protected function readRoleProperties(ilObjRole $role)
	{
		global $rbacreview;
		
		include_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';

		$data['title'] = $role->getTitle();
		$data['desc'] = $role->getDescription();
		$data['reg'] = $role->getAllowRegister();
		$data['la'] = $role->getAssignUsersStatus();
		if(ilDiskQuotaActivationChecker::_isActive())
		{
			$data['disk_quota'] = $role->getDiskQuota() / (pow(ilFormat::_getSizeMagnitude(),2));
		}
		$data['pro'] = $rbacreview->isProtected($this->rolf_ref_id, $role->getId());
		
		$this->form->setValuesByArray($data);
	}
	



	/**
	 * Only called from administration -> role folder ?
	 * Otherwise this check access is wrong
	 * @return 
	 */
	public function createObject()
	{
		global $rbacsystem;
		
		if(!$rbacsystem->checkAccess('create_role',$this->rolf_ref_id))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}
		
		$this->initFormRoleProperties(self::MODE_GLOBAL_CREATE);
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * Edit role properties
	 * @return 
	 */
	public function editObject()
	{
		global $rbacsystem, $rbacreview, $ilSetting,$ilErr;

		if(!$this->checkAccess('write','edit_permission'))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}
		$this->initFormRoleProperties(self::MODE_GLOBAL_UPDATE);
		$this->readRoleProperties($this->object);
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	* edit object
	*
	* @access	public
	*/
	function editObject2()
	{
		global $rbacsystem, $rbacreview, $ilSetting;
		require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		require_once './Services/Utilities/classes/class.ilFormat.php';

		#if (!$rbacsystem->checkAccess("write", $this->rolf_ref_id))
		if(!$this->checkAccess('write','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_edit.html", "Services/AccessControl");

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			if (substr($this->object->getTitle(false),0,3) != "il_")
			{
				$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"]),true);
				$this->tpl->setVariable("DESC",ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]));
			}
		
			$allow_register = ($_SESSION["error_post_vars"]["Fobject"]["allow_register"]) ? "checked=\"checked\"" : "";
			$assign_users = ($_SESSION["error_post_vars"]["Fobject"]["assign_users"]) ? "checked=\"checked\"" : "";
			$protect_permissions = ($_SESSION["error_post_vars"]["Fobject"]["protect_permissions"]) ? "checked=\"checked\"" : "";
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				$disk_quota = $_SESSION["error_post_vars"]["Fobject"]["disk_quota"];
			}
		}
		else
		{
			if (substr($this->object->getTitle(),0,3) != "il_")
			{
				$this->tpl->setVariable("TITLE",ilUtil::prepareFormOutput($this->object->getTitle()));
				$this->tpl->setVariable("DESC",ilUtil::stripSlashes($this->object->getDescription()));
			}

			$allow_register = ($this->object->getAllowRegister()) ? "checked=\"checked\"" : "";
			$assign_users = $this->object->getAssignUsersStatus() ? "checked=\"checked\"" : "";
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				$disk_quota = $this->object->getDiskQuota() / ilFormat::_getSizeMagnitude() / ilFormat::_getSizeMagnitude();
			}
			$protect_permissions = $rbacreview->isProtected($this->rolf_ref_id,$this->object->getId()) ? "checked=\"checked\"" : "";

		}

		$obj_str = "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt("desc"));
		
		// exclude allow register option for anonymous role, system role and all local roles
		$global_roles = $rbacreview->getGlobalRoles();

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		if (substr($this->object->getTitle(),0,3) == "il_")
		{
			$this->tpl->setVariable("SHOW_TITLE",ilObjRole::_getTranslation($this->object->getTitle())." (".$this->object->getTitle().")");
			
			$rolf = $rbacreview->getFoldersAssignedToRole($this->object->getId(),true);
			$parent_node = $this->tree->getParentNodeData($rolf[0]);

			$this->tpl->setVariable("SHOW_DESC",$this->lng->txt("obj_".$parent_node['type'])." (".$parent_node['obj_id'].") <br/>".$parent_node['title']);

			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("back"));
			$this->tpl->setVariable("CMD_SUBMIT", "cancel");
		}

		if ($this->object->getId() != ANONYMOUS_ROLE_ID and 
			$this->object->getId() != SYSTEM_ROLE_ID and 
			in_array($this->object->getId(),$global_roles))
		{
			$this->tpl->setCurrentBlock("allow_register");
			$this->tpl->setVariable("TXT_ALLOW_REGISTER",$this->lng->txt("allow_register"));
			$this->tpl->setVariable("ALLOW_REGISTER",$allow_register);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("assign_users");
			$this->tpl->setVariable("TXT_ASSIGN_USERS",$this->lng->txt('allow_assign_users'));
			$this->tpl->setVariable("ASSIGN_USERS",$assign_users);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("protect_permissions");
			$this->tpl->setVariable("TXT_PROTECT_PERMISSIONS",$this->lng->txt('role_protect_permissions'));
			$this->tpl->setVariable("PROTECT_PERMISSIONS",$protect_permissions);
			$this->tpl->parseCurrentBlock();

			require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				$this->tpl->setCurrentBlock("disk_quota");
				$this->tpl->setVariable("TXT_DISK_QUOTA",$this->lng->txt("disk_quota"));
				$this->tpl->setVariable("TXT_DISK_QUOTA_DESC",$this->lng->txt("enter_in_mb_desc").'<br>'.$this->lng->txt("disk_quota_on_role_desc"));
				$this->tpl->setVariable("DISK_QUOTA",$disk_quota);
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	
	/**
	 * Save new role
	 * @return 
	 */
	public function saveObject()
	{
		global $rbacadmin,$rbacreview;
		
		$this->initFormRoleProperties(self::MODE_GLOBAL_CREATE);
		if($this->form->checkInput() and !$this->checkDuplicate())
		{
			include_once './Services/AccessControl/classes/class.ilObjRole.php';
			$this->loadRoleProperties($this->role = new ilObjRole());
			$this->role->create();
			$rbacadmin->assignRoleToFolder($this->role->getId(), $this->rolf_ref_id,'y');
			$rbacadmin->setProtected(
				$this->rolf_ref_id,
				$this->role->getId(),
				$this->form->getInput('pro') ? 'y' : 'n'
			);
			ilUtil::sendSuccess($this->lng->txt("role_added"),true);
			$this->ctrl->returnToParent($this);
		}
		
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
		return false;
	}
	
	/**
	 * Check if role with same name already exists in this folder
	 * @return bool 
	 */
	protected function checkDuplicate($a_role_id = 0)
	{
		global $rbacreview;

		foreach($rbacreview->getRolesOfRoleFolder($this->rolf_ref_id) as $role_id)
		{
			if($role_id == $a_role_id)
			{
				continue;
			}
			
			$title = trim(ilObject::_lookupTitle($role_id));
			if(strcmp($title, trim($this->form->getInput('title'))) === 0)
			{
				$this->form->getItemByPostVar('title')->setAlert($this->lng->txt('rbac_role_exists_alert'));
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Save role settings
	 * @return 
	 */
	public function updateObject()
	{
		global $rbacadmin;
		
		$this->initFormRoleProperties(self::MODE_GLOBAL_UPDATE);
		if($this->form->checkInput() and !$this->checkDuplicate($this->object->getId()))
		{
			include_once './Services/AccessControl/classes/class.ilObjRole.php';
			$this->loadRoleProperties($this->object);
			$this->object->update();
			$rbacadmin->setProtected(
				$this->rolf_ref_id,
				$this->object->getId(),
				$this->form->getInput('pro') ? 'y' : 'n'
			);
			ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
			$this->ctrl->redirect($this,'edit');
		}
		
		ilUtil::sendFailure($this->lng->txt('err_check_input'));
		$this->form->setValuesByPost();
		$this->tpl->setContent($this->form->getHTML());
		return false;
	}
	
	/**
	 * Show template permissions
	 * @return void
	 */
	protected function permObject($a_show_admin_permissions = false)
	{
		global $ilTabs, $ilErr, $ilToolbar, $objDefinition,$rbacreview;
		
		$ilTabs->setTabActive('default_perm_settings');
		
		$this->setSubTabs('default_perm_settings');
		
		if($a_show_admin_permissions)
		{
			$ilTabs->setSubTabActive('rbac_admin_permissions');
		}
		else
		{
			$ilTabs->setSubTabActive('rbac_repository_permissions');	
		}
		
		if(!$this->checkAccess('write','edit_permission'))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->MESSAGE);
			return true;
		}
		
		// Show copy role button
		$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
		$ilToolbar->addButton(
			$this->lng->txt("adopt_perm_from_template"),
			$this->ctrl->getLinkTarget($this,'adoptPerm')
		);
		if($rbacreview->isDeleteable($this->object->getId(), $this->rolf_ref_id))
		{
			$ilToolbar->addButton(
				$this->lng->txt('rbac_delete_role'),
				$this->ctrl->getLinkTarget($this,'confirmDeleteRole')
			);
		}
		
		$this->tpl->addBlockFile(
			'ADM_CONTENT',
			'adm_content',
			'tpl.rbac_template_permissions.html',
			'Services/AccessControl'
		);
		
		$this->tpl->setVariable('PERM_ACTION',$this->ctrl->getFormAction($this));
		
		include_once './Services/Accordion/classes/class.ilAccordionGUI.php';
		$acc = new ilAccordionGUI();
		$acc->setBehaviour(ilAccordionGUI::FORCE_ALL_OPEN);
		$acc->setId('template_perm_'.$this->getParentRefId());
		
		if($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			if($a_show_admin_permissions)
			{
				$subs = $objDefinition->getSubObjectsRecursively('adm',true,true);
			}
			else
			{
				$subs = $objDefinition->getSubObjectsRecursively('root',true,$a_show_admin_permissions);
			}
		}
		else
		{
			$subs = $objDefinition->getSubObjectsRecursively($this->getParentType(),true,$a_show_admin_permissions);
		}
		
		$sorted = array();
		foreach($subs as $subtype => $def)
		{
			if($objDefinition->isPlugin($subtype))
			{
				$translation = ilPlugin::lookupTxt("rep_robj", $subtype,"obj_".$subtype);
			}
			elseif($objDefinition->isSystemObject($subtype))
			{
				$translation = $this->lng->txt("obj_".$subtype);
			}
			else
			{
				$translation = $this->lng->txt('objs_'.$subtype);
			}
			
			$sorted[$subtype] = $def;
			$sorted[$subtype]['translation'] = $translation;
		}
		
		
		$sorted = ilUtil::sortArray($sorted, 'translation','asc',true,true);
		foreach($sorted as $subtype => $def)
		{
			if($objDefinition->isPlugin($subtype))
			{
				$translation = ilPlugin::lookupTxt("rep_robj", $subtype,"obj_".$subtype);
			}
			elseif($objDefinition->isSystemObject($subtype))
			{
				$translation = $this->lng->txt("obj_".$subtype);
			}
			else
			{
				$translation = $this->lng->txt('objs_'.$subtype);
			}

			include_once 'Services/AccessControl/classes/class.ilObjectRoleTemplatePermissionTableGUI.php';
			$tbl = new ilObjectRoleTemplatePermissionTableGUI(
				$this,
				'perm',
				$this->getParentRefId(),
				$this->object->getId(),
				$subtype,
				$a_show_admin_permissions
			);
			$tbl->parse();
			
			$acc->addItem($translation, $tbl->getHTML());
		}

		$this->tpl->setVariable('ACCORDION',$acc->getHTML());
		
		// Add options table
		include_once './Services/AccessControl/classes/class.ilObjectRoleTemplateOptionsTableGUI.php';
		$options = new ilObjectRoleTemplateOptionsTableGUI(
			$this,
			'perm',
			$this->rolf_ref_id,
			$this->object->getId(),
			$a_show_admin_permissions
		);
		$options->addMultiCommand(
			$a_show_admin_permissions ? 'adminPermSave' : 'permSave',
			$this->lng->txt('save')
		);

		$options->parse();
		$this->tpl->setVariable('OPTIONS_TABLE',$options->getHTML());
	}
	
	/**
	 * Show administration permissions
	 * @return 
	 */
	protected function adminPermObject()
	{
		return $this->permObject(true);
	}
	
	/**
	 * Save admin permissions
	 * @return 
	 */
	protected function adminPermSaveObject()
	{
		return $this->permSaveObject(true);
	}

	/**
	* display permission settings template
	*
	* @access	public
	*/
	function perm2Object()
	{
		global $rbacadmin, $rbacreview, $rbacsystem, $objDefinition, $tree,$ilTabs, $ilToolbar;

		$ilTabs->setTabActive('default_perm_settings');
			
		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}
		
		
		$perm_def = $this->object->__getPermissionDefinitions();

		$rbac_objects =& $perm_def[0];
		$rbac_operations =& $perm_def[1];

		foreach ($rbac_objects as $key => $obj_data)
		{
			if ($objDefinition->isPlugin($obj_data["type"]))
			{
				$rbac_objects[$key]["name"] = ilPlugin::lookupTxt("rep_robj", $obj_data["type"],
						"obj_".$obj_data["type"]);
			}
			else
			{
				$rbac_objects[$key]["name"] = $this->lng->txt("obj_".$obj_data["type"]);
			}
			$rbac_objects[$key]["ops"] = $rbac_operations[$key];
		}
		
		// for local roles display only the permissions settings for allowed subobjects
		if ($this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			// first get object in question (parent of role folder object)
			$parent_data = $this->tree->getParentNodeData($this->rolf_ref_id);
			// get allowed subobjects of object recursively
			$subobj_data = $this->objDefinition->getSubObjectsRecursively($parent_data["type"]);
			
			// remove not allowed object types from array but keep the type definition of object itself
			foreach ($rbac_objects as $key => $obj_data)
			{
				if ($obj_data["type"] == "rolf")
				{
					unset($rbac_objects[$key]);
					continue;
				}
				
				if (!$subobj_data[$obj_data["type"]] and $parent_data["type"] != $obj_data["type"])
				{
					unset($rbac_objects[$key]);
				}
			}
		} // end if local roles
		
		// now sort computed result
		//sort($rbac_objects);
			
		/*foreach ($rbac_objects as $key => $obj_data)
		{
			sort($rbac_objects[$key]["ops"]);
		}*/
		
		// sort by (translated) name of object type
		$rbac_objects = ilUtil::sortArray($rbac_objects,"name","asc");

		// BEGIN CHECK_PERM
		foreach ($rbac_objects as $key => $obj_data)
		{
			$arr_selected = $rbacreview->getOperationsOfRole($this->object->getId(), $obj_data["type"], $this->rolf_ref_id);
			$arr_checked = array_intersect($arr_selected,array_keys($rbac_operations[$obj_data["obj_id"]]));

			foreach ($rbac_operations[$obj_data["obj_id"]] as $operation)
			{
				// check all boxes for system role
				if ($this->object->getId() == SYSTEM_ROLE_ID)
				{
					$checked = true;
					$disabled = true;
				}
				else
				{
					$checked = in_array($operation["ops_id"],$arr_checked);
					$disabled = false;
				}

				// Es wird eine 2-dim Post Variable uebergeben: perm[rol_id][ops_id]
				$box = ilUtil::formCheckBox($checked,"template_perm[".$obj_data["type"]."][]",$operation["ops_id"],$disabled);
				$output["perm"][$obj_data["obj_id"]][$operation["ops_id"]] = $box;
			}
		}
		// END CHECK_PERM

		$output["col_anz"] = count($rbac_objects);
		$output["txt_save"] = $this->lng->txt("save");
		$output["check_recursive"] = ilUtil::formCheckBox(0,"recursive",1);
		$output["text_recursive"] = $this->lng->txt("change_existing_objects");
		$output["text_recursive_desc"] = $this->lng->txt("change_existing_objects_desc");
		
		$protected_disabled = true;
		
		if ($this->rolf_ref_id == ROLE_FOLDER_ID or $rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id))
		{
			$protected_disabled = false;
		}
		
		$output["check_protected"] = ilUtil::formCheckBox($rbacreview->isProtected($this->rolf_ref_id,$this->object->getId()),
															"protected",
															1,
															$protected_disabled);
		
		$output["text_protected"] = $this->lng->txt("role_protect_permissions");
		$output["text_protected_desc"] = $this->lng->txt("role_protect_permissions_desc");

		/* send message for system role
		if ($this->object->getId() == SYSTEM_ROLE_ID)
		{
			$output["adopt"] = array();
			$output["sysrole_msg"] = $this->lng->txt("msg_sysrole_not_editable");
		}
		 */

		$output["formaction"] = $this->ctrl->getFormAction($this);

		$this->data = $output;


/************************************/
/*			generate output			*/
/************************************/

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.adm_perm_role.html',
			"Services/AccessControl");

	
		if($access and $this->object->isDeletable($this->rolf_ref_id))
		{
			$this->tpl->setVariable('LINK_DELETE_ROLE',$this->ctrl->getLinkTarget($this,'confirmDeleteRole'));
			$this->tpl->setVariable('TXT_DELETE_ROLE',$this->lng->txt('rbac_delete_role'));
			$this->tpl->setVariable('TXT_FOOTER_DELETE_ROLE',$this->lng->txt('rbac_delete_role'));
		}

		foreach ($rbac_objects as $obj_data)
		{
			// BEGIN object_operations
			$this->tpl->setCurrentBlock("object_operations");

			$ops_ids = "";

			foreach ($obj_data["ops"] as $operation)
			{
				$ops_ids[] = $operation["ops_id"];
				
				//$css_row = ilUtil::switchColor($j++, "tblrow1", "tblrow2");
				$css_row = "tblrow1";
				$this->tpl->setVariable("CSS_ROW",$css_row);
				$this->tpl->setVariable("PERMISSION",$operation["name"]);
				if (substr($operation["title"], 0, 7) == "create_")
				{
					if ($this->objDefinition->getDevMode(substr($operation["title"], 7, strlen($operation["title"]) -7)))
					{
						$this->tpl->setVariable("TXT_NOT_IMPL", "(".$this->lng->txt("not_implemented_yet").")");
					}
				}
				$this->tpl->setVariable("CHECK_PERMISSION",$this->data["perm"][$obj_data["obj_id"]][$operation["ops_id"]]);
				$this->tpl->setVariable("LABEL_ID","template_perm_".$obj_data["type"]."_".$operation["ops_id"]);
				$this->tpl->parseCurrentBlock();
			} // END object_operations

			// BEGIN object_type
			$this->tpl->setCurrentBlock("object_type");

			// add administration for adminstrative items
			if ($objDefinition->isSystemObject($obj_data["type"]) &&
				$obj_data["type"] != "root")
			{
				$this->tpl->setVariable("TXT_ADMINIS", "(".$this->lng->txt("administration").") ");
			}

			$this->tpl->setVariable("TXT_OBJ_TYPE",$obj_data["name"]);

// TODO: move this if in a function and query all objects that may be disabled or inactive
			if ($this->objDefinition->getDevMode($obj_data["type"]))
			{
				$this->tpl->setVariable("TXT_NOT_IMPL", "(".$this->lng->txt("not_implemented_yet").")");
			}
			else if ($obj_data["type"] == "icrs" and !$this->ilias->getSetting("ilinc_active"))
			{
				$this->tpl->setVariable("TXT_NOT_IMPL", "(".$this->lng->txt("not_enabled_or_configured").")");
			}

			// option: change permissions of exisiting objects of that type
			$this->tpl->setVariable("OBJ_TYPE",$obj_data["type"]);
			$this->tpl->setVariable("CHANGE_PERM_OBJ_TYPE_DESC",$this->lng->txt("change_existing_object_type_desc"));

			// use different Text for system objects		
			if ($objDefinition->isPlugin($obj_data["type"]))
			{
				$this->tpl->setVariable("CHANGE_PERM_OBJ_TYPE",$this->lng->txt("change_existing_prefix")." ".
					ilPlugin::lookupTxt("rep_robj", $obj_data["type"], "objs_".$obj_data["type"]).
					" ".$this->lng->txt("change_existing_suffix"));
			}
			else if ($objDefinition->isSystemObject($obj_data["type"]))
			{
				$this->tpl->setVariable("CHANGE_PERM_OBJ_TYPE",$this->lng->txt("change_existing_prefix_single")." ".$this->lng->txt("obj_".$obj_data["type"])." ".$this->lng->txt("change_existing_suffix_single"));

			}
			else
			{
				$this->tpl->setVariable("CHANGE_PERM_OBJ_TYPE",$this->lng->txt("change_existing_prefix")." ".$this->lng->txt("objs_".$obj_data["type"])." ".$this->lng->txt("change_existing_suffix"));
			}

			// js checkbox toggles
			$this->tpl->setVariable("JS_VARNAME","template_perm_".$obj_data["type"]);
			$this->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($ops_ids));
			$this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));			
			
			$this->tpl->parseCurrentBlock();
			// END object_type
		}

		// don't display adopt permissions form for system role
		if ($this->object->getId() != SYSTEM_ROLE_ID)
		{
			$this->tpl->setCurrentBlock("tblfooter_special_options");
			$this->tpl->setVariable("TXT_PERM_SPECIAL_OPTIONS",$this->lng->txt("perm_special_options"));
			$this->tpl->parseCurrentBlock();
		
			$this->tpl->setCurrentBlock("tblfooter_recursive");
			$this->tpl->setVariable("COL_ANZ",3);
			$this->tpl->setVariable("CHECK_RECURSIVE",$this->data["check_recursive"]);
			$this->tpl->setVariable("TXT_RECURSIVE",$this->data["text_recursive"]);
			$this->tpl->setVariable("TXT_RECURSIVE_DESC",$this->data["text_recursive_desc"]);
			$this->tpl->parseCurrentBlock();
			
			$this->tpl->setCurrentBlock("tblfooter_protected");
			$this->tpl->setVariable("COL_ANZ",3);
			$this->tpl->setVariable("CHECK_PROTECTED",$this->data["check_protected"]);
			$this->tpl->setVariable("TXT_PROTECTED",$this->data["text_protected"]);
			$this->tpl->setVariable("TXT_PROTECTED_DESC",$this->data["text_protected_desc"]);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tblfooter_standard");
			$this->tpl->setVariable("COL_ANZ_PLUS",3);
			$this->tpl->setVariable("TXT_SAVE",$this->data["txt_save"]);
			$this->tpl->parseCurrentBlock();

			// Show copy role button
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			$ilToolbar->addButton($this->lng->txt("adopt_perm_from_template"),$this->ctrl->getLinkTarget($this,'adoptPerm'));
		}
		else
		{
			// display form buttons not for system role
			$this->tpl->setCurrentBlock("tblfooter_sysrole");
			$this->tpl->setVariable("COL_ANZ_SYS",3);
			$this->tpl->parseCurrentBlock();

			// display sysrole_msg
			$this->tpl->setCurrentBlock("sysrole_msg");
			$this->tpl->setVariable("TXT_SYSROLE_MSG",$this->data["sysrole_msg"]);
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath("icon_".$this->object->getType().".gif"));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt($this->object->getType()));
		$this->tpl->setVariable("TBL_HELP_IMG",ilUtil::getImagePath("icon_help.gif"));
		$this->tpl->setVariable("TBL_HELP_LINK","tbl_help.php");
		$this->tpl->setVariable("TBL_HELP_IMG_ALT",$this->lng->txt("help"));
		
		// compute additional information in title
		$global_roles = $rbacreview->getGlobalRoles();
		
		if (in_array($this->object->getId(),$global_roles))
		{
			$desc = "global";
		}
		else
		{
			// description for autogenerated roles
			if($rolf = $rbacreview->getFoldersAssignedToRole($this->object->getId(),true))
			{
				$parent_node = $this->tree->getParentNodeData($rolf[0]);
				$desc = $this->lng->txt("obj_".$parent_node['type'])." (#".$parent_node['obj_id'].") : ".$parent_node['title'];
			}
		}
		
		$description = "&nbsp;<span class=\"small\">(".$desc.")</span>";

		// translation for autogenerated roles
		if (substr($this->object->getTitle(),0,3) == "il_")
		{
			$title = ilObjRole::_getTranslation($this->object->getTitle())." (".$this->object->getTitle().")";
		}
		else
		{
			$title = $this->object->getTitle();
		}

		$this->tpl->setVariable("TBL_TITLE",$title.$description);

		// info text
		$pid = $tree->getParentId($this->rolf_ref_id);
		$ptitle = ilObject::_lookupTitle(ilObject::_lookupObjId($pid));
		if ($this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			$info = sprintf($this->lng->txt("perm_role_info_1"),
				$this->object->getTitle(), $ptitle)." ".
				sprintf($this->lng->txt("perm_role_info_2"),
				$this->object->getTitle(), $ptitle);
		}
		else
		{
			$info = sprintf($this->lng->txt("perm_role_info_glob_1"),
				$this->object->getTitle(), $ptitle)." ".
				sprintf($this->lng->txt("perm_role_info_glob_2"),
				$this->object->getTitle(), $ptitle);
		}
		$this->tpl->setVariable("TXT_TITLE_INFO", $info);
		
		$this->tpl->setVariable("TXT_PERMISSION",$this->data["txt_permission"]);
		$this->tpl->setVariable("FORMACTION",$this->data["formaction"]);
		$this->tpl->parseCurrentBlock();
	}

	protected function adoptPermObject()
	{
		global $rbacreview;

		$output = array();
		
		$parent_role_ids = $rbacreview->getParentRoleIds($this->rolf_ref_id,true);
		$ids = array();
		foreach($parent_role_ids as $id => $tmp)
		{
			$ids[] = $id;
		}

		// Sort ids
		$sorted_ids = ilUtil::_sortIds($ids,'object_data','type,title','obj_id');
		$key = 0;
		foreach($sorted_ids as $id)
		{
			$par = $parent_role_ids[$id];
			if ($par["obj_id"] != SYSTEM_ROLE_ID && $this->object->getId() != $par["obj_id"])
			{
				$radio = ilUtil::formRadioButton(0,"adopt",$par["obj_id"]);
				$output["adopt"][$key]["css_row_adopt"] = ($key % 2 == 0) ? "tblrow1" : "tblrow2";
				$output["adopt"][$key]["check_adopt"] = $radio;
				$output["adopt"][$key]["role_id"] = $par["obj_id"];
				$output["adopt"][$key]["type"] = ($par["type"] == 'role' ? $this->lng->txt('obj_role') : $this->lng->txt('obj_rolt'));
				$output["adopt"][$key]["role_name"] = ilObjRole::_getTranslation($par["title"]);
				$output["adopt"][$key]["role_desc"] = $par["desc"];
				$key++;
			}
		}

		$output["formaction_adopt"] = $this->ctrl->getFormAction($this);
		$output["message_middle"] = $this->lng->txt("adopt_perm_from_template");


		$tpl = new ilTemplate("tpl.adm_copy_role.html", true, true, "Services/AccessControl");

		$tpl->setCurrentBlock("ADOPT_PERM_ROW");
		foreach ($output["adopt"] as $key => $value)
		{
			$tpl->setVariable("CSS_ROW_ADOPT",$value["css_row_adopt"]);
			$tpl->setVariable("CHECK_ADOPT",$value["check_adopt"]);
			$tpl->setVariable("LABEL_ID",$value["role_id"]);
			$tpl->setVariable("TYPE",$value["type"]);
			$tpl->setVariable("ROLE_NAME",$value["role_name"]);
			if(strlen($value['role_desc']))
			{
				$tpl->setVariable('ROLE_DESC',$value['role_desc']);
			}
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->setVariable("MESSAGE_MIDDLE",$output["message_middle"]);
		$tpl->setVariable("FORMACTION_ADOPT",$output["formaction_adopt"]);
		$tpl->setVariable("ADOPT",$this->lng->txt('copy'));
		$tpl->setVariable("CANCEL",$this->lng->txt('cancel'));

		$tpl->setVariable('HEAD_ROLE',$this->lng->txt('title'));
		$tpl->setVariable('HEAD_TYPE',$this->lng->txt('type'));

		$this->tpl->setContent($tpl->get());
	}
	
	/**
	 * Show delete confirmation screen
	 * @return 
	 */
	protected function confirmDeleteRoleObject()
	{
		global $ilErr,$rbacreview,$ilUser;
		
		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->WARNING);
		}

		$question = $this->lng->txt('rbac_role_delete_qst');
		if($rbacreview->isAssigned($ilUser->getId(), $this->object->getId()))
		{
			$question .= ('<br />'.$this->lng->txt('rbac_role_delete_self'));
		}
		ilUtil::sendQuestion($question);
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setHeaderText($question);
		$confirm->setCancel($this->lng->txt('cancel'), 'perm');
		$confirm->setConfirm($this->lng->txt('rbac_delete_role'), 'performDeleteRole');
		
		$confirm->addItem(
			'role',
			$this->object->getId(),
			$this->object->getTitle(),
			ilUtil::getImagePath('icon_role.gif')
		);
		
		$this->tpl->setContent($confirm->getHTML());
		return true;				
	}

	
	/**
	 * Delete role
	 * @return 
	 */
	protected function performDeleteRoleObject()
	{
		global $ilErr;

		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_perm'),$ilErr->WARNING);
		}
		
		$this->object->setParent((int) $_GET['rolf_ref_id']);
		$this->object->delete();
		ilUtil::sendSuccess($this->lng->txt('msg_deleted_role'),true);
		
		if($back = $this->getBackTarget())
		{
			ilUtil::redirect($back['link']);
		}
		else
		{
			$this->ctrl->returnToParent($this);
		}
	}

	/**
	* save permissions
	* 
	* @access	public
	*/
	function permSaveObject($a_show_admin_permissions = false)
	{
		global $rbacsystem, $rbacadmin, $rbacreview, $objDefinition, $tree;

		// for role administration check write of global role folder
		/*
		if ($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$access = $rbacsystem->checkAccess('write',$this->rolf_ref_id);
		}
		else	// for local roles check 'edit permission' of parent object of the local role folder
		{
			$access = $rbacsystem->checkAccess('edit_permission',$tree->getParentId($this->rolf_ref_id));
		}
		*/
		$access = $this->checkAccess('visible,write','edit_permission');
			
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

		// rbac log
		include_once "Services/AccessControl/classes/class.ilRbacLog.php";
		$rbac_log_active = ilRbacLog::isActive();
		if($rbac_log_active)
		{
			$rbac_log_old = ilRbacLog::gatherTemplate($this->rolf_ref_id, $this->object->getId());
		}

		// delete all template entries of enabled types
		if($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			if($a_show_admin_permissions)
			{
				$subs = $objDefinition->getSubObjectsRecursively('adm',true,true);
			}
			else
			{
				$subs = $objDefinition->getSubObjectsRecursively('root',true,false);
			}
		}
		else
		{
			$subs = $objDefinition->getSubObjectsRecursively($this->getParentType(),true,false);
		}
		
		foreach($subs as $subtype => $def)
		{
			// Delete per object type
			$rbacadmin->deleteRolePermission($this->object->getId(),$this->rolf_ref_id,$subtype);
		}

		if (empty($_POST["template_perm"]))
		{
			$_POST["template_perm"] = array();
		}

		foreach ($_POST["template_perm"] as $key => $ops_array)
		{
			// sets new template permissions
			$rbacadmin->setRolePermission($this->object->getId(), $key, $ops_array, $this->rolf_ref_id);
		}

		if($rbac_log_active)
		{
			$rbac_log_new = ilRbacLog::gatherTemplate($this->rolf_ref_id, $this->object->getId());
			$rbac_log_diff = ilRbacLog::diffTemplate($rbac_log_old, $rbac_log_new);
			ilRbacLog::add(ilRbacLog::EDIT_TEMPLATE, $this->obj_ref_id, $rbac_log_diff);
		}

		// update object data entry (to update last modification date)
		$this->object->update();
		
		// set protected flag
		if ($this->rolf_ref_id == ROLE_FOLDER_ID or $rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id))
		{
			$rbacadmin->setProtected($this->rolf_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST['protected']));
		}
		
		if($a_show_admin_permissions)
		{
			$_POST['recursive'] = true;
		}
		
		// Redirect if Change existing objects is not chosen
		if(!$_POST['recursive'] and !is_array($_POST['recursive_list']))
		{
			ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
			if($a_show_admin_permissions)
			{
				$this->ctrl->redirect($this,'adminPerm');
			}
			else
			{
				$this->ctrl->redirect($this,'perm');
			}
		}
		// New implementation
		if($this->isChangeExistingObjectsConfirmationRequired() and !$a_show_admin_permissions)
		{
			$this->showChangeExistingObjectsConfirmation();
			return true;
		}
		
		$start = ($this->rolf_ref_id == ROLE_FOLDER_ID ? ROOT_FOLDER_ID : $tree->getParentId($this->rolf_ref_id));
		if($a_show_admin_permissions)
		{
			$start = $tree->getParentId($this->rolf_ref_id);
		}

		if($_POST['protected'])
		{
			$this->object->changeExistingObjects(
				$start,
				ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
				array('all'),
				array()
				#$a_show_admin_permissions ? array('adm') : array()
			);
		}
		else
		{
			$this->object->changeExistingObjects(
				$start,
				ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
				array('all'),
				array()
				#$a_show_admin_permissions ? array('adm') : array()
			);
		}
		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
		
		if($a_show_admin_permissions)
		{
			$this->ctrl->redirect($this,'adminPerm');
		}
		else
		{
			$this->ctrl->redirect($this,'perm');
		}
		return true;
	}


	/**
	* copy permissions from role
	* 
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		global $rbacadmin, $rbacsystem, $rbacreview, $tree;

		if(!$_POST['adopt'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->adoptPermObject();
			return false;
		}
	
		$access = $this->checkAccess('visible,write','edit_permission');
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

		if ($this->object->getId() == $_POST["adopt"])
		{
			ilUtil::sendFailure($this->lng->txt("msg_perm_adopted_from_itself"),true);
		}
		else
		{
			$rbacadmin->deleteRolePermission($this->object->getId(), $this->rolf_ref_id);
			$parentRoles = $rbacreview->getParentRoleIds($this->rolf_ref_id,true);
			$rbacadmin->copyRoleTemplatePermissions(
				$_POST["adopt"],
				$parentRoles[$_POST["adopt"]]["parent"],
				$this->rolf_ref_id,
				$this->object->getId(),
				false);		

			// update object data entry (to update last modification date)
			$this->object->update();

			// send info
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_POST["adopt"]);
			ilUtil::sendSuccess($this->lng->txt("msg_perm_adopted_from1")." '".$obj_data->getTitle()."'.<br/>".
					 $this->lng->txt("msg_perm_adopted_from2"),true);
		}

		$this->ctrl->redirect($this, "perm");
	}

	/**
	* wrapper for renamed function
	*
	* @access	public
	*/
	function assignSaveObject()
	{
        $this->assignUserObject();
    }


	
	/**
	 * Assign user (callback from ilRepositorySearchGUI) 
	 * @param	array	$a_user_ids		Array of user ids
	 * @return
	 */
	public function addUserObject($a_user_ids)
	{
		global $rbacreview,$rbacadmin;
		
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			ilUtil::sendFailure($this->lng->txt('msg_no_perm_assign_user_to_role'),true);
			return false;
		}
		if(!$rbacreview->isAssignable($this->object->getId(),$this->rolf_ref_id) &&
			$this->rolf_ref_id != ROLE_FOLDER_ID)
		{
			ilUtil::sendFailure($this->lng->txt('err_role_not_assignable'),true);
			return false;
		}
		if(!$a_user_ids)
		{
			$GLOBALS['lng']->loadLanguageModule('search');
			ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'),true);
			return false;
		}

		$assigned_users_all = $rbacreview->assignedUsers($this->object->getId());
				
		// users to assign
		$assigned_users_new = array_diff($a_user_ids,array_intersect($a_user_ids,$assigned_users_all));
		
		// selected users all already assigned. stop
        if (count($assigned_users_new) == 0)
		{
			ilUtil::sendFailure($this->lng->txt("rbac_msg_user_already_assigned"),true);
			return false;
		}
		
		// assign new users
        foreach ($assigned_users_new as $user)
		{
			$rbacadmin->assignUser($this->object->getId(),$user,false);
        }
        
    	// update object data entry (to update last modification date)
		$this->object->update();

		ilUtil::sendSuccess($this->lng->txt("msg_userassignment_changed"),true);
		$this->ctrl->redirect($this,'userassignment');
	}
	
	/**
	* de-assign users from role
	*
	* @access	public
	*/
	function deassignUserObject()
	{
    	global $rbacsystem, $rbacadmin, $rbacreview;

		#if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

    	$selected_users = ($_POST["user_id"]) ? $_POST["user_id"] : array($_GET["user_id"]);

		if ($selected_users[0]=== NULL)
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// prevent unassignment of system user from system role
		if ($this->object->getId() == SYSTEM_ROLE_ID)
		{
            if ($admin = array_search(SYSTEM_USER_ID,$selected_users) !== false)
			    unset($selected_users[$admin]);
		}

		// check for each user if the current role is his last global role before deassigning him
		$last_role = array();
		$global_roles = $rbacreview->getGlobalRoles();
		
		foreach ($selected_users as $user)
		{
			$assigned_roles = $rbacreview->assignedRoles($user);
			$assigned_global_roles = array_intersect($assigned_roles,$global_roles);

			if (count($assigned_roles) == 1 or (count($assigned_global_roles) == 1 and in_array($this->object->getId(),$assigned_global_roles)))
			{
				$userObj = $this->ilias->obj_factory->getInstanceByObjId($user);
				$last_role[$user] = $userObj->getFullName();
				unset($userObj);
			}
		}

		
		// ... else perform deassignment
		foreach ($selected_users as $user)
        {
			if(!isset($last_role[$user]))
			{
				$rbacadmin->deassignUser($this->object->getId(), $user);
			}
		}

    	// update object data entry (to update last modification date)
		$this->object->update();

		// raise error if last role was taken from a user...
		if(count($last_role))
		{
			$user_list = implode(", ",$last_role);
			ilUtil::sendFailure($this->lng->txt('msg_is_last_role').': '.$user_list.'<br />'.$this->lng->txt('msg_min_one_role'),true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("msg_userassignment_changed"), true);
		}
		$this->ctrl->redirect($this,'userassignment');
	}
	
	/**
	* update role object
	* 
	* @access	public
	*/
	function updateObject2()
	{
		global $rbacsystem, $rbacreview, $rbacadmin, $tree;
		require_once 'Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php';
		require_once './Services/Utilities/classes/class.ilFormat.php';

		// for role administration check write of global role folder
		/*
		if ($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			$access = $rbacsystem->checkAccess('write',$this->rolf_ref_id);
		}
		else	// for local roles check 'edit permission' of parent object of the local role folder
		{
			$access = $rbacsystem->checkAccess('edit_permission',$tree->getParentId($this->rolf_ref_id));
		}
		*/
		$access = $this->checkAccess('write','edit_permission');	
		if (!$access)
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_modify_role"),$this->ilias->error_obj->MESSAGE);
		}

		if (substr($this->object->getTitle(),0,3) != "il_")
		{
			// check required fields
			if (empty($_POST["Fobject"]["title"]))
			{
				$this->ilias->raiseError($this->lng->txt("fill_out_all_required_fields"),$this->ilias->error_obj->MESSAGE);
			}
	
			// check if role title has il_ prefix
			if (substr($_POST["Fobject"]["title"],0,3) == "il_")
			{
				$this->ilias->raiseError($this->lng->txt("msg_role_reserved_prefix"),$this->ilias->error_obj->MESSAGE);
			}
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				// check if disk quota is empty or is numeric and positive
				if (! is_numeric(trim($_POST["Fobject"]["disk_quota"])) ||
						trim($_POST["Fobject"]["disk_quota"]) < 0
				)
				{
					$this->ilias->raiseError($this->lng->txt("msg_disk_quota_illegal_value"),$this->ilias->error_obj->MESSAGE);
				}
			}


	
			// update
			$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
			$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
			if (ilDiskQuotaActivationChecker::_isActive())
			{
				$this->object->setDiskQuota($_POST["Fobject"]["disk_quota"] * ilFormat::_getSizeMagnitude() * ilFormat::_getSizeMagnitude());
			}
		}


		
		// ensure that at least one role is available in the new user register form if registration is enabled
		if ($_POST["Fobject"]["allow_register"] == "")
		{
			$roles_allowed = $this->object->_lookupRegisterAllowed();

			if (count($roles_allowed) == 1 and $roles_allowed[0]['id'] == $this->object->getId())
			{
				$this->ilias->raiseError($this->lng->txt("msg_last_role_for_registration"),$this->ilias->error_obj->MESSAGE);
			}	
		}

		$this->object->setAllowRegister($_POST["Fobject"]["allow_register"]);
		$this->object->toggleAssignUsersStatus($_POST["Fobject"]["assign_users"]);
		$rbacadmin->setProtected($this->rolf_ref_id,$this->object->getId(),ilUtil::tf2yn($_POST["Fobject"]["protect_permissions"]));	
		$this->object->update();
		
		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);

		$this->ctrl->redirect($this,'edit');
	}
	
	
	/**
	* display user assignment panel
	*/
	function userassignmentObject()
	{
		global $rbacreview, $rbacsystem, $lng;
		
		//if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->tabs_gui->setTabActive('user_assignment');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.rbac_ua.html','Services/AccessControl');
		
		include_once './Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php';
		$tb = new ilToolbarGUI();

		// add member
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$tb,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'submit_name'			=> $lng->txt('add')
			)
		);

/*		include_once("./Services/Form/classes/class.ilUserLoginAutoCompleteInputGUI.php");
		$ul = new ilUserLoginAutoCompleteInputGUI($lng->txt("user"), "user_login", $this, "assignUserAutoComplete");
		$ul->setSize(15);
		$tb->addInputItem($ul, true);

		// add button
		$tb->addFormButton($lng->txt("add"), "assignUser");
*/
		$tb->addSpacer();

		$tb->addButton(
			$this->lng->txt('search_user'),
			$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start')
		);
		$tb->addSpacer();
		$tb->addButton(
			$this->lng->txt('role_mailto'),
			$this->ctrl->getLinkTarget($this,'mailToRole')
		);
		$this->tpl->setVariable('BUTTONS_UA',$tb->getHTML());
		
		include_once './Services/AccessControl/classes/class.ilAssignedUsersTableGUI.php';
		$ut = new ilAssignedUsersTableGUI($this,'userassignment',$this->object->getId());
		
		$this->tpl->setVariable('TABLE_UA',$ut->getHTML());
		
		return true;
		
    }


	
	function __showAssignedUsersTable($a_result_set,$a_user_ids = NULL)
	{
        global $rbacsystem;

		$actions = array("deassignUser"  => $this->lng->txt("remove"));

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();
		
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button add user
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('role_add_user'));
		$this->tpl->parseCurrentBlock();
		
		$this->__showButton('mailToRole',$this->lng->txt('role_mailto'),'_blank');
		
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		foreach ($actions as $name => $value)
		{
			$tpl->setCurrentBlock("tbl_action_btn");
			$tpl->setVariable("BTN_NAME",$name);
			$tpl->setVariable("BTN_VALUE",$value);
			$tpl->parseCurrentBlock();
		}
			
		if (!empty($a_user_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user_id");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

        $tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$this->ctrl->setParameter($this,"cmd","userassignment");

		// title & header columns
		$tbl->setTitle($this->lng->txt("assigned_users"),"icon_usr.gif",$this->lng->txt("users"));

		//user must be administrator
		$tbl->setHeaderNames(array("",$this->lng->txt("username"),$this->lng->txt("firstname"),
			$this->lng->txt("lastname"),$this->lng->txt("grp_options")));
		$tbl->setHeaderVars(array("","login","firstname","lastname","functions"),
			$this->ctrl->getParameterArray($this,"",false));
		$tbl->setColumnWidth(array("","20%","25%","25%","30%"));
		
		$this->__setTableGUIBasicData($tbl,$a_result_set,"userassignment");
		$tbl->render();
		$this->tpl->setVariable("ADM_CONTENT",$tbl->tpl->get());

		return true;
	}

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}

	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			case "group":
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				break;

			case "role":
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				break;

			default:
				// init sort_by (unfortunatly sort_by is preset with 'title')
	           	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
                {
                    $_GET["sort_by"] = "login";
                }
                $order = $_GET["sort_by"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}

	function searchUserFormObject()
	{
		global $rbacsystem;

		//if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

		$this->lng->loadLanguageModule('search');

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.role_users_search.html","Services/AccessControl");

		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("role_search_users"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["role_search_str"] ? $_SESSION["role_search_str"] : "");
		$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER",$this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE",$this->lng->txt("exc_roles"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("exc_groups"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));

        $usr = ($_POST["search_for"] == "usr" || $_POST["search_for"] == "") ? 1 : 0;
		$grp = ($_POST["search_for"] == "grp") ? 1 : 0;
		$role = ($_POST["search_for"] == "role") ? 1 : 0;

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton($usr,"search_for","usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE",ilUtil::formRadioButton($role,"search_for","role"));
        $this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton($grp,"search_for","grp"));

		$this->__unsetSessionVariables();
	}

	function __unsetSessionVariables()
	{
		unset($_SESSION["role_delete_member_ids"]);
		unset($_SESSION["role_delete_subscriber_ids"]);
		unset($_SESSION["role_search_str"]);
		unset($_SESSION["role_search_for"]);
		unset($_SESSION["role_role"]);
		unset($_SESSION["role_group"]);
		unset($_SESSION["role_archives"]);
	}

	/**
	* cancelObject is called when an operation is canceled, method links back
	* @access	public
	*/
	function cancelObject()
	{
		if ($_GET["new_type"] != "role")
		{
			$this->ctrl->redirect($this, "userassignment");
		}
		else
		{
			$this->ctrl->redirectByClass("ilobjrolefoldergui","view");
		}
	}

	function searchObject()
	{
		global $rbacsystem, $tree;

		#if (!$rbacsystem->checkAccess("edit_userassignment", $this->rolf_ref_id))
		if(!$this->checkAccess('edit_userassignment','edit_permission'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_assign_user_to_role"),$this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["role_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["role_search_str"];
		$_SESSION["role_search_for"] = $_POST["search_for"] = $_POST["search_for"] ? $_POST["search_for"] : $_SESSION["role_search_for"];

		if (!isset($_POST["search_for"]) or !isset($_POST["search_str"]))
		{
			ilUtil::sendFailure($this->lng->txt("role_search_enter_search_string"));
			$this->searchUserFormObject();

			return false;
		}

		if (!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]),$_POST["search_for"])))
		{
			ilUtil::sendInfo($this->lng->txt("role_no_results_found"));
			$this->searchUserFormObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_usr_selection.html", "Services/AccessControl");
		$this->__showButton("searchUserForm",$this->lng->txt("role_new_search"));

		$counter = 0;
		$f_result = array();

		switch($_POST["search_for"])
		{
        	case "usr":
				foreach($result as $user)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
					{
						continue;
					}
					
					$user_ids[$counter] = $user["id"];
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result,$user_ids);

				return true;

			case "role":
				foreach($result as $role)
				{
                    // exclude anonymous role
                    if ($role["id"] == ANONYMOUS_ROLE_ID)
                    {
                        continue;
                    }

                    if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($role["id"],false))
					{
						continue;
					}

				    // exclude roles with no users assigned to
                    if ($tmp_obj->getCountMembers() == 0)
                    {
                        continue;
                    }

					$role_ids[$counter] = $role["id"];

					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset($tmp_obj);
					++$counter;
				}

				$this->__showSearchRoleTable($f_result,$role_ids);

				return true;

			case "grp":
				foreach($result as $group)
				{
					if(!$tree->isInTree($group["id"]))
					{
						continue;
					}

					if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group["id"],false))
					{
						continue;
					}

                    // exclude myself :-)
                    if ($tmp_obj->getId() == $this->object->getId())
                    {
                        continue;
                    }

					$grp_ids[$counter] = $group["id"];

					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$group["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchGroupTable($f_result,$grp_ids);

				return true;
		}
	}

	function __search($a_search_string,$a_search_for)
	{
		include_once("./Services/Search/classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");
		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => $a_search_for));
		$search->setSearchType('new');

		if ($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUserForm");
		}

		return $search->getResultByType($a_search_for);
	}

	function __showSearchUserTable($a_result_set,$a_user_ids = NULL,$a_cmd = "search")
	{
        $return_to  = "searchUserForm";

    	if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup")
    	{
            $return_to = "search";
        }

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",$return_to);
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","assignUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		if (!empty($a_user_ids))
		{		
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("role_header_edit_users"),"icon_usr.gif",$this->lng->txt("role_header_edit_users"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname"),
							$this->ctrl->getParameterArray($this,$a_cmd,false));
			//array("ref_id" => $this->rolf_ref_id,
			//  "obj_id" => $this->object->getId(),
			// "cmd" => $a_cmd,
			//"cmdClass" => "ilobjrolegui",
			// "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","33%","33%","33%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchRoleTable($a_result_set,$a_role_ids = NULL)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersRole");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("role_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_role_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","role");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("role_header_edit_users"),"icon_usr.gif",$this->lng->txt("role_header_edit_users"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_role"),
								   $this->lng->txt("role_count_users")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							$this->ctrl->getParameterArray($this,"search",false));
			//array("ref_id" => $this->rolf_ref_id,
			//"obj_id" => $this->object->getId(),
			//"cmd" => "search",
			//"cmdClass" => "ilobjrolegui",
			//"cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"role");
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSearchGroupTable($a_result_set,$a_grp_ids = NULL)
	{
    	$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUserForm");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("grp_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_grp_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","group");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_grp_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("grp_header_edit_members"),"icon_usr.gif",$this->lng->txt("grp_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("grp_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->rolf_ref_id,
                                  "obj_id" => $this->object->getId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjrolegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview;

		$_SESSION["role_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["role_role"];

		if (!is_array($_POST["role"]))
		{
			ilUtil::sendFailure($this->lng->txt("role_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_usr_selection.html", "Services/AccessControl");
		$this->__showButton("searchUserForm",$this->lng->txt("role_new_search"));

		// GET ALL MEMBERS
		$members = array();

		foreach ($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();

		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
			
			// TODO: exclude anonymous user
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();

			unset($tmp_obj);
			++$counter;
		}

		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}

	function listUsersGroupObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["role_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["role_group"];

		if (!is_array($_POST["group"]))
		{
			ilUtil::sendFailure($this->lng->txt("role_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.role_usr_selection.html", "Services/AccessControl");
		$this->__showButton("searchUserForm",$this->lng->txt("role_new_search"));

		// GET ALL MEMBERS
		$members = array();

		foreach ($_POST["group"] as $group_id)
		{
			if (!$tree->isInTree($group_id))
			{
				continue;
			}
			if (!$tmp_obj = ilObjectFactory::getInstanceByRefId($group_id))
			{
				continue;
			}

			$members = array_merge($tmp_obj->getGroupMemberIds(),$members);

			unset($tmp_obj);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();

		foreach($members as $user)
		{
			if (!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;			

			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getFirstname();
			$f_result[$counter][] = $tmp_obj->getLastname();

			unset($tmp_obj);
			++$counter;
		}

		$this->__showSearchUserTable($f_result,$user_ids,"listUsersGroup");

		return true;
	}


	function __formatPath($a_path_arr)
	{
		$counter = 0;

		foreach ($a_path_arr as $data)
		{
			if ($counter++)
			{
				$path .= " -> ";
			}

			$path .= $data['title'];
		}

		if (strlen($path) > 50)
		{
			return '...'.substr($path,-50);
		}

		return $path;
	}

	function __prepareOutput()
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output locator
		//$this->__setLocator();

		// output message
		if ($this->message)
		{
			ilUtil::sendInfo($this->message);
		}

		// display infopanel if something happened
		ilUtil::infoPanel();

		// set header
		$this->__setHeader();
	}

	function __setHeader()
	{
		$this->tpl->setTitle($this->lng->txt('role'));
		$this->tpl->setDescription($this->object->getTitle());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_role.gif"));

		$this->getTabs($this->tabs_gui);
	}

	function __setLocator()
	{
		global $tree, $ilCtrl;
		
		return;
		
		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html", "Services/Locator");

		$counter = 0;

		foreach ($tree->getPathFull($this->rolf_ref_id) as $key => $row)
		{
			if ($counter++)
			{
				$this->tpl->touchBlock('locator_separator_prefix');
			}

			$this->tpl->setCurrentBlock("locator_item");

			if ($row["type"] == 'rolf')
			{
				$this->tpl->setVariable("ITEM",$this->object->getTitle());
				$this->tpl->setVariable("LINK_ITEM",$this->ctrl->getLinkTarget($this));
			}
			elseif ($row["child"] != $tree->getRootId())
			{
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $row["child"]);
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM",
					$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
			}
			else
			{
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $row["child"]);
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				$this->tpl->setVariable("LINK_ITEM",
					$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
			}
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}
	
	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
	{
		global $ilLocator;

		if ($_GET["admin_mode"] == "settings"
			&& $_GET["ref_id"] == ROLE_FOLDER_ID)	// system settings
		{		
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));

			$ilLocator->addItem($this->lng->txt("obj_".ilObject::_lookupType(
				ilObject::_lookupObjId($_GET["ref_id"]))),
				$this->ctrl->getLinkTargetByClass("ilobjrolefoldergui", "view"));
			
			if ($_GET["obj_id"] > 0)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "view"));
			}
		}
		else							// repository administration
		{
			// ?
		}
	}
	
	function showUpperIcon()
	{
	}



	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$rbacreview;

		$base_role_folder = $rbacreview->getFoldersAssignedToRole($this->object->getId(),true);
		
//var_dump($base_role_folder);
//echo "-".$this->rolf_ref_id."-";

		$activate_role_edit = false;
		
		// todo: activate the following (allow editing of local roles in
		// roles administration)
		//if (in_array($this->rolf_ref_id,$base_role_folder))
		if (in_array($this->rolf_ref_id,$base_role_folder) ||
			(strtolower($_GET["baseClass"]) == "iladministrationgui" &&
			$_GET["admin_mode"] == "settings"))
		{
			$activate_role_edit = true;
		}

		// not so nice (workaround for using tabs in repository)
		$tabs_gui->clearTargets();

		if ($this->back_target != "")
		{
			$tabs_gui->setBackTarget(
				$this->back_target["text"],$this->back_target["link"]);
		}

		if($this->checkAccess('write','edit_permission') && $activate_role_edit)
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit","update"), get_class($this));
		}
/*
		if($this->checkAccess('write','edit_permission') and $this->showDefaultPermissionSettings())
		{
			$force_active = ($_GET["cmd"] == "perm" || $_GET["cmd"] == "")
				? true
				: false;
			$tabs_gui->addTarget("default_perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), array("perm", "adoptPermSave", "permSave"),
				get_class($this),
				"", $force_active);
		}
*/
		if($this->checkAccess('write','edit_permission') and $this->showDefaultPermissionSettings())
		{
			$tabs_gui->addTarget(
				"default_perm_settings",
				$this->ctrl->getLinkTarget($this, "perm"), array(),get_class($this)
			);
		}

		if($this->checkAccess('write','edit_permission') && $activate_role_edit && $this->object->getId() != ANONYMOUS_ROLE_ID)
		{
			$tabs_gui->addTarget("user_assignment",
				$this->ctrl->getLinkTarget($this, "userassignment"),
				array("deassignUser", "userassignment", "assignUser", "searchUserForm", "search"),
				get_class($this));
		}

		if($this->checkAccess('write','edit_permission') && $activate_role_edit  && $this->object->getId() != ANONYMOUS_ROLE_ID)
		{
			$tabs_gui->addTarget("desktop_items",
				$this->ctrl->getLinkTarget($this, "listDesktopItems"),
				array("listDesktopItems", "deleteDesktopItems", "selectDesktopItem", "askDeleteDesktopItem"),
				get_class($this));
		}
	}

	function mailToRoleObject()
	{
		global $rbacreview;
		
		$obj_ids = ilObject::_getIdsForTitle($this->object->getTitle(), $this->object->getType());		
		if(count($obj_ids) > 1)
		{
			$_SESSION['mail_roles'][] = '#il_role_'.$this->object->getId();
		}
		else
		{		
			$_SESSION['mail_roles'][] = $rbacreview->getRoleMailboxAddress($this->object->getId());
		}

        require_once 'Services/Mail/classes/class.ilMailFormCall.php';
        $script = ilMailFormCall::_getRedirectTarget($this, 'userassignment', array(), array('type' => 'role'));
		ilUtil::redirect($script);
	}
	
	function checkAccess($a_perm_global,$a_perm_obj = '')
	{
		global $rbacsystem,$ilAccess;
		
		$a_perm_obj = $a_perm_obj ? $a_perm_obj : $a_perm_global;
		
		if($this->rolf_ref_id == ROLE_FOLDER_ID)
		{
			return $rbacsystem->checkAccess($a_perm_global,$this->rolf_ref_id);
		}
		else
		{
			return $ilAccess->checkAccess($a_perm_obj,'',$this->obj_ref_id);
		}
	}
	
	/**
	 * Check if a confirmation about further settings is required or not
	 * @return bool
	 */
	protected function isChangeExistingObjectsConfirmationRequired()
	{
		global $rbacreview;
		
		if(!(int) $_POST['recursive'] and !is_array($_POST['recursive_list']))
		{
			return false;
		}
		
		// Role is protected
		if($rbacreview->isProtected($this->rolf_ref_id, $this->object->getId()))
		{
			// TODO: check if recursive_list is enabled
			// and if yes: check if inheritance is broken for the relevant object types
			return count($rbacreview->getFoldersAssignedToRole($this->object->getId())) > 1;
		}
		else
		{
			// TODO: check if recursive_list is enabled
			// and if yes: check if inheritance is broken for the relevant object types
			return count($rbacreview->getFoldersAssignedToRole($this->object->getId())) > 1;
		}
	}
	
	/**
	 * Show confirmation screen
	 * @return 
	 */
	protected function showChangeExistingObjectsConfirmation()
	{
		$protected = $_POST['protected'];
		
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'changeExistingObjects'));
		$form->setTitle($this->lng->txt('rbac_change_existing_confirm_tbl'));
		
		$form->addCommandButton('changeExistingObjects', $this->lng->txt('change_existing_objects'));
		$form->addCommandButton('perm',$this->lng->txt('cancel'));
		
		$hidden = new ilHiddenInputGUI('type_filter');
		$hidden->setValue(
			$_POST['recursive'] ?
				serialize(array('all')) :
				serialize($_POST['recursive_list'])
		);
		$form->addItem($hidden);

		$rad = new ilRadioGroupInputGUI($this->lng->txt('rbac_local_policies'),'mode');
		
		if($protected)
		{
			$rad->setValue(ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES);
			$keep = new ilRadioOption(
				$this->lng->txt('rbac_keep_local_policies'),
				ilObjRole::MODE_PROTECTED_KEEP_LOCAL_POLICIES,
				$this->lng->txt('rbac_keep_local_policies_info')
			);
		}
		else
		{
			$rad->setValue(ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES);
			$keep = new ilRadioOption(
				$this->lng->txt('rbac_keep_local_policies'),
				ilObjRole::MODE_UNPROTECTED_KEEP_LOCAL_POLICIES,
				$this->lng->txt('rbac_unprotected_keep_local_policies_info')
			);
			
		}
		$rad->addOption($keep);
		
		if($protected)
		{
			$del =  new ilRadioOption(
				$this->lng->txt('rbac_delete_local_policies'),
				ilObjRole::MODE_PROTECTED_DELETE_LOCAL_POLICIES,
				$this->lng->txt('rbac_delete_local_policies_info')
			);
		}
		else
		{
			$del =  new ilRadioOption(
				$this->lng->txt('rbac_delete_local_policies'),
				ilObjRole::MODE_UNPROTECTED_DELETE_LOCAL_POLICIES,
				$this->lng->txt('rbac_unprotected_delete_local_policies_info')
			);
		}
		$rad->addOption($del);
		
		$form->addItem($rad);
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * Change existing objects
	 * @return 
	 */
	protected function changeExistingObjectsObject()
	{
		global $tree,$rbacreview,$rbacadmin;
		
		$mode = (int) $_POST['mode'];
		$start = ($this->rolf_ref_id == ROLE_FOLDER_ID ? ROOT_FOLDER_ID : $tree->getParentId($this->rolf_ref_id));
		$this->object->changeExistingObjects($start,$mode,unserialize(ilUtil::stripSlashes($_POST['type_filter'])));
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'perm');
	}
	
	/**
	 * Set sub tabs
	 * @param object $a_tab
	 * @return 
	 */
	protected function setSubTabs($a_tab)
	{
		global $ilTabs;
		
		switch($a_tab)
		{
			case 'default_perm_settings':
				if($this->rolf_ref_id != ROLE_FOLDER_ID)
				{
					return true;
				}
				$ilTabs->addSubTabTarget(
					'rbac_repository_permissions',
					$this->ctrl->getLinkTarget($this,'perm')
				);
				$ilTabs->addSubTabTarget(
					'rbac_admin_permissions',
					$this->ctrl->getLinkTarget($this,'adminPerm')
				);
		}
		return true;
	}
	
	
} // END class.ilObjRoleGUI
?>
