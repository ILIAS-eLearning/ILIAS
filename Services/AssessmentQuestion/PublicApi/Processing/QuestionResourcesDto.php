<?php

namespace ILIAS\Services\AssessmentQuestion\PublicApi\Processing;

/**
 * Interface QuestionResourcesDto
 *
 * @package ILIAS\Services\AssessmentQuestion\PublicApi
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 * @author  Adrian Lüthi <al@studer-raimann.ch>
 * @author  Björn Heyser <bh@bjoernheyser.de>
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
interface QuestionResourcesDto  {

	/**
	 * QuestionResourcesCollector constructor
	 */
	public function __construct();


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
	public function getJsFiles(): array ;


	/**
	 * @return array
	 */
	public function getCssFiles(): array;

}
