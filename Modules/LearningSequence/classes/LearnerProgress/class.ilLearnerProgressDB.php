<?php

declare(strict_types=1);

/**
 * Get LearningProgress and availability of items in sequence.
 *
 * @author Daniel Weise <daniel.weise@concepts-and-training.de>
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLearnerProgressDB
{
    /**
     * @var ilLSStateDB
     */
    protected $state_db;

    public function __construct(ilLSStateDB $state_db, ilAccess $access)
    {
        $this->state_db = $state_db;
        $this->access = $access;
    }

    /**
     * Decorate LSItems with learning progress, availability (from conditions)
     * kiosk-mode state information.
     */
    public function getLearnerItems(int $usr_id, int $container_ref_id, array $ls_items) : array
    {
        $items =[];
        $states = $this->state_db->getStatesFor($container_ref_id, [$usr_id]);
        foreach ($ls_items as $ls_item) {
            if ($this->isItemVisibleForUser($usr_id, $ls_item) === false) {
                continue;
            }
            $lp = $this->getLearningProgressFor($usr_id, $ls_item);
            $av = $this->getAvailabilityFor($usr_id, $ls_item);
            $state = $this->getStateFor($ls_item, $states[$usr_id]);
            $items[] = new LSLearnerItem($usr_id, $lp, $av, $state, $ls_item);
        }

        return $items;
    }

    protected function getObjIdForRefId(int $ref_id) : int
    {
        return (int) ilObject::_lookupObjId($ref_id);
    }

    protected function getStateFor(LSItem $ls_item, array $states) : ILIAS\KioskMode\State
    {
        if (array_key_exists($ls_item->getRefId(), $states)) {
            return $states[$ls_item->getRefId()];
        }

        return new ILIAS\KioskMode\State();
    }

    protected function getLearningProgressFor(int $usr_id, LSItem $ls_item) : int
    {
        $obj_id = $this->getObjIdForRefId($ls_item->getRefId());
        $il_lp_status = ilLPStatus::_lookupStatus($obj_id, $usr_id, true);

        return (int) $il_lp_status;
    }

    protected function isItemVisibleForUser(int $usr_id, LSItem $ls_item) : bool
    {
        $online = $ls_item->isOnline();
        $access = $this->access->checkAccessOfUser(
            $usr_id,
            "visible",
            "",
            $ls_item->getRefId()
        );

        if (!($online && $access)) {
            return false;
        }

        return true;
    }

    protected function getAvailabilityFor(int $usr_id, LSItem $ls_item) : int
    {
        $readable = $this->access->checkAccessOfUser($usr_id, 'read', '', $ls_item->getRefId());
        if ($readable) {
            return ILIAS\UI\Component\Listing\Workflow\Step::AVAILABLE;
        }

        return ILIAS\UI\Component\Listing\Workflow\Step::NOT_AVAILABLE;
    }
}
