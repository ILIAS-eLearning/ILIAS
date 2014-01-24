<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Used for container export with tests
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesSurvey
 */
class ilSurveyExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
	}


	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		target release
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		$refs = ilObject::_getAllReferences($a_id);
		$svy_ref_id = current($refs);
		
		include_once './Modules/Survey/classes/class.ilObjSurvey.php';
		$svy = new ilObjSurvey($a_id,false);
		$svy->loadFromDb();
		
		include_once("./Modules/Survey/classes/class.ilSurveyExport.php");
		$svy_exp = new ilSurveyExport($svy, 'xml');
		$zip = $svy_exp->buildExportFile();
		
		// Unzip, since survey deletes this dir
		ilUtil::unzip($zip);
		
		$GLOBALS['ilLog']->write(__METHOD__.': Created zip file '.$zip);
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top.
	 *
	 * @return
	 */
	function getValidSchemaVersions($a_entity)
	{
		return array (
			"4.1.0" => array(
				"namespace" => "http://www.ilias.de/Modules/Survey/htlm/4_1",
				"xsd_file" => "ilias_svy_4_1.xsd",
				"uses_dataset" => false,
				"min" => "4.1.0",
				"max" => "")
		);
	}
}

?>