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
use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\Presentation\Definitions\OverviewTableColumns;
use ILIAS\Questions\Presentation\Views\Edit;
use ILIAS\Questions\Question\Persistence\Repository;
use ILIAS\Data\Range;
use ILIAS\Data\Order;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Button\Primary as PrimaryButton;
use ILIAS\UI\Component\Input\Container\Filter\Standard as Filter;

class QuestionsTable implements Renderable, DataRetrieval
{
    private ?PrimaryButton $create_question_button = null;

    public function __construct(
        private readonly \ilUIService $ui_service,
        private readonly AnswerFormFactory $answer_form_factory,
        private readonly Repository $questions_repository,
        private readonly DefaultEnvironment $environment
    ) {
        $environment->getLanguage()->loadLanguageModule('qpl');
    }

    public function render(
        UIRenderer $ui_renderer
    ): string {

        $rendered_content = '';

        if ($this->create_question_button !== null) {
            $toolbar = new \ilToolbarGUI();
            $toolbar->addComponent(
                $this->create_question_button
            );
            $rendered_content = $toolbar->getHTML();
        }
        return $rendered_content . $ui_renderer->render($this->buildContent());
    }

    public function withCreateQuestionButton(
        PrimaryButton $create_question_button
    ): self {
        $clone = clone $this;
        $clone->create_question_button = $create_question_button;
        return $clone;
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
        foreach ($this->questions_repository->getQuestionDataOnlyForAllQuestions(
            $range,
            $order,
            $filter_data
        ) as $question) {
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
        return $this->questions_repository->getQuestionsCount();
    }

    private function buildContent(): array
    {
        $filter = $this->buildFilter(
            $this->environment->getUrlBuilder()->buildURI()->__toString()
        );

        return [
            $filter,
            $this->environment->getUIFactory()->table()->data(
                $this,
                $this->environment->getLanguage()->txt('questions'),
                OverviewTableColumns::getTableColums(
                    $this->environment->getLanguage(),
                    $this->environment->getUIFactory()->table()->column()
                ),
            )->withActions(
                $this->getActions()
            )->withRange(new Range(0, 20))
            ->withFilter(
                $this->ui_service->filter()->getData($filter)
            )->withRequest($this->environment->getHttpServices()->request())
        ];
    }

    private function buildFilter(
        string $action
    ): Filter {
        $filter_inputs = OverviewTableColumns::getFilderInputs(
            $this->environment->getLanguage(),
            $this->environment->getUIFactory()->input()->field(),
            $this->answer_form_factory->getAnswerFormTypesArrayForSelect(
                $this->environment->getLanguage()
            )
        );

        $active = array_fill(0, count($filter_inputs), true);

        $filter = $this->ui_service->filter()->standard(
            'question_table_filter_id',
            $action,
            $filter_inputs,
            $active,
            true,
            true
        );

        $request = $this->environment->getHttpServices()->request();
        return $request->getMethod() === 'POST'
            ? $filter->withRequest($request)
            : $filter;
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
