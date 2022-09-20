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
 * Cell of a grid
 * @author Alexander Killing <killing@leifos.de>
 */
class ilPCGridCell extends ilPageContent
{
    /**
    * Init page content component.
    */
    public function init(): void
    {
        $this->setType("gcell");
    }

    public function deleteCell(): void
    {
        $grid_cell = $this->getNode();
        $grid_cell->unlink($grid_cell);
    }

    public function moveCellRight(): void
    {
        $grid_cell = $this->getNode();
        $next = $grid_cell->next_sibling();
        $next_copy = $next->clone_node(true);
        $grid_cell->insert_before($next_copy, $grid_cell);
        $next->unlink($next);
    }

    public function moveCellLeft(): void
    {
        $grid_cell = $this->getNode();
        $prev = $grid_cell->previous_sibling();
        $grid_cell_copy = $grid_cell->clone_node(true);
        $prev->insert_before($grid_cell_copy, $prev);
        $grid_cell->unlink($grid_cell);
    }
}
