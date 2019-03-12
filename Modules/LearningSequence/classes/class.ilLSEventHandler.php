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
		$obj_ref_id = $obj_deleted->getRefId();
		if(empty($obj_ref_id) || !$this->tree->isInTree($obj_ref_id)) {
			return;
		}
		$parent_lso = $this->getParentLSOInfo((int)$obj_ref_id);
		if($parent_lso) {
			$lso = $this->getInstanceByRefId((int)$parent_lso['ref_id']);
			$lso->getStateDB()->deleteForItem(
				(int)$parent_lso['ref_id'],
				(int)$obj_ref_id
			);
		}
	}

	public function handleParticipantDeletion(int $obj_id, int $usr_id)
	{
		$lso = $this->getInstanceByObjId($obj_id);
		$db = $lso->getStateDB();
		$db->deleteFor($lso->getRefId(), [$usr_id]);
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

	protected function getInstanceByObjId(int $obj_id): ilObjLearningSequence
	{
		$refs = \ilObject::_getAllReferences($obj_id);
		$ref_id = array_shift(array_keys($refs));
		return $this->getInstanceByRefId($ref_id);
	}

}
