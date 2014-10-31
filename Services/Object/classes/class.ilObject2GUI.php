<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
* New implementation of ilObjectGUI. (beta)
*
* Differences to the ilObject implementation:
* - no $this->ilias anymore
* - no $this->tree anymore
* - no $this->formaction anymore
* - no $this->return_location anymore
* - no $this->target_frame anymore
* - no $this->actions anymore
* - no $this->sub_objects anymore
* - no $this->data anymore
* - no $this->prepare_output anymore
*
*
* All new modules should derive from this class.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesObject
*/
abstract class ilObject2GUI extends ilObjectGUI
{
	protected $object_id;
	protected $node_id;
	protected $creation_forms = array();
	protected $id_type = array();
	protected $parent_id;
	public $tree;
	protected $access_handler;

	const OBJECT_ID = 0;
	const REPOSITORY_NODE_ID = 1;
	const WORKSPACE_NODE_ID = 2;
	const REPOSITORY_OBJECT_ID = 3;
	const WORKSPACE_OBJECT_ID = 4;
	const PORTFOLIO_OBJECT_ID = 5;
	
	/**
	 * Constructor
	 *
	 * @param int $a_id
	 * @param int $a_id_type
	 * @param int $a_parent_node_id
	 */
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		global $objDefinition, $tpl, $ilCtrl, $ilErr, $lng, $ilTabs, $tree, $ilAccess;
		
		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr =& $ilErr;
		}

		$this->id_type = $a_id_type;
		$this->parent_id = $a_parent_node_id;
		$this->type = $this->getType();
		$this->html = "";
		
		// use globals instead?
		$this->tabs_gui = $ilTabs;
		$this->objDefinition = $objDefinition;
		$this->tpl = $tpl;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;

		$params = array();		
		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
				$this->node_id = $a_id;
				$this->object_id = ilObject::_lookupObjectId($this->node_id);
				$this->tree = $tree;
				$this->access_handler = $ilAccess;
				$this->call_by_reference = true;  // needed for prepareOutput()
				$params[] = "ref_id";
				break;

			case self::REPOSITORY_OBJECT_ID:
				$this->object_id = $a_id;
				$this->tree = $tree;
				$this->access_handler = $ilAccess;
				$params[] = "obj_id"; // ???
				break;

			case self::WORKSPACE_NODE_ID:
				global $ilUser;
				$this->node_id = $a_id;
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
				$this->tree = new ilWorkspaceTree($ilUser->getId());
				$this->object_id = $this->tree->lookupObjectId($this->node_id);				
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
				$this->access_handler = new ilWorkspaceAccessHandler($this->tree);
				$params[] = "wsp_id";
				break;

			case self::WORKSPACE_OBJECT_ID:
				global $ilUser;
				$this->object_id = $a_id;
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
				$this->tree = new ilWorkspaceTree($ilUser->getId());
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
				$this->access_handler = new ilWorkspaceAccessHandler($this->tree);
				$params[] = "obj_id"; // ???
				break;
			
			case self::PORTFOLIO_OBJECT_ID:
				$this->object_id = $a_id;
				include_once('./Modules/Portfolio/classes/class.ilPortfolioAccessHandler.php');
				$this->access_handler = new ilPortfolioAccessHandler();
				$params[] = "prt_id";
				break;

			case self::OBJECT_ID:
				$this->object_id = $a_id;
				include_once "Services/Object/classes/class.ilDummyAccessHandler.php";
			    $this->access_handler = new ilDummyAccessHandler();
				$params[] = "obj_id";
				break;
		}
		$this->ctrl->saveParameter($this, $params);


		
		// old stuff for legacy code (obsolete?)
		if(!$this->object_id)
		{
			$this->creation_mode = true;
		}
		if($this->node_id)
		{
			// add parent node id if missing
			if(!$this->parent_id && $this->tree)
			{
				$this->parent_id = $this->tree->getParentId($this->node_id);
			}
		}
		$this->ref_id = $this->node_id;
		$this->obj_id = $this->object_id;
		


		$this->assignObject();
		
		// set context
		if (is_object($this->object))
		{
			$this->ctrl->setContext($this->object->getId(), $this->object->getType());
		}
		
		$this->afterConstructor();
	}
	
	/**
	 * Do anything that should be done after constructor in here.
	 */
	protected function afterConstructor()
	{
	}
	
	/**
	 * execute command
	 */
	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		$this->prepareOutput(); 

		switch($next_class)
		{
			case "ilworkspaceaccessgui";		
				if($this->node_id)
				{
					$this->tabs_gui->activateTab("id_permissions");

					include_once('./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php');
					$wspacc = new ilWorkspaceAccessGUI($this->node_id, $this->getAccessHandler());
					$this->ctrl->forwardCommand($wspacc);
					break;
				}
			
			default:				
				if(!$cmd)
				{
					$cmd = "render";
				}
				return $this->$cmd();
		}

		return true;
	}

	/**
	 * create object instance as internal property (repository/workspace switch)
	 */
	final protected function assignObject()
	{
		if ($this->object_id != 0)
		{
			switch($this->id_type)
			{				
				case self::OBJECT_ID:
				case self::REPOSITORY_OBJECT_ID:
				case self::WORKSPACE_OBJECT_ID:
				case self::PORTFOLIO_OBJECT_ID:
					$this->object = ilObjectFactory::getInstanceByObjId($this->object_id);
					break;

				case self::REPOSITORY_NODE_ID:
					$this->object = ilObjectFactory::getInstanceByRefId($this->node_id);
					break;

				case self::WORKSPACE_NODE_ID:
					// to be discussed
					$this->object = ilObjectFactory::getInstanceByObjId($this->object_id);
					break;
			}
		}
	}
	
	/**
	 * Get access handler
	 *
	 * @return object
	 */
	protected function getAccessHandler()
	{
		return $this->access_handler;
	}

	/**
	 * set Locator
	 */
	final protected function setLocator()
	{
		global $ilLocator, $tpl;

		if ($this->omit_locator)
		{
			return;
		}

		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
				$ref_id = $this->node_id
					? $this->node_id
					: $this->parent_id;
				$ilLocator->addRepositoryItems($ref_id);
				
				// not so nice workaround: todo: handle $ilLocator as tabs in ilTemplate
				if ($_GET["admin_mode"] == "" &&
					strtolower($this->ctrl->getCmdClass()) == "ilobjrolegui")
				{
					$this->ctrl->setParameterByClass("ilobjrolegui",
						"rolf_ref_id", $_GET["rolf_ref_id"]);
					$this->ctrl->setParameterByClass("ilobjrolegui",
						"obj_id", $_GET["obj_id"]);
					$ilLocator->addItem($this->lng->txt("role"),
						$this->ctrl->getLinkTargetByClass(array("ilpermissiongui",
							"ilobjrolegui"), "perm"));
				}
				
				// in workspace this is done in ilPersonalWorkspaceGUI
				if($this->object_id)
				{
					$this->addLocatorItems();
				}
				$tpl->setLocator();
				
				break;

			case self::WORKSPACE_NODE_ID:
				// :TODO:
				break;
		}

		
	}

	/**
	 * Display delete confirmation form (repository/workspace switch)
	 */
	public  function delete()
	{
		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
			case self::REPOSITORY_OBJECT_ID:
				return parent::deleteObject();

			case self::WORKSPACE_NODE_ID:
			case self::WORKSPACE_OBJECT_ID:
				return $this->deleteConfirmation();

			case self::OBJECT_ID:
			case self::PORTFOLIO_OBJECT_ID:
				// :TODO: should this ever occur? 
				break;
		}
	}

	/**
	 * Display delete confirmation form (workspace specific)
	 *
	 * This should probably be moved elsewhere as done with RepUtil
	 */
	protected function deleteConfirmation()
	{
		global $lng, $tpl, $objDefinition;

		$node_id = (int)$_REQUEST["item_ref_id"];
		if (!$node_id)
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, "");
		}

		// on cancel or fail we return to parent node
		$parent_node = $this->tree->getParentId($node_id);
		$this->ctrl->setParameter($this, "wsp_id", $parent_node);

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setHeaderText($lng->txt("info_delete_sure")."<br/>".
			$lng->txt("info_delete_warning_no_trash"));

		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($lng->txt("cancel"), "cancelDelete");
		$cgui->setConfirm($lng->txt("confirm"), "confirmedDelete");

	    $a_ids = array($node_id);
		foreach ($a_ids as $node_id)
		{
			$children = $this->tree->getSubTree($this->tree->getNodeData($node_id));
			foreach($children as $child)
			{
				$node_id = $child["wsp_id"];
				$obj_id = $this->tree->lookupObjectId($node_id);
				$type = ilObject::_lookupType($obj_id);
				$title = call_user_func(array(ilObjectFactory::getClassByType($type),'_lookupTitle'), $obj_id);

				// if anything fails, abort the whole process
				if(!$this->checkPermissionBool("delete", "", "", $node_id))
				{
					ilUtil::sendFailure($lng->txt("msg_no_perm_delete")." ".$title, true);
					$this->ctrl->redirect($this);
				}

				$cgui->addItem("id[]", $node_id, $title,
					ilObject::_getIcon($obj_id, "small", $type),
					$lng->txt("icon")." ".$lng->txt("obj_".$type));
			}
		}
		
		$tpl->setContent($cgui->getHTML());
	}

	/**
	 * Delete objects (repository/workspace switch)
	 */
	public function confirmedDelete()
	{
		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
			case self::REPOSITORY_OBJECT_ID:
				return parent::confirmedDeleteObject();

			case self::WORKSPACE_NODE_ID:
			case self::WORKSPACE_OBJECT_ID:
				return $this->deleteConfirmedObjects();

			case self::OBJECT_ID:
			case self::PORTFOLIO_OBJECT_ID:
				// :TODO: should this ever occur?
				break;
		}
	}

	/**
	 * Delete objects (workspace specific)
	 *
	 * This should probably be moved elsewhere as done with RepUtil
	 */
	protected function deleteConfirmedObjects()
	{
		global $lng, $objDefinition;

		if(sizeof($_POST["id"]))
		{			
			foreach($_POST["id"] as $node_id)
			{
				$node = $this->tree->getNodeData($node_id);

				// tree/reference
				$this->tree->deleteReference($node_id);
				$this->tree->deleteTree($node);

				// permission
				$this->getAccessHandler()->removePermission($node_id);

				// object
				$object = ilObjectFactory::getInstanceByObjId($node["obj_id"], false);
				if($object)
				{
					$object->delete();
				}
			}

			ilUtil::sendSuccess($lng->txt("msg_removed"), true);
		}
		else
		{
			ilUtil::sendFailure($lng->txt("no_checkbox"), true);
		}

		$this->ctrl->redirect($this, "");
	}
	
	public function getHTML() { return parent::getHTML(); }

	/**
	 * Final/Private declaration of unchanged parent methods
	 */
	final public function withReferences() { return parent::withReferences(); }
	final public function setCreationMode($a_mode = true) { return parent::setCreationMode($a_mode); }
	final public function getCreationMode() { return parent::getCreationMode(); }
	final protected function prepareOutput() { return parent::prepareOutput(); }
	protected function setTitleAndDescription() { return parent::setTitleAndDescription(); }
	final protected function showUpperIcon() { return parent::showUpperIcon(); }
