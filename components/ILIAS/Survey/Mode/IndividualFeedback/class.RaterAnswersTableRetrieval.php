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

namespace ILIAS\Survey\Mode\IndividualFeedback;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

/**
 * Provides the answers of the raters to a single (non matrix) question
 * (Results > Details, individual feedback mode) for a KS data table.
 */
class RaterAnswersTableRetrieval implements DataRetrieval
{
    public const COL_RATER = "rater";
    public const COL_DATE = "date";
    public const COL_ANSWER = "answer";

    /**
     * @param array<int, array{rater: string, date: string, tstamp: int, answer: string}> $records keyed by active id
     */
    public function __construct(
        protected array $records
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
        $records = $this->getSortedRecords($order);
        $records = array_slice(
            $records,
            $range->getStart(),
            $range->getLength(),
            true
        );
        foreach ($records as $active_id => $record) {
            yield $row_builder->buildDataRow(
                (string) $active_id,
                [
                    self::COL_RATER => $this->escape($record["rater"]),
                    self::COL_DATE => $this->escape($record["date"]),
                    self::COL_ANSWER => $this->escape($record["answer"])
                ]
            );
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->records);
    }

    protected function getSortedRecords(Order $order): array
    {
        [$field, $direction] = $order->join(
            [],
            static fn(array $ret, string $key, string $value): array => [$key, $value]
        );

        $records = $this->records;
        uasort(
            $records,
            static function (array $a, array $b) use ($field): int {
                return match ($field) {
                    self::COL_DATE => $a["tstamp"] <=> $b["tstamp"],
                    self::COL_ANSWER => strnatcasecmp($a["answer"], $b["answer"]),
                    default => strnatcasecmp($a["rater"], $b["rater"])
                };
            }
        );
        if ($direction === Order::DESC) {
            $records = array_reverse($records, true);
        }
        return $records;
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, "UTF-8", false);
    }
}
