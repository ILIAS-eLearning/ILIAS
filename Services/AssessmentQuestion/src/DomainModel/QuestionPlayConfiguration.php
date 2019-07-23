<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use JsonSerializable;

/**
 * Class QuestionPlayConfiguration
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 */
class QuestionPlayConfiguration implements JsonSerializable{

	/**
	 * @var string
	 */
	private $presenter_class;
	/**
	 * @var string
	 */
	private $editor_class;
	/**
	 * @var JsonSerializable
	 */
	private $editor_configuration;
	/**
	 * @var string
	 */
	private $scoring_class;
	/**
	 * @var int Working time in seconds
	 */
	private $working_time;


	/**
	 * QuestionPlayConfiguration constructor.
	 *
	 * @param string                $presenter_class
	 * @param string                $editor_class
	 * @param string                $scoring_class
	 * @param int                   $working_time
	 * @param JsonSerializable|null $editor_configuration
	 */
	public function __construct(
		string $presenter_class,
		string $editor_class,
		string $scoring_class,
		int $working_time,
		JsonSerializable $editor_configuration = null
	) {
		$this->presenter_class = $presenter_class;
		$this->editor_class = $editor_class;
		$this->scoring_class = $scoring_class;
		$this->working_time = $working_time;
		$this->editor_configuration = $editor_configuration;
	}

	/**
	 * @return string
	 */
	public function getPresenterClass(): string {
		return $this->presenter_class;
	}

	/**
	 * @return string
	 */
	public function getEditorClass(): string {
		return $this->editor_class;
	}

	/**
	 * @return string
	 */
	public function getScoringClass(): string {
		return $this->scoring_class;
	}

	/**
	 * @return int
	 */
	public function getWorkingTime(): int {
		return $this->working_time;
	}

	/**
	 * @return JsonSerializable
	 */
	public function getEditorConfiguration(): ?JsonSerializable {
		return $this->editor_configuration;
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
}