<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arWhere.php');

/**
 * Class arWhereCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arWhereCollection extends arStatementCollection
{

    /**
     * @return string
     */
    public function asSQLStatement()
    {
        $return = '';
        if ($this->hasStatements()) {
            $return .= ' WHERE ';
            $wheres = $this->getWheres();
            $last = end($wheres);
            foreach ($wheres as $arWhere) {
                $return .= $arWhere->asSQLStatement($this->getAr());
                if ($arWhere != $last) {
                    $return .= ' ' . $arWhere->getLink() . ' ';
                }
            }
        }

        return $return;
    }


    /**
     * @return arWhere[]
     */
    public function getWheres()
    {
        return $this->statements;
    }
}
