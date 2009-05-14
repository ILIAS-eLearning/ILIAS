<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

include_once("./classes/class.ilObjectGUI.php");

/**
* New implementation of ilObjectGUI. (alpha)
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
	/**
	* Constructor.
	*/
	function __construct($a_id = 0, $a_call_by_reference = true)
	{
		global $objDefinition, $tpl, $ilCtrl, $ilErr, $lng, $ilTabs;
		
		$this->type = $this->getType();
		
		$this->tabs_gui =& $ilTabs;

		if (!isset($ilErr))
		{
			$ilErr = new ilErrorHandling();
			$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		}
		else
		{
			$this->ilErr =& $ilErr;
		}

		$this->objDefinition = $objDefinition;
		$this->tpl = $tpl;
		$this->html = "";
		$this->ctrl = $ilCtrl;

		$params = array("ref_id");

		if (!$a_call_by_reference)
		{
			$params = array("ref_id","obj_id");
		}

		$this->ctrl->saveParameter($this, $params);
		
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;
		$this->creation_mode = false;
		$this->ref_id = ($this->call_by_reference) ? $this->id : $_GET["ref_id"];
		$this->obj_id = ($this->call_by_reference) ? $_GET["obj_id"] : $this->id;
		$this->lng = $lng;

		$this->assignObject();
		
		// set context
		if (is_object($this->object))
		{
			if ($this->call_by_reference && $this->ref_id = $_GET["ref_id"])
			{
				$this->ctrl->setContext($this->object->getId(), 
					$this->object->getType());
			}
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
				if(!$cmd)
				{
					$cmd = "view";
				}
				return $this->performCommand($cmd);
				break;
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
	
	
	/**
	* Final/Private declaration of unchanged parent methods
	*/
	final public function withReferences() { return parent::withReferences(); }
	final public function setCreationMode($a_mode = true) { return parent::setCreationMode($a_mode); }
	final public function getCreationMode() { return parent::getCreationMode(); }
	final protected function assignObject() { return parent::assignObject(); }
	final protected function prepareOutput() { return parent::prepareOutput(); }
	final protected function setTitleAndDescription() { return parent::setTitleAndDescription(); }
	final protected function showUpperIcon() { return parent::showUpperIcon(); }
//	final private function showMountWebfolderIcon() { return parent::showMountWebfolderIcon(); }
	final public function getHTML() { return parent::getHTML(); }
	final protected function setLocator() { return parent::setLocator(); }
	final protected function omitLocator($a_omit = true) { return parent::omitLocator($a_omit); }
	final protected  function getTargetFrame() { return parent::getTargetFrame(); }
	final protected  function setTargetFrame($a_cmd, $a_target_frame) { return parent::setTargetFrame($a_cmd, $a_target_frame); }
	final public function isVisible() { return parent::isVisible(); }
	final protected function getCenterColumnHTML() { return parent::getCenterColumnHTML(); }
	final protected function getRightColumnHTML() { return parent::getRightColumnHTML(); }
	final protected function setColumnSettings($column_gui) { return parent::setColumnSettings($column_gui); }
	final protected function checkPermission($a_perm, $a_cmd = "") { return parent::checkPermission($a_perm, $a_cmd); }
	
	// -> ilContainerGUI
	final protected function showPossibleSubObjects() { return parent::showPossibleSubObjects(); }
	// -> ilRepUtilGUI
	final public  function deleteObject() { return parent::deleteObject(); }	// done
	final public  function trashObject() { return parent::trashObject(); }		// done
	// -> ilRepUtil
	final public function undeleteObject() { return parent::undeleteObject(); } // done
	final public function confirmedDeleteObject() { return parent::confirmedDeleteObject(); } // done
	final public function cancelDeleteObject() { return parent::cancelDeleteObject(); } // ok
	final public function removeFromSystemObject() { return parent::removeFromSystemObject(); } // done 
	final protected function redirectToRefId() { return parent::redirectToRefId(); } // ok
	
	// -> stefan
	final protected function fillCloneTemplate($a_tpl_varname,$a_type) { return parent::fillCloneTemplate($a_tpl_varname,$a_type); }
	final protected function fillCloneSearchTemplate($a_tpl_varname,$a_type) { return parent::fillCloneSearchTemplate($a_tpl_varname,$a_type); }
	final protected function searchCloneSourceObject() { return parent::searchCloneSourceObject(); }
	final public function cloneAllObject() { return parent::cloneAllObject(); }
	final protected function buildCloneSelect($existing_objs) { return parent::buildCloneSelect($existing_objs); }

	// -> ilAdministration
	final private function displayList() { return parent::displayList(); }
	final public function viewObject() { return parent::viewObject(); }
//	final private function setAdminTabs() { return parent::setAdminTabs(); }
	final public function getAdminTabs() { return parent::getAdminTabs(); }
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
	* create new object form
	*
	* @access	public
	*/
	function create()
	{
		global $rbacsystem, $tpl;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			$this->ctrl->setParameter($this, "new_type", $new_type);
			$this->initEditForm("create", $new_type);
			$tpl->setContent($this->form->getHTML());
			
		}
	}
	
	
	/**
	* save object
	*
	* @access	public
	*/
	function save()
	{
		global $rbacsystem, $objDefinition, $tpl, $lng;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
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
			$newObj->create();
			$newObj->createReference();
			$newObj->putInTree($_GET["ref_id"]);
			$newObj->setPermissions($_GET["ref_id"]);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->afterSave($newObj);
			return;
		}
		
		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
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
	*
	*/
	protected function cancel()
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	final function cancelCreation($in_rep = false)
	{
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
	}

	/**
	* edit object
	*
	* @access	public
	*/
	function editObject()
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
	function updateObject()
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
	
	function afterUpdate()
	{
		$this->ctrl->redirect($this);
	}

}
