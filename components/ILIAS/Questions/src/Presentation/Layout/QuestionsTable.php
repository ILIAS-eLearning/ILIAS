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
use ILIAS\Questions\Persistence\Repository;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;
use Psr\Http\Message\ServerRequestInterface;

class QuestionsTable implements Renderable, DataRetrieval
{
    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly \ilUIService $ui_service,
        private readonly Language $lng,
        private readonly ServerRequestInterface $request,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository,
        private readonly EnvironmentImplementation $environment
    ) {
        $lng->loadLanguageModule('qpl');
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
            Edit::CMD_EDIT_QUESTION
        );
        foreach ($this->questions_repository->getQuestionDataOnlyForAllQuestions() as $question) {
            yield $question->toTableRow(
                $row_builder,
                $this->ui_factory,
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
            $this->ui_factory->table()->data(
                $this,
                $this->lng->txt('questions'),
                $this->getColums(),
            )->withActions(
                $this->getActions()
            )->withRange(new Range(0, 20))
            ->withRequest($this->request)
        ];
    }

    private function buildFilter(
        string $action
    ): Filter {
        $question_type_options = [
            '' => $this->lng->txt('filter_all_question_types')
        ];

        $field_factory = $this->ui_factory->input()->field();
        $filter_inputs = [
            'title' => $field_factory->text($this->lng->txt('title')),
            'contains_type' => $field_factory->select(
                $this->lng->txt('contains_type'),
                $question_type_options + $this->answer_form_factory->getAnswerFormTypesArrayForSelect($this->lng)
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
        $f = $this->ui_factory->table()->column();

        return [
            'title' => $f->link($this->lng->txt('title')),
            'type' => $f->text($this->lng->txt('question_type'))->withIsOptional(true, true),
        ];
    }

    private function getActions(): array
    {
        return [
            'delete' => $this->ui_factory->table()->action()->standard(
                $this->lng->txt('delete'),
                $this->environment->withActionParameter(Edit::CMD_DELETE_QUESTIONS)
                    ->getUrlBuilder(),
                $this->environment->getQuestionIdsToken()
            )->withAsync(true)
        ];
    }
}
