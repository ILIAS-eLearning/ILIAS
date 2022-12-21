<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *********************************************************************/

namespace ILIAS\Services\Database\Integrity;

class ilDBField
{
    private string $table_name;
    private string $field_name;
    private string $original_table_name;
    private string $converted_table_name;

    public function __construct(string $table_name, string $field_name, ?string $alias = null)
    {
        $this->table_name = $table_name;
        $this->field_name = $field_name;
        $this->converted_table_name = $table_name;
        $this->original_table_name = $table_name;

        if (null !== $alias) {
            $this->converted_table_name = $table_name . ' as ' . $alias;
            $this->table_name = $alias;
            $this->original_table_name = $table_name;
        }
    }

    public function tableName() : string
    {
        return $this->converted_table_name;
    }

    public function fieldName() : string
    {
        return $this->table_name . '.' . $this->field_name;
    }

    public function rawFieldName() : string
    {
        return $this->field_name;
    }

    public function rawTableName() : string
    {
        return $this->original_table_name;
    }
}