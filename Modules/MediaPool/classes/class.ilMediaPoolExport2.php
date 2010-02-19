<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Export2 class for media pools
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id: $
 * @ingroup ModulesMediaPool
 */
class ilMediaPoolExport2
{
	/**
	 * Get export sequence
	 *
	 * @param
	 * @return
	 */
	function getXmlExportSequence($a_target_release, $a_id)
	{
		include_once("./Modules/MediaPool/classes/class.ilObjMediaPool.php");
		$mob_ids = ilObjMediaPool::getAllMobIds($a_id);

		return array (
			array(
				"component" => "Services/MediaObjects",
				"ds_class" => "MediaObjectDataSet",
				"entity" => "mob",
				"where" => array("id" => $mob_ids)),
			array(
				"component" => "Modules/MediaPool",
				"ds_class" => "MediaPoolDataSet",
				"entity" => "mep",
				"where" => array("id" => $a_id))
			);
	}
}

?>