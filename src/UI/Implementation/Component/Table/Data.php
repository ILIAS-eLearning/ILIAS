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
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;

class Data extends Table implements T\Data, JSBindable
{
    use JavaScriptBindable;

    public const VIEWCONTROL_KEY_PAGINATION = 'range';
    public const VIEWCONTROL_KEY_ORDERING = 'order';
    public const VIEWCONTROL_KEY_FIELDSELECTION = 'selected_optional';

    public const STORAGE_ID_PREFIX = self::class . '_';

    /**
     * @var array<string, Column>
     */
    protected array $columns = [];

    /**
     * @var array<string, Action>
     */
    protected array $actions_single = [];

    /**
     * @var array<string, Action>
     */
    protected array $actions_multi = [];

    /**
     * @var array<string, Action>
     */
    protected array $actions_std = [];

    protected Signal $multi_action_signal;
    protected Signal $selection_signal;
    protected Signal $async_action_signal;
    protected ?ServerRequestInterface $request = null;
    protected int $number_of_rows = 800;
    /**
     * @var string[]
     */
    protected ?array $selected_optional_column_ids = null;
    protected ?Range $range = null;
    protected ?Order $order = null;
    protected ?array $filter = null;
    protected ?array $additional_parameters = null;
    protected ?string $id = null;
    protected ViewControl\Factory $view_control_factory;
    protected ViewControlContainer\Factory $view_control_container_factory;
    protected DataFactory $data_factory;
    protected DataRowBuilder $data_row_builder;
    protected T\DataRetrieval $data_retrieval;
    protected \ArrayAccess $storage;

    /**
     * @param array<string, Column> $columns
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        ViewControl\Factory $view_control_factory,
        ViewControlContainer\Factory $view_control_container_factory,
        DataFactory $data_factory,
        DataRowBuilder $data_row_builder,
        string $title,
        array $columns,
        T\DataRetrieval $data_retrieval,
        \ArrayAccess $storage
    ) {
        $this->checkArgListElements('columns', $columns, [Column::class]);
        if ($columns === []) {
            throw new \InvalidArgumentException('cannot construct a table without columns.');
        }
        parent::__construct($title);
        $this->multi_action_signal = $signal_generator->create();
        $this->selection_signal = $signal_generator->create();
        $this->async_action_signal = $signal_generator->create();

        $this->columns = $this->enumerateColumns($columns);
        $this->view_control_factory = $view_control_factory;
        $this->view_control_container_factory = $view_control_container_factory;
        $this->data_factory = $data_factory;
        $this->data_row_builder = $data_row_builder;
        $this->data_retrieval = $data_retrieval;
        $this->storage = $storage;
    }

    /**
     * @param array<string, Column> $columns
     * @return array<string, Column>
     */
    private function enumerateColumns(array $columns): array
    {
        $ret = [];
        $idx = 0;
        foreach ($columns as $id => $col) {
            $ret[$id] = $col->withIndex($idx++);
        }
        return $ret;
    }

    private function initialOrder(): string
    {
        $visible_cols = $this->getVisibleColumns();
        $sortable_visible_cols = array_filter(
            $visible_cols,
            static fn ($c): bool => $c->isSortable()
        );
        if ($sortable_visible_cols === []) {
            return array_key_first($visible_cols);
        }
        return array_key_first($sortable_visible_cols);
    }

    /**
     * @return array<string, Column>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getDataRetrieval(): T\DataRetrieval
    {
        return $this->data_retrieval;
    }

    /**
     * @inheritdoc
     */
    public function withActions(array $actions): self
    {
        $this->checkArgListElements('actions', $actions, [T\Action\Action::class]);
        $clone = clone $this;

        foreach ($actions as $id => $action) {
            switch (true) {
                case ($action instanceof T\Action\Single):
                    $clone->actions_single[$id] = $action;
                    break;
                case ($action instanceof T\Action\Multi):
                    $clone->actions_multi[$id] = $action;
                    break;
                case ($action instanceof T\Action\Standard):
                    $clone->actions_std[$id] = $action;
                    break;
            }
        }
        return $clone;
    }

    public function withRequest(ServerRequestInterface $request): self
    {
        $clone = clone $this;
        $clone->request = $request;
        return $clone;
    }

    public function getRequest(): ?ServerRequestInterface
    {
        return $this->request;
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

    public function getMultiActionSignal(): Signal
    {
        return $this->multi_action_signal;
    }

    public function getSelectionSignal(): Signal
    {
        return $this->selection_signal;
    }

    public function getAsyncActionSignal(): Signal
    {
        return $this->async_action_signal;
    }

    public function hasSingleActions(): bool
    {
        return $this->getSingleActions() !== [];
    }

    public function hasMultiActions(): bool
    {
        return $this->getMultiActions() !== [];
    }

    /**
     * @return array<string, T\Action\Action>
     */
    public function getMultiActions(): array
    {
        return array_merge($this->actions_multi, $this->actions_std);
    }

    /**
     * @return array<string, T\Action\Action>
     */
    public function getSingleActions(): array
    {
        return array_merge($this->actions_single, $this->actions_std);
    }

    /**
     * @return array<string, T\Action\Action>
     */
    public function getAllActions(): array
    {
        return array_merge($this->actions_single, $this->actions_multi, $this->actions_std);
    }

    public function getColumnCount(): int
    {
        return count($this->columns);
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
            static fn ($c): bool => $c->isOptional()
        );
    }

    /**
     * @return array<int, Column>
     */
    protected function getInitiallyVisibleColumns(): array
    {
        return array_filter(
            $this->getOptionalColumns(),
            static fn ($c): bool => $c->isInitiallyVisible()
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
            fn (Column $col, string $col_id): bool => !$col->isOptional() || in_array($col_id, $visible_optional_columns, true),
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

    protected function getStorageData(): ?array
    {
        if (null !== ($storage_id = $this->getStorageId())) {
            return $this->storage[$storage_id] ?? null;
        }
        return null;
    }

    protected function setStorageData(array $data): void
    {
        if (null !== ($storage_id = $this->getStorageId())) {
            $this->storage[$storage_id] = $data;
        }
    }

    protected function applyValuesToViewcontrols(
        ViewControlContainer\ViewControl $view_controls,
        ServerRequestInterface $request
    ): ViewControlContainer\ViewControl {
        $stored_values = new ArrayInputData($this->getStorageData() ?? []);
        $view_controls = $view_controls
            ->withStoredInput($stored_values)
            ->withRequest($request);
        $this->setStorageData($view_controls->getComponentInternalValues());
        return $view_controls;
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
            $view_controls = $this->applyValuesToViewcontrols($view_controls, $request);
            $data = $view_controls->getData();

            $range = $data[self::VIEWCONTROL_KEY_PAGINATION] ?? null;
            if($range) {
                $range = $range->croppedTo($total_count ?? PHP_INT_MAX);
            }
            $table = $table
                ->withRange($range)
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
            static fn ($c): bool => $c->isSortable()
        );

        if ($sortable_visible_cols === []) {
            return null;
        }

        $sort_options = [];
        foreach ($sortable_visible_cols as $col_id => $col) {

            $order_asc = $this->data_factory->order($col_id, Order::ASC);
            $order_desc = $this->data_factory->order($col_id, Order::DESC);

            $labels = $col->getOrderingLabels();
            $sort_options[$labels[0]] = $order_asc;
            $sort_options[$labels[1]] = $order_desc;
        }
        return $this->view_control_factory->sortation($sort_options);
    }

    protected function getViewControlFieldSelection(): ?ViewControl\FieldSelection
    {
        $optional_cols = $this->getOptionalColumns();
        if ($optional_cols === []) {
            return null;
        }

        return $this->view_control_factory
            ->fieldSelection(array_map(
                static fn ($c): string => $c->getTitle(),
                $optional_cols
            ))
            ->withValue($this->getSelectedOptionalColumns());
    }

    public function withId(string $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        return $clone;
    }

    protected function getStorageId(): ?string
    {
        if (null !== ($id = $this->getId())) {
            return self::STORAGE_ID_PREFIX . $id;
        }
        return null;
    }

    protected function getId(): ?string
    {
        return $this->id;
    }
}
