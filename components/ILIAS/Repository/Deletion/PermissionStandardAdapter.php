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
        protected TreeInterface $tree,
        protected \ilObjectDefinition $obj_definition
    ) {
    }

    public function checkAccess(string $operation, int $ref_id): bool
    {
        return $this->access->checkAccess($operation, "", $ref_id);
    }

    public function getRefIdsWithoutDeletePermission(array $ids): array
    {
        $not_deletable = [];
        $deactivated_plugin_types = [];
        foreach ($ids as $id) {
            if (!$this->access->checkAccess('delete', "", $id)) {
                $node_data = $this->tree->isInTree($id)
                    ? $this->tree->getNodeData($id)
                    : [];
                if ($this->isDeactivatedPlugin($node_data)) {
                    $deactivated_plugin_types[$node_data['type']][] = (int) $id;
                } else {
                    $not_deletable[] = (int) $id;
                }
            }

            if ($this->tree->isInTree($id)) {
                $node_data = $this->tree->getNodeData($id);
                $subtree_nodes = $this->tree->getSubTree($node_data);

                foreach ($subtree_nodes as $node) {
                    if ($node['type'] === 'rolf') {
                        continue;
                    }
                    if (!$this->access->checkAccess('delete', "", $node["child"])) {
                        if ($this->isDeactivatedPlugin($node)) {
                            $deactivated_plugin_types[$node['type']][] = (int) $node['child'];
                        } else {
                            $not_deletable[] = (int) $node["child"];
                        }
                    }
                }
            }
        }

        if ($deactivated_plugin_types !== []) {
            $plugin_messages = [];
            foreach ($deactivated_plugin_types as $type => $ref_ids) {
                $plugin_messages[] = $type . ' (' . implode(', ', array_unique($ref_ids)) . ')';
            }
            throw new MissingPermissionException(
                'Deletion: The following plugin types are deactivated: ' . implode(', ', $plugin_messages) .
                '. Please activate the listed plugin(s) before deleting their objects.'
            );
        }

        return array_unique($not_deletable);
    }

    protected function isDeactivatedPlugin(array $node): bool
    {
        $type = (string) ($node['type'] ?? '');
        return $this->obj_definition->isPluginTypeName($type) && !$this->obj_definition->isPlugin($type);
    }

    public function revokePermission(int $ref_id): void
    {
        $this->rbacadmin->revokePermission($ref_id);
    }
}