//	final private function showMountWebfolderIcon() { return parent::showMountWebfolderIcon(); }
	final protected function omitLocator($a_omit = true) { return parent::omitLocator($a_omit); }
	final protected  function getTargetFrame($a_cmd, $a_target_frame = "") { return parent::getTargetFrame($a_cmd, $a_target_frame); }
	final protected  function setTargetFrame($a_cmd, $a_target_frame) { return parent::setTargetFrame($a_cmd, $a_target_frame); }
	final public function isVisible() { return parent::isVisible(); }
	final protected function getCenterColumnHTML() { return parent::getCenterColumnHTML(); }
	final protected function getRightColumnHTML() { return parent::getRightColumnHTML(); }
	final protected function setColumnSettings($column_gui) { return parent::setColumnSettings($column_gui); }
	final protected function checkPermission($a_perm, $a_cmd = "") { return parent::checkPermission($a_perm, $a_cmd); }
	
	// -> ilContainerGUI
	final protected function showPossibleSubObjects() { return parent::showPossibleSubObjects(); }
	// -> ilRepUtilGUI
	final public  function trash() { return parent::trashObject(); }		// done
	// -> ilRepUtil
	final public function undelete() { return parent::undeleteObject(); } // done
	final public function cancelDelete() { return parent::cancelDeleteObject(); } // ok
	final public function removeFromSystem() { return parent::removeFromSystemObject(); } // done 
	final protected function redirectToRefId() { return parent::redirectToRefId(); } // ok
	
	// -> stefan
	final protected function fillCloneTemplate($a_tpl_varname,$a_type) { return parent::fillCloneTemplate($a_tpl_varname,$a_type); }
	final protected function fillCloneSearchTemplate($a_tpl_varname,$a_type) { return parent::fillCloneSearchTemplate($a_tpl_varname,$a_type); }
	final protected function searchCloneSource() { return parent::searchCloneSourceObject(); }
	final public function cloneAll() { return parent::cloneAllObject(); }
	final protected function buildCloneSelect($existing_objs) { return parent::buildCloneSelect($existing_objs); }

	// -> ilAdministration
	final private function displayList() { return parent::displayList(); }
