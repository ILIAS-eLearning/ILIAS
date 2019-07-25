<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;


use ILIAS\Services\AssessmentQuestion\PublicApi\AdditionalConfigSection;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AdditionalConfigSectionContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\UserAnswerSubmitContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\UserAnswerSubmit;
use ILIAS\Services\AssessmentQuestion\PublicApi\UserAnswerId;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionResourcesCollector;
use ilFormSectionHeaderGUI;
use ilFormPropertyGUI;
use JsonSerializable;


/**
 * Class Consumer
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Factory
 */
class Consumer
{
	/**
	 * @param string $questionUuid
	 * @return QuestionIdContract
	 */
	public function questionUuid($questionUuid = ''): QuestionIdContract
	{
		return new QuestionId($questionUuid);
	}
	
	/**
	 * @param string $userAnswerUuid
	 * @return UserAnswerIdContract
	 */
	public function userAnswerUuid($userAnswerUuid = ''): UserAnswerIdContract
	{
		return new UserAnswerId($userAnswerUuid);
	}
	
	/**
	 * @param UserAnswerIdContract $userAnswerUuid
	 * @param QuestionIdContract $questionUuid
	 * @param int $user_id
	 * @param JsonSerializable $user_answer
	 * @return UserAnswerSubmitContract
	 */
	public function userAnswerSubmit(
		UserAnswerIdContract $userAnswerUuid,
		QuestionIdContract $questionUuid,
		int $user_id,
		JsonSerializable $user_answer
	): UserAnswerSubmitContract
	{
		return new UserAnswerSubmit($userAnswerUuid, $questionUuid, $user_id, $user_answer);
	}
	
	/**
	 * @return QuestionResourcesCollector
	 */
	public function questionRessourcesCollector(): QuestionResourcesCollector
	{
		return new QuestionResourcesCollector();
	}
	
	/**
	 * @param ilFormSectionHeaderGUI $sectionHeader
	 * @param ilFormPropertyGUI[] $sectionInputs
	 * @return AdditionalConfigSectionContract
	 */
	public function questionConfigSection(
		ilFormSectionHeaderGUI $sectionHeader, array $sectionInputs
	): AdditionalConfigSectionContract
	{
		return new AdditionalConfigSection($sectionHeader, $sectionInputs);
	}
}
