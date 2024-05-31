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

use assQuestion;
use ilCtrl;
use ilGlobalTemplateInterface;
use ILIAS\UI\Component\Table\Data;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Factory;
use ilLanguage;
use ilObjFileHandlingQuestionType;
use ilObjTest;
use ilObjTestGUI;
use ilTabsGUI;
use ilTestEvaluationGUI;
use ilTestTabsManager;

class ResultsByQuestionTable implements DataRetrieval
{
    use TableRecordsTrait;

    public function __construct(
        private readonly Factory $ui_factory,
        private readonly ilLanguage $lng,
        private readonly int $parent_obj_id,
        private readonly int $request_ref_id,
        private readonly ilObjTest $object,
        private readonly ilCtrl $ctrl,
        private readonly ilGlobalTemplateInterface $tpl,
        private readonly ilTabsGUI $tabs,
        private readonly bool $statistics_access
    ) {
    }


    public function getComponent(): Data
    {
        return $this->ui_factory->table()->data(
            $this->lng->txt('tst_answered_questions_test'),
            $this->getColumns(),
            $this,
        )->withId('rqt' . $this->parent_obj_id . '_' . $this->request_ref_id);
    }

    public function getColumns(): array
    {
        $column_factory = $this->ui_factory->table()->column();

        return [
            'question_id' => $column_factory
                ->text($this->lng->txt('question_id')),
            'question_title' => $column_factory
                ->text($this->lng->txt('question_title')),
            'number_of_answers' => $column_factory
                ->text($this->lng->txt('number_of_answers')),
            'output' => $column_factory
                ->text($this->lng->txt('output'))
                ->withIsSortable(false),
            'file_uploads' => $column_factory
                ->text($this->lng->txt('file_uploads'))
                ->withIsSortable(false)
        ];
    }

    protected function collectRecords(?array $filter_data, ?array $additional_parameters): array
    {
        if (!$this->statistics_access) {
            ilObjTestGUI::accessViolationRedirect();
        }

        $this->object->setAccessFilteredParticipantList(
            $this->object->buildStatisticsAccessFilteredParticipantList()
        );

        $this->tabs->activateTab(ilTestTabsManager::TAB_ID_STATISTICS);

        $data = $this->object->getCompleteEvaluationData();
        $counter = 0;
        $found_participants = $data->getParticipants();

        $rows = [];
        foreach ($data->getQuestionTitles() as $question_id => $question_title) {
            $answered = 0;
            $reached = 0;
            $max = 0;
            foreach ($found_participants as $userdata) {
                $pass = $userdata->getScoredPass();
                if (is_object($userdata->getPass($pass))) {
                    $question = $userdata->getPass($pass)->getAnsweredQuestionByQuestionId($question_id);
                    if (is_array($question)) {
                        $answered++;
                    }
                }
            }
            $counter++;
            $this->ctrl->setParameter($this, 'qid', $question_id);

            $question_object = assQuestion::instantiateQuestion($question_id);

            $download = '';
            if ($question_object instanceof ilObjFileHandlingQuestionType
                && $question_object->hasFileUploads($this->object->getTestId())) {
                $download = '<a href="' . $this->ctrl->getLinkTargetByClass(ilTestEvaluationGUI::class, 'exportFileUploadsForAllParticipants') . '&qid=' . $question_object->getId() . '">' . $this->lng->txt('download') . '</a>';
            }

            $rows[] = [
                'question_id' => $question_id,
                'question_title' => $question_title,
                'number_of_answers' => $answered,
                'output' => '<a target="_blank" href="' . $this->ctrl->getLinkTargetByClass(ilTestEvaluationGUI::class, 'exportQuestionForAllParticipants') . '&qid=' . $question_object->getId() . '">' . $this->lng->txt('print') . '</a>',
                'file_uploads' => $download
            ];
        }

        return $rows;
    }

    protected function getRowID(array $record): string
    {
        return (string) $record['question_id'];
    }
}
