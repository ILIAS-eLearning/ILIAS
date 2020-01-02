<?php

/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageContent.php");

/**
 * Cell of a grid
 *
 * @author Alex Killing <killing@leifos.de>
 *
 * @ingroup ServicesCOPage
 */
class ilPCGridCell extends ilPageContent
{
    /**
    * Init page content component.
    */
    public function init()
    {
        $this->setType("gcell");
    }

    /**
     * delete tab
     */
    public function deleteCell()
    {
        $grid_cell = $this->getNode();
        $grid_cell->unlink($grid_cell);
    }

    /**
     * Move cell right
     */
    public function moveCellRight()
    {
        $grid_cell = $this->getNode();
        $next = $grid_cell->next_sibling();
        $next_copy = $next->clone_node(true);
        $grid_cell->insert_before($next_copy, $grid_cell);
        $next->unlink($next);
    }

    /**
     * Move cell left
     */
    public function moveCellLeft()
    {
        $grid_cell = $this->getNode();
        $prev = $grid_cell->previous_sibling();
        $grid_cell_copy = $grid_cell->clone_node(true);
        $prev->insert_before($grid_cell_copy, $prev);
        $grid_cell->unlink($grid_cell);
    }
}
