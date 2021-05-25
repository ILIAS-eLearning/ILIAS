<?php

/**
 * Class arStatement
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
abstract class arStatement
{

    /**
     * @var string
     */
    protected $table_name_as = '';

    /**
     * @param ActiveRecord $ar
     * @return string
     */
    abstract public function asSQLStatement(ActiveRecord $ar) : string;

    /**
     * @return string
     */
    public function getTableNameAs() : string
    {
        return $this->table_name_as;
    }

    /**
     * @param string $table_name_as
     * @return void
     */
    public function setTableNameAs(string $table_name_as) : void
    {
        $this->table_name_as = $table_name_as;
    }
}
