<?php

declare(strict_types=1);

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

/**
 * Get LearningProgress and availability of items in sequence.
 */
class ilLearnerProgressDB
{
    protected ilLSItemsDB $items_db;
    protected ilAccess $access;
    protected ilObjectDataCache $obj_data_cache;

    public function __construct(
        ilLSItemsDB $items_db,
        ilAccess $access,
        ilObjectDataCache $obj_data_cache
    ) {
        $this->items_db = $items_db;
        $this->access = $access;
        $this->obj_data_cache = $obj_data_cache;
    }

    /**
     * Decorate LSItems with learning progress and availability (from conditions)
     *
     * @return LSLearnerItem[]|[]
     */
    public function getLearnerItems(int $usr_id, int $container_ref_id): array
    {
        $items = [];
        $ls_items = $this->items_db->getLSItems($container_ref_id);

        foreach ($ls_items as $ls_item) {
            if ($this->isItemVisibleForUser($usr_id, $ls_item) === false) {
                continue;
            }
            $lp = $this->getLearningProgressFor($usr_id, $ls_item);
            $av = $this->getAvailabilityFor($usr_id, $ls_item);
            $items[] = new LSLearnerItem($usr_id, $lp, $av, $ls_item);
        }

        return $items;
    }

    protected function getObjIdForRefId(int $ref_id): int
    {
        return ilObject::_lookupObjId($ref_id);
    }

    protected function getLearningProgressFor(int $usr_id, LSItem $ls_item): int
    {
        $obj_id = $this->getObjIdForRefId($ls_item->getRefId());

        $il_lp_status = ilLPStatus::LP_STATUS_NOT_ATTEMPTED_NUM;
        if (ilObjectLP::isSupportedObjectType($this->obj_data_cache->lookupType($obj_id))) {
            $il_lp_status = ilLPStatus::_lookupStatus($obj_id, $usr_id, true);
        }
        return (int) $il_lp_status;
    }

    protected function isItemVisibleForUser(int $usr_id, LSItem $ls_item): bool
    {
        $online = $ls_item->isOnline();
        $access = $this->access->checkAccessOfUser(
            $usr_id,
            "visible",
            "",
            $ls_item->getRefId()
        );
        return ($online && $access);
    }

    protected function getAvailabilityFor(int $usr_id, LSItem $ls_item): int
    {
        $this->access->clear(); //clear access cache; condition-checks refer to previous state otherwise.
        $readable = $this->access->checkAccessOfUser($usr_id, 'read', '', $ls_item->getRefId());
        if ($readable) {
            return ILIAS\UI\Component\Listing\Workflow\Step::AVAILABLE;
        }
        return ILIAS\UI\Component\Listing\Workflow\Step::NOT_AVAILABLE;
    }
}
