<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Xml Exporter class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesExport
 */
abstract class ilXmlExporter
{
	protected $dir_relative;
	protected $dir_absolute;

	/**
	 * Constructor
	 *
	 * @param
	 * @return
	 */
	public function __construct()
	{

	}

	/**
	 * export directory lookup
	 * @return string export directory
	 */
	public static function lookupExportDirectory($a_obj_type, $a_obj_id, $a_export_type = 'xml', $a_entity = "")
	{
		$ent = ($a_entity == "")
			? ""
			: "_".$a_entity;
			
		if($a_export_type == 'xml')
		{
			return ilUtil::getDataDir()."/".$a_obj_type.$ent."_data"."/".$a_obj_type."_".$a_obj_id."/export";
		}
		return ilUtil::getDataDir()."/".$a_obj_type.$ent."_data"."/".$a_obj_type."_".$a_obj_id."/export_".$a_export_type;
	}

	/**
	 * Get xml representation
	 *
	 * @param	string		entity
	 * @param	string		schema version
	 * @param	string		id
	 * @return	string		xml string
	 */
	abstract public function getXmlRepresentation($a_entity, $a_schema_version, $a_id);

	abstract public function init();


	/**
	 * Export directories
	 *
	 * @param	string		relative directory
	 * @param	string		absolute directory
	 */
	public function setExportDirectories($a_dir_relative, $a_dir_absolute)
	{
		$this->dir_relative = $a_dir_relative;
		$this->dir_absolute = $a_dir_absolute;
	}

	/**
	 * Get relative export directory
	 *
	 * @return	string	relative directory
	 */
	public function getRelativeExportDirectory()
	{
		return $this->dir_relative;
	}

	/**
	 * Get absolute export directory
	 *
	 * @return	string	absolute directory
	 */
	public function getAbsoluteExportDirectory()
	{
		return $this->dir_absolute;
	}

	/**
	 * Get head dependencies
	 *
	 * @param		string		entity
	 * @param		string		target release
	 * @param		array		ids
	 * @return		array		array of array with keys "component", entity", "ids"
	 */
	public function getXmlExportHeadDependencies($a_entity, $a_target_release, $a_ids)
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
	public function getXmlExportTailDependencies($a_entity, $a_target_release, $a_ids)
	{
		return array();
	}

	/**
	 * Returns schema versions that the component can export to.
	 * ILIAS chooses the first one, that has min/max constraints which
	 * fit to the target release. Please put the newest on top. Example:
	 *
	 * 		return array (
	 *		"4.1.0" => array(
	 *			"namespace" => "http://www.ilias.de/Services/MetaData/md/4_1",
	 *			"xsd_file" => "ilias_md_4_1.xsd",
	 *			"min" => "4.1.0",
	 *			"max" => "")
	 *		);
	 *
	 *
	 * @return		array
	 */
	abstract public function getValidSchemaVersions($a_entity);

	/**
	 * Determine schema version
	 *
	 * @param
	 * @return
	 */
	public final function determineSchemaVersion($a_entity, $a_target_release)
	{
		$svs = $this->getValidSchemaVersions($a_entity);
		$found = false;
		foreach ($svs as $k => $sv)
		{
			if (!$found)
			{
				if (version_compare($sv["min"], ILIAS_VERSION_NUMERIC, "<=")
					&& ($sv["max"] == "" || version_compare($sv["max"], ILIAS_VERSION_NUMERIC, ">=")))
				{
					$rsv = $sv;
					$rsv["schema_version"] = $k;
					$found = true;
				}
			}
		}

		return $rsv;
	}
}
?>
