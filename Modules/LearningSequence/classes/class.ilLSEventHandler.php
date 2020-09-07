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
        $obj_ref_id = (int) $obj_deleted->getRefId();

        if (!$this->isExistingObject($obj_ref_id)) {
            return;
        }

        $parent_lso = $this->getParentLSOInfo($obj_ref_id);
        if ($parent_lso) {
            $this->deleteLSOItem($obj_ref_id, (int) $parent_lso['ref_id']);
        }
    }

    /**
     * @param  array  $parameter [obj_id, ref_id, old_parent_ref_id]
     */
    public function handleObjectToTrash(array $parameter)
    {
        $obj_ref_id = (int) $parameter['ref_id'];
        $old_parent_ref_id = (int) $parameter['old_parent_ref_id'];
        $parent_lso = $this->getParentLSOInfo($obj_ref_id);

        if (!$this->isExistingObject($obj_ref_id) || !$parent_lso) {
            return;
        }

        if ($old_parent_ref_id) {
            $this->deleteLSOItem($obj_ref_id, $old_parent_ref_id);
        }
    }

    protected function isExistingObject(int $ref_id) : bool
    {
        if (empty($ref_id) || !$this->tree->isInTree($ref_id)) {
            return false;
        }
        return true;
    }

    protected function deleteLSOItem(int $obj_ref_id, int $parent_lso_ref_id)
    {
        $lso = $this->getInstanceByRefId($parent_lso_ref_id);
        $lso->getStateDB()->deleteForItem(
            $parent_lso_ref_id,
            $obj_ref_id
        );
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

    protected function getRefIdsOfObjId(int $triggerer_obj_id) : array
    {
        return ilObject::_getAllReferences($triggerer_obj_id);
    }

    protected function getInstanceByRefId(int $ref_id) : ilObject
    {
        return ilObjectFactory::getInstanceByRefId($ref_id);
    }

    protected function getInstanceByObjId(int $obj_id) : ilObjLearningSequence
    {
        $refs = \ilObject::_getAllReferences($obj_id);
        $ref_id = array_shift(array_keys($refs));
        return $this->getInstanceByRefId($ref_id);
    }
}
