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

namespace ILIAS\Questions\AnswerForm\Capabilities\AsyncView;

use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackProvider;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Capabilities\TypeSpecification;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerForm\Views\Participant;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\ViewConfiguration;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Component;
use ILIAS\UICore\GlobalTemplate;

abstract class AsyncView implements TypeSpecification, Participant
{
    private const string KEY_BEST_RESPONSE = 'best_response';
    private const string KEY_BEST_RESPONSE_OUTPUT = 'best_response_output';

    private const string JAVASCRIPT_BASE_FILE = 'assets/js/questions.js';

    private const string TEMPLATE_VARIABLE_ANSWER_FORM = 'ANSWER_FORM';
    private const string TEMPLATE_VARIABLE_CHECK_BUTTON = 'CHECK_BUTTON';

    private const string PLACEHOLDER_PANEL_TITLE = '-panel-title-to-replace-';
    private const string PLACEHOLDER_PANEL_CONTENT = '-panel-content-to-replace-';

    public function __construct(
        protected readonly UIRenderer $ui_renderer
    ) {
    }

    abstract protected function getJavascriptFiles(): array;

    abstract protected function getAnswerFormPresentation(
        Language $lng,
        UIFactory $ui_factory,
        ViewConfiguration $view_configuration,
        AnswerFormProperties $answer_form_properties,
        ?AdditionalAttemptData $attempt_data,
        ?Response $response_data
    ): string;

    #[\Override]
    final public static function getCapabilityIdentifier(): string
    {
        return Capability::getIdentifier();
    }

    #[\Override]
    final public function show(
        GlobalTemplate $global_tpl,
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        RequiredCapabilities $required_capabilities,
        ViewConfiguration $view_configuration,
        AnswerFormProperties $answer_form_properties,
        ?AdditionalAttemptData $attempt_data,
        ?Response $response_data
    ): Component {
        $tpl = new \ilTemplate(
            'tpl.qsts_async.html',
            true,
            true,
            'components/ILIAS/Questions'
        );

        $tpl->setVariable(
            self::TEMPLATE_VARIABLE_ANSWER_FORM,
            $this->getAnswerFormPresentation(
                $lng,
                $ui_factory,
                $view_configuration,
                $answer_form_properties,
                $attempt_data,
                $response_data
            )
        );

        if (!$view_configuration->isInteractive()) {
            return $ui_factory->legacy()->content($tpl->get());
        }

        $global_tpl->addJavaScript(self::JAVASCRIPT_BASE_FILE);

        foreach ($this->getJavascriptFiles() as $file) {
            $global_tpl->addJavaScript($file);
        }

        $feedback_array = $this->buildFeedbackArray(
            $lng,
            $refinery,
            $ui_factory,
            $required_capabilities,
            $view_configuration,
            $answer_form_properties,
            $attempt_data
        );

        $tpl->setVariable(
            self::TEMPLATE_VARIABLE_CHECK_BUTTON,
            $this->ui_renderer->render(
                $ui_factory->button()->standard(
                    $lng->txt('check'),
                    ''
                )->withAdditionalOnLoadCode(
                    fn(string $id) => "document.querySelector(`#{$id}`).addEventListener("
                . '"click", (e) => il.questions.asyncView.showFeedback(e, "'
                . base64_encode(
                    $this->buildFeedbackPanelWithPlaceholders($ui_factory)
                ) . '", "' . base64_encode(json_encode($feedback_array['feedback_data'])) . '", "'
                . ($feedback_array[self::KEY_BEST_RESPONSE] === null
                    ? ''
                    : base64_encode(json_encode($feedback_array[self::KEY_BEST_RESPONSE]))) . '", '
                . (array_key_exists(self::KEY_BEST_RESPONSE_OUTPUT, $feedback_array)
                    ? '"' . base64_encode($feedback_array[self::KEY_BEST_RESPONSE_OUTPUT]) . '"'
                    : 'undefined')
                . ', '
                . implode(', ', $feedback_array['feedback_callbacks'])
                . '));'
                )
            )
        );

        return $ui_factory->legacy()->content($tpl->get());
    }

    private function buildFeedbackArray(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        RequiredCapabilities $required_capabilities,
        ViewConfiguration $view_configuration,
        AnswerFormProperties $answer_form_properties,
        ?AdditionalAttemptData $attempt_data,
    ): array {
        $best_response = $required_capabilities
            ->getMarking($answer_form_properties)
            ->getBestResponse($answer_form_properties);
        $feedbacks = [
            self::KEY_BEST_RESPONSE => $best_response->toClientSideRepresentation()
        ];

        if ($view_configuration->showBestResponse()) {
            $feedbacks[self::KEY_BEST_RESPONSE_OUTPUT] = $this->ui_renderer->render(
                $ui_factory->panel()->standard(
                    $lng->txt('best_response'),
                    $ui_factory->legacy()->content(
                        $this->getAnswerFormPresentation(
                            $lng,
                            $ui_factory,
                            $view_configuration->withShowBestResponse(true),
                            $answer_form_properties,
                            $attempt_data,
                            $best_response
                        )
                    )
                )
            );
        }

        if (!$view_configuration->showFeedback()) {
            return $feedbacks;
        }

        return $this->addFeedbacksFromProvidersToFeedbacks(
            $lng,
            $refinery,
            $ui_factory,
            $required_capabilities,
            $answer_form_properties,
            $feedbacks
        );
    }

    private function addFeedbacksFromProvidersToFeedbacks(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        RequiredCapabilities $required_capabilities,
        AnswerFormProperties $answer_form_properties,
        array $feedbacks
    ): array {
        $feedbacks['feedback_callbacks'] = [];
        $feedbacks['feedback_data'] = [];

        return array_reduce(
            $required_capabilities->getRequiredFeedbackProviders(),
            function (
                array $c,
                FeedbackProvider $v
            ) use (
                $lng,
                $refinery,
                $ui_factory,
                $required_capabilities,
                $answer_form_properties
            ): array {
                $feedback = $v->getFeedback($answer_form_properties);
                $feedback_data = $feedback->getAllFeedbacksForClientSideCode(
                    $lng,
                    $refinery,
                    $ui_factory,
                    $this->ui_renderer,
                    $required_capabilities,
                    $answer_form_properties
                );

                if ($feedback_data !== []) {
                    $c['feedback_callbacks'][] = $feedback->getFeedbackClientSideEndPoint();
                    $c['feedback_data'][] = $feedback_data;
                }

                return $c;
            },
            $feedbacks
        );
    }

    private function buildFeedbackPanelWithPlaceholders(
        UIFactory $ui_factory
    ): string {
        return $this->ui_renderer->render(
            $ui_factory->panel()->standard(
                self::PLACEHOLDER_PANEL_TITLE,
                $ui_factory->legacy()->content(
                    self::PLACEHOLDER_PANEL_CONTENT
                )
            )
        );
    }
}
