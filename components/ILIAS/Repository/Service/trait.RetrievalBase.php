<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Repository;

use ILIAS\Data\Range;
use ILIAS\Data\Order;

trait RetrievalBase
{
    protected function applyOrder(array $data, ?Order $order = null): array
    {
        if ($order !== null) {
            $order_field = array_keys($order->get())[0];
            $order_direction = $order->get()[$order_field];

            if (count(array_column($data, $order_field)) === 0) {
                return $data;
            }
            array_multisort(
                array_column($data, $order_field),
                $order_direction === 'ASC' ? SORT_ASC : SORT_DESC,
                $this->isFieldNumeric($order_field) ? SORT_NUMERIC : SORT_STRING,
                $data
            );
        }
        return $data;
    }

    protected function applyRange(array $data, ?Range $range = null): array
    {
        if ($range !== null) {
            $offset = $range->getStart();
            $limit = $range->getLength();
            $data = array_slice($data, $offset, $limit);
        }
        return $data;
    }
}
