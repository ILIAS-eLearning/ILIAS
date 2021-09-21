<?php

/**
 * Class arConcatCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arConcatCollection extends arStatementCollection
{

    public function asSQLStatement() : string
    {
        $return = '';
        if ($this->hasStatements()) {
            $return = ', ';
            $concats = $this->getConcats();
            foreach ($concats as $concat) {
                $return .= $concat->asSQLStatement($this->getAr());
                if ($concat !== end($concats)) {
                    $return .= ', ';
                }
            }
        }

        return $return;
    }

    /**
     * @return arConcat[]
     */
    public function getConcats() : array
    {
        return $this->statements;
    }
}
