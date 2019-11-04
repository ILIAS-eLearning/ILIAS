<?php

namespace ILIAS\AssessmentQuestion\DomainModel\Answer;

use ILIAS\AssessmentQuestion\CQRS\Aggregate\Entity;
use JsonSerializable;

/**
 * Class Answer
 *
 * @package ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Option;
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class Answer implements Entity, JsonSerializable {

	/**
	 * @var int
	 */
	protected $answerer_id;
	/**
	 * @var string
	 */
	protected $question_id;
    /**
     * @var string
     */
    protected $revision_key;
	/**
	 * @var string
	 */
	protected $value;
	/**
	 * @var int
	 */
	protected $test_id;
    /**
     * @var int
     */
    protected $attempt_number;

	public function __construct(int $anwerer_id, string $question_id,  string $revision_key, int $test_id, int $attempt_number, string $value) {
		$this->answerer_id = $anwerer_id;
		$this->question_id = $question_id;
        $this->revision_key = $revision_key;
		$this->test_id = $test_id;
		$this->attempt_number = $attempt_number;

		$this->value = $value;
	}

	/**
	 * @return int
	 */
	public function getAnswererId(): int {
		return $this->answerer_id;
	}

	/**
	 * @return string
	 */
	public function getQuestionId(): string {
		return $this->question_id;
	}


    /**
     * @return string
     */
    public function getRevisionKey() : string
    {
        return $this->revision_key;
    }



	/**
	 * @return string
	 */
	public function getValue(): string {
		return $this->value;
	}

	/**
	 * @return string
	 */
	public function getTestId(): string {
		return $this->test_id;
	}


    /**
     * @return int
     */
    public function getAttemptNumber() : int
    {
        return $this->attempt_number;
    }


    /**
     * @param int $attempt_number
     */
    public function setAttemptNumber(int $attempt_number) : void
    {
        $this->attempt_number = $attempt_number;
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