<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiComponentQuestionContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiServicePlayContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiServicePlaySpecContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiDtoScoringContract;
use ILIAS\UI\Component\Component;

/**
 * Interface AsqApiServicePlay
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AsqApiServicePlay implements AsqApiServicePlayContract {

	public function __construct(AsqApiServicePlaySpecContract $asq_api_service_play_spec) {
		//TODO
	}


	public function GetQuestionPresentation(): AsqApiComponentQuestionContract {
		// TODO: Implement GetQuestionPresentation() method.
	}


	public function GetStandaloneQuestionExportPresentation(): Component {
		// TODO: Implement GetStandaloneQuestionExportPresentation() method.
	}


	public function GetUserAnswerPresentation(int $user_answer_id): AsqApiComponentQuestionContract {
		// TODO: Implement GetUserAnswerPresentation() method.
	}


	public function getGenericFeedbackOutput(int $user_answer_id): Component {
		// TODO: Implement getGenericFeedbackOutput() method.
	}


	public function getSpecificFeedbackOutput(int $user_answer_id): Component {
		// TODO: Implement getSpecificFeedbackOutput() method.
	}


	public function SaveUserAnswer(\ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AsqApiDtoUserAnswerContract $user_answer): int {
		// TODO: Implement SaveUserAnswer() method.
	}


	public function GetUserScore(int $user_answer_id): AsqApiDtoScoringContract {
		// TODO: Implement GetUserScore() method.
	}


	public function GetUserScoreOfBestScoredAnswer(int $user_id): AsqApiDtoScoringContract {
		// TODO: Implement GetUserScoreOfBestScoredAnswer() method.
	}


	public function GetUserScoreLastOfSubmittedAnswer(int $user_id): AsqApiDtoScoringContract {
		// TODO: Implement GetUserScoreLastOfSubmittedAnswer() method.
	}
}