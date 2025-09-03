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

namespace ILIAS\Questions\Question\Views;

use ILIAS\Questions\Question\Question;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Questions\Question\Lifecycle;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Input\Container\Form\Standard as StandardForm;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\RequestInterface;

class Edit
{
    private const string CMD_SAVE_QUESTION = 'sq';

    public const string PAGE_ID_PARAM_FOR_EDITOR = 'p_id';

    public function __construct(
        private readonly Language $lng,
        private readonly \ilObjUser $current_user,
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly RequestInterface $request,
        private readonly \ilCtrl $ctrl,
        private readonly DataFactory $data_factory,
        private readonly QuestionImplementation $question
    ) {

    }

    public function create(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|Question {
        return match ($step) {
            self::CMD_SAVE_QUESTION => $this->onBasicPropertiesFormSubmission($url_builder, $step_token),
            default => [$this->buildBasicPropertiesForm($url_builder, $step_token)]
        };
    }

    public function edit(
        URLBuilder $url_builder,
        URLBuilderToken $step_token,
        string $step
    ): array|Question {
        return match ($step) {
            self::CMD_SAVE_QUESTION => $this->onBasicPropertiesFormSubmission($url_builder, $step_token),
            default => [
                $this->buildBasicPropertiesForm($url_builder, $step_token),
                $this->buildPreviewPanel($url_builder, $step_token)
            ]
        };
    }

    private function buildBasicPropertiesForm(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): StandardForm {
        return $this->ui_factory->input()->container()->form()->standard(
            $url_builder->withParameter($step_token, self::CMD_SAVE_QUESTION)
                ->buildURI()->__toString(),
            $this->buildBasicPropertiesInputs()
        );
    }

    private function onBasicPropertiesFormSubmission(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): array|Question {
        $form = $this->buildBasicPropertiesForm($url_builder, $step_token)
            ->withRequest($this->request);
        $data = $form->getData();
        if ($data === null) {
            return [$form];
        }

        return $data['question'];
    }

    private function buildBasicPropertiesInputs(): array
    {
        $ff = $this->ui_factory->input()->field();
        $section = $ff->section(
            [
                'title' => $ff->text($this->lng->txt('title'))
                    ->withRequired(true),
                'author' => $ff->text($this->lng->txt('author'))
                    ->withValue($this->current_user->getFullname()),
                'lifecycle' => $ff->select(
                    $this->lng->txt('lifecycle'),
                    array_reduce(
                        Lifecycle::cases(),
                        function (array $c, Lifecycle $v): array {
                            $c[$v->value] = $this->lng->txt($v->value);
                            return $c;
                        },
                        []
                    )
                )->withRequired(true),
                'remarks' => $ff->textarea($this->lng->txt('remarks'))
            ],
            $this->lng->txt('edit_basic_form_properties')
        )->withAdditionalTransformation($this->buildAddBasicPropertiesToQuestionTrafo());

        return [
            'question' => $section->withValue([
                'title' => $this->question->getTitle(),
                'author' => $this->question->getAuthor(),
                'lifecycle' => $this->question->getLifecycle()->value,
                'remarks' => $this->question->getRemarks()
            ])
        ];
    }

    private function buildAddBasicPropertiesToQuestionTrafo(): Transformation
    {
        return $this->refinery->custom()->transformation(
            static fn(array $vs): QuestionImplementation => new QuestionImplementation(
                null,
                null,
                $vs['title'],
                $vs['author'],
                Lifecycle::tryFrom($vs['lifecycle']) ?? Lifecycle::Draft,
                $vs['remarks']
            )
        );
    }

    private function buildPreviewPanel(
        URLBuilder $url_builder,
        URLBuilderToken $step_token
    ): StandardPanel {
        $this->ctrl->setParameterByClass(
            \QstsQuestionPageGUI::class,
            self::PAGE_ID_PARAM_FOR_EDITOR,
            $this->question->getPageId()
        );
        return $this->ui_factory->panel()->standard(
            $this->lng->txt('preview'),
            $this->ui_factory->legacy()->content($this->question->getTitle())
        )->withActions(
            $this->ui_factory->dropdown()->standard([
                $this->ui_factory->link()->standard(
                    $this->lng->txt('edit'),
                    $url_builder
                        ->withURI(
                            $this->data_factory->uri(
                                ILIAS_HTTP_PATH . '/' . $this->ctrl->getLinkTargetByClass(\QstsQuestionPageGUI::class, 'edit')
                            )
                        )->buildURI()->__toString()
                )
            ])
        );
    }
}
