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

namespace ILIAS\Questions\Persistence;

class Table
{
    public function __construct(
        private readonly CoreTables|TableTypes $table_definition,
        private readonly ?TableNameBuilder $table_name_builder,
        private readonly string $table_identifier
    ) {
    }

    public function getName(): string
    {
        if ($this->table_definition instanceof CoreTables) {
            return $this->table_definition->value;
        }

        return $this->table_name_builder->getTableNameFor(
            $this->table_definition,
            $this->table_identifier
        );
    }
}
