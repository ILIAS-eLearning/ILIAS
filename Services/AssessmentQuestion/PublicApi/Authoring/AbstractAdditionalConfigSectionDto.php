<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Authoring;

use ILIAS\Services\AssessmentQuestion\PublicApi\AdditionalConfigSection;
use ilPropertyFormGUI;
use ilFormSectionHeaderGUI;
use ilFormPropertyGUI;

/**
 * Class AbstractAdditionalConfigSection
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class AdditionalConfigSectionDto  {
	/**
	 * @var ilFormSectionHeaderGUI
	 */
	protected $sectionHeader;
	
	/**
	 * @var ilFormPropertyGUI[]
	 */
	protected $sectionInputs = [];
	
	/**
	 * AdditionalConfigSection constructor.
	 * @param ilFormSectionHeaderGUI $sectionHeader
	 * @param ilFormPropertyGUI[] $sectionInputs
	 */
	public function __construct(ilFormSectionHeaderGUI $sectionHeader)
	{
		$this->sectionHeader = $sectionHeader;
	}

	public function appendSectionInput(ilFormPropertyGUI $section_input):void {
		$sectionInputs[] = $section_input;
	}
}
