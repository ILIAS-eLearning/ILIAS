<?php
namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question\Answer\Type;


use JsonSerializable;

class AnswerType implements AnswerTypeContract, JsonSerializable {
	protected $answer_type_id;

	public function __construct(string $answer_type_id) {
		$this->answer_type_id = $answer_type_id;
	}


	/**
	 * @return string
	 */
	public function getAnswerType(): string {
		return $this->answer_type_id;
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