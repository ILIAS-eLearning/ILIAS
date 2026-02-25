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

class TableNameBuilder
{
    public function __construct(
        private readonly string $component_name_space,
        private readonly ?TableSubNameSpace $table_sub_name_space
    ) {

    }

    public function getTableNameFor(
        TableTypes $table,
        string $specifier = ''
    ): string {
        if ($table->value === '' && $specifier === '') {
            throw \InvalidArgumentException(
                'Identifier cannot be empty if Type->value is empty.'
            );
        }

        $base_name = "{$this->component_name_space}";
        if ($table->value !== '') {
            $base_name .= "_{$table->value}";
        }

        $additions = '';

        if ($this->table_sub_name_space !== null) {
            $additions = $this->table_sub_name_space->get();
        }

        if ($specifier !== '') {
            $additions .= "_{$specifier}";
        }

        if ($additions === '') {
            return $base_name;
        }

        return "{$base_name}_{$additions}";
    }
}
