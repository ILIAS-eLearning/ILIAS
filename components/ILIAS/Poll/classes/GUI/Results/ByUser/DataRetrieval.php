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

namespace ILIAS\Poll\GUI\Results\ByUser;

use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Table\DataRetrieval as DataRetrievalInterface;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ilObjPoll;
use ilLanguage;
use Generator;
use ilUtil;

class DataRetrieval implements DataRetrievalInterface
{
    protected array $data;

    public function __construct(
        protected ilObjPoll $poll,
        protected UIFactory $ui_factory,
        protected ilLanguage $lng
    ) {
    }

    public function getAnswers(): array
    {
        return $this->poll->getAnswers();
    }

    public function getPollQuestion(): string
    {
        return $this->poll->getQuestion();
    }

    protected function getRawData(): array
    {
        if (isset($this->data)) {
            return $this->data;
        }

        $answer_ids = $this->getAnswerIds();
        $data = [];
        foreach ($this->poll->getVotesByUsers() as $user_id => $vote) {
            $answers = (array) ($vote["answers"] ?? []);
            unset($vote["answers"]);

            foreach ($answer_ids as $answer_id) {
                $vote[TableBuilder::TABLE_COL_ANSWER_PREFIX . $answer_id] = in_array($answer_id, $answers);
            }

            $data[] = $vote;
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
        $answer_ids = $this->getAnswerIds();
        $comparator = null;
        $rows = $this->getRawData();
        for ($i = 0; $i < count($rows); $i++) {
            $rows[$i]['column'] = $column_name;
        }
        switch ($column_name) {
            case TableBuilder::TABLE_COL_LOGIN:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['login'], $f2['login']);
                };
                break;
            case TableBuilder::TABLE_COL_FIRSTNAME:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['firstname'], $f2['firstname']);
                };
                break;
            case TableBuilder::TABLE_COL_LASTNAME:
                $comparator = function (array $f1, array $f2) {
                    return strcmp($f1['lastname'], $f2['lastname']);
                };
                break;
            default:
                $comparator = function (array $f1, array $f2) {
                    return $f1[$f1['column']] ? $f2[$f2['column']] ? 0 : 1 : -1;
                };
        }
        uasort($rows, $comparator);
        if ($direction === "DESC") {
            $rows = array_reverse($rows, true);
        }
        $rows = array_slice($rows, $range->getStart(), $range->getLength(), true);
        $checked_icon = $this->ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath('standard/icon_ok.svg'),
            $this->lng->txt('poll_answer_selected_alt_text'),
            'medium'
        );
        $unchecked_icon = $this->ui_factory->symbol()->icon()->custom(
            ilUtil::getImagePath('standard/icon_not_ok.svg'),
            $this->lng->txt('poll_answer_selected_alt_text'),
            'medium'
        );
        foreach ($rows as $row) {
            $record = [
                TableBuilder::TABLE_COL_LOGIN => (string) ($row['login'] ?? ''),
                TableBuilder::TABLE_COL_FIRSTNAME => (string) ($row['firstname'] ?? ''),
                TableBuilder::TABLE_COL_LASTNAME => (string) ($row['lastname'] ?? ''),
            ];
            foreach ($answer_ids as $answer_id) {
                $icon = $row[TableBuilder::TABLE_COL_ANSWER_PREFIX . $answer_id]
                    ? $checked_icon
                    : $unchecked_icon;
                $record[TableBuilder::TABLE_COL_ANSWER_PREFIX . $answer_id] = $icon;
            }
            yield $row_builder->buildDataRow(
                '' . $row['login'],
                $record
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

    protected function getAnswerIds(): array
    {
        $a_answer_ids = [];
        foreach ($this->getAnswers() as $answer) {
            $a_answer_ids[] = (int) ($answer["id"] ?? 0);
        }
        return $a_answer_ids;
    }
}
