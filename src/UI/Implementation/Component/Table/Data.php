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
use ILIAS\UI\Component\Input\ViewControl\ViewControl;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as JSBindable;
use ILIAS\Data\Order;
use ILIAS\Data\Range;

class Data extends Table implements T\Data, JSBindable
{
    use JavaScriptBindable;

    protected string $title;
    protected int $number_of_rows;
    protected DataRetrieval $data_retrieval;

    /**
     * @var array <string, Column>
     */
    protected $columns;

    /**
     * @var array <string, Action>
     */
    protected $actions = [];

    protected Signal $multi_action_signal;
    protected Signal $selection_signal;
    protected array $visible_optional_column_ids;

    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $title,
        int $number_of_rows
    ) {
        $this->title = $title;
        $this->number_of_rows = $number_of_rows;
        $this->multi_action_signal = $signal_generator->create();
        $this->selection_signal = $signal_generator->create();
        parent::__construct($title);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getNumberOfRows(): ?int
    {
        return $this->number_of_rows;
    }

    public function withData(T\DataRetrieval $data_retrieval): self
    {
        $clone = clone $this;
        $clone->data_retrieval = $data_retrieval;
        return $clone;
    }

    public function getData(): T\DataRetrieval
    {
        return $this->data_retrieval;
    }

    /**
     * @inheritdoc
     */
    public function withColumns(array $columns): self
    {
        $clone = clone $this;
        $counter = 0;
        foreach ($columns as $id => $column) {
            $clone->columns[$id] = $column->withIndex($counter);
            $counter++;
        }
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalViewControl(ViewControl $view_control): self
    {
        //NYI
        return $this;
    }

    /**
     * @return ViewControl[]
     */
    public function getViewControls(): array
    {
        //NYI
        return [];
    }

    public function withRequest(ServerRequestInterface $request): self
    {
        //NYI
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withActions(array $actions): self
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    public function hasActions(): bool
    {
        return count($this->actions) > 0;
    }

    public function getActionSignal(): Signal
    {
        return $this->multi_action_signal;
    }
    public function getSelectionSignal(): Signal
    {
        return $this->selection_signal;
    }

    /**
     * Get all Actions on this table BUT the given one.
     * @param string $exclude the actions' class-name to _not_ return
     */
    protected function getFilteredActions(string $exclude): array
    {
        return array_filter(
            $this->getActions(),
            function ($action) use ($exclude) {
                return !is_a($action, $exclude);
            }
        );
    }

    public function getMultiActions(): array
    {
        return $this->getFilteredActions(T\Action\Single::class);
    }

    public function getSingleActions(): array
    {
        return $this->getFilteredActions(T\Action\Multi::class);
    }

    public function getColumnCount(): int
    {
        return count($this->columns);
    }

    /**
     * This is an anti-pattern and should not be copied!
     * The RowFactory should be injected (or constructed as anon class).
     * However, it merely transports columns- and action-information,
     * and I don't see a reason (yet) for having another/different RowFactory
     * in the table.
     */
    public function getRowFactory(): RowFactory
    {
        return new RowFactory(
            $this->hasActions(),
            $this->getFilteredColumns(),
            $this->getSingleActions()
        );
    }

    public function withSelectedOptionalColumns(array $selected_optional_columns): self
    {
        $clone = clone $this;
        $clone->visible_optional_column_ids = $selected_optional_columns;
        return $clone;
    }

    /**
     * @return <string, Column\Column>
     */
    public function getFilteredColumns(): array
    {
        return array_filter(
            $this->getColumns(),
            fn ($col, $col_id) => !$col->isOptional() || in_array($col_id, $this->visible_optional_column_ids),
            ARRAY_FILTER_USE_BOTH
        );
    }

    public function getRange(): Range
    {
        return $this->range;
    }
    public function withRange(Range $range): self
    {
        $clone = clone $this;
        $clone->range = $range;
        return $clone;
    }
    public function getOrder(): Order
    {
        return $this->order;
    }
    public function withOrder(Order $order): self
    {
        $clone = clone $this;
        $clone->order = $order;
        return $clone;
    }
}
