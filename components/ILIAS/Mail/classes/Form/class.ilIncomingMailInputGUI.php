<?php

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

declare(strict_types=1);

class ilIncomingMailInputGUI extends ilRadioGroupInputGUI
{
    protected bool $free_option_choice = true;
    protected bool $options_initialized = false;

    public function __construct(string $title = '', string $post_var = '', bool $free_option_choice = true)
    {
        parent::__construct($title, $post_var);
        $this->setFreeOptionChoice($free_option_choice);
    }

    protected function initializeOptions(): void
    {
        if (!$this->options_initialized) {
            $this->addSubOptions();
            $this->options_initialized = true;
        }
    }

    public function getOptions(): array
    {
        $this->initializeOptions();
        return parent::getOptions();
    }

    public function setValueByArray($a_values): void
    {
        $this->initializeOptions();
        parent::setValueByArray($a_values);
    }

    public function checkInput(): bool
    {
        $this->initializeOptions();
        return parent::checkInput();
    }

    public function render(): string
    {
        $this->initializeOptions();
        return parent::render();
    }

    public function getItemByPostVar(string $a_post_var): ?ilFormPropertyGUI
    {
        $this->initializeOptions();
        return parent::getItemByPostVar($a_post_var);
    }

    public function getSubInputItemsRecursive(): array
    {
        $this->initializeOptions();
        return parent::getSubInputItemsRecursive();
    }

    public function isFreeOptionChoice(): bool
    {
        return $this->free_option_choice;
    }

    public function setFreeOptionChoice(bool $free_option_choice): void
    {
        $this->free_option_choice = $free_option_choice;
    }