//	final private function setAdminTabs() { return parent::setAdminTabs(); }
//	final public function getAdminTabs($a) { return parent::getAdminTabs($a); }
	final protected function addAdminLocatorItems() { return parent::addAdminLocatorItems(); }

	/**
	 * view object content (repository/workspace switch)
	 */
	public function view()
	{
		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
			case self::REPOSITORY_OBJECT_ID:
				return parent::viewObject();

			case self::WORKSPACE_NODE_ID:
			case self::WORKSPACE_OBJECT_ID:
				return $this->render();

			case self::OBJECT_ID:
			case self::PORTFOLIO_OBJECT_ID:
				// :TODO: should this ever occur?  do nothing or edit() ?!
				break;
		}
	}

	/**
	 * create tabs (repository/workspace switch)
	 *
	 * this had to be moved here because of the context-specific permission tab
	 */
	protected function setTabs()
	{
		global $ilTabs, $lng;

		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
			case self::REPOSITORY_OBJECT_ID:
				if ($this->checkPermissionBool("edit_permission"))
				{
					$ilTabs->addTab("id_permissions",
						$lng->txt("perm_settings"),
						$this->ctrl->getLinkTargetByClass(array(get_class($this), "ilpermissiongui"), "perm"));
				}
				break;

			case self::WORKSPACE_NODE_ID:
			case self::WORKSPACE_OBJECT_ID:
				// only files and blogs can be shared for now
				if ($this->checkPermissionBool("edit_permission") && in_array($this->type, array("file", "blog")) && $this->node_id)
				{
					$ilTabs->addTab("id_permissions",
						$lng->txt("wsp_permissions"),
						$this->ctrl->getLinkTargetByClass(array(get_class($this), "ilworkspaceaccessgui"), "share"));
				}
				break;
		}
	}
	
	/**
	 * Deprecated functions
	 */
//	final private function setSubObjects() { die("ilObject2GUI::setSubObjects() is deprecated."); }
//	final public function getFormAction() { die("ilObject2GUI::getFormAction() is deprecated."); }
//	final protected  function setFormAction() { die("ilObject2GUI::setFormAction() is deprecated."); }
	final protected  function getReturnLocation() { die("ilObject2GUI::getReturnLocation() is deprecated."); }
	final protected  function setReturnLocation() { die("ilObject2GUI::setReturnLocation() is deprecated."); }
	final protected function showActions() { die("ilObject2GUI::showActions() is deprecated."); }
	final protected function getTitlesByRefId() { die("ilObject2GUI::getTitlesByRefId() is deprecated."); }
	final protected function getTabs() {nj(); die("ilObject2GUI::getTabs() is deprecated."); }
	final protected function __showButton() { die("ilObject2GUI::__showButton() is deprecated."); }
	final protected function hitsperpageObject() { die("ilObject2GUI::hitsperpageObject() is deprecated."); }
	final protected function __initTableGUI() { die("ilObject2GUI::__initTableGUI() is deprecated."); }
	final protected function __setTableGUIBasicData() { die("ilObject2GUI::__setTableGUIBasicData() is deprecated."); }

	/**
	 * Functions to be overwritten
	 */
	protected function addLocatorItems() {}
	
	/**
	 * Functions that must be overwritten
	 */
	abstract function getType();
	
	/**
	 * Deleted in ilObject
	 */
//	final private function permObject() { parent::permObject(); }
//	final private function permSaveObject() { parent::permSaveObject(); }
//	final private function infoObject() { parent::infoObject(); }
//	final private function __buildRoleFilterSelect() { parent::__buildRoleFilterSelect(); }
//	final private function __filterRoles() { parent::__filterRoles(); }
//	final private function ownerObject() { parent::ownerObject(); }
//	final private function changeOwnerObject() { parent::changeOwnerObject(); }
//	final private function addRoleObject() { parent::addRoleObject(); }
//	final private function setActions() { die("ilObject2GUI::setActions() is deprecated."); }
//	final protected function getActions() { die("ilObject2GUI::getActions() is deprecated."); }

	/**
	 * CRUD
	 */
	public function create() { parent::createObject(); }
	public function save() { parent::saveObject(); }
	public function edit() { parent::editObject(); }
	public function update() { parent::updateObject(); }
	public function cancel() { parent::cancelObject(); }

	/**
	 * Init creation froms
	 *
	 * this will create the default creation forms: new, import, clone
	 *
	 * @param	string	$a_new_type
	 * @return	array
	 */
	protected function initCreationForms($a_new_type)
	{
		$forms = parent::initCreationForms($a_new_type);
		
		// cloning doesn't work in workspace yet
		if($this->id_type == self::WORKSPACE_NODE_ID)
		{
			unset($forms[self::CFORM_CLONE]);
		}

		return $forms;
	}
	
	/**
	 * Import
	 *
	 * @access	public
	 */
	function importFile()
	{
		parent::importFileObject($this->parent_id);
	}

	/**
	 * Add object to tree at given position
	 *
	 * @param ilObject $a_obj
	 * @param int $a_parent_node_id
	 */
	protected function putObjectInTree(ilObject $a_obj, $a_parent_node_id = null)
	{
		global $rbacreview, $ilUser, $objDefinition;

		$this->object_id = $a_obj->getId();

		if(!$a_parent_node_id)
		{
			$a_parent_node_id = $this->parent_id;
		}		
		
		// add new object to custom parent container
		if((int)$_REQUEST["crtptrefid"])
		{
			$a_parent_node_id = (int)$_REQUEST["crtptrefid"];
		}

		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
			case self::REPOSITORY_OBJECT_ID:
				if(!$this->node_id)
				{
					$a_obj->createReference();
					$this->node_id = $a_obj->getRefId();
				}
				$a_obj->putInTree($a_parent_node_id);
				$a_obj->setPermissions($a_parent_node_id);

				// rbac log
				include_once "Services/AccessControl/classes/class.ilRbacLog.php";
				$rbac_log_roles = $rbacreview->getParentRoleIds($this->node_id, false);
				$rbac_log = ilRbacLog::gatherFaPa($this->node_id, array_keys($rbac_log_roles), true);
				ilRbacLog::add(ilRbacLog::CREATE_OBJECT, $this->node_id, $rbac_log);

				$this->ctrl->setParameter($this, "ref_id", $this->node_id);
				break;

			case self::WORKSPACE_NODE_ID:
			case self::WORKSPACE_OBJECT_ID:
				if(!$this->node_id)
				{
					$this->node_id = $this->tree->insertObject($a_parent_node_id, $this->object_id);
				}
				$this->getAccessHandler()->setPermissions($a_parent_node_id, $this->node_id);

				$this->ctrl->setParameter($this, "wsp_id", $this->node_id);
				break;

			case self::OBJECT_ID:
			case self::PORTFOLIO_OBJECT_ID:
				// do nothing
				break;
		}
		
		// BEGIN ChangeEvent: Record save object.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		ilChangeEvent::_recordWriteEvent($this->object_id, $ilUser->getId(), 'create');
		// END ChangeEvent: Record save object.
			
		// use forced callback after object creation		
		self::handleAfterSaveCallback($a_obj, $_REQUEST["crtcb"]);		
	}
	
	/**
	 * After creation callback
	 * 
	 * @param ilObject $a_obj
	 * @param int $a_callback_ref_id
	 */
	public static function handleAfterSaveCallback(ilObject $a_obj, $a_callback_ref_id)
	{
		global $objDefinition;
		
		$a_callback_ref_id = (int)$a_callback_ref_id;		
		if($a_callback_ref_id)
		{
			$callback_type = ilObject::_lookupType($a_callback_ref_id, true);
			$class_name = "ilObj".$objDefinition->getClassName($callback_type)."GUI";
			$location = $objDefinition->getLocation($callback_type);
			include_once($location."/class.".$class_name.".php");
			if (in_array(strtolower($class_name), array("ilobjitemgroupgui")))
			{
				$callback_obj = new $class_name($a_callback_ref_id);
			}
			else
			{
				$callback_obj = new $class_name(null, $a_callback_ref_id, true, false);
			}
			$callback_obj->afterSaveCallback($a_obj);
		}
	}

	/**
	 * Check permission
	 *
	 * @param string $a_perm
	 * @param string $a_cmd
	 * @param string $a_type
	 * @param int $a_node_id
	 * @return bool
	 */
	protected function checkPermissionBool($a_perm, $a_cmd = "", $a_type = "", $a_node_id = null)
	{
		global $ilUser;
		
		if($a_perm == "create")
		{
			if(!$a_node_id)
			{
				$a_node_id = $this->parent_id;
			}
			if($a_node_id)
			{
				return $this->getAccessHandler()->checkAccess($a_perm."_".$a_type, $a_cmd, $a_node_id);
			}
		}
		else
		{

			if (!$a_node_id)
			{
				$a_node_id = $this->node_id;
			}
			if($a_node_id)
			{
				return $this->getAccessHandler()->checkAccess($a_perm, $a_cmd, $a_node_id);
			}
		}
		
		// if we do not have a node id, check if current user is owner
		if($this->obj_id && $this->object->getOwner() == $ilUser->getId())
		{
			return true;
		}		
		return false;
	}
	
	/**
	 * Add header action menu
	 * 
	 * @param string $a_sub_type
	 * @param int $a_sub_id
	 * @return ilObjectListGUI
	 */
	protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
	{
		global $ilAccess; 
		
		if($this->id_type == self::WORKSPACE_NODE_ID)
		{
			if(!$this->creation_mode && $this->object_id)
			{
				include_once "Services/Object/classes/class.ilCommonActionDispatcherGUI.php";
				$dispatcher = new ilCommonActionDispatcherGUI(ilCommonActionDispatcherGUI::TYPE_WORKSPACE, 
					$this->getAccessHandler(), $this->getType(), $this->node_id, $this->object_id);
				
				$dispatcher->setSubObject($a_sub_type, $a_sub_id);
				
				include_once "Services/Object/classes/class.ilObjectListGUI.php";
				ilObjectListGUI::prepareJSLinks($this->ctrl->getLinkTarget($this, "redrawHeaderAction", "", true), 
					$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "ilnotegui"), "", "", true, false), 
					$this->ctrl->getLinkTargetByClass(array("ilcommonactiondispatchergui", "iltagginggui"), "", "", true, false));
				
				$lg = $dispatcher->initHeaderAction();
				
				if(is_object($lg))
				{								
					// to enable add to desktop / remove from desktop
					if($this instanceof ilDesktopItemHandling)
					{
						$lg->setContainerObject($this);
					}

					// for activation checks see ilObjectGUI
					// $lg->enableComments(true);
					$lg->enableNotes(true);
					// $lg->enableTags(true);
				}

				return $lg;
			}
		}
		else
		{
			return parent::initHeaderAction();
		}
	}
	
	/**
	 * Updating icons after ajax call
	 */
	protected function redrawHeaderAction()
	{
		parent::redrawHeaderActionObject();
	}
	
	protected function getPermanentLinkWidget($a_append = null, $a_center = false)
	{
		if($this->id_type == self::WORKSPACE_NODE_ID)
		{
			$a_append .= "_wsp";
		}
		
		include_once('Services/PermanentLink/classes/class.ilPermanentLinkGUI.php');
		$plink = new ilPermanentLinkGUI($this->getType(), $this->node_id , $a_append);
		$plink->setIncludePermanentLinkText(false);
		$plink->setAlignCenter($a_center);
		return $plink->getHTML();
	}
	
	
	protected function handleAutoRating(ilObject $a_new_obj)
	{
		// only needed in repository
		if($this->id_type == self::REPOSITORY_NODE_ID)
		{
			parent::handleAutoRating($a_new_obj);
		}				
	}
}

?>