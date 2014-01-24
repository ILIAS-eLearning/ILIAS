<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Stores the applied template id for objects
 *
 * @author Stefan Meyer <meyer@ilias@gmx.de>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateObjSettings
{

	/**
	 * Lookup template id
	 * @global ilDB $ilDB
	 * @param int $a_ref_id
	 * @return int
	 */
	public static function lookupTemplateId($a_ref_id)
	{
		global $ilDB;

		$query = 'SELECT tpl_id FROM didactic_tpl_objs '.
			'WHERE ref_id = '.$ilDB->quote($a_ref_id,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			return $row->tpl_id;
		}
		return 0;
	}


	/**
	 * Delete by obj id
	 * @global ilDB $ilDB
	 * @param int $a_obj_id
	 * @return bool
	 */
	public static function deleteByObjId($a_obj_id)
	{
		global $ilDB;

		$query = 'DELETE FROM didactic_tpl_objs '.
			'WHERE obj_id = '.$ilDB->quote($a_obj_id,'integer');
		$ilDB->manipulate($query);
		return true;
	}

	/**
	 * Delete by template id
	 * @global ilDB $ilDB
	 * @param int $a_tpl_id
	 * @return bool
	 */
	public static function deleteByTemplateId($a_tpl_id)
	{
		global $ilDB;

		$query = 'DELETE FROM didactic_tpl_objs '.
			'WHERE tpl_id = '.$ilDB->quote($a_tpl_id,'integer');
		$ilDB->manipulate($query);
		return true;
	}

	/**
	 * Delete by ref_id
	 * @global ilDB $ilDB
	 * @param int $a_ref_id
	 */
	public static function deleteByRefId($a_ref_id)
	{
		global $ilDB;

		$query = 'DELETE FROM didactic_tpl_objs '.
			'WHERE ref_id = '.$ilDB->quote($a_ref_id,'integer');
		$ilDB->manipulate($query);
	}

	/**
	 * Assign template to object
	 * @global ilDB $ilDB
	 * @param int $a_obj_id
	 * @param int $a_tpl_id
	 * @return bool
	 */
	public static function assignTemplate($a_ref_id,$a_obj_id,$a_tpl_id)
	{
		global $ilDB;

		self::deleteByRefId($a_ref_id);

		$query = 'INSERT INTO didactic_tpl_objs (ref_id,obj_id,tpl_id) '.
			'VALUES ( '.
			$ilDB->quote($a_ref_id,'integer').', '.
			$ilDB->quote($a_obj_id,'integer').', '.
			$ilDB->quote($a_tpl_id,'integer').' '.
			')';
		$ilDB->manipulate($query);
		return true;
	}

}
?>
