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
* @ilCtrl_Calls ilObjCourseGUI: ilCourseRegisterGUI
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
	function cancelMemberObject()
	{

		$this->__unsetSessionVariables();
		$this->membersObject();
		
		return true;
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
			$this->initCourseContentInterface();
			$this->cci_view();
		}
	}

	function editObject()
	{
		global $rbacsystem;

		if(!$rbacsystem->checkAccess("write", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_edit.html",true);

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
		$this->tpl->setVariable("TXT_SYLLABUS",$this->lng->txt("syllabus"));
		$this->tpl->setVariable("TXT_CONTACT",$this->lng->txt("contact"));
		$this->tpl->setVariable("TXT_CONTACT_NAME",$this->lng->txt("contact_name"));
		$this->tpl->setVariable("TXT_CONTACT_RESPONSIBILITY",$this->lng->txt("contact_responsibility"));
		$this->tpl->setVariable("TXT_CONTACT_EMAIL",$this->lng->txt("contact_email"));
		$this->tpl->setVariable("TXT_CONTACT_PHONE",$this->lng->txt("contact_phone"));
		$this->tpl->setVariable("TXT_CONTACT_CONSULTATION",$this->lng->txt("contact_consultation"));

		$this->tpl->setVariable("TXT_ACTIVATION",$this->lng->txt("activation"));
		$this->tpl->setVariable("TXT_ACTIVATION_UNLIMITED",$this->lng->txt("activation_unlimited"));
		$this->tpl->setVariable("TXT_ACTIVATION_START",$this->lng->txt("activation_start"));
		$this->tpl->setVariable("TXT_ACTIVATION_END",$this->lng->txt("activation_end"));
		$this->tpl->setVariable("TXT_ACTIVATION_OFFLINE",$this->lng->txt("offline"));

		$this->tpl->setVariable("TXT_SUBSCRIPTION",$this->lng->txt("subscription"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_UNLIMITED",$this->lng->txt("subscription_unlimited"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_START",$this->lng->txt("subscription_start"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_END",$this->lng->txt("subscription_end"));

		$this->tpl->setVariable("TXT_SUBSCRIPTION_OPTIONS",$this->lng->txt("subscription_type"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_MAX_MEMBERS",$this->lng->txt("subscription_max_members"));
		$this->tpl->setVariable("TXT_SUBSCRIPTION_NOTIFY",$this->lng->txt("subscription_notify"));
		$this->tpl->setVariable("TXT_DEACTIVATED",$this->lng->txt("subscription_options_deactivated"));
		$this->tpl->setVariable("TXT_CONFIRMATION",$this->lng->txt("subscription_options_confirmation"));
		$this->tpl->setVariable("TXT_DIRECT",$this->lng->txt("subscription_options_direct"));
		$this->tpl->setVariable("TXT_PASSWORD",$this->lng->txt("subscription_options_password"));

		$this->tpl->setVariable("TXT_SORTORDER",$this->lng->txt("sortorder"));
		$this->tpl->setVariable("TXT_MANUAL",$this->lng->txt("manual"));
		$this->tpl->setVariable("TXT_TITLE",$this->lng->txt("title"));
		$this->tpl->setVariable("TXT_ACTIVATION",$this->lng->txt("activation"));

		$this->tpl->setVariable("TXT_ARCHIVE",$this->lng->txt("archive"));
		$this->tpl->setVariable("TXT_ARCHIVE_START",$this->lng->txt("archive_start"));
		$this->tpl->setVariable("TXT_ARCHIVE_TYPE",$this->lng->txt("archive_type"));
		$this->tpl->setVariable("TXT_ARCHIVE_END",$this->lng->txt("archive_end"));
		$this->tpl->setVariable("TXT_DISABLED",$this->lng->txt("archive_disabled"));
		$this->tpl->setVariable("TXT_READ",$this->lng->txt("archive_read"));
		$this->tpl->setVariable("TXT_DOWNLOAD",$this->lng->txt("archive_download"));

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

		$this->tpl->setVariable("CHECK_ACTIVATION_OFFLINE",ilUtil::formCheckbox($offline,"crs[activation_offline]",1));

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
		$this->object->setOfflineStatus($_POST["crs"]["activation_offline"]);

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

		if($this->object->validate())
		{
			$this->object->update();
			sendInfo($this->lng->txt("settings_saved"));
		}
		else
		{
			sendInfo($this->object->getMessage());
			#$ilErr->raiseError($this->object->getMessage(),$ilErr->WARNING);
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

	// MEMBER METHODS
	function membersObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}

		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_members.html",true);
		$this->__showButton("members",$this->lng->txt("print_list"),"target=\"_blank\"");

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
					$f_result[$counter][]   = strftime("%c",$member_data["time"]);

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
				$this->lng->txt("grp_mem_change_status")."\" title=\"".$this->lng->txt("crs_mem_change_status").
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
							$f_result[$counter][] = $this->lng->txt("notify");
							break;

						case $this->object->members_obj->STATUS_NO_NOTIFY:
							$f_result[$counter][] = $this->lng->txt("no_notify");
							break;

						case $this->object->members_obj->STATUS_BLOCKED:
							$f_result[$counter][] = $this->lng->txt("blocked");
							break;

						case $this->object->members_obj->STATUS_UNBLOCKED:
							$f_result[$counter][] = $this->lng->txt("unblocked");
							break;
					}
					$link_mail = "<a target=\"_blank\" href=\"mail_new.php?type=new&mail_data[rcp_to]=".
						$tmp_obj->getLogin()."\"".$img_mail."</a>";

					$this->ctrl->setParameter($this,"member_id",$tmp_obj->getId());
					$link_change = "<a href=\"".$this->ctrl->getLinkTarget($this,"editMember")."\" ".
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
			$this->ilias->raiseError($this->lng->txt("no_valid_member_id_given"),$this->ilias->error_obj->MESSAGE);
		}
		
		$member_data = $this->object->members_obj->getUserData((int) $_GET["member_id"]);

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",true);


		$f_result = array();

		// GET USER OBJ
		$tmp_obj = ilObjectFactory::getInstanceByObjId($member_data["usr_id"],false);

		$f_result[0][]	= $tmp_obj->getLogin();
		$f_result[0][]	= $tmp_obj->getFirstname();
		$f_result[0][]	= $tmp_obj->getLastname();

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

		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		// CHECK MEMBER_ID
		if(!isset($_GET["member_id"]) or !$this->object->members_obj->isAssigned((int) $_GET["member_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_valid_member_id_given"),$this->ilias->error_obj->MESSAGE);
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
				$this->ilias->raiseError($this->lng->txt("no_valid_status_given"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->members_obj->update((int) $_GET["member_id"],$role,$status);

		sendInfo($this->lng->txt("member_updated"));
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
			++$added_users;
		}
		if($limit_reached)
		{
			sendInfo($this->lng->txt("crs_members_limit_reached"));
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
			sendInfo($number." ".$this->lng->txt("crs_number_users_added"));
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
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",true);
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
			sendInfo($this->lng->txt("no_member_selected"));
			$this->membersObject();

			return false;
		}
		sendInfo($this->lng->txt("crs_delete_members_sure"));

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",true);
		$this->object->initCourseMemberObject();

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
				$f_result[$counter][]   = $member_data["role"] == $this->object->members_obj->ROLE_ADMIN 
					? $this->lng->txt("admin") 
					: $this->lng->txt("member");

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
			sendInfo($this->lng->txt("no_member_selected"));
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
		unset($_SESSION["crs_delete_member_ids"]);
		sendInfo($this->lng->txt("members_deleted"));
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
			sendInfo($this->lng->txt("no_subscribers_selected"));
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
		unset($_SESSION["crs_delete_subscriber_ids"]);
		sendInfo($this->lng->txt("subscribers_deleted"));
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
			sendInfo($this->lng->txt("max_members_reached"));
			$this->membersObject();

			return false;
		}
		$this->tpl->addBlockFile("ADM_CONTENT","adm_content","tpl.crs_members_search.html",true);
		
		$this->tpl->setVariable("F_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SEARCH_ASSIGN_USR",$this->lng->txt("search_members"));
		$this->tpl->setVariable("SEARCH_SEARCH_TERM",$this->lng->txt("search_search_term"));
		$this->tpl->setVariable("SEARCH_VALUE",$_SESSION["crs_search_str"] ? $_SESSION["crs_search_str"] : "");
		$this->tpl->setVariable("SEARCH_FOR",$this->lng->txt("exc_search_for"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_USER",$this->lng->txt("exc_users"));
		$this->tpl->setVariable("SEARCH_ROW_TXT_GROUP",$this->lng->txt("exc_groups"));
		#$this->tpl->setVariable("SEARCH_ROW_TXT_COURSE",$this->lng->txt("courses"));
		$this->tpl->setVariable("BTN2_VALUE",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN1_VALUE",$this->lng->txt("search"));

		$this->tpl->setVariable("SEARCH_ROW_CHECK_USER",ilUtil::formRadioButton(1,"search_for","usr"));
		$this->tpl->setVariable("SEARCH_ROW_CHECK_GROUP",ilUtil::formRadioButton(0,"search_for","grp"));
        #$this->tpl->setVariable("SEARCH_ROW_CHECK_COURSE",ilUtil::formRadioButton(0,"search_for",$this->SEARCH_COURSE));

		$this->__unsetSessionVariables();
	}
	
	function searchObject()
	{
		global $rbacsystem,$tree;

		$_SESSION["crs_search_str"] = $_POST["search_str"] = $_POST["search_str"] ? $_POST["search_str"] : $_SESSION["crs_search_str"];
		$_SESSION["crs_search_for"] = $_POST["search_for"] = $_POST["search_for"] ? $_POST["search_for"] : $_SESSION["crs_search_for"];
		
		// MINIMUM ACCESS LEVEL = 'administrate'
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		$this->object->initCourseMemberObject();

		if(!isset($_POST["search_for"]) or !isset($_POST["search_str"]))
		{
			sendInfo($this->lng->txt("search_enter_search_string"));
			$this->searchUserObject();
			
			return false;
		}
		if(!count($result = $this->__search(ilUtil::stripSlashes($_POST["search_str"]),$_POST["search_for"])))
		{
			sendInfo($this->lng->txt("no_results_found"));
			$this->searchUserObject();

			return false;
		}

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html",true);
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
					$f_result[$counter][] = $tmp_obj->getTitle();
					$f_result[$counter][] = $tmp_obj->getDescription();
					$f_result[$counter][] = $tmp_obj->getCountMembers();
					
					unset($tmp_obj);
					++$counter;
				}
				$this->__showSearchGroupTable($f_result);

				return true;
		}
	}

	function listUsersObject()
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

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_usr_selection.html",true);
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
		$this->__showSearchUserTable($f_result,"listUsers");

		return true;
	}
		
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTarget("view_content",
				$this->ctrl->getLinkTarget($this, ""), "", get_class($this));
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
	}


	
	// META DATA METHODS
	function editMetaObject()
	{
		$this->__initMetaDataGUI();
		
		$this->meta_gui->edit("ADM_CONTENT","adm_content",$this->__getMetaTarget(),$_REQUEST["meta_section"]);

		return true;
	}
	function editMeta()
	{
		// TOODOO
		$this->__setMetaTarget($this->ctrl->getLinkTarget($this));
		return $this->editMetaObject();
	}
	function saveMetaObject()
	{
		$this->__initMetaDataGUI();

		$this->meta_gui->save($_POST["meta_section"]);

		sendInfo($this->lng->txt("msg_obj_modified"),true);

		ilUtil::redirect(ilUtil::appendUrlParameterString(
							 $this->__getMetaTarget(),
							 "meta_section=".$_POST["meta_section"]));
	}
	function saveMeta()
	{
		// TOODOO
		$this->__setMetaTarget($this->ctrl->getLinkTarget($this,"editMeta"));
		return $this->saveMetaObject();
	}
	function chooseMetaSectionObject()
	{
		$this->__initMetaDataGUI();
		$this->meta_gui->edit("ADM_CONTENT", "adm_content",$this->__getMetaTarget(),$_REQUEST["meta_section"]);
	}
	function chooseMetaSection()
	{
		// TOODOO
		$this->__setMetaTarget($this->ctrl->getLinkTarget($this,"editMeta"));
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
		$this->editMetaObject();
		
		return true;
	}
	function addMeta()
	{
		// TOODOO
		$this->__setMetaTarget($this->ctrl->getLinkTarget($this,"editMeta"));
		$this->addMetaObject();
	}
	function deleteMetaObject()
	{
		$this->__initMetaDataGUI();
		$this->meta_gui->meta_obj->delete($_GET["meta_name"], $_GET["meta_path"], $_REQUEST["meta_index"]);

		sendInfo($this->lng->txt("deleted_item"));
		$this->editMetaObject();

		return true;
	}
	function deleteMeta()
	{
		// TOODOO
		$this->__setMetaTarget($this->ctrl->getLinkTarget($this,"editMeta"));
		$this->deleteMetaObject();
	}
	// END META DATA METHODS




	// PRIVATE
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
				$order = $_GET["update_members"] ? $_GET["sort_by"] : '';
				$direction = $_GET["update_members"] ? $_GET["sort_order"] : '';
				break;

			case "subscribers":
				$offset = $_GET["update_subscribers"] ? $_GET["offset"] : 0;
				$order = $_GET["update_subscribers"] ? $_GET["sort_by"] : '';
				$direction = $_GET["update_subscribers"] ? $_GET["sort_order"] : '';
				break;

			default:
				$offset = $_GET["offset"];
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
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array($this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("role/status")));
		$tbl->setHeaderVars(array("login",
								  "firstname",
								  "lastname",
								  "role"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%","25%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->disable('sort');
		$tbl->render();
		
		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());
	}

	function __showSearchUserTable($a_result_set,$a_cmd = "search")
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();


		// SET FORMACTION
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","members");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","addUser");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("add"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("login"),
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

		$tbl->setColumnWidth(array("3%","32%","32%","32%"));

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
		$tpl->setVariable("BTN_NAME","members");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","listUsers");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("listUsers"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",5);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_edit_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array("",
								   $this->lng->txt("title"),
								   $this->lng->txt("description"),
								   $this->lng->txt("count_members")));
		$tbl->setHeaderVars(array("",
								  "title",
								  "description",
								  "nr_members"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "search",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("3%","32%","32%","32%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->disable('sort');
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
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_delete_members"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array($this->lng->txt("login"),
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

		$tbl->setTitle($this->lng->txt("crs_header_delete_subscribers"),"icon_usr_b.gif",$this->lng->txt("crs_header_members"));
		$tbl->setHeaderNames(array($this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("sub_time")));
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

	function __showMembersTable($a_result_set)
	{
		$actions = array("deleteMembersObject"	=> $this->lng->txt("crs_delete_member"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// SET FORMAACTION
		$tpl->setCurrentBlock("tbl_form_header");

		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","searchUser");
		$tpl->setVariable("PBTN_VALUE",$this->lng->txt("add_member"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();

		$tpl->setVariable("COLUMN_COUNTS",7);

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
								   $this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("role"),
								   $this->lng->txt("status"),
								   $this->lng->txt("options")));
		$tbl->setHeaderVars(array("",
								  "login",
								  "firstname",
								  "lastname",
								  "role",
								  "status",
								  "options"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "update_members" => 1,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array("4%","17%","17%","17%","17%","17%","17%"));


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
								   $this->lng->txt("login"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("sub_time")));
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
		$search->setSearchString(ilUtil::stripSlashes($a_search_string));
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

		$this->meta_gui =& new ilMetaDataGUI();
		$this->meta_gui->setObject($this->object);
	}

	function __getMetaTarget()
	{
		return $this->meta_target ? $this->meta_target : "adm_object.php?cmd=editMeta&ref_id=".$this->object->getRefId();
	}
	function __setMetaTarget($a_target)
	{
		$this->meta_target = $a_target;
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
	}

	function __showButton($a_cmd,$a_text,$a_target = '')
	{
		$this->tpl->addBlockfile("BUTTONS", "buttons", "tpl.buttons.html");
		
		// display button
		$this->tpl->setCurrentBlock("btn_cell");
		$this->tpl->setVariable("BTN_LINK",$this->ctrl->getLinkTarget($this,$a_cmd));
		$this->tpl->setVariable("BTN_TXT",$a_text);
		if($a_target)
		{
			$this->tpl->setVariable("BTN_TARGET",$a_target);
		}

		$this->tpl->parseCurrentBlock();
	}		
	
	function &executeCommand()
	{
		global $rbacsystem;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			case "ilcourseregistergui":
				$this->ctrl->setReturn($this,"");
				$reg_gui =& new ilCourseRegisterGUI();
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			default:
				if(!$rbacsystem->checkAccess("read",$this->object->getRefId()))
				{
					$this->ctrl->setReturn($this,"");
					$reg_gui =& new ilCourseRegisterGUI();
					$ret =& $this->ctrl->forwardCommand($reg_gui);
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
		return array("ilCourseRegisterGUI");
	}

	// METHODS FOR COURSE CONTENT INTERFACE
	function initCourseContentInterface()
	{
		include_once "./course/classes/class.ilCourseContentInterface.php";
			
		aggregate($this,"ilCourseContentInterface");
		$this->cci_init($this,$this->object->getRefId());
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
		$this->cci_edit();

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
		$this->cci_update();

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
		$this->cci_move();

		return true;;
	}


} // END class.ilObjCourseGUI
?>
