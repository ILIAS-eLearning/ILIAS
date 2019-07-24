<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use JsonSerializable;

interface AsqApiDtoUserAnswerContract {

	public function __construct(AsqApiIdContainerContract $asq_api_container_id, string $question_uuid, int $user_id, JsonSerializable $user_answer);
}