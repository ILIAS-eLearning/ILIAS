<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiRevisionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\Exception\AsqApiContainerIsNotResponsibleForQuestionException;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface AsqApiAuthoringQuestionService
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface AsqApiAuthoringQuestionService {

	/**
	 * AsqApiAuthoringQuestionService constructor.
	 *
	 * @param AsqApiAuthoringQuestionServiceSpec $asq_authoring_spec
	 */
	//TODO remove construct in Interface?
	public function __construct(AsqApiAuthoringQuestionServiceSpec $asq_authoring_spec);


	/**
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function deleteQuestion():void;


	/**
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function GetEditConfigLink(): Link;

	/**
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getPreviewLink(): Link;


	/**
	 * @param string $question_uuid
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getEdiPageLink(): Link;

	/**
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getEditFeedbacksLink(): Link;

	/**
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getEditHintsLink(): Link;

	/**
	 *
	 * @return Link
	 *
	 * @throws AsqApiContainerIsNotResponsibleForQuestionException
	 */
	public function getStatisticLink(): Link;


	/**
	 * @return AsqApiRevisionId
	 */
	public function publishNewRevision(): AsqApiRevisionId;
}