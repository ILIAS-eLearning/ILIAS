<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiIdContainerContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiServicePlaySpecContract;
use ILIAS\UI\Component\Link\Link;

class AsqApiServicePlaySpec implements AsqApiServicePlaySpecContract {

	public function __construct(AsqApiIdContainerContract $container_id, int $actor_user_id, Link $container_backlink, string $question_uuid, string $question_revision_uuid) {

	}


	public function withAdditionalButton() {
		// TODO: Implement withAdditionalButton() method.
	}


	public function withQuestionActionLink() {
		// TODO: Implement withQuestionActionLink() method.
	}
}