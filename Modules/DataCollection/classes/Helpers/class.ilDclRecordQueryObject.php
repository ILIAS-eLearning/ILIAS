<?php

/**
 * Class ilDclRecordQueryObject
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRecordQueryObject
{
    protected $selectStatement;
    protected $joinStatement;
    protected $whereStatement;
    protected $groupStatement;
    protected $orderStatement;


    /**
     * @return mixed
     */
    public function getSelectStatement()
    {
        return $this->selectStatement;
    }


    /**
     * @param mixed $selectStatement
     */
    public function setSelectStatement($selectStatement)
    {
        $this->selectStatement = $selectStatement;
    }


    /**
     * @return mixed
     */
    public function getJoinStatement()
    {
        return $this->joinStatement;
    }


    /**
     * @param mixed $joinStatement
     */
    public function setJoinStatement($joinStatement)
    {
        $this->joinStatement = $joinStatement;
    }


    /**
     * @return mixed
     */
    public function getWhereStatement()
    {
        return $this->whereStatement;
    }


    /**
     * @param mixed $whereStatement
     */
    public function setWhereStatement($whereStatement)
    {
        $this->whereStatement = $whereStatement;
    }


    /**
     * @return mixed
     */
    public function getGroupStatement()
    {
        return $this->groupStatement;
    }


    /**
     * @param mixed $groupStatement
     */
    public function setGroupStatement($groupStatement)
    {
        $this->groupStatement = $groupStatement;
    }


    /**
     * @return mixed
     */
    public function getOrderStatement()
    {
        return $this->orderStatement;
    }


    /**
     * @param mixed $orderStatement
     */
    public function setOrderStatement($orderStatement)
    {
        $this->orderStatement = $orderStatement;
    }


    /**
     * Apply custom sorting
     *
     * @param ilDclBaseFieldModel $field
     * @param array               $all_records
     * @param string              $direction
     *
     * @return array
     */
    public function applyCustomSorting(ilDclBaseFieldModel $field, array $all_records, $direction = 'asc')
    {
        return $all_records;
    }
}
