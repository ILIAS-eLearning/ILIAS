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
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		return $DIC->assessment()->service()->query()->getQuestionQtiXml($a_id);
	}
}