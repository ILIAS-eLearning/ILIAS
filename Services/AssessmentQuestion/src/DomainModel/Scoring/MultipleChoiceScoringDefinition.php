<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Scoring;

use ILIAS\AssessmentQuestion\DomainModel\Answer\Option\ScoringDefinition;
use ILIAS\AssessmentQuestion\UserInterface\Web\Form\Config\AnswerOptionFormFieldDefinition;
use stdClass;

/**
 * Class MultipleChoiceScoringDefinition
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceScoringDefinition extends ScoringDefinition {

	const VAR_MCSD_SELECTED = 'mcsd_selected';
	const VAR_MCSD_UNSELECTED = 'mcsd_unselected';

	/**
	 * @var int
	 */
	private $points_selected;
	/**
	 * @var int
	 */
	private $points_unselected;


	/**
	 * MultipleChoiceScoringDefinition constructor.
	 *
	 * @param int $points_selected
	 * @param int $points_unselected
	 */
	public function __construct(int $points_selected = 0, int $points_unselected = 0)
	{
		$this->points_selected = $points_selected;
		$this->points_unselected = $points_unselected;
	}


	/**
	 * @return int
	 */
	public function getPointsSelected(): int {
		return $this->points_selected;
	}

	/**
	 * @return int
	 */
	public function getPointsUnselected(): int {
		return $this->points_unselected;
	}

	/**
	 * Specify data which should be serialized to JSON
	 *
	 * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize() {
		return get_object_vars($this);
	}

	public static function getFields(): array {
	    $fields = [];
		$fields[] = new AnswerOptionFormFieldDefinition(
			'Checked',
			AnswerOptionFormFieldDefinition::TYPE_NUMBER,
			self::VAR_MCSD_SELECTED
		);

		$fields[] = new AnswerOptionFormFieldDefinition(
			'Unchecked',
			AnswerOptionFormFieldDefinition::TYPE_NUMBER,
			self::VAR_MCSD_UNSELECTED
		);

		return $fields;
	}

	public static function getValueFromPost(string $index) {
		return new MultipleChoiceScoringDefinition(
			$_POST[$index . self::VAR_MCSD_SELECTED],
			$_POST[$index . self::VAR_MCSD_UNSELECTED]
		);
	}

	public function getValues(): array {
		return [$this->points_selected, $this->points_unselected];
	}


	public static function deserialize(stdClass $data) {
		return new MultipleChoiceScoringDefinition(
			$data->points_selected,
			$data->points_unselected
		);
	}
}