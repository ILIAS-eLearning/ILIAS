<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionAuthoringServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionAuthoringServiceSpecContract;
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
class QuestionAuthoringService implements QuestionAuthoringServiceContract {

	/**
	 * QuestionAuthoringService constructor.
	 *
	 * @param QuestionAuthoringServiceSpecContract $asq_authoring_spec
	 */
	public function __construct(QuestionAuthoringServiceSpecContract $asq_authoring_spec) {

	}


	public function deleteQuestion(): void {
		// TODO: Implement deleteQuestion() method.
	}


	public function GetEditLink(): Link {
		// TODO: Implement GetEditConfigLink() method.
	}


	public function getPreviewLink(): Link {
		// TODO: Implement getPreviewLink() method.
	}


	public function getEdiPageLink(): Link {
		// TODO: Implement getEdiPageLink() method.
	}


	public function getEditFeedbacksLink(): Link {
		// TODO: Implement getEditFeedbacksLink() method.
	}


	public function getEditHintsLink(): Link {
		// TODO: Implement getEditHintsLink() method.
	}


	public function getStatisticLink(): Link {
		// TODO: Implement getStatisticLink() method.
	}


	public function publishNewRevision(RevisionIdContract $asq_api_id_revision): void {
		// TODO: Implement publishNewRevision() method.
	}
}