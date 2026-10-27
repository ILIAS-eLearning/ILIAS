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
 * Represents a node in a learning map.
 */
final class LSOLearningMapNode
{
    /**
     * Creates a learning map node.
     *
     * @param int[] $successors
     * @param int[] $passable_successors
     * @param int[] $input_condition_ids
     * @param int[] $output_condition_ids
     */
    public function __construct(
        /** @var int The object ID. */
        public readonly int $obj_id,
        /** @var string The item title. */
        public readonly string $title,
        /** @var string The item description. */
        public readonly string $description,
        /** @var string The icon path. */
        public readonly string $icon,
        /** @var string|null The player link. */
        public readonly ?string $player_link,
        /** @var bool Whether the item can be accessed. */
        public readonly bool $can_access,
        /** @var bool Whether the item was visited. */
        public readonly bool $has_visited,
        /** @var bool Whether the item was completed. */
        public readonly bool $has_completed,
        /** @var bool Whether the item can be left. */
        public readonly bool $can_leave,
        /** @var string The item's situation. */
        public readonly string $situation,
        /** @var int[] The successor object IDs. */
        public readonly array $successors,
        /** @var int[] The passable successor object IDs. */
        public readonly array $passable_successors,
        /** @var int[] The input condition IDs. */
        public readonly array $input_condition_ids,
        /** @var int[] The output condition IDs. */
        public readonly array $output_condition_ids,
        /** @var int The number of visits. */
        public readonly int $visit_count,
        /** @var int|null The last visit timestamp. */
        public readonly ?int $last_visited_ts,
        /** @var bool Whether the item is current. */
        public readonly bool $is_current,
        /** @var bool Whether the item is on the walked path. */
        public readonly bool $is_on_walked_path,
        /** @var int The graph depth. */
        public readonly int $depth
    ) {
    }

    /**
     * Returns the node as an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(): array
    {
        return [
            'obj_id' => $this->obj_id,
            'title' => $this->title,
            'description' => $this->description,
            'icon' => $this->icon,
            'player_link' => $this->player_link,
            'can_access' => $this->can_access,
            'has_visited' => $this->has_visited,
            'has_completed' => $this->has_completed,
            'can_leave' => $this->can_leave,
            'situation' => $this->situation,
            'successors' => array_values($this->successors),
            'passable_successors' => array_values($this->passable_successors),
            'input_condition_ids' => array_values($this->input_condition_ids),
            'output_condition_ids' => array_values($this->output_condition_ids),
            'visit_count' => $this->visit_count,
            'last_visited_ts' => $this->last_visited_ts,
            'is_current' => $this->is_current,
            'is_on_walked_path' => $this->is_on_walked_path,
            'depth' => $this->depth
        ];
    }
}
