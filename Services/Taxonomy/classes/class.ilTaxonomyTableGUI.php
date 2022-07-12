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
 * TableGUI class for taxonomies
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

        // @todo introduce request wrapper
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

    public function numericOrdering(string $a_field) : bool
    {
        if ($a_field == "order_nr") {
            return true;
        }
        return false;
    }

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
