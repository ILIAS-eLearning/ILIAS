<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer;

/**
 * Class HTMLFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class HTMLFormat extends AbstractFormat {

	/**
	 * @var Template
	 */
	protected $tpl;


	/**
	 * @inheritDoc
	 */
	public function getFormatId(): string {
		return self::FORMAT_HTML;
	}


	/**
	 * @inheritDoc
	 */
	protected function getFileExtension(): string {
		return "html";
	}


	/**
	 * @inheritDoc
	 */
	protected function initTemplate(Table $component, Data $data, Filter $filter, Renderer $renderer): void {
		$this->tpl = $this->tpl_factory->getTemplate($this->tpl_path . "tpl.datatable.html", true, true);

		$this->tpl->setVariable("ID", $component->getTableId());

		$this->tpl->setVariable("TITLE", $component->getTitle());

		$this->handleNoDataText($data, $component);
	}


	/**
	 * @inheritDoc
	 */
	protected function handleColumns(Table $component, array $columns, Filter $filter, Renderer $renderer): void {
		$this->tpl->setCurrentBlock("header");

		parent::handleColumns($component, $columns, $filter, $renderer);
	}


	/**
	 * @inheritDoc
	 */
	protected function handleColumn(string $formated_column, Table $component, Column $column, Filter $filter, Renderer $renderer): void {
		$this->tpl->setVariable("HEADER", $formated_column);

		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @inheritDoc
	 */
	protected function handleRows(Table $component, array $columns, Data $data, Filter $filter, Renderer $renderer): void {
		$this->tpl->setCurrentBlock("body");

		parent::handleRows($component, $columns, $data, $filter, $renderer);
	}


	/**
	 * @inheritDoc
	 */
	protected function handleRow(Table $component, array $columns, RowData $row, Filter $filter, Renderer $renderer): void {
		$tpl = $this->tpl;

		$this->tpl = $this->tpl_factory->getTemplate($this->tpl_path . "tpl.datatablerow.html", true, true);

		$this->handleRowTemplate($component, $row);

		$this->tpl->setCurrentBlock("row");

		parent::handleRow($component, $columns, $row, $filter, $renderer);

		$tpl->setVariable("ROW", $this->tpl->get());

		$tpl->parseCurrentBlock();

		$this->tpl = $tpl;
	}


	/**
	 * @inheritDoc
	 */
	protected function handleRowTemplate(Table $component, RowData $row): void {

	}


	/**
	 * @inheritDoc
	 */
	protected function handleRowColumn(string $formated_row_column): void {
		$this->tpl->setVariable("COLUMN", $formated_row_column);

		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @inheritDoc
	 */
	protected function renderTemplate(Table $component): string {
		return $this->tpl->get();
	}


	/**
	 * @param Data  $data
	 * @param Table $component
	 */
	protected function handleNoDataText(Data $data, Table $component): void {
		if ($data->getDataCount() === 0) {
			$this->tpl->setCurrentBlock("no_data");

			$this->tpl->setVariable("NO_DATA_TEXT", $component->getDataFetcher()->getNoDataText());

			$this->tpl->parseCurrentBlock();
		}
	}
}
