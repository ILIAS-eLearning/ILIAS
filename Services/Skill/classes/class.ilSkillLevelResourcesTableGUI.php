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
 * TableGUI class for skill level resources
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillLevelResourcesTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilTree $tree;
    protected int $level_id = 0;
    protected bool $write_permission = false;
    protected ilSkillResources $resources;

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        int $a_skill_id,
        int $a_tref_id,
        int $a_level_id,
        bool $a_write_permission = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tree = $DIC->repositoryTree();

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        $this->level_id = $a_level_id;
        $this->write_permission = $a_write_permission;
        
        $this->resources = new ilSkillResources($a_skill_id, $a_tref_id);
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setData($this->resources->getResourcesOfLevel($this->level_id));
        $this->setTitle($lng->txt("resources"));

        if ($this->write_permission) {
            $this->addColumn("", "", "1px", true);
        }
        $this->addColumn($this->lng->txt("type"), "", "1px");
        $this->addColumn($this->lng->txt("title"), "");
        $this->addColumn($this->lng->txt("path"));
        $this->addColumn($this->lng->txt("skmg_suggested"));
        $this->addColumn($this->lng->txt("skmg_lp_triggers_level"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.level_resources_row.html", "Services/Skill");

        if ($this->write_permission) {
            $this->addMultiCommand("confirmLevelResourcesRemoval", $lng->txt("remove"));
            $this->addCommandButton("saveResourceSettings", $lng->txt("skmg_save_settings"));
        }
    }

    protected function fillRow(array $a_set) : void
    {
        $lng = $this->lng;
        $tree = $this->tree;

        $ref_id = $a_set["rep_ref_id"];
        $obj_id = ilObject::_lookupObjId($ref_id);
        $obj_type = ilObject::_lookupType($obj_id);

        if ($a_set["imparting"]) {
            $this->tpl->touchBlock("sugg_checked");
        }
        $this->tpl->setCurrentBlock("suggested_checkbox");
        $this->tpl->setVariable("SG_ID", $ref_id);
        $this->tpl->parseCurrentBlock();

        if (ilObjectLP::isSupportedObjectType($obj_type)) {
            if ($a_set["trigger"]) {
                $this->tpl->touchBlock("trig_checked");
            }
            $this->tpl->setCurrentBlock("trigger_checkbox");
            $this->tpl->setVariable("TR_ID", $ref_id);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("TITLE", ilObject::_lookupTitle($obj_id));
        $this->tpl->setVariable("IMG", ilUtil::img(ilObject::_getIcon($obj_id, "tiny")));
        if ($this->write_permission) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable("ID", $ref_id);
            $this->tpl->parseCurrentBlock();
        }
        
        $path = $tree->getPathFull($ref_id);
        $path_items = [];
        foreach ($path as $p) {
            if ($p["type"] != "root" && $p["child"] != $ref_id) {
                $path_items[] = $p["title"];
            }
        }
        $this->tpl->setVariable("PATH", implode(" > ", $path_items));
    }
}
