<?php

declare(strict_types=1);

/**
 * Handle LP-events.
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */

class ilLSLPEventHandler
{
    /**
     * @var ilTree
     */
    protected $tree;
    /**
     * @var ilLPStatusWrapper
     */
    protected $lpstatus;

    /**
     * @var array
     */
    protected $cached_parent_lso = [];
    /**
     * @var array
     */
    protected $cached_refs_for_obj = [];


    public function __construct(
        ilTree $tree,
        ilLPStatusWrapper $lp_status_wrapper
    ) {
        $this->tree = $tree;
        $this->lpstatus = $lp_status_wrapper;
    }

    public function updateLPForChildEvent(array $parameter)
    {
        $refs = $this->getRefIdsOfObjId((int) $parameter['obj_id']);
        foreach ($refs as $ref_id) {
            $lso_id = $this->getParentLSOObjId((int) $ref_id);
            if ($lso_id !== false) {
                $usr_id = $parameter['usr_id'];
                $this->lpstatus::_updateStatus($lso_id, $usr_id);
            }
        }
    }

    /**
     * get the LSO up from $child_ref_if
     * @return int | false;
     */
    protected function getParentLSOObjId(int $child_ref_id)
    {
        if (!array_key_exists($child_ref_id, $this->cached_parent_lso)) {
            $this->cached_parent_lso[$child_ref_id] = $this->getParentLSOIdFromTree($child_ref_id);
        }
        return $this->cached_parent_lso[$child_ref_id];
    }

    private function getParentLSOIdFromTree(int $child_ref_id)
    {
        $parent_nd = $this->tree->getParentNodeData($child_ref_id);
        if ($parent_nd['type'] === 'lso') {
            return $parent_nd['obj_id'];
        }
        return false;
    }

    protected function getRefIdsOfObjId(int $triggerer_obj_id) : array
    {
        if (!array_key_exists($triggerer_obj_id, $this->cached_refs_for_obj)) {
            $this->cached_refs_for_obj[$triggerer_obj_id] = ilObject::_getAllReferences($triggerer_obj_id);
        }
        return $this->cached_refs_for_obj[$triggerer_obj_id];
    }
}
