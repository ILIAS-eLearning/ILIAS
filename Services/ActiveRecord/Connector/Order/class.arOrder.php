<?php

/**
 * Class arOrder
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arOrder extends arStatement
{

    protected string $fieldname = '';
    protected string $direction = 'ASC';

    public function asSQLStatement(ActiveRecord $ar) : string
    {
        return ' ' . $this->getFieldname() . ' ' . strtoupper($this->getDirection());
    }

    public function setDirection(string $direction) : void
    {
        $this->direction = $direction;
    }

    public function getDirection() : string
    {
        return $this->direction;
    }

    public function setFieldname(string $fieldname) : void
    {
        $this->fieldname = $fieldname;
    }

    public function getFieldname() : string
    {
        return $this->fieldname;
    }
}
