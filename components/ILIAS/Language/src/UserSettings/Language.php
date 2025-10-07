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

namespace ILIAS\Language\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language as SystemLanguage;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class Language implements SettingDefinition
{
    private \ilLanguage $lng;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC['lng'];
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getIdentifier(): string
    {
        return 'language';
    }

    public function getLabel(SystemLanguage $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::MainSettings;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Main;
    }

    public function getInput(
        FieldFactory $field_factory,
        SystemLanguage $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        $options = $this->buildSelectOptions($lng);
        return $field_factory->select(
            $this->getLabel($lng),
            $options
        )->withDisabled(
            count($options) <= 1
        )->withRequired(true)
        ->withValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : $this->lng->getDefaultLanguage()
        );
    }

    public function getLegacyInput(
        SystemLanguage $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI {
        $options = $this->buildSelectOptions($lng);
        $input = new \ilSelectInputGUI($this->getLabel($lng));
        $input->setOptionsLangAttribute(fn($options, $key) => $key);
        $input->setOptions($options);
        $input->setDisabled(count($options) <= 1);
        $input->setValue(
            $user !== null
                ? $this->retrieveValueFromUser($user)
                : $this->lng->getDefaultLanguage()
        );
        return $input;
    }

    public function getDefaultValueForDisplay(
        SystemLanguage $lng,
        \ilSetting $settings
    ): null {
        return null;
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool {
        return true;
    }

    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser {
        $user->setLanguage($input);
        return $user;
    }

    public function retrieveValueFromUser(\ilObjUser $user): string
    {
        return $user->getLanguage();
    }

    private function buildSelectOptions(SystemLanguage $lng): array
    {
        return array_reduce(
            $lng->getInstalledLanguages(),
            function (array $c, string $lang_key) use ($lng): array {
                $c[$lang_key] = $lng->txtlng('meta', 'meta_l_' . $lang_key, $lang_key);
                return $c;
            },
            []
        );
    }
}
