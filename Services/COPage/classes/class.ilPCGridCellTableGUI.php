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
 * Grid table
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCGridCellTableGUI extends ilTable2GUI
{
    protected int $pos;
    protected ilPCGrid $grid;

    public function __construct(
        object $a_parent_obj,
        string $a_parent_cmd,
        ilPCGrid $a_grid
    ) {
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
    
    protected function fillRow(array $a_set) : void
    {
        $this->pos += 10;
        $this->tpl->setVariable("POS", ilLegacyFormElementsUtil::prepareFormOutput($this->pos));
        $tid = $a_set["hier_id"] . ":" . $a_set["pc_id"];
        $this->tpl->setVariable("TID", $tid);
        foreach (ilPCGrid::getSizes() as $s) {
            $this->tpl->setCurrentBlock("select_width");
            $this->tpl->setVariable(
                "SELECT_WIDTH",
                ilLegacyFormElementsUtil::formSelect(
                    $a_set[$s],
                    "width_" . $s . "[$tid]",
                    ["" => ""] + ilPCGrid::getWidths(),
                    false,
                    true
                )
            );
            $this->tpl->parseCurrentBlock();
        }
    }
}
