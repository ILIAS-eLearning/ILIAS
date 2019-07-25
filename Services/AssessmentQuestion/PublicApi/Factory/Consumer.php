<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;


use ILIAS\Services\AssessmentQuestion\PublicApi\AdditionalConfigSection;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AdditionalConfigSectionContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionResourcesCollector;
use ilFormSectionHeaderGUI;
use ilFormPropertyGUI;


/**
 * Class Consumer
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class Consumer
{
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
