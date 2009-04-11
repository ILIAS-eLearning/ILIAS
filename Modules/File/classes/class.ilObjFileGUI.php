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


require_once "./classes/class.ilObjectGUI.php";
require_once "./Modules/File/classes/class.ilObjFile.php";
require_once "./Modules/File/classes/class.ilObjFileAccess.php";

/**
* GUI class for file objects.
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @ilCtrl_Calls ilObjFileGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilShopPurchaseGUI
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
		$this->lng->loadLanguageModule('file');
	}
	
	function _forwards()
	{
		return array();
	}
	
	function &executeCommand()
	{
		global $ilAccess, $ilNavigationHistory;	
		
		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilNavigationHistory->addItem($_GET["ref_id"],
				"repository.php?cmd=infoScreen&ref_id=".$_GET["ref_id"], "file");
		}		
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		if(!$this->getCreationMode())
		{
			include_once 'payment/classes/class.ilPaymentObject.php';				
			if(ilPaymentObject::_isBuyable($this->object->getRefId()) && 
			   !ilPaymentObject::_hasAccess($this->object->getRefId()))
			{
				$this->setLocator();
				$this->tpl->getStandardTemplate();			

				include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
				$pp = new ilShopPurchaseGUI((int)$_GET['ref_id']);				
				$ret = $this->ctrl->forwardCommand($pp);
				return true;
			}
		}
		
		$this->prepareOutput();
		
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
		$this->single_form_gui->setMultipart(true);
		
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
		$this->zip_form_gui->setMultipart(true);
				
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
		
		if ($rbacsystem->checkAccess("create", $_GET["ref_id"], "file")) {
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
					ilUtil::sendSuccess($this->lng->txt("file_added"),true);					
				}
				catch (ilFileUtilsException $e) 
				{
					ilUtil::sendFailure($e->getMessage(), true);
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
		else
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveObject()
	{
		global $rbacsystem, $objDefinition, $ilUser;

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
			else
			{
				// BEGIN WebDAV: Ensure that object title ends with the filename extension
				$fileExtension = ilObjFileAccess::_getFileExtension($upload_file["name"]);
				$titleExtension = ilObjFileAccess::_getFileExtension($title);
				if ($titleExtension != $fileExtension && strlen($fileExtension) > 0)
				{
					$title .= '.'.$fileExtension;
				}
				// END WebDAV: Ensure that object title ends with the filename extension
			}

			// create and insert file in grp_tree
			include_once("./Modules/File/classes/class.ilObjFile.php");
			$fileObj = new ilObjFile();
			// BEGIN WebDAV: Workaround for Firefox: Enforce filetype application/pdf for filetype application/x-pdf
			$fileObj->setFileType(
					$_FILES["Fobject"]["type"]["file"] == 'application/x-pdf' ?
						'application/pdf' :
						$_FILES["Fobject"]["type"]["file"]
				);
			// END WebDAV: Workaround for Firefox: Enforce filetype application/pdf for filetype application/x-pdf
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
	
			// BEGIN ChangeEvent: Record write event.
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			if (ilChangeEvent::_isActive())
			{
				ilChangeEvent::_recordWriteEvent($fileObj->getId(), $ilUser->getId(), 'create');
			}
			// END ChangeEvent: Record write event.
			ilUtil::sendSuccess($this->lng->txt("file_added"),true);
			
			$this->ctrl->setParameter($this, "ref_id", $fileObj->getRefId());
			if ($this->ctrl->getCmd() == "saveAndMeta")
			{
				$target =
					$this->ctrl->getLinkTargetByClass(array("ilobjfilegui", "ilmdeditorgui"), "listSection");
				$target = str_replace("new_type=", "nt=", $target);
				ilUtil::redirect($this->getReturnLocation("save", $target));
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
		$this->tabs_gui->setTabActive('edit');
		
		$this->initPropertiesForm('edit');
		if(!$this->form->checkInput())
		{
			$this->form->setValuesByPost();
			$this->tpl->setContent($this->form->getHTML());
			return false;	
		}
		
		$data = $this->form->getInput('file');		

		// delete trailing '/' in filename
		while (substr($data["name"],-1) == '/')
		{
			$data["name"] = substr($data["name"],0,-1);
		}
		
		$filename = empty($data["name"]) ? $this->object->getFileName() : $data["name"];
		$title = $this->form->getInput('title');
		if(strlen(trim($title)) == 0)
		{
			$title = $filename;
		}
		else
		{
			// BEGIN WebDAV: Ensure that object title ends with the filename extension
			$fileExtension = ilObjFileAccess::_getFileExtension($filename);
			$titleExtension = ilObjFileAccess::_getFileExtension($title);
			if ($titleExtension != $fileExtension && strlen($fileExtension) > 0)
			{
				$title .= '.'.$fileExtension;
			}
			// END WebDAV: Ensure that object title ends with the filename extension
		}
		$this->object->setTitle($title);
				

		if (!empty($data["name"]["file"]))
		{
			switch($this->form->getInput('replace'))
			{
				case 1:
					$this->object->deleteVersions();
					$this->object->clearDataDirectory();
				case 0:
					$this->object->replaceFile($data['tmp_name'],$data['name']);
					$this->object->setFileName($data['name']);
					$this->object->setFileType($data['type']);
					$this->object->setFileSize($data['size']);
			}
		}
		$this->object->setDescription($this->form->getInput('description'));
		
		$this->update = $this->object->update();

		// BEGIN ChangeEvent: Record update event.
		if (!empty($data["name"]))
		{
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			if (ilChangeEvent::_isActive())
			{
				global $ilUser;
				ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
				ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
			}
		}
		// END ChangeEvent: Record update event.
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,'edit'));
	}

	
	/**
	* edit object
	*
	* @access	public
	*/
	function editObject()
	{
		global $rbacsystem, $ilAccess;

		if (!$ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('edit');

		$this->initPropertiesForm('edit');
		$this->getPropertiesValues('edit');
		
		$this->tpl->setContent($this->form->getHTML());
		return true;


		$fields = array();

		if ($_SESSION["error_post_vars"])
		{
			// fill in saved values in case of error
			$fields["title"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true);
			$fields["desc"] = ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["desc"], true);
		}
		else
		{
			$fields["title"] = ilUtil::prepareFormOutput($this->object->getTitle());
			$fields["desc"] = ilUtil::prepareFormOutput($this->object->getLongDescription());
		}
		
		$this->getTemplateFile("edit");
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TITLE", $fields["title"]);
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("DESC", $fields["desc"]);
		$this->tpl->setVariable("TXT_REPLACE_FILE", $this->lng->txt("replace_file"));
		//$this->tpl->parseCurrentBlock();

		$obj_str = ($this->call_by_reference) ? "" : "&obj_id=".$this->obj_id;

		$this->tpl->setVariable("FORMACTION", $this->getFormAction("update",$this->ctrl->getFormAction($this, "update").$obj_str));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt($this->object->getType()."_edit"));
		$this->tpl->setVariable("TARGET", $this->getTargetFrame("update"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_SUBMIT", "update");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		//$this->tpl->parseCurrentBlock();
	}
	
	protected function getPropertiesValues($a_mode = 'edit')
	{
		if($a_mode == 'edit')
		{
			$val['title'] = $this->object->getTitle();
			$val['description'] = $this->object->getLongDescription();
			$this->form->setValuesByArray($val);
		}
		return true;
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	protected function initPropertiesForm($a_mode)
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this),'update');
		$this->form->setTitle($this->lng->txt('file_edit'));
		$this->form->addCommandButton('update',$this->lng->txt('save'));
		$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
			
		#$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		#$title->setValue($this->object->getTitle());
		#$this->form->addItem($title);
		
		
		$file = new ilFileInputGUI($this->lng->txt('obj_file'),'file');
		$file->setRequired(false);
		$file->enableFileNameSelection('title');
		$this->form->addItem($file);
		
			$group = new ilRadioGroupInputGUI('','replace');
			$group->setValue(0);
			
			$replace = new ilRadioOption($this->lng->txt('replace_file'),1);
			$replace->setInfo($this->lng->txt('replace_file_info'));
			$group->addOption($replace);
			
			
			$keep = new ilRadioOption($this->lng->txt('file_new_version'),0);
			$keep->setInfo($this->lng->txt('file_new_version_info'));
			$group->addOption($keep);
		
		$file->addSubItem($group);
			
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'description');
		$desc->setRows(3);
		#$desc->setCols(40);
		$this->form->addItem($desc);
	}
	
	function sendFileObject()
	{
		global $ilAccess;
		
		if ($ilAccess->checkAccess("read", "", $this->ref_id))
		{
			// BEGIN ChangeEvent: Record read event.
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			if (ilChangeEvent::_isActive())
			{
				global $ilUser;
				// Record read event and catchup with write events
				ilChangeEvent::_recordReadEvent($this->object->getId(), $ilUser->getId());
			}
			// END ChangeEvent: Record read event.

			$this->object->sendFile($_GET["hist_id"]);
		}
		else
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}
		return true;
	}


	/**
	* file versions/history
	*
	* @access	public
	*/
	function versionsObject()
	{
		global $rbacsystem, $ilAccess;

		if (!$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
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
		// BEGIN WebDAV Guess file type.
		$info->addProperty($this->lng->txt("type"),
				$this->object->guessFileType());
		// END WebDAV Guess file type.
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
		global $rbacsystem, $ilAccess;
		
//echo "-".$this->ctrl->getCmd()."-";

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($ilAccess->checkAccess("visible", "", $this->ref_id))
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

		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$tabs_gui->addTarget("edit",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", "");
		}
		
		// meta data 
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$tabs_gui->addTarget("meta_data",
				 $this->ctrl->getLinkTargetByClass(array('ilobjfilegui','ilmdeditorgui'),'listSection'),
				 "", 'ilmdeditorgui');
		}

		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$tabs_gui->addTarget("versions",
				$this->ctrl->getLinkTarget($this, "versions"), "versions", get_class($this));
		}

		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
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
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
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
