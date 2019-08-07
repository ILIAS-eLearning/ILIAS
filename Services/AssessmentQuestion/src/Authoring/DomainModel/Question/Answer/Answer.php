<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer;

use ILIAS\AssessmentQuestion\Common\DomainModel\Aggregate\Entity;
use JsonSerializable;

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
	protected $value;
	/**
	 * @var string
	 */
	protected $test_id;

	public function __construct(int $anwerer_id, string $question_id, string $test_id, string $value) {
		$this->answerer_id = $anwerer_id;
		$this->question_id = $question_id;
		$this->test_id = $test_id;
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