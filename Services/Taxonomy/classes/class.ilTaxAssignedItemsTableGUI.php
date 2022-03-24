<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * TableGUI class for taxonomy list
 * @author Alexander Killing <killing@leifos.de>
 */
class ilTaxAssignedItemsTableGUI extends ilTable2GUI
{
    protected ilAccessHandler $access;
    protected ilObjTaxonomy $tax;
    protected int $node_id;
    protected string $comp_id;
    protected int $obj_id;
    protected string $item_type;
    protected ilTaxAssignedItemInfo $info_obj;

    /**
     * Constructor
     */
    public function __construct(
        $a_parent_obj,
        string $a_parent_cmd,
        int $a_node_id,
        ilObjTaxonomy $a_tax,
        string $a_comp_id,
        int $a_obj_id,
        string $a_item_type,
        ilTaxAssignedItemInfo $a_info_obj
    ) {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();

        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();

        $this->setId("tax_ass_it");
        $this->setLimit(9999);
        $this->tax = $a_tax;
        $this->node_id = $a_node_id;
        $this->comp_id = $a_comp_id;
        $this->obj_id = $a_obj_id;
        $this->item_type = $a_item_type;
        $this->info_obj = $a_info_obj;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $tax_ass = new ilTaxNodeAssignment($this->comp_id, $this->obj_id, $this->item_type, $this->tax->getId());
        $this->setData($tax_ass->getAssignmentsOfNode($this->node_id));
        $this->setTitle($lng->txt("tax_assigned_items"));

        $this->addColumn($this->lng->txt("tax_order"));
        $this->setDefaultOrderField("order_nr");
        $this->setDefaultOrderDirection("asc");

        $this->addColumn($this->lng->txt("title"));

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.tax_ass_items_row.html", "Services/Taxonomy");
        $this->addCommandButton("saveAssignedItemsSorting", $lng->txt("save"));
    }

    public function numericOrdering(string $a_field) : bool
    {
        return $a_field == "order_nr";
    }

    protected function fillRow(array $a_set) : void
    {
        $this->tpl->setVariable("ONODE_ID", $a_set["item_id"]);
        $this->tpl->setVariable("ORDER_NR", (int) $a_set["order_nr"]);
        $this->tpl->setVariable("TITLE", $this->info_obj->getTitle(
            $a_set["component"],
            $a_set["item_type"],
            (int) $a_set["item_id"]
        ));
    }
}
