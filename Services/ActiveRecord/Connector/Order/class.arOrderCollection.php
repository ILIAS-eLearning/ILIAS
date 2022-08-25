<?php

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class arOrderCollection
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @version 2.0.7
 */
class arOrderCollection extends arStatementCollection
{
    public function asSQLStatement(): string
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
    public function getOrders(): array
    {
        return $this->statements;
    }
}
