<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use \ILIAS\Skill\Tree;

/**
 * TableGUI class for skill profile levels
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSkillProfileLevelsTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /**
     * @var ilBasicSkillTreeRepository
     */
    protected $tree_repo;

    /**
     * @var \ILIAS\Skill\Service\SkillInternalManagerService
     */
    protected $skill_manager;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var int
     */
    protected $requested_ref_id;

    /**
     * Constructor
     */
    public function __construct($a_parent_obj, $a_parent_cmd, $a_profile)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->request = $DIC->http()->request();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $params = $this->request->getQueryParams();
        $this->requested_ref_id = (int) ($params["ref_id"] ?? 0);
        
        $this->skill_manager = $DIC->skills()->internal()->manager();
        $this->tree_repo = $DIC->skills()->internal()->repo()->getTreeRepo();
        
        $this->profile = $a_profile;
        parent::__construct($a_parent_obj, $a_parent_cmd);

        $this->setData($this->profile->getSkillLevels());
        $this->setTitle($lng->txt("skmg_target_levels"));

        $access_manager = $this->skill_manager->getAccessManager($this->requested_ref_id);
        if ($access_manager->hasManageProfilesPermission()) {
            $this->addColumn("", "", "1", true);
            $this->addColumn($this->lng->txt("skmg_order"), "", "1px");
        }
        $this->addColumn($this->lng->txt("skmg_skill"));
        $this->addColumn($this->lng->txt("skmg_level"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.skill_profile_level_row.html", "Services/Skill");

        if ($access_manager->hasManageProfilesPermission()) {
            $this->addMultiCommand("confirmLevelAssignmentRemoval", $lng->txt("skmg_remove_levels"));
            if (count($this->profile->getSkillLevels()) > 0) {
                $this->addCommandButton("saveLevelOrder", $lng->txt("skmg_save_order"));
            }
        }
    }
    
    /**
     * Fill table row
     */
    protected function fillRow($a_set)
    {
        $tree_id = $this->tree_repo->getTreeIdForNodeId($a_set["base_skill_id"]);
        $node_manager = $this->skill_manager->getTreeNodeManager($tree_id);
        $this->tpl->setVariable(
            "SKILL_TITLE",
            $node_manager->getWrittenPath(
                $a_set["base_skill_id"],
                $a_set["tref_id"]
            )
        );
        
        $this->tpl->setVariable("LEVEL_TITLE", ilBasicSkill::lookupLevelTitle($a_set["level_id"]));

        $access_manager = $this->skill_manager->getAccessManager($this->requested_ref_id);
        if ($access_manager->hasManageProfilesPermission()) {
            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable(
                "ID",
                ((int) $a_set["base_skill_id"]) . ":" . ((int) $a_set["tref_id"]) . ":" . ((int) $a_set["level_id"]) .
                ":" . ((int) $a_set["order_nr"])
            );
            $this->tpl->setVariable("SKILL_ID", (int) $a_set["base_skill_id"]);
            $this->tpl->setVariable("TREF_ID", (int) $a_set["tref_id"]);
            $this->tpl->parseCurrentBlock();
            $this->tpl->setCurrentBlock("order");
            $this->tpl->setVariable("ORDER_NR", (int) $a_set["order_nr"]);
            $this->tpl->parseCurrentBlock();
        }

    }
}
