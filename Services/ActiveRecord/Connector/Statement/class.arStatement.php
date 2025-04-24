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

/**
 * Class arStatement
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
abstract class arStatement
{
    protected string $table_name_as = '';

    abstract public function asSQLStatement(ActiveRecord $activeRecord, ilDBInterface $db): string;

    public function getTableNameAs(): string
    {
        return $this->table_name_as;
    }

    public function setTableNameAs(string $table_name_as): void
    {
        $this->table_name_as = $table_name_as;
    }

    protected function wrapFields(array $fields, ilDBInterface $db): array
    {
        $wrapped_fields = [];

        foreach ($fields as $field) {
            if (empty($field)) {
                continue;
            }
            $wrapped_fields[] = $this->wrapField($field, $db);
        }

        return $wrapped_fields;
    }

    protected function wrapField(string $field, ilDBInterface $db): string
    {
        $slitted = explode('.', $field);

        if (count($slitted) === 1 && $slitted[0] === '*') {
            return $field;
        }

        if (count($slitted) === 2) {
            return $db->quoteIdentifier($slitted[0]) . '.' . $db->quoteIdentifier($slitted[1]);
        }

        return $db->quoteIdentifier($field);
    }
}
