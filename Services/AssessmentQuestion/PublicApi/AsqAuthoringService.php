<?php

/**
 * Class AsqAuthoringService
 *
 * Service providing the needed Methods for Editing and Creating Questions
 */
class AsqAuthoringService
{
	private $QuestionRepository;


	/**
	 * @param string $question_uuid
	 *
	 * @return QuestionDto
	 *
	 * Get a single question from the DB by ID
	 */
	public function GetQuestion(string $question_uuid) : QuestionDto {
		return $question_repository->getQuestion($question_uuid);
	}


	/**
	 * @param string $parent_id
	 *
	 * @return QuestionDto
	 *
	 * Gets all questions of a Container (QuestionPool, Test) from DB
	 */
	public function GetQuestionsOfContainer(string $parent_id) : QuestionDto[] {
		return $question_repository->getQuestionsOfParent($parent_id);
	}

	/**
	 * @param string          $parent_id
	 * @param AsqColumns|null $columns
	 *
	 * @return mixed
	 *
	 * Gets all questions of a Container from db as an Array containing
	 * the columns defined in the $columns parameter
	 */
	public function GetQuestionsOfContainerAsArray(string $parent_id, AsqColumns $columns) {
		return $question_repository->getQuestionsOfContainerAsArray($parent_id, $columns);
	}

	/**
	 * @param string|null $question_uuid
	 *
	 * @return QuestionAuthoringGUI
	 *
	 * Gets the Authoring GUI used for editing questions if a question_id is set
	 * that question will be loaded in the form
	 */
	public function GetQuestionAuthoringGUI(string $question_uuid = null) : QuestionAuthoringGUI {
		if (is_null($question_uuid)) {
			$question = new QuestionDto();
		} else {
			$question = $question_repository->getQuestion($question_uuid);
		}

		return new QuestionAuthoringGUI($question);
	}

	/**
	 * @param QuestionDto $question
	 *
	 * Saves question to DB
	 */
	public function SaveQuestion(QuestionDto $question) {
		$question_repository->save($question);
	}
}