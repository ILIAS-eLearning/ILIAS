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
use ILIAS\Questions\AnswerForm\Definition;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;

class Repository
{
    public function __construct(
        private readonly \ilDBInterface $db,
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
        yield from $this->getForBaseQuery(new Query($this->db));
    }

    public function getForQuestionId(Uuid $question_id): ?QuestionImplementation
    {
        return $this->getForBaseQuery(
            (new Query($this->db))->withAdditionalWhere(
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
            (new Query($this->db))->withAdditionalWhere(
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
        $result = array_reduce(
            $this->answer_form_factory->getAvailableDefinitions(),
            fn(Query $c, Definition $v) => $v->getPersistence()->completeQuery(
                new TableNameBuilder($v->getPersistence()->getPublicNameSpace()),
                $c,
                CoreTables::Questions->getIdColumn()
            ),
            $query
        )->toSql();

        $question_records = [$this->db->fetchObject($result)];
        if ($question_records[0] === null) {
            return null;
        }
        while (($db_record = $this->db->fetchObject($result)) !== null) {
            if ($db_record->id === $question_records[0]->id) {
                $question_records[] = $db_record;
                continue;
            }
            yield $this->buildQuestionFromDBRecords($question_records);
            $question_records = [$db_record];
        }
        yield $this->buildQuestionFromDBRecords($question_records);
    }

    private function buildQuestionFromDBRecords(array $db_record): QuestionImplementation
    {
        $basic_properties = $db_record[0];
        return new QuestionImplementation(
            $this->uuid_factory->fromString($basic_properties->id),
            $basic_properties->page_id,
            $basic_properties->title,
            $basic_properties->author,
            Lifecycle::from($basic_properties->lifecycle),
            $basic_properties->remarks,
            $basic_properties->original_id === null
                ? null
                : $this->uuid_factory->fromString($basic_properties->original_id),
            new \DateTimeImmutable('@' . $basic_properties->last_update, new \DateTimeZone('UTC')),
            new \DateTimeImmutable('@' . $basic_properties->created, new \DateTimeZone('UTC'))
        );
    }

    /**
     *
     * @param array<\ILIAS\Questions\Question\Persistence\Storable> $storable
     * @return array<ILIAS\Data\UUID\Uuid>
     */
    public function store(
        array $storable
    ): void {
        array_reduce(
            $storable,
            fn(Manipulate $c, Storable $v): Manipulate => $v->toStorage($c),
            new Manipulate($this->db)
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
