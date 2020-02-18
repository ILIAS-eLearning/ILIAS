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
            $lso_info = $this->getParentLSO((int) $ref_id);
            if ($lso_info !== false) {
                $obj_id = $lso_info['obj_id'];
                $usr_id = $parameter['usr_id'];
                $this->lpstatus::_refreshStatus($obj_id, [$usr_id]);
            }
        }
    }

    /**
     * get the LSO up from $child_ref_if
     * @return int | false;
     */
    protected function getParentLSO(int $child_ref_id)
    {
        $path = $this->tree->getPathFull($child_ref_id);
        if (!$path) {
            return false;
        }

        foreach ($path as $hop) {
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
}
