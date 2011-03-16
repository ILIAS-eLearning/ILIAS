<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "Services/Object/classes/class.ilObject2.php";

/**
* Class ilObjBlog
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFolder.php 25528 2010-09-03 10:37:11Z smeyer $
*
* @extends ilObject2
*/
class ilObjBlog extends ilObject2
{
	protected $notes; // [bool]
	
	function initType()
	{
		$this->type = "blog";
	}

	/**
	 * Toggle notes status
	 *
	 * @param bool $a_status
	 * @return bool
	 */
	function updateNotesStatus($a_status)
	{
		global $ilDB;

		if($this->id)
		{
			$ilDB->query("UPDATE il_blog".
				" SET notes = ".$ilDB->quote($a_status, "integer").
				" WHERE id = ".$ilDB->quote($this->id, "integer"));
			$this->notes = (bool)$a_status;
			return true;
		}
		return false;
	}

	protected function doRead()
	{
		global $ilDB;

		$set = $ilDB->query("SELECT notes FROM il_blog".
				" WHERE id = ".$ilDB->quote($this->id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		$this->notes = (bool)$row["notes"];
	}

	protected function doCreate()
	{
		global $ilDB;
		
		$ilDB->query("INSERT INTO il_blog (id,notes) VALUES (".
			$ilDB->quote($this->id, "integer").",".
			$ilDB->quote(true, "integer"));
	}

	function getNotesStatus()
	{
		return $this->notes;
	}
}

?>