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

namespace ILIAS\Administration\Node;

/**
 * Groups of the administration menu.
 */
enum Group: string
{
    case MAINTENANCE = 'maintenance';
    case LAYOUT_AND_NAVIGATION = 'layout_and_navigation';
    case REPOSITORY_AND_OBJECTS = 'repository_and_objects';
    case PERSONAL_WORKSPACE = 'personal_workspace';
    case ACHIEVEMENTS = 'achievements';
    case COMMUNICATION = 'communication';
    case USER_ADMINISTRATION = 'user_administration';
    case SEARCH_AND_FIND = 'search_and_find';
    case EXTENDING_ILIAS = 'extending_ilias';
    case LEGAL_REGULATIONS = 'legal_regulations';

    /**
     * Language variable of the title of the group
     */
    public function getTitleLanguageVariable(): string
    {
        return 'adm_' . $this->value;
    }

    public function getIconName(): string
    {
        return match ($this) {
            self::MAINTENANCE => 'icon_sysa',
            self::LAYOUT_AND_NAVIGATION => 'icon_laya',
            self::REPOSITORY_AND_OBJECTS => 'icon_repa',
            self::PERSONAL_WORKSPACE => 'icon_pwsa',
            self::ACHIEVEMENTS => 'icon_achva',
            self::COMMUNICATION => 'icon_coma',
            self::USER_ADMINISTRATION => 'icon_usra',
            self::SEARCH_AND_FIND => 'icon_safa',
            self::EXTENDING_ILIAS => 'icon_exta',
            self::LEGAL_REGULATIONS => 'icon_lega',
        };
    }
}
