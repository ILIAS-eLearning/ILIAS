<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Answer;

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

	function score(Answer $answer) : int {
		$selected_options = json_decode($answer->getValue(), true);
		
		$score = 0;

		/** @var AnswerOption $answer_option */
        foreach ($this->question->getAnswerOptions()->getOptions() as $answer_option) {
		    if(in_array($answer_option->getOptionId(), $selected_options)) {
		        $score += $answer_option->getScoringDefinition()->getPointsSelected();
		    } else {
		        $score += $answer_option->getScoringDefinition()->getPointsUnselected();
		    }
		}
		
		return $score;
	}
	
    public static function readConfig()
    {
        return new MultipleChoiceScoringConfiguration();
    }

}