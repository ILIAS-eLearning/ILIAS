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

namespace ILIAS\Questions\AnswerFormTypes\Cloze\Layout;

use ILIAS\Questions\AnswerFormTypes\Cloze\Views\Edit;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\Factory as TableFactory;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use Psr\Http\Message\ServerRequestInterface;

class OverviewTable implements DataRetrieval
{
    public function __construct(
        private readonly TableFactory $table_factory,
        private readonly Language $lng,
        private readonly ServerRequestInterface $request,
        private readonly Environment $environment
    ) {
    }

    public function getTable(): DataTable
    {
        return $this->table_factory->data(
            $this,
            $this->lng->txt('gaps'),
            $this->getColums()
        )->withActions($this->getActions())
        ->withRequest($this->request);
    }

    #[\Override]
    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): \Generator {
        yield from $this->environment->getAnswerFormProperties()->getGaps()
            ->toTableRows($row_builder, $this->lng);
    }

    #[\Override]
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return $this->environment->getAnswerFormProperties()->getGaps()->getNumberOfGaps();
    }

    private function getColums(): array
    {
        $f = $this->table_factory->column();

        return [
            'gap' => $f->text($this->lng->txt('title')),
            'type' => $f->text($this->lng->txt('question_type')),
            'answers_options_awarding_points' => $f->text($this->lng->txt('answer_options_awarding_points')),
            'available_points' => $f->number($this->lng->txt('available_points'))
        ];
    }

    private function getActions(): array
    {
        return [
            'edit_gaps' => $this->table_factory->action()->standard(
                $this->lng->txt('edit_gaps'),
                $this->environment
                    ->withStepParameter(Edit::STEP_JUMP_TO_SET_GAP_TYPES)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            ),
            'edit_answer_options' => $this->table_factory->action()->standard(
                $this->lng->txt('edit_answer_options'),
                $this->environment
                    ->withStepParameter(Edit::STEP_JUMP_TO_SET_ANSWER_OPTIONS)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            ),
            'edit_points' => $this->table_factory->action()->standard(
                $this->lng->txt('edit_available_points'),
                $this->environment
                    ->withStepParameter(Edit::STEP_JUMP_TO_SET_POINTS)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            )
        ];
    }
}
