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

use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Definitions\EnvironmentImplementation;
use ILIAS\Questions\Question\Question;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\UserSettings\CreateMode;
use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\Language\Language;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use Psr\Http\Message\RequestInterface;

class Edit
{
    private const string CMD_SAVE_QUESTION = 'sq';

    public function __construct(
        private readonly Language $lng,
        private readonly ConfigurationRepository $configuration_repository,
        private readonly \ilObjUser $current_user,
        private readonly UIFactory $ui_factory,
        private readonly Refinery $refinery,
        private readonly RequestInterface $request,
        private readonly \ilCtrl $ctrl,
        private readonly QuestionImplementation $question
    ) {

    }

    public function create(
        EnvironmentImplementation $environment
    ): EditForm|Question {
        return match ($environment->getStep()) {
            self::CMD_SAVE_QUESTION => $this->processBasicPropertiesCreateForm(
                $environment
            ),
            default => $this->buildBasicPropertiesCreateForm($environment)
        };
    }

    public function edit(
        EnvironmentImplementation $environment,
        Participant $participant_view
    ): EditForm|Question {
        return match ($environment->getStep()) {
            self::CMD_SAVE_QUESTION => $this->processBasicPropertiesEditingForm(
                $environment
            ),
            default => $this->buildBasicPropertiesEditingForm($environment)
                ->withContentAfterForm(
                    $this->buildPreviewPanel($environment, $participant_view)
                )
        };
    }

    private function buildBasicPropertiesCreateForm(
        EnvironmentImplementation $environment
    ): EditForm {
        $ff = $this->ui_factory->input()->field();

        $inputs = [
            'question' => $ff->group(
                $this->buildBasicPropertiesInputs()
            )->withAdditionalTransformation(
                $this->buildAddBasicPropertiesToQuestionTrafo()
            )->withValue(
                $this->buildBasicPropertiesBasicValuesArray()
            )
        ];

        if ($this->configuration_repository->isCreateModeChangeableByUser()) {
            $inputs['create_mode'] = $this->configuration_repository->getInputForCreateMode(
                $ff,
                $this->lng,
                $this->refinery
            )->withAdditionalTransformation(
                $this->refinery->custom()->transformation(
                    fn(string $v): CreateModes => CreateModes::tryFrom($v)
                        ?? $this->configuration_repository->getGlobalCreateMode()
                )
            );
        }

        return $environment->getPresentationFactory()->getEditForm(
            $environment
                ->getUrlBuilderWithStepParameter(self::CMD_SAVE_QUESTION)
                ->buildURI(),
            $ff->section(
                $inputs,
                $this->lng->txt('edit_basic_form_properties')
            ),
            true
        );
    }

    private function processBasicPropertiesCreateForm(
        EnvironmentImplementation $environment
    ): EditForm|Question {
        $form = $this->buildBasicPropertiesCreateForm(
            $environment
        )->withRequest($this->request);

        $data = $form->getData();

        $mode = $data['create_mode'];

        return $data === null
            ? $form
            : $data['question']->withCreateMode($mode);
    }

    private function buildBasicPropertiesEditingForm(
        EnvironmentImplementation $environment
    ): EditForm {
        return $environment->getPresentationFactory()->getEditForm(
            $environment
                ->getUrlBuilderWithStepParameter(self::CMD_SAVE_QUESTION)
                ->buildURI(),
            $this->ui_factory->input()->field()->section(
                $this->buildBasicPropertiesInputs(),
                $this->lng->txt('edit_basic_form_properties')
            )->withAdditionalTransformation(
                $this->buildAddBasicPropertiesToQuestionTrafo()
            )->withValue(
                $this->buildBasicPropertiesBasicValuesArray()
            ),
            true
        );
    }

    private function processBasicPropertiesEditingForm(
        EnvironmentImplementation $environment
    ): EditForm|Question {
        $form = $this->buildBasicPropertiesEditingForm(
            $environment
        )->withRequest($this->request);

        $data = $form->getData();
        return $data === null
            ? $form
            : $data;
    }

    private function buildBasicPropertiesInputs(): array
    {
        $ff = $this->ui_factory->input()->field();

        return [
            'title' => $ff->text($this->lng->txt('title'))
                ->withRequired(true),
            'author' => $ff->text($this->lng->txt('author')),
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
        ];
    }

    private function buildBasicPropertiesBasicValuesArray(): array
    {
        return [
            'title' => $this->question->getTitle(),
            'author' => $this->question->getAuthor() !== ''
                ? $this->question->getAuthor()
                : $this->current_user->getFullname(),
            'lifecycle' => $this->question->getLifecycle()->value,
            'remarks' => $this->question->getRemarks()
        ];
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

    private function buildPreviewPanel(
        EnvironmentImplementation $environment,
        Participant $participant_view
    ): StandardPanel {
        $environment->preserveParametersForPageEditorCmds();
        return $this->ui_factory->panel()->standard(
            $this->lng->txt('preview'),
            $this->ui_factory->legacy()->content(
                $participant_view->get($environment->getObjId())
            )
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
