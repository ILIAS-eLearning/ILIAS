<?php

/**
 * Class arOrderCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arOrderCollection extends arStatementCollection
{

    /**
     * @return string
     */
    public function asSQLStatement() : string
    {
        $return = '';
        if ($this->hasStatements()) {
            $return .= ' ORDER BY ';
            $orders = $this->getOrders();
            foreach ($orders as $order) {
                $return .= $order->asSQLStatement($this->getAr());
                if ($order !== end($orders)) {
                    $return .= ', ';
                }
            }
        }

        return $return;
    }

    /**
     * @return arOrder[]
     */
    public function getOrders() : array
    {
        return $this->statements;
    }
}
