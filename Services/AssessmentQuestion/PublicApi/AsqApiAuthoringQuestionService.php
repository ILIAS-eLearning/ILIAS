<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiAuthoringQuestionServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiAuthoringQuestionServiceSpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiRevisionIdContract;
use ILIAS\UI\Component\Link\Link;

/**
 * Interface AsqApiAuthoringQuestionServiceContract
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class AsqApiAuthoringQuestionService implements AsqApiAuthoringQuestionServiceContract {

	/**
	 * AsqApiAuthoringQuestionServiceContract constructor.
	 *
	 * @param AsqApiAuthoringQuestionServiceSpecContract $asq_authoring_spec
	 */
	public function __construct(AsqApiAuthoringQuestionServiceSpecContract $asq_authoring_spec) {

	}


	public function deleteQuestion(): void {
		// TODO: Implement deleteQuestion() method.
	}


	public function GetEditConfigLink(): Link {
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


	public function publishNewRevision(): AsqApiRevisionIdContract {
		// TODO: Implement publishNewRevision() method.
	}
}