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

namespace ILIAS\Questions\AnswerForm\Capabilities\Definitions;

use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Response;
use ILIAS\Questions\AnswerForm\Properties;
use ILIAS\Questions\AnswerForm\Capabilities\Definitions\FeedbackView;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;

interface Feedback
{
    public function getParticipantOutput(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        Properties $properties,
        ?Response $response,
        RequiredCapabilities $required_capabilities
    ): ?FeedbackView;

    /**
     *
     * @return string A javascript callable that will receive an array with all
     * the inputs provided by the user as well as JSON generated from the return
     * of getAllFeedbacksForClientSide and has to return the string that will be
     * shown to the participant as feedback.
     */
    public function getFeedbackClientSideEndPoint(): string;

    /**
     *
     * @return array<string, FeedbackView> An array of all possible feedbacks
     * as FeedbackViews structured in a way that makes it possible to retrieve
     * the correct array corresponding to the answer of the user in the callback
     * provided in getClientSideFeedbackEndPoint. The component takes care of
     * transporting the information to javascript.
     */
    public function getAllFeedbacksForClientSideCode(
        Language $lng,
        Refinery $refinery,
        UIFactory $ui_factory,
        UIRenderer $ui_renderer,
        RequiredCapabilities $required_capabilities,
        Properties $properties
    ): array;
}
