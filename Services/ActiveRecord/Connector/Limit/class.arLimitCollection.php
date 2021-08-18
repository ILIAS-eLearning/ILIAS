<?php

/**
 * Class arLimitCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arLimitCollection extends arStatementCollection
{

    public function asSQLStatement() : string
    {
        if ($this->hasStatements()) {
            /**
             * @var $last arLimit
             */
            $statements = $this->getStatements();
            $last = end($statements);

            return $last->asSQLStatement($this->getAr());
        }
        return '';
    }
}
