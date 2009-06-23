<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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



require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id$
*
* @ilCtrl_Calls ilObjCourseGUI: ilCourseRegistrationGUI, ilShopPurchaseGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI, ilMDEditorGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjCourseGUI: ilRepositorySearchGUI, ilConditionHandlerInterface
* @ilCtrl_Calls ilObjCourseGUI: ilCourseContentGUI, ilPublicUserProfileGUI, ilMemberExportGUI
* @ilCtrl_Calls ilObjCourseGUI: ilCourseUserFieldsGUI, ilCourseAgreementGUI, ilSessionOverviewGUI
* @ilCtrl_Calls ilObjCourseGUI: ilColumnGUI, ilPageObjectGUI, ilCourseItemAdministrationGUI
*
* 
* @extends ilContainerGUI
*/
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

			default:
				$this->viewObject();
				break;
		}
		return true;
	}
	
	/**
	 * Gateway for member administration commands
	 *
	 * @access public
	 * 
	 */
	public function memberGatewayObject()
	{
		if(isset($_POST['btn_pressed']['deleteMembers']))
		{
			return $this->deleteMembersObject();
		}
		elseif($_POST['btn_pressed']['sendMailToSelectedUsers'])
		{
			return $this->sendMailToSelectedUsersObject();
		}		
		else
		{
			return $this->updateMembersObject();
		}
	}

	function sendMailToSelectedUsersObject()
	{
		if(isset($_GET['member_id']))
		{
			$_POST['member'] = array($_GET['member_id']);
		}
		else
		{
			$_POST['member'] = array_unique(array_merge((array) $_POST['members'],
				(array) $_POST['tutors'],
				(array) $_POST['admins'],
				(array) $_POST['waiting'],
				(array) $_POST['subscribers']));
		}
		

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
		
		$this->ctrl->setParameter($this, "new_type",'crs');
		$this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this, "save"));
		
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
	
	/**
	 * 
	 * @param
	 * @return
	 */
	protected function forwardToTimingsView()
	{
		if(!$this->ctrl->getCmd() and $this->object->getViewMode() == ilContainer::VIEW_TIMING)
		{
			if(!isset($_SESSION['crs_timings'])) {
				$_SESSION['crs_timings'] = true;
			}
			
			if($_SESSION['crs_timings'] == true) {
				include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
				$course_content_obj = new ilCourseContentGUI($this);
				$this->ctrl->setCmdClass(get_class($course_content_obj));
				$this->ctrl->setCmd('editUserTimings');
				$this->ctrl->forwardCommand($course_content_obj);
				return true;
			}	
		}
		$_SESSION['crs_timings'] = false;
		return false;
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

		if(!$this->__checkStartObjects())
		{
			$this->showStartObjects();
		}

		// views handled by general container logic
		if ($this->object->getViewMode() == ilContainer::VIEW_SIMPLE ||
			$this->object->getViewMode() == ilContainer::VIEW_BY_TYPE ||
			$this->object->getViewMode() == ilContainer::VIEW_SESSIONS ||
			$this->object->getViewMode() == ilContainer::VIEW_TIMING ||
			$this->object->getViewMode() == ilContainer::VIEW_OBJECTIVE
			)
		{
			$ret = parent::renderObject();
			return $ret;
		}
		else
		{
			include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
			$course_content_obj = new ilCourseContentGUI($this);
	
			$this->ctrl->setCmdClass(get_class($course_content_obj));
			$this->ctrl->forwardCommand($course_content_obj);
		}

		return true;
	}

	function renderContainer()
	{
		return parent::renderObject();
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
			return false;
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

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO,'crs',$this->object->getId());
		$record_gui->setInfoObject($info);
		$record_gui->parse();
		
		// meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
			 
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
			$emails = split(",",$this->object->getContactEmail());
			foreach ($emails as $email) {
				$email = trim($email);
				$etpl = new ilTemplate("tpl.crs_contact_email.html", true, true , 'Modules/Course');
				$etpl->setVariable("EMAIL_LINK","ilias.php?baseClass=ilmailgui&type=new&rcp_to=".$email);
				$etpl->setVariable("CONTACT_EMAIL", $email);				
				$mailString .= $etpl->get()."<br />";
			}
			$info->addProperty($this->lng->txt("crs_contact_email"), $mailString);
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
			$info->addProperty($this->lng->txt('crs_visibility'),
				ilDatePresentation::formatPeriod(
					new ilDateTime($this->object->getActivationStart(),IL_CAL_UNIX),
					new ilDateTime($this->object->getActivationEnd(),IL_CAL_UNIX)));
					
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
								   ilDatePresentation::formatDate(new ilDateTime($this->object->getSubscriptionEnd(),IL_CAL_UNIX)));
			}
			elseif($this->object->getSubscriptionStart() > time())
			{
				$info->addProperty($this->lng->txt("crs_reg_until"),
								   $this->lng->txt('crs_from').' '.
								   ilDatePresentation::formatDate(new ilDateTime($this->object->getSubscriptionStart(),IL_CAL_UNIX)));
			}
			if ($this->object->isSubscriptionMembershipLimited()) 
			{
				$info->addProperty($this->lng->txt("mem_free_places"),
								   max(0,$this->object->getSubscriptionMaxMembers()- count($this->object->getMembers())));
				
			}
				
		}
		
		// archive
		if($this->object->getViewMode() == IL_CRS_VIEW_ARCHIVE)
		{		
			if($this->object->getArchiveType() != IL_CRS_ARCHIVE_NONE)
			{
				$info->addProperty($this->lng->txt("crs_archive"),
					ilDatePresentation::formatPeriod(
						new ilDateTime($this->object->getArchiveStart(),IL_CAL_UNIX),
						new ilDateTime($this->object->getArchiveStart(),IL_CAL_UNIX)));
			}
		}		
		// Confirmation
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		if($privacy->confirmationRequired() or ilCourseDefinedFieldDefinition::_getFields($this->object->getId()) or $privacy->enabledExport())
		{
			include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
			
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
	
	/**
	 * Edit info page informations
	 *
	 * @access public
	 * 
	 */
	public function editInfoObject()
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
	 	
	 	$this->initInfoEditor();
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.edit_info.html','Modules/Course');
		$this->tpl->setVariable('INFO_TABLE',$this->form->getHTML());
		
		if(!count($files = ilCourseFile::_readFilesByCourse($this->object->getId())))
		{
			return true;
		}
		$rows = array();
		foreach($files as $file)
		{
			$table_data['id'] = $file->getFileId();
			$table_data['filename'] = $file->getFileName();
			$table_data['filetype'] = $file->getFileType();
			$table_data['filesize'] = $file->getFileSize();
			
			$rows[] = $table_data; 
		}
		
		include_once("./Modules/Course/classes/class.ilCourseInfoFileTableGUI.php");
		$table_gui = new ilCourseInfoFileTableGUI($this, "edit");
		$table_gui->setTitle($this->lng->txt("crs_info_download"));
		$table_gui->setData($rows);
		$table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
		$table_gui->addMultiCommand("confirmDeleteInfoFiles", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("file_id");
		$this->tpl->setVariable('INFO_FILE_TABLE',$table_gui->getHTML());

		return true;
		
	}
	
	/**
	 * show info file donfimation table
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function confirmDeleteInfoFilesObject()
	{
		if(!count($_POST['file_id']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->editInfoObject();
			return false;
		}

		$this->setSubTabs('properties');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_info_settings');
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteInfoFiles"));
		$c_gui->setHeaderText($this->lng->txt("info_delete_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "editInfo");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteInfoFiles");

		// add items to delete
		include_once('Modules/Course/classes/class.ilCourseFile.php');
		foreach($_POST["file_id"] as $file_id)
		{
			$file = new ilCourseFile($file_id);
			$c_gui->addItem("file_id[]", $file_id, $file->getFileName());
		}
		
		$this->tpl->setContent($c_gui->getHTML());
	}
	
	/**
	 * Delete info files
	 *
	 * @access public
	 * 
	 */
	public function deleteInfoFilesObject()
	{
		if(!count($_POST['file_id']))
		{
			ilUtil::sendInfo($this->lng->txt('select_one'));
			$this->editInfoObject();
			return false;
		}
		include_once('Modules/Course/classes/class.ilCourseFile.php');
		
		foreach($_POST['file_id'] as $file_id)
		{
			$file = new ilCourseFile($file_id);
			if($this->object->getId() == $file->getCourseId())
			{
				$file->delete();
			}
		}
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->editInfoObject();
		return true;	
	}
	 	
	/**
	 * init info editor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function initInfoEditor()
	{
		if(is_object($this->form))
		{
			return true;
		}
	
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		$this->form->setMultipart(true);
		$this->form->setTitle($this->lng->txt('crs_general_info'));
		$this->form->addCommandButton('updateInfo',$this->lng->txt('save'));
		$this->form->addCommandButton('editInfo',$this->lng->txt('cancel'));
		
		$area = new ilTextAreaInputGUI($this->lng->txt('crs_important_info'),'important');
		$area->setValue($this->object->getImportantInformation());
		$area->setRows(6);
		$area->setCols(80);
		$this->form->addItem($area);
		
		$area = new ilTextAreaInputGUI($this->lng->txt('crs_syllabus'),'syllabus');
		$area->setValue($this->object->getSyllabus());
		$area->setRows(6);
		$area->setCols(80);
		$this->form->addItem($area);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('crs_info_download'));
		$this->form->addItem($section);
		
		$file = new ilFileInputGUI($this->lng->txt('crs_file'),'file');
		$file->enableFileNameSelection('file_name');
		$this->form->addItem($file);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('crs_contact'));
		$this->form->addItem($section);
		
		$text = new ilTextInputGUI($this->lng->txt('crs_contact_name'),'contact_name');
		$text->setValue($this->object->getContactName());
		$text->setSize(40);
		$text->setMaxLength(70);
		$this->form->addItem($text);
		
		$text = new ilTextInputGUI($this->lng->txt('crs_contact_responsibility'),'contact_responsibility');
		$text->setValue($this->object->getContactResponsibility());
		$text->setSize(40);
		$text->setMaxLength(70);
		$this->form->addItem($text);

		$text = new ilTextInputGUI($this->lng->txt('crs_contact_phone'),'contact_phone');
		$text->setValue($this->object->getContactPhone());
		$text->setSize(40);
		$text->setMaxLength(40);
		$this->form->addItem($text);

		$text = new ilTextInputGUI($this->lng->txt('crs_contact_email'),'contact_email');
		$text->setValue($this->object->getContactEmail());
		$text->setInfo($this->lng->txt('crs_contact_email_info'));
		$text->setSize(40);
		$text->setMaxLength(255);
		$this->form->addItem($text);

		$area = new ilTextAreaInputGUI($this->lng->txt('crs_contact_consultation'),'contact_consultation');
		$area->setValue($this->object->getContactConsultation());
		$area->setRows(6);
		$area->setCols(80);
		$this->form->addItem($area);
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,'crs',$this->object->getId());
		$record_gui->setPropertyForm($this->form);
		$record_gui->parse();

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
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,
			'crs',$this->object->getId());
		$record_gui->loadFromPost();

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
		$record_gui->saveValues();

		$this->object->updateECSContent();

		ilUtil::sendInfo($this->lng->txt("crs_settings_saved"));
		$this->editInfoObject();
		return true;
	}

	function updateObject()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));		
		
		$activation_start = $this->loadDate('activation_start');
		$activation_end = $this->loadDate('activation_end');
		$subscription_start = $this->loadDate('subscription_start');
		$subscription_end = $this->loadDate('subscription_end');
		$archive_start = $this->loadDate('archive_start');
		$archive_end = $this->loadDate('archive_end');

		$this->object->setActivationType((int) $_POST['activation_type']);
		$this->object->setActivationStart($activation_start->get(IL_CAL_UNIX));
		$this->object->setActivationEnd($activation_end->get(IL_CAL_UNIX));
		$this->object->setSubscriptionLimitationType((int) $_POST['subscription_limitation_type']);
		$this->object->setSubscriptionType((int) $_POST['subscription_type']);
		$this->object->setSubscriptionPassword(ilUtil::stripSlashes($_POST['subscription_password']));
		$this->object->setSubscriptionStart($subscription_start->get(IL_CAL_UNIX));
		$this->object->setSubscriptionEnd($subscription_end->get(IL_CAL_UNIX));
		$this->object->enableSubscriptionMembershipLimitation((int) $_POST['subscription_membership_limitation']);
		$this->object->setSubscriptionMaxMembers((int) $_POST['subscription_max']);
		$this->object->enableWaitingList((int) $_POST['waiting_list']);
		#$this->object->setSubscriptionNotify((int) $_POST['subscription_notification']);
		$this->object->setViewMode((int) $_POST['view_mode']);

		if($this->object->getViewMode() == IL_CRS_VIEW_TIMING)
		{
			$this->object->setOrderType(ilContainer::SORT_ACTIVATION);
		}
		else
		{
			$this->object->setOrderType((int) $_POST['order_type']);
		}
		$this->object->setArchiveStart($archive_start->get(IL_CAL_UNIX));
		$this->object->setArchiveEnd($archive_end->get(IL_CAL_UNIX));
		$this->object->setArchiveType($_POST['archive_type']);
		$this->object->setAboStatus((int) $_POST['abo']);
		$this->object->setShowMembers((int) $_POST['show_members']);

		if($this->object->validate())
		{
			$this->object->update();

			// BEGIN ChangeEvent: Record write event
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			if (ilChangeEvent::_isActive())
			{
				global $ilUser;
				ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
				ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
			}
			// END ChangeEvent: Record write event

			// Update ecs export settings
			if(!$this->updateECSExportSettings())
			{
				$this->editObject();
				return false;
			}

			ilUtil::sendInfo($this->lng->txt('settings_saved'));
		}
		else
		{
			ilUtil::sendInfo($this->object->getMessage());
		}
		$this->editObject();
	}

	/**
	 * Update ECS Export Settings
	 *
	 * @access protected
	 */
	protected function updateECSExportSettings()
	{
		global $rbacadmin;

		include_once('./Services/WebServices/ECS/classes/class.ilECSSettings.php');
		
		// ECS enabled
		$ecs_settings = ilECSSettings::_getInstance();
		if(!$ecs_settings->isEnabled())
		{
			return true;
		}
		if($_POST['ecs_export'] and !$_POST['ecs_owner'])
		{
			ilUtil::sendInfo($this->lng->txt('ecs_no_owner'));
			return false;
		}
		try
		{
			$this->object->handleECSSettings((bool) $_POST['ecs_export'],(int) $_POST['ecs_owner'],(array) $_POST['ecs_mids']);
			
			// update performed now grant/revoke ecs user permissions
			include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
			$export = new ilECSExport($this->object->getId());
			if($export->isExported())
			{
				// Grant permission
				$rbacadmin->grantPermission($ecs_settings->getGlobalRole(),
					ilRbacReview::_getOperationIdsByName(array('join','visible')),
					$this->object->getRefId());
				
			}
			else
			{
				$rbacadmin->revokePermission($this->object->getRefId(),
					$ecs_settings->getGlobalRole());
			}
		}
		catch(ilECSConnectorException $exc)
		{
			ilUtil::sendInfo('Error connecting to ECS server: '.$exc->getMessage());
			return false;
		}
		catch(ilECSContentWriterException $exc)
		{
			ilUtil::sendInfo('Course export failed with message: '.$exc->getMessage());
			return false;
		}
		return true;
	}
	
	
	/**
	 * edit object
	 *
	 * @access public
	 * @return
	 */
	public function editObject()
	{
		$this->checkPermission('write');
		
		$this->setSubTabs('properties');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_settings');
		
		$this->initForm();

		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * init form
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initForm()
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		
		if(!is_object($this->form))
		{
			$this->form = new ilPropertyFormGUI();
		}

		$this->form->setTitle($this->lng->txt('crs_edit'));
		$this->form->setTitleIcon(ilUtil::getImagePath('icon_crs_s.gif'));
	
		$this->form->addCommandButton('update',$this->lng->txt('save'));
		$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$this->form->setTableWidth('75%');
		$this->form->setFormAction($this->ctrl->getFormAction($this));
		
		// title
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setValue($this->object->getTitle());
		$title->setSize(40);
		$title->setMaxLength(128);
		$title->setRequired(true);
		$this->form->addItem($title);
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		$desc->setValue($this->object->getLongDescription());
		$desc->setRows(2);
		$desc->setCols(40);
		$this->form->addItem($desc);
		
		// reg type
		$act_type = new ilRadioGroupInputGUI($this->lng->txt('crs_visibility'),'activation_type');
		$act_type->setValue($this->object->getActivationType());
		
			$opt = new ilRadioOption($this->lng->txt('crs_visibility_unvisible'),IL_CRS_ACTIVATION_OFFLINE);
			$opt->setInfo($this->lng->txt('crs_availability_unvisible_info'));
			$act_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_visibility_limitless'),IL_CRS_ACTIVATION_UNLIMITED);
			$opt->setInfo($this->lng->txt('crs_availability_limitless_info'));
			$act_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_visibility_until'),IL_CRS_ACTIVATION_LIMITED);
			$opt->setInfo($this->lng->txt('crs_availability_until_info'));

				$start = new ilDateTimeInputGUI($this->lng->txt('crs_start'),'activation_start');
				$start->setShowTime(true);
				$start_date = new ilDateTime($this->object->getActivationStart(),IL_CAL_UNIX);
				$start->setDate($start_date);
				$opt->addSubItem($start);

				$end = new ilDateTimeInputGUI($this->lng->txt('crs_end'),'activation_end');
				$end->setShowTime(true);
				$end_date = new ilDateTime($this->object->getActivationEnd(),IL_CAL_UNIX);
				$end->setDate($end_date);
				$opt->addSubItem($end);
				
			$act_type->addOption($opt);
		
		$this->form->addItem($act_type);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('crs_reg'));
		$this->form->addItem($section);
		
		$reg_type = new ilRadioGroupInputGUI($this->lng->txt('crs_reg_period'),'subscription_limitation_type');
		$reg_type->setValue($this->object->getSubscriptionLimitationType());		
		
			$opt = new ilRadioOption($this->lng->txt('crs_reg_deactivated'),IL_CRS_SUBSCRIPTION_DEACTIVATED);
			$opt->setInfo($this->lng->txt('crs_registration_deactivated'));
			$reg_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_registration_unlimited'),IL_CRS_SUBSCRIPTION_UNLIMITED);
			$opt->setInfo($this->lng->txt('crs_reg_unlim_info'));
			$reg_type->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_registration_limited'),IL_CRS_SUBSCRIPTION_LIMITED);
			$opt->setInfo($this->lng->txt('crs_reg_lim_info'));

				$start = new ilDateTimeInputGUI($this->lng->txt('crs_start'),'subscription_start');
				$start->setShowTime(true);
				$start_date = new ilDateTime($this->object->getSubscriptionStart(),IL_CAL_UNIX);
				$start->setDate($start_date);
				$opt->addSubItem($start);

				$end = new ilDateTimeInputGUI($this->lng->txt('crs_end'),'subscription_end');
				$end->setShowTime(true);
				$end_date = new ilDateTime($this->object->getSubscriptionEnd(),IL_CAL_UNIX);
				$end->setDate($end_date);
				$opt->addSubItem($end);
				
			$reg_type->addOption($opt);

		$this->form->addItem($reg_type);
		
		
		
		$reg_proc = new ilRadioGroupInputGUI($this->lng->txt('crs_registration_type'),'subscription_type');
		$reg_proc->setValue($this->object->getSubscriptionType());
		$reg_proc->setInfo($this->lng->txt('crs_reg_type_info'));

			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_confirmation'),IL_CRS_SUBSCRIPTION_CONFIRMATION);
			$reg_proc->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_direct'),IL_CRS_SUBSCRIPTION_DIRECT);
			$reg_proc->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_password'),IL_CRS_SUBSCRIPTION_PASSWORD);
			
				$pass = new ilTextInputGUI('','subscription_password');
				$pass->setSize(12);
				$pass->setMaxLength(12);
				$pass->setValue($this->object->getSubscriptionPassword());
			
			$opt->addSubItem($pass);
			$reg_proc->addOption($opt);

		$this->form->addItem($reg_proc);
		
		
		$lim = new ilCheckboxInputGUI($this->lng->txt('crs_subscription_max_members_short'),'subscription_membership_limitation');
		$lim->setValue(1);
		$lim->setOptionTitle($this->lng->txt('crs_subscription_max_members'));
		$lim->setChecked($this->object->isSubscriptionMembershipLimited());
		
			$max = new ilTextInputGUI('','subscription_max');
			$max->setSize(4);
			$max->setMaxLength(4);
			$max->setValue($this->object->getSubscriptionMaxMembers() ? $this->object->getSubscriptionMaxMembers() : '');
			$max->setTitle($this->lng->txt('members').':');
			$max->setInfo($this->lng->txt('crs_reg_max_info'));
		
		$lim->addSubItem($max);
		
			$wait = new ilCheckboxInputGUI('','waiting_list');
			$wait->setOptionTitle($this->lng->txt('crs_waiting_list'));
			$wait->setChecked($this->object->enabledWaitingList());
			$wait->setInfo($this->lng->txt('crs_wait_info'));
			$lim->addSubItem($wait);
		
		$this->form->addItem($lim);
		
		$pres = new ilFormSectionHeaderGUI();
		$pres->setTitle($this->lng->txt('crs_view_mode'));
		
		$this->form->addItem($pres);
		
		// presentation type
		$view_type = new ilRadioGroupInputGUI($this->lng->txt('crs_presentation_type'),'view_mode');
		$view_type->setValue($this->object->getViewMode());
		
			$opt = new ilRadioOption($this->lng->txt('cntr_view_sessions'),IL_CRS_VIEW_SESSIONS);
			$opt->setInfo($this->lng->txt('cntr_view_info_sessions'));
			$view_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('cntr_view_simple'),IL_CRS_VIEW_SIMPLE);
			$opt->setInfo($this->lng->txt('cntr_view_info_simple'));
			$view_type->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('cntr_view_by_type'),IL_CRS_VIEW_BY_TYPE);
			$opt->setInfo($this->lng->txt('cntr_view_info_by_type'));
			$view_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_view_objective'),IL_CRS_VIEW_OBJECTIVE);
			$opt->setInfo($this->lng->txt('crs_view_info_objective'));
			$view_type->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_view_timing'),IL_CRS_VIEW_TIMING);
			$opt->setInfo($this->lng->txt('crs_view_info_timing'));
			$view_type->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_view_archive'),IL_CRS_VIEW_ARCHIVE);
			$opt->setInfo($this->lng->txt('crs_archive_info'));
			
				$down = new ilCheckboxInputGUI('','archive_type');
				$down->setOptionTitle($this->lng->txt('crs_archive_download'));
				$down->setChecked($this->object->getArchiveType() == IL_CRS_ARCHIVE_DOWNLOAD);
				$opt->addSubItem($down);
				
				$start = new ilDateTimeInputGUI($this->lng->txt('crs_start'),'archive_start');
				$start->setShowTime(true);
				$start_date = new ilDateTime($this->object->getArchiveStart(),IL_CAL_UNIX);
				$start->setDate($start_date);
				$opt->addSubItem($start);

				$end = new ilDateTimeInputGUI($this->lng->txt('crs_end'),'archive_end');
				$end->setShowTime(true);
				$end_date = new ilDateTime($this->object->getArchiveEnd(),IL_CAL_UNIX);
				$end->setDate($end_date);
				$opt->addSubItem($end);
				
			$view_type->addOption($opt);
		$this->form->addItem($view_type);
		
		// sorting type
		$sort = new ilRadioGroupInputGUI($this->lng->txt('crs_sortorder_abo'),'order_type');
		$sort->setValue($this->object->getOrderType());
		
			$opt = new ilRadioOption($this->lng->txt('crs_sort_title'),ilContainer::SORT_TITLE);
			$opt->setInfo($this->lng->txt('crs_sort_title_info'));
			$sort->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_sort_manual'),ilContainer::SORT_MANUAL);
			$opt->setInfo($this->lng->txt('crs_sort_manual_info'));
			$sort->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_sort_activation'),ilContainer::SORT_ACTIVATION);
			$opt->setInfo($this->lng->txt('crs_sort_timing_info'));
			$sort->addOption($opt);

		$this->form->addItem($sort);
		
		$further = new ilFormSectionHeaderGUI();
		$further->setTitle($this->lng->txt('crs_further_settings'));
		$this->form->addItem($further);
		
		$desk = new ilCheckboxInputGUI($this->lng->txt('crs_add_remove_from_desktop'),'abo');
		$desk->setChecked($this->object->getAboStatus());
		$desk->setInfo($this->lng->txt('crs_add_remove_from_desktop_info'));
		$this->form->addItem($desk);
		
		$mem = new ilCheckboxInputGUI($this->lng->txt('crs_show_members'),'show_members');
		$mem->setChecked($this->object->getShowMembers());
		$mem->setInfo($this->lng->txt('crs_show_members_info'));
		$this->form->addItem($mem);
		
		$this->fillECSExportSettings();
	}
				
	/**
	 * 
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function fillECSExportSettings()
	{
		global $ilLog;
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSSettings.php');
		
		// ECS enabled
		$ecs_settings = ilECSSettings::_getInstance();
		if(!$ecs_settings->isEnabled())
		{
			return true;
		}
		
		$this->lng->loadLanguageModule('ecs');
		
		$ecs = new ilFormSectionHeaderGUI();
		$ecs->setTitle($this->lng->txt('ecs_export'));
		$this->form->addItem($ecs);
		
		include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
		$ecs_export = new ilECSExport($this->object->getId());
		
		$exp = new ilRadioGroupInputGUI($this->lng->txt('ecs_export_obj_settings'),'ecs_export');
		$exp->setRequired(true);
		$exp->setValue($ecs_export->isExported() ? 1 : 0);
		
			$off = new ilRadioOption($this->lng->txt('ecs_export_disabled'),0);
			$exp->addOption($off);
			
			$on = new ilRadioOption($this->lng->txt('ecs_export_enabled'),1);
			$exp->addOption($on);
			
		$this->form->addItem($exp);

		try
		{
			$owner = 0;
			$members = array();
			if($ecs_export->getEContentId())
			{
				include_once('./Services/WebServices/ECS/classes/class.ilECSEContentReader.php');
				
				$econtent_reader = new ilECSEContentReader($ecs_export->getEContentId());
				$econtent_reader->read();
				if($content = $econtent_reader->getEContent())
				{
					$members = $content[0]->getParticipants();
					$owner = $content[0]->getOwner();
				}
			}
			
			include_once('./Services/WebServices/ECS/classes/class.ilECSCommunityReader.php');
			$reader = ilECSCommunityReader::_getInstance();
			if(count($parts = $reader->getPublishableParticipants()) > 1)
			{
				$ilLog->write(__METHOD__.': Found '.count($parts).' participants for publishing');
				$publish_as = new ilCustomInputGUI('','');
				$publish_as->setHtml('<strong>'.$this->lng->txt('ecs_publish_as').'</strong>');
				
				$coms = new ilRadioGroupInputGUI('','ecs_owner');
				$coms->setValue($owner);
				
				foreach($parts as $participant)
				{
					$community = $reader->getCommunityById($participant->getCommunityId());
					
					$part = new ilRadioOption($community->getTitle(),$participant->getMID());
					$part->setInfo($community->getDescription());
					$coms->addOption($part);
				}
				$publish_as->addSubItem($coms);
				$on->addSubItem($publish_as);
			}
			//elseif(count($parts) == 1)
			else
			{
				$ilLog->write(__METHOD__.': Found '.count($parts).' participants for publishing');
				$hidden = new ilHiddenInputGUI('ecs_owner');
				$owner_ids = $reader->getOwnMIDs();
				$hidden->setValue($owner_ids[0]);
				$this->form->addItem($hidden);
			}
			
			$publish_for = new ilCustomInputGUI('','');
			$publish_for->setHtml('<strong>'.$this->lng->txt('ecs_publish_for').'</strong>');
			
			foreach($reader->getEnabledParticipants() as $participant)
			{
				$community = $reader->getCommunityById($participant->getCommunityId());
				
				$com = new ilCheckboxInputGUI('','ecs_mids[]');
				$com->setOptionTitle($community->getTitle().': '.$participant->getParticipantName());
				$com->setValue($participant->getMID());
				$com->setChecked(in_array($participant->getMID(),$members));
				$publish_for->addSubItem($com);
			}
			
			$on->addSubItem($publish_for);
		}
		catch(ilECSConnectorException $exc)
		{
			$ilLog->write(__METHOD__.': Error connecting to ECS server. '.$exc->getMessage());
			return true;
		}
		catch(ilECSReaderException $exc)
		{
			$ilLog->write(__METHOD__.': Error parsing ECS query: '.$exc->getMessage());
			return true;
		}
		return true;		
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
			$this->object->saveIcons($_FILES["cont_big_icon"]['tmp_name'],
				$_FILES["cont_small_icon"]['tmp_name'], $_FILES["cont_tiny_icon"]['tmp_name']);
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
				// only show if export permission pis granted
				$privacy = ilPrivacySettings::_getInstance();
				if($rbacsystem->checkAccess('export_member_data',$privacy->getPrivacySettingsRefId()) and
					($privacy->enabledExport() or
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
													 $this->ctrl->getLinkTargetByClass('ilsessionoverviewgui','listSessions'),
													 "", 'ilsessionoverviewgui');
				}

				include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
				$privacy = ilPrivacySettings::_getInstance();
				// only show export tab when write AND export permission pis granted
				if($ilAccess->checkAccess('write','',$this->object->getRefId()) 
					&& $privacy->enabledExport() and $rbacsystem->checkAccess('export_member_data',$privacy->getPrivacySettingsRefId()))
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
		
		// BEGIN ChangeEvent: Record write event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			global $ilUser;
			ilChangeEvent::_recordWriteEvent($newObj->getId(), $ilUser->getId(), 'create');
		}
		// END ChangeEvent: Record write event.

		// always send a message
		ilUtil::sendInfo($this->lng->txt("crs_added"),true);
		
		$this->ctrl->setParameter($this, "ref_id", $newObj->getRefId());
		ilUtil::redirect($this->getReturnLocation("save",
			$this->ctrl->getLinkTarget($this, "edit")));
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
	
	/**
	 * set preferences (show/hide tabel content)
	 *
	 * @access public
	 * @return
	 */
	public function setShowHidePrefs()
	{
		global $ilUser;
		
		if(isset($_GET['admin_hide']))
		{
			$ilUser->writePref('crs_admin_hide',(int) $_GET['admin_hide']);
		}
		if(isset($_GET['tutor_hide']))
		{
			$ilUser->writePref('crs_tutor_hide',(int) $_GET['tutor_hide']);
		}
		if(isset($_GET['member_hide']))
		{
			$ilUser->writePref('crs_member_hide',(int) $_GET['member_hide']);
		}
		if(isset($_GET['subscriber_hide']))
		{
			$ilUser->writePref('crs_subscriber_hide',(int) $_GET['subscriber_hide']);
		}
		if(isset($_GET['wait_hide']))
		{
			$ilUser->writePref('crs_wait_hide',(int) $_GET['wait_hide']);
		}
	}
	
	protected function readMemberData($ids,$role = 'admin')
	{
		if($this->show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = ilLPStatusWrapper::_getCompleted($this->object->getId());
			$in_progress = ilLPStatusWrapper::_getInProgress($this->object->getId());
			$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->object->getId());
			$failed = ilLPStatusWrapper::_getFailed($this->object->getId());
		}
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();

		if($privacy->enabledCourseAccessTimes())
		{
			include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
			$progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
		}

		foreach($ids as $usr_id)
		{
			$name = ilObjUser::_lookupName($usr_id);
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['lastname'] = $name['lastname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);
			$tmp_data['passed'] = $this->object->members_obj->hasPassed($usr_id) ? 1 : 0;
			$tmp_data['notification'] = $this->object->members_obj->isNotificationEnabled($usr_id) ? 1 : 0;
			$tmp_data['blocked'] = $this->object->members_obj->isBlocked($usr_id) ? 1 : 0;
			$tmp_data['usr_id'] = $usr_id;

			if($this->show_tracking)
			{
				if(in_array($usr_id,$completed))
				{
					$tmp_data['progress'] = LP_STATUS_COMPLETED;
				}
				elseif(in_array($usr_id,$in_progress))
				{
					$tmp_data['progress'] = LP_STATUS_IN_PROGRESS;
				}
				elseif(in_array($usr_id,$failed))
				{
					$tmp_data['progress'] = LP_STATUS_FAILED;
				}
				else
				{
					$tmp_data['progress'] = LP_STATUS_NOT_ATTEMPTED;
				}
			}

			if($privacy->enabledCourseAccessTimes())
			{
				if(isset($progress[$usr_id]['ts']) and $progress[$usr_id]['ts'])
				{
					$tmp_data['access_time'] = ilDatePresentation::formatDate(new ilDateTime($progress[$usr_id]['ts'],IL_CAL_DATETIME));
				}
				else
				{
					$tmp_data['access_time'] = $this->lng->txt('no_date');
				}
			}
			$members[] = $tmp_data;
		}
		return $members ? $members : array();
	}
	
	
	
	/**
	 * member administration
	 *
	 * @access protected
	 * @return
	 */
	protected function membersObject()
	{
		global $ilUser, $rbacsystem, $ilToolbar, $lng, $ilCtrl, $tpl;
		
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		include_once('./Modules/Course/classes/class.ilCourseParticipantsTableGUI.php');
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		include_once('./Services/Tracking/classes/class.ilLPObjSettings.php');
		
		
		if(isset($_GET['member_table_nav']))
		{
			list($_SESSION['crs_print_sort'],$_SESSION['crs_print_order'],$tmp) = explode(':',$_GET['member_table_nav']);
		}

		$this->checkPermission('write');
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and 
			ilObjUserTracking::_enabledUserRelatedData() and
			ilLPObjSettings::_lookupMode($this->object->getId()) != LP_MODE_DEACTIVATED);
		$part = ilCourseParticipants::_getInstanceByObjId($this->object->getId());

		include_once('./Modules/Course/classes/class.ilCourseItems.php');
		$this->timings_enabled = (ilCourseItems::_hasTimings($this->object->getRefId()) and 
			($this->object->getViewMode() == IL_CRS_VIEW_TIMING));
			
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.crs_edit_members.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		
		// add members
		
		// user input
		include_once("./Services/Form/classes/class.ilUserLoginAutoCompleteInputGUI.php");
		$ul = new ilUserLoginAutoCompleteInputGUI($lng->txt("user"), "user_login", $this, "addMemberAutoComplete");
		$ul->setSize(15);
		$ilToolbar->addInputItem($ul, true);

		// member type
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array(
			ilCourseContants::CRS_MEMBER => $lng->txt("crs_member"),
			ilCourseContants::CRS_TUTOR => $lng->txt("crs_tutor"),
			ilCourseContants::CRS_ADMIN => $lng->txt("crs_admin")
			);
		$si = new ilSelectInputGUI("", "member_type");
		$si->setOptions($options);
		$ilToolbar->addInputItem($si);
		
		// add button
		$ilToolbar->addFormButton($lng->txt("add"), "addAsMember");
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		
		// spacer
		$ilToolbar->addSeparator();

		// search button
		$ilToolbar->addButton($this->lng->txt("crs_search_users"),
			$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
			
		// separator
		$ilToolbar->addSeparator();
			
		// print button
		$ilToolbar->addButton($this->lng->txt("crs_print_list"),
			$this->ctrl->getLinkTarget($this, 'printMembers'), "_blank");


		$this->setShowHidePrefs();
		
		// Waiting list table
		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->object->getId());
		if(count($wait = $waiting_list->getAllUsers()))
		{
			include_once('./Services/Membership/classes/class.ilWaitingListTableGUI.php');
			if($ilUser->getPref('crs_wait_hide'))
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,false);
				$this->ctrl->setParameter($this,'wait_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,true);
				$this->ctrl->setParameter($this,'wait_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setUsers($wait);
			$table_gui->setTitle($this->lng->txt('crs_waiting_list'),'icon_usr.gif',$this->lng->txt('crs_waiting_list'));
			$this->tpl->setVariable('TABLE_WAIT',$table_gui->getHTML());
		}

		// Subscriber table
		if(count($subscribers = $part->getSubscribers()))
		{
			include_once('./Services/Membership/classes/class.ilSubscriberTableGUI.php');
			if($ilUser->getPref('crs_subscriber_hide'))
			{
				$table_gui = new ilSubscriberTableGUI($this,$part,false);
				$this->ctrl->setParameter($this,'subscriber_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilSubscriberTableGUI($this,$part,true);
				$this->ctrl->setParameter($this,'subscriber_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setSubscribers($subscribers);
			$table_gui->setTitle($this->lng->txt('group_new_registrations'),'icon_usr.gif',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_SUB',$table_gui->getHTML());
		}
				
		
		
		if(count($part->getAdmins()))
		{
			// Security: display the list of course administrators read-only, 
			// if the user doesn't have the 'edit_permission' permission. 
 			$showEditLink = $rbacsystem->checkAccess("edit_permission", $this->object->getRefId());
			if($ilUser->getPref('crs_admin_hide'))
			{
				$table_gui = new ilCourseParticipantsTableGUI($this,'admin',false,$this->show_tracking,$this->timings_enabled, $showEditLink);
				$this->ctrl->setParameter($this,'admin_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI($this,'admin',true,$this->show_tracking,$this->timings_enabled, $showEditLink);
				$this->ctrl->setParameter($this,'admin_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_administrators'),'icon_usr.gif',$this->lng->txt('crs_administrators'));
			$table_gui->setData($this->readMemberData($part->getAdmins()));
			$this->tpl->setVariable('ADMINS',$table_gui->getHTML());	
		}
		if(count($part->getTutors()))
		{
			if($ilUser->getPref('crs_tutor_hide'))
			{
				$table_gui = new ilCourseParticipantsTableGUI($this,'tutor',false,$this->show_tracking,$this->timings_enabled);
				$this->ctrl->setParameter($this,'tutor_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI($this,'tutor',true,$this->show_tracking,$this->timings_enabled);
				$this->ctrl->setParameter($this,'tutor_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_tutors'),'icon_usr.gif',$this->lng->txt('crs_tutors'));
			$table_gui->setData($this->readMemberData($part->getTutors()));
			$this->tpl->setVariable('TUTORS',$table_gui->getHTML());	
		}
		if(count($part->getMembers()))
		{
			if($ilUser->getPref('crs_member_hide'))
			{
				$table_gui = new ilCourseParticipantsTableGUI($this,'member',false,$this->show_tracking,$this->timings_enabled);
				$this->ctrl->setParameter($this,'member_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI($this,'member',true,$this->show_tracking,$this->timings_enabled);
				$this->ctrl->setParameter($this,'member_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
				
			$table_gui->setTitle($this->lng->txt('crs_members'),'icon_usr.gif',$this->lng->txt('crs_members'));
			$table_gui->setData($this->readMemberData($part->getMembers()));
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());	
			
		}
		
		
		$this->tpl->setVariable('TXT_SELECTED_USER',$this->lng->txt('crs_selected_users'));
		$this->tpl->setVariable('BTN_FOOTER_EDIT',$this->lng->txt('edit'));
		$this->tpl->setVariable('BTN_FOOTER_VAL',$this->lng->txt('remove'));
		$this->tpl->setVariable('BTN_FOOTER_MAIL',$this->lng->txt('crs_mem_send_mail'));
		$this->tpl->setVariable('ARROW_DOWN',ilUtil::getImagePath('arrow_downright.gif'));
		
	}
	
	/**
	* Add Member for autoComplete
	*/
	function addMemberAutoCompleteObject()
	{
		include_once("./Services/Form/classes/class.ilUserLoginAutoCompleteInputGUI.php");
		ilUserLoginAutoCompleteInputGUI::echoAutoCompleteList();
	}


	/**
	 * update admin status
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateAdminStatusObject()
	{
		$this->checkPermission('write');
		
		$visible_members = array_intersect(array_unique((array) $_POST['visible_member_ids']),$this->object->members_obj->getAdmins());
		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$notification = is_array($_POST['notification']) ? $_POST['notification'] : array();
		
		$this->updateParticipantsStatus('admins',$visible_members,$passed,$notification,array());
	}
	
	/**
	 * update tuto status
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateTutorStatusObject()
	{
		$this->checkPermission('write');
		
		$visible_members = array_intersect(array_unique((array) $_POST['visible_member_ids']),$this->object->members_obj->getTutors());
		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$notification = is_array($_POST['notification']) ? $_POST['notification'] : array();

		$this->updateParticipantsStatus('admins',$visible_members,$passed,$notification,array());
	}
	
	/**
	 * updateMemberStatus
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateMemberStatusObject()
	{
		$this->checkPermission('write');
		
		$visible_members = array_intersect(array_unique((array) $_POST['visible_member_ids']),$this->object->members_obj->getMembers());
		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$blocked = is_array($_POST['blocked']) ? $_POST['blocked'] : array();
		
		$this->updateParticipantsStatus('members',$visible_members,$passed,array(),$blocked);
	
	}

	function updateParticipantsStatus($type,$visible_members,$passed,$notification,$blocked)
	{
		global $ilAccess,$ilErr,$ilUser,$rbacadmin;

		foreach($visible_members as $member_id)
		{
			$this->object->members_obj->updatePassed($member_id,in_array($member_id,$passed));
			switch($type)
			{
				case 'admins';
					$this->object->members_obj->updateNotification($member_id,in_array($member_id,$notification));
					$this->object->members_obj->updateBlocked($member_id,false);
					break;
					
				case 'members':
					$this->object->members_obj->updateNotification($member_id,false);
					$this->object->members_obj->updateBlocked($member_id,in_array($member_id,$blocked));
					break;
			}
		}
		
		ilUtil::sendInfo($this->lng->txt('settings_saved'));
		$this->membersObject();
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
					$message = '';
					// Check if user is member in course grouping
					foreach(ilObjCourseGrouping::_getGroupingCourseIds($this->object->getRefId(),
						$this->object->getId()) as $course_data)
					{
						$tmp_members = ilCourseParticipants::_getInstanceByObjId($course_data['id']);
						if($course_data['id'] != $this->object->getId() and
							$tmp_members->isGroupingMember($tmp_obj->getId(),$course_data['unique']))
						{
							$message .= ('<br /><font class="alert">'.$this->lng->txt('crs_member_of').' ');
							$message .= (ilObject::_lookupTitle($course_data['id'])."</font>");
						}
					}
					
					$f_result[$counter][]	= ilUtil::formCheckbox(0,"waiting_list[]",$waiting_data['usr_id']);
					$f_result[$counter][]	= $tmp_obj->getLastname().', '.$tmp_obj->getFirstname().$message;
					$f_result[$counter][]   = $tmp_obj->getLogin();
					$f_result[$counter][] = ilDatePresentation::formatDate(new ilDateTime($waiting_data['time'],IL_CAL_UNIX));
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
					$f_result[$counter][]	= $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();
					$f_result[$counter][]	= $tmp_obj->getLogin();
					$f_result[$counter][] = ilDatePresentation::formatDate(new ilDateTime($member_data['time'],IL_CAL_UNIX));

					unset($tmp_obj);
					++$counter;
				}
			}
			$this->__showSubscribersTable($f_result,$subscriber_ids);

		} // END SUBSCRIBERS
	}
	
	/**
	 * edit member 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function editMemberObject()
	{
		$_POST['members'] = array((int) $_GET['member_id']);
		$this->editMembersObject();
		return true;
	}
	
	
	/**
	 * edit members
	 *
	 * @access public
	 * @return
	 */
	public function editMembersObject()
	{
		$this->checkPermission('write');
		
		$participants = array_unique(array_merge((array) $_POST['admins'],(array) $_POST['tutors'],(array) $_POST['members']));
		
		if(!count($participants))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');
		
		include_once('./Modules/Course/classes/class.ilCourseEditParticipantsTableGUI.php');
		$table_gui = new ilCourseEditParticipantsTableGUI($this);
		$table_gui->setTitle($this->lng->txt('crs_header_edit_members'),'icon_usr.gif',$this->lng->txt('crs_header_edit_members'));
		$table_gui->setData($this->readMemberData($participants));

		$this->tpl->setContent($table_gui->getHTML());
		return true;
	}
	
	/**
	 * update members
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateMembersObject()
	{
		global $rbacsystem, $rbacreview;
                
		$this->checkPermission('write');
		
		if(!count($_POST['participants']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		$notifications = $_POST['notification'] ? $_POST['notification'] : array();
		$passed = $_POST['passed'] ? $_POST['passed'] : array();
		$blocked = $_POST['blocked'] ? $_POST['blocked'] : array();
		
		// Determine whether the user has the 'edit_permission' permission
		$hasEditPermissionAccess = $rbacsystem->checkAccess('edit_permission', $this->object->getRefId());

		// Get all assignable local roles of the course object, and
		// determine the role id of the course administrator role.
		$assignableLocalCourseRoles = array();
        $courseAdminRoleId = null;
		foreach ($this->object->getLocalCourseRoles(false) as $title => $role_id)
		{
			if (substr($title, 0, 12) == 'il_crs_admin')
			{
				$courseAdminRoleId = $role_id;
			}
                        $assignableLocalCourseRoles[$role_id] = $title;
		}
                
		// Validate the user ids and role ids in the post data
		foreach($_POST['participants'] as $usr_id)
		{
			$memberIsCourseAdmin = $rbacreview->isAssigned($usr_id, $courseAdminRoleId);
                        
			// If the current user doesn't have the 'edit_permission' 
                        // permission, make sure he doesn't remove the course
                        // administrator role of members who are course administrator.
			if (! $hasEditPermissionAccess && $memberIsCourseAdmin &&
				! in_array($courseAdminRoleId, $_POST['roles'][$usr_id])
			)
			{
				$_POST['roles'][$usr_id][] = $courseAdminRoleId;
			}
                        
			// Validate the role ids in the post data
			foreach ((array) $_POST['roles'][$usr_id] as $role_id)
			{
				if (! array_key_exists($role_id, $assignableLocalCourseRoles))
				{
					ilUtil::sendInfo($this->lng->txt('msg_no_perm_perm'));
					$this->membersObject();
					return false;
		        }
		        if (!$hasEditPermissionAccess && 
					$role_id == $courseAdminRoleId &&
					! $memberIsCourseAdmin)
				{
					ilUtil::sendInfo($this->lng->txt('msg_no_perm_perm'));
					$this->membersObject();
					return false;
				}
			}
		}                        
                
		foreach($_POST['participants'] as $usr_id)
		{
			$this->object->members_obj->updateRoleAssignments($usr_id,(array) $_POST['roles'][$usr_id]);
			
			// Disable notification for all of them
			$this->object->members_obj->updateNotification($usr_id,0);
			if(($this->object->members_obj->isTutor($usr_id) or $this->object->members_obj->isAdmin($usr_id)) and in_array($usr_id,$notifications))
			{
				$this->object->members_obj->updateNotification($usr_id,1);
			}
			
			$this->object->members_obj->updateBlocked($usr_id,0);
			if((!$this->object->members_obj->isAdmin($usr_id) and !$this->object->members_obj->isTutor($usr_id)) and in_array($usr_id,$blocked))
			{
				$this->object->members_obj->updateBlocked($usr_id,1);
			}
			$this->object->members_obj->updatePassed($usr_id,in_array($usr_id,$passed));
			$this->object->members_obj->sendNotification(
				$this->object->members_obj->NOTIFY_STATUS_CHANGED,
				$usr_id);
		}
		ilUtil::sendInfo($this->lng->txt("msg_obj_modified"));
		$this->membersObject();
		return true;		
	
	}
	
	


	function updateMemberObject()
	{
		global $rbacsystem;

		$this->object->initCourseMemberObject();

		$this->checkPermission('write');

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

	/**
	* Add user as member
	*/
	public function addAsMemberObject()
	{	
		$users = explode(',', $_POST['user_login']);
		foreach($users as $user)
		{
			$_POST['user'][] = ilObjUser::_lookupId($user);
		}
		
		if(!$this->assignMembersObject())
		{
			$this->membersObject();
		}
	}
	
	public function assignMembersObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["user"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"));
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
			
			switch($_POST['member_type'])
			{
				case ilCourseContants::CRS_MEMBER:
					$this->object->members_obj->add($user_id,IL_CRS_MEMBER);
					break;
				case ilCourseContants::CRS_TUTOR:
					$this->object->members_obj->add($user_id,IL_CRS_TUTOR);
					break;
				case ilCourseContants::CRS_ADMIN:
					$this->object->members_obj->add($user_id,IL_CRS_ADMIN);
					break;
				
			}
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
		ilUtil::sendFailure($this->lng->txt("crs_users_already_assigned"));
		
		return false;
	}

	public function assignFromWaitingListObject()
	{
		global $rbacsystem;

		$this->checkPermission('write');

		if(!count($_POST["waiting"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();

		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->object->getId());

		$added_users = 0;
		foreach($_POST["waiting"] as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			if($this->object->members_obj->isAssigned($user_id))
			{
				continue;
			}
			$this->object->members_obj->add($user_id,IL_CRS_MEMBER);
			$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_ACCEPT_USER,$user_id);
			$waiting_list->removeFromList($user_id);

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
			$this->membersObject();
			return false;
		}
	}
	
	/**
	 * refuse from waiting list
	 *
	 * @access public
	 * @return
	 */
	public function refuseFromListObject()
	{
		global $ilUser;
		
		$this->checkPermission('write');
		
		if(!count($_POST['waiting']))
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->object->getId());

		foreach($_POST["waiting"] as $user_id)
		{
			$waiting_list->removeFromList($user_id);
			$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_DISMISS_SUBSCRIBER,$user_id);
		}
		
		ilUtil::sendInfo($this->lng->txt('crs_users_removed_from_list'));
		$this->membersObject();
		return true;
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

		
	public function assignSubscribersObject()
	{
		global $rbacsystem,$ilErr;


		$this->checkPermission('write');

		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();
		
		if(!$this->object->members_obj->assignSubscribers($_POST["subscribers"]))
		{
			ilUtil::sendInfo($ilErr->getMessage());
			$this->membersObject();
			return false;
		}
		else
		{
			foreach($_POST["subscribers"] as $usr_id)
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

		$this->checkPermission('write');
		$this->object->initCourseMemberObject();

		if($this->object->isSubscriptionMembershipLimited() and $this->object->getSubscriptionMaxMembers() and 
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
				$f_result[$counter][]   = ilDatePresentation::formatDate(new ilDateTime($member_data['time']),IL_CAL_UNIX);

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
				$f_result[$counter][]   = ilDatePresentation::formatDate(new ilDateTime($user_data['time'],IL_CAL_UNIX));

				unset($tmp_obj);
				++$counter;
			}
		}
		return $this->__showRemoveFromWaitingListTable($f_result);
	}
	
	public function leaveObject()
	{
		$this->checkPermission('leave');
		
		$this->tabs_gui->setTabActive('crs_unsubscribe');
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_unsubscribe_sure.html",'Modules/Course');
		ilUtil::sendQuestion($this->lng->txt('crs_unsubscribe_sure'));
		
		$this->tpl->setVariable("UNSUB_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_SUBMIT",'performUnsubscribe');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("crs_unsubscribe"));
		return true;
				
	}
	
	
	function unsubscribeObject()
	{
		global $rbacsystem,$ilAccess;

		// CHECK ACCESS
		if(!$ilAccess->checkAccess("leave",'', $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tabs_gui->setTabActive('crs_unsubscribe');
		#$this->setSubTabs('members');


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_unsubscribe_sure.html",'Modules/Course');
		ilUtil::sendQuestion($this->lng->txt('crs_unsubscribe_sure'));
		
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

	function deleteMembersObject()
	{
		$this->checkPermission('write');
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');
		
		$participants = array_merge((array) $_POST['admins'],(array) $_POST['tutors'], (array) $_POST['members']);
		
		if(!$participants)
		{
			ilUtil::sendInfo($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return true;
		}

		// Check last admin
		$this->object->initCourseMemberObject();
		if(!$this->object->members_obj->checkLastAdmin($participants))
		{
			ilUtil::sendInfo($this->lng->txt('crs_at_least_one_admin'));
			$this->membersObject();

			return false;
		}
		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this,'deleteMembers'));
		$confirm->setHeaderText($this->lng->txt('crs_header_delete_members'));
		$confirm->setConfirm($this->lng->txt('confirm'),'removeMembers');
		$confirm->setCancel($this->lng->txt('cancel'),'members');
		
		foreach($this->readMemberData($participants) as $participant)
		{
			$confirm->addItem('participants[]',
				$participant['usr_id'],
				$participant['lastname'].', '.$participant['firstname'].' ['.$participant['login'].']',
				ilUtil::getImagePath('icon_usr.gif'));
		}
		
		$this->tpl->setContent($confirm->getHTML());
		
	}

	function removeMembersObject()
	{
		global $rbacreview, $rbacsystem;
                
		$this->checkPermission('write');
		
		if(!is_array($_POST["participants"]) or !count($_POST["participants"]))
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_member_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();
		
		// If the user doesn't have the edit_permission, he may not remove
		// members who have the course administrator role
		if (! $rbacsystem->checkAccess('edit_permission', $this->object->getRefId()))
		{
			// Determine the role id of the course administrator role.
			$courseAdminRoleId = null;
			foreach ($this->object->getLocalCourseRoles(false) as $title => $role_id)
			{
				if (substr($title, 0, 12) == 'il_crs_admin')
				{
					$courseAdminRoleId = $role_id;
				}
			}
                
			foreach ($_POST['participants'] as $usr_id)
			{
				if ($rbacreview->isAssigned($usr_id, $courseAdminRoleId))
				{
					ilUtil::sendInfo($this->lng->txt("msg_no_perm_perm"));
					$this->membersObject();
					return false;
				}
			}
		}
        
		if(!$this->object->members_obj->deleteParticipants($_POST["participants"]))
		{
			ilUtil::sendInfo($this->object->getMessage());
			$this->membersObject();

			return false;
		}
		else
		{
			// SEND NOTIFICATION
			foreach($_POST["participants"] as $usr_id)
			{
				$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_DISMISS_MEMBER,$usr_id);
			}
		}
		ilUtil::sendInfo($this->lng->txt("crs_members_deleted"));
		$this->membersObject();

		return true;
	}

	function refuseSubscribersObject()
	{
		global $rbacsystem;

		$this->checkPermission('write');
		
		if(!$_POST['subscribers'])
		{
			ilUtil::sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();
			return false;
		}
	
		$this->object->initCourseMemberObject();

		if(!$this->object->members_obj->deleteSubscribers($_POST["subscribers"]))
		{
			ilUtil::sendInfo($this->object->getMessage());
			$this->membersObject();
			return false;
		}
		else
		{
			foreach($_POST['subscribers'] as $usr_id)
			{
				$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_DISMISS_SUBSCRIBER,$usr_id);
			}
		}

		ilUtil::sendInfo($this->lng->txt("crs_subscribers_deleted"));
		$this->membersObject();
		return true;
	}

	/**
	* Get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$ilAccess,$ilUser, $lng;

		$this->object->initCourseMemberObject();

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if($ilAccess->checkAccess('read','',$this->ref_id))
		{
			$tabs_gui->addTab('view_content', $lng->txt("content"),
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
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_ENABLED &&
			$ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$tabs_gui->addTarget("members",
								 $this->ctrl->getLinkTarget($this, "membersGallery"), 
								 "members",
								 get_class($this));
		}
		
		// learning objectives
		
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			include_once('./Modules/Course/classes/class.ilCourseObjective.php');
			if($this->object->getViewMode() == IL_CRS_VIEW_OBJECTIVE or ilCourseObjective::_getCountObjectives($this->object->getId()))
			{
				$force_active = (strtolower($_GET["cmdClass"]) == "ilcourseobjectivesgui")
					? true
					: false;
				$tabs_gui->addTarget("crs_objectives",
									 $this->ctrl->getLinkTarget($this,"listObjectives"), 
									 "listObjectives",
									 get_class($this), "", $force_active);
			}
		}
		

		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
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
		// Join/Leave
		if($ilAccess->checkAccess('join','',$this->ref_id)
			and !$this->object->members_obj->isAssigned($ilUser->getId()))
		{
			include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
			if(ilCourseWaitingList::_isOnList($ilUser->getId(), $this->object->getId()))
			{
				$tabs_gui->addTab(
					'leave',
					$this->lng->txt('membership_leave'),
					$this->ctrl->getLinkTargetByClass('ilcourseregistrationgui','show','')
				);
					
			}
			else
			{			
				
				$tabs_gui->addTarget("join",
									 $this->ctrl->getLinkTargetByClass('ilcourseregistrationgui', "show"), 
									 'show',
									 "");
			}
		}
		if($ilAccess->checkAccess('leave','',$this->object->getRefId())
			and $this->object->members_obj->isMember($ilUser->getId()))
		{
			$tabs_gui->addTarget("crs_unsubscribe",
								 $this->ctrl->getLinkTarget($this, "unsubscribe"), 
								 'leave',
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
				$print_member[$member_id]['time'] = ilDatePresentation::formatDate(new ilDateTime($member_data['time'],IL_CAL_UNIX));
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
		global $ilAccess,$lng;

		$lng->loadLanguageModule('trac');

		#$is_admin = (bool) $ilAccess->checkAccess("write",'',$this->object->getRefId());
		$is_admin = true;
		
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();

		if($privacy->enabledCourseAccessTimes())
		{
			include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
			$progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
		}
		
		if($this->show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = ilLPStatusWrapper::_getCompleted($this->object->getId());
			$in_progress = ilLPStatusWrapper::_getInProgress($this->object->getId());
			$not_attempted = ilLPStatusWrapper::_getNotAttempted($this->object->getId());
			$failed = ilLPStatusWrapper::_getFailed($this->object->getId());
		}
		
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
				if($privacy->enabledCourseAccessTimes())
				{
					if(isset($progress[$member_id]['ts']) and $progress[$member_id]['ts'])
					{
						$print_member[$member_id]['access'] = ilFormat::formatDate($progress[$member_id]['ts'],'datetime',true); 
					}
					else
					{
						$print_member[$member_id]['access'] = $this->lng->txt('no_date');
					}
				}
				if($this->show_tracking)
				{
					if(in_array($member_id,$completed))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_COMPLETED);
					}
					elseif(in_array($member_id,$in_progress))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_IN_PROGRESS);
					}
					elseif(in_array($member_id,$failed))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_FAILED);
					}
					else
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_NOT_ATTEMPTED);
					}
				}
				
			}
		}
		
		switch($_SESSION['crs_print_sort'])
		{
			case 'progress':
				return ilUtil::sortArray($print_member,'progress',$_SESSION['crs_print_order']);
			
			case 'access_time':
				return ilUtil::sortArray($print_member,'access',$_SESSION['crs_print_order']);
			
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

		$this->checkPermission('write');
		
		$is_admin = true;
		$tpl = new ilTemplate('tpl.crs_members_print.html',true,true,'Modules/Course');

		$this->object->initCourseMemberObject();
		
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		if($privacy->enabledCourseAccessTimes())
		{
			include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
			$progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
		}

		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		include_once('./Services/Tracking/classes/class.ilLPObjSettings.php');
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and 
			ilObjUserTracking::_enabledUserRelatedData() and
			ilLPObjSettings::_lookupMode($this->object->getId()) != LP_MODE_DEACTIVATED);
		

		// MEMBERS
		if(count($members = $this->object->members_obj->getParticipants()))
		{
			$members = $this->fetchPrintMemberData($members);
			
			foreach($members as $member_data)
			{
				if($this->show_tracking)
				{
					$tpl->setCurrentBlock('progress_row');
					$tpl->setVariable('VAL_PROGRESS',$member_data['progress']);
					$tpl->parseCurrentBlock();
				}
				
				if($privacy->enabledCourseAccessTimes())
				{
					$tpl->setCurrentBlock('access_row');
					$tpl->setVariable('VAL_ACCESS',$member_data['access']);
					$tpl->parseCurrentBlock();
				}
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
			
			if($this->show_tracking)
			{
				$tpl->setCurrentBlock('progress');
				$tpl->setVariable('TXT_PROGRESS',$this->lng->txt('learning_progress'));
				$tpl->parseCurrentBlock();
			}
			
			if($privacy->enabledCourseAccessTimes())
			{
				$tpl->setCurrentBlock('access');
				$tpl->setVariable('TXT_ACCESS',$this->lng->txt('last_access'));
				$tpl->parseCurrentBlock();
			}
			
			
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

	/*
	 * @author Arturo Gonzalez <arturogf@gmail.com>
	 * @access       public
	 */
	function membersGalleryObject()
	{

		global $rbacsystem, $ilErr, $ilAccess, $ilUser;

		$is_admin = (bool) $ilAccess->checkAccess("write", "", $this->object->getRefId());

		if (!$is_admin &&
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_DISABLED)
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}


		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_members_gallery.html','Modules/Course');
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_members_gallery');
		
		$this->object->initCourseMemberObject();

		// MEMBERS 
		if(count($members = $this->object->members_obj->getParticipants()))
		{
			$ordered_members = array();

			foreach($members as $member_id)
			{
				if(!($usr_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
				{
					continue;
				}
				
				// please do not use strtoupper on first/last name for output
				// this messes up with some unicode characters, i guess
				// depending on php verion, alex
				array_push($ordered_members,array("id" => $member_id, 
								  "login" => $usr_obj->getLogin(),
								  "lastname" => $usr_obj->getLastName(),
								  "firstname" => $usr_obj->getFirstName(),
								  "sortlastname" => strtoupper($usr_obj->getLastName()).strtoupper($usr_obj->getFirstName()),
								  "usr_obj" => $usr_obj));
			}

			$ordered_members=ilUtil::sortArray($ordered_members,"sortlastname","asc");

			foreach($ordered_members as $member)
			{
			  $usr_obj = $member["usr_obj"];

				$public_profile = $usr_obj->getPref("public_profile");
				
				// SET LINK TARGET FOR USER PROFILE
				$this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user", $member["id"]);
				$profile_target = $this->ctrl->getLinkTargetByClass("ilpublicuserprofilegui","getHTML");
			  
				// GET USER IMAGE
				$file = $usr_obj->getPersonalPicturePath("xsmall");
				
				if($this->object->members_obj->isAdmin($member["id"]) or $this->object->members_obj->isTutor($member["id"]))
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
					$this->tpl->setVariable("FIRSTNAME", $member["firstname"]);
					$this->tpl->setVariable("LASTNAME", $member["lastname"]);
				}
				$this->tpl->setVariable("LOGIN", $member["login"]);
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

		// SET FOOTER BUTTONS
		$tpl->setCurrentBlock("tbl_action_row");

		// BUTTONS FOR ADD USER  
		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","autoFill");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("crs_auto_fill"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",4);
		
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();

		$tbl->enable('select_all');
		$tbl->setFormName("subscriber_form");
		$tbl->setSelectAllCheckbox("subscriber");

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_subscribers"),"icon_usr.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("name"),
								   $this->lng->txt("login"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("",
								  "name",
								  "login",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "update_subscribers" => 1,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array('1%'));

		$this->__setTableGUIBasicData($tbl,$a_result_set,"subscribers");
		$tbl->render();

		$this->tpl->setCurrentBlock('sub_wait_table');
		$this->tpl->setVariable('SUB_WAIT_NAME','subscriber_form');
		$this->tpl->setVariable('SUB_WAIT_FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SUB_WAIT_TABLE_CONTENT",$tbl->tpl->get());
		$this->tpl->parseCurrentBlock();

		return true;
	}
	function __showWaitingListTable($a_result_set,$a_waiting_list_ids = NULL)
	{
		$actions = array("addFromWaitingList"		=> $this->lng->txt("crs_add_subscribers"),
						 "removeFromWaitingList"	=> $this->lng->txt("crs_delete_from_waiting_list"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tbl->enable('select_all');
		$tbl->setFormName("wait_form");
		$tbl->setSelectAllCheckbox("waiting_list");


		// SET FOOTER BUTTONS
		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setVariable("COLUMN_COUNTS",5);
		
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_waiting_list"),"icon_usr.gif",$this->lng->txt("crs_waiting_list"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("name"),
								   $this->lng->txt("login"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("",
								  "name",
								  "login",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "update_subscribers" => 1,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array('1%'));

		$this->__setTableGUIBasicData($tbl,$a_result_set,"subscribers");
		$tbl->render();

		$this->tpl->setCurrentBlock('sub_wait_table');
		$this->tpl->setVariable('SUB_WAIT_NAME','wait_form');
		$this->tpl->setVariable('SUB_WAIT_FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SUB_WAIT_TABLE_CONTENT",$tbl->tpl->get());
		$this->tpl->parseCurrentBlock();

		return true;
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
		global $rbacreview, $ilErr, $ilAccess, $ilObjDataCache;			
		include_once('./Services/AccessControl/classes/class.ilObjRole.php');


		$is_admin = (bool) $ilAccess->checkAccess("write", "", $this->object->getRefId());

		if (!$is_admin &&
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_DISABLED)
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('mail_members');
		
		//$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.mail_members.html','Modules/Course');
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.mail_members.html','Services/Contact');


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
		
		// Sort by relevance
		$sorted_role_ids = array();
		$counter = 3;
		foreach($role_ids as $role_id)
		{
			switch(substr(ilObject::_lookupTitle($role_id),0,8))
			{
				case 'il_crs_a':
					$sorted_role_ids[2] = $role_id;
					break;
					
				case 'il_crs_t':
					$sorted_role_ids[1] = $role_id;
					break;

				case 'il_crs_m':
					$sorted_role_ids[0] = $role_id;
					break;
					
				default:
					$sorted_role_ids[$counter++] = $role_id;
					break;
			}
		}
		ksort($sorted_role_ids,SORT_NUMERIC);
		foreach ((array) $sorted_role_ids as $role_id)
		{
			$this->tpl->setCurrentBlock("mailbox_row");
			$role_addr = $rbacreview->getRoleMailboxAddress($role_id);
			$this->tpl->setVariable("CHECK_MAILBOX",ilUtil::formCheckbox(1,'roles[]',
					htmlspecialchars($role_addr)
			));
			if (ilMail::_usePearMail())
			{
				// if pear mail is enabled, mailbox addresses are already localized in the language of the user
				$this->tpl->setVariable("MAILBOX",$role_addr);
			}
			else
			{
				// if pear mail is not enabled, we need to localize mailbox addresses in the language of the user
				$this->tpl->setVariable("MAILBOX",ilObjRole::_getTranslation($ilObjDataCache->lookupTitle($role_id)). " (" . $role_addr . ")");
			}

			$this->tpl->parseCurrentBlock();
		}
	}
	
	function &executeCommand()
	{
		global $rbacsystem,$ilUser,$ilAccess,$ilErr,$ilTabs,$ilNavigationHistory;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
	
		$this->prepareOutput();
		
		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$ilNavigationHistory->addItem($_GET['ref_id'],
				'repository.php?cmd=frameset&ref_id='.$_GET['ref_id'], 'crs');
		}
		
		if(!$this->getCreationMode())
		{
			include_once 'payment/classes/class.ilPaymentObject.php';
			if(ilPaymentObject::_isBuyable($this->object->getRefId()) &&
			   !ilPaymentObject::_hasAccess($this->object->getRefId()))
			{
				$ilTabs->setTabActive('info_short');
				
				include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';	
				$this->ctrl->setReturn($this, '');
				$pp_gui = new ilShopPurchaseGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($pp_gui);	
				return true;
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

			case 'ilcourseregistrationgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setTabActive('join');
				include_once('./Modules/Course/classes/class.ilCourseRegistrationGUI.php');
				$registration = new ilCourseRegistrationGUI($this->object);
				$this->ctrl->forwardCommand($registration);
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

			case "ilcolumngui":
				$this->tabs_gui->setTabActive('none');
				$this->checkPermission("read");
				//$this->prepareOutput();
				//$this->getSubItems();
				//include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				//$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
				//	ilObjStyleSheet::getContentStylePath(0));
				//$this->renderObject();
				$this->viewObject();
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
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$this->tabs_gui->setTabActive('perm_settings');
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,
					'assignMembersObject',
					array(
						ilCourseContants::CRS_MEMBER => $this->lng->txt('crs_member'),
						ilCourseContants::CRS_TUTOR	=> $this->lng->txt('crs_tutor'),
						ilCourseContants::CRS_ADMIN => $this->lng->txt('crs_admin')
						)
					);
						

				// Set tabs
				$this->ctrl->setReturn($this,'members');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('crs_member_administration');
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

			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$html = $this->ctrl->forwardCommand($profile_gui);
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
				
			case 'ilsessionoverviewgui':
				include_once('./Modules/Session/classes/class.ilSessionOverviewGUI.php');
				
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('events');
				$overview = new ilSessionOverviewGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($overview);				
				break;
				
			case 'ilcourseitemadministrationgui':
				include_once 'Modules/Course/classes/class.ilCourseItemAdministrationGUI.php';
				$this->tabs_gui->clearSubTabs();
				$this->ctrl->setReturn($this,'view');
				$item_adm_gui = new ilCourseItemAdministrationGUI($this->object,(int) $_REQUEST['item_id']);
				$this->ctrl->forwardCommand($item_adm_gui);
				break;
				
			// container page editing
			case "ilpageobjectgui":
				//$this->prepareOutput(false);
				$this->checkPermission("write");
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;
				
			default:
				if(!$this->creation_mode and !$ilAccess->checkAccess('visible','',$this->object->getRefId(),'crs'))
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}
				if( !$this->creation_mode
					&& $cmd != 'infoScreen'
					&& $cmd != 'sendfile'
					&& $cmd != 'unsubscribe'
					&& $cmd != 'performUnsubscribe'
					&& !$ilAccess->checkAccess("read",'',$this->object->getRefId())
					|| $cmd == 'join'
					|| $cmd == 'subscribe')
				{
					include_once './Modules/Course/classes/class.ilCourseParticipants.php';
					if($ilAccess->checkAccess('join','',$this->object->getRefId()) &&
						!ilCourseParticipants::_isParticipant($this->object->getRefId(),$ilUser->getId()))
					{
						include_once('./Modules/Course/classes/class.ilCourseRegistrationGUI.php');
						$this->ctrl->redirectByClass("ilCourseRegistrationGUI");
					}
					else
					{
						$this->infoScreenObject();
						break;
					}
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
				
				// Dirty hack for course timings view
				if($this->forwardToTimingsView())
				{
					break;
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
		if(($privacy->confirmationRequired() or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())) 
			and !ilCourseAgreement::_hasAccepted($ilUser->getId(),$this->object->getId()))
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
		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_objectives_ask_reset();

		return true;;
	}
	function cciResetObject()
	{
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

	/**
	* Modify Item ListGUI for presentation in container
	*/
	function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
	{
		return ilObjCourseGUI::_modifyItemGUI($a_item_list_gui, 'ilcoursecontentgui', $a_item_data, $a_show_path,
			$this->object->getAboStatus(), $this->object->getRefId(), $this->object->getId());
	}
	
	/**
	* We need a static version of this, e.g. in folders of the course
	*/
	static function _modifyItemGUI($a_item_list_gui, $a_cmd_class, $a_item_data, $a_show_path,
		$a_abo_status, $a_course_ref_id, $a_course_obj_id, $a_parent_ref_id = 0)
	{
		global $lng, $ilCtrl, $ilAccess;
		
		// this is set for folders within the course
		if ($a_parent_ref_id == 0)
		{
			$a_parent_ref_id = $a_course_ref_id;
		}

		// Special handling for tests in courses with learning objectives
		if($a_item_data['type'] == 'tst' and
			ilObjCourse::_lookupViewMode($a_course_obj_id) == ilContainer::VIEW_OBJECTIVE)
		{
			$a_item_list_gui->addCommandLinkParameter(array('crs_show_result' => $a_course_ref_id));				
		}
		
		// ACTIVATION
		switch($a_item_data['timing_type'])
		{
			case IL_CRS_TIMINGS_ACTIVATION:
				$activation = ilDatePresentation::formatPeriod(
					new ilDateTime($a_item_data['start'],IL_CAL_UNIX),
					new ilDateTime($a_item_data['end'],IL_CAL_UNIX));
					break;
					
			case IL_CRS_TIMINGS_PRESETTING:
				$activation = ilDatePresentation::formatPeriod(
					new ilDate($a_item_data['start'],IL_CAL_UNIX),
					new ilDate($a_item_data['end'],IL_CAL_UNIX));
					break;
					
			default:
				$activation = '';
				break;
		}

		$a_item_list_gui->enableSubscribe($a_abo_status);
		
		// add activation custom property
		if ($activation != "")
		{
			$a_item_list_gui->addCustomProperty($lng->txt($a_item_data['activation_info']), $activation,
				false, true);
		}

		$is_tutor = ($ilAccess->checkAccess('write','',
			$a_course_ref_id,'crs', $a_course_obj_id));
		
		if($a_show_path and $is_tutor)
		{
			$a_item_list_gui->addCustomProperty($lng->txt('path'),
				
				ilContainer::_buildPath($a_item_data['ref_id'], $a_course_ref_id),
				false,
				true);
		}

		if($is_tutor)
		{
			$ilCtrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
				$a_parent_ref_id);
			$ilCtrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
				$a_item_data['child']);
			$a_item_list_gui->addCustomCommand($ilCtrl->getLinkTargetByClass(
				array(strtolower($a_cmd_class), 'ilCourseItemAdministrationGUI'),
				'edit'),
				'activation');
		}
	}
	
	/**
	* Set content sub tabs
	*/
	function setContentSubTabs()
	{
		global $ilAccess, $lng, $ilCtrl;

		if ($this->object->getType() != 'crs')
		{
			return true;
		}
		if (!$ilAccess->checkAccess('write','',
			$this->object->getRefId(),'crs',$this->object->getId()))
		{
			$is_tutor = false;
			// No further tabs if objective view or archives
			if($this->object->enabledObjectiveView())
			{
				return false;
			}
		}
		else
		{
			$is_tutor = true;
		}

		// These subtabs should also work, if the command is called directly in
		// ilObjCourseGUI, so please use ...ByClass methods.
		// (see ilObjCourseGUI->executeCommand: case "ilcolumngui")
		
		if(!$_SESSION['crs_timings_panel'][$this->object->getId()] or 1)
		{
			if (!$this->isActiveAdministrationPanel())
			{
				$this->tabs_gui->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTargetByClass("ilobjcoursegui", "view"));
			}
			else
			{
				$this->tabs_gui->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTargetByClass("ilobjcoursegui", "disableAdministrationPanel"));
			}
		}
		include_once 'Modules/Course/classes/class.ilCourseItems.php';
		if($this->object->getViewMode() == IL_CRS_VIEW_TIMING)
		{
			$this->tabs_gui->addSubTabTarget('timings_timings',
				$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','editUserTimings'));
		}
		
		$this->addStandardContainerSubTabs(false);

		if($is_tutor)
		{
			$this->tabs_gui->addSubTabTarget('crs_archives',
				$this->ctrl->getLinkTargetByClass(
					array('ilcoursecontentgui', 'ilcoursearchivesgui'),'view'));
		}

		return true;
	}
	
	/**
	 * load date
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function loadDate($a_field)
	{
		global $ilUser;

		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		
		$dt['year'] = (int) $_POST[$a_field]['date']['y'];
		$dt['mon'] = (int) $_POST[$a_field]['date']['m'];
		$dt['mday'] = (int) $_POST[$a_field]['date']['d'];
		$dt['hours'] = (int) $_POST[$a_field]['time']['h'];
		$dt['minutes'] = (int) $_POST[$a_field]['time']['m'];
		$dt['seconds'] = (int) $_POST[$a_field]['time']['s'];
		
		$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
		return $date;		
	}
	
	/**
	 * ask reset test results
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function askResetObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_ask_reset.html",'Modules/Course');
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_objectives_reset_sure'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_RESET",$this->lng->txt('reset'));
		
		return true;
	}
	
	function resetObject()
	{
		global $ilUser;

		include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';
		
		$tmp_obj_res = new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->reset($this->object->getId());
		
		$ilUser->deletePref('crs_objectives_force_details_'.$this->object->getId());
		
		ilUtil::sendInfo($this->lng->txt('crs_objectives_reseted'));
		$this->viewObject();
	}
	
	function __checkStartObjects()
	{
		include_once './Modules/Course/classes/class.ilCourseStart.php';

		global $ilAccess,$ilUser;

		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			return true;
		}
		$this->start_obj = new ilCourseStart($this->object->getRefId(),$this->object->getId());
		if(count($this->start_obj->getStartObjects()) and !$this->start_obj->allFullfilled($ilUser->getId()))
		{
			return false;
		}
		return true;
	}

	function showStartObjects()
	{
		include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
		include_once './Services/Repository/classes/class.ilRepositoryExplorer.php';
		include_once './classes/class.ilLink.php';

		global $rbacsystem,$ilias,$ilUser,$ilAccess,$ilObjDataCache;

		$this->tabs_gui->setSubTabActive('view');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_start_view.html",'Modules/Course');
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_info_start'));
		$this->tpl->setVariable("TBL_TITLE_START",$this->lng->txt('crs_table_start_objects'));
		$this->tpl->setVariable("HEADER_NR",$this->lng->txt('crs_nr'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("HEADER_EDITED",$this->lng->txt('crs_objective_accomplished'));


		$lm_continue =& new ilCourseLMHistory($this->object->getRefId(),$ilUser->getId());
		$continue_data = $lm_continue->getLMHistory();

		$counter = 0;
		foreach($this->start_obj->getStartObjects() as $start)
		{
			$obj_id = $ilObjDataCache->lookupObjId($start['item_ref_id']);
			$ref_id = $start['item_ref_id'];
			$type = $ilObjDataCache->lookupType($obj_id);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($ref_id,$obj_id);

			$obj_link = ilLink::_getLink($ref_id,$type);
			$obj_frame = ilRepositoryExplorer::buildFrameTarget($type,$ref_id,$obj_id);
			$obj_frame = $obj_frame ? $obj_frame : '';

			// Tmp fix for tests
			$obj_frame = $type == 'tst' ? '' : $obj_frame;

			$contentObj = false;

			if($ilAccess->checkAccess('read','',$ref_id))
			{
				$this->tpl->setCurrentBlock("start_read");
				$this->tpl->setVariable("READ_TITLE_START",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->setVariable("READ_TARGET_START",$obj_frame);
				$this->tpl->setVariable("READ_LINK_START", $obj_link.'&crs_show_result='.$this->object->getRefId());
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("start_visible");
				$this->tpl->setVariable("VISIBLE_LINK_START",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->parseCurrentBlock();
			}

			// CONTINUE LINK
			if(isset($continue_data[$ref_id]))
			{
				$this->tpl->setCurrentBlock("link");
				$this->tpl->setVariable("LINK_HREF",ilLink::_getLink($ref_id,'',array('obj_id',
																					  $continue_data[$ref_id]['lm_page_id'])));
				#$this->tpl->setVariable("CONTINUE_LINK_TARGET",$target);
				$this->tpl->setVariable("LINK_NAME",$this->lng->txt('continue_work'));
				$this->tpl->parseCurrentBlock();
			}

			// add to desktop link
			if(!$ilUser->isDesktopItem($ref_id,$type) and
			   $this->object->getAboStatus())
			{
				if ($ilAccess->checkAccess('read','',$ref_id))
				{
					$this->tpl->setCurrentBlock("link");
					$this->ctrl->setParameterByClass(get_class($this),'item_ref_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'item_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'type',$type);

					$this->tpl->setVariable("LINK_HREF",$this->ctrl->getLinkTarget($this,'addToDesk'));
					$this->tpl->setVariable("LINK_NAME", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}
			elseif($this->object->getAboStatus())
			{
					$this->tpl->setCurrentBlock("link");
					$this->ctrl->setParameterByClass(get_class($this),'item_ref_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'item_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'type',$type);

					$this->tpl->setVariable("LINK_HREF",$this->ctrl->getLinkTarget($this,'removeFromDesk'));
					$this->tpl->setVariable("LINK_NAME", $this->lng->txt("unsubscribe"));
					$this->tpl->parseCurrentBlock();
			}


			// Description
			if(strlen($ilObjDataCache->lookupDescription($obj_id)))
			{
				$this->tpl->setCurrentBlock("start_description");
				$this->tpl->setVariable("DESCRIPTION_START",$ilObjDataCache->lookupDescription($obj_id));
				$this->tpl->parseCurrentBlock();
			}


			if($this->start_obj->isFullfilled($ilUser->getId(),$ref_id))
			{
				$accomplished = 'accomplished';
			}
			else
			{
				$accomplished = 'not_accomplished';
			}
			$this->tpl->setCurrentBlock("start_row");
			$this->tpl->setVariable("EDITED_IMG",ilUtil::getImagePath('crs_'.$accomplished.'.gif'));
			$this->tpl->setVariable("EDITED_ALT",$this->lng->txt('crs_objective_'.$accomplished));
			$this->tpl->setVariable("ROW_CLASS",'option_value');
			$this->tpl->setVariable("ROW_CLASS_CENTER",'option_value_center');
			$this->tpl->setVariable("OBJ_NR_START",++$counter.'.');
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}
	
	
} // END class.ilObjCourseGUI
?>
