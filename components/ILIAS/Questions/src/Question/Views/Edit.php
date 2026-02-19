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
use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class Edit
{
    private const string CMD_SAVE_QUESTION = 'sq';

    public function __construct(
        private readonly ConfigurationRepository $configuration_repository,
        private readonly \ilObjUser $current_user,
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
        $ff = $environment->getUIFactory()->input()->field();

        $inputs = [
            'question' => $ff->group(
                $this->buildBasicPropertiesInputs(
                    $environment->getUIFactory()->input()->field(),
                    $environment->getLanguage()
                )
            )->withAdditionalTransformation(
                $this->buildAddBasicPropertiesToQuestionTrafo(
                    $environment->getRefinery()
                )
            )->withValue(
                $this->buildBasicPropertiesBasicValuesArray()
            )
        ];

        if ($this->configuration_repository->isCreateModeChangeableByUser()) {
            $inputs['create_mode'] = $this->configuration_repository->getInputForCreateMode(
                $ff,
                $environment->getLanguage(),
                $environment->getRefinery()
            )->withAdditionalTransformation(
                $environment->getRefinery()->custom()->transformation(
                    fn(string $v): CreateModes => CreateModes::tryFrom($v)
                        ?? $this->configuration_repository->getGlobalCreateMode()
                )
            );
        }

        return $environment->getPresentationFactory()->getEditForm(
            $ff->section(
                $inputs,
                $environment->getLanguage()->txt('edit_basic_form_properties')
            ),
            $environment
                ->withStepParameter(self::CMD_SAVE_QUESTION)
                ->getUrlBuilder(),
            null,
            false
        );
    }

    private function processBasicPropertiesCreateForm(
        EnvironmentImplementation $environment
    ): EditForm|Question {
        $form = $this->buildBasicPropertiesCreateForm(
            $environment
        )->withRequest($environment->getHttpServices()->request());

        $data = $form->getData();
        return $data === null
            ? $form
            : $data['question']->withCreateMode($data['create_mode']);
    }

    private function buildBasicPropertiesEditingForm(
        EnvironmentImplementation $environment
    ): EditForm {
        return $environment->getPresentationFactory()->getEditForm(
            $environment->getUIFactory()->input()->field()->section(
                $this->buildBasicPropertiesInputs(
                    $environment->getUIFactory()->input()->field(),
                    $environment->getLanguage()
                ),
                $environment->getLanguage()->txt('edit_basic_form_properties')
            )->withAdditionalTransformation(
                $this->buildAddBasicPropertiesToQuestionTrafo(
                    $environment->getRefinery()
                )
            )->withValue(
                $this->buildBasicPropertiesBasicValuesArray()
            ),
            $environment
                ->withStepParameter(self::CMD_SAVE_QUESTION)
                ->getUrlBuilder(),
            null,
            true
        );
    }

    private function processBasicPropertiesEditingForm(
        EnvironmentImplementation $environment
    ): EditForm|Question {
        $form = $this->buildBasicPropertiesEditingForm(
            $environment
        )->withRequest($environment->getHttpServices()->request());

        $data = $form->getData();
        return $data === null
            ? $form
            : $data;
    }

    private function buildBasicPropertiesInputs(
        FieldFactory $field_factory,
        Language $lng
    ): array {
        return [
            'title' => $field_factory->text($lng->txt('title'))
                ->withRequired(true),
            'author' => $field_factory->text($lng->txt('author')),
            'lifecycle' => $field_factory->select(
                $lng->txt('qst_lifecycle'),
                array_reduce(
                    Lifecycle::cases(),
                    function (array $c, Lifecycle $v) use ($lng): array {
                        $c[$v->value] = $lng->txt("qst_lifecycle_{$v->value}");
                        return $c;
                    },
                    []
                )
            )->withRequired(true),
            'remarks' => $field_factory->textarea($lng->txt('qst_remarks'))
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

    private function buildAddBasicPropertiesToQuestionTrafo(
        Refinery $refinery
    ): Transformation {
        return $refinery->custom()->transformation(
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
        return $environment->getUIFactory()->panel()->standard(
            $environment->getLanguage()->txt('preview'),
            $environment->getUIFactory()->legacy()->content(
                $participant_view->get($environment->getObjId())
            )
        )->withActions(
            $environment->getUIFactory()->dropdown()->standard([
                $environment->getUIFactory()->link()->standard(
                    $environment->getLanguage()->txt('edit'),
                    $this->ctrl->getLinkTargetByClass(\QstsQuestionPageGUI::class, 'edit')
                )
            ])
        );
    }
}
