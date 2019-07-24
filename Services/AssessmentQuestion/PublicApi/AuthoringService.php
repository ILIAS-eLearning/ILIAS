<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AuthoringServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\RevisionIdContract;
use ILIAS\UI\Component\Link\Link;

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
	 *
	 */
	public function deleteQuestion(): void {
		// TODO: Implement deleteQuestion() method.
	}


	/**
	 * @return Link
	 */
	public function getEditLink(): Link {
		// TODO: Implement GetEditConfigLink() method.
	}


	/**
	 * @return Link
	 */
	public function getPreviewLink(): Link {
		// TODO: Implement getPreviewLink() method.
	}


	/**
	 * @return Link
	 */
	public function getEditPageLink(): Link {
		// TODO: Implement getEdiPageLink() method.
	}


	/**
	 * @return Link
	 */
	public function getEditFeedbacksLink(): Link {
		// TODO: Implement getEditFeedbacksLink() method.
	}


	/**
	 * @return Link
	 */
	public function getEditHintsLink(): Link {
		// TODO: Implement getEditHintsLink() method.
	}


	/**
	 * @return Link
	 */
	public function getStatisticLink(): Link {
		// TODO: Implement getStatisticLink() method.
	}


	/**
	 * @param RevisionIdContract $asq_api_id_revision
	 */
	public function publishNewRevision(RevisionIdContract $asq_api_id_revision): void {
		// TODO: Implement publishNewRevision() method.
	}
}