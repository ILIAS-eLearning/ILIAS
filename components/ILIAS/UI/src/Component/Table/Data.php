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
use ILIAS\UI\Component\Input\Container\ViewControl\ViewControlInput;
use Psr\Http\Message\ServerRequestInterface;
use ILIAS\Data\Order;
use ILIAS\Data\Range;

/**
 * This describes a Data Table.
 */
interface Data extends Table
{
    /**
     * @param array<string, Action\Action>    $actions
     */
    public function withActions(array $actions): static;

    /**
     * Rendering the Table must be done using the current Request:
     * it (the request) will be forwarded to the Table's View Control Container,
     * and parameters will already influence e.g. the presentation of
     * column-titles (think of ordering...).
     */
    public function withRequest(ServerRequestInterface $request): static;

    /**
     * Not all columns are neccessarily visible; "selected optional" is the
     * positive list of shown columns (the non-optional columns are always shown
     * and are not included here)
     * @param string[]  $selected_optional_column_ids
     */
    public function withSelectedOptionalColumns(array $selected_optional_column_ids): static;

    public function withOrder(?Order $order): self;
    public function withRange(?Range $range): self;
    public function withFilter(mixed $filter): self;
    public function withAdditionalParameters(mixed $additional_parameters): self;

    /**
     * The DataTable comes with a storage to keep e.g. ViewControl-settings throughout requests.
     * Set an Id to enable the storage and identify the distinct table.
     */
    public function withId(string $id): static;

    /**
     * Consumers may add additional view controls; their data will be relayed to
     * DataRetrieval::getRows and ::getTotalRowCount as additional parameter.
     * If you want to add more than one additional view control, bundle them into
     * an Input\ViewControl\Group.
     */
    public function withAdditionalViewControl(
        ViewControlInput $view_control
    ): self;
}
