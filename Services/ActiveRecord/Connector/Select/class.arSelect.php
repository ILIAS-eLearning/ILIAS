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
 * Class arSelect
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arSelect extends arStatement
{
    protected string $table_name = '';
    protected string $as = '';
    protected string $field_name = '';

    public function asSQLStatement(ActiveRecord $activeRecord): string
    {
        $return = '';
        if ($this->getTableName() !== '' && $this->getTableName() !== '0') {
            $return .= $this->getTableName() . '.';
        }
        $return .= $this->getFieldName();
        if ($this->getAs() && $this->getFieldName() !== '*') {
            $return .= ' AS ' . $this->getAs();
        }

        return $return;
    }

    public function getTableName(): string
    {
        return $this->table_name;
    }

    public function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }

    public function getAs(): string
    {
        return $this->as;
    }

    public function setAs(string $as): void
    {
        $this->as = $as;
    }

    public function getFieldName(): string
    {
        return $this->field_name;
    }

    public function setFieldName(string $field_name): void
    {
        $this->field_name = $field_name;
    }
}
