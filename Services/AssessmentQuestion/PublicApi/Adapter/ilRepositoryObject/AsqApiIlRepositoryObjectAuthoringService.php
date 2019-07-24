<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Adapter\ilRepositoryObject;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringService;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringServiceSpec;
use ILIAS\UI\Component\Link\Link;

/**
 * Class AsqIlRepositoryObjectPublicAuthoringService
 *
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 * Service providing the needed Methods for Editing and Creating Questions
 */
class AsqApiIlRepositoryObjectAuthoringService implements AsqApiAuthoringService {

	/**
	 * @var AsqApiIlRepositoryObjectAuthoringServiceSpec
	 */
	protected $asq_authoring_spec;


	public function __construct(AsqApiAuthoringServiceSpec $asq_authoring_spec) {
		$this->asq_authoring_spec = $asq_authoring_spec;
	}


	public function GetQuestionsOfContainerAsAssocArray(): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
	}


	public function deleteQuestion(string $question_uuid): void {
		// TODO: Implement deleteQuestion() method.
	}


	public function GetEditConfigLink(string $question_uuid): Link {
		// TODO: Implement GetEditConfigLink() method.
	}


	public function getPreviewLink(string $question_uuid): Link {
		// TODO: Implement getPreviewLink() method.
	}


	public function getEdiPageLink(string $question_uuid): Link {
		// TODO: Implement getEdiPageLink() method.
	}


	public function getEditFeedbacksLink(string $question_uuid): Link {
		// TODO: Implement getEditFeedbacksLink() method.
	}


	public function getEditHintsLink(string $question_uuid): Link {
		// TODO: Implement getEditHintsLink() method.
	}


	public function getStatisticLink(string $question_uuid): Link {
		// TODO: Implement getStatisticLink() method.
	}
}