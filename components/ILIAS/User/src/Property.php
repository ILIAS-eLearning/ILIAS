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

namespace ILIAS\User;

use ILIAS\User\Settings\User\AvailableSections as SettingsSections;
use ILIAS\User\Profile\Fields\AvailableSections as ProfileSections;
use ILIAS\Language\Language;

interface Property
{
    public function getIdentifier(): string;
    public function getLabel(Language $lng): string;
    public function getSection(): SettingsSections|ProfileSections;

    /**
     * You don't need to add a post_var to the input as the User will handle this
     * for you, thus you can also not rely on the post_var anywhere else, as it
     * will be changed.
     */
    public function getInput(
        Language $lng,
        \ilObjUser $current_user
    ): \ilFormPropertyGUI;

    public function getValueForUser(\ilObjUser $current_user): mixed;
}
