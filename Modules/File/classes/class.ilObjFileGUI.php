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
require_once "./Modules/File/classes/class.ilObjFile.php";

/**
* GUI class for file objects.
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @ilCtrl_Calls ilObjFileGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI
*
* @ingroup ModulesFile
*/
class ilObjFileGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access	public
	*/
	function ilObjFileGUI($a_data,$a_id,$a_call_by_reference = true, $a_prepare_output = true)
	{
		$this->type = "file";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
	}
	
	function _forwards()
	{
		return array();
	}
	
	function &executeCommand()
	{
		global $ilAccess, $ilNavigationHistory;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
		
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"repository.php?cmd=infoScreen&ref_id=".$_GET["ref_id"], "file");
		}
		
//echo "-$cmd-";
		switch ($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;

			case 'ilmdeditorgui':

				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');
				
				// todo: make this work
				$md_gui->addObserver($this->object,'MDUpdateListener','Technical');
				
				$this->ctrl->forwardCommand($md_gui);
				break;
				
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if (empty($cmd))
				{
					// does not seem to work
					//$this->ctrl->returnToParent($this);
					//$cmd = "view";
					$cmd = "infoScreen";
				}

				$cmd .= "Object";
				$this->$cmd();
				break;
		}		
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
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		// fill in saved values in case of error
		$data = array();
		$data["fields"] = array();
		$data["fields"]["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
		$data["fields"]["desc"] = ilUtil::stripSlashes($_SESSION["error_post_vars"]["Fobject"]["desc"]);
		$data["fields"]["file"] = $_SESSION["error_post_vars"]["Fobject"]["file"];

		$this->getTemplateFile("new",$this->type);

		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_file.gif'));
		$this->tpl->setVariable("ALT_IMG", $this->lng->txt('obj_file'));

		foreach ($data["fields"] as $key => $val)
		{
			$this->tpl->setVariable("TXT_".strtoupper($key), $this->lng->txt($key));
			$this->tpl->setVariable(strtoupper($key), $val);
			#$this->tpl->parseCurrentBlock();
		}
		
		$this->ctrl->setParameter($this, "new_type", $new_type);
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("TXT_TITLE_NOTE", $this->lng->txt("if_no_title_then_filename"));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->type."_new"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt($this->type."_add"));
		$this->tpl->setVariable("TXT_SUBMIT_AND_META", $this->lng->txt("file_add_and_metadata"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("CMD_SUBMIT_AND_META", "saveAndMeta");
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		
		$this->fillCloneTemplate('DUPLICATE','file');
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $objDefinition;

		$data = $_FILES["Fobject"];

		// delete trailing '/' in filename
		while (substr($data["name"]["file"],-1) == '/')
		{
			$data["name"]["file"] = substr($data["name"]["file"],0,-1);
		}

		if (empty($data["name"]["file"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_file"),$this->ilias->error_obj->MESSAGE);
		}

		if (empty($_POST["Fobject"]["title"]))
		{
			$_POST["Fobject"]["title"] = $_FILES["Fobject"]["name"]["file"];
			//$this->ilias->raiseError($this->lng->txt("msg_no_title"),$this->ilias->error_obj->MESSAGE);
		}

		// create and insert file in grp_tree
		include_once("./Modules/File/classes/class.ilObjFile.php");
		$fileObj = new ilObjFile();
		$fileObj->setType($this->type);
		$fileObj->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$fileObj->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$fileObj->setFileName(ilUtil::stripSlashes($_FILES["Fobject"]["name"]["file"]));
		$fileObj->setFileType($_FILES["Fobject"]["type"]["file"]);
		$fileObj->setFileSize($_FILES["Fobject"]["size"]["file"]);
		$fileObj->create();
		$fileObj->createReference();
		$fileObj->putInTree($_GET["ref_id"]);
		$fileObj->setPermissions($_GET["ref_id"]);
		// upload file to filesystem
		$fileObj->createDirectory();
		$fileObj->getUploadFile($_FILES["Fobject"]["tmp_name"]["file"],ilUtil::stripSlashes($_FILES["Fobject"]["name"]["file"]));

		ilUtil::sendInfo($this->lng->txt("file_added"),true);
		
		$this->ctrl->setParameter($this, "ref_id", $fileObj->getRefId());
		if ($this->ctrl->getCmd() == "saveAndMeta")
		{
			ilUtil::redirect($this->getReturnLocation("save",
				$this->ctrl->getLinkTargetByClass(array("ilobjfilegui", "ilmdeditorgui"), "listSection")));
		}
		else
		{
			$this->ctrl->returnToParent($this);
		}
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveAndMetaObject()
	{
		$this->saveObject();
	}

	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	function updateObject()
	{
		$data = $_FILES["Fobject"];

		// delete trailing '/' in filename
		while (substr($data["name"]["file"],-1) == '/')
		{
			$data["name"]["file"] = substr($data["name"]["file"],0,-1);
		}

		if (empty($data["name"]["file"]) && empty($_POST["Fobject"]["title"]))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_title"),$this->ilias->error_obj->MESSAGE);
		}

		if (empty($_POST["Fobject"]["title"]))
		{
			$_POST["Fobject"]["title"] = $_FILES["Fobject"]["name"]["file"];
		}

		if (!empty($data["name"]["file"]))
		{
			$this->object->replaceFile($_FILES["Fobject"]["tmp_name"]["file"],$_FILES["Fobject"]["name"]["file"]);
			$this->object->setFileName($_FILES["Fobject"]["name"]["file"]);
			$this->object->setFileType($_FILES["Fobject"]["type"]["file"]);
			$this->object->setFileSize($_FILES["Fobject"]["size"]["file"]);
		}
		
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));

		$this->update = $this->object->update();

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
//echo "-".$this->ctrl->getLinkTarget($this)."-";
		ilUtil::redirect($this->getReturnLocation("update",$this->ctrl->getLinkTarget($this, "edit")));
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
			$fields["desc"] = ilUtil::stripSlashes($this->object->getDescription());
		}
		
		$this->getTemplateFile("edit");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TITLE", $fields["title"]);
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("DESC", $fields["desc"]);
		$this->tpl->setVariable("TXT_REPLACE_FILE", $this->lng->txt("replace_file"));
		//$this->tpl->parseCurrentBlock();

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update",$this->ctrl->getFormAction($this).$obj_str));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		//$this->tpl->parseCurrentBlock();
	}
	
	function sendFileObject()
	{
		$this->object->sendFile($_GET["hist_id"]);
		return true;
	}


	/**
	* file versions/history
	*
	* @access	public
	*/
	function versionsObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $_GET["ref_id"]))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		require_once("classes/class.ilHistoryGUI.php");
		
		$hist_gui =& new ilHistoryGUI($this->object->getId());
		
		// not nice, should be changed, if ilCtrl handling
		// has been introduced to administration
		$hist_html = $hist_gui->getVersionsTable(
			array("ref_id" => $_GET["ref_id"], "cmd" => "versions",
			"cmdClass" =>$_GET["cmdClass"], "cmdNode" =>$_GET["cmdNode"]));
		
		$this->tpl->setVariable("ADM_CONTENT", $hist_html);
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		if ($ilAccess->checkAccess("read", "sendfile", $this->ref_id))
		{
			$info->addButton($this->lng->txt("file_read"), $this->ctrl->getLinkTarget($this, "sendfile"));
		}
		
		$info->enablePrivateNotes();
		
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$info->enableNews();
		}

		// no news editing for files, just notifications
		$info->enableNewsEditing(false);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
				$info->setBlockProperty("news", "public_notifications_option", true);
			}
		}

		
		// standard meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		$info->addSection($this->lng->txt("file_info"));
		$info->addProperty($this->lng->txt("filename"),
			$this->object->getFileName());
		$info->addProperty($this->lng->txt("type"),
			$this->object->getFileType());
		$info->addProperty($this->lng->txt("size"),
			ilObjFile::_lookupFileSize($this->object->getId(), true));
		$info->addProperty($this->lng->txt("version"),
			$this->object->getVersion());

		// forward the command
		$this->ctrl->forwardCommand($info);
	}


	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;
		
//echo "-".$this->ctrl->getCmd()."-";

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('visible',$this->ref_id))
		{
			$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTargetByClass(
				 array("ilobjfilegui", "ilinfoscreengui"), "showSummary"),
				 array("showSummary","", "infoScreen"),
				 "", "", $force_active);
		}

		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("edit",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", "");
		}
		
		// meta data 
		if($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass(array('ilobjfilegui','ilmdeditorgui'),'listSection'),
				 "", 'ilmdeditorgui');
		}

		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("versions",
				$this->ctrl->getLinkTarget($this, "versions"), "versions", get_class($this));
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["cmd"] = "infoScreen";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			$_GET["cmd"] = "frameset";
			$_GET["target"] = "";
			$_GET["ref_id"] = ROOT_FOLDER_ID;
			ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			include("repository.php");
			exit;
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);

	}

	/**
	*
	*/
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $_GET["ref_id"]);
		}
	}

} // END class.ilObjFileGUI
?>
