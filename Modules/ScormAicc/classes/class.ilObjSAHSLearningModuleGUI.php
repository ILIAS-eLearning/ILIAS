<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "classes/class.ilObjectGUI.php";
require_once("classes/class.ilFileSystemGUI.php");
require_once("classes/class.ilTabsGUI.php");

/**
* SCORM/AICC/HACP Learning Modules
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjSAHSLearningModuleGUI: ilFileSystemGUI, ilMDEditorGUI, ilPermissionGUI, ilInfoScreenGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjSAHSLearningModuleGUI: ilLicenseGUI, ilCommonActionDispatcherGUI
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
		global $ilAccess, $ilTabs;
		
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
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case "ilfilesystemgui":
				$this->fs_gui =& new ilFileSystemGUI($this->object->getDataDirectory());
				$this->fs_gui->setTableId("sahsfs".$this->object->getId());
				$ret =& $this->ctrl->forwardCommand($this->fs_gui);
				break;

			case "ilcertificategui":
				include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
				include_once "./Modules/ScormAicc/classes/class.ilSCORMCertificateAdapter.php";
				$output_gui = new ilCertificateGUI(new ilSCORMCertificateAdapter($this->object));
				$ret =& $this->ctrl->forwardCommand($output_gui);
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,$this->object->getRefId());
				$this->ctrl->forwardCommand($new_gui);

				break;

			case 'illicensegui':
				include_once("./Services/License/classes/class.ilLicenseGUI.php");
				$license_gui =& new ilLicenseGUI($this);
				$ret =& $this->ctrl->forwardCommand($license_gui);
				break;

			case "ilinfoscreengui":
				include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");

				$info = new ilInfoScreenGUI($this);
				$info->enablePrivateNotes();
				$info->enableLearningProgress();
				
				// add read / back button
				if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
				{
					if (!$this->object->getEditable())
					{
						$info->addButton($this->lng->txt("view"),
							"ilias.php?baseClass=ilSAHSPresentationGUI&amp;ref_id=".$this->object->getRefID(),
							' target="ilContObj'.$this->object->getId().'" ');
					}
				}

				$info->enableNews();
				if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
				{
					$info->enableNewsEditing();
					$news_set = new ilSetting("news");
					$enable_internal_rss = $news_set->get("enable_rss_for_internal");
					if ($enable_internal_rss)
					{
						$info->setBlockProperty("news", "settings", true);
					}
				}
				// show standard meta data section
				$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
				// forward the command
				$this->ctrl->forwardCommand($info);
				break;
				
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case "ilobjstylesheetgui":
				//$this->addLocations();
				$this->ctrl->setReturn($this, "properties");
				$ilTabs->clearTargets();
				$style_gui =& new ilObjStyleSheetGUI("", $this->object->getStyleSheetId(), false, false);
				$style_gui->omitLocator();
				if ($cmd == "create" || $_GET["new_type"]=="sty")
				{
					$style_gui->setCreationMode(true);
				}
				//$ret =& $style_gui->executeCommand();

				if ($cmd == "confirmedDelete")
				{
					$this->object->setStyleSheetId(0);
					$this->object->update();
				}
				$ret =& $this->ctrl->forwardCommand($style_gui);
				if ($cmd == "save" || $cmd == "copyStyle" || $cmd == "importStyle")
				{
					$style_id = $ret;
					$this->object->setStyleSheetId($style_id);
					$this->object->update();
					$this->ctrl->redirectByClass("ilobjstylesheetgui", "edit");
				}
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

	////
	//// CREATION
	////

	/**
	* no manual SCORM creation, only import at the time
	*/
	function  initCreationForms($a_new_type)
	{
		$forms = array();

		$this->initUploadForm();
		$forms[self::CFORM_IMPORT] = $this->form;

		$this->initCreationForm();
		$forms[self::CFORM_NEW] = $this->form;
	
		return $forms;
	}

	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initCreationForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// title
		$ti = new ilTextInputGUI($this->lng->txt("title"), "title");
		$ti->setMaxLength(128);
		$ti->setSize(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// text area
		$ta = new ilTextAreaInputGUI($this->lng->txt("description"), "desc");
		$ta->setCols(40);
		$ta->setRows(2);
		$this->form->addItem($ta);
		
	
		$this->form->addCommandButton("save", $lng->txt("create"));
		$this->form->addCommandButton("cancel", $lng->txt("cancel"));
	                
		$this->form->setTitle($lng->txt("scorm_new"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->setTarget(ilFrameTargetInfo::_getFrame("MainContent"));
	}
	
	/**
	* Init upload form.
	*/
	public function initUploadForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// type selection
		$options = array(
			"scorm2004" => $lng->txt("lm_type_scorm2004"),
			"scorm" => $lng->txt("lm_type_scorm"),
			"aicc" => $lng->txt("lm_type_aicc"),
			"hacp" => $lng->txt("lm_type_hacp")
			);
		$si = new ilSelectInputGUI($this->lng->txt("type"), "sub_type");
		$si->setOptions($options);
		$this->form->addItem($si);
		
		// input file
		$fi = new ilFileInputGUI($this->lng->txt("select_file"), "scormfile");
		$fi->setRequired(true);
		$this->form->addItem($fi);
		
		// todo "uploaded file"
		// todo wysiwyg editor removement
		
		include_once 'Services/FileSystemStorage/classes/class.ilUploadFiles.php';
		if (ilUploadFiles::_getUploadDirectory())
		{
			$options = array();
			$fi->setRequired(false);
			$files = ilUploadFiles::_getUploadFiles();
			$options[""] = $this->lng->txt("cont_select_from_upload_dir");
			foreach($files as $file)
			{
				$file = htmlspecialchars($file, ENT_QUOTES, "utf-8");
				$options[$file] = $file;
			}
			// 
			$si = new ilSelectInputGUI($this->lng->txt("cont_uploaded_file"), "uploaded_file");
			$si->setOptions($options);
			$this->form->addItem($si);
		}

		
		// validate file
		$cb = new ilCheckboxInputGUI($this->lng->txt("cont_validate_file"), "validate");
		$cb->setValue("y");
		$cb->setChecked(true);
		$this->form->addItem($cb);

		// import for editing
		$cb = new ilCheckboxInputGUI($this->lng->txt("sahs_authoring_mode"), "editable");
		$cb->setValue("y");
		$cb->setInfo($this->lng->txt("sahs_authoring_mode_info"));
		$this->form->addItem($cb);
		
		// 
		$radg = new ilRadioGroupInputGUI($lng->txt("sahs_sequencing"), "import_sequencing");
		$radg->setValue(0);
			$op1 = new ilRadioOption($lng->txt("sahs_std_sequencing"), 0,$lng->txt("sahs_std_sequencing_info"));
			$radg->addOption($op1);
			$op1 = new ilRadioOption($lng->txt("sahs_import_sequencing"), 1,$lng->txt("sahs_import_sequencing_info"));
			$radg->addOption($op1);
		$cb->addSubItem($radg);
		

		$this->form->addCommandButton("upload", $lng->txt("import"));
		$this->form->addCommandButton("cancel", $lng->txt("cancel"));
	                
		$this->form->setTitle($lng->txt("import_sahs"));
		$this->form->setFormAction($ilCtrl->getFormAction($this));
		$this->form->setTarget(ilFrameTargetInfo::_getFrame("MainContent"));
	}
	
	/**
	* Cancel action and go back to previous page
	*/
	function cancelObject($in_rep = false)
	{
		ilUtil::redirect("repository.php?cmd=frameset&ref_id=".$_GET["ref_id"]);
		//$this->ctrl->redirectByClass("ilrepositorygui", "frameset");
	}

	/**
	* display dialogue for importing SCORM package
	*
	* @access	public
	*/
/*		DEPRECATED
	function importObject()
	{
				
		// display import form
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.slm_import.html", "Modules/ScormAicc");
		
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_slm.gif'));
		$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_sahs"));
		
		$this->ctrl->setParameter($this, "new_type", "sahs");
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));

		$this->tpl->setVariable("BTN_NAME", "save");
		$this->tpl->setVariable("TARGET", ' target="'.ilFrameTargetInfo::_getFrame("MainContent").'" ');

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
*/

	/**
	* display status information or report errors messages
	* in case of error
	*
	* @access	public
	*/
	function uploadObject()
	{
		global $_FILES, $rbacsystem;

		include_once 'Services/FileSystemStorage/classes/class.ilUploadFiles.php';

		// check create permission
		if (!$rbacsystem->checkAccess("create", $_GET["ref_id"], "sahs"))
		{
			$this->ilias->raiseError($this->lng->txt("no_create_permission"), $this->ilias->error_obj->WARNING);
		}
		elseif ($_FILES["scormfile"]["name"])
		{
			// check if file was uploaded
			$source = $_FILES["scormfile"]["tmp_name"];
			if (($source == 'none') || (!$source))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_file"),$this->ilias->error_obj->MESSAGE);
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
		}
		elseif ($_POST["uploaded_file"])
		{
			// check if the file is in the upload directory and readable
			if (!ilUploadFiles::_checkUploadFile($_POST["uploaded_file"]))
			{
				$this->ilias->raiseError($this->lng->txt("upload_error_file_not_found"),$this->ilias->error_obj->MESSAGE);
			}

			$file = pathinfo($_POST["uploaded_file"]);
		}
		else
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_file"),$this->ilias->error_obj->MESSAGE);
		}
		
		$name = substr($file["basename"], 0, strlen($file["basename"]) - strlen($file["extension"]) - 1);
		if ($name == "")
		{
			$name = $this->lng->txt("no_title");
		}

		// create and insert object in objecttree
		switch ($_POST["sub_type"])
		{
			
			case "scorm2004":
				include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
				$newObj = new ilObjSCORM2004LearningModule();
				$newObj->setEditable($_POST["editable"]=='y');
				$newObj->setImportSequencing($_POST["import_sequencing"]);
				$newObj->setSequencingExpertMode($_POST["import_sequencing"]);
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
		$newObj->create(true);
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());

		// create data directory, copy file to directory
		$newObj->createDataDirectory();

		if ($_FILES["scormfile"]["name"])
		{
			// copy uploaded file to data directory
			$file_path = $newObj->getDataDirectory()."/".$_FILES["scormfile"]["name"];
		
			ilUtil::moveUploadedFile($_FILES["scormfile"]["tmp_name"],
					$_FILES["scormfile"]["name"], $file_path);
		}
		else
		{
			// copy uploaded file to data directory
			$file_path = $newObj->getDataDirectory()."/". $_POST["uploaded_file"];

			ilUploadFiles::_copyUploadFile($_POST["uploaded_file"], $file_path);
		}

		ilUtil::unzip($file_path);
		ilUtil::renameExecutables($newObj->getDataDirectory());

		$title = $newObj->readObject();
		if ($title != "")
		{
			ilObject::_writeTitle($newObj->getId(), $title);
			/*$md = new ilMD($newObj->getId(),0, $newObj->getType());
			if(is_object($md_gen = $md->getGeneral()))
			{
				$md_gen->setTitle($title);
				$md_gen->update();
			}*/
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
		if (trim($_POST["title"]) == "")
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_title"),$this->ilias->error_obj->MESSAGE);
		}
		
		include_once("./Modules/Scorm2004/classes/class.ilObjSCORM2004LearningModule.php");
		$newObj = new ilObjSCORM2004LearningModule();
		$newObj->setTitle(ilUtil::stripSlashes($_POST["title"]));
		$newObj->setSubType("scorm2004");
		$newObj->setEditable(true);
		$newObj->setDescription(ilUtil::stripSlashes($_POST["desc"]));
		$newObj->create();
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->notify("new",$_GET["ref_id"],$_GET["parent_non_rbac_id"],$_GET["ref_id"],$newObj->getRefId());
		$newObj->createDataDirectory();
		$newObj->createScorm2004Tree();
		ilUtil::sendInfo( $this->lng->txt($newObj->getType()."_added"), true);
		ilUtil::redirect("ilias.php?baseClass=ilSAHSEditGUI&ref_id=".$newObj->getRefId());
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

		$this->tpl->getStandardTemplate();
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
		$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lm_b.gif"));
		$this->tpl->setTitle($this->object->getTitle());
		$this->getTabs($this->tabs_gui);
	}

	/**
	* Shows the certificate editor
	*/
	function certificate()
	{
		include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
		include_once "./Modules/ScormAicc/classes/class.ilSCORMCertificateAdapter.php";
		$output_gui = new ilCertificateGUI(new ilSCORMCertificateAdapter($this->object));
		$output_gui->certificateEditor();
	}
	
	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $ilUser, $ilCtrl;
		
		if ($this->ctrl->getCmd() == "delete")
		{
			return;
		}

		// file system gui tabs
		// properties
		$ilCtrl->setParameterByClass("ilfilesystemgui", "resetoffset", 1);
		$tabs_gui->addTarget("cont_list_files",
			$this->ctrl->getLinkTargetByClass("ilfilesystemgui", "listFiles"), "",
			"ilfilesystemgui");
		$ilCtrl->setParameterByClass("ilfilesystemgui", "resetoffset", "");

		// info screen
		$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui")
			? true
			: false;
		$tabs_gui->addTarget("info_short",
			$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"), "",
			"ilinfoscreengui", "", $force_active);
			
		// properties
		$tabs_gui->addTarget("settings",
			$this->ctrl->getLinkTarget($this, "properties"), "properties",
			get_class($this));
			
		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}

		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		if($privacy->enabledSahsProtocolData())
		{
			// tracking data
			$tabs_gui->addTarget("cont_tracking_data",
			$this->ctrl->getLinkTarget($this, "showTrackingItems"), "showTrackingItems",
			get_class($this));
		}

		// create and insert object in objecttree
		switch ($this->object->getSubType())
		{
			
			case "scorm2004":
			case "scorm":
				// certificate
				$tabs_gui->addTarget("certificate",
					$this->ctrl->getLinkTarget($this, "certificate"),
					array("certificate", "certificateEditor", "certificateRemoveBackground", "certificateSave",
						"certificatePreview", "certificateDelete", "certificateUpload", "certificateImport")
				);
				break;
		}
		
		include_once("Services/License/classes/class.ilLicenseAccess.php");
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId())
		and ilLicenseAccess::_isEnabled())
		{
			$tabs_gui->addTarget("license",
				$this->ctrl->getLinkTargetByClass('illicensegui', ''),
			"", "illicensegui");
		}
		
		// edit meta
		$tabs_gui->addTarget("meta_data",
			 $this->ctrl->getLinkTargetByClass('ilmdeditorgui',''),
			 "", "ilmdeditorgui");

		// perm
		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	* goto target course
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		$parts = explode("_", $a_target);

		if ($ilAccess->checkAccess("write", "", $parts[0]))
		{
			$_GET["cmd"] = "";
			$_GET["baseClass"] = "ilSAHSEditGUI";
			$_GET["ref_id"] = $parts[0];
			$_GET["obj_id"] = $parts[1];
			include("ilias.php");
			exit;
		}
		if ($ilAccess->checkAccess("visible", "", $parts[0]))
		{
			$_GET["cmd"] = "infoScreen";
			$_GET["baseClass"] = "ilSAHSPresentationGUI";
			$_GET["ref_id"] = $parts[0];
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
					ilObject::_lookupTitle(ilObject::_lookupObjId($parts[0]))), true);
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
