<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Factory;


use ILIAS\Services\AssessmentQuestion\PublicApi\AdditionalConfigSection;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AdditionalConfigSectionContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\QuestionIdContract;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionId;
use ILIAS\Services\AssessmentQuestion\PublicApi\QuestionResourcesCollector;
use ilFormSectionHeaderGUI;
use ilFormPropertyGUI;


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