    private function addSubOptions(): void
    {
        global $DIC;

        $incoming_local = new ilRadioOption(
            $DIC->language()->txt('mail_incoming_local'),
            (string) ilMailOptions::INCOMING_LOCAL
        );
        $incoming_local->setDisabled($this->getDisabled());

        $incoming_external = new ilRadioOption(
            $DIC->language()->txt('mail_incoming_smtp'),
            (string) ilMailOptions::INCOMING_EMAIL
        );
        $incoming_external->setDisabled($this->getDisabled());

        $incoming_both = new ilRadioOption(
            $DIC->language()->txt('mail_incoming_both'),
            (string) ilMailOptions::INCOMING_BOTH
        );
        $incoming_both->setDisabled($this->getDisabled());

        $this->addOption($incoming_local);
        $this->addOption($incoming_external);
        $this->addOption($incoming_both);

        $incoming_external_address_choice = new ilRadioGroupInputGUI('', 'mail_address_option');
        $incoming_external_address_choice->setDisabled($this->getDisabled());

        $sub_mail_opt1 = new ilRadioOption(
            $DIC->language()->txt('mail_first_email'),
            (string) ilMailOptions::FIRST_EMAIL
        );
        $sub_mail_opt1->setDisabled($this->getDisabled());

        $sub_mail_opt2 = new ilRadioOption(
            $DIC->language()->txt('mail_second_email'),
            (string) ilMailOptions::SECOND_EMAIL
        );
        $sub_mail_opt2->setDisabled($this->getDisabled());
        $sub_mail_opt3 = new ilRadioOption(
            $DIC->language()->txt('mail_both_email'),
            (string) ilMailOptions::BOTH_EMAIL
        );
        $sub_mail_opt3->setDisabled($this->getDisabled());

        $incoming_both_address_choice = new ilRadioGroupInputGUI('', 'mail_address_option_both');
        $incoming_both_address_choice->setDisabled($this->getDisabled());
        $sub_both_opt1 = new ilRadioOption(
            $DIC->language()->txt('mail_first_email'),
            (string) ilMailOptions::FIRST_EMAIL
        );
        $sub_both_opt1->setDisabled($this->getDisabled());

        $sub_both_opt2 = new ilRadioOption(
            $DIC->language()->txt('mail_second_email'),
            (string) ilMailOptions::SECOND_EMAIL
        );
        $sub_both_opt2->setDisabled($this->getDisabled());
        $sub_both_opt3 = new ilRadioOption(
            $DIC->language()->txt('mail_both_email'),
            (string) ilMailOptions::BOTH_EMAIL
        );
        $sub_both_opt3->setDisabled($this->getDisabled());
        if ($this->isFreeOptionChoice()) {
            $incoming_external_address_choice->addOption($sub_mail_opt1);
            $incoming_external_address_choice->addOption($sub_mail_opt2);
            $incoming_external_address_choice->addOption($sub_mail_opt3);
            $incoming_both_address_choice->addOption($sub_both_opt1);
            $incoming_both_address_choice->addOption($sub_both_opt2);
            $incoming_both_address_choice->addOption($sub_both_opt3);

            $incoming_external->addSubItem($incoming_external_address_choice);
            $incoming_both->addSubItem($incoming_both_address_choice);
        } else {
            $email_info = [];
            if (
                $DIC->settings()->get('usr_settings_disable_mail_incoming_mail') === '1') {
                $this->setDisabled(true);
            }

            if ($DIC->user()->getEmail() === '') {
                $sub_mail_opt1->setInfo($DIC->language()->txt('first_email_missing_info'));
                $sub_mail_opt3->setInfo($DIC->language()->txt('first_email_missing_info'));
                $sub_both_opt1->setInfo($DIC->language()->txt('first_email_missing_info'));
                $sub_both_opt3->setInfo($DIC->language()->txt('first_email_missing_info'));
            } else {
                $email_info[] = $DIC->user()->getEmail();
            }
            if ($DIC->settings()->get('usr_settings_disable_mail_incoming_mail') === '1') {
                $sub_mail_opt1->setDisabled(true);
                $sub_mail_opt3->setDisabled(true);
                $sub_both_opt1->setDisabled(true);
                $sub_both_opt3->setDisabled(true);
            }

            if ($DIC->user()->getSecondEmail() === '') {
                $sub_mail_opt2->setInfo($DIC->language()->txt('second_email_missing_info'));
                $sub_mail_opt3->setInfo($DIC->language()->txt('second_email_missing_info'));
                $sub_both_opt2->setInfo($DIC->language()->txt('second_email_missing_info'));
                $sub_both_opt3->setInfo($DIC->language()->txt('second_email_missing_info'));
            } else {
                $email_info[] = $DIC->user()->getSecondEmail();
            }
            if ($DIC->settings()->get('usr_settings_disable_mail_incoming_mail') === '1') {
                $sub_mail_opt2->setDisabled(true);
                $sub_mail_opt3->setDisabled(true);
                $sub_both_opt2->setDisabled(true);
                $sub_both_opt3->setDisabled(true);
            }

            if (count($email_info) > 1) {
                $sub_mail_opt1->setInfo($email_info[0]);
                $sub_both_opt1->setInfo($email_info[0]);
                $sub_mail_opt2->setInfo($email_info[1]);
                $sub_both_opt2->setInfo($email_info[1]);
                $sub_mail_opt3->setInfo(implode(', ', $email_info));
                $sub_both_opt3->setInfo(implode(', ', $email_info));
            }

            if (count($email_info) === 1) {
                $incoming_external->setInfo($email_info[0]);
                $incoming_both->setInfo($email_info[0]);
            } else {
                $incoming_external_address_choice->addOption($sub_mail_opt1);
                $incoming_external_address_choice->addOption($sub_mail_opt2);
                $incoming_external_address_choice->addOption($sub_mail_opt3);

                $incoming_both_address_choice->addOption($sub_both_opt1);
                $incoming_both_address_choice->addOption($sub_both_opt2);
                $incoming_both_address_choice->addOption($sub_both_opt3);

                $incoming_external->addSubItem($incoming_external_address_choice);
                $incoming_both->addSubItem($incoming_both_address_choice);
            }
        }
    }
}
