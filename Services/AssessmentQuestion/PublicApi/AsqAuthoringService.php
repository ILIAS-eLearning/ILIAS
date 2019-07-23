<?php

/**
 * Class AsqAuthoringService
 *
 * Service providing the needed Methods for Editing and Creating Questions
 */
class AsqAuthoringService
{

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
	 *
	 * @return mixed
	 *
	 * Gets all questions of a Container from db as an Array containing
	 * the generic question data fields
	 */
	public function GetQuestionsOfContainerAsAssocArray(string $parent_id) {
		return $question_repository->getQuestionsOfContainerAsArray($parent_id);
	}

	/**
	 * @param string|null $question_uuid
	 *
	 * @return ILIAS\UI\Component\Link
	 *
	 * Gets the link to the question authoring component
	 */
	public function GetEditLink(string $question_uuid) : \ILIAS\UI\Component\Link\Link {

	}

}