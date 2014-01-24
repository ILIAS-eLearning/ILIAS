<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Used for container export with tests
 *
 * @author Helmut Schottmüller <ilias@aurealis.de>
 * @version $Id$
 * @ingroup ModulesTest
 */
class ilTestQuestionPoolExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
	}

	/**
	 * Overwritten for qpl
	 * @param string $a_obj_type
	 * @param int $a_obj_id
	 * @param string $a_export_type 
	 */
	public static function  lookupExportDirectory($a_obj_type, $a_obj_id, $a_export_type = 'xml', $a_entity = "")
	{
		if($a_export_type == 'xml')
		{
			return ilUtil::getDataDir() . "/qpl_data" . "/qpl_" . $a_obj_id . "/export_zip";

		}
		return ilUtil::getDataDir()."/qpl_data"."/qpl_".$a_obj_id."/export_".$a_export_type;
	}


	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		schema version
	 * @param	string		id
	 * @return	string		xml string
	 */
	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		include_once './Modules/TestQuestionPool/classes/class.ilObjQuestionPool.php';
		$qpl = new ilObjQuestionPool($a_id,false);

		include_once("./Modules/TestQuestionPool/classes/class.ilQuestionpoolExport.php");
		$qpl_exp = new ilQuestionpoolExport($qpl, 'xml');
		$zip = $qpl_exp->buildExportFile();
		
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
				"namespace" => "http://www.ilias.de/Modules/TestQuestionPool/htlm/4_1",
				"xsd_file" => "ilias_qpl_4_1.xsd",
				"uses_dataset" => false,
				"min" => "4.1.0",
				"max" => "")
		);
	}

}

?>