<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Component\Table\Action\Action;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as JSBindable;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Input\ViewControl;
use ILIAS\UI\Component\Input\Container\ViewControl as ViewControlContainer;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

class Data extends AbstractTable implements T\Data
{
    public const VIEWCONTROL_KEY_PAGINATION = 'range';
    public const VIEWCONTROL_KEY_ORDERING = 'order';
    public const VIEWCONTROL_KEY_FIELDSELECTION = 'selected_optional';

    protected int $number_of_rows = 800;
    /**
     * @var string[]
     */
    protected ?array $selected_optional_column_ids = null;
    protected ?Range $range = null;
    protected ?Order $order = null;
    protected ?array $filter = null;
    protected ?array $additional_parameters = null;

    /**
     * @param array<string, Column> $columns
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        protected ViewControl\Factory $view_control_factory,
        protected ViewControlContainer\Factory $view_control_container_factory,
        protected DataFactory $data_factory,
        protected DataRowBuilder $data_row_builder,
        string $title,
        array $columns,
        protected T\DataRetrieval $data_retrieval
    ) {
        parent::__construct(
            $signal_generator,
            $title,
            $columns
        );

        $this->selected_optional_column_ids = $this->filterVisibleColumnIds($columns);
        $this->order = $this->data_factory->order($this->initialOrder(), Order::ASC);
        $this->range = $data_factory->range(0, $this->number_of_rows);
    }

    /**
     * @param array<string, Column> $columns
     * @return array<string>
     */
    private function filterVisibleColumnIds(array $columns): array
    {
        return array_keys(
            array_filter(
                $columns,
                static fn($c): bool => $c->isInitiallyVisible()
            )
        );
    }

    private function initialOrder(): string
    {
        $visible_cols = $this->getVisibleColumns();
        $sortable_visible_cols = array_filter(
            $visible_cols,
            static fn($c): bool => $c->isSortable()
        );
        if ($sortable_visible_cols === []) {
            return array_key_first($visible_cols);
        }
        return array_key_first($sortable_visible_cols);
    }

    public function getDataRetrieval(): T\DataRetrieval
    {
        return $this->data_retrieval;
    }

    public function withNumberOfRows(int $number_of_rows): self
    {
        $clone = clone $this;
        $clone->number_of_rows = $number_of_rows;
        return $clone;
    }

    public function getNumberOfRows(): int
    {
        return $this->number_of_rows;
    }

    public function withOrder(?Order $order): self
    {
        $clone = clone $this;
        $clone->order = $order;
        return $clone;
    }

    public function getOrder(): Order
    {
        return $this->order ?? $this->data_factory->order($this->initialOrder(), Order::ASC);
    }

    public function withRange(?Range $range): self
    {
        $clone = clone $this;
        $clone->range = $range;
        return $clone;
    }

    public function getRange(): Range
    {
        return $this->range ?? $this->data_factory->range(0, $this->number_of_rows);
    }

    public function withFilter(?array $filter): self
    {
        $clone = clone $this;
        $clone->filter = $filter;
        return $clone;
    }

    public function getFilter(): ?array
    {
        return $this->filter;
    }

    public function withAdditionalParameters(?array $additional_parameters): self
    {
        $clone = clone $this;
        $clone->additional_parameters = $additional_parameters;
        return $clone;
    }

    public function getAdditionalParameters(): ?array
    {
        return $this->additional_parameters;
    }

    /**
     * @param string[] $selected_optional_column_ids
     */
    public function withSelectedOptionalColumns(?array $selected_optional_column_ids): self
    {
        $clone = clone $this;
        $clone->selected_optional_column_ids = $selected_optional_column_ids;
        return $clone;
    }

    /**
     * @return string[]
     */
    public function getSelectedOptionalColumns(): array
    {
        if (is_null($this->selected_optional_column_ids)) {
            return array_keys($this->getInitiallyVisibleColumns());
        }
        return $this->selected_optional_column_ids;
    }

    /**
     * @return array<int, Column>
     */
    protected function getOptionalColumns(): array
    {
        return array_filter(
            $this->getColumns(),
            static fn($c): bool => $c->isOptional()
        );
    }

    /**
     * @return array<int, Column>
     */
    protected function getInitiallyVisibleColumns(): array
    {
        return array_filter(
            $this->getOptionalColumns(),
            static fn($c): bool => $c->isInitiallyVisible()
        );
    }

    /**
     * @return array<string, Column>
     */
    public function getVisibleColumns(): array
    {
        $visible_optional_columns = $this->getSelectedOptionalColumns();
        return array_filter(
            $this->getColumns(),
            fn(Column $col, string $col_id): bool => !$col->isOptional() || in_array($col_id, $visible_optional_columns, true),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function getRowBuilder(): DataRowBuilder
    {
        return $this->data_row_builder
            ->withMultiActionsPresent($this->hasMultiActions())
            ->withSingleActions($this->getSingleActions())
            ->withVisibleColumns($this->getVisibleColumns());
    }

    /**
     * @return array<self, ViewControlContainer\ViewControl>
     */
    public function applyViewControls(
        array $filter_data,
        array $additional_parameters
    ): array {
        $table = $this;
        $total_count = $this->getDataRetrieval()->getTotalRowCount($filter_data, $additional_parameters);
        $view_controls = $this->getViewControls($total_count);

        if ($request = $this->getRequest()) {
            $view_controls = $view_controls->withRequest($request);
            $data = $view_controls->getData();
            $table = $table
                ->withRange(($data[self::VIEWCONTROL_KEY_PAGINATION] ?? null)?->croppedTo($total_count ?? PHP_INT_MAX))
                ->withOrder($data[self::VIEWCONTROL_KEY_ORDERING] ?? null)
                ->withSelectedOptionalColumns($data[self::VIEWCONTROL_KEY_FIELDSELECTION] ?? null);
        }

        return [
            $table
                ->withFilter($filter_data)
                ->withAdditionalParameters($additional_parameters),
            $view_controls
        ];
    }

    protected function getViewControls(?int $total_count = null): ViewControlContainer\ViewControl
    {
        $view_controls = [
            self::VIEWCONTROL_KEY_PAGINATION => $this->getViewControlPagination($total_count),
            self::VIEWCONTROL_KEY_ORDERING => $this->getViewControlOrdering(),
            self::VIEWCONTROL_KEY_FIELDSELECTION => $this->getViewControlFieldSelection(),
        ];
        $view_controls = array_filter($view_controls);
        return $this->view_control_container_factory->standard($view_controls);
    }

    protected function getViewControlPagination(?int $total_count = null): ?ViewControl\Pagination
    {
        $smallest_option = current(Pagination::DEFAULT_LIMITS);
        if (is_null($total_count) || $total_count >= $smallest_option) {
            $range = $this->getRange();
            return
                $this->view_control_factory->pagination()
                    ->withTotalCount($total_count)
                    ->withValue([
                        Pagination::FNAME_OFFSET => $range->getStart(),
                        Pagination::FNAME_LIMIT => $range->getLength()
                    ]);
        }
        return null;
    }

    protected function getViewControlOrdering(): ?ViewControl\Sortation
    {
        $sortable_visible_cols = array_filter(
            $this->getVisibleColumns(),
            static fn($c): bool => $c->isSortable()
        );
        $sort_options = [];
        foreach ($sortable_visible_cols as $id => $col) {
            $sort_options[$col->getTitle() . ', ' . 'ascending'] = $this->data_factory->order($id, 'ASC');
            $sort_options[$col->getTitle() . ', ' . 'decending'] = $this->data_factory->order($id, 'DESC');
        }

        if ($sort_options !== []) {
            return $this->view_control_factory->sortation($sort_options);
        }
        return null;
    }

    protected function getViewControlFieldSelection(): ?ViewControl\FieldSelection
    {
        $optional_cols = $this->getOptionalColumns();
        if ($optional_cols === []) {
            return null;
        }

        return $this->view_control_factory
            ->fieldSelection(array_map(
                static fn($c): string => $c->getTitle(),
                $optional_cols
            ))
            ->withValue($this->getSelectedOptionalColumns());
    }
}
