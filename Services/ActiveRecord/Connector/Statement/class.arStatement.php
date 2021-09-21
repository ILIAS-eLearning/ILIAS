<?php

/**
 * Class arStatement
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
abstract class arStatement
{

    protected string $table_name_as = '';

    abstract public function asSQLStatement(ActiveRecord $ar) : string;

    public function getTableNameAs() : string
    {
        return $this->table_name_as;
    }

    public function setTableNameAs(string $table_name_as) : void
    {
        $this->table_name_as = $table_name_as;
    }
}
