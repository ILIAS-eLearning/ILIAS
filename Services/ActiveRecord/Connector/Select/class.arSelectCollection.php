<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arSelect.php');

/**
 * Class arSelectCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arSelectCollection extends arStatementCollection
{

    /**
     * @return string
     */
    public function asSQLStatement()
    {
        $return = 'SELECT ';
        if ($this->hasStatements()) {
            $activeRecord = $this->getAr();
            $selectSQLs = array_map(function ($select) use ($activeRecord) {
                return $select->asSQLStatement($activeRecord);
            }, $this->getSelects());
            $return .= join(', ', $selectSQLs);
        }

        //		$return .= ' FROM ' . $this->getAr()->getConnectorContainerName();

        return $return;
    }


    /**
     * @return arSelect[]
     */
    public function getSelects()
    {
        return $this->statements;
    }
}
