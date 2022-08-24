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
 ********************************************************************
 */

/**
 * TableGUI class for assigned objects of skills
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilSkillAssignedObjectsTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilTree $tree;

    public function __construct($a_parent_obj, string $a_parent_cmd, array $a_ass_objects)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $data = [];
        foreach ($a_ass_objects as $obj) {
            if (ilObject::_hasUntrashedReference($obj)) {
                $data[] = array("obj_id" => $obj);
            }
        }

        $this->setData($data);
        $this->setTitle($this->lng->txt("skmg_assigned_objects"));

        $this->addColumn($this->lng->txt("title"), "title");
        $this->addColumn($this->lng->txt("path"), "path");

        $this->setRowTemplate("tpl.skill_assigned_objects_row.html", "Services/Skill");
    }

    protected function fillRow(array $a_set): void
    {
        $obj_type = ilObject::_lookupType($a_set["obj_id"]);
        $this->tpl->setVariable(
            "OBJECT_IMG",
            ilUtil::img(
                ilObject::_getIcon(
                    (int) $a_set["obj_id"]
                ),
                $this->lng->txt("icon") . " " . $this->lng->txt($obj_type)
            )
        );
        $this->tpl->setVariable("OBJECT_TITLE", ilObject::_lookupTitle($a_set["obj_id"]));

        $obj_ref_id = ilObject::_getAllReferences($a_set["obj_id"]);
        $obj_ref_id = end($obj_ref_id);
        $obj_ref_id_parent = $this->tree->getParentId($obj_ref_id);

        $path = new ilPathGUI();

        $this->tpl->setVariable(
            "PATH",
            $path->getPath($this->tree->getParentId($obj_ref_id_parent), (int) $obj_ref_id)
        );
    }
}
