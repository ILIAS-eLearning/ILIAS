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
	 * @param string $questionUuid
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function deleteQuestion(string $questionUuid): void;


	/**
	 * @param string $questionUuid
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditLink(string $questionUuid): Link;


	/**
	 * @param string $questionUuid
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getPreviewLink(string $questionUuid): Link;


	/**
	 * @param string $questionUuid
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditPageLink(string $questionUuid): Link;


	/**
	 * @param string $questionUuid
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditFeedbacksLink(string $questionUuid): Link;


	/**
	 * @param string $questionUuid
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getEditHintsLink(string $questionUuid): Link;


	/**
	 * @param string $questionUuid
	 * @return Link
	 *
	 * @throws ContainerIsNotResponsibleForQuestionException
	 */
	public function getStatisticLink(string $questionUuid): Link;


	/**
	 * @param RevisionIdContract $asq_api_id_revision
	 */
	public function publishNewRevision(RevisionIdContract $asq_api_id_revision):void;
	
	/**
	 * @param ilQtiItem $qtiItem
	 */
	public function importQtiItem(ilQtiItem $qtiItem): void;
	
	/**
	 * @param string $questionUuid
	 */
	public function changeQuestionContainer(string $questionUuid): void;
}