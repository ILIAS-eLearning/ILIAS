<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ilExcel;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Component\Table\Data\Settings\Settings;
use ILIAS\UI\Renderer;

/**
 * Class ExcelFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class ExcelFormat extends AbstractFormat
{

    /**
     * @var ilExcel
     */
    protected $tpl;
    /**
     * @var int
     */
    protected $current_col = 0;
    /**
     * @var int
     */
    protected $current_row = 1;


    /**
     * @inheritDoc
     */
    public function getFormatId() : string
    {
        return self::FORMAT_EXCEL;
    }


    /**
     * @inheritDoc
     */
    protected function getFileExtension() : string
    {
        return "xlsx";
    }


    /**
     * @inheritDoc
     */
    protected function initTemplate(Table $component, Data $data, Settings $settings, Renderer $renderer) : void
    {
        $this->tpl = new ilExcel();

        $this->tpl->addSheet($component->getTitle());
    }


    /**
     * @inheritDoc
     */
    public function getTemplate() : object
    {
        return (object) [
            "tpl"         => $this->tpl,
            "current_row" => $this->current_row,
            "current_col" => $this->current_col
        ];
    }


    /**
     * @inheritDoc
     */
    protected function handleColumns(Table $component, array $columns, Settings $settings, Renderer $renderer) : void
    {
        $this->current_col = 0;

        parent::handleColumns($component, $columns, $settings, $renderer);

        $this->current_row++;
    }


    /**
     * @inheritDoc
     */
    protected function handleColumn(string $formated_column, Table $component, Column $column, Settings $settings, Renderer $renderer) : void
    {
        $this->tpl->setCell($this->current_row, $this->current_col, $formated_column);

        $this->current_col++;
    }


    /**
     * @inheritDoc
     */
    protected function handleRow(Table $component, array $columns, RowData $row, Settings $settings, Renderer $renderer) : void
    {
        $this->current_col = 0;

        parent::handleRow($component, $columns, $row, $settings, $renderer);

        $this->current_row++;
    }


    /**
     * @inheritDoc
     */
    protected function handleRowColumn(string $formated_row_column) : void
    {
        $this->tpl->setCell($this->current_row, $this->current_col, $formated_row_column);

        $this->current_col++;
    }


    /**
     * @inheritDoc
     */
    protected function renderTemplate(Table $component) : string
    {
        $tmp_file = $this->tpl->writeToTmpFile();

        $data = file_get_contents($tmp_file);

        unlink($tmp_file);

        return $data;
    }
}
