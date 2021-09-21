<?php declare(strict_types=1);

/* Copyright (c) 2021 - Nils Haagen <nils.haagen@concepts-and-training.de> - Extended GPL, see LICENSE */

/**
 * Handle LP-events.
 */
class ilLSLPEventHandler
{
    protected ilTree $tree;
    protected ilLPStatusWrapper $lpstatus;

    public function __construct(ilTree $tree, ilLPStatusWrapper $lp_status_wrapper)
    {
        $this->tree = $tree;
        $this->lpstatus = $lp_status_wrapper;
    }

    public function updateLPForChildEvent(array $parameter) : void
    {
        $refs = $this->getRefIdsOfObjId((int) $parameter['obj_id']);
        foreach ($refs as $ref_id) {
            $lso_info = $this->getParentLSO((int) $ref_id);
            if (!is_null($lso_info)) {
                $obj_id = $lso_info['obj_id'];
                $usr_id = $parameter['usr_id'];
                $this->lpstatus::_refreshStatus($obj_id, [$usr_id]);
            }
        }
    }

    /**
     * get the LSO up from $child_ref_if
     */
    protected function getParentLSO(int $child_ref_id) : ?array
    {
        $path = $this->tree->getPathFull($child_ref_id);
        if (!$path) {
            return null;
        }

        foreach ($path as $hop) {
            if ($hop['type'] === 'lso') {
                return $hop;
            }
        }
        return null;
    }

    /**
     * @return array<int|string>
     */
    protected function getRefIdsOfObjId(int $triggerer_obj_id) : array
    {
        return ilObject::_getAllReferences($triggerer_obj_id);
    }
}
