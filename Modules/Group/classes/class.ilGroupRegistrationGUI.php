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
* GUI class for group registrations
*
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ModulesGroup 
*/
class ilGroupRegistrationGUI extends ilRegistrationGUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object container object
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
		global $ilUser,$ilTabs;
		
		$next_class = $this->ctrl->getNextClass($this);
		
		if($this->getWaitingList()->isOnList($ilUser->getId()))
		{
			$ilTabs->activateTab('leave');
		}

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
		return $this->lng->txt('grp_registration');
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
		if($this->container->getInformation())
		{
			$imp = new ilNonEditableValueGUI($this->lng->txt('crs_important_info'));
			$value =  nl2br(ilUtil::makeClickable($this->container->getInformation(), true));
			$imp->setValue($value);
			$this->form->addItem($imp);
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

		if($this->container->isRegistrationUnlimited())
		{
			$reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_period'));
			$reg->setValue($this->lng->txt('mem_unlimited'));
			$this->form->addItem($reg);
			return true;
		}
		
		$start = $this->container->getRegistrationStart();
		$end = $this->container->getRegistrationEnd();
		
		
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
			$reg->setAlert($warning);
		}
		$this->form->addItem($reg);
		return true;
	}
	
	/**
	 * fill max member informations
	 *
	 * @access protected
	 * @return
	 */
	protected function fillMaxMembers()
	{
		global $ilUser;
		
		if(!$this->container->isMembershipLimited())
		{
			return true;
		}
		$tpl = new ilTemplate('tpl.max_members_form.html',true,true,'Services/Membership');
		$tpl->setVariable('TXT_MAX',$this->lng->txt('mem_max_users'));
		$tpl->setVariable('NUM_MAX',$this->container->getMaxMembers());
		
		$tpl->setVariable('TXT_FREE',$this->lng->txt('mem_free_places').":");
		$free = max(0,$this->container->getMaxMembers() - $this->participants->getCountMembers());
		
		if($free)
			$tpl->setVariable('NUM_FREE',$free);
		else
			$tpl->setVariable('WARN_FREE',$free);
			

		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->container->getId());
		
		if($this->container->isWaitingListEnabled() and (!$free or $waiting_list->getCountUsers()))
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
		if(!$free and !$this->container->isWaitingListEnabled())
		{
			// Disable registration
			$this->enableRegistration(false);
			$alert = $this->lng->txt('mem_alert_no_places');	
		}
		elseif($this->container->isWaitingListEnabled() and $waiting_list->isOnList($ilUser->getId()))
		{
			// Disable registration
			$this->enableRegistration(false);
		}
		elseif(!$free and $this->container->isWaitingListEnabled())
		{
			$alert = $this->lng->txt('grp_warn_no_max_set_on_waiting_list');
		}
		elseif($free and $this->container->isWaitingListEnabled() and $this->getWaitingList()->getCountUsers())
		{
			$alert = $this->lng->txt('grp_warn_wl_set_on_waiting_list');
		}
		
		$max = new ilCustomInputGUI($this->lng->txt('mem_participants'));
		$max->setHtml($tpl->get());
		if(strlen($alert))
		{
			$max->setAlert($alert);
		}
		$this->form->addItem($max);
	}
	
	/**
	 * fill registration procedure
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function fillRegistrationType()
	{
		global $ilUser;
		
		if($this->getWaitingList()->isOnList($ilUser->getId()))
		{
			return true;
		}
		
		switch($this->container->getRegistrationType())
		{
			case GRP_REGISTRATION_DEACTIVATED:
				$reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$reg->setValue($this->lng->txt('grp_reg_disabled'));
				#$reg->setAlert($this->lng->txt('grp_reg_deactivated_alert'));
				$this->form->addItem($reg);
		
				// Disable registration
				$this->enableRegistration(false);
				
				break;
				
			case GRP_REGISTRATION_PASSWORD:
				$txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$txt->setValue($this->lng->txt('grp_pass_request'));
					

				$pass = new ilTextInputGUI($this->lng->txt('passwd'),'grp_passw');
				$pass->setInputType('password');
				$pass->setSize(12);
				$pass->setMaxLength(32);
				#$pass->setRequired(true);
				$pass->setInfo($this->lng->txt('group_password_registration_msg'));
				
				$txt->addSubItem($pass);
				$this->form->addItem($txt);
				break;
				
			case GRP_REGISTRATION_REQUEST:
				
				// no "request" info if waiting list is active
				if($this->isWaitingListActive())
					return true;
					
				$txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$txt->setValue($this->lng->txt('grp_reg_request'));
			
				$sub = new ilTextAreaInputGUI($this->lng->txt('subject'),'grp_subject');
				$sub->setValue($_POST['grp_subject']);
				$sub->setInfo($this->lng->txt('group_req_registration_msg'));
				$sub->setCols(40);
				$sub->setRows(5);
				if($this->participants->isSubscriber($ilUser->getId()))
				{
					$sub->setAlert($this->lng->txt('grp_already_applied'));
					$this->enableRegistration(false);					
				}
				$txt->addSubItem($sub);
				$this->form->addItem($txt);
				break;
				
			case GRP_REGISTRATION_DIRECT:

				// no "direct registration" info if waiting list is active
				if($this->isWaitingListActive())
					return true;

				$txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$txt->setValue($this->lng->txt('group_req_direct'));
				
				$this->form->addItem($txt);
				break;

			default:
				return true;
		}
		
		return true;
	}
	
	/**
	 * validate join request
	 *
	 * @access protected
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
		
		if(!$this->isRegistrationPossible())
		{
			$this->join_error = $this->lng->txt('mem_error_preconditions');
			return false;
		}
		if($this->container->getRegistrationType() == GRP_REGISTRATION_PASSWORD)
		{
			if(!strlen($pass = ilUtil::stripSlashes($_POST['grp_passw'])))
			{
				$this->join_error = $this->lng->txt('err_wrong_password');
				return false;
			}
			if(strcmp($pass,$this->container->getPassword()) !== 0)
			{
				$this->join_error = $this->lng->txt('err_wrong_password');
				return false;
			}
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
		global $ilUser,$tree, $rbacreview, $lng;
		
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$free = max(0,$this->container->getMaxMembers() - $this->participants->getCountMembers());
		$waiting_list = new ilGroupWaitingList($this->container->getId());
		if($this->container->isMembershipLimited() and $this->container->isWaitingListEnabled() and (!$free or $waiting_list->getCountUsers()))
		{
			$waiting_list->addToList($ilUser->getId());
			$info = sprintf($this->lng->txt('grp_added_to_list'),
				$this->container->getTitle(),
				$waiting_list->getPosition($ilUser->getId()));
			ilUtil::sendInfo($info,true);
			ilUtil::redirect("repository.php?ref_id=".$tree->getParentId($this->container->getRefId()));
		}
				

		switch($this->container->getRegistrationType())
		{
			case GRP_REGISTRATION_REQUEST:
				$this->participants->addSubscriber($ilUser->getId());
				$this->participants->updateSubscriptionTime($ilUser->getId(),time());
				$this->participants->updateSubject($ilUser->getId(),ilUtil::stripSlashes($_POST['grp_subject']));
				
				// send an e-mail to the group administrators, so that they know,
				// that a new registration request has been issued
				$roles = $rbacreview->getAssignableChildRoles($this->container->getRefId());
				$grp_admin_role = null;
				foreach ($roles as $role)
				{
					if (strpos($role['title'], 'il_grp_admin') !== false) {
						$grp_admin_role = $role;
						break;
					}
				}
				if ($grp_admin_role != null)
				{
					global $ilIliasIniFile;

					$mail = new ilMail($_SESSION["AccountId"]);
					// XXX - The message should be sent in the language of the receiver,
					// instead of in the language of the current user
					$mail->sendMail($rbacreview->getRoleMailboxAddress($grp_admin_role['rol_id']),"","",
						sprintf($lng->txt('grp_membership_request_subject'), $ilUser->getFirstname(), $ilUser->getLastname(), $this->container->getTitle()),
						sprintf(str_replace('\n',"\n",$lng->txt('grp_membership_request_body')), $ilUser->getFirstname(), $ilUser->getLastname(), $ilUser->getLogin(), $ilUser->getEmail(), 
								$this->container->getTitle(), $ilIliasIniFile->readVariable('server','http_path').'/goto.php?client_id='.CLIENT_ID.'&target=grp_'.$this->container->getRefId(), $_POST["grp_subject"]),
						array(),array('system'));	
				}

				ilUtil::sendInfo($this->lng->txt("application_completed"),true);
				ilUtil::redirect("repository.php?ref_id=".$tree->getParentId($this->container->getRefId()));
				break;
			
			default:
				$this->participants->add($ilUser->getId(),IL_GRP_MEMBER);
				ilUtil::sendInfo($this->lng->txt("grp_registration_completed"),true);
				$this->ctrl->returnToParent($this);
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
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		$this->participants = ilGroupParticipants::_getInstanceByObjId($this->obj_id);
	}
	
    /**
     * @see ilRegistrationGUI::initWaitingList()
     * @access protected
     */
    protected function initWaitingList()
    {
		include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
		$this->waiting_list = new ilGroupWaitingList($this->container->getId());
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
		if(!$this->container->isWaitingListEnabled())
		{
			return $active = false;
		}

		$free = max(0,$this->container->getMaxMembers() - $this->participants->getCountMembers());
		return $active = (!$free or $this->getWaitingList()->getCountUsers());
    }
}
?>