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
 * All administration nodes contributed by the components.
 */
class AdministrationNodes
{
    /**
     * @var array<string, AdministrationNode>|null
     */
    private ?array $nodes = null;

    /**
     * @param AdministrationNode[] $nodes
     */
    public function __construct(
        private readonly array $contributed_nodes
    ) {
    }

    /**
     * @return array<string, AdministrationNode> object type => node
     */
    public function getAll(): array
    {
        if ($this->nodes !== null) {
            return $this->nodes;
        }
        $nodes = [];
        foreach ($this->contributed_nodes as $node) {
            if (isset($nodes[$node->getObjectType()])) {
                throw new \LogicException(
                    "Administration node '{$node->getObjectType()}' is contributed more than once."
                );
            }
            $nodes[$node->getObjectType()] = $node;
        }
        return $this->nodes = $nodes;
    }

    public function has(string $object_type): bool
    {
        return isset($this->getAll()[$object_type]);
    }

    /**
     * Nodes per group, in the order of the groups and sorted by position.
     *
     * @return array<string, AdministrationNode[]> group => nodes
     */
    public function getGrouped(): array
    {
        $grouped = [];
        foreach (Group::cases() as $group) {
            $nodes = array_values(array_filter(
                $this->getAll(),
                static fn(AdministrationNode $node): bool => $node->getGroup() === $group
            ));
            usort(
                $nodes,
                static fn(AdministrationNode $a, AdministrationNode $b): int => $a->getPosition() <=> $b->getPosition()
            );
            if ($nodes !== []) {
                $grouped[$group->value] = $nodes;
            }
        }
        return $grouped;
    }
}
