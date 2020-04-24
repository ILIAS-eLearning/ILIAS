<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Table;

use ILIAS\UI\Component\Table as T;
use ILIAS\UI\Component\Input\ViewControl\ViewControl;
use Psr\Http\Message\ServerRequestInterface;

class Data extends Table implements T\Data
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var DataRetrieval
     */
    protected $data;

    /**
     * @var array <string, ViewControl>
     */
    protected $view_controls;

    /**
     * @var bool
     */
    protected $has_pagination = true;

    /**
     * @var int
     */
    protected $number_of_rows = 50;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function withColumns(array $columns) : T\Data
    {
        $clone = clone $this;
        foreach ($columns as $id => $column) {
            $clone->columns[$id] = $column;
        }
        return $clone;
    }

    public function getColumns() : array
    {
        return $this->columns;
    }

    public function withData(T\DataRetrieval $data_retrieval) : T\Data
    {
        $clone = clone $this;
        $clone->data = $data_retrieval;
        return $clone;
    }

    public function getData() : T\DataRetrieval
    {
        return $this->data;
    }

    public function withAdditionalViewControl(ViewControl $view_control) : T\Data
    {
        $clone = clone $this;
        $type = get_class($view_control);
        $clone->view_controls[$type] = $view_control;
        return $clone;
    }

    public function getViewControls() : array
    {
        return $this->view_controls;
    }

    public function getNumberOfRows() : int
    {
        return $this->number_of_rows;
    }

    public function withRequest(ServerRequestInterface $request) : T\Data
    {
        return $this;
    }
}
