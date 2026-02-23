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

namespace ILIAS\Authentication\UserSettings;

use ILIAS\User\Settings\SettingDefinition;
use ILIAS\User\Settings\AvailablePages;
use ILIAS\User\Settings\AvailableSections;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

class Password implements SettingDefinition
{
    public function getIdentifier(): string
    {
        return 'password';
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getLabel(Language $lng): string
    {
        return $lng->txt($this->getIdentifier());
    }

    public function getSettingsPage(): AvailablePages
    {
        return AvailablePages::Password;
    }

    public function getSection(): AvailableSections
    {
        return AvailableSections::Main;
    }

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input {
        throw new \Exception('This Setting does not provide an Input.');
    }

    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $current_user = null
    ): \ilFormPropertyGUI {
        throw new \Exception('This Setting does not provide an Input.');
    }

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): null {
        return null;
    }

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $current_user
    ): bool {
        return true;
    }

    public function persistUserInput(
        \ilObjUser $current_user,
        mixed $input
    ): \ilObjUser {
        throw new \Exception('We do cannot store this Setting.');
    }

    public function retrieveValueFromUser(\ilObjUser $current_user): null
    {
        return null;
    }
}
