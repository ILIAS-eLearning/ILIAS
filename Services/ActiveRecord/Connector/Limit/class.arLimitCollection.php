<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arLimit.php');

/**
 * Class arLimitCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arLimitCollection extends arStatementCollection
{

    /**
     * @return string
     */
    public function asSQLStatement()
    {
        if ($this->hasStatements()) {
            /**
             * @var $last arLimit
             */
            $last = end($this->getStatements());

            return $last->asSQLStatement($this->getAr());
        }
    }
}
