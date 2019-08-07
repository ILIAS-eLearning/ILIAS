<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Services\AssessmentQuestion\PublicApi\Authoring\AuthoringService;
use ILIAS\UI\Component\Link\Link;

/**
 * Class exQuestionPoolImporter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 * @version    $Id$
 *
 * @package    Modules/Test(QuestionPool)
 */
class exQuestionPoolImporter extends ilXmlImporter
{
	/**
	 * @var AuthoringService
	 */
	protected $authoring_service;


	public function __construct() {
		/* @var ILIAS\DI\Container $DIC */ global $DIC;

		$this->authoring_service = $DIC->assessment()->questionAuthoring($this->object->getId(), $DIC->user()->getId());
	}

	/**
	 * @param string $a_entity
	 * @param int $a_id
	 * @param string $a_xml
	 * @param array $a_mapping
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		/**
		 * here consumers can regularly process their own import stuff.
		 * 
		 * although the assessment questions are imported by declared tail depencies,
		 * any consumer component can import any overall qti xml file, that was added
		 * to the export by the consumer itself.
		 */
		$question_import = $this->authoring_service->questionImport();

		$question_import->importQtiItem($a_xml);

	}
	
	/**
	 * Final processing
	 *
	 * @param ilImportMapping $a_mapping
	 * @return
	 */
	function finalProcessing($a_mapping)
	{
		//TODO
	}
}