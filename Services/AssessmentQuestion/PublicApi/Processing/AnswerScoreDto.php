<?php
declare(strict_types=1);

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

/**
 * Interface AnswerScoreDto
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Processing
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AnswerScoreDto
{

    public static function createNew(
        float $max_points,
        float $reached_points,
        int $hint_points,
        int $requested_hints,
        int $percent_solved,
        $answer_feedback_type
    ): AnswerScoreDto;

    /**
     * @return int
     */
    public function getRequestedHints() : int;

    /**
     * @return int
     */
    public function getHintPoints() : int;



    /**
     * @return float
     */
    public function getMaxPoints() : float;


    /**
     * @return float
     */
    public function getReachedPoints() : float;


    /**
     * @return int
     */
    public function getPercentSolved() : int;


    /**
     * @return int
     */
    public function getAnswerFeedbackType() : int;
}
