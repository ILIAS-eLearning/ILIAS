<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use JsonSerializable;

interface AsqApiUserAnswerDto {

	public function __construct(AsqApiContainerId $asq_api_container_id, string $question_uuid, int $user_id, JsonSerializable $user_answer);
}