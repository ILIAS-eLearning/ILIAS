<?php declare(strict_types=1);

/* Copyright (c) 2020 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Input\ViewControl\ViewControl;
use Psr\Http\Message\ServerRequestInterface;

/**
 * This describes a Data Table.
 */
interface Data extends \ILIAS\UI\Component\Table\Table
{
    public function withData(DataRetrieval $data_retrieval) : Data;

    public function getData() : DataRetrieval;

    /**
     * @param array <string, Column>
     */
    public function withColumns(array $columns) : Data;

    /**
     * @return array <string, Column>
     */
    public function getColumns() : array;

    public function withAdditionalViewControl(ViewControl $view_control) : Data;

    /**
     * @return ViewControl[]
     */
    public function getViewControls() : array;

    public function withPagination(bool $flag) : Data;

    public function hasPagination() : bool;

    public function withNumberOfRows(int $amount) : Data;

    public function getNumberOfRows() : int;

    public function withRequest(ServerRequestInterface $request) : Data;
}
