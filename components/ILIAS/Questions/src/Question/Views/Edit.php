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

use ILIAS\Questions\Presentation\Layout\Definitions\EditForm;
use ILIAS\Questions\Presentation\Layout\Definitions\Factory as DefinitionsFactory;
use ILIAS\Questions\Presentation\Layout\Definitions\Environment;
use ILIAS\Questions\Question\Question;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Component\Input\Field\Section;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\RequestInterface;

class Edit
{
    private const string CMD_SAVE_QUESTION = 'sq';

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
        Environment $environment
    ): EditForm|Question {
        return match ($environment->getStep()) {
            self::CMD_SAVE_QUESTION => $this->processBasicPropertiesForm($environment),
            default => $this->buildBasicPropertiesForm($environment)
        };
    }

    public function edit(
        Environment $environment
    ): EditForm|Question {
        return match ($environment->getStep()) {
            self::CMD_SAVE_QUESTION => $this->processBasicPropertiesForm($environment),
            default => $this->buildBasicPropertiesForm($environment)->withContentAfterForm(
                $this->buildPreviewPanel($environment)
            )
        };
    }

    private function buildBasicPropertiesForm(
        Environment $environment
    ): EditForm {
        return $environment->getDefinitionsFactory()->getEditForm(
            $environment->getUrlBuilderWithStepParameter(self::CMD_SAVE_QUESTION),
            $this->buildBasicPropertiesInputs(),
            true
        );
    }

    private function processBasicPropertiesForm(
        Environment $environment
    ): EditForm|Question {
        $form = $this->buildBasicPropertiesForm(
            $environment
        )->withRequest($this->request);

        $data = $form->getData();
        return $data === null
            ? $form
            : $data;
    }

    private function buildBasicPropertiesInputs(): Section
    {
        $ff = $this->ui_factory->input()->field();
        $section = $ff->section(
            [
                'title' => $ff->text($this->lng->txt('title'))
                    ->withRequired(true),
                'author' => $ff->text($this->lng->txt('author'))
                    ->withValue($this->current_user->getFullname()),
                'lifecycle' => $ff->select(
                    $this->lng->txt('qst_lifecycle'),
                    array_reduce(
                        Lifecycle::cases(),
                        function (array $c, Lifecycle $v): array {
                            $c[$v->value] = $this->lng->txt("qst_lifecycle_{$v->value}");
                            return $c;
                        },
                        []
                    )
                )->withRequired(true),
                'remarks' => $ff->textarea($this->lng->txt('qst_remarks'))
            ],
            $this->lng->txt('edit_basic_form_properties')
        )->withAdditionalTransformation($this->buildAddBasicPropertiesToQuestionTrafo());

        return $section->withValue([
            'title' => $this->question->getTitle(),
            'author' => $this->question->getAuthor(),
            'lifecycle' => $this->question->getLifecycle()->value,
            'remarks' => $this->question->getRemarks()
        ]);
    }

    private function buildAddBasicPropertiesToQuestionTrafo(): Transformation
    {
        return $this->refinery->custom()->transformation(
            function (array $vs): QuestionImplementation {
                $question = $this->question
                    ->withTitle($vs['title'])
                    ->withAuthor($vs['author'])
                    ->withRemarks($vs['remarks']);

                $lifecycle = Lifecycle::tryFrom($vs['lifecycle']);
                if ($lifecycle !== null) {
                    return $question->withLifecycle($lifecycle);
                }

                return $question;
            }
        );
    }

    private function buildPreviewPanel(): StandardPanel
    {
        return $this->ui_factory->panel()->standard(
            $this->lng->txt('preview'),
            $this->ui_factory->legacy()->content($this->question->getTitle())
        )->withActions(
            $this->ui_factory->dropdown()->standard([
                $this->ui_factory->link()->standard(
                    $this->lng->txt('edit'),
                    $this->ctrl->getLinkTargetByClass(\QstsQuestionPageGUI::class, 'edit')
                )
            ])
        );
    }
}
