<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Filter\Sort\FilterSortField;
use ILIAS\UI\Component\Table\Data\Filter\Storage\FilterStorage;
use ILIAS\UI\Component\Table\Data\Format\BrowserFormat;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Renderer;
use ilUtil;
use LogicException;
use Throwable;

/**
 * Class DefaultBrowserFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class DefaultBrowserFormat extends HTMLFormat implements BrowserFormat {

	/**
	 * @var Standard|null
	 */
	protected $filter_form = null;


	/**
	 * @inheritDoc
	 */
	public function getFormatId(): string {
		return self::FORMAT_BROWSER;
	}


	/**
	 * @inheritDoc
	 */
	public function getOutputType(): int {
		return self::OUTPUT_TYPE_PRINT;
	}


	/**
	 * @inheritDoc
	 */
	public function devliver(string $data, Table $component): void {
		throw new LogicException("Seperate devliver browser format not possible!");
	}


	/**
	 * @param Table $component
	 *
	 * @return string|null
	 */
	public function getInputFormatId(Table $component): ?string {
		return filter_input(INPUT_GET, self::actionParameter(FilterStorage::VAR_EXPORT_FORMAT_ID, $component->getTableId()));
	}


	/**
	 * @inheritDoc
	 */
	protected function getColumns(Table $component, Filter $filter): array {
		return $this->getColumnsBase($component, $filter);
	}


	/**
	 * @inheritDoc
	 */
	protected function initTemplate(Table $component, Data $data, Filter $filter, Renderer $renderer): void {
		parent::initTemplate($component, $data, $filter, $renderer);

		$this->handleFilterForm($component, $filter, $renderer);

		$this->handleActionsPanel($component, $filter, $data, $renderer);

		$this->handleDisplayCount($filter, $data);

		$this->handleMultipleActions($component, $renderer);
	}


	/**
	 * @inheritDoc
	 */
	protected function handleColumns(Table $component, array $columns, Filter $filter, Renderer $renderer): void {
		if (count($component->getMultipleActions()) > 0) {
			$this->tpl->setCurrentBlock("header");

			$this->tpl->setVariable("HEADER", "");

			$this->tpl->parseCurrentBlock();
		}

		parent::handleColumns($component, $columns, $filter, $renderer);
	}


	/**
	 * @inheritDoc
	 */
	protected function handleColumn(string $formated_column, Table $component, Column $column, Filter $filter, Renderer $renderer): void {
		$deselect_button = $this->dic->ui()->factory()->legacy("");
		$sort_button = $formated_column;
		$remove_sort_button = $this->dic->ui()->factory()->legacy("");

		if ($column->isSelectable()) {
			$deselect_button = $this->dic->ui()->factory()->button()->shy($renderer->render($this->dic->ui()->factory()->symbol()->glyph()
				->remove()), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_DESELECT_COLUMN => $column->getKey() ], $component->getTableId()));
		}

		if ($column->isSortable()) {
			$sort_field = $filter->getSortField($column->getKey());

			if ($sort_field !== null) {
				if ($sort_field->getSortFieldDirection() === FilterSortField::SORT_DIRECTION_DOWN) {
					$sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render([
						$this->dic->ui()->factory()->legacy($sort_button),
						$this->dic->ui()->factory()->symbol()->glyph()->sortDescending()
					]), self::getActionUrl($component->getActionUrl(), [
						FilterStorage::VAR_SORT_FIELD => $column->getKey(),
						FilterStorage::VAR_SORT_FIELD_DIRECTION => FilterSortField::SORT_DIRECTION_UP
					], $component->getTableId()));
				} else {
					$sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render([
						$this->dic->ui()->factory()->legacy($sort_button),
						$this->dic->ui()->factory()->symbol()->glyph()->sortAscending()
					]), self::getActionUrl($component->getActionUrl(), [
						FilterStorage::VAR_SORT_FIELD => $column->getKey(),
						FilterStorage::VAR_SORT_FIELD_DIRECTION => FilterSortField::SORT_DIRECTION_DOWN
					], $component->getTableId()));
				}

				$remove_sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render($this->dic->ui()->factory()->symbol()->glyph()
					->back() // TODO: Other icon for remove sort
				), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_REMOVE_SORT_FIELD => $column->getKey() ], $component->getTableId()));
			} else {
				$sort_button = $this->dic->ui()->factory()->button()->shy($sort_button, self::getActionUrl($component->getActionUrl(), [
					FilterStorage::VAR_SORT_FIELD => $column->getKey(),
					FilterStorage::VAR_SORT_FIELD_DIRECTION => FilterSortField::SORT_DIRECTION_UP
				], $component->getTableId()));
			}
		} else {
			$sort_button = $this->dic->ui()->factory()->legacy($sort_button);
		}

		// TODO: Dragable columns

		$formated_column = $renderer->render([
			$deselect_button,
			$sort_button,
			$remove_sort_button
		]);

		parent::handleColumn($formated_column, $component, $column, $filter, $renderer);
	}


	/**
	 * @inheritDoc
	 */
	protected function handleRowTemplate(Table $component, RowData $row): void {
		parent::handleRowTemplate($component, $row);

		if (count($component->getMultipleActions()) > 0) {
			$this->tpl->setCurrentBlock("row_checkbox");

			$this->tpl->setVariable("POST_VAR", self::actionParameter(Table::MULTIPLE_SELECT_POST_VAR, $component->getTableId()) . "[]");

			$this->tpl->setVariable("ROW_ID", $row->getRowId());

			$this->tpl->parseCurrentBlock();
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
	 * @inheritDoc
	 */
	public function handleFilterInput(Table $component, Filter $filter): Filter {
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
	 * @param Table    $component
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 */
	protected function handleFilterForm(Table $component, Filter $filter, Renderer $renderer): void {
		if (count($component->getFilterFields()) === 0) {
			return;
		}

		$this->initFilterForm($component, $filter);

		$filter_form = $renderer->render($this->filter_form);

		$this->tpl->setCurrentBlock("filter");

		$this->tpl->setVariable("FILTER_FORM", $filter_form);

		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @param Table    $component
	 * @param Filter   $filter
	 * @param Data     $data
	 * @param Renderer $renderer
	 */
	protected function handleActionsPanel(Table $component, Filter $filter, Data $data, Renderer $renderer): void {
		$this->tpl->setCurrentBlock("actions");

		$this->tpl->setVariable("ACTIONS", $renderer->render($this->dic->ui()->factory()->panel()->standard("", [
			$this->getPagesSelector($component, $filter, $data, $renderer),
			$this->getColumnsSelector($component, $filter, $renderer),
			$this->getRowsPerPageSelector($component, $filter, $renderer),
			$this->getExportsSelector($component)
		])));

		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @param Table    $component
	 * @param Filter   $filter
	 * @param Data     $data
	 * @param Renderer $renderer
	 *
	 * @return Component
	 */
	protected function getPagesSelector(Table $component, Filter $filter, Data $data, Renderer $renderer): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (int $page) use ($component, $filter, $renderer): Component {
			if ($filter->getCurrentPage() === $page) {
				return $this->dic->ui()->factory()->legacy($renderer->render([
					$this->dic->ui()->factory()->symbol()->glyph()->apply(),
					$this->dic->ui()->factory()->legacy(strval($page))
				]));
			} else {
				return $this->dic->ui()->factory()->button()
					->shy(strval($page), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_CURRENT_PAGE => $page ], $component->getTableId()));
			}
		}, range(1, $filter->getTotalPages($data->getMaxCount()))))->withLabel(sprintf($this->dic->language()->txt(Table::LANG_MODULE
			. "_pages"), $filter->getCurrentPage(), $filter->getTotalPages($data->getMaxCount())));
	}


	/**
	 * @param Table    $component
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 *
	 * @return Component
	 */
	protected function getColumnsSelector(Table $component, Filter $filter, Renderer $renderer): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (Column $column) use ($component, $filter, $renderer): Shy {
			return $this->dic->ui()->factory()->button()->shy($renderer->render([
				$this->dic->ui()->factory()->symbol()->glyph()->add(),
				$this->dic->ui()->factory()->legacy($column->getTitle())
			]), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_SELECT_COLUMN => $column->getKey() ], $component->getTableId()));
		}, array_filter($component->getColumns(), function (Column $column) use ($filter): bool {
			return ($column->isSelectable() && !in_array($column->getKey(), $filter->getSelectedColumns()));
		})))->withLabel($this->dic->language()->txt(Table::LANG_MODULE . "_add_columns"));
	}


	/**
	 * @param Table    $component
	 * @param Filter   $filter
	 * @param Renderer $renderer
	 *
	 * @return Component
	 */
	protected function getRowsPerPageSelector(Table $component, Filter $filter, Renderer $renderer): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (int $count) use ($component, $filter, $renderer): Component {
			if ($filter->getRowsCount() === $count) {
				return $this->dic->ui()->factory()->legacy($renderer->render([
					$this->dic->ui()->factory()->symbol()->glyph()->apply(),
					$this->dic->ui()->factory()->legacy(strval($count))
				]));
			} else {
				return $this->dic->ui()->factory()->button()
					->shy(strval($count), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_ROWS_COUNT => $count ], $component->getTableId()));
			}
		}, Filter::ROWS_COUNT))->withLabel(sprintf($this->dic->language()->txt(Table::LANG_MODULE . "_rows_per_page"), $filter->getRowsCount()));
	}


	/**
	 * @param Table $component
	 *
	 * @return Component
	 */
	protected function getExportsSelector(Table $component): Component {
		return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (Format $format) use ($component): Shy {
			return $this->dic->ui()->factory()->button()
				->shy($format->getDisplayTitle(), self::getActionUrl($component->getActionUrl(), [ FilterStorage::VAR_EXPORT_FORMAT_ID => $format->getFormatId() ], $component->getTableId()));
		}, $component->getFormats()))->withLabel($this->dic->language()->txt(Table::LANG_MODULE . "_export"));
	}


	/**
	 * @param Filter $filter
	 * @param Data   $data
	 */
	protected function handleDisplayCount(Filter $filter, Data $data): void {
		$count = sprintf($this->dic->language()->txt(Table::LANG_MODULE . "_count"), ($data->getDataCount() > 0 ? $filter->getLimitStart()
			+ 1 : 0), min($filter->getLimitEnd(), $data->getMaxCount()), $data->getMaxCount());

		$this->tpl->setCurrentBlock("count_top");
		$this->tpl->setVariable("COUNT_TOP", $count);
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("count_bottom");
		$this->tpl->setVariable("COUNT_BOTTOM", $count);
		$this->tpl->parseCurrentBlock();
	}


	/**
	 * @param Table    $component
	 * @param Renderer $renderer
	 */
	protected function handleMultipleActions(Table $component, Renderer $renderer): void {
		if (count($component->getMultipleActions()) === 0) {
			return;
		}

		$tpl_checkbox = $this->tpl_factory->getTemplate($this->tpl_path . "tpl.datatablerow.html", true, false);

		$tpl_checkbox->setCurrentBlock("row_checkbox");

		$multiple_actions = [
			$this->dic->ui()->factory()->legacy($tpl_checkbox->get()),
			$this->dic->ui()->factory()->legacy($this->dic->language()->txt(Table::LANG_MODULE . "_select_all")),
			$this->dic->ui()->factory()->dropdown()->standard(array_map(function (string $title, string $action): Shy {
				return $this->dic->ui()->factory()->button()->shy($title, $action);
			}, array_keys($component->getMultipleActions()), $component->getMultipleActions()))->withLabel($this->dic->language()
				->txt(Table::LANG_MODULE . "_multiple_actions"))
		];

		$this->tpl->setCurrentBlock("multiple_actions_top");
		$this->tpl->setVariable("MULTIPLE_ACTIONS_TOP", $renderer->render($multiple_actions));
		$this->tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("multiple_actions_bottom");
		$this->tpl->setVariable("MULTIPLE_ACTIONS_BOTTOM", $renderer->render($multiple_actions));
		$this->tpl->parseCurrentBlock();
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
