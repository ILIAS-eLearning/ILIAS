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

namespace ILIAS\Survey\Evaluation;

use Generator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

class ResultsPerUserTableRetrieval implements DataRetrieval
{
    public const COL_USERNAME = "username";
    public const COL_QUESTION = "question";
    public const COL_RESULTS = "results";
    public const COL_WORKINGTIME = "workingtime";
    public const COL_FINISHED = "finished";

    public function __construct(
        protected \ilLanguage $lng,
        protected array $data
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
        $rows = array_slice(
            $this->getFlatRows($order),
            $range->getStart(),
            $range->getLength()
        );
        foreach ($rows as [$row_id, $record]) {
            yield $row_builder->buildDataRow($row_id, $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        $count = 0;
        foreach ($this->data as $participant) {
            $count += 1 + count(array_filter(
                $participant["subitems"] ?? [],
                static fn($subitem): bool => is_array($subitem)
            ));
        }
        return $count;
    }

    /**
     * @return array<int, array{0: string, 1: array<string, string>}>
     */
    protected function getFlatRows(Order $order): array
    {
        $rows = [];
        foreach ($this->getSortedParticipants($order) as $active_id => $participant) {
            $rows[] = [$active_id . "_0", $this->getRecord($participant)];
            $index = 1;
            foreach ($participant["subitems"] ?? [] as $subitem) {
                if (!is_array($subitem)) {
                    continue;
                }
                $rows[] = [$active_id . "_" . $index++, $this->getRecord($subitem)];
            }
        }
        return $rows;
    }

    protected function getSortedParticipants(Order $order): array
    {
        [$field, $direction] = $order->join(
            [],
            static fn(array $ret, string $key, string $value): array => [$key, $value]
        );

        $participants = $this->data;
        uasort(
            $participants,
            static function (array $a, array $b) use ($field): int {
                return match ($field) {
                    self::COL_WORKINGTIME => (int) ($a["workingtime"] ?? 0) <=> (int) ($b["workingtime"] ?? 0),
                    self::COL_FINISHED => (int) $a["finished"] <=> (int) $b["finished"],
                    default => strnatcasecmp((string) $a["username"], (string) $b["username"])
                };
            }
        );
        if ($direction === Order::DESC) {
            $participants = array_reverse($participants, true);
        }
        return $participants;
    }

    /**
     * Same presentation as ilSurveyResultsUserTableGUI::fillRow()
     */
    protected function getRecord(array $a_set): array
    {
        $results = array_map(
            static fn($i): string => htmlentities((string) $i),
            $a_set["results"] ?? []
        );

        $workingtime = $a_set["workingtime"] ?? null;

        $finished = "";
        if ($a_set["finished"] !== null) {
            if ($a_set["finished"] !== false) {
                $finished = \ilDatePresentation::formatDate(
                    new \ilDateTime($a_set["finished"], IL_CAL_UNIX)
                );
            } else {
                $finished = "-";
            }
        }

        return [
            self::COL_USERNAME => $this->escape((string) $a_set["username"]),
            self::COL_QUESTION => $this->escape((string) $a_set["question"]),
            self::COL_RESULTS => $results
                ? implode("<br />", $results)
                : \ilObjSurvey::getSurveySkippedValue(),
            self::COL_WORKINGTIME => $this->formatTime(
                $workingtime === null ? null : (int) $workingtime
            ),
            self::COL_FINISHED => $finished
        ];
    }

    protected function formatTime(?int $timeinseconds): string
    {
        if (is_null($timeinseconds)) {
            return "";
        }
        if ($timeinseconds === 0) {
            return $this->lng->txt("not_available");
        }
        return sprintf(
            "%02d:%02d:%02d",
            (int) ($timeinseconds / 3600),
            (int) ($timeinseconds / 60) % 60,
            $timeinseconds % 60
        );
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars(trim($value), ENT_QUOTES, "UTF-8", false);
    }
}
