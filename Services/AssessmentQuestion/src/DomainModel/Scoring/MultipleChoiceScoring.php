<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;
use ILIAS\AssessmentQuestion\DomainModel\AnswerScoreDto;

/**
 * Class MultipleChoiceScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceScoring extends AbstractScoring {

	function score(Answer $answer) : AnswerScoreDto {

        $reached_points = 0;
        $max_points = 0;

		$selected_options = json_decode($answer->getValue(), true);


		/** @var AnswerOption $answer_option */
        foreach ($this->question->getAnswerOptions()->getOptions() as $answer_option) {
            $max_points += $answer_option->getScoringDefinition()->getPointsSelected();
            $max_points += $answer_option->getScoringDefinition()->getPointsUnselected();
		    if(in_array($answer_option->getOptionId(), $selected_options)) {
                $reached_points += $answer_option->getScoringDefinition()->getPointsSelected();
		    } else {
                $reached_points += $answer_option->getScoringDefinition()->getPointsUnselected();
		    }
		}

        return new AnswerScoreDto($reached_points,$max_points,$this->getAnswerFeedbackType($reached_points,$max_points));
	}
	
    public static function readConfig()
    {
        return new MultipleChoiceScoringConfiguration();
    }

}