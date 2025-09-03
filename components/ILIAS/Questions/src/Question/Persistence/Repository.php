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

use ILIAS\Questions\AnswerForm\Type;
use ILIAS\Questions\AnswerFormTypes\Factory as FormTypesFactory;
use ILIAS\Questions\Question\Lifecycle;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;

class Repository
{
    public const string QUESTION_TABLE = 'qsts_questions';
    public const string QUESTION_TABLE_ID_COLUMN = 'id';
    public const array QUESTION_TABLE_COLUMNS = [
        'id',
        'page_id',
        'title',
        'author',
        'lifecycle',
        'remarks',
        'original_id',
        'last_update',
        'created'
    ];

    public const string ANSWER_FORM_TABLE = 'qsts_answer_forms';
    public const string ANSWER_FORM_TABLE_ID_COLUMN = 'id';
    public const string ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN = 'question_id';
    public const array ANSWER_FORM_TABLE_COLUMNS = [
        'id AS answer_form_id',
        'type',
        'available_points',
        'image_size',
        'shuffle_answer_options',
        'additional_text',
        'additional_text_legacy'
    ];

    private const string PAGE_EDITOR_TABLE = 'page_object';

    public function __construct(
        private readonly \ilDBInterface $db,
        private readonly UuidFactory $uuid_factory,
        private readonly FormTypesFactory $form_types_factory
    ) {
    }

    /**
     * @return array<string, \ILIAS\Questions\AnswerForm\Type>
     */
    public function getAvailableAnswerTypes(): array
    {
        return $this->form_types_factory->getAvailableAnswerTypes();
    }

    public function getAvailableAnswerTypeByClass(string $class): ?Type
    {
        return $this->form_types_factory->getAnswerFormTypeByClass($class);
    }

    /**
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getAllQuestions(): \Generator
    {
        yield from $this->getForWhereClause('');
    }

    public function getForQuestionId(string $question_id): ?QuestionImplementation
    {
        if ($question_id < 1) {
            return new QuestionImplementation();
        }

        return $this->getForWhereClause("q.id='{$question_id}'")->current();
    }

    /**
     *
     * @param array<\ILIAS\Data\Uuid> $question_ids
     * @return \Generator<\ILIAS\Questions\Question\QuestionImplementation>
     */
    public function getForQuestionIds(array $question_ids): \Generator
    {
        yield from $this->getForWhereClause(
            $this->db->in(
                'q.question_id',
                $question_ids,
                false,
                \ilDBConstants::T_INTEGER
            )
        );
    }

    private function buildQuestionFromDBRecords(\stdClass $db_record): QuestionImplementation
    {
        return new QuestionImplementation(
            $this->uuid_factory->fromString($db_record->id),
            $db_record->page_id,
            $db_record->title,
            $db_record->author,
            Lifecycle::from($db_record->lifecycle),
            $db_record->remarks,
            $db_record->original_id === null ? null : $this->uuid_factory->fromString($db_record->original_id),
            new \DateTimeImmutable('@' . $db_record->last_update, new \DateTimeZone('UTC')),
            new \DateTimeImmutable('@' . $db_record->created, new \DateTimeZone('UTC'))
        );
    }

    /**
     * @return \Generator<ILIAS\Questions\Question\Question>
     */
    private function getForWhereClause(string $where): \Generator
    {
        $query_result = $this->db->query(
            'SELECT q.id, q.page_id, q.title, q.author, q.lifecycle, q.remarks,' . PHP_EOL
            . 'q.original_id, q.last_update, q.created' . PHP_EOL
            . 'FROM ' . self::QUESTION_TABLE . ' q' . PHP_EOL
            . ($where === '' ? '' : 'WHERE ' . $where)
        );

        while (($db_record = $this->db->fetchObject($query_result)) !== null) {
            yield $this->buildQuestionFromDBRecords($db_record);
        }
    }

    /**
     *
     * @param ILIAS\Questions\Question\Question|array<ILIAS\Questions\Question\QuestionImplementation> $questions
     * @return array<ILIAS\Data\UUID\Uuid>
     */
    public function store(
        QuestionImplementation|array $questions
    ): array {
        if ($questions instanceof QuestionImplementation) {
            return [$this->storeQuestion($questions)];
        }

        return array_map(
            fn(QuestionImplementation $v) => $this->storeQuestion($v),
            $questions
        );
    }

    private function storeQuestion(QuestionImplementation $question): Uuid
    {
        if ($question->getPageId() === null) {
            $question = $question->withPageId($this->buildQuestionPage());
        }

        if ($question->getId() === null) {
            $question = $question->withQuestionId($this->buildAvailableUuid());
            $this->db->insert(
                self::QUESTION_TABLE,
                $question->toStorage()
            );
            return $question->getId();
        }
        $this->db->update(
            self::QUESTION_TABLE,
            $question->toStorage(),
            [
                'id' => [
                    \ilDBConstants::T_TEXT,
                    $question->getId()->toString()
                ]
            ]
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
                'SELECT MAX(page_id) AS last FROM ' . self::PAGE_EDITOR_TABLE
                    . ' WHERE parent_type = "qsts"'
            )
        )->last;
        if ($last_id === null) {
            return 1;
        }

        return $last_id + 1;
    }
}
