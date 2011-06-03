<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
require_once "./Modules/File/classes/class.ilObjFile.php";
require_once "./Modules/File/classes/class.ilObjFileAccess.php";

/**
* GUI class for file objects.
*
* @author Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @ilCtrl_Calls ilObjFileGUI: ilMDEditorGUI, ilInfoScreenGUI, ilPermissionGUI, ilShopPurchaseGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjFileGUI: ilExportGUI, ilWorkspaceAccessGUI, ilPublicUserProfileGUI, ilPortfolioPageGUI
*
* @ingroup ModulesFile
*/
class ilObjFileGUI extends ilObject2GUI
{
	function getType()
	{
		return "file";
	}

	// ???
	function _forwards()
	{
		return array();
	}
	
	function executeCommand()
	{
		global $ilNavigationHistory, $ilCtrl, $ilUser, $ilTabs;
		
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		if(!$this->getCreationMode())
		{
			// do not move this payment block!!
			if(IS_PAYMENT_ENABLED)
			{
				include_once './Services/Payment/classes/class.ilPaymentObject.php';
				if(ANONYMOUS_USER_ID == $ilUser->getId() && isset($_GET['transaction']))
				{
					$transaction = $_GET['transaction'];
					include_once './Services/Payment/classes/class.ilPaymentBookings.php';
					$valid_transaction = ilPaymentBookings::_readBookingByTransaction($transaction);
				}
			
				if(ilPaymentObject::_requiresPurchaseToAccess($this->node_id, $type = (isset($_GET['purchasetype'])
						? $_GET['purchasetype'] : NULL) ))
				{
					$this->setLocator();
					$this->tpl->getStandardTemplate();

					include_once './Services/Payment/classes/class.ilShopPurchaseGUI.php';
					$pp = new ilShopPurchaseGUI((int)$this->node_id);
					$ret = $this->ctrl->forwardCommand($pp);
					return true;
				}
			}
			else if($this->id_type == self::REPOSITORY_NODE_ID 
				&& $this->checkPermissionBool("read"))
			{
				// add entry to navigation history
				$ilNavigationHistory->addItem($this->node_id,
					"repository.php?cmd=infoScreen&ref_id=".$this->node_id, "file");
			}
		}
		
		$this->prepareOutput();
		
		switch ($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;

			case 'ilmdeditorgui':
				$ilTabs->activateTab("id_meta");

				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');
				
				// todo: make this work
				$md_gui->addObserver($this->object,'MDUpdateListener','Technical');
				
				$this->ctrl->forwardCommand($md_gui);
				break;
				
			// repository permissions
			case 'ilpermissiongui':
				$ilTabs->activateTab("id_permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;
		
			case "ilexportgui":
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$ret = $this->ctrl->forwardCommand($exp_gui);
				break;

			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('file');
				$this->ctrl->forwardCommand($cp);
				break;
			
			// personal workspace permissions
			case "ilworkspaceaccessgui";				
				$ilTabs->activateTab("id_permissions");
				include_once('./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessGUI.php');
				$wspacc = new ilWorkspaceAccessGUI($this->node_id, $this->getAccessHandler());
				$this->ctrl->forwardCommand($wspacc);
				break;
			
			// personal workspace share user profile
			case "ilpublicuserprofilegui";				
				$ilTabs->clearTargets();
				include_once('./Services/User/classes/class.ilPublicUserProfileGUI.php');
				$prof = new ilPublicUserProfileGUI($_REQUEST["user"]);
				$prof->setBackUrl($this->ctrl->getLinkTargetByClass("ilworkspaceaccessgui", "share"));
				$this->tpl->setContent($prof->getHTML());
				break;
			
			default:
				// in personal workspace use object2gui 
				if($this->id_type == self::WORKSPACE_NODE_ID)
				{
					$ilTabs->clearTargets();
					return parent::executeCommand();
				}
				
				if (empty($cmd))
				{
					$cmd = "infoScreen";
				}

				$this->$cmd();
				break;
		}		
	}
	
	protected function initCreationForms()
	{
		$forms = array();
		$forms[] = $this->initSingleUploadForm();
		$forms[] = $this->initZipUploadForm();
		return $forms;
	}

	/**
	* FORM: Init single upload form.
	*/
	public function initSingleUploadForm()
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$single_form_gui = new ilPropertyFormGUI();
		$single_form_gui->setMultipart(true);
		
		// File Title
		$in_title = new ilTextInputGUI($lng->txt("title"), "title");
		$in_title->setInfo($this->lng->txt("if_no_title_then_filename"));
		$in_title->setMaxLength(128);
		$in_title->setSize(40);
		$single_form_gui->addItem($in_title);
		
		// File Description
		$in_descr = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$single_form_gui->addItem($in_descr);
		
		// File
		$in_file = new ilFileInputGUI($lng->txt("file"), "upload_file");
		$in_file->setRequired(true);
		$single_form_gui->addItem($in_file);
		
		$single_form_gui->addCommandButton("save", $this->lng->txt($this->type."_add"));
		$single_form_gui->addCommandButton("saveAndMeta", $this->lng->txt("file_add_and_metadata"));
		$single_form_gui->addCommandButton("cancel", $lng->txt("cancel"));
		
		$single_form_gui->setTableWidth("600px");
		$single_form_gui->setTarget($this->getTargetFrame("save"));
		$single_form_gui->setTitle($this->lng->txt($this->type."_new"));
		$single_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.gif'), $this->lng->txt('obj_file'));
		
		$this->ctrl->setParameter($this, "new_type", "file");
	
		$single_form_gui->setFormAction($this->ctrl->getFormAction($this));

		return $single_form_gui;
	}

