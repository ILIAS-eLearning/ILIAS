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

namespace ILIAS\Chatroom;

use ilRbacSystem;

class AccessBridge
{
    public function __construct(private readonly ilRbacSystem $rbac)
    {
    }

    public function checkAccess(
        string $a_permission,
        string $a_cmd,
        int $a_ref_id,
        string $a_type = "",
        ?int $a_obj_id = null,
        ?int $a_tree_id = null
    ): bool {
        return $this->rbac->checkAccess($a_permission, $a_ref_id);
    }
}
