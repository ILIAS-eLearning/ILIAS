<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipRegistrationSettings.php';

/**
* Registration settings 
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesMembership
*/
abstract class ilMembershipRegistrationSettingsGUI
{
	private $object = null;
	private $gui_object = null;
	private $options = array();
	
	/**
	 * Constructor
	 * @param ilObjectGUI $gui_object
	 * @param ilObject $object
	 */
	public function __construct(ilObjectGUI $gui_object, ilObject $object, $a_options)
	{
		$this->gui_object = $gui_object;
		$this->object = $object;
		$this->options = $a_options;
	}
	
	/**
	 * Set form values
	 */
	abstract public function setFormValues(ilPropertyFormGUI $form);
	
	/**
	 * Get current object
	 * @return ilObject
	 */
	public function getCurrentObject()
	{
		return $this->object;
	}
	
	/**
	 * Get gui object
	 * @return ilObjectGUI
	 */
	public function getCurrentGUI()
	{
		return $this->gui_object;
	}
	
	/**
	 * Get options
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}
	
	/**
	 * Add membership form elements
	 * @param ilPropertyFormGUI $form
	 */
	public final function addMembershipFormElements(ilPropertyFormGUI $form, $a_parent_post = '')
	{
		// Registration type
		$reg_type = new ilRadioGroupInputGUI($this->txt('reg_type'),'registration_type');
		//$reg_type->setValue($this->object->getRegistrationType());

		if(in_array(ilMembershipRegistrationSettings::TYPE_DIRECT,$this->getOptions()))
		{
			$opt_dir = new ilRadioOption($this->txt('reg_direct'),  ilMembershipRegistrationSettings::TYPE_DIRECT);#$this->lng->txt('grp_reg_direct_info'));
			$reg_type->addOption($opt_dir);
		}
		if(in_array(ilMembershipRegistrationSettings::TYPE_PASSWORD,$this->getOptions()))
		{
			$opt_pass = new ilRadioOption($this->txt('reg_pass'),  ilMembershipRegistrationSettings::TYPE_PASSWORD);
			$pass = new ilTextInputGUI($GLOBALS['lng']->txt("password"),'password');
			$pass->setInfo($this->txt('reg_password_info'));
			#$pass->setValue($this->object->getPassword());
			$pass->setSize(10);
			$pass->setMaxLength(32);
			$opt_pass->addSubItem($pass);
			$reg_type->addOption($opt_pass);
		}

		if(in_array(ilMembershipRegistrationSettings::TYPE_REQUEST,$this->getOptions()))
		{
			$opt_req = new ilRadioOption($this->txt('reg_request'),  ilMembershipRegistrationSettings::TYPE_REQUEST,$this->txt('reg_request_info'));
			$reg_type->addOption($opt_req);
		}
		if(in_array(ilMembershipRegistrationSettings::TYPE_NONE,$this->getOptions()))
		{
			$opt_deact = new ilRadioOption($this->txt('reg_disabled'),ilMembershipRegistrationSettings::TYPE_NONE,$this->txt('reg_disabled_info'));
			$reg_type->addOption($opt_deact);
		}
		
		// Add to form
		$form->addItem($reg_type);
		
		if(in_array(ilMembershipRegistrationSettings::REGISTRATION_LIMITED_USERS,$this->getOptions()))
		{
			// max member
			$lim = new ilCheckboxInputGUI($this->txt('reg_max_members_short'),'registration_membership_limited');
			$lim->setValue(1);
			#$lim->setOptionTitle($this->lng->txt('reg_grp_max_members'));
			#$lim->setChecked($this->object->isMembershipLimited());

			$max = new ilTextInputGUI($this->txt('reg_max_members'),'registration_max_members');
			#$max->setValue($this->object->getMaxMembers() ? $this->object->getMaxMembers() : '');
			//$max->setTitle($this->lng->txt('members'));
			$max->setSize(3);
			$max->setMaxLength(4);
			$max->setInfo($this->txt('reg_max_members_info'));
			$lim->addSubItem($max);

			$wait = new ilCheckboxInputGUI($this->txt('reg_waiting_list'),'waiting_list');
			$wait->setValue(1);
			//$wait->setOptionTitle($this->lng->txt('grp_waiting_list'));
			$wait->setInfo($this->txt('reg_waiting_list_info'));
			#$wait->setChecked($this->object->isWaitingListEnabled() ? true : false);
			$lim->addSubItem($wait);
			
			$form->addItem($lim);
		}
		
		$this->setFormValues($form);
	}
	
	/**
	 * Translate type specific
	 */
	protected function txt($a_lang_key)
	{
		$prefix = $this->getCurrentObject()->getType();
		return $GLOBALS['lng']->txt($prefix.'_'.$a_lang_key);
	}
}
?>