	/**
	* save object
	*
	* @access	public
	*/
	function save()
	{
		global $objDefinition, $ilUser;

		if (!$this->checkPermissionBool("create", "", "file"))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		$single_form_gui = $this->initSingleUploadForm();

		if ($single_form_gui->checkInput())
		{
			$title = $single_form_gui->getInput("title");
			$description = $single_form_gui->getInput("description");
			$upload_file = $single_form_gui->getInput("upload_file");

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
			$fileObj->setTitle($title);
			$fileObj->setDescription($description);
			$fileObj->setFileName($upload_file["name"]);
			//$fileObj->setFileType($upload_file["type"]);
			include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
			$fileObj->setFileType(ilMimeTypeUtil::getMimeType(
				"", $upload_file["name"], $upload_file["type"]));
			$fileObj->setFileSize($upload_file["size"]);
			$this->object_id = $fileObj->create();

			$this->putObjectInTree($fileObj, $this->parent_id);

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

			if ($this->ctrl->getCmd() == "saveAndMeta")
			{
				$target = $this->ctrl->getLinkTargetByClass(array("ilobjfilegui", "ilmdeditorgui"), "listSection", "", false, false);
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
			$single_form_gui->setValuesByPost();
			$this->tpl->setContent($single_form_gui->getHTML());
		}
	}

	/**
	* save object
	*
	* @access	public
	*/
	function saveAndMeta()
	{
		$this->save();
	}

	/**
	* FORM: Init zip upload form.
	*/
	public function initZipUploadForm($a_mode = "create")
	{
		global $lng;
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$zip_form_gui = new ilPropertyFormGUI();
		$zip_form_gui->setMultipart(true);
				
		// File
		$in_file = new ilFileInputGUI($lng->txt("file"), "zip_file");
		$in_file->setRequired(true);
		$in_file->setSuffixes(array("zip"));
		$zip_form_gui->addItem($in_file);

		// Take over structure
		$in_str = new ilCheckboxInputGUI($this->lng->txt("take_over_structure"), "adopt_structure");
		$in_str->setInfo($this->lng->txt("take_over_structure_info"));
		$zip_form_gui->addItem($in_str);
		
		$zip_form_gui->addCommandButton("saveUnzip", $this->lng->txt($this->type."_add"));
		$zip_form_gui->addCommandButton("cancel", $lng->txt("cancel"));
		
		$zip_form_gui->setTableWidth("600px");
		$zip_form_gui->setTarget($this->getTargetFrame("save"));
		$zip_form_gui->setTitle($this->lng->txt("header_zip"));
		$zip_form_gui->setTitleIcon(ilUtil::getImagePath('icon_file.gif'), $this->lng->txt('obj_file'));
		
		$this->ctrl->setParameter($this, "new_type", "file");
		
		$zip_form_gui->setFormAction($this->ctrl->getFormAction($this));

		return $zip_form_gui;
	}

	/**
	* saveUnzip object
	*
	* @access	public
	*/
	function saveUnzip()
	{
		$zip_form_gui = $this->initZipUploadForm();

		if ($this->checkPermissionBool("create", "", "file"))
		{
			if ($zip_form_gui->checkInput())
			{
				$zip_file = $zip_form_gui->getInput("zip_file");
				$adopt_structure = $zip_form_gui->getInput("adopt_structure");

				include_once ("Services/Utilities/classes/class.ilFileUtils.php");

				// Create unzip-directory
				$newDir = ilUtil::ilTempnam();
				ilUtil::makeDir($newDir);

				// Check if permission is granted for creation of object, if necessary
				$type = ilObject::_lookupType((int)$this->parent_id, true);
				if($type == 'cat' or $type == 'root')
				{
					$permission = $this->checkPermissionBool("create", "", "cat");
					$containerType = "Category";
				}
				else {
					$permission = $this->checkPermissionBool("create", "", "fold");
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
						$this->parent_id,
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
				$zip_form_gui->setValuesByPost();
				$this->tpl->setContent($zip_form_gui->getHTML());
			}
		}
		else
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}
	}

	/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	function update()
	{
		global $ilTabs;
		
		$form = $this->initPropertiesForm();
		if(!$form->checkInput())
		{
			$ilTabs->activateTab("settings");
			$form->setValuesByPost();
			$this->tpl->setContent($form->getHTML());
			return false;	
		}

		$data = $form->getInput('file');		

		// delete trailing '/' in filename
		while (substr($data["name"],-1) == '/')
		{
			$data["name"] = substr($data["name"],0,-1);
		}
		
		$filename = empty($data["name"]) ? $this->object->getFileName() : $data["name"];
		$title = $form->getInput('title');
		if(strlen(trim($title)) == 0)
		{
			$title = $filename;
		}
		else
		{
			$title = $this->object->checkFileExtension($filename,$title);
		}
		$this->object->setTitle($title);

		if (!empty($data["name"]))
		{
			switch($form->getInput('replace'))
			{
				case 1:
					$this->object->deleteVersions();
					$this->object->clearDataDirectory();
					$this->object->replaceFile($data['tmp_name'],$data['name']);
					break;
				case 0:
					$this->object->addFileVersion($data['tmp_name'],$data['name']);
					break;
			}
			$this->object->setFileName($data['name']);
			include_once("./Services/Utilities/classes/class.ilMimeTypeUtil.php");
			$this->object->setFileType(ilMimeTypeUtil::getMimeType(
				"", $data["name"], $data["type"]));
			$this->object->setFileSize($data['size']);
		}
		$this->object->setDescription($form->getInput('description'));
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
		ilUtil::redirect($this->ctrl->getLinkTarget($this,'edit','',false,false));
	}
	
	/**
	* edit object
	*
	* @access	public
	*/
	function edit()
	{
		global $ilTabs, $ilErr;

		if (!$this->checkPermissionBool("write"))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"));
		}

		$ilTabs->activateTab("settings");

		$form = $this->initPropertiesForm();

		$val = array();
		$val['title'] = $this->object->getTitle();
		$val['description'] = $this->object->getLongDescription();
		$form->setValuesByArray($val);
		
		$this->tpl->setContent($form->getHTML());
		return true;
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	protected function initPropertiesForm()
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');

		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this),'update');
		$form->setTitle($this->lng->txt('file_edit'));
		$form->addCommandButton('update',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
			
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setValue($this->object->getTitle());
		$title->setInfo($this->lng->txt("if_no_title_then_filename"));
		$form->addItem($title);
		
		$file = new ilFileInputGUI($this->lng->txt('obj_file'),'file');
		$file->setRequired(false);
//		$file->enableFileNameSelection('title');
		$form->addItem($file);
		
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
		$form->addItem($desc);

		return $form;
	}
	
	function sendFile()
	{
		global $ilUser, $ilCtrl;
		
		if(ANONYMOUS_USER_ID == $ilUser->getId() && isset($_GET['transaction']) )
		{
			$this->object->sendFile($_GET["hist_id"]);
		}

		if ($this->checkPermissionBool("read"))
		{
			// BEGIN ChangeEvent: Record read event.
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			if (ilChangeEvent::_isActive())
			{
				global $ilUser;
				// Record read event and catchup with write events
				ilChangeEvent::_recordReadEvent($this->object->getType(), $this->object->getRefId(),
					$this->object->getId(), $ilUser->getId());
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
	function versions()
	{
		global $ilTabs;
		
		$ilTabs->activateTab("id_versions");

		if (!$this->checkPermissionBool("write"))
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}

		require_once("classes/class.ilHistoryGUI.php");
		
		$hist_gui =& new ilHistoryGUI($this->object->getId());
		
		// not nice, should be changed, if ilCtrl handling
		// has been introduced to administration
		$hist_html = $hist_gui->getVersionsTable(
			array("ref_id" => $this->node_id, "cmd" => "versions",
			"cmdClass" =>$_GET["cmdClass"], "cmdNode" =>$_GET["cmdNode"]));
		
		$this->tpl->setVariable("ADM_CONTENT", $hist_html);
	}
	
	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilTabs, $ilErr;
		
		$ilTabs->activateTab("id_info");

		if (!$this->checkPermissionBool("visible"))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"));
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		if ($this->checkPermissionBool("read", "sendfile"))
		{
			$info->addButton($this->lng->txt("file_read"), $this->ctrl->getLinkTarget($this, "sendfile"));
		}
		
		$info->enablePrivateNotes();
		
		if ($this->checkPermissionBool("read"))
		{
			$info->enableNews();
		}

		// no news editing for files, just notifications
		$info->enableNewsEditing(false);
		if ($this->checkPermissionBool("write"))
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
			ilFormat::formatSize(ilObjFile::_lookupFileSize($this->object->getId()),'long'));
		$info->addProperty($this->lng->txt("version"),
			$this->object->getVersion());

		// forward the command
	    $this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
	    $this->ctrl->forwardCommand($info);
	}


	// get tabs
	function setTabs()
	{
		global $ilTabs, $lng;

		$this->ctrl->setParameter($this,"ref_id",$this->node_id);

		if ($this->checkPermissionBool("visible"))
		{
			$ilTabs->addTab("id_info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass(array("ilobjfilegui", "ilinfoscreengui"), "showSummary"));
		}

		if ($this->checkPermissionBool("write"))
		{
			$ilTabs->addTab("settings",
				$lng->txt("edit"),
				$this->ctrl->getLinkTarget($this, "edit"));
		}

		if ($this->checkPermissionBool("write"))
		{
			$ilTabs->addTab("id_versions",
				$lng->txt("versions"),
				$this->ctrl->getLinkTarget($this, "versions"));
		}

		// meta data
		if ($this->checkPermissionBool("write"))
		{
			$ilTabs->addTab("id_meta",
				$lng->txt("meta_data"),
				$this->ctrl->getLinkTargetByClass(array('ilobjfilegui','ilmdeditorgui'),'listSection'));
		}

		// export
		if ($this->checkPermissionBool("write"))
		{
			$ilTabs->addTab("export",
				$lng->txt("export"),
				$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}

		// will add permission tab if needed
		parent::setTabs();
	}
	
	function _goto($a_target)
	{
		global $ilErr, $lng, $ilAccess;

		// static method, no workspace support yet

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
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""), "", $this->node_id);
		}
	}

} // END class.ilObjFileGUI
?>