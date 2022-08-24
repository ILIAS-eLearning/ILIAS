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

/**
 * tree explorer lm public area
 * @author Fabian Wolf <wolf@leifos.com>
 */
class ilPublicSectionExplorerGUI extends ilTreeExplorerGUI
{
    protected ilObjLearningModule $lm;
    public string $exp_id = "public_section";
    public string $requested_transl = "";

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilObjLearningModule $a_lm,
        string $requested_transl = ""
    ) {
        $this->lm = $a_lm;
        $this->requested_transl = $requested_transl;

        $tree = ilLMTree::getInstance($this->lm->getId());

        parent::__construct("lm_public_section_" . $this->lm->getId(), $a_parent_obj, $a_parent_cmd, $tree);
    }

    /**
     * @param object|array $a_node
     */
    public function getNodeContent($a_node): string
    {
        $lang = ($this->requested_transl != "")
            ? $this->requested_transl
            : "-";
        return ilLMObject::_getNodePresentationTitle(
            $a_node,
            ilLMObject::PAGE_TITLE,
            $this->lm->isActiveNumbering(),
            false,
            false,
            $this->lm->getId(),
            $lang
        );
    }

    /**
     * @param object|array $a_node
     */
    public function getNodeIcon($a_node): string
    {
        if ($a_node["child"] == $this->getNodeId($this->getRootNode())) {
            $icon = ilUtil::getImagePath("icon_lm.svg");
        } else {
            $a_name = "icon_" . $a_node["type"] . ".svg";
            if ($a_node["type"] == "pg") {
                $lm_set = new ilSetting("lm");
                $active = ilLMPage::_lookupActive(
                    $a_node["child"],
                    $this->lm->getType(),
                    $lm_set->get("time_scheduled_page_activation")
                );

                // is page scheduled?
                $img_sc = ($lm_set->get("time_scheduled_page_activation") &&
                    ilLMPage::_isScheduledActivation($a_node["child"], $this->lm->getType()))
                    ? "_sc"
                    : "";

                $a_name = "icon_pg" . $img_sc . ".svg";

                if (!$active) {
                    $a_name = "icon_pg_d" . $img_sc . ".svg";
                } else {
                    $contains_dis = ilLMPage::_lookupContainsDeactivatedElements(
                        $a_node["child"],
                        $this->lm->getType()
                    );
                    if ($contains_dis) {
                        $a_name = "icon_pg_del" . $img_sc . ".svg";
                    }
                }
            }
            $icon = ilUtil::getImagePath($a_name);
        }

        return $icon;
    }

    public function beforeRendering(): void
    {
        //select public pages and open public chapters
        foreach ($this->getAllNodes() as $node) {
            if ($node["public_access"] == "y" && $node["type"] == "pg") {
                $this->setNodeSelected($node["obj_id"]);
            }
            if ($node["public_access"] == "y" && $node["type"] == "st") {
                $this->setNodeOpen($node["obj_id"]);
            }
        }
    }

    protected function getAllNodes(?int $from_id = null): array
    {
        $nodes = array();

        if ($from_id === null) {
            $from_id = $this->getNodeId($this->getRootNode());
        }

        foreach ($this->getChildsOfNode($from_id) as $node) {
            $nodes[] = $node;

            if ($node["type"] == "st") {
                $nodes = array_merge($nodes, $this->getAllNodes($node["obj_id"]));
            }
        }
        return $nodes;
    }

    /**
     * @param object|array $a_node
     * @return bool
     */
    public function isNodeClickable($a_node): bool
    {
        if ($a_node["type"] == "pg") {
            return true;
        }
        return false;
    }
}
