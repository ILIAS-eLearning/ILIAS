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

namespace ILIAS\Questions\Question\Persistence;

use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Definition as AnswerFormDefinition;
use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableDefinitions;
use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableTypes;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\Question\Question;
use ILIAS\Questions\Persistence\Column;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Operator;
use ILIAS\Questions\Persistence\Query;
use ILIAS\Questions\Persistence\Manipulate;
use ILIAS\Questions\Persistence\ManipulationType;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\Order as DataOrder;
use ILIAS\Data\Range as DataRange;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Database\FieldDefinition;
use ILIAS\Refinery\Factory as Refinery;

class Repository
{
    public const string COMPONENT_NAMESPACE = 'qsts';

    private readonly TableNameBuilder $question_table_names_builder;

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly UuidFactory $uuid_factory,
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableDefinitions $question_table_definitions,
        private readonly AnswerFormGenericTableDefinitions $answer_form_generic_table_definitions,
        private readonly AnswerFormFactory $answer_form_factory
    ) {
        $this->question_table_names_builder = new TableNameBuilder(
            self::COMPONENT_NAMESPACE,
            null
        );
    }

    public function getNew(
        int $parent_obj_id
    ): Question {
        return new Question(
            $this->buildAvailableUuid(),
            $parent_obj_id
        );
    }

    /**
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getQuestionDataOnlyForAllQuestions(): \Generator
    {
        foreach ($this->buildQuestionsQuery()->withGroupBy(
            $this->buildGroupByColumn()
        )->loadNextRecord() as $query_with_record) {
            yield $this->retrieveQuestionFromQuery(
                $query_with_record,
                []
            );
        }
    }

    /**
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getQuestionDataOnlyForQuestionIds(
        array $question_ids
    ): \Generator {
        foreach ($this->buildQuestionsQuery()->withAdditionalWhere(
            $this->persistence_factory->where(
                $this->question_table_definitions->getIdColumn(
                    $this->question_table_names_builder,
                    TableTypes::Questions
                ),
                $this->persistence_factory->value(
                    FieldDefinition::T_TEXT,
                    array_map(
                        fn(Uuid $v): string => $v->toString(),
                        $question_ids
                    )
                ),
                Operator::In
            )
        )->withGroupBy(
            $this->buildGroupByColumn()
        )->loadNextRecord() as $query_with_record) {
            yield $this->retrieveQuestionFromQuery(
                $query_with_record,
                []
            );
        }
    }

    public function getForQuestionId(
        Uuid $question_id
    ): ?Question {
        return $this->getForBaseQuery(
            $this->buildQuestionsQuery()->withAdditionalWhere(
                $this->persistence_factory->where(
                    $this->question_table_definitions->getIdColumn(
                        $this->question_table_names_builder,
                        TableTypes::Questions
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        $question_id->toString()
                    ),
                    Operator::Equal
                )
            ),
            [$question_id]
        )->current();
    }

    /**
     *
     * @param list<\ILIAS\Data\Uuid> $question_ids
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getForQuestionIds(
        array $question_ids
    ): \Generator {
        yield from $this->getForBaseQuery(
            $this->buildQuestionsQuery()->withAdditionalWhere(
                $this->persistence_factory->where(
                    $this->question_table_definitions->getIdColumn(
                        $this->question_table_names_builder,
                        TableTypes::Questions
                    ),
                    $this->persistence_factory->value(
                        FieldDefinition::T_TEXT,
                        array_map(
                            fn(Uuid $v): string => $v->toString(),
                            $question_ids
                        )
                    ),
                    Operator::In
                )
            ),
            $question_ids
        );
    }

    /**
     * @param array<\ILIAS\Questions\Question\QuestionImplementation> $questions
     */
    public function create(
        array $questions
    ): void {
        $this->store(
            array_map(
                fn(Question $v): Question => $v
                    ->withPageId($this->buildQuestionPage($v->getParentObjId())),
                $questions
            ),
            $this->buildManipulate(
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
            $this->buildManipulate(
                ManipulationType::Update
            )
        );
    }

    public function delete(
        array $questions
    ): void {
        array_reduce(
            $questions,
            fn(Manipulate $c, Question $v): Manipulate
                => $v->toDelete(
                    $c,
                    $this->persistence_factory,
                    $this->question_table_definitions
                ),
            $this->buildManipulate(
                ManipulationType::Delete
            )
        )->run();

        foreach ($questions as $question) {
            (new \QstsQuestionPage($question->getPageId()))->delete();
        }
    }

    /**
     * @param  array<\ILIAS\Data\Uuid> $question_ids
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    private function getForBaseQuery(
        Query $query,
        array $question_ids
    ): \Generator {
        $query_with_answer_forms = array_reduce(
            $this->getAnswerFormTypesForQuestionIds($question_ids),
            fn(Query $c, AnswerFormDefinition $v) => $v->getTableDefinitions()->completeQuery(
                $c,
                $this->answer_form_generic_table_definitions->getIdColumn(
                    $this->question_table_names_builder,
                    AnswerFormGenericTableTypes::AnswerForms
                )
            ),
            $query
        );

        foreach ($query_with_answer_forms->withGroupBy(
            $this->buildGroupByColumn()
        )->loadNextRecord() as $query_with_record) {
            yield $this->retrieveQuestionFromQuery(
                $query_with_record,
                $this->retrieveAnswerFormsFromQuery($query_with_record)
            );
        }
    }

    /**
     * $param array<\ILIAS\Questions\AnswerForms\Properties> $answer_forms
     */
    private function retrieveQuestionFromQuery(
        Query $query,
        array $answer_forms
    ): Question {
        $linking_info = $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->question_table_names_builder,
                TableTypes::Linking
            ),
            $this->refinery->identity()
        );

        $question = $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->question_table_names_builder,
                TableTypes::Questions,
            ),
            $this->refinery->custom()->transformation(
                fn(array $vs): Question => new Question(
                    $this->uuid_factory->fromString($vs[0]['id']),
                    $linking_info[0]['obj_id'],
                    $linking_info[0]['position'],
                    $vs[0]['page_id'],
                    $vs[0]['title'],
                    $vs[0]['author'],
                    Lifecycle::from($vs[0]['lifecycle']),
                    $vs[0]['remarks'],
                    $vs[0]['original_id'] === null
                        ? null
                        : $this->uuid_factory->fromString($vs[0]['original_id']),
                    new \DateTimeImmutable('@' . $vs[0]['last_update'], new \DateTimeZone('UTC')),
                    new \DateTimeImmutable('@' . $vs[0]['created'], new \DateTimeZone('UTC')),
                    $answer_forms
                )
            )
        );

        if ($answer_forms === [] || $question->getPageId() !== 0) {
            return $question;
        }

        return $this->migrateQuestionPage($question);
    }

    /**
     * @return array<\ILIAS\Questions\AnswerForms\Properties>
     */
    private function retrieveAnswerFormsFromQuery(
        Query $query
    ): array {
        return $query->retrieveCurrentRecord(
            $this->persistence_factory->table(
                $this->question_table_names_builder,
                AnswerFormGenericTableTypes::AnswerForms
            ),
            $this->refinery->custom()->transformation(
                function (array $vs) use ($query): array {
                    if (count($vs) === 1 && $vs[0]['type'] === null) {
                        return [];
                    }

                    $answer_forms = [];
                    $previous_answer_form_id = null;
                    foreach ($vs as $data_set) {
                        if ($data_set['id'] === $previous_answer_form_id) {
                            continue;
                        }
                        $previous_answer_form_id = $data_set['id'];
                        $definition = $this->answer_form_factory
                            ->getDefinitionForClass($data_set['type']);
                        $answer_forms[] = $definition->buildProperties(
                            $this->answer_form_factory->buildTypeGenericPropertiesFromDatabase($data_set),
                            $query
                        );
                    }
                    return $answer_forms;
                }
            )
        );
    }

    /**
     * @param  array<\ILIAS\Data\Uuid> $question_ids
     * @return array<\ILIAS\Questions\AnswerForm\Definition>
     */
    private function getAnswerFormTypesForQuestionIds(
        array $question_ids
    ): array {
        $table_name = $this->question_table_names_builder
            ->getTableNameFor(AnswerFormGenericTableTypes::AnswerForms);

        $query = $this->db->query(
            "SELECT DISTINCT type FROM {$table_name}" . PHP_EOL
                . "WHERE {$this->db->in(
                    'question_id',
                    $question_ids,
                    false,
                    FieldDefinition::T_TEXT
                )}"
        );
        $answer_form_types = [];
        while (($type_class = $this->db->fetchObject($query)?->type) !== null) {
            $answer_form_types[] = $this->answer_form_factory->getDefinitionForClass($type_class);
        }
        return $answer_form_types;
    }

    /**
     * @param array<\ILIAS\Questions\Question\QuestionImplementation> $questions
     */
    private function store(
        array $questions,
        Manipulate $manipulate
    ): void {
        array_reduce(
            $questions,
            fn(Manipulate $c, Question $v): Manipulate => $v->toStorage(
                $c,
                $this->persistence_factory,
                $this->question_table_definitions,
                $this->answer_form_generic_table_definitions
            ),
            $manipulate
        )->run();
    }

    private function buildQuestionsQuery(): Query
    {
        return $this->answer_form_generic_table_definitions->completeQuery(
            $this->question_table_definitions->completeQuery(
                new Query(
                    $this->db,
                    $this->refinery,
                    self::COMPONENT_NAMESPACE,
                    $this->persistence_factory->table(
                        $this->question_table_names_builder,
                        TableTypes::Linking
                    )
                ),
                null
            ),
            $this->question_table_definitions->getIdColumn(
                $this->question_table_names_builder,
                TableTypes::Questions
            )
        );
    }

    private function buildManipulate(
        ManipulationType $manipulation_type
    ): Manipulate {
        return new Manipulate(
            $this->db,
            $manipulation_type,
            self::COMPONENT_NAMESPACE
        );
    }

    private function buildGroupByColumn(): Column
    {
        return $this->question_table_definitions->getIdColumn(
            $this->question_table_names_builder,
            TableTypes::Questions
        );
    }

    private function buildAvailableUuid(): Uuid
    {
        do {
            $uuid = $this->uuid_factory->uuid4();
            if ($this->checkAvailabilityOfId($uuid)) {
                return $uuid;
            }
        } while (true);
    }

    private function checkAvailabilityOfId(
        Uuid $uuid
    ): bool {
        $table_name = $this->question_table_names_builder
            ->getTableNameFor(TableTypes::Questions);

        return $this->db->fetchObject(
            $this->db->query(
                "SELECT COUNT(*) as cnt FROM {$table_name}" . PHP_EOL
                . "WHERE id='{$uuid->toString()}'"
            )
        )->cnt === 0;
    }

    private function buildQuestionPage(
        int $parent_obj_id
    ): int {
        $page = new \QstsQuestionPage();
        $page->setId($this->getNextAvailableQuestionPageId());
        $page->setParentId($parent_obj_id);
        $page->createFromXML();
        return $page->getId();
    }

    private function getNextAvailableQuestionPageId(): int
    {

        $last_id = $this->db->fetchObject(
            $this->db->query(
                'SELECT MAX(page_id) AS last FROM page_object '
                . 'WHERE parent_type = "qsts"'
            )
        )->last;
        if ($last_id === null) {
            return 1;
        }

        return $last_id + 1;
    }

    private function migrateQuestionPage(
        Question $question
    ): Question {
        $table_name = $this->question_table_names_builder
            ->getTableNameFor(TableTypes::MigrationsTable);

        $old_page_id = $this->db->fetchObject(
            $this->db->query(
                "SELECT old_question_id FROM {$table_name}" . PHP_EOL
                . "WHERE new_question_id = {$this->db->quote($question->getId(), FieldDefinition::T_TEXT)}"
            )
        )->old_question_id;

        $new_page_id = $this->getNextAvailableQuestionPageId();
        $old_qsts_page = new \ilAssQuestionPage($old_page_id);
        $old_qsts_page->setQuestion($question);
        $old_qsts_page->copyToAnswerForm($new_page_id, $question);

        $new_question = $question->withPageId($new_page_id);

        $this->update([$new_question]);

        return $new_question;
    }
}
