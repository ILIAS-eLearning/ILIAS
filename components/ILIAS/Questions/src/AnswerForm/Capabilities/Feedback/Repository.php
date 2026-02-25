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

namespace ILIAS\Questions\AnswerForm\Capabilities\Feedback;

use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Questions\Question\Persistence\Repository as QuestionRepository;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\Order as DataOrder;
use ILIAS\Data\Range as DataRange;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Refinery\Factory as Refinery;

class Repository
{
    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly UuidFactory $uuid_factory,
        private readonly Factory $persistence_factory,
        private readonly AnswerFormFactory $answer_form_factory
    ) {
    }

    /**
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getFor(
        Uuid $answer_form_id
    ): \Generator {
        foreach ($query = new Query(
            $this->db,
            $this->refinery,
            QuestionRepository::COMPONENT_NAMESPACE
        )->loadNextRecord() as $query_with_record) {
            yield $this->retrieveQuestionFromQuery(
                $query_with_record,
                []
            );
        }
    }

    /**
     * @param array<\ILIAS\Questions\Question\QuestionImplementation> $questions
     */
    public function create(
        array $questions
    ): void {
        $this->store(
            array_map(
                fn(QuestionImplementation $v): QuestionImplementation => $v
                    ->withPageId($this->buildQuestionPage($v->getParentObjId())),
                $questions
            ),
            new Manipulate(
                $this->db,
                $this->answer_form_factory,
                ManipulationType::Create
            )
        );
    }

    /**
     * @param array<\ILIAS\Questions\Question\QuestionImplementation> $questions
     */
    public function update(
        array $questions
    ): void {
        $this->store(
            $questions,
            new Manipulate(
                $this->db,
                $this->answer_form_factory,
                ManipulationType::Update
            )
        );
    }

    public function delete(
        array $questions
    ): void {
        array_reduce(
            $questions,
            fn(Manipulate $c, QuestionImplementation $v): Manipulate => $v->toDelete($c),
            new Manipulate(
                $this->db,
                $this->answer_form_factory,
                ManipulationType::Delete
            )
        )->run();

        foreach ($questions as $question) {
            (new \QstsQuestionPage($question->getPageId()))->delete();
        }
    }
}
