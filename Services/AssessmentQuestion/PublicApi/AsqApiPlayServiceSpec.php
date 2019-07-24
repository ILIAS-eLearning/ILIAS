<?php

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiContainerId;
use ILIAS\UI\Component\Link\Link;

interface AsqApiPlayServiceSpec {

	public function __construct(AsqApiContainerId $container_id, int $actor_user_id, Link $container_backlink, string $question_uuid);

	public function withAdditionalButton();

	public function withQuestionActionLink();
}