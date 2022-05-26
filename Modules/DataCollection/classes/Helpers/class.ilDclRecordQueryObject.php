<?php

/**
 * Class ilDclRecordQueryObject
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclRecordQueryObject
{
    protected string $selectStatement = "";
    protected string $joinStatement = "";
    protected string $whereStatement = "";
    protected string $groupStatement = "";
    protected string $orderStatement = "";

    public function getSelectStatement() : string
    {
        return $this->selectStatement;
    }

    public function setSelectStatement(string $selectStatement) : void
    {
        $this->selectStatement = " " . $selectStatement;
    }

    public function getJoinStatement() : string
    {
        return $this->joinStatement;
    }

    public function setJoinStatement(string $joinStatement) : void
    {
        $this->joinStatement = " " . $joinStatement;
    }

    public function getWhereStatement() : string
    {
        return $this->whereStatement;
    }

    public function setWhereStatement(string $whereStatement) : void
    {
        $this->whereStatement = " " . $whereStatement;
    }

    public function getGroupStatement() : string
    {
        return $this->groupStatement;
    }

    public function setGroupStatement(string $groupStatement) : void
    {
        $this->groupStatement = " " . $groupStatement;
    }

    public function getOrderStatement() : string
    {
        return $this->orderStatement;
    }

    public function setOrderStatement(string $orderStatement) : void
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
