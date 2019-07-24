<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerIdContract;
use ILIAS\UI\Component\Link\Link;

interface AsqApiPlayServiceSpecContract {

	public function __construct(AsqApiContainerIdContract $container_id, int $actor_user_id, Link $container_backlink, string $question_uuid, string $question_revision_uuid);


	public function withAdditionalButton();


	public function withQuestionActionLink();
}