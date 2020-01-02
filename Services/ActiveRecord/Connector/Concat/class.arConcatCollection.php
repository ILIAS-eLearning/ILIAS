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
    public function asSQLStatement()
    {
        $return = '';
        if ($this->hasStatements()) {
            $return = ', ';
            foreach ($this->getConcats() as $concat) {
                $return .= $concat->asSQLStatement($this->getAr());
                if ($concat != end($this->getConcats())) {
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
