<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* User Interface class for file based learning modules (HTML)
*
* @author Alex Killing <alex.killing@gmx.de>
*
* $Id$
*
* @ilCtrl_Calls ilObjFileBasedLMGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI
*
* @extends ilObjectGUI
* @package content
*/

require_once("classes/class.ilObjectGUI.php");
require_once("content/classes/class.ilObjFileBasedLM.php");
require_once("classes/class.ilTableGUI.php");
require_once("classes/class.ilFileSystemGUI.php");

class ilObjFileBasedLMGUI extends ilObjectGUI
{
	var $output_prepared;

	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjFileBasedLMGUI($a_data,$a_id = 0,$a_call_by_reference = true, $a_prepare_output = true)
	{
		global $lng, $ilCtrl;

		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this, array("ref_id"));

		include_once("classes/class.ilTabsGUI.php");
		$this->tabs_gui =& new ilTabsGUI();

		$this->type = "htlm";
		$lng->loadLanguageModule("content");

		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, false);
		//$this->actions = $this->objDefinition->getActions("mep");
		$this->output_prepared = $a_prepare_output;

	}

	/**
	* execute command
	*/
	function &executeCommand()
	{		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
			$this->getCreationMode() == true)
		{
			$this->prepareOutput();
		}
		else
		{
			$this->getTemplate();
			$this->setLocator();
			$this->setTabs();
		}

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case 'ilmdeditorgui':

				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				break;

			case "ilfilesystemgui":
				$fs_gui =& new ilFileSystemGUI($this->object->getDataDirectory());
				//$fs_gui->getTabs($this->tabs_gui);
				$fs_gui->activateLabels(true, $this->lng->txt("cont_purpose"));
				if ($this->object->getStartFile() != "")
				{
					$fs_gui->labelFile($this->object->getStartFile(),
						$this->lng->txt("cont_startfile"));
				}
				$fs_gui->addCommand($this, "setStartFile", $this->lng->txt("cont_set_start_file"));
				//$ret =& $fs_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($fs_gui);
				break;
				
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				$cmd = $this->ctrl->getCmd("frameset");
				if (strtolower($_GET["baseClass"]) == "iladministrationgui" ||
					$this->getCreationMode() == true)
				{
					$cmd.= "Object";
				}
				$ret =& $this->$cmd();
				break;
		}
		//$this->tpl->show();
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

			$this->ctrl->setParameter($this, "new_type", $new_type);
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
			//$this->tpl->setVariable("FORMACTION", $this->getFormAction("save","adm_object.php?cmd=gateway&ref_id=".
			//	$_GET["ref_id"]."&new_type=".$new_type));
			$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($new_type."_new"));
			$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
			$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($new_type."_add"));
			$this->tpl->setVariable("CMD_SUBMIT", "save");
			$this->tpl->setVariable("TARGET", ' target="'.
				ilFrameTargetInfo::_getFrame("MainContent").'" ');
			$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		}
	}


	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function properties()
	{
		global $rbacsystem, $tree, $tpl;

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// view link
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", "fblm_presentation.php?ref_id=".$this->object->getRefID());
		$this->tpl->setVariable("BTN_TARGET"," target=\"ilContObj".$this->object->getID()."\" ");
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("view"));
		$this->tpl->parseCurrentBlock();

		// lm properties
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.fblm_properties.html", true);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_PROPERTIES", $this->lng->txt("cont_lm_properties"));

		// online
		$this->tpl->setVariable("TXT_ONLINE", $this->lng->txt("cont_online"));
		$this->tpl->setVariable("CBOX_ONLINE", "cobj_online");
		$this->tpl->setVariable("VAL_ONLINE", "y");
		if ($this->object->getOnline())
		{
			$this->tpl->setVariable("CHK_ONLINE", "checked");
		}

		// start file
		$this->tpl->setVariable("TXT_START_FILE", $this->lng->txt("cont_startfile"));
		$this->tpl->setVariable("VAL_START_FILE", $this->object->getStartFile());
		$this->tpl->setVariable("TXT_SET_START_FILE", $this->lng->txt("cont_set_start_file"));
		$this->tpl->setVariable("LINK_SET_START_FILE",
			$this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"));

		$this->tpl->setCurrentBlock("commands");
		$this->tpl->setVariable("BTN_NAME", "saveProperties");
		$this->tpl->setVariable("BTN_TEXT", $this->lng->txt("save"));
		$this->tpl->parseCurrentBlock();

	}

	/**
	* save properties
	*/
	function saveProperties()
	{
		$this->object->setOnline(ilUtil::yn2tf($_POST["cobj_online"]));
		$this->object->update();
		sendInfo($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "properties");
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// create and insert forum in objecttree
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles
		//$roles = $newObj->initDefaultRoles();

		// ...finally assign role to creator of object
		//$rbacadmin->assignUser($roles[0], $newObj->getOwner(), "y");

		// put here object specific stuff

		// always send a message
		sendInfo($this->lng->txt("object_added"),true);
		ilUtil::redirect("content/fblm_edit.php?ref_id=".$newObj->getRefId());

		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));
	}

	/**
	* edit properties of object (admin form)
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $tree, $tpl;

		if (!$rbacsystem->checkAccess("visible,write",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// edit button
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if (!defined("ILIAS_MODULE"))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK","content/fblm_edit.php?ref_id=".$this->object->getRefID());
			$this->tpl->setVariable("BTN_TARGET"," target=\"bottom\" ");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("edit"));
			$this->tpl->parseCurrentBlock();
		}

		//parent::editObject();
	}

	/**
	* edit properties of object (module form)
	*/
	function edit()
	{
		$this->prepareOutput();
		$this->setFormAction("update", "fblm_edit.php?cmd=post&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->editObject();
		//$this->tpl->show();
	}

	/**
	* cancel editing
	*/
	function cancel()
	{
		$this->setReturnLocation("cancel","fblm_edit.php?cmd=listFiles&ref_id=".$_GET["ref_id"]);
		$this->cancelObject();
	}
	
	/**
	* cancel action and go back to previous page
	* @access	public
	*
	*/
	function cancelObject($in_rep = false)
	{
		sendInfo($this->lng->txt("msg_cancel"),true);
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
		//$this->ctrl->redirectByClass("ilrepositorygui", "frameset");
	}


	/**
	* update properties
	*/
	function update()
	{
		$this->setReturnLocation("update", "fblm_edit.php?cmd=listFiles&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->updateObject();
	}


	function setStartFile($a_file)
	{
		$this->object->setStartFile($a_file);
		$this->object->update();
		$this->ctrl->redirectByClass("ilfilesystemgui", "listFiles");
	}

	/**
	* permission form
	*/
	function perm()
	{
		$this->setFormAction("permSave", "fblm_edit.php?cmd=permSave&ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]);
		$this->setFormAction("addRole", "fblm_edit.php?ref_id=".$_GET["ref_id"].
			"&obj_id=".$_GET["obj_id"]."&cmd=addRole");
		$this->permObject();
	}
	
	/**
	* info permissions
	*/
	function info()
	{
		$this->infoObject();
	}

	/**
	* save permissions
	*/
	function permSave()
	{
		$this->setReturnLocation("permSave",
			"fblm_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		$this->permSaveObject();
	}

	/**
	* add role
	*/
	function addRole()
	{
		$this->setReturnLocation("addRole",
			"fblm_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".$_GET["obj_id"]."&cmd=perm");
		$this->addRoleObject();
	}

	/**
	* show owner of learning module
	*/
	function owner()
	{
		$this->ownerObject();
	}

	/**
	* save bib item (admin call)
	*/
	function saveBibItemObject($a_target = "")
	{
		include_once "content/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		$bibItemIndex *= 1;
		if ($bibItemIndex < 0)
		{
			$bibItemIndex = 0;
		}
		$bibItemIndex = $bib_gui->save($bibItemIndex);

		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* save bib item (module call)
	*/
	function saveBibItem()
	{
		//$this->setTabs();
		$this->saveBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* edit bib items (admin call)
	*/
	function editBibItemObject($a_target = "")
	{
		include_once "content/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		$bibItemIndex *= 1;
		if ($bibItemIndex < 0)
		{
			$bibItemIndex = 0;
		}
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* edit bib items (module call)
	*/
	function editBibItem()
	{
		//$this->setTabs();
		$this->editBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* delete bib item (admin call)
	*/
	function deleteBibItemObject($a_target = "")
	{
		include_once "content/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		$bib_gui->bib_obj->delete($_GET["bibItemName"], $_GET["bibItemPath"], $bibItemIndex);
		if (strpos($bibItemIndex, ",") > 0)
		{
			$bibItemIndex = substr($bibItemIndex, 0, strpos($bibItemIndex, ","));
		}
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* delete bib item (module call)
	*/
	function deleteBibItem()
	{
		//$this->setTabs();
		$this->deleteBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* add bib item (admin call)
	*/
	function addBibItemObject($a_target = "")
	{
		$bibItemName = $_POST["bibItemName"] ? $_POST["bibItemName"] : $_GET["bibItemName"];
		$bibItemIndex = $_POST["bibItemIndex"] ? $_POST["bibItemIndex"] : $_GET["bibItemIndex"];
		if ($bibItemName == "BibItem")
		{
			include_once "content/classes/class.ilBibItem.php";
			$bib_item =& new ilBibItem();
			$bib_item->setId($this->object->getId());
			$bib_item->setType($this->object->getType());
			$bib_item->read();
		}

		include_once "content/classes/class.ilBibItemGUI.php";
		$bib_gui =& new ilBibItemGUI();
		$bib_gui->setObject($this->object);
		if ($bibItemIndex == "")
			$bibItemIndex = 0;
		$bibItemPath = $_POST["bibItemPath"] ? $_POST["bibItemPath"] : $_GET["bibItemPath"];

		//if ($bibItemName != "" && $bibItemName != "BibItem")
		if ($bibItemName != "")
		{
			$bib_gui->bib_obj->add($bibItemName, $bibItemPath, $bibItemIndex);
			$data = $bib_gui->bib_obj->getElement("BibItem");
			$bibItemIndex = (count($data) - 1);
		}
		else
		{
			sendInfo($this->lng->txt("bibitem_choose_element"), true);
		}
		if ($a_target == "")
		{
			$a_target = "adm_object.php?ref_id=" . $this->object->getRefId();
		}

		$bib_gui->edit("ADM_CONTENT", "adm_content", $a_target, $bibItemIndex);
	}

	/**
	* add bib item (module call)
	*/
	function addBibItem()
	{
		//$this->setTabs();
		$this->addBibItemObject($this->ctrl->getLinkTarget($this));
	}

	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.fblm_edit_frameset.html", false, false, "content");
		$this->tpl->setVariable("HREF_FILES",$this->ctrl->getLinkTargetByClass(
			"ilfilesystemgui", "listFiles"));
		$this->tpl->show();
		exit;
	}

	/**
	* directory explorer
	*/
	function explorer()
	{
		$this->tpl = new ilTemplate("tpl.main.html", true, true);

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		require_once ("content/classes/class.ilFileExplorer.php");
		$exp = new ilFileExplorer($this->lm->getDataDirectory());

	}


	/**
	* set locator
	*/
	function setLocator($a_tree = "", $a_id = "", $scriptname="adm_object.php")
	{
		global $ilias_locator, $tree;
		if (!defined("ILIAS_MODULE"))
		{
			parent::setLocator();
		}
		else
		{
			$a_tree =& $tree;
			$a_id = $_GET["ref_id"];

			$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

			$path = $a_tree->getPathFull($a_id);

			// this is a stupid workaround for a bug in PEAR:IT
			$modifier = 1;

			if (!empty($_GET["obj_id"]))
			{
				$modifier = 0;
			}

			// ### AA 03.11.10 added new locator GUI class ###
			$i = 1;

			if ($this->object->getType() != "grp" && ($_GET["cmd"] == "delete" || $_GET["cmd"] == "edit"))
			{
				unset($path[count($path) - 1]);
			}

			foreach ($path as $key => $row)
			{

				if ($key < count($path) - $modifier)
				{
					$this->tpl->touchBlock("locator_separator");
				}

				$this->tpl->setCurrentBlock("locator_item");
				if ($row["child"] != $a_tree->getRootId())
				{
					$this->tpl->setVariable("ITEM", $row["title"]);
				}
				else
				{
					$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
				}
				if($row["type"] == "htlm")
				{
					$this->tpl->setVariable("LINK_ITEM", "fblm_edit.php?ref_id=".$row["child"]);
				}
				else
				{
					$this->tpl->setVariable("LINK_ITEM", "../repository.php?cmd=frameset&ref_id=".$row["child"]);
					$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
					$this->tpl->setVariable("LINK_TARGET", " target=\"$t_frame\" ");
				}

				$this->tpl->parseCurrentBlock();

				$this->tpl->setCurrentBlock("locator");

				// ### AA 03.11.10 added new locator GUI class ###
				// navigate locator
				if ($row["child"] != $a_tree->getRootId())
				{
					$ilias_locator->navigate($i++,$row["title"],"../repository.php?ref_id=".$row["child"],"bottom");
				}
				else
				{
					$ilias_locator->navigate($i++,$this->lng->txt("repository"),"../repository.php?ref_id=".$row["child"],"bottom");
				}
			}

			/*
			if (DEBUG)
			{
				$debug = "DEBUG: <font color=\"red\">".$this->type."::".$this->id."::".$_GET["cmd"]."</font><br/>";
			}

			$prop_name = $this->objDefinition->getPropertyName($_GET["cmd"],$this->type);

			if ($_GET["cmd"] == "confirmDeleteAdm")
			{
				$prop_name = "delete_object";
			}*/

			$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
			$this->tpl->parseCurrentBlock();
		}

	}

	/**
	* output main header (title and locator)
	*/
	function getTemplate()
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		//$this->tpl->setVariable("HEADER", $a_header_title);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		//$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
	}

	function showLearningModule()
	{
		$dir = $this->object->getDataDirectory();
		if (($this->object->getStartFile() != "") &&
			(@is_file($dir."/".$this->object->getStartFile())))
		{
			ilUtil::redirect("../".$dir."/".$this->object->getStartFile());
		}
		else if (@is_file($dir."/index.html"))
		{
			ilUtil::redirect("../".$dir."/index.html");
		}
		else if (@is_file($dir."/index.htm"))
		{
			ilUtil::redirect("../".$dir."/index.htm");
		}
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_lm_b.gif"));
		$this->tpl->parseCurrentBlock();
		
		$this->getTabs($this->tabs_gui);
		$this->tpl->setVariable("TABS", $this->tabs_gui->getHTML());
		$this->tpl->setVariable("HEADER", $this->object->getTitle());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;
		
		// properties
		$tabs_gui->addTarget("cont_list_files",
			$this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"), "",
			"ilfilesystemgui");
			
		// properties
		$tabs_gui->addTarget("properties",
			$this->ctrl->getLinkTarget($this, "properties"), "properties",
			get_class($this));

		$tabs_gui->addTarget("meta_data",
			$this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
			"", "ilmdeditorgui");

		// edit bib item information
		$tabs_gui->addTarget("bib_data",
			$this->ctrl->getLinkTarget($this, "editBibItem"),
			array("editBibItem", "saveBibItem", "deleteBibItem", "addBibItem"),
			get_class($this));

		// perm
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
}
?>
