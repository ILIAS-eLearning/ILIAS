<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlExporter.php");

/**
 * Export2 class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolExporter extends ilXmlExporter
{
	private $ds;

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolDataSet.php");
		$this->ds = new ilMediaPoolDataSet();
		$this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
		$this->ds->setDSPrefix("ds");
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
		include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		$pg_ids = array();
		$mob_ids = array();

		foreach ($a_ids as $id)
		{
			$m_ids = ilObjMediaPool::getAllMobIds($id);
			foreach ($m_ids as $m)
			{
				$mob_ids[] = $m;
			}
		}

		return array (
			array(
				"component" => "Services/MediaObjects",
				"entity" => "mob",
				"ids" => $mob_ids)
			);
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
		include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		$pg_ids = array();
		$mob_ids = array();

		foreach ($a_ids as $id)
		{
			$pages = ilMediaPoolItem::getIdsForType($id, "pg");
			foreach ($pages as $p)
			{
				$pg_ids[] = "mep:".$p;
			}
		}

		return array (
			array(
				"component" => "Services/COPage",
				"entity" => "pg",
				"ids" => $pg_ids)
			);
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
		return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, $a_id, "", true, true);
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
				"namespace" => "http://www.ilias.de/Modules/MediaPool/mep/4_1",
				"xsd_file" => "ilias_mep_4_1.xsd",
				"uses_dataset" => true,
				"min" => "4.1.0",
				"max" => "")
		);
	}

}

?>