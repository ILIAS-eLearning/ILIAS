<?php declare(strict_types=1);

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
 * Handle LP-events.
 */
class ilLSLPEventHandler
{
    protected ilTree $tree;
    protected ilLPStatusWrapper $lpstatus;
    protected array $cached_parent_lso = [];
    protected array $cached_refs_for_obj = [];

    public function __construct(
        ilTree $tree,
        ilLPStatusWrapper $lp_status_wrapper
    ) {
        $this->tree = $tree;
        $this->lpstatus = $lp_status_wrapper;
    }

    public function updateLPForChildEvent(array $parameter) : void
    {
        $refs = $this->getRefIdsOfObjId((int) $parameter['obj_id']);
        foreach ($refs as $ref_id) {
            $lso_id = $this->getParentLSOObjId((int) $ref_id);
            if ($lso_id !== null) {
                $usr_id = $parameter['usr_id'];
                $this->lpstatus::_updateStatus($lso_id, $usr_id);
            }
        }
    }

    /**
     * get the LSO up from $child_ref_if
     */
    protected function getParentLSOObjId(int $child_ref_id) : ?int
    {
        if (!array_key_exists($child_ref_id, $this->cached_parent_lso)) {
            $this->cached_parent_lso[$child_ref_id] = $this->getParentLSOIdFromTree($child_ref_id);
        }
        return $this->cached_parent_lso[$child_ref_id];
    }

    private function getParentLSOIdFromTree(int $child_ref_id) : ?int
    {
        $parent_nd = $this->tree->getParentNodeData($child_ref_id);
        if ($parent_nd['type'] === 'lso') {
            return (int) $parent_nd['obj_id'];
        }
        return null;
    }

    /**
     * @return array<int|string>
     */
    protected function getRefIdsOfObjId(int $triggerer_obj_id) : array
    {
        if (!array_key_exists($triggerer_obj_id, $this->cached_refs_for_obj)) {
            $this->cached_refs_for_obj[$triggerer_obj_id] = ilObject::_getAllReferences($triggerer_obj_id);
        }
        return $this->cached_refs_for_obj[$triggerer_obj_id];
    }
}
