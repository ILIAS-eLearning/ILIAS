<?php

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

declare(strict_types=1);

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
        $this->lng->loadLanguageModule('prg');
    }

    public static function _goto(string $target): void
    {
        global $DIC;
        $access = $DIC['ilAccess'];
        if ($access->checkAccess('write', '', (int) $target)) {
            $ilCtrl = $DIC['ilCtrl'];
            $ilCtrl->setTargetScript('ilias.php');
            $ilCtrl->setParameterByClass(self::class, "ref_id", $target);
            $ilCtrl->redirectByClass([ilRepositoryGUI::class, self::class], "view");
        } else {
            $target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId((int) $target));
            ilObjStudyProgrammeGUI::_goto((string) $target_ref_id);
        }
    }

    public function saveObject(): void
    {
        $ilAccess = $this->access;
        $target_id = $this->cont_request->getTargetId();
        $create = true;

        if ($target_id === 0) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('select_object_to_link'));
            $this->createObject();
            $create = false;
        }
        if ($create && !$ilAccess->checkAccess('visible', '', $target_id)) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('permission_denied'));
            $this->createObject();
            $create = false;
        }
        if ($create && $this->tryingToCreateCircularReference($target_id, $this->cont_request->getRefId())) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('prgr_may_not_create_circular_reference'));
            $this->createObject();
            $create = false;
        }
        if ($create) {
            parent::saveObject();
        }
    }

    public function updateObject(): void
    {
        $form = $this->initForm();
        $form->checkInput();
        $target_id = (int) $form->getInput('target_id');
        $self_id = (int) $this->object->getRefId();
        $container_id = $this->tree->getParentId($self_id);
        $do_update = true;

        if (!$this->access->checkAccess('visible', '', $target_id)) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('permission_denied'), true);
            $this->editObject($form);
            $do_update = false;
        }

        if ($do_update && $this->tryingToCreateCircularReference($target_id, $container_id)) {
            $this->tpl->setOnScreenMessage("failure", $this->lng->txt('prgr_may_not_create_circular_reference'));
            $this->editObject($form);
            $do_update = false;
        }

        if ($do_update) {
            parent::updateObject();
        }
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
