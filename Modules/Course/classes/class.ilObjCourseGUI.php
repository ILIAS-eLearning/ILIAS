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
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
*
* @ilCtrl_Calls ilObjCourseGUI: ilCourseRegisterGUI, ilPaymentPurchaseGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI, ilMDEditorGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjCourseGUI: ilRepositorySearchGUI, ilConditionHandlerInterface
* @ilCtrl_Calls ilObjCourseGUI: ilCourseContentGUI, ilObjUserGUI, ilMemberExportGUI
* @ilCtrl_Calls ilObjCourseGUI: ilCourseUserFieldsGUI, ilCourseAgreementGUI, ilEventAdministrationGUI
*
* 
* @extends ilContainerGUI
*/

require_once "./classes/class.ilContainerGUI.php";
require_once "./Modules/Course/classes/class.ilCourseRegisterGUI.php";


class ilObjCourseGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjCourseGUI()
	{
		global $ilCtrl;

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->type = "crs";
		$this->ilContainerGUI('',(int) $_GET['ref_id'],true,false);

		$this->lng->loadLanguageModule('crs');

		$this->SEARCH_USER = 1;
		$this->SEARCH_GROUP = 2;
		$this->SEARCH_COURSE = 3;
	}

	function gatewayObject()
	{
		switch($_POST["action"])
		{
			case "deleteMembersObject":
				$this->deleteMembers();
				break;

			case "deleteSubscribers":
				$this->deleteSubscribers();
				break;

			case "addSubscribers":
				$this->addSubscribers();
				break;

			case "addFromWaitingList":
				$this->addFromWaitingList();
				break;

			case "removeFromWaitingList":
				$this->removeFromWaitingList();
				break;

			case 'sendMail':
				$this->sendMailToSelectedUsers();
				break;

			default:
				$this->viewObject();
				break;
		}
		return true;
	}

	function sendMailToSelectedUsers()
	{
		$_POST['member'] = array_merge((array) $_POST['member_ids'],(array) $_POST['tutor_ids'],(array) $_POST['admin_ids']);

		if (!count($_POST["member"]))
		{
			ilUtil::sendInfo($this->lng->txt("no_checkbox"));
			$this->membersObject();
			return false;
		}
		foreach($_POST["member"] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}
		ilUtil::redirect("ilias.php?baseClass=ilmailgui&type=new&rcp_to=".implode(',',$rcps));
	}
	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function cancelMemberObject()
	{
		$this->__unsetSessionVariables();

		$return_location = "members";

		ilUtil::sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
	}

	function createObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("create",$_GET["ref_id"],'crs'))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_create"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_create.html",'Modules/Course');
		$this->tpl->setVariable("FORMACTION",'repository.php?ref_id='.$_GET["ref_id"].'&cmd=post&new_type=crs');
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("crs_new"));
		$this->tpl->setVariable("TYPE_IMG",
			ilUtil::getImagePath("icon_crs.gif"));
		$this->tpl->setVariable("ALT_IMG", $this->lng->txt("obj_crs"));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("crs_add"));
		$this->tpl->setVariable("CMD_SUBMIT", "save");
		$this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt('title'));
		$this->tpl->setVariable("TXT_DESC",$this->lng->txt('desc'));


		// IMPORT
		$this->tpl->setVariable("TXT_IMPORT_CRS", $this->lng->txt("import_crs"));
		$this->tpl->setVariable("TXT_CRS_FILE", $this->lng->txt("file"));
		$this->tpl->setVariable("TXT_IMPORT", $this->lng->txt("import"));

		// get the value for the maximal uploadable filesize from the php.ini (if available)
		$umf=get_cfg_var("upload_max_filesize");
		// get the value for the maximal post data from the php.ini (if available)
		$pms=get_cfg_var("post_max_size");

		// use the smaller one as limit
		$max_filesize=min($umf, $pms);
		if (!$max_filesize) 
			$max_filesize=max($umf, $pms);
	
		// gives out the limit as a littel notice :)
		$this->tpl->setVariable("TXT_FILE_INFO", $this->lng->txt("file_notice").$max_filesize);

		$this->fillCloneTemplate('DUPLICATE','crs');

		return true;
	}

	function importFileObject()
	{
		global $_FILES, $rbacsystem, $ilDB;

		// check if file was uploaded
		if($_FILES['xmldoc']['tmp_name'] == 'none' or !$_FILES['xmldoc']['tmp_name'])
		{
			$this->ilias->raiseError("No file selected!",$this->ilias->error_obj->MESSAGE);
		}

		// check correct file type
		$info = pathinfo($_FILES["xmldoc"]["name"]);
		if (strtolower($info["extension"]) != "zip")
		{
			$this->ilias->raiseError("File must be a zip file!",$this->ilias->error_obj->MESSAGE);
		}

		// Create new object
		include_once("Modules/Course/classes/class.ilObjCourse.php");

		$newObj = new ilObjCourse();
		$newObj->setType('crs');
		$newObj->setTitle($_FILES["xmldoc"]["name"]);
		$newObj->setDescription("");
		$newObj->create(true); // true for upload
		$newObj->createReference();
		$newObj->putInTree($_GET["ref_id"]);
		$newObj->setPermissions($_GET["ref_id"]);
		$newObj->initDefaultRoles();

		// Copy xml file
		include_once 'Modules/Course/classes/class.ilFileDataCourse.php';

		$course_files = new ilFileDataCourse($newObj->getId());

		$course_files->createImportFile($_FILES["xmldoc"]["tmp_name"],$_FILES['xmldoc']['name']);
		$course_files->unpackImportFile();
		$course_files->validateImportFile();

		include_once 'Modules/Course/classes/class.ilCourseXMLParser.php';

		$xml_parser = new ilCourseXMLParser($newObj,$course_files->getImportFile());

		$xml_parser->startParsing();

		// Update title description
		$newObj->MDUpdateListener('General');
		
		// delete import file
		#$course_files->deleteImportFile();

		ilUtil::sendInfo($this->lng->txt('crs_added'),true);
	   	
		$this->ctrl->setParameter($this, "ref_id", $newObj->getRefId());
		ilUtil::redirect($this->getReturnLocation("save",
			$this->ctrl->getLinkTarget($this, "edit")));

		//ilUtil::redirect($this->getReturnLocation("save","adm_object.php?".$this->link_params));

	}

	function renderObject()
	{
		$this->ctrl->setCmd("view");
		$this->viewObject();
	}
	
	
	function viewObject()
	{
		global $rbacsystem, $ilUser, $ilCtrl;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return true;
		}
		
		// Fill meta header tags
		include_once('Services/MetaData/classes/class.ilMDUtils.php');
		ilMDUtils::_fillHTMLMetaTags($this->object->getId(),$this->object->getId(),'crs');
		
	
		// Trac access
		include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
		ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),'crs');

		if(!$this->checkAgreement())
		{
			include_once('Modules/Course/classes/class.ilCourseAgreementGUI.php');
			$this->ctrl->setReturn($this,'view_content');
			$agreement = new ilCourseAgreementGUI($this->object->getRefId());
			$this->ctrl->setCmdClass(get_class($agreement));
			$this->ctrl->forwardCommand($agreement);
			return true;
		}


		include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
		$course_content_obj = new ilCourseContentGUI($this);

		$this->ctrl->setCmdClass(get_class($course_content_obj));
		$this->ctrl->forwardCommand($course_content_obj);

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
		global $ilErr,$ilAccess;

		if(!$ilAccess->checkAccess('visible','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		// Fill meta header tags
		include_once('Services/MetaData/classes/class.ilMDUtils.php');
		ilMDUtils::_fillHTMLMetaTags($this->object->getId(),$this->object->getId(),'crs');

		$this->tabs_gui->setTabActive('info_short');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		include_once 'Modules/Course/classes/class.ilCourseFile.php';

		$files =& ilCourseFile::_readFilesByCourse($this->object->getId());

		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
		$info->enableFeedback();
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$info->enableNewsEditing();
		}

		
		if(strlen($this->object->getImportantInformation()) or
		   strlen($this->object->getSyllabus()) or
		   count($files))
		{
			$info->addSection($this->lng->txt('crs_general_informations'));
		}
		if(strlen($this->object->getImportantInformation()))
		{
			$info->addProperty($this->lng->txt('crs_important_info'),
							   "<strong>".nl2br(
							   ilUtil::makeClickable($this->object->getImportantInformation(), true)."</strong>"));
		}
		if(strlen($this->object->getSyllabus()))
		{
			$info->addProperty($this->lng->txt('crs_syllabus'), nl2br(
								ilUtil::makeClickable ($this->object->getSyllabus(), true)));
		}
		// files
		if(count($files))
		{
			$tpl = new ilTemplate('tpl.event_info_file.html',true,true,'Modules/Course');
			
			foreach($files as $file)
			{
				$tpl->setCurrentBlock("files");
				$this->ctrl->setParameter($this,'file_id',$file->getFileId());
				$tpl->setVariable("DOWN_LINK",$this->ctrl->getLinkTarget($this,'sendfile'));
				$tpl->setVariable("DOWN_NAME",$file->getFileName());
				$tpl->setVariable("DOWN_INFO_TXT",$this->lng->txt('crs_file_size_info'));
				$tpl->setVariable("DOWN_SIZE",$file->getFileSize());
				$tpl->setVariable("TXT_BYTES",$this->lng->txt('bytes'));
				$tpl->parseCurrentBlock();
			}
			$info->addProperty($this->lng->txt('crs_file_download'),
							   $tpl->get());
		}
			 
		// contact
		if($this->object->hasContactData())
		{
			$info->addSection($this->lng->txt("crs_contact"));
		}
		if(strlen($this->object->getContactName()))
		{
			$info->addProperty($this->lng->txt("crs_contact_name"),
							   $this->object->getContactName());
		}
		if(strlen($this->object->getContactResponsibility()))
		{
			$info->addProperty($this->lng->txt("crs_contact_responsibility"),
							   $this->object->getContactResponsibility());
		}
		if(strlen($this->object->getContactPhone()))
		{
			$info->addProperty($this->lng->txt("crs_contact_phone"),
							   $this->object->getContactPhone());
		}
		if($this->object->getContactEmail())
		{
			$etpl = new ilTemplate("tpl.crs_contact_email.html", true, true , 'Modules/Course');
			$etpl->setVariable("EMAIL_LINK","ilias.php?baseClass=ilmailgui&type=new&rcp_to=".$this->object->getContactEmail());
			$etpl->setVariable("CONTACT_EMAIL",$this->object->getContactEmail());
			$info->addProperty($this->lng->txt("crs_contact_email"),
				$etpl->get());
		}
		if(strlen($this->object->getContactConsultation()))
		{
			$info->addProperty($this->lng->txt("crs_contact_consultation"),
							   nl2br($this->object->getContactConsultation()));
		}		
		//	
		// access
		//
		$info->addSection($this->lng->txt("access"));
		$info->showLDAPRoleGroupMappingInfo();
		
		// activation
		if($this->object->getOfflineStatus())
		{
			$info->addProperty($this->lng->txt('crs_visibility'),
							   $this->lng->txt('crs_visibility_unvisible'));
		}
		elseif($this->object->getActivationUnlimitedStatus())
		{
			$info->addProperty($this->lng->txt("crs_visibility"),
				$this->lng->txt('crs_unlimited'));
		}
		else
		{
			$info->addProperty($this->lng->txt("crs_visibility"),
							   ilFormat::formatUnixTime($this->object->getActivationStart(),true)." - ".
							   ilFormat::formatUnixTime($this->object->getActivationEnd(),true));
		}
		switch($this->object->getSubscriptionLimitationType())
		{
			case IL_CRS_SUBSCRIPTION_DEACTIVATED:
				$txt = $this->lng->txt("crs_info_reg_deactivated");
				break;

			default:
				switch($this->object->getSubscriptionType())
				{
					case IL_CRS_SUBSCRIPTION_CONFIRMATION:
						$txt = $this->lng->txt("crs_info_reg_confirmation");
						break;
					case IL_CRS_SUBSCRIPTION_DIRECT:
						$txt = $this->lng->txt("crs_info_reg_direct");
						break;
					case IL_CRS_SUBSCRIPTION_PASSWORD:
						$txt = $this->lng->txt("crs_info_reg_password");
						break;
				}
		}
		
		// subscription
		$info->addProperty($this->lng->txt("crs_info_reg"),$txt);


		if($this->object->getSubscriptionLimitationType() != IL_CRS_SUBSCRIPTION_DEACTIVATED)
		{
			if($this->object->getSubscriptionUnlimitedStatus())
			{
				$info->addProperty($this->lng->txt("crs_reg_until"),
								   $this->lng->txt('crs_unlimited'));
			}
			elseif($this->object->getSubscriptionStart() < time())
			{
				$info->addProperty($this->lng->txt("crs_reg_until"),
								   $this->lng->txt('crs_to').' '.
								   ilFormat::formatUnixTime($this->object->getSubscriptionEnd(),true));
			}
			elseif($this->object->getSubscriptionStart() > time())
			{
				$info->addProperty($this->lng->txt("crs_reg_until"),
								   $this->lng->txt('crs_from').' '.
								   ilFormat::formatUnixTime($this->object->getSubscriptionStart(),true));
			}
		}
		
		// archive
		if($this->object->getArchiveType() != IL_CRS_ARCHIVE_NONE)
		{
			$info->addProperty($this->lng->txt("crs_archive"),
							   ilFormat::formatUnixTime($this->object->getArchiveStart(),true)." - ".
							   ilFormat::formatUnixTime($this->object->getArchiveEnd(),true));
		}
		
		// Confirmation
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		
		if($privacy->confirmationRequired() or $privacy->enabledExport())
		{
			include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
			include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
			
			$field_info = ilExportFieldsInfo::_getInstance();
		
			$this->lng->loadLanguageModule('ps');
			$info->addSection($this->lng->txt('crs_user_agreement_info'));
			$info->addProperty($this->lng->txt('ps_export_data'),$field_info->exportableFieldsToInfoString());
			
			if(count($fields = ilCourseDefinedFieldDefinition::_fieldsToInfoString($this->object->getId())))
			{
				$info->addProperty($this->lng->txt('ps_crs_user_fields'),$fields);
			}
		}
		
		$info->enableLearningProgress(true);

		// forward the command
		$this->ctrl->forwardCommand($info);
	}

	function listStructureObject()
	{
		include_once './Modules/Course/classes/class.ilCourseStart.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_start_objects');


		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_list_starter.html",'Modules/Course');
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if(!count($starter = $crs_start->getStartObjects()))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'selectStarter'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('crs_add_starter'));
			$this->tpl->parseCurrentBlock();

			ilUtil::sendInfo($this->lng->txt('crs_no_starter_created'));

			return true;
		}

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_crs'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_edit_start_objects'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("HEADER_OPT",$this->lng->txt('options'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('crs_add_starter'));

		$counter = 0;
		foreach($starter as $start_id => $data)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($data['item_ref_id']);

			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_STARTER",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("starter_row");
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("STARTER_TITLE",$tmp_obj->getTitle());

			$this->ctrl->setParameter($this,'del_starter',$start_id);
			$this->tpl->setVariable("DELETE_LINK",$this->ctrl->getLinkTarget($this,'deleteStarter'));
			$this->tpl->setVariable("DELETE_ALT",$this->lng->txt('delete'));
 			$this->tpl->parseCurrentBlock();
		}
	}

	function deleteStarterObject()
	{
		include_once './Modules/Course/classes/class.ilCourseStart.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());
		$crs_start->delete((int) $_GET['del_starter']);
	
		ilUtil::sendInfo($this->lng->txt('crs_starter_deleted'));
		$this->listStructureObject();
		
		return true;
	}
		

	function selectStarterObject()
	{
		include_once './Modules/Course/classes/class.ilCourseStart.php';

		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_start_objects');

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_add_starter.html",'Modules/Course');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt('obj_crs'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_select_starter'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('crs_add_starter'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));

		
		$this->object->initCourseItemObject();
		$counter = 0;
		foreach($crs_start->getPossibleStarters($this->object->items_obj) as $item_ref_id)
		{
			$tmp_obj =& ilObjectFactory::getInstanceByRefId($item_ref_id);

			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_STARTER",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("starter_row");
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->setVariable("CHECK_STARTER",ilUtil::formCheckbox(0,'starter[]',$item_ref_id));
			$this->tpl->setVariable("STARTER_TITLE",$tmp_obj->getTitle());
 			$this->tpl->parseCurrentBlock();
		}
	}

	function addStarterObject()
	{
		include_once './Modules/Course/classes/class.ilCourseStart.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['starter']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_select_one_object'));
			$this->selectStarterObject();

			return false;
		}

		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());
		$added = 0;
		foreach($_POST['starter'] as $item_ref_id)
		{
			if(!$crs_start->exists($item_ref_id))
			{
				++$added;
				$crs_start->add($item_ref_id);
			}
		}
		if($added)
		{
			ilUtil::sendInfo($this->lng->txt('crs_added_starters'));
			$this->listStructureObject();

			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt('crs_starters_already_assigned'));
			$this->selectStarterObject();

			return false;
		}
	}
	
	function editInfoObject()
	{
		include_once 'Modules/Course/classes/class.ilCourseFile.php';

		global $ilErr,$ilAccess;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		$this->setSubTabs('properties');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_info_settings');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.edit_info.html','Modules/Course');

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_GENERAL_INFO",$this->lng->txt('crs_general_info'));
		$this->tpl->setVariable("TXT_IMPORTANT",$this->lng->txt('crs_important_info'));
		$this->tpl->setVariable("TXT_SYLLABUS",$this->lng->txt('crs_syllabus'));
		$this->tpl->setVariable("TXT_DOWNLOAD",$this->lng->txt('crs_info_download'));
		$this->tpl->setVariable("TXT_FILENAME",$this->lng->txt('crs_file_name'));
		$this->tpl->setVariable("TXT_FILE",$this->lng->txt('crs_file'));
		$this->tpl->setVariable("TXT_FILE_NAME",$this->lng->txt('crs_filename'));
		$this->tpl->setVariable("TXT_FILESIZE",ilUtil::getFileSizeInfo());
		
		$this->tpl->setVariable("TXT_CONTACT",$this->lng->txt('crs_contact'));
		$this->tpl->setVariable("TXT_CONTACT_NAME",$this->lng->txt("crs_contact_name"));
		$this->tpl->setVariable("TXT_CONTACT_RESPONSIBILITY",$this->lng->txt("crs_contact_responsibility"));
		$this->tpl->setVariable("TXT_CONTACT_EMAIL",$this->lng->txt("crs_contact_email"));
		$this->tpl->setVariable("TXT_CONTACT_PHONE",$this->lng->txt("crs_contact_phone"));
		$this->tpl->setVariable("TXT_CONTACT_CONSULTATION",$this->lng->txt("crs_contact_consultation"));

		
		foreach($file_objs =& ilCourseFile::_readFilesByCourse($this->object->getId()) as $file_obj)
		{
			$this->tpl->setCurrentBlock("file");
			$this->tpl->setVariable("FILE_ID",$file_obj->getFileId());
			$this->tpl->setVariable("DEL_FILE",$file_obj->getFileName());
			$this->tpl->setVariable("TXT_DEL_FILE",$this->lng->txt('crs_delete_file'));
			$this->tpl->parseCurrentBlock();
		}
		if(count($file_objs))
		{
			$this->tpl->setCurrentBlock("files");
			$this->tpl->setVariable("TXT_EXISTING_FILES",$this->lng->txt('crs_existing_files'));
			$this->tpl->parseCurrentBlock();
		}


		$this->tpl->setVariable("IMPORTANT",$this->object->getImportantInformation());
		$this->tpl->setVariable("SYLLABUS",$this->object->getSyllabus());
		$this->tpl->setVariable("CONTACT_NAME",$this->object->getContactName());
		$this->tpl->setVariable("CONTACT_RESPONSIBILITY",$this->object->getContactResponsibility());
		$this->tpl->setVariable("CONTACT_PHONE",$this->object->getContactPhone());
		$this->tpl->setVariable("CONTACT_EMAIL",$this->object->getContactEmail());
		$this->tpl->setVariable("CONTACT_CONSULTATION",$this->object->getContactConsultation());

		$this->tpl->setVariable("TXT_BTN_UPDATE",$this->lng->txt('save'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));

		return true;
	}


	function updateInfoObject()
	{
		global $ilErr,$ilAccess;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		
		include_once 'Modules/Course/classes/class.ilCourseFile.php';
		$file_obj = new ilCourseFile();
		$file_obj->setCourseId($this->object->getId());
		$file_obj->setFileName(strlen($_POST['file_name']) ?
							   ilUtil::stripSlashes($_POST['file_name']) :
							   $_FILES['file']['name']);
		$file_obj->setFileSize($_FILES['file']['size']);
		$file_obj->setFileType($_FILES['file']['type']);
		$file_obj->setTemporaryName($_FILES['file']['tmp_name']);
		$file_obj->setErrorCode($_FILES['file']['error']);

		$this->object->setImportantInformation(ilUtil::stripSlashes($_POST['important']));
		$this->object->setSyllabus(ilUtil::stripSlashes($_POST['syllabus']));
		$this->object->setContactName(ilUtil::stripSlashes($_POST['contact_name']));
		$this->object->setContactResponsibility(ilUtil::stripSlashes($_POST['contact_responsibility']));
		$this->object->setContactPhone(ilUtil::stripSlashes($_POST['contact_phone']));
		$this->object->setContactEmail(ilUtil::stripSlashes($_POST['contact_email']));
		$this->object->setContactConsultation(ilUtil::stripSlashes($_POST['contact_consultation']));

		// Validate
		$ilErr->setMessage('');
		$file_obj->validate();
		$this->object->validateInfoSettings();

		if(strlen($ilErr->getMessage()))
		{
			ilUtil::sendInfo($ilErr->getMessage());
			$this->editInfoObject();
			return false;
		}
		$this->object->update();
		$file_obj->create();

		// Delete files
		if(count($_POST['del_files']))
		{
			foreach($file_objs =& ilCourseFile::_readFilesByCourse($this->object->getId()) as $file_obj)
			{
				if(in_array($file_obj->getFileId(),$_POST['del_files']))
				{
					$file_obj->delete();
				}
			}
		}
		ilUtil::sendInfo($this->lng->txt("crs_settings_saved"));
		$this->editInfoObject();
		return true;
	}

	function updateObject()
	{
		$this->object->setActivationType((int) $_POST['activation_type']);
		$this->object->setActivationStart($this->toUnix($_POST['activation_start'],$_POST['activation_start_time']));
		$this->object->setActivationEnd($this->toUnix($_POST['activation_end'],$_POST['activation_end_time']));
		$this->object->setSubscriptionLimitationType((int) $_POST['subscription_limitation_type']);
		$this->object->setSubscriptionType((int) $_POST['subscription_type']);
		$this->object->setSubscriptionPassword(ilUtil::stripSlashes($_POST['subscription_password']));
		$this->object->setSubscriptionStart($this->toUnix($_POST['subscription_start'],$_POST['subscription_start_time']));
		$this->object->setSubscriptionEnd($this->toUnix($_POST['subscription_end'],$_POST['subscription_end_time']));
		$this->object->setSubscriptionMaxMembers((int) $_POST['subscription_max']);
		$this->object->enableWaitingList((int) $_POST['waiting_list']);
		$this->object->setSubscriptionNotify((int) $_POST['subscription_notification']);
		$this->object->setViewMode((int) $_POST['view_mode']);

		if($this->object->getViewMode() == IL_CRS_VIEW_TIMING)
		{
			$this->object->setOrderType(IL_CRS_SORT_ACTIVATION);
		}
		else
		{
			$this->object->setOrderType((int) $_POST['order_type']);
		}
		$this->object->setArchiveStart($this->toUnix($_POST['archive_start'],$_POST['archive_start_time']));
		$this->object->setArchiveEnd($this->toUnix($_POST['archive_end'],$_POST['archive_end_time']));
		$this->object->setArchiveType($_POST['archive_type']);
		$this->object->setAboStatus((int) $_POST['abo']);
		$this->object->setShowMembers((int) $_POST['show_members']);

		if($this->object->validate())
		{
			$this->object->update();
			ilUtil::sendInfo($this->lng->txt('settings_saved'));
		}
		else
		{
			ilUtil::sendInfo($this->object->getMessage());
		}
		$this->editObject();
	}

	function editObject()
	{
		global $ilAccess,$ilErr;

		if(!$ilAccess->checkAccess('write','',$this->ref_id))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		$this->setSubTabs('properties');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_settings');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_settings.html','Modules/Course');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// Visibility
		$this->tpl->setVariable("TXT_VISIBILITY",$this->lng->txt('crs_visibility'));
		$this->tpl->setVariable("TXT_VISIBILITY_UNVISIBLE",$this->lng->txt('crs_visibility_unvisible'));
		$this->tpl->setVariable("TXT_VISIBILITY_LIMITLESS",$this->lng->txt('crs_visibility_limitless'));
		$this->tpl->setVariable("TXT_VISIBILITY_UNTIL",$this->lng->txt('crs_visibility_until'));
		$this->tpl->setVariable("ACTIVATION_UNV_INFO",$this->lng->txt('crs_availability_unvisible_info'));
		$this->tpl->setVariable("ACTIVATION_UNL_INFO",$this->lng->txt('crs_availability_limitless_info'));
		$this->tpl->setVariable("ACTIVATION_LIM_INFO",$this->lng->txt('crs_availability_until_info'));

		$this->tpl->setVariable("ACTIVATION_OFFLINE",
								ilUtil::formRadioButton(($this->object->getActivationType() == IL_CRS_ACTIVATION_OFFLINE) ? 1 : 0,
														'activation_type',
														IL_CRS_ACTIVATION_OFFLINE));
		
		$this->tpl->setVariable("ACTIVATION_UNLIMITED",
								ilUtil::formRadioButton(($this->object->getActivationType() == IL_CRS_ACTIVATION_UNLIMITED) ? 1 : 0,
														'activation_type',
														IL_CRS_ACTIVATION_UNLIMITED));
		
		$this->tpl->setVariable("ACTIVATION_UNTIL",
								ilUtil::formRadioButton(($this->object->getActivationType() == IL_CRS_ACTIVATION_LIMITED) ? 1 : 0,
														'activation_type',
														IL_CRS_ACTIVATION_LIMITED));
		$this->tpl->setVariable("TXT_BEGIN",$this->lng->txt('crs_start'));
		$this->tpl->setVariable("TXT_END",$this->lng->txt('crs_end'));
		$this->tpl->setVariable("TXT_TIME",$this->lng->txt('time'));
		
		$date = $this->__prepareDateSelect($this->object->getActivationStart());
		$this->tpl->setVariable("ACTIVATION_START_DATE_SELECT",
								ilUtil::makeDateSelect('activation_start',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($this->object->getActivationStart());
		$this->tpl->setVariable("ACTIVATION_START_TIME_SELECT",
								ilUtil::makeTimeSelect('activation_start_time',true,$date['h'],$date['m'],0,false));

		$date = $this->__prepareDateSelect($this->object->getActivationEnd());
		$this->tpl->setVariable("ACTIVATION_END_DATE_SELECT",
								ilUtil::makeDateSelect('activation_end',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($this->object->getActivationEnd());
		$this->tpl->setVariable("ACTIVATION_END_TIME_SELECT",
								ilUtil::makeTimeSelect('activation_end_time',true,$date['h'],$date['m'],0,false));

		// Registration
		$this->tpl->setVariable("TXT_REGISTRATION_DEACTIVATED",$this->lng->txt('crs_reg_deactivated'));
		$this->tpl->setVariable("TXT_REGISTRATION_UNLIMITED",$this->lng->txt('crs_registration_unlimited'));
		$this->tpl->setVariable("TXT_REGISTRATION_LIMITED",$this->lng->txt('crs_registration_limited'));
		$this->tpl->setVariable("TXT_REGISTRATION_TYPE",$this->lng->txt('crs_registration_type'));

		$this->tpl->setVariable("REG_DEAC_INFO",$this->lng->txt('crs_registration_deactivated'));
		$this->tpl->setVariable("REG_UNLIM_INFO",$this->lng->txt('crs_reg_unlim_info'));
		$this->tpl->setVariable("REG_LIM_INFO",$this->lng->txt('crs_reg_lim_info'));
		$this->tpl->setVariable("REG_MAX_INFO",$this->lng->txt('crs_reg_max_info'));
		$this->tpl->setVariable("REG_NOTY_INFO",$this->lng->txt('crs_reg_notify_info'));
		$this->tpl->setVariable("REG_WAIT_INFO",$this->lng->txt('crs_wait_info'));
		$this->tpl->setVariable('REG_TYPE_INFO',$this->lng->txt('crs_reg_type_info'));
		

		$this->tpl->setVariable("TXT_SUBSCRIPTION",$this->lng->txt("crs_reg"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_UNLIMITED",$this->lng->txt("crs_unlimited"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_START",$this->lng->txt("crs_start"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_END",$this->lng->txt("crs_end"));

		$this->tpl->setVariable("TXT_SUBSCRIPTION_OPTIONS",$this->lng->txt("crs_subscription_type"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_MAX_MEMBERS",$this->lng->txt("crs_subscription_max_members"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_NOTIFY",$this->lng->txt("crs_subscription_notify"));
		$this->tpl->setVariable("TXT_DEACTIVATED",$this->lng->txt("crs_subscription_options_deactivated"));
		$this->tpl->setVariable("TXT_CONFIRMATION",$this->lng->txt("crs_subscription_options_confirmation"));
		$this->tpl->setVariable("TXT_DIRECT",$this->lng->txt("crs_subscription_options_direct"));
		$this->tpl->setVariable("TXT_PASSWORD",$this->lng->txt("crs_subscription_options_password"));
		$this->tpl->setVariable("TXT_WAIT",$this->lng->txt('crs_waiting_list'));
		$this->tpl->setVariable("TXT_NOTIFY",$this->lng->txt('crs_notification'));

		$this->tpl->setVariable("REGISTRATION_DEACTIVATED",
								ilUtil::formRadioButton(($this->object->getSubscriptionLimitationType() == 
														 IL_CRS_SUBSCRIPTION_DEACTIVATED) ? 1 : 0,
														'subscription_limitation_type',
														IL_CRS_SUBSCRIPTION_DEACTIVATED));
		
		$this->tpl->setVariable("REGISTRATION_UNLIMITED",
								ilUtil::formRadioButton(($this->object->getSubscriptionLimitationType() == 
														 IL_CRS_SUBSCRIPTION_UNLIMITED) ? 1 : 0,
														'subscription_limitation_type',
														IL_CRS_SUBSCRIPTION_UNLIMITED));
		
		$this->tpl->setVariable("REGISTRATION_LIMITED",
								ilUtil::formRadioButton(($this->object->getSubscriptionLimitationType() == 
														 IL_CRS_SUBSCRIPTION_LIMITED) ? 1 : 0,
														'subscription_limitation_type',
														IL_CRS_SUBSCRIPTION_LIMITED));

		$this->tpl->setVariable("RADIO_SUB_CONFIRMATION",
								ilUtil::formRadioButton(($this->object->getSubscriptionType() == 
														 IL_CRS_SUBSCRIPTION_CONFIRMATION) ? 1 : 0,
														'subscription_type',
														IL_CRS_SUBSCRIPTION_CONFIRMATION));
		
		$this->tpl->setVariable("RADIO_SUB_DIRECT",
								ilUtil::formRadioButton(($this->object->getSubscriptionType() == 
														 IL_CRS_SUBSCRIPTION_DIRECT) ? 1 : 0,
														'subscription_type',
														IL_CRS_SUBSCRIPTION_DIRECT));
		
		$this->tpl->setVariable("RADIO_SUB_PASSWORD",
								ilUtil::formRadioButton(($this->object->getSubscriptionType() == 
														 IL_CRS_SUBSCRIPTION_PASSWORD) ? 1 : 0,
														'subscription_type',
														IL_CRS_SUBSCRIPTION_PASSWORD));
		$this->tpl->setVariable("SUBSCRIPTION_PASSWORD",$this->object->getSubscriptionPassword());

		$date = $this->__prepareDateSelect($this->object->getSubscriptionStart());
		$this->tpl->setVariable("SUBSCRIPTION_START_DATE_SELECT",
								ilUtil::makeDateSelect('subscription_start',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($this->object->getSubscriptionStart());
		$this->tpl->setVariable("SUBSCRIPTION_START_TIME_SELECT",
								ilUtil::makeTimeSelect('subscription_start_time',true,$date['h'],$date['m'],0,false));

		$date = $this->__prepareDateSelect($this->object->getSubscriptionEnd());
		$this->tpl->setVariable("SUBSCRIPTION_END_DATE_SELECT",
								ilUtil::makeDateSelect('subscription_end',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($this->object->getSubscriptionEnd());
		$this->tpl->setVariable("SUBSCRIPTION_END_TIME_SELECT",
								ilUtil::makeTimeSelect('subscription_end_time',true,$date['h'],$date['m'],0,false));

		$this->tpl->setVariable("SUBSCRIPTION_MAX_MEMBERS",$this->object->getSubscriptionMaxMembers());
		$this->tpl->setVariable("CHECK_WAIT",ilUtil::formCheckbox($this->object->enabledWaitingList(),
																  'waiting_list',
																  1));
		$this->tpl->setVariable("CHECK_SUBSCRIPTION_NOTIFY",ilUtil::formCheckbox($this->object->getSubscriptionNotify(),
																				 'subscription_notification',
																				 1));
		
		// Viewmode
		$this->tpl->setVariable("TXT_VIEWMODE",$this->lng->txt('crs_view_mode'));
		$this->tpl->setVariable("TXT_STANDARD_VIEW",$this->lng->txt('crs_view_standard'));
		$this->tpl->setVariable("TXT_OBJ_VIEW",$this->lng->txt('crs_view_objective'));
		$this->tpl->setVariable("TXT_TIMING_VIEW",$this->lng->txt('crs_view_timing'));
		$this->tpl->setVariable("TXT_ARCHIVE_VIEW",$this->lng->txt('crs_view_archive'));
		$this->tpl->setVariable("TXT_DOWNLOAD",$this->lng->txt('crs_archive_download'));

		$this->tpl->setVariable("VIEW_STANDARD_INFO",$this->lng->txt('crs_view_info_standard'));
		$this->tpl->setVariable("VIEW_OBJECTIVE_INFO",$this->lng->txt('crs_view_info_objective'));
		$this->tpl->setVariable("VIEW_TIMING_INFO",$this->lng->txt('crs_view_info_timing'));
		$this->tpl->setVariable("VIEW_ARCHIVE_INFO",$this->lng->txt('crs_archive_info'));

		$this->tpl->setVariable("VIEW_STANDARD",ilUtil::formRadioButton(
									($this->object->getViewMode() == IL_CRS_VIEW_STANDARD) ? true : false,
									'view_mode',
									IL_CRS_VIEW_STANDARD));
		$this->tpl->setVariable("VIEW_OBJECTIVE",ilUtil::formRadioButton(
									($this->object->getViewMode() == IL_CRS_VIEW_OBJECTIVE) ? true : false,
									'view_mode',
									IL_CRS_VIEW_OBJECTIVE));
		$this->tpl->setVariable("VIEW_TIMING",ilUtil::formRadioButton(
									($this->object->getViewMode() == IL_CRS_VIEW_TIMING) ? true : false,
									'view_mode',
									IL_CRS_VIEW_TIMING));
		$this->tpl->setVariable("VIEW_ARCHIVE",ilUtil::formRadioButton(
									($this->object->getViewMode() == IL_CRS_VIEW_ARCHIVE) ? true : false,
									'view_mode',
									IL_CRS_VIEW_ARCHIVE));

		$date = $this->__prepareDateSelect($this->object->getArchiveStart());
		$this->tpl->setVariable("ARCHIVE_START_DATE_SELECT",
								ilUtil::makeDateSelect('archive_start',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($this->object->getArchiveStart());
		$this->tpl->setVariable("ARCHIVE_START_TIME_SELECT",
								ilUtil::makeTimeSelect('archive_start_time',true,$date['h'],$date['m'],0,false));

		$date = $this->__prepareDateSelect($this->object->getArchiveEnd());
		$this->tpl->setVariable("ARCHIVE_END_DATE_SELECT",
								ilUtil::makeDateSelect('archive_end',$date['y'],$date['m'],$date['d'],date('Y',time())));

		$date = $this->__prepareTimeSelect($this->object->getArchiveEnd());
		$this->tpl->setVariable("ARCHIVE_END_TIME_SELECT",
								ilUtil::makeTimeSelect('archive_end_time',true,$date['h'],$date['m'],0,false));

		$this->tpl->setVariable("CHECK_ARCHIVE_DOWNLOAD",ilUtil::formCheckbox(
									$this->object->getArchiveType() == IL_CRS_ARCHIVE_DOWNLOAD ? true : false,
									'archive_type',
									IL_CRS_ARCHIVE_DOWNLOAD));

		// Sorting
		$this->tpl->setVariable("TXT_SORT",$this->lng->txt('crs_sortorder_abo'));
		$this->tpl->setVariable("TXT_MANUAL",$this->lng->txt("crs_sort_manual"));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("crs_sort_title"));
		$this->tpl->setVariable("TXT_SORT_ACTIVATION",$this->lng->txt("crs_sort_activation"));

		$this->tpl->setVariable("SORT_TITLE",ilUtil::formRadioButton(
									$this->object->getOrderType() == IL_CRS_SORT_TITLE ? true : false,
									'order_type',
									IL_CRS_SORT_TITLE));
		$this->tpl->setVariable("SORT_MANUAL",ilUtil::formRadioButton(
									$this->object->getOrderType() == IL_CRS_SORT_MANUAL ? true : false,
									'order_type',
									IL_CRS_SORT_MANUAL));
		$this->tpl->setVariable("SORT_TIMING",ilUtil::formRadioButton(
									$this->object->getOrderType() == IL_CRS_SORT_ACTIVATION ? true : false,
									'order_type',
									IL_CRS_SORT_ACTIVATION));

		$this->tpl->setVariable("SORT_TITLE_INFO",$this->lng->txt('crs_sort_title_info'));
		$this->tpl->setVariable("SORT_MANUAL_INFO",$this->lng->txt('crs_sort_manual_info'));
		$this->tpl->setVariable("SORT_TIMING_INFO",$this->lng->txt('crs_sort_timing_info'));

		// Further settings
		$this->tpl->setVariable("TXT_FURTHER_SETTINGS",$this->lng->txt('crs_further_settings'));
		$this->tpl->setVariable("TXT_ADD_REMOVE_DESKTOP_ITEMS",$this->lng->txt('crs_add_remove_from_desktop'));
		$this->tpl->setVariable("TXT_ADD_DESKTOP_INFO",$this->lng->txt('crs_add_remove_from_desktop_info'));

		$this->tpl->setVariable("CHECK_DESKTOP",ilUtil::formCheckbox($this->object->getAboStatus(),
																	 'abo',
																	 1));
		
		$this->tpl->setVariable("TXT_SHOW_MEMBERS",$this->lng->txt('crs_show_members'));
		$this->tpl->setVariable("TXT_SHOW_MEMBERS_INFO",$this->lng->txt('crs_show_members_info'));

		$this->tpl->setVariable("SHOW_MEMBERS",ilUtil::formCheckbox($this->object->getShowMembers(),
																	 'show_members',
																	 1));

		// Footer
		$this->tpl->setVariable("TXT_BTN_UPDATE",$this->lng->txt('save'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
	}

				

	/**
	* edit container icons
	*/
	function editCourseIconsObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_edit_icons.html",'Modules/Course');
		$this->showCustomIconsEditing();
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
		$this->tpl->setVariable("CMD_CANCEL", "cancel");
		$this->tpl->setVariable("CMD_SUBMIT", "updateCourseIcons");
		$this->tpl->parseCurrentBlock();
	}

	function sendFileObject()
	{
		include_once 'Modules/Course/classes/class.ilCourseFile.php';
		$file = new ilCourseFile((int) $_GET['file_id']);
		
		ilUtil::deliverFile($file->getAbsolutePath(),$file->getFileName(),$file->getFileType());
		return true;
	}
	
	/**
	* update container icons
	*/
	function updateCourseIconsObject()
	{
		global $rbacsystem;
		
		if (!$rbacsystem->checkAccess("write",$_GET["ref_id"]) )
		{
			$this->ilErr->raiseError($this->lng->txt("permission_denied"),$this->ilErr->MESSAGE);
		}
		
		//save custom icons
		if ($this->ilias->getSetting("custom_icons"))
		{
			$this->object->saveIcons($_FILES["cont_big_icon"],
				$_FILES["cont_small_icon"], $_FILES["cont_tiny_icon"]);
		}

		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this,"editCourseIcons");

	}


	/**
	* set sub tabs
	*/
	function setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser,$ilAccess;
		
		switch ($a_tab)
		{
			case "properties":
				$this->tabs_gui->addSubTabTarget("crs_settings",
												 $this->ctrl->getLinkTarget($this,'edit'),
												 "edit", get_class($this));

				$this->tabs_gui->addSubTabTarget("crs_info_settings",
												 $this->ctrl->getLinkTarget($this,'editInfo'),
												 "editInfo", get_class($this));

				$this->tabs_gui->addSubTabTarget("preconditions",
												 $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
												 "", "ilConditionHandlerInterface");
				$this->tabs_gui->addSubTabTarget("crs_start_objects",
												 $this->ctrl->getLinkTarget($this,'listStructure'),
												 "listStructure", get_class($this));
				$this->tabs_gui->addSubTabTarget('groupings',
												 $this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','listGroupings'),
												 'listGroupings',
												 get_class($this));

				// custom icon
				if ($this->ilias->getSetting("custom_icons"))
				{
					$this->tabs_gui->addSubTabTarget("icon_settings",
													 $this->ctrl->getLinkTarget($this,'editCourseIcons'),
													 "editCourseIcons", get_class($this));
				}
				
				// map settings
				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
				if (ilGoogleMapUtil::isActivated())
				{
					$this->tabs_gui->addSubTabTarget("crs_map_settings",
						 $this->ctrl->getLinkTarget($this,'editMapSettings'),
						 "editMapSettings", get_class($this));
				}

				
				include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
				include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
				$privacy = ilPrivacySettings::_getInstance();
				if($rbacsystem->checkAccess('export_member_data',$privacy->getPrivacySettingsRefId()) and
					(($privacy->enabledExport() and $privacy->confirmationRequired()) or
						ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())))
				{
					$this->tabs_gui->addSubTabTarget('user_fields',
													$this->ctrl->getLinkTargetByClass('ilcourseuserfieldsgui'),
													'',
													'ilcourseuserfieldsgui');
				}
				break;
				
			case "item_activation":
				$this->tabs_gui->addSubTabTarget("activation",
												 $this->ctrl->getLinkTargetByClass('ilCourseItemAdministrationGUI','edit'),
												 "edit", get_class($this));
				$this->ctrl->setParameterByClass('ilconditionhandlerinterface','item_id',(int) $_GET['item_id']);
				$this->tabs_gui->addSubTabTarget("preconditions",
												 $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
												 "", "ilConditionHandlerInterface");
				break;
				
			case 'members':
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("crs_member_administration",
													 $this->ctrl->getLinkTarget($this,'members'),
													 "members", get_class($this));
				}
				$this->tabs_gui->addSubTabTarget("crs_members_gallery",
												 $this->ctrl->getLinkTarget($this,'membersGallery'),
												 "membersGallery", get_class($this));
				
				// members map
				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
				if (ilGoogleMapUtil::isActivated() && $this->object->getEnableCourseMap())
				{
					$this->tabs_gui->addSubTabTarget("crs_members_map",
						$this->ctrl->getLinkTarget($this,'membersMap'),
						"membersMap", get_class($this));
				}

				
				include_once 'Services/Mail/classes/class.ilMail.php';
				$mail =& new ilMail($ilUser->getId());
				if($rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId()))
				{
					$this->tabs_gui->addSubTabTarget("mail_members",
													 $this->ctrl->getLinkTarget($this,'mailMembers'),
													 "mailMembers", get_class($this));
				}
				
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("events",
													 $this->ctrl->getLinkTargetByClass('ileventadministrationgui','eventsList'),
													 "", 'ileventadministrationgui');
				}

				include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
				$privacy = ilPrivacySettings::_getInstance();
				if($privacy->enabledExport() and $rbacsystem->checkAccess('export_member_data',$privacy->getPrivacySettingsRefId()))
				{
					$this->tabs_gui->addSubTabTarget('export_members',
													$this->ctrl->getLinkTargetByClass('ilmemberexportgui','show'));
				}
				
				break;

				
		}
	}

	/**
	* remove small icon
	*
	* @access	public
	*/
	function removeSmallIconObject()
	{
		$this->object->removeSmallIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editCourseIcons"));
	}

	/**
	* remove big icon
	*
	* @access	public
	*/
	function removeBigIconObject()
	{
		$this->object->removeBigIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editCourseIcons"));
	}


	/**
	* remove small icon
	*
	* @access	public
	*/
	function removeTinyIconObject()
	{
		$this->object->removeTinyIcon();
		ilUtil::redirect($this->ctrl->getLinkTarget($this, "editCourseIcons"));
	}

	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin,$ilUser;

		$newObj =& parent::saveObject();
		$newObj->initDefaultRoles();
		$newObj->initCourseMemberObject();
		$newObj->members_obj->add($ilUser->getId(),IL_CRS_ADMIN);
		
		// always send a message
		ilUtil::sendInfo($this->lng->txt("crs_added"),true);
		
		$this->ctrl->setParameter($this, "ref_id", $newObj->getRefId());
		ilUtil::redirect($this->getReturnLocation("save",
			$this->ctrl->getLinkTarget($this, "edit")));
		//ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}



	
	function downloadArchivesObject()
	{
		global $rbacsystem;

		$_POST["archives"] = $_POST["archives"] ? $_POST["archives"] : array();

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['archives']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_no_archive_selected'));
			$this->archiveObject();

			return false;
		}
		if(count($_POST['archives']) > 1)
		{
			ilUtil::sendInfo($this->lng->txt('crs_select_one_archive'));
			$this->archiveObject();

			return false;
		}

		$this->object->initCourseArchiveObject();
		
		$abs_path = $this->object->archives_obj->getArchiveFile((int) $_POST['archives'][0]);
		$basename = basename($abs_path);

		ilUtil::deliverFile($abs_path,$basename);
	}


	function __readMemberData($ids,$role = 'admin')
	{
		if($this->show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = ilLPStatusWrapper::_getCompleted($this->object->getId());
			$in_progress = ilLPStatusWrapper::_getInProgress($this->object->getId());
			$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->object->getId());
		}

		foreach($ids as $usr_id)
		{
			switch($role)
			{
				case 'tutor':
					if($this->object->members_obj->isAdmin($usr_id))
					{
						continue(2);
					}
					break;
				case 'member':
					if($this->object->members_obj->isTutor($usr_id) or $this->object->members_obj->isAdmin($usr_id))
					{
						continue(2);
					}
					break;
			}

			$name = ilObjUser::_lookupName($usr_id);
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['lastname'] = $name['lastname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);
			$tmp_data['passed'] = $this->object->members_obj->hasPassed($usr_id) ? 1 : 0;
			$tmp_data['notification'] = $this->object->members_obj->isNotificationEnabled($usr_id) ? 1 : 0;
			$tmp_data['blocked'] = $this->object->members_obj->isBlocked($usr_id) ? 1 : 0;
			$tmp_data['usr_id'] = $usr_id;
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);

			if($this->show_tracking)
			{
				if(in_array($usr_id,$completed))
				{
					$tmp_data['progress'] = $this->lng->txt('trac_completed');
				}
				elseif(in_array($usr_id,$in_progress))
				{
					$tmp_data['progress'] = $this->lng->txt('trac_in_progress');
				}
				else
				{
					$tmp_data['progress'] = $this->lng->txt('trac_not_attempted');
				}
			}
					
			$members[] = $tmp_data;
		}
		return $members ? $members : array();
	}

	function membersObject()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		include_once './Modules/Course/classes/class.ilCourseItems.php';
		
		$_SESSION['crs_print_sort'] = $_GET['sort_by'] ? $_GET['sort_by'] : 'lastname';
		$_SESSION['crs_print_order'] = $_GET['sort_order'] ? $_GET['sort_order'] : 'asc';
		
		$this->lng->loadLanguageModule('trac');
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and ilObjUserTracking::_enabledUserRelatedData());

		$_SESSION['crs_admin_hide'] = isset($_GET['admin_show_details']) ? !$_GET['admin_show_details'] : 
			$_SESSION['crs_admin_hide'];
		$_SESSION['crs_tutor_hide'] = isset($_GET['tutor_show_details']) ? !$_GET['tutor_show_details'] : 
			$_SESSION['crs_tutor_hide'];
		$_SESSION['crs_member_hide'] = isset($_GET['member_show_details']) ? !$_GET['member_show_details'] : 
			$_SESSION['crs_member_hide'];

		global $ilAccess,$ilErr,$ilUser;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$this->timings_enabled = (ilCourseItems::_hasChangeableTimings($this->object->getRefId()) and 
			($this->object->getViewMode() == IL_CRS_VIEW_TIMING));

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');

		// Waitinglist
		$this->__showWaitingList();
		$this->__showSubscribers();

		// add members
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt("crs_add_member"));
		$this->tpl->parseCurrentBlock();
		
		// print
		$this->__showButton("printMembers",$this->lng->txt("crs_print_list"),"target=\"_blank\"");

		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.edit_members.html','Modules/Course');
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_usr.gif'));
		$this->tpl->setVariable("HEADER_ALT",$this->lng->txt('crs_members_table'));
		$this->tpl->setVariable("MEMBER_TABLE_TITLE",$this->lng->txt('crs_members_table'));


		////////////////////////////////////////////////////////
		// Admins
		////////////////////////////////////////////////////////
		$this->__renderAdminsTable();
		
		////////////////////////////////////////////////////////
		// Tutors
		////////////////////////////////////////////////////////
		$this->__renderTutorsTable();
			
		////////////////////////////////////////////////////////
		// Members
		////////////////////////////////////////////////////////
		$this->__renderMembersTable();

		$actions = array("deleteMembersObject"	=> $this->lng->txt("crs_delete_member"),
						 "sendMail" => $this->lng->txt('crs_mem_send_mail'));
		$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$this->tpl->setVariable("ARROW_DOWNRIGHT",ilUtil::getImagePath("arrow_downright.gif"));
		$this->tpl->setVariable("TXT_BTN_EXECUTE",$this->lng->txt('execute'));
		$this->tpl->setVariable("TXT_BTN_UPDATE",$this->lng->txt('save'));

	}

	function updateMembersObject()
	{
		global $ilAccess,$ilErr,$ilUser,$rbacadmin;

		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}
		if(!is_array($_POST['visible_member_ids']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$blocked = is_array($_POST['blocked']) ? $_POST['blocked'] : array();
		$notification = is_array($_POST['notification']) ? $_POST['notification'] : array();

		foreach($_POST['visible_member_ids'] as $member_id)
		{
			$this->object->members_obj->updatePassed($member_id,in_array($member_id,$passed));
			if($this->object->members_obj->isAdmin($member_id) or $this->object->members_obj->isTutor($member_id))
			{
				$this->object->members_obj->updateNotification($member_id,in_array($member_id,$notification));
				$this->object->members_obj->updateBlocked($member_id,false);
			}
			elseif($this->object->members_obj->isMember($member_id))
			{
				$this->object->members_obj->updateNotification($member_id,false);
				$this->object->members_obj->updateBlocked($member_id,in_array($member_id,$blocked));
			}
		}
			

		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->membersObject();
	}

	function __renderAdminsTable()
	{
		$this->tpl->setVariable("TXT_ADMINISTRATORS",$this->lng->txt('crs_administrators'));

		if($_SESSION['crs_admin_hide'])
		{
			$this->tpl->setVariable("ADMIN_HIDE_TEXT",$this->lng->txt('show_details'));
			$this->ctrl->setParameter($this,'admin_show_details',1);
			$this->tpl->setVariable("ADMIN_HIDE",$this->ctrl->getLinkTarget($this,'members'));
			$this->ctrl->clearParameters($this);
			return true;
		}
		
		$this->tpl->setVariable("ADMIN_HIDE_TEXT",$this->lng->txt('hide_details'));
		$this->ctrl->setParameter($this,'admin_show_details',0);
		$this->tpl->setVariable("ADMIN_HIDE",$this->ctrl->getLinkTarget($this,'members'));
		$this->ctrl->clearParameters($this);
			

		$admin_tpl = new ilTemplate('tpl.table.html',true,true);
		$admin_tpl->addBlockfile('TBL_CONTENT','tbl_content','tpl.member_admin_row.html','Modules/Course');


		$all_admins_data = $this->__readMemberData($admins = $this->object->members_obj->getAdmins(),'admin');
		$sorted_admins = ilUtil::sortArray($all_admins_data,$_GET["admin_sort_by"],$_GET["admin_sort_order"]);
		$sliced_admins = array_slice($sorted_admins,$_GET['admin_offset'],$_GET['limit']); 
		$counter = 0;
		foreach($sliced_admins as $admin)
		{
			$admin_tpl->setCurrentBlock("link");
			$this->ctrl->setParameter($this,'member_id',$admin['usr_id']);
			$admin_tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTarget($this,'editMember'));
			$admin_tpl->setVariable("LINK_TXT",$this->lng->txt('edit'));
			$admin_tpl->parseCurrentBlock();
			$this->ctrl->clearParameters($this);

			if($this->timings_enabled)
			{
				$admin_tpl->setCurrentBlock("link");
				$this->ctrl->setParameterByClass('ilcoursecontentgui','member_id',$admin['usr_id']);
				$admin_tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','showUserTimings'));
				$admin_tpl->setVariable("LINK_TXT",$this->lng->txt('timings_timings'));
				$admin_tpl->parseCurrentBlock();
				$this->ctrl->clearParametersByClass('ilcoursecontentgui');
			}
				

			$admin_tpl->setCurrentBlock("tbl_content");

			if($admin['passed'])
			{
				$admin_tpl->setVariable("CHECKED_PASSED",'checked="checked"');
			}
			if($admin['notification'])
			{
				$admin_tpl->setVariable("CHECKED_NOTIFICATION",'checked="checked"');
			}
			if($this->show_tracking)
			{
				$admin_tpl->setVariable("VAL_PROGRESS",$admin['progress']);
			}

			$admin_tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$admin_tpl->setVariable("USER_ID",$admin['usr_id']);
			$admin_tpl->setVariable("LASTNAME",$admin['lastname']);
			$admin_tpl->setVariable("FIRSTNAME",$admin['firstname']);
			$admin_tpl->setVariable("LOGIN",$admin['login']);
			$admin_tpl->parseCurrentBlock();
		}
		$admin_tpl->setCurrentBlock("select_row");
		$admin_tpl->setVariable("ROWCLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$admin_tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$admin_tpl->parseCurrentBlock();

		$tbl = new ilTableGUI($admins,false);
		$tbl->setTemplate($admin_tpl);
		
		if($this->show_tracking)
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt('name'),
									   $this->lng->txt('login'),
									   $this->lng->txt('learning_progress'),
									   $this->lng->txt('crs_passed'),
									   $this->lng->txt('crs_notification'),''));
			$tbl->setHeaderVars(array("",
									  "lastname",
									  "login",
									  "progress",
									  "passed",
									  "notification",''),
								$this->ctrl->getParameterArray($this,'members'));
		}
		else
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt('name'),
									   $this->lng->txt('login'),
									   $this->lng->txt('crs_passed'),
									   $this->lng->txt('crs_notification'),''));
			$tbl->setHeaderVars(array("",
									  "lastname",
									  "login",
									  "passed",
									  "notification",''),
								$this->ctrl->getParameterArray($this,'members'));
		}		
		$tbl->setOrderColumn($_GET["admin_sort_by"]);
		$tbl->setOrderDirection($_GET["admin_sort_order"]);
		$tbl->setOffset($_GET["admin_offset"]);
		$tbl->setMaxCount(count($admins));
		$tbl->setPrefix('admin_');
		$tbl->disable('table');
		$tbl->disable('form');
		$tbl->disable('title');
		$tbl->disable('icon');
		$tbl->disable('content');

		$this->tpl->setVariable("ADMINISTRATORS",$tbl->render());
	}

	function __renderTutorsTable()
	{
		$all_tutors_data = $this->__readMemberData($tutors = $this->object->members_obj->getTutors(),'tutor');
		if(!count($all_tutors_data))
		{
			return false;
		}
		
		$this->tpl->setVariable("TXT_TUTORS",$this->lng->txt('crs_tutors'));

		if($_SESSION['crs_tutor_hide'])
		{
			$this->tpl->setVariable("TUTOR_HIDE_TEXT",$this->lng->txt('show_details'));
			$this->ctrl->setParameter($this,'tutor_show_details',1);
			$this->tpl->setVariable("TUTOR_HIDE",$this->ctrl->getLinkTarget($this,'members'));
			$this->ctrl->clearParameters($this);
			return true;
		}
		
		$this->tpl->setVariable("TUTOR_HIDE_TEXT",$this->lng->txt('hide_details'));
		$this->ctrl->setParameter($this,'tutor_show_details',0);
		$this->tpl->setVariable("TUTOR_HIDE",$this->ctrl->getLinkTarget($this,'members'));
		$this->ctrl->clearParameters($this);

		$tutor_tpl = new ilTemplate('tpl.table.html',true,true);
		$tutor_tpl->addBlockfile('TBL_CONTENT','tbl_content','tpl.member_tutor_row.html','Modules/Course');


		$sorted_tutors = ilUtil::sortArray($all_tutors_data,$_GET["tutor_sort_by"],$_GET["tutor_sort_order"]);
		$sliced_tutors = array_slice($sorted_tutors,$_GET['tutor_offset'],$_GET['limit']); 
		$counter = 0;
		foreach($sliced_tutors as $tutor)
		{
			$tutor_tpl->setCurrentBlock("link");
			$this->ctrl->setParameter($this,'member_id',$tutor['usr_id']);
			$tutor_tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTarget($this,'editMember'));
			$tutor_tpl->setVariable("LINK_TXT",$this->lng->txt('edit'));
			$tutor_tpl->parseCurrentBlock();
			$this->ctrl->clearParameters($this);

			if($this->timings_enabled)
			{
				$tutor_tpl->setCurrentBlock("link");
				$this->ctrl->setParameterByClass('ilcoursecontentgui','member_id',$tutor['usr_id']);
				$tutor_tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','showUserTimings'));
				$tutor_tpl->setVariable("LINK_TXT",$this->lng->txt('timings_timings'));
				$tutor_tpl->parseCurrentBlock();
				$this->ctrl->clearParametersByClass('ilcoursecontentgui');
			}

			$tutor_tpl->setCurrentBlock("tbl_content");

			if($tutor['passed'])
			{
				$tutor_tpl->setVariable("CHECKED_PASSED",'checked="checked"');
			}
			if($tutor['notification'])
			{
				$tutor_tpl->setVariable("CHECKED_NOTIFICATION",'checked="checked"');
			}
			if($this->show_tracking)
			{
				$tutor_tpl->setVariable("VAL_PROGRESS",$tutor['progress']);
			}

			$tutor_tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$tutor_tpl->setVariable("USER_ID",$tutor['usr_id']);
			$tutor_tpl->setVariable("LASTNAME",$tutor['lastname']);
			$tutor_tpl->setVariable("FIRSTNAME",$tutor['firstname']);
			$tutor_tpl->setVariable("LOGIN",$tutor['login']);
			$tutor_tpl->parseCurrentBlock();
		}

		$tutor_tpl->setCurrentBlock("select_row");
		$tutor_tpl->setVariable("ROWCLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$tutor_tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$tutor_tpl->parseCurrentBlock();


		$tbl = new ilTableGUI($tutors,false);
		$tbl->setTemplate($tutor_tpl);
		
		if($this->show_tracking)
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt('name'),
									   $this->lng->txt('login'),
									   $this->lng->txt('learning_progress'),
									   $this->lng->txt('crs_passed'),
									   $this->lng->txt('crs_notification'),''));
			$tbl->setHeaderVars(array("",
									  "lastname",
									  "login",
									  "progress",
									  "passed",
									  "notification",''),
								$this->ctrl->getParameterArray($this,'members'));
		}
		else
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt('name'),
									   $this->lng->txt('login'),
									   $this->lng->txt('crs_passed'),
									   $this->lng->txt('crs_notification'),''));
			$tbl->setHeaderVars(array("",
									  "lastname",
									  "login",
									  "passed",
									  "notification",''),
								$this->ctrl->getParameterArray($this,'members'));
		}		
		$tbl->setOrderColumn($_GET["tutor_sort_by"]);
		$tbl->setOrderDirection($_GET["tutor_sort_order"]);
		$tbl->setOffset($_GET["tutor_offset"]);
		$tbl->setMaxCount(count($tutors));
		$tbl->setPrefix('tutor_');
		$tbl->disable('table');
		$tbl->disable('form');
		$tbl->disable('title');
		$tbl->disable('icon');
		$tbl->disable('content');

		$this->tpl->setVariable("TUTORS",$tbl->render());
	}
	function __renderMembersTable()
	{
		$all_members_data = $this->__readMemberData($members = $this->object->members_obj->getMembers(),'member');
		if(!count($all_members_data))
		{
			return false;
		}
		$this->tpl->setVariable("TXT_MEMBERS",$this->lng->txt('crs_members'));

		if($_SESSION['crs_member_hide'])
		{
			$this->tpl->setVariable("MEMBER_HIDE_TEXT",$this->lng->txt('show_details'));
			$this->ctrl->setParameter($this,'member_show_details',1);
			$this->tpl->setVariable("MEMBER_HIDE",$this->ctrl->getLinkTarget($this,'members'));
			$this->ctrl->clearParameters($this);
			return true;
		}
		
		$this->tpl->setVariable("MEMBER_HIDE_TEXT",$this->lng->txt('hide_details'));
		$this->ctrl->setParameter($this,'member_show_details',0);
		$this->tpl->setVariable("MEMBER_HIDE",$this->ctrl->getLinkTarget($this,'members'));
		$this->ctrl->clearParameters($this);


		$member_tpl = new ilTemplate('tpl.table.html',true,true);
		$member_tpl->addBlockfile('TBL_CONTENT','tbl_content','tpl.member_member_row.html','Modules/Course');

		$sorted_members = ilUtil::sortArray($all_members_data,$_GET["sort_by"],$_GET["sort_order"]);
		$sliced_members = array_slice($sorted_members,$_GET['offset'],$_GET['limit']); 
		$counter = 0;
		foreach($sliced_members as $member)
		{
			$member_tpl->setCurrentBlock("link");
			$this->ctrl->setParameter($this,'member_id',$member['usr_id']);
			$member_tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTarget($this,'editMember'));
			$member_tpl->setVariable("LINK_TXT",$this->lng->txt('edit'));
			$member_tpl->parseCurrentBlock();
			$this->ctrl->clearParameters($this);

			if($this->timings_enabled)
			{
				$member_tpl->setCurrentBlock("link");
				$this->ctrl->setParameterByClass('ilcoursecontentgui','member_id',$member['usr_id']);
				$member_tpl->setVariable('LINK_NAME',$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','showUserTimings'));
				$member_tpl->setVariable("LINK_TXT",$this->lng->txt('timings_timings'));
				$member_tpl->parseCurrentBlock();
				$this->ctrl->clearParametersByClass('ilcoursecontentgui');
			}

			$member_tpl->setCurrentBlock("tbl_content");

			if($member['passed'])
			{
				$member_tpl->setVariable("CHECKED_PASSED",'checked="checked"');
			}
			if($member['blocked'])
			{
				$member_tpl->setVariable("CHECKED_BLOCKED",'checked="checked"');
			}
			if($this->show_tracking)
			{
				$member_tpl->setVariable("VAL_PROGRESS",$member['progress']);
			}

			$member_tpl->setVariable("CSS_ROW",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$member_tpl->setVariable("USER_ID",$member['usr_id']);
			$member_tpl->setVariable("LASTNAME",$member['lastname']);
			$member_tpl->setVariable("FIRSTNAME",$member['firstname']);
			$member_tpl->setVariable("LOGIN",$member['login']);
			$member_tpl->parseCurrentBlock();
		}

		$member_tpl->setCurrentBlock("select_row");
		$member_tpl->setVariable("ROWCLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
		$member_tpl->setVariable("SELECT_ALL",$this->lng->txt('select_all'));
		$member_tpl->parseCurrentBlock();
		
		
		$tbl = new ilTableGUI($members,false);
		$tbl->setTemplate($member_tpl);
		
		if($this->show_tracking)
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt('name'),
									   $this->lng->txt('login'),
									   $this->lng->txt('learning_progress'),
									   $this->lng->txt('crs_passed'),
									   $this->lng->txt('crs_blocked'),''));
			$tbl->setHeaderVars(array("",
									  "lastname",
									  "login",
									  "progress",
									  "passed",
									  "blocked",''),
								$this->ctrl->getParameterArray($this,'members'));
		}
		else
		{
			$tbl->setHeaderNames(array('',
									   $this->lng->txt('name'),
									   $this->lng->txt('login'),
									   $this->lng->txt('crs_passed'),
									   $this->lng->txt('crs_blocked'),''));
			$tbl->setHeaderVars(array("",
									  "lastname",
									  "login",
									  "passed",
									  "blocked",''),
								$this->ctrl->getParameterArray($this,'members'));
		}		
		$tbl->setOrderColumn($_GET["sort_by"]);
		$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET['limit']);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount(count($members));
		$tbl->disable('table');
		$tbl->disable('form');
		$tbl->disable('title');
		$tbl->disable('icon');
		$tbl->disable('content');
		$this->tpl->setVariable("MEMBERS",$tbl->render());
	}



	function __showWaitingList()
	{
		include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';

		$this->object->initCourseMemberObject();
		$this->object->initWaitingList();
		if($this->object->waiting_list_obj->getCountUsers())
		{
			$counter = 0;
			$f_result = array();
			foreach($this->object->waiting_list_obj->getAllUsers() as $waiting_data)
			{
				// GET USER OBJ
				if($tmp_obj = ilObjectFactory::getInstanceByObjId($waiting_data['usr_id'],false))
				{
					$waiting_list_ids[] = $waiting_data['usr_id'];
					
					$f_result[$counter][]	= ilUtil::formCheckbox(0,"waiting_list[]",$waiting_data['usr_id']);

					$message = '';
					// Check if user is member in course grouping
					foreach(ilObjCourseGrouping::_getGroupingCourseIds($this->object->getId()) as $course_data)
					{
						$tmp_members = ilCourseParticipants::_getInstanceByObjId($course_data['id']);
						if($course_data['id'] != $this->object->getId() and
							$tmp_members->isGroupingMember($tmp_obj->getId(),$course_data['unique']))
						{
							$message .= ('<br /><font class="alert">'.$this->lng->txt('crs_member_of').' ');
							$message .= (ilObject::_lookupTitle($course_data['id'])."</font>");
						}
					}
					$f_result[$counter][]   = $tmp_obj->getLogin().$message;
					$f_result[$counter][]	= $tmp_obj->getFirstname();
					$f_result[$counter][]	= $tmp_obj->getLastname();
					#$f_result[$counter][]   = strftime("%Y-%m-%d %R",$waiting_data["time"]);
					$f_result[$counter][]   = ilFormat::formatUnixTime($waiting_data["time"],true);


					unset($tmp_obj);
					++$counter;
				}
			}
			$this->__showWaitingListTable($f_result,$waiting_list_ids);

		} // END waiting list
	}

	function __showSubscribers()
	{
		if(count($this->object->members_obj->getSubscribers()))
		{
			$counter = 0;
			$f_result = array();
			foreach($this->object->members_obj->getSubscribers() as $member_id)
			{
				$member_data = $this->object->members_obj->getSubscriberData($member_id);

				// GET USER OBJ
				if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
				{
					$subscriber_ids[$counter] = $member_id;
					
					$f_result[$counter][]	= ilUtil::formCheckbox(0,"subscriber[]",$member_id);
					$f_result[$counter][]	= $tmp_obj->getLogin();
					$f_result[$counter][]	= $tmp_obj->getFirstname();
					$f_result[$counter][]	= $tmp_obj->getLastname();
					#$f_result[$counter][]   = strftime("%Y-%m-%d %R",$member_data["time"]);
					$f_result[$counter][]   = ilFormat::formatUnixTime($member_data["time"],true);

					unset($tmp_obj);
					++$counter;
				}
			}
			$this->__showSubscribersTable($f_result,$subscriber_ids);

		} // END SUBSCRIBERS
	}

	function editMemberObject()
	{
		global $rbacsystem,$ilObjDataCache;
		
		include_once('classes/class.ilObjRole.php');

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');

		$this->object->initCourseMemberObject();

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		// CHECK MEMBER_ID
		if(!isset($_GET["member_id"]) or !$this->object->members_obj->isAssigned((int) $_GET["member_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("crs_no_valid_member_id_given"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",'Modules/Course');

		$f_result = array();
		// GET USER OBJ
		$tmp_obj = ilObjectFactory::getInstanceByObjId((int) $_GET['member_id'],false);

		$f_result[0][]	= $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();
		$f_result[0][]	= $tmp_obj->getLogin();
		$f_result[0][]	= ilUtil::formCheckbox($this->object->members_obj->hasPassed((int) $_GET['member_id']) ? 1 : 0,
			'passed',
			1);
		$f_result[0][]	= ilUtil::formCheckbox($this->object->members_obj->isNotificationEnabled((int) $_GET['member_id']) ? 1 : 0,
			'notification',
			1);
		$f_result[0][]	= ilUtil::formCheckbox($this->object->members_obj->isBlocked((int) $_GET['member_id']) ? 1 : 0,
			'blocked',
			1);

		foreach($this->object->members_obj->getRoles() as $role_id)
		{
			$roles[$role_id] = ilObjRole::_getTranslation($ilObjDataCache->lookupTitle($role_id));
		}
		$f_result[0][] = ilUtil::formSelect($this->object->members_obj->getAssignedRoles((int) $_GET['member_id']),
			"roles[]",
			$roles,
			true,true,count($roles));
		$this->__showEditMemberTable($f_result);

		return true;
	}

	function updateMemberObject()
	{
		global $rbacsystem;

		$this->object->initCourseMemberObject();

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		// CHECK MEMBER_ID
		if(!isset($_GET["member_id"]) or !$this->object->members_obj->isAssigned((int) $_GET["member_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("crs_no_valid_member_id_given"),$this->ilias->error_obj->MESSAGE);
		}

		
		// Remember settings for notification
		$passed = $this->object->members_obj->hasPassed((int) $_GET['member_id']);
		$notify = $this->object->members_obj->isNotificationEnabled((int) $_GET['member_id']);
		$blocked = $this->object->members_obj->isBlocked((int) $_GET['member_id']);
		
		$this->object->members_obj->updateRoleAssignments((int) $_GET['member_id'],$_POST['roles']);
		$this->object->members_obj->updatePassed((int) $_GET['member_id'],(int) $_POST['passed']);
		$this->object->members_obj->updateNotification((int) $_GET['member_id'],(int) $_POST['notification']);
		$this->object->members_obj->updateBlocked((int) $_GET['member_id'],(int) $_POST['blocked']);
		
		if($passed != $this->object->members_obj->hasPassed((int) $_GET['member_id']) or
			$notify != $this->object->members_obj->isNotificationEnabled((int) $_GET['member_id']) or
			$blocked != $this->object->members_obj->isBlocked((int) $_GET['member_id']))
		{
			$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_STATUS_CHANGED,(int) $_GET['member_id']);
		}

		ilUtil::sendInfo($this->lng->txt("crs_member_updated"));
		$this->membersObject();
		return true;		

	}
	
	function assignMembersObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["user"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_users_selected"));
			return false;
		}
		$this->object->initCourseMemberObject();

		$added_users = 0;
		foreach($_POST["user"] as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id))
			{
				continue;
			}
			if($this->object->members_obj->isAssigned($user_id))
			{
				continue;
			}
			$this->object->members_obj->add($user_id,IL_CRS_MEMBER);
			$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_ACCEPT_USER,$user_id);

			++$added_users;
		}
		if($added_users)
		{
			ilUtil::sendInfo($this->lng->txt("crs_users_added"),true);
			unset($_SESSION["crs_search_str"]);
			unset($_SESSION["crs_search_for"]);
			unset($_SESSION['crs_usr_search_result']);
			$this->ctrl->redirect($this,'members');
		}
		ilUtil::sendInfo($this->lng->txt("crs_users_already_assigned"));
		
		return false;
	}

	function addFromWaitingList()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["waiting_list"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();
		$this->object->initWaitingList();

		$added_users = 0;
		foreach($_POST["waiting_list"] as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id))
			{
				continue;
			}
			if($this->object->members_obj->isAssigned($user_id))
			{
				continue;
			}
			$this->object->members_obj->add($user_id,IL_CRS_MEMBER);
			$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_ACCEPT_USER,$user_id);
			$this->object->waiting_list_obj->removeFromList($user_id);

			++$added_users;
		}
		if($added_users)
		{
			ilUtil::sendInfo($this->lng->txt("crs_users_added"));
			$this->membersObject();

			return true;
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("crs_users_already_assigned"));
			$this->searchObject();

			return false;
		}
	}

	function performRemoveFromWaitingListObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_SESSION["crs_delete_waiting_list_ids"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}

		$this->object->initWaitingList();
		foreach($_SESSION['crs_delete_waiting_list_ids'] as $usr_id)
		{
			$this->object->waiting_list_obj->removeFromList($usr_id);
		}
		ilUtil::sendInfo($this->lng->txt('crs_users_removed_from_list'));
		$this->membersObject();

		return true;
	}

		
	function addSubscribers()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		if(!is_array($_POST["subscriber"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();
		
		if($this->object->getSubscriptionMaxMembers() and 
		   ($this->object->getSubscriptionMaxMembers() < ($this->object->members_obj->getCountMembers() + count($_POST["subscriber"]))))
		{
			ilUtil::sendInfo($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		if(!$this->object->members_obj->assignSubscribers($_POST["subscriber"]))
		{
			ilUtil::sendInfo($this->object->getMessage());
			$this->membersObject();

			return false;
		}
		else
		{
			// SEND NOTIFICATION
			foreach($_POST["subscriber"] as $usr_id)
			{
				$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_ACCEPT_SUBSCRIBER,$usr_id);
			}
		}
		ilUtil::sendInfo($this->lng->txt("crs_subscribers_assigned"));
		$this->membersObject();
		
		return true;
	}

	function autoFillObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->object->initCourseMemberObject();

		if($this->object->getSubscriptionMaxMembers() and 
		   $this->object->getSubscriptionMaxMembers() <= $this->object->members_obj->getCountMembers())
		{
			ilUtil::sendInfo($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		if($number = $this->object->members_obj->autoFillSubscribers())
		{
			ilUtil::sendInfo($this->lng->txt("crs_number_users_added")." ".$number);
		}
		else
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_users_added"));
		}
		$this->membersObject();

		return true;
	}


	function deleteSubscribers()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('members');

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["subscriber"]) or !count($_POST["subscriber"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		ilUtil::sendInfo($this->lng->txt("crs_delete_subscribers_sure"));

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",'Modules/Course');
		$this->object->initCourseMemberObject();

		// SAVE IDS IN SESSION
		$_SESSION["crs_delete_subscriber_ids"] = $_POST["subscriber"];

		$counter = 0;
		$f_result = array();

		foreach($_POST["subscriber"] as $member_id)
		{
			$member_data = $this->object->members_obj->getSubscriberData($member_id);

			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $tmp_obj->getFirstname();
				$f_result[$counter][]	= $tmp_obj->getLastname();
				#$f_result[$counter][]   = strftime("%Y-%m-%d %R",$member_data["time"]);
				$f_result[$counter][]   = ilFormat::formatUnixTime($member_data["time"],true);

				unset($tmp_obj);
				++$counter;
			}
		}
		return $this->__showDeleteSubscriberTable($f_result);
	}
		
	function removeFromWaitingList()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('members');

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["waiting_list"]) or !count($_POST["waiting_list"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}
		ilUtil::sendInfo($this->lng->txt("crs_delete_from_list_sure"));

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",'Modules/Course');
		$this->object->initCourseMemberObject();
		$this->object->initWaitingList();

		// SAVE IDS IN SESSION
		$_SESSION["crs_delete_waiting_list_ids"] = $_POST["waiting_list"];

		$counter = 0;
		$f_result = array();

		foreach($_POST["waiting_list"] as $wait_id)
		{
			$user_data =& $this->object->waiting_list_obj->getUser($wait_id);
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($wait_id,false))
			{
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $tmp_obj->getFirstname();
				$f_result[$counter][]	= $tmp_obj->getLastname();
				#$f_result[$counter][]   = strftime("%Y-%m-%d %R",$user_data["time"]);
				$f_result[$counter][]   = ilFormat::formatUnixTime($user_data["time"],true);

				unset($tmp_obj);
				++$counter;
			}
		}
		return $this->__showRemoveFromWaitingListTable($f_result);
	}
	
	function unsubscribeObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("leave", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('members');
		#$this->setSubTabs('members');


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_unsubscribe_sure.html",'Modules/Course');
		ilUtil::sendInfo($this->lng->txt('crs_unsubscribe_sure'));
		
		$this->tpl->setVariable("UNSUB_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_SUBMIT",'performUnsubscribe');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("crs_unsubscribe"));
		
		return true;
	}

	function performUnsubscribeObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("leave", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->initCourseMemberObject();
		$this->object->members_obj->delete($this->ilias->account->getId());
		$this->object->members_obj->sendUnsubscribeNotificationToAdmins($this->ilias->account->getId());
		
		ilUtil::sendInfo($this->lng->txt('crs_unsubscribed_from_crs'),true);

		ilUtil::redirect("repository.php?ref_id=".$this->tree->getParentId($this->ref_id));
	}

	function deleteMembers()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('members');

		$_POST['member'] = array_merge((array) $_POST['member_ids'],(array) $_POST['tutor_ids'],(array) $_POST['admin_ids']);

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["member"]) or !count($_POST["member"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_member_selected"));
			$this->membersObject();

			return false;
		}
		ilUtil::sendInfo($this->lng->txt("crs_delete_members_sure"));

		$this->object->initCourseMemberObject();

		// CHECK LAST ADMIN
		if(!$this->object->members_obj->checkLastAdmin($_POST['member']))
		{
			ilUtil::sendInfo($this->lng->txt('crs_at_least_one_admin'));
			$this->membersObject();

			return false;
		}

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",'Modules/Course');

		

		// SAVE IDS IN SESSION
		$_SESSION["crs_delete_member_ids"] = $_POST["member"];

		$counter = 0;
		$f_result = array();

		foreach($_POST["member"] as $member_id)
		{
			#$member_data = $this->object->members_obj->getUserData($member_id);

			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $tmp_obj->getFirstname();
				$f_result[$counter][]	= $tmp_obj->getLastname();

				$message = '';
				if($this->object->members_obj->isAdmin($member_id))
				{
					$message = $this->lng->txt("crs_admin");
				}
				if($this->object->members_obj->isTutor($member_id))
				{
					$message = $this->lng->txt("crs_tutor");
				}
				if($this->object->members_obj->isMember($member_id))
				{
					$message = $this->lng->txt("crs_member");
				}
				$f_result[$counter][] = $message;

				unset($tmp_obj);
				++$counter;
			}
		}
		$this->__showDeleteMembersTable($f_result);

		return true;
	}

	function removeMembersObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_SESSION["crs_delete_member_ids"]) or !count($_SESSION["crs_delete_member_ids"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_member_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();

		if(!$this->object->members_obj->deleteParticipants($_SESSION["crs_delete_member_ids"]))
		{
			ilUtil::sendInfo($this->object->getMessage());
			unset($_SESSION["crs_delete_member_ids"]);
			$this->membersObject();

			return false;
		}
		else
		{
			// SEND NOTIFICATION
			foreach($_SESSION["crs_delete_member_ids"] as $usr_id)
			{
				$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_DISMISS_MEMBER,$usr_id);
			}
		}
		unset($_SESSION["crs_delete_member_ids"]);
		ilUtil::sendInfo($this->lng->txt("crs_members_deleted"));
		$this->membersObject();

		return true;
	}

	function removeSubscribersObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_SESSION["crs_delete_subscriber_ids"]) or !count($_SESSION["crs_delete_subscriber_ids"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();

		if(!$this->object->members_obj->deleteSubscribers($_SESSION["crs_delete_subscriber_ids"]))
		{
			ilUtil::sendInfo($this->object->getMessage());
			unset($_SESSION["crs_delete_subscriber_ids"]);
			$this->membersObject();

			return false;
		}
		else
		{
			// SEND NOTIFICATION
			foreach($_SESSION["crs_delete_subscriber_ids"] as $usr_id)
			{
				$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_DISMISS_SUBSCRIBER,$usr_id);
			}
		}

		unset($_SESSION["crs_delete_subscriber_ids"]);
		ilUtil::sendInfo($this->lng->txt("crs_subscribers_deleted"));
		$this->membersObject();

		return true;
	}


	function searchUserObject()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('members');

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->initCourseMemberObject();
		if($this->object->getSubscriptionMaxMembers() and 
		   $this->object->getSubscriptionMaxMembers() <= $this->object->members_obj->getCountMembers())
		{
			ilUtil::sendInfo($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_members_search.html",'Modules/Course');
		
		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("crs_search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["crs_search_str"] ? 
								ilUtil::prepareFormOutput($_SESSION["crs_search_str"],true) : "");
		$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER",$this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("exc_groups"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_ROLE",$this->lng->txt("exc_roles"));
		#$this->tpl->setVariable("SEARCH_ROW_TXT_COURSE",$this->lng->txt("courses"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));

        $usr = ($_POST["search_for"] == "usr" || $_POST["search_for"] == "") ? 1 : 0;
		$grp = ($_POST["search_for"] == "grp") ? 1 : 0;
		$role = ($_POST["search_for"] == "role") ? 1 : 0;

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton($usr,"search_for","usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_ROLE",ilUtil::formRadioButton($role,"search_for","role"));
        $this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton($grp,"search_for","grp"));
        #$this->tpl->setVariable("SEARCH_ROW_CHECK_COURSE",ilUtil::formRadioButton(0,"search_for",$this->SEARCH_COURSE));

		$this->__unsetSessionVariables();
	}

	function __appendToStoredResults($a_result)
	{
		$tmp_array = array();
		foreach($a_result as $result)
		{
			if(is_array($result))
			{
				$tmp_array[] = $result['id'];
			}
			elseif($result)
			{
				$tmp_array[] = $result;
			}
		}
		// merge results
		
		$_SESSION['crs_usr_search_result'] = array_unique(array_merge((array) $_SESSION['crs_usr_search_result'],$tmp_array));
		return $_SESSION['crs_usr_search_result'];
	}

	function cancelSearchObject()
	{
		$_SESSION['crs_usr_search_result'] = array();
		$_SESSION['crs_search_str'] = '';
		$this->searchUserObject();
	}
	
	function searchObject()
	{
		global $rbacsystem,$tree;

		$this->tabs_gui->setTabActive('members');

		#$this->__unsetSessionVariables();
		

		$_SESSION["crs_search_str"] = $_POST["search_str"] = $_POST["search_str"] 
			? $_POST["search_str"] 
			: $_SESSION["crs_search_str"];
		$_SESSION["crs_search_for"] = $_POST["search_for"] = $_POST["search_for"] 
			? $_POST["search_for"] 
			: $_SESSION["crs_search_for"];
		

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->initCourseMemberObject();

		if(!isset($_POST["search_for"]) or !isset($_POST["search_str"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_search_enter_search_string"));
			$this->searchUserObject();
			
			return false;
		}
		if(!count($result = $this->__search($_POST["search_str"],$_POST["search_for"])))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_results_found"));
			$this->searchUserObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html",'Modules/Course');
		#$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
		
		$counter = 0;
		$f_result = array();
		switch($_POST["search_for"])
		{
			case "usr":
				foreach($result as $user)
				{
					if(!is_object($tmp_obj = ilObjectFactory::getInstanceByObjId($user,false)))
					{
						continue;
					}
					$user_ids[$counter] = $user;
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getFirstname();
					$f_result[$counter][] = $tmp_obj->getLastname();

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result,$user_ids);

				return true;

			case "grp":
				foreach($result as $group)
				{
					if(!$tree->isInTree($group["id"]))
					{
						continue;
					}
					if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group["id"],false))
					{
						continue;
					}
					
					$grp_ids[$counter] = $group["id"];
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$group["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchGroupTable($f_result,$grp_ids);

				return true;
				
			case "role":
				foreach($result as $role)
				{
                    // exclude anonymous role
                    if ($role["id"] == ANONYMOUS_ROLE_ID)
                    {
                        continue;
                    }

                    if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($role["id"],false))
					{
						continue;
					}

				    // exclude roles with no users assigned to
                    if ($tmp_obj->getCountMembers() == 0)
                    {
                        continue;
                    }

					$role_ids[$counter] = $role["id"];
					
					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset($tmp_obj);
					++$counter;
				}

				$this->__showSearchRoleTable($f_result,$role_ids);

				return true;
		}
	}

	function listUsersGroupObject()
	{
		global $rbacsystem,$tree;

		$this->tabs_gui->setTabActive('members');

		$_SESSION["crs_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["crs_group"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["group"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html",'Modules/Course');
		#$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
		$this->object->initCourseMemberObject();

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["group"] as $group_id)
		{
			if(!$tree->isInTree($group_id))
			{
				continue;
			}
			if(!$tmp_obj = ilObjectFactory::getInstanceByRefId($group_id))
			{
				continue;
			}
			$members = array_merge($tmp_obj->getGroupMemberIds(),$members);

			unset($tmp_obj);
		}
		$members = array_unique($members);
		$members = $this->__appendToStoredResults($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
					
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersGroup");

		return true;
	}
	
	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		$this->tabs_gui->setTabActive('members');

		$_SESSION["crs_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["crs_role"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["role"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html",'Modules/Course');
		#$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
		$this->object->initCourseMemberObject();

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);
		$members = $this->__appendToStoredResults($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			
			$user_ids[$counter] = $user;
					
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,$user_ids,"listUsersRole");

		return true;
	}

	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$ilAccess,$ilUser;

		$this->object->initCourseMemberObject();

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if($ilAccess->checkAccess('read','',$this->ref_id))
		{
			$tabs_gui->addTarget('view_content',
								 $this->ctrl->getLinkTarget($this,''));
		}
		if ($ilAccess->checkAccess('visible','',$this->ref_id))
		{
			//$next_class = $this->ctrl->getNextClass($this);
			
			// this is not nice. tabs should be displayed in ilcoursegui
			// not via ilrepositorygui, then next_class == ilinfoscreengui
			// could be checked
			$force_active = (strtolower($_GET["cmdClass"]) == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(
								 array("ilobjcoursegui", "ilinfoscreengui"), "showSummary"),
								 "infoScreen",
								 "", "", $force_active);
		}
		if ($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$force_active = (strtolower($_GET["cmdClass"]) == "ilconditionhandlerinterface"
				&& $_GET["item_id"] == "")
				? true
				: false;
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"),
				array("edit", "editMapSettings", "editCourseIcons", "listStructure"), "", "", $force_active);
		}

		// lom meta data
		if ($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$tabs_gui->addTarget("meta_data",
								 $this->ctrl->getLinkTargetByClass(array('ilobjcoursegui','ilmdeditorgui'),'listSection'),
								 "",
								 "ilmdeditorgui");
		}

		// member list
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$tabs_gui->addTarget("members",
								 $this->ctrl->getLinkTarget($this, "members"), 
								 "members",
								 get_class($this));
		}			
		elseif ($ilAccess->checkAccess('read','',$this->ref_id) &&
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_ENABLED)
		{
			$tabs_gui->addTarget("members",
								 $this->ctrl->getLinkTarget($this, "membersGallery"), 
								 "members",
								 get_class($this));
		}
		
		// learning objectives
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$force_active = (strtolower($_GET["cmdClass"]) == "ilcourseobjectivesgui")
				? true
				: false;
			$tabs_gui->addTarget("crs_objectives",
								 $this->ctrl->getLinkTarget($this,"listObjectives"), 
								 "listObjectives",
								 get_class($this), "", $force_active);
		}
		
		// learning progress
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		include_once('Services/Tracking/classes/class.ilLPObjSettings.php');

		if(ilObjUserTracking::_enabledLearningProgress() and
		   (($ilAccess->checkAccess('read','',$this->ref_id) and (ilLPObjSettings::_lookupMode($this->object->getId()) != LP_MODE_DEACTIVATED)) or
		   ($ilAccess->checkAccess('write','',$this->ref_id))))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjcoursegui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		if ($ilAccess->checkAccess('edit_permission','',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
								 array("perm","info","owner"), 'ilpermissiongui');
		}

		if ($this->ctrl->getTargetScript() == "adm_object.php")
		{
			$tabs_gui->addTarget("show_owner",
								 $this->ctrl->getLinkTarget($this, "owner"), "owner", get_class($this));
			
			if ($this->tree->getSavedNodeData($this->ref_id))
			{
				$tabs_gui->addTarget("trash",
									 $this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
			}
		}
		if($ilAccess->checkAccess('join','',$this->ref_id)
			and !$this->object->members_obj->isAssigned($ilUser->getId()))
		{
			$tabs_gui->addTarget("join",
								 $this->ctrl->getLinkTarget($this, "join"), 
								 'join',
								 "");
		}			
	}
	
	function fetchPrintSubscriberData($a_members)
	{
		foreach($a_members as $member_id)
		{
			
			$member_data = $this->object->members_obj->getSubscriberData($member_id);

			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$print_member[$member_id]['login'] = $tmp_obj->getLogin();
				$print_member[$member_id]['name'] = $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();
				$print_member[$member_id]['time'] = ilFormat::formatUnixTime($member_data['time'],true);
			}
		}
		switch($_SESSION['crs_print_sort'])
		{
			case 'lastname':
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order']);
				
			case 'login':
				return ilUtil::sortArray($print_member,'login',$_SESSION['crs_print_order']);
			
			case 'sub_time':
				return ilUtil::sortArray($print_member,'time',$_SESSION['crs_print_order']);
			
			default:
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order']);
		}
	}
	
	function fetchPrintMemberData($a_members)
	{
		global $ilAccess;

		$is_admin = (bool) $ilAccess->checkAccess("write",'',$this->object->getRefId());
		
		foreach($a_members as $member_id)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$print_member[$member_id]['login'] = $tmp_obj->getLogin();
				$print_member[$member_id]['name'] = $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();

				if($this->object->members_obj->isAdmin($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_admin");
				}
				elseif($this->object->members_obj->isTutor($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_tutor");
				}
				elseif($this->object->members_obj->isMember($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_member");
				}
				if($this->object->members_obj->isAdmin($member_id) or $this->object->members_obj->isTutor($member_id))
				{
					if($this->object->members_obj->isNotificationEnabled($member_id))
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_notify");
					}
					else
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_no_notify");
					}
				}
				else
				{
					if($this->object->members_obj->isBlocked($member_id))
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_blocked");
					}
					else
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_unblocked");
					}
				}
	
				if($is_admin)
				{
					$print_member[$member_id]['passed'] = $this->object->members_obj->hasPassed($member_id) ?
									  $this->lng->txt('crs_member_passed') :
									  $this->lng->txt('crs_member_not_passed');
					
				}
			}
		}
		
		switch($_SESSION['crs_print_sort'])
		{
			case 'lastname':
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order']);
				
			case 'login':
				return ilUtil::sortArray($print_member,'login',$_SESSION['crs_print_order']);
			
			case 'passed':
				return ilUtil::sortArray($print_member,'passed',$_SESSION['crs_print_order']);
			
			case 'blocked':
			case 'notification':
				return ilUtil::sortArray($print_member,'status',$_SESSION['crs_print_order']);
			
			default:
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order']);
		}
	}
	

	function printMembersObject()
	{
		global $rbacsystem;

		$is_admin = (bool) $rbacsystem->checkAccess("write", $this->object->getRefId());

		$tpl =& new ilTemplate('tpl.crs_members_print.html',true,true,'Modules/Course');

		$this->object->initCourseMemberObject();

		// MEMBERS
		if(count($members = $this->object->members_obj->getParticipants()))
		{
			$members = $this->fetchPrintMemberData($members);
			
			foreach($members as $member_data)
			{
				$tpl->setCurrentBlock("members_row");
				$tpl->setVariable("LOGIN",$member_data['login']);
				$tpl->setVariable("NAME",$member_data['name']);
				$tpl->setVariable("ROLE",$member_data['role']);
				$tpl->setVariable("STATUS",$member_data['status']);
				$tpl->setVariable("PASSED",$member_data['passed']);
				
				if($is_admin)
				{
					$tpl->setVariable("STATUS",$member_data['status']);
					$tpl->setVariable("PASSED",$member_data['passed']);
				}
				$tpl->parseCurrentBlock();
			}

			$tpl->setCurrentBlock("members");
			$tpl->setVariable("MEMBERS_IMG_SOURCE",ilUtil::getImagePath('icon_usr.gif'));
			$tpl->setVariable("MEMBERS_IMG_ALT",$this->lng->txt('crs_header_members'));
			$tpl->setVariable("MEMBERS_TABLE_HEADER",$this->lng->txt('crs_members_table'));
			$tpl->setVariable("TXT_LOGIN",$this->lng->txt('username'));
			$tpl->setVariable("TXT_NAME",$this->lng->txt('name'));
			$tpl->setVariable("TXT_ROLE",$this->lng->txt('crs_role'));

			if($is_admin)
			{
				$tpl->setVariable("TXT_STATUS",$this->lng->txt('crs_status'));
				$tpl->setVariable("TXT_PASSED",$this->lng->txt('crs_passed'));
			}

			$tpl->parseCurrentBlock();

		}
		// SUBSCRIBERS
		if(count($members = $this->object->members_obj->getSubscribers()))
		{
			$members = $this->fetchPrintSubscriberData($members);
			foreach($members as $member_data)
			{
				$tpl->setCurrentBlock("members_row");
				$tpl->setVariable("SLOGIN",$member_data['login']);
				$tpl->setVariable("SNAME",$member_data['name']);
				$tpl->setVariable("STIME",$member_data["time"]);
				$this->tpl->parseCurrentBlock();
			}
			
			$tpl->setCurrentBlock("subscribers");
			$tpl->setVariable("SUBSCRIBERS_IMG_SOURCE",ilUtil::getImagePath('icon_usr.gif'));
			$tpl->setVariable("SUBSCRIBERS_IMG_ALT",$this->lng->txt('crs_subscribers'));
			$tpl->setVariable("SUBSCRIBERS_TABLE_HEADER",$this->lng->txt('crs_subscribers'));
			$tpl->setVariable("TXT_SLOGIN",$this->lng->txt('username'));
			$tpl->setVariable("TXT_SNAME",$this->lng->txt('name'));
			$tpl->setVariable("TXT_STIME",$this->lng->txt('crs_time'));
			$tpl->parseCurrentBlock();

		}

		$tpl->setVariable("TITLE",$this->lng->txt('crs_members_print_title'));
		$tpl->setVariable("CSS_PATH",ilUtil::getStyleSheetLocation());
		
		$headline = $this->lng->txt('obj_crs').': '.$this->object->getTitle().
			' -> '.$this->lng->txt('crs_header_members').' ('.ilFormat::formatUnixTime(time(),true).')';

		$tpl->setVariable("HEADLINE",$headline);

		$tpl->show();
		exit;
	}


	/**
	 * Builds a course members gallery as a layer of left-floating images
	 * @author Arturo Gonzalez <arturogf@gmail.com>
	 * @access       public
	 */
	function membersGalleryObject()
	{

		global $rbacsystem, $ilErr, $ilAccess,$ilUser;

		$is_admin = (bool) $ilAccess->checkAccess("write", "", $this->object->getRefId());

		if (!$is_admin &&
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_DISABLED)
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}

		$this->tabs_gui->setTabActive('members');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_members_gallery.html','Modules/Course');
		
		$this->setSubTabs('members');

		// Unsubscribe
		if($ilAccess->checkAccess('leave','',$this->object->getRefId()) and
		   $this->object->members_obj->isAssigned($ilUser->getId()))
		{
			$this->__showButton($this->ctrl->getLinkTarget($this,'unsubscribe'),$this->lng->txt("crs_unsubscribe"));
		}


		$this->object->initCourseMemberObject();

		// MEMBERS
		if(count($members = $this->object->members_obj->getParticipants()))
		{
			foreach($members as $member_id)
			{
				// get user object
				if(!($usr_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
				{
					continue;
				}

				$public_profile = $usr_obj->getPref("public_profile");
				
				// SET LINK TARGET FOR USER PROFILE
				$this->ctrl->setParameterByClass("ilobjusergui", "user", $member_id);
				$profile_target = $this->ctrl->getLinkTargetByClass("ilobjusergui","getPublicProfile");
			  
				// GET USER IMAGE
				$file = $usr_obj->getPersonalPicturePath("xsmall");
				
				if($this->object->members_obj->isAdmin($member_id) or $this->object->members_obj->isTutor($member_id))
				{
					if ($public_profile == "y")
					{
						$this->tpl->setCurrentBlock("tutor_linked");
						$this->tpl->setVariable("LINK_PROFILE", $profile_target);
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("tutor_not_linked");
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("tutor");
				}
				else
				{
					if ($public_profile == "y")
					{
						$this->tpl->setCurrentBlock("member_linked");
						$this->tpl->setVariable("LINK_PROFILE", $profile_target);
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("member_not_linked");
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("member");
				}
				
				// do not show name, if public profile is not activated
				if ($public_profile == "y")
				{
					$this->tpl->setVariable("FIRSTNAME", $usr_obj->getFirstname());
					$this->tpl->setVariable("LASTNAME", $usr_obj->getLastname());
				}
				$this->tpl->setVariable("LOGIN", $usr_obj->getLogin());
				$this->tpl->parseCurrentBlock();

			}
			$this->tpl->setCurrentBlock("members");	
			$this->tpl->setVariable("MEMBERS_TABLE_HEADER",$this->lng->txt('crs_members_title'));
			$this->tpl->parseCurrentBlock();
			
		}
		
		$this->tpl->setVariable("TITLE",$this->lng->txt('crs_members_print_title'));
		$this->tpl->setVariable("CSS_PATH",ilUtil::getStyleSheetLocation());
		
		$headline = $this->object->getTitle()."<br/>".$this->object->getDescription();
		
		$this->tpl->setVariable("HEADLINE",$headline);
		
		$this->tpl->show();
		exit;
	}
	

	function &__initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}


	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			case "members":
				$offset = $_GET["update_members"] ? $_GET["offset"] : 0;
				$order = $_GET["update_members"] ? $_GET["sort_by"] : 'login';
				$direction = $_GET["update_members"] ? $_GET["sort_order"] : '';
				break;

			case "subscribers":
				$offset = $_GET["update_subscribers"] ? $_GET["offset"] : 0;
				$order = $_GET["update_subscribers"] ? $_GET["sort_by"] : 'sub_time';
				$direction = $_GET["update_subscribers"] ? $_GET["sort_order"] : '';
				break;
				
			case "group":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;
				
   			case "role":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;

			default:
				$offset = $_GET["offset"];
				// init sort_by (unfortunatly sort_by is preset with 'title'
	           	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
                {
                    $_GET["sort_by"] = "login";
                }
                $order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}
		


	function __showEditMemberTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$this->ctrl->setParameter($this,"member_id",(int) $_GET["member_id"]);
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();


		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","updateMember");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("save"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","members");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",6);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array($this->lng->txt("name"),
								   $this->lng->txt("login"),
								   $this->lng->txt('crs_passed'),
								   $this->lng->txt("crs_notification"),
								   $this->lng->txt("crs_blocked"),
								   $this->lng->txt("crs_role_status")));
		$tbl->setHeaderVars(array("name",
								  "login",
								  "passed",
								  "notification",
								  "blocked",
								  "role"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("16%","16%","16%","16%","16%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->disable('sort');
		$tbl->disable('numinfo');
		$tbl->render();
		
		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());
	}

	function __showSearchUserTable($a_result_set,$a_user_ids = NULL,$a_cmd = "search")
	{
        $return_to  = "searchUser";

    	if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup")
    	{
            $return_to = "search";
        }

		$this->__showButton($return_to,$this->lng->txt("back"));

        
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","assignMembers");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME",'searchUser');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt('append_search'));
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME",'cancelSearch');
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("crs_new_search"));
		$tpl->parseCurrentBlock();

		if (!empty($a_user_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","user");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_user_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => $a_cmd,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","33%","33%","33%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}
	
	function __showSearchGroupTable($a_result_set,$a_grp_ids = NULL)
	{
		$this->__showButton('searchUser',$this->lng->txt("back"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("crs_list_users"));
		$tpl->parseCurrentBlock();

		if (!empty($a_grp_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","group");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_grp_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("crs_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"group");
		$tbl->render();

		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}
	
	function __showSearchRoleTable($a_result_set,$a_role_ids)
	{
		$this->__showButton('searchUser',$this->lng->txt("back"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","searchUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsersRole");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("crs_list_users"));
		$tpl->parseCurrentBlock();
		
		if (!empty($a_role_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","role");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_role_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_edit_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("obj_grp"),
								   $this->lng->txt("crs_count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("","80%","19%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"role");
		$tbl->render();
		
		$this->tpl->setVariable("SEARCH_RESULT_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showDeleteMembersTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();


		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","removeMembers");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("crs_delete_member"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","cancelMember");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_delete_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_delete_members"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("role")));
		$tbl->setHeaderVars(array("login",
								  "firstname",
								  "lastname",
								  "role"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->disable('sort');
		$tbl->render();
		
		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showRemoveFromWaitingListTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();
		
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","cancelMember");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","performRemoveFromWaitingList");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_remove_from_waiting_list"),"icon_usr_b.gif",
					   $this->lng->txt("crs_header_remove_from_waiting_list"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("login",
								  "firstname",
								  "lastname",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showDeleteSubscriberTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();
		
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","removeSubscribers");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","cancelMember");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_delete_subscribers"),"icon_usr_b.gif",$this->lng->txt("crs_header_delete_members"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("login",
								  "firstname",
								  "lastname",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());

		return true;
	}



	function __showSubscribersTable($a_result_set,$a_subscriber_ids = NULL)
	{
		$actions = array("addSubscribers"		=> $this->lng->txt("crs_add_subscribers"),
						 "deleteSubscribers"	=> $this->lng->txt("crs_delete_subscribers"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		// SET FOOTER BUTTONS
		$tpl->setCurrentBlock("tbl_action_row");

		// BUTTONS FOR ADD USER  
		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","autoFill");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("crs_auto_fill"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",5);
		
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();

		if (!empty($a_subscriber_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","subscriber");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_subscriber_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_subscribers"),"icon_usr.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "update_subscribers" => 1,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","24%","24%","24%","24%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set,"subscribers");
		$tbl->render();

		$this->tpl->setVariable("SUBSCRIBER_TABLE",$tbl->tpl->get());

		return true;
	}
	function __showWaitingListTable($a_result_set,$a_waiting_list_ids = NULL)
	{
		$actions = array("addFromWaitingList"		=> $this->lng->txt("crs_add_subscribers"),
						 "removeFromWaitingList"	=> $this->lng->txt("crs_delete_from_waiting_list"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		// SET FOOTER BUTTONS
		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setVariable("COLUMN_COUNTS",5);
		
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();

		if (!empty($a_waiting_list_ids))
		{
			// set checkbox toggles
			$tpl->setCurrentBlock("tbl_action_toggle_checkboxes");
			$tpl->setVariable("JS_VARNAME","waiting_list");			
			$tpl->setVariable("JS_ONCLICK",ilUtil::array_php2js($a_waiting_list_ids));
			$tpl->setVariable("TXT_CHECKALL", $this->lng->txt("check_all"));
			$tpl->setVariable("TXT_UNCHECKALL", $this->lng->txt("uncheck_all"));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_waiting_list"),"icon_usr.gif",$this->lng->txt("crs_waiting_list"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "update_subscribers" => 1,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","24%","24%","24%","24%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set,"subscribers");
		$tbl->render();

		$this->tpl->setVariable("SUBSCRIBER_TABLE",$tbl->tpl->get());

		return true;
	}

	function __search($a_search_string,$a_search_for)
	{
		include_once("./classes/class.ilSearch.php");

		$this->lng->loadLanguageModule("content");

		$search =& new ilSearch($_SESSION["AccountId"]);
		$search->setPerformUpdate(false);
		$search->setMinWordLength(1);
		$search->setSearchString($a_search_string);
		$search->setCombination("and");
		$search->setSearchFor(array(0 => $a_search_for));
		$search->setSearchType('new');

		$message = '';
		if($search->validate($message))
		{
			$search->performSearch();
		}
		else
		{
			ilUtil::sendInfo($message,true);
			$this->ctrl->redirect($this,"searchUser");
		}

		if($a_search_for == 'usr')
		{
			$this->__appendToStoredResults($search->getResultByType($a_search_for));
			return $_SESSION['crs_usr_search_result'];
		}

		return $search->getResultByType($a_search_for);
	}		


	function __getDateSelect($a_type,$a_varname,$a_selected)
	{
		switch($a_type)
		{
			case "minute":
				for($i=0;$i<=60;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "hour":
				for($i=0;$i<24;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "day":
				for($i=1;$i<32;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);
			
			case "month":
				for($i=1;$i<13;$i++)
				{
					$month[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$month,false,true);

			case "year":
				for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
				{
					$year[$i] = $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$year,false,true);
		}
	}

	function __toUnix($a_time_arr)
	{
		return mktime($a_time_arr["hour"],
					  $a_time_arr["minute"],
					  $a_time_arr["second"],
					  $a_time_arr["month"],
					  $a_time_arr["day"],
					  $a_time_arr["year"]);
	}
	function __unsetSessionVariables()
	{
		unset($_SESSION["crs_delete_member_ids"]);
		unset($_SESSION["crs_delete_subscriber_ids"]);
		unset($_SESSION["crs_search_str"]);
		unset($_SESSION["crs_search_for"]);
		unset($_SESSION["crs_group"]);
		unset($_SESSION["crs_role"]);
		unset($_SESSION["crs_archives"]);
	}

	function mailMembersObject()
	{
		global $rbacreview, $ilErr, $ilAccess;

		$is_admin = (bool) $ilAccess->checkAccess("write", "", $this->object->getRefId());

		if (!$is_admin &&
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_DISABLED)
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}

		$this->tabs_gui->setTabActive('members');

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.mail_members.html','Modules/Course');

		$this->setSubTabs('members');

		$this->tpl->setVariable("MAILACTION",'ilias.php?baseClass=ilmailgui&type=role');
		$this->tpl->setVariable("SELECT_ACTION",'ilias.php?baseClass=ilmailgui&view=my_courses&search_crs='.$this->object->getId());
		$this->tpl->setVariable("MAIL_SELECTED",$this->lng->txt('send_mail_selected'));
		$this->tpl->setVariable("MAIL_MEMBERS",$this->lng->txt('send_mail_members'));
		$this->tpl->setVariable("MAIL_TUTOR",$this->lng->txt('send_mail_tutors'));
		$this->tpl->setVariable("MAIL_ADMIN",$this->lng->txt('send_mail_admins'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("OK",$this->lng->txt('ok'));

		// Display roles with user friendly mailbox addresses
		$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());
		$role_ids = $rbacreview->getRolesOfRoleFolder($role_folder['ref_id'], false);
		foreach ($role_ids as $role_id)
		{
			$this->tpl->setCurrentBlock("mailbox_row");
			$role_addr = $rbacreview->getRoleMailboxAddress($role_id);
			$this->tpl->setVariable("CHECK_MAILBOX",ilUtil::formCheckbox(1,'roles[]',
					htmlspecialchars($role_addr)
			));
			$this->tpl->setVariable("MAILBOX",$role_addr);
			$this->tpl->parseCurrentBlock();
		}
	}

	
	function &executeCommand()
	{
		global $rbacsystem,$ilUser,$ilAccess,$ilErr,$ilTabs,$ilNavigationHistory;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();
		
		// check if object is purchased
		include_once './payment/classes/class.ilPaymentObject.php';

		if (!$this->creation_mode)	// don't check, if new object is created
		{
			if(!ilPaymentObject::_hasAccess($this->object->getRefId()))
			{
				if ($cmd != "addToShoppingCart")
				{
					$this->ctrl->setCmd("");
					$cmd = "";
				}
	
				include_once './payment/classes/class.ilPaymentPurchaseGUI.php';
	
				$this->ctrl->setReturn($this,"");
				$pp_gui =& new ilPaymentPurchaseGUI($this->object->getRefId());
	
				$this->ctrl->forwardCommand($pp_gui);
	
				return true;
			}
			
			// add entry to navigation history
			if (!$this->getCreationMode() &&
				$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
			{
				$ilNavigationHistory->addItem($_GET["ref_id"],
					"repository.php?cmd=frameset&ref_id=".$_GET["ref_id"], "crs");
			}

		}

		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;

			case 'ilmdeditorgui':

				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				$this->tabs_gui->setTabActive('meta_data');
				break;

			case "ilcourseregistergui":
				$this->ctrl->setReturn($this,"");
				$reg_gui =& new ilCourseRegisterGUI($this->object->getRefId());
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;
				
			case 'ilcourseuserfieldsgui':
				include_once 'Modules/Course/classes/Export/class.ilCourseUserFieldsGUI.php';
				
				$cdf_gui = new ilCourseUserFieldsGUI($this->object->getId());
				$this->setSubTabs('properties');
				$this->tabs_gui->setTabActive('settings');
				$this->ctrl->forwardCommand($cdf_gui);
				break;

			case "ilcourseobjectivesgui":
				include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';

				$this->ctrl->setReturn($this,"");
				$reg_gui =& new ilCourseObjectivesGUI($this->object->getRefId());
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			case 'ilobjcoursegroupinggui':
				include_once './Modules/Course/classes/class.ilObjCourseGroupingGUI.php';

				$this->ctrl->setReturn($this,'edit');
				$this->setSubTabs('properties');
				$crs_grp_gui =& new ilObjCourseGroupingGUI($this->object,(int) $_GET['obj_id']);
				$this->ctrl->forwardCommand($crs_grp_gui);
				$this->tabs_gui->setTabActive('settings');
				$this->tabs_gui->setSubTabActive('groupings');
				break;

			case "ilconditionhandlerinterface":
				include_once './classes/class.ilConditionHandlerInterface.php';
				
				// preconditions for single course items
				if($_GET['item_id'])
				{
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->tabs_gui->setTabActive('content');
					$this->setSubTabs("item_activation");

					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else	// preconditions for whole course
				{
					$this->setSubTabs("properties");
					$this->tabs_gui->setTabActive('settings');
					$new_gui =& new ilConditionHandlerInterface($this);

					$this->ctrl->forwardCommand($new_gui);
				}
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;
				
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				$this->tabs_gui->setTabActive('perm_settings');
				break;

			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,'assignMembersObject');

				// Set tabs
				$this->tabs_gui->setTabActive('members');
				$this->ctrl->setReturn($this,'members');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->setSubTabs('members');
				$this->tabs_gui->setSubTabActive('members');
				break;

			case 'ilcoursecontentinterface':

				$this->initCourseContentInterface();
				$this->cci_obj->cci_setContainer($this);

				$this->ctrl->forwardCommand($this->cci_obj);
				$this->setSubTabs('content');
				$this->tabs_gui->setTabActive('content');
				break;

			case 'ilcoursecontentgui':
				$this->ctrl->setReturn($this,'members');
				include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
				$course_content_obj = new ilCourseContentGUI($this);
				$this->ctrl->forwardCommand($course_content_obj);
				break;

			case 'ilobjusergui':
				require_once "./classes/class.ilObjUserGUI.php";
				$user_gui = new ilObjUserGUI("",$_GET["user"], false, false);
				$html = $this->ctrl->forwardCommand($user_gui);
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('crs_members_gallery');
				$this->tpl->setVariable("ADM_CONTENT", $html);
				break;

			case 'ilmemberexportgui':
				include_once('./Modules/Course/classes/Export/class.ilMemberExportGUI.php');
				
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('export_members');
				$export = new ilMemberExportGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($export);
				break;
				
			case 'ilcourseagreementgui':
				$this->forwardToAgreement();
				break;
				
			case 'ileventadministrationgui':
			
				include_once('Modules/Course/classes/Event/class.ilEventAdministrationGUI.php');
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('events');
				
				$events = new ilEventAdministrationGUI($this);
				$this->ctrl->forwardCommand($events);				
				break;
				
			default:
				if(!$this->creation_mode and !$ilAccess->checkAccess('visible','',$this->object->getRefId(),'crs'))
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}
				if( !$this->creation_mode
					&& $cmd != 'infoScreen'
					&& $cmd != 'sendfile'
					&& !$rbacsystem->checkAccess("read",$this->object->getRefId())
					|| $cmd == 'join'
					|| $cmd == 'subscribe')
				{
					$this->ctrl->setReturn($this,"infoScreen");
					$reg_gui =& new ilCourseRegisterGUI($this->object->getRefId());
					$ret =& $this->ctrl->forwardCommand($reg_gui);
					break;
				}
				
				if($cmd == 'listObjectives')
				{
					include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';

					$this->ctrl->setReturn($this,"");
					$obj_gui =& new ilCourseObjectivesGUI($this->object->getRefId());
					$ret =& $this->ctrl->forwardCommand($obj_gui);
					break;
				}
				
				if((!$this->creation_mode)&&(!$rbacsystem->checkAccess("write",$this->object->getRefId()))){
					include_once('Services/Feedback/classes/class.ilFeedbackGUI.php');
					$feedbackGUI = new ilFeedbackGUI();
					$feedbackGUI->handleRequiredFeedback($this->object->getRefId());
				}

				if(!$cmd)
				{
					$cmd = 'view';
				}
				$cmd .= 'Object';
				$this->$cmd();
					
				break;
		}

		return true;
	}
	
	/**
	 * Check agreement and redirect if it is not accepted
	 *
	 * @access private
	 * 
	 */
	private function checkAgreement()
	{
		global $ilUser,$ilAccess;
		
		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			return true;
		}
		
		// Disable aggrement if is not member of course
		if(!$this->object->members_obj->isAssigned($ilUser->getId()))
		{
			return true;
		}
		
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		include_once('Modules/Course/classes/class.ilCourseAgreement.php');
		$privacy = ilPrivacySettings::_getInstance();
		
		// Check agreement
		if($privacy->confirmationRequired() and !ilCourseAgreement::_hasAccepted($ilUser->getId(),$this->object->getId()))
		{
			return false;
		}
		// Check required fields
		include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
		if(!ilCourseUserData::_checkRequired($ilUser->getId(),$this->object->getId()))
		{
			return false;
		}
		return true;
	}
	
	/**
	 * Forward to CourseAgreementGUI
	 *
	 * @access private
	 * 
	 */
	private function forwardToAgreement()
	{
		include_once('Modules/Course/classes/class.ilCourseAgreementGUI.php');
		$this->ctrl->setReturn($this,'');
		$agreement = new ilCourseAgreementGUI($this->object->getRefId());
		$this->ctrl->forwardCommand($agreement);
	}

	// STATIC
	function _forwards()
	{
		return array("ilCourseRegisterGUI",'ilConditionHandlerInterface');
	}


	function cciObjectivesObject()
	{
		$this->tabs_gui->setTabActive('learners_view');
		
		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_objectives();

		return true;;
	}
	function cciObjectivesEditObject()
	{
		$this->tabs_gui->setTabActive('edit_content');

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_view();

		return true;
	}
	function cciObjectivesAskResetObject()
	{
		$this->tabs_gui->setTabActive('learners_view');

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_objectives_ask_reset();

		return true;;
	}
	function cciResetObject()
	{
		$this->tabs_gui->setTabActive('learners_view');

		global $ilUser;

		include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';

		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->reset($this->object->getId());

		ilUtil::sendInfo($this->lng->txt('crs_objectives_reseted'));

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_objectives();
	}


	// Methods for ConditionHandlerInterface
	function initConditionHandlerGUI($item_id)
	{
		include_once './classes/class.ilConditionHandlerInterface.php';

		if(!is_object($this->chi_obj))
		{
			if($_GET['item_id'])
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this,$item_id);
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
			}
			else
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this);
			}
		}
		return true;
	}


	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			default:
				#$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""));
				break;
		}
	}

	/**
	* goto target course
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			$_GET["cmd"] = "frameset";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		else
		{
			// to do: force flat view
			if ($ilAccess->checkAccess("visible", "", $a_target))
			{
				$_GET["cmd"] = "infoScreen";
				$_GET["ref_id"] = $a_target;
				include("repository.php");
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
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}


	function toUnix($date,$time = array())
	{
		return mktime($time['h'],$time['m'],0,$date['m'],$date['d'],$date['y']);
	}

	function __prepareDateSelect($a_unix_time)
	{
		return array('y' => date('Y',$a_unix_time),
					 'm' => date('n',$a_unix_time),
					 'd' => date('d',$a_unix_time));
	}

	function __prepareTimeSelect($a_unix_time)
	{
		return array('h' => date('G',$a_unix_time),
					 'm' => date('i',$a_unix_time),
					 's' => date('s',$a_unix_time));
	}

	// Copy wizard

	/**
	* Edit Map Settings
	*/
	function editMapSettingsObject()
	{
		global $ilUser, $ilCtrl, $ilUser, $ilAccess;

		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');
		
		if (!ilGoogleMapUtil::isActivated() ||
			!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			return;
		}

		$latitude = $this->object->getLatitude();
		$longitude = $this->object->getLongitude();
		$zoom = $this->object->getLocationZoom();
		
		// Get Default settings, when nothing is set
		if ($latitude == 0 && $longitude == 0 && $zoom == 0)
		{
			$def = ilGoogleMapUtil::getDefaultSettings();
			$latitude = $def["latitude"];
			$longitude = $def["longitude"];
			$zoom =  $def["zoom"];
		}

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		//$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$form->setTitle($this->lng->txt("crs_map_settings"));
			
		// enable map
		$public = new ilCheckboxInputGUI($this->lng->txt("crs_enable_map"),
			"enable_map");
		$public->setValue("1");
		$public->setChecked($this->object->getEnableCourseMap());
		$form->addItem($public);

		// map location
		$loc_prop = new ilLocationInputGUI($this->lng->txt("crs_map_location"),
			"location");
		$loc_prop->setLatitude($latitude);
		$loc_prop->setLongitude($longitude);
		$loc_prop->setZoom($zoom);
		$form->addItem($loc_prop);
		
		$form->addCommandButton("saveMapSettings", $this->lng->txt("save"));
		
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		//$this->tpl->show();
	}

	function saveMapSettingsObject()
	{
		global $ilCtrl, $ilUser;

		$this->object->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
		$this->object->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
		$this->object->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
		$this->object->setEnableCourseMap(ilUtil::stripSlashes($_POST["enable_map"]));
		$this->object->update();
		
		$ilCtrl->redirect($this, "editMapSettings");
	}

	/**
	* Members map
	*/
	function membersMapObject()
	{
		global $tpl;

		$this->tabs_gui->setTabActive("members");
		$this->setSubTabs('members');
		$this->tabs_gui->setSubTabActive("crs_members_map");
		
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
		if (!ilGoogleMapUtil::isActivated() || !$this->object->getEnableCourseMap())
		{
			return;
		}
		
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
		$map = new ilGoogleMapGUI();
		$map->setMapId("course_map");
		$map->setWidth("700px");
		$map->setHeight("500px");
		$map->setLatitude($this->object->getLatitude());
		$map->setLongitude($this->object->getLongitude());
		$map->setZoom($this->object->getLocationZoom());
		$map->setEnableTypeControl(true);
		$map->setEnableNavigationControl(true);

		$this->object->initCourseMemberObject();
		if(count($members = $this->object->members_obj->getParticipants()))
		{
			foreach($members as $user_id)
			{
				$map->addUserMarker($user_id);
			}
		}

		$tpl->setContent($map->getHTML());
		$tpl->setLeftContent($map->getUserListHTML());
	}

} // END class.ilObjCourseGUI
?>
