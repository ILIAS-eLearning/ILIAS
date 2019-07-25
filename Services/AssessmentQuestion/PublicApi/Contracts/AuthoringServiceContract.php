<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\Exception\ContainerIsNotResponsibleForQuestionException;
use ILIAS\UI\Component\Link\Link;
use ilQtiItem;

/**
 * Interface QuestionAuthoringServiceContract
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AuthoringServiceContract {

	/**
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function deleteQuestion(string $questionUuid): void;


	/**
	 *
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditLink(): Link;


	/**
	 *
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getPreviewLink(): Link;


	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditPageLink(): Link;


	/**
	 *
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditFeedbacksLink(): Link;


	/**
	 *
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditHintsLink(): Link;


	/**
	 *
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getStatisticLink(): Link;


	/**
	 * @param RevisionIdContract $asq_api_id_revision
	 */
	public function publishNewRevision(RevisionIdContract $asq_api_id_revision):void;
	
	/**
	 * @param ilQtiItem $qtiItem
	 */
	public function importQtiItem(ilQtiItem $qtiItem): void;
}