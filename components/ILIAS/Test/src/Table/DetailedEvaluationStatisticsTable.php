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

namespace ILIAS\Test\Table;

use ilCtrlException;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Data;
use ilLanguage;
use ilTestEvaluationData;

class DetailedEvaluationStatisticsTable extends TestTable
{
    private array $rowData = [];

    public function __construct(
        private readonly ilLanguage $lng,
        private readonly UIFactory $uiFactory,
        private readonly int $activeId,
        private readonly ilTestEvaluationData $data,
        private readonly int $pass,
    ) {
    }

    protected function collectRecords(?array $filter_data, ?array $additional_parameters): array
    {
        if (empty($this->rowData)) {
            $this->load();
        }

        return $this->rowData;
    }

    /**
     * @throws ilCtrlException
     */
    public function getComponent(): Data
    {
        return $this->uiFactory
            ->table()
            ->data($this->lng->txt('questions'), $this->getColumns(), $this)
            ->withTitle(sprintf($this->lng->txt('tst_eval_question_points'), $this->pass + 1))
            ->withActions($this->getActions())
        ;
    }

    protected function getColumns(): array
    {
        $columnFactory = $this->uiFactory->table()->column();

        return  [
            'counter' => $columnFactory
                ->text($this->lng->txt('counter')),
            'question_id' => $columnFactory
                ->number($this->lng->txt('id')),
            'title' => $columnFactory
                ->text($this->lng->txt('title')),
            'points' => $columnFactory
                ->text($this->lng->txt('points')),
        ];
    }

    protected function getActions(): array
    {
        return [];
    }

    private function getTableData(array $questions, ilTestEvaluationData $data, int $activeId, int $pass): array
    {
        $tableData = [];

        $counter = 0;
        foreach ($questions as $question) {
            $userDataData = [
                'counter' => ++$counter,
                'question_id' => $question['id'],
                'id_txt' => $this->lng->txt('question_id_short'),
                'title' => $data->getQuestionTitle($question['id'])
            ];

            $answeredQuestion = $data->getParticipant($activeId)->getPass($pass)?->getAnsweredQuestionByQuestionId($question['id']);
            if (is_array($answeredQuestion)) {
                $percent = $answeredQuestion['points'] ? $answeredQuestion['reached'] / $answeredQuestion['points'] * 100.0 : 0;
                $userDataData['points'] = $answeredQuestion['reached'] . ' ' . strtolower($this->lng->txt('of')) . ' ' . $answeredQuestion['points'] . ' (' . sprintf('%.2f', $percent) . ' %)';
                $tableData[] = $userDataData;
                continue;
            }

            $userDataData['points'] = '0 ' . strtolower($this->lng->txt('of')) . ' ' . $question['points'] . ' (' . sprintf('%.2f', 0) . ' %) - ' . $this->lng->txt('question_not_answered');
            $tableData[] = $userDataData;
        }

        return $tableData;
    }

    private function load(): void
    {
        $this->rowData = $this->getTableData(
            $this->data->getParticipant($this->activeId)->getQuestions($this->pass)
                ?? $this->data->getParticipant($this->activeId)->getQuestions(0),
            $this->data,
            $this->activeId,
            $this->pass,
        );
    }

    protected function getRowID(array $record): string
    {
        return (string) $record['question_id'];
    }
}
