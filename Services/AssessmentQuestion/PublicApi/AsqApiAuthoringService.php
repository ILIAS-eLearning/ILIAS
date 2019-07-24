<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Exception\AsqApiContainerIsNotResponsibleForQuestionException;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface AsqApiAuthoringService
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiAuthoringService {

	/**
	 * AsqApiAuthoringService constructor.
	 *
	 * @param AsqApiAuthoringServiceSpec $asq_authoring_spec
	 */
	public function __construct(AsqApiAuthoringServiceSpec $asq_authoring_spec);


	/**
	 * @return array
	 *
	 * Gets all questions of a Container from db as an Array containing
	 * the generic question data fields
	 */
	public function GetQuestionsOfContainerAsAssocArray():array;


	/**
	 * @param string $question_uuid
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function deleteQuestion(string $question_uuid):void;


	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function GetEditConfigLink(string $question_uuid): Link;

	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getPreviewLink(string $question_uuid): Link;


	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getEdiPageLink(string $question_uuid): Link;

	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getEditFeedbacksLink(string $question_uuid): Link;

	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getEditHintsLink(string $question_uuid): Link;

	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getStatisticLink(string $question_uuid): Link;
}