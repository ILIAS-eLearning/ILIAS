<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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
* @package ilias-core
*/
class ilObjectGUI
{
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

	/**
	* Constructor
	* @access	public
	* @param	array	??
	* @param	integer	object id
	* @param	boolean	call be reference
	*/
	function ilObjectGUI($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
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
		$this->tab_target_script = "adm_object.php";
		$this->actions = "";
		$this->sub_objects = "";

		$this->data = $a_data;
		$this->id = $a_id;
		$this->call_by_reference = $a_call_by_reference;
		$this->prepare_output = $a_prepare_output;

		$this->ref_id = ($this->call_by_reference) ? $this->id : $_GET["ref_id"];
		$this->obj_id = ($this->call_by_reference) ? $_GET["obj_id"] : $this->id;

		if ($this->id != 0)
		{
			$this->link_params = "ref_id=".$this->ref_id;
		}

		// get the object
		$this->assignObject();

		// use global $lng instead, when creating new objects object is not available
		//$this->lng =& $this->object->lng;

		//prepare output
		if ($a_prepare_output)
		{
			$this->prepareOutput();
		}

		// set default sort column
		if (empty($_GET["sort_by"]))
		{
			// TODO: init sort_by better in obj class?
			if ($this->object->getType() == "usrf"
				or $this->object->getType() == "rolf")
			{
				$_GET["sort_by"] = "name";
			}
			elseif ($this->object->getType() == "typ")
			{
				$_GET["sort_by"] = "operation";
			}
			elseif ($this->object->getType() == "lngf")
			{
				$_GET["sort_by"] = "language";
			}
			else
			{
				$_GET["sort_by"] = "title";
			}
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

	function assignObject()
	{
		// TODO: it seems that we always have to pass only the ref_id
//echo "assign:".get_class($this).":".$this->id.":<br>";
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

	function prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$title = $this->object->getTitle();

		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setAdminTabs($_POST["new_type"]);
		$this->setLocator();

	}

	/**
	* set admin tabs
	* @access	public
	*/
	function setAdminTabs($a_new_type = 0)
	{
		// temp. for groups and systemfolder
		// TODO: use this style for all objects
		if (($this->object->getType() == "grp" or $this->object->getType() == "adm"
			or $this->object->getType() == "sty" or $this->object->getType() == "svy"
			or $this->object->getType() == "spl" or $this->object->getType() == "tst"
			or $this->object->getType() == "qpl" or $this->object->getType() == "exc") &&
			(
				$this->ctrl->getTargetScript() != 'adm_object.php' ||
				$this->object->getType() == "sty" ||
				$this->object->getType() == "adm"
			)
		)
		{
			include_once "./classes/class.ilTabsGUI.php";
			$tabs_gui =& new ilTabsGUI();
			$this->getTabs($tabs_gui);

			// output tabs
			$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
		}
		else
		{
			global $rbacsystem;
	
			$tabs = array();
			$this->tpl->addBlockFile("TABS", "tabs", "tpl.tabs.html");
	
			// for new objects display properties of parent object
			if ($a_new_type)
			{
				$d = $this->objDefinition->getProperties($this->object->getType());
			}
			else
			{
				$d = $this->objDefinition->getProperties($this->type);
			}
	
			foreach ($d as $key => $row)
			{
				$tabs[] = array($row["lng"], $row["name"]);
			}
	
			// check for call_by_reference too to avoid hacking
			if (isset($_GET["obj_id"]) and $this->call_by_reference === false)
			{
				$object_link = "&obj_id=".$_GET["obj_id"];
			}
	
			foreach ($tabs as $row)
			{
				$i++;
	
				if ($row[1] == $_GET["cmd"])
				{
					$tabtype = "tabactive";
					$tab = $tabtype;
				}
				else
				{
					$tabtype = "tabinactive";
					$tab = "tab";
				}
	
				$show = true;
	
				// only check permissions for tabs if object is a permission object
				// TODO: automize checks by using objects.xml definitions!!
				if (true)
				//if ($this->call_by_reference)
				{
					// only show tab when the corresponding permission is granted
					switch ($row[1])
					{
						case 'view':
							if (!$rbacsystem->checkAccess('visible',$this->ref_id))
							{
								$show = false;
							}
							break;
	
						case 'edit':
							if (!$rbacsystem->checkAccess('write',$this->ref_id))
							{
								$show = false;
							}
							break;
	
						case 'perm':
							if (!$rbacsystem->checkAccess('edit_permission',$this->ref_id))
							{
								$show = false;
							}
							break;
	
						case 'trash':
							if (!$this->tree->getSavedNodeData($this->ref_id))
							{
								$show = false;
							}
							break;
	
						// user object only
						case 'roleassignment':
							if (!$rbacsystem->checkAccess('edit_roleassignment',$this->ref_id))
							{
								$show = false;
							}
							break;

						// role object only
						case 'userassignment':
							if (!$rbacsystem->checkAccess('edit_userassignment',$this->ref_id))
							{
								$show = false;
							}
							break;
					} //switch
				}
	
				if (!$show)
				{
					continue;
				}
	
				$this->tpl->setCurrentBlock("tab");
				$this->tpl->setVariable("TAB_TYPE", $tabtype);
				$this->tpl->setVariable("TAB_TYPE2", $tab);
				$this->tpl->setVariable("IMG_LEFT", ilUtil::getImagePath("eck_l.gif"));
				$this->tpl->setVariable("IMG_RIGHT", ilUtil::getImagePath("eck_r.gif"));
				$this->tpl->setVariable("TAB_LINK", $this->tab_target_script."?ref_id=".$_GET["ref_id"].$object_link."&cmd=".$row[1]);
				$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($row[0]));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	function getHTML()
	{
		return $this->html;
	}

	function setTabTargetScript($a_script = "adm_object.php")
	{
		$this->tab_target_script = $a_script;
	}

	function getTabTargetScript()
	{
		return $this->tab_target_script;
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
	* @param	scriptanme that is used for linking; if not set adm_object.php is used
	* @access	public
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="adm_object.php",
		$a_child_param = "ref_id", $a_output_obj = true, $a_root_title = "")
	{
		global $ilias_locator;

		if (!is_object($a_tree))
		{
			$a_tree =& $this->tree;
		}

		if (!($a_id))
		{
			$a_id = $_GET["ref_id"];
		}

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);

        //check if object isn't in tree, this is the case if parent_parent is set
		// TODO: parent_parent no longer exist. need another marker
		if ($a_parent_parent)
		{
			//$subObj = getObject($a_ref_id);
			$subObj =& $this->ilias->obj_factory->getInstanceByRefId($a_ref_id);

			$path[] = array(
				"id"	 => $a_ref_id,
				"title"  => $this->lng->txt($subObj->getTitle())
				);
		}

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (isset($_GET["obj_id"]) && $a_output_obj)
		{
			$modifier = 0;
		}

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		foreach ($path as $key => $row)
		{
			if ($key < count($path)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");

			if ($a_root_title != "" && ($row["child"] == $a_tree->getRootId()))
			{
				$title = $a_root_title;
			}
			else
			{
				$title = $row["title"];
			}

			$this->tpl->setVariable("ITEM", $title);

			$this->tpl->setVariable("LINK_ITEM",
				ilUtil::appendUrlParameterString($scriptname, $a_child_param."=".$row["child"]));
			$this->tpl->parseCurrentBlock();

			// ### AA 03.11.10 added new locator GUI class ###
			// navigate locator
			$ilias_locator->navigate($i++,$title,
				ilUtil::appendUrlParameterString($scriptname, $a_child_param."=".$row["child"]),"bottom");
		}

		if (($_GET["obj_id"] != "") && $a_output_obj)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($_GET["obj_id"]);

			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $obj_data->getTitle());

			$this->tpl->setVariable("LINK_ITEM",
				ilUtil::appendUrlParameterString($scriptname, "ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]));
			$this->tpl->parseCurrentBlock();

			// ### AA 03.11.10 added new locator GUI class ###
			// navigate locator
			$ilias_locator->navigate($i++,$obj_data->getTitle(),
				ilUtil::appendUrlParameterString($scriptname, "ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]),"bottom");
		}

		$this->tpl->setCurrentBlock("locator");

		if (DEBUG)
		{
			$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
		}

		$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

		if ($_GET["cmd"] == "confirmDeleteAdm")
		{
			$prop_name = "delete_object";
		}

		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* copy object to clipboard
	*
	* @access	public
	*/
	function copyObject()
	{
		global $rbacsystem;

		if (!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// FOR ALL OBJECTS THAT SHOULD BE COPIED
		foreach ($_POST["id"] as $ref_id)
		{
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($ref_id);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK READ PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
			foreach ($subtree_nodes as $node)
			{
				if (!$rbacsystem->checkAccess('read',$node["ref_id"]))
				{
					$no_copy[] = $node["ref_id"];
				}
			}
		}
		// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'read'
		if (count($no_copy))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_copy")." ".implode(',',$this->getTitlesByRefId($no_copy)),
									 $this->ilias->error_obj->MESSAGE);
		}

		$_SESSION["clipboard"]["parent"] = $_GET["ref_id"];
		$_SESSION["clipboard"]["cmd"] = key($_POST["cmd"]);
		$_SESSION["clipboard"]["ref_ids"] = $_POST["id"];

		sendinfo($this->lng->txt("msg_copy_clipboard"),true);

		ilUtil::redirect($this->getReturnLocation("copy","adm_object.php?ref_id=".$_GET["ref_id"]));
	}

	/**
	* clone Object subtree
	*
	* @access	private
	*/
	function cloneObject($a_ref_ids)
	{
		global $rbacsystem;

		if(!is_array($a_ref_ids))
		{
			$this->ilias->raiseError($this->lng->txt("msg_error_copy"),$this->ilias->error_obj->MESSAGE);
		}
		
		// NOW CLONE ALL OBJECTS
		// THEREFORE THE CLONE METHOD OF ALL OBJECTS IS CALLED
		foreach ($a_ref_ids as $id)
		{
			$this->cloneNodes($id,$this->ref_id,$mapping);
		}
		
		// inform other objects in hierarchy about copy operation
		//$this->object->notify("copy",$_SESSION["clipboard"]["parent"],$_SESSION["clipboard"]["parent_non_rbac_id"],$_GET["ref_id"],$mapping);
 
		$this->clearObject();

		sendinfo($this->lng->txt("msg_cloned"),true);
		ilUtil::redirect($this->getReturnLocation("paste","adm_object.php?ref_id=".$_GET["ref_id"]));

	} // END CLONE

	/**
	* clone all nodes
	* recursive function
	*
	* @access	private
	* @param	integer ref_id of source object
	* @param	integer ref_id of destination object
	* @param    array	mapping new_ref_id => new_ref_id
	* @return	boolean	true
	*/
	function cloneNodes($a_source_id,$a_dest_id,&$mapping)
	{
		if (!$mapping)
		{
			$mapping = array();
		}

		// FIRST CLONE THE OBJECT (THEREFORE THE CLONE METHOD OF EACH OBJECT IS CALLED)
		$source_obj =& $this->ilias->obj_factory->getInstanceByRefId($a_source_id);
		$new_ref_id = $source_obj->ilClone($a_dest_id);
		unset($source_obj);

		$mapping[$new_ref_id] = $a_source_id;

		// GET ALL CHILDS OF SOURCE OBJECT AND CALL THIS METHOD FOR OF THEM
		foreach ($this->tree->getChilds($a_source_id) as $child)
		{
			// STOP IF CHILD OBJECT IS ROLE FOLDER SINCE IT DOESN'T MAKE SENSE TO CLONE LOCAL ROLES
			if ($child["type"] != 'rolf')
			{
				$this->cloneNodes($child["ref_id"],$new_ref_id,$mapping);
			}
			else
			{
				if (count($rolf = $this->tree->getChildsByType($new_ref_id,"rolf")))
				{
					$mapping[$rolf[0]["ref_id"]] = $child["ref_id"];
				}
			}
		}

		return true;
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
									 implode(',',$no_paste),$this->ilias->error_obj->MESSAGE);
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
		
		sendInfo($this->lng->txt("msg_undeleted"),true);
		
		ilUtil::redirect($this->getReturnLocation("undelete","adm_object.php?ref_id=".$_GET["ref_id"]));

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

		$this->tree->insertNode($a_source_id,$a_dest_id);
		
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
			sendInfo($this->lng->txt("msg_no_perm_delete")." ".$not_deletable."<br/>".$this->lng->txt("msg_cancel"),true);
			ilUtil::redirect($this->getReturnLocation("confirmedDelete","adm_object.php?ref_id=".$_GET["ref_id"]));

//			$this->ilias->raiseError($this->lng->txt("msg_no_perm_delete")." ".
//									 $not_deletable,$this->ilias->error_obj->MESSAGE);
		}
		if(count($buyable))
		{
			foreach($buyable as $id)
			{
				$tmp_object =& ilObjectFactory::getInstanceByRefId($id);

				$titles[] = $tmp_object->getTitle();
			}
			$title_str = implode(',',$titles);

			sendInfo($this->lng->txt('msg_obj_not_deletable_sold').' '.$title_str,true);

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
				sendInfo($this->lng->txt("no_perm_delete")."<br/>".$this->lng->txt("msg_cancel"),true);
				ilUtil::redirect($this->getReturnLocation("confirmedDelete","adm_object.php?ref_id=".$_GET["ref_id"]));
			}
		}
		else
		{
			// SAVE SUBTREE AND DELETE SUBTREE FROM TREE
			foreach ($_SESSION["saved_post"] as $id)
			{
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

				$this->tree->saveSubTree($id);
				$this->tree->deleteTree($this->tree->getNodeData($id));

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
		
		// Feedback
		sendInfo($this->lng->txt("info_deleted"),true);
		
		ilUtil::redirect($this->getReturnLocation("confirmedDelete","adm_object.php?ref_id=".$_GET["ref_id"]));

	}

	/**
	* cancel deletion of object
	*
	* @access	public
	*/
	function cancelDeleteObject()
	{
		session_unregister("saved_post");

		sendInfo($this->lng->txt("msg_cancel"),true);

		ilUtil::redirect($this->getReturnLocation("cancelDelete","adm_object.php?ref_id=".$_GET["ref_id"]));

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
		
		sendInfo($this->lng->txt("msg_removed"),true);

		ilUtil::redirect($this->getReturnLocation("removeFromSystem","adm_object.php?ref_id=".$_GET["ref_id"]));

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
		global $log;
		
		$q = "SELECT tree FROM tree WHERE parent='".$a_node_id."' AND tree < 0";
		
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

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);

				if ($this->prepare_output)
				{
					$this->tpl->parseCurrentBlock();
				}
			}

			$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
																	   $_GET["ref_id"]."&new_type=".$new_type));
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

		sendInfo($this->lng->txt("msg_cancel"),true);

		//sendInfo($this->lng->txt("action_aborted"),true);
		$return_location = $_GET["cmd_return_location"];
//echo "-".$_GET["cmd_return_location"]."-".$this->ctrl->getLinkTarget($this,$return_location);
		//ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
		if ($in_rep)
		{
			$this->ctrl->returnToParent($this);
		}
		else
		{
			ilUtil::redirect($this->getReturnLocation("cancel",$this->ctrl->getTargetScript()."?".$this->link_params));
		}
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

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update",$this->ctrl->getFormAction($this).$obj_str));
		//$this->tpl->setVariable("FORMACTION", $this->getFormAction("update","adm_object.php?cmd=gateway&ref_id=".$this->ref_id.$obj_str));
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

		sendInfo($this->lng->txt("msg_obj_modified"),true);

		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getLinkTarget($this)));
		//ilUtil::redirect($this->getReturnLocation("update","adm_object.php?ref_id=".$this->ref_id));
	}

	/**
	* show permissions of current node
	*
	* @access	public
	*/
	function permObject()
	{
		global $rbacsystem, $rbacreview;

		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this, "perm"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("permission_settings"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this, "info"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("info_status_info"));
		$this->tpl->parseCurrentBlock();

		static $num = 0;
		if (!$rbacsystem->checkAccess("edit_permission", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_perm"),$this->ilias->error_obj->MESSAGE);
		}

		// only display superordinate roles; local roles with other scope are not displayed
		$parentRoles = $rbacreview->getParentRoleIds($this->object->getRefId());

		$data = array();

		// GET ALL LOCAL ROLE IDS
		$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());
		
		$global_roles = $rbacreview->getGlobalRoles();

		$local_roles = array();

		if ($role_folder)
		{
			$local_roles = $rbacreview->getRolesOfRoleFolder($role_folder["ref_id"]);
		}
		foreach ($parentRoles as $key => $r)
		{
			// exclude system admin role from list
			if ($r["obj_id"] == SYSTEM_ROLE_ID)
			{
				unset($parentRoles[$key]);
				continue;
			}
			
			$r["global"] = false;
			
			if (in_array($r["obj_id"],$global_roles))
			{
				$r["global"] = true;
			}

			if (!in_array($r["obj_id"],$local_roles))
			{
				$data["check_inherit"][] = ilUtil::formCheckBox(0,"stop_inherit[]",$r["obj_id"]);
				$r["link"] = false;
			}
			else
			{
				$r["link"] = true;

				// don't display a checkbox for local roles AND system role
				if ($rbacreview->isAssignable($r["obj_id"],$role_folder["ref_id"]))
				{
					$data["check_inherit"][] = "&nbsp;";
				}
				else
				{
					// linked local roles with stopped inheritance
					$data["check_inherit"][] = ilUtil::formCheckBox(1,"stop_inherit[]",$r["obj_id"]);
				}
			}

			$data["roles"][] = $r;
		}
		
		$ope_list = getOperationList($this->object->getType());
		
		// BEGIN TABLE_DATA_OUTER
		foreach ($ope_list as $key => $operation)
		{
			$opdata = array();

			$opdata["name"] = $operation["operation"];

			$colspan = count($parentRoles) + 1;

			foreach ($parentRoles as $role)
			{
				$checked = $rbacsystem->checkPermission($this->object->getRefId(), $role["obj_id"],$operation["operation"],$_GET["parent"]);
				$disabled = false;

				// Es wird eine 2-dim Post Variable bergeben: perm[rol_id][ops_id]
				$box = ilUtil::formCheckBox($checked,"perm[".$role["obj_id"]."][]",$operation["ops_id"],$disabled);
				$opdata["values"][] = $box;
			}

			$data["permission"][] = $opdata;
		}

		/////////////////////
		// START DATA OUTPUT
		/////////////////////
		$this->getTemplateFile("perm");
		$this->tpl->setCurrentBlock("tableheader");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("permission_settings"));
		$this->tpl->setVariable("COLSPAN", $colspan);
		$this->tpl->setVariable("TXT_OPERATION", $this->lng->txt("operation"));
		$this->tpl->setVariable("TXT_ROLES", $this->lng->txt("roles"));
		$this->tpl->parseCurrentBlock();

		$num = 0;
		//var_dump("<pre>",$data["roles"],"</pre>");

		foreach ($data["roles"] as $role)
		{
			$tmp_role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());
			$tmp_local_roles = array();
			if ($tmp_role_folder)
			{
				$tmp_local_roles = $rbacreview->getRolesOfRoleFolder($tmp_role_folder["ref_id"]);
			}
			// Is it a real or linked lokal role
			if(in_array($role['obj_id'],$tmp_local_roles))
			{
				$role_folder_data = $rbacreview->getRoleFolderOfObject($_GET['ref_id']);
				$role_folder_id = $role_folder_data['ref_id'];


				$this->tpl->setCurrentBlock("ROLELINK_OPEN");

				if($this->ctrl->getTargetScript() != 'adm_object.php')
				{
					$up_path = defined('ILIAS_MODULE') ? "../" : "";
					$this->tpl->setVariable("LINK_ROLE_RULESET",$up_path.'role.php?cmd=perm&ref_id='.
											$role_folder_id.'&obj_id='.$role['obj_id']);

					#$this->ctrl->setParameterByClass('ilobjrolegui','obj_id',$role['obj_id']);
					#$this->tpl->setVariable("LINK_ROLE_RULESET",
					#						$this->ctrl->getLinkTargetByClass('ilobjrolegui','perm'));
				}
				else
				{
					$this->tpl->setVariable("LINK_ROLE_RULESET",'adm_object.php?cmd=perm&ref_id='.
											$role_folder_id.'&obj_id='.$role['obj_id']);
				}
				$this->tpl->setVariable("TXT_ROLE_RULESET",$this->lng->txt("edit_perm_ruleset"));
				$this->tpl->parseCurrentBlock();

				$this->tpl->touchBlock("ROLELINK_CLOSE");
			}

			$this->tpl->setCurrentBlock("ROLENAMES");
			
			// display human readable role names for autogenerated roles
			include_once ('class.ilObjRole.php');
			$this->tpl->setVariable("ROLE_NAME",str_replace(" ","&nbsp;",ilObjRole::_getTranslation($role["title"])));
			
			/*if (substr($role["title"],0,3) == "il_")
			{
				$readable_title = substr($role["title"],0,strlen($role['title']) - strlen($role['obj_id']));
				$this->tpl->setVariable("ROLE_NAME",$this->lng->txt($readable_title));
			}
			else
			{
				$this->tpl->setVariable("ROLE_NAME",$role["title"]);
			}*/
			
			// display role context
			if ($role["global"])
			{
				$this->tpl->setVariable("ROLE_CONTEXT_TYPE","global");
			}
			else
			{
				$rolf = $rbacreview->getFoldersAssignedToRole($role["obj_id"],true);
				$parent_node = $this->tree->getParentNodeData($rolf[0]);
				$this->tpl->setVariable("ROLE_CONTEXT_TYPE",$this->lng->txt("obj_".$parent_node["type"])."&nbsp;(#".$parent_node["obj_id"].")");
				$this->tpl->setVariable("ROLE_CONTEXT",$parent_node["title"]);
			}
				
			
			$this->tpl->parseCurrentBlock();

			// BLOCK CHECK INHERIT
			if ($this->objDefinition->stopInheritance($this->type))
			{
				$this->tpl->setCurrentBLock("CHECK_INHERIT");
				$this->tpl->setVariable("CHECK_INHERITANCE",$data["check_inherit"][$num]);
				$this->tpl->parseCurrentBlock();
			}

			$num++;
		}

		// save num for required column span and the end of parsing
		$colspan = $num + 1;
		$num = 0;

		// offer option 'stop inheritance' only to those objects where this option is permitted
		if ($this->objDefinition->stopInheritance($this->type))
		{
			$this->tpl->setCurrentBLock("STOP_INHERIT");
			$this->tpl->setVariable("TXT_STOP_INHERITANCE", $this->lng->txt("stop_inheritance"));
			$this->tpl->parseCurrentBlock();
		}

		foreach ($data["permission"] as $ar_perm)
		{
			foreach ($ar_perm["values"] as $box)
			{
				// BEGIN TABLE CHECK PERM
				$this->tpl->setCurrentBlock("CHECK_PERM");
				$this->tpl->setVariable("CHECK_PERMISSION",$box);
				$this->tpl->parseCurrentBlock();
				// END CHECK PERM
			}

			// BEGIN TABLE DATA OUTER
			$this->tpl->setCurrentBlock("TABLE_DATA_OUTER");
			$css_row = ilUtil::switchColor($num++, "tblrow1", "tblrow2");
			$this->tpl->setVariable("CSS_ROW",$css_row);
			$this->tpl->setVariable("PERMISSION", $this->lng->txt($this->object->getType()."_".$ar_perm["name"]));
			if (substr($ar_perm["name"], 0, 7) == "create_")
			{
				if ($this->objDefinition->getDevMode(substr($ar_perm["name"], 7, strlen($ar_perm["name"]) -7)))
				{
					$this->tpl->setVariable("TXT_NOT_IMPL", "(".$this->lng->txt("not_implemented_yet").")");
				}
			}
			$this->tpl->parseCurrentBlock();
			// END TABLE DATA OUTER
		}

		// ADD LOCAL ROLE
		
		// do not display this option for admin section and root node
		$object_types_exclude = array("adm","root","mail","objf","lngf","trac","taxf","auth", "assf",'seas');

		if (!in_array($this->object->getType(),$object_types_exclude) and $this->object->getRefId() != ROLE_FOLDER_ID)
		//if ($this->object->getRefId() != ROLE_FOLDER_ID and $rbacsystem->checkAccess('create_role',$this->object->getRefId()))
		{
			$this->tpl->setCurrentBlock("LOCAL_ROLE");

			// fill in saved values in case of error
			$data = array();
			$data["fields"] = array();
			$data["fields"]["title"] = $_SESSION["error_post_vars"]["Fobject"]["title"];
			$data["fields"]["desc"] = $_SESSION["error_post_vars"]["Fobject"]["desc"];

			foreach ($data["fields"] as $key => $val)
			{
				$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
				$this->tpl->setVariable(strtoupper($key), $val);
			}

			$this->tpl->setVariable("FORMACTION_LR",$this->getFormAction("addRole", $this->ctrl->getLinkTarget($this, "addRole")));
//			$this->tpl->setVariable("FORMACTION_LR",$this->getFormAction("addRole", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=addRole"));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("you_may_add_local_roles"));
			$this->tpl->setVariable("TXT_ADD_ROLE", $this->lng->txt("role_add_local"));
			$this->tpl->setVariable("TARGET", $this->getTargetFrame("addRole"));
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
			$this->tpl->parseCurrentBlock();
		}
