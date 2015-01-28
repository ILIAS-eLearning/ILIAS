<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Export class for adv md
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id: $
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMetaDataExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		
	}

	/**
	 * Get head dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
	{
		return array();
	}


	/**
	 * Get tail dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		return array();
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
		global $ilDB;
		
		$parts = explode(":", $a_id);
		if(sizeof($parts) != 2)
		{
			return;
		}
		$obj_id = $parts[0];
		$rec_id = $parts[1];
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php');
		$field_ids = array();
		foreach(ilAdvancedMDFieldDefinition::_getDefinitionsByRecordId($rec_id) as $def)
		{
			$field_ids[] = $def->getFieldId();
		}
		
		if(!sizeof($field_ids))
		{
			return;
		}
			
		$xml = new ilXmlWriter;
		$xml->xmlStartTag('AdvancedMetaData');
					
		$query = "SELECT field_id,value,sub_id,sub_type".
			" FROM adv_md_values ".
			" WHERE obj_id = ".$ilDB->quote($a_id, "integer").
			" AND ".$ilDB->in("field_id", $field_ids, "", "integer");
		$set = $ilDB->query($query);
		while($row = $ilDB->fetchAssoc($set))
		{
			if(trim($row['value']) != "")
			{
				$xml->xmlElement('Value',
					array(
						'id' => ilAdvancedMDFieldDefinition::_lookupImportId($row["field_id"]),
						'sub_id' => $row['sub_id'],
						'sub_type' => $row['sub_type'],
					),
					$row['value']);
			}
		}
		
		$xml->xmlEndTag('AdvancedMetaData');	
		
		return $xml->xmlDumpMem(false);
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
			"4.4.0" => array(
				"namespace" => "http://www.ilias.de/Services/AdvancedMetaData/advmd/4_4",
				"xsd_file" => "ilias_advmd_4_4.xsd",
				"uses_dataset" => true,
				"min" => "4.4.0",
				"max" => "")
		);
	}

}

?>