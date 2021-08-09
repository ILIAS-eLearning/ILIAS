<?php

/**
 * Class arWhereCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arWhereCollection extends arStatementCollection
{

    public function asSQLStatement() : string
    {
        $return = '';
        if ($this->hasStatements()) {
            $return .= ' WHERE ';
            $wheres = $this->getWheres();
            $last = end($wheres);
            foreach ($wheres as $arWhere) {
                $return .= $arWhere->asSQLStatement($this->getAr());
                if ($arWhere !== $last) {
                    $return .= ' ' . $arWhere->getLink() . ' ';
                }
            }
        }

        return $return;
    }

    /**
     * @return arWhere[]
     */
    public function getWheres() : array
    {
        return $this->statements;
    }
}
