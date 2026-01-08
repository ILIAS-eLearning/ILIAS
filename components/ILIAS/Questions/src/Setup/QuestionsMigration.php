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

namespace ILIAS\Questions\Setup;

use ILIAS\Questions\AnswerForm\Migration as AnswerFormMigration;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Questions\Question\QuestionImplementation;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Setup;
use ILIAS\Setup\CLI\IOWrapper;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;
use ILIAS\DI\Container;

class QuestionsMigration implements Migration
{
    private const string QUESTIONS_TABLE = 'qpl_questions';
    private const string TEST_QUESTIONS_SEQUENCE_TABLE = 'tst_test_question';
    public const string MIGRATIONS_TABLE = 'qsts_migrations';

    private \ilDBInterface $db;
    private IOWrapper $io;
    private UuidFactory $uuid_factory;

    private bool $ilias_is_initialized = false;
    private ?array $question_to_learning_module_mapping = null;
    private ?array $allready_migrated_questions = null;

    public function __construct(
        private readonly array $answer_form_migrations
    ) {
    }

    #[\Override]
    public function getLabel(): string
    {
        return 'Migrate questions to Questions Component.';
    }

    #[\Override]
    public function getDefaultAmountOfStepsPerRun(): int
    {
        return 100;
    }

    #[\Override]
    public function getPreconditions(Environment $environment): array
    {
        return [new \ilDatabaseInitializedObjective()];
    }

    #[\Override]
    public function prepare(Environment $environment): void
    {
        $this->db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $this->io = $environment->getResource(Setup\Environment::RESOURCE_ADMIN_INTERACTION);
        $this->uuid_factory = new UuidFactory();
    }

    #[\Override]
    public function step(
        Environment $environment
    ): void {
        /**
         * sk, 2026-01-14: Sadly this is necessary to clone the question pages
         * without duplicating a humongous amount of code. It is mighty
         * depressing, but the structure of `COPage` is yay stupid.
         */
        if (!$this->ilias_is_initialized) {
            \ilContext::init(\ilContext::CONTEXT_CRON);
            entry_point('ILIAS Legacy Initialisation Adapter');
            $this->ilias_is_initialized = true;
        }

        $db_values = $this->fetchValidRecord();

        if ($db_values->obj_fi === 0) {
            $question->obj_fi = $this->getObjIdFromMapping($db_values->question_id);
        }

        if ($db_values->obj_fi === null) {
            $this->io->error(
                "The question with the id {$db_values->question_id} could not be "
               . "migrated as it doesn't belong to any object."
            );
            return;
        }

        $question = new QuestionImplementation(
            $this->uuid_factory->uuid4(),
            $db_values->obj_fi,
            $db_values->sequence,
            $this->buildQuestionPage($db_values),
            $db_values->title,
            $db_values->author,
            Lifecycle::tryFrom($db_values->lifecycle),
            $db_values->description,
            $db_values->original_id,
            $db_values->created,
            time()
        );
    }

    #[\Override]
    public function getRemainingAmountOfSteps(): int
    {
        return $this->db->fetchObject(
            $this->db->query(
                'SELECT COUNT(question_id) cnt FROM ' . self::QUESTIONS_TABLE . ' q' . PHP_EOL
                . 'JOIN qpl_qst_type t ON q.question_type_fi = t.question_type_id' . PHP_EOL
                . 'LEFT JOIN ' . self::MIGRATIONS_TABLE . ' m ON q.question_id = m.old_question_id' . PHP_EOL
                . 'WHERE t.type_tag IN ('
                . implode(
                    ', ',
                    array_map(
                        fn(AnswerFormMigration $v): string => "'{$v->getOldQuestionIdentifier()}'",
                        $this->answer_form_migrations
                    )
                ) . ')' . PHP_EOL
                . 'AND q.complete = 1' . PHP_EOL
                . 'AND m.old_question_id IS NULL'
            )
        )->cnt;
    }

    private function fetchValidRecord(): array
    {
        $query_string = 'SELECT q.*, t.sequence FROM ' . self::QUESTIONS_TABLE . ' q' . PHP_EOL
            . 'JOIN qpl_qst_type t ON q.question_type_fi = t.question_type_id' . PHP_EOL
            . 'LEFT JOIN ' . self::MIGRATIONS_TABLE . ' m ON q.question_id = m.old_question_id' . PHP_EOL
            . 'LEFT JOIN ' . self::TEST_QUESTIONS_SEQUENCE_TABLE . ' t ON q.question_id = t.question_fi'
            . 'WHERE t.type_tag IN ('
            . implode(
                ', ',
                array_map(
                    fn(AnswerFormMigration $v): string => "'{$v->getOldQuestionIdentifier()}'",
                    $this->answer_form_migrations
                )
            ) . ')' . PHP_EOL
            . 'AND q.complete = 1' . PHP_EOL
            . 'AND m.old_question_id IS NULL'
            . 'LIMIT 1';

        do {
            $db_values = $this->db->fetchObject(
                $this->db->query($query_string)
            );
        } while (!$this->areDbValuesValid($db_values));

        $db_values->original_id = $this->getNewQuestionIdForOld($db_values->original_id);
        return $db_values;
    }

    private function areDbValuesValid(
        array $db_values
    ): bool {
        if ($db_values->original_id === null) {
            return true;
        }

        if ($this->allready_migrated_questions === null) {
            $this->allready_migrated_questions = $this->loadAlreadyMigratedQuestions();
        }

        if (isset($this->allready_migrated_questions[$db_values->original_id])) {
            return true;
        }

        return false;
    }

    private function getNewQuestionIdForOld(
        ?int $question_id
    ): ?uuid {
        if ($question_id === null) {
            return null;
        }

        if ($this->allready_migrated_questions === null) {
            $this->allready_migrated_questions = $this->loadAlreadyMigratedQuestions();
        }

        if (!isset($this->allready_migrated_questions[$question_id])) {
            return null;
        }

        return $this->uuid_factory->fromString(
            $this->allready_migrated_questions[$question_id]
        );
    }

    private function loadAlreadyMigratedQuestions(): array
    {

        $query = $this->db->query(
            'SELECT * FROM ' . self::MIGRATIONS_TABLE
        );

        $questions = [];
        while (($row = $this->db->fetchObject($query)) !== null) {
            $questions[$row->old_question_id] = $row->new_question_id;
        }
        return $questions;
    }

    private function getObjIdFromMapping(
        int $question_id
    ): ?int {
        if ($this->question_to_learning_module_mapping === null) {
            $this->question_to_learning_module_mapping = $this->loadQuestionsToLearningModuleMapping();
        }

        $this->question_to_learning_module_mapping[$question_id] ?? null;
    }

    private function loadQuestionsToLearningModuleMapping(): array
    {

        $query = $this->db->query(
            'SELECT question_id, obj_id FROM page_question pq' . PHP_EOL
            . 'JOIN page_object po ON pq.page_id = po.page_id' . PHP_EOL
                . 'AND pq.page_parent_type = po.parent_type' . PHP_EOL
            . 'JOIN object_data o ON po.parent_id = o.obj_id' . PHP_EOL
            . 'WHERE page_parent_type = "lm"'
        );

        $mapping = [];
        while (($row = $this->db->fetchObject($query)) !== null) {
            $mapping[$row->question_id] = $row->obj_id;
        }
        return $mapping;
    }

    private function buildQuestionPage(

    ): int {
        $new_id = $this->getNextAvailableQuestionPageId();
        $page = new \ilAssQuestionPage();
        $page->copy(
            $new_id,
            'qsts',
        );
        return $new_id;
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
