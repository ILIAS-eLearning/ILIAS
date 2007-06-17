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
	function withReferences()
	{
		return $this->call_by_reference;
	}
	
	/**
	* if true, a creation screen is displayed
	* the current $_GET[ref_id] don't belong
	* to the current class!
	* the mode is determined in ilrepositorygui
	*/
	function setCreationMode($a_mode = true)
	{
		$this->creation_mode = $a_mode;
	}
	
	/**
	* get creation mode
	*/
	function getCreationMode()
	{
		return $this->creation_mode;
	}

	function assignObject()
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
	function prepareOutput()
	{
		global $ilLocator, $tpl;

		$this->tpl->getStandardTemplate();
		// administration prepare output
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			$this->addAdminLocatorItems();
			$tpl->setLocator();

			ilUtil::sendInfo();
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
		ilUtil::sendInfo();
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
		}
		
		return true;
	}
	

	/**
	* called by prepare output
	*/
	function setTitleAndDescription()
	{
		$this->tpl->setTitle($this->object->getTitle());
		$this->tpl->setDescription($this->object->getLongDescription());
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_".$this->object->getType()."_b.gif"), $this->lng->txt("obj_" . $this->object->getType()));
	}
	
	function showUpperIcon()
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


	/**
	* set admin tabs
	* @access	public
	*/
	function setTabs()
	{
		$this->getTabs($this->tabs_gui);
	}

	/**
	* set admin tabs
	* @access	public
	*/
	function setAdminTabs()
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
	function setActions($a_actions = "")
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
	function setSubObjects($a_sub_objects = "")
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
	function setLocator()
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
	function addLocatorItems()
	{
	}
	
	function omitLocator($a_omit = true)
	{
		$this->omit_locator = $a_omit;
	}

	/**
	* should be overwritten to add object specific items
	* (repository items are preloaded)
	*/
	function addAdminLocatorItems()
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
	* get object back from trash
	*
	* @access	public
	*/
	function undeleteObject()
	{
		global $rbacsystem, $log;

		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_POST["trash_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		foreach ($_POST["trash_id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);

			if (!$rbacsystem->checkAccess('create',$_GET["ref_id"],$obj_data->getType()))
			{
				$no_create[] = $id;
			}
		}

		if (count($no_create))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_paste")." ".
									 implode(',',$no_create),$this->ilias->error_obj->MESSAGE);
		}
		foreach ($_POST["trash_id"] as $id)
		{
			// INSERT AND SET PERMISSIONS
			$this->insertSavedNodes($id,$_GET["ref_id"],-(int) $id);
			// DELETE SAVED TREE
			$saved_tree = new ilTree(-(int)$id);
			$saved_tree->deleteTree($saved_tree->getNodeData($id));
			
		}

		//$this->object->notify("undelete", $_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$_POST["trash_id"]);
		
		ilUtil::sendInfo($this->lng->txt("msg_undeleted"),true);
		
		$this->ctrl->redirect($this, "view");
	}

	/**
	* recursive method to insert all saved nodes of the clipboard
	* (maybe this function could be moved to a rbac class ?)
	*
	* @access	private
	* @param	integer
	* @param	integer
	* @param	integer
	*/
	function insertSavedNodes($a_source_id,$a_dest_id,$a_tree_id)
	{
		global $rbacadmin, $rbacreview, $log;

		$this->tree->insertNode($a_source_id,$a_dest_id, IL_LAST_NODE, true);
		
		// write log entry
		$log->write("ilObjectGUI::insertSavedNodes(), restored ref_id $a_source_id from trash");

		// SET PERMISSIONS
		$parentRoles = $rbacreview->getParentRoleIds($a_dest_id);
		$obj =& $this->ilias->obj_factory->getInstanceByRefId($a_source_id);

		foreach ($parentRoles as $parRol)
		{
			$ops = $rbacreview->getOperationsOfRole($parRol["obj_id"], $obj->getType(), $parRol["parent"]);
			$rbacadmin->grantPermission($parRol["obj_id"],$ops,$a_source_id);
		}

		$saved_tree = new ilTree($a_tree_id);
		$childs = $saved_tree->getChilds($a_source_id);

		foreach ($childs as $child)
		{
			$this->insertSavedNodes($child["child"],$a_source_id,$a_tree_id);
		}
	}

	/**
	* confirmed deletion if object -> objects are moved to trash
	*
	* However objects are only removed from tree!! That means that the objects
	* itself stay in the database but are not linked in any context within the system.
	* Trash Bin Feature: Objects can be refreshed in trash
	*
	* @access	public
	*/
	function confirmedDeleteObject()
	{
		include_once './payment/classes/class.ilPaymentObject.php';

		global $rbacsystem, $rbacadmin, $log;
	
		// TODO: move checkings to deleteObject
		// TODO: cannot distinguish between obj_id from ref_id with the posted IDs.
		// change the form field and use instead of 'id' 'ref_id' and 'obj_id'. Then switch with varname
		
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_SESSION["saved_post"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL SELECTED OBJECTS
		foreach ($_SESSION["saved_post"] as $id)
		{
			if($this->tree->isDeleted($id))
			{
				$log->write(__METHOD__.': Object with ref_id: '.$id.' already deleted.');
				ilUtil::sendInfo('Object already deleted.',true);
				$this->ctrl->returnToParent($this);
			}
			
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS
			foreach ($subtree_nodes as $node)
			{
				if($node['type'] == 'rolf')
				{
					continue;
				}
				if (!$rbacsystem->checkAccess('delete',$node["child"]))
				{
					$not_deletable[] = $node["child"];
					$perform_delete = false;
				}
				else if(ilPaymentObject::_isBuyable($node['child']))
				{
					$buyable[] = $node['child'];
					$perform_delete = false;
				}
			}
		}

		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO DELETE
		if (count($not_deletable))
		{
			$not_deletable = implode(',',$not_deletable);
			session_unregister("saved_post");
			ilUtil::sendInfo($this->lng->txt("msg_no_perm_delete")." ".$not_deletable."<br/>".$this->lng->txt("msg_cancel"),true);

			$this->ctrl->returnToParent($this);
		}

		if(count($buyable))
		{
			foreach($buyable as $id)
			{
				$tmp_object =& ilObjectFactory::getInstanceByRefId($id);

				$titles[] = $tmp_object->getTitle();
			}
			$title_str = implode(',',$titles);

			ilUtil::sendInfo($this->lng->txt('msg_obj_not_deletable_sold').' '.$title_str,true);

			$_POST['id'] = $_SESSION['saved_post'];
			$this->deleteObject(true);

			return false;
		}

		// DELETE THEM
		if (!$all_node_data[0]["type"])
		{
			// OBJECTS ARE NO 'TREE OBJECTS'
			if ($rbacsystem->checkAccess('delete',$_GET["ref_id"]))
			{
				foreach($_SESSION["saved_post"] as $id)
				{
					$obj =& $this->ilias->obj_factory->getInstanceByObjId($id);
					$obj->delete();
					
					// write log entry
					$log->write("ilObjectGUI::confirmedDeleteObject(), deleted obj_id ".$obj->getId().
						", type: ".$obj->getType().", title: ".$obj->getTitle());
				}
			}
			else
			{
				unset($_SESSION["saved_post"]);
				ilUtil::sendInfo($this->lng->txt("no_perm_delete")."<br/>".$this->lng->txt("msg_cancel"),true);
				$this->ctrl->returnToParent($this);
			}
		}
		else
		{
			// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
			foreach ($_SESSION["saved_post"] as $id)
			{
				if($this->tree->isDeleted($id))
				{
					$log->write(__METHOD__.': Object with ref_id: '.$id.' already deleted.');
					ilUtil::sendInfo('Object already deleted.',true);
					$this->ctrl->returnToParent($this);
				}
				
				// DELETE OLD PERMISSION ENTRIES
				$subnodes = $this->tree->getSubtree($this->tree->getNodeData($id));

				foreach ($subnodes as $subnode)
				{
					$rbacadmin->revokePermission($subnode["child"]);
					// remove item from all user desktops
					$affected_users = ilUtil::removeItemFromDesktops($subnode["child"]);
				
					// TODO: inform users by mail that object $id was deleted
					//$mail->sendMail($id,$msg,$affected_users);
				}

				if(!$this->tree->saveSubTree($id, true))
				{
					$log->write(__METHOD__.': Object with ref_id: '.$id.' already deleted.');
					ilUtil::sendInfo('Object already deleted.',true);
					$this->ctrl->returnToParent($this);
				}

				// write log entry
				$log->write("ilObjectGUI::confirmedDeleteObject(), moved ref_id ".$id.
					" to trash");
				
				// remove item from all user desktops
				$affected_users = ilUtil::removeItemFromDesktops($id);

				// TODO: inform users by mail that object $id was deleted
				//$mail->sendMail($id,$msg,$affected_users);
			}
			// inform other objects in hierarchy about paste operation
			//$this->object->notify("confirmedDelete", $_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$_SESSION["saved_post"]);
		}
		
		if ($this->ilias->getSetting('enable_trash'))
		{
			// Feedback
			ilUtil::sendInfo($this->lng->txt("info_deleted"),true);
		
			$this->ctrl->returnToParent($this);
		}
		else  // skip trash if 'enable_trash' is 0
		{
			$_POST["trash_id"] = $_SESSION["saved_post"];
			
			$this->removeFromSystemObject();
		}
	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function cancelDeleteObject()
	{
		session_unregister("saved_post");

		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		$this->ctrl->returnToParent($this);

	}

	/**
	* remove objects from trash bin and all entries therefore every object needs a specific deleteObject() method
	*
	* @access	public
	*/
	function removeFromSystemObject()
	{
		global $rbacsystem, $log;
		
		// AT LEAST ONE OBJECT HAS TO BE CHOSEN.
		if (!isset($_POST["trash_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		//$this->object->notify("removeFromSystem", $_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$_POST["trash_id"]);

		// DELETE THEM
		foreach ($_POST["trash_id"] as $id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$saved_tree = new ilTree(-(int)$id);
			$node_data = $saved_tree->getNodeData($id);
			$subtree_nodes = $saved_tree->getSubTree($node_data);

			// remember already checked deleted node_ids
			$checked[] = -(int) $id;

			// dive in recursive manner in each already deleted subtrees and remove these objects too
			$this->removeDeletedNodes($id,$checked);

			foreach ($subtree_nodes as $node)
			{
				$node_obj =& $this->ilias->obj_factory->getInstanceByRefId($node["ref_id"]);

				// write log entry
				$log->write("ilObjectGUI::removeFromSystemObject(), delete obj_id: ".$node_obj->getId().
					", ref_id: ".$node_obj->getRefId().", type: ".$node_obj->getType().", ".
					"title: ".$node_obj->getTitle());
					
				$node_obj->delete();
			}

			// FIRST DELETE ALL ENTRIES IN RBAC TREE
			#$this->tree->deleteTree($node_data);
			// Use the saved tree object here (negative tree_id)
			$saved_tree->deleteTree($node_data);

			// write log entry
			$log->write("ilObjectGUI::removeFromSystemObject(), deleted tree, tree_id: ".$node_data["tree"].
				", child: ".$node_data["child"]);

		}
		
		ilUtil::sendInfo($this->lng->txt("msg_removed"),true);

		$this->ctrl->returnToParent($this);
	}

	/**
	* remove already deleted objects within the objects in trash
	* recursive function
	*
	* @access	public
	* @param	integer ref_id of source object
	* @param    boolean 
	*/
	function removeDeletedNodes($a_node_id, $a_checked, $a_delete_objects = true)
	{
		global $log, $ilDB;
		
		// @todo: belongs to app
		
		$q = "SELECT tree FROM tree WHERE parent= ".
			$ilDB->quote($a_node_id)." AND tree < 0";
		
		$r = $this->ilias->db->query($q);

		while($row = $r->fetchRow(DB_FETCHMODE_OBJECT))
		{
			// only continue recursion if fetched node wasn't touched already!
			if (!in_array($row->tree,$a_checked))
			{
				$deleted_tree = new ilTree($row->tree);
				$a_checked[] = $row->tree;

				$row->tree = $row->tree * (-1);
				$del_node_data = $deleted_tree->getNodeData($row->tree);
				$del_subtree_nodes = $deleted_tree->getSubTree($del_node_data);

				$this->removeDeletedNodes($row->tree,$a_checked);
			
				if ($a_delete_objects)
				{
					foreach ($del_subtree_nodes as $node)
					{
						$node_obj =& $this->ilias->obj_factory->getInstanceByRefId($node["ref_id"]);
						
						// write log entry
						$log->write("ilObjectGUI::removeDeletedNodes(), delete obj_id: ".$node_obj->getId().
							", ref_id: ".$node_obj->getRefId().", type: ".$node_obj->getType().", ".
							"title: ".$node_obj->getTitle());
							
						$node_obj->delete();
					}
				}
			
				$this->tree->deleteTree($del_node_data);
				
				// write log entry
				$log->write("ilObjectGUI::removeDeletedNodes(), deleted tree, tree_id: ".$del_node_data["tree"].
					", child: ".$del_node_data["child"]);
			}
		}
		
		return true;
	}

	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
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
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
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
	function cancelObject($in_rep = false)
	{
		session_unregister("saved_post");

		ilUtil::sendInfo($this->lng->txt("msg_cancel"),true);

		//ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		$return_location = $_GET["cmd_return_location"];
//echo "-".$_GET["cmd_return_location"]."-".$this->ctrl->getLinkTarget($this,$return_location);
		//ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
//echo "1";
		//if ($in_rep)
		//{
			$this->ctrl->returnToParent($this);
		//}
		//else
		//{
//echo "3";
		//	ilUtil::redirect($this->getReturnLocation("cancel",$this->ctrl->getTargetScript()."?".$this->link_params));
		//}
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $objDefinition;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		// create permission is already checked in createObject. This check here is done to prevent hacking attempts
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->MESSAGE);
		}
//echo ":".$_GET["new_type"].":".$_POST["new_type"].":";
		$module = $objDefinition->getModule($_GET["new_type"]);
		$module_dir = ($module == "")
			? ""
			: $module."/";

			// create and insert object in objecttree
		$class_name = "ilObj".$objDefinition->getClassName($_GET["new_type"]);
		include_once($module_dir."classes/class.".$class_name.".php");
		$newObj = new $class_name();
		$newObj->setType($_GET["new_type"]);
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
	* import new object form
	*
	* @access	public
	*/
	function importObject()
	{
		global $rbacsystem;
		// CHECK ACCESS 'write' of role folder
		// TODO: new_type will never be checked, if queried operation is not 'create'
		if (!$rbacsystem->checkAccess('write', $_GET["ref_id"], $_POST["new_type"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->WARNING);
		}

		$imp_obj =$this->objDefinition->getImportObjects($this->object->getType());

		if (!in_array($_POST["new_type"], $imp_obj))
		{
			$this->ilias->raiseError($this->lng->txt("no_import_available").
				" ".$this->lng->txt("obj_".$_POST["new_type"]),
				$this->ilias->error_obj->MESSAGE);
		}
		// no general implementation of this feature, the specialized classes
		// must provide further processing
	}


	/**
	* edit object
	*
	* @access	public
	*/
	function editObject()
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
	function displayEditForm($fields)
	{
		$this->getTemplateFile("edit");

		foreach ($fields as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			$this->tpl->parseCurrentBlock();
		}

		//$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		if (!$this->call_by_reference)
		{
			$this->ctrl->setParameter($this, "obj_id", $this->obj_id);
		}

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
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
	function updateObject()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->update = $this->object->update();

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		
		$this->afterUpdate();
	}
	
	function afterUpdate()
	{
		$this->ctrl->redirect($this);
	}

	/**
	* show permissions of current node
	*
	* @access	public
	*/
	function permObject()
	{
		include_once './classes/class.ilPermissionGUI.php';
		$perm_gui =& new ilPermissionGUI($this);
		
		// dirty work around to forward command in admin panel
		$this->ctrl->current_node = 1;
		$this->ctrl->setCmd('perm');
		$ret =& $this->ctrl->forwardCommand($perm_gui);
		
		return true;
	}

	/**
	* get form action for command (command is method name without "Object", e.g. "perm")
	* @param	string		$a_cmd			command
	* @param	string		$a_formaction	default formaction (is returned, if no special
	*										formaction was set)
	* @access	public
	* @return	string
	*/
	function getFormAction($a_cmd, $a_formaction ="")
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
	function setFormAction($a_cmd, $a_formaction)
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
	function getReturnLocation($a_cmd, $a_location ="")
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
	function setReturnLocation($a_cmd, $a_location)
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
	function getTargetFrame($a_cmd, $a_target_frame = "")
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
	function setTargetFrame($a_cmd, $a_target_frame)
	{
		$this->target_frame[$a_cmd] = "target=\"".$a_target_frame."\"";
	}

	/**
	* save permissions
	*
	* @access	public
	*/
	function permSaveObject()
	{
		include_once './classes/class.ilPermissionGUI.php';
		$perm_gui =& new ilPermissionGUI($this);
		
		// dirty work around to forward command in admin panel
		$this->ctrl->current_node = 1;
		$this->ctrl->setCmd('permSave');
		$ret =& $this->ctrl->forwardCommand($perm_gui);
		
		return true;
	}

	/**
	* display object list
	*
	* @access	public
 	*/
	function displayList()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		if (!$this->call_by_reference)
		{
			$this->ctrl->setParameter($this, "obj_id", $this->obj_id); 
		}
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->object->getTitle(),"icon_".$this->object->getType().".gif",
					   $this->lng->txt("obj_".$this->object->getType()));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));

		foreach ($this->data["cols"] as $val)
		{
			$header_names[] = $this->lng->txt($val);
		}

		$tbl->setHeaderNames($header_names);

		//$header_params = array("ref_id" => $this->ref_id);
		//$header_params = array("ref_id" => $this->ref_id);
		$header_params = $this->ctrl->getParameterArray($this, "view");
		$tbl->setHeaderVars($this->data["cols"],$header_params);
		$tbl->setColumnWidth(array("15","15","75%","25%"));

		// control
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($this->maxcount);

		$this->tpl->setVariable("COLUMN_COUNTS",count($this->data["cols"]));

		// footer
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));

		if (!empty($this->data["data"][0]))
		{
			//table cell
			for ($i=0; $i < count($this->data["data"]); $i++)
			{
				$data = $this->data["data"][$i];
				$ctrl = $this->data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow2","tblrow1");

				// surpress checkbox for particular object types AND the system role
				if (!$this->objDefinition->hasCheckbox($ctrl["type"]) or $ctrl["obj_id"] == SYSTEM_ROLE_ID or $ctrl["obj_id"] == SYSTEM_USER_ID or $ctrl["obj_id"] == ANONYMOUS_ROLE_ID)
				{
					$this->tpl->touchBlock("empty_cell");
				}
				else
				{
					// TODO: this object type depending 'if' could become really a problem!!
					if ($ctrl["type"] == "usr" or $ctrl["type"] == "role" or $ctrl["type"] == "rolt")
					{
						$link_id = $ctrl["obj_id"];
					}
					else
					{
						$link_id = $ctrl["ref_id"];
					}
					
					// dirty workaround to have ids for function showActions (checkbox toggle option)
					$this->ids[] = $link_id;
					
					$this->tpl->setCurrentBlock("checkbox");
					$this->tpl->setVariable("CHECKBOX_ID", $link_id);
					$this->tpl->setVariable("CSS_ROW", $css_row);
					$this->tpl->parseCurrentBlock();
				}

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data as $key => $val)
				{
					//build link
					$obj_type = ilObject::_lookupType($ctrl["ref_id"],true);
					$class_name = $this->objDefinition->getClassName($obj_type);
					$class = strtolower("ilObj".$class_name."GUI");
					$this->ctrl->setParameterByClass($class, "ref_id", $ctrl["ref_id"]);
					$this->ctrl->setParameterByClass($class, "obj_id", $ctrl["obj_id"]);
					$link = $this->ctrl->getLinkTargetByClass($class, "view");

					/*
					$n = 0;

					foreach ($ctrl as $key2 => $val2)
					{
						$link .= $key2."=".$val2;

						if ($n < count($ctrl)-1)
						{
					    	$link .= "&";
							$n++;
						}
					}*/

					if ($key == "name" || $key == "title")
					{
						$name_field = explode("#separator#",$val);
					}

					if ($key == "title" || $key == "name" || $key == "type")
					{
						$this->tpl->setCurrentBlock("begin_link");
						$this->tpl->setVariable("LINK_TARGET", $link);

						$this->tpl->parseCurrentBlock();
						$this->tpl->touchBlock("end_link");
					}

					// process clipboard information
					if (($key == "title" || $key == "name") and is_array(($_SESSION["clipboard"])))
					{
						// TODO: broken! fix me
						if (in_array($ctrl["ref_id"],$_SESSION["clipboard"]["ref_ids"]))
						{
                            switch($_SESSION["clipboard"]["cmd"])
							{
                                case "cut":
                                    $name_field[0] = "<del>".$name_field[0]."</del>";
                                    break;

                                case "copy":
                                    $name_field[0] = "<font color=\"green\">+</font>  ".$name_field[0];
                                    break;
                                        
                                case "link":
                                    $name_field[0] = "<font color=\"black\"><</font> ".$name_field[0];
                                    break;
                            }
         				}
         			}

					$this->tpl->setCurrentBlock("text");

					if ($key == "type")
					{
						$val = ilUtil::getImageTagByType($val,$this->tpl->tplPath);
					}

					if ($key == "name" || $key == "title")
					{
						$this->tpl->setVariable("TEXT_CONTENT", $name_field[0]);
						
						$this->tpl->setCurrentBlock("subtitle");
						$this->tpl->setVariable("DESC", ilUtil::shortenText($name_field[1],MAXLENGTH_OBJ_DESC,true));
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setVariable("TEXT_CONTENT", $val);
					}
					
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			} //for
		} //if is_array
		else
		{
            $tbl->disable("header");
			$tbl->disable("footer");
			
			$this->tpl->setCurrentBlock("text");
			
			// do not display an "error message" here
			// this confuses people in administratino
			//$this->tpl->setVariable("TEXT_CONTENT",
			//	$this->lng->txt("obj_not_found"));
			$this->tpl->setVariable("CSS_ROW", "tblrow1");
			$this->tpl->setVariable("TEXT_CONTENT",
				"&nbsp;");
			$this->tpl->parseCurrentBlock();
		}
		
		$this->showActions(true);
		
		// render table
		$tbl->render();
	}

	/**
	* list childs of current object
	*
	* @access	public
	*/
	function viewObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		//prepare objectlist
		$this->objectList = array();
		$this->data["data"] = array();
		$this->data["ctrl"] = array();
		$this->data["cols"] = array("", "type", "title", "last_change");

		$childs = $this->tree->getChilds($_GET["ref_id"], $_GET["order"], $_GET["direction"]);

		foreach ($childs as $key => $val)
	    {
			// visible
			if (!$rbacsystem->checkAccess("visible",$val["ref_id"]))
			{
				continue;
			}
			
			// hide object types in devmode
			if ($this->objDefinition->getDevMode($val["type"]))
			{
				continue;
			}
			
			// don't show administration in root node list
			if ($val["type"] == "adm")
			{
				continue;
			}

			//visible data part
			$this->data["data"][] = array(
										"type" => $val["type"],
										"title" => $val["title"]."#separator#".$val["desc"],
										//"description" => $val["desc"],
										"last_change" => $val["last_update"],
										"ref_id" => $val["ref_id"]
										);

			//control information is set below

	    } //foreach

		$this->maxcount = count($this->data["data"]);
		// sorting array
		$this->data["data"] = ilUtil::sortArray($this->data["data"],$_GET["sort_by"],$_GET["sort_order"]);
		$this->data["data"] = array_slice($this->data["data"],$_GET["offset"],$_GET["limit"]);

		// now compute control information
		foreach ($this->data["data"] as $key => $val)
		{
			$this->data["ctrl"][$key] = array(
											"type" => $val["type"],
											"ref_id" => $val["ref_id"]
											);

			unset($this->data["data"][$key]["ref_id"]);
						$this->data["data"][$key]["last_change"] = ilFormat::formatDate($this->data["data"][$key]["last_change"]);
		}

		$this->displayList();
	}

	/**
	* display deletion confirmation screen
	* only for referenced objects. For user,role & rolt overwrite this function in the appropriate
	* Object folders classes (ilObjUserFolderGUI,ilObjRoleFolderGUI)
	*
	* @access	public
 	*/
	function deleteObject($a_error = false)
	{
		if ($_GET["item_ref_id"] != "")
		{
			$_POST["id"] = array($_GET["item_ref_id"]);
		}

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "last_change");

		foreach ($_POST["id"] as $id)
		{
			// TODO: cannot distinguish between obj_id from ref_id with the posted IDs.
			// change the form field and use instead of 'id' 'ref_id' and 'obj_id'. Then switch with varname
			//if ($this->call_by_reference)
			//{
				$obj_data =& $this->ilias->obj_factory->getInstanceByRefId($id);
			//}
			//else
			//{
			//	$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);
			//}

			$this->data["data"]["$id"] = array(
												"type"        => $obj_data->getType(),
												"title"       => $obj_data->getTitle()."#separator#".$obj_data->getDescription()." ",	// workaround for empty desc
												"last_update" => $obj_data->getLastUpdateDate()
											);
		}

		$this->data["buttons"] = array( "confirmedDelete"  => $this->lng->txt("confirm"),
								  "cancelDelete"  => $this->lng->txt("cancel"));

		$this->getTemplateFile("confirm");

		if(!$a_error)
		{
			$msg = $this->lng->txt("info_delete_sure");
			
			if (!$this->ilias->getSetting('enable_trash'))
			{
				$msg .= "<br/>".$this->lng->txt("info_delete_warning_no_trash");
			}
			
			ilUtil::sendInfo($msg);
		}

		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
	
		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($this->data["data"] as $key => $value)
		{
			// BEGIN TABLE CELL
			foreach ($value as $key => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");

				// CREATE TEXT STRING
				if ($key == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				elseif ($key == "title")
				{
					$name_field = explode("#separator#",$cell_data);

					$this->tpl->setVariable("TEXT_CONTENT", "<b>".$name_field[0]."</b>");
						
					$this->tpl->setCurrentBlock("subtitle");
					$this->tpl->setVariable("DESC", $name_field[1]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* show trash content of object
	*
	* @access	public
 	*/
	function trashObject()
	{
		$objects = $this->tree->getSavedNodeData($_GET["ref_id"]);

		if (count($objects) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("msg_trash_empty"));
			$this->data["empty"] = true;
		}
		else
		{
			$this->data["empty"] = false;
			$this->data["cols"] = array("","type", "title", "last_change");

			foreach ($objects as $obj_data)
			{
				$this->data["data"]["$obj_data[child]"] = array(
															"checkbox"		=> "",
															"type"			=> $obj_data["type"],
															"title"			=> $obj_data["title"]."#separator#".$obj_data["desc"],
															"last_update"	=> $obj_data["last_update"]
									);
			}

			$this->data["buttons"] = array( "undelete"  => $this->lng->txt("btn_undelete"),
									  "removeFromSystem"  => $this->lng->txt("btn_remove_system"));
		}

		$this->getTemplateFile("confirm");

		if ($this->data["empty"] == true)
		{
			return;
		}
		
		/* TODO: fix message display in conjunction with ilUtil::sendInfo & raiseError functionality
		$this->tpl->addBlockfile("MESSAGE", "adm_trash", "tpl.message.html");
		$this->tpl->setCurrentBlock("adm_trash");
		$this->tpl->setVariable("MSG",$this->lng->txt("info_trash"));
		$this->tpl->parseCurrentBlock();
		*/
		//ilUtil::sendInfo($this->lng->txt("info_trash"));
		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		// BEGIN TABLE HEADER
		foreach ($this->data["cols"] as $key)
		{
			$this->tpl->setCurrentBlock("table_header");
			$this->tpl->setVariable("TEXT",$this->lng->txt($key));
			$this->tpl->parseCurrentBlock();
		}
		// END TABLE HEADER

		// BEGIN TABLE DATA
		$counter = 0;

		foreach ($this->data["data"] as $key1 => $value)
		{
			// BEGIN TABLE CELL
			foreach ($value as $key2 => $cell_data)
			{
				$this->tpl->setCurrentBlock("table_cell");
				// CREATE CHECKBOX
				if ($key2 == "checkbox")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::formCheckBox(0,"trash_id[]",$key1));
				}
				// CREATE TEXT STRING
				elseif ($key2 == "type")
				{
					$this->tpl->setVariable("TEXT_CONTENT",ilUtil::getImageTagByType($cell_data,$this->tpl->tplPath));
				}
				elseif ($key2 == "title")
				{
					$name_field = explode("#separator#",$cell_data);

					$this->tpl->setVariable("TEXT_CONTENT", "<b>".$name_field[0]."</b>");
						
					$this->tpl->setCurrentBlock("subtitle");
					$this->tpl->setVariable("DESC", $name_field[1]);
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("table_cell");
				}
				else
				{
					$this->tpl->setVariable("TEXT_CONTENT",$cell_data);
				}

				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("table_row");
			$this->tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
			// END TABLE CELL
		}
		// END TABLE DATA

		// BEGIN OPERATION_BTN
		foreach ($this->data["buttons"] as $name => $value)
		{
			$this->tpl->setCurrentBlock("operation_btn");
			$this->tpl->setVariable("BTN_NAME",$name);
			$this->tpl->setVariable("BTN_VALUE",$value);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* adds a local role
	* This method is only called when choose the option 'you may add local roles'. This option
	* is displayed in the permission settings dialogue for an object
	* TODO: this will be changed
	* @access	public
	*/
	function addRoleObject()
	{
		include_once './classes/class.ilPermissionGUI.php';
		$perm_gui =& new ilPermissionGUI($this);
		
		// dirty work around to forward command in admin panel
		$this->ctrl->current_node = 1;
		$this->ctrl->setCmd('addRole');
		$ret =& $this->ctrl->forwardCommand($perm_gui);
		
		return true;
	}

	/**
	* show possible action (form buttons)
	*
	* @param	boolean
	* @access	public
 	*/
	function showActions($with_subobjects = false)
	{
		$notoperations = array();
		// NO PASTE AND CLEAR IF CLIPBOARD IS EMPTY
		if (empty($_SESSION["clipboard"]))
		{
			$notoperations[] = "copy";			// disable copy operation!
			$notoperations[] = "paste";
			$notoperations[] = "clear";
		}
		// CUT COPY PASTE LINK DELETE IS NOT POSSIBLE IF CLIPBOARD IS FILLED
		if ($_SESSION["clipboard"])
		{
			$notoperations[] = "cut";
			$notoperations[] = "copy";
			$notoperations[] = "link";
		}

		$operations = array();

		if ($this->actions == "")
		{
			$d = $this->objDefinition->getActions($this->object->getType());
			//$d = $this->objDefinition->getActions($_GET["type"]);
		}
		else
		{
			$d = $this->actions;
		}

		foreach ($d as $row)
		{
			if (!in_array($row["name"], $notoperations))
			{
				$operations[] = $row;
			}
		}

		if (count($operations) > 0)
		{
			foreach ($operations as $val)
			{
				$this->tpl->setCurrentBlock("tbl_action_btn");
				$this->tpl->setVariable("BTN_NAME", $val["name"]);
				$this->tpl->setVariable("BTN_VALUE", $this->lng->txt($val["lng"]));
				$this->tpl->parseCurrentBlock();
			}
		}

		if ($with_subobjects === true)
		{
			//$this->showPossibleSubObjects();
		}
		
		if (!empty($this->ids) && count($operations) > 0)
		{
			// set checkbox toggles
			$this->tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$this->tpl->setVariable("JS_VARNAME","id");			
			$this->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($this->ids));
			$this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$this->tpl->parseCurrentBlock();
		}
		
		if (count($operations) > 0)
		{
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
 	*/
	function showPossibleSubObjects()
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

					if ($row["import"] == "1")	// import allowed?
					{
						$import = true;
					}
				}
			}
		}

		if (is_array($subobj))
		{
			// show import button if at least one
			// object type can be imported
			if ($import)
			{
				$this->tpl->setCurrentBlock("import_object");
				$this->tpl->setVariable("BTN_IMP", "import");
				$this->tpl->setVariable("TXT_IMP", $this->lng->txt("import"));
				$this->tpl->parseCurrentBlock();
			}

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
	function getTemplateFile($a_cmd,$a_type = "")
	{
		if (!$a_type)
		{
			$a_type = $this->type;
		}

		$template = "tpl.".$a_type."_".$a_cmd.".html";

		if (!$this->tpl->fileExists($template))
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
	function getTitlesByRefId($a_ref_ids)
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
	function getTabs(&$tabs_gui)
	{
		// please define your tabs here

	}

	// PROTECTED
	function __showButton($a_cmd,$a_text,$a_target = '')
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

	function hitsperpageObject()
	{
        $_SESSION["tbl_limit"] = $_POST["hitsperpage"];
        $_GET["limit"] = $_POST["hitsperpage"];
	}
	

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}
	
	/**
	 * standard implementation for tables
	 * use 'from' variable use different initial setting of table 
	 * 
	 */
	function __setTableGUIBasicData(&$tbl,&$result_set,$a_from = "")
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
	
	function __showClipboardTable($a_result_set,$a_from = "")
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
	
	function infoObject()
	{
		include_once './classes/class.ilPermissionGUI.php';
		$perm_gui =& new ilPermissionGUI($this);
		
		// dirty work around to forward command in admin panel
		$this->ctrl->current_node = 1;
		$this->ctrl->setCmd('info');
		$ret =& $this->ctrl->forwardCommand($perm_gui);
		
		return true;
	}
	
	function __buildRoleFilterSelect()
	{
		$action[1] = $this->lng->txt('all_roles');
		$action[2] = $this->lng->txt('all_global_roles');
		$action[3] = $this->lng->txt('all_local_roles');
		$action[4] = $this->lng->txt('linked_local_roles');
		$action[5] = $this->lng->txt('local_roles_this_object_only');
		
		return ilUtil::formSelect($_SESSION['perm_filtered_roles'],"filter",$action,false,true);
	}
	
	function __filterRoles($a_roles,$a_filter)
	{
		global $rbacreview;

		switch ($a_filter)
		{
			case 1:	// all roles
				return $a_roles;
				break;
			
			case 2:	// all global roles
				$arr_global_roles = $rbacreview->getGlobalRoles();
				$arr_remove_roles = array_diff(array_keys($a_roles),$arr_global_roles);

				foreach ($arr_remove_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}
				
				return $a_roles;
				break;			

			case 3:	// all local roles
				$arr_global_roles = $rbacreview->getGlobalRoles();

				foreach ($arr_global_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}
				
				return $a_roles;
				break;
				
			case 4:	// all roles
				return $a_roles;
				break;
				
			case 5:	// local role only at this position
				
				$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());
		
				if (!$role_folder)
				{
					return array();
				}
				
				$arr_local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);
				$arr_remove_roles = array_diff(array_keys($a_roles),$arr_local_roles);

				foreach ($arr_remove_roles as $role_id)
				{
					unset($a_roles[$role_id]);
				}

				return $a_roles;
				break;
		}

		return $a_roles;
	}

	function ownerObject()
	{
		include_once './classes/class.ilPermissionGUI.php';
		$perm_gui =& new ilPermissionGUI($this);
		
		// dirty work around to forward command in admin panel
		$this->ctrl->current_node = 1;
		$this->ctrl->setCmd('owner');
		$ret =& $this->ctrl->forwardCommand($perm_gui);
		
		return true;
	}

	function changeOwnerObject()
	{
		include_once './classes/class.ilPermissionGUI.php';
		$perm_gui =& new ilPermissionGUI($this);
		
		// dirty work around to forward command in admin panel
		$this->ctrl->current_node = 1;
		$this->ctrl->setCmd('changeOwner');
		$ret =& $this->ctrl->forwardCommand($perm_gui);
		
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
	function redirectToRefId($a_ref_id, $a_cmd = "")
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
	public function fillCloneTemplate($a_tpl_varname,$a_type)
	{
		global $objDefinition,$ilUser;
		
		if(!count($existing_objs = ilUtil::_getObjectsByOperations($a_type,'copy',$ilUser->getId(),-1)))
		{
			// No Objects with write permission found
			return false;
		}
		$this->tpl->addBlockFile(strtoupper($a_tpl_varname),strtolower($a_tpl_varname),'tpl.obj_duplicate.html');
	 	$this->ctrl->setParameter($this,'new_type',$a_type);
	 	$this->tpl->setVariable('FORMACTION_CLONE',$this->ctrl->getFormAction($this));
	 	$this->tpl->setVariable('TYPE_IMG3',ilUtil::getImagePath('icon_'.$a_type.'.gif'));
	 	$this->tpl->setVariable('ALT_IMG3',$this->lng->txt('obj_'.$a_type));
	 	$this->tpl->setVariable('TXT_DUPLICATE',$this->lng->txt('obj_'.$a_type.'_duplicate'));
	 	
	 	$this->tpl->setVariable('WIZARD_TXT_SELECT',$this->lng->txt('obj_'.$a_type));
	 	$this->tpl->setVariable('WIZARD_OBJS',$this->buildCloneSelect($existing_objs));
	 	
		if($this->copyWizardHasOptions(self::COPY_WIZARD_NEEDS_PAGE))
		{
		 	$this->tpl->setVariable('BTN_WIZARD',$this->lng->txt('btn_next'));
		 	$this->tpl->setVariable('CMD_WIZARD','cloneWizardPage');
		}
		else
		{
		 	$this->tpl->setVariable('BTN_WIZARD',$this->lng->txt('obj_'.$a_type.'_duplicate'));
		 	$this->tpl->setVariable('CMD_WIZARD','cloneAll');
		}
	 	
	 	$this->tpl->setVariable('WIZARD_TXT_CANCEL',$this->lng->txt('cancel'));
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
			ilUtil::sendInfo($this->lng->txt('select_one'));
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

		ilUtil::sendInfo($this->lng->txt("object_duplicated"),true);
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
	function getCenterColumnHTML()
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
	function getRightColumnHTML()
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
	function setColumnSettings($column_gui)
	{
		global $ilAccess;

		$column_gui->setRepositoryMode(true);
		$column_gui->setEnableEdit(false);
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$column_gui->setEnableEdit(true);
		}
	}

} // END class.ilObjectGUI
?>
