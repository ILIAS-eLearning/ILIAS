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
* Class ilObjExerciseGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @ilCtrl_Calls ilObjExerciseGUI: ilPermissionGUI, ilLearningProgressGUI
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjExerciseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjExerciseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		$this->type = "exc";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
	}

	function getFiles()
	{
		return $this->files ? $this->files : array();
	}
	function setFiles($a_files)
	{
		$this->files = $a_files;
	}

	function createObject()
	{
		parent::createObject();

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

		return true;
	}

	function viewObject()
	{
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
		$this->tpl->setVariable("TITLE",$this->object->getTitle());
		$this->tpl->setVariable("DESCRIPTION",$this->object->getDescription());
		$this->tpl->setVariable("INSTRUCTION_TXT",$this->lng->txt("exc_instruction"));
		$this->tpl->setVariable("INSTRUCTION",nl2br($this->object->getInstruction()));
		$this->tpl->setVariable("EDIT_UNTIL_TXT",$this->lng->txt("exc_edit_until"));
		$this->tpl->setVariable("EDIT_UNTIL",date("H:i, d.m.Y",$this->object->getTimestamp()));

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
		require_once "./classes/class.ilUtil.php";
		
		if (mktime() > $this->object->getTimestamp())
		{
			sendInfo($this->lng->txt("exercise_time_over"));
		}
		else
		{
			if ($_POST["cmd"]["delete"])
			{
				if (count($_POST["delivered"]))
				{
					$this->object->deleteDeliveredFiles($_POST["delivered"], $ilUser->id);
				}
				else
				{
					sendInfo($this->lng->txt("please_select_a_delivered_file_to_delete"));
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
					sendInfo($this->lng->txt("please_select_a_delivered_file_to_download"));
				}
			}
	
			$this->getTemplateFile("deliver_file", "exc");
			$delivered_files = $this->object->getDeliveredFiles($ilUser->id);
			$color_class = array("tblrow1", "tblrow2");
			$counter = 0;
			foreach ($delivered_files as $index => $file)
			{
				$this->tpl->setCurrentBlock("delivered_row");
				$this->tpl->setVariable("COLOR_CLASS", $color_class[$counter % 2]);
				$this->tpl->setVariable("FILE_ID", $file["returned_id"]);
				$this->tpl->setVariable("DELIVERED_FILE", $file["filetitle"]);
				preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $file["TIMESTAMP14"], $matches);
				$stamp = strtotime(sprintf("%04d-%02d-%02d %02d:%02d:%02d", $matches[1], $matches[2], $matches[3], $matches[4], $matches[5], $matches[6]));
				$date = date($this->lng->text["lang_dateformat"] . " " . $this->lng->text["lang_timeformat"], $stamp);
				$this->tpl->setVariable("DELIVERED_DATE", $date);
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
				$this->tpl->setVariable("TEXT_NO_DELIVERED_FILES", $this->lng->txt("message_no_delivered_files"));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("delivered_files");
			$this->tpl->setVariable("DELIVER_FORMACTION", $this->getFormAction("deliver", "exercise.php?cmd=deliver&ref_id=".$this->ref_id));
			$this->tpl->setVariable("TEXT_DATE", $this->lng->txt("date"));
			$this->tpl->setVariable("TEXT_DELIVERED_FILENAME", $this->lng->txt("filename"));
			$this->tpl->setVariable("TEXT_HEADING_DELIVERED_FILES", $this->lng->txt("already_delivered_files"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("adm_content");
			$this->tpl->setVariable("FORMACTION", $this->getFormAction("deliverFile", "exercise.php?cmd=deliverFile&ref_id=".$this->ref_id));
			$this->tpl->setVariable("BUTTON_DELIVER", $this->lng->txt("upload"));
			$this->tpl->setVariable("TEXT_FILENAME", $this->lng->txt("enter_filename_deliver"));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function deliverFileObject()
	{
		global $ilUser;

		if(!$this->object->deliverFile($_FILES["deliver"], $ilUser->id))
		{
			sendInfo($this->lng->txt("exc_upload_error"),true);
		}
		$this->deliverObject();
	}
	
	function downloadFileObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!isset($_POST["file"]))
		{
			sendInfo($this->lng->txt("exc_select_one_file"),true);
			$this->ctrl->redirect($this, "view");
		}
		$files = $this->object->getFiles();
		$file_exist = false;

		foreach($this->object->getFiles() as $file)
		{
			if($file["name"] == urldecode($_POST["file"]))
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
		ilUtil::deliverFile($this->object->file_obj->getAbsolutePath(urldecode($_POST["file"])),
							urldecode($_POST["file"]));

		return true;
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		// CHECK INPUT
		include_once("./classes/class.ilObjExercise.php");
		$tmp_obj =& new ilObjExercise();

		$tmp_obj->setDate($_POST["d_hour"],$_POST["d_minutes"],$_POST["d_day"],$_POST["d_month"],$_POST["d_year"]);
		if(!$tmp_obj->checkDate())
		{
			$this->ilias->raiseError($this->lng->txt("exc_date_not_valid"), $this->ilias->error_obj->MESSAGE);
		}
		unset($tmp_obj);
		// END INPUT CHECK

		// always call parent method first to create an object_data entry & a reference
		$newObj = parent::saveObject();

		// setup rolefolder & default local roles if needed (see ilObjForum & ilObjForumGUI for an example)
		//$roles = $newObj->initDefaultRoles();

		// put here your object specific stuff	
		
		$newObj->setDate($_POST["d_hour"],$_POST["d_minutes"],$_POST["d_day"],$_POST["d_month"],$_POST["d_year"]);
	
		$newObj->setInstruction(ilUtil::stripSlashes($_POST["Fobject"]["instruction"]));
		$newObj->saveData();

		// always send a message
		sendInfo($this->lng->txt("exc_added"),true);
		ilUtil::redirect("exercise.php?ref_id=".$newObj->getRefId()."&cmd=edit");
		//header("Location:".$this->getReturnLocation("save","adm_object.php?".$this->link_params));
		//exit();
	}

	function editObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		// LOAD SAVED DATA IN CASE OF ERROR
		$title = $_SESSION["error_post_vars"]["Fobject"]["title"] ?
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["Fobject"]["title"],true) :
			ilUtil::prepareFormOutput($this->object->getTitle());
		$desc  = $_SESSION["error_post_vars"]["Fobject"]["desc"] ?
			$_SESSION["error_post_vars"]["Fobject"]["desc"] :
			$this->object->getDescription();

		$instruction  = $_SESSION["error_post_vars"]["Fobject"]["instruction"] ?
			$_SESSION["error_post_vars"]["Fobject"]["instruction"] :
			$this->object->getInstruction();

		$hour  = $_SESSION["error_post_vars"]["Fobject"]["d_hour"] ?
			$_SESSION["error_post_vars"]["Fobject"]["d_hour"] :
			date("H",$this->object->getTimestamp());

		$minutes  = $_SESSION["error_post_vars"]["Fobject"]["d_minutes"] ?
			$_SESSION["error_post_vars"]["Fobject"]["d_minutes"] :
			date("i",$this->object->getTimestamp());

		$day  = $_SESSION["error_post_vars"]["Fobject"]["d_day"] ?
			$_SESSION["error_post_vars"]["Fobject"]["d_day"] :
			date("d",$this->object->getTimestamp());

		$month  = $_SESSION["error_post_vars"]["Fobject"]["d_month"] ?
			$_SESSION["error_post_vars"]["Fobject"]["d_month"] :
			date("m",$this->object->getTimestamp());

		$year  = $_SESSION["error_post_vars"]["Fobject"]["year"] ?
			$_SESSION["error_post_vars"]["Fobject"]["year"] :
			date("Y",$this->object->getTimestamp());
		
		// SET TPL VARIABLES
		$this->getTemplateFile("edit","exc");

		// TEXT VAIRABLES
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
		$this->tpl->setVariable("TXT_INSTRUCTION", $this->lng->txt("exc_instruction"));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("exc_edit_exercise"));
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		//$this->tpl->setVariable("FORMACTION", 
		//		$this->getFormAction("gateway","adm_object.php?ref_id=".$this->ref_id."&cmd=gateway"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("save"));
		$this->tpl->setVariable("TXT_EDIT_UNTIL",$this->lng->txt("exc_edit_until"));


		// SHOW INPUT
		$this->tpl->setVariable("TITLE",$title);
		$this->tpl->setVariable("DESC",$desc);
		$this->tpl->setVariable("INSTRUCTION",$instruction);
		


		// SHOW DATE SELECTS
		$this->tpl->setVariable("TXT_HOUR",$this->lng->txt("time_h"));
		$this->tpl->setVariable("TXT_DAY",$this->lng->txt("time_d"));
		$this->tpl->setVariable("SELECT_HOUR",$this->__getDateSelect("hour",$hour));	
		$this->tpl->setVariable("SELECT_MINUTES",$this->__getDateSelect("minutes",$minutes));
		$this->tpl->setVariable("SELECT_DAY",$this->__getDateSelect("day",$day));
		$this->tpl->setVariable("SELECT_MONTH",$this->__getDateSelect("month",$month));
		$this->tpl->setVariable("SELECT_YEAR",$this->__getDateSelect("year",$year));
		
		$this->tpl->setVariable("CMD_SUBMIT","update");
		$this->tpl->setVariable("CMD_CANCEL","cancelEdit");

		// SHOW FILES
		if(count($files = $this->object->getFiles()))
		{
			foreach($files as $file)
			{
				$this->tpl->setCurrentBlock("FILE_ROW");
				$this->tpl->setVariable("ROW_FILE",$file["name"]);
				$this->tpl->setVariable("ROW_CHECKBOX",$this->lng->txt("exc_ask_delete")."&nbsp".
										ilUtil::formCheckbox(0,"delete_file[]",$file["name"]));
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("FILE_DATA");
			$this->tpl->setVariable("TXT_FILES",$this->lng->txt("exc_files").":");
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("FILES");
		$this->tpl->setVariable("TXT_HEADER_FILE",$this->lng->txt("file_add"));
		$this->tpl->setVariable("TXT_FILE",$this->lng->txt("file"));
		$this->tpl->setVariable("TXT_UPLOAD",$this->lng->txt("upload"));
		$this->tpl->setVariable("FORMACTION_FILE", $this->ctrl->getFormAction($this));
		//$this->tpl->setVariable("FORMACTION_FILE",
		//		$this->getFormAction("gateway","adm_object.php?ref_id=".$this->ref_id."&cmd=gateway"));
		$this->tpl->setVariable("CMD_FILE_SUBMIT","uploadFile");
		$this->tpl->parseCurrentBlock();

	}

	function updateObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
	  
		$this->object->setInstruction(ilUtil::stripSlashes($_POST["Fobject"]["instruction"]));
		$this->object->setDate($_POST["d_hour"],$_POST["d_minutes"],$_POST["d_day"],$_POST["d_month"],$_POST["d_year"]);
		if($_POST["delete_file"])
		{
			$this->object->deleteFiles($_POST["delete_file"]);
		}
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->update = $this->object->update();

		sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->redirect($this, "edit");

	}
	
	function cancelEditObject()
	{
		sendInfo($this->lng->txt("msg_cancel"),true);
		$this->ctrl->redirect($this, "view");
	}
		
	function uploadFileObject()
	{
		global $rbacsystem;
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		if(!$this->object->addUploadedFile($_FILES["file"]))
		{
			sendInfo($this->lng->txt("exc_upload_error"),true);
		}
		$this->ctrl->redirect($this, "edit");
		//header("location: ".$this->getReturnLocation("uploadFile","adm_object.php?ref_id=$_GET[ref_id]"));
		//exit;
	}
	function updateMembersObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

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
					$this->__saveStatus();
					sendInfo($this->lng->txt("exc_status_saved"),true);
					break;
				case "send_member":
					if(!count($_POST["member"]))
					{
						sendInfo($this->lng->txt("select_one"),true);
					}
					else
					{
						$this->object->send($_POST["member"]);
						sendInfo($this->lng->txt("exc_sent"),true);
					}
					break;
				case "delete_member":
					$this->__deassignMembers();
					break;
			}
		}
		$this->ctrl->redirect($this, "members");
		//header("location: ".$this->getReturnLocation("members","adm_object.php?ref_id=$_GET[ref_id]&cmd=members"));
		//exit;
	}
		
	function membersObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}
		
		//add template for buttons
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		// add member button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK", $this->ctrl->getLinkTarget($this, 'newmembers'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("add_member"));
		$this->tpl->parseCurrentBlock();

		$this->getTemplateFile("members","exc");

		if(!count($this->object->members_obj->getMembers()))
		{
			sendInfo($this->lng->txt("exc_no_members_assigned"));
		}
		else
		{
			$counter = 0;
			foreach($this->object->members_obj->getMembers() as $member_id)
			{
				include_once "./classes/class.ilObjectFactory.php";

				if(!($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
				{
					continue;
				}
				$f_result[$counter][]	= ilUtil::formCheckbox(0,"member[$member_id]",1);
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $tmp_obj->getFirstname();
				$f_result[$counter][]	= $tmp_obj->getLastname();
				$f_result[$counter][]	= array("notice[$member_id]",
												ilUtil::prepareFormOutput($this->object->members_obj->getNoticeByMember($member_id)));
				if ($this->object->members_obj->hasReturned($member_id))
				{
					$f_result[$counter][]	= "<input class=\"submit\" type=\"submit\" name=\"downloadReturned[$member_id]\" value=\"" . 
						$this->lng->txt("download") . "\" />";
				}
				else
				{
					$f_result[$counter][]	= "";
				}
				$f_result[$counter][]	= ilUtil::formCheckbox($this->object->members_obj->getStatusReturnedByMember($member_id),
															   "returned[$member_id]",1);
				$f_result[$counter][]	= ilUtil::formCheckbox($this->object->members_obj->getStatusSolvedByMember($member_id),
															   "solved[$member_id]",1);
				$f_result[$counter][]	= ilUtil::formCheckbox($this->object->members_obj->getStatusSentByMember($member_id),
															   "sent[$member_id]",1);

				$member_ids[] = $member_id;

				unset($tmp_obj);
				++$counter;
			}
			$this->__showMembersTableContent($this->__showMembersTable($f_result,$member_ids));
			
			
			if(count($this->object->members_obj->getAllDeliveredFiles()))
			{
				$this->tpl->addBlockFile("SPECIAL_BUTTONS", "special_buttons", "tpl.exc_download_all.html");
				$this->tpl->setCurrentBlock("download_all");
				$this->tpl->setVariable("BUTTON_DOWNLOAD_ALL", $this->lng->txt("download_all_returned_files"));
				$this->tpl->setVariable("FORMACTION", 
										$this->getFormAction("downloadAll", "exercise.php?cmd=downloadAll&ref_id=".$this->ref_id));
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	function downloadAllObject()
	{
		$members = array();

		foreach($this->object->members_obj->getMembers() as $member_id)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByObjId($member_id);
			$members[$member_id] = $tmp_obj->getFirstname() . " " . $tmp_obj->getLastname();
			unset($tmp_obj);
		}

		$this->object->file_obj->downloadAllDeliveredFiles($members);
	}
	
	function newMembersObject()
	{
		global $rbacsystem;

		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("permission_denied"),$this->ilias->error_obj->MESSAGE);
		}

		// SEARCH CANCELED
		if(isset($_POST["cancel"]))
		{
			$this->ctrl->redirect($this, "members");
			//header("location: ".$this->getReturnLocation("members","adm_object.php?ref_id=$_GET[ref_id]"));
			//exit;
		}
		
		if(isset($_POST["select"]))
		{
			if(is_array($_POST["id"]))
			{
				if(!$this->object->members_obj->assignMembers($_POST["id"]))
				{
					sendInfo($this->lng->txt("exc_members_already_assigned"),true);
				}
				else
				{
					sendInfo($this->lng->txt("exc_members_assigned"),true);
				}
				$this->ctrl->redirect($this, "members");
				//header("location: ".$this->getReturnLocation("members","adm_object.php?ref_id=$_GET[ref_id]"));
				//exit;
			}
		}
		$show_search = true;

		$this->getTemplateFile("add_member","exc");
		$this->tpl->setVariable("F_ACTION",$this->ctrl->getLinkTarget($this, "newMembers"));
		//$this->tpl->setVariable("F_ACTION", 
		//	$this->getFormAction("newMembers","adm_object.php?ref_id=$_GET[ref_id]&cmd=newMembers"));
		
		if($_POST["search_str"])
		{
			$result = $this->__searchMembers(ilUtil::stripSlashes($_POST["search_str"]),$_POST["search_for"]);
			
			switch(count($result))
			{
				case 0:
					// SHOW ERROR MESSAGE
					sendInfo($this->lng->txt("cont_no_object_found"));
					break;

				case 1:
					$result = $this->__getMembersOfObject($result,$_POST["search_for"]);
					$this->__showMembersSelect($result);
					$show_search = false;
					break;

				default:
					if($_POST["search_for"] == 'usr')
					{
						$this->__showMembersSelect($result);
					}
					else
					{
						$this->__showObjectSelect($result,$_POST["search_for"]);
					}
					$show_search = false;
					break;
			}
		}
		if($_POST["obj_select"])
		{
			if(count($_POST["obj"]))
			{
				$result = $this->__getMembersOfObject($_POST["obj"],"grp");
				$this->__showMembersSelect($result);
				$show_search = false;
			}
		}


		if($show_search)
		{
			$this->lng->loadLanguageModule("content");
			$this->lng->loadLanguageModule("search");

			$search_for = array("usr" => $this->lng->txt("exc_users"),
								"grp"	=> $this->lng->txt("exc_groups"));
#								"role"	=> $this->lng->txt("!!Rollen"));
			
			$counter = 0;
			foreach($search_for as $key => $value)
			{
				$this->tpl->setCurrentBlock("USR_SEARCH_ROW");
				$this->tpl->setVariable("SEARCH_ROW_CHECK",ilUtil::formRadioButton(++$counter == 1 ? 1 : 0,"search_for",$key));
				$this->tpl->setVariable("SEARCH_ROW_TXT",$value);
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("add_member"));
			$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
			$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
			$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));
			$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		}
	}

	// PRIVATE METHODS
	function __showMembersTableContent($a_data)
	{
		
		$counter = 0;
		foreach($a_data as $row)
		{
			foreach($row as $key => $column)
			{
				switch($key)
				{
					case 4:
						$this->tpl->setCurrentBlock("form_input");
						$this->tpl->setVariable("ROW_INPUT_NAME",$column[0]);
						$this->tpl->setVariable("ROW_INPUT_VALUE",$column[1]);
						$this->tpl->parseCurrentBlock();
						break;

					default:
						$this->tpl->setCurrentBlock("text");
						$this->tpl->setVariable("ROW_TEXT",$column);
						$this->tpl->parseCurrentBlock();

						break;
				}
				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("row");
			$this->tpl->setVariable("ROW_CSS",ilUtil::switchColor(++$counter,"tblrow1","tblrow2"));
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("tbl_content");
		$this->tpl->parseCurrentBlock();

		return true;
	}

	function __getMembersOfObject($a_result,$a_type)
	{

		switch($a_type)
		{
			case "usr":
				return $a_result;

			case "grp":
				include_once "./classes/class.ilObjGroup.php";

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

	function __showMembersSelect($a_result)
	{
		include_once "./classes/class.ilObjectFactory.php";
		include_once "./classes/class.ilUtil.php";
		
		$ids = array();

		foreach($a_result as $user)
		{
		  array_push($ids, $user["id"]);

			$tmp_obj =& ilObjectFactory::getInstanceByObjId($user["id"]);

			$this->tpl->setCurrentBlock("USR_SELECT_ROW");
			$this->tpl->setVariable("ROW_LOGIN",$tmp_obj->getLogin());
			$this->tpl->setVariable("NAME_ID","id_".$tmp_obj->getId());
			$this->tpl->setVariable("ROW_ID",$tmp_obj->getId());
			$this->tpl->setVariable("ROW_FIRSTNAME",$tmp_obj->getFirstname());
			$this->tpl->setVariable("ROW_LASTNAME",$tmp_obj->getLastname());
			$this->tpl->parseCurrentBlock();
			
			unset($tmp_obj);
		}

		$this->tpl->setCurrentBlock("USR_SELECT");
		$this->tpl->setVariable("SELECT_LOGIN",$this->lng->txt("login"));
		$this->tpl->setVariable("SELECT_FIRSTNAME",$this->lng->txt("firstname"));
		$this->tpl->setVariable("SELECT_LASTNAME",$this->lng->txt("lastname"));

		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("assign"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("JS_VARNAME","id");
		$this->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($ids));
		$this->tpl->setVariable("TXT_CHECKALL",$this->lng->txt("check_all"));
		$this->tpl->setVariable("TXT_UNCHECKALL",$this->lng->txt("uncheck_all"));

		$this->tpl->parseCurrentBlock();
	}
	function __searchMembers($a_search_str,$a_search_for)
	{
		include_once("./classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");

		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setSearchString(ilUtil::stripSlashes($_POST["search_str"]));
		$search->setCombination("and");
		$search->setSearchFor(array(0 => $a_search_for));
		$search->setSearchType('new');

		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			sendInfo($message,true);
			$this->ctrl->redirect($this, "newMembers");
			//header("location: ".$this->getReturnLocation("newMembers",
			//		"adm_object.php?ref_id=".$this->object->getRefId()."&cmd=newMembers"));
			exit;
		}
		return $search->getResultByType($a_search_for);
	}		
	function __deassignMembers()
	{
		if(is_array($_POST["member"]))
		{
			foreach($_POST["member"] as $usr_id => $member)
			{
				$this->object->members_obj->deassignMember($usr_id);
			}
			return true;
		}
		else
		{
			sendInfo($this->lng->txt("select_one"),true);
			return false;
		}
	}

	function __saveStatus()
	{
		foreach($this->object->members_obj->getMembers() as $member)
		{
			if(!isset($_POST['notice'][$member]))
			{
				continue;
			}
			$this->object->members_obj->setNoticeForMember($member,ilUtil::stripSlashes($_POST["notice"][$member]));
			$this->object->members_obj->setStatusSolvedForMember($member,$_POST["solved"][$member] ? 1 : 0);
			$this->object->members_obj->setStatusSentForMember($member,$_POST["sent"][$member] ? 1 : 0);
			$this->object->members_obj->setStatusReturnedForMember($member,$_POST["returned"][$member] ? 1 : 0);
		}
		return true;
	}

	function __showMembersTable($a_data,$a_member_ids)
	{
		global $ilUser;

		$actions = array("save_status"		=> $this->lng->txt("exc_save_changes"),
						 "send_member"		=> $this->lng->txt("exc_send_exercise"),
						 "delete_member"	=> $this->lng->txt("exc_deassign_members"));

		$this->tpl->addBlockFile("MEMBER_TABLE","member_table","tpl.table.html");
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.exc_members_row.html");


		// SET FORMAACTION
		$this->tpl->setCurrentBlock("tbl_form_header");
		
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getLinkTarget($this, "updateMembers"));
		
		//$this->tpl->setVariable("FORMACTION",
		//	$this->getFormAction("updateMembers","adm_object.php?ref_id=$_GET[ref_id]&cmd=updateMembers"));
		$this->tpl->parseCurrentBlock();

		// SET FOOTER BUTTONS
		$this->tpl->setCurrentBlock("tbl_action_row");


		// show select all
		if (count($a_member_ids))
		{
			// set checkbox toggles
			$this->tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$this->tpl->setVariable("JS_VARNAME","member");			
			$this->tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_member_ids));
			$this->tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$this->tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$this->tpl->parseCurrentBlock();
		}



		$this->tpl->setVariable("COLUMN_COUNTS",6);
		$this->tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$this->tpl->setCurrentBlock("tbl_action_select");
		$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$this->tpl->setVariable("BTN_NAME","execute");
		$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("tbl_action_row");
		$this->tpl->setVariable("COLUMN_COUNTS",9);
		$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$this->tpl->parseCurrentBlock();

		include_once "./classes/class.ilTableGUI.php";

		$tbl = new ilTableGUI();

		$tbl->setTitle($this->lng->txt("exc_header_members"),"icon_usr.gif",$this->lng->txt("exc_header_members"));
		$tbl->setHeaderNames(array('',$this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("exc_notices"),
								   $this->lng->txt("exc_files_returned"),
								   $this->lng->txt("exc_status_returned"),
								   $this->lng->txt("exc_status_solved"),
								   $this->lng->txt("sent")));
		$tbl->setHeaderVars(array("","login","firstname","lastname","","","","",""),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members"));
		$tbl->setColumnWidth(array("5%","10%","10%","10%","30%","15%","7%","7%","7%"));
		$tbl->disable('content');

		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($ilUser->getPref('hits_per_page'));
		$tbl->setMaxCount(count($a_data));
		$tbl->setOffset($_GET["offset"] ? $_GET["offset"] : 0);
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($a_data);
		$tbl->sortData();
		$tbl->render();
		
		return $tbl->getData();
	}

	function __getDateSelect($a_type,$a_selected)
	{
		switch($a_type)
		{
			case "hour":
				for($i=0;$i<24;$i++)
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
				for($i=1;$i<32;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,"d_day",$days,false,true);
				
			case "month":
				for($i=1;$i<13;$i++)
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
		global $rbacsystem;

//if ($this->ctrl->getCmd() != "gateway")
//echo "-".$this->ctrl->getCmd()."-";

		// view
		$tabs_gui->addTarget("view",
			$this->ctrl->getLinkTarget($this, 'view'),
			array("view",""), "");

		// edit properties
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
				$this->ctrl->getLinkTarget($this, 'edit'),
				"edit", "");
		}

		// deliver exercise
		$tabs_gui->addTarget("deliver",
			$this->ctrl->getLinkTarget($this, 'deliver'),
			array("deliver", "deliverFile"), "");

		// learning progress
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if($rbacsystem->checkAccess('read',$this->ref_id) and ilObjUserTracking::_enabledLearningProgress())
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjexercisegui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}


		// members
		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			$tabs_gui->addTarget("show_members",
				$this->ctrl->getLinkTarget($this, 'members'),
				array("members", "newMembers", "newmembers"), "");

			// add member
			/*
			$tabs_gui->addTarget("add_member",
				$this->ctrl->getLinkTarget($this, 'newmembers'),
				"newmembers", get_class($this));
			*/
		}

		// permissions
		if ($rbacsystem->checkAccess("edit_permission", $this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
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
} // END class.ilObjExerciseGUI
?>
