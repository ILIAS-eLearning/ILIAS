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

interface TreeInterface
{
    // standard
    public function isDeleted(int $a_node_id): bool;

    public function useCache(bool $a_use): void;

    public function getNodeData(int $id): array;
    public function getSubTree(array $node): array;

    // custom
    public function getDeletedTreeNodeIds(array $ids): array;

    public function getTree(int $tree_id): TreeInterface;

    public function getTrashTree(int $ref_id): TreeInterface;

    public function deleteTree(array $node_data): void;

    public function moveToTrash(int $ref_id): bool;

    /**
     * @return int[]
     */
    public function getTrashedSubtrees(int $ref_id): array;

}
