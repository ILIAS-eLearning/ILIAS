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

use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\UI\Component\Table\DataRetrieval;
use ILIAS\UI\Component\Table\DataRowBuilder;

class ilSurveyResultsCumulatedTable implements DataRetrieval
{
    public const COL_TITLE = "title";
    public const COL_QUESTION = "question";
    public const COL_QUESTION_TYPE = "question_type";
    public const COL_USERS_ANSWERED = "users_answered";
    public const COL_USERS_SKIPPED = "users_skipped";
    public const COL_MODE = "mode";
    public const COL_MODE_NR_OF_SELECTIONS = "mode_nr_of_selections";
    public const COL_MEDIAN = "median";
    public const COL_ARITHMETIC_MEAN = "arithmetic_mean";

    protected array $data = [];

    /**
     * @param array $a_results results per question: \ilSurveyEvaluationResults
     *                         or (matrix) list of [row title, \ilSurveyEvaluationResults]
     */
    public function __construct(
        array $a_results
    ) {
        $this->data = $this->getItems($a_results);
    }

    protected function getItems(
        array $a_results
    ): array {
        $data = [];

        foreach ($a_results as $question_res) {
            if (!is_array($question_res)) {
                $question = $question_res->getQuestion();

                $data[] = [
                    self::COL_TITLE => $question->getTitle(),
                    self::COL_QUESTION => strip_tags($question->getQuestiontext()),
                    self::COL_QUESTION_TYPE => \SurveyQuestion::_getQuestionTypeName($question->getQuestionType()),
                    self::COL_USERS_ANSWERED => $question_res->getUsersAnswered(),
                    self::COL_USERS_SKIPPED => $question_res->getUsersSkipped(),
                    self::COL_MODE => $question_res->getModeValueAsText(),
                    self::COL_MODE_NR_OF_SELECTIONS => $question_res->getModeNrOfSelections(),
                    self::COL_MEDIAN => $question_res->getMedianAsText(),
                    self::COL_ARITHMETIC_MEAN => $question_res->getMean()
                ];
            }
            // matrix
            else {
                foreach ($question_res as $item) {
                    $row_title = $item[0];
                    $row_res = $item[1];
                    $question = $row_res->getQuestion();

                    $data[] = [
                        self::COL_TITLE => $question->getTitle(),
                        self::COL_QUESTION => $row_title,
                        self::COL_QUESTION_TYPE => \SurveyQuestion::_getQuestionTypeName($question->getQuestionType()),
                        self::COL_USERS_ANSWERED => $row_res->getUsersAnswered(),
                        self::COL_USERS_SKIPPED => $row_res->getUsersSkipped(),
                        self::COL_MODE => $row_res->getModeValueAsText(),
                        self::COL_MODE_NR_OF_SELECTIONS => $row_res->getModeNrOfSelections(),
                        self::COL_MEDIAN => $row_res->getMedianAsText(),
                        self::COL_ARITHMETIC_MEAN => $row_res->getMean()
                    ];
                }
            }
        }

        return $data;
    }

    public function getRows(
        DataRowBuilder $row_builder,
        array $visible_column_ids,
        Range $range,
        Order $order,
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): Generator {
        $rows = array_slice(
            $this->data,
            $range->getStart(),
            $range->getLength(),
            true
        );
        foreach ($rows as $row_index => $row) {
            $record = [];
            foreach ($row as $col_id => $value) {
                $record[$col_id] = htmlspecialchars(
                    trim((string) ($value ?? "")),
                    ENT_QUOTES,
                    "UTF-8",
                    false
                );
            }
            yield $row_builder->buildDataRow((string) $row_index, $record);
        }
    }

    public function getTotalRowCount(
        mixed $additional_viewcontrol_data,
        mixed $filter_data,
        mixed $additional_parameters
    ): ?int {
        return count($this->data);
    }
}
