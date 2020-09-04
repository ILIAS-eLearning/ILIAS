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
    public function getNumberOfRows() : ?int;

    /**
     * Configure the Table to retrieve data with an instance of DataRetrieval;
     * the table itself is agnostic of the source or the way of retrieving records.
     * However, it provides View Controls and therefore parameters that will
     * influence the way data is being retrieved. E.g., it is usually a good idea
     * to delegate sorting to the database, or limit records to the amount of
     * actually shown rows.
     * Those parameters are beeing provided to DataRetrieval::getRows.
     */
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

    /**
     * The Data Table brings some View Controls along - it is common enough to
     * use pagination, ordering and column selection. However, consumers might
     * need more Controls than those, or configure View Controls to their special
     * needs.
     * Since there must be but one View Control of a kind, e.g. a Pagination added here
     * will substitue the default one.
     */
    public function withAdditionalViewControl(ViewControl $view_control) : Data;

    /**
     * @return ViewControl[]
     */
    public function getViewControls() : array;

    /**
     * Rendering the Table must be done using the current Request:
     * it (the request) will be forwarded to the Table's View Control Container,
     * and parameters will already influence e.g. the presentation of
     * column-titles (think of ordering...).
     */
    public function withRequest(ServerRequestInterface $request) : Data;

    /**
     * @param array <string, Action>    $actions
     */
    public function withActions(array $actions) : Data;

    /**
     * @return Action[]
     */
    public function getActions() : array;
}
