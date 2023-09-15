<?php

declare(strict_types=1);

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

namespace ILIAS\Style\Content;

use ILIAS\Style\Content\Access\StyleAccessManager;
use ilObjStyleSheet;
use ilStyleCharacteristicGUI;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class CharacteristicUIFactory
{
    protected InternalGUIService $gui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->domain_service = $domain_service;
        $this->gui_service = $gui_service;
    }

    // characteristics editing
    public function ilStyleCharacteristicGUI(
        ilObjStyleSheet $style_sheet_obj,
        string $super_type,
        StyleAccessManager $access_manager,
        CharacteristicManager $characteristic_manager,
        ImageManager $image_manager
    ): ilStyleCharacteristicGUI {
        return new ilStyleCharacteristicGUI(
            $this->domain_service,
            $this->gui_service,
            $style_sheet_obj,
            $super_type,
            $access_manager,
            $characteristic_manager,
            $image_manager
        );
    }

    // characteristics table
    public function CharacteristicTableGUI(
        object $a_parent_obj,
        string $a_parent_cmd,
        string $a_super_type,
        ilObjStyleSheet $a_style,
        CharacteristicManager $manager,
        Access\StyleAccessManager $access_manager
    ): CharacteristicTableGUI {
        return new CharacteristicTableGUI(
            $this->gui_service,
            $a_parent_obj,
            $a_parent_cmd,
            $a_super_type,
            $a_style,
            $manager,
            $access_manager
        );
    }
}
