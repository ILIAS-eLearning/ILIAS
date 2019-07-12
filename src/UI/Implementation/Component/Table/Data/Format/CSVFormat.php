<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ilCSVWriter;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Renderer;

/**
 * Class CSVFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class CSVFormat extends AbstractFormat {

	/**
	 * @var ilCSVWriter
	 */
	protected $tpl;


	/**
	 * @inheritDoc
	 */
	public function getFormatId(): string {
		return self::FORMAT_CSV;
	}


	/**
	 * @inheritDoc
	 */
	protected function getFileExtension(): string {
		return "csv";
	}


	/**
	 * @inheritDoc
	 */
	protected function initTemplate(Table $component, Data $data, Filter $filter, Renderer $renderer): void {
		$this->tpl = new ilCSVWriter();

		$this->tpl->setSeparator(";");
	}


	/**
	 * @inheritDoc
	 */
	protected function handleColumns(Table $component, array $columns, Filter $filter, Renderer $renderer): void {
		parent::handleColumns($component, $columns, $filter, $renderer);

		$this->tpl->addRow();
	}


	/**
	 * @inheritDoc
	 */
	protected function handleColumn(string $formated_column, Table $component, Column $column, Filter $filter, Renderer $renderer): void {
		$this->tpl->addColumn($formated_column);
	}


	/**
	 * @inheritDoc
	 */
	protected function handleRow(Table $component, array $columns, RowData $row, Filter $filter, Renderer $renderer): void {
		parent::handleRow($component, $columns, $row, $filter, $renderer);

		$this->tpl->addRow();
	}


	/**
	 * @inheritDoc
	 */
	protected function handleRowColumn(string $formated_row_column): void {
		$this->tpl->addColumn($formated_row_column);
	}


	/**
	 * @inheritDoc
	 */
	protected function renderTemplate(Table $component): string {
		return $this->tpl->getCSVString();
	}
}
