<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use GuzzleHttp\Psr7\Stream;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Render\TemplateFactory;
use ILIAS\UI\Renderer;
use ilMimeTypeUtil;

/**
 * Class AbstractFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
abstract class AbstractFormat implements Format {

	/**
	 * @var Container
	 */
	protected $dic;
	/**
	 * @var object
	 */
	protected $tpl;
	/**
	 * @var TemplateFactory
	 */
	protected $tpl_factory;
	/**
	 * @var string
	 */
	protected $tpl_path;


	/**
	 * @inheritDoc
	 */
	public function __construct(Container $dic) {
		$this->dic = $dic;
	}


	/**
	 * @inheritDoc
	 */
	public function getDisplayTitle(): string {
		return $this->dic->language()->txt(Table::LANG_MODULE . "_format_" . $this->getFormatId());
	}


	/**
	 * @inheritDoc
	 */
	public function getOutputType(): int {
		return self::OUTPUT_TYPE_DOWNLOAD;
	}


	/**
	 * @inheritDoc
	 */
	public function getTemplate(): object {
		return $this->tpl;
	}


	/**
	 * @return string
	 */
	protected abstract function getFileExtension(): string;


	/**
	 * @inheritDoc
	 */
	public function render(TemplateFactory $tpl_factory, string $tpl_path, Table $component, Data $data, Filter $filter, Renderer $renderer): string {
		$this->tpl_factory = $tpl_factory;
		$this->tpl_path = $tpl_path;

		$this->initTemplate($component, $data, $filter, $renderer);

		$columns = $this->getColumns($component, $filter);

		$this->handleColumns($component, $columns, $filter, $renderer);

		$this->handleRows($component, $columns, $data, $filter, $renderer);

		return $this->renderTemplate($component);
	}


	/**
	 * @inheritDoc
	 */
	public function devliver(string $data, Table $component): void {
		$filename = $component->getTitle() . "." . $this->getFileExtension();

		$stream = new Stream(fopen("php://memory", "rw"));
		$stream->write($data);

		$this->dic->http()->saveResponse($this->dic->http()->response()->withBody($stream)->withHeader("Content-Disposition", 'attachment; filename="'
			. $filename . '"')// Filename
		->withHeader("Content-Type", ilMimeTypeUtil::APPLICATION__OCTET_STREAM)// Force download
		->withHeader("Expires", "0")->withHeader("Pragma", "public"));// No cache

		$this->dic->http()->sendResponse();

		exit;
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Column[]
	 */
	protected function getColumnsBase(Table $component, Filter $filter): array {
		return array_filter($component->getColumns(), function (Column $column) use ($filter): bool {
			if ($column->isSelectable()) {
				return in_array($column->getKey(), $filter->getSelectedColumns());
			} else {
				return true;
			}
		});
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Column[]
	 */
	protected function getColumnsForExport(Table $component, Filter $filter): array {
		return array_filter($this->getColumnsBase($component, $filter), function (Column $column): bool {
			return $column->isExportable();
		});
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Column[]
	 */
	protected function getColumns(Table $component, Filter $filter): array {
		return $this->getColumnsForExport($component, $filter);
	}


	/**
	 * @param Table    $component
	 * @param Data     $data
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 */
	protected abstract function initTemplate(Table $component, Data $data, Filter $filter, Renderer $renderer): void;


	/**
	 * @param Table    $component
	 * @param Column[] $columns
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 */
	protected function handleColumns(Table $component, array $columns, Filter $filter, Renderer $renderer): void {
		foreach ($columns as $column) {
			$this->handleColumn($column->getFormater()
				->formatHeader($this, $column, $component->getTableId(), $renderer), $component, $column, $filter, $renderer);
		}
	}


	/**
	 * @param string   $formated_column
	 * @param Table    $component
	 * @param Column   $column
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 *
	 * @return mixed
	 */
	protected abstract function handleColumn(string $formated_column, Table $component, Column $column, Filter $filter, Renderer $renderer);


	/**
	 * @param Table    $component
	 * @param Column[] $columns
	 * @param Data     $data
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 */
	protected function handleRows(Table $component, array $columns, Data $data, Filter $filter, Renderer $renderer): void {
		foreach ($data->getData() as $row) {
			$this->handleRow($component, $columns, $row, $filter, $renderer);
		}
	}


	/**
	 * @param Table    $component
	 * @param Column[] $columns
	 * @param RowData  $row
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 */
	protected function handleRow(Table $component, array $columns, RowData $row, Filter $filter, Renderer $renderer): void {
		foreach ($columns as $column) {
			$this->handleRowColumn($column->getFormater()->formatRow($this, $column, $row, $component->getTableId(), $renderer));
		}
	}


	/**
	 * @param string $formated_row_column
	 */
	protected abstract function handleRowColumn(string $formated_row_column);


	/**
	 * @param Table $component
	 *
	 * @return string
	 */
	protected abstract function renderTemplate(Table $component): string;
	// TODO: Footer
}
