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


include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
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
	protected $privacy = null;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param object course object
	 */
	public function __construct($a_container)
	{
		parent::__construct($a_container);	
		
		$this->privacy = ilPrivacySettings::_getInstance();
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 */
	public function executeCommand()
	{
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
			$imp = new ilNonEditableValueGUI($this->lng->txt('crs_important_info'));
			$value =  nl2br(ilUtil::makeClickable($this->container->getImportantInformation(), true));
			$imp->setValue($value);
			$this->form->addItem($imp);
		}
		
		if($this->container->getSyllabus())
		{
			$syl = new ilNonEditableValueGUI($this->lng->txt('crs_syllabus'));
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
			$reg->setAlert($warning);
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
		$tpl->setVariable('NUM_FREE',$free);

		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->container->getId());
		if($this->container->enabledWaitingList() and (!$free or $waiting_list->getCountUsers()))
		{
			if($waiting_list->isOnList($ilUser->getId()))
			{
				$tpl->setVariable('TXT_WAIT',$this->lng->txt('mem_waiting_list_position'));
				$tpl->setVariable('NUM_WAIT',$waiting_list->getPosition($ilUser->getId()));
				
			}
			else
			{
				$tpl->setVariable('TXT_WAIT',$this->lng->txt('mem_waiting_list'));
				$tpl->setVariable('NUM_WAIT',$waiting_list->getCountUsers());
			}
		}
		
		$alert = '';
		if(!$free and !$this->container->enabledWaitingList())
		{
			// Disable registration
			$this->enableRegistration(false);
			$alert = $this->lng->txt('mem_alert_no_places');	
		}
		elseif($this->container->enabledWaitingList() and $waiting_list->isOnList($ilUser->getId()))
		{
			// Disable registration
			$this->enableRegistration(false);
			$alert = $this->lng->txt('mem_already_on_list');
		}
		elseif(!$free and $this->container->enabledWaitingList())
		{
			$alert = $this->lng->txt('crs_set_on_waiting_list');
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
			$reg = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
			$reg->setValue($this->lng->txt('crs_info_reg_deactivated'));
			$reg->setAlert($this->lng->txt('grp_reg_deactivated_alert'));
			$this->form->addItem($reg);
		
			// Disable registration
			$this->enableRegistration(false);
			return true;
		}

		switch($this->container->getSubscriptionType())
		{
			case IL_CRS_SUBSCRIPTION_DIRECT:
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
				$txt = new ilNonEditableValueGUI($this->lng->txt('mem_reg_type'));
				$txt->setValue($this->lng->txt('crs_subscription_options_confirmation'));
			
				$sub = new ilTextAreaInputGUI($this->lng->txt('subject'),'grp_subject');
				$sub->setValue($_POST['grp_subject']);
				$sub->setInfo($this->lng->txt('crs_info_reg_confirmation'));
				$sub->setCols(40);
				$sub->setRows(5);
				if($this->participants->isSubscriber($ilUser->getId()))
				{
					$sub->setAlert($this->lng->txt('crs_reg_user_already_subscribed'));
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
	 * Show user agreement
	 *
	 * @access protected
	 * @return
	 */
	protected function fillAgreement()
	{
		global $ilUser;

		if(!$this->isRegistrationPossible())
		{
			return true;
		}

		
		$this->privacy = ilPrivacySettings::_getInstance();
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');		
		if(!$this->privacy->confirmationRequired() and !ilCourseDefinedFieldDefinition::_hasFields($this->container->getId()))
		{
			return true;
		}
		
		$this->lng->loadLanguageModule('ps');
		
		include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
		$fields_info = ilExportFieldsInfo::_getInstance();
		
		if(!count($fields_info->getExportableFields()))
		{
			return true;
		}
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('usr_agreement'));
		$this->form->addItem($section);
		
		$fields = new ilCustomInputGUI($this->lng->txt('crs_user_agreement'),'');
		$tpl = new ilTemplate('tpl.agreement_form.html',true,true,'Services/Membership');
		$tpl->setVariable('TXT_INFO_AGREEMENT',$this->lng->txt('crs_info_agreement'));
		foreach($fields_info->getExportableFields() as $field)
		{
			$tpl->setCurrentBlock('field_item');
			$tpl->setVariable('FIELD_NAME',$this->lng->txt($field));
			$tpl->parseCurrentBlock();
		}
		$fields->setHtml($tpl->get());
		$this->form->addItem($fields);

		$this->showCourseDefinedFields();

		// Checkbox agreement				
		$agreement = new ilCheckboxInputGUI($this->lng->txt('crs_agree'),'agreement');
		$agreement->setRequired(true);
		$agreement->setOptionTitle($this->lng->txt('crs_info_agree'));
		$agreement->setValue(1);
		$this->form->addItem($agreement);
		
		
		return true;
	}
	
	/**
	 * Show course defined fields
	 *
	 * @access protected
	 */
	protected function showCourseDefinedFields()
	{
		global $ilUser;
		
	 	include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
	 	include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');

		if(!count($cdf_fields = ilCourseDefinedFieldDefinition::_getFields($this->container->getId())))
		{
			return true;
		}
		
		$cdf = new ilNonEditableValueGUI($this->lng->txt('ps_crs_user_fields'));
		$cdf->setValue($this->lng->txt('ps_cdf_info'));
		$cdf->setRequired(true);
		
		foreach($cdf_fields as $field_obj)
		{
			$course_user_data = new ilCourseUserData($ilUser->getId(),$field_obj->getId());
			
			switch($field_obj->getType())
			{
				case IL_CDF_TYPE_SELECT:
					$select = new ilSelectInputGUI($field_obj->getName(),'cdf['.$field_obj->getId().']');
					$select->setValue(ilUtil::stripSlashes($_POST['cdf'][$field_obj->getId()]));
					$select->setOptions($field_obj->prepareSelectBox());
					if($field_obj->isRequired())
					{
						$select->setRequired(true);
					}
					
					$cdf->addSubItem($select);
					
					
					break;				

				case IL_CDF_TYPE_TEXT:
					$text = new ilTextInputGUI($field_obj->getName(),'cdf['.$field_obj->getId().']');
					$text->setValue(ilUtil::stripSlashes($_POST['cdf'][$field_obj->getId()]));
					$text->setSize(32);
					$text->setMaxLength(255);
					if($field_obj->isRequired())
					{
						$text->setRequired(true);
					}
					$cdf->addSubItem($text);
					break;
			}
		}
		$this->form->addItem($cdf);
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
		if(!$this->validateCourseDefinedFields())
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
	 * Check Agreement
	 *
	 * @access protected
	 * 
	 */
	private function validateAgreement()
	{
		global $ilUser;
		
	 	if($_POST['agreement'])
	 	{
	 		return true;
	 	}
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		if(!$this->privacy->confirmationRequired() and !ilCourseDefinedFieldDefinition::_hasFields($this->container->getId()))
		{
			return true;
		}
	 	return false;
	}
	
	/**
	 * Check required course fields
	 *
	 * @access protected
	 * 
	 */
	private function validateCourseDefinedFields()
	{
		global $ilUser;
		
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
		
		$all_required = true;
		foreach(ilCourseDefinedFieldDefinition::_getFields($this->container->getId()) as $field_obj)
		{
			switch($field_obj->getType())
			{
				case IL_CDF_TYPE_SELECT:
					$value = ilUtil::stripSlashes($_POST['cdf'][$field_obj->getId()]);
					break;
				
				case IL_CDF_TYPE_TEXT:
					$value = ilUtil::stripSlashes($_POST['cdf'][$field_obj->getId()]);	
					break;
			}
			$course_user_data = new ilCourseUserData($ilUser->getId(),$field_obj->getId());
			$course_user_data->setValue($value);
			$course_user_data->update();
			
			if($field_obj->isRequired() and (!strlen($value) or $value == -1))
			{
				$all_required = false;
			}
		}
		return $all_required;
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
		global $ilUser,$tree;

		// TODO: language vars

		// set aggreement accepted
		$this->setAccepted(true);		

		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$free = max(0,$this->container->getSubscriptionMaxMembers() - $this->participants->getCountMembers());
		$waiting_list = new ilCourseWaitingList($this->container->getId());
		if($this->container->isSubscriptionMembershipLimited() and $this->container->enabledWaitingList() and (!$free or $waiting_list->getCountUsers()))
		{
			$waiting_list->addToList($ilUser->getId());
			$info = sprintf($this->lng->txt('crs_added_to_list'),$waiting_list->getPosition($ilUser->getId()));
			ilUtil::sendInfo($info,true);
			ilUtil::redirect("repository.php?ref_id=".$tree->getParentId($this->container->getRefId()));
		}


		switch($this->container->getSubscriptionType())
		{
			case IL_CRS_SUBSCRIPTION_CONFIRMATION:
				$this->participants->addSubscriber($ilUser->getId());
				$this->participants->updateSubscriptionTime($ilUser->getId(),time());
				$this->participants->updateSubject($ilUser->getId(),ilUtil::stripSlashes($_POST['grp_subject']));
				
				ilUtil::sendInfo($this->lng->txt("application_completed"),true);
				ilUtil::redirect("repository.php?ref_id=".$tree->getParentId($this->container->getRefId()));
				break;
			
			default:
				$this->participants->add($ilUser->getId(),IL_CRS_MEMBER);
				ilUtil::sendInfo($this->lng->txt("crs_subscription_successful"),true);
				$this->ctrl->returnToParent($this);
				break;
		}
	}
	
	/**
	 * Set Agreement accepted
	 *
	 * @access private
	 * @param bool 
	 */
	private function setAccepted($a_status)
	{
		global $ilUser;

		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		if(!$this->privacy->confirmationRequired() and !ilCourseDefinedFieldDefinition::_hasFields($this->container->getId()))
		{
			return true;
		}

		include_once('Modules/Course/classes/class.ilCourseAgreement.php');
		$this->agreement = new ilCourseAgreement($ilUser->getId(),$this->container->getId());
 		$this->agreement->setAccepted($a_status);
 		$this->agreement->setAcceptanceTime(time());
 		$this->agreement->save();
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
}
?>