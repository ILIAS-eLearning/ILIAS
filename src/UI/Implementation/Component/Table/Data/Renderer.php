<?php

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterStandard;
use ILIAS\UI\Component\Table\Data\Column\TableColumn;
use ILIAS\UI\Component\Table\Data\Data\TableData;
use ILIAS\UI\Component\Table\Data\DataTable;
use ILIAS\UI\Component\Table\Data\Export\TableExportFormat;
use ILIAS\UI\Component\Table\Data\Filter\Sort\TableFilterSortField;
use ILIAS\UI\Component\Table\Data\Filter\Storage\TableFilterStorage;
use ILIAS\UI\Component\Table\Data\Filter\TableFilter;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;
use ilUtil;
use Throwable;

/**
 * Class Renderer
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Renderer extends AbstractComponentRenderer {

	/**
	 * @var FilterStandard|null
	 */
	protected $filter_form = null;
	/**
	 * @var Container
	 */
	protected $dic;


	/**
	 * @inheritDoc
	 */
	protected function getComponentInterfaceName(): array {
		return [ DataTable::class ];
	}


	/**
	 * @inheritDoc
	 */
	public function render(Component $component, RendererInterface $default_renderer): string {
		global $DIC;

		$this->dic = $DIC;

		$this->dic->language()->loadLanguageModule(DataTable::LANG_MODULE);

		$this->checkComponent($component);

		return $this->renderStandard($component, $default_renderer);
	}


	/**
	 * @param DataTable         $component
	 * @param RendererInterface $renderer
	 *
	 * @return string
	 */
	protected function renderStandard(DataTable $component, RendererInterface $renderer): string {
		$filter = $component->getFilterStorage()->read($component->getTableId(), $this->dic->user()->getId(), $component->getFactory());

		$filter = $this->handleFilterInput($component, $filter);

		$filter = $this->handleDefaultSort($component, $filter);

		$filter = $this->handleDefaultSelectedColumns($component, $filter);

		$columns = $this->getColumns($component, $filter);

		$data = $this->handleFetchData($component, $filter);

		$this->handleExport($component, $columns, $data, $renderer);

		$tpl = $this->getTemplate("Data/table.html", true, true);

		$tpl->setVariable("ID", $component->getTableId());

		$tpl->setVariable("TITLE", $component->getTitle());

		$this->handleFilterForm($tpl, $component, $filter, $renderer);

		$this->handleActionsPanel($tpl, $component, $filter, $data, $renderer);

		$this->handleColumns($tpl, $component, $columns, $filter, $renderer);

		$this->handleRows($tpl, $component, $columns, $data, $renderer);

		$this->handleDisplayCount($tpl, $filter, $data);

		$this->handleMultipleActions($tpl, $component, $renderer);

		$html = $tpl->get();

		$component->getFilterStorage()->store($filter);

		return $html;
	}


	/**
	 * @inheritDoc
	 */
	public function registerResources(ResourceRegistry $registry): void {
		parent::registerResources($registry);

		$registry->register('./src/UI/templates/js/Table/Data/datatable.min.js');
	}


	/**
	 * @param DataTable   $component
	 * @param TableFilter $filter
	 *
	 * @return TableColumn[]
	 */
	protected function getColumns(DataTable $component, TableFilter $filter): array {
		return array_filter($component->getColumns(), function (TableColumn $column) use ($filter): bool {
			if ($column->isSelectable()) {
				return in_array($column->getKey(), $filter->getSelectedColumns());
			} else {
				return true;
			}
		});
	}


	/**
	 * @param DataTable         $component
	 * @param TableFilter       $filter
	 * @param RendererInterface $renderer
	 *
	 * @return Component
	 */
	protected function getColumnsSelector(DataTable $component, TableFilter $filter, RendererInterface $renderer): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (TableColumn $column) use ($component, $filter, $renderer): Shy {
			return $this->dic->ui()->factory()->button()->shy($renderer->render([
				$this->dic->ui()->factory()->symbol()->glyph()->add(),
				$this->dic->ui()->factory()->legacy($column->getTitle())
			]), self::getActionUrl($component->getActionUrl(), [ TableFilterStorage::VAR_SELECT_COLUMN => $column->getKey() ], $component->getTableId()));
		}, array_filter($component->getColumns(), function (TableColumn $column) use ($filter): bool {
			return ($column->isSelectable() && !in_array($column->getKey(), $filter->getSelectedColumns()));
		})))->withLabel($this->dic->language()->txt(DataTable::LANG_MODULE . "_add_columns"));
	}


	/**
	 * @param DataTable $component
	 *
	 * @return Component
	 */
	protected function getExportsSelector(DataTable $component): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (TableExportFormat $export_format) use ($component): Shy {
			return $this->dic->ui()->factory()->button()
				->shy($export_format->getTitle(), self::getActionUrl($component->getActionUrl(), [ TableFilterStorage::VAR_EXPORT_FORMAT_ID => $export_format->getExportId() ], $component->getTableId()));
		}, $component->getExportFormats()))->withLabel($this->dic->language()->txt(DataTable::LANG_MODULE . "_export"));
	}


	/**
	 * @param DataTable         $component
	 * @param TableFilter       $filter
	 * @param TableData         $data
	 * @param RendererInterface $renderer
	 *
	 * @return Component
	 */
	protected function getPagesSelector(DataTable $component, TableFilter $filter, TableData $data, RendererInterface $renderer): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (int $page) use ($component, $filter, $renderer): Component {
			if ($filter->getCurrentPage() === $page) {
				return $this->dic->ui()->factory()->legacy($renderer->render([
					$this->dic->ui()->factory()->symbol()->glyph()->apply(),
					$this->dic->ui()->factory()->legacy(strval($page))
				]));
			} else {
				return $this->dic->ui()->factory()->button()
					->shy(strval($page), self::getActionUrl($component->getActionUrl(), [ TableFilterStorage::VAR_CURRENT_PAGE => $page ], $component->getTableId()));
			}
		}, range(1, $filter->getTotalPages($data->getMaxCount()))))->withLabel(sprintf($this->dic->language()->txt(DataTable::LANG_MODULE
			. "_pages"), $filter->getCurrentPage(), $filter->getTotalPages($data->getMaxCount())));
	}


	/**
	 * @param DataTable         $component
	 * @param TableFilter       $filter
	 * @param RendererInterface $renderer
	 *
	 * @return Component
	 */
	protected function getRowsPerPageSelector(DataTable $component, TableFilter $filter, RendererInterface $renderer): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (int $count) use ($component, $filter, $renderer): Component {
			if ($filter->getRowsCount() === $count) {
				return $this->dic->ui()->factory()->legacy($renderer->render([
					$this->dic->ui()->factory()->symbol()->glyph()->apply(),
					$this->dic->ui()->factory()->legacy(strval($count))
				]));
			} else {
				return $this->dic->ui()->factory()->button()
					->shy(strval($count), self::getActionUrl($component->getActionUrl(), [ TableFilterStorage::VAR_ROWS_COUNT => $count ], $component->getTableId()));
			}
		}, TableFilter::ROWS_COUNT))->withLabel(sprintf($this->dic->language()->txt(DataTable::LANG_MODULE
			. "_rows_per_page"), $filter->getRowsCount()));
	}


	/**
	 * @param Template          $tpl
	 * @param DataTable         $component
	 * @param TableFilter       $filter
	 * @param TableData         $data
	 * @param RendererInterface $renderer
	 */
	protected function handleActionsPanel(Template $tpl, DataTable $component, TableFilter $filter, TableData $data, RendererInterface $renderer): void {
		$tpl->setVariable("ACTIONS", $renderer->render($this->dic->ui()->factory()->panel()->standard("", [
			$this->getPagesSelector($component, $filter, $data, $renderer),
			$this->getColumnsSelector($component, $filter, $renderer),
			$this->getRowsPerPageSelector($component, $filter, $renderer),
			$this->getExportsSelector($component)
		])));
	}


	/**
	 * @param Template          $tpl
	 * @param DataTable         $component
	 * @param TableColumn[]     $columns
	 * @param TableFilter       $filter
	 * @param RendererInterface $renderer
	 */
	protected function handleColumns(Template $tpl, DataTable $component, array $columns, TableFilter $filter, RendererInterface $renderer): void {
		$tpl->setCurrentBlock("header");

		if (count($component->getMultipleActions()) > 0) {
			$tpl->setVariable("HEADER", "");

			$tpl->parseCurrentBlock();
		}

		foreach ($columns as $column) {
			$deselect_button = $this->dic->ui()->factory()->legacy("");
			$sort_button = $column->getColumnFormater()->formatHeader($column, $component->getTableId(), $renderer);
			$remove_sort_button = $this->dic->ui()->factory()->legacy("");

			if ($column->isSelectable()) {
				$deselect_button = $this->dic->ui()->factory()->button()->shy($renderer->render($this->dic->ui()->factory()->symbol()->glyph()
					->remove()), self::getActionUrl($component->getActionUrl(), [ TableFilterStorage::VAR_DESELECT_COLUMN => $column->getKey() ], $component->getTableId()));
			}

			if ($column->isSortable()) {
				$sort_field = $filter->getSortField($column->getKey());

				if ($sort_field !== null) {
					if ($sort_field->getSortFieldDirection() === TableFilterSortField::SORT_DIRECTION_DOWN) {
						$sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render([
							$this->dic->ui()->factory()->legacy($sort_button),
							$this->dic->ui()->factory()->symbol()->glyph()->sortDescending()
						]), self::getActionUrl($component->getActionUrl(), [
							TableFilterStorage::VAR_SORT_FIELD => $column->getKey(),
							TableFilterStorage::VAR_SORT_FIELD_DIRECTION => TableFilterSortField::SORT_DIRECTION_UP
						], $component->getTableId()));
					} else {
						$sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render([
							$this->dic->ui()->factory()->legacy($sort_button),
							$this->dic->ui()->factory()->symbol()->glyph()->sortAscending()
						]), self::getActionUrl($component->getActionUrl(), [
							TableFilterStorage::VAR_SORT_FIELD => $column->getKey(),
							TableFilterStorage::VAR_SORT_FIELD_DIRECTION => TableFilterSortField::SORT_DIRECTION_DOWN
						], $component->getTableId()));
					}

					$remove_sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render($this->dic->ui()->factory()->symbol()->glyph()
						->back() // TODO: Other icon for remove sort
					), self::getActionUrl($component->getActionUrl(), [ TableFilterStorage::VAR_REMOVE_SORT_FIELD => $column->getKey() ], $component->getTableId()));
				} else {
					$sort_button = $this->dic->ui()->factory()->button()->shy($sort_button, self::getActionUrl($component->getActionUrl(), [
						TableFilterStorage::VAR_SORT_FIELD => $column->getKey(),
						TableFilterStorage::VAR_SORT_FIELD_DIRECTION => TableFilterSortField::SORT_DIRECTION_UP
					], $component->getTableId()));
				}
			} else {
				$sort_button = $this->dic->ui()->factory()->legacy($sort_button);
			}

			$tpl->setVariable("HEADER", $renderer->render([ $deselect_button, $sort_button, $remove_sort_button ]));

			$tpl->parseCurrentBlock();
			// TODO: Dragable columns
		}
	}


	/**
	 * @param DataTable   $component
	 * @param TableFilter $filter
	 *
	 * @return TableFilter
	 */
	protected function handleDefaultSelectedColumns(DataTable $component, TableFilter $filter): TableFilter {
		if (!$filter->isFilterSet() && empty($filter->getSelectedColumns())) {
			$filter = $filter->withSelectedColumns(array_map(function (TableColumn $column): string {
				return $column->getKey();
			}, array_filter($component->getColumns(), function (TableColumn $column): bool {
				return ($column->isSelectable() && $column->isDefaultSelected());
			})));
		}

		return $filter;
	}


	/**
	 * @param DataTable   $component
	 * @param TableFilter $filter
	 *
	 * @return TableFilter
	 */
	protected function handleDefaultSort(DataTable $component, TableFilter $filter): TableFilter {
		if (!$filter->isFilterSet() && empty($filter->getSortFields())) {
			$filter = $filter->withSortFields(array_map(function (TableColumn $column) use ($component): TableFilterSortField {
				return $component->getFactory()->filterSortField($column->getKey(), $column->getDefaultSortDirection());
			}, array_filter($component->getColumns(), function (TableColumn $column): bool {
				return ($column->isSortable() && $column->isDefaultSort());
			})));
		}

		return $filter;
	}


	/**
	 * @param Template    $tpl
	 * @param TableFilter $filter
	 * @param TableData   $data
	 */
	protected function handleDisplayCount(Template $tpl, TableFilter $filter, TableData $data): void {
		$tpl->setVariable("COUNT", sprintf($this->dic->language()->txt(DataTable::LANG_MODULE . "_count"), ($filter->getLimitStart()
			+ 1), min($filter->getLimitEnd(), $data->getMaxCount()), $data->getMaxCount()));
	}


	/**
	 * @param DataTable         $component
	 * @param TableColumn[]     $columns
	 * @param TableData         $data
	 * @param RendererInterface $renderer
	 */
	protected function handleExport(DataTable $component, array $columns, TableData $data, RendererInterface $renderer): void {
		$export_format_id = strval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_EXPORT_FORMAT_ID, $component->getTableId())));

		if (empty($export_format_id)) {
			return;
		}

		/**
		 * @var TableExportFormat|null $export_format
		 */
		$export_format = current(array_filter($component->getExportFormats(), function (TableExportFormat $export_format) use ($export_format_id): bool {
			return ($export_format->getExportId() === $export_format_id);
		}));

		if ($export_format === null) {
			return;
		}

		$columns = array_filter($columns, function (TableColumn $column): bool {
			return ($column->getExportFormater() !== null);
		});

		$columns_ = [];
		foreach ($columns as $column) {
			$columns_[] = $column->getExportFormater()->formatHeader($export_format, $column, $component->getTableId(), $renderer);
		}

		$rows_ = [];
		foreach ($data->getData() as $row) {
			$row_ = [];
			foreach ($columns as $column) {
				$row_[] = $column->getExportFormater()->formatRow($export_format, $column, $row, $component->getTableId(), $renderer);
			}
			$rows_[] = $row_;
		}

		$export_format->export($columns_, $rows_, $component->getTitle(), $component->getTableId(), $renderer);
	}


	/**
	 * @param DataTable   $component
	 * @param TableFilter $filter
	 *
	 * @return TableData
	 */
	protected function handleFetchData(DataTable $component, TableFilter $filter): TableData {
		if (!$component->isFetchDataNeedsFilterFirstSet() || $filter->isFilterSet()) {
			$data = $component->getDataFetcher()->fetchData($filter, $component->getFactory());
		} else {
			$data = $component->getFactory()->data([], 0);
		}

		return $data;
	}


	/**
	 * @param Template          $tpl
	 * @param DataTable         $component
	 * @param TableFilter       $filter
	 * @param RendererInterface $renderer
	 */
	protected function handleFilterForm(Template $tpl, DataTable $component, TableFilter $filter, RendererInterface $renderer): void {
		if (count($component->getFilterFields()) === 0) {
			return;
		}

		$this->initFilterForm($component, $filter);

		$filter_form = $renderer->render($this->filter_form);

		switch ($component->getFilterPosition()) {
			case TableFilter::FILTER_POSITION_BOTTOM:
				$tpl->setVariable("FILTER_FORM_BOTTOM", $filter_form);
				break;

			case TableFilter::FILTER_POSITION_TOP:
			default:
				$tpl->setVariable("FILTER_FORM_TOP", $filter_form);
				break;
		}
	}


	/**
	 * @param DataTable   $component
	 * @param TableFilter $filter
	 *
	 * @return TableFilter
	 */
	protected function handleFilterInput(DataTable $component, TableFilter $filter): TableFilter {
		//if (strtoupper(filter_input(INPUT_SERVER, "REQUEST_METHOD")) === "POST") {

		$sort_field = strval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_SORT_FIELD, $component->getTableId())));
		$sort_field_direction = intval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_SORT_FIELD_DIRECTION, $component->getTableId())));
		if (!empty($sort_field) && !empty($sort_field_direction)) {
			$filter = $filter->addSortField($component->getFactory()->filterSortField($sort_field, $sort_field_direction));

			$filter = $filter->withFilterSet(true);
		}

		$remove_sort_field = strval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_REMOVE_SORT_FIELD, $component->getTableId())));
		if (!empty($remove_sort_field)) {
			$filter = $filter->removeSortField($remove_sort_field);

			$filter = $filter->withFilterSet(true);
		}

		$rows_count = intval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_ROWS_COUNT, $component->getTableId())));
		if (!empty($rows_count)) {
			$filter = $filter->withRowsCount($rows_count);
			$filter = $filter->withCurrentPage(); // Reset current page on row change
		}

		$current_page = intval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_CURRENT_PAGE, $component->getTableId())));
		if (!empty($current_page)) {
			$filter = $filter->withCurrentPage($current_page);

			$filter = $filter->withFilterSet(true);
		}

		$select_column = strval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_SELECT_COLUMN, $component->getTableId())));
		if (!empty($select_column)) {
			$filter = $filter->selectColumn($select_column);

			$filter = $filter->withFilterSet(true);
		}

		$deselect_column = strval(filter_input(INPUT_GET, self::actionParameter(TableFilterStorage::VAR_DESELECT_COLUMN, $component->getTableId())));
		if (!empty($deselect_column)) {
			$filter = $filter->deselectColumn($deselect_column);

			$filter = $filter->withFilterSet(true);
		}

		if (count($component->getFilterFields()) > 0) {
			$this->initFilterForm($component, $filter);
			try {
				$data = $this->dic->uiService()->filter()->getData($this->filter_form);

				if (is_array($data)) {
					$filter = $filter->withFieldValues($data);

					$filter = $filter->withFilterSet(true);
				}
			} catch (Throwable $ex) {

			}
		}

		return $filter;
	}


	/**
	 * @param Template          $tpl
	 * @param DataTable         $component
	 * @param RendererInterface $renderer
	 */
	protected function handleMultipleActions(Template $tpl, DataTable $component, RendererInterface $renderer): void {
		if (count($component->getMultipleActions()) === 0) {
			return;
		}

		$tpl_checkbox = $this->getTemplate("Data/checkbox.html", true, true);

		$tpl_checkbox->setVariable("TXT", $this->dic->language()->txt(DataTable::LANG_MODULE . "_select_all"));

		$multiple_actions = [
			$this->dic->ui()->factory()->legacy($tpl_checkbox->get()),
			$this->dic->ui()->factory()->dropdown()->standard(array_map(function (string $title, string $action): Shy {
				return $this->dic->ui()->factory()->button()->shy($title, $action);
			}, array_keys($component->getMultipleActions()), $component->getMultipleActions()))->withLabel($this->dic->language()
				->txt(DataTable::LANG_MODULE . "_multiple_actions"))
		];

		$tpl->setVariable("MULTIPLE_ACTIONS_TOP", $renderer->render($multiple_actions));
		$tpl->setVariable("MULTIPLE_ACTIONS_BOTTOM", $renderer->render($multiple_actions));
	}


	/**
	 * @param Template          $tpl
	 * @param DataTable         $component
	 * @param TableColumn[]     $columns
	 * @param TableData         $data
	 * @param RendererInterface $renderer
	 */
	protected function handleRows(Template $tpl, DataTable $component, array $columns, TableData $data, RendererInterface $renderer): void {
		$tpl->setCurrentBlock("body");

		foreach ($data->getData() as $row) {
			$tpl_row = $this->getTemplate("Data/row.html", true, true);

			$tpl_row->setCurrentBlock("row");

			if (count($component->getMultipleActions()) > 0) {
				$tpl_checkbox = $this->getTemplate("Data/checkbox.html", true, true);

				$tpl_checkbox->setVariable("POST_VAR", self::actionParameter(DataTable::MULTIPLE_SELECT_POST_VAR, $component->getTableId()) . "[]");

				$tpl_checkbox->setVariable("ROW_ID", $row->getRowId());

				$tpl_row->setVariable("COLUMN", $tpl_checkbox->get());

				$tpl_row->parseCurrentBlock();
			}

			foreach ($columns as $column) {
				$value = $column->getColumnFormater()->formatRow($column, $row, $component->getTableId(), $renderer);

				if ($value === "") {
					$value = "&nbsp;";
				}

				$tpl_row->setVariable("COLUMN", $value);

				$tpl_row->parseCurrentBlock();
			}

			$tpl->setVariable("ROW", $tpl_row->get());

			$tpl->parseCurrentBlock();
		}
	}


	/**
	 * @param DataTable   $component
	 * @param TableFilter $filter
	 */
	protected function initFilterForm(DataTable $component, TableFilter $filter): void {
		if ($this->filter_form === null) {
			$filter_fields = $component->getFilterFields();

			$this->filter_form = $this->dic->uiService()->filter()
				->standard($component->getTableId(), self::getActionUrl($component->getActionUrl(), [], $component->getTableId()), $filter_fields, array_fill(0, count($filter_fields), false), true, true);
		}
	}


	/**
	 * @param string $action_url
	 * @param string $table_id
	 *
	 * @return string
	 */
	public static function getActionUrl(string $action_url, array $params, string $table_id): string {
		foreach ($params as $key => $value) {
			$action_url = ilUtil::appendUrlParameterString($action_url, self::actionParameter($key, $table_id) . "=" . $value);
		}

		return $action_url;
	}


	/**
	 * @param string $key
	 * @param string $table_id
	 *
	 * @return string
	 */
	public static function actionParameter(string $key, string $table_id): string {
		return $key . "_" . $table_id;
	}
}
