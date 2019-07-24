<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiIdContainerContract;
use ILIAS\UI\Component\Link\Link;

interface AsqApiServicePlaySpecContract {

	public function withAdditionalButton();


	public function withQuestionActionLink();
}