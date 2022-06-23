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
