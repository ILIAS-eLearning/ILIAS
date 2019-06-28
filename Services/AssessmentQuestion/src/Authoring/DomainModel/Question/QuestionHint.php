<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

/**
 * Class QuestionHint
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 */
class QuestionHint {

	/**
	 * @var string
	 */
	private $label_hint;
	/**
	 * @var float
	 */
	private $points;


	/**
	 * QuestionHint constructor.
	 *
	 * @param string $label_hint
	 * @param float  $points
	 */
	public function __construct(string $label_hint, float $points) {
		$this->label_hint = $label_hint;
		$this->points = $points;
	}


	/**
	 * @return string
	 */
	public function getLabelHint(): string {
		return $this->label_hint;
	}


	/**
	 * @return float
	 */
	public function getPoints(): float {
		return $this->points;
	}


}