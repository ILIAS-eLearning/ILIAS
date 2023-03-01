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

namespace ILIAS\UI\Component\Table;

use ILIAS\UI\Component\Input\ViewControl\ViewControl;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Order;
use ILIAS\Data\Range;

/**
 * This describes a Data Table.
 */
interface Data extends Table
{
    /**
     * @param array <string, Action>    $actions
     */
    public function withActions(array $actions): self;

    /**
     * Configure the Table to retrieve data with an instance of DataRetrieval;
     * the table itself is agnostic of the source or the way of retrieving records.
     * However, it provides View Controls and therefore parameters that will
     * influence the way data is being retrieved. E.g., it is usually a good idea
     * to delegate sorting to the database, or limit records to the amount of
     * actually shown rows.
     * Those parameters are being provided to DataRetrieval::getRows.
     */
    //public function withData(DataRetrieval $data_retrieval): self;

    /**
     * The Data Table brings some View Controls along - it is common enough to
     * use pagination, ordering and column selection. However, consumers might
     * need more Controls than those, or configure View Controls to their special
     * needs.
     * Since there must be but one View Control of a kind, e.g. a Pagination added here
     * will substitute the default one.
     */
//    public function withAdditionalViewControl(ViewControl $view_control): self;

    /**
     * Rendering the Table must be done using the current Request:
     * it (the request) will be forwarded to the Table's View Control Container,
     * and parameters will already influence e.g. the presentation of
     * column-titles (think of ordering...).
     */
    public function withRequest(ServerRequestInterface $request): self;

    /**
     * Number of Rows is the amount of rows shown per page
     */
    public function withNumberOfRows(int $number_of_rows): self;

    /**
     * Not all columns are neccessarily visible; "selected optional" is the
     * positive list of shown columns (the non-optional columns are always shown
     * and are not included here)
     */
    public function withSelectedOptionalColumns(array $selected_optional_column_ids): self;

    public function withOrder(Order $order): self;
    public function withRange(Range $range): self;
    public function withFilter(?array $filter): self;
    public function withAdditionalParameters(?array $additional_parameters): self;
}
