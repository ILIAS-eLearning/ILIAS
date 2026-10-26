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

class ilIntLinkRepItemExplorerGUI extends ilRepositorySelectorExplorerGUI
{
    protected string $link_target_script;

    /**
     * @param object|array $a_parent_obj parent gui class or class array
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd
    ) {
        parent::__construct($a_parent_obj, $a_parent_cmd, null, "", "");

        // #14587 - ilRepositorySelectorExplorerGUI::__construct() does NOT include side blocks!
        $list = $this->getTypeWhiteList();
        $list[] = "poll";
        $this->setTypeWhiteList($list);
    }

    /**
     * Set "set link target" script
     */
    public function setSetLinkTargetScript(string $a_script): void
    {
        $this->link_target_script = $a_script;
    }

    /**
     * Get "set link target" script
     */
    public function getSetLinkTargetScript(): string
    {
        return $this->link_target_script;
    }

    /**
     * @param array|object $a_node
     */
    public function getNodeHref($a_node): string
    {
        if ($this->getSetLinkTargetScript() === "") {
            return "#";
        }

        $link = ilUtil::appendUrlParameterString(
            $this->getSetLinkTargetScript(),
            "linktype=RepositoryItem&linktarget=il__" . $a_node["type"] . "_" . $a_node["child"]
        );

        return $link;
    }

    /**
     * get onclick event handling
     * @param array|object $a_node
     */
    public function getNodeOnClick($a_node): string
    {
        if ($this->getSetLinkTargetScript() === "") {
            $ref_id = $this->getRefIdFromNodeId($a_node["child"]);
            $anchor = $this->getAnchorFromNodeId($a_node["child"]);

            $anchor_str = "";
            if ($anchor !== "") {
                $anchor_str = " anchor=&quot;" . $anchor . "&quot;";
            }
            return "return il.IntLink.addInternalLink('[iln " . $a_node['type'] . "=&quot;" . $ref_id . "&quot;" . $anchor_str . "]','[/iln]', event);";
        }

        return "";
    }

    public function getChildsOfNode($a_parent_node_id): array
    {
        if (str_contains($a_parent_node_id, "#")) {
            return [];
        }
        $ref_id = $this->getRefIdFromNodeId($a_parent_node_id);
        $node = $this->tree->getNodeData($ref_id);
        $childs = [];
        if (!str_contains($a_parent_node_id, "#") && in_array($node["type"], ["crs", "cat", "grp", "fold"])) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            foreach (ilPCParagraph::_readAnchors("cont", $obj_id, "") as $anchor) {
                $anchor_node = $node;
                $anchor_node["child"] = $ref_id . "#" . $anchor;
                $anchor_node["title"] = "#" . $anchor;
                $childs[] = $anchor_node;
            }
        }
        $parent_childs = parent::getChildsOfNode($ref_id);
        $childs = array_merge($childs, $parent_childs);
        return $childs;
    }

    protected function getRefIdFromNodeId(string $node_id): int
    {
        $e = explode("#", $node_id);
        return (int) $e[0];
    }

    protected function getAnchorFromNodeId(string $node_id): string
    {
        $e = explode("#", $node_id);
        return $e[1] ?? "";
    }

    public function isNodeVisible($a_node): bool
    {
        $a_node["child"] = $this->getRefIdFromNodeId($a_node["child"]);
        return parent::isNodeVisible($a_node);
    }

    public function getNodeIcon($a_node): string
    {
        if (str_contains($a_node["child"], "#")) {
            return "";
        }
        $a_node["child"] = $this->getRefIdFromNodeId($a_node["child"]);
        return parent::getNodeIcon($a_node);
    }

    public function isNodeClickable($a_node): bool
    {
        $a_node["child"] = $this->getRefIdFromNodeId($a_node["child"]);
        return parent::isNodeClickable($a_node);
    }

    public function isNodeHighlighted($a_node): bool
    {
        if (str_contains($a_node["child"], "#")) {
            return false;
        }
        $a_node["child"] = $this->getRefIdFromNodeId($a_node["child"]);
        return parent::isNodeHighlighted($a_node);
    }

}
