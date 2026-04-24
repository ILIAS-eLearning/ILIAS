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

namespace ILIAS\AccessControl\Tree;

/**
 * @internal
 */
class RepositoryTreeAccessProxy
{

    private function tree():\ilTree
    {
        global $DIC;
        return $DIC->repositoryTree();
    }
    public function isInTree(?int $ref_id): bool
    {
        return $this->tree()->isInTree($ref_id);
    }

    public function isDeleted(int $ref_id): bool
    {
        return $this->tree()->isDeleted($ref_id);
    }

    public function getPathId(int $a_endnode_id, int $a_startnode_id = 0): array
    {
        return $this->tree()->getPathId($a_endnode_id, $a_startnode_id);
    }

    public function getSubTreeIds(int $a_ref_id): array
    {
        return $this->tree()->getSubTreeIds($a_ref_id);
    }

}
