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
        $GLOBALS['DIC']['lng']->loadLanguageModule('sess');
    }
    
    public function setFormValues(ilPropertyFormGUI $form)
    {
        $form->getItemByPostVar('registration_type')->setValue($this->getCurrentObject()->getRegistrationType());
        $form->getItemByPostVar('registration_membership_limited')->setChecked($this->getCurrentObject()->isRegistrationUserLimitEnabled());
        
        /* not supported yet
        $form->getItemByPostVar('registration_min_members')->setValue(
            $this->getCurrentObject()->getRegistrationMinUsers() > 0 ?
            $this->getCurrentObject()->getRegistrationMinUsers() : "");
        */
        
        $form->getItemByPostVar('registration_max_members')->setValue(
            $this->getCurrentObject()->getRegistrationMaxUsers() > 0 ?
            $this->getCurrentObject()->getRegistrationMaxUsers() : ""
        );
        
        $wait = 0;
        if ($this->getCurrentObject()->hasWaitingListAutoFill()) {
            $wait = 2;
        } elseif ($this->getCurrentObject()->isRegistrationWaitingListEnabled()) {
            $wait = 1;
        }
        $form->getItemByPostVar('waiting_list')->setValue($wait);
    }
}
