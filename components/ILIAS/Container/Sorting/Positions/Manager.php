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

namespace ILIAS\Container\Sorting\Positions;

use ILIAS\Container\InternalRepoService;
use ILIAS\Container\InternalDomainService;
use ILIAS\Container\InternalDataService;
use Generator;
use ilObject;
use ilCopyWizardOptions;

class Manager
{
    public function __construct(
        protected InternalDataService $data,
        protected InternalRepoService $repo,
        protected InternalDomainService $domain
    ) {
    }

    /**
     * @return Grouping[]
     */
    public function getPositionsInObject(int $obj_id): Generator
    {
        yield from $this->repo->sorting()->positions()->getPositions($obj_id);
    }

    public function clonePositions(
        int $obj_id,
        int $target_ref_id,
        int $copy_id
    ): void {
        $logger = $this->domain->logger()->cont();
        $logger->debug("Cloning container sorting.");

        $target_obj_id = ilObject::_lookupObjId($target_ref_id);
        $mappings = ilCopyWizardOptions::_getInstance($copy_id)->getMappings();

        $logger->debug("Read container_sorting for obj_id = " . $obj_id);

        $groupings = $this->repo->sorting()->positions()->getPositions($obj_id);

        foreach ($groupings as $grouping) {
            $new_parent_id = 0;
            if ($grouping->getParentID()) {
                // see bug #20347
                // at least in the case of sessions and item groups parent_ids in container sorting are object IDs but $mappings store references
                if (in_array($grouping->getParentType(), ["sess", "itgr"])) {
                    $par_refs = ilObject::_getAllReferences($grouping->getParentID());
                    $par_ref_id = current($par_refs);			// should be only one
                    $logger->debug("Got ref id: " . $par_ref_id . " for obj_id " . $grouping->getParentID() . " map ref id: " . ($mappings[$par_ref_id] ?? "") . ".");
                    if (isset($mappings[$par_ref_id])) {
                        $new_parent_ref_id = (int) $mappings[$par_ref_id];
                        $new_parent_id = ilObject::_lookupObjectId($new_parent_ref_id);
                    }
                } else {		// not sure if this is still used for other cases that expect ref ids
                    (int) $new_parent_id = $mappings[$grouping->getParentID()];
                }
                if ((int) $new_parent_id === 0) {
                    $logger->debug("No mapping found for parent id:" . $grouping->getParentID());
                    continue;
                }
            }

            foreach ($grouping->getPositions() as $position) {
                if (!isset($mappings[$position->getChildID()]) || !$mappings[$position->getChildID()]) {
                    $logger->debug("No mapping found for child id:" . $position->getChildID());
                    continue;
                }

                $this->repo->sorting()->positions()->deletePositionsForChild(
                    $target_obj_id,
                    $mappings[$position->getChildID()],
                    $new_parent_id
                );
                $this->repo->sorting()->positions()->savePositionForChild(
                    $target_obj_id,
                    $mappings[$position->getChildID()],
                    $position->getPosition(),
                    $grouping->getParentType(),
                    $new_parent_id
                );
            }
        }
    }

    public function savePositionForChild(
        int $obj_id,
        int $child_id,
        int $position,
        string $parent_type,
        int $parent_id
    ): void {
        if (!$parent_id) {
            $parent_type = '';
        }
        $this->repo->sorting()->positions()->savePositionForChild(
            $obj_id,
            $child_id,
            $position,
            $parent_type,
            $parent_id
        );
    }

    /**
     * @param array $type_positions positions e.g array(crs => array(1,2,3),'lres' => array(3,5,6))
     */
    public function saveFromPost(int $obj_id, array $type_positions): void
    {
        $items = [];
        foreach ($type_positions as $key => $position) {
            if (!is_array($position)) {
                $items[$key] = ((float) $position) * 100;
            } else {
                foreach ($position as $parent_id => $sub_items) {
                    $this->saveSubItemsFromPost($obj_id, $key, $parent_id, $sub_items ?: []);
                }
            }
        }

        if (!count($items)) {
            $this->saveItemsFromPost($obj_id, []);
            return;
        }

        asort($items);
        $new_indexed = [];
        $position = 0;
        foreach ($items as $key => $null) {
            $new_indexed[$key] = ++$position;
        }

        $this->saveItemsFromPost($obj_id, $new_indexed);
    }

    protected function saveItemsFromPost(int $obj_id, array $items): void
    {
        foreach ($items as $child_id => $position) {
            $this->savePositionForChild(
                $obj_id,
                (int) $child_id,
                (int) $position,
                '',
                0
            );
        }
    }

    protected function saveSubItemsFromPost(
        int $obj_id,
        string $parent_type,
        int $parent_id,
        array $items
    ): void {
        foreach ($items as $child_id => $position) {
            $this->savePositionForChild(
                $obj_id,
                (int) $child_id,
                (int) $position,
                $parent_type,
                $parent_id
            );
        }
    }
}
