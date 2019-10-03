<?php

declare(strict_types=1);

namespace ILIAS\UI\Component\Table\Data\Column\Formater;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Renderer;

/**
 * Interface Formater
 *
 * @package ILIAS\UI\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Formater
{

    /**
     * @param Format   $format
     * @param Column   $column
     * @param string   $table_id
     * @param Renderer $renderer
     *
     * @return string
     */
    public function formatHeaderCell(Format $format, Column $column, string $table_id, Renderer $renderer) : string;


    /**
     * @param Format   $format
     * @param mixed    $value
     * @param Column   $column
     * @param RowData  $row
     * @param string   $table_id
     * @param Renderer $renderer
     *
     * @return string
     */
    public function formatRowCell(Format $format, $value, Column $column, RowData $row, string $table_id, Renderer $renderer) : string;
}
