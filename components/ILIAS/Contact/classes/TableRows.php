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

namespace ILIAS\Contact;

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Component\Table\DataRetrieval;
use Generator;
use Closure;

/**
 * @phpstan-import-type RelationRecord from \ILIAS\Contact\BuddySystem\Tables\RelationsTable
 */
class TableRows implements DataRetrieval
{
    /**
     * @param list<RelationRecord> $records
     * @param list<string> $sigle_actions
     * @param Closure(int, string): string $link_to_profile
     */
    public function __construct(
        private readonly array $records,
        private readonly array $sigle_actions,
        private readonly Closure $link_to_profile
    ) {
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        [$order_field, $order_direction] = $order->join(
            [],
            fn($ret, $key, $value) => [$key, $value]
        );

        $records = $this->records;

        $times = $order_direction === 'ASC' ? 1 : -1;
        usort(
            $records,
            static fn(array $a, array $b): int => $times * strcasecmp(
                $a[$order_field],
                $b[$order_field]
            )
        );

        if ($range) {
            $records = \array_slice($records, $range->getStart(), $range->getLength());
        }

        foreach ($records as $row) {
            $row['public_name'] = ($this->link_to_profile)($row['user_id'], $row['public_name']);
            $row['login'] = ($this->link_to_profile)($row['user_id'], $row['login']);

            $transitions = array_map(
                static fn($s): string => $row['state'] . '->' . $s,
                $row['target_states']
            );

            $data_row = $row_builder->buildDataRow((string) $row['user_id'], $row);
            foreach ($this->sigle_actions as $action) {
                if (!\in_array($action, $transitions, true)) {
                    $data_row = $data_row->withDisabledAction($action);
                }
            }

            yield $data_row;
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return \count($this->records);
    }
}
