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

namespace ILIAS\components\ILIAS\Glossary\Table;

use ILIAS\Data;

/**
 * Wrapper for sortation and pagination of table records
 * @author Thomas Famula <famula@leifos.de>
 */
trait TableRecords
{
    protected function orderRecords(array $records, Data\Order $order): array
    {
        [$aspect, $direction] = $order->join("", function ($i, $k, $v) {
            return [$k, $v];
        });
        usort($records, static function (array $a, array $b) use ($aspect): int {
            if (!isset($a[$aspect]) && !isset($b[$aspect])) {
                return 0;
            }
            if (!isset($a[$aspect])) {
                return -1;
            }
            if (!isset($b[$aspect])) {
                return 1;
            }
            if (is_numeric($a[$aspect]) || is_bool($a[$aspect])) {
                return $a[$aspect] <=> $b[$aspect];
            }
            if (is_array($a[$aspect])) {
                return $a[$aspect] <=> $b[$aspect];
            }
            if ($a[$aspect] instanceof \ILIAS\UI\Component\Link\Link) {
                return $a[$aspect]->getLabel() <=> $b[$aspect]->getLabel();
            }

            return strcmp($a[$aspect], $b[$aspect]);
        });

        if ($direction === $order::DESC) {
            $records = array_reverse($records);
        }
        return $records;
    }

    protected function limitRecords(array $records, Data\Range $range): array
    {
        $records = array_slice($records, $range->getStart(), $range->getLength());

        return $records;
    }
}
