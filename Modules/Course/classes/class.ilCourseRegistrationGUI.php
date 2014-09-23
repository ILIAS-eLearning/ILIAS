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


include_once('./Services/Membership/classes/class.ilRegistrationGUI.php');

/**
* GUI class for course registrations
* 
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesCourse
* 
* @ilCtrl_Calls ilCourseRegistrationGUI: 
*/
class ilCourseRegistrationGUI extends ilRegistrationGUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object course object
	 */
	public function __construct($a_container)
	{
		parent::__construct($a_container);	
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 */
	public function executeCommand()
	{
		global $ilTabs,$ilUser;
		
		if($this->getWaitingList()->isOnList($ilUser->getId()))
		{
			$ilTabs->activateTab('leave');
		}
		
		if(!$GLOBALS['ilAccess']->checkAccess('join','',$this->getRefId()))
		{
			$this->ctrl->returnToParent($this);
			return FALSE;
		}
		
		$next_class = $this->ctrl->getNextClass($this);
		switch($next_class)
		{
			default:
				$cmd = $this->ctrl->getCmd("show");
				$this->$cmd();
				break;
		}
		return true;
	}
	
	/**
	 * get form title
	 *
	 * @access protected
	 * @return string title
	 */
	protected function getFormTitle()
	{
		global $ilUser;
		
		if($this->getWaitingList()->isOnList($ilUser->getId()))
		{
			return $this->lng->txt('member_status');
		}
		return $this->lng->txt('crs_registration');
	}
	
	/**
	 * fill informations
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function fillInformations()
	{
		if($this->container->getImportantInformation())
		{
			$imp = new ilNonEditableValueGUI($this->lng->txt('crs_important_info'), "", true);
			$value =  nl2br(ilUtil::makeClickable($this->container->getImportantInformation(), true));
			$imp->setValue($value);
			$this->form->addItem($imp);
		}
		
		if($this->container->getSyllabus())
		{
			$syl = new ilNonEditableValueGUI($this->lng->txt('crs_syllabus'), "", true);
			$value = nl2br(ilUtil::makeClickable ($this->container->getSyllabus(), true));
			$syl->setValue($value);
			$this->form->addItem($syl);
		}
	}
	
	/**
	 * show informations about the registration period
	 *
	 * @access protected
	 */
	protected function fillRegistrationPeriod()
	{
		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		$now = new ilDateTime(time(),IL_CAL_UNIX,'UTC');

		if($this->container->getSubscriptionUnlimitedStatus())
		{
			$reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_period'));
			$reg->setValue($this->lng->txt('mem_unlimited'));
			$this->form->addItem($reg);
			return true;
		}
		elseif($this->container->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_DEACTIVATED)
		{
			return true;			
		}
		
		$start = new ilDateTime($this->container->getSubscriptionStart(),IL_CAL_UNIX,'UTC');
		$end = new ilDateTime($this->container->getSubscriptionEnd(),IL_CAL_UNIX,'UTC');
		
		if(ilDateTime::_before($now,$start))
		{
			$tpl = new ilTemplate('tpl.registration_period_form.html',true,true,'Services/Membership');
			$tpl->setVariable('TXT_FIRST',$this->lng->txt('mem_start'));
			$tpl->setVariable('FIRST',ilDatePresentation::formatDate($start));
			
			$tpl->setVariable('TXT_END',$this->lng->txt('mem_end'));
			$tpl->setVariable('END',ilDatePresentation::formatDate($end));
			
			$warning = $this->lng->txt('mem_reg_not_started');
		}
		elseif(ilDateTime::_after($now,$end))
		{
			$tpl = new ilTemplate('tpl.registration_period_form.html',true,true,'Services/Membership');
			$tpl->setVariable('TXT_FIRST',$this->lng->txt('mem_start'));
			$tpl->setVariable('FIRST',ilDatePresentation::formatDate($start));
			
			$tpl->setVariable('TXT_END',$this->lng->txt('mem_end'));
			$tpl->setVariable('END',ilDatePresentation::formatDate($end));
			
			
			$warning = $this->lng->txt('mem_reg_expired');
		}
		else
		{
			$tpl = new ilTemplate('tpl.registration_period_form.html',true,true,'Services/Membership');
			$tpl->setVariable('TXT_FIRST',$this->lng->txt('mem_end'));
			$tpl->setVariable('FIRST',ilDatePresentation::formatDate($end));
		}
		
		$reg = new ilCustomInputGUI($this->lng->txt('mem_reg_period'));
		$reg->setHtml($tpl->get());
		if(strlen($warning))
		{
			// Disable registration
			$this->enableRegistration(false);
			ilUtil::sendFailure($warning);
			#$reg->setAlert($warning);
		}
		$this->form->addItem($reg);
		return true;
	}
	
	
	/**
	 * fill max members
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function fillMaxMembers()
	{
		global $ilUser;
		
		if(!$this->container->isSubscriptionMembershipLimited())
		{
			return true;
		}
		$tpl = new ilTemplate('tpl.max_members_form.html',true,true,'Services/Membership');
		$tpl->setVariable('TXT_MAX',$this->lng->txt('mem_max_users'));
		$tpl->setVariable('NUM_MAX',$this->container->getSubscriptionMaxMembers());
		
		$tpl->setVariable('TXT_FREE',$this->lng->txt('mem_free_places').":");
		$free = max(0,$this->container->getSubscriptionMaxMembers() - $this->participants->getCountMembers());

		if($free)
			$tpl->setVariable('NUM_FREE',$free);
		else
			$tpl->setVariable('WARN_FREE',$free);
		

		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->container->getId());
		if(
			$this->container->isSubscriptionMembershipLimited() and
			$this->container->enabledWaitingList() and 
			(!$free or $waiting_list->getCountUsers()))
		{
			if($waiting_list->isOnList($ilUser->getId()))
			{
				$tpl->setVariable('TXT_WAIT',$this->lng->txt('mem_waiting_list_position'));
				$tpl->setVariable('NUM_WAIT',$waiting_list->getPosition($ilUser->getId()));
				
			}
			else
			{
				$tpl->setVariable('TXT_WAIT',$this->lng->txt('mem_waiting_list'));
				if($free and $waiting_list->getCountUsers())
					$tpl->setVariable('WARN_WAIT',$waiting_list->getCountUsers());
				else
					$tpl->setVariable('NUM_WAIT',$waiting_list->getCountUsers());
			}
		}
		
		$alert = '';
		if(
				!$free and 
				!$this->container->enabledWaitingList())
		{
			// Disable registration
			$this->enableRegistration(false);
			ilUtil::sendFailure($this->lng->txt('mem_alert_no_places'));
			#$alert = $this->lng->txt('mem_alert_no_places');	
		}
		elseif(
				$this->container->enabledWaitingList() and 
				$this->container->isSubscriptionMembershipLimited() and
				$waiting_list->isOnList($ilUser->getId())
		)
		{
			// Disable registration
			$this->enableRegistration(false);
		}
		elseif(
				!$free and 
				$this->container->enabledWaitingList() and
				$this->container->isSubscriptionMembershipLimited())
				
		{
			ilUtil::sendFailure($this->lng->txt('crs_warn_no_max_set_on_waiting_list'));
			#$alert = $this->lng->txt('crs_warn_no_max_set_on_waiting_list');
		}
		elseif(
				$free and 
				$this->container->enabledWaitingList() and 
				$this->container->isSubscriptionMembershipLimited() and
				$this->getWaitingList()->getCountUsers())
		{
			ilUtil::sendFailure($this->lng->txt('crs_warn_wl_set_on_waiting_list'));
			#$alert = $this->lng->txt('crs_warn_wl_set_on_waiting_list');
		}
				
		$max = new ilCustomInputGUI($this->lng->txt('mem_participants'));
		$max->setHtml($tpl->get());
		if(strlen($alert))
		{
			$max->setAlert($alert);
		}
		$this->form->addItem($max);
		return true;
	}
	
	/**
	 * fill registration type
	 *
	 * @access protected
	 * @return
	 */
	protected function fillRegistrationType()
	{
		global $ilUser;
		
		if($this->container->getSubscriptionLimitationType() == IL_CRS_SUBSCRIPTION_DEACTIVATED)
		{
			$reg = new ilCustomInputGUI($this->lng->txt('mem_reg_type'));
			#$reg->setHtml($this->lng->txt('crs_info_reg_deactivated'));
			$reg->setAlert($this->lng->txt('crs_info_reg_deactivated'));
			#ilUtil::sendFailure($this->lng->txt('crs_info_reg_deactivated'));
			#$reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
			#$reg->setValue($this->lng->txt('crs_info_reg_deactivated'));
			#$reg->setAlert($this->lng->txt('grp_reg_deactivated_alert'));
			$this->form->addItem($reg);
		
			// Disable registration
			$this->enableRegistration(false);
			return true;
		}

		switch($this->container->getSubscriptionType())
		{
			case IL_CRS_SUBSCRIPTION_DIRECT:

				// no "request" info if waiting list is active
				if($this->isWaitingListActive())
					return true;

				$txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$txt->setValue($this->lng->txt('crs_info_reg_direct'));
				
				$this->form->addItem($txt);
				break;

			case IL_CRS_SUBSCRIPTION_PASSWORD:
				$txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$txt->setValue($this->lng->txt('crs_subscription_options_password'));
					

				$pass = new ilTextInputGUI($this->lng->txt('passwd'),'grp_passw');
				$pass->setInputType('password');
				$pass->setSize(12);
				$pass->setMaxLength(32);
				#$pass->setRequired(true);
				$pass->setInfo($this->lng->txt('crs_info_reg_password'));
				
				$txt->addSubItem($pass);
				$this->form->addItem($txt);
				break;
				
			case IL_CRS_SUBSCRIPTION_CONFIRMATION:

				// no "request" info if waiting list is active
				if($this->isWaitingListActive())
					return true;

				$txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$txt->setValue($this->lng->txt('crs_subscription_options_confirmation'));
			
				$sub = new ilTextAreaInputGUI($this->lng->txt('crs_reg_subject'),'subject');
				$sub->setValue($_POST['subject']);
				$sub->setInfo($this->lng->txt('crs_info_reg_confirmation'));
				$sub->setCols(40);
				$sub->setRows(5);
				if($this->participants->isSubscriber($ilUser->getId()))
				{
					$sub_data = $this->participants->getSubscriberData($ilUser->getId());
					$sub->setValue($sub_data['subject']);
					$sub->setInfo('');
					ilUtil::sendFailure($this->lng->txt('crs_reg_user_already_subscribed'));
					$this->enableRegistration(false);	
				}
				$txt->addSubItem($sub);
				$this->form->addItem($txt);
				break;
				

			default:
				return true;
		}
		
		return true;
	}
	
	/**
	 * Add group specific command buttons
	 * @return 
	 */
	protected function addCommandButtons()
	{
		global $ilUser;
		
		parent::addCommandButtons();
		

		switch($this->container->getSubscriptionType())
		{
			case IL_CRS_SUBSCRIPTION_CONFIRMATION:
				if($this->participants->isSubscriber($ilUser->getId()))
				{
					$this->form->clearCommandButtons();
					$this->form->addCommandButton('updateSubscriptionRequest', $this->lng->txt('crs_update_subscr_request'));				
					$this->form->addCommandButton('cancelSubscriptionRequest', $this->lng->txt('crs_cancel_subscr_request'));				
				}
				elseif($this->isRegistrationPossible())
				{
					$this->form->clearCommandButtons();
					$this->form->addCommandButton('join', $this->lng->txt('crs_join_request'));
					$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
				}
				break;
		}
		if(!$this->isRegistrationPossible())
		{
			return false;
		}

		return true;		
	}

	/**
	 * Validate subscription request
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function validate()
	{
		global $ilUser;
		
		if($ilUser->getId() == ANONYMOUS_USER_ID)
		{
			$this->join_error = $this->lng->txt('permission_denied');
			return false;
		}
		
		// Set aggrement to not accepted
		$this->setAccepted(false);
		
		if(!$this->isRegistrationPossible())
		{
			$this->join_error = $this->lng->txt('mem_error_preconditions');
			return false;
		}
		if($this->container->getSubscriptionType() == IL_CRS_SUBSCRIPTION_PASSWORD)
		{
			if(!strlen($pass = ilUtil::stripSlashes($_POST['grp_passw'])))
			{
				$this->join_error = $this->lng->txt('crs_password_required');
				return false;
			}
			if(strcmp($pass,$this->container->getSubscriptionPassword()) !== 0)
			{
				$this->join_error = $this->lng->txt('crs_password_not_valid');
				return false;
			}
		}
		if(!$this->validateCustomFields())
		{
			$this->join_error = $this->lng->txt('fill_out_all_required_fields');
			return false;
		}
		if(!$this->validateAgreement())
		{
			$this->join_error = $this->lng->txt('crs_agreement_required');
			return false;
		}
		
		return true;
	}
	
	/**
	 * add user 
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function add()
	{
		global $ilUser,$tree, $ilCtrl;

		// TODO: language vars

		// set aggreement accepted
		$this->setAccepted(true);

		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$free = max(0,$this->container->getSubscriptionMaxMembers() - $this->participants->getCountMembers());
		$waiting_list = new ilCourseWaitingList($this->container->getId());
		if($this->container->isSubscriptionMembershipLimited() and $this->container->enabledWaitingList() and (!$free or $waiting_list->getCountUsers()))
		{
			$waiting_list->addToList($ilUser->getId());
			$info = sprintf($this->lng->txt('crs_added_to_list'),
				$waiting_list->getPosition($ilUser->getId()));
			ilUtil::sendSuccess($info,true);
			
			$this->participants->sendNotification($this->participants->NOTIFY_SUBSCRIPTION_REQUEST,$ilUser->getId());
			$this->participants->sendNotification($this->participants->NOTIFY_WAITING_LIST,$ilUser->getId());
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
				$tree->getParentId($this->container->getRefId()));
			$ilCtrl->redirectByClass("ilrepositorygui", "");
		}

		switch($this->container->getSubscriptionType())
		{
			case IL_CRS_SUBSCRIPTION_CONFIRMATION:
				$this->participants->addSubscriber($ilUser->getId());
				$this->participants->updateSubscriptionTime($ilUser->getId(),time());
				$this->participants->updateSubject($ilUser->getId(),ilUtil::stripSlashes($_POST['subject']));
				$this->participants->sendNotification($this->participants->NOTIFY_SUBSCRIPTION_REQUEST,$ilUser->getId());
				
				ilUtil::sendSuccess($this->lng->txt("application_completed"),true);
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
					$tree->getParentId($this->container->getRefId()));
				$ilCtrl->redirectByClass("ilrepositorygui", "");
				break;
			
			default:
				
				if($this->container->isSubscriptionMembershipLimited() && $this->container->getSubscriptionMaxMembers())
				{
					$success = $GLOBALS['rbacadmin']->assignUserLimited(
						ilParticipants::getDefaultMemberRole($this->container->getRefId()),
						$ilUser->getId(),
						$this->container->getSubscriptionMaxMembers(),
						array(ilParticipants::getDefaultMemberRole($this->container->getRefId()))
					);
					if(!$success)
					{
						ilUtil::sendFailure($this->lng->txt('crs_subscription_failed_limit'));
						$this->show();
						return FALSE;
					}
				}
				
				$this->participants->add($ilUser->getId(),IL_CRS_MEMBER);
				$this->participants->sendNotification($this->participants->NOTIFY_ADMINS,$ilUser->getId());
				$this->participants->sendNotification($this->participants->NOTIFY_REGISTERED,$ilUser->getId());

				include_once './Modules/Forum/classes/class.ilForumNotification.php';
				ilForumNotification::checkForumsExistsInsert($this->container->getRefId(), $ilUser->getId());
								
				if($this->container->getType() == "crs")
				{
					$this->container->checkLPStatusSync($ilUser->getId());
				}

				if(!$_SESSION["pending_goto"])
				{
					ilUtil::sendSuccess($this->lng->txt("crs_subscription_successful"),true);
					$this->ctrl->returnToParent($this);
				}
				else
				{
					$tgt = $_SESSION["pending_goto"];
					unset($_SESSION["pending_goto"]);
					ilUtil::redirect($tgt);
				}
				break;
		}
	}
	
	
	
	
	/**
	 * Init course participants
	 *
	 * @access protected
	 */
	protected function initParticipants()
	{
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		$this->participants = ilCourseParticipants::_getInstanceByObjId($this->obj_id);
	}
	

    /**
     * @see ilRegistrationGUI::initWaitingList()
     * @access protected
     */
    protected function initWaitingList()
    {
		include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
		$this->waiting_list = new ilCourseWaitingList($this->container->getId());
    }
	
    /**
     * @see ilRegistrationGUI::isWaitingListActive()
     */
    protected function isWaitingListActive()
    {
		global $ilUser;
		static $active = null;
		
		if($active !== null)
		{
			return $active;
		}
		if(!$this->container->enabledWaitingList() or !$this->container->isSubscriptionMembershipLimited())
		{
			return $active = false;
		}
		if(!$this->container->getSubscriptionMaxMembers())
		{
			return $active = false;
		}

		$free = max(0,$this->container->getSubscriptionMaxMembers() - $this->participants->getCountMembers());
		return $active = (!$free or $this->getWaitingList()->getCountUsers());
    }
}
?>