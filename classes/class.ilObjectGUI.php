<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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


/**
* Class ilObjectGUI
* Basic methods of all Output classes
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
*/
class ilObjectGUI
{
	const COPY_WIZARD_NEEDS_PAGE = 1;
	
	/**
	* ilias object
	* @var		object ilias
	* @access	private
	*/
	var $ilias;

	/**
	* object Definition Object
	* @var		object ilias
	* @access	private
	*/
	var $objDefinition;

	/**
	* template object
	* @var		object ilias
	* @access	private
	*/
	var $tpl;

	/**
	* tree object
	* @var		object ilias
	* @access	private
	*/
	var $tree;

	/**
	* language object
	* @var		object language (of ilObject)
	* @access	private
	*/
	var $lng;

	/**
	* output data
	* @var		data array
	* @access	private
	*/
	var $data;

	/**
	* object
	* @var          object
	* @access       private
	*/
	var $object;
	var $ref_id;
	var $obj_id;
	var $maxcount;			// contains number of child objects
	var $formaction;		// special formation (array "cmd" => "formaction")
	var $return_location;	// special return location (array "cmd" => "location")
	var $target_frame;	// special target frame (array "cmd" => "location")

	var $tab_target_script;
	var $actions;
	var $sub_objects;
	var $omit_locator = false;

	/**
	* Constructor
	* @access	public
	* @param	array	??
	* @param	integer	object id
	* @param	boolean	call be reference
	*/
	function ilObjectGUI($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $ilias, $objDefinition, $tpl, $tree, $ilCtrl, $ilErr, $lng, $ilTabs;

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

		$this->ilias =& $ilias;
		$this->objDefinition =& $objDefinition;
		$this->tpl =& $tpl;
		$this->html = "";
		$this->ctrl =& $ilCtrl;

		$params = array("ref_id");

		if (!$a_call_by_reference)
		{
			$params = array("ref_id","obj_id");
		}

		$this->ctrl->saveParameter($this, $params);

		$this->lng =& $lng;
		$this->tree =& $tree;
		$this->formaction = array();
		$this->return_location = array();
		$this->target_frame = array();
		$this->actions = "";
		$this->sub_objects = "";

		$this->data = $a_data;
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;
		$this->prepare_output = $a_prepare_output;
		$this->creation_mode = false;

		$this->ref_id = ($this->call_by_reference) ? $this->id : $_GET["ref_id"];
		$this->obj_id = ($this->call_by_reference) ? $_GET["obj_id"] : $this->id;

		if ($this->id != 0)
		{
			$this->link_params = "ref_id=".$this->ref_id;
		}

		// get the object
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

		// use global $lng instead, when creating new objects object is not available
		//$this->lng =& $this->object->lng;

		//prepare output
		if ($a_prepare_output)
		{
			$this->prepareOutput();
		}
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
				$this->prepareOutput();
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();
					
				break;
		}

		return true;
	}


	/**
	* determines wether objects are referenced or not (got ref ids or not)
	*/
	public function withReferences()
	{
		return $this->call_by_reference;
	}
	
	/**
	* if true, a creation screen is displayed
	* the current $_GET[ref_id] don't belong
	* to the current class!
	* the mode is determined in ilrepositorygui
	*/
	public function setCreationMode($a_mode = true)
	{
		$this->creation_mode = $a_mode;
	}
	
	/**
	* get creation mode
	*/
	public function getCreationMode()
	{
		return $this->creation_mode;
	}

	protected function assignObject()
	{
		// TODO: it seems that we always have to pass only the ref_id
//echo "<br>ilObjectGUIassign:".get_class($this).":".$this->id.":<br>";
		if ($this->id != 0)
		{
			if ($this->call_by_reference)
			{
				$this->object =& $this->ilias->obj_factory->getInstanceByRefId($this->id);
			}
			else
			{
				$this->object =& $this->ilias->obj_factory->getInstanceByObjId($this->id);
			}
		}
	}

	/**
	* prepare output
	*/
	protected function prepareOutput()
	{
		global $ilLocator, $tpl;

		$this->tpl->getStandardTemplate();
		// administration prepare output
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			$this->addAdminLocatorItems();
			$tpl->setLocator();

//			ilUtil::sendInfo();
			ilUtil::infoPanel();

			$this->setTitleAndDescription();

			if ($this->getCreationMode() != true)
			{
				$this->setAdminTabs();
				$this->showUpperIcon();
			}
			
			return false;
		}
		// set locator
		$this->setLocator();
		// catch feedback message
//		ilUtil::sendInfo();
		ilUtil::infoPanel();

		// in creation mode (parent) object and gui object
		// do not fit
		if ($this->getCreationMode() == true)
		{
			// get gui class of parent and call their title and description method
			$obj_type = ilObject::_lookupType($_GET["ref_id"],true);
			$class_name = $this->objDefinition->getClassName($obj_type);
			$class = strtolower("ilObj".$class_name."GUI");
			$class_path = $this->ctrl->lookupClassPath($class);
			include_once($class_path);
			$class_name = $this->ctrl->getClassForClasspath($class_path);
//echo "<br>instantiating parent for title and description";
			$this->parent_gui_obj = new $class_name("", $_GET["ref_id"], true, false);
			$this->parent_gui_obj->setTitleAndDescription();
		}
		else
		{
			// set title and description and title icon
			$this->setTitleAndDescription();
	
			// set tabs
			$this->setTabs();
			$this->showUpperIcon();

			// BEGIN WebDAV: Display Mount Webfolder icon.
			require_once 'Services/WebDAV/classes/class.ilDAVServer.php';
			if (ilDAVServer::_isActive() && 
				$this->ilias->account->getId() != ANONYMOUS_USER_ID)
			{
				$this->showMountWebfolderIcon();
			}
			// END WebDAV: Display Mount Webfolder icon.
		}
		
		return true;
	}
	

	/**
	* called by prepare output
	*/
	private function setTitleAndDescription()
	{
		$this->tpl->setTitle($this->object->getPresentationTitle());
		$this->tpl->setDescription($this->object->getLongDescription());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"), $this->lng->txt("obj_" . $this->object->getType()));
	}
	
	private function showUpperIcon()
	{
		global $tree, $tpl, $objDefinition;

		if ($this->object->getRefId() == "")
		{
			return;
		}

		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{		
			if ($this->object->getRefId() != ROOT_FOLDER_ID &&
				$this->object->getRefId() != SYSTEM_FOLDER_ID)
			{
				$par_id = $tree->getParentId($this->object->getRefId());
				$obj_type = ilObject::_lookupType($par_id,true);
				$class_name = $objDefinition->getClassName($obj_type);
				$class = strtolower("ilObj".$class_name."GUI");
				$this->ctrl->setParameterByClass($class, "ref_id", $par_id);
				$tpl->setUpperIcon($this->ctrl->getLinkTargetByClass($class, "view"));
				$this->ctrl->clearParametersByClass($class);
			}
			// link repository admin to admin settings
			else if ($this->object->getRefId() == ROOT_FOLDER_ID)
			{
				$this->ctrl->setParameterByClass("iladministrationgui", "ref_id", "");
				$this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");
				$tpl->setUpperIcon($this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
					ilFrameTargetInfo::_getFrame("MainContent"));
				$this->ctrl->clearParametersByClass("iladministrationgui");
			}
		}
		else
		{
			if ($this->object->getRefId() != ROOT_FOLDER_ID &&
				$this->object->getRefId() != SYSTEM_FOLDER_ID &&
				$_GET["obj_id"] == "")
			{
				if (defined("ILIAS_MODULE"))
				{
					$prefix = "../";
				}
				$par_id = $tree->getParentId($this->object->getRefId());
				$tpl->setUpperIcon($prefix."repository.php?cmd=frameset&ref_id=".$par_id,
					ilFrameTargetInfo::_getFrame("MainContent"));
			}
		}
	}
	// BEGIN WebDAV: Show Mount Webfolder Icon.
	private function showMountWebfolderIcon()
	{
		global $tree, $tpl, $objDefinition;

		if ($this->object->getRefId() == "")
		{
			return;
		}

		$tpl->setMountWebfolderIcon($this->object->getRefId());
	}
	// END WebDAV: Show Mount Webfolder Icon.


	/**
	* set admin tabs
	* @access	public
	*/
	private function setTabs()
	{
		$this->getTabs($this->tabs_gui);
	}

	/**
	* set admin tabs
	* @access	public
	*/
	private function setAdminTabs()
	{
		$this->getAdminTabs($this->tabs_gui);
	}

	/**
	* administration tabs show only permissions and trash folder
	*/
	function getAdminTabs(&$tabs_gui)
	{
		global $rbacsystem;

		if ($_GET["admin_mode"] == "repository")
		{
			$this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "settings");
			$tabs_gui->setBackTarget($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));
			$this->ctrl->setParameterByClass("iladministrationgui", "admin_mode", "repository");
		}
		
		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("view",
				$this->ctrl->getLinkTarget($this, "view"), array("", "view"), get_class($this));
		}
		
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), "", "ilpermissiongui");
		}
			
		if ($this->tree->getSavedNodeData($this->object->getRefId()))
		{
			$tabs_gui->addTarget("trash",
				$this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
		}
	}


	function getHTML()
	{
		return $this->html;
	}


	/**
	* set possible actions for objects in list. if actions are set
	* via this method, the values of objects.xml are ignored.
	*
	* @param	array		$a_actions		array with $command => $lang_var pairs
	*/
	private function setActions($a_actions = "")
	{
		if (is_array($a_actions))
		{
			foreach ($a_actions as $name => $lng)
			{
				$this->actions[$name] = array("name" => $name, "lng" => $lng);
			}
		}
		else
		{
			$this->actions = "";
		}
	}

	/**
	* set possible subobjects for this object. if subobjects are set
	* via this method, the values of objects.xml are ignored.
	*
	* @param	array		$a_actions		array with $command => $lang_var pairs
	*/
	private function setSubObjects($a_sub_objects = "")
	{
		if (is_array($a_sub_objects))
		{
			foreach ($a_sub_objects as $name => $options)
			{
				$this->sub_objects[$name] = array("name" => $name, "max" => $options["max"]);
			}
		}
		else
		{
			$this->sub_objects = "";
		}
	}

	/**
	* set Locator
	*
	* @param	object	tree object
	* @param	integer	reference id
	* @param	scriptanme that is used for linking;
	* @access	public
	*/
	protected function setLocator()
	{
		global $ilLocator, $tpl;
		
		if ($this->omit_locator)
		{
			return;
		}
		
		// todo: admin workaround
		// in the future, objectgui classes should not be called in
		// admin section anymore (rbac/trash handling in own classes)
		$ref_id = ($_GET["ref_id"] != "")
			? $_GET["ref_id"]
			: $this->object->getRefId();
		$ilLocator->addRepositoryItems($ref_id);
		
		if(!$this->creation_mode)
		{
			$this->addLocatorItems();
		}
		
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

		$tpl->setLocator();
	}
	
	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	protected function addLocatorItems()
	{
	}
	
	protected function omitLocator($a_omit = true)
	{
		$this->omit_locator = $a_omit;
	}

	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	protected function addAdminLocatorItems()
	{
		global $ilLocator;
		
		if ($_GET["admin_mode"] == "settings")	// system settings
		{		
			$ilLocator->addItem($this->lng->txt("administration"),
				$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
				ilFrameTargetInfo::_getFrame("MainContent"));
			if ($this->object->getRefId() != SYSTEM_FOLDER_ID)
			{
				$ilLocator->addItem($this->object->getTitle(),
					$this->ctrl->getLinkTarget($this, "view"));
			}
		}
		else							// repository administration
		{
			$this->ctrl->setParameterByClass("iladministrationgui",
				"ref_id", "");
			$this->ctrl->setParameterByClass("iladministrationgui",
				"admin_mode", "settings");
			//$ilLocator->addItem($this->lng->txt("administration"),
			//	$this->ctrl->getLinkTargetByClass("iladministrationgui", "frameset"),
			//	ilFrameTargetInfo::_getFrame("MainContent"));
			$this->ctrl->clearParametersByClass("iladministrationgui");
			$ilLocator->addAdministrationItems();
		}

	}

	/**
	* Get objects back from trash
	*/
	public function undeleteObject()
	{
		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		$ru->restoreObjects($_GET["ref_id"], $_POST["trash_id"]);
		$this->ctrl->redirect($this, "trash");
	}

	/**
	* confirmed deletion of object -> objects are moved to trash or deleted
	* immediately, if trash is disabled
	*/
	public function confirmedDeleteObject()
	{
		global $ilSetting, $lng;
		
		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		$ru->deleteObjects($_GET["ref_id"], $_SESSION["saved_post"]);
		session_unregister("saved_post");
		$this->ctrl->returnToParent($this);
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	public function cancelDeleteObject()
	{
		session_unregister("saved_post");
		$this->ctrl->returnToParent($this);
	}

	/**
	* remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method
	*
	* @access	public
	*/
	public function removeFromSystemObject()
	{
		global $rbacsystem, $log, $ilAppEventHandler, $lng;
		
		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		$ru->removeObjectsFromSystem($_POST["trash_id"]);
		$this->ctrl->redirect($this, "trash");
	}

	/**
	* create new object form
	*
	* @access	public
	*/
	public function createObject()
	{
		global $rbacsystem;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		else
		{
			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);

			$this->getTemplateFile("edit",$new_type);
			
			// show obj type image
			$this->tpl->setCurrentBlock("img");
			$this->tpl->setVariable("TYPE_IMG",
				ilUtil::getImagePath("icon_".$new_type.".gif"));
			$this->tpl->setVariable("ALT_IMG",
				$this->lng->txt("obj_".$new_type));
			$this->tpl->parseCurrentBlock();

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);

				if ($this->prepare_output)
				{
					$this->tpl->parseCurrentBlock();
				}
			}
			$this->ctrl->setParameter($this, "new_type", $new_type);
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "save"));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}
	

	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	public function cancelObject($in_rep = false)
	{
		session_unregister("saved_post");

		$this->ctrl->returnToParent($this);
	}

	/**
	* save object
	*
	* @access	public
	*/
	public function saveObject()
	{
		global $rbacsystem, $objDefinition;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
		}
