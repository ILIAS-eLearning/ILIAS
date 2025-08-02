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

namespace ILIAS\Mail\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class IncomingMail implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'incoming_mail';
    }

    public function isAvailable(): bool
    {
        return (new \ilSetting())->get('show_mail_settings') === '1';
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('mail_incoming');
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Communication;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $lng->loadLanguageModule('mail');
        $byline = $this->buildByline($lng, $user);
        $inputs = $this->buildEmailInputs($lng, $field_factory, $user);
        return $field_factory->switchableGroup(
            [
                \ilMailOptions::INCOMING_LOCAL => $field_factory->group([], $lng->txt('mail_incoming_local')),
                \ilMailOptions::INCOMING_EMAIL => $field_factory->group(
                    $inputs,
                    $lng->txt('mail_incoming_smtp'),
                    $byline
                )->withDisabled($user->getEmail() === '' && $user->getSecondEmail() === ''),
                \ilMailOptions::INCOMING_BOTH => $field_factory->group(
                    $inputs,
                    $lng->txt('mail_incoming_both'),
                    $byline
                )->withDisabled($user->getEmail() === '' && $user->getSecondEmail() === ''),
            ],
            $lng->txt('mail_incoming')
        )->withAdditionalTransformation(
            $this->buildTransformation($refinery, $user)
        )->withValue(
            $this->buildValueSetterArray($settings, $user)
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('mail');
        $input = new \ilIncomingMailInputGUI($lng->txt('mail_incoming'), 'incoming_mail');
        $input->setFreeOptionChoice(false);
        $input->setValueByArray(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : [
                    'incoming_mail' => (int) $settings->get('mail_incoming_mail', (string) \ilMailOptions::INCOMING_LOCAL),
                    'mail_address_option' => (int) $settings->get('mail_address_option', (string) \ilMailOptions::FIRST_EMAIL)
                ]
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        $setting = (int) $settings->get('mail_incoming_mail', (string) \ilMailOptions::INCOMING_LOCAL);
        $value = match ($setting) {
            \ilMailOptions::INCOMING_LOCAL => $lng->txt('mail_incoming_local'),
            \ilMailOptions::INCOMING_EMAIL => $lng->txt('mail_incoming_smtp'),
            \ilMailOptions::INCOMING_BOTH => $lng->txt('mail_incoming_both')
        };
        if ($setting === \ilMailOptions::INCOMING_LOCAL) {
            return $value;
        }

        return match ((int) $settings->get('mail_address_option', (string) \ilMailOptions::FIRST_EMAIL)) {
            \ilMailOptions::FIRST_EMAIL => "{$value}: {$lng->txt('mail_first_email')}",
            \ilMailOptions::SECOND_EMAIL => "{$value}: {$lng->txt('mail_second_email')}",
            \ilMailOptions::BOTH_EMAIL => "{$value}: {$lng->txt('mail_both_email')}",
        };
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        $current = $this->retrieveValueFromUser($user);
        $default = [
            'incoming_mail' => (int) $settings->get('mail_incoming_mail', (string) \ilMailOptions::INCOMING_LOCAL),
            'mail_address_option' => (int) $settings->get('mail_address_option', (string) \ilMailOptions::FIRST_EMAIL)
        ];
        return $current !== $default;
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input,
        ?\ilPropertyFormGUI $form = null
    ): \ilObjUser {
        $mail_options = new \ilMailOptions($user->getId());
        if ($input === null) {
            $settings = new \ilSetting();
            $mail_options->setIncomingType(
                (int) $settings->get('mail_incoming_mail', (string) \ilMailOptions::INCOMING_LOCAL)
            );
            $mail_options->setEmailAddressmode(
                (int) $settings->get('mail_address_option', (string) \ilMailOptions::FIRST_EMAIL)
            );
            $mail_options->updateOptions();
            return $user;
        }

        $type = is_array($input) ? $input['incoming_mail'] : (int) $input;
        $mail_options->setIncomingType($type);
        if ((int) $input === \ilMailOptions::INCOMING_LOCAL) {
            $mail_options->updateOptions();
            return $user;
        }

        $address_mode = is_array($input)
            ? $input['mail_address_option']
            : (int) $form->getInput('mail_address_option');
        $mail_options->setEmailAddressmode($address_mode);
        $mail_options->updateOptions();
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): ?array
    {
        $mail_options = new \ilMailOptions($user->getId());
        return [
            'incoming_mail' => $mail_options->getIncomingType(),
            'mail_address_option' => $mail_options->getEmailAddressMode()
        ];
    }

    private function buildTransformation(
        Refinery $refinery,
        \ilObjUser $user
    ): Transformation {
        return $refinery->custom()->transformation(
            static function (array $v) use ($refinery, $user): array {
                $email_address_option = $v[1]['mail_address_option'] ?? null;
                if ($user->getEmail() !== '' && $user->getSecondEmail() === '') {
                    $email_address_option = \ilMailOptions::FIRST_EMAIL;
                } elseif ($user->getSecondEmail() !== '' && $user->getEmail() === '') {
                    $email_address_option = \ilMailOptions::SECOND_EMAIL;
                }
                return [
                    'incoming_mail' => $refinery->kindlyTo()->int()->transform($v[0]),
                    'mail_address_option' => $refinery->kindlyTo()->int()->transform($email_address_option)
                ];
            }
        );
    }

    private function buildValueSetterArray(
        \ilSetting $settings,
        ?\ilObjUser $user
    ): int|array {
        $value = $user !== null
            ? $this->retrieveValueFromUser($user)
            : [
                'incoming_mail' => (int) $settings->get('mail_incoming_mail', (string) \ilMailOptions::INCOMING_LOCAL),
                'mail_address_option' => (int) $settings->get('mail_address_option', (string) \ilMailOptions::FIRST_EMAIL)
            ];
        if ($value['incoming_mail'] === \ilMailOptions::INCOMING_LOCAL
            || $user->getEmail() === ''
            || $user->getSecondEmail() === '') {
            return $value['incoming_mail'];
        }

        return [
            0 => $value['incoming_mail'],
            1 => [
                'mail_address_option' => $value['mail_address_option']
            ]
        ];
    }

    private function buildByline(
        Language $lng,
        ?\ilObjUser $user
    ): string {
        if ($user->getEmail() !== '' && $user->getSecondEmail() !== '') {
            return '';
        }

        if ($user->getEmail() !== '') {
            return $user->getEmail();
        }

        if ($user->getSecondEmail() !== '') {
            return $user->getSecondEmail();
        }

        return $lng->txt('no_email');
    }

    private function buildEmailInputs(
        Language $lng,
        FieldFactory $field_factory,
        ?\ilObjUser $user
    ): array {
        if ($user === null
            || $user->getEmail() === '' || $user->getSecondEmail() === '') {
            return [];
        }

        return [
            'mail_address_option' => $field_factory->radio(
                $lng->txt('email')
            )->withOption((string) \ilMailOptions::FIRST_EMAIL, $lng->txt('mail_first_email'))
            ->withOption((string) \ilMailOptions::SECOND_EMAIL, $lng->txt('mail_second_email'))
            ->withOption((string) \ilMailOptions::BOTH_EMAIL, $lng->txt('mail_both_email'))
        ];
    }
}
