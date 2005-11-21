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
* Class ilObjFolderGUI
*
* @author Martin Rus <develop-ilias@uni-koeln.de>
* $Id$
*
* @ilCtrl_Calls ilObjFolderGUI: ilConditionHandlerInterface, ilPermissionGUI
*
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilContainerGUI.php";

class ilObjFolderGUI extends ilContainerGUI
{
	var $folder_tree;		// folder tree

	/**
	* Constructor
	* @access	public
	*/
	function ilObjFolderGUI($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "fold";
		$this->ilContainerGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}


	function viewObject()
	{
		global $tree;

		if($this->ctrl->getTargetScript() == "adm_object.php")
		{
			parent::viewObject();
			return true;
		}
		else if(!$tree->checkForParentType($this->ref_id,'crs'))
		{
			//$this->ctrl->returnToParent($this);
			$this->renderObject();
		}
		else
		{
			$this->initCourseContentInterface();
			$this->cci_obj->cci_setContainer($this);
			$this->cci_obj->cci_view();
		}
		return true;
	}
		


	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class)
		{
			case "ilconditionhandlerinterface":
				include_once './classes/class.ilConditionHandlerInterface.php';

				if($_GET['item_id'])
				{
					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$new_gui->setBackButtons(array('edit' => $this->ctrl->getLinkTarget($this,'cciEdit'),
												   'preconditions' => $this->ctrl->getLinkTargetByClass('ilconditionhandlerinterface',
																										'listConditions')));

					$this->ctrl->forwardCommand($new_gui);
				}
				else
				{
					$new_gui =& new ilConditionHandlerInterface($this);
					$this->ctrl->forwardCommand($new_gui);
				}
				break;
				
			case 'ilpermissiongui':
					include_once("./classes/class.ilPermissionGUI.php");
					$perm_gui =& new ilPermissionGUI($this);
					$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if (empty($cmd))
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
	}

	/**
	* set tree
	*/
	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
	}

	/**
	* create new object form
	*
	* @access	public
	*/
	function createObject()
	{
		global $lng;

		$this->lng =& $lng;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

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
		}

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("save",$this->ctrl->getFormAction($this)."&new_type=".$new_type));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($this->type."_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveObject($a_parent = 0)
	{
		global $lng;

		$this->lng =& $lng;

		if ($a_parent == 0)
		{
			$a_parent = $_GET["ref_id"];
		}

		// create and insert Folder in grp_tree
		include_once("classes/class.ilObjFolder.php");
		$folderObj = new ilObjFolder(0,$this->withReferences());
		$folderObj->setType($this->type);
		$folderObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$folderObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$folderObj->create();
		$this->object =& $folderObj;

		if (is_object($this->folder_tree))		// groups gui should call ObjFolderGUI->setFolderTree also
		{
			$folderObj->setFolderTree($this->folder_tree);
		}
		else
		{
			$folderObj->setFolderTree($this->tree);
		}

		if ($this->withReferences())		// check if this folders use references
		{									// note: e.g. folders in media pools don't
			$folderObj->createReference();
			$folderObj->setPermissions($a_parent);
		}

		$folderObj->putInTree($a_parent);
			
		sendInfo($this->lng->txt("fold_added"),true);
		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
	}

	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""), "", get_class($this));
		}
		
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}

		// show clipboard in repository
		if ($this->ctrl->getTargetScript() == "repository.php" and !empty($_SESSION['il_rep_clipboard']))
		{
			$tabs_gui->addTarget("clipboard",
				 $this->ctrl->getLinkTarget($this, "clipboard"), "clipboard", get_class($this));
		}

		if ($this->ctrl->getTargetScript() == "adm_object.php")
		{
			$tabs_gui->addTarget("show_owner",
				$this->ctrl->getLinkTarget($this, "owner"), "owner", get_class($this));
			
			if ($this->tree->getSavedNodeData($this->ref_id))
			{
				$tabs_gui->addTarget("trash",
					$this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
			}
		}
	}

	// METHODS FOR COURSE CONTENT INTERFACE
	function initCourseContentInterface()
	{
		include_once "./course/classes/class.ilCourseContentInterface.php";
			
		$this->cci_obj =& new ilCourseContentInterface($this,$this->object->getRefId());


		#aggregate($this,"ilCourseContentInterface");
		#$this->cci_init($this,$this->object->getRefId());
	}

	function cciEditObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_edit();

		return true;;
	}

	function cciUpdateObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_update();

		return true;;
	}
	function cciMoveObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_move();

		return true;;
	}
	// Methods for ConditionHandlerInterface
	function initConditionHandlerGUI($item_id)
	{
		include_once './classes/class.ilConditionHandlerInterface.php';

		if(!is_object($this->chi_obj))
		{
			if($_GET['item_id'])
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this,$item_id);
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
			}
			else
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this);
			}
		}
		return true;
	}

	function chi_updateObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_update();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}		
	function chi_deleteObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_delete();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}

	function chi_selectorObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_selector();
	}		

	function chi_assignObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_assign();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}
} // END class.ilObjFolderGUI
?>
