<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Membership/classes/class.ilMembershipRegistrationSettingsGUI.php';

/**
* Registration settings 
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @version $Id$
*
* @ingroup ServicesMembership
*/
class ilSessionMembershipRegistrationSettingsGUI extends ilMembershipRegistrationSettingsGUI
{
	/**
	 * Overwitten to load language module
	 * @param \ilObjectGUI $gui_object
	 * @param \ilObject $object
	 * @param type $a_options
	 */
	public function __construct(ilObjectGUI $gui_object, ilObject $object, $a_options)
	{
		parent::__construct($gui_object, $object, $a_options);
		$GLOBALS['lng']->loadLanguageModule('sess');
	}
	
	public function setFormValues(ilPropertyFormGUI $form)
	{
		$form->getItemByPostVar('registration_type')->setValue($this->getCurrentObject()->getRegistrationType());
		$form->getItemByPostVar('registration_membership_limited')->setChecked($this->getCurrentObject()->isRegistrationUserLimitEnabled());
		$form->getItemByPostVar('registration_max_members')->setValue($this->getCurrentObject()->getRegistrationMaxUsers());
		$form->getItemByPostVar('waiting_list')->setChecked($this->getCurrentObject()->isRegistrationWaitingListEnabled());
	}
}
?>
