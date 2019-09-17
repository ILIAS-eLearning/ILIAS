<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Container\Filter\Standard;
use ILIAS\UI\Component\Table\Data\Column\Column;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Data\Row\RowData;
use ILIAS\UI\Component\Table\Data\Format\BrowserFormat;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Settings;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Sort\SortField as SortFieldInterface;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Storage\SettingsStorage;
use ILIAS\UI\Implementation\Component\Table\Data\UserTableSettings\Sort\SortField;
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
class DefaultBrowserFormat extends HTMLFormat implements BrowserFormat
{

    /**
     * @var Standard|null
     */
    protected $filter_form = null;


    /**
     * @inheritDoc
     */
    public function getFormatId() : string
    {
        return self::FORMAT_BROWSER;
    }


    /**
     * @inheritDoc
     */
    public function getOutputType() : int
    {
        return self::OUTPUT_TYPE_PRINT;
    }


    /**
     * @inheritDoc
     */
    public function devliver(string $data, Table $component) : void
    {
        throw new LogicException("Seperate devliver browser format not possible!");
    }


    /**
     * @param Table $component
     *
     * @return string|null
     */
    public function getInputFormatId(Table $component) : ?string
    {
        return filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_EXPORT_FORMAT_ID, $component->getTableId()));
    }


    /**
     * @inheritDoc
     */
    protected function getColumns(Table $component, Settings $user_table_settings) : array
    {
        return $this->getColumnsBase($component, $user_table_settings);
    }


    /**
     * @inheritDoc
     */
    protected function initTemplate(Table $component, Data $data, Settings $user_table_settings, Renderer $renderer) : void
    {
        parent::initTemplate($component, $data, $user_table_settings, $renderer);

        $this->handleFilterForm($component, $user_table_settings, $renderer);

        $this->handleActionsPanel($component, $user_table_settings, $data, $renderer);

        $this->handleDisplayCount($user_table_settings, $data);

        $this->handleMultipleActions($component, $renderer);
    }


    /**
     * @inheritDoc
     */
    protected function handleColumns(Table $component, array $columns, Settings $user_table_settings, Renderer $renderer) : void
    {
        if (count($component->getMultipleActions()) > 0) {
            $this->tpl->setCurrentBlock("header");

            $this->tpl->setVariable("HEADER", "");

            $this->tpl->parseCurrentBlock();
        }

        parent::handleColumns($component, $columns, $user_table_settings, $renderer);
    }


    /**
     * @inheritDoc
     */
    protected function handleColumn(string $formated_column, Table $component, Column $column, Settings $user_table_settings, Renderer $renderer) : void
    {
        $deselect_button = $this->dic->ui()->factory()->legacy("");
        $sort_button = $formated_column;
        $remove_sort_button = $this->dic->ui()->factory()->legacy("");

        if ($column->isSelectable()) {
            $deselect_button = $this->dic->ui()->factory()->button()->shy($renderer->render($this->dic->ui()->factory()->symbol()->glyph()
                ->remove()), self::getActionUrl($component->getActionUrl(), [SettingsStorage::VAR_DESELECT_COLUMN => $column->getKey()], $component->getTableId()));
        }

        if ($column->isSortable()) {
            $sort_field = $user_table_settings->getSortField($column->getKey());

            if ($sort_field !== null) {
                if ($sort_field->getSortFieldDirection() === SortFieldInterface::SORT_DIRECTION_DOWN) {
                    $sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render([
                        $this->dic->ui()->factory()->legacy($sort_button),
                        $this->dic->ui()->factory()->symbol()->glyph()->sortDescending()
                    ]), self::getActionUrl($component->getActionUrl(), [
                        SettingsStorage::VAR_SORT_FIELD           => $column->getKey(),
                        SettingsStorage::VAR_SORT_FIELD_DIRECTION => SortFieldInterface::SORT_DIRECTION_UP
                    ], $component->getTableId()));
                } else {
                    $sort_button = $this->dic->ui()->factory()->button()->shy($renderer->render([
                        $this->dic->ui()->factory()->legacy($sort_button),
                        $this->dic->ui()->factory()->symbol()->glyph()->sortAscending()
                    ]), self::getActionUrl($component->getActionUrl(), [
                        SettingsStorage::VAR_SORT_FIELD           => $column->getKey(),
                        SettingsStorage::VAR_SORT_FIELD_DIRECTION => SortFieldInterface::SORT_DIRECTION_DOWN
                    ], $component->getTableId()));
                }

                $remove_sort_button = $this->dic->ui()->factory()->button()->shy($this->dic->language()->txt(Table::LANG_MODULE
                    . "_remove_sort"),
                    self::getActionUrl($component->getActionUrl(), [SettingsStorage::VAR_REMOVE_SORT_FIELD => $column->getKey()], $component->getTableId())); // TODO: Remove sort icon
            } else {
                $sort_button = $this->dic->ui()->factory()->button()->shy($sort_button, self::getActionUrl($component->getActionUrl(), [
                    SettingsStorage::VAR_SORT_FIELD           => $column->getKey(),
                    SettingsStorage::VAR_SORT_FIELD_DIRECTION => SortFieldInterface::SORT_DIRECTION_UP
                ], $component->getTableId()));
            }
        } else {
            $sort_button = $this->dic->ui()->factory()->legacy($sort_button);
        }

        $formated_column = $renderer->render([
            $deselect_button,
            $sort_button,
            $remove_sort_button
        ]);

        parent::handleColumn($formated_column, $component, $column, $user_table_settings, $renderer);
    }


    /**
     * @inheritDoc
     */
    protected function handleRowTemplate(Table $component, RowData $row) : void
    {
        parent::handleRowTemplate($component, $row);

        if (count($component->getMultipleActions()) > 0) {
            $this->tpl->setCurrentBlock("row_checkbox");

            $this->tpl->setVariable("POST_VAR", self::actionParameter(Table::MULTIPLE_SELECT_POST_VAR, $component->getTableId()) . "[]");

            $this->tpl->setVariable("ROW_ID", $row->getRowId());

            $this->tpl->parseCurrentBlock();
        }
    }


    /**
     * @param Table    $component
     * @param Settings $user_table_settings
     */
    protected function initFilterForm(Table $component, Settings $user_table_settings) : void
    {
        if ($this->filter_form === null) {
            $filter_fields = $component->getFilterFields();

            $this->filter_form = $this->dic->uiService()->filter()
                ->standard($component->getTableId(), self::getActionUrl($component->getActionUrl(), [], $component->getTableId()), $filter_fields, array_fill(0, count($filter_fields), false), true,
                    true);
        }
    }


    /**
     * @inheritDoc
     */
    public function handleUserTableSettingsInput(Table $component, Settings $user_table_settings) : Settings
    {
        //if (strtoupper(filter_input(INPUT_SERVER, "REQUEST_METHOD")) === "POST") {

        $sort_field = strval(filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_SORT_FIELD, $component->getTableId())));
        $sort_field_direction = intval(filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_SORT_FIELD_DIRECTION, $component->getTableId())));
        if (!empty($sort_field) && !empty($sort_field_direction)) {
            $user_table_settings = $user_table_settings->addSortField(new SortField($sort_field, $sort_field_direction));

            $user_table_settings = $user_table_settings->withFilterSet(true);
        }

        $remove_sort_field = strval(filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_REMOVE_SORT_FIELD, $component->getTableId())));
        if (!empty($remove_sort_field)) {
            $user_table_settings = $user_table_settings->removeSortField($remove_sort_field);

            $user_table_settings = $user_table_settings->withFilterSet(true);
        }

        $rows_count = intval(filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_ROWS_COUNT, $component->getTableId())));
        if (!empty($rows_count)) {
            $user_table_settings = $user_table_settings->withRowsCount($rows_count);
            $user_table_settings = $user_table_settings->withCurrentPage(); // Reset current page on row change
        }

        $current_page = filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_CURRENT_PAGE, $component->getTableId()));
        if ($current_page !== null) {
            $user_table_settings = $user_table_settings->withCurrentPage(intval($current_page));

            $user_table_settings = $user_table_settings->withFilterSet(true);
        }

        $select_column = strval(filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_SELECT_COLUMN, $component->getTableId())));
        if (!empty($select_column)) {
            $user_table_settings = $user_table_settings->selectColumn($select_column);

            $user_table_settings = $user_table_settings->withFilterSet(true);
        }

        $deselect_column = strval(filter_input(INPUT_GET, self::actionParameter(SettingsStorage::VAR_DESELECT_COLUMN, $component->getTableId())));
        if (!empty($deselect_column)) {
            $user_table_settings = $user_table_settings->deselectColumn($deselect_column);

            $user_table_settings = $user_table_settings->withFilterSet(true);
        }

        if (count($component->getFilterFields()) > 0) {
            $this->initFilterForm($component, $user_table_settings);
            try {
                $data = $this->dic->uiService()->filter()->getData($this->filter_form) ?? [];

                $user_table_settings = $user_table_settings->withFilterFieldValues($data);

                if (!empty(array_filter($data))) {
                    $user_table_settings = $user_table_settings->withFilterSet(true);
                }
            } catch (Throwable $ex) {

            }
        }

        return $user_table_settings;
    }


    /**
     * @param Table    $component
     * @param Settings $user_table_settings
     * @param Renderer $renderer
     */
    protected function handleFilterForm(Table $component, Settings $user_table_settings, Renderer $renderer) : void
    {
        if (count($component->getFilterFields()) === 0) {
            return;
        }

        $this->initFilterForm($component, $user_table_settings);

        $filter_form = $renderer->render($this->filter_form);

        $this->tpl->setCurrentBlock("filter");

        $this->tpl->setVariable("FILTER_FORM", $filter_form);

        $this->tpl->parseCurrentBlock();
    }


    /**
     * @param Table    $component
     * @param Settings $user_table_settings
     * @param Data     $data
     * @param Renderer $renderer
     */
    protected function handleActionsPanel(Table $component, Settings $user_table_settings, Data $data, Renderer $renderer) : void
    {
        $this->tpl->setCurrentBlock("actions");

        $this->tpl->setVariable("ACTIONS", $renderer->render($this->dic->ui()->factory()->panel()->standard("", [
            $this->getPagesSelector($component, $user_table_settings, $data),
            $this->getColumnsSelector($component, $user_table_settings, $renderer),
            $this->getRowsPerPageSelector($component, $user_table_settings, $renderer),
            $this->getExportsSelector($component)
        ])));

        $this->tpl->parseCurrentBlock();
    }


    /**
     * @param Table    $component
     * @param Settings $user_table_settings
     * @param Data     $data
     *
     * @return Component
     */
    protected function getPagesSelector(Table $component, Settings $user_table_settings, Data $data) : Component
    {
        return $user_table_settings->getPagination($data)
            ->withTargetURL($component->getActionUrl(), self::actionParameter(SettingsStorage::VAR_CURRENT_PAGE, $component->getTableId()));
    }


    /**
     * @param Table    $component
     * @param Settings $user_table_settings
     * @param Renderer $renderer
     *
     * @return Component
     */
    protected function getColumnsSelector(Table $component, Settings $user_table_settings, Renderer $renderer) : Component
    {
        return $this->dic->ui()->factory()->dropdown()
            ->standard(array_map(function (Column $column) use ($component, $user_table_settings, $renderer): Shy {
                return $this->dic->ui()->factory()->button()->shy($renderer->render([
                    $this->dic->ui()->factory()->symbol()->glyph()->add(),
                    $this->dic->ui()->factory()->legacy($column->getTitle())
                ]), self::getActionUrl($component->getActionUrl(), [SettingsStorage::VAR_SELECT_COLUMN => $column->getKey()], $component->getTableId()));
            }, array_filter($component->getColumns(), function (Column $column) use ($user_table_settings): bool {
                return ($column->isSelectable() && !in_array($column->getKey(), $user_table_settings->getSelectedColumns()));
            })))->withLabel($this->dic->language()->txt(Table::LANG_MODULE . "_add_columns"));
    }


    /**
     * @param Table    $component
     * @param Settings $user_table_settings
     * @param Renderer $renderer
     *
     * @return Component
     */
    protected function getRowsPerPageSelector(Table $component, Settings $user_table_settings, Renderer $renderer) : Component
    {
        return $this->dic->ui()->factory()->dropdown()
            ->standard(array_map(function (int $count) use ($component, $user_table_settings, $renderer): Component {
                if ($user_table_settings->getRowsCount() === $count) {
                    return $this->dic->ui()->factory()->legacy($renderer->render([
                        $this->dic->ui()->factory()->symbol()->glyph()->apply(),
                        $this->dic->ui()->factory()->legacy(strval($count))
                    ]));
                } else {
                    return $this->dic->ui()->factory()->button()
                        ->shy(strval($count), self::getActionUrl($component->getActionUrl(), [SettingsStorage::VAR_ROWS_COUNT => $count], $component->getTableId()));
                }
            }, Settings::ROWS_COUNT))->withLabel(sprintf($this->dic->language()->txt(Table::LANG_MODULE
                . "_rows_per_page"), $user_table_settings->getRowsCount()));
    }


    /**
     * @param Table $component
     *
     * @return Component
     */
    protected function getExportsSelector(Table $component) : Component
    {
        return $this->dic->ui()->factory()->dropdown()->standard(array_map(function (Format $format) use ($component): Shy {
            return $this->dic->ui()->factory()->button()
                ->shy($format->getDisplayTitle(), self::getActionUrl($component->getActionUrl(), [SettingsStorage::VAR_EXPORT_FORMAT_ID => $format->getFormatId()], $component->getTableId()));
        }, $component->getFormats()))->withLabel($this->dic->language()->txt(Table::LANG_MODULE . "_export"));
    }


    /**
     * @param Settings $user_table_settings
     * @param Data     $data
     */
    protected function handleDisplayCount(Settings $user_table_settings, Data $data) : void
    {
        $count = sprintf($this->dic->language()->txt(Table::LANG_MODULE . "_count"), ($data->getDataCount()
        > 0 ? ($user_table_settings->getLimitStart() + 1) : 0), $data->getMaxCount());

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
    protected function handleMultipleActions(Table $component, Renderer $renderer) : void
    {
        if (count($component->getMultipleActions()) === 0) {
            return;
        }

        $tpl_checkbox = ($this->get_template)("tpl.datatablerow.html", true, false);

        $tpl_checkbox->setCurrentBlock("row_checkbox");

        $multiple_actions = [
            $this->dic->ui()->factory()->legacy($tpl_checkbox->get()),
            $this->dic->ui()->factory()->legacy($this->dic->language()->txt(Table::LANG_MODULE . "_select_all")),
            $this->dic->ui()->factory()->dropdown()->standard(array_map(function (string $title, string $action) : Shy {
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
    public static function getActionUrl(string $action_url, array $params, string $table_id) : string
    {
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
    public static function actionParameter(string $key, string $table_id) : string
    {
        return $key . "_" . $table_id;
    }
}
