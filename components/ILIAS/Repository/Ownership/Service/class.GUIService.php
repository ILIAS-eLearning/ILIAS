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

namespace ILIAS\Repository\Ownership;

use ILIAS\Repository\InternalGUIService;
use ILIAS\Repository\InternalDomainService;

class GUIService
{
    protected InternalGUIService $gui_service;
    protected InternalDomainService $domain_service;

    public function __construct(
        InternalDomainService $domain_service,
        InternalGUIService $gui_service
    ) {
        $this->gui_service = $gui_service;
        $this->domain_service = $domain_service;
    }

    public function ownershipManagementGUI(?int $user_id = null, bool $read_only = false): \ilObjectOwnershipManagementGUI
    {
        return new \ilObjectOwnershipManagementGUI(
            $this->domain_service,
            $this->gui_service,
            $user_id,
            $read_only
        );
    }

    public function ownershipManagementTableBuilder(
        int $user_id,
        string $title,
        array $objects,
        string $selected_type,
        object $parent_gui,
        string $parent_cmd
    ): OwnershipManagementTableBuilder {
        return new OwnershipManagementTableBuilder(
            $this->domain_service,
            $this->gui_service,
            $user_id,
            $title,
            $objects,
            $selected_type,
            $parent_gui,
            $parent_cmd
        );
    }
}
