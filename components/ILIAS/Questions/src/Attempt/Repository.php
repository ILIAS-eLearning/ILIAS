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

namespace ILIAS\Questions\Attempt;

use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Question\Persistence\Repository as QuestionRepository;
use ILIAS\Questions\Question\Question;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Random\Seed\RandomSeed;

class Repository
{
    private readonly TableNameBuilder $table_names_builder;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly UuidFactory $uuid_factory,
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableDefinitions $table_definitions
    ) {
        $this->table_names_builder = new TableNameBuilder(
            QuestionRepository::COMPONENT_NAMESPACE,
            null
        );
    }

    /**
     * @param array<Question> $questions
     * @throws InvalidArgumentException
     */
    public function getAttemptFor(
        ?Uuid $attempt_id,
        array $questions
    ): Attempt {
        if ($attempt_id === null) {
            $attempt = $this->getNewAttempt($questions);
            $this->storeAttempt($attempt);
            return $attempt;
        }

        $base_table_id_column = $this->table_definitions->getIdColumn(
            $this->table_names_builder,
            TableTypes::Responses
        );

        $database_values = array_reduce(
            $questions,
            fn(Query $c, Question $v): Query => $v->completeResponseQuery(
                $c,
                $base_table_id_column
            ),
            $this->buildQuery($attempt_id)
        )->getRecords()
        ->current();

        if ($database_values === null) {
            throw new \InvalidArgumentException('No Attempt With Given Identifier');
        }

        return array_reduce(
            $questions,
            fn(Attempt $c, Question $v): Attempt => $c->withResponse(
                $this->retrieveCurrentResponseFromQuery(
                    $v,
                    $c->getId(),
                    $database_values
                )
            ),
            $this->retrieveAttemptFromQuery(
                $attempt_id,
                $database_values,
                $this->retriveAdditionalDataFromQuery(
                    $database_values
                )
            )
        );
    }

    public function getNewResponseFor(
        Uuid $question_id,
        Uuid $attempt_id
    ): Response {
        return new Response(
            $this->uuid_factory->uuid4(),
            $question_id,
            $attempt_id,
            new \DateTimeImmutable('@' . time())
        );
    }

    public function storeResponse(
        Response $response
    ): void {
        $response->toStorage(
            $this->persistence_factory,
            $this->table_definitions,
            $this->table_names_builder,
            ManipulationType::Create,
            new Manipulate(
                $this->db,
                QuestionRepository::COMPONENT_NAMESPACE
            )
        )->run();
    }

    public function deleteResponsesFor(
        Uuid $attempt_id,
        Question $question
    ): void {
        $manipulate = new Manipulate(
            $this->db,
            QuestionRepository::COMPONENT_NAMESPACE
        );
        foreach ($this->getAllResponsesFor($attempt_id, $question) as $response) {
            $manipulate = $response->toDelete(
                $this->persistence_factory,
                $this->table_definitions,
                $this->table_names_builder,
                $manipulate
            );
        }

        $manipulate->run();
    }

    public function getAttemptFromPreviewData(
        Question $question,
        string $preview_data
    ): ?Attempt {
        $data_array = json_decode($preview_data, true);

        if (!is_array($data_array) || $data_array === []) {
            return $this->getNewAttempt([$question]);
        }

        $response_id = $this->uuid_factory->uuid4();

        return array_reduce(
            $data_array[Attempt::KEY_RESPONSES] ?? [],
            fn(Attempt $c, array $v): Attempt => $c->withResponse(
                new Response(
                    $response_id,
                    $question->getId(),
                    $this->uuid_factory->uuid4(),
                    new \DateTimeImmutable(
                        'now',
                        new \DateTimeZone('UTC')
                    ),
                    $v[Response::KEY_POINTS] ?? 0.0,
                    $question->retrieveAnswerFormResponsesFromPreviewData(
                        $response_id,
                        $v[Response::KEY_RESPONSES] ?? []
                    )
                )
            ),
            new Attempt(
                $this->uuid_factory->uuid4(),
                0,
                $data_array[Attempt::KEY_ADDITONAL_DATA] ?? []
            )
        );
    }

    private function storeAttempt(
        Attempt $attempt
    ): void {
        $manipulate = (new Manipulate(
            $this->db,
            QuestionRepository::COMPONENT_NAMESPACE
        ))->withAdditionalStatement(
            $attempt->basicDataToStorage(
                $this->persistence_factory,
                $this->table_definitions,
                $this->table_names_builder
            )
        );

        $additional_data_statement = $attempt->additionalDataToStorage(
            $this->persistence_factory,
            $this->table_definitions,
            $this->table_names_builder
        );

        if ($additional_data_statement !== null) {
            $manipulate = $manipulate->withAdditionalStatement(
                $additional_data_statement
            );
        }

        $manipulate->run();
    }

    /**
     * @param array<Question> $questions
     */
    private function getNewAttempt(
        array $questions
    ): Attempt {
        $attempt = new Attempt(
            $this->uuid_factory->uuid4(),
            (new RandomSeed())->createSeed()
        );

        return array_reduce(
            $questions,
            fn(Attempt $c, Question $v) => $v->initializeAttemptData($c),
            $attempt
        );
    }

    private function getAllResponsesFor(
        Uuid $attempt_id,
        Question $question
    ): \Generator {
        $base_table_id_column = $this->table_definitions->getIdColumn(
            $this->table_names_builder,
            TableTypes::Responses
        );

        $database_values = $question->completeResponseQuery(
            $this->buildQuery($attempt_id),
            $base_table_id_column
        )->getRecords()
        ->current();

        if ($database_values === null) {
            throw new \InvalidArgumentException('No Attempt With Given Identifier');
        }

        return $this->retrieveAllResponsesFromQuery(
            $question,
            $attempt_id,
            $database_values
        );
    }

    private function buildQuery(
        Uuid $attempt_id
    ): Query {
        $attempt_data_id_column = $this->table_definitions->getIdColumn(
            $this->table_names_builder,
            TableTypes::AttemptData
        );
        return $this->table_definitions->completeAttemptQuery(
            new Query(
                $this->db,
                $this->refinery,
                QuestionRepository::COMPONENT_NAMESPACE,
                $this->persistence_factory->table(
                    $this->table_names_builder,
                    TableTypes::AttemptData
                )
            ),
            $attempt_data_id_column
        )->withAdditionalWhere(
            $this->persistence_factory->where(
                $attempt_data_id_column,
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    $attempt_id->toString()
                )
            )
        )->withGroupBy(
            $attempt_data_id_column
        );
    }

    private function retriveAdditionalDataFromQuery(
        Query $query
    ): array {
        return $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->table_names_builder,
                TableTypes::AdditionalAttemptData
            ),
            $this->refinery->custom()->transformation(
                function (array $vs): array {
                    return array_reduce(
                        $vs,
                        function (array $c, array $v): array {
                            $c[$v['parent_id']] = $v['data'];
                            return $c;
                        },
                        []
                    );
                }
            )
        );
    }

    private function retrieveAttemptFromQuery(
        Uuid $attempt_id,
        Query $query,
        array $additional_seeds
    ): Attempt {
        return $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->table_names_builder,
                TableTypes::AttemptData
            ),
            $this->refinery->custom()->transformation(
                fn(array $vs): Attempt => new Attempt(
                    $attempt_id,
                    $vs[0]['shuffler_seed'],
                    $additional_seeds
                )
            )
        );
    }

    private function retrieveCurrentResponseFromQuery(
        Question $question,
        Uuid $attempt_id,
        Query $query
    ): Response {
        return $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->table_names_builder,
                TableTypes::Responses
            ),
            $this->refinery->custom()->transformation(
                function (
                    array $vs
                ) use (
                    $question,
                    $attempt_id,
                    $query
                ): Response {
                    $question_id_string = $question->getId()->toString();

                    $last_record = array_reduce(
                        $vs,
                        fn(array $c, array $v): array
                            => $v['question_id'] === $question_id_string ? $v : $c,
                        []
                    );

                    if ($last_record === []) {
                        return $this->getNewResponseFor(
                            $question->getId(),
                            $attempt_id
                        );
                    }

                    $response_id = $this->uuid_factory
                        ->fromString($last_record['id']);

                    return new Response(
                        $response_id,
                        $question->getId(),
                        $attempt_id,
                        new \DateTimeImmutable("@{$last_record['create_timestamp']}"),
                        $last_record['awarded_points'],
                        $question->retrieveAnswerFormResponsesFromQuery(
                            $response_id,
                            $query
                        )
                    );
                }
            )
        );
    }

    private function retrieveAllResponsesFromQuery(
        Question $question,
        Uuid $attempt_id,
        Query $query
    ): \Generator {
        return $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->table_names_builder,
                TableTypes::Responses
            ),
            $this->refinery->custom()->transformation(
                function (
                    array $vs
                ) use (
                    $question,
                    $attempt_id,
                    $query
                ): \Generator {
                    if ($vs === []) {
                        return;
                    }

                    foreach ($vs as $v) {
                        $response_id = $this->uuid_factory
                            ->fromString($v['id']);

                        yield new Response(
                            $response_id,
                            $question->getId(),
                            $attempt_id,
                            new \DateTimeImmutable("@{$v['create_timestamp']}"),
                            $v['awarded_points'],
                            $question->retrieveAnswerFormResponsesFromQuery(
                                $response_id,
                                $query
                            )
                        );
                    }
                }
            )
        );
    }
}
