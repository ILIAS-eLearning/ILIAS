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

enum CoreTables: string
{
    private const string QUESTION_TABLE_ID_COLUMN = 'id';
    private const array QUESTION_TABLE_COLUMNS = [
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

    public const string ANSWER_FORM_TABLE_ID_COLUMN = 'id';
    private const string ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN = 'question_id';
    private const array ANSWER_FORM_TABLE_COLUMNS = [
        'id',
        'type',
        'question_id',
        'available_points',
        'image_size',
        'shuffle_answer_options',
        'additional_text',
        'additional_text_legacy'
    ];

    public const string LINKING_TABLE_ID_COLUMN = 'question_id';
    private const string LINKING_TABLE_FOREIGN_KEY_COLUMN = 'obj_id';
    private const array LINKING_TABLE_COLUMNS = [
        'question_id',
        'obj_id',
        'position'
    ];

    public const string MIGRATIONS_TABLE_ID_COLUMN = 'new_question_id';
    private const string MIGRATIONS_TABLE_FOREIGN_KEY_COLUMN = 'old_question_id';
    private const array MIGRATIONS_TABLE_COLUMNS = [
        'old_question_id',
        'new_question_id',
        'success'
    ];

    case Questions = 'qsts_questions';
    case AnswerForms = 'qsts_answer_forms';
    case Responses = 'qsts_responses';
    case Linking = 'qsts_linking';
    case MigrationsTable = 'qsts_migrations';

    public function getTable(
        Factory $persistence_factory
    ): Table {
        return $persistence_factory->table($this);
    }

    public function getColumns(
        Factory $persistence_factory,
        array $columns_to_skip = []
    ): array {
        $table = $this->getTable($persistence_factory);
        $column_identifiers = match($this) {
            self::Questions => self::QUESTION_TABLE_COLUMNS,
            self::AnswerForms => self::ANSWER_FORM_TABLE_COLUMNS,
            self::Linking => self::LINKING_TABLE_COLUMNS,
            self::MigrationsTable => self::MIGRATIONS_TABLE_COLUMNS
        };
        return array_map(
            fn(string $v): Column => $persistence_factory->column($table, $v),
            array_values(
                array_filter(
                    $column_identifiers,
                    fn(string $v) => !in_array($v, $columns_to_skip)
                )
            )
        );
    }

    public function getIdColumn(
        Factory $persistence_factory
    ): Column {
        return match($this) {
            self::Questions => $persistence_factory->column(
                $this->getTable($persistence_factory),
                self::QUESTION_TABLE_ID_COLUMN
            ),
            self::AnswerForms => $persistence_factory->column(
                $this->getTable($persistence_factory),
                self::ANSWER_FORM_TABLE_ID_COLUMN
            ),
            self::Linking => $persistence_factory->column(
                $this->getTable($persistence_factory),
                self::LINKING_TABLE_ID_COLUMN
            ),
            self::MigrationsTable => $persistence_factory->column(
                $this->getTable($persistence_factory),
                self::MIGRATIONS_TABLE_ID_COLUMN
            )
        };
    }

    public function getForeignKeyColumn(
        Factory $persistence_factory
    ): ?Column {
        return match($this) {
            self::AnswerForms => $persistence_factory->column(
                $this->getTable($persistence_factory),
                self::ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN
            ),
            self::Linking => $persistence_factory->column(
                $this->getTable($persistence_factory),
                self::LINKING_TABLE_FOREIGN_KEY_COLUMN
            ),
            self::MigrationsTable => $persistence_factory->column(
                $this->getTable($persistence_factory),
                self::MIGRATIONS_TABLE_FOREIGN_KEY_COLUMN
            ),
            default => null
        };
    }
}
