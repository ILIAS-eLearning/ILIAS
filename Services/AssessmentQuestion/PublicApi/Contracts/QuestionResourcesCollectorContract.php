<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */


namespace ILIAS\Services\AssessmentQuestion\PublicApi\Contracts;

/**
 * Interface QuestionResourcesCollectorContract
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi\Contracts
 */
interface QuestionResourcesCollectorContract
{
	/**
	 * @return array
	 */
	public function getMobs(): array;
	
	/**
	 * @return array
	 */
	public function getMediaFiles(): array;
	
	/**
	 * @return array
	 */
	public function getJsFiles(): array;
}