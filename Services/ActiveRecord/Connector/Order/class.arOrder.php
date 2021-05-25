<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arOrder
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arOrder extends arStatement
{

    /**
     * @var string
     */
    protected $fieldname = '';
    /**
     * @var string
     */
    protected $direction = 'ASC';

    /**
     * @param ActiveRecord $ar
     * @return string
     */
    public function asSQLStatement(ActiveRecord $ar) : string
    {
        return ' ' . $this->getFieldname() . ' ' . strtoupper($this->getDirection());
    }

    /**
     * @param string $direction
     */
    public function setDirection(string $direction) : void
    {
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getDirection() : string
    {
        return $this->direction;
    }

    /**
     * @param string $fieldname
     */
    public function setFieldname(string $fieldname) : void
    {
        $this->fieldname = $fieldname;
    }

    /**
     * @return string
     */
    public function getFieldname() : string
    {
        return $this->fieldname;
    }
}
