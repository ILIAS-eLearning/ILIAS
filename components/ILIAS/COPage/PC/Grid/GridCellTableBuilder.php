<?php

/* Copyright (c) 1998-2024 ILIAS open source, Extended GPL, see docs/LICENSE */

declare(strict_types=1);

namespace ILIAS\COPage\PC\Grid;

use ILIAS\Repository\RetrievalInterface;
use ILIAS\COPage\InternalDomainService;
use ILIAS\COPage\InternalGUIService;
use ILIAS\Repository\Table\CommonTableBuilder;
use ILIAS\Repository\Table\TableAdapterGUI;
use ilPCGrid;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class GridCellTableBuilder extends CommonTableBuilder
{
    public function __construct(
        protected InternalDomainService $domain,
        protected InternalGUIService $gui,
        protected ilPCGrid $grid,
        object $parent_gui,
        string $parent_cmd
    ) {
        parent::__construct($parent_gui, $parent_cmd, false);
    }

    protected function getId(): string
    {
        return "pcgrid";
    }

    protected function getTitle(): string
    {
        return $this->domain->lng()->txt("cont_ed_grid_col_widths");
    }

    protected function getRetrieval(): RetrievalInterface
    {
        return $this->domain->pc()->gridCellRetrieval($this->grid);
    }

    protected function transformRow(array $data_row): array
    {
        return $data_row;
    }

    protected function getOrderingCommand(): string
    {
        return "savePositions";
    }

    protected function build(TableAdapterGUI $table): TableAdapterGUI
    {
        $lng = $this->domain->lng();
        $table = $table
            ->textColumn("s", $lng->txt("cont_grid_width_s"), false)
            ->textColumn("m", $lng->txt("cont_grid_width_m"), false)
            ->textColumn("l", $lng->txt("cont_grid_width_l"), false)
            ->textColumn("xl", $lng->txt("cont_grid_width_xl"), false)
            ->multiAction("confirmCellDeletion", $lng->txt("delete"), true)
            ->singleAction("editWidths", $lng->txt("edit"), true);

        return $table;
    }
}
