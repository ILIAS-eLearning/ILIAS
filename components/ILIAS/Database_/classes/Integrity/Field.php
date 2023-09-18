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

namespace ILIAS\Services\Database\Integrity;

class Field
{
    private const _AS = 'as';
    private const COMBINE_TABLE_AND_FIELD = '.';
    private string $original_table_name;
    private string $converted_table_name;

    public function __construct(private string $table_name, private string $field_name, ?string $alias = null)
    {
        $this->converted_table_name = $table_name;
        $this->original_table_name = $table_name;

        if (null !== $alias) {
            $this->converted_table_name = $table_name . ' ' . self::_AS . ' ' . $alias;
            $this->table_name = $alias;
            $this->original_table_name = $table_name;
        }
    }

    public function tableName(): string
    {
        return $this->converted_table_name;
    }

    public function fieldName(): string
    {
        return $this->table_name . self::COMBINE_TABLE_AND_FIELD . $this->field_name;
    }

    public function rawFieldName(): string
    {
        return $this->field_name;
    }

    public function rawTableName(): string
    {
        return $this->original_table_name;
    }
}
