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
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
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
