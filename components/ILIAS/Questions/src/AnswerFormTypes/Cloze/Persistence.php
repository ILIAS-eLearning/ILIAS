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

namespace ILIAS\Questions\AnswerFormTypes\Cloze;

use ILIAS\Questions\AnswerForm\Persistence as PersistenceInterface;
use ILIAS\Questions\Question\Persistence\SelectQuery;
use ILIAS\Questions\Question\Persistence\Join;
use ILIAS\Questions\Question\Persistence\Column;
use ILIAS\Questions\Question\Persistence\JoinType;
use ILIAS\Questions\Question\Persistence\Select;
use ILIAS\Questions\Question\Persistence\TableNameBuilder;
use ILIAS\Questions\Question\Persistence\TableNameSpace;
use ILIAS\Questions\Question\Persistence\TableNameSpaceCore;

class Persistence implements PersistenceInterface
{
    private const string ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array ANSWER_FORM_TABLE_COLUMNS = [
        'case_sensitive',
        'identical_responses_valid',
        'max_chars',
        'min_auto_complete'
    ];

    private const string ANSWER_INPUTS_TABLE_ID_COLUMN = 'id';
    private const string ANSWER_INPUTS_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array ANSWER_INPUTS_TABLE_COLUMNS = [
        'id AS answer_input_id',
        'gap_type',
        'max_chars'
    ];

    private const string ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN = 'answer_input_id';
    private const array ANSWER_OPTIONS_TABLE_COLUMNS = [
        'id AS answer_option_id',
        'position',
        'value',
        'points'
    ];

    private const string COMBINATIONS_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array COMBINATIONS_TABLE_COLUMNS = [
        'id AS combination_id',
        'answer_options',
        'points'
    ];

    public function __construct(
        private readonly TableNameSpaceCore $table_namespace
    ) {
    }

    public function getPublicNameSpace(): TableNameSpace
    {
        return $this->table_namespace;
    }

    public function completeSelectQuery(
        TableNameBuilder $table_name_builder,
        SelectQuery $select_query,
        Column $base_table_id_column
    ): SelectQuery {
        return $select_query->withAdditionalJoin(
            new Join(
                $base_table_id_column,
                new Column(
                    $table_name_builder->getTypeSpecificAnswerFormsTableName(),
                    self::ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN
                )
            )
        )->withAdditionalSelect(
            new Select(
                $table_name_builder->getTypeSpecificAnswerFormsTableName(),
                self::ANSWER_FORM_TABLE_COLUMNS
            )
        )->withAdditionalJoin(
            new Join(
                $base_table_id_column,
                new Column(
                    $table_name_builder->getAnswerInputsTableName(),
                    self::ANSWER_INPUTS_TABLE_FOREIGN_KEY_COLUMN
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            new Select(
                $table_name_builder->getAnswerInputsTableName(),
                self::ANSWER_INPUTS_TABLE_COLUMNS
            )
        )->withAdditionalJoin(
            new Join(
                new Column(
                    $table_name_builder->getAnswerInputsTableName(),
                    self::ANSWER_INPUTS_TABLE_ID_COLUMN
                ),
                new Column(
                    $table_name_builder->getAnswerOptionsTableName(),
                    self::ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            new Select(
                $table_name_builder->getAnswerOptionsTableName(),
                self::ANSWER_OPTIONS_TABLE_COLUMNS
            )
        )->withAdditionalJoin(
            new Join(
                $base_table_id_column,
                new Column(
                    $table_name_builder->getAdditionalTableName('combinations'),
                    self::COMBINATIONS_TABLE_FOREIGN_KEY_COLUMN
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            new Select(
                $table_name_builder->getAdditionalTableName('combinations'),
                self::COMBINATIONS_TABLE_COLUMNS
            )
        );
    }
}
