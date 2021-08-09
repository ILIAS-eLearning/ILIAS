<?php

/**
 * Class arSelectCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arSelectCollection extends arStatementCollection
{

    public function asSQLStatement() : string
    {
        $return = 'SELECT ';
        if ($this->hasStatements()) {
            $activeRecord = $this->getAr();
            $selectSQLs = array_map(fn($select) => $select->asSQLStatement($activeRecord), $this->getSelects());
            $return .= implode(', ', $selectSQLs);
        }

        return $return;
    }

    /**
     * @return arSelect[]
     */
    public function getSelects() : array
    {
        return $this->statements;
    }
}
