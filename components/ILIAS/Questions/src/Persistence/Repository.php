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
use ILIAS\Questions\AnswerForm\Properties as AnswerFormProperties;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Refinery\Factory as Refinery;

class Repository
{
    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly Refinery $refinery,
        private readonly UuidFactory $uuid_factory,
        private readonly AnswerFormFactory $answer_form_factory
    ) {
    }

    public function getNew(): QuestionImplementation
    {
        return new QuestionImplementation(
            $this->buildAvailableUuid()
        );
    }

    /**
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getAllQuestions(): \Generator
    {
        yield from $this->getForBaseQuery(
            new Query(
                $this->db,
                $this->answer_form_factory,
                $this->refinery
            )
        );
    }

    public function getForQuestionId(Uuid $question_id): ?QuestionImplementation
    {
        return $this->getForBaseQuery(
            (new Query(
                $this->db,
                $this->answer_form_factory,
                $this->refinery
            ))->withAdditionalWhere(
                new Where(
                    CoreTables::Questions->getIdColumn(),
                    new Value(
                        \ilDBConstants::T_TEXT,
                        $question_id->toString()
                    ),
                    Operator::Equal
                )
            )
        )->current();
    }

    /**
     *
     * @param array<\ILIAS\Data\Uuid> $question_ids
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getForQuestionIds(array $question_ids): \Generator
    {
        yield from $this->getForBaseQuery(
            (new Query(
                $this->db,
                $this->answer_form_factory,
                $this->refinery
            ))->withAdditionalWhere(
                new Where(
                    CoreTables::Questions->getIdColumn(),
                    new Value(
                        \ilDBConstants::T_TEXT,
                        array_map(
                            fn(Uuid $v): string => $v->toString(),
                            $question_ids
                        )
                    ),
                    Operator::In
                )
            )
        );
    }

    /**
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    private function getForBaseQuery(Query $query): \Generator
    {
        $query_with_answer_forms = array_reduce(
            $this->answer_form_factory->getAvailableDefinitions(),
            fn(Query $c, AnswerFormDefinition $v) => $v->getPersistence()->completeQuery(
                $c,
                CoreTables::AnswerForms->getIdColumn()
            ),
            $query
        );

        foreach ($query_with_answer_forms->loadNextRecord() as $query_with_record) {
            yield $this->retrieveQuestionFromQuery(
                $query_with_record,
                $this->retrieveAnswerFormsFromQuery($query_with_record)
            );
        }
    }

    public function create(
        array $storable
    ): void {
        $this->store(
            $storable,
            new Manipulate(
                $this->db,
                $this->answer_form_factory,
                ManipulationType::Create
            )
        );
    }

    public function update(
        array $storable
    ): void {
        $this->store(
            $storable,
            new Manipulate(
                $this->db,
                $this->answer_form_factory,
                ManipulationType::Update
            )
        );
    }

    private function retrieveQuestionFromQuery(
        Query $query,
        array $answer_forms
    ): QuestionImplementation {
        return $query->retrieveCurrentRecord(
            CoreTables::Questions->getTable(),
            $this->refinery->custom()->transformation(
                fn(array $vs): QuestionImplementation => new QuestionImplementation(
                    $this->uuid_factory->fromString($vs[0]['id']),
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
    }

    private function retrieveAnswerFormsFromQuery(
        Query $query
    ): array {
        return $query->retrieveCurrentRecord(
            CoreTables::AnswerForms->getTable(),
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
     *
     * @param array<\ILIAS\Questions\Persistence\Storable> $storable
     * @return array<ILIAS\Data\UUID\Uuid>
     */
    private function store(
        array $storable,
        Manipulate $manipulate
    ): void {
        array_reduce(
            $storable,
            fn(Manipulate $c, Storable $v): Manipulate => $v->toStorage($c),
            $manipulate
        )->run();
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

    private function checkAvailabilityOfId(Uuid $uuid): bool
    {
        return $this->db->fetchObject(
            $this->db->query(
                'SELECT COUNT(*) as cnt FROM ' . self::QUESTION_TABLE
                    . " WHERE id='{$uuid->toString()}'"
            )
        )->cnt === 0;
    }

    private function buildQuestionPage(): int
    {
        $page = new \QstsQuestionPage();
        $page->setId($this->getNextAvailableQuestionPageId());
        $page->createFromXML();
        return $page->getId();
    }

    private function getNextAvailableQuestionPageId(): int
    {

        $last_id = $this->db->fetchObject(
            $this->db->query(
                'SELECT MAX(page_id) AS last FROM ' . CoreTables::PageEditor->value
                    . ' WHERE parent_type = "qsts"'
            )
        )->last;
        if ($last_id === null) {
            return 1;
        }

        return $last_id + 1;
    }
}
