<?php

/**
 * Class ilDclRecordQueryObject
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRecordQueryObject
{
    protected string $selectStatement;
    protected string $joinStatement;
    protected string $whereStatement;
    protected string $groupStatement;
    protected string $orderStatement;

    public function getSelectStatement() : string
    {
        return $this->selectStatement;
    }

    public function setSelectStatement(string $selectStatement)
    {
        $this->selectStatement = " " . $selectStatement;
    }

    public function getJoinStatement() : string
    {
        return $this->joinStatement;
    }

    public function setJoinStatement(string $joinStatement)
    {
        $this->joinStatement = " " . $joinStatement;
    }

    public function getWhereStatement() : string
    {
        return $this->whereStatement;
    }

    public function setWhereStatement(string $whereStatement)
    {
        $this->whereStatement = " " . $whereStatement;
    }

    public function getGroupStatement() : string
    {
        return $this->groupStatement;
    }

    public function setGroupStatement(string $groupStatement)
    {
        $this->groupStatement = " " . $groupStatement;
    }

    public function getOrderStatement() : string
    {
        return $this->orderStatement;
    }

    public function setOrderStatement(string $orderStatement)
    {
        $this->orderStatement = " " . $orderStatement;
    }

    /**
     * Apply custom sorting
     */
    public function applyCustomSorting(
        ilDclBaseFieldModel $field,
        array $all_records,
        string $direction = 'asc'
    ) : array {
        return $all_records;
    }
}
