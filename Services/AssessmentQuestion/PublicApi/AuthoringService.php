<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\UI\Component\Link\Link;
use ilQtiItem;

/**
 * Interface QuestionAuthoringService
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AuthoringService implements AuthoringServiceContract {

	/**
	 * QuestionAuthoringService constructor.
	 *
	 * @param AuthoringServiceSpecContract $asq_authoring_spec
	 */
	public function __construct(AuthoringServiceSpecContract $asq_authoring_spec) {

	}

	/**
	 * @param string $questionUuid
	 */
	public function deleteQuestion(string $questionUuid): void {
		// TODO: Implement deleteQuestion() method.
	}


	/**
	 * @param string $questionUuid
	 * @return Link
	 */
	public function getEditLink(string $questionUuid): Link {
		// TODO: Implement GetEditConfigLink() method.
	}


	/**
	 * @param string $questionUuid
	 * @return Link
	 */
	public function getPreviewLink(string $questionUuid): Link {
		// TODO: Implement getPreviewLink() method.
	}


	/**
	 * @param string $questionUuid
	 * @return Link
	 */
	public function getEditPageLink(string $questionUuid): Link {
		// TODO: Implement getEdiPageLink() method.
	}


	/**
	 * @param string $questionUuid
	 * @return Link
	 */
	public function getEditFeedbacksLink(string $questionUuid): Link {
		// TODO: Implement getEditFeedbacksLink() method.
	}


	/**
	 * @param string $questionUuid
	 * @return Link
	 */
	public function getEditHintsLink(string $questionUuid): Link {
		// TODO: Implement getEditHintsLink() method.
	}


	/**
	 * @param string $questionUuid
	 * @return Link
	 */
	public function getStatisticLink(string $questionUuid): Link {
		// TODO: Implement getStatisticLink() method.
	}


	/**
	 * @param RevisionIdContract $asq_api_id_revision
	 */
	public function publishNewRevision(RevisionIdContract $asq_api_id_revision): void {
		// TODO: Implement publishNewRevision() method.
	}
	
	/**
	 * @param ilQtiItem $qtiItem
	 */
	public function importQtiItem(ilQtiItem $qtiItem): void
	{
		// TODO: implement
	}
	
	public function changeQuestionContainer(string $questionUuid): void
	{
		// TODO: Implement changeQuestionContainer() method.
	}
}