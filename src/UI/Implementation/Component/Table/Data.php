<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Component\Input\ViewControl\ViewControl;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\JavaScriptBindable as JSBindable;

class Data extends Table implements T\Data, JSBindable
{
    use JavaScriptBindable;

    /**
     * @var int
     */
    protected $number_of_rows;

    /**
     * @var DataRetrieval
     */
    protected $data_retrieval;

    /**
     * @var array <string, Column>
     */
    protected $columns;

    /**
     * @var array <string, Action>
     */
    protected $actions;

    /**
     * @var Signal
     */
    protected $multi_action_signal;


    public function __construct(
        SignalGeneratorInterface $signal_generator,
        string $title,
        int $number_of_rows
    ) {
        $this->number_of_rows = $number_of_rows;
        $this->multi_action_signal = $signal_generator->create();
        parent::__construct($title);
    }

    public function getNumberOfRows() : ?int
    {
        return $this->number_of_rows;
    }

    public function withData(T\DataRetrieval $data_retrieval) : T\Data
    {
        $clone = clone $this;
        $clone->data_retrieval = $data_retrieval;
        return $clone;
    }

    public function getData() : T\DataRetrieval
    {
        return $this->data_retrieval;
    }

    /**
     * @inheritdoc
     */
    public function withColumns(array $columns) : T\Data
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
    public function getColumns() : array
    {
        return $this->columns;
    }

    /**
     * @inheritdoc
     */
    public function withAdditionalViewControl(ViewControl $view_control) : T\Data
    {
        //NYI
        return $this;
    }

    /**
     * @return ViewControl[]
     */
    public function getViewControls() : array
    {
        return [];
    }

    public function withRequest(ServerRequestInterface $request) : T\Data
    {
        //NYI
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function withActions(array $actions) : T\Data
    {
        $clone = clone $this;
        $clone->actions = $actions;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getActions() : array
    {
        return $this->actions;
    }

    public function getActionSignal(string $id = null) : Signal
    {
        $sig = $this->multi_action_signal;
        if ($id) {
            $sig = clone $sig;
            $sig->addOption('action', $id);
        }
        return $sig;
    }
}
