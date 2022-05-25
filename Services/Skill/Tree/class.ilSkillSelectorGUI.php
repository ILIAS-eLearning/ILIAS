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

use ILIAS\Skill\Service\SkillAdminGUIRequest;

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 */
class ilSkillSelectorGUI extends ilVirtualSkillTreeExplorerGUI
{
    protected string $select_gui = "";
    protected string $select_cmd = "";
    protected string $select_par = "";
    protected SkillAdminGUIRequest $admin_gui_request;

    /**
     * @var string[]
     */
    protected array $requested_selected_ids = [];

    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        $a_select_gui,
        string $a_select_cmd,
        string $a_select_par = "selected_skill",
        int $a_skill_tree_id = 0
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        parent::__construct("skill_sel", $a_parent_obj, $a_parent_cmd, $a_skill_tree_id);
        $this->select_gui = (is_object($a_select_gui))
            ? strtolower(get_class($a_select_gui))
            : $a_select_gui;
        $this->select_cmd = $a_select_cmd;
        $this->select_par = $a_select_par;
        $this->setSkipRootNode(true);
        $this->requested_selected_ids = $this->admin_gui_request->getSelectedIds($this->select_postvar);
    }

    public function setSkillSelected(string $a_id) : void
    {
        $this->setNodeSelected($this->vtree->getCSkillIdForVTreeId($a_id));
    }

    public function getSelectedSkills() : array
    {
        $skills = [];
        $pa = $this->requested_selected_ids;
        if (!empty($pa)) {
            foreach ($pa as $p) {
                $skills[] = $this->vtree->getCSkillIdForVTreeId($p);
            }
        }
        return $skills;
    }
    
    /**
     * @inheritdoc
     */
    public function getNodeHref($a_node) : string
    {
        if ($this->select_multi) {
            return "#";
        }

        $ilCtrl = $this->ctrl;
        
        // we have a tree id like <skl_tree_id>:<skl_template_tree_id>
        // and make a "common" skill id in format <skill_id>:<tref_id>
        
        $id_parts = explode(":", $a_node["id"]);
        if ($id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $a_node["id"];
        } else {
            // skill in template
            $skill_id = $id_parts[1] . ":" . $id_parts[0];
        }
        
        $ilCtrl->setParameterByClass($this->select_gui, $this->select_par, $skill_id);
        $ret = $ilCtrl->getLinkTargetByClass($this->select_gui, $this->select_cmd);
        $ilCtrl->setParameterByClass($this->select_gui, $this->select_par, "");
        
        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function isNodeClickable($a_node) : bool
    {
        return $this->nodeHasAction($a_node);
    }

    /**
     * @inheritdoc
     */
    protected function isNodeSelectable($a_node) : bool
    {
        return $this->nodeHasAction($a_node);
    }

    /**
     * @param array|object $a_node
     * @return bool
     */
    private function nodeHasAction($a_node) : bool
    {
        if (in_array($a_node["type"], array("skll", "sktp"))) {
            return true;
        }
        // references that refer directly to a (basic) skill template
        if ($a_node["type"] == "sktr" && ilSkillTreeNode::_lookupType($a_node["skill_id"]) == "sktp") {
            return true;
        }

        return false;
    }
}
