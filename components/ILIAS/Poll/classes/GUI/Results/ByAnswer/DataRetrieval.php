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

namespace ILIAS\Poll\GUI\Results\ByAnswer;

use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ilObjPoll;
use Generator;

class DataRetrieval implements DataRetrievalInterface
{
    protected array $data;

    public function __construct(
        protected ilObjPoll $poll
    ) {
    }

    public function getQuestion(): string
    {
        return $this->poll->getQuestion();
    }

    protected function getRawData(): array
    {
        if (isset($this->data)) {
            return $this->data;
        }

        $data = $this->poll->getAnswers();
        $perc = $this->poll->getVotePercentages();
        $perc = (array) ($perc["perc"] ?? []);
        // add current percentages
        foreach ($data as $idx => $item) {
            $item_id = (int) ($item['id'] ?? 0);
            if (!isset($perc[$item_id])) {
                $data[$idx]["percentage"] = 0;
                $data[$idx]["votes"] = 0;
            } else {
                $data[$idx]["percentage"] = round((float) ($perc[$item_id]["perc"] ?? 0));
                $data[$idx]["votes"] = (int) ($perc[$item_id]["abs"] ?? 0);
            }
        }
        return $this->data = $data;
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
        [$column_name, $direction] = $order->join([], fn($ret, $key, $value) => [$key, $value]);
        $comparator = function (array $f1, array $f2) {
            return 0;
        };
        switch ($column_name) {
            case TableBuilder::TABLE_COL_ORDER:
                $comparator = function (array $f1, array $f2) {
                    if ((int) $f1['pos'] === (int) $f2['pos']) {
                        return 0;
                    }
                    return (int) $f1['pos'] > (int) $f2['pos'] ? 1 : -1;
                };
                break;
            case TableBuilder::TABLE_COL_ANSWER:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['answer'], $f2['answer']);
                };
                break;
            case TableBuilder::TABLE_COL_CURRENT_VOTES:
                $comparator = function (array $f1, array $f2) {
                    if ((int) $f1['votes'] === (int) $f2['votes']) {
                        return 0;
                    }
                    return (int) $f1['votes'] > (int) $f2['votes'] ? 1 : -1;
                };
                break;
            case TableBuilder::TABLE_COL_CURRENT_PERCENTAGE:
                $comparator = function (array $f1, array $f2) {
                    if ((int) $f1['percentage'] === (int) $f2['percentage']) {
                        return 0;
                    }
                    return (int) $f1['percentage'] > (int) $f2['percentage'] ? 1 : -1;
                };
                break;
        }
        $rows = $this->getRawData();
        uasort($rows, $comparator);
        if ($direction === "DESC") {
            $rows = array_reverse($rows, true);
        }
        $rows = array_slice($rows, $range->getStart(), $range->getLength(), true);
        foreach ($rows as $row) {
            yield $row_builder->buildDataRow(
                $row['id'] . '',
                [
                    TableBuilder::TABLE_COL_ORDER => (int) ($row['pos'] ?? 10) / 10,
                    TableBuilder::TABLE_COL_ANSWER => $row['answer'] ?? '',
                    TableBuilder::TABLE_COL_CURRENT_VOTES => (int) ($row['votes'] ?? 0),
                    TableBuilder::TABLE_COL_CURRENT_PERCENTAGE => (int) ($row['percentage'] ?? 0),
                ]
            );
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->getRawData());
    }
}
