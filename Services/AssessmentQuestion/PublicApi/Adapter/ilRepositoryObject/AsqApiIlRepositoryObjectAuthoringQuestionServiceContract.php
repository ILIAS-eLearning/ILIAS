<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Adapter\ilRepositoryObject;

use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQuestionServiceContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\AsqApiAuthoringQuestionServiceSpecInterface;
use ILIAS\Services\AssessmentQuestion\PublicApi\Exception\AsqApiContainerIsNotResponsibleForQuestionException;
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
class AsqApiIlRepositoryObjectAuthoringQuestionServiceContract implements AsqApiAuthoringQuestionServiceContract {

	/**
	 * @var AsqApiIlRepositoryObjectAuthoringQuestionServiceSpecInterface
	 */
	protected $asq_authoring_spec;


	/**
	 * AsqApiIlRepositoryObjectAuthoringQuestionServiceContract constructor.
	 *
	 * @param AsqApiIlRepositoryObjectAuthoringQuestionServiceSpecInterface $asq_authoring_spec
	 */
	public function __construct(AsqApiAuthoringQuestionServiceSpecInterface $asq_authoring_spec) { $this->asq_authoring_spec = $asq_authoring_spec; }


	public function GetQuestionsOfContainerAsAssocArray(): array {
		// TODO: Implement GetQuestionsOfContainerAsAssocArray() method.
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
}