<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssessmentQuestionExporter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 *
 * @package    Services/AssessmentQuestion
 */
class ilAssessmentQuestionExporter extends ilXmlExporter
{
	public function getValidSchemaVersions($a_entity)
	{
		/* export schema versions code */
	}
	
	public function init()
	{
		/* assessment question init code */
	}
	
	/**
	 * @param string $a_entity
	 * @param array $a_schema_version
	 * @param int $a_id
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		/**
		 * the assessment question export does simply get the id an returns
		 * the qti xml representation of the question.
		 */
		
		global $DIC; /* @var ILIAS\DI\Container $DIC */
		
		$questionInstance = $DIC->question()->getQuestionInstance($a_id);
		
		return $questionInstance->toQtiXML();
	}
}