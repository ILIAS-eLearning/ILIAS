<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Taxonomy explorer GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilTaxonomyExplorerGUI extends ilTreeExplorerGUI
{
    protected string $requested_tax_node;
    protected string $onclick = "";

    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct(
        $a_parent_obj,
        $a_parent_cmd,
        $a_tax_id,
        $a_target_gui,
        $a_target_cmd,
        $a_id = ""
    ) {
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tax_tree = new ilTaxonomyTree($a_tax_id);
        if ($a_id != "") {
            $this->id = $a_id;
        } else {
            $this->id = "tax_expl_" . $this->tax_tree->getTreeId();
        }
        if (ilObjTaxonomy::lookupSortingMode($a_tax_id) == ilObjTaxonomy::SORT_ALPHABETICAL) {
            $this->setOrderField("title");
        } else {
            $this->setOrderField("order_nr", true);
        }
        $this->setPreloadChilds(true);
        $this->target_gui = $a_target_gui;
        $this->target_cmd = $a_target_cmd;
        $params = $DIC->http()->request()->getQueryParams();
        $this->requested_tax_node = ilUtil::stripSlashes((string) ($params["tax_node"] ?? ""));
        parent::__construct($this->id, $a_parent_obj, $a_parent_cmd, $this->tax_tree);
    }
    
    
    /**
     * Get content of node
     *
     * @param
     * @return
     */
    public function getNodeContent($a_node)
    {
        $rn = $this->getRootNode();
        if ($rn["child"] == $a_node["child"]) {
            return ilObject::_lookupTitle($this->tax_tree->getTreeId());
        } else {
            return $a_node["title"];
        }
    }
    
    /**
     * Get node href
     *
     * @param
     * @return
     */
    public function getNodeHref($a_node)
    {
        $ilCtrl = $this->ctrl;

        if (!$this->onclick && $this->target_gui != "") {
            $ilCtrl->setParameterByClass($this->target_gui, "tax_node", $a_node["child"]);
            if (is_array($this->parent_obj)) {
                // Used for taxonomies in categories
                $href = $ilCtrl->getLinkTargetByClass($this->parent_obj, $this->target_cmd);
            } else {
                // See: https://mantis.ilias.de/view.php?id=27727
                $href = $ilCtrl->getLinkTargetByClass($this->target_gui, $this->target_cmd);
            }
            if ($this->requested_tax_node != "" && !is_array($this->requested_tax_node)) {
                $ilCtrl->setParameterByClass($this->target_gui, "tax_node", $this->requested_tax_node);
            }
            return $href;
        } else {
            return "#";
        }
    }
    
    /**
     * Get node icon
     *
     * @param
     * @return
     */
    public function getNodeIcon($a_node)
    {
        return ilUtil::getImagePath("icon_taxn.svg");
    }
    
    /**
     *
     *
     * @param
     * @return
     */
    public function isNodeHighlighted($a_node)
    {
        if ((!$this->onclick && $a_node["child"] == $this->requested_tax_node) ||
            ($this->onclick && is_array($this->selected_nodes) && in_array($a_node["child"], $this->selected_nodes))) {
            return true;
        }
        return false;
    }
    
    public function setOnClick($a_value)
    {
        $this->onclick = $a_value;
    }
    
    public function getNodeOnClick($a_node)
    {
        if ($this->onclick) {
            return str_replace("{NODE_CHILD}", $a_node["child"], $this->onclick);
        } else {
            // #14623
            return parent::getNodeOnClick($a_node);
        }
    }
}
