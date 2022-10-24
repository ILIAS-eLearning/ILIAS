<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

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
    public function __construct(ilObjectGUI $gui_object, ilObject $object, array $a_options)
    {
        parent::__construct($gui_object, $object, $a_options);
        $this->lng->loadLanguageModule('sess');
    }

    public function setFormValues(ilPropertyFormGUI $form): void
    {
        $form->getItemByPostVar('registration_type')->setValue((string) $this->getCurrentObject()->getRegistrationType());

        if ($this->getCurrentObject()->isCannotParticipateOptionEnabled()) {
            $form->getItemByPostVar('show_cannot_participate_direct')->setChecked(true);
            $form->getItemByPostVar('show_cannot_participate_request')->setChecked(true);
        }
        $form->getItemByPostVar('registration_membership_limited')->setChecked((bool) $this->getCurrentObject()->isRegistrationUserLimitEnabled());

        $notificationCheckBox = $form->getItemByPostVar('registration_notification');
        $notificationCheckBox->setChecked($this->getCurrentObject()->isRegistrationNotificationEnabled());

        $notificationOption = $form->getItemByPostVar('notification_option');
        $notificationOption->setValue($this->getCurrentObject()->getRegistrationNotificationOption());
        $form->getItemByPostVar('registration_max_members')->setValue(
            $this->getCurrentObject()->getRegistrationMaxUsers() > 0 ?
                (string) $this->getCurrentObject()->getRegistrationMaxUsers() : ""
        );

        $wait = 0;
        if ($this->getCurrentObject()->hasWaitingListAutoFill()) {
            $wait = 2;
        } elseif ($this->getCurrentObject()->isRegistrationWaitingListEnabled()) {
            $wait = 1;
        }
        $form->getItemByPostVar('waiting_list')->setValue((string) $wait);
    }
}
