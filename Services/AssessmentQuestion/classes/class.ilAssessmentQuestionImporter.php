<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAssessmentQuestionImporter
 *
 * @author    Björn Heyser <info@bjoernheyser.de>
 *
 * @package    Services/AssessmentQuestion
 */
class ilAssessmentQuestionImporter extends ilXmlImporter
{
	/**
	 * @param string $a_entity
	 * @param int $a_id
	 * @param string $a_xml
	 * @param array $a_mapping
	 */
	public function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		global $DIC; /* @var \ILIAS\DI\Container $DIC */
		
		// TODO:
		// - parse the given xml to ilQtiItems
		// - get parent container corresponding authoring service from DIC
		// - import the ilQtiItems
	}
}