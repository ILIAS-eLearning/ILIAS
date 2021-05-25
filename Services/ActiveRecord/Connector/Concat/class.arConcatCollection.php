<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arConcat.php');

/**
 * Class arConcatCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arConcatCollection extends arStatementCollection
{

    /**
     * @return string
     */
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
    public function getConcats()
    {
        return $this->statements;
    }
}
