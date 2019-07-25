<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	}
	
	/**
	 * Final processing
	 *
	 * @param ilImportMapping $a_mapping
	 * @return
	 */
	function finalProcessing($a_mapping)
	{
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$maps = $a_mapping->getMappingsOfEntity("Modules/TestQuestionPool", "qpl");
		
		foreach ($maps as $old => $new)
		{
			if ($old != "new_id" && (int) $old > 0)
			{
				$authoringSecification = $DIC->assessment()->specification()->authoring(
					$new,
					$DIC->user()->getId(),
					$DIC->ui()->factory()->link()->standard('', '')
				);
				
				$newQstIds = $a_mapping->getMapping("Services/AssessmentQuestion", "qst", $old);
				
				if($newQstIds !== false)
				{
					$qstIds = explode(":", $newQstIds);
					foreach($qstIds as $qId)
					{
						$authoringService = $DIC->assessment()->service()->authoring(
							$authoringSecification, $DIC->assessment()->consumer()->questionUuid($qId)
						);
						
						// updates the parent container id since the imported container has a new one
						$authoringService->changeQuestionContainer();
					}
				}
				
				$qstMappings = $a_mapping->getMappingsOfEntity('Services/AssessmentQuestion', 'qst');
				
				foreach($qstMappings as $oldQstId => $newQstId)
				{
					// process all question ids within the consumer component database,
					// look for the old qst id and map to the new qst id
				}
			}
		}
	}
}