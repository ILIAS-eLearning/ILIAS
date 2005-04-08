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
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
*
* @ilCtrl_Calls ilObjCourseGUI: ilCourseRegisterGUI, ilPaymentPurchaseGUI, ilCourseObjectivesGUI, ilConditionHandlerInterface
* @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "./classes/class.ilObjectGUI.php";
require_once "./course/classes/class.ilCourseRegisterGUI.php";


class ilObjCourseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjCourseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $ilCtrl;

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->type = "crs";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

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

			default:
				$this->viewObject();
				break;
		}
		return true;
	}


	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function cancelMemberObject()
	{
		$this->__unsetSessionVariables();

		$return_location = "members";

		sendInfo($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location));
	}

	function viewObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		if($this->ctrl->getTargetScript() == "adm_object.php")
		{
			parent::viewObject();
			return true;
		}
		else
		{
			if($rbacsystem->checkAccess("write", $this->ref_id) or
			   ($this->object->isActivated() and !$this->object->isArchived()))
			{
				$this->initCourseContentInterface();
				$this->cci_obj->cci_view();
			}
			else
			{
				$this->archiveObject();
			}
		}
	}

	function detailsObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("read", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_details.html","course");


		$this->tpl->setVariable("TITLE",$this->lng->txt("crs_details"));
		$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("ALT_IMG",$this->lng->txt("crs_details"));
		
		// SET TXT VARIABLES
		$this->tpl->setVariable("TXT_SYLLABUS",$this->lng->txt("crs_syllabus"));
		$this->tpl->setVariable("TXT_CONTACT",$this->lng->txt("crs_contact"));
		$this->tpl->setVariable("TXT_CONTACT_NAME",$this->lng->txt("crs_contact_name"));
		$this->tpl->setVariable("TXT_CONTACT_RESPONSIBILITY",$this->lng->txt("crs_contact_responsibility"));
		$this->tpl->setVariable("TXT_CONTACT_EMAIL",$this->lng->txt("crs_contact_email"));
		$this->tpl->setVariable("TXT_CONTACT_PHONE",$this->lng->txt("crs_contact_phone"));
		$this->tpl->setVariable("TXT_CONTACT_CONSULTATION",$this->lng->txt("crs_contact_consultation"));
		$this->tpl->setVariable("TXT_DATES",$this->lng->txt("crs_dates"));
		$this->tpl->setVariable("TXT_ACTIVATION",$this->lng->txt("crs_activation"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION",$this->lng->txt("crs_subscription"));
		$this->tpl->setVariable("TXT_ARCHIVE",$this->lng->txt("crs_archive"));

		// FILL 
		$this->tpl->setVariable("SYLLABUS",nl2br($this->object->getSyllabus() ? 
												 $this->object->getSyllabus() : 
												 $this->lng->txt("crs_not_available")));

		$this->tpl->setVariable("CONTACT_NAME",$this->object->getContactName() ? 
								$this->object->getContactName() : 
								$this->lng->txt("crs_not_available"));
		$this->tpl->setVariable("CONTACT_RESPONSIBILITY",$this->object->getContactResponsibility() ? 
								$this->object->getContactResponsibility() : 
								$this->lng->txt("crs_not_available"));
		$this->tpl->setVariable("CONTACT_PHONE",$this->object->getContactPhone() ? 
								$this->object->getContactPhone() : 
								$this->lng->txt("crs_not_available"));
		$this->tpl->setVariable("CONTACT_CONSULTATION",nl2br($this->object->getContactConsultation() ? 
								$this->object->getContactConsultation() : 
								$this->lng->txt("crs_not_available")));
		if($this->object->getContactEmail())
		{
			$this->tpl->setCurrentBlock("email_link");
			$this->tpl->setVariable("EMAIL_LINK","mail_new.php?type=new&rcp_to=".$this->object->getContactEmail());
			$this->tpl->setVariable("CONTACT_EMAIL",$this->object->getContactEmail());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock("no_mail");
			$this->tpl->setVariable("NO_CONTACT_EMAIL",$this->object->getContactEmail());
			$this->tpl->parseCurrentBlock();
		}
		if($this->object->getActivationUnlimitedStatus())
		{
			$this->tpl->setVariable("ACTIVATION",$this->lng->txt('crs_unlimited'));
		}
		else
		{
			$str = $this->lng->txt("crs_from")." ".strftime("%Y-%m-%d %R",$this->object->getActivationStart())." ".
				$this->lng->txt("crs_to")." ".strftime("%Y-%m-%d %R",$this->object->getActivationEnd());
			$this->tpl->setVariable("ACTIVATION",$str);
		}
		if($this->object->getSubscriptionUnlimitedStatus())
		{
			$this->tpl->setVariable("SUBSCRIPTION",$this->lng->txt('crs_unlimited'));
		}
		else
		{
			$str = $this->lng->txt("crs_from")." ".strftime("%Y-%m-%d %R",$this->object->getSubscriptionStart())." ".
				$this->lng->txt("crs_to")." ".strftime("%Y-%m-%d %R",$this->object->getSubscriptionEnd());
			$this->tpl->setVariable("SUBSCRIPTION",$str);
		}
		if($this->object->getArchiveType() == $this->object->ARCHIVE_DISABLED)
		{
			$this->tpl->setVariable("ARCHIVE",$this->lng->txt('crs_archive_disabled'));
		}
		else
		{
			$str = $this->lng->txt("crs_from")." ".strftime("%Y-%m-%d %R",$this->object->getArchiveStart())." ".
				$this->lng->txt("crs_to")." ".strftime("%Y-%m-%d %R",$this->object->getArchiveEnd());
			$this->tpl->setVariable("ARCHIVE",$str);
		}
			
	}

	function listStructureObject()
	{
		include_once './course/classes/class.ilCourseStart.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_list_starter.html","course");
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		if(!count($starter = $crs_start->getStartObjects()))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'selectStarter'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('crs_add_starter'));
			$this->tpl->parseCurrentBlock();

			sendInfo($this->lng->txt('crs_no_starter_created'));

			return true;
		}

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_start_objects'));
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
			$this->tpl->setVariable("DELETE_IMG",ilUtil::getImagePath('delete.gif'));
			$this->tpl->setVariable("DELETE_ALT",$this->lng->txt('delete'));
 			$this->tpl->parseCurrentBlock();
		}
	}

	function deleteStarterObject()
	{
		include_once './course/classes/class.ilCourseStart.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());
		$crs_start->delete((int) $_GET['del_starter']);
	
		sendInfo($this->lng->txt('crs_starter_deleted'));
		$this->listStructureObject();
		
		return true;
	}
		

	function selectStarterObject()
	{
		include_once './course/classes/class.ilCourseStart.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_add_starter.html","course");

		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");

		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'listStructure'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('back'));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_select_starter'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_ADD",$this->lng->txt('crs_add_starter'));

		
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
		include_once './course/classes/class.ilCourseStart.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['starter']))
		{
			sendInfo($this->lng->txt('crs_select_one_object'));
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
			sendInfo($this->lng->txt('crs_added_starters'));
			$this->listStructureObject();

			return true;
		}
		else
		{
			sendInfo($this->lng->txt('crs_starters_already_assigned'));
			$this->selectStarterObject();

			return false;
		}
	}
	function editObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		$this->ctrl->setReturn($this,'editObject');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_edit.html","course");
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		
		if($this->ctrl->getTargetScript() != 'adm_object.php')
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('preconditions'));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("btn_cell");
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,'listStructure'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('crs_crs_structure'));
			$this->tpl->parseCurrentBlock();
		}


		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));

		// LOAD SAVED DATA IN CASE OF ERROR
		$syllabus = $_SESSION["error_post_vars"]["crs"]["syllabus"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["syllabus"],true) :
			ilUtil::prepareFormOutput($this->object->getSyllabus());

		$contact_name= $_SESSION["error_post_vars"]["crs"]["contact_name"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["contact_name"],true) :
			ilUtil::prepareFormOutput($this->object->getContactName());

		$contact_responsibility = $_SESSION["error_post_vars"]["crs"]["contact_responsibility"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["contact_responsibility"],true) :
			ilUtil::prepareFormOutput($this->object->getContactResponsibility());

		$contact_email = $_SESSION["error_post_vars"]["crs"]["contact_email"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["contact_email"],true) :
			ilUtil::prepareFormOutput($this->object->getContactEmail());

		$contact_phone = $_SESSION["error_post_vars"]["crs"]["contact_phone"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["contact_phone"],true) :
			ilUtil::prepareFormOutput($this->object->getContactPhone());

		$contact_email = $_SESSION["error_post_vars"]["crs"]["contact_email"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["contact_email"],true) :
			ilUtil::prepareFormOutput($this->object->getContactEmail());

		$contact_consultation = $_SESSION["error_post_vars"]["crs"]["contact_consultation"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["contact_consultation"],true) :
			ilUtil::prepareFormOutput($this->object->getContactConsultation());

		$activation_unlimited = $_SESSION["error_post_vars"]["crs"]["activation_unlimited"] ? 
			1 : 
			(int) $this->object->getActivationUnlimitedStatus();

		$activation_start = $_SESSION["error_post_vars"]["crs"]["activation_start"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["crs"]["activation_start"]) :
			$this->object->getActivationStart();

		$activation_end = $_SESSION["error_post_vars"]["crs"]["activation_end"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["crs"]["activation_end"]) :
			$this->object->getActivationEnd();

		$offline = $_SESSION["error_post_vars"]["crs"]["activation_offline"] ? 1 : (int) $this->object->getOfflineStatus();
  
		$subscription_unlimited = $_SESSION["error_post_vars"]["crs"]["subscription_unlimited"] ? 
			1 : 
			(int) $this->object->getSubscriptionUnlimitedStatus();

		$subscription_start = $_SESSION["error_post_vars"]["crs"]["subscription_start"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["crs"]["subscription_start"]) :
			$this->object->getSubscriptionStart();

		$subscription_end = $_SESSION["error_post_vars"]["crs"]["subscription_end"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["crs"]["subscription_end"]) :
			$this->object->getSubscriptionEnd();

		$subscription_type = $_SESSION["error_post_vars"]["crs"]["subscription_type"] ? 
			$_SESSION["error_post_vars"]["crs"]["subscription_type"] : 
			$this->object->getSubscriptionType();

		$subscription_password = $_SESSION["error_post_vars"]["crs"]["subscription_password"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["subscription_password"],true) :
			ilUtil::prepareFormOutput($this->object->getSubscriptionPassword());

		$subscription_max_members = $_SESSION["error_post_vars"]["crs"]["subscription_max_members"] ? 
			ilUtil::prepareFormOutput($_SESSION["error_post_vars"]["crs"]["subscription_max_members"],true) :
			ilUtil::prepareFormOutput($this->object->getSubscriptionMaxMembers());

		$subscription_notify = $_SESSION["error_post_vars"]["crs"]["subscription_notify"] ? 1 
			: (int) $this->object->getSubscriptionNotify();

		$sortorder_type = $_SESSION["error_post_vars"]["crs"]["sortorder_type"] ? 
			$_SESSION["error_post_vars"]["crs"]["sortorder_type"] : 
			$this->object->getOrderType();

		$archive_start = $_SESSION["error_post_vars"]["crs"]["archive_start"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["crs"]["archive_start"]) :
			$this->object->getArchiveStart();

		$archive_end = $_SESSION["error_post_vars"]["crs"]["archive_end"] ? 
			$this->__toUnix($_SESSION["error_post_vars"]["crs"]["archive_end"]) :
			$this->object->getArchiveEnd();

		$archive_type = $_SESSION["error_post_vars"]["crs"]["archive_type"] ? 
			$_SESSION["error_post_vars"]["crs"]["archive_type"] : 
			$this->object->getArchiveType();

		$abo_status = $_SESSION["error_post_vars"]["crs"]["abo_status"] ? 
			$_SESSION["error_post_vars"]["crs"]["abo_status"] : 
			$this->object->getAboStatus();

		$objective_view_status = $_SESSION["error_post_vars"]["crs"]["objective_view"] ? 
			$_SESSION["error_post_vars"]["crs"]["objective_view"] : 
			$this->object->enabledObjectiveView();

		// SET VALUES
		$this->tpl->setVariable("SYLLABUS",$syllabus);
		$this->tpl->setVariable("CONTACT_NAME",$contact_name);
		$this->tpl->setVariable("CONTACT_RESPONSIBILITY",$contact_responsibility);
		$this->tpl->setVariable("CONTACT_EMAIL",$contact_email);
		$this->tpl->setVariable("CONTACT_PHONE",$contact_phone);
		$this->tpl->setVariable("CONTACT_CONSULTATION",$contact_consultation);
		$this->tpl->setVariable("SUBSCRIPTION_PASSWORD",$subscription_password);
		$this->tpl->setVariable("SUBSCRIPTION_MAX_MEMBERS",$subscription_max_members);
		
		// SET TXT VARIABLES
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("crs_settings"));
		$this->tpl->setVariable("TXT_SYLLABUS",$this->lng->txt("crs_syllabus"));
		$this->tpl->setVariable("TXT_CONTACT",$this->lng->txt("crs_contact"));
		$this->tpl->setVariable("TXT_CONTACT_NAME",$this->lng->txt("crs_contact_name"));
		$this->tpl->setVariable("TXT_CONTACT_RESPONSIBILITY",$this->lng->txt("crs_contact_responsibility"));
		$this->tpl->setVariable("TXT_CONTACT_EMAIL",$this->lng->txt("crs_contact_email"));
		$this->tpl->setVariable("TXT_CONTACT_PHONE",$this->lng->txt("crs_contact_phone"));
		$this->tpl->setVariable("TXT_CONTACT_CONSULTATION",$this->lng->txt("crs_contact_consultation"));

		$this->tpl->setVariable("TXT_ACTIVATION",$this->lng->txt("crs_activation"));
		$this->tpl->setVariable("TXT_ACTIVATION_UNLIMITED",$this->lng->txt("crs_unlimited"));
		$this->tpl->setVariable("TXT_ACTIVATION_START",$this->lng->txt("crs_start"));
		$this->tpl->setVariable("TXT_ACTIVATION_END",$this->lng->txt("crs_end"));
		$this->tpl->setVariable("TXT_ACTIVATION_OFFLINE",$this->lng->txt("set_online"));

		$this->tpl->setVariable("TXT_SUBSCRIPTION",$this->lng->txt("crs_subscription"));
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

		$this->tpl->setVariable("TXT_SORTORDER",$this->lng->txt("crs_sortorder_abo"));
		$this->tpl->setVariable("TXT_MANUAL",$this->lng->txt("crs_sort_manual"));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("crs_sort_title"));
		$this->tpl->setVariable("TXT_SORT_ACTIVATION",$this->lng->txt("crs_sort_activation"));
		$this->tpl->setVariable("TXT_ABO",$this->lng->txt('crs_allow_abo'));
		$this->tpl->setVariable("TXT_OBJ_VIEW",$this->lng->txt('crs_objective_view'));

		$this->tpl->setVariable("TXT_ARCHIVE",$this->lng->txt("crs_archive"));
		$this->tpl->setVariable("TXT_ARCHIVE_START",$this->lng->txt("crs_start"));
		$this->tpl->setVariable("TXT_ARCHIVE_TYPE",$this->lng->txt("crs_archive_select_type"));
		$this->tpl->setVariable("TXT_ARCHIVE_END",$this->lng->txt("crs_end"));
		$this->tpl->setVariable("TXT_DISABLED",$this->lng->txt("crs_archive_type_disabled"));
		$this->tpl->setVariable("TXT_READ",$this->lng->txt("crs_archive_read"));
		$this->tpl->setVariable("TXT_DOWNLOAD",$this->lng->txt("crs_archive_download"));

		$this->tpl->setVariable("TXT_REQUIRED_FLD",$this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("submit"));

		$this->tpl->setVariable("ACTIVATION_UNLIMITED",ilUtil::formCheckbox($activation_unlimited,"crs[activation_unlimited]",1));


		$this->tpl->setVariable("SELECT_ACTIVATION_START_MINUTE",$this->__getDateSelect("minute","crs[activation_start][minute]",
																					 date("i",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_HOUR",$this->__getDateSelect("hour","crs[activation_start][hour]",
																					 date("G",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_DAY",$this->__getDateSelect("day","crs[activation_start][day]",
																					 date("d",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_MONTH",$this->__getDateSelect("month","crs[activation_start][month]",
																					   date("m",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_START_YEAR",$this->__getDateSelect("year","crs[activation_start][year]",
																					  date("Y",$activation_start)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_MINUTE",$this->__getDateSelect("minute","crs[activation_end][minute]",
																					 date("i",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_HOUR",$this->__getDateSelect("hour","crs[activation_end][hour]",
																					 date("G",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_DAY",$this->__getDateSelect("day","crs[activation_end][day]",
																				   date("d",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_MONTH",$this->__getDateSelect("month","crs[activation_end][month]",
																					 date("m",$activation_end)));
		$this->tpl->setVariable("SELECT_ACTIVATION_END_YEAR",$this->__getDateSelect("year","crs[activation_end][year]",
																					date("Y",$activation_end)));

		$this->tpl->setVariable("CHECK_ACTIVATION_OFFLINE",ilUtil::formCheckbox(!$offline,"crs[activation_offline]",1));

		$this->tpl->setVariable("SUBSCRIPTION_UNLIMITED",ilUtil::formCheckbox($subscription_unlimited,"crs[subscription_unlimited]",1));

		$this->tpl->setVariable("SELECT_SUBSCRIPTION_START_MINUTE",$this->__getDateSelect("minute","crs[subscription_start][minute]",
																					 date("i",$subscription_start)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_START_HOUR",$this->__getDateSelect("hour","crs[subscription_start][hour]",
																					 date("G",$subscription_start)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_START_DAY",$this->__getDateSelect("day","crs[subscription_start][day]",
																					 date("d",$subscription_start)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_START_MONTH",$this->__getDateSelect("month","crs[subscription_start][month]",
																						 date("m",$subscription_start)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_START_YEAR",$this->__getDateSelect("year","crs[subscription_start][year]",
																						date("Y",$subscription_start)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_END_MINUTE",$this->__getDateSelect("minute","crs[subscription_end][minute]",
																					 date("i",$subscription_end)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_END_HOUR",$this->__getDateSelect("hour","crs[subscription_end][hour]",
																					 date("G",$subscription_end)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_END_DAY",$this->__getDateSelect("day","crs[subscription_end][day]",
																					 date("d",$subscription_end)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_END_MONTH",$this->__getDateSelect("month","crs[subscription_end][month]",
																					   date("m",$subscription_end)));
		$this->tpl->setVariable("SELECT_SUBSCRIPTION_END_YEAR",$this->__getDateSelect("year","crs[subscription_end][year]",
																					  date("Y",$subscription_end)));

		$this->tpl->setVariable("RADIO_SUB_DEACTIVATED",
								ilUtil::formRadioButton($subscription_type == $this->object->SUBSCRIPTION_DEACTIVATED ? 1 : 0,
														"crs[subscription_type]",$this->SUBSCRIPTION_DEACTIVATED));
		$this->tpl->setVariable("RADIO_SUB_CONFIRMATION",
								ilUtil::formRadioButton($subscription_type == $this->object->SUBSCRIPTION_CONFIRMATION ? 1 : 0,
														"crs[subscription_type]",
														$this->object->SUBSCRIPTION_CONFIRMATION));
		$this->tpl->setVariable("RADIO_SUB_DIRECT",ilUtil::formRadioButton($subscription_type == $this->object->SUBSCRIPTION_DIRECT ? 1 : 0,
																		   "crs[subscription_type]",$this->object->SUBSCRIPTION_DIRECT));
		$this->tpl->setVariable("RADIO_SUB_PASSWORD",
								ilUtil::formRadioButton($subscription_type == $this->object->SUBSCRIPTION_PASSWORD ? 1 : 0,
														"crs[subscription_type]",
														$this->object->SUBSCRIPTION_PASSWORD));

		$this->tpl->setVariable("CHECK_SUBSCRIPTION_NOTIFY",ilUtil::formCheckbox($subscription_notify,
																				 "crs[subscription_notify]",1));

		$this->tpl->setVariable("RADIO_SORTORDER_MANUAL",ilUtil::formRadioButton($sortorder_type == $this->object->SORT_MANUAL ? 1 : 0
																				 ,"crs[sortorder_type]",$this->object->SORT_MANUAL));
		$this->tpl->setVariable("RADIO_SORTORDER_TITLE",ilUtil::formRadioButton($sortorder_type == $this->object->SORT_TITLE ? 1 : 0
																				,"crs[sortorder_type]",$this->object->SORT_TITLE));
		$this->tpl->setVariable("RADIO_SORTORDER_ACTIVATION",
								ilUtil::formRadioButton($sortorder_type == $this->object->SORT_ACTIVATION ? 1 : 0,
														"crs[sortorder_type]",$this->object->SORT_ACTIVATION));

		$this->tpl->setVariable("CHECK_ABO",
								ilUtil::formCheckbox($abo_status == $this->object->ABO_ENABLED ? 1 : 0,
														"crs[abo_status]",$this->object->ABO_ENABLED));

		$this->tpl->setVariable("CHECK_OBJ_VIEW",
								ilUtil::formCheckbox($objective_view_status ?  1 : 0,
														"crs[objective_view]",1));

		$this->tpl->setVariable("SELECT_ARCHIVE_START_MINUTE",$this->__getDateSelect("minute","crs[archive_start][minute]",
																					 date("i",$archive_start)));
		$this->tpl->setVariable("SELECT_ARCHIVE_START_HOUR",$this->__getDateSelect("hour","crs[archive_start][hour]",
																					 date("G",$archive_start)));
		$this->tpl->setVariable("SELECT_ARCHIVE_START_DAY",$this->__getDateSelect("day","crs[archive_start][day]",
																					 date("d",$archive_start)));
		$this->tpl->setVariable("SELECT_ARCHIVE_START_MONTH",$this->__getDateSelect("month","crs[archive_start][month]",
																						 date("m",$archive_start)));
		$this->tpl->setVariable("SELECT_ARCHIVE_START_YEAR",$this->__getDateSelect("year","crs[archive_start][year]",
																						date("Y",$archive_start)));

		$this->tpl->setVariable("SELECT_ARCHIVE_END_MINUTE",$this->__getDateSelect("minute","crs[archive_end][minute]",
																					 date("i",$archive_end)));
		$this->tpl->setVariable("SELECT_ARCHIVE_END_HOUR",$this->__getDateSelect("hour","crs[archive_end][hour]",
																					 date("G",$archive_end)));
		$this->tpl->setVariable("SELECT_ARCHIVE_END_DAY",$this->__getDateSelect("day","crs[archive_end][day]",
																					 date("d",$archive_end)));
		$this->tpl->setVariable("SELECT_ARCHIVE_END_MONTH",$this->__getDateSelect("month","crs[archive_end][month]",
																					   date("m",$archive_end)));
		$this->tpl->setVariable("SELECT_ARCHIVE_END_YEAR",$this->__getDateSelect("year","crs[archive_end][year]",
																					  date("Y",$archive_end)));
		$this->tpl->setVariable("RADIO_ARCHIVE_DISABLED",ilUtil::formRadioButton($archive_type == $this->object->ARCHIVE_DISABLED ? 1 : 0,
																				 "crs[archive_type]",$this->object->ARCHIVE_DISABLED));
		$this->tpl->setVariable("RADIO_ARCHIVE_READ",ilUtil::formRadioButton($archive_type == $this->object->ARCHIVE_READ ? 1 : 0,
																			 "crs[archive_type]",$this->object->ARCHIVE_READ));
		$this->tpl->setVariable("RADIO_ARCHIVE_DOWNLOAD",ilUtil::formRadioButton($archive_type == $this->object->ARCHIVE_DOWNLOAD ? 1 : 0,
																				 "crs[archive_type]",$this->object->ARCHIVE_DOWNLOAD));

		$this->tpl->setVariable("CMD_SUBMIT","update");

		$this->initConditionHandlerGUI($this->object->getRefId());

		#$this->tpl->setVariable("PRECONDITION_TABLE",$this->chi_obj->chi_list());
		#$this->ctrl->setReturn($this,'editObject');
		#$this->tpl->setVariable("PRECONDITION_TABLE",$this->ctrl->forwardCommand($this->chi_obj));
	}

	function updateObject()
	{
		global $ilErr,$rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		// CREATE UNIX TIMESTAMPS FROM SELECT
		$this->object->setSyllabus(ilUtil::stripSlashes($_POST["crs"]["syllabus"]));
		$this->object->setContactName(ilUtil::stripSlashes($_POST["crs"]["contact_name"]));
		$this->object->setContactConsultation(ilUtil::stripSlashes($_POST["crs"]["contact_consultation"]));
		$this->object->setContactPhone(ilUtil::stripSlashes($_POST["crs"]["contact_phone"]));
		$this->object->setContactEmail(ilUtil::stripSlashes($_POST["crs"]["contact_email"]));
		$this->object->setContactResponsibility(ilUtil::stripSlashes($_POST["crs"]["contact_responsibility"]));

		$this->object->setActivationUnlimitedStatus((bool) $_POST["crs"]["activation_unlimited"]);
		$this->object->setActivationStart($this->__toUnix($_POST["crs"]["activation_start"]));
		$this->object->setActivationEnd($this->__toUnix($_POST["crs"]["activation_end"]));
		$this->object->setOfflineStatus(!$_POST["crs"]["activation_offline"]);

		$this->object->setSubscriptionUnlimitedStatus((bool) $_POST["crs"]["subscription_unlimited"]);
		$this->object->setSubscriptionStart($this->__toUnix($_POST["crs"]["subscription_start"]));
		$this->object->setSubscriptionEnd($this->__toUnix($_POST["crs"]["subscription_end"]));
		$this->object->setSubscriptionType($_POST["crs"]["subscription_type"]);
		$this->object->setSubscriptionPassword(ilUtil::stripSlashes($_POST["crs"]["subscription_password"]));
		$this->object->setSubscriptionMaxMembers($_POST["crs"]["subscription_max"]);
		$this->object->setSubscriptionNotify($_POST["crs"]["subscription_notify"]);
		$this->object->setOrderType($_POST["crs"]["sortorder_type"]);
		$this->object->setArchiveStart($this->__toUnix($_POST["crs"]["archive_start"]));
		$this->object->setArchiveEnd($this->__toUnix($_POST["crs"]["archive_end"]));
		$this->object->setArchiveType($_POST["crs"]["archive_type"]);
		$this->object->setAboStatus($_POST['crs']['abo_status']);
		$this->object->setObjectiveViewStatus((bool) $_POST['crs']['objective_view']);

		if($this->object->validate())
		{
			$this->object->update();
			sendInfo($this->lng->txt("crs_settings_saved"));
		}
		else
		{
			sendInfo($this->object->getMessage());
		}
		return $this->editObject();
	}


	/**
	* save object
	* @access	public
	*/
	function saveObject()
	{
		global $rbacadmin;

		$newObj =& parent::saveObject();
		$newObj->__initDefaultRoles();
		$newObj->initCourseMemberObject();
		$newObj->members_obj->add($this->ilias->account,$newObj->members_obj->ROLE_ADMIN);
		
		// always send a message
		sendInfo($this->lng->txt("crs_added"),true);

		ilUtil::redirect($this->getReturnLocation("save",$this->ctrl->getLinkTarget($this,"")));
	}


	// ARCHIVE METHODS
	function archiveObject()
	{
		global $rbacsystem;


		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		$edit_perm = $rbacsystem->checkAccess('write',$this->object->getRefId());
		$download_perm = ($rbacsystem->checkAccess('write',$this->object->getRefId()) or 
						  $this->object->getArchiveType() == $this->object->ARCHIVE_DOWNLOAD) 
			? true : false;

		$this->object->initCourseArchiveObject();
		$this->object->archives_obj->initCourseFilesObject();


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_archive.html","course");

		
		if($edit_perm)
		{
			$this->__showButton('archiveAdmin',$this->lng->txt("crs_edit_archive"));
		}

		if(!count($archives = $this->object->archives_obj->getPublicArchives()))
		{
			sendInfo($this->lng->txt("crs_no_archives_available"));
			return true;
		}
		
		$counter = 0;
		foreach($archives as $id => $archive_data)
		{
			if($download_perm)
			{
				$f_result[$counter][]	= ilUtil::formCheckbox(0,"archives[]",$id);
			}
			$link = '<a href="'.$this->object->archives_obj->course_files_obj->getOnlineLink($archive_data['archive_name']).'"'.
				' target="_blank">'.$archive_data["archive_name"].'</a>';

			$f_result[$counter][]	= $link;
			$f_result[$counter][]	= strftime("%Y-%m-%d %R",$archive_data["archive_date"]);
			$f_result[$counter][]	= $archive_data["archive_size"];

			if($archive_data["archive_lang"])
			{
				$f_result[$counter][]	= $this->lng->txt('lang_'.$archive_data["archive_lang"]);
			}
			else
			{
				$f_result[$counter][]	= $this->lng->txt('crs_not_available');
			}
				
			switch($archive_data["archive_type"])
			{
				case $this->object->archives_obj->ARCHIVE_XML:
					$type = $this->lng->txt("crs_xml");
					break;

				case $this->object->archives_obj->ARCHIVE_HTML:
					$type = $this->lng->txt("crs_html");
					break;

				case $this->object->archives_obj->ARCHIVE_PDF:
					$type = $this->lng->txt("crs_pdf");
					break;
			}
			$f_result[$counter][]	= $type;
			
			++$counter;
		}
		$this->__showArchivesTable($f_result,$download_perm);

		return true;
	}		


	function archiveAdminObject($a_show_confirm = false)
	{
		global $rbacsystem;


		$_POST["archives"] = $_POST["archives"] ? $_POST["archives"] : array();


		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initCourseArchiveObject();
		$this->object->archives_obj->initCourseFilesObject();

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_archive_adm.html","course");

		$this->__showButton('addXMLArchive',$this->lng->txt("crs_add_archive_xml"));
		$this->__showButton('selectArchiveLanguage',$this->lng->txt("crs_add_archive_html"));

		// Temporaly disabled
		#$this->__showButton('addPDFArchive',$this->lng->txt("crs_add_archive_pdf"));


		if($a_show_confirm)
		{
			$this->tpl->setCurrentBlock("confirm_delete");
			$this->tpl->setVariable("CONFIRM_FORMACTION",$this->ctrl->getFormAction($this));
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
			$this->tpl->setVariable("CONFIRM_CMD",'performDeleteArchives');
			$this->tpl->setVariable("TXT_CONFIRM",$this->lng->txt('delete'));
			$this->tpl->parseCurrentBlock();
		}

		if(!count($archives = $this->object->archives_obj->getArchives()))
		{
			sendInfo($this->lng->txt("crs_no_archives_available"));
			return true;
		}
		
		$counter = 0;
		foreach($archives as $id => $archive_data)
		{
			$f_result[$counter][]	= ilUtil::formCheckbox(in_array($id,$_POST["archives"]),"archives[]",$id);

			if($archive_data['archive_type'] == $this->object->archives_obj->ARCHIVE_HTML)
			{
				$link = '<a href="'.$this->object->archives_obj->course_files_obj->getOnlineLink($archive_data['archive_name']).'"'.
					' target="_blank">'.$archive_data["archive_name"].'</a>';
			}
			else
			{
				$link = $archive_data["archive_name"];
			}
			$f_result[$counter][]	= $link;
			$f_result[$counter][]	= strftime("%Y-%m-%d %R",$archive_data["archive_date"]);
			$f_result[$counter][]	= $archive_data["archive_size"];

			if($archive_data["archive_lang"])
			{
				$f_result[$counter][]	= $this->lng->txt('lang_'.$archive_data["archive_lang"]);
			}
			else
			{
				$f_result[$counter][]	= $this->lng->txt('crs_no_language');
			}

			switch($archive_data["archive_type"])
			{
				case $this->object->archives_obj->ARCHIVE_XML:
					$type = $this->lng->txt("crs_xml");
					break;

				case $this->object->archives_obj->ARCHIVE_HTML:
					$type = $this->lng->txt("crs_html");
					break;

				case $this->object->archives_obj->ARCHIVE_PDF:
					$type = $this->lng->txt("crs_pdf");
					break;
			}
			$f_result[$counter][]	= $type;
			
			++$counter;
		}
		$this->__showArchivesAdminTable($f_result);

		return true;
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
			sendInfo($this->lng->txt('crs_no_archive_selected'));
			$this->archiveAdminObject();

			return false;
		}
		if(count($_POST['archives']) > 1)
		{
			sendInfo($this->lng->txt('crs_select_one_archive'));
			$this->archiveAdminObject();

			return false;
		}

		$this->object->initCourseArchiveObject();
		
		$abs_path = $this->object->archives_obj->getArchiveFile((int) $_POST['archives'][0]);
		$basename = basename($abs_path);

		ilUtil::deliverFile($abs_path,$basename);
	}

	function deleteArchivesObject()
	{
		if(!$_POST["archives"])
		{
			sendInfo($this->lng->txt("crs_no_archives_selected"));
			$this->archiveAdminObject(false);
		}
		else
		{
			$_SESSION["crs_archives"] = $_POST["archives"];
			sendInfo($this->lng->txt("crs_sure_delete_selected_archives"));
			$this->archiveAdminObject(true);
		}

		return true;
	}
	
	function performDeleteArchivesObject()
	{
		if(!$_SESSION["crs_archives"])
		{
			sendInfo($this->lng->txt("crs_no_archives_selected"));
			$this->archiveAdminObject(false);
		}
		else
		{
			$this->object->initCourseArchiveObject();
			foreach($_SESSION["crs_archives"] as $archive_id)
			{
				$this->object->archives_obj->delete($archive_id);
			}
			sendInfo($this->lng->txt('crs_archives_deleted'));
			$this->archiveAdminObject(false);
			unset($_SESSION["crs_archives"]);
		}
	}
	function selectArchiveLanguageObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		foreach($this->lng->getInstalledLanguages() as $lang_code)
		{
			$actions["$lang_code"] = $this->lng->txt('lang_'.$lang_code);

			if($this->lng->getLangKey() == $lang_code)
			{
				$selected = $lang_code;
			}
		}

		sendInfo($this->lng->txt('crs_select_archive_language'));

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_selectLanguage.html","course");

		$this->tpl->setVariable("SELECT_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("LANG_SELECTOR",ilUtil::formSelect($selected,'lang',$actions,false,true));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt('crs_add_html_archive'));
		$this->tpl->setVariable("CMD_SUBMIT",'addHTMLArchive');

		return true;
	}

	function addXMLArchiveObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->object->initCourseArchiveObject();
		$this->object->archives_obj->addXML();
		
		sendInfo($this->lng->txt("crs_added_new_archive"));
		$this->archiveAdminObject();

		return true;
	}
	function addHTMLArchiveObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'write'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		
		$this->object->initCourseArchiveObject();
		$this->object->archives_obj->setLanguage($_POST['lang']);
		$this->object->archives_obj->addHTML();

		sendInfo($this->lng->txt("crs_added_new_archive"));
		$this->archiveAdminObject();

		return true;
	}		



	// MEMBER METHODS
	function membersObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_members.html","course");
		$this->__showButton("printMembers",$this->lng->txt("crs_print_list"),"target=\"_blank\"");

		// INFO NO MEMBERS
		$this->object->initCourseMemberObject();

		if(!count($this->object->members_obj->getAssignedUsers()) and
		   !count($this->object->member_obj->getSubscribers()))
		{
			sendInfo($this->lng->txt("crs_no_members_assigned"));
			return false;
		}

		// SUBSCRIBERS
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
					$f_result[$counter][]	= ilUtil::formCheckbox(0,"subscriber[]",$member_id);
					$f_result[$counter][]	= $tmp_obj->getLogin();
					$f_result[$counter][]	= $tmp_obj->getFirstname();
					$f_result[$counter][]	= $tmp_obj->getLastname();
					$f_result[$counter][]   = strftime("%Y-%m-%d %R",$member_data["time"]);

					unset($tmp_obj);
					++$counter;
				}
			}
			$this->__showSubscribersTable($f_result);

		} // END SUBSCRIBERS

		// MEMBERS
		if(count($this->object->members_obj->getAssignedUsers()))
		{
			$counter = 0;
			$f_result = array();

			$img_mail = "<img src=\"".ilUtil::getImagePath("icon_pencil_b.gif")."\" alt=\"".
				$this->lng->txt("crs_mem_send_mail").
				"\" title=\"".$this->lng->txt("crs_mem_send_mail")."\" border=\"0\" vspace=\"0\"/>";

			$img_change = "<img src=\"".ilUtil::getImagePath("icon_change_b.gif")."\" alt=\"".
				$this->lng->txt("crs_mem_change_status")."\" title=\"".$this->lng->txt("crs_mem_change_status").
				"\" border=\"0\" vspace=\"0\"/>";

			foreach($this->object->members_obj->getAssignedUsers() as $member_id)
			{
				$member_data = $this->object->members_obj->getUserData($member_id);

				// GET USER OBJ
				if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
				{
					$f_result[$counter][]	= ilUtil::formCheckbox(0,"member[]",$member_id);
					$f_result[$counter][]	= $tmp_obj->getLogin();
					$f_result[$counter][]	= $tmp_obj->getFirstname();
					$f_result[$counter][]	= $tmp_obj->getLastname();

					switch($member_data["role"])
					{
						case $this->object->members_obj->ROLE_ADMIN:
							$role = $this->lng->txt("crs_admin");
							break;

						case $this->object->members_obj->ROLE_TUTOR:
							$role = $this->lng->txt("crs_tutor");
							break;

						case $this->object->members_obj->ROLE_MEMBER:
							$role = $this->lng->txt("crs_member");
							break;
					}
					$f_result[$counter][]   = $role;
					
					switch($member_data["status"])
					{
						case $this->object->members_obj->STATUS_NOTIFY:
							$f_result[$counter][] = $this->lng->txt("crs_notify");
							break;

						case $this->object->members_obj->STATUS_NO_NOTIFY:
							$f_result[$counter][] = $this->lng->txt("crs_no_notify");
							break;

						case $this->object->members_obj->STATUS_BLOCKED:
							$f_result[$counter][] = $this->lng->txt("crs_blocked");
							break;

						case $this->object->members_obj->STATUS_UNBLOCKED:
							$f_result[$counter][] = $this->lng->txt("crs_unblocked");
							break;
					}

					$f_result[$counter]['passed'] = $member_data['passed'] ?
						$this->lng->txt('crs_member_passed') :
						$this->lng->txt('crs_member_not_passed');

					$link_mail = "<a target=\"_blank\" href=\"mail_new.php?type=new&rcp_to=".
						$tmp_obj->getLogin()."\">".$img_mail."</a>";

					$this->ctrl->setParameter($this,"member_id",$tmp_obj->getId());
					$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editMember")."\">".
						$img_change."</a>";
					$f_result[$counter][]	= $link_mail." ".$link_change;
					unset($tmp_obj);
					++$counter;
				}
			} // END IF MEMBERS

		}
		return $this->__showMembersTable($f_result);
	}


	function editMemberObject()
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
		
		$member_data = $this->object->members_obj->getUserData((int) $_GET["member_id"]);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html","course");


		$f_result = array();

		// GET USER OBJ
		$tmp_obj = ilObjectFactory::getInstanceByObjId($member_data["usr_id"],false);

		$f_result[0][]	= $tmp_obj->getLogin();
		$f_result[0][]	= $tmp_obj->getFirstname();
		$f_result[0][]	= $tmp_obj->getLastname();

		$f_result[0][]	= ilUtil::formCheckbox($member_data['passed'] ? 1 : 0,'passed',1);

		$actions = array(0	=> $this->lng->txt("crs_member_unblocked"),
						 1 	=> $this->lng->txt("crs_member_blocked"),
						 2	=> $this->lng->txt("crs_tutor_notify"),
						 3	=> $this->lng->txt("crs_tutor_no_notify"),
						 4	=> $this->lng->txt("crs_admin_notify"),
						 5	=> $this->lng->txt("crs_admin_no_notify"));

		// GET SELECTED
		switch($member_data["role"])
		{
			case $this->object->members_obj->ROLE_ADMIN:
				if($member_data["status"] == $this->object->members_obj->STATUS_NOTIFY)
				{
					$selected = 4;
				}
				else
				{
					$selected = 5;
				}
				break;

			case $this->object->members_obj->ROLE_TUTOR:
				if($member_data["status"] == $this->object->members_obj->STATUS_NOTIFY)
				{
					$selected = 2;
				}
				else
				{
					$selected = 3;
				}
				break;

			case $this->object->members_obj->ROLE_MEMBER:
				if($member_data["status"] == $this->object->members_obj->STATUS_UNBLOCKED)
				{
					$selected = 0;
				}
				else
				{
					$selected = 1;
				}
				break;
		}
		$f_result[0][]	= ilUtil::formSelect($selected,"role_status",$actions,false,true);

		unset($tmp_obj);
		
		$this->__showEditMemberTable($f_result);

		return true;
	}

	function updateMemberObject()
	{
		global $rbacsystem;

		$this->object->initCourseMemberObject();

		// USED FOR NOTIFICATION
		$user_data = $this->object->members_obj->getUserData($_GET["member_id"]);

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
		// CHECK LAST ADMIN
		if((int) $_POST['role_status'] != 4 or (int) $_POST['role_status'] != 5)
		{
			if(!$this->object->members_obj->checkLastAdmin(array((int) $_GET['member_id'])))
			{
				$this->ilias->raiseError($this->lng->txt("crs_at_least_one_admin"),$this->ilias->error_obj->MESSAGE);
			}
		}
		
		// UPDATE MEMBER
		switch((int) $_POST["role_status"])
		{
			case 0:
				// CHECK IF LIMIT MAX MEMBERS IS REACHED
				if($this->object->getSubscriptionMaxMembers() and
				   $this->object->members_obj->getCountMembers() >= $this->object->getSubscriptionMaxMembers())
				{
					sendInfo($this->lng->txt("crs_max_members_reached"));
					$this->membersObject();

					return false;
				}
				$status = $this->object->members_obj->STATUS_UNBLOCKED;;
				$role = $this->object->members_obj->ROLE_MEMBER;
				break;

			case 1:
				$status = $this->object->members_obj->STATUS_BLOCKED;;
				$role = $this->object->members_obj->ROLE_MEMBER;
				break;


			case 2:
				$status = $this->object->members_obj->STATUS_NOTIFY;
				$role = $this->object->members_obj->ROLE_TUTOR;
				break;

			case 3:
				$status = $this->object->members_obj->STATUS_NO_NOTIFY;
				$role = $this->object->members_obj->ROLE_TUTOR;
				break;

			case 4:
				$status = $this->object->members_obj->STATUS_NOTIFY;
				$role = $this->object->members_obj->ROLE_ADMIN;
				break;

			case 5:
				$status = $this->object->members_obj->STATUS_NO_NOTIFY;
				$role = $this->object->members_obj->ROLE_ADMIN;
				break;

			default:
				$this->ilias->raiseError("No valid status given",$this->ilias->error_obj->MESSAGE);
		}
		$this->object->members_obj->update((int) $_GET["member_id"],$role,$status,(int) $_POST['passed']);

		// NOTIFICATION
		if($user_data["role"] != $role or 
		   $user_data["status"] != $status or 
		   $user_data['passed'] != (bool) $_POST['passed'])
		{
			$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_STATUS_CHANGED,$_GET["member_id"]);
		}
		

		sendInfo($this->lng->txt("crs_member_updated"));
		$this->membersObject();
	}
	function addUserObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["user"]))
		{
			sendInfo($this->lng->txt("crs_no_users_selected"));
			$this->searchObject();

			return false;
		}
		$this->object->initCourseMemberObject();

		$added_users = 0;
		$limit_reached = false;
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
			if($this->object->getSubscriptionMaxMembers() and
			   $this->object->getSubscriptionMaxMembers() <= $this->object->members_obj->getCountMembers())
			{
				$limit_reached = true;
				break;
			}
			$this->object->members_obj->add($tmp_obj,$this->object->members_obj->ROLE_MEMBER);
			$this->object->members_obj->sendNotification($this->object->members_obj->NOTIFY_ACCEPT_USER,$user_id);

			++$added_users;
		}
		if($limit_reached)
		{
			sendInfo($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		if($added_users)
		{
			sendInfo($this->lng->txt("crs_users_added"));
			unset($_SESSION["crs_search_str"]);
			unset($_SESSION["crs_search_for"]);
			$this->membersObject();

			return true;
		}
		else
		{
			sendInfo($this->lng->txt("crs_users_already_assigned"));
			$this->searchObject();

			return false;
		}
		return false;
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
			sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();
		
		if($this->object->getSubscriptionMaxMembers() and 
		   ($this->object->getSubscriptionMaxMembers() < ($this->object->members_obj->getCountMembers() + count($_POST["subscriber"]))))
		{
			sendInfo($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		if(!$this->object->members_obj->assignSubscribers($_POST["subscriber"]))
		{
			sendInfo($this->object->getMessage());
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
		sendInfo($this->lng->txt("crs_subscribers_assigned"));
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
			sendInfo($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		if($number = $this->object->members_obj->autoFillSubscribers())
		{
			sendInfo($this->lng->txt("crs_number_users_added")." ".$number);
		}
		else
		{
			sendInfo($this->lng->txt("crs_no_users_added"));
		}
		$this->membersObject();

		return true;
	}


	function deleteSubscribers()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["subscriber"]) or !count($_POST["subscriber"]))
		{
			sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		sendInfo($this->lng->txt("crs_delete_subscribers_sure"));

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html","course");
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
				$f_result[$counter][]   = $member_data["time"];

				unset($tmp_obj);
				++$counter;
			}
		}
		return $this->__showDeleteSubscriberTable($f_result);
	}
		
	
	function unsubscribeObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("leave", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_unsubscribe_sure.html","course");
		sendInfo($this->lng->txt('crs_unsubscribe_sure'));
		
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
		
		sendInfo($this->lng->txt('crs_unsubscribed_from_crs'),true);
		$this->ctrl->setParameterByClass("ilRepositoryGUI","ref_id",$this->tree->getParentId($this->ref_id));
		$this->ctrl->redirectByClass("ilRepositoryGUI","ShowList");
	}

	function deleteMembers()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["member"]) or !count($_POST["member"]))
		{
			sendInfo($this->lng->txt("crs_no_member_selected"));
			$this->membersObject();

			return false;
		}
		sendInfo($this->lng->txt("crs_delete_members_sure"));

		$this->object->initCourseMemberObject();

		// CHECK LAST ADMIN
		if(!$this->object->members_obj->checkLastAdmin($_POST['member']))
		{
			sendInfo($this->lng->txt('crs_at_least_one_admin'));
			$this->membersObject();

			return false;
		}

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html","course");

		

		// SAVE IDS IN SESSION
		$_SESSION["crs_delete_member_ids"] = $_POST["member"];

		$counter = 0;
		$f_result = array();

		foreach($_POST["member"] as $member_id)
		{
			$member_data = $this->object->members_obj->getUserData($member_id);

			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $tmp_obj->getFirstname();
				$f_result[$counter][]	= $tmp_obj->getLastname();

				switch($member_data['role'])
				{
					case $this->object->members_obj->ROLE_ADMIN:
						$f_result[$counter][] = $this->lng->txt("crs_admin"); 
						break;
					case $this->object->members_obj->ROLE_TUTOR:
						$f_result[$counter][] = $this->lng->txt("crs_tutor");
						break;
					case $this->object->members_obj->ROLE_MEMBER:
						$f_result[$counter][] = $this->lng->txt("crs_member"); 
						break;
				}

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
			sendInfo($this->lng->txt("crs_no_member_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();

		if(!$this->object->members_obj->deleteMembers($_SESSION["crs_delete_member_ids"]))
		{
			sendInfo($this->object->getMessage());
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
		sendInfo($this->lng->txt("crs_members_deleted"));
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
			sendInfo($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		$this->object->initCourseMemberObject();

		if(!$this->object->members_obj->deleteSubscribers($_SESSION["crs_delete_subscriber_ids"]))
		{
			sendInfo($this->object->getMessage());
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
		sendInfo($this->lng->txt("crs_subscribers_deleted"));
		$this->membersObject();

		return true;
	}


	function searchUserObject()
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
			sendInfo($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_members_search.html","course");
		
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
	
	function searchObject()
	{
		global $rbacsystem,$tree;

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
			sendInfo($this->lng->txt("crs_search_enter_search_string"));
			$this->searchUserObject();
			
			return false;
		}
		if(!count($result = $this->__search($_POST["search_str"],$_POST["search_for"])))
		{
			sendInfo($this->lng->txt("crs_no_results_found"));
			$this->searchUserObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html","course");
		$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
		
		$counter = 0;
		$f_result = array();
		switch($_POST["search_for"])
		{
			case "usr":
				foreach($result as $user)
				{
					if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user["id"],false))
					{
						continue;
					}
					$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user["id"]);
					$f_result[$counter][] = $tmp_obj->getLogin();
					$f_result[$counter][] = $tmp_obj->getLastname();
					$f_result[$counter][] = $tmp_obj->getFirstname();

					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchUserTable($f_result);

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
					$f_result[$counter][] = ilUtil::formCheckbox(0,"group[]",$group["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchGroupTable($f_result);

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

					$f_result[$counter][] = ilUtil::formCheckbox(0,"role[]",$role["id"]);
					$f_result[$counter][] = array($tmp_obj->getTitle(),$tmp_obj->getDescription());
					$f_result[$counter][] = $tmp_obj->getCountMembers();

					unset($tmp_obj);
					++$counter;
				}

				$this->__showSearchRoleTable($f_result);

				return true;
		}
	}

	function listUsersGroupObject()
	{
		global $rbacsystem,$tree;

		$_SESSION["crs_group"] = $_POST["group"] = $_POST["group"] ? $_POST["group"] : $_SESSION["crs_group"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["group"]))
		{
			sendInfo($this->lng->txt("crs_no_groups_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html","course");
		$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
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

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,"listUsersGroup");

		return true;
	}
	
	function listUsersRoleObject()
	{
		global $rbacsystem,$rbacreview,$tree;

		$_SESSION["crs_role"] = $_POST["role"] = $_POST["role"] ? $_POST["role"] : $_SESSION["crs_role"];

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!is_array($_POST["role"]))
		{
			sendInfo($this->lng->txt("crs_no_roles_selected"));
			$this->searchObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html","course");
		$this->__showButton("searchUser",$this->lng->txt("crs_new_search"));
		$this->object->initCourseMemberObject();

		// GET ALL MEMBERS
		$members = array();
		foreach($_POST["role"] as $role_id)
		{
			$members = array_merge($rbacreview->assignedUsers($role_id),$members);
		}

		$members = array_unique($members);

		// FORMAT USER DATA
		$counter = 0;
		$f_result = array();
		foreach($members as $user)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user,false))
			{
				continue;
			}
			$f_result[$counter][] = ilUtil::formCheckbox(0,"user[]",$user);
			$f_result[$counter][] = $tmp_obj->getLogin();
			$f_result[$counter][] = $tmp_obj->getLastname();
			$f_result[$counter][] = $tmp_obj->getFirstname();

			unset($tmp_obj);
			++$counter;
		}
		$this->__showSearchUserTable($f_result,"listUsersRole");
		
		return true;
	}
		
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->object->initCourseMemberObject();

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($this->object->isActivated(false))
		{
			if($rbacsystem->checkAccess('write',$this->ref_id) and $this->object->enabledObjectiveView())
			{
				$tabs_gui->addTarget('learners_view',
									 $this->ctrl->getLinkTarget($this, "cciObjectives"), "", get_class($this));
			}
			else
			{
				$tabs_gui->addTarget('view_content',
									 $this->ctrl->getLinkTarget($this, ""), "", get_class($this));
			}
		}
		if($rbacsystem->checkAccess('write',$this->ref_id) and $this->object->enabledObjectiveView())
		{
			$tabs_gui->addTarget('edit_content',
								 $this->ctrl->getLinkTarget($this, 'cciObjectivesEdit'), "", get_class($this));

		}

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("crs_details",
								 $this->ctrl->getLinkTarget($this, "details"), "details", get_class($this));
		}
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("edit_properties",
								 $this->ctrl->getLinkTarget($this, "edit"), "edit", get_class($this));
		}

		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("meta_data",
								 $this->ctrl->getLinkTarget($this, "editMeta"), "editMeta", get_class($this));
		}

		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("members",
								 $this->ctrl->getLinkTarget($this, "members"), "members", get_class($this));
		}
		if ($rbacsystem->checkAccess('write',$this->ref_id) or 
			$this->object->isArchived())
		{
			$tabs_gui->addTarget("crs_archives",
								 $this->ctrl->getLinkTarget($this, "archive"), "archive", get_class($this));
		}
		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("crs_objectives",
								 $this->ctrl->getLinkTarget($this,"listObjectives"), 
								 "objectives", 
								 get_class($this));
		}
		if($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("crs_groupings",
								 $this->ctrl->getLinkTarget($this, "listGroupings"), "groupings", get_class($this));
		}
		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTarget($this, "perm"), "perm", get_class($this));
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
		if($rbacsystem->checkAccess('leave',$this->ref_id) and 
		   $this->object->members_obj->isMember($this->ilias->account->getId()))
		{
			$tabs_gui->addTarget("crs_unsubscribe",
								 $this->ctrl->getLinkTarget($this, "unsubscribe"), "unsubscribe", get_class($this));
		}
	}

	function printMembersObject()
	{
		$tpl =& new ilTemplate('tpl.crs_members_print.html',true,true,'course');

		$this->object->initCourseMemberObject();


		// MEMBERS
		if(count($members = $this->object->members_obj->getAssignedUsers()))
		{
			foreach($members as $member_id)
			{
				$member_data = $this->object->members_obj->getUserData($member_id);

				// GET USER OBJ
				if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
				{
					$tpl->setCurrentBlock("members_row");
					$tpl->setVariable("LOGIN",$tmp_obj->getLogin());
					$tpl->setVariable("FIRSTNAME",$tmp_obj->getFirstname());
					$tpl->setVariable("LASTNAME",$tmp_obj->getLastname());

					switch($member_data["role"])
					{
						case $this->object->members_obj->ROLE_ADMIN:
							$role = $this->lng->txt("crs_admin");
							break;

						case $this->object->members_obj->ROLE_TUTOR:
							$role = $this->lng->txt("crs_tutor");
							break;

						case $this->object->members_obj->ROLE_MEMBER:
							$role = $this->lng->txt("crs_member");
							break;
					}
					$tpl->setVariable("ROLE",$role);
					
					switch($member_data["status"])
					{
						case $this->object->members_obj->STATUS_NOTIFY:
							$status = $this->lng->txt("crs_notify");
							break;

						case $this->object->members_obj->STATUS_NO_NOTIFY:
							$status = $this->lng->txt("crs_no_notify");
							break;

						case $this->object->members_obj->STATUS_BLOCKED:
							$status = $this->lng->txt("crs_blocked");
							break;

						case $this->object->members_obj->STATUS_UNBLOCKED:
							$status = $this->lng->txt("crs_unblocked");
							break;
					}
					$tpl->setVariable("STATUS",$status);
					$tpl->setVariable("PASSED",$member_data['passed'] ? 
									  $this->lng->txt('crs_member_passed') :
									  $this->lng->txt('crs_member_not_passed'));
					$tpl->parseCurrentBlock();
				}
			}
			$tpl->setCurrentBlock("members");

			$tpl->setVariable("MEMBERS_IMG_SOURCE",ilUtil::getImagePath('icon_usr_b.gif'));
			$tpl->setVariable("MEMBERS_IMG_ALT",$this->lng->txt('crs_header_members'));
			$tpl->setVariable("MEMBERS_TABLE_HEADER",$this->lng->txt('crs_members_title'));
			$tpl->setVariable("TXT_LOGIN",$this->lng->txt('username'));
			$tpl->setVariable("TXT_FIRSTNAME",$this->lng->txt('firstname'));
			$tpl->setVariable("TXT_LASTNAME",$this->lng->txt('lastname'));
			$tpl->setVariable("TXT_ROLE",$this->lng->txt('crs_role'));
			$tpl->setVariable("TXT_STATUS",$this->lng->txt('crs_status'));
			$tpl->setVariable("TXT_PASSED",$this->lng->txt('crs_passed'));

			$tpl->parseCurrentBlock();

		}
		// SUBSCRIBERS
		if(count($members = $this->object->members_obj->getSubscribers()))
		{
			foreach($members as $member_id)
			{
				$member_data = $this->object->members_obj->getSubscriberData($member_id);

				// GET USER OBJ
				if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
				{
					$tpl->setCurrentBlock("members_row");
					$tpl->setVariable("SLOGIN",$tmp_obj->getLogin());
					$tpl->setVariable("SFIRSTNAME",$tmp_obj->getFirstname());
					$tpl->setVariable("SLASTNAME",$tmp_obj->getLastname());
					$tpl->setVariable("STIME",$member_data["time"]);
					$tpl->parseCurrentBlock();
				}
			}
			$tpl->setCurrentBlock("members");

			$tpl->setVariable("SUBSCRIBERS_IMG_SOURCE",ilUtil::getImagePath('icon_usr_b.gif'));
			$tpl->setVariable("SUBSCRIBERS_IMG_ALT",$this->lng->txt('crs_subscribers'));
			$tpl->setVariable("SUBSCRIBERS_TABLE_HEADER",$this->lng->txt('crs_subscribers'));
			$tpl->setVariable("TXT_SLOGIN",$this->lng->txt('username'));
			$tpl->setVariable("TXT_SFIRSTNAME",$this->lng->txt('firstname'));
			$tpl->setVariable("TXT_SLASTNAME",$this->lng->txt('lastname'));
			$tpl->setVariable("TXT_STIME",$this->lng->txt('crs_time'));

			$tpl->parseCurrentBlock();

		}

		$tpl->setVariable("TITLE",$this->lng->txt('crs_members_print_title'));
		$tpl->setVariable("CSS_PATH",$this->tpl->tplPath);
		
		$headline = $this->lng->txt('obj_crs').': '.$this->object->getTitle().
			' -> '.$this->lng->txt('crs_header_members').' ('.strftime("%Y-%m-%d %R",time()).')';

		$tpl->setVariable("HEADLINE",$headline);

		$tpl->show();
		exit;
	}

	// GROUPING METHODS
	function listGroupingsObject()
	{
		include_once './course/classes/class.ilObjCourseGrouping.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_groupings_list.html","course");
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->ctrl->setParameterByClass('ilobjcoursegroupinggui','ref_id',$this->object->getRefId());
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','create'));
		$this->tpl->setVariable("BTN_TXT",$this->lng->txt('crs_add_grouping'));
		$this->tpl->parseCurrentBlock();

		if(ilObjCourseGrouping::_getAllGroupings($this->object->getRefId(),false))
		{
			$this->tpl->setCurrentBlock("btn_cell");
			$this->ctrl->setParameterByClass('ilobjcoursegroupinggui','ref_id',$this->object->getRefId());
			$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','otherSelectAssign'));
			$this->tpl->setVariable("BTN_TXT",$this->lng->txt('crs_other_groupings'));
			$this->tpl->parseCurrentBlock();
		}

		if(!count($groupings = ilObjCourseGrouping::_getGroupings($this->object->getId())))
		{
			sendInfo($this->lng->txt('crs_no_groupings_assigned'));
		
			return true;
		}
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs_groupings'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_groupings'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("HEADER_UNAMBIGUOUSNESS",$this->lng->txt('unambiguousness'));
		$this->tpl->setVariable("HEADER_OPTIONS",$this->lng->txt('crs_options'));
		$this->tpl->setVariable("HEADER_ASSIGNED",$this->lng->txt('crs_grp_assigned_courses'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		
		
		$counter = 0;
		foreach($groupings as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping($grouping_id);

			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_GRP",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("grouping_row");
			$this->tpl->setVariable("GRP_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("CHECK_GRP",ilUtil::formCheckbox(0,'grouping[]',$grouping_id));
			$this->tpl->setVariable("AMB_GRP",$this->lng->txt($tmp_obj->getUniqueField()));
			$this->tpl->setVariable("EDIT_IMG",ilUtil::getImagePath('edit.gif'));
			$this->tpl->setVariable("EDIT_ALT",$this->lng->txt('edit'));
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));

			$this->ctrl->setParameterByClass('ilobjcoursegroupinggui','obj_id',$grouping_id);
			$this->tpl->setVariable("EDIT_LINK",$this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','edit'));

			if($num_courses = $tmp_obj->getCountAssignedCourses())
			{
				$this->tpl->setVariable("ASSIGNED_COURSES",$this->lng->txt('crs_grp_assigned_courses_info')." <b>$num_courses</b> ");
			}
			else
			{
				$this->tpl->setVariable("ASSIGNED_COURSES",$this->lng->txt('crs_grp_no_courses_assigned'));
			}
			$this->tpl->parseCurrentBlock();
		}	

	}

	function askDeleteGroupingObject()
	{
		include_once './course/classes/class.ilObjCourseGrouping.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_POST['grouping']))
		{
			sendInfo($this->lng->txt('crs_grouping_select_one'));
			$this->listGroupingsObject();
			
			return false;
		}

		sendInfo($this->lng->txt('crs_grouping_delete_sure'));
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_ask_delete_goupings.html","course");

		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TBL_TITLE_IMG",ilUtil::getImagePath('icon_crs.gif'));
		$this->tpl->setVariable("TBL_TITLE_IMG_ALT",$this->lng->txt('crs_groupings'));
		$this->tpl->setVariable("TBL_TITLE",$this->lng->txt('crs_groupings_ask_delete'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("BTN_DELETE",$this->lng->txt('delete'));
		
		
		$counter = 0;
		foreach($_POST['grouping'] as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping($grouping_id);

			if(strlen($tmp_obj->getDescription()))
			{
				$this->tpl->setCurrentBlock("description");
				$this->tpl->setVariable("DESCRIPTION_GRP",$tmp_obj->getDescription());
				$this->tpl->parseCurrentBlock();
			}
			$this->tpl->setCurrentBlock("grouping_row");
			$this->tpl->setVariable("GRP_TITLE",$tmp_obj->getTitle());
			$this->tpl->setVariable("ROW_CLASS",ilUtil::switchColor(++$counter,'tblrow1','tblrow2'));
			$this->tpl->parseCurrentBlock();
		}
		$_SESSION['crs_grouping_del'] = $_POST['grouping'];
	}

	function deleteGroupingObject()
	{
		include_once './course/classes/class.ilObjCourseGrouping.php';

		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		if(!count($_SESSION['crs_grouping_del']))
		{
			sendInfo('No grouping selected');
			$this->listGroupingsObject();

			return false;
		}
		foreach($_SESSION['crs_grouping_del'] as $grouping_id)
		{
			$tmp_obj =& new ilObjCourseGrouping($grouping_id);
			$tmp_obj->delete();
		}
		sendInfo($this->lng->txt('crs_grouping_deleted'));
		$this->listGroupingsObject();
		
		unset($_SESSION['crs_grouping_del']);
		return true;
	}

	
	// META DATA METHODS
	function editMetaObject()
	{
		$this->__initMetaDataGUI();

		$this->meta_gui->edit("ADM_CONTENT","adm_content",$this->ctrl->getLinkTarget($this),$_REQUEST["meta_section"]);

		return true;
	}
	function editMeta()
	{
		return $this->editMetaObject();
	}
	function saveMetaObject()
	{
		$this->__initMetaDataGUI();

		$this->meta_gui->save($_POST["meta_section"]);

		sendInfo($this->lng->txt("msg_obj_modified"),true);

		$this->ctrl->setParameter($this,'meta_section',$_POST["meta_section"]);
		$this->ctrl->redirect($this,'editMeta');
	}
	function saveMeta()
	{
		return $this->saveMetaObject();
	}
	function chooseMetaSectionObject()
	{
		$this->__initMetaDataGUI();

		$this->meta_gui->edit("ADM_CONTENT", "adm_content",$this->ctrl->getLinkTarget($this),$_REQUEST["meta_section"]);
	}
	function chooseMetaSection()
	{
		$this->chooseMetaSectionObject();
	}
	function addMetaObject()
	{
		$this->__initMetaDataGUI();

		if($_REQUEST["meta_name"])
		{
			$_REQUEST["meta_index"] = $_REQUEST["meta_index"] ? $_REQUEST["meta_index"] : 0;
			
			$this->meta_gui->meta_obj->add($_REQUEST["meta_name"],$_REQUEST["meta_path"], $_REQUEST["meta_index"]);

			sendInfo($this->lng->txt("added_item"));

		}
		else
		{
			sendInfo($this->lng->txt("meta_choose_element"), true);
		}
		$this->meta_gui->edit("ADM_CONTENT","adm_content",$this->ctrl->getLinkTarget($this),$_REQUEST['meta_section']);

		return true;
	}
	function addMeta()
	{
		$this->addMetaObject();
	}
	function deleteMetaObject()
	{
		$this->__initMetaDataGUI();
		$this->meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $_REQUEST["meta_index"]);

		$this->editMetaObject("ADM_CONTENT","adm_content",$this->ctrl->getLinkTarget($this),$_REQUEST["meta_section"]);

		sendInfo($this->lng->txt("deleted_item"));

		return true;
	}
	function deleteMeta()
	{
		$this->deleteMetaObject();
	}
	// END META DATA METHODS


	function &__initTableGUI()
	{
		include_once "./classes/class.ilTableGUI.php";

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
				$order = $_GET["update_subscribers"] ? $_GET["sort_by"] : 'login';
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

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","members");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","updateMember");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("save"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt('crs_passed'),
								   $this->lng->txt("crs_role_status")));
		$tbl->setHeaderVars(array("login",
								  "firstname",
								  "lastname",
								  "passed",
								  "role"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("20%","20%","20%","20%","20%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->disable('sort');
		$tbl->render();
		
		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());
	}

	function __showSearchUserTable($a_result_set,$a_cmd = "search")
	{
        $return_to  = "searchUser";

    	if ($a_cmd == "listUsersRole" or $a_cmd == "listUsersGroup")
    	{
            $return_to = "search";
        }
        
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME",$return_to);
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("back"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

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
	
		function __showSearchGroupTable($a_result_set)
	{
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
		$tpl->setVariable("BTN_NAME","listUsersGroup");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("crs_list_users"));
		$tpl->parseCurrentBlock();

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
	
	function __showSearchRoleTable($a_result_set)
	{
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
		$tpl->setVariable("BTN_NAME","cancelMember");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","removeMembers");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("crs_delete_member"));
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

	function __showDeleteSubscriberTable($a_result_set)
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
		$tpl->setVariable("BTN_NAME","removeSubscribers");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
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

	function __showArchivesTable($a_result_set,$a_download_perm)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		if($a_download_perm)
		{
			$tpl->setCurrentBlock("tbl_action_row");
			$tpl->setVariable("COLUMN_COUNTS",6);
			$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

			#$tpl->setCurrentBlock("tbl_action_btn");
			#$tpl->setVariable("BTN_NAME","deleteArchives");
			#$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
			#$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("tbl_action_btn");
			$tpl->setVariable("BTN_NAME","downloadArchives");
			$tpl->setVariable("BTN_VALUE",$this->lng->txt("download"));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("tbl_action_row");
			$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
			$tpl->parseCurrentBlock();
		}
		$tbl->setTitle($this->lng->txt("crs_header_archives"),"icon_crs.gif",$this->lng->txt("crs_header_archives"));

		if($a_download_perm)
		{
			$header_names = array('',
								  $this->lng->txt("crs_file_name"),
								  $this->lng->txt("crs_create_date"),
								  $this->lng->txt("crs_size"),
								  $this->lng->txt("crs_archive_lang"),
								  $this->lng->txt("crs_archive_type"));

			$header_vars = array("",
								 "name",
								 "type",
								 "date",
								 "lang",
								 "size");
			$column_width = array("4%","26%","20%","10%","20%");
		}
		else
		{
			$header_names = array($this->lng->txt("crs_file_name"),
								  $this->lng->txt("crs_create_date"),
								  $this->lng->txt("crs_size"),
								  $this->lng->txt("crs_archive_lang"),
								  $this->lng->txt("crs_archive_type"));

			$header_vars = array("name",
								 "type",
								 "date",
								 "lang",
								 "size");
			$column_width = array("28%","22%","10%","20%");
		}
		
		$tbl->setHeaderNames($header_names);
		$tbl->setHeaderVars($header_vars,
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "archive",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth($column_width);


		$this->__setTableGUIBasicData($tbl,$a_result_set,"archive");
		$tbl->render();

		$this->tpl->setVariable("ARCHIVE_TABLE",$tbl->tpl->get());

		return true;
	}
	function __showArchivesAdminTable($a_result_set)
	{
		#$actions = array("deleteArchivesObject"	=> $this->lng->txt("crs_delete_archive"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setVariable("COLUMN_COUNTS",6);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_btn");
		#$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","deleteArchives");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","downloadArchives");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("download"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_archives"),"icon_crs.gif",$this->lng->txt("crs_header_archives"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("crs_file_name"),
								   $this->lng->txt("crs_create_date"),
								   $this->lng->txt("crs_size"),
								   $this->lng->txt("crs_archive_lang"),
								   $this->lng->txt("crs_archive_type")));
		$tbl->setHeaderVars(array("",
								  "name",
								  "type",
								  "date",
								  "language",
								  "size"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "archiveAdmin",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","26%","20%","10%","20%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"archive");
		$tbl->render();

		$this->tpl->setVariable("ARCHIVE_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showMembersTable($a_result_set)
	{
		$actions = array("deleteMembersObject"	=> $this->lng->txt("crs_delete_member"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

		#$tpl->setCurrentBlock("input_text");
		#$tpl->setVariable("PB_TXT_NAME",'member');
		#$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","addUser");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("crs_add_member"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",8);

		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("crs_role"),
								   $this->lng->txt("crs_status"),
								   $this->lng->txt("crs_passed"),
								   $this->lng->txt("crs_options")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "role",
								  "status",
								  "passed",
								  "options"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "update_members" => 1,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("","15%","15%","15%","15%","15%","15%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set,"members");
		$tbl->render();

		$this->tpl->setVariable("MEMBER_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showSubscribersTable($a_result_set)
	{
		$actions = array("addSubscribers"		=> $this->lng->txt("crs_add_subscribers"),
						 "deleteSubscribers"	=> $this->lng->txt("crs_delete_subscribers"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
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

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_header_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
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
		$search->setSearchString($a_search_string);
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
			$this->ctrl->redirect($this,"searchUser");
		}
		return $search->getResultByType($a_search_for);
	}		

	function __initMetaDataGUI()
	{
		include_once "./classes/class.ilMetaDataGUI.php";

		if(!is_object($this->meta_gui))
		{
			$this->meta_gui =& new ilMetaDataGUI();
			$this->meta_gui->setObject($this->object);
		}
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

	
	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();


		// check if object is purchased
		include_once './payment/classes/class.ilPaymentObject.php';

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

		switch($next_class)
		{
			case "ilcourseregistergui":
				$this->ctrl->setReturn($this,"");
				$reg_gui =& new ilCourseRegisterGUI($this->object->getRefId());
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			case "ilcourseobjectivesgui":
				include_once './course/classes/class.ilCourseObjectivesGUI.php';

				$this->ctrl->setReturn($this,"");
				$reg_gui =& new ilCourseObjectivesGUI($this->object->getRefId());
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			case 'ilobjcoursegroupinggui':
				include_once './course/classes/class.ilObjCourseGroupingGUI.php';

				$this->ctrl->setReturn($this,'listGroupings');
				$crs_grp_gui =& new ilObjCourseGroupingGUI($this->object,(int) $_GET['obj_id']);

				$this->ctrl->forwardCommand($crs_grp_gui);
				break;

			case "ilconditionhandlerinterface":
				include_once './classes/class.ilConditionHandlerInterface.php';

				if($_GET['item_id'])
				{
					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else
				{
					$new_gui =& new ilConditionHandlerInterface($this);
					$this->ctrl->forwardCommand($new_gui);
				}
				break;

			default:
				if(!$rbacsystem->checkAccess("read",$this->object->getRefId()))
				{
					$this->ctrl->setReturn($this,"");
					$reg_gui =& new ilCourseRegisterGUI($this->object->getRefId());
					$ret =& $this->ctrl->forwardCommand($reg_gui);
					break;
				}
				elseif($cmd == 'listObjectives')
				{
					include_once './course/classes/class.ilCourseObjectivesGUI.php';

					$this->ctrl->setReturn($this,"");
					$obj_gui =& new ilCourseObjectivesGUI($this->object->getRefId());
					$ret =& $this->ctrl->forwardCommand($obj_gui);
					break;
				}
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

	// STATIC
	function _forwards()
	{
		return array("ilCourseRegisterGUI",'ilConditionHandlerInterface');
	}

	// METHODS FOR COURSE CONTENT INTERFACE
	function initCourseContentInterface()
	{
		global $ilCtrl;

		include_once "./course/classes/class.ilCourseContentInterface.php";
		
		$this->object->ctrl =& $ilCtrl;
		$this->cci_obj =& new ilCourseContentInterface($this,$this->object->getRefId());
	}

	function cciObjectivesObject()
	{
		$this->initCourseContentInterface();
		$this->cci_obj->cci_objectives();

		return true;;
	}
	function cciObjectivesEditObject()
	{
		$this->initCourseContentInterface();
		$this->cci_obj->cci_view();

		return true;
	}
	function cciObjectivesAskResetObject()
	{
		$this->initCourseContentInterface();
		$this->cci_obj->cci_objectives_ask_reset();

		return true;;
	}
	function cciResetObject()
	{
		global $ilUser;

		include_once './course/classes/class.ilCourseObjectiveResult.php';

		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->reset($this->object->getId());

		sendInfo($this->lng->txt('crs_objectives_reseted'));

		$this->initCourseContentInterface();
		$this->cci_obj->cci_objectives();
	}

	function cciEditObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_edit();

		return true;;
	}

	function cciUpdateObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_update();

		return true;;
	}
	function cciMoveObject()
	{
		global $rbacsystem;

		// CHECK ACCESS
		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->initCourseContentInterface();
		$this->cci_obj->cci_move();

		return true;;
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

	function chi_updateObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_update();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}
	
	/**
	* delete condition(s)
	*/
	function chi_deleteObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_delete();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}

	function chi_selectorObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_selector();
	}		

	function chi_assignObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_assign();

		if($_GET['item_id'])
		{
			$this->cciEditObject();
		}
		else
		{
			$this->editObject();
		}
	}
	function chi_addObject()
	{
		$this->initConditionHandlerGUI($_GET['item_id'] ? $_GET['item_id'] : $this->object->getRefId());
		$this->chi_obj->chi_add();

		return true;
	}		
} // END class.ilObjCourseGUI
?>
