<?php

/**
 * Class AsqAuthoringService
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * Service providing the needed Methods for Editing and Creating Questions
 */
class AsqAuthoringService
{

	/**
	 * AsqAuthoringSpec
	 */
	protected $asq_authoring_spec;

	//TODO
	public function __construct(AsqAuthoringSpec $asq_authoring_spec) {
		$this->asq_authoring_spec = $asq_authoring_spec;
	}

	/**
	 * @return QuestionDto
	 *
	 * Gets all questions of a Container (QuestionPool, Test) from DB
	 */
	//TODO
	public function GetQuestionsOfContainer() : QuestionDto[] {
		return $question_repository->getQuestionsOfParent($this->asq_authoring_spec->ge);
	}

	/**
	 *
	 * @return mixed
	 *
	 * Gets all questions of a Container from db as an Array containing
	 * the generic question data fields
	 */
	public function GetQuestionsOfContainerAsAssocArray() {
		//TODO
		return $question_repository->getQuestionsOfContainerAsArray(TODO $parent_id);

		//
	}

	//TODO
	public function deleteQuestion(string $question_uuid)

	/**
	 * @param string|null $question_uuid
	 *
	 * @return ILIAS\UI\Component\Link
	 *
	 * Gets the link to the question authoring component
	 */
	public function GetEditConfigLink(string $question_uuid) : \ILIAS\UI\Component\Link\Link;

	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getPreviewLink() : \ILIAS\UI\Component\Link\Link;


	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getEdiPageLink() : \ILIAS\UI\Component\Link\Link;

	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getEditFeedbacksLink() : \ILIAS\UI\Component\Link\Link;

	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getEditHintsLink() : \ILIAS\UI\Component\Link\Link;

	/**
	 * @return \ILIAS\UI\Component\Link\Link
	 */
	public function getStatisticLink() : \ILIAS\UI\Component\Link\Link;

}