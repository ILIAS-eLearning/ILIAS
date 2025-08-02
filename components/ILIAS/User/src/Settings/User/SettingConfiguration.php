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

namespace ILIAS\User\Settings\User;

use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;

interface SettingConfiguration
{
    public function getIdentifier(): string;
    public function isAvailable(): bool;
    public function getLanguageVariable(): string;
    public function getSettingsPage(): AvailablePages;
    public function getSection(): AvailableSections;

    /**
     * You don't need to add a post_var to the input as the User will handle this
     * for you, thus you can also not rely on the post_var anywhere else, as it
     * will be changed.
     */
    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI;
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
     * @param \ilFormPropertyGUI|null $input Null will be handed in, if the  user
     * wants to use the system default.
     */
    public function storeUserChoice(
        \ilObjUser $current_user,
        ?\ilFormPropertyGUI $input
    ): void;
}
