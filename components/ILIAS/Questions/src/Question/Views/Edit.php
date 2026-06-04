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

use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Response as AnswerFormResponse;
use ILIAS\Questions\Administration\ConfigurationRepository;
use ILIAS\Questions\Attempt\Attempt;
use ILIAS\Questions\Attempt\Repository as AttemptRepository;
use ILIAS\Questions\Attempt\Response;
use ILIAS\Questions\Presentation\Layout\EditForm;
use ILIAS\Questions\Presentation\Definitions\DefaultEnvironment;
use ILIAS\Questions\Question\Question;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\UserSettings\CreateModes;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\HTTP\Wrapper\RequestWrapper;
use ILIAS\Language\Language;
use ILIAS\UI\Component\Input\Field\Factory as FieldFactory;
use ILIAS\UI\Component\Panel\Standard as StandardPanel;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;

class Edit
{
    private const string CMD_SAVE_QUESTION = 'sq';
    private const string CMD_RESET_PREVIEW = 'rp';
    private const string CMD_SEND_RESPONSE = 'sr';

    private const string SESSION_VAR_RESPONSE_DATA = 'response_data';

    public function __construct(
        private readonly \ilObjUser $current_user,
        private readonly \ilCtrl $ctrl,
        private readonly RequestWrapper $post_wrapper,
        private readonly UIRenderer $ui_renderer,
        private readonly UuidFactory $uuid_factory,
        private readonly ConfigurationRepository $configuration_repository,
        private readonly AttemptRepository $attempt_repository,
        private readonly RequiredCapabilities $required_capabilities,
        private readonly Question $question
    ) {

    }

    public function create(
        DefaultEnvironment $environment
    ): EditForm|Question {
        return match ($environment->getSubAction()) {
            self::CMD_SAVE_QUESTION => $this->processBasicPropertiesCreateForm(
                $environment
            ),
            default => $this->buildBasicPropertiesCreateForm($environment)
        };
    }

    public function edit(
        DefaultEnvironment $environment,
    ): EditForm|Question {
        return match ($environment->getSubAction()) {
            self::CMD_SAVE_QUESTION => $this->processBasicPropertiesEditingForm(
                $environment
            ),
            self::CMD_SEND_RESPONSE => $this->sendResponse(
                $environment
            ),
            self::CMD_RESET_PREVIEW => $this->resetPreview(
                $environment
            ),
            default => $this->buildBasicPropertiesEditingForm($environment)
                ->withContentAfterForm(
                    $this->buildPreviewPanel($environment)
                )
        };
    }

    private function buildBasicPropertiesCreateForm(
        DefaultEnvironment $environment
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
                $environment->getLanguage()->txt('edit_basic_question_properties')
            ),
            $environment
                ->withSubActionParameter(self::CMD_SAVE_QUESTION)
                ->getUrlBuilder(),
            null
        );
    }

    private function processBasicPropertiesCreateForm(
        DefaultEnvironment $environment
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
        DefaultEnvironment $environment
    ): EditForm {
        return $environment->getPresentationFactory()->getEditForm(
            $environment->getUIFactory()->input()->field()->section(
                $this->buildBasicPropertiesInputs(
                    $environment->getUIFactory()->input()->field(),
                    $environment->getLanguage()
                ),
                $environment->getLanguage()->txt('edit_basic_question_properties')
            )->withAdditionalTransformation(
                $this->buildAddBasicPropertiesToQuestionTrafo(
                    $environment->getRefinery()
                )
            )->withValue(
                $this->buildBasicPropertiesBasicValuesArray()
            ),
            $environment
                ->withSubActionParameter(self::CMD_SAVE_QUESTION)
                ->getUrlBuilder(),
            null
        )->withIsFinalStep(true);
    }

    private function processBasicPropertiesEditingForm(
        DefaultEnvironment $environment
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
            function (array $vs): Question {
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
        DefaultEnvironment $environment
    ): StandardPanel {
        $environment->preserveParametersForPageEditorCmds();

        $session_key = $this->buildPreviewSessionKey();

        $attempt_data = $this->attempt_repository->getAttemptFromPreviewData(
            $this->question,
            \ilSession::get($session_key) ?? ''
        );

        if (!\ilSession::has($session_key)) {
            \ilSession::set(
                $session_key,
                $attempt_data->toPreviewStorage()
            );
        }

        return $environment->getUIFactory()->panel()->standard(
            $environment->getLanguage()->txt('preview'),
            [
                $environment->getUIFactory()->button()->primary(
                    $environment->getLanguage()->txt('edit'),
                    $this->ctrl->getLinkTargetByClass(\QstsQuestionPageGUI::class, 'edit')
                ),
                $environment->getUIFactory()->button()->standard(
                    $environment->getLanguage()->txt('reset_preview'),
                    $environment
                        ->withSubActionParameter(self::CMD_RESET_PREVIEW)
                        ->getUrlBuilder()
                        ->buildURI()
                        ->__toString()
                ),
                ...$this->buildPreviewPanelQuestionContent(
                    $environment,
                    $attempt_data
                )
            ]
        );
    }

    private function buildPreviewPanelQuestionContent(
        DefaultEnvironment $environment,
        Attempt $attempt_data
    ): array {
        $content = [
            $environment->getUIFactory()->legacy()->content(
                $this->buildQuestionForm(
                    $environment,
                    $attempt_data
                )
            )
        ];

        if ($attempt_data->getResponseForQuestion($this->question->getId()) !== null) {
            $content[] = $environment->getUIFactory()->divider()->horizontal();
            $content[] = $environment->getUIFactory()->panel()->standard(
                $environment->getLanguage()->txt('feedback'),
                $this->question->getParticipantView(
                    $environment->getLanguage(),
                    $environment->getRefinery(),
                    $environment->getUIFactory(),
                    $this->required_capabilities,
                    $attempt_data,
                    false,
                    true,
                    true,
                    true
                )->getUI()
            );
        }

        return $content;
    }

    private function buildQuestionForm(
        DefaultEnvironment $environment,
        Attempt $attempt_data
    ): string {
        $tpl = new \ilTemplate(
            'tpl.qsts_preview_presentation_interactive.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $tpl->setVariable(
            'FORM_ACTION',
            $environment
                ->withSubActionParameter(self::CMD_SEND_RESPONSE)
                ->getUrlBuilder()
                ->buildURI()
                ->__toString()
        );

        $tpl->setVariable(
            'QUESTION_OUTPUT',
            $this->ui_renderer->render(
                $this->question->getParticipantView(
                    $environment->getLanguage(),
                    $environment->getRefinery(),
                    $environment->getUIFactory(),
                    $this->required_capabilities,
                    $attempt_data
                )->getUI()
            )
        );

        $tpl->setVariable(
            'SUBMIT_BUTTON_LABEL',
            $environment->getLanguage()->txt('send')
        );

        return $tpl->get();
    }

    private function sendResponse(
        DefaultEnvironment $environment
    ) {
        $session_key = $this->buildPreviewSessionKey();
        $attempt = $this->attempt_repository->getAttemptFromPreviewData(
            $this->question,
            \ilSession::get($session_key) ?? ''
        );

        \ilSession::set(
            $session_key,
            $attempt->withResponse(
                $this->retrieveResponse()
            )->toPreviewStorage()
        );

        return $this->buildBasicPropertiesEditingForm($environment)
            ->withContentAfterForm(
                $this->buildPreviewPanel($environment)
            );
    }

    private function resetPreview(
        DefaultEnvironment $environment
    ): EditForm {
        \ilSession::clear($this->buildPreviewSessionKey());
        return $this->buildBasicPropertiesEditingForm($environment)
            ->withContentAfterForm(
                $this->buildPreviewPanel($environment)
            );
    }

    private function retrieveResponse(): Response
    {
        $response = $this->attempt_repository->getNewResponseFor(
            $this->question->getId(),
            $this->uuid_factory->uuid4()
        );

        $response_with_data = array_reduce(
            $this->question->retrieveAnswerFormResponsesFromPost(
                $this->required_capabilities,
                $this->post_wrapper,
                $response->getId()
            ),
            fn(Response $c, AnswerFormResponse $v): Response
                => $c->withAnswerFormResponse($v),
            $response
        );

        if ($this->required_capabilities->isMarkingRequired()) {
            return $this->question->addAwardedPointsToResponse($response_with_data);
        }

        return $response_with_data;
    }

    private function buildPreviewSessionKey(): string
    {
        return self::SESSION_VAR_RESPONSE_DATA
            . "_{$this->question->getId()->toString()}";
    }
}
