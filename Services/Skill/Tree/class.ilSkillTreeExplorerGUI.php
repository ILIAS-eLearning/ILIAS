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
class ilSkillTreeExplorerGUI extends ilVirtualSkillTreeExplorerGUI
{
    protected ilLanguage $lng;
    protected SkillAdminGUIRequest $admin_gui_request;
    protected int $requested_skill_node_id = 0;
    protected int $requested_tref_id = 0;

    /**
     * @param object|string[]   $a_parent_obj
     * @param string            $a_parent_cmd
     * @param int               $tree_id
     */
    public function __construct($a_parent_obj, string $a_parent_cmd, int $tree_id = 0)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->admin_gui_request = $DIC->skills()->internal()->gui()->admin_request();
        parent::__construct("skill_exp", $a_parent_obj, $a_parent_cmd, $tree_id);

        $this->requested_skill_node_id = $this->admin_gui_request->getNodeId();
        $this->requested_tref_id = $this->admin_gui_request->getTrefId();

        // node should be hidden #26849 (not not hidden, see discussion in #26813 and JF 6 Jan 2020)
        $this->setSkipRootNode(false);
        $this->setAjax(false);
        $this->setShowDraftNodes(true);
        $this->setShowOutdatedNodes(true);
    }

    /**
     * @inheritdoc
     */
    public function getNodeContent($a_node): string
    {
        $lng = $this->lng;

        $a_parent_id_parts = explode(":", $a_node["id"]);
        $a_parent_skl_tree_id = (int) $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = isset($a_parent_id_parts[1]) ? (int) $a_parent_id_parts[1] : 0;

        // title
        $title = $a_node["title"];

        // root?
        if ($a_node["type"] == "skrt") {
            $tree_obj = $this->skill_tree_manager->getTree($a_node["skl_tree_id"]);
            $title = $tree_obj->getTitle();
        } else {
            if ($a_node["type"] == "sktr") {
                $tid = ilSkillTemplateReference::_lookupTemplateId($a_parent_skl_tree_id);
                $title .= " (" . ilSkillTreeNode::_lookupTitle($tid) . ")";
            }

            // @todo: fix this if possible for skill/tref_id combination
            if (ilSkillTreeNode::_lookupSelfEvaluation($a_parent_skl_tree_id)) {
                if ($a_parent_skl_template_tree_id == 0 || $a_node["type"] == "sktr") {
                    $title = "<u>" . $title . "</u>";
                }
            }
        }

        if ($this->vtree->isOutdated($a_node["id"])) {
            $title = "<span class='light'>" . $title . "</span>";
        }

        return $title;
    }

    /**
     * @inheritdoc
     */
    public function getNodeIcon($a_node): string
    {
        $a_parent_id_parts = explode(":", $a_node["id"]);
        $a_parent_skl_tree_id = (int) $a_parent_id_parts[0];
        $a_parent_skl_template_tree_id = isset($a_parent_id_parts[1]) ? (int) $a_parent_id_parts[1] : 0;


        // root?
        if ($a_node["type"] == "skrt") {
            $icon = ilUtil::getImagePath("icon_scat.svg");
        } elseif (in_array($a_node["type"], array("skll", "scat", "sctr", "sktr", "sctp", "sktp"))) {
            $icon = ilSkillTreeNode::getIconPath(
                $a_parent_skl_tree_id,
                $a_node["type"],
                "",
                ($this->vtree->isDraft($a_node["id"]) || $this->vtree->isOutdated($a_node["id"]))
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
        $id_parts = explode(":", $a_node["id"]);
        if (!isset($id_parts[1]) || $id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $id_parts[0];
            $tref_id = 0;
        } else {
            // skill in template
            $tref_id = $id_parts[0];
            $skill_id = $id_parts[1];
        }

        if ($this->requested_skill_node_id == "" && $a_node["type"] == "skrt") {
            return true;
        }

        if ($skill_id == $this->requested_skill_node_id &&
            ($this->requested_tref_id == $tref_id)) {
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

        $id_parts = explode(":", $a_node["id"]);
        if (!isset($id_parts[1]) || $id_parts[1] == 0) {
            // skill in main tree
            $skill_id = $id_parts[0];
            $tref_id = 0;
        } else {
            // skill in template
            $tref_id = $id_parts[0];
            $skill_id = $id_parts[1];
        }

        $gui_class = array(
            "skrt" => "ilskillrootgui",
            "scat" => "ilskillcategorygui",
            "sktr" => "ilskilltemplatereferencegui",
            "skll" => "ilbasicskillgui",
            "sktp" => "ilbasicskilltemplategui",
            "sctp" => "ilskilltemplatecategorygui"
        );

        $cmd = array(
            "skrt" => "listSkills",
            "scat" => "listItems",
            "sktr" => "listItems",
            "skll" => "edit",
            "sktp" => "edit",
            "sctp" => "listItems"
        );

        $gui_class = $gui_class[$a_node["type"]];
        $cmd = $cmd[$a_node["type"]];

        $ilCtrl->setParameterByClass($gui_class, "tref_id", $tref_id);
        $ilCtrl->setParameterByClass($gui_class, "node_id", $skill_id);
        $ret = $ilCtrl->getLinkTargetByClass(["ilAdministrationGUI", "ilObjSkillManagementGUI",
                                              "SkillTreeAdminGUI", "ilObjSkillTreeGUI", $gui_class], $cmd);
        $ilCtrl->setParameterByClass($gui_class, "node_id", $this->requested_skill_node_id);
        $ilCtrl->setParameterByClass($gui_class, "tref_id", $this->requested_tref_id);

        return $ret;
    }

    /**
     * @inheritdoc
     */
    public function isNodeClickable($a_node): bool
    {
        return true;
    }
}
