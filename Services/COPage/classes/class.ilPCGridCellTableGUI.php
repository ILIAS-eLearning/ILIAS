<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
 * Grid table
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesCOPage
 */
class ilPCGridCellTableGUI extends ilTable2GUI
{
    /**
     * @var ilCtrl
     */
    protected $ctrl;


    public function __construct($a_parent_obj, $a_parent_cmd, ilPCGrid $a_grid)
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "", "1");
        $this->addColumn($lng->txt("cont_position"), "", "1");
        $this->addColumn($lng->txt("cont_grid_width_s"));
        $this->addColumn($lng->txt("cont_grid_width_m"));
        $this->addColumn($lng->txt("cont_grid_width_l"));
        $this->addColumn($lng->txt("cont_grid_width_xl"));
        $this->setEnableHeader(true);
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate(
            "tpl.grid_cell_row.html",
            "Services/COPage"
        );
            
        $this->grid = $a_grid;
        //$caps = $this->tabs->getCaptions();
        $this->setData($this->grid->getCellData());
        $this->setLimit(0);
        
        $this->addMultiCommand("confirmCellDeletion", $lng->txt("delete"));
        $this->addCommandButton("saveCellData", $lng->txt("save"));
        
        $this->setTitle($lng->txt("cont_ed_grid_col_widths"));
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $this->pos += 10;
        $this->tpl->setVariable("POS", ilUtil::prepareFormOutput($this->pos));
        $tid = $a_set["hier_id"] . ":" . $a_set["pc_id"];
        $this->tpl->setVariable("TID", $tid);
        foreach (ilPCGrid::getSizes() as $s) {
            $this->tpl->setCurrentBlock("select_width");
            $this->tpl->setVariable("SELECT_WIDTH", ilUtil::formSelect($a_set[$s], "width_" . $s . "[$tid]", array("" => "") + ilPCGrid::getWidths(), false, true));
            $this->tpl->parseCurrentBlock();
        }
    }
}
