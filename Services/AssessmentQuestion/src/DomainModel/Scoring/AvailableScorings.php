<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

/**
 * Class AvailableScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AvailableScorings {
	public static function getAvailableScorings() {
		//TODO get scorings from DB
		$scorings = [];
		$scorings[MultipleChoiceScoring::class] = "MultipleChoiceScoring";
		return $scorings;
	}

	public static function getDefaultScoring() {
		return MultipleChoiceScoring::class;
	}
}