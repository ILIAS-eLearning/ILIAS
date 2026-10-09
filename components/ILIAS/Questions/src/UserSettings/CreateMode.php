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

namespace ILIAS\Questions\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Field\Radio;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class CreateMode implements SettingDefinition
{
    private \ilSetting $settings;
    public function __construct()
    {
        $this->settings = new \ilSetting('questions');
    }
    #[\Override]
    public function getIdentifier(): string
    {
        return 'question_create_mode';
    }

    #[\Override]
    public function isAvailable(): bool
    {
        return true;
    }

    #[\Override]
    public function getLabel(Language $lng): string
    {
        return $lng->txt('question_create_mode');
    }

    #[\Override]
    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    #[\Override]
    public function getSection(): AvailableSections
    {
        return AvailableSections::Additional;
    }

    #[\Override]
    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $lng->loadLanguageModule('questions');
        return array_reduce(
            CreateModes::cases(),
            fn(Radio $c, CreateModes $v): Radio => $c->withOption(
                $v->value,
                $v->getLabelForInput($lng),
                $v->getBylineForInput($lng)
            ),
            $field_factory->radio(
                $lng->txt('question_create_mode')
            )
        )->withValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : $this->settings->get(
                    'default_create_mode',
                    CreateModes::getDefaultMode()->value
                )
        );
    }

    #[\Override]
    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('questions');
        $input = array_reduce(
            CreateModes::cases(),
            function (
                \ilRadioGroupInputGUI $c,
                CreateModes $v
            ) use ($lng): \ilRadioGroupInputGUI {
                $c->addOption(
                    new \ilRadioOption(
                        $v->getLabelForInput($lng),
                        $v->value,
                        $v->getBylineForInput($lng)
                    )
                );
                return $c;
            },
            new \ilRadioGroupInputGUI($lng->txt('question_create_mode'))
        );

        $input->setValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : $this->settings->get(
                    'default_create_mode',
                    CreateModes::getDefaultMode()->value
                )
        );
        return $input;
    }

    #[\Override]
    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return CreateModes::getDefaultMode()->getLabelForInput($lng);
    }

    #[\Override]
    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return $this->retrieveValueFromUser($user)
            !== $this->settings->get(
                'default_create_mode',
                CreateModes::getDefaultMode()->value
            );
    }

    #[\Override]
    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        $user->setPref(
            'question_create_mode',
            $input !== null
                ? $input
                : $this->settings->get(
                    'default_create_mode',
                    CreateModes::getDefaultMode()->value
                )
        );
        return $user;
    }

    #[\Override]
    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        return $user->getPref('question_create_mode')
            ?? $this->settings->get(
                'default_create_mode',
                CreateModes::getDefaultMode()->value
            );
    }
}
