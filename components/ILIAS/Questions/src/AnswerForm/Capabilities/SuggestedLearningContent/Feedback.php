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

namespace ILIAS\Questions\AnswerForm\Capabilities\SuggestedLearningContent;

use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Feedback as FeedbackInterface;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackView;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

class Feedback implements FeedbackInterface
{
    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly StaticURLServices $static_url,
        private readonly Repository $repository
    ) {
    }

    #[\Override]
    public function getParticipantOutput(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        Properties $properties,
        ?Response $response,
        RequiredCapabilities $required_capabilities
    ): ?FeedbackView {
        return $this->buildFeedbackView(
            $lng,
            $properties,
            $ui_factory
        );
    }

    #[\Override]
    public function getFeedbackClientSideEndPoint(): string
    {
        return 'il.questions.suggestedLearningContent.retrieve';
    }

    #[\Override]
    public function getAllFeedbacksForClientSideCode(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        RequiredCapabilities $required_capabilities,
        Properties $properties
    ): array {
        $feedback_view = $this->buildFeedbackView(
            $lng,
            $properties,
            $ui_factory
        );
        return $feedback_view === null
            ? []
            : [
                $ui_renderer->render(
                    $feedback_view->getUI()
                )
            ];
    }

    private function buildFeedbackView(
        Language $lng,
        Properties $properties,
        UIFactory $ui_factory
    ): ?FeedbackView {
        $content = $this->repository->getFor(
            $properties->getAnswerFormId()
        )->getContentForPresentation(
            $lng,
            $this->ctrl,
            $this->static_url,
            $ui_factory
        );
        if ($content === null) {
            return null;
        }

        return new FeedbackView(
            $ui_factory->panel()->standard(
                $lng->txt('suggested_learning_content'),
                $content
            )
        );
    }
}
