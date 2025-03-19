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

namespace ILIAS\Test\Questions\Presentation;

use ILIAS\Test\Utilities\TitleColumnsBuilder;
use ILIAS\Test\Questions\Properties\Repository as TestQuestionsRepository;
use ILIAS\Test\Questions\Properties\Properties as TestQuestionProperties;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Table\Ordering;
use ILIAS\UI\Component\Table\OrderingRetrieval;
use ILIAS\UI\Component\Table\OrderingRowBuilder;
use ILIAS\Language\Language;
use Psr\Http\Message\ServerRequestInterface;

class QuestionsTable implements OrderingRetrieval
{
    /**
     * @param array $data <string, mixed>
     */
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly ServerRequestInterface $request,
        private readonly QuestionsTableActions $table_actions,
        private readonly Language $lng,
        private readonly \ilObjTest $test_obj,
        private readonly TestQuestionsRepository $questionrepository,
        private readonly TitleColumnsBuilder $title_builder,
    ) {
    }

    public function getTableComponent(): Ordering
    {
        $table = $this->ui_factory->table()->ordering(
            $this,
            $this->table_actions->getOrderActionUrl(),
            $this->lng->txt('list_of_questions'),
            $this->getColumns(),
        )
        ->withId((string) $this->test_obj->getId())
        ->withActions($this->table_actions->getActions())
        ->withRequest($this->request);

        return $table;
    }

    public function getRows(
        OrderingRowBuilder $row_builder,
        array $visible_column_ids
    ): \Generator {
        foreach ($this->getRecords() as $record) {
            $row = $record->getAsQuestionsTableRow(
                $this->lng,
                $this->ui_factory,
                $this->table_actions->getQuestionTargetLinkBuilder(),
                $row_builder,
                $this->title_builder
            );
            yield $this->table_actions->setDisabledActions($row, $record);
        }
    }

    private function getColumns(): array
    {
        $f = $this->ui_factory;
        $columns = [
            'question_id' => $f->table()->column()->text($this->lng->txt('question_id'))
                ->withIsOptional(true, false),
            'title' => $f->table()->column()->link($this->lng->txt('tst_question_title')),
            'description' => $f->table()->column()->text($this->lng->txt('description'))
                ->withIsOptional(true, false),
            'complete' => $f->table()->column()->boolean(
                $this->lng->txt('question_complete_title'),
                $f->symbol()->icon()->custom('assets/images/standard/icon_checked.svg', '', 'small'),
                $f->symbol()->icon()->custom('assets/images/standard/icon_alert.svg', '', 'small')
            ),
            'type_tag' => $f->table()->column()->text($this->lng->txt('tst_question_type')),
            'points' => $f->table()->column()->text($this->lng->txt('points')),
            'author' => $f->table()->column()->text($this->lng->txt('author'))
                ->withIsOptional(true, false),
            'lifecycle' => $f->table()->column()->text($this->lng->txt('qst_lifecycle'))
                ->withIsOptional(true, false),
            'qpl' => $f->table()->column()->link($this->lng->txt('qpl')),
            'nr_of_answers' => $f->table()->column()->number($this->lng->txt('number_of_answers'))
                ->withIsOptional(true, false),
            'average_points' => $f->table()->column()->number($this->lng->txt('average_reached_points'))
                ->withIsOptional(true, false),
            'percentage_points_achieved' => $f->table()->column()->text($this->lng->txt('percentage_points_achieved'))
                ->withIsOptional(true, false),
        ];

        return $columns;
    }

    private function getActions(): array
    {
        $this->table_actions->getActions();
    }

    private function getRecords(): \Generator
    {
        $records = $this->questionrepository
            ->getQuestionPropertiesWithAggregatedResultsForTest($this->test_obj);
        usort(
            $records,
            static fn(TestQuestionProperties $a, TestQuestionProperties $b): int =>
                $a->getSequenceInformation()?->getPlaceInSequence() <=> $b->getSequenceInformation()?->getPlaceInSequence()
        );
        yield from $records;
    }
}
