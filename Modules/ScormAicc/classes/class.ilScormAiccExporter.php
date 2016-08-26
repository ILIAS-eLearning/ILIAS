<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */
include_once("./Services/Export/classes/class.ilXmlExporter.php");
class ilScormAiccExporter extends ilXmlExporter
{
	public function __construct()
	{
		include_once("./Modules/ScormAicc/classes/class.ilScormAiccDataSet.php");
		$this->dataset = new ilScormAiccDataSet();
	}

	function init()
	{
	}

	public function getXmlRepresentation($a_entity, $a_schema_version, $a_id)
	{
		$this->dataset->setExportDirectories($this->dir_relative, $this->dir_absolute);
		//using own getXmlRepresentation function in ilSAHSDataSet
		return $this->dataset->getExtendedXmlRepresentation($a_entity, $a_schema_version, $a_id, "", false, true);
	}

	//todo:check if xsd files must be provided
	function getValidSchemaVersions($a_entity)
	{
		return array (
			"4.3.0" => array(
				"namespace" => "http://www.ilias.de/Modules/ScormAicc/sahs/4_3",
				"xsd_file" => "ilias_mod_4_3.xsd",
				"uses_dataset" => true,
				"min" => "4.3.0",
				"max" => "4.4.99"),
			"4.5.0" => array(
				"namespace" => "http://www.ilias.de/Modules/ScormAicc/sahs/4_5",
				"xsd_file" => "ilias_mod_4_5.xsd",
				"uses_dataset" => true,
				"min" => "4.5.0",
				"max" => "")
		);
	}

	public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		$md_ids = array();
		foreach ($a_ids as $mob_id)
		{
			$md_ids[] = "0:".$mob_id.":mob";
		}
		return array (
			array(
				"component" => "Modules/ScormAicc",
				"entity" => "md",
				"ids" => $md_ids)
			);
	}
	
}
?>
