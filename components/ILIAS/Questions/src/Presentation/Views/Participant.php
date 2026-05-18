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

namespace ILIAS\Questions\Presentation\Views;

use ILIAS\Questions\AnswerForm\Capabilities\Factory as CapabilitiesFactory;
use ILIAS\Questions\AnswerForm\Capabilities\RequiredCapabilities;
use ILIAS\Questions\AnswerForm\Capabilities\TextFeedback\Capability as TextFeedback;
use ILIAS\Questions\AnswerForm\Response as AnswerFormResponse;
use ILIAS\Questions\Attempt\Repository as AttemptRepository;
use ILIAS\Questions\Attempt\Response;
use ILIAS\Questions\Question\Persistence\Repository as QuestionRepository;
use ILIAS\Questions\Question\Views\Participant as QuestionParticipantView;
use ILIAS\Data\UUID\Uuid;
use ILIAS\HTTP\Services as HTTP;
use ILIAS\Language\Language;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Factory as UIFactory;

class Participant
{
    private readonly RequiredCapabilities $required_capabilities;

    private bool $shuffle_question_order = false;

    private array $attempt_cache = [];

    /**
     * @param list<string>> $capability_identifiers
     */
    public function __construct(
        private readonly Language $lng,
        private readonly Refinery $refinery,
        private readonly UIFactory $ui_factory,
        private readonly HTTP $http,
        private readonly QuestionRepository $question_repository,
        private readonly AttemptRepository $attempt_repository,
        private readonly CapabilitiesFactory $capabilities_factory,
        array $capability_identifiers,
        private readonly int $owner_object_id
    ) {
        $this->required_capabilities = $this->capabilities_factory->get(
            $capability_identifiers
        );
    }

    public function getPresentationIdentifier(): Uuid
    {
        return $this->presentation_identifier;
    }

    public function withShuffleQuestionOrder(
        bool $shuffle_question_order
    ): self {
        $clone = clone $this;
        $clone->shuffle_question_order = $shuffle_question_order;
        return $clone;
    }

    public function getQuestionView(
        Uuid $question_id,
        ?Uuid $attempt_id,
        bool $interactive,
        bool $show_marks,
        bool $show_best_response,
        bool $show_feedback
    ): QuestionParticipantView {
        $question = $this->question_repository->getForQuestionId(
            $question_id
        );

        $marking_required = $this->required_capabilities->isMarkingRequired();

        if ($attempt_id === null
            || !isset($this->attempt_cache[$attempt_id->toString()][$question_id->toString()])) {
            $attempt = $this->attempt_repository->getAttemptFor(
                $attempt_id,
                [$question]
            );
            $attempt_id = $attempt->getId();
            $this->attempt_cache[$attempt_id->toString()][$question_id->toString()] = $attempt;
        }

        return $question->getParticipantView(
            $this->lng,
            $this->refinery,
            $this->ui_factory,
            $this->required_capabilities,
            $this->attempt_cache[$attempt_id->toString()][$question_id->toString()],
            $interactive,
            $show_marks && $marking_required,
            $show_best_response && $marking_required,
            $show_feedback && $this->required_capabilities->isCapabilityRequired(
                TextFeedback::getIdentifier()
            )
        );
    }

    public function persistResponse(
        Uuid $question_id,
        Uuid $attempt_id
    ): void {
        $question = $this->question_repository->getForQuestionId(
            $question_id
        );

        $attempt_data = $this->attempt_repository->getAttemptFor(
            $attempt_id,
            [$question]
        );

        if ($attempt_data === null) {
            throw new \UnexpectedValueException(
                'The provided attempt identifier is invalid. Response cannot be persisted.'
            );
        }

        $response = $this->attempt_repository->getNewResponseFor(
            $question_id,
            $attempt_id
        );

        $response_with_values_from_post = array_reduce(
            $question->retrieveAnswerFormResponsesFromPost(
                $this->required_capabilities,
                $this->http->wrapper()->post(),
                $response->getId()
            ),
            fn(Response $c, AnswerFormResponse $v): Response
                => $c->withAnswerFormResponse($v),
            $response
        );

        if ($this->required_capabilities->isMarkingRequired()) {
            $response_with_values_from_post = $question->addAwardedPointsToResponse(
                $response_with_values_from_post
            );
        }

        $this->attempt_repository->storeResponse(
            $response_with_values_from_post
        );
    }

    public function deleteResponsesFor(
        Uuid $attempt_id,
        Uuid $question_id
    ): void {
        $this->attempt_repository->deleteResponsesFor(
            $attempt_id,
            $this->question_repository->getForQuestionId(
                $question_id
            )
        );
    }
}
