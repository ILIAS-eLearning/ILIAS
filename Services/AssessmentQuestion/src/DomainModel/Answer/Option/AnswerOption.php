<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer\Option;

use JsonSerializable;
use stdClass;

/**
 * Interface AnswerOption
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AnswerOption implements JsonSerializable {

	const DISPLAY_DEF_CLASS = "ddclass";
	const SCORING_DEF_CLASS = "sdclass";

	/**
	 * @var string
	 */
	private $option_id;
	/**
	 * @var ?DisplayDefinition
	 */
	private $display_definition;
	/**
	 * @var ?ScoringDefinition
	 */
	private $scoring_definition;

	public function __construct(int $id, ?DisplayDefinition $display_definition = null, ?ScoringDefinition $scoring_definition = null)
	{
		$this->option_id = $id;
		$this->display_definition = $display_definition;
		$this->scoring_definition = $scoring_definition;
	}


	/**
	 * @return string
	 */
	public function getOptionId(): string {
		return $this->option_id;
	}


	/**
	 * @return mixed
	 */
	public function getDisplayDefinition() {
		return $this->display_definition;
	}


	/**
	 * @return mixed
	 */
	public function getScoringDefinition() {
		return $this->scoring_definition;
	}

	/**
	 * @return array
	 */
	public function rawValues() : array {
		$dd_fields = $this->display_definition !== null ? $this->display_definition->getValues() : [];
		$sd_fields = $this->scoring_definition !== null ? $this->scoring_definition->getValues() : [];

		return array_merge($dd_fields, $sd_fields);
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
		$vars = get_object_vars($this);
		$vars[self::DISPLAY_DEF_CLASS] = get_class($this->display_definition);
		$vars[self::SCORING_DEF_CLASS] = get_class($this->scoring_definition);
		return $vars;
	}

	public function deserialize(stdClass $option) {
		$dd_class = $option->{self::DISPLAY_DEF_CLASS};
		$this->display_definition = call_user_func(array($dd_class, 'deserialize'), $option->display_definition);

		$sd_class = $option->{self::SCORING_DEF_CLASS};
		$this->scoring_definition = call_user_func(array($sd_class, 'deserialize'), $option->scoring_definition);
	}
}
