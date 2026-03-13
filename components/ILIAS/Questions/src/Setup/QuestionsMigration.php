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

use ILIAS\Questions\AnswerForm\Capabilities\Migration as CapabilityMigration;
use ILIAS\Questions\AnswerForm\Migration\Migration as AnswerFormMigration;
use ILIAS\Questions\AnswerForm\Migration\MigrationInsert as AnswerFormMigrationInsert;
use ILIAS\Questions\AnswerForm\Persistence\AnswerFormGenericTableDefinitions;
use ILIAS\Questions\Question\Persistence\TableDefinitions as QuestionTableDefinitions;
use ILIAS\Questions\Question\Persistence\TableTypes as QuestionTableTypes;
use ILIAS\Questions\Persistence\Factory as PersistenceFactory;
use ILIAS\Questions\Persistence\Insert;
use ILIAS\Questions\Persistence\TableNameBuilder;
use ILIAS\Questions\Question\Definitions\Lifecycle;
use ILIAS\Data\UUID\Factory as UuidFactory;
use ILIAS\Data\UUID\Uuid;
use ILIAS\Setup;
use ILIAS\Setup\CLI\IOWrapper;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Migration;

class QuestionsMigration implements Migration
{
    private const string OLD_QUESTIONS_TABLE = 'qpl_questions';
    private const string OLD_QUESTION_TYPE_TABLE = 'qpl_qst_type';
    private const string TEST_QUESTIONS_SEQUENCE_TABLE = 'tst_test_question';

    private \ilDBInterface $db;
    private IOWrapper $io;
    private UuidFactory $uuid_factory;
    private readonly array $answer_form_migrations;

    private ?array $question_to_learning_module_mapping = null;
    private ?array $allready_migrated_questions = null;
    private ?array $allready_migrated_questions_in_qpls = null;

    /**
     * @param array<\ILIAS\Questions\AnswerForm\Migration\Migration> $answer_form_migrations
     * @param array<\ILIAS\Questions\AnswerForm\Capabilities\Migration> $capability_migrations
     */
    public function __construct(
        private readonly PersistenceFactory $persistence_factory,
        private readonly TableNameBuilder $question_table_name_builder,
        private readonly QuestionTableDefinitions $question_table_definitions,
        private readonly AnswerFormGenericTableDefinitions $answer_form_generic_table_definitions,
        array $answer_form_migrations,
        private readonly array $capability_migrations
    ) {
        $this->answer_form_migrations = array_reduce(
            $answer_form_migrations,
            function (array $c, AnswerFormMigration $v): array {
                $c[$v->getOldQuestionTypeIdentifier()] = $v;
                return $c;
            },
            []
        );
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
        $db_values = $this->fetchValidRecord();

        if ($db_values === null) {
            return;
        }

        if ($db_values->obj_fi === 0) {
            $db_values->obj_fi = $this->getObjIdFromLearningModulMapping($db_values->question_id);
        }

        if ($db_values->obj_fi === null) {
            $this->io->error(
                "The question with the id {$db_values->question_id} could not be "
               . "migrated as it doesn't belong to any object."
            );
            return;
        }

        /** @var \ILIAS\Questions\AnswerForm\Migration\Migration $answer_form_migration */
        $answer_form_migration = $this->answer_form_migrations[$db_values->type_tag];

        $new_question_id = $this->uuid_factory->uuid4();

        $migration_insert = $answer_form_migration->completeMigrationInsert(
            $environment,
            $this->persistence_factory,
            $this->buildMigrationInsert(
                $answer_form_migration,
                [
                    $this->buildInsertLinkingStatement(
                        $new_question_id,
                        $db_values->obj_fi,
                        $db_values->sequence
                    ),
                    $this->buildInsertQuestionStatement(
                        $new_question_id,
                        $db_values->title,
                        $db_values->author,
                        Lifecycle::tryFrom($db_values->lifecycle),
                        $db_values->description,
                        $db_values->original_id,
                        $db_values->created
                    ),
                    $this->buildInsertMigrationStatement(
                        $db_values->question_id,
                        $new_question_id
                    )
                ],
                $new_question_id,
                $db_values
            )
        );

        if ($migration_insert === null) {
            $this->db->manipulate(
                $this->buildInsertMigrationStatement(
                    $db_values->question_id,
                    null
                )->toManipulateString($this->db)
            );
            $this->io->inform(
                "{$db_values->question_id} could not be migrated due to missing question data."
            );
            return;
        }

        array_reduce(
            $this->capability_migrations,
            fn(AnswerFormMigrationInsert $c, CapabilityMigration $v): AnswerFormMigration
                => $v->completeMigrationInsert(
                    $environment,
                    $this->persistence_factory,
                    $migration_insert
                ),
            $migration_insert
        )->run();
        $this->io->inform("{$new_question_id->toString()} successfully migrated.");
    }

    #[\Override]
    public function getRemainingAmountOfSteps(): int
    {
        $migration_table_name = $this->question_table_name_builder
            ->getTableNameFor(QuestionTableTypes::MigrationsTable);

        $query = $this->db->query(
            'SELECT COUNT(question_id) cnt FROM ' . self::OLD_QUESTIONS_TABLE . ' q' . PHP_EOL
                . 'JOIN ' . self::OLD_QUESTION_TYPE_TABLE . ' t ON q.question_type_fi = t.question_type_id' . PHP_EOL
                . "LEFT JOIN {$migration_table_name}"
                . ' m ON q.question_id = m.old_question_id' . PHP_EOL
                . 'WHERE t.type_tag IN ('
                . implode(
                    ', ',
                    array_map(
                        fn(AnswerFormMigration $v): string => "'{$v->getOldQuestionTypeIdentifier()}'",
                        $this->answer_form_migrations
                    )
                ) . ')' . PHP_EOL
                . 'AND q.complete = 1' . PHP_EOL
                . 'AND m.old_question_id IS NULL'
        );
        return $this->db->fetchObject(
            $query
        )->cnt;
    }

    private function fetchValidRecord(): ?\stdClass
    {
        $migration_table_name = $this->question_table_name_builder
            ->getTableNameFor(QuestionTableTypes::MigrationsTable);

        $query = $this->db->query(
            'SELECT q.*, t.type_tag, s.sequence FROM ' . self::OLD_QUESTIONS_TABLE . ' q' . PHP_EOL
            . 'JOIN ' . self::OLD_QUESTION_TYPE_TABLE . ' t ON q.question_type_fi = t.question_type_id' . PHP_EOL
            . "LEFT JOIN {$migration_table_name}"
            . ' m ON q.question_id = m.old_question_id' . PHP_EOL
            . 'LEFT JOIN ' . self::TEST_QUESTIONS_SEQUENCE_TABLE . ' s ON q.question_id = s.question_fi' . PHP_EOL
            . 'WHERE t.type_tag IN ('
            . implode(
                ', ',
                array_map(
                    fn(AnswerFormMigration $v): string => "'{$v->getOldQuestionTypeIdentifier()}'",
                    $this->answer_form_migrations
                )
            ) . ')' . PHP_EOL
            . 'AND q.complete = 1' . PHP_EOL
            . 'AND m.old_question_id IS NULL'
        );

        do {
            $db_values = $this->db->fetchObject($query);
            if ($db_values === null) {
                return null;
            }
        } while (!$this->areDbValuesValid($db_values));

        $db_values->original_id = $this->cleanupAndMigrateOriginalId($db_values->original_id);
        return $db_values;
    }

    private function areDbValuesValid(
        \stdClass $db_values
    ): bool {
        if ($db_values->original_id === null) {
            return true;
        }

        if ($this->allready_migrated_questions === null) {
            $this->loadAlreadyMigratedQuestions();
        }

        if (isset($this->allready_migrated_questions[$db_values->original_id])) {
            return true;
        }

        return false;
    }

    private function cleanupAndMigrateOriginalId(
        ?int $original_id
    ): ?Uuid {
        if ($original_id === null
            || in_array($original_id, $this->allready_migrated_questions_in_qpls)) {
            return null;
        }
        return $this->uuid_factory->fromString(
            $this->allready_migrated_questions[$original_id]
        );
    }

    private function loadAlreadyMigratedQuestions(): void
    {
        $migration_table_name = $this->question_table_name_builder
            ->getTableNameFor(QuestionTableTypes::MigrationsTable);
        $linking_table_name = $this->question_table_name_builder
            ->getTableNameFor(QuestionTableTypes::Linking);

        $query = $this->db->query(
            "SELECT m.*, o.type FROM {$migration_table_name} m" . PHP_EOL
            . "JOIN {$linking_table_name} l" . PHP_EOL
            . 'ON m.new_question_id = l.question_id' . PHP_EOL
            . 'JOIN object_data o ON l.obj_id = o.obj_id' . PHP_EOL
        );

        $this->allready_migrated_questions = [];
        $this->allready_migrated_questions_in_qpls = [];
        while (($row = $this->db->fetchObject($query)) !== null) {
            $this->allready_migrated_questions[$row->old_question_id] = $row->new_question_id;
            if ($row->type === 'qpl') {
                $this->allready_migrated_questions_in_qpls[] = $row->new_question_id;
            }
        }
    }

    private function getObjIdFromLearningModulMapping(
        int $question_id
    ): ?int {
        if ($this->question_to_learning_module_mapping === null) {
            $this->loadQuestionsToLearningModuleMapping();
        }

        return $this->question_to_learning_module_mapping[$question_id] ?? null;
    }

    private function loadQuestionsToLearningModuleMapping(): void
    {

        $query = $this->db->query(
            'SELECT question_id, obj_id FROM page_question pq' . PHP_EOL
            . 'JOIN page_object po ON pq.page_id = po.page_id' . PHP_EOL
                . 'AND pq.page_parent_type = po.parent_type' . PHP_EOL
            . 'JOIN object_data o ON po.parent_id = o.obj_id' . PHP_EOL
            . 'WHERE page_parent_type = "lm"'
        );

        $this->question_to_learning_module_mapping = [];
        while (($row = $this->db->fetchObject($query)) !== null) {
            $this->question_to_learning_module_mapping[$row->question_id] = $row->obj_id;
        }
    }

    private function buildInsertLinkingStatement(
        Uuid $new_question_id,
        int $obj_id,
        ?int $position
    ): Insert {
        return $this->persistence_factory->insert(
            $this->question_table_definitions->getColumns(
                $this->question_table_name_builder,
                QuestionTableTypes::Linking
            ),
            [
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $new_question_id->toString()
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $obj_id
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $position
                )
            ]
        );
    }

    private function buildInsertQuestionStatement(
        Uuid $id,
        string $title,
        string $author,
        Lifecycle $lifecycle,
        string $remarks,
        ?Uuid $original_id,
        int $create_date
    ): Insert {
        return $this->persistence_factory->insert(
            $this->question_table_definitions->getColumns(
                $this->question_table_name_builder,
                QuestionTableTypes::Questions
            ),
            [
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $id->toString()
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    0
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $title
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $author
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $lifecycle->value
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $remarks
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $original_id?->toString()
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    time()
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $create_date
                )
            ]
        );
    }

    private function buildInsertMigrationStatement(
        int $old_question_id,
        ?Uuid $new_question_id
    ): Insert {
        return $this->persistence_factory->insert(
            $this->persistence_factory->getColumns(
                $this->question_table_name_builder,
                QuestionTableTypes::MigrationsTable
            ),
            [
                $this->persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $old_question_id
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_TEXT,
                    $new_question_id?->toString()
                ),
                $this->persistence_factory->value(
                    \ilDBConstants::T_INTEGER,
                    $new_question_id === null
                        ? '0'
                        : '1'
                )
            ]
        );
    }

    private function buildMigrationInsert(
        AnswerFormMigration $answer_form_migration,
        array $question_inserts,
        Uuid $new_question_id,
        \stdClass $db_values
    ): AnswerFormMigrationInsert {
        return new AnswerFormMigrationInsert(
            $this->db,
            $this->io,
            $this->uuid_factory,
            $this->persistence_factory,
            $this->answer_form_generic_table_definitions,
            new TableNameBuilder(
                $answer_form_migration->getTableNameSpace()
            ),
            $question_inserts,
            $db_values->question_id,
            $new_question_id,
            $this->uuid_factory->uuid4(),
            $answer_form_migration->getDefinitionClass(),
            $db_values->add_cont_edit_mode === \assQuestion::ADDITIONAL_CONTENT_EDITING_MODE_IPE
        );
    }
}
