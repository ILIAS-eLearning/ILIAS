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
        $grid_cell = $this->getDomNode();
        $grid_cell->parentNode->removeChild($grid_cell);
    }

    public function moveCellRight(): void
    {
        $grid_cell = $this->getDomNode();
        $next = $grid_cell->nextSibling;
        $next_copy = $next->cloneNode(true);
        $grid_cell->parentNode->insertBefore($next_copy, $grid_cell);
        $next->parentNode->removeChild($next);
    }

    public function moveCellLeft(): void
    {
        $grid_cell = $this->getDomNode();
        $prev = $grid_cell->previousSibling;
        $grid_cell_copy = $grid_cell->cloneNode(true);
        $prev->parentNode->insertBefore($grid_cell_copy, $prev);
        $grid_cell->parentNode->removeChild($grid_cell);
    }
}
