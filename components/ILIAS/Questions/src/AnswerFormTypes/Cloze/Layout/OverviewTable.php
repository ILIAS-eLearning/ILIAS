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

use ILIAS\Questions\AnswerFormTypes\Cloze\Views\EditGaps;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\Data as DataTable;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

class OverviewTable implements DataRetrieval
{
    public function __construct(
        private readonly Environment $environment
    ) {
    }

    public function getTable(): DataTable
    {
        return $this->environment->getUIFactory()->table()->data(
            $this,
            $this->environment->getLanguage()->txt('gaps'),
            $this->getColums()
        )->withActions($this->getActions())
        ->withRequest(
            $this->environment->getHttpServices()->request()
        );
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
            ->toTableRows($row_builder, $this->environment->getLanguage());
    }

    #[\Override]
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return $this->environment->getAnswerFormProperties()
            ->getGaps()->getNumberOfGaps();
    }

    private function getColums(): array
    {
        $f = $this->environment->getUIFactory()->table()->column();

        return [
            'gap' => $f->text(
                $this->environment->getLanguage()->txt('title')
            )->withIsSortable(false),
            'type' => $f->text(
                $this->environment->getLanguage()->txt('cloze_type')
            )->withIsSortable(false),
            'answers_options_awarding_points' => $f->text(
                $this->environment->getLanguage()->txt('answer_options_awarding_points')
            )->withIsSortable(false),
            'available_points' => $f->number(
                $this->environment->getLanguage()->txt('available_points')
            )->withDecimals(4)
            ->withIsSortable(false)
        ];
    }

    private function getActions(): array
    {
        $taf = $this->environment->getUIFactory()->table()->action();
        return [
            'edit_gaps' => $taf->standard(
                $this->environment->getLanguage()->txt('edit_gaps'),
                $this->environment
                    ->withStepParameter(EditGaps::STEP_JUMP_TO_SET_GAP_TYPES)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            ),
            'edit_answer_options' => $taf->standard(
                $this->environment->getLanguage()->txt('edit_answer_options'),
                $this->environment
                    ->withStepParameter(EditGaps::STEP_JUMP_TO_SET_ANSWER_OPTIONS)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            ),
            'edit_points' => $taf->standard(
                $this->environment->getLanguage()->txt('edit_available_points'),
                $this->environment
                    ->withStepParameter(EditGaps::STEP_JUMP_TO_ASSIGN_POINTS)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            )
        ];
    }
}
