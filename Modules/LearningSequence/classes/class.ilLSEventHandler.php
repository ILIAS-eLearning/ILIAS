<?php

declare(strict_types=1);

/**
 * Handle events.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ilLSEventHandler
{
	/**
	 * @var ilTree
	 */
	protected $tree;

	public function __construct(ilTree $tree)
	{
		$this->tree = $tree;
	}

	/**
	 * Find out, if a subobject is about to be deleted.
	 * cleanup states.
	 */
	public function handleObjectDeletion(array $parameter)
	{
		$obj_deleted = $parameter['object'];
		$parent_lso = $this->getParentLSOInfo((int)$obj_deleted->getRefId());
		if ($parent_lso !== false) {
			$lso = $this->getInstanceByRefId((int)$parent_lso['ref_id']);
			$lso->getStateDB()->deleteForItem(
				(int)$parent_lso['ref_id'],
				(int)$obj_deleted->getRefId()
			);
		}
	}


	/**
	 * get the LSO up from $child_ref_if
	 * @return int | false;
	 */
	protected function getParentLSOInfo(int $child_ref_id)
	{
		foreach ($this->tree->getPathFull($child_ref_id) as $hop) {
			if ($hop['type'] === 'lso') {
				return $hop;
			}
		}
		return false;
	}

	protected function getRefIdsOfObjId(int $triggerer_obj_id): array
	{
		return ilObject::_getAllReferences($triggerer_obj_id);
	}

	protected function getInstanceByRefId(int $ref_id): ilObjLearningSequence
	{
		return ilObjectFactory::getInstanceByRefId($ref_id);
	}

}
