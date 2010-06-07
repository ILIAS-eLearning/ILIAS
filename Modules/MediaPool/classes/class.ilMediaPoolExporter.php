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
	}

	/**
	 * Get export sequence
	 *
	 * @param
	 * @return
	 */
	function getXmlExportHeadDependencies($a_target_release, $a_id)
	{
		include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
		$mob_ids = ilObjMediaPool::getAllMobIds($a_id);

		$pg_ids = array();
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		
		$pages = ilMediaPoolItem::getIdsForType($a_id, "pg");

		foreach ($pages as $p)
		{
			$pg_ids[] = "mep:".$p;
		}

		return array (
			array(
				"component" => "Services/MediaObjects",
				"exp_class" => "ilMediaObjectExporter",
				"entity" => "mob",
				"ids" => $mob_ids)
			,
			array(
				"component" => "Services/COPage",
				"exp_class" => "ilCOPageExporter",
				"entity" => "pg",
				"ids" => $pg_ids)
			);
	}

	public function getXmlRepresentation($a_entity, $a_target_release, $a_ids)
	{
		return $this->ds->getXmlRepresentation($a_entity, $a_target_release, $a_ids);
	}
}

?>