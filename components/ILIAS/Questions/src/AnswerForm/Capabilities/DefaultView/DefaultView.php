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

namespace ILIAS\Questions\AnswerForm\Capabilities\DefaultView;

use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackProvider;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Capabilities\TypeSpecification;
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\AnswerForm\Response as AnswerFormResponse;
use ILIAS\Questions\AnswerForm\Views\Participant;
use ILIAS\Questions\Attempt\AdditionalAttemptData;
use ILIAS\Questions\Presentation\Definitions\ViewConfiguration;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UICore\GlobalTemplate;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory as UIFactory;

abstract class DefaultView implements TypeSpecification, Participant
{
    private const string JAVASCRIPT_BASE_FILE = 'assets/js/questions.js';

    abstract protected function getJavascriptFiles(): array;

    abstract protected function getAnswerFormPresentation(
        Language $lng,
        UIFactory $ui_factory,
        ViewConfiguration $view_configuration,
        AnswerFormProperties $properties,
        ?AdditionalAttemptData $attempt_data,
        ?AnswerFormResponse $response_data
    ): array|Component;

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
        AnswerFormProperties $properties,
        ?AdditionalAttemptData $attempt_data,
        ?AnswerFormResponse $response_data
    ): array|Component {
        $main_content = $this->buildMainContent(
            $global_tpl,
            $lng,
            $ui_factory,
            $required_capabilities,
            $view_configuration,
            $properties,
            $attempt_data,
            $response_data
        );

        if (!$view_configuration->showFeedback()) {
            return $main_content;
        }

        return $this->addFeedbackSubPanelsToContent(
            $lng,
            $refinery,
            $ui_factory,
            $required_capabilities,
            $properties,
            $response_data,
            [
                $ui_factory->panel()->standard(
                    $this->buildMainContentLabel(
                        $lng,
                        $view_configuration->showBestResponse()
                    ),
                    $main_content
                )
            ]
        );
    }

    private function buildMainContent(
        GlobalTemplate $global_tpl,
        Language $lng,
        UIFactory $ui_factory,
        RequiredCapabilities $required_capabilities,
        ViewConfiguration $view_configuration,
        ?AnswerFormProperties $answer_form_properties,
        ?AdditionalAttemptData $additional_attempt_data,
        ?AnswerFormResponse $answer_form_response
    ): array|Component {
        if ($view_configuration->showBestResponse()) {
            $best_response = $required_capabilities->getMarking(
                $answer_form_properties
            )?->getBestResponse(
                $answer_form_properties
            );

            return $this->getAnswerFormPresentation(
                $lng,
                $ui_factory,
                $view_configuration->withShowBestResponse(true),
                $answer_form_properties,
                $additional_attempt_data,
                $best_response
            );
        }

        $global_tpl->addJavaScript(self::JAVASCRIPT_BASE_FILE);

        foreach ($this->getJavascriptFiles() as $file) {
            $global_tpl->addJavaScript($file);
        }

        return $this->getAnswerFormPresentation(
            $lng,
            $ui_factory,
            $view_configuration,
            $answer_form_properties,
            $additional_attempt_data,
            $answer_form_response
        );
    }

    private function addFeedbackSubPanelsToContent(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        RequiredCapabilities $required_capabilities,
        AnswerFormProperties $answer_form_properties,
        AnswerFormResponse $answer_form_response,
        array $content
    ): array {
        return array_reduce(
            $required_capabilities->getRequiredFeedbackProviders(),
            function (
                array $c,
                FeedbackProvider $v
            ) use (
                $lng,
                $refinery,
                $ui_factory,
                $answer_form_properties,
                $answer_form_response,
                $required_capabilities
            ): array {
                $output = $v->getFeedback(
                    $answer_form_properties
                )->getParticipantOutput(
                    $lng,
                    $refinery,
                    $ui_factory,
                    $answer_form_properties,
                    $answer_form_response,
                    $required_capabilities
                );

                if ($output === null) {
                    return $c;
                }

                $c[] = $output->getUI();
                return $c;
            },
            $content
        );
    }

    private function buildMainContentLabel(
        Language $lng,
        bool $show_best_response
    ): string {
        if ($show_best_response) {
            return $lng->txt('best_response');
        }

        return $lng->txt('question');
    }
}
