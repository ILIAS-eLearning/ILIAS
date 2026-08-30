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

use ILIAS\UI\Component\Table\Column\Column;
use ILIAS\UI\Implementation\Component\ComponentHelper;

abstract class Row
{
    use ComponentHelper;

    /**
     * The records's key is the column-id of the table.
     *
     * @param array<string, Column> $columns
     * @param array<string, mixed> $record
     */
    public function __construct(
        protected bool $table_has_singleactions,
        protected bool $table_has_multiactions,
        protected array $columns,
        protected array $record
    ) {
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function tableHasSingleActions(): bool
    {
        return $this->table_has_singleactions;
    }

    public function tableHasMultiActions(): bool
    {
        return $this->table_has_multiactions;
    }
}
