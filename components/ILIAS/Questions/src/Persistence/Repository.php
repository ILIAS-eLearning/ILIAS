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

namespace ILIAS\Questions\Persistence;

use ILIAS\Questions\AnswerForm\Factory as AnswerFormFactory;
use ILIAS\Questions\AnswerForm\Definition as AnswerFormDefinition;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\Question\QuestionImplementation;
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

    public function getNew(
        int $parent_obj_id
    ): QuestionImplementation {
        return new QuestionImplementation(
            $this->buildAvailableUuid(),
            $parent_obj_id
        );
    }

    /**
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getQuestionDataOnlyForAllQuestions(): \Generator
    {
        foreach ($this->buildQuestionsQuery()->loadNextRecord(
            $this->buildGroupByColumn()
        ) as $query_with_record) {
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
                CoreTables::Questions->getIdColumn(
                    $this->persistence_factory
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    array_map(
                        fn(Uuid $v): string => $v->toString(),
                        $question_ids
                    )
                ),
                Operator::In
            )
        )->loadNextRecord($this->buildGroupByColumn()) as $query_with_record) {
            yield $this->retrieveQuestionFromQuery(
                $query_with_record,
                []
            );
        }
    }

    public function getForQuestionId(
        Uuid $question_id
    ): ?QuestionImplementation {
        return $this->getForBaseQuery(
            $this->buildQuestionsQuery()->withAdditionalWhere(
                $this->persistence_factory->where(
                    CoreTables::Questions->getIdColumn(
                        $this->persistence_factory
                    ),
                    $this->persistence_factory->value(
                        \ilDBConstants::T_TEXT,
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
                    CoreTables::Questions->getIdColumn(
                        $this->persistence_factory
                    ),
                    $this->persistence_factory->value(
                        \ilDBConstants::T_TEXT,
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
                fn(QuestionImplementation $v): QuestionImplementation => $v
                    ->withPageId($this->buildQuestionPage($v->getParentObjId())),
                $questions
            ),
            new Manipulate(
                $this->db,
                $this->persistence_factory,
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
                $this->persistence_factory,
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
                $this->persistence_factory,
                $this->answer_form_factory,
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
            fn(Query $c, AnswerFormDefinition $v) => $v->getPersistence()->completeQuestionsQuery(
                $c,
                CoreTables::AnswerForms->getIdColumn(
                    $this->persistence_factory
                )
            ),
            $query
        );

        foreach ($query_with_answer_forms->loadNextRecord(
            $this->buildGroupByColumn()
        ) as $query_with_record) {
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
    ): QuestionImplementation {
        $linking_info = $query->retrieveCurrentRecord(
            CoreTables::Linking->getTable(
                $query->getPersistenceFactory()
            ),
            $this->refinery->identity()
        );

        $question = $query->retrieveCurrentRecord(
            CoreTables::Questions->getTable(
                $query->getPersistenceFactory()
            ),
            $this->refinery->custom()->transformation(
                fn(array $vs): QuestionImplementation => new QuestionImplementation(
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
            CoreTables::AnswerForms->getTable(
                $query->getPersistenceFactory()
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
        $query = $this->db->query(
            'SELECT DISTINCT type FROM ' . CoreTables::AnswerForms->value . PHP_EOL
                . "WHERE {$this->db->in(
                    'question_id',
                    $question_ids,
                    false,
                    \ilDBConstants::T_TEXT
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
            fn(Manipulate $c, QuestionImplementation $v): Manipulate => $v->toStorage($c),
            $manipulate
        )->run();
    }

    private function buildQuestionsQuery(): Query
    {
        $query = new Query(
            $this->db,
            $this->refinery,
            $this->persistence_factory,
            $this->answer_form_factory
        );

        $questions_linking_table_definition = CoreTables::Linking;
        $questions_table_definition = CoreTables::Questions;
        $answer_form_table_definition = CoreTables::AnswerForms;
        $questions_id_column = $questions_table_definition->getIdColumn(
            $this->persistence_factory
        );

        return $query->withAdditionalSelect(
            $this->persistence_factory->select(
                $questions_linking_table_definition->getColumns(
                    $this->persistence_factory
                )
            )
        )->withAdditionalSelect(
            $this->select[] = $this->persistence_factory->select(
                $questions_table_definition->getColumns(
                    $this->persistence_factory
                )
            )
        )->withAdditionalSelect(
            $this->select[] = $this->persistence_factory->select(
                $answer_form_table_definition->getColumns(
                    $this->persistence_factory
                )
            )
        )->withAdditionalJoin(
            $this->joins[] = $this->persistence_factory->join(
                $questions_linking_table_definition->getIdColumn(
                    $this->persistence_factory
                ),
                $questions_table_definition->getIdColumn(
                    $this->persistence_factory
                ),
                JoinType::Inner
            )
        )->withAdditionalJoin(
            $this->joins[] = $this->persistence_factory->join(
                $questions_id_column,
                $answer_form_table_definition->getForeignKeyColumn(
                    $this->persistence_factory
                ),
                JoinType::Left
            )
        )->withAdditionalOrder(
            $this->persistence_factory->order(
                $questions_id_column
            )
        )->withAdditionalOrder(
            $this->order[] = $this->persistence_factory->order(
                $answer_form_table_definition->getIdColumn(
                    $this->persistence_factory
                )
            )
        );
    }

    private function buildGroupByColumn(): Column
    {
        return CoreTables::Questions->getIdColumn(
            $this->persistence_factory
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
        return $this->db->fetchObject(
            $this->db->query(
                'SELECT COUNT(*) as cnt FROM ' . CoreTables::Questions->value
                    . " WHERE id='{$uuid->toString()}'"
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
        QuestionImplementation $question
    ): QuestionImplementation {
        $old_page_id = $this->db->fetchObject(
            $this->db->query(
                'SELECT old_question_id FROM ' . CoreTables::MigrationsTable->value . PHP_EOL
                . "WHERE new_question_id = {$this->db->quote($question->getId(), \ilDBConstants::T_TEXT)}"
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