//echo ":".$_GET["new_type"].":".$_POST["new_type"].":";
		$location = $objDefinition->getLocation($new_type);

			// create and insert object in objecttree
		$class_name = "ilObj".$objDefinition->getClassName($new_type);
		include_once($location."/class.".$class_name.".php");
		$newObj = new $class_name();
		$newObj->setType($new_type);
		$newObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$newObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		//$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		return $newObj;
	}

	/**
	* edit object
	*
	* @access	public
	*/
	public function editObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$fields = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$fields["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$fields["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		}
		else
		{
			$fields["title"] = ilUtil::prepareFormOutput($this->object->getTitle());
			$fields["desc"] = ilUtil::stripSlashes($this->object->getLongDescription());
		}

		$this->displayEditForm($fields);
	}

	/**
	* display edit form (usually called by editObject)
	*
	* @access	private
	* @param	array	$fields		key/value pairs of input fields
	*/
	protected function displayEditForm($fields)
	{
		$this->getTemplateFile("edit");

		foreach ($fields as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
//			$this->tpl->parseCurrentBlock();
		}

		//$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		if (!$this->call_by_reference)
		{
			$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		}

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "update"));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));

	}


	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	public function updateObject()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->update = $this->object->update();

		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		
		$this->afterUpdate();
	}
	
	protected function afterUpdate()
	{
		$this->ctrl->redirect($this);
	}


	/**
	* get form action for command (command is method name without "Object", e.g. "perm")
	* @param	string		$a_cmd			command
	* @param	string		$a_formaction	default formaction (is returned, if no special
	*										formaction was set)
	* @access	public
	* @return	string
	*/
	public function getFormAction($a_cmd, $a_formaction ="")
	{
		if ($this->formaction[$a_cmd] != "")
		{
			return $this->formaction[$a_cmd];
		}
		else
		{
			return $a_formaction;
		}
	}

	/**
	* set specific form action for command
	*
	* @param	string		$a_cmd			command
	* @param	string		$a_formaction	default formaction (is returned, if no special
	*										formaction was set)
	* @access	public 
	*/
	protected function setFormAction($a_cmd, $a_formaction)
	{
		$this->formaction[$a_cmd] = $a_formaction;
	}

	/**
	* get return location for command (command is method name without "Object", e.g. "perm")
	* @param	string		$a_cmd		command
	* @param	string		$a_location	default return location (is returned, if no special
	*									return location was set)
	* @access	public
	*/
	protected function getReturnLocation($a_cmd, $a_location ="")
	{
		if ($this->return_location[$a_cmd] != "")
		{
			return $this->return_location[$a_cmd];
		}
		else
		{
			return $a_location;
		}
	}

	/**
	* set specific return location for command
	* @param	string		$a_cmd		command
	* @param	string		$a_location	default return location (is returned, if no special
	*									return location was set)
	* @access	public
	*/
	protected function setReturnLocation($a_cmd, $a_location)
	{
//echo "-".$a_cmd."-".$a_location."-";
		$this->return_location[$a_cmd] = $a_location;
	}

	/**
	* get target frame for command (command is method name without "Object", e.g. "perm")
	* @param	string		$a_cmd			command
	* @param	string		$a_target_frame	default target frame (is returned, if no special
	*										target frame was set)
	* @access	public
	*/
	protected function getTargetFrame($a_cmd, $a_target_frame = "")
	{
		if ($this->target_frame[$a_cmd] != "")
		{
			return $this->target_frame[$a_cmd];
		}
		elseif (!empty($a_target_frame))
		{
			return "target=\"".$a_target_frame."\"";
		}
		else
		{
			return;
		}
	}

	/**
	* set specific target frame for command
	* @param	string		$a_cmd			command
	* @param	string		$a_target_frame	default target frame (is returned, if no special
	*										target frame was set)
	* @access	public
	*/
	protected function setTargetFrame($a_cmd, $a_target_frame)
	{
		$this->target_frame[$a_cmd] = "target=\"".$a_target_frame."\"";
	}

	// BEGIN Security: Hide objects which aren't accessible by the user.
	public function isVisible($a_ref_id,$a_type)
	{
		global $rbacsystem, $ilBench;
		
		$ilBench->start("Explorer", "setOutput_isVisible");
		$visible = $rbacsystem->checkAccess('visible,read',$a_ref_id);
		
		if ($visible && $a_type == 'crs') {
			global $tree;
			if($crs_id = $tree->checkForParentType($a_ref_id,'crs'))
			{
				if(!$rbacsystem->checkAccess('write',$crs_id))
				{
					// Show only activated courses
					$tmp_obj =& ilObjectFactory::getInstanceByRefId($crs_id,false);
	
					if(!$tmp_obj->isActivated())
					{
						unset($tmp_obj);
						$visible = false;
					}
					if(($crs_id != $a_ref_id) and $tmp_obj->isArchived())
					{
						$visible = false;
					}
					// Show only activated course items
					include_once "./course/classes/class.ilCourseItems.php";
	
					if(($crs_id != $a_ref_id) and (!ilCourseItems::_isActivated($a_ref_id)))
					{
						$visible = false;
					}
				}
			}
		}
		
		$ilBench->stop("Explorer", "setOutput_isVisible");

		return $visible;
	}
	// END Security: Hide objects which aren't accessible by the user.

	/**
	* list childs of current object
	*
	* @access	public
	*/
	public function viewObject()
	{
		global $rbacsystem, $tpl;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		// BEGIN ChangeEvent: record read event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			global $ilUser;
			ilChangeEvent::_recordReadEvent($this->object->getId(), $ilUser->getId());
		}
		// END ChangeEvent: record read event.

		include_once("./Services/Repository/classes/class.ilAdminSubItemsTableGUI.php");
		if (!$this->call_by_reference)
		{
			$this->ctrl->setParameter($this, "obj_id", $this->obj_id); 
		}
		$itab = new ilAdminSubItemsTableGUI($this, "view", $_GET["ref_id"]);
		
		$tpl->setContent($itab->getHTML());
	}

	/**
	* Display deletion confirmation screen.
	* Only for referenced objects. For user,role & rolt overwrite this function in the appropriate
	* Object folders classes (ilObjUserFolderGUI,ilObjRoleFolderGUI)
	*
	* @access	public
 	*/
	public function deleteObject($a_error = false)
	{
		global $tpl, $ilCtrl;
		
		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		// SAVE POST VALUES (get rid of this
		$_SESSION["saved_post"] = $_POST["id"];

		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		if (!$ru->showDeleteConfirmation($_POST["id"], $a_error))
		{
			$ilCtrl->returnToParent($this);
		}
	}

	/**
	* Show trash content of object
	*
	* @access	public
 	*/
	public function trashObject()
	{
		global $tpl;

		include_once("./Services/Repository/classes/class.ilRepUtilGUI.php");
		$ru = new ilRepUtilGUI($this);
		$ru->showTrashTable($_GET["ref_id"]);
	}

	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
 	*/
	protected function showPossibleSubObjects()
	{
		if ($this->sub_objects == "")
		{
			$d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
		}
		else
		{
			$d = $this->sub_objects;
		}

		$import = false;

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;

				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}

				if ($row["max"] == "" || $count < $row["max"])
				{
					$subobj[] = $row["name"];
/* deprecated
					if ($row["import"] == "1")	// import allowed?
					{
						$import = true;
					}
*/
				}
			}
		}

		if (is_array($subobj))
		{
			// show import button if at least one
			// object type can be imported
/* deprecated
			if ($import)
			{
				$this->tpl->setCurrentBlock("import_object");
				$this->tpl->setVariable("BTN_IMP", "import");
				$this->tpl->setVariable("TXT_IMP", $this->lng->txt("import"));
				$this->tpl->parseCurrentBlock();
			}
*/

			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$this->tpl->setCurrentBlock("add_object");
			$this->tpl->setVariable("SELECT_OBJTYPE", $opts);
			$this->tpl->setVariable("BTN_NAME", "create");
			$this->tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* get a template blockfile
	* format: tpl.<objtype>_<command>.html
	*
	* @param	string	command
	* @param	string	object type definition
	* @access	public
 	*/
	public function getTemplateFile($a_cmd,$a_type = "")
	{
		if (!$a_type)
		{
			$a_type = $this->type;
		}

		$template = "tpl.".$a_type."_".$a_cmd.".html";

		if (!$this->tpl->fileExists($template) &&
			!file_exists("./templates/default/".$template))
		{
			$template = "tpl.obj_".$a_cmd.".html";
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", $template,$a_in_module);

	}

	/**
	* get Titles of objects
	* this method is used for error messages in methods cut/copy/paste
	*
	* @param	array	Array of ref_ids (integer)
	* @return   array	Array of titles (string)
	* @access	private
 	*/
	protected function getTitlesByRefId($a_ref_ids)
	{
		foreach ($a_ref_ids as $id)
		{
			// GET OBJECT TITLE
			$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($id);
			$title[] = $tmp_obj->getTitle();
			unset($tmp_obj);
		}

		return $title ? $title : array();
	}

	/**
	* get tabs
	* abstract method.
	* @abstract	overwrite in derived GUI class of your object type
	* @access	public
	* @param	object	instance of ilTabsGUI
	*/
	protected function getTabs(&$tabs_gui)
	{
		// please define your tabs here

	}

	// PROTECTED
	protected function __showButton($a_cmd,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,$a_cmd));
		$this->tpl->setVariable("BTN_TXT",$a_text);
		if($a_target)
		{
			$this->tpl->setVariable("BTN_TARGET",$a_target);
		}

		$this->tpl->parseCurrentBlock();
	}

	protected function hitsperpageObject()
	{
        $_SESSION["tbl_limit"] = $_POST["hitsperpage"];
        $_GET["limit"] = $_POST["hitsperpage"];
	}
	

	protected function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}
	
	/**
	 * standard implementation for tables
	 * use 'from' variable use different initial setting of table 
	 * 
	 */
	protected function __setTableGUIBasicData(&$tbl,&$result_set,$a_from = "")
	{
		switch ($a_from)
		{
			case "clipboardObject":
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				$tbl->disable("footer");
				break;

			default:
				$offset = $_GET["offset"];
				$order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}
	
	protected function __showClipboardTable($a_result_set,$a_from = "")
	{
    	$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getTargetScript()."?".$this->link_params."&cmd=post");
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","paste");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("insert_object_here"));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","clear");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("clear_clipboard"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",3);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("spacer.gif"));
		$tpl->parseCurrentBlock();
		
		$tbl->setTitle($this->lng->txt("clipboard"),"icon_typ_b.gif",$this->lng->txt("clipboard"));
		$tbl->setHeaderNames(array($this->lng->txt('obj_type'),
								   $this->lng->txt('title'),
								   $this->lng->txt('action')));
		$tbl->setHeaderVars(array('type',
                                  'title',
								  'act'),
							array('ref_id' => $this->object->getRefId(),
								  'cmd' => 'clipboard',
								  'cmdClass' => $_GET['cmdClass'],
								  'cmdNode' => $_GET['cmdNode']));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,$a_from);
		$tbl->render();
		
		$this->tpl->setVariable("RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	/**
	* redirects to (repository) view per ref id
	* usually to a container and usually used at
	* the end of a save/import method where the object gui
	* type (of the new object) doesn't match with the type
	* of the current $_GET["ref_id"] value
	*
	* @param	int		$a_ref_id		reference id
	*/
	protected function redirectToRefId($a_ref_id, $a_cmd = "")
	{
		$obj_type = ilObject::_lookupType($a_ref_id,true);
		$class_name = $this->objDefinition->getClassName($obj_type);
		$class = strtolower("ilObj".$class_name."GUI");
		$this->ctrl->redirectByClass(array("ilrepositorygui", $class), $a_cmd);
	}
	
	// Object Cloning
	/**
	 * Fill object clone template
	 * This method can be called from any object GUI class that wants to offer object cloning. 
	 *
	 * @access public
	 * @param string template variable name that will be filled
	 * @param string type of new object
	 * 
	 */
	protected function fillCloneTemplate($a_tpl_varname,$a_type)
	{
		global $objDefinition,$ilUser,$ilSetting;
		
		$max_entries = $ilSetting->get('search_max_hits',100);
		
		if(!count($existing_objs = ilUtil::_getObjectsByOperations($a_type,'copy',$ilUser->getId(),$max_entries)))
		{
			// No Objects with copy permission found
			return false;
		}
		
		if(count($existing_objs) >= $max_entries)
		{
			return $this->fillCloneSearchTemplate($a_tpl_varname,$a_type);
		}
		unset($_SESSION['wizard_search_title']);
		$this->tpl->addBlockFile(strtoupper($a_tpl_varname),strtolower($a_tpl_varname),'tpl.obj_duplicate.html');
	 	$this->ctrl->setParameter($this,'new_type',$a_type);
	 	$this->tpl->setVariable('TYPE_IMG3',ilUtil::getImagePath('icon_'.$a_type.'.gif'));
	 	$this->tpl->setVariable('ALT_IMG3',$this->lng->txt('obj_'.$a_type));
	 	$this->tpl->setVariable('TXT_DUPLICATE',$this->lng->txt('obj_'.$a_type.'_duplicate'));
	 	
	 	$this->tpl->setVariable('WIZARD_TXT_SELECT',$this->lng->txt('obj_'.$a_type));
	 	$this->tpl->setVariable('WIZARD_OBJS',$this->buildCloneSelect($existing_objs));
	 	
		if($this->copyWizardHasOptions(self::COPY_WIZARD_NEEDS_PAGE))
		{
		 	$this->tpl->setVariable('FORMACTION_CLONE',$this->ctrl->getFormAction($this,'cloneWizardPage'));
		 	$this->tpl->setVariable('BTN_WIZARD',$this->lng->txt('btn_next'));
		 	$this->tpl->setVariable('CMD_WIZARD','cloneWizardPage');
		}
		else
		{
		 	$this->tpl->setVariable('FORMACTION_CLONE',$this->ctrl->getFormAction($this,'cloneAll'));
		 	$this->tpl->setVariable('BTN_WIZARD',$this->lng->txt('obj_'.$a_type.'_duplicate'));
		 	$this->tpl->setVariable('CMD_WIZARD','cloneAll');
		}
	 	
	 	$this->tpl->setVariable('WIZARD_TXT_CANCEL',$this->lng->txt('cancel'));
	}
	
	/**
	 * Add an object search in case the number of existing objects is too big
	 * to offer a selection list.
	 * 
 	 * @param string template variable name that will be filled
	 * @param string type of new object
	 * @access public
	 */
	protected function fillCloneSearchTemplate($a_tpl_varname,$a_type)
	{
		unset($_SESSION['wizard_search_title']);
		
		$this->tpl->addBlockFile(strtoupper($a_tpl_varname),strtolower($a_tpl_varname),'tpl.obj_duplicate_search.html');
	 	$this->ctrl->setParameter($this,'new_type',$a_type);
	 	$this->tpl->setVariable('FORMACTION_CLONE',$this->ctrl->getFormAction($this,'searchCloneSource'));
	 	$this->tpl->setVariable('TYPE_IMG3',ilUtil::getImagePath('icon_'.$a_type.'.gif'));
	 	$this->tpl->setVariable('ALT_IMG3',$this->lng->txt('obj_'.$a_type));
	 	$this->tpl->setVariable('TXT_DUPLICATE',$this->lng->txt('obj_'.$a_type.'_duplicate'));
	 	
	 	$this->tpl->setVariable('WIZARD_TXT_TITLE',$this->lng->txt('title'));
	 	$this->tpl->setVariable('WIZARD_TITLE',ilUtil::prepareFormOutput($_POST['wizard_search_title'],true));
	 	$this->tpl->setVariable('WIZARD_TITLE_INFO',$this->lng->txt('wizard_title_info'));
	 	
	 	$this->tpl->setVariable('BTN_WIZARD',$this->lng->txt('btn_next'));
	 	$this->tpl->setVariable('CMD_WIZARD','searchCloneSource');
	 	$this->tpl->setVariable('WIZARD_TXT_CANCEL',$this->lng->txt('cancel'));
	}
	
	/**
	 * Search clone source by title
	 *
	 * @access protected
	 */
	protected function searchCloneSourceObject()
	{
		global $tree,$ilObjDataCache;
		
		$this->ctrl->setParameter($this,'new_type',$_REQUEST['new_type']);
		
		$_SESSION['wizard_search_title'] = ilUtil::stripSlashes($_POST['wizard_search_title']) ? 
			ilUtil::stripSlashes($_POST['wizard_search_title']) :
			$_SESSION['wizard_search_title'];
		
		$this->lng->loadLanguageModule('search');
		include_once './Services/Search/classes/class.ilQueryParser.php';
		$query_parser =& new ilQueryParser(ilUtil::stripSlashes($_SESSION['wizard_search_title']));
		$query_parser->setMinWordLength(1);
		$query_parser->setCombination(QP_COMBINATION_AND);
		$query_parser->parse();
		if(!$query_parser->validate())
		{
			ilUtil::sendInfo($query_parser->getMessage());
			$this->createObject();
			return true;
		}

		// only like search since fulltext does not support search with less than 3 characters
		include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
		$object_search =& new ilLikeObjectSearch($query_parser);

		$object_search->setFilter(array($_REQUEST['new_type']));
		$res = $object_search->performSearch();
		$res->setRequiredPermission('copy');

		// Add callback functions to receive only search_max_hits valid results
		$res->filter(ROOT_FOLDER_ID,true);
		
		if(!count($results = $res->getResultsByObjId()))
		{
			ilUtil::sendInfo($this->lng->txt('search_no_match'));
			$this->createObject();
			return true;
		}

	 	$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.obj_duplicate_search_results.html');

		$num_rows = 0;
		foreach($results as $obj_id => $references)
		{
			foreach($references as $ref_id)
			{
				$this->tpl->setCurrentBlock('ref_row');
				$this->tpl->setVariable('RADIO_REF',ilUtil::formRadioButton(0,'clone_source',$ref_id));
				$this->tpl->setVariable('TXT_PATH',$this->lng->txt('path'));
				
				$path_arr = $tree->getPathFull($ref_id,ROOT_FOLDER_ID);
				$counter = 0;
				$path = '';
				foreach($path_arr as $data)
				{
					if($counter++)
					{
						$path .= " -> ";
					}
					$path .= $data['title'];
				}
				$this->tpl->setVariable('PATH',$path);
				$this->tpl->parseCurrentBlock();
				break;
			}
			if(strlen($desc = $ilObjDataCache->lookupDescription($obj_id)))
			{
				$this->tpl->setCurrentBlock('desc');
				$this->tpl->setVariable('DESCRIPTION',$desc);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock('res_row');
			$this->tpl->setVariable('TBLROW',ilUtil::switchColor($num_rows++,'tblrow1','tblrow2'));
			$this->tpl->setVariable('TITLE',$ilObjDataCache->lookupTitle($obj_id));
			$this->tpl->setVariable('REFERENCES',$this->lng->txt('pathes'));
			$this->tpl->parseCurrentBlock();
		}

	 	$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this,'cancel'));
	 	$this->tpl->setVariable('TYPE_IMG',ilUtil::getImagePath('icon_'.$_REQUEST['new_type'].'.gif'));
	 	$this->tpl->setVariable('ALT_IMG',$this->lng->txt('obj_'.$_REQUEST['new_type']));
	 	$this->tpl->setVariable('TXT_DUPLICATE',$this->lng->txt('obj_'.$_REQUEST['new_type'].'_duplicate'));
	 	$this->tpl->setVariable('INFO_DUPLICATE',$this->lng->txt('wizard_search_list'));
		if($this->copyWizardHasOptions(self::COPY_WIZARD_NEEDS_PAGE))
		{
		 	$this->tpl->setVariable('BTN_COPY',$this->lng->txt('btn_next'));
		 	$this->tpl->setVariable('CMD_COPY','cloneWizardPage');
		}
		else
		{
		 	$this->tpl->setVariable('BTN_COPY',$this->lng->txt('obj_'.$_REQUEST['new_type'].'_duplicate'));
		 	$this->tpl->setVariable('CMD_COPY','cloneAll');
		}
		$this->tpl->setVariable('BTN_BACK',$this->lng->txt('btn_back'));
		return true;
	}
	
	/**
	 * Clone single (not container object)
	 * Method is overwritten in ilContainerGUI
	 *
	 * @access public
	 */
	public function cloneAllObject()
	{
		include_once('classes/class.ilLink.php');
		include_once('Services/CopyWizard/classes/class.ilCopyWizardOptions.php');
		
		global $ilAccess,$ilErr,$rbacsystem,$ilUser;
		
	 	$new_type = $_REQUEST['new_type'];
	 	if(!$rbacsystem->checkAccess('create',(int) $_GET['ref_id'],$new_type))
	 	{
	 		$ilErr->raiseError($this->lng->txt('permission_denied'));
	 	}
		if(!(int) $_REQUEST['clone_source'])
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->createObject();
			return false;
		}
		if(!$ilAccess->checkAccess('write','',(int) $_REQUEST['clone_source'],$new_type))
		{
	 		$ilErr->raiseError($this->lng->txt('permission_denied'));
		}
		
		// Save wizard options
		$copy_id = ilCopyWizardOptions::_allocateCopyId();
		$wizard_options = ilCopyWizardOptions::_getInstance($copy_id);
		$wizard_options->saveOwner($ilUser->getId());
		$wizard_options->saveRoot((int) $_REQUEST['clone_source']);
		
		$options = $_POST['cp_options'] ? $_POST['cp_options'] : array();
		foreach($options as $source_id => $option)
		{
			$wizard_options->addEntry($source_id,$option);
		}
		$wizard_options->read();
		
		$orig = ilObjectFactory::getInstanceByRefId((int) $_REQUEST['clone_source']);
		$new_obj = $orig->cloneObject((int) $_GET['ref_id'],$copy_id);
		
		// Delete wizard options
		$wizard_options->deleteAll();

		ilUtil::sendSuccess($this->lng->txt("object_duplicated"),true);
		ilUtil::redirect(ilLink::_getLink($new_obj->getRefId()));
	}
	
	/**
	 * Check if there is any modules specific option
	 *
	 * @access public
	 * @param int wizard mode COPY_WIZARD_GENERAL,COPY_WIZARD_NEEDS_PAGE, COPY_WIZARD_OBJ_SPECIFIC
	 * 
	 */
	public function copyWizardHasOptions($a_mode)
	{
	 	return false;
	}
	
	/**
	 * Build a select box for clonable objects (permission write)
	 *
	 * @access protected
	 * @param string obj_type 
	 */
	protected function buildCloneSelect($existing_objs)
	{
 		$options = ilObject::_prepareCloneSelection($existing_objs,$_REQUEST['new_type']);
	 	return ilUtil::formSelect((int) $_REQUEST['clone_source'],'clone_source',$options,false,true);
	}
	
	/**
	* Get center column
	*/
	protected function getCenterColumnHTML()
	{
		global $ilCtrl, $ilAccess;

		include_once("Services/Block/classes/class.ilColumnGUI.php");

		$obj_id = ilObject::_lookupObjId($this->object->getRefId());
		$obj_type = ilObject::_lookupType($obj_id);

		if ($ilCtrl->getNextClass() != "ilcolumngui")
		{
			// normal command processing	
			return $this->getContent();
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				//if ($column_gui->getScreenMode() != IL_SCREEN_SIDE)
				if (ilColumnGUI::getScreenMode() != IL_SCREEN_SIDE)
				{
					// right column wants center
					if (ilColumnGUI::getCmdSide() == IL_COL_RIGHT)
					{
						$column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
						$this->setColumnSettings($column_gui);
						$this->html = $ilCtrl->forwardCommand($column_gui);
					}
					// left column wants center
					if (ilColumnGUI::getCmdSide() == IL_COL_LEFT)
					{
						$column_gui = new ilColumnGUI($obj_type, IL_COL_LEFT);
						$this->setColumnSettings($column_gui);
						$this->html = $ilCtrl->forwardCommand($column_gui);
					}
				}
				else
				{
					// normal command processing	
					return $this->getContent();
				}
			}
		}
	}
	
	/**
	* Display right column
	*/
	protected function getRightColumnHTML()
	{
		global $ilUser, $lng, $ilCtrl, $ilAccess;
		
		$obj_id = ilObject::_lookupObjId($this->object->getRefId());
		$obj_type = ilObject::_lookupType($obj_id);

		include_once("Services/Block/classes/class.ilColumnGUI.php");
		$column_gui = new ilColumnGUI($obj_type, IL_COL_RIGHT);
		
		if ($column_gui->getScreenMode() == IL_SCREEN_FULL)
		{
			return "";
		}
		
		$this->setColumnSettings($column_gui);
		
		if ($ilCtrl->getNextClass() == "ilcolumngui" &&
			$column_gui->getCmdSide() == IL_COL_RIGHT &&
			$column_gui->getScreenMode() == IL_SCREEN_SIDE)
		{
			$html = $ilCtrl->forwardCommand($column_gui);
		}
		else
		{
			if (!$ilCtrl->isAsynch())
			{
				$html = $ilCtrl->getHTML($column_gui);
			}
		}

		return $html;
	}

	/**
	* May be overwritten in subclasses.
	*/
	protected function setColumnSettings($column_gui)
	{
		global $ilAccess;

		$column_gui->setRepositoryMode(true);
		$column_gui->setEnableEdit(false);
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$column_gui->setEnableEdit(true);
		}
	}
	
	protected function checkPermission($a_perm, $a_cmd = "")
	{
		global $ilAccess, $lng, $PHP_SELF;
		
		if (!is_object($this->object))
		{
			return;
		}

		if (!$ilAccess->checkAccess($a_perm, $a_cmd, $this->object->getRefId()))
		{
			$_SESSION["il_rep_ref_id"] = "";
			ilUtil::sendFailure($lng->txt("permission_denied"), true);

			if (!is_int(strpos($PHP_SELF, "goto.php")))
			{
				ilUtil::redirect("goto.php?target=".$this->object->getType()."_".
					$this->object->getRefId());
			}
			else	// we should never be here
			{
				die("Permission Denied.");
			}
		}
	}
	
} // END class.ilObjectGUI (3.10: 2896 loc)
?>
