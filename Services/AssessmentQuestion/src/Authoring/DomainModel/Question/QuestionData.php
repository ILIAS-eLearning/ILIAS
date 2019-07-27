<?php

namespace ILIAS\AssessmentQuestion\Authoring\DomainModel\Question;

use JsonSerializable;

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
	/**
	 * @var string
	 */
	private $author;


	/**
	 * QuestionData constructor.
	 *
	 * @param string $title
	 * @param string $description
	 * @param string $text
	 * @param string $author
	 */
	public function __construct(string $title, string $description, string $text, string $author) {
		$this->title = $title;
		$this->description = $description;
		$this->question_text = $text;
		$this->author = $author;
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

	public function getAuthor(): string {
		return $this->author;
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