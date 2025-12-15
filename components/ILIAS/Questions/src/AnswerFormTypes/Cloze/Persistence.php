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
use ILIAS\Questions\Question\Persistence\TableTypes;
use ILIAS\Questions\Question\Persistence\Query;
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
        'scoring_identical_responses',
        'combinations_activated'
    ];

    private const string ANSWER_INPUTS_TABLE_ID_COLUMN = 'id';
    private const string ANSWER_INPUTS_TABLE_FOREIGN_KEY_COLUMN = 'answer_form_id';
    private const array ANSWER_INPUTS_TABLE_COLUMNS = [
        'id AS answer_input_id',
        'position',
        'gap_type',
        'max_chars',
        'step_size',
        'text_matching_method',
        'shuffle_answer_options'
    ];

    private const string ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN = 'answer_input_id';
    private const array ANSWER_OPTIONS_TABLE_COLUMNS = [
        'id AS answer_option_id',
        'position',
        'text_value',
        'points',
        'lower_limit',
        'upper_limit'
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

    public function completeQuery(
        TableNameBuilder $table_name_builder,
        Query $query,
        Column $base_table_id_column
    ): Query {
        $answer_form_specific_table = TableTypes::TypeSpecificAnswerForms
            ->getTable($table_name_builder);
        $answer_input_table = TableTypes::AnswerInputs
            ->getTable($table_name_builder);
        $answer_options_table = TableTypes::AnswerOptions
            ->getTable($table_name_builder);
        $combinations_table = TableTypes::Additional
            ->getTable($table_name_builder, 'combinations');

        return $query->withAdditionalJoin(
            new Join(
                $base_table_id_column,
                new Column(
                    $answer_form_specific_table,
                    self::ANSWER_FORM_TABLE_FOREIGN_KEY_COLUMN
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            new Select(
                $answer_form_specific_table,
                self::ANSWER_FORM_TABLE_COLUMNS
            )
        )->withAdditionalJoin(
            new Join(
                $base_table_id_column,
                new Column(
                    $answer_input_table,
                    self::ANSWER_INPUTS_TABLE_FOREIGN_KEY_COLUMN
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            new Select(
                $answer_input_table,
                self::ANSWER_INPUTS_TABLE_COLUMNS
            )
        )->withAdditionalJoin(
            new Join(
                new Column(
                    $answer_input_table,
                    self::ANSWER_INPUTS_TABLE_ID_COLUMN
                ),
                new Column(
                    $answer_options_table,
                    self::ANSWER_OPTIONS_TABLE_FOREIGN_KEY_COLUMN
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            new Select(
                $answer_options_table,
                self::ANSWER_OPTIONS_TABLE_COLUMNS
            )
        )->withAdditionalJoin(
            new Join(
                $base_table_id_column,
                new Column(
                    $combinations_table,
                    self::COMBINATIONS_TABLE_FOREIGN_KEY_COLUMN
                ),
                JoinType::Left
            )
        )->withAdditionalSelect(
            new Select(
                $combinations_table,
                self::COMBINATIONS_TABLE_COLUMNS
            )
        );
    }
}
