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
 * Skill level table
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillLevelTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected int $skill_id = 0;
    protected ilBasicSkill $skill;
    protected int $tref_id = 0;
    protected bool $in_use = false;
    protected bool $manage_perm = false;

    public function __construct(
        int $a_skill_id,
        $a_parent_obj,
        string $a_parent_cmd,
        int $a_tref_id = 0,
        bool $a_in_use = false,
        bool $a_manage_perm = false
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->skill_id = $a_skill_id;
        $this->skill = new ilBasicSkill($a_skill_id);
        $this->tref_id = $a_tref_id;
        $this->in_use = $a_in_use;
        $this->manage_perm = $a_manage_perm;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->setLimit(9999);
        $this->setData($this->getSkillLevelData());
        $this->setTitle($lng->txt("skmg_skill_levels"));
        if ($this->tref_id == 0) {
            $this->setDescription($lng->txt("skmg_from_lower_to_higher_levels"));
        }

        if ($this->tref_id == 0 && !$this->in_use) {
            if ($this->manage_perm) {
                $this->addColumn("", "", "1", true);
            }
            $this->addColumn($this->lng->txt("skmg_nr"));
        }
        $this->addColumn($this->lng->txt("title"));
        $this->addColumn($this->lng->txt("description"));
        $this->addColumn($this->lng->txt("skmg_suggested_resources"));
        $this->addColumn($this->lng->txt("skmg_lp_triggers_level_resources"));
        if ($this->manage_perm) {
            $this->addColumn($this->lng->txt("actions"));
        }
        $this->setDefaultOrderField("nr");
        $this->setDefaultOrderDirection("asc");

        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_level_row.html", "Services/Skill");
        $this->setEnableTitle(true);

        if ($this->tref_id == 0 && !$this->in_use && $this->manage_perm) {
            $this->addMultiCommand("confirmLevelDeletion", $lng->txt("delete"));
            if (count($this->getData()) > 0) {
                $this->addCommandButton("updateLevelOrder", $lng->txt("skmg_update_order"));
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function numericOrdering(string $a_field): bool
    {
        if ($a_field == "nr") {
            return true;
        }
        return false;
    }

    public function getSkillLevelData(): array
    {
        $levels = $this->skill->getLevelData();

        // add ressource data
        $res = [];
        $resources = new ilSkillResources($this->skill_id, $this->tref_id);
        foreach ($resources->getResources() as $level_id => $item) {
            $res[$level_id] = $item;
        }

        foreach ($levels as $idx => $item) {
            if (isset($res[$item["id"]])) {
                $levels[$idx]["ressources"] = $res[$item["id"]];
            }
        }

        return $levels;
    }

    protected function fillRow(array $a_set): void
    {
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        if ($this->tref_id == 0 && !$this->in_use) {
            if ($this->manage_perm) {
                $this->tpl->setCurrentBlock("cb");
                $this->tpl->setVariable("CB_ID", $a_set["id"]);
                $this->tpl->parseCurrentBlock();
            }

            $this->tpl->setCurrentBlock("nr");
            $this->tpl->setVariable("ORD_ID", $a_set["id"]);
            $this->tpl->setVariable("VAL_NR", ((int) $a_set["nr"]) * 10);
            if (!$this->manage_perm) {
                $this->tpl->touchBlock("disabled");
            }
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setCurrentBlock("cmd");
        if ($this->manage_perm) {
            $this->tpl->setVariable("TXT_CMD", $lng->txt("edit"));
        }
        $ilCtrl->setParameter($this->parent_obj, "level_id", $a_set["id"]);
        if ($this->tref_id == 0) {
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "editLevel")
            );
        } else {
            $this->tpl->setVariable(
                "HREF_CMD",
                $ilCtrl->getLinkTarget($this->parent_obj, "showLevelResources")
            );
        }
        $this->tpl->parseCurrentBlock();

        $this->tpl->setVariable("TXT_TITLE", $a_set["title"]);
        $this->tpl->setVariable("TXT_DESCRIPTION", $a_set["description"]);
        if (isset($a_set["ressources"]) && is_array($a_set["ressources"])) {
            foreach ($a_set["ressources"] as $rref_id => $ressource) {
                $robj_id = ilObject::_lookupObjId($rref_id);
                $robj_img = ilUtil::img(ilObject::_getIcon($robj_id, "tiny"));
                $robj_title = ilObject::_lookupTitle($robj_id);
                $robj_link = ilLink::_getStaticLink($rref_id);

                if ($ressource["imparting"]) {
                    $this->tpl->setCurrentBlock("ressource_sugg");
                    $this->tpl->setVariable("RSRC_IMG", $robj_img);
                    $this->tpl->setVariable("RSRC_TITLE", $robj_title);
                    $this->tpl->setVariable("RSRC_URL", $robj_link);
                    $this->tpl->parseCurrentBlock();
                }

                if ($ressource["trigger"]) {
                    $this->tpl->setCurrentBlock("ressource_trigg");
                    $this->tpl->setVariable("RSRC_IMG", $robj_img);
                    $this->tpl->setVariable("RSRC_TITLE", $robj_title);
                    $this->tpl->setVariable("RSRC_URL", $robj_link);
                    $this->tpl->parseCurrentBlock();
                }
            }
        }
    }
}
