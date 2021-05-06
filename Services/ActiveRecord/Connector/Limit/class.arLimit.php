<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatement.php');

/**
 * Class arLimit
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arLimit extends arStatement
{

    /**
     * @var int
     */
    protected $start = 0;
    /**
     * @var int
     */
    protected $end = 0;

    /**
     * @param ActiveRecord $ar
     * @return string
     */
    public function asSQLStatement(ActiveRecord $ar) : string
    {
        return ' LIMIT ' . $this->getStart() . ', ' . $this->getEnd();
    }

    /**
     * @param int $end
     */
    public function setEnd(int $end) : void
    {
        $this->end = $end;
    }

    /**
     * @return int
     */
    public function getEnd() : int
    {
        return $this->end;
    }

    /**
     * @param int $start
     */
    public function setStart(int $start) : void
    {
        $this->start = $start;
    }

    /**
     * @return int
     */
    public function getStart() : int
    {
        return $this->start;
    }
}
