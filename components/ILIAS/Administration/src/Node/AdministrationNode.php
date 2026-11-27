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
 * A node of the administration, contributed by a component via
 * $contribute[AdministrationNode::class]. The node is an object of the given
 * object type below the administration (SYSTEM_FOLDER_ID), so permissions are
 * handled by RBAC. The object type must be contributed as ObjectType with the
 * flags ADMINISTRATION and SYSTEM and the parent type "adm".
 */
interface AdministrationNode
{
    public function getObjectType(): string;

    public function getGroup(): Group;

    /**
     * Position within the group, lower first.
     */
    public function getPosition(): int;
}
