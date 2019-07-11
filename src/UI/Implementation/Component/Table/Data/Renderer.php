<?php

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterStandard;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField;
use ILIAS\UI\Component\Table\Data\Filter\Storage\FilterStorage;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
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
		return [ Table::class ];
	}


	/**
	 * @inheritDoc
	 *
	 * @param Table $component
	 */
	public function render(Component $component, RendererInterface $default_renderer): string {
		global $DIC;

		$this->dic = $DIC;

		$this->dic->language()->loadLanguageModule(Table::LANG_MODULE);

		$this->checkComponent($component);

		return $this->renderDataTable($component, $default_renderer);
	}


	/**
	 * @param Table             $component
	 * @param RendererInterface $renderer
	 *
	 * @return string
	 */
	protected function renderDataTable(Table $component, RendererInterface $renderer): string {
		$filter = $component->getFilterStorage()->read($component->getTableId(), $this->dic->user()->getId());

		$filter = $this->handleFilterInput($component, $filter);

		$filter = $this->handleDefaultSort($component, $filter);

		$filter = $this->handleDefaultSelectedColumns($component, $filter);

		$columns = $this->getColumns($component, $filter);

		$data = $this->handleFetchData($component, $filter);

		$this->handleExport($component, $columns, $data, $renderer);

		$tpl = $this->getTemplate("tpl.datatable.html", true, true);

		$tpl->setVariable("ID", $component->getTableId());

		$tpl->setVariable("TITLE", $component->getTitle());

		$this->handleFilterForm($tpl, $component, $filter, $renderer);

		$this->handleActionsPanel($tpl, $component, $filter, $data, $renderer);

		$this->handleColumns($tpl, $component, $columns, $filter, $renderer);

		$this->handleRows($tpl, $component, $columns, $data, $renderer);

		$this->handleNoDataText($tpl, $data, $component);

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

		$registry->register("./src/UI/templates/js/Table/datatable.min.js");
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Column[]
	 */
	protected function getColumns(Table $component, Filter $filter): array {
		return array_filter($component->getColumns(), function (Column $column) use ($filter): bool {
			if ($column->isSelectable()) {
				return in_array($column->getKey(), $filter->getSelectedColumns());
			} else {
				return true;
			}
		});
	}


	/**
	 * @param Table             $component
	 * @param Filter            $filter
	 * @param RendererInterface $renderer
	 *
	 * @return Component
	 */
	protected function getColumnsSelector(Table $component, Filter $filter, RendererInterface $renderer): Component {
		return $this->getUIFactory()->dropdown()->standard(array_map(function (Column $column) use ($component, $filter, $renderer): Shy {
			return $this->getUIFactory()->button()->shy($renderer->render([
				$this->getUIFactory()->symbol()->glyph()->add(),
				$this->getUIFactory()->legacy($column->getTitle())
			]), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_SELECT_COLUMN => $column->getKey() ], $component->getTableId()));
		}, array_filter($component->getColumns(), function (Column $column) use ($filter): bool {
			return ($column->isSelectable() && !in_array($column->getKey(), $filter->getSelectedColumns()));
		})))->withLabel($this->txt(Table::LANG_MODULE . "_add_columns"));
	}


	/**
	 * @param Table $component
	 *
	 * @return Component
	 */
	protected function getExportsSelector(Table $component): Component {
		return $this->getUIFactory()->dropdown()->standard(array_map(function (Format $format) use ($component): Shy {
			return $this->getUIFactory()->button()
				->shy($format->getDisplayTitle(), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_EXPORT_FORMAT_ID => $format->getFormatId() ], $component->getTableId()));
		}, $component->getFormats()))->withLabel($this->txt(Table::LANG_MODULE . "_export"));
	}


	/**
	 * @param Table             $component
	 * @param Filter            $filter
	 * @param Data              $data
	 * @param RendererInterface $renderer
	 *
	 * @return Component
	 */
	protected function getPagesSelector(Table $component, Filter $filter, Data $data, RendererInterface $renderer): Component {
		return $this->getUIFactory()->dropdown()->standard(array_map(function (int $page) use ($component, $filter, $renderer): Component {
			if ($filter->getCurrentPage() === $page) {
				return $this->getUIFactory()->legacy($renderer->render([
					$this->getUIFactory()->symbol()->glyph()->apply(),
					$this->getUIFactory()->legacy(strval($page))
				]));
			} else {
				return $this->getUIFactory()->button()
					->shy(strval($page), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_CURRENT_PAGE => $page ], $component->getTableId()));
			}
		}, range(1, $filter->getTotalPages($data->getMaxCount()))))->withLabel(sprintf($this->txt(Table::LANG_MODULE
			. "_pages"), $filter->getCurrentPage(), $filter->getTotalPages($data->getMaxCount())));
	}


	/**
	 * @param Table             $component
	 * @param Filter            $filter
	 * @param RendererInterface $renderer
	 *
	 * @return Component
	 */
	protected function getRowsPerPageSelector(Table $component, Filter $filter, RendererInterface $renderer): Component {
		return $this->getUIFactory()->dropdown()->standard(array_map(function (int $count) use ($component, $filter, $renderer): Component {
			if ($filter->getRowsCount() === $count) {
				return $this->getUIFactory()->legacy($renderer->render([
					$this->getUIFactory()->symbol()->glyph()->apply(),
					$this->getUIFactory()->legacy(strval($count))
				]));
			} else {
				return $this->getUIFactory()->button()
					->shy(strval($count), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_ROWS_COUNT => $count ], $component->getTableId()));
			}
		}, Filter::ROWS_COUNT))->withLabel(sprintf($this->txt(Table::LANG_MODULE . "_rows_per_page"), $filter->getRowsCount()));
	}


	/**
	 * @param Template          $tpl
	 * @param Table             $component
	 * @param Filter            $filter
	 * @param Data              $data
	 * @param RendererInterface $renderer
	 */
	protected function handleActionsPanel(Template $tpl, Table $component, Filter $filter, Data $data, RendererInterface $renderer): void {
		$tpl->setCurrentBlock("actions");

		$tpl->setVariable("ACTIONS", $renderer->render($this->getUIFactory()->panel()->standard("", [
			$this->getPagesSelector($component, $filter, $data, $renderer),
			$this->getColumnsSelector($component, $filter, $renderer),
			$this->getRowsPerPageSelector($component, $filter, $renderer),
			$this->getExportsSelector($component)
		])));

		$tpl->parseCurrentBlock();
	}


	/**
	 * @param Template          $tpl
	 * @param Table             $component
	 * @param Column[]          $columns
	 * @param Filter            $filter
	 * @param RendererInterface $renderer
	 */
	protected function handleColumns(Template $tpl, Table $component, array $columns, Filter $filter, RendererInterface $renderer): void {
		$tpl->setCurrentBlock("header");

		if (count($component->getMultipleActions()) > 0) {
			$tpl->setVariable("HEADER", "");

			$tpl->parseCurrentBlock();
		}

		foreach ($columns as $column) {
			$deselect_button = $this->getUIFactory()->legacy("");
			$sort_button = $column->getFormater()->formatHeader(Format::FORMAT_BROWSER, $column, $component->getTableId(), $renderer);
			$remove_sort_button = $this->getUIFactory()->legacy("");

			if ($column->isSelectable()) {
				$deselect_button = $this->getUIFactory()->button()->shy($renderer->render($this->getUIFactory()->symbol()->glyph()
					->remove()), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_DESELECT_COLUMN => $column->getKey() ], $component->getTableId()));
			}

			if ($column->isSortable()) {
				$sort_field = $filter->getSortField($column->getKey());

				if ($sort_field !== null) {
					if ($sort_field->getSortFieldDirection() === FilterSortField::SORT_DIRECTION_DOWN) {
						$sort_button = $this->getUIFactory()->button()->shy($renderer->render([
							$this->getUIFactory()->legacy($sort_button),
							$this->getUIFactory()->symbol()->glyph()->sortDescending()
						]), self::getActionUrl($component->getActionUrl(), [
							FilterStorage::VAR_SORT_FIELD => $column->getKey(),
							FilterStorage::VAR_SORT_FIELD_DIRECTION => FilterSortField::SORT_DIRECTION_UP
						], $component->getTableId()));
					} else {
						$sort_button = $this->getUIFactory()->button()->shy($renderer->render([
							$this->getUIFactory()->legacy($sort_button),
							$this->getUIFactory()->symbol()->glyph()->sortAscending()
						]), self::getActionUrl($component->getActionUrl(), [
							FilterStorage::VAR_SORT_FIELD => $column->getKey(),
							FilterStorage::VAR_SORT_FIELD_DIRECTION => FilterSortField::SORT_DIRECTION_DOWN
						], $component->getTableId()));
					}

					$remove_sort_button = $this->getUIFactory()->button()->shy($renderer->render($this->getUIFactory()->symbol()->glyph()
						->back() // TODO: Other icon for remove sort
					), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_REMOVE_SORT_FIELD => $column->getKey() ], $component->getTableId()));
				} else {
					$sort_button = $this->getUIFactory()->button()->shy($sort_button, self::getActionUrl($component->getActionUrl(), [
						FilterStorage::VAR_SORT_FIELD => $column->getKey(),
						FilterStorage::VAR_SORT_FIELD_DIRECTION => FilterSortField::SORT_DIRECTION_UP
					], $component->getTableId()));
				}
			} else {
				$sort_button = $this->getUIFactory()->legacy($sort_button);
			}

			$tpl->setVariable("HEADER", $renderer->render([ $deselect_button, $sort_button, $remove_sort_button ]));

			$tpl->parseCurrentBlock();
			// TODO: Dragable columns
		}
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Filter
	 */
	protected function handleDefaultSelectedColumns(Table $component, Filter $filter): Filter {
		if (!$filter->isFilterSet() && empty($filter->getSelectedColumns())) {
			$filter = $filter->withSelectedColumns(array_map(function (Column $column): string {
				return $column->getKey();
			}, array_filter($component->getColumns(), function (Column $column): bool {
				return ($column->isSelectable() && $column->isDefaultSelected());
			})));
		}

		return $filter;
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Filter
	 */
	protected function handleDefaultSort(Table $component, Filter $filter): Filter {
		if (!$filter->isFilterSet() && empty($filter->getSortFields())) {
			$filter = $filter->withSortFields(array_map(function (Column $column) use ($component): FilterSortField {
				return $component->getFilterStorage()->sortField($column->getKey(), $column->getDefaultSortDirection());
			}, array_filter($component->getColumns(), function (Column $column): bool {
				return ($column->isSortable() && $column->isDefaultSort());
			})));
		}

		return $filter;
	}


	/**
	 * @param Template $tpl
	 * @param Filter   $filter
	 * @param Data     $data
	 */
	protected function handleDisplayCount(Template $tpl, Filter $filter, Data $data): void {
		$count = sprintf($this->txt(Table::LANG_MODULE . "_count"), ($data->getDataCount() > 0 ? $filter->getLimitStart()
			+ 1 : 0), min($filter->getLimitEnd(), $data->getMaxCount()), $data->getMaxCount());

		$tpl->setCurrentBlock("count_top");
		$tpl->setVariable("COUNT_TOP", $count);
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("count_bottom");
		$tpl->setVariable("COUNT_BOTTOM", $count);
		$tpl->parseCurrentBlock();
	}


	/**
	 * @param Table             $component
	 * @param Column[]          $columns
	 * @param Data              $data
	 * @param RendererInterface $renderer
	 */
	protected function handleExport(Table $component, array $columns, Data $data, RendererInterface $renderer): void {
		$export_format_id = strval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_EXPORT_FORMAT_ID, $component->getTableId())));

		if (empty($export_format_id)) {
			return;
		}

		/**
		 * @var Format|null $format
		 */
		$format = current(array_filter($component->getFormats(), function (Format $format) use ($export_format_id): bool {
			return ($format->getFormatId() === $export_format_id);
		}));

		if ($format === null) {
			return;
		}

		$columns = array_filter($columns, function (Column $column): bool {
			return $column->isExportable();
		});

		$columns_ = [];
		foreach ($columns as $column) {
			$columns_[] = $column->getFormater()->formatHeader($format->getFormatId(), $column, $component->getTableId(), $renderer);
		}

		$rows_ = [];
		foreach ($data->getData() as $row) {
			$row_ = [];
			foreach ($columns as $column) {
				$row_[] = $column->getFormater()->formatRow($format->getFormatId(), $column, $row, $component->getTableId(), $renderer);
			}
			$rows_[] = $row_;
		}

		$data = $format->render($columns_, $rows_, $component->getTitle(), $component->getTableId(), $renderer);

		$format->devliver($data, $component->getTitle(), $component->getTableId());
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Data
	 */
	protected function handleFetchData(Table $component, Filter $filter): Data {
		if (!$component->isFetchDataNeedsFilterFirstSet() || $filter->isFilterSet()) {
			$data = $component->getDataFetcher()->fetchData($filter);
		} else {
			$data = $component->getDataFetcher()->data([], 0);
		}

		return $data;
	}


	/**
	 * @param Template          $tpl
	 * @param Table             $component
	 * @param Filter            $filter
	 * @param RendererInterface $renderer
	 */
	protected function handleFilterForm(Template $tpl, Table $component, Filter $filter, RendererInterface $renderer): void {
		if (count($component->getFilterFields()) === 0) {
			return;
		}

		$this->initFilterForm($component, $filter);

		$filter_form = $renderer->render($this->filter_form);

		switch ($component->getFilterPosition()) {
			case Filter::FILTER_POSITION_BOTTOM:
				$tpl->setCurrentBlock("filter_bottom");

				$tpl->setVariable("FILTER_FORM_BOTTOM", $filter_form);

				$tpl->parseCurrentBlock();
				break;

			case Filter::FILTER_POSITION_TOP:
			default:
				$tpl->setCurrentBlock("filter_top");

				$tpl->setVariable("FILTER_FORM_TOP", $filter_form);

				$tpl->parseCurrentBlock();
				break;
		}
	}


	/**
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Filter
	 */
	protected function handleFilterInput(Table $component, Filter $filter): Filter {
		//if (strtoupper(filter_input(INPUT_SERVER, "REQUEST_METHOD")) === "POST") {

		$sort_field = strval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_SORT_FIELD, $component->getTableId())));
		$sort_field_direction = intval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_SORT_FIELD_DIRECTION, $component->getTableId())));
		if (!empty($sort_field) && !empty($sort_field_direction)) {
			$filter = $filter->addSortField($component->getFilterStorage()->sortField($sort_field, $sort_field_direction));

			$filter = $filter->withFilterSet(true);
		}

		$remove_sort_field = strval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_REMOVE_SORT_FIELD, $component->getTableId())));
		if (!empty($remove_sort_field)) {
			$filter = $filter->removeSortField($remove_sort_field);

			$filter = $filter->withFilterSet(true);
		}

		$rows_count = intval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_ROWS_COUNT, $component->getTableId())));
		if (!empty($rows_count)) {
			$filter = $filter->withRowsCount($rows_count);
			$filter = $filter->withCurrentPage(); // Reset current page on row change
		}

		$current_page = intval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_CURRENT_PAGE, $component->getTableId())));
		if (!empty($current_page)) {
			$filter = $filter->withCurrentPage($current_page);

			$filter = $filter->withFilterSet(true);
		}

		$select_column = strval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_SELECT_COLUMN, $component->getTableId())));
		if (!empty($select_column)) {
			$filter = $filter->selectColumn($select_column);

			$filter = $filter->withFilterSet(true);
		}

		$deselect_column = strval(filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_DESELECT_COLUMN, $component->getTableId())));
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
	 * @param Table             $component
	 * @param RendererInterface $renderer
	 */
	protected function handleMultipleActions(Template $tpl, Table $component, RendererInterface $renderer): void {
		if (count($component->getMultipleActions()) === 0) {
			return;
		}

		$tpl_checkbox = $this->getTemplate("tpl.datatablerow.html", true, false);

		$tpl_checkbox->setCurrentBlock("row_checkbox");

		$multiple_actions = [
			$this->getUIFactory()->legacy($tpl_checkbox->get()),
			$this->getUIFactory()->legacy($this->txt(Table::LANG_MODULE . "_select_all")),
			$this->getUIFactory()->dropdown()->standard(array_map(function (string $title, string $action): Shy {
				return $this->getUIFactory()->button()->shy($title, $action);
			}, array_keys($component->getMultipleActions()), $component->getMultipleActions()))->withLabel($this->txt(Table::LANG_MODULE
				. "_multiple_actions"))
		];

		$tpl->setCurrentBlock("multiple_actions_top");
		$tpl->setVariable("MULTIPLE_ACTIONS_TOP", $renderer->render($multiple_actions));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("multiple_actions_bottom");
		$tpl->setVariable("MULTIPLE_ACTIONS_BOTTOM", $renderer->render($multiple_actions));
		$tpl->parseCurrentBlock();
	}


	/**
	 * @param Template $tpl
	 * @param Data     $data
	 * @param Table    $component
	 */
	protected function handleNoDataText(Template $tpl, Data $data, Table $component): void {
		if ($data->getDataCount() === 0) {
			$tpl->setCurrentBlock("no_data");

			$tpl->setVariable("NO_DATA_TEXT", $component->getDataFetcher()->getNoDataText());

			$tpl->parseCurrentBlock();
		}
	}


	/**
	 * @param Template          $tpl
	 * @param Table             $component
	 * @param Column[]          $columns
	 * @param Data              $data
	 * @param RendererInterface $renderer
	 */
	protected function handleRows(Template $tpl, Table $component, array $columns, Data $data, RendererInterface $renderer): void {
		$tpl->setCurrentBlock("body");

		foreach ($data->getData() as $row) {
			$tpl_row = $this->getTemplate("tpl.datatablerow.html", true, true);

			if (count($component->getMultipleActions()) > 0) {
				$tpl_row->setCurrentBlock("row_checkbox");

				$tpl_row->setVariable("POST_VAR", self::actionParameter(Table::MULTIPLE_SELECT_POST_VAR, $component->getTableId()) . "[]");

				$tpl_row->setVariable("ROW_ID", $row->getRowId());

				$tpl_row->parseCurrentBlock();
			}

			$tpl_row->setCurrentBlock("row");

			foreach ($columns as $column) {
				$value = $column->getFormater()->formatRow(Format::FORMAT_BROWSER, $column, $row, $component->getTableId(), $renderer);

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
	 * @param Table  $component
	 * @param Filter $filter
	 */
	protected function initFilterForm(Table $component, Filter $filter): void {
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
