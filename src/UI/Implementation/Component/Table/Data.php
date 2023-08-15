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
use ILIAS\UI\Component\Input\ViewControl\ViewControl;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as JSBindable;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\Data\Range;

class Data extends Table implements T\Data, JSBindable
{
    use JavaScriptBindable;

    /**
     * @var array<string, Column>
     */
    protected $columns = [];

    /**
     * @var array<string, Action>
     */
    protected $actions_single = [];

    /**
     * @var array<string, Action>
     */
    protected $actions_multi = [];

    /**
     * @var array<string, Action>
     */
    protected $actions_std = [];

    protected Signal $multi_action_signal;
    protected Signal $selection_signal;
    protected ?ServerRequestInterface $request = null;
    protected int $number_of_rows = 800;
    /**
     * @var string[]
     */
    protected array $selected_optional_column_ids = [];
    protected Range $range;
    protected Order $order;
    protected ?array $filter = null;
    protected ?array $additional_parameters = null;

    /**
     * @param array<string, Column> $columns
     */
    public function __construct(
        SignalGeneratorInterface $signal_generator,
        protected DataFactory $data_factory,
        protected DataRowBuilder $data_row_builder,
        string $title,
        array $columns,
        protected T\DataRetrieval $data_retrieval
    ) {
        $this->checkArgListElements('columns', $columns, [Column::class]);
        if ($columns === []) {
            throw new \InvalidArgumentException('cannot construct a table without columns.');
        }

        parent::__construct($title);
        $this->multi_action_signal = $signal_generator->create();
        $this->selection_signal = $signal_generator->create();

        $this->columns = $this->enumerateColumns($columns);
        $this->selected_optional_column_ids = $this->filterVisibleColumnIds($columns);
        $this->order = $this->data_factory->order($this->initialOrder(), Order::ASC);
        $this->range = $data_factory->range(0, $this->number_of_rows);
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

    /**
     * @param array<string, Column> $columns
     * @return array<string>
     */
    private function filterVisibleColumnIds(array $columns): array
    {
        return array_keys(
            array_filter(
                $columns,
                static fn ($c): bool => $c->isInitiallyVisible()
            )
        );
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

    public function withOrder(Order $order): self
    {
        $clone = clone $this;
        $clone->order = $order;
        return $clone;
    }
    public function getOrder(): Order
    {
        return $this->order;
    }

    public function withRange(Range $range): self
    {
        $clone = clone $this;
        $clone->range = $range;
        return $clone;
    }
    public function getRange(): Range
    {
        return $this->range;
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
     * @return ViewControl[]
     */
    public function getViewControls(): array
    {
        //NYI
        return [];
    }

    public function getActionSignal(): Signal
    {
        return $this->multi_action_signal;
    }

    public function getSelectionSignal(): Signal
    {
        return $this->selection_signal;
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
    public function withSelectedOptionalColumns(array $selected_optional_column_ids): self
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
        return $this->selected_optional_column_ids;
    }

    /**
     * @return array<string, Column>
     */
    public function getVisibleColumns(): array
    {
        return array_filter(
            $this->getColumns(),
            fn (Column $col, string $col_id): bool => !$col->isOptional() || in_array($col_id, $this->selected_optional_column_ids, true),
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
}
