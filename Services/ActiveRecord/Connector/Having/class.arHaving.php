<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arHaving
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arHaving extends arStatement
{
    protected string $table_name = '';
    protected string $fieldname = '';
    /**
     * @var mixed
     */
    protected $value;
    protected string $operator = '=';
    protected string $statement = '';
    protected string $glue = 'AND';

    /**
     * @description Build WHERE Statement
     * @throws arException
     */
    public function asSQLStatement(ActiveRecord $ar): string
    {
        $statement = '';
        if ($this->getTableName()) {
            $statement .= $this->getTableName() . '.';
        }
        $statement .= $this->getFieldname() . ' ' . $this->getOperator() . ' "' . $this->getValue() . '"';
        $this->setStatement($statement);

        return $this->getStatement();
    }

    public function getFieldname(): string
    {
        return $this->fieldname;
    }

    public function setFieldname(string $fieldname): void
    {
        $this->fieldname = $fieldname;
    }

    /**
     * @return mixed|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value): void
    {
        $this->value = $value;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): void
    {
        $this->operator = $operator;
    }

    public function getStatement(): string
    {
        return $this->statement;
    }

    public function setStatement(string $statement): void
    {
        $this->statement = $statement;
    }

    public function getGlue(): string
    {
        return $this->glue;
    }

    public function setGlue(string $glue): void
    {
        $this->glue = $glue;
    }

    public function getTableName(): string
    {
        return $this->table_name;
    }

    public function setTableName(string $table_name): void
    {
        $this->table_name = $table_name;
    }
}
