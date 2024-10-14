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

namespace ILIAS\Repository\Deletion;

class PermissionStandardAdapter implements PermissionInterface
{
    public function __construct(
        protected \ilAccess $access,
        protected \ilRbacAdmin $rbacadmin,
        protected TreeInterface $tree
    ) {
    }

    public function checkAccess(string $operation, int $ref_id): bool
    {
        return $this->access->checkAccess($operation, "", $ref_id);
    }

    public function getRefIdsWithoutDeletePermission(array $ids): array
    {
        $not_deletable = [];
        foreach ($ids as $id) {
            if (!$this->access->checkAccess('delete', "", $id)) {
                $not_deletable[] = (int) $id;
            }

            $node_data = $this->tree->getNodeData($id);
            $subtree_nodes = $this->tree->getSubTree($node_data);

            foreach ($subtree_nodes as $node) {
                if ($node['type'] === 'rolf') {
                    continue;
                }
                if (!$this->access->checkAccess('delete', "", $node["child"])) {
                    $not_deletable[] = (int) $node["child"];
                }
            }
        }
        return $not_deletable;
    }

    public function revokePermission(int $ref_id): void
    {
        $this->rbacadmin->revokePermission($ref_id);
    }
}
