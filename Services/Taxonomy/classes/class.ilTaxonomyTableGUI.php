<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for taxonomies
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxonomyTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected string $requested_tax_node;
    protected ilTaxonomyTree $tree;
    protected ilObjTaxonomy $tax;
    protected int $node_id;

    /**
     * Constructor
     */
    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilTaxonomyTree $a_tree,
        int $a_node_id,
        ilObjTaxonomy $a_tax
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();

        $params = $DIC->http()->request()->getQueryParams();

        $this->requested_tax_node = $params["tax_node"] ?? "";

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        if ($a_node_id == "") {
            $a_node_id = $a_tree->readRootId();
        }
        
        $this->tree = $a_tree;
        $this->tax = $a_tax;
        $this->node_id = $a_node_id;

        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $childs = $this->tree->getChildsByTypeFilter(
            $a_node_id,
            array("taxn")
        );
        
        if ($a_tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
            $childs = ilArrayUtil::sortArray($childs, "order_nr", "asc", false);
        } else {
            $childs = ilArrayUtil::sortArray($childs, "title", "asc", false);
        }
        $this->setData($childs);
        
        $this->setTitle($lng->txt("tax_nodes"));
        
        $this->addColumn($this->lng->txt(""), "", "1px", true);
        if ($this->tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
            $this->addColumn($this->lng->txt("tax_order"), "order_nr", "1px");
            $this->setDefaultOrderField("order_nr");
            $this->setDefaultOrderDirection("asc");
        }
        $this->addColumn($this->lng->txt("title"));
        
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.tax_row.html", "Services/Taxonomy");

        $this->addMultiCommand("deleteItems", $lng->txt("delete"));
        $this->addMultiCommand("moveItems", $lng->txt("move"));
        $this->addCommandButton("saveSorting", $lng->txt("save"));
    }
    
        
    /**
     * @inheritDoc
     */
    public function numericOrdering(string $a_field) : bool
    {
        if (in_array($a_field, array("order_nr"))) {
            return true;
        }
        return false;
    }
    
    /**
     * @inheritDoc
     */
    protected function fillRow(array $a_set) : void
    {
        $ilCtrl = $this->ctrl;

        $ilCtrl->setParameter($this->parent_obj, "tax_node", $a_set["child"]);
        $ret = $ilCtrl->getLinkTargetByClass("ilobjtaxonomygui", "listNodes");
        $ilCtrl->setParameter($this->parent_obj, "tax_node", $this->requested_tax_node);
        if ($this->tax->getSortingMode() == ilObjTaxonomy::SORT_MANUAL) {
            $this->tpl->setCurrentBlock("order");
            $this->tpl->setVariable("ORDER_NR", $a_set["order_nr"]);
            $this->tpl->setVariable("ONODE_ID", $a_set["child"]);
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("HREF_TITLE", $ret);
        
        $this->tpl->setVariable("TITLE", ilLegacyFormElementsUtil::prepareFormOutput($a_set["title"]));
        $this->tpl->setVariable("NODE_ID", $a_set["child"]);
    }
}
