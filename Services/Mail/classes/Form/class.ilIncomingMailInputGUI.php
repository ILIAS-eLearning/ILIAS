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
 * Class ilIncomingMailInputGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 */
class ilIncomingMailInputGUI extends ilRadioGroupInputGUI
{
    protected bool $freeOptionChoice = true;
    protected bool $optionsInitialized = false;

    public function __construct(string $title = '', string $post_var = '', bool $freeOptionChoice = true)
    {
        parent::__construct($title, $post_var);
        $this->setFreeOptionChoice($freeOptionChoice);
    }

    protected function initializeOptions(): void
    {
        if (!$this->optionsInitialized) {
            $this->addSubOptions();
            $this->optionsInitialized = true;
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
        return $this->freeOptionChoice;
    }

    public function setFreeOptionChoice(bool $freeOptionChoice): void
    {
        $this->freeOptionChoice = $freeOptionChoice;
    }

    private function addSubOptions(): void
    {
        global $DIC;

        $incomingLocal = new ilRadioOption(
            $DIC->language()->txt('mail_incoming_local'),
            (string) ilMailOptions::INCOMING_LOCAL
        );
        $incomingLocal->setDisabled($this->getDisabled());

        $incomingExternal = new ilRadioOption(
            $DIC->language()->txt('mail_incoming_smtp'),
            (string) ilMailOptions::INCOMING_EMAIL
        );
        $incomingExternal->setDisabled($this->getDisabled());

        $incomingBoth = new ilRadioOption(
            $DIC->language()->txt('mail_incoming_both'),
            (string) ilMailOptions::INCOMING_BOTH
        );
        $incomingBoth->setDisabled($this->getDisabled());

        $this->addOption($incomingLocal);
        $this->addOption($incomingExternal);
        $this->addOption($incomingBoth);

        $incomingExternalAddressChoice = new ilRadioGroupInputGUI('', 'mail_address_option');
        $incomingExternalAddressChoice->setDisabled($this->getDisabled());

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

        $incomingBothAddressChoice = new ilRadioGroupInputGUI('', 'mail_address_option_both');
        $incomingBothAddressChoice->setDisabled($this->getDisabled());
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
        if (!$this->isFreeOptionChoice()) {
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
                $incomingExternal->setInfo($email_info[0]);
                $incomingBoth->setInfo($email_info[0]);
            } else {
                $incomingExternalAddressChoice->addOption($sub_mail_opt1);
                $incomingExternalAddressChoice->addOption($sub_mail_opt2);
                $incomingExternalAddressChoice->addOption($sub_mail_opt3);

                $incomingBothAddressChoice->addOption($sub_both_opt1);
                $incomingBothAddressChoice->addOption($sub_both_opt2);
                $incomingBothAddressChoice->addOption($sub_both_opt3);

                $incomingExternal->addSubItem($incomingExternalAddressChoice);
                $incomingBoth->addSubItem($incomingBothAddressChoice);
            }
        } else {
            $incomingExternalAddressChoice->addOption($sub_mail_opt1);
            $incomingExternalAddressChoice->addOption($sub_mail_opt2);
            $incomingExternalAddressChoice->addOption($sub_mail_opt3);
            $incomingBothAddressChoice->addOption($sub_both_opt1);
            $incomingBothAddressChoice->addOption($sub_both_opt2);
            $incomingBothAddressChoice->addOption($sub_both_opt3);

            $incomingExternal->addSubItem($incomingExternalAddressChoice);
            $incomingBoth->addSubItem($incomingBothAddressChoice);
        }
    }
}
