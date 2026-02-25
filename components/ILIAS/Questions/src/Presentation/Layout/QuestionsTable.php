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

namespace ILIAS\Questions\Presentation\Layout;

use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\Presentation\Definitions\EnvironmentImplementation;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;

class QuestionsTable implements Renderable, DataRetrieval
{
    public function __construct(
        private readonly \ilUIService $ui_service,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository,
        private readonly EnvironmentImplementation $environment
    ) {
        $environment->getLanguage()->loadLanguageModule('qpl');
    }

    public function render(
        UIRenderer $ui_renderer
    ): string {
        return $ui_renderer->render($this->buildContent());
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
        $environment_with_action = $this->environment->withActionParameter(
            Edit::ACTION_EDIT_QUESTION
        );
        foreach ($this->questions_repository->getQuestionDataOnlyForAllQuestions() as $question) {
            yield $question->toTableRow(
                $row_builder,
                $environment_with_action
            );
        }
    }

    #[\Override]
    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return 0;
    }

    private function buildContent(): array
    {
        return [
            $this->buildFilter($this->environment->getUrlBuilder()->buildURI()->__toString()),
            $this->environment->getUIFactory()->table()->data(
                $this,
                $this->environment->getLanguage()->txt('questions'),
                $this->getColums(),
            )->withActions(
                $this->getActions()
            )->withRange(new Range(0, 20))
            ->withRequest($this->environment->getHttpServices()->request())
        ];
    }

    private function buildFilter(
        string $action
    ): Filter {
        $question_type_options = [
            '' => $this->environment->getLanguage()->txt('filter_all_question_types')
        ];

        $field_factory = $this->environment->getUIFactory()->input()->field();
        $filter_inputs = [
            'title' => $field_factory->text(
                $this->environment->getLanguage()->txt('title')
            ),
            'contains_type' => $field_factory->select(
                $this->environment->getLanguage()->txt('contains_type'),
                $question_type_options + $this->answer_form_factory
                    ->getAnswerFormTypesArrayForSelect(
                        $this->environment->getLanguage()
                    )
            ),
        ];

        $active = array_fill(0, count($filter_inputs), true);

        $filter = $this->ui_service->filter()->standard(
            'question_table_filter_id',
            $action,
            $filter_inputs,
            $active,
            true,
            true
        );
        return $filter;
    }

    private function getColums(): array
    {
        $f = $this->environment->getUIFactory()->table()->column();

        return [
            'title' => $f->link($this->environment->getLanguage()->txt('title')),
            'type' => $f->text(
                $this->environment->getLanguage()->txt('question_type')
            )->withIsOptional(true, true),
        ];
    }

    private function getActions(): array
    {
        return [
            'delete' => $this->environment->getUIFactory()->table()->action()->standard(
                $this->environment->getLanguage()->txt('delete'),
                $this->environment->withActionParameter(Edit::ACTION_DELETE_QUESTIONS)
                    ->getUrlBuilder(),
                $this->environment->getTableRowIdToken()
            )->withAsync(true)
        ];
    }
}
