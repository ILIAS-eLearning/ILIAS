<?php declare(strict_types=1);

/**
 * This combines calls to ProgressDB and StateDB to handle learner-items
 * in the context of a specific LSObject and a specific user.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSLearnerItemsQueries
{
	public function __construct(
		ilLearnerProgressDB $progress_db,
		ilLSStateDB $states_db,
		int $ls_ref_id,
		int $usr_id
	) {
		$this->progress_db = $progress_db;
		$this->states_db = $states_db;
		$this->ls_ref_id = $ls_ref_id;
		$this->usr_id = $usr_id;
	}

	/**
	 * @return LSLearnerItems[]
	 */
	public function getItems(): array
	{
		return $this->progress_db->getLearnerItems($this->usr_id, $this->ls_ref_id);
	}

	public function getCurrentItemRefId(): int
	{
		$current = $this->states_db->getCurrentItemsFor($this->ls_ref_id, [$this->usr_id]);
		$ref_id = max(0, $current[$usr_id]); //0 or greater
		return $ref_id;
	}

	public function getCurrentItemPosition(): int
	{
		$current_position = 0;
		$items = $this->getItems();
		foreach ($items as $index=>$item) {
			if($item->getRefId() === $this->getCurrentItemRefId()) {
				$current_position = $index;
			}
		}
		return $current_position;
	}

	public function getStateFor(LSItem $ls_item): ILIAS\KioskMode\State
	{
		$states = $this->states_db->getStatesFor($this->ls_ref_id, [$this->usr_id]);
		if (array_key_exists($ls_item->getRefId(), $states)) {
			return $states[$ls_item->getRefId()];
		}
		return new ILIAS\KioskMode\State();
	}

	public function storeState(
		ILIAS\KioskMode\State $state,
		int $state_item_ref_id,
		int $current_item_ref_id
	) {
		$this->states_db->updateState(
			$this->ls_ref_id,
			$this->usr_id,
			$state_item_ref_id,
			$state,
			$current_item_ref_id
		);
	}

}
