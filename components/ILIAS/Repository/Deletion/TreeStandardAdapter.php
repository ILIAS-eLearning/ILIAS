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

use ILIAS\Repository\InternalRepoService;

class TreeStandardAdapter implements TreeInterface
{
    public function __construct(
        protected InternalRepoService $repo,
        protected \ilTree $tree,
        protected int $user_id
    ) {
    }
    public function isDeleted(int $node_id): bool
    {
        return $this->tree->isDeleted($node_id);
    }
    public function useCache(bool $use): void
    {
        $this->tree->useCache($use);
    }
    public function getNodeData(int $id): array
    {
        return $this->tree->getNodeData($id);
    }
    public function getSubTree(array $node): array
    {
        return $this->tree->getSubTree($node);
    }

    public function moveToTrash(int $ref_id): bool
    {
        return $this->tree->moveToTrash($ref_id, true, $this->user_id);
    }

    /**
     * @return int[]
     */
    public function getDeletedTreeNodeIds(array $ids): array
    {
        $deleted_ids = [];
        foreach ($ids as $id) {
            if ($this->tree->isDeleted($id)) {
                $deleted_ids[] = $id;
            }
        }
        return $deleted_ids;
    }

    public function getTree(int $tree_id): TreeInterface
    {
        return new self(new \ilTree($tree_id), $this->user_id);
    }

    public function getTrashTree(int $ref_id): TreeInterface
    {
        $trees = \ilTree::lookupTreesForNode($ref_id);
        $tree_id = end($trees);

        if ($tree_id) {
            if ($tree_id >= 0) {
                throw new NotInTrashException('Trying to delete node from trash, but node is not in trash: ' . $ref_id);
            }
            return new self(new \ilTree($tree_id), $this->user_id);
        }
        throw new NotInTrashException('Trying to delete node from trash, but no valid tree id found for node id: ' . $ref_id);
    }

    public function deleteTree(array $node_data): void
    {
        $this->tree->deleteTree($node_data);
    }

    /**
     * Get (negative) tree ids of trashed children
     * @return int[]
     */
    public function getTrashedSubtrees(int $ref_id): array
    {
        $tree_repo = $this->repo->tree();
        return $tree_repo->getTrashedSubtrees($ref_id);
    }
}
