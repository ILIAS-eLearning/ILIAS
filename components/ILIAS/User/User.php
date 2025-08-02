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

namespace ILIAS;

use ILIAS\User\Setup\Agent;
use ILIAS\User\Settings\User\UserSettings;
use ILIAS\User\Settings\User\Settings\Settings as SettingsOfUser;
use ILIAS\User\Profile\Fields\Custom\Type as CustomProfileFieldType;
use ILIAS\User\Profile\Fields\Custom\Text as CustomTypeText;
use ILIAS\User\Profile\Fields\Custom\TextArea as CustomTypeTextArea;
use ILIAS\User\Profile\Fields\Custom\Select as CustomTypeSelect;
use ILIAS\User\Profile\ChangeListeners\UserFieldAttributesChangeListener;
use ILIAS\Setup\Agent as SetupAgent;

class User implements Component\Component
{
    public function init(
        array | \ArrayAccess &$define,
        array | \ArrayAccess &$implement,
        array | \ArrayAccess &$use,
        array | \ArrayAccess &$contribute,
        array | \ArrayAccess &$seek,
        array | \ArrayAccess &$provide,
        array | \ArrayAccess &$pull,
        array | \ArrayAccess &$internal,
    ): void {
        $contribute[SetupAgent::class] = fn() =>
            new Agent(
                $seek[UserSettings::class],
                $seek[CustomProfileFieldType::class],
                $seek[UserFieldAttributesChangeListener::class]
            );
        $contribute[UserSettings::class] = fn() =>
            new SettingsOfUser();
        $contribute[CustomProfileFieldType::class] = fn() =>
            new CustomTypeText();
        $contribute[CustomProfileFieldType::class] = fn() =>
            new CustomTypeTextArea();
        $contribute[CustomProfileFieldType::class] = fn() =>
            new CustomTypeSelect();
    }
}
