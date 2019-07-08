<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\AnswerOptionScoring;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\Points\AnswerOptionScoringPointsWhenNotSelected;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Scoring\Points\AnswerOptionScoringPointsWhenSelected;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\AnswerOptionValuePredefined;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format\AnswerOptionValueFormatInHtml;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format\AnswerOptionValueFormatInImage;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option\Value\Format\AnswerOptionValueFormatInText;
use ilObjMediaObject;

class AnswerOptionFactory {

	/**
	 * @param string           $answer_text
	 * @param ilObjMediaObject $answer_image
	 * @param int              $points_selected
	 * @param int              $points_not_selected
	 *
	 * @return AnswerOption
	 */
	public function createNewMultipleChoiceSingleLineAnswerOption(string $answer_text, ilObjMediaObject $answer_image, int $points_selected, int $points_not_selected): AnswerOption {

		$answer_option_values = [];

		$answer_option_in_format = new AnswerOptionValueFormatInText($answer_text);
		$answer_option_values[] = new AnswerOptionValuePredefined($answer_option_in_format);

		$answer_option_in_format = new AnswerOptionValueFormatInImage($answer_image);
		$answer_option_values[] = new AnswerOptionValuePredefined($answer_option_in_format);

		$points = [];
		$points[] = new AnswerOptionScoringPointsWhenSelected($points_selected);
		$points[] = new AnswerOptionScoringPointsWhenNotSelected($points_not_selected);

		$scoring = new AnswerOptionScoring($points);

		return new AnswerOptionWithScoring($answer_option_values, $scoring);
	}


	/**
	 * @param string $answer_text
	 * @param int    $points_selected
	 * @param int    $points_not_selected
	 *
	 * @return AnswerOptionWithScoring
	 * nswer
	 */
	public function createNewMultipleChoiceMultiLineAnswerOption(string $answer_text, int $points_selected, int $points_not_selected): AnswerOptionWithScoring {

		$answer_option_values = [];
		$answer_option_in_format = new AnswerOptionValueFormatInHtml($answer_text);
		$answer_option_values[] = new AnswerOptionValuePredefined($answer_option_in_format);

		$points = [];
		$points[] = new AnswerOptionScoringPointsWhenSelected($points_selected);
		$points[] = new AnswerOptionScoringPointsWhenNotSelected($points_not_selected);

		$scoring = new AnswerOptionScoring($points);

		return new AnswerOptionWithScoring($answer_option_values, $scoring);
	}
}