<?php

declare(strict_types=1);

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
* @ilCtrl_Calls ilObjStudyProgrammeReferenceGUI: ilPermissionGUI, ilInfoScreenGUI, ilPropertyFormGUI
*/
class ilObjStudyProgrammeReferenceGUI extends ilContainerReferenceGUI
{
    public function __construct(
        $data,
        int $id,
        bool $call_by_reference = true,
        bool $prepare_output = false
    ) {
        $this->target_type = 'prg';
        $this->reference_type = 'prgr';
        parent::__construct($data, $id, $call_by_reference, $prepare_output);
    }

    public static function _goto(int $target): void
    {
        $target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId($target));
        ilObjStudyProgrammeGUI::_goto($target_ref_id . "_");
    }

    public function saveObject(): void
    {
        $ilAccess = $this->access;

        if (!(int) $_REQUEST['target_id']) {
            $this->createObject();
        }
        if (!$ilAccess->checkAccess('visible', '', (int) $_REQUEST['target_id'])) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('permission_denied'));
            $this->createObject();
        }
        if ($this->tryingToCreateCircularReference((int) $_REQUEST['target_id'], (int) $_REQUEST['ref_id'])) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('prgr_may_not_create_circular_reference'));
            $this->createObject();
        }
        parent::saveObject();
    }

    public function putObjectInTree(ilObject $obj, $parent_node_id = null): void
    {
        // when this is called, the target already should be defined...
        $target_obj_id = ilObject::_lookupObjId((int) $this->form->getInput('target_id'));
        $obj->setTargetId($target_obj_id);
        $obj->update();
        parent::putObjectInTree($obj, $parent_node_id);
    }

    protected function tryingToCreateCircularReference(int $obj_to_be_referenced, int $reference_position): bool
    {
        if ($reference_position === $obj_to_be_referenced) {
            return true;
        }
        $queque = [$reference_position];
        while ($parent = array_shift($queque)) {
            $p_parent = (int) $this->tree->getParentId($parent);
            if ($p_parent === $obj_to_be_referenced) {
                return true;
            }
            if (ilObject::_lookupType($p_parent, true) === 'prg') {
                $queque[] = $p_parent;
            }
            foreach (ilContainerReference::_lookupSourceIds(ilObject::_lookupObjId($parent)) as $parent_ref_obj_id) {
                $ref_ids = ilObject::_getAllReferences($parent_ref_obj_id);
                $parent_ref_ref_id = (int) array_shift($ref_ids);
                $parent_ref_loc = (int) $this->tree->getParentId($parent_ref_ref_id);
                if ($parent_ref_loc === $obj_to_be_referenced) {
                    return true;
                }
                if (ilObject::_lookupType($parent_ref_loc, true) === 'prg') {
                    $queque[] = $parent_ref_loc;
                }
            }
        }
        return false;
    }
}
