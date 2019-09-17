<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Column\Formater;

use ilExcel;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Renderer;

/**
 * Class DefaultFormater
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Column\Formater
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DefaultFormater extends AbstractFormater
{

    /**
     * @inheritDoc
     */
    public function formatHeaderCell(Format $format, Column $column, string $table_id, Renderer $renderer) : string
    {
        $title = $column->getTitle();

        switch ($format->getFormatId()) {
            case Format::FORMAT_PDF:
                return "<b>{$title}</b>";

            case Format::FORMAT_EXCEL:
                /**
                 * @var ilExcel $tpl
                 */ $tpl = $format->getTemplate()->tpl;
                $cord = $tpl->getColumnCoord($format->getTemplate()->current_col) . $format->getTemplate()->current_col;
                $tpl->setBold($cord . ":" . $cord);

                return $title;

            default:
                return $title;
        }
    }


    /**
     * @inheritDoc
     */
    public function formatRowCell(Format $format, $value, Column $column, RowData $row, string $table_id, Renderer $renderer) : string
    {
        $value = strval($value);

        switch ($format->getFormatId()) {
            case Format::FORMAT_BROWSER:
                if ($value === "") {
                    $value = "&nbsp;";
                }

                return $value;

            default:
                return $value;
        }
    }
}
