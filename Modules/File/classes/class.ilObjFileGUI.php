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
//var_dump($_GET);
//var_dump($_POST);
//var_dump($_SESSION);
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
	function createObject($a_reload_form = "")
	{
		global $rbacsystem, $ilCtrl;

		$new_type = $_POST["new_type"] ? $_POST["new_type"] : $_GET["new_type"];

		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], $new_type))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		// fill in saved values in case of error
		$this->getTemplateFile("new",$this->type);

		if ($a_reload_form != "single_upload")
		{
			$this->initSingleUploadForm("create");
		}
		$this->tpl->setVariable("SINGLE_UPLOAD_FORM", $this->single_form_gui->getHtml());

		if ($a_reload_form != "zip_upload")
		{
			$this->initZipUploadForm("create");
		}
		$this->tpl->setVariable("ZIP_UPLOAD_FORM", $this->zip_form_gui->getHtml());

		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_TAKE_OVER_STRUCTURE", $this->lng->txt("take_over_structure"));
		$this->tpl->setVariable("TXT_HEADER_ZIP", $this->lng->txt("header_zip"));
		
		$this->fillCloneTemplate('DUPLICATE','file');
	}

	/**
	* FORM: Init single upload form.
	*
	* @param        int        $a_mode        "create" / "update" (not implemented)
	*/
	public function initSingleUploadForm($a_mode = "create")
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->single_form_gui = new ilPropertyFormGUI();
		
		// File Title
		$in_title = new ilTextInputGUI($lng->txt("title"), "title");
		$in_title->setInfo($this->lng->txt("if_no_title_then_filename"));
		$in_title->setMaxLength(128);
		$in_title->setSize(40);
		$this->single_form_gui->addItem($in_title);
		
		// File Description
		$in_descr = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$this->single_form_gui->addItem($in_descr);
		
		// File
		$in_file = new ilFileInputGUI($lng->txt("file"), "upload_file");
		$in_file->setRequired(true);
		$this->single_form_gui->addItem($in_file);
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->single_form_gui->addCommandButton("save", $this->lng->txt($this->type."_add"));
			$this->single_form_gui->addCommandButton("saveAndMeta", $this->lng->txt("file_add_and_metadata"));
			$this->single_form_gui->addCommandButton("cancel", $lng->txt("cancel"));
		}
		else
		{
			//$this->single_form_gui->addCommandButton("update", $lng->txt("save"));
			//$this->single_form_gui->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		
		$this->single_form_gui->setTableWidth("60%");
		$this->single_form_gui->setTarget($this->getTargetFrame("save"));
		$this->single_form_gui->setTitle($this->lng->txt($this->type."_new"));
		$this->single_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.gif'), $this->lng->txt('obj_file'));
		
		if ($a_mode == "create")
		{
			$this->ctrl->setParameter($this, "new_type", "file");
		}
		$this->single_form_gui->setFormAction($this->ctrl->getFormAction($this));
	}

	/**
	* FORM: Init zip upload form.
	*
	* @param        int        $a_mode        "create" / "update" (not implemented)
	*/
	public function initZipUploadForm($a_mode = "create")
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->zip_form_gui = new ilPropertyFormGUI();
				
		// File
		$in_file = new ilFileInputGUI($lng->txt("file"), "zip_file");
		$in_file->setRequired(true);
		$in_file->setSuffixes(array("zip"));
		$this->zip_form_gui->addItem($in_file);

		// Take over structure
		$in_str = new ilCheckboxInputGUI($this->lng->txt("take_over_structure"), "adopt_structure");
		$in_str->setInfo($this->lng->txt("take_over_structure_info"));
		$this->zip_form_gui->addItem($in_str);
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->zip_form_gui->addCommandButton("saveUnzip", $this->lng->txt($this->type."_add"));
			$this->zip_form_gui->addCommandButton("cancel", $lng->txt("cancel"));
		}
		else
		{
			//$this->zip_form_gui->addCommandButton("update", $lng->txt("save"));
			//$this->zip_form_gui->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		
		$this->zip_form_gui->setTableWidth("60%");
		$this->zip_form_gui->setTarget($this->getTargetFrame("save"));
		$this->zip_form_gui->setTitle($this->lng->txt("header_zip"));
		$this->zip_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.gif'), $this->lng->txt('obj_file'));
		
		if ($a_mode == "create")
		{
			$this->ctrl->setParameter($this, "new_type", "file");
		}
		$this->zip_form_gui->setFormAction($this->ctrl->getFormAction($this));
	}

	/**
	* saveUnzip object
	*
	* @access	public
	*/
	function saveUnzipObject()
	{
		global $rbacsystem;
		
		$this->initZipUploadForm("create");
		
		if ($this->zip_form_gui->checkInput())
		{
			$zip_file = $this->zip_form_gui->getInput("zip_file");
			$adopt_structure = $this->zip_form_gui->getInput("adopt_structure");

			include_once ("Services/Utilities/classes/class.ilFileUtils.php");

			// Create unzip-directory
			$newDir = ilUtil::ilTempnam();
			ilUtil::makeDir($newDir);
		
			// Check if permission is granted for creation of object, if necessary
			if (ilObject::_lookupType($_GET["ref_id"], TRUE) == "cat")
			{
				$permission = $rbacsystem->checkAccess("create", $_GET["ref_id"], "cat");
				$containerType = "Category";
			}
			else {
				$permission = $rbacsystem->checkAccess("create", $_GET["ref_id"], "fold");
				$containerType = "Folder";			
			}
		
			// 	processZipFile ( 
			//		Dir to unzip, 
			//		Path to uploaded file, 
			//		should a structure be created (+ permission check)?
			//		ref_id of parent
			//		object that contains files (folder or category)  
			//		should sendInfo be persistent?)
			
			try 
			{
				$processDone = ilFileUtils::processZipFile( $newDir, 
					$zip_file["tmp_name"],
					($adopt_structure && $permission),
					$_GET["ref_id"],
					$containerType,
					true);
				ilUtil::sendInfo($this->lng->txt("file_added"),true);					
			}
			catch (ilFileUtilsException $e) 
			{
				ilUtil::sendInfo($e->getMessage(), true);
			}
		
			ilUtil::delDir($newDir);
			$this->ctrl->returnToParent($this);
		}
		else
		{
			$this->zip_form_gui->setValuesByPost();
			$this->createObject("zip_upload");
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

		$this->initSingleUploadForm("create");
		
		if ($this->single_form_gui->checkInput())
		{
			$title = $this->single_form_gui->getInput("title");
			$description = $this->single_form_gui->getInput("description");
			$upload_file = $this->single_form_gui->getInput("upload_file");

			if (trim($title) == "")
			{
				$title = $upload_file["name"];
			}

			// create and insert file in grp_tree
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$fileObj = new ilObjFile();
			$fileObj->setType($this->type);
			$fileObj->setTitle($title);
			$fileObj->setDescription($description);
			$fileObj->setFileName($upload_file["name"]);
			$fileObj->setFileType($upload_file["type"]);
			$fileObj->setFileSize($upload_file["size"]);
			$fileObj->create();
			$fileObj->createReference();
			$fileObj->putInTree($_GET["ref_id"]);
			$fileObj->setPermissions($_GET["ref_id"]);
			// upload file to filesystem
			$fileObj->createDirectory();
			$fileObj->getUploadFile($upload_file["tmp_name"],
				$upload_file["name"]);
	
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
		else
		{
			$this->single_form_gui->setValuesByPost();
			$this->createObject("single_upload");
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
