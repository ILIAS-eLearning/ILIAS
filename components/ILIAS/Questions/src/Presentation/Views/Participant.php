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
use ILIAS\Questions\AnswerForm\Capabilities\Marking\Marking;
use ILIAS\Questions\Attempt\Repository as AttemptRepository;
use ILIAS\Questions\Question\Persistence\Repository as QuestionRepository;
use ILIAS\Questions\Question\Views\Participant as QuestionParticipantView;
use ILIAS\Data\UUID\Uuid;
use ILIAS\UI\Factory as UIFactory;

class Participant
{
    private array $required_capabilities = [];

    private bool $shuffle_question_order = false;

    public function __construct(
        private readonly UIFactory $ui_factory,
        private readonly CapabilitiesFactory $capabilities_factory,
        private readonly QuestionRepository $question_repository,
        private readonly AttemptRepository $attempt_repository,
        private readonly int $owner_object_id
    ) {

    }

    public function getPresentationIdentifier(): Uuid
    {
        return $this->presentation_identifier;
    }

    public function withRequiredCapabilities(
        array $capability_class_names
    ): self {
        $clone = clone $this;
        $clone->required_capabilities = array_reduce(
            $capability_class_names,
            function (array $c, string $v): array {
                $c[$v] = $this->capabilities_factory->get($v);

                if ($c[$v] === null) {
                    throw new \InvalidArgumentException(
                        "The capability {$v} does not exist."
                    );
                }

                return $c;
            },
            []
        );
        return $clone;
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
        ?Uuid $attempt_id = null,
        bool $interactive = true,
        bool $show_marks = false,
        bool $show_correct_solution = false
    ): QuestionParticipantView {
        $question = $this->question_repository->getForQuestionId(
            $question_id
        );

        return $question->getParticipantView(
            $this->ui_factory,
            $this->required_capabilities,
            $this->attempt_repository->getAttemptFor(
                $attempt_id,
                [$question]
            ),
            $interactive,
            $show_marks && in_array(Marking::class, $this->required_capabilities),
            $show_correct_solution && in_array(Marking::class, $this->required_capabilities)
        );
    }

    public function persistResponse(
        Uuid $question_id,
        Uuid $attempt_id
    ): Uuid {
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

        $current_reponse = $attempt_data->getResponseFor($question_id);
        if ($current_reponse === null) {
            $current_response = $this->attempt_repository->getNewResponseFor(
                $question_id,
                $attempt_id
            );
        }

        foreach ($this->question->getAnswerFormProperties() as $property) {
            $property->getDefinition()->getParticipantView()->retrieveResponse();
        }
    }
}
