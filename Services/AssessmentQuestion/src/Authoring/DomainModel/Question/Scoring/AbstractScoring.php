<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Scoring;

use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Answer;
use ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Question;
use JsonSerializable;
use stdClass;

/**
 * Abstract Class AbstractScoring
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AbstractScoring {
	const SCORING_DEFINITION_SUFFIX = 'Definition';

	/**
	 * @var Question
	 */
	private $question;
	/**
	 * @var array
	 */
	private $configuration;


	/**
	 * AbstractScoring constructor.
	 *
	 * @param Question $question
	 * @param array    $configuration
	 */
	public function __construct(Question $question, array $configuration) {
		$this->question = $question;
		$this->configuration = $configuration;
	}

	abstract function score(Answer $answer);

	/**
	 * @return array|null
	 */
	public static function generateFields(): ?array {
		return null;
	}

	/**
	 * @return JsonSerializable|null
	 */
	public static function readConfig() : ?JsonSerializable {
		return null;
	}

	/**
	 * @param stdClass $input
	 *
	 * @return JsonSerializable|null
	 */
	public static function deserialize(stdClass $input = null) : ?JsonSerializable {
		return null;
	}


	/**
	 * @return string
	 */
	public static function getScoringDefinitionClass(): string {
		return get_called_class() . self::SCORING_DEFINITION_SUFFIX;
	}
}