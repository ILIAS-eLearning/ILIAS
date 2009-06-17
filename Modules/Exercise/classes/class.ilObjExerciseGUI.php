<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "classes/class.ilObjectGUI.php";

/**
* Class ilObjExerciseGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @ilCtrl_Calls ilObjExerciseGUI: ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI, ilRepositorySearchGUI
* 
* @ingroup ModulesExercise
*/
class ilObjExerciseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjExerciseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $lng;
		
		$this->type = "exc";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		$lng->loadLanguageModule("exercise");
		$this->ctrl->saveParameter($this, array("sort_by", "sort_order", "offset"));
	}
  
	function getFiles()
	{
		return $this->files ? $this->files : array();
	}
	
	function setFiles($a_files)
	{
		$this->files = $a_files;
	}

	function createObject($a_reload = false)
	{
		global $tpl;
		
		$this->getTemplateFile("edit", "exc");
		
		if (!$a_reload)
		{
			$this->initPropertiesForm("create");
		}
		$this->tpl->setVariable("EDIT_FORM", $this->form_gui->getHtml());
		
		$this->fillCloneTemplate('DUPLICATE','exc');
return;
		parent::createObject();

		$this->tpl->setVariable("INSTRUCTION",
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["instruction"], true));

		// SET ADDITIONAL TEMPLATE VARIABLES
		$this->tpl->setVariable("TXT_INSTRUCTION",$this->lng->txt("exc_instruction"));
		$this->tpl->setVariable("TXT_EDIT_UNTIL",$this->lng->txt("exc_edit_until"));
		$this->tpl->setVariable("TXT_HOUR",$this->lng->txt("time_h"));
		$this->tpl->setVariable("TXT_DAY",$this->lng->txt("time_d"));
		$this->tpl->setVariable("SELECT_HOUR",$this->__getDateSelect("hour",(int) date("H",time())));
		$this->tpl->setVariable("SELECT_MINUTES",$this->__getDateSelect("minutes",(int) date("i",time())));
		$this->tpl->setVariable("SELECT_DAY",$this->__getDateSelect("day",(int) date("d",time())));
		$this->tpl->setVariable("SELECT_MONTH",$this->__getDateSelect("month",(int) date("m",time())));
		$this->tpl->setVariable("SELECT_YEAR",$this->__getDateSelect("year",1));
		$this->tpl->setVariable("CMD_CANCEL", "cancel");
	
		$this->fillCloneTemplate('DUPLICATE','exc');
	
		return true;
	}
  
	function viewObject()
	{
		$this->infoScreenObject();
		return;
		global $rbacsystem,$ilUser;
	
		include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
		ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),'exc');
	
	
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return;
		}

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->getTemplateFile("view", "exc");
	
		$this->tpl->setVariable("FORM_DOWNLOAD_ACTION", $this->ctrl->getFormAction($this, "downloadFile"));
		$this->tpl->setVariable("TITLE_TXT",$this->lng->txt("title"));
		$this->tpl->setVariable("TITLE",$this->object->getTitle());
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("exc_details"));
		$this->tpl->setVariable("DESCRIPTION_TXT",$this->lng->txt("description"));
		$this->tpl->setVariable("DESCRIPTION",$this->object->getDescription());
		$this->tpl->setVariable("INSTRUCTION_TXT",$this->lng->txt("exc_instruction"));
		$this->tpl->setVariable("INSTRUCTION",nl2br($this->object->getInstruction()));
		$this->tpl->setVariable("EDIT_UNTIL_TXT",$this->lng->txt("exc_edit_until"));
		$this->tpl->setVariable("EDIT_UNTIL",date("H:i, d.m.Y",$this->object->getTimestamp()));
		$this->tpl->setVariable("TIME_TO_SEND_TXT",$this->lng->txt("exc_time_to_send"));
	
		if ($this->object->getTimestamp()-time() <= 0)
		{
			$this->tpl->setCurrentBlock("TIME_REACHED");
			$this->tpl->setVariable("TIME_TO_SEND",$this->lng->txt("exc_time_over_short"));
			$this->tpl->parseCurrentBlock();		
		}
		else {
			$timediff = ilUtil::int2array($this->object->getTimestamp()-time(),null);
			$timestr = ilUtil::timearray2string($timediff);
			$this->tpl->setCurrentBlock("TIME_NOT_REACHED");
			$this->tpl->setVariable("TIME_TO_SEND",$timestr);
			$this->tpl->parseCurrentBlock();
		}
	
		$anyfiles = false;
		foreach($this->object->getFiles() as $file)
		{
			$this->tpl->setCurrentBlock("FILES_ROW");
			$this->tpl->setVariable("FILE_DATA",$file["name"]);
			$this->tpl->setVariable("FILE_CHECK",ilUtil::formRadioButton(0,"file",urlencode($file["name"])));
			$this->tpl->parseCurrentBlock();
			$anyfiles = true;
		}
	
		if ($anyfiles)
		{
			$this->tpl->setCurrentBlock("FILES");
			$this->tpl->setVariable("FILES_TXT",$this->lng->txt("exc_files"));
			$this->tpl->setVariable("TXT_DOWNLOAD",$this->lng->txt("download"));
			$this->tpl->setVariable("IMG",ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->parseCurrentBlock();
		}
	
		$this->tpl->setCurrentBlock("perma_link");
		$this->tpl->setVariable("PERMA_LINK", ILIAS_HTTP_PATH.
			"/goto.php?target=".
			$this->object->getType().
			"_".$this->object->getRefId()."&client_id=".CLIENT_ID);
		$this->tpl->setVariable("TXT_PERMA_LINK", $this->lng->txt("perma_link"));
		$this->tpl->setVariable("PERMA_TARGET", "_top");
		$this->tpl->parseCurrentBlock();
	
		return true;
	}
  
	/**
	* Displays a form which allows members to deliver their solutions
	*
	* @access public
	*/
	function deliverObject()
	{
		global $ilUser;
		require_once "./Services/Utilities/classes/class.ilUtil.php";
		
		$this->checkPermission("read");
		
		$this->tabs_gui->setTabActive("exc_your_submission");
		
		if (mktime() > $this->object->getTimestamp())
		{
			ilUtil::sendFailure($this->lng->txt("exercise_time_over"));
		}


		if ($_POST["cmd"]["delete"] && mktime() < $this->object->getTimestamp())
		{
			if (count($_POST["delivered"]))
			{
				$this->object->deleteDeliveredFiles($_POST["delivered"], $ilUser->id);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_delete"));
			}
		}

		if ($_POST["cmd"]["download"])
		{
			if (count($_POST["delivered"]))
			{
				$this->object->members_obj->downloadSelectedFiles($_POST["delivered"]);
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("please_select_a_delivered_file_to_download"));
			}
		}


		if (mktime() > $this->object->getTimestamp())
		{
			$this->getTemplateFile("delivered_files", "exc");
		}
		else
		{
			$this->getTemplateFile("deliver_file", "exc");
		}

		$delivered_files = $this->object->getDeliveredFiles($ilUser->id);
		$color_class = array("tblrow1", "tblrow2");
		$counter = 0;
		foreach ($delivered_files as $index => $file)
		{
			$this->tpl->setCurrentBlock("delivered_row");
			$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
			$this->tpl->setVariable("FILE_ID", $file["returned_id"]);
			$this->tpl->setVariable("DELIVERED_FILE", $file["filetitle"]);
			/*
			preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $file["timestamp14"], $matches);
			$stamp = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:%02d", 
				$matches[1], $matches[2], $matches[3], 
				$matches[4], $matches[5], $matches[6]));
			$date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], $stamp);
			*/
			$date = new ilDateTime($file['timestamp14'],IL_CAL_TIMESTAMP);
			$this->tpl->setVariable("DELIVERED_DATE", ilDatePresentation::formatDate($date));
			$this->tpl->parseCurrentBlock();
			$counter++;
		}
		if (count($delivered_files))
		{
			$this->tpl->setCurrentBlock("footer_content");
			$this->tpl->setVariable("ARROW_SIGN", ilUtil::getImagePath("arrow_downright.gif"));
			$this->tpl->setVariable("BUTTON_DELETE", $this->lng->txt("delete"));
			$this->tpl->setVariable("BUTTON_DOWNLOAD", $this->lng->txt("download"));
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("footer_empty");
			$this->tpl->setVariable("TEXT_NO_DELIVERED_FILES", 
				$this->lng->txt("message_no_delivered_files"));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->tpl->setCurrentBlock("delivered_files");
		$this->tpl->setVariable("DELIVER_FORMACTION", 
			$this->ctrl->getLinkTarget($this, "deliver"));
		$this->tpl->setVariable("TEXT_DATE", $this->lng->txt("date"));
		$this->tpl->setVariable("TEXT_DELIVERED_FILENAME", $this->lng->txt("filename"));
		$this->tpl->setVariable("TEXT_HEADING_DELIVERED_FILES", $this->lng->txt("already_delivered_files"));
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", 
			$this->ctrl->getLinkTarget($this, "deliverFile"));
		$this->tpl->setVariable("BUTTON_DELIVER", $this->lng->txt("upload"));
		$this->tpl->setVariable("TEXT_FILENAME", $this->lng->txt("enter_filename_deliver"));
		$this->tpl->setVariable("TXT_UPLOAD_FILE", $this->lng->txt("file_add"));
		$this->tpl->setVariable("TXT_UPLOAD_ZIPFILE", $this->lng->txt("header_zip"));
		$this->tpl->parseCurrentBlock();
		
	}
  
	function deliverFileObject()
	{
		global $ilUser, $lng;
		
		$this->checkPermission("read");

		$this->tabs_gui->setTabActive("view");
		$this->tabs_gui->setTabActive("exc_your_submission");

		if (!empty($_POST["cmd"][deliverUnzip]) && preg_match("/zip/",$_FILES["deliver"]["type"]) == 1)
		{
			$this->object->processUploadedFile($_FILES["deliver"]["tmp_name"], "deliverFile", false);
			
		}
		else
		{
			if(!$this->object->deliverFile($_FILES["deliver"], $ilUser->id))
			{
				ilUtil::sendFailure($this->lng->txt("exc_upload_error"),true);
			}
		}
		
		
		$this->deliverObject();
	}
  
	function downloadFileObject()
	{
		global $rbacsystem;
		
		$file = ($_POST["file"])
			? $_POST["file"]
			: $_GET["file"];

		$this->checkPermission("read");
		
		if (!isset($file))
		{
			ilUtil::sendFailure($this->lng->txt("exc_select_one_file"),true);
			$this->ctrl->redirect($this, "view");
		}
		$files = $this->object->getFiles();
		$file_exist = false;
	
		foreach($this->object->getFiles() as $lfile)
		{
			if($lfile["name"] == urldecode($file))
			{
				$file_exist = true;
				break;
			}
		}
		if(!$file_exist)
		{
			echo "FILE DOES NOT EXIST";
			exit;
		}
		ilUtil::deliverFile($this->object->file_obj->getAbsolutePath(urldecode($file)),
				urldecode($file));
	
		return true;
	}
  
	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;
	
		$this->initPropertiesForm("create");

		if ($this->form_gui->checkInput())
		{
			// always call parent method first to create an object_data entry & a reference
			$newObj = parent::saveObject();
		
			// setup rolefolder & default local roles if needed (see ilObjForum & ilObjForumGUI for an example)
			//$roles = $newObj->initDefaultRoles();
		
			// put here your object specific stuff	
		
			$newObj->setTitle($this->form_gui->getInput("title"));
			$newObj->setDescription($this->form_gui->getInput("desc"));
			$newObj->setInstruction($this->form_gui->getInput("instruction"));
			$edit_date =
				$this->form_gui->getItemByPostVar("edit_date")->getDate();
			$newObj->setTimestamp($edit_date->get(IL_CAL_UNIX));
			
			$newObj->saveData();
			$newObj->update();
		
			// always send a message
			ilUtil::sendSuccess($this->lng->txt("exc_added"),true);
			ilUtil::redirect("ilias.php?baseClass=ilExerciseHandlerGUI&ref_id=".$newObj->getRefId()."&cmd=edit");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$this->createObject(true);
		}
	}
  
	function editObject()
	{
		global $rbacsystem, $tpl;

		$this->checkPermission("write");

		$this->getTemplateFile("edit", "exc");

		$this->initPropertiesForm("edit");
		$this->getPropertiesValues();

		$this->tpl->setVariable("EDIT_FORM", $this->form_gui->getHtml());

		$this->tpl->setCurrentBlock("FILES");
		$this->tpl->setVariable("TXT_HEADER_FILE",$this->lng->txt("file_add"));
		$this->tpl->setVariable("TXT_FILE",$this->lng->txt("file"));
		$this->tpl->setVariable("TXT_UPLOAD",$this->lng->txt("upload"));
		$this->tpl->setVariable("FORMACTION_FILE", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_HEADER_ZIP", $this->lng->txt("header_zip"));
		$this->tpl->setVariable("CMD_FILE_SUBMIT","uploadFile");
		$this->tpl->parseCurrentBlock();
	}
  
	/**
	* Init properties form.
	*
	* @param        int        $a_mode        "create"/"edit"
	*/
	public function initPropertiesForm($a_mode = "create")
	{
		global $lng, $ilCtrl;

		// init form
		$lng->loadLanguageModule("form");
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form_gui = new ilPropertyFormGUI();
		$this->form_gui->setTableWidth("60%");
		if ($a_mode == "edit")
		{
			$this->form_gui->setTitle($lng->txt("exc_edit_exercise"));
		}
		else
		{
			$this->form_gui->setTitle($lng->txt("exc_new"));
		}
		$this->form_gui->setTitleIcon(ilUtil::getImagePath("icon_exc.gif"));
		$this->form_gui->setFormAction($ilCtrl->getFormAction($this));

		
		// on creation: Type
		if ($a_mode == "create")
		{
			$type_input = new ilHiddenInputGUI("new_type");
			$type_input->setValue("exc");
			$this->form_gui->addItem($type_input);
		}
		
		// Title
		$title_input = new ilTextInputGUI($lng->txt("title"), "title");
		$title_input->setRequired(true);
		$title_input->setMaxLength(128);
		$this->form_gui->addItem($title_input);
		
		// Description
		$desc_input = new ilTextAreaInputGUI($lng->txt("desc"), "desc");
		$this->form_gui->addItem($desc_input);
		
		// Work Instructions
		$desc_input = new ilTextAreaInputGUI($lng->txt("exc_instruction"), "instruction");
		$this->form_gui->addItem($desc_input);
		
		// Edit until...
		$edit_date = new ilDateTimeInputGUI($lng->txt("exc_edit_until"), "edit_date");
		//$dt_prop->setDate(new ilDateTime("2006-12-24 15:44:00", IL_CAL_DATETIME);
		$edit_date->setShowTime(true);
		//$dt_prop->setInfo("Info text for the start date.");
		$this->form_gui->addItem($edit_date);

		// files
		if ($a_mode == "edit")
		{
			if(count($files = $this->object->getFiles()))
			{
				// Files section header
				$files_head = new ilFormSectionHeaderGUI();
				$files_head->setTitle($lng->txt("files"));
				$this->form_gui->addItem($files_head);

				foreach($files as $file)
				{
					$i++;
					$file_cb = new ilCheckboxInputGUI($this->lng->txt("exc_ask_delete"),
						"delete_file_".$i);
					$file_cb->setOptionTitle($file["name"]);
					$file_cb->setValue($file["name"]);
					$this->form_gui->addItem($file_cb);
				}
			}
		}

		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form_gui->addCommandButton("save", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancel", $lng->txt("cancel"));
		}
		else
		{
			$this->form_gui->addCommandButton("update", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelEdit", $lng->txt("cancel"));
		}
	}
	
	/**
	* Get values for properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["desc"] = $this->object->getLongDescription();
		$values["instruction"] = $this->object->getInstruction();
		$this->form_gui->setValuesByArray($values);
		
//echo "-".$this->object->getTimestamp()."-";
		$edit_date = new ilDateTime($this->object->getTimestamp(), IL_CAL_UNIX);
		$ed_item = $this->form_gui->getItemByPostVar("edit_date");
		$ed_item->setDate($edit_date);
	}
		
	/**
	* Update exercise from values from form
	*/
	function updateObject()
	{
		global $rbacsystem, $tpl;
	
		$this->checkPermission("write");
		
		$this->initPropertiesForm("edit");
		if ($this->form_gui->checkInput())
		{
			$this->object->setTitle($this->form_gui->getInput("title"));
			$this->object->setDescription($this->form_gui->getInput("desc"));
			$this->object->setInstruction($this->form_gui->getInput("instruction"));
			$edit_date =
				$this->form_gui->getItemByPostVar("edit_date")->getDate();
			$this->object->setTimestamp($edit_date->get(IL_CAL_UNIX));
			
			// delete files
			$del_files = array();
			foreach ($_POST as $k => $v)
			{
				if (substr($k, 0, 12) == "delete_file_" && $v != "")
				{
					$del_files[] = $v;
				}
			}

			$this->object->deleteFiles($del_files);
			
			$this->object->update();
			
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
			$this->ctrl->redirect($this, "edit");
		}
		else
		{
			$this->form_gui->setValuesByPost();
			$tpl->setContent($this->form_gui->getHTML());
		}
		
return;
	
		$this->object->setInstruction(ilUtil::stripSlashes($_POST["Fobject"]["instruction"]));
		$this->object->setDate($_POST["d_hour"],$_POST["d_minutes"],$_POST["d_day"],
				$_POST["d_month"],$_POST["d_year"]);
		if($_POST["delete_file"])
		{
			$this->object->deleteFiles($_POST["delete_file"]);
		}
			
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->update = $this->object->update();
	
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
	
		$this->ctrl->redirect($this, "edit");
	}
  
	function cancelEditObject()
	{
		$this->ctrl->redirect($this, "view");
	}

	function uploadZipObject()
	{
		global $rbacsystem;
		
		$this->checkPermission("write");

		if(!$this->object->addUploadedFile($_FILES["zipfile"], true))
		{
			ilUtil::sendFailure($this->lng->txt("exc_upload_error"),true);
		}
		$this->ctrl->redirect($this, "edit");
	
	}

	function uploadFileObject()
	{
		global $rbacsystem;

		$this->checkPermission("write");

		if(!$this->object->addUploadedFile($_FILES["file"]))
		{
			ilUtil::sendFailure($this->lng->txt("exc_upload_error"),true);
		}
		$this->ctrl->redirect($this, "edit");
	}
	
	
	/**
	* update data of members table
	*/
	function updateMembersObject()
	{
		global $rbacsystem;
	
		$this->checkPermission("write");
	
		if ($_POST["downloadReturned"])
		{
			$this->object->members_obj->deliverReturnedFiles(key($_POST["downloadReturned"]));
			exit;
		}
		else
		{
			switch($_POST["action"])
			{
				case "save_status":
					$this->saveStatusObject();
					break;
					
				case "send_member":
					$this->sendMembersObject();
					break;
				
				case "redirectFeedbackMail":
					$this->redirectFeedbackMailObject();
					break;
					
				case "delete_member":
					$this->deassignMembersObject();
					break;
			}
		}
	}

	/**
	* Download submitted files of user.
	*/
	function downloadReturnedObject()
	{
		$this->checkPermission("write");

		if (!$this->object->members_obj->deliverReturnedFiles($_GET["member_id"]))
		{
			$this->ctrl->redirect($this, "members");
		}
		exit;
	}

	/**
	* Download newly submitted files of user.
	*/
	function downloadNewReturnedObject()
	{
		$this->checkPermission("write");
		
		if (!$this->object->members_obj->deliverReturnedFiles($_GET["member_id"], true))
		{
			$this->ctrl->redirect($this, "members");
		}
		exit;
	}

	function addMembersObject()
	{
		global $ilAccess,$ilErr;

		$this->checkPermission("write");
		if(!count($_POST['user']))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			return false;
		}

		if(!$this->object->members_obj->assignMembers($_POST["user"]))
		{
			ilUtil::sendFailure($this->lng->txt("exc_members_already_assigned"));
			return false;
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("exc_members_assigned"),true);
		}
		$this->ctrl->redirect($this, "members");
		return false;
	}


	function membersObject()
	{
		global $rbacsystem, $tree, $tpl;

		include_once 'Services/Tracking/classes/class.ilLPMarks.php';
	
		$this->checkPermission("write");
		
		//add template for buttons	
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
	
		// add member button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("add_member"));
		$this->tpl->parseCurrentBlock();
		
		// add course members button, in case the exercise is inside a course
		$parent_id = $tree->getParentId($_GET["ref_id"]);
		$parent_obj_id = ilObject::_lookupObjId($parent_id);
		$type = ilObject::_lookupType($parent_obj_id);
		while ($parent_id != 1 && $type != "crs")
		{
			$parent_id = $tree->getParentId($parent_id);
			$parent_obj_id = ilObject::_lookupObjId($parent_id);
			$type = ilObject::_lookupType($parent_obj_id);
		}

		if ($type == "crs") 
		{
			$search_for_role = "il_crs_member_" . $parent_id;
			$this->tpl->setCurrentBlock("btn_cell");
		
			$_SESSION['rep_query']['role']['title'] = $search_for_role;
			$_SESSION['rep_search_type'] = 'role';
			
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','performSearch'));
			$this->lng->loadLanguageModule("exercise");
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt("exc_crs_add_members"));
			$this->tpl->parseCurrentBlock();
		}
		
include_once("./Modules/Exercise/classes/class.ilExerciseMemberTableGUI.php");
$exc_tab = new ilExerciseMemberTableGUI($this, "members", $this->object);
$tpl->setContent($exc_tab->getHTML());
return;		
		
		$this->getTemplateFile("members","exc");
	
		if(!count($this->object->members_obj->getMembers()))
		{
			ilUtil::sendInfo($this->lng->txt("exc_no_members_assigned"));
		}
		else	
		{
			$counter = 0;
			$members = $this->object->getMemberListData();

			include_once("./Services/Table/classes/class.ilTableGUI.php");
			$tbl = new ilTableGUI();
			$this->tpl->addBlockfile("MEMBER_TABLE", "term_table", "tpl.table.html");
			$this->tpl->addBlockfile("TBL_CONTENT", "member_row", "tpl.exc_members_row.html", "Modules/Exercise");
			
			$sent_col = $this->object->_lookupAnyExerciseSent($this->object->getId());
			
			// SET FORMAACTION
			$this->tpl->setCurrentBlock("tbl_form_header");
			
			$this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this, "updateMembers"));
			$this->tpl->parseCurrentBlock();
	
			// SET FOOTER BUTTONS
			$this->tpl->setCurrentBlock("tbl_action_row");

			$this->tpl->setVariable("COLUMN_COUNTS",6);
			$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

			$actions = array("save_status"		=> $this->lng->txt("exc_save_changes"),
				"redirectFeedbackMail"	=> $this->lng->txt("exc_send_mail"),
				"send_member"		=> $this->lng->txt("exc_send_exercise"),
				"delete_member"	=> $this->lng->txt("exc_deassign_members"));

			$this->tpl->setCurrentBlock("tbl_action_select");
			$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
			$this->tpl->setVariable("BTN_NAME","execute");
			$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
			$this->tpl->parseCurrentBlock();
	
			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("COLUMN_COUNTS",10);
			$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);
			$this->tpl->parseCurrentBlock();

			// title & header columns
			if ($sent_col)
			{
				$sent_str = $this->lng->txt("exc_exercise_sent");
			}
			else
			{
				$sent_str = "&nbsp;";
			}
			$tbl->setTitle($this->lng->txt("members"),"icon_usr.gif",
				$this->lng->txt("exc_header_members"));
			$tbl->setHeaderNames(array("", "", $this->lng->txt("name"),
				$this->lng->txt("login"),
				$sent_str,
				$this->lng->txt("exc_submission"),
				$this->lng->txt("exc_grading"),
				$this->lng->txt("mail")
				));

			$tbl->setColumnWidth(array("1%", "1%", "", "", "", "", "", ""));
			$cols = array("", "", "name", "login", "sent_time", "submission",
				"solved_time", "feedback_time");
			
			if (!$_GET["sort_by"])
			{
				$_GET["sort_by"] = "name";
			}
			if (!$_GET["sort_order"])
			{
				$_GET["sort_order"] = "asc";
			}
			
			$header_params = $this->ctrl->getParameterArray($this);
			unset($header_params["sort_by"]);
			unset($header_params["sort_order"]);
			unset($header_params["offset"]);
			$header_params["cmd"] = "members";
			$tbl->setHeaderVars($cols, $header_params);
			$members = ilUtil::sortArray($members, $_GET["sort_by"], $_GET["sort_order"]);
			$tbl->setOrderColumn($_GET["sort_by"]);
			$tbl->setOrderDirection($_GET["sort_order"]);
			$tbl->setOffset($_GET["offset"]);
			$tbl->setLimit($_GET["limit"]);
			$tbl->setMaxCount(count($members));
			$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
			$members = array_slice($members, $_GET["offset"], $_GET["limit"]);
			$tbl->render();

			
			// new table
			foreach ($members as $member)
			{
				include_once "./classes/class.ilObjectFactory.php";
		
				$member_id = $member["usr_id"];
				if(!($mem_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
				{
					continue;
				}
				
				// checkbox
				$this->tpl->setCurrentBlock("member_row");
				$this->tpl->setVariable("ROW_CSS",
					ilUtil::switchColor($counter++,"tblrow1","tblrow2"));
				$this->tpl->setVariable("VAL_CHKBOX",
					ilUtil::formCheckbox(0,"member[$member_id]",1));
				$this->tpl->setVariable("VAL_ID",
					$member_id);
					
				// name and login
				$this->tpl->setVariable("TXT_NAME",
					$member["name"]);
				$this->tpl->setVariable("TXT_LOGIN",
					"[".$member["login"]."]");
					
				// image
				$this->tpl->setVariable("USR_IMAGE",
					$mem_obj->getPersonalPicturePath("xxsmall"));
				$this->tpl->setVariable("USR_ALT", $this->lng->txt("personal_picture"));

				// mail sent
				if ($this->object->members_obj->getStatusSentByMember($member_id))
				{
					if (($st = ilObjExercise::_lookupSentTime($this->object->getId(),
						$member_id)) > 0)
					{
						$this->tpl->setVariable("TXT_MAIL_SENT",
							sprintf($this->lng->txt("exc_sent_at"),
							ilDatePresentation::formatDate(new ilDateTime($st,IL_CAL_DATE))));
					}
					else
					{
						$this->tpl->setVariable("TXT_MAIL_SENT",
							$this->lng->txt("sent"));
					}
				}

				// submission:
				// see if files have been resubmmited after solved
				$last_sub =
					$this->object->getLastSubmission($member_id);
					
				if ($last_sub)
				{
					$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
				}
				else
				{
					$last_sub = "---";
				}
				if ($this->object->_lookupUpdatedSubmission($this->object->getId(), $member_id) == 1) 
				{
					$last_sub = "<b>".$last_sub."</b>";
				}
				$this->tpl->setVariable("VAL_LAST_SUBMISSION", $last_sub);
				$this->tpl->setVariable("TXT_LAST_SUBMISSION",
					$this->lng->txt("exc_last_submission"));

				// nr of submitted files
				$this->tpl->setVariable("TXT_SUBMITTED_FILES",
					$this->lng->txt("exc_files_returned"));
				$sub_cnt = count($this->object->getDeliveredFiles($member_id));
				$new = $this->object->_lookupNewFiles($this->object->getId(), $member_id);
				if (count($new) > 0)
				{
					$sub_cnt.= " ".sprintf($this->lng->txt("cnt_new"),count($new));
				}
				$this->tpl->setVariable("VAL_SUBMITTED_FILES",
					$sub_cnt);
				
				// download command
				$this->ctrl->setParameter($this, "member_id", $member_id);
				if ($sub_cnt > 0)
				{
					$this->tpl->setCurrentBlock("download_link");
					$this->tpl->setVariable("LINK_DOWNLOAD",
						$this->ctrl->getLinkTarget($this, "downloadReturned"));
					if (count($new) <= 0)
					{
						$this->tpl->setVariable("TXT_DOWNLOAD",
							$this->lng->txt("exc_download_files"));
					}
					else
					{
						$this->tpl->setVariable("TXT_DOWNLOAD",
							$this->lng->txt("exc_download_all"));
					}
					$this->tpl->parseCurrentBlock();
					
					// download new files only
					if (count($new) > 0)
					{
						$this->tpl->setCurrentBlock("download_link");
						$this->tpl->setVariable("LINK_NEW_DOWNLOAD",
							$this->ctrl->getLinkTarget($this, "downloadNewReturned"));
						$this->tpl->setVariable("TXT_NEW_DOWNLOAD",
							$this->lng->txt("exc_download_new"));
						$this->tpl->parseCurrentBlock();
					}
					
					$this->tpl->setCurrentBlock("member_row");
				}
				
				// note
				$this->tpl->setVariable("TXT_NOTE", $this->lng->txt("note"));
				$this->tpl->setVariable("NAME_NOTE",
					"notice[$member_id]");
				$this->tpl->setVariable("VAL_NOTE",
					ilUtil::prepareFormOutput($this->object->members_obj->getNoticeByMember($member_id)));
					
				// comment for learner
				$this->tpl->setVariable("TXT_LCOMMENT", $this->lng->txt("exc_comment_for_learner"));
				$this->tpl->setVariable("NAME_LCOMMENT",
					"lcomment[$member_id]");
				$lpcomment = ilLPMarks::_lookupComment($member_id,$this->object->getId());
				$this->tpl->setVariable("VAL_LCOMMENT",
					ilUtil::prepareFormOutput($lpcomment));

				// solved
				//$this->tpl->setVariable("CHKBOX_SOLVED",
				//	ilUtil::formCheckbox($this->object->members_obj->getStatusByMember($member_id),"solved[$member_id]",1));
				$status = ilExerciseMembers::_lookupStatus($this->object->getId(), $member_id);
				$this->tpl->setVariable("SEL_".strtoupper($status), ' selected="selected" ');
				$this->tpl->setVariable("TXT_NOTGRADED", $this->lng->txt("exc_notgraded"));
				$this->tpl->setVariable("TXT_PASSED", $this->lng->txt("exc_passed"));
				$this->tpl->setVariable("TXT_FAILED", $this->lng->txt("exc_failed"));
				if (($sd = ilObjExercise::_lookupStatusTime($this->object->getId(), $member_id)) > 0)
				{
					$this->tpl->setCurrentBlock("status_date");
					$this->tpl->setVariable("TXT_LAST_CHANGE", $this->lng->txt("last_change"));
					$this->tpl->setVariable('VAL_STATUS_DATE',
						ilDatePresentation::formatDate(new ilDateTime($sd,IL_CAL_DATETIME)));
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("member_row");
				}
				switch($status)
				{
					case "passed": 	$pic = "scorm/passed.gif"; break;
					case "failed":	$pic = "scorm/failed.gif"; break;
					default: 		$pic = "scorm/not_attempted.gif"; break;
				}
				$this->tpl->setVariable("IMG_STATUS", ilUtil::getImagePath($pic));
				$this->tpl->setVariable("ALT_STATUS", $this->lng->txt("exc_".$status));
				
				// mark
				$this->tpl->setVariable("TXT_MARK", $this->lng->txt("exc_mark"));
				$this->tpl->setVariable("NAME_MARK",
					"mark[$member_id]");
				$mark = ilLPMarks::_lookupMark($member_id,$this->object->getId());
				$this->tpl->setVariable("VAL_MARK",
					ilUtil::prepareFormOutput($mark));
					
				// feedback
				$this->ctrl->setParameter($this, "member_id", $member_id);
				$this->tpl->setVariable("CHKBOX_FEEDBACK",
					ilUtil::formCheckbox($this->object->members_obj->getStatusFeedbackByMember($member_id),"feedback[$member_id]",1));
				if (($ft = ilObjExercise::_lookupFeedbackTime($this->object->getId(), $member_id)) > 0)
				{
					$this->tpl->setCurrentBlock("feedback_date");
					$this->tpl->setVariable("TXT_FEEDBACK_MAIL_SENT",
						sprintf($this->lng->txt("exc_sent_at"),
						ilDatePresentation::formatDate(new ilDateTime($ft,IL_CAL_DATETIME))));
					$this->tpl->parseCurrentBlock();
					$this->tpl->setCurrentBlock("member_row");
				}
				$this->ctrl->setParameter($this, "rcp_to", $mem_obj->getLogin());
				$this->tpl->setVariable("LINK_FEEDBACK",
					$this->ctrl->getLinkTarget($this, "redirectFeedbackMail"));
					//"ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".$mem_obj->getLogin());
				$this->tpl->setVariable("TXT_FEEDBACK",
					$this->lng->txt("exc_send_mail"));
				$this->ctrl->setParameter($this, "rcp_to", "");

				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("tbl_content");
			$this->tpl->parseCurrentBlock();

			
			//$this->__showMembersTableContent($this->__showMembersTable($f_result,$member_ids));

			if(count($this->object->members_obj->getAllDeliveredFiles()))
			{
				$this->tpl->addBlockFile("SPECIAL_BUTTONS", "special_buttons", "tpl.exc_download_all.html",
					"Modules/Exercise");
				$this->tpl->setCurrentBlock("download_all");
				$this->tpl->setVariable("BUTTON_DOWNLOAD_ALL", $this->lng->txt("download_all_returned_files"));
				$this->tpl->setVariable("FORMACTION", 
					$this->ctrl->getLinkTarget($this, "downloadAll"));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	/**
	* set feedback status for member and redirect to mail screen
	*/
	function redirectFeedbackMailObject()
	{
		$this->checkPermission("write");
		
		if ($_GET["member_id"] != "")
		{
			$this->object->members_obj->setStatusFeedbackForMember($_GET["member_id"], 1);
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".urlencode($_GET["rcp_to"]));
		}
		else if(count($_POST["member"]) > 0)
		{
			include_once('./Services/User/classes/class.ilObjUser.php');
			$logins = array();
			foreach($_POST["member"] as $member => $val)
			{
				$logins[] = ilObjUser::_lookupLogin($member);
				$this->object->members_obj->setStatusFeedbackForMember($member, 1);
			}
			$logins = implode($logins, ",");
			ilUtil::redirect("ilias.php?baseClass=ilMailGUI&type=new&rcp_to=".$logins);
		}

		ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
		$this->ctrl->redirect($this, "members");
	}
	
	/**
	* Download all submitted files (of all members).
	*/
	function downloadAllObject()
	{
		$this->checkPermission("write");
		
		$members = array();

		foreach($this->object->members_obj->getMembers() as $member_id)
		{
			// update download time
			$this->object->members_obj->updateTutorDownloadTime($member_id);

			// get member object (ilObjUser)
			if (ilObject::_exists($member_id))
			{
				$tmp_obj =& ilObjectFactory::getInstanceByObjId($member_id);
				$members[$member_id] = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
				unset($tmp_obj);
			}
		}
	
		$this->object->file_obj->downloadAllDeliveredFiles($members);
	}
	

	function __getMembersOfObject($a_result,$a_type)
	{

		switch($a_type)
		{
			case "usr":
				return $a_result;
			case "grp":
				include_once "./Modules/Group/classes/class.ilObjGroup.php";
	
				$all_members = array();
				foreach($a_result as $group)
				{
					$tmp_grp_obj = ilObjectFactory::getInstanceByRefId($group["id"]);
	
					$members = $tmp_grp_obj->getGroupMemberIds();
					$all_members = array_merge($all_members,$members);
				}
				// FORMAT ARRAY
				$all_members = array_unique($all_members);
				foreach($all_members as $member)
				{
					$result[] = array("id" => $member);
				}
				return $result;
		}
  		return true;
	}

	function __showObjectSelect($a_result,$a_type)
	{
  		include_once "./classes/class.ilObjectFactory.php";
  
		foreach($a_result as $obj)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($obj["id"]);
			$this->tpl->setCurrentBlock("OBJ_SELECT_ROW");
			$this->tpl->setVariable("OBJ_ROW_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("OBJ_ROW_ID",$tmp_obj->getRefId());
			$this->tpl->setVariable("OBJ_ROW_DESCRIPTION",$tmp_obj->getDescription());
			$this->tpl->parseCurrentBlock();
	
			unset($tmp_obj);
		}
		$this->tpl->setCurrentBlock("OBJ_SELECT");
		$this->tpl->setVariable("OBJ_SELECT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("OBJ_SELECT_DESCRIPTION",$this->lng->txt("description"));
  
		$this->tpl->setVariable("OBJ_BTN1_VALUE",$this->lng->txt("select"));
		$this->tpl->setVariable("OBJ_BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->parseCurrentBlock();
	}

	/**
	* Send exercise to members
	*/
	function sendMembersObject()
	{
		global $ilCtrl;
		
		if(!count($_POST["member"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
		}
		else
		{
			$this->object->send($_POST["member"]);
			ilUtil::sendSuccess($this->lng->txt("exc_sent"),true);
		}
		$ilCtrl->redirect($this, "members");
	}

	/**
	 * Deassign members from exercise 
	 */
	function deassignMembersObject()
	{
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		if(is_array($_POST["member"]))
		{
			foreach($_POST["member"] as $usr_id => $member)
			{
				$this->object->members_obj->deassignMember($usr_id);
			}
			$ilCtrl->redirect($this, "members");
		}
  		else
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
			$ilCtrl->redirect($this, "members");
		}
	}

	function saveCommentsObject() 
	{
		$this->checkPermission("write");
		
		if(!isset($_POST['comments_value']))
		{
			continue;
		}
  
		$this->object->members_obj->setNoticeForMember($_GET["member_id"],
			ilUtil::stripSlashes($_POST["comments_value"]));
		ilUtil::sendSuccess($this->lng->txt("exc_members_comments_saved"));
		$this->membersObject();
	}

	function getComments($member_id) 
	{
		return $this->object->members_obj->getNoticeByMember($member_id);
	}

	/**
	 * Save status of selecte members 
	 */
	function saveStatusObject()
	{
		global $ilCtrl;
		
		$this->checkPermission("write");
		
		include_once 'Services/Tracking/classes/class.ilLPMarks.php';

		$saved_for = array();
		
		foreach($_POST["id"] as $key => $value)
		{
			if (count($_POST["member"]) > 0 && $_POST["member"][$key] != "1")
			{
				continue;
			}
			else
			{
				if (count($_POST["member"]) > 0)
				{
					$uname = ilObjUser::_lookupName($key);
					$saved_for[] = $uname["lastname"].", ".$uname["firstname"];
				}
			}
			
			$this->object->members_obj->setStatusForMember($key, $_POST["status"][$key]);
			//$this->object->members_obj->setStatusFeedbackForMember($key, $_POST["feedback"][$key] ? 1 : 0);
			$this->object->members_obj->setNoticeForMember($key,ilUtil::stripSlashes($_POST["notice"][$key]));

			if (ilUtil::stripSlashes($_POST['mark'][$key]) != 
				ilLPMarks::_lookupMark($key, $this->object->getId()))
			{
				$this->object->members_obj->updateStatusTimeForMember($key);
			}
				
			// save mark and comment
			$marks_obj = new ilLPMarks($this->object->getId(),$key);
			$marks_obj->setMark(ilUtil::stripSlashes($_POST['mark'][$key]));
			$marks_obj->setComment(ilUtil::stripSlashes($_POST['lcomment'][$key]));
			$marks_obj->update();
		}
		if (count($saved_for) > 0)
		{
			$save_for_str = "(".implode($saved_for, " - ").")";
		}
		ilUtil::sendSuccess($this->lng->txt("exc_status_saved")." ".$save_for_str,true);
		$ilCtrl->redirect($this, "members");
	}

	function __getDateSelect($a_type,$a_selected)
	{
  		switch($a_type)
		{
			case "hour":
				for($i=0; $i<24; $i++)
				{
					$hours[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_hour",$hours,false,true);
	
			case "minutes":
				for($i=0;$i<60;$i++)
				{
					$minutes[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_minutes",$minutes,false,true);
	
			case "day":
				for($i=1; $i<32; $i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_day",$days,false,true);
	
			case "month":
				for($i=1; $i<13; $i++)
				{
					$month[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_month",$month,false,true);
	
			case "year":
				for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
				{
					$year[$i] = $i;
				}
				return ilUtil::formSelect($a_selected,"d_year",$year,false,true);
		}
	}

	function __filterAssignedUsers($a_result)
	{
		foreach($a_result as $user)
		{
			if(!$this->object->members_obj->isAssigned($user["id"]))
			{
				$filtered[] = $user;
			}
		}
	
  		return $filtered ? $filtered : array();
	}
	
	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $ilAccess,$ilUser;
  
		$next_class = strtolower($this->ctrl->getNextClass());
		if ($ilAccess->checkAccess("visible", "", $this->object->getRefId()))
		{
			$force_active = ($next_class == "ilinfoscreengui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"),
				 "showSummary",
				 "ilinfoscreengui", "", $force_active);
		}

		/*
		$tabs_gui->addTarget("view",
			$this->ctrl->getLinkTarget($this, 'view'),
			array("view",""), "");*/
			
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$tabs_gui->addTarget("exc_your_submission",
				$this->ctrl->getLinkTarget($this, "deliver"),
				"deliver", "");
		}

		// edit properties
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, 'edit'),
				"edit", "");
			
			$tabs_gui->addTarget("members",
			$this->ctrl->getLinkTarget($this, 'members'),
			array("members", "newMembers", "newmembers"), "");
		}

		// learning progress
		$save_sort_order = $_GET["sort_order"];		// hack, because exercise sort parameters
		$save_sort_by = $_GET["sort_by"];			// must not be forwarded to learning progress
		$save_offset = $_GET["offset"];
		$_GET["offset"] = $_GET["sort_by"] = $_GET["sort_order"] = "";
		
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjexercisegui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}

		$_GET["sort_order"] = $save_sort_order;		// hack, part ii
		$_GET["sort_by"] = $save_sort_by;
		$_GET["offset"] = $save_offset;
		
		// permissions
		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
			$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), 
			array("perm","info","owner"), 'ilpermissiongui');
		}
	}
	
	function &executeCommand()
	{
  		global $ilUser;
  
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
  
//echo "-".$next_class."-".$cmd."-"; exit;
  		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
			break;
	
			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
	
				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,
					$this->object->getRefId(),
					$_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
			break;

			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,'addMembersObject');

				// Set tabs
				$this->tabs_gui->setTabActive('members');
				$this->ctrl->setReturn($this,'members');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				#$this->__setSubTabs('members');
				#$this->tabs_gui->setSubTabActive('members');
				break;

	
			default:
				if(!$cmd)
				{
					$cmd = "infoScreen";
				}
	
				$cmd .= "Object";
	
				$this->$cmd();
	
			break;
		}
  
  		return true;
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
		global $ilAccess, $ilUser;

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		$info->enablePrivateNotes();
		
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$info->enableNewsEditing();
			$info->setBlockProperty("news", "settings", true);
		}
		
		// standard meta data
		//$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		// instructions
		$info->addSection($this->lng->txt("exc_instruction"));
		$info->addProperty("",
			nl2br($this->object->getInstruction()));
		
		// schedule
		$info->addSection($this->lng->txt("exc_schedule"));
		$info->addProperty($this->lng->txt("exc_edit_until"),
			ilDatePresentation::formatDate(new ilDateTime($this->object->getTimestamp(),IL_CAL_UNIX)));
		
		if ($this->object->getTimestamp()-time() <= 0)
		{
			$time_str = $this->lng->txt("exc_time_over_short");
		}
		else
		{
			$time_diff = ilUtil::int2array($this->object->getTimestamp()-time(),null);
			// BEGIN Usability: Never display seconds
			unset($time_diff['seconds']);
			// END Usability: Never display seconds
			// BEGIN Usability: Don't display minutes, if it takes days
			if (isset($time_diff['days']))
			{
				unset($time_diff['minutes']);
			}
			// END Usability: Don't display minutes, if it takes days
			// BEGIN Usability: Don't display hours, if it takes months
			if (isset($time_diff['months']))
			{
				unset($time_diff['hours']);
			}
			// END Usability: Don't display minutes, if it takes months
			$time_str = ilUtil::timearray2string($time_diff);
		}
		$info->addProperty($this->lng->txt("exc_time_to_send"),
			"<b>".$time_str."</b>");
			
		// download files
		if ($ilAccess->checkAccess("read", "", $this->ref_id))
		{
			$files = $this->object->getFiles();
			if (count($files) > 0)
			{
				$info->addSection($this->lng->txt("exc_files"));
				foreach($files as $file)
				{
					$this->ctrl->setParameter($this, "file", urlencode($file["name"]));
					$info->addProperty($file["name"],
						$this->lng->txt("download"),
						$this->ctrl->getLinkTarget($this, "downloadFile"));
					$this->ctrl->setParameter($this, "file", "");
				}
			}
		}

		// submission and feedback info only if read permission given
		if ($ilAccess->checkAccess("read", "", $this->ref_id))
		{
			// submission
			$info->addSection($this->lng->txt("exc_your_submission"));
			$delivered_files = $this->object->getDeliveredFiles($ilUser->id);
			$titles = array();
			foreach($delivered_files as $file)
			{
				$titles[] = $file["filetitle"];
			}
			$files_str = implode($titles, ", ");
			if ($files_str == "")
			{
				$files_str = $this->lng->txt("message_no_delivered_files");
			}
			$info->addProperty($this->lng->txt("exc_files_returned"),
				$files_str);
			$last_sub = $this->object->getLastSubmission($ilUser->getId());
			if ($last_sub)
			{
				$last_sub = ilDatePresentation::formatDate(new ilDateTime($last_sub,IL_CAL_DATETIME));
			}
			else
			{
				$last_sub = "---";
			}

			$info->addProperty($this->lng->txt("exc_last_submission"),
				$last_sub);
			
			// feedback from tutor
			include_once("Services/Tracking/classes/class.ilLPMarks.php");
			$lpcomment = ilLPMarks::_lookupComment($ilUser->getId(), $this->object->getId());
			$mark = ilLPMarks::_lookupMark($ilUser->getId(), $this->object->getId());
			$status = ilExerciseMembers::_lookupStatus($this->object->getId(), $ilUser->getId());
			if ($lpcomment != "" || $mark != "" || $status != "notgraded")
			{
				$info->addSection($this->lng->txt("exc_feedback_from_tutor"));
				if ($lpcomment != "")
				{
					$info->addProperty($this->lng->txt("exc_comment"),
						$lpcomment);
				}
				if ($mark != "")
				{
					$info->addProperty($this->lng->txt("exc_mark"),
						$mark);
				}

				if ($status == "") 
				{
				  $info->addProperty($this->lng->txt("status"),
						$this->lng->txt("message_no_delivered_files"));				
				}
				else
					if ($status != "notgraded")
					{
					  $info->addProperty($this->lng->txt("status"),
							$this->lng->txt("exc_".$status));
					}
			}
		}

		// forward the command
		$this->ctrl->forwardCommand($info);
	}


	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	function _goto($a_target)
	{
		global $rbacsystem, $ilErr, $lng, $ilAccess;

		if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "infoScreen";
			$_GET["baseClass"] = "ilExerciseHandlerGUI";
			include("ilias.php");
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
	* Add locator item
	*/
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "infoScreen"), "", $_GET["ref_id"]);
		}
	}
	
} // END class.ilObjExerciseGUI
?>
