<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./classes/class.ilObjectGUI.php");

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

	const CFORM_NEW = "new";
	const CFORM_CLONE = "clone";
	const CFORM_IMPORT = "import";

	const OBJECT_ID = 0;
	const REPOSITORY_NODE_ID = 1;
	const WORKSPACE_NODE_ID = 2;
	const REPOSITORY_OBJECT_ID = 3;
	const WORKSPACE_OBJECT_ID = 4;
	
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
		
		$this->creation_forms = array(
			ilObject2GUI::CFORM_NEW,
			ilObject2GUI::CFORM_CLONE,
			ilObject2GUI::CFORM_IMPORT
			);

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
				$this->access_handler = new ilWorkspaceAccessHandler();
				$params[] = "wsp_id";
				break;

			case self::WORKSPACE_OBJECT_ID:
				global $ilUser;
				$this->object_id = $a_id;
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
				$this->tree = new ilWorkspaceTree($ilUser->getId());
				include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
				$this->access_handler = new ilWorkspaceAccessHandler();
				$params[] = "obj_id"; // ???
				break;

			case self::OBJECT_ID:
				$this->object_id = $a_id;
				include_once "Services/Objects/classes/class.ilDummyAccessHandler.php";
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
			$this->call_by_reference = true;
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

		switch($next_class)
		{
			default:
				// $this->prepareOutput(); ???
				if(!$cmd)
				{
					$cmd = "view";
				}
				return $this->performCommand($cmd);
		}

		return true;
	}
	
	/**
	* Handles all commmands of this class, centralizes permission checks
	*/
	function performCommand($cmd)
	{
/*		switch ($cmd)
		{
			case ...:
				$this->checkPermission();
				return $this->$cmd();
				break;
		}*/
	}

	final protected function assignObject()
	{
		if ($this->object_id != 0)
		{
			switch($this->id_type)
			{				
				case self::OBJECT_ID:
				case self::REPOSITORY_OBJECT_ID:
				case self::WORKSPACE_OBJECT_ID:
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
				break;

			case self::WORKSPACE_NODE_ID:
				// :TODO:
				break;
		}

		if($this->object_id)
		{
			$this->addLocatorItems();
		}

		$tpl->setLocator();
	}
	
	/**
	* Final/Private declaration of unchanged parent methods
	*/
	final public function withReferences() { return parent::withReferences(); }
	final public function setCreationMode($a_mode = true) { return parent::setCreationMode($a_mode); }
	final public function getCreationMode() { return parent::getCreationMode(); }
	final protected function prepareOutput() { return parent::prepareOutput(); }
	final protected function setTitleAndDescription() { return parent::setTitleAndDescription(); }
	final protected function showUpperIcon() { return parent::showUpperIcon(); }
//	final private function showMountWebfolderIcon() { return parent::showMountWebfolderIcon(); }
	final public function getHTML() { return parent::getHTML(); }
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
	final public  function delete() { return parent::deleteObject(); }	// done
	final public  function trash() { return parent::trashObject(); }		// done
	// -> ilRepUtil
	final public function undelete() { return parent::undeleteObject(); } // done
	final public function confirmedDelete() { return parent::confirmedDeleteObject(); } // done
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
	final public function view() { return parent::viewObject(); }
//	final private function setAdminTabs() { return parent::setAdminTabs(); }
	final public function getAdminTabs($a) { return parent::getAdminTabs($a); }
	final protected function addAdminLocatorItems() { return parent::addAdminLocatorItems(); }
	
	/**
	* Deprecated functions
	*/
//	final private function setSubObjects() { die("ilObject2GUI::setSubObjects() is deprecated."); }
//	final public function getFormAction() { die("ilObject2GUI::getFormAction() is deprecated."); }
//	final protected  function setFormAction() { die("ilObject2GUI::setFormAction() is deprecated."); }
	final protected  function getReturnLocation() { die("ilObject2GUI::getReturnLocation() is deprecated."); }
	final protected  function setReturnLocation() { die("ilObject2GUI::setReturnLocation() is deprecated."); }
	final protected function showActions() { die("ilObject2GUI::showActions() is deprecated."); }
	final public function getTemplateFile() {mk(); die("ilObject2GUI::getTemplateFile() is deprecated."); }
	final protected function getTitlesByRefId() { die("ilObject2GUI::getTitlesByRefId() is deprecated."); }
	final protected function getTabs() {nj(); die("ilObject2GUI::getTabs() is deprecated."); }
	final protected function __showButton() { die("ilObject2GUI::__showButton() is deprecated."); }
	final protected function hitsperpageObject() { die("ilObject2GUI::hitsperpageObject() is deprecated."); }
	final protected function __initTableGUI() { die("ilObject2GUI::__initTableGUI() is deprecated."); }
	final protected function __setTableGUIBasicData() { die("ilObject2GUI::__setTableGUIBasicData() is deprecated."); }
	final protected function __showClipboardTable() { die("ilObject2GUI::__showClipboardTable() is deprecated."); }
	
	/**
	* Functions to be overwritten
	*/
	protected function addLocatorItems() {}
	public function copyWizardHasOptions($a_mode) { return false; }
	protected function setTabs() { }
	
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
	 * Deactivate creation form
	 *
	 * @param
	 * @return
	 */
	function deactivateCreationForm($a_type)
	{
		foreach ($this->creation_forms as $k => $v)
		{
			if ($v == $a_type)
			{
				unset($this->creation_forms[$k]);
				break;
			}
		}
	}
	
	/**
	 * Add creation form
	 *
	 * @param	object	form object
	 */
	function addCreationForm($a_header, $a_form)
	{
		$this->creation_forms[] = array("header" => $a_header,
			"form" => $a_form);
	}

	/**
	* Create new object form
	*
	* @access	public
	*/
	function create($a_reuse_form = null)
	{
		global $rbacsystem, $tpl, $ilCtrl, $ilErr;
		
		$new_type = $_REQUEST["new_type"];
		$ilCtrl->setParameter($this, "new_type", $new_type);
		$this->initCreationForms($a_reuse_form);

		if (!$this->getAccessHandler()->checkAccess("create", "", $this->parent_id, $new_type))
		{
			$ilErr->raiseError($this->lng->txt("permission_denied"));
		}
		else
		{
			$tpl->setContent($this->getCreationFormsHTML());
//			$this->ctrl->setParameter($this, "new_type", $new_type);
//			$this->initEditForm("create", $new_type);
//			$tpl->setContent($this->form->getHTML());
			
//			if ($new_type != "mep")		// bad hack, should be removed (implemented!)
//			{
//				$clone_html = $this->fillCloneTemplate('', $new_type);
//			}
			
//			$tpl->setContent($this->form->getHTML().$clone_html);
		}
	}
	
	/**
	 * Init creation froms
	 */
	protected function initCreationForms($a_reuse_form = null)
	{
	}
	
	/**
	 * Get HTML for creation forms
	 */
	function getCreationFormsHTML()
	{
		global $lng;
		
		$new_type = $_REQUEST["new_type"];
		$lng->loadLanguageModule($new_type);
		
		if (count($this->creation_forms) == 1)
		{
			$cf = $this->creation_forms[0];
			if (is_array($cf))
			{
				return $cf["form"]->getHTML();
			}
			else if ($cf == ilObject2GUI::CFORM_NEW)
			{
				$this->initEditForm("create", $new_type);
				return $this->form->getHTML();
			}
			else if ($cf == ilObject2GUI::CFORM_CLONE)
			{
return "";
				return $this->fillCloneTemplate('', $new_type);
			}
			else if($cf == ilObject2GUI::CFORM_IMPORT)
			{
				$this->initImportForm($new_type);
				return $this->form->getHTML();
			}	
		}
		else if (count($this->creation_forms) > 1)
		{
			include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");

			$html = "";
//			$acc = new ilAccordionGUI();
//			$acc->setBehaviour(ilAccordionGUI::FIRST_OPEN);
			$cnt = 1;
			foreach ($this->creation_forms as $cf)
			{
//				$htpl = new ilTemplate("tpl.creation_acc_head.html", true, true, "Services/Object");
//				$htpl->setVariable("IMG_ARROW", ilUtil::getImagePath("accordion_arrow.gif"));
				
//				$ot = $lng->txt("option")." ".$cnt.": ";
				if (is_array($cf))
				{
//					$htpl->setVariable("TITLE", $ot.$cf["header"]);
//					$acc->addItem($htpl->get(), $cf["form"]->getHTML());
$html.= $cf["form"]->getHTML()."<br />";
				}
				else if ($cf == ilObject2GUI::CFORM_NEW)
				{
					$this->initEditForm("create", $new_type);
//					$htpl->setVariable("TITLE", $ot.$lng->txt($new_type."_create"));
//					$acc->addItem($htpl->get(), $this->form->getHTML());
$html.= $this->form->getHTML()."<br />";
				}
				else if ($cf == ilObject2GUI::CFORM_CLONE)
				{
//					$clone_html = $this->fillCloneTemplate('', $new_type);
//					$htpl->setVariable("TITLE", $ot.$lng->txt($new_type."_clone"));
//					$acc->addItem($htpl->get(), $clone_html);
				}
				else if($cf == ilObject2GUI::CFORM_IMPORT)
				{
					$this->initImportForm($new_type);
//					$htpl->setVariable("TITLE", $ot.$lng->txt($new_type."_import"));
//					$acc->addItem($htpl->get(), $this->form->getHTML());
$html.= $this->form->getHTML()."<br />";
				}
				$cnt++;
			}
			
//			return $acc->getHTML();
			return $html;
		}	
	}
	
	
	/**
	* Save object
	*
	* @access	public
	*/
	function save()
	{
		global $rbacsystem, $objDefinition, $tpl, $lng, $ilErr;

		$new_type = $_REQUEST["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$this->getAccessHandler()->checkAccess("create", "", $this->parent_id, $new_type))
		{
			$ilErr->raiseError($this->lng->txt("no_create_permission"));
		}
		
		$this->ctrl->setParameter($this, "new_type", $new_type);
		$this->initEditForm("create", $new_type);
		if ($this->form->checkInput())
		{			
			$location = $objDefinition->getLocation($new_type);
	
			// create and insert object in objecttree
			$class_name = "ilObj".$objDefinition->getClassName($new_type);
			include_once($location."/class.".$class_name.".php");
			$newObj = new $class_name();
			$newObj->setType($new_type);
			$newObj->setTitle(ilUtil::stripSlashes($_POST["title"]));
			$newObj->setDescription(ilUtil::stripSlashes($_POST["desc"]));
			$this->object_id = $newObj->create();

			$this->putObjectInTree($newObj, $this->parent_id);

			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->afterSave($newObj);
			return;
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	protected function afterSave(ilObject $a_new_object)
	{
		$this->ctrl->returnToParent($this);
	}

	/**
	* Init object creation form
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initEditForm($a_mode = "edit", $a_new_type = "")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTarget("_top");
	
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$this->form->addItem($ta);
	
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("save", $lng->txt($a_new_type."_add"));
			$this->form->addCommandButton("cancelCreation", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt($a_new_type."_new"));
		}
		else
		{
			$this->form->addCommandButton("update", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdate", $lng->txt("cancel"));
			$this->form->setTitle($lng->txt("edit"));
		}
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}

	/**
	* Get values for edit form
	*/
	function getEditFormValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getDescription();
		$this->form->setValuesByArray($values);
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*/
	protected function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*/
	final function cancelCreation($in_rep = false)
	{
		global $ilCtrl;

		switch($this->id_type)
		{
			case self::REPOSITORY_NODE_ID:
			case self::REPOSITORY_OBJECT_ID: // ???
				ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$this->parent_id);
				

			case self::WORKSPACE_NODE_ID:
			case self::WORKSPACE_OBJECT_ID:
				$ilCtrl->setParameterByClass("ilpersonalworkspacegui", "wsp_id", $this->parent_id);
				$ilCtrl->redirectByClass("ilpersonalworkspacegui", "");
				break;

			case self::OBJECT_ID:
				// do nothing ???
				break;
		}		
	}

	/**
	* edit object
	*
	* @access	public
	*/
	function edit()
	{
		global $tpl;
		
		$this->initEditForm("edit");
		$this->getEditFormValues();
		$tpl->setContent($this->form->getHTML());
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	final function cancelUpdate()
	{
		$this->ctrl->redirect($this);
	}

	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	function update()
	{
		global $lng, $tpl;
		
		$this->initEditForm("edit");
		if ($this->form->checkInput())
		{
			$this->object->setTitle($_POST["title"]);
			$this->object->setDescription($_POST["desc"]);
			$this->update = $this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->afterUpdate();
			return;
		}
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
	
	protected function afterUpdate()
	{
		$this->ctrl->redirect($this);
	}

	/**
	* Init object import form
	*
	* @param        string        new type
	*/
	public function initImportForm($a_new_type = "")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTarget("_top");
	
		// Import file
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($lng->txt("import_file"), "importfile");
		$fi->setSuffixes(array("zip"));
		$this->form->addItem($fi);
	
		$this->form->addCommandButton("importFile", $lng->txt("import"));
		$this->form->addCommandButton("cancelCreation", $lng->txt("cancel"));
		$this->form->setTitle($lng->txt($a_new_type."_import"));
	                
		$this->form->setFormAction($ilCtrl->getFormAction($this));	 
	}

	/**
	 * Import
	 *
	 * @access	public
	 */
	function importFile()
	{
		global $rbacsystem, $objDefinition, $tpl, $lng, $ilErr;

		$new_type = $_REQUEST["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$this->getAccessHandler()->checkAccess("create", "", $this->parent_id, $new_type))
		{
			$ilErr->raiseError($this->lng->txt("no_create_permission"));
		}
		$this->ctrl->setParameter($this, "new_type", $new_type);
		$this->initImportForm($new_type);
		if ($this->form->checkInput())
		{
			// todo: make some check on manifest file
			include_once("./Services/Export/classes/class.ilImport.php");
			$imp = new ilImport((int)$this->parent_id);
			$new_id = $imp->importObject($newObj, $_FILES["importfile"]["tmp_name"],
				$_FILES["importfile"]["name"], $new_type);

			// put new object id into tree
			if ($new_id > 0)
			{
				// :TODO
				$newObj = ilObjectFactory::getInstanceByObjId($new_id);
				$this->putObjectInTree($newObj, $this->parent_id);

				ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
				$this->afterSave($newObj);
			}
			return;
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}

	/**
	 * Add object to tree at given position
	 *
	 * @param ilObject $a_obj
	 * @param int $a_parent_node_id
	 */
	protected function putObjectInTree(ilObject $a_obj, $a_parent_node_id)
	{
		global $rbacreview;

		$this->object_id = $a_obj->getId();

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
				$rbac_log = ilRbacLog::gatherFaPa($this->node_id, array_keys($rbac_log_roles));
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
				// do nothing
				break;
		}
	}
}

?>