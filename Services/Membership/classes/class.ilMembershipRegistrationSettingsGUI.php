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
 *********************************************************************/

/**
 * Registration settings
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @ingroup ServicesMembership
 */
abstract class ilMembershipRegistrationSettingsGUI
{
    private ilObject $object;
    private ilObjectGUI $gui_object;
    protected ilLanguage $lng;
    private array $options = [];

    public function __construct(ilObjectGUI $gui_object, ilObject $object, array $a_options)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->gui_object = $gui_object;
        $this->object = $object;
        $this->options = $a_options;
    }

    /**
     * Set form values
     */
    abstract public function setFormValues(ilPropertyFormGUI $form): void;

    public function getCurrentObject(): ilObject
    {
        return $this->object;
    }

    public function getCurrentGUI(): ilObjectGUI
    {
        return $this->gui_object;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    final public function addMembershipFormElements(ilPropertyFormGUI $form, string $a_parent_post = ''): void
    {
        // Registration type
        $reg_type = new ilRadioGroupInputGUI($this->txt('reg_type'), 'registration_type');
        //$reg_type->setValue($this->object->getRegistrationType());

        if (in_array(ilMembershipRegistrationSettings::TYPE_DIRECT, $this->getOptions())) {
            $opt_dir = new ilRadioOption(
                $this->txt('reg_direct'),
                (string) ilMembershipRegistrationSettings::TYPE_DIRECT
            );
            $opt_dir->setInfo($this->txt('reg_direct_info'));
            $reg_type->addOption($opt_dir);

            // cannot participate
            $cannot_participate = new ilCheckboxInputGUI(
                $this->txt('reg_cannot_participate'),
                'show_cannot_participate_direct'
            );
            $cannot_participate->setInfo($this->txt('reg_cannot_participate_info'));
            $cannot_participate->setValue("1");
            $opt_dir->addSubItem($cannot_participate);
        }
        if (in_array(ilMembershipRegistrationSettings::TYPE_PASSWORD, $this->getOptions())) {
            $opt_pass = new ilRadioOption(
                $this->txt('reg_pass'),
                (string) ilMembershipRegistrationSettings::TYPE_PASSWORD
            );
            $pass = new ilTextInputGUI($GLOBALS['DIC']['lng']->txt("password"), 'password');
            $pass->setInfo($this->txt('reg_password_info'));
            #$pass->setValue($this->object->getPassword());
            $pass->setSize(10);
            $pass->setMaxLength(32);
            $opt_pass->addSubItem($pass);
            $reg_type->addOption($opt_pass);
        }

        if (in_array(ilMembershipRegistrationSettings::TYPE_REQUEST, $this->getOptions())) {
            $opt_req = new ilRadioOption(
                $this->txt('reg_request'),
                (string) ilMembershipRegistrationSettings::TYPE_REQUEST,
                $this->txt('reg_request_info')
            );
            $reg_type->addOption($opt_req);

            // cannot participate
            $cannot_participate = new ilCheckboxInputGUI(
                $this->txt('reg_cannot_participate'),
                'show_cannot_participate_request'
            );
            $cannot_participate->setInfo($this->txt('reg_cannot_participate_info'));
            $cannot_participate->setValue("1");
            $opt_req->addSubItem($cannot_participate);
        }
        if (in_array(ilMembershipRegistrationSettings::TYPE_TUTOR, $this->getOptions())) {
            $opt_tutor = new ilRadioOption(
                $this->txt('reg_tutor'),
                (string) ilMembershipRegistrationSettings::TYPE_TUTOR,
                $this->txt('reg_tutor_info')
            );
            $reg_type->addOption($opt_tutor);
        }
        if (in_array(ilMembershipRegistrationSettings::TYPE_NONE, $this->getOptions())) {
            $opt_deact = new ilRadioOption(
                $this->txt('reg_disabled'),
                (string) ilMembershipRegistrationSettings::TYPE_NONE,
                $this->txt('reg_disabled_info')
            );
            $reg_type->addOption($opt_deact);
        }

        // Add to form
        $form->addItem($reg_type);

        if (in_array(ilMembershipRegistrationSettings::REGISTRATION_LIMITED_USERS, $this->getOptions())) {
            // max member
            $lim = new ilCheckboxInputGUI($this->txt('reg_max_members_short'), 'registration_membership_limited');
            $lim->setValue("1");

            $max = new ilTextInputGUI($this->txt('reg_max_members'), 'registration_max_members');
            #$max->setValue($this->object->getMaxMembers() ? $this->object->getMaxMembers() : '');
            //$max->setTitle($this->lng->txt('members'));
            $max->setSize(3);
            $max->setMaxLength(4);
            $max->setInfo($this->txt('reg_max_members_info'));
            $lim->addSubItem($max);

            $wait = new ilRadioGroupInputGUI($this->txt('reg_waiting_list'), 'waiting_list');

            $option = new ilRadioOption($this->txt('reg_waiting_list_none'), "0");
            $wait->addOption($option);

            $option = new ilRadioOption($this->txt('reg_waiting_list_no_autofill'), "1");
            $option->setInfo($this->txt('reg_waiting_list_no_autofill_info'));
            $wait->addOption($option);

            $option = new ilRadioOption($this->txt('reg_waiting_list_autofill'), "2");
            $option->setInfo($this->txt('reg_waiting_list_autofill_info'));
            $wait->addOption($option);

            $lim->addSubItem($wait);

            $form->addItem($lim);
        }

        $notificationCheckbox = new ilCheckboxInputGUI(
            $this->txt('registration_notification'),
            'registration_notification'
        );
        $notificationCheckbox->setInfo($this->txt('registration_notification_info'));

        $notificationOption = new ilRadioGroupInputGUI($this->txt('notification_option'), 'notification_option');
        $notificationOption->setRequired(true);

        $inheritOption = new ilRadioOption(
            $this->txt(ilSessionConstants::NOTIFICATION_INHERIT_OPTION),
            ilSessionConstants::NOTIFICATION_INHERIT_OPTION
        );
        $inheritOption->setInfo($this->txt('notification_option_inherit_info'));
        $notificationOption->addOption($inheritOption);

        $manualOption = new ilRadioOption(
            $this->txt(ilSessionConstants::NOTIFICATION_MANUAL_OPTION),
            ilSessionConstants::NOTIFICATION_MANUAL_OPTION
        );
        $manualOption->setInfo($this->txt('notification_option_manual_info'));
        $notificationOption->addOption($manualOption);

        $notificationCheckbox->addSubItem($notificationOption);
        $form->addItem($notificationCheckbox);

        $this->setFormValues($form);
    }

    /**
     * Translate type specific
     */
    protected function txt(string $a_lang_key): string
    {
        $prefix = $this->getCurrentObject()->getType();
        return $this->lng->txt($prefix . '_' . $a_lang_key);
    }
}
