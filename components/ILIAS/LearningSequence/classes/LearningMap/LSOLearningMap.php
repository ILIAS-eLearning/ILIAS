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

namespace ILIAS\LearningSequence\LearningMap;

/**
 * Represents the data required to render a learning map.
 */
final class LSOLearningMap
{
    /**
     * Creates a learning map.
     *
     * @param array<int, LSOLearningMapNode> $nodes
     */
    public function __construct(
        /** @var int The learning sequence object ID. */
        public readonly int $lso_obj_id,
        /** @var int The user ID. */
        public readonly int $usr_id,
        /** @var int The view mode. */
        public readonly int $mode,
        /** @var int The start object ID. */
        public readonly int $start_obj_id,
        /** @var int The end object ID. */
        public readonly int $end_obj_id,
        /** @var array<int, LSOLearningMapNode> The map nodes. */
        public readonly array $nodes
    ) {
    }

    /**
     * Returns the node for an object.
     */
    public function getNode(int $obj_id): ?LSOLearningMapNode
    {
        return $this->nodes[$obj_id] ?? null;
    }

    /**
     * Returns the learning map as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'lso_obj_id' => $this->lso_obj_id,
            'usr_id' => $this->usr_id,
            'mode' => $this->mode,
            'start_obj_id' => $this->start_obj_id,
            'end_obj_id' => $this->end_obj_id,
            'nodes' => array_values(array_map(
                static fn(LSOLearningMapNode $node): array => $node->toArray(),
                $this->nodes
            ))
        ];
    }
}
