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

namespace ILIAS\MediaPool;

class MediaPoolManager
{
    public function __construct(
        protected InternalDomainService $domain,
        protected int $obj_id
    ) {
    }

    public function copySelectedFromEditClipboard(int $target_id): void
    {
        $ids = \ilEditClipboardGUI::_getSelectedIDs();
        foreach ($ids as $id) {
            $this->copyItemFromEditClipboard($id, $target_id);
        }
    }

    public function copyItemFromEditClipboard(string $insert_id, int $target_id): void
    {
        $id = explode(":", $insert_id);
        $type = $id[0];
        $id = (int) $id[1];

        if ($type === "mob") {		// media object
            if (!\ilObjMediaPool::isForeignIdInTree($this->obj_id, $id)) {
                $item = new \ilMediaPoolItem();
                $item->setType("mob");
                $item->setForeignId($id);
                $item->setTitle(\ilObject::_lookupTitle($id));
                $item->create();
                if ($item->getId() > 0) {
                    $this->domain->tree($this->obj_id)->insertInMepTree($item->getId(), $target_id);
                }
            }
        }
        if ($type === "incl") {		// content snippet
            if (!\ilObjMediaPool::isItemIdInTree($this->obj_id, $id)) {
                $original = new \ilMediaPoolPage($id);

                // copy the page into the pool
                $item = new \ilMediaPoolItem();
                $item->setType("pg");
                $item->setTitle(\ilMediaPoolItem::lookupTitle($id));
                $item->create();
                if ($item->getId() > 0) {
                    $this->domain->tree($this->obj_id)->insertInMepTree($item->getId(), $target_id);

                    // create page
                    $page = new \ilMediaPoolPage();
                    $page->setId($item->getId());
                    $page->setParentId($this->obj_id);
                    $page->create(false);

                    // copy content
                    $original->copy($page->getId(), $page->getParentType(), $page->getParentId(), true);


                    // copy adv metadata
                    $pool_ids = \ilMediaPoolItem::getPoolForItemId($id);
                    if (count($pool_ids) === 1) {
                        $source_pool_id = current($pool_ids);
                        $this->copyMetadataOfItem(
                            $source_pool_id,
                            $this->obj_id,
                            $id,
                            $page->getId()
                        );
                    }
                }
            }
        }
    }

    // move action
    public function pasteFromClipboard(int $target_folder_id): void
    {
        $target_tree = $this->domain->tree($this->obj_id);

        // sanity check
        $move_ids = \ilSession::get("mep_move_ids");
        if (is_array($move_ids)) {
            foreach ($move_ids as $id) {
                $pool_ids = \ilMediaPoolItem::getPoolForItemId($id);

                if (!in_array(\ilMediaPoolItem::lookupType($target_folder_id), ["fold", "dummy"])) {
                    throw new InvalidTargetException("Invalid target " . $target_folder_id .
                     " (" . \ilMediaPoolItem::lookupType($target_folder_id) . ")");
                }
                if ($this->isTargetWithinSource($id, $target_folder_id)) {
                    throw new InvalidTargetException("Invalid target " . $target_folder_id .
                        " (" . \ilMediaPoolItem::lookupType($target_folder_id) . ")");
                }

                $subnodes = [];
                $source_pool_id = 0;
                foreach ($pool_ids as $pool_id) {
                    $source_pool_id = $pool_id;
                    $source_tree = $this->domain->tree($pool_id);
                    $subnodes = $source_tree->getSubtree($source_tree->getNodeData($id));
                    $source_tree->deleteTree($source_tree->getNodeData($id));
                }

                $target_tree->insertNode($id, $target_folder_id);
                $this->copyMetadataOfItem(
                    $source_pool_id,
                    $this->obj_id,
                    $id,
                    $id
                );
                foreach ($subnodes as $node) {
                    if ($node["child"] != $id) {
                        $target_tree->insertNode($node["child"], $node["parent"]);
                        $this->copyMetadataOfItem(
                            $source_pool_id,
                            $this->obj_id,
                            (int) $node["child"],
                            (int) $node["child"]
                        );
                    }
                }
            }
        }
        \ilSession::clear("mep_move_ids");
    }

    protected function copyMetadataOfItem(
        int $source_pool_id,
        int $target_pool_id,
        int $source_child_id,
        int $target_child_id
    ): void {
        if (\ilMediaPoolItem::lookupType($source_child_id) === "pg") {
            \ilAdvancedMDValues::_cloneValues(
                0,
                $source_pool_id,
                $target_pool_id,
                "mpg",
                $source_child_id,
                $target_child_id
            );

            $md = new \ilMD($source_pool_id, $source_child_id, "mpg");
            $new_md = $md->cloneMD($target_pool_id, $target_child_id, "mpg");
        }
    }

    public function isTargetWithinSource(int $source_id, int $target_folder_id): bool
    {
        $pool_ids = \ilMediaPoolItem::getPoolForItemId($source_id);

        /*
        $parent_id = $this->mep_request->getItemId();
        if (ilMediaPoolItem::lookupType($parent_id) !== "fold") {
            $parent_id = $target_tree->readRootId();
        }*/

        $subnodes = [];
        foreach ($pool_ids as $pool_id) {
            $source_tree = $this->domain->tree($pool_id);

            // if source tree == target tree, check if target is within source tree
            $subnodes = $source_tree->getSubtree($source_tree->getNodeData($source_id));
            foreach ($subnodes as $subnode) {
                if ((int) $subnode["child"] === (int) $target_folder_id) {
                    return true;
                }
            }
        }
        return false;
    }
}
