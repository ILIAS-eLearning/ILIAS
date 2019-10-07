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
	 * @var ilLSItemsDB
	 */
	protected $items_db;
	/**
	 * @var ilAccess
	 */
	protected $access;

	public function __construct(
		ilLSItemsDB $items_db,
		ilAccess $access
	) {
		$this->items_db = $items_db;
		$this->access = $access;
	}
	/**
	 * Decorate LSItems with learning progress and availability (from conditions)
	 */
	public function getLearnerItems(int $usr_id, int $container_ref_id): array
	{
		$items =[];
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
		return (int)ilObject::_lookupObjId($ref_id);
	}

	protected function getLearningProgressFor(int $usr_id, LSItem $ls_item): int
	{
		$obj_id = $this->getObjIdForRefId($ls_item->getRefId());
		$il_lp_status = ilLPStatus::_lookupStatus($obj_id, $usr_id, true);
		return (int)$il_lp_status;
	}

	protected function isItemVisibleForUser(int $usr_id, LSItem $ls_item): bool
	{
		$online = $ls_item->isOnline();
		$access = $this->access->checkAccessOfUser(
			$usr_id, "visible", "", $ls_item->getRefId()
		);
		return ($online && $access);
	}

	protected function getAvailabilityFor(int $usr_id, LSItem $ls_item): int
	{
		$readable = $this->access->checkAccessOfUser($usr_id, 'read', '', $ls_item->getRefId());
		if($readable) {
			return ILIAS\UI\Component\Listing\Workflow\Step::AVAILABLE;
		}
		return ILIAS\UI\Component\Listing\Workflow\Step::NOT_AVAILABLE;
	}
}
