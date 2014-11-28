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

/**
* Base class for Course and Group registration
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesRegistration
*/

abstract class ilRegistrationGUI
{
	protected $privacy = null;

	protected $container = null;
	protected $ref_id;
	protected $obj_id;
	
	protected $participants;
	protected $waiting_list = null;
	protected $form;
	
	protected $registration_possible = true;
	protected $join_error = '';
	

	protected $tpl;
	protected $lng;
	protected $ctrl; 

	/**
	 * Constructor
	 *
	 * @access public
	 * @param object Course or Group object
	 * @return
	 */
	public function __construct($a_container)
	{
		global $lng,$ilCtrl,$tpl;
		
		$this->lng = $lng;
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule('grp');
		$this->lng->loadLanguageModule('ps');
		
		$this->ctrl = $ilCtrl;
		$this->tpl = $tpl;
		
		$this->container = $a_container;
		$this->ref_id = $this->container->getRefId();
		$this->obj_id = ilObject::_lookupObjId($this->ref_id);
		$this->type = ilObject::_lookupType($this->obj_id);
		
		// Init participants
		$this->initParticipants();
		
		// Init waiting list
		$this->initWaitingList();
		
		$this->privacy = ilPrivacySettings::_getInstance();
	}
	
	/**
	 * Parent object
	 * @return ilObject
	 */
	public function getContainer()
	{
		return $this->container;
	}
	
	/**
	 * Get ref
	 * @return type
	 */
	public function getRefId()
	{
		return $this->ref_id;
	}
	
	/**
	 * check if registration is possible
	 *
	 * @access protected
	 * @return bool
	 */
	protected function isRegistrationPossible()
	{
		return (bool) $this->registration_possible;
	}
	
	/**
	 * set registration disabled
	 *
	 * @access protected
	 * @param bool 
	 * @return
	 */
	protected function enableRegistration($a_status)
	{
		$this->registration_possible = $a_status;
	}
	
	
	/**
	 * Init participants object (course or group participants)
	 *
	 * @access protected
	 * @return
	 */
	abstract protected function initParticipants();
	
	/**
	 * Init waiting list (course or group waiting list)
	 * 
	 * @access protected
	 * @abstract
	 * @return 
	 */
	abstract protected function initWaitingList();
	
	/**
	 * Check if the waiting list is active
	 * Maximum of members exceeded or
	 * any user on the waiting list
	 * @return 
	 */
	abstract protected function isWaitingListActive();
	
	/**
	 * Get waiting list object
	 * @return object waiting list
	 * @access protected
	 */
	protected function getWaitingList()
	{
		return $this->waiting_list;
	}
	
	protected function leaveWaitingList()
	{
		global $ilUser,$tree,$ilCtrl;
		
		$this->getWaitingList()->removeFromList($ilUser->getId());
		$parent = $tree->getParentId($this->container->getRefId());
		
		$message = sprintf($this->lng->txt($this->container->getType().'_removed_from_waiting_list'),
			$this->container->getTitle());
		ilUtil::sendSuccess($message,true);

		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $parent);
		$ilCtrl->redirectByClass("ilrepositorygui", "");
	}
	
	/**
	 * Get title for property form
	 *
	 * @access protected
	 * @return string title
	 */
	abstract protected function getFormTitle();
	
	/**
	 * fill informations
	 *
	 * @access protected
	 * @return
	 */
	abstract protected function fillInformations();
	
	/**
	 * show informations about the registration period
	 *
	 * @access protected
	 */
	abstract protected function fillRegistrationPeriod();
	
	/**
	 * show informations about the maximum number of user.
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	abstract protected function fillMaxMembers();
	
	
	/**
	 * show informations about registration procedure
	 *
	 * @access protected
	 * @return
	 */
	abstract protected function fillRegistrationType();
	
	/**
	 * Show membership limitations
	 *
	 * @access protected
	 * @return
	 */
	protected function fillMembershipLimitation()
	{
		global $ilAccess, $ilCtrl;
		
		include_once('Modules/Course/classes/class.ilObjCourseGrouping.php');
		if(!$items = ilObjCourseGrouping::_getGroupingItems($this->container))
		{
			return true;
		}
		
		$mem = new ilCustomInputGUI($this->lng->txt('groupings'));
		
		$tpl = new ilTemplate('tpl.membership_limitation_form.html',true,true,'Services/Membership');
		$tpl->setVariable('LIMIT_INTRO',$this->lng->txt($this->type.'_grp_info_reg'));
		
		foreach($items as $ref_id)
		{
			$obj_id = ilObject::_lookupObjId($ref_id);
			$type = ilObject::_lookupType($obj_id);
			$title = ilObject::_lookupTitle($obj_id);
			
			if($ilAccess->checkAccess('visible','',$ref_id,$type))
			{
				include_once('./Services/Link/classes/class.ilLink.php');
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $ref_id);
				$tpl->setVariable('LINK_ITEM',
					$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				$tpl->setVariable('ITEM_LINKED_TITLE',$title);
			}
			else
			{
				$tpl->setVariable('ITEM_TITLE');
			}
			$tpl->setCurrentBlock('items');
			$tpl->setVariable('TYPE_ICON',ilObject::_getIcon($obj_id,tiny,$type));
			$tpl->setVariable('ALT_ICON',$this->lng->txt('obj_'.$type));
			$tpl->parseCurrentBlock();
		}
		
		$mem->setHtml($tpl->get());
		
		
		if(!ilObjCourseGrouping::_checkGroupingDependencies($this->container))
		{
			$mem->setAlert($this->container->getMessage());
			$this->enableRegistration(false);
		}
		$this->form->addItem($mem);
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

		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');		
		if(!$this->privacy->confirmationRequired($this->type) and !ilCourseDefinedFieldDefinition::_hasFields($this->container->getId()))
		{
			return true;
		}
		
		$this->lng->loadLanguageModule('ps');
		
		include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
		$fields_info = ilExportFieldsInfo::_getInstanceByType(ilObject::_lookupType($this->container->getId()));
		
		if(!count($fields_info->getExportableFields()))
		{
			return true;
		}
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('usr_agreement'));
		$this->form->addItem($section);
		
		include_once './Services/Membership/classes/class.ilMemberAgreementGUI.php';
		ilMemberAgreementGUI::addExportFieldInfo($this->form, $this->obj_id, $this->type);
		

		ilMemberAgreementGUI::addCustomFields($this->form, $this->obj_id, $this->type);

		// Checkbox agreement		
		if($this->privacy->confirmationRequired($this->type))
		{
			ilMemberAgreementGUI::addAgreement($this->form, $this->obj_id, $this->type);
		}
		return true;
	}
	
	/**
	 * Show course defined fields
	 *
	 * @access protected
	 */
	protected function showCustomFields()
	{
		global $ilUser;
		
	 	include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
	 	include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');

		if(!count($cdf_fields = ilCourseDefinedFieldDefinition::_getFields($this->container->getId())))
		{
			return true;
		}
		
		$cdf = new ilNonEditableValueGUI($this->lng->txt('ps_crs_user_fields'));
		$cdf->setValue($this->lng->txt($this->type.'_ps_cdf_info'));
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
	 * Check Agreement
	 *
	 * @access protected
	 * 
	 */
	protected function validateAgreement()
	{
		global $ilUser;
		
	 	if($_POST['agreement'])
	 	{
	 		return true;
	 	}
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		if(!$this->privacy->confirmationRequired($this->type))
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
	protected function validateCustomFields()
	{
		global $ilUser;
		

		$required_fullfilled = true;
		foreach(ilCourseDefinedFieldDefinition::_getFields($this->container->getId()) as $field_obj)
		{
			switch($field_obj->getType())
			{
				case IL_CDF_TYPE_SELECT:
					
					// Split value id from post
					list($field_id,$option_id) = explode('_', $_POST['cdf_'.$field_obj->getId()]);
					
					#$GLOBALS['ilLog']->write(__METHOD__.': '.print_r($field_id,TRUE).' '.print_r($option_id,TRUE).' '.print_r($_POST,TRUE));
					
					$open_answer_indexes = (array) $field_obj->getValueOptions();
					if(in_array($option_id, $open_answer_indexes))
					{
						$value = $_POST['cdf_oa_'.$field_obj->getId().'_'.$option_id];
					}
					else
					{
						$value = $field_obj->getValueById($option_id);
					}
					break;
					
				case IL_CDF_TYPE_TEXT:
					$value = $_POST['cdf_'.$field_obj->getId()];
					break;
			}
			
			$GLOBALS['ilLog']->write(__METHOD__.': new value '. $value);
			
			
			$course_user_data = new ilCourseUserData($ilUser->getId(),$field_obj->getId());
			$course_user_data->setValue($value);
			$course_user_data->update();
			
			if($field_obj->isRequired() and !$value)
			{
				$required_fullfilled = false;
			}
		}

		return $required_fullfilled;
	}
	
	/**
	 * Set Agreement accepted
	 *
	 * @access private
	 * @param bool 
	 */
	protected function setAccepted($a_status)
	{
		global $ilUser;

		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		if(!$this->privacy->confirmationRequired($this->type) and !ilCourseDefinedFieldDefinition::_hasFields($this->container->getId()))
		{
			return true;
		}

		include_once('Services/Membership/classes/class.ilMemberAgreement.php');
		$this->agreement = new ilMemberAgreement($ilUser->getId(),$this->container->getId());
 		$this->agreement->setAccepted($a_status);
 		$this->agreement->setAcceptanceTime(time());
 		$this->agreement->save();
	}
	
	/**
	 * cancel subscription
	 *
	 * @access public
	 */
	public function cancel()
	{
		global $tree, $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
			$tree->getParentId($this->container->getRefId()));
		$ilCtrl->redirectByClass("ilrepositorygui", "");
	}
	
	/**
	 * show registration form
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function show()
	{
		$this->initForm();
		
		if($_SESSION["pending_goto"])
		{			
			ilUtil::sendInfo($this->lng->txt("reg_goto_parent_membership_info"));
		}
		
		$this->tpl->setContent($this->form->getHTML());
	}
	
	/**
	 * join 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function join()
	{
		$this->initForm();

		if(!$this->validate())
		{
			ilUtil::sendFailure($this->join_error);
			$this->show();
			return false;
		}
		
		$this->add();
	}
	
	
	/**
	 * validate join request
	 *
	 * @access protected
	 * @return bool
	 */
	protected function validate()
	{
		return true;
	}
	
	/**
	 * init registration form
	 *
	 * @access protected
	 * @return
	 */
	protected function initForm()
	{
		global $ilUser;
		
		if(is_object($this->form))
		{
			return true;
		}

		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'join'));
		$this->form->setTitle($this->getFormTitle());
		
		$this->fillInformations();
		$this->fillMembershipLimitation();
		if($this->isRegistrationPossible())
		{
			$this->fillRegistrationPeriod();
		}
		if($this->isRegistrationPossible())
		{
			$this->fillRegistrationType();
		}
		if($this->isRegistrationPossible())
		{
			$this->fillMaxMembers();
		}
		if($this->isRegistrationPossible())
		{
			$this->fillAgreement();
		}
		$this->addCommandButtons();
	}
	
	/**
	 * Add command buttons
	 * @return 
	 */
	protected function addCommandButtons()
	{
		global $ilUser;
		
		if($this->isRegistrationPossible() and $this->isWaitingListActive() and !$this->getWaitingList()->isOnList($ilUser->getId()))
		{
			$this->form->addCommandButton('join',$this->lng->txt('mem_add_to_wl'));
			$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		}
		elseif($this->isRegistrationPossible() and !$this->getWaitingList()->isOnList($ilUser->getId()))
		{
			$this->form->addCommandButton('join',$this->lng->txt('join'));
			$this->form->addCommandButton('cancel',$this->lng->txt('cancel'));
		}
		if($this->getWaitingList()->isOnList($ilUser->getId()))
		{
			ilUtil::sendQuestion(
				sprintf($this->lng->txt($this->container->getType().'_cancel_waiting_list'),
				$this->container->getTitle())
			);
			$this->form->addCommandButton('leaveWaitingList', $this->lng->txt('leave_waiting_list'));
			$this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
		}
	}
	
	/**
	 * Update subscription message
	 * @return void
	 */
	protected function updateSubscriptionRequest()
	{
		global $ilUser, $tree, $ilCtrl;
		
		$this->participants->updateSubject($ilUser->getId(),ilUtil::stripSlashes($_POST['subject']));
		ilUtil::sendSuccess($this->lng->txt('sub_request_saved'),true);
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
			$tree->getParentId($this->container->getRefId()));
		$ilCtrl->redirectByClass("ilrepositorygui", "");

	}
	
	protected function cancelSubscriptionRequest()
	{
		global $ilUser, $tree, $ilCtrl;
		
		$this->participants->deleteSubscriber($ilUser->getId());
		ilUtil::sendSuccess($this->lng->txt('sub_request_deleted'),true);
		
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
			$tree->getParentId($this->container->getRefId()));
		$ilCtrl->redirectByClass("ilrepositorygui", "");
	}
}
?>