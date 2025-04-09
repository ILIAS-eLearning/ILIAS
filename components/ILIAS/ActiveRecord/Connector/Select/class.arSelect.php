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

    /**
     * @param bool $disabled_escaping In some special cases, one need to disable the escaping of the field name.
     *                                In this cases, the consumer is responsible to avoid sqm injection.
     */
    public function __construct(private bool $disabled_escaping = false)
    {
    }

    public function asSQLStatement(ActiveRecord $activeRecord, ilDBInterface $db): string
    {
        $return = '';
        if ($this->getTableName() !== '' && $this->getTableName() !== '0') {
            $return .= $this->getTableName() . '.';
        }
        if ($this->disabled_escaping) {
            $return .= $this->getFieldName();
        } else {
            $return .= $this->wrapField($this->getFieldName(), $db);
        }
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
