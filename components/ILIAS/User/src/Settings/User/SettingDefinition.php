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

namespace ILIAS\User\Settings\User;

use ILIAS\User\Property;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

interface SettingDefinition extends Property
{
    public function isAvailable(): bool;
    public function getSettingsPage(): AvailablePages;

    public function getDefaultValueForDisplay(
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings
    ): ?string;
    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $current_user
    ): bool;

    /**
     * @param mixed $input `Null` will be handed in, if the  user
     * wants to use the system default. If you are able to set the preference on
     * the user without saving it, you can rely on the User-object being saved
     * after the call to this function.
     */
    public function persistUserInput(
        \ilObjUser $current_user,
        mixed $input
    ): void;
}
