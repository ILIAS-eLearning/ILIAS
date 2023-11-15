<?php

declare(strict_types=1);

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

namespace ILIAS\Skill\Table;

use ILIAS\Data;

/**
 * Wrapper for sortation and pagination of table records
 * @author Thomas Famula <famula@leifos.de>
 */
trait TableRecords
{
    protected function orderRecords(array $records, Data\Order $order): array
    {
        list($order_field, $order_direction) = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        usort($records, fn($a, $b) => $a[$order_field] <=> $b[$order_field]);
        if ($order_direction === "DESC") {
            $records = array_reverse($records);
        }

        return $records;
    }

    protected function limitRecords(array $records, Data\Range $range): array
    {
        $records = array_slice($records, max($range->getStart() - 1, 0), $range->getLength());
        //replace with line below when PR 6527 is merged
        //$records = array_slice($records, $range->getStart(), $range->getLength());

        return $records;
    }
}
