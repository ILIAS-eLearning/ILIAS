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

require_once "./classes/class.ilObjectGUI.php";

/**
* Class ilObjRoleFolderGUI
*
* @author Stefan Meyer <meyer@leifos.com>
* @author Sascha Hofmann <saschahofmann@gmx.de>
*
* @version $Id$
* 
* @ilCtrl_Calls ilObjRoleFolderGUI: ilPermissionGUI
*
* @ingroup	ServicesAccessControl
*/
class ilObjRoleFolderGUI extends ilObjectGUI
{
	/**
	* ILIAS3 object type abbreviation
	* @var		string
	* @access	public
	*/
	var $type;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjRoleFolderGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "rolf";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

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
	 *
	 * @global ilErrorHandler $ilErr
	 * @global ilRbacSystem $rbacsystem
	 * @global ilToolbarGUI $ilToolbar
	 */
	public function viewObject()
	{
		global $ilErr, $rbacsystem, $ilToolbar,$rbacreview,$ilTabs;

		$ilTabs->activateTab('view');

		if(!$rbacsystem->checkAccess('visible,read',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->MESSAGE);
		}

		$this->ctrl->setParameter($this,'new_type','role');
		$ilToolbar->addButton(
			$this->lng->txt('rolf_create_role'),
			$this->ctrl->getLinkTarget($this,'create')
		);
		$this->ctrl->setParameter($this,'new_type','rolt');
		$ilToolbar->addButton(
			$this->lng->txt('rolf_create_rolt'),
			$this->ctrl->getLinkTarget($this,'create')
		);
		$this->ctrl->clearParameters($this);

		include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
		$table = new ilRoleTableGUI($this,'view');
		$table->init();
		$table->parse($this->object->getId());

		$this->tpl->setContent($table->getHTML());
	}

	/**
	 * Apply role filter
	 */
	protected function applyFilterObject()
    {
		include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
		$table = new ilRoleTableGUI($this,'view');
		$table->init();
		$table->resetOffset();
		$table->writeFilterToSession();

		$this->viewObject();
	}

	/**
	 * Reset role filter
	 */
	function resetFilterObject()
    {
		include_once './Services/AccessControl/classes/class.ilRoleTableGUI.php';
		$table = new ilRoleTableGUI($this,'view');
		$table->init();
		$table->resetOffset();
		$table->resetFilter();

		$this->viewObject();
	}

	/**
	 * Confirm deletion of roles
	 */
	protected function confirmDeleteObject()
	{
		global $ilCtrl;

		if(!count($_POST['roles']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$ilCtrl->redirect($this,'view');
		}

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($ilCtrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('delete'), 'deleteRole');
		$confirm->setCancel($this->lng->txt('cancel'), 'cancel');


		include_once './Services/AccessControl/classes/class.ilObjRole.php';
		foreach($_POST['roles'] as $role_id)
		{
			$confirm->addItem(
				'roles',
				$role_id,
				ilObjRole::_getTranslation(ilObject::_lookupTitle($role_id))
			);
		}
		$this->tpl->setContent($confirm->getHTML());
	}

	/**
	 * Delete roles
	 */
	protected function deleteRoleObject()
	{
		global $rbacsystem,$ilErr,$rbacreview,$ilCtrl;

		if(!$rbacsystem->checkAccess('delete',$this->object->getRefId()))
		{
			$ilErr->raiseError(
				$this->lng->txt('msg_no_perm_delete'),
				$ilErr->MESSAGE
			);
		}

		foreach((array) $_POST['roles'] as $id)
		{
			// instatiate correct object class (role or rolt)
			$obj = ilObjectFactory::getInstanceByObjId($id,false);

			if ($obj->getType() == "role")
			{
				$rolf_arr = $rbacreview->getFoldersAssignedToRole($obj->getId(),true);
				$obj->setParent($rolf_arr[0]);
			}

			$obj->delete();
		}

		// set correct return location if rolefolder is removed
		ilUtil::sendSuccess($this->lng->txt("msg_deleted_roles_rolts"),true);
		$ilCtrl->redirect($this,'view');
	}


	

	
	/**
	* role folders are created automatically
	* POSSIBLE DEPRECATED !!!
	* @access	public
	*/
	function createObject()
	{
		$this->object->setTitle($this->lng->txt("obj_".$this->object->getType()."_local"));
		$this->object->setDescription("obj_".$this->object->getType()."_local_desc");
		
		$this->saveObject();
	}
	
	/**
	* display deletion confirmation screen
	*
	* @access	public
	*/
	function deleteObject()
	{
		if (!isset($_POST["role_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["role_id"];

		unset($this->data);
		$this->data["cols"] = array("type", "title", "description", "last_change");

		foreach($_POST["role_id"] as $id)
		{
			$obj_data =& $this->ilias->obj_factory->getInstanceByObjId($id);

			$this->data["data"]["$id"] = array(
				"type"        => $obj_data->getType(),
				"title"       => $obj_data->getTitle(),
				"desc"        => $obj_data->getDescription(),
				"last_update" => $obj_data->getLastUpdateDate());
		}

		$this->data["buttons"] = array( "cancelDelete"  => $this->lng->txt("cancel"),
								  "confirmedDelete"  => $this->lng->txt("confirm"));

		$this->getTemplateFile("confirm");

		ilUtil::sendQuestion($this->lng->txt("info_delete_sure"));

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
	* ???
	* TODO: what is the purpose of this function?
	* @access	public
	*/
	function adoptPermSaveObject()
	{
		ilUtil::sendSuccess($this->lng->txt("saved_successfully"),true);
		
		$this->ctrl->redirect($this, "view");
	}
	
	/**
	* show possible subobjects (pulldown menu)
	* overwritten to prevent displaying of role templates in local role folders
	*
	* @access	public
 	*/
	function showPossibleSubObjects($a_tpl)
	{
		global $rbacsystem;

		$d = $this->objDefinition->getCreatableSubObjects($this->object->getType());
		
		if ($this->object->getRefId() != ROLE_FOLDER_ID or !$rbacsystem->checkAccess('create_rolt',ROLE_FOLDER_ID))
		{
			unset($d["rolt"]);
		}
		
		if (!$rbacsystem->checkAccess('create_role',$this->object->getRefId()))
		{
			unset($d["role"]);			
		}

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
				}
			}
		}

		if (is_array($subobj))
		{
			//build form
			$opts = ilUtil::formSelect(12,"new_type",$subobj);
			$a_tpl->setCurrentBlock("add_object");
			$a_tpl->setVariable("SELECT_OBJTYPE", $opts);
			$a_tpl->setVariable("BTN_NAME", "create");
			$a_tpl->setVariable("TXT_ADD", $this->lng->txt("add"));
			$a_tpl->parseCurrentBlock();
		}
		
		return $a_tpl;
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// role folders are created automatically
		$_GET["new_type"] = $this->object->getType();
		$_POST["Fobject"]["title"] = $this->object->getTitle();
		$_POST["Fobject"]["desc"] = $this->object->getDescription();

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// put here your object specific stuff	

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("rolf_added"),true);
		
		$this->ctrl->redirect($this, "view");
	}


} // END class.ilObjRoleFolderGUI
?>
