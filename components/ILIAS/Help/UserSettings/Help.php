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

namespace ILIAS\Help\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language as Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class Help implements SettingDefinition
{
    private readonly ?\ilHelpGUI $help;

    public function __construct()
    {
        global $DIC;
        $this->help = $DIC['ilHelp'] ?? null;
    }

    public function getIdentifier(): string
    {
        return 'help';
    }

    public function isAvailable(): bool
    {
        return $this->help->areTooltipsActive();
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt('help_toggle_tooltips');
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Additional;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $lng->loadLanguageModule('help');
        return $field_factory->checkbox(
            $lng->txt('help_toggle_tooltips'),
            $lng->txt('help_toggle_tooltips_info')
        )->withValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : false
        );
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $lng->loadLanguageModule('help');
        $input = new \ilCheckboxInputGUI($lng->txt('help_toggle_tooltips'));
        $input->setInfo($lng->txt('help_toggle_tooltips_info'));
        $input->setChecked(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : false
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): string {
        return $lng->txt('inactive');
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return $this->retrieveValueFromUser($user) === true;
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        if ($this->help->areTooltipsActive()) {
            $user->setPref('hide_help_tt', $input === true || $input === '1' ? '0' : '1');
        }
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): bool
    {
        return $user->getPref('hide_help_tt') !== '1';
    }
}