//vd($this->link_params);

		// PARSE BLOCKFILE
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION",
			$this->getFormAction("permSave",$this->ctrl->getLinkTarget($this,"permSave")));
//		$this->tpl->setVariable("FORMACTION",
//			$this->getFormAction("permSave","adm_object.php?".$this->link_params."&cmd=permSave"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("COL_ANZ",$colspan);
		$this->tpl->parseCurrentBlock();
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
	* @param	string		$a_formaaction	default formaction (is returned, if no special
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
		global $rbacsystem, $rbacreview, $rbacadmin;

		// first save the new permission settings for all roles
		$rbacadmin->revokePermission($this->ref_id);

		if (is_array($_POST["perm"]))
		{
			foreach ($_POST["perm"] as $key => $new_role_perms)
			{
				// $key enthaelt die aktuelle Role_Id
				$rbacadmin->grantPermission($key,$new_role_perms,$this->ref_id);
			}
		}

		// update object data entry (to update last modification date)
		$this->object->update();

		// Wenn die Vererbung der Rollen Templates unterbrochen werden soll,
		// muss folgendes geschehen:
		// - existiert kein RoleFolder, wird er angelegt und die Rechte aus den Permission Templates ausgelesen
		// - existiert die Rolle im aktuellen RoleFolder werden die Permission Templates dieser Rolle angezeigt
		// - existiert die Rolle nicht im aktuellen RoleFolder wird sie dort angelegt
		//   und das Permission Template an den Wert des nihst hher gelegenen Permission Templates angepasst

		// get rolefolder data if a rolefolder already exists
		$rolf_data = $rbacreview->getRoleFolderOfObject($this->ref_id);
		$rolf_id = $rolf_data["child"];

		if ($_POST["stop_inherit"])
		{
			// rolefolder does not exist, so create one
			if (empty($rolf_id))
			{
				// create a local role folder
				$rfoldObj = $this->object->createRoleFolder();

				// set rolf_id again from new rolefolder object
				$rolf_id = $rfoldObj->getRefId();
			}

			// CHECK ACCESS 'write' of role folder
			if (!$rbacsystem->checkAccess('write',$rolf_id))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
			}

			foreach ($_POST["stop_inherit"] as $stop_inherit)
			{
				$roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_id);

				// create role entries for roles with stopped inheritance
				if (!in_array($stop_inherit,$roles_of_folder))
				{
					$parentRoles = $rbacreview->getParentRoleIds($rolf_id);
					$rbacadmin->copyRolePermission($stop_inherit,$parentRoles[$stop_inherit]["parent"],
												   $rolf_id,$stop_inherit);
					$rbacadmin->assignRoleToFolder($stop_inherit,$rolf_id,'n');
				}
			}// END FOREACH
		}// END STOP INHERIT
		elseif 	(!empty($rolf_id))
		{
			// TODO: this feature doesn't work at the moment
			// ok. if the rolefolder is not empty, delete the local roles
			//if (!empty($roles_of_folder = $rbacreview->getRolesOfRoleFolder($rolf_data["ref_id"])));
			//{
				//foreach ($roles_of_folder as $obj_id)
				//{
					//$rolfObj =& $this->ilias->obj_factory->getInstanceByRefId($rolf_data["child"]);
					//$rolfObj->delete();
					//unset($rolfObj);
				//}
			//}
		}

		sendinfo($this->lng->txt("saved_successfully"),true);
		ilUtil::redirect($this->getReturnLocation("permSave",$this->ctrl->getLinkTarget($this,"perm")));
	}

	/**
	* display object list
	*
	* @access	public
 	*/
	function displayList()
	{
		include_once "./classes/class.ilTableGUI.php";

		// load template for table
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		$num = 0;

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;
		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$this->ref_id."$obj_str&cmd=gateway");

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

		$header_params = array("ref_id" => $this->ref_id);
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
					$link = "adm_object.php?";

					$n = 0;

					foreach ($ctrl as $key2 => $val2)
					{
						$link .= $key2."=".$val2;

						if ($n < count($ctrl)-1)
						{
					    	$link .= "&";
							$n++;
						}
					}

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
					if (($key == "title" || $key == "name") and isset($_SESSION["clipboard"]))
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
			$this->tpl->setVariable("TEXT_CONTENT", $this->lng->txt("obj_not_found"));
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
			sendInfo($this->lng->txt("info_delete_sure"));
		}

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("delete",
			"adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway"));
	
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
			sendInfo($this->lng->txt("msg_trash_empty"));
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
		
		/* TODO: fix message display in conjunction with sendInfo & raiseError functionality
		$this->tpl->addBlockfile("MESSAGE", "adm_trash", "tpl.message.html");
		$this->tpl->setCurrentBlock("adm_trash");
		$this->tpl->setVariable("MSG",$this->lng->txt("info_trash"));
		$this->tpl->parseCurrentBlock();
		*/
		//sendInfo($this->lng->txt("info_trash"));

		$this->tpl->setVariable("FORMACTION", "adm_object.php?ref_id=".$_GET["ref_id"]."&cmd=gateway");
		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

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
		global $rbacadmin, $rbacreview, $rbacsystem;

		// first check if role title is unique
		if ($rbacreview->roleExists($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_exists1")." '".ilUtil::stripSlashes($_POST["Fobject"]["title"])."' ".
									 $this->lng->txt("msg_role_exists2"),$this->ilias->error_obj->MESSAGE);
		}

		// check if role title has il_ prefix
		if (substr($_POST["Fobject"]["title"],0,3) == "il_")
		{
			$this->ilias->raiseError($this->lng->txt("msg_role_reserved_prefix"),$this->ilias->error_obj->MESSAGE);
		}

		// if the current object is no role folder, create one
		if ($this->object->getType() != "rolf")
		{
			$rolf_data = $rbacreview->getRoleFolderOfObject($this->ref_id);

			// is there already a rolefolder?
			if (!($rolf_id = $rolf_data["child"]))
			{
				// can the current object contain a rolefolder?
				$subobjects = $this->objDefinition->getSubObjects($this->object->getType());

				if (!isset($subobjects["rolf"]))
				{
					$this->ilias->raiseError($this->lng->txt("msg_no_rolf_allowed1")." '".$this->object->getTitle()."' ".
											$this->lng->txt("msg_no_rolf_allowed2"),$this->ilias->error_obj->WARNING);
				}

				// CHECK ACCESS 'create' rolefolder
				if (!$rbacsystem->checkAccess('create',$this->ref_id,'rolf'))
				{
					$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_rolf"),$this->ilias->error_obj->WARNING);
				}

				// create a rolefolder
				$rolfObj = $this->object->createRoleFolder();
				$rolf_id = $rolfObj->getRefId();
			}
		}
		else
		{
			// Current object is already a rolefolder. To create the role we take its reference id
			$rolf_id = $this->object->getRefId();
		}

		// CHECK ACCESS 'write' of role folder
		if (!$rbacsystem->checkAccess('write',$rolf_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		else	// create role
		{
			if ($this->object->getType() == "rolf")
			{
				$roleObj = $this->object->createRole($_POST["Fobject"]["title"],$_POST["Fobject"]["desc"]);
			}
			else
			{
				$rfoldObj = $this->ilias->obj_factory->getInstanceByRefId($rolf_id);
				$roleObj = $rfoldObj->createRole($_POST["Fobject"]["title"],$_POST["Fobject"]["desc"]);
			}
		}

		sendInfo($this->lng->txt("role_added"),true);
		
		if ($this->ctrl->getTargetScript() != "repository.php")
		{
			$this->ctrl->setParameter($this,"obj_id",$roleObj->getId());
			$this->ctrl->setParameter($this,"ref_id",$rolf_id);
			ilUtil::redirect($this->getReturnLocation("addRole",$this->ctrl->getLinkTarget($this,"perm")));
		}

		ilUtil::redirect($this->getReturnLocation("addRole",$this->ctrl->getLinkTarget($this,"perm")));
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
			$d = $this->objDefinition->getActions($_GET["type"]);
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
			$this->showPossibleSubObjects();
		}
		
		if (!empty($this->ids))
		{
			// set checkbox toggles
			$this->tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$this->tpl->setVariable("JS_VARNAME","id");			
			$this->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($this->ids));
			$this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->parseCurrentBlock();	
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
			$d = $this->objDefinition->getCreatableSubObjects($_GET["type"]);
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
		include_once "./classes/class.ilTableGUI.php";

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
		
		//add template for view button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this, "perm"));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("permission_settings"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this, "info"));
		$this->tpl->setVariable("BTN_TXT", $this->lng->txt("info_status_info"));
		$this->tpl->parseCurrentBlock();

		include_once('classes/class.ilObjectStatusGUI.php');
		
		$ilInfo = new ilObjectStatusGUI($this->object);
		
		$this->tpl->setVariable("ADM_CONTENT",$ilInfo->getHTML());
	}
} // END class.ilObjectGUI
?>
