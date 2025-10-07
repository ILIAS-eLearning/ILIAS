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

namespace ILIAS\User\Settings;

use ILIAS\User\Property;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Input\Input;
use ILIAS\Refinery\Factory as Refinery;

interface SettingDefinition extends Property
{
    /**
     * If this function returns `false` the setting will not be shown, even if
     * it's `PropertyAttributes` would allow it to be. This is meant e.g. to
     * check if the chat server is configured or the badges enabled. Settings
     * that are not available will also not be available on the table to define
     * their `PropertyAttributes`
     */
    public function isAvailable(): bool;

    public function getSettingsPage(): AvailablePages;

    public function getDefaultValueForDisplay(
        Language $lng,
        \ilSetting $settings
    ): ?string;

    public function hasUserPersonalizedSetting(
        \ilSetting $settings,
        \ilObjUser $user
    ): bool;

    public function getInput(
        FieldFactory $field_factory,
        Language $lng,
        Refinery $refinery,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): Input;

    /**
     * You don't need to add a post_var to the input as the User will handle this
     * for you, thus you can also not rely on the post_var anywhere else, as it
     * will be changed.
     */
    public function getLegacyInput(
        Language $lng,
        \ilSetting $settings,
        ?\ilObjUser $user = null
    ): \ilFormPropertyGUI;

    /**
     * @param mixed $input `Null` will be handed in, if the  user
     * wants to use the system default. If you are able to set the preference on
     * the user without saving it, you can rely on the User-object being saved
     * after the call to this function. If your input has different structures
     * depending on its provenience (KS-Input, Legacy-Input, ...), the function
     * needs to be able to handle them all.
     */
    public function persistUserInput(
        \ilObjUser $user,
        mixed $input
    ): \ilObjUser;
}
