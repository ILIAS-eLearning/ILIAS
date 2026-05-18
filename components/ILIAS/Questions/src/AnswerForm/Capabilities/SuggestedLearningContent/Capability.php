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

use ILIAS\Questions\AnswerForm\Capabilities\Capability as CapabilityInterface;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\ActionWithTab;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\AdditionalTabProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\Feedback;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackProvider;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackView;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\Presentation\Definitions\Environment;
use ILIAS\Questions\Presentation\Layout\Async;
use ILIAS\Questions\Presentation\Layout\Viewable;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\StaticURL\Services as StaticURLServices;
use ILIAS\UI\Factory as UIFactory;

class Capability implements CapabilityInterface, AdditionalTabProvider, FeedbackProvider, Feedback
{
    public function __construct(
        private readonly \ilCtrl $ctrl,
        private readonly \ilRbacSystem $rbac_system,
        private readonly \ilTree $tree,
        private readonly StaticURLServices $static_url,
        private readonly \ilObjUser $current_user,
        private readonly Repository $repository
    ) {
    }

    #[\Override]
    public static function getIdentifier(): string
    {
        return 'suggested_learning_content';
    }


    #[\Override]
    public function isAvailableFor(
        Properties $answer_form_properties
    ): bool {
        return true;
    }

    #[\Override]
    public function getAnswerFormEditAdditionalTab(): ActionWithTab
    {
        return new ActionWithTab(
            $this,
            'suggested_learning_content',
            $this->buildDoEditActionClosure()
        );
    }

    #[\Override]
    public function getFeedback(
        Properties $answer_form_properties
    ): ?Feedback {
        return $this;
    }

    #[\Override]
    public function onAnswerFormUpdate(
        Properties $answer_form_properties
    ): void {
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

    private function buildDoEditActionClosure(): \Closure
    {
        return function (
            Environment $environment
        ): Async|Viewable {
            $sub_action = $environment->getSubAction();
            $overview = $this->buildOverview($environment);
            if ($sub_action === '') {
                return $overview;
            }

            return $overview->doAction(
                $sub_action
            );
        };
    }

    private function buildOverview(
        Environment $environment
    ): Overview {
        return new Overview(
            $this->ctrl,
            $this->rbac_system,
            $this->tree,
            $this->current_user,
            $this->static_url,
            $environment,
            $this->repository
        );
    }
}
