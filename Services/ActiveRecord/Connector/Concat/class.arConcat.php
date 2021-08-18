<?php

/**
 * Class arConcat
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arConcat extends arStatement
{

    protected string $as = '';
    /**
     * @var array
     */
    protected array $fields = [];

    public function asSQLStatement(ActiveRecord $ar) : string
    {
        return ' CONCAT(' . implode(', ', $this->getFields()) . ') AS ' . $this->getAs();
    }

    public function getAs() : string
    {
        return $this->as;
    }

    public function setAs(string $as) : void
    {
        $this->as = $as;
    }

    public function getFields() : array
    {
        return $this->fields;
    }

    public function setFields(array $fields) : void
    {
        $this->fields = $fields;
    }
}
