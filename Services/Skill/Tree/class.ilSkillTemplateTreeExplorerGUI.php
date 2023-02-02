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
 ********************************************************************
 */

use ILIAS\Skill\Service\SkillAdminGUIRequest;

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 */
class ilSkillTemplateTreeExplorerGUI extends ilTreeExplorerGUI
{
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_skill_node_id = 0;
    /**
     * @var array<int, int>
     */
    protected array $parent = [];
    /**
     * @var array<int, bool>
     */
    protected array $draft = [];

    /**
     * @param object|string[] $a_parent_obj parent gui object(s)
     * @param string          $a_parent_cmd parent command
     */
    public function __construct($a_parent_obj, string $a_parent_cmd, int $tree_id)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        $tree = $DIC->skills()->internal()->factory()->tree()->getTreeById($tree_id);
        parent::__construct("skill_exp", $a_parent_obj, $a_parent_cmd, $tree);

        $this->requested_skill_node_id = $this->admin_gui_request->getNodeId();

        $this->setTypeWhiteList(array("skrt", "sktp", "sctp"));

        $this->setSkipRootNode(false);
        $this->setAjax(true);
        $this->setOrderField("order_nr");
    }

    public function getRootNode(): array
    {
        $path = $this->getTree()->getPathId($this->requested_skill_node_id);
        return $this->getTree()->getNodeData($path[1]);
    }

    /**
     * @inheritdoc
     */
    public function getChildsOfNode($a_parent_node_id): array
    {
        $childs = parent::getChildsOfNode($a_parent_node_id);

        foreach ($childs as $c) {
            $this->parent[$c["child"]] = $c["parent"];
            if ($this->draft[$c["parent"]]) {
                $this->draft[$c["child"]] = true;
            } else {
                $this->draft[$c["child"]] = (ilSkillTreeNode::_lookupStatus((int) $c["child"]) == ilSkillTreeNode::STATUS_DRAFT);
            }
        }
        return $childs;
    }

    /**
     * @inheritdoc
     */
    public function getNodeContent($a_node): string
    {
        $lng = $this->lng;

        // title
        $title = $a_node["title"];

        // root?
        if ($a_node["type"] == "skrt") {
            $title = $lng->txt("skmg_skill_templates");
        } else {
            if ($a_node["type"] == "sktr") {
                $tid = ilSkillTemplateReference::_lookupTemplateId((int) $a_node["child"]);
                $title .= " (" . ilSkillTreeNode::_lookupTitle($tid) . ")";
            }
            if (ilSkillTreeNode::_lookupSelfEvaluation((int) $a_node["child"])) {
                $title = "<u>" . $title . "</u>";
            }
        }

        return $title;
    }

    /**
     * @inheritdoc
     */
    public function getNodeIcon($a_node): string
    {
        // root?
        if ($a_node["type"] == "skrt") {
            $icon = ilUtil::getImagePath("icon_sctp.svg");
        } elseif (in_array($a_node["type"], array("skll", "scat", "sctr", "sktr"))) {
            $icon = ilSkillTreeNode::getIconPath(
                $a_node["child"],
                $a_node["type"],
                "",
                (int) $this->draft[$a_node["child"]]
            );
        } else {
            $icon = ilUtil::getImagePath("icon_" . $a_node["type"] . ".svg");
        }

        return $icon;
    }

    /**
     * @inheritdoc
     */
    public function isNodeHighlighted($a_node): bool
    {
        if ($a_node["child"] == $this->requested_skill_node_id ||
            ($this->requested_skill_node_id == "" && $a_node["type"] == "skrt")) {
            return true;
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getNodeHref($a_node): string
    {
        $ilCtrl = $this->ctrl;

        switch ($a_node["type"]) {
            // root
            case "skrt":
                $ilCtrl->setParameterByClass("ilskillrootgui", "node_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI",
                                                      "ilObjSkillManagementGUI",
                                                      "SkillTreeAdminGUI",
                                                      "ilObjSkillTreeGUI",
                                                      "ilskillrootgui"
                ], "listTemplates");
                $ilCtrl->setParameterByClass("ilskillrootgui", "node_id", $this->requested_skill_node_id);
                return $ret;

                // template
            case "sktp":
                $ilCtrl->setParameterByClass("ilbasicskilltemplategui", "node_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI",
                                                      "ilObjSkillManagementGUI",
                                                      "SkillTreeAdminGUI",
                                                      "ilObjSkillTreeGUI",
                                                      "ilbasicskilltemplategui"
                ], "edit");
                $ilCtrl->setParameterByClass("ilbasicskilltemplategui", "node_id", $this->requested_skill_node_id);
                return $ret;

                // template category
            case "sctp":
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "node_id", $a_node["child"]);
                $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI",
                                                      "ilObjSkillManagementGUI",
                                                      "SkillTreeAdminGUI",
                                                      "ilObjSkillTreeGUI",
                                                      "ilskilltemplatecategorygui"
                ], "listItems");
                $ilCtrl->setParameterByClass("ilskilltemplatecategorygui", "node_id", $this->requested_skill_node_id);
                return $ret;

            default:
                return "";
        }
    }

    /**
     * @inheritdoc
     */
    public function getNodeIconAlt($a_node): string
    {
        $lng = $this->lng;

        if ($lng->exists("skmg_" . $a_node["type"])) {
            return $lng->txt("skmg_" . $a_node["type"]);
        }

        return $lng->txt($a_node["type"]);
    }
}
