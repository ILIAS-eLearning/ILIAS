<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/interfaces/interface.ilXmlExporter.php");

/**
 * Export2 class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilMediaObjectExporter implements ilXmlExporter
{
	private $ds;

	/**
	 * Constructor
	 */
	function __construct()
	{
		include_once("./Services/MediaObjects/classes/class.ilMediaObjectDataSet.php");
		$this->ds = new ilMediaObjectDataSet();
	}

	/**
	 * Get export sequence
	 *
	 * @param
	 * @return
	 */
	function getXmlExportHeadDependencies($a_target_release, $a_id)
	{
		return array();
	}

	public function getXmlExportTailDependencies($a_target_release, $a_id)
	{
		if (!is_array($a_id))
		{
			if ($a_id <= 0)
			{
				return array();
			}
			$a_id = array($a_id);
		}

		$md_ids = array();
		foreach ($a_id as $mob_id)
		{
			$md_ids[] = "0:".$mob_id.":mob";
		}

		return array (
			array(
				"component" => "Services/MetaData",
				"exp_class" => "ilMetaDataExporter",
				"entity" => "md",
				"ids" => $md_ids)
			);
	}

	public function setExportDirectories($a_dir_relative, $a_dir_absolute)
	{
		$this->ds->setExportDirectories($a_dir_relative, $a_dir_absolute);
	}

	public function getXmlRepresentation($a_entity, $a_target_release, $a_ids)
	{
		return $this->ds->getXmlRepresentation($a_entity, $a_target_release, $a_ids);
	}
}

?>