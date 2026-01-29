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

    public function getTable(): Table
    {
        return new Table($this);
    }

    public function getColumns(
        array $columns_to_skip = []
    ): array {
        $table = $this->getTable();
        $column_identifiers = match($this) {
            self::Questions => self::QUESTION_TABLE_COLUMNS,
            self::AnswerForms => self::ANSWER_FORM_TABLE_COLUMNS,
            self::Linking => self::LINKING_TABLE_COLUMNS,
            self::MigrationsTable => self::MIGRATIONS_TABLE_COLUMNS
        };
        return array_map(
            fn(string $v): Column => new Column($table, $v),
            array_values(
                array_filter(
                    $column_identifiers,
                    fn(string $v) => !in_array($v, $columns_to_skip)
                )
            )
        );
    }

    public function getIdColumn(): Column
    {
        return match($this) {
            self::Questions => new Column(
                $this->getTable(),
                self::QUESTION_TABLE_ID_COLUMN
            ),
            self::AnswerForms => new Column(
                $this->getTable(),
                self::ANSWER_FORM_TABLE_ID_COLUMN
            ),
            self::Linking => new Column(
                $this->getTable(),
                self::LINKING_TABLE_ID_COLUMN
            ),
            self::MigrationsTable => new Column(
                $this->getTable(),
                self::MIGRATIONS_TABLE_ID_COLUMN
            )
        };
    }

    public function getForeignKeyColumn(): ?Column
    {
        return match($this) {
            self::AnswerForms => new Column(
                $this->getTable(),
                self::ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN
            ),
            self::Linking => new Column(
                $this->getTable(),
                self::LINKING_TABLE_FOREIGN_KEY_COLUMN
            ),
            self::MigrationsTable => new Column(
                $this->getTable(),
                self::MIGRATIONS_TABLE_FOREIGN_KEY_COLUMN
            ),
            default => null
        };
    }
}
