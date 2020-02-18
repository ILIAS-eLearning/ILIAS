<?php
require_once(dirname(__FILE__) . '/../Statement/class.arStatementCollection.php');
require_once('class.arOrder.php');

/**
 * Class arOrderCollection
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arOrderCollection extends arStatementCollection
{

    /**
     * @return string
     */
    public function asSQLStatement()
    {
        $return = '';
        if ($this->hasStatements()) {
            $return .= ' ORDER BY ';
            foreach ($this->getOrders() as $order) {
                $return .= $order->asSQLStatement($this->getAr());
                if ($order != end($this->getOrders())) {
                    $return .= ', ';
                }
            }
        }

        return $return;
    }


    /**
     * @return arOrder[]
     */
    public function getOrders()
    {
        return $this->statements;
    }
}
