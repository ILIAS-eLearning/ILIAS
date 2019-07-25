<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi;

use ILIAS\Services\AssessmentQuestion\PublicApi\Contracts\AdditionalConfigSectionContract;
use ilPropertyFormGUI;
use ilFormSectionHeaderGUI;
use ilFormPropertyGUI;

/**
 * Class AdditionalConfigSection
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class AdditionalConfigSection implements AdditionalConfigSectionContract
{
	/**
	 * @var ilFormSectionHeaderGUI
	 */
	protected $sectionHeader;
	
	/**
	 * @var ilFormPropertyGUI[]
	 */
	protected $sectionInputs;
	
	/**
	 * AdditionalConfigSection constructor.
	 * @param ilFormSectionHeaderGUI $sectionHeader
	 * @param ilFormPropertyGUI[] $sectionInputs
	 */
	public function __construct(ilFormSectionHeaderGUI $sectionHeader, array $sectionInputs)
	{
		$this->sectionHeader = $sectionHeader;
		$this->sectionInputs = $sectionInputs;
	}
	
	/**
	 * @param ilPropertyFormGUI $formGUI
	 */
	public function completeForm(ilPropertyFormGUI $formGUI): void
	{
		$formGUI->addItem($this->sectionHeader);
		
		foreach($this->sectionInputs as $sectionInput)
		{
			$formGUI->addItem($sectionInput);
		}
	}
}
