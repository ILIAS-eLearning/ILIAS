<?php

class QuestionData implements JsonSerializable {
	/**
	 * @var string
	 */
	private $title;
	/**
	 * @var string
	 */
	private $description;
	/**
	 * @var string
	 */
	private $question_text;

	public function __construct(string $title, string $description, string $text) {
		$this->title = $title;
		$this->description = $description;
		$this->question_text = $text;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}

	/**
	 * @return string
	 */
	public function getQuestionText(): string {
		return $this->question_text;
	}

	/**
	 * @param string $question_text
	 */
	public function setQuestionText(string $question_text): void {
		$this->question_text = $question_text;
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
}}