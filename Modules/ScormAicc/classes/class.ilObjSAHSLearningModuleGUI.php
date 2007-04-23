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

require_once "classes/class.ilObjectGUI.php";
require_once("classes/class.ilFileSystemGUI.php");
require_once("classes/class.ilTabsGUI.php");

/**
* SCORM/AICC/HACP Learning Modules
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjSAHSLearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI
*
* @ingroup ModulesScormAicc
*/
class ilObjSAHSLearningModuleGUI extends ilObjectGUI
{
	/**
	* Constructor
	*
	* @access	public
	*/
	function ilObjSAHSLearningModuleGUI($a_data,$a_id,$a_call_by_reference, $a_prepare_output = true)
	{
		global $lng;

		$lng->loadLanguageModule("content");
		$this->type = "sahs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		#$this->tabs_gui =& new ilTabsGUI();

	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilAccess;
		
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
				
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case "ilfilesystemgui":
				$this->fs_gui =& new ilFileSystemGUI($this->object->getDataDirectory());
				$ret =& $this->ctrl->forwardCommand($this->fs_gui);
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,$this->object->getRefId());
				$this->ctrl->forwardCommand($new_gui);

				break;

			case "ilinfoscreengui":
				include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

				$info = new ilInfoScreenGUI($this);
				$info->enablePrivateNotes();
				$info->enableLearningProgress();
				
				// add read / back button
				if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
				{
					$info->addButton($this->lng->txt("view"),
						"ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->object->getRefID(),
						' target="ilContObj'.$this->object->getId().'" ');
				}

				$info->enableNews();
				if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
				{
					$info->enableNewsEditing();
					$info->setBlockProperty("news", "settings", true);
				}
				// show standard meta data section
				$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
				// forward the command
				$this->ctrl->forwardCommand($info);
				break;

			default:
				$cmd = $this->ctrl->getCmd("frameset");
				if ((strtolower($_GET["baseClass"]) == "iladministrationgui" ||
					$this->getCreationMode() == true) &&
					$cmd != "frameset")
				{
					$cmd.= "Object";
				}
				$ret =& $this->$cmd();
				break;
		}
	}


	function viewObject()
	{
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
		}
		else
		{
		}
	}

	/**
	* module properties
	*/
	function properties()
	{
	}

	/**
	* save properties
	*/
	function saveProperties()
	{
	}


	/**
	* no manual SCORM creation, only import at the time
	*/
	function createObject()
	{
		$this->importObject();
	}

	/**
	* display dialogue for importing SCORM package
	*
	* @access	public
	*/
	function importObject()
	{
		// display import form
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.slm_import.html", "Modules/ScormAicc");
		
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.gif'));
		$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_sahs"));
		
		$this->ctrl->setParameter($this, "new_type", "sahs");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("BTN_NAME", "save");
		$this->tpl->setVariable("TARGET", ' target="'.
			ilFrameTargetInfo::_getFrame("MainContent").'" ');

		$this->tpl->setVariable("TXT_SELECT_LMTYPE", $this->lng->txt("type"));
		$this->tpl->setVariable("TXT_TYPE_AICC", $this->lng->txt("lm_type_aicc"));
		$this->tpl->setVariable("TXT_TYPE_HACP", $this->lng->txt("lm_type_hacp"));
		$this->tpl->setVariable("TXT_TYPE_SCORM", $this->lng->txt("lm_type_scorm"));

		$this->tpl->setVariable("TXT_TYPE_SCORM2004", $this->lng->txt("lm_type_scorm2004"));
		
		$this->tpl->setVariable("TXT_UPLOAD", $this->lng->txt("upload"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_IMPORT_LM", $this->lng->txt("import_sahs"));
		$this->tpl->setVariable("TXT_SELECT_FILE", $this->lng->txt("select_file"));
		$this->tpl->setVariable("TXT_VALIDATE_FILE", $this->lng->txt("cont_validate_file"));

		// get the value for the maximal uploadable filesize from the php.ini (if available)
		$umf=get_cfg_var("upload_max_filesize");
		// get the value for the maximal post data from the php.ini (if available)
		$pms=get_cfg_var("post_max_size");
		
		//convert from short-string representation to "real" bytes
		$multiplier_a=array("K"=>1024, "M"=>1024*1024, "G"=>1024*1024*1024);
		
		$umf_parts=preg_split("/(\d+)([K|G|M])/", $umf, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        $pms_parts=preg_split("/(\d+)([K|G|M])/", $pms, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);
        
        if (count($umf_parts) == 2) { $umf = $umf_parts[0]*$multiplier_a[$umf_parts[1]]; }
        if (count($pms_parts) == 2) { $pms = $pms_parts[0]*$multiplier_a[$pms_parts[1]]; }
        
        // use the smaller one as limit
		$max_filesize=min($umf, $pms);

		if (!$max_filesize) $max_filesize=max($umf, $pms);
	
    	//format for display in mega-bytes
		$max_filesize=sprintf("%.1f MB",$max_filesize/1024/1024);

		// gives out the limit as a little notice
		$this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice")." $max_filesize");
	}

	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject()
	{
		global $_FILES, $rbacsystem;

		// check if file was uploaded
		$source = $_FILES["scormfile"]["tmp_name"];
		if (($source == 'none') || (!$source))
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}
		// check create permission
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], "sahs"))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
		}
		// get_cfg_var("upload_max_filesize"); // get the may filesize form t he php.ini
		switch ($__FILES["scormfile"]["error"])
		{
			case UPLOAD_ERR_INI_SIZE:
				$this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_FORM_SIZE:
				$this->ilias->raiseError($this->lng->txt("err_max_file_size_exceeds"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_PARTIAL:
				$this->ilias->raiseError($this->lng->txt("err_partial_file_upload"),$this->ilias->error_obj->MESSAGE);
				break;

			case UPLOAD_ERR_NO_FILE:
				$this->ilias->raiseError($this->lng->txt("err_no_file_uploaded"),$this->ilias->error_obj->MESSAGE);
				break;
		}

		$file = pathinfo($_FILES["scormfile"]["name"]);
		$name = substr($file["basename"], 0, strlen($file["basename"]) - strlen($file["extension"]) - 1);
		if ($name == "")
		{
			$name = $this->lng->txt("no_title");
		}

		// create and insert object in objecttree
		switch ($_POST["sub_type"])
		{
			
			case "scorm2004":
				include_once("./Modules/ScormAicc/classes/class.ilObjSCORM2004LearningModule.php");
				$newObj = new ilObjSCORM2004LearningModule();
				break;

			case "scorm":
				include_once("./Modules/ScormAicc/classes/class.ilObjSCORMLearningModule.php");
				$newObj = new ilObjSCORMLearningModule();
				break;

			case "aicc":
				include_once("./Modules/ScormAicc/classes/class.ilObjAICCLearningModule.php");
				$newObj = new ilObjAICCLearningModule();
				break;

			case "hacp":
				include_once("./Modules/ScormAicc/classes/class.ilObjHACPLearningModule.php");
				$newObj = new ilObjHACPLearningModule();
				break;
		}

		$newObj->setTitle($name);
		$newObj->setSubType($_POST["sub_type"]);
		$newObj->setDescription("");
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		// create data directory, copy file to directory
		$newObj->createDataDirectory();

		// copy uploaded file to data directory
		$file_path = $newObj->getDataDirectory()."/".$_FILES["scormfile"]["name"];
		
		ilUtil::moveUploadedFile($_FILES["scormfile"]["tmp_name"],
			$_FILES["scormfile"]["name"], $file_path);

		//move_uploaded_file($_FILES["scormfile"]["tmp_name"], $file_path);

		ilUtil::unzip($file_path);
		ilUtil::renameExecutables($newObj->getDataDirectory());

		$title = $newObj->readObject();		
		if ($title != "")
		{
			ilObject::_writeTitle($newObj->getId(), $title);
			$md = new ilMD($newObj->getId(),0, $newObj->getType());
			if(is_object($md_gen = $md->getGeneral()))
			{
				$md_gen->setTitle($title);
				$md_gen->update();
			}
		}
		
		ilUtil::sendInfo( $this->lng->txt($newObj->getType()."_added"), true);
		ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&ref_id=".$newObj->getRefId());
	}

	function upload()
	{
		$this->uploadObject();
	}

	/**
	* save new learning module to db
	*/
	function saveObject()
	{
		global $rbacadmin;

		$this->uploadObject();
	}


	/**
	* permission form
	*/
	function info()
	{
		$this->infoObject();
	}

	/**
	* show owner of learning module
	*/
	function owner()
	{
		$this->ownerObject();
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


	/**
	* output main frameset of media pool
	* left frame: explorer tree of folders
	* right frame: media pool content
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.sahs_edit_frameset.html", false, false, "Modules/ScormAicc");
		$this->tpl->setVariable("SRC",
			$this->ctrl->getLinkTarget($this, "properties"));
		$this->tpl->show("DEFAULT", false);
		exit;
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
		#$this->tpl->setVariable("TABS", $this->tabs_gui->getHTML());
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
		
		if ($this->ctrl->getCmd() == "delete")
		{
			return;
		}

		// info screen
		$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui")
			? true
			: false;
		$tabs_gui->addTarget("info_short",
			$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"), "",
			"ilinfoscreengui", "", $force_active);

		// properties
		$tabs_gui->addTarget("properties",
			$this->ctrl->getLinkTarget($this, "properties"), "properties",
			get_class($this));

		// file system gui tabs
		// properties
		$tabs_gui->addTarget("cont_list_files",
			$this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"), "",
			"ilfilesystemgui");

		// tracking data
		$tabs_gui->addTarget("cont_tracking_data",
			$this->ctrl->getLinkTarget($this, "showTrackingItems"), "showTrackingItems",
			get_class($this));

		// edit meta
/*
		$tabs_gui->addTarget("meta_data",
			$this->ctrl->getLinkTarget($this, "editMeta"), "editMeta",
			get_class($this));
*/
		$tabs_gui->addTarget("meta_data",
			 $this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
			 "", "ilmdeditorgui");

		// learning progress
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(ilObjUserTracking::_enabledLearningProgress())
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		// perm
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}

		// owner
/*
		$tabs_gui->addTarget("owner",
			$this->ctrl->getLinkTarget($this, "owner"), "owner",
			get_class($this));
*/
	}

	/**
	* goto target course
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		// to do: force flat view
		if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["cmd"] = "infoScreen";
			$_GET["baseClass"] = "ilSAHSPresentationGUI";
			$_GET["ref_id"] = $a_target;
			include("ilias.php");
			exit;
		}
		else
		{
			if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
			{
				$_GET["cmd"] = "frameset";
				$_GET["target"] = "";
				$_GET["ref_id"] = ROOT_FOLDER_ID;
				ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
					ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
				include("repository.php");
				exit;
			}
		}

		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}

	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(),
				$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"), "", $_GET["ref_id"]);
		}
	}

} // END class.ilObjSAHSLearningModule
?>
