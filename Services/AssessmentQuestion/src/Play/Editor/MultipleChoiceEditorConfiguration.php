<?php

namespace ILIAS\AssessmentQuestion\Play\Editor;

use JsonSerializable;

/**
 * Class MultipleChoiceEditor
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class MultipleChoiceEditorConfiguration implements JsonSerializable {

	/**
	 * @var bool
	 */
	private $shuffle_answers;
	/**
	 * @var int
	 */
	private $max_answers;
	/**
	 * @var int
	 */
	private $thumbnail_size;

	/**
	 * MultipleChoiceEditor constructor.
	 *
	 * @param bool $shuffle_answers
	 * @param int  $max_answers
	 * @param int  $thumbnail_size
	 */
	public function __construct(bool $shuffle_answers = false, int $max_answers = 1, int $thumbnail_size = 0) {
		$this->shuffle_answers = $shuffle_answers;
		$this->max_answers = $max_answers;
		$this->thumbnail_size = $thumbnail_size;
	}


	/**
	 * @return bool
	 */
	public function isShuffleAnswers(): bool {
		return $this->shuffle_answers;
	}


	/**
	 * @return int
	 */
	public function getMaxAnswers(): int {
		return $this->max_answers;
	}


	/**
	 * @return int
	 */
	public function getThumbnailSize(): int {
		return $this->thumbnail_size;
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
