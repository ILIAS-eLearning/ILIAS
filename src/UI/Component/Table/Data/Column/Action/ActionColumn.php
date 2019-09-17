<?php

declare(strict_types=1);

namespace ILIAS\UI\Component\Table\Data\Column\Action;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;

/**
 * Interface ActionColumn
 *
 * @package ILIAS\UI\Component\Table\Data\Column\Action
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface ActionColumn extends Column
{

    /**
     * @param RowData $row
     *
     * @return string[]
     */
    public function getActions(RowData $row) : array;
}
