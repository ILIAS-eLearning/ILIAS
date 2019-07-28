<?php

namespace ILIAS\AssessmentQuestion\Authoring\Infrastructure\Persistence\ilDB;

use ActiveRecord;

class QuestionListItem extends ActiveRecord {

	const STORAGE_NAME = "asq_question_list_item";

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return self::STORAGE_NAME;
	}

	/**
	 * @var int
	 *
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     8
	 * @con_sequence   true
	 */
	protected $id;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     200
	 * @con_index      true
	 * @con_is_notnull true
	 */
	protected $question_id;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     200
	 * @con_is_notnull true
	 */
	protected $title;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     400
	 */
	protected $description;
	/**
	 * @var string
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     200
	 */
	protected $question;


	/**
	 * @return int
	 */
	public function getId(): int {
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId(int $id): void {
		$this->id = $id;
	}


	/**
	 * @return string
	 */
	public function getQuestionId(): string {
		return $this->question_id;
	}


	/**
	 * @param string $question_id
	 */
	public function setQuestionId(string $question_id): void {
		$this->question_id = $question_id;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string {
		return $this->title;
	}


	/**
	 * @param string $title
	 */
	public function setTitle(string $title): void {
		$this->title = $title;
	}


	/**
	 * @return string
	 */
	public function getDescription(): string {
		return $this->description;
	}


	/**
	 * @param string $description
	 */
	public function setDescription(string $description): void {
		$this->description = $description;
	}


	/**
	 * @return string
	 */
	public function getQuestion(): string {
		return $this->question;
	}


	/**
	 * @param string $question
	 */
	public function setQuestion(string $question): void {
		$this->question = $question;
	}
}