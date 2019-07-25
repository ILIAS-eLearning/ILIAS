<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use GuzzleHttp\Psr7\Stream;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Settings;
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
	public function render(TemplateFactory $tpl_factory, string $tpl_path, Table $component, Data $data, Settings $user_table_settings, Renderer $renderer): string {
		$this->tpl_factory = $tpl_factory;
		$this->tpl_path = $tpl_path;

		$this->initTemplate($component, $data, $user_table_settings, $renderer);

		$columns = $this->getColumns($component, $user_table_settings);

		$this->handleColumns($component, $columns, $user_table_settings, $renderer);

		$this->handleRows($component, $columns, $data, $user_table_settings, $renderer);

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
	 * @param Table    $component
	 * @param Settings $user_table_settings
	 *
	 * @return Column[]
	 */
	protected function getColumnsBase(Table $component, Settings $user_table_settings): array {
		return array_filter($component->getColumns(), function (Column $column) use ($user_table_settings): bool {
			if ($column->isSelectable()) {
				return in_array($column->getKey(), $user_table_settings->getSelectedColumns());
			} else {
				return true;
			}
		});
	}


	/**
	 * @param Table    $component
	 * @param Settings $user_table_settings
	 *
	 * @return Column[]
	 */
	protected function getColumnsForExport(Table $component, Settings $user_table_settings): array {
		return array_filter($this->getColumnsBase($component, $user_table_settings), function (Column $column): bool {
			return $column->isExportable();
		});
	}


	/**
	 * @param Table    $component
	 * @param Settings $user_table_settings
	 *
	 * @return Column[]
	 */
	protected function getColumns(Table $component, Settings $user_table_settings): array {
		return $this->getColumnsForExport($component, $user_table_settings);
	}


	/**
	 * @param Table    $component
	 * @param Data     $data
	 * @param Settings $user_table_settings
	 * @param Renderer $renderer
	 */
	protected abstract function initTemplate(Table $component, Data $data, Settings $user_table_settings, Renderer $renderer): void;


	/**
	 * @param Table    $component
	 * @param Column[] $columns
	 * @param Settings $user_table_settings
	 * @param Renderer $renderer
	 */
	protected function handleColumns(Table $component, array $columns, Settings $user_table_settings, Renderer $renderer): void {
		foreach ($columns as $column) {
			$this->handleColumn($column->getFormater()
				->formatHeaderCell($this, $column, $component->getTableId(), $renderer), $component, $column, $user_table_settings, $renderer);
		}
	}


	/**
	 * @param string   $formated_column
	 * @param Table    $component
	 * @param Column   $column
	 * @param Settings $user_table_settings
	 * @param Renderer $renderer
	 *
	 * @return mixed
	 */
	protected abstract function handleColumn(string $formated_column, Table $component, Column $column, Settings $user_table_settings, Renderer $renderer);


	/**
	 * @param Table    $component
	 * @param Column[] $columns
	 * @param Data     $data
	 * @param Settings $user_table_settings
	 * @param Renderer $renderer
	 */
	protected function handleRows(Table $component, array $columns, Data $data, Settings $user_table_settings, Renderer $renderer): void {
		foreach ($data->getData() as $row) {
			$this->handleRow($component, $columns, $row, $user_table_settings, $renderer);
		}
	}


	/**
	 * @param Table    $component
	 * @param Column[] $columns
	 * @param RowData  $row
	 * @param Settings $user_table_settings
	 * @param Renderer $renderer
	 */
	protected function handleRow(Table $component, array $columns, RowData $row, Settings $user_table_settings, Renderer $renderer): void {
		foreach ($columns as $column) {
			$this->handleRowColumn($column->getFormater()
				->formatRowCell($this, $column, $row, $row($column->getKey()), $component->getTableId(), $renderer));
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
