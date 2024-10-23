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

interface EventInterface
{
    public function beforeMoveToTrash(int $ref_id, array $subnodes): void;
    public function afterMoveToTrash(int $ref_id, int $old_parent_ref_id): void;
    public function beforeSubtreeRemoval(int $obj_id): void;

    public function beforeObjectRemoval(
        int $obj_id,
        int $ref_id,
        string $type,
        string $title
    ): void;

    public function failedRemoval(
        int $obj_id,
        int $ref_id,
        string $type,
        string $title,
        string $message
    ): void;

    public function afterObjectRemoval(
        int $obj_id,
        int $ref_id,
        string $type,
        int $old_parent_ref_id
    ): void;


    public function afterTreeDeletion(
        int $tree_id,
        int $child
    ): void;
}
