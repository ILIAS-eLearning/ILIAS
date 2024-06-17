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

use Psr\Http\Message\ServerRequestInterface;

/**
 * This describes a Table to specify the order of its data (rows).
 */
interface Ordering extends Table
{
    /**
     * @param array<string, Action\Action>    $actions
     */
    public function withActions(array $actions): static;

    public function withRequest(ServerRequestInterface $request): static;

    /**
     * @return string[] the row-ids in the current (submitted) order
     */
    public function getData(): array;

    /**
     * Turns ordering capabilites off/on.
     */
    public function withOrderingDisabled(bool $flag): self;

    /**
     * Not all columns are neccessarily visible; "selected optional" is the
     * positive list of shown columns (the non-optional columns are always shown
     * and are not included here)
     * @param string[]  $selected_optional_column_ids
     */
    public function withSelectedOptionalColumns(array $selected_optional_column_ids): static;

    /**
     * The DataTable comes with a storage to keep e.g. ViewControl-settings throughout requests.
     * Set an Id to enable the storage and identify the distinct table.
     */
    public function withId(string $id): static;
}
