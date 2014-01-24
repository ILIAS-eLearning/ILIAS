<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilDiskQuotaHandler
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id: class.ilObjFile.php 40384 2013-03-06 13:08:21Z sborn $
*
* @ingroup ServicesDiskQuota
*/
class ilDiskQuotaHandler
{		
	/**
	 * Find and update/create all related entries for source object
	 * 
	 * @param string $a_src_obj_type
	 * @param int $a_src_obj_id
	 * @param int $a_src_filesize
	 * @param array $a_owner_obj_ids
	 */
	public static function handleUpdatedSourceObject($a_src_obj_type, $a_src_obj_id, $a_src_filesize, $a_owner_obj_ids = null, $a_is_prtf = false)
	{
		global $ilDB;		
		
		$done = array();
		
		if(is_array($a_owner_obj_ids) && sizeof($a_owner_obj_ids) && (int)$a_src_filesize > 0)
		{
			// we are (currently) only interested in personal workspace objects
		
			if(!$a_is_prtf)
			{
				$set = $ilDB->query("SELECT DISTINCT(od.owner)".
					" FROM object_data od".
					" JOIN object_reference_ws ref ON (ref.obj_id = od.obj_id)".
					" JOIN tree_workspace t ON (t.child = ref.wsp_id)".
					" WHERE ".$ilDB->in("od.obj_id", $a_owner_obj_ids, "", "integer").
					" AND t.tree = od.owner");
			}
			else
			{
				$set = $ilDB->query("SELECT DISTINCT(od.owner)".
					" FROM object_data od".
					" JOIN usr_portfolio prtf ON (prtf.id = od.obj_id)".
					" WHERE ".$ilDB->in("od.obj_id", $a_owner_obj_ids, "", "integer"));
			}
			while($row = $ilDB->fetchAssoc($set))
			{					
				$done[] = $row["owner"];

				self::handleEntry(
					$row["owner"], 
					$a_src_obj_type,
					$a_src_obj_id,
					(int)$a_src_filesize
				);		
			}		
		}
				
		// delete obsolete entries
		$existing = self::getOwnersBySourceObject($a_src_obj_type, $a_src_obj_id);
		$existing = array_diff($existing, $done);		
		if(sizeof($existing))
		{
			foreach($existing as $owner)
			{
				self::deleteEntry($owner, $a_src_obj_type, $a_src_obj_id);
			}			
		}
	}
	
	/**
	 * Delete entry for owner and source object
	 * 
	 * @param int $a_owner_id
	 * @param string $a_src_obj_type
	 * @param int $a_src_obj_id
	 */
	protected static function deleteEntry($a_owner_id, $a_src_obj_type, $a_src_obj_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM il_disk_quota".
			" WHERE owner_id = ".$ilDB->quote($a_owner_id, "integer").
			" AND src_type = ".$ilDB->quote($a_src_obj_type, "text").
			" AND src_obj_id = ".$ilDB->quote($a_src_obj_id, "integer"));	
	}
	
	/**
	 * Delete all entries for owner
	 * 
	 * @param int $a_owner_id
	 */
	public static function deleteByOwner($a_owner_id)
	{
		global $ilDB;
		
		$ilDB->manipulate("DELETE FROM il_disk_quota".
			" WHERE owner_id = ".$ilDB->quote($a_owner_id, "integer"));	
	}
	
	/**
	 * Get owner ids by source object
	 * 
	 * @param string $a_src_obj_type
	 * @param int $a_src_obj_id
	 * @return array
	 */
	protected static function getOwnersBySourceObject($a_src_obj_type, $a_src_obj_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT owner_id".
			" FROM il_disk_quota".
			" WHERE src_type = ".$ilDB->quote($a_src_obj_type, "text").
			" AND src_obj_id = ".$ilDB->quote($a_src_obj_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row["owner_id"];
		}
		
		return $res;
	}
	
	/**
	 * Get all source objects for owner
	 * 
	 * @param int $a_owner_id
	 * @return array
	 */
	protected static function getSourceObjectsByOwner($a_owner_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT src_type, src_obj_id".
			" FROM il_disk_quota".
			" WHERE owner_id = ".$ilDB->quote($a_owner_id, "integer"));
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[$row["src_type"]][] = $row["src_obj_id"];
		}
		
		return $res;		
	}
	
	/**
	 * Update/create owner-related entry of source object
	 * 
	 * @param int $a_owner_id
	 * @param int $a_src_obj_type
	 * @param int $a_src_obj_id
	 * @param int $a_src_filesize
	 */
	protected static function handleEntry($a_owner_id, $a_src_obj_type, $a_src_obj_id, $a_src_filesize)
	{
		global $ilDB;
		
		$existing = self::getSourceObjectsByOwner($a_owner_id);
		
		// update
		if($existing && 
			isset($existing[$a_src_obj_type]) && 
			in_array($a_src_obj_id, $existing[$a_src_obj_type]))
		{
			$ilDB->manipulate("UPDATE il_disk_quota".
				" SET src_size = ".$ilDB->quote($a_src_filesize, "integer").
				" WHERE owner_id = ".$ilDB->quote($a_owner_id, "integer").
				" AND src_type = ".$ilDB->quote($a_src_obj_type, "text").
				" AND src_obj_id = ".$ilDB->quote($a_src_obj_id, "integer"));	
		}
		// insert
		else
		{
			$ilDB->manipulate("INSERT INTO il_disk_quota".
				" (owner_id, src_type, src_obj_id, src_size)".
				" VALUES (".$ilDB->quote($a_owner_id, "integer").
				", ".$ilDB->quote($a_src_obj_type, "text").
				", ".$ilDB->quote($a_src_obj_id, "integer").
				", ".$ilDB->quote($a_src_filesize, "integer").")");		
		}
	}	
	
	/**
	 * Get current storage size for owner
	 * 
	 * @param int $a_owner_id
	 * @return int
	 */
	public static function getFilesizeByOwner($a_owner_id)
	{
		global $ilDB;
		
		$set = $ilDB->query("SELECT sum(src_size) fsize".
			" FROM il_disk_quota".
			" WHERE owner_id = ".$ilDB->quote($a_owner_id, "integer"));
		$row = $ilDB->fetchAssoc($set);
		return (int)$row["fsize"];
	}	
	
	/**
	 * Get current storage size for owner (grouped by type)
	 * 
	 * @param int $a_owner_id
	 * @return int
	 */
	public static function getFilesizeByTypeAndOwner($a_owner_id)
	{
		global $ilDB;
		
		$res = array();
		
		$set = $ilDB->query("SELECT sum(src_size) filesize, src_type, COUNT(*) count".
			" FROM il_disk_quota".
			" WHERE owner_id = ".$ilDB->quote($a_owner_id, "integer").
			" GROUP BY src_type");
		while($row = $ilDB->fetchAssoc($set))
		{
			$res[] = $row;
		}
		
		return $res;
	}	
	
	public static function isUploadPossible($a_additional_size = null)
	{
		global $ilUser;
				
		include_once "Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php";			
		if(!ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
		{			
			return true;
		}
		
		include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
		$usage = ilDiskQuotaHandler::getFilesizeByOwner($ilUser->getId());				
		if($a_additional_size)
		{
			$usage += $a_additional_size;
		}
						
		include_once "Services/WebDAV/classes/class.ilDiskQuotaChecker.php";	
		$quota = ilDiskQuotaChecker::_lookupPersonalWorkspaceDiskQuota($ilUser->getId());				
		$quota = $quota["disk_quota"];
		
		// administrator
		if(is_infinite($quota))
		{
			return true;
		}
		
		return $usage < $quota;
	}
	
	public static function getStatusLegend()
	{
		global $ilUser, $lng;
		
		include_once "Services/WebDAV/classes/class.ilDiskQuotaActivationChecker.php";			
		if(!ilDiskQuotaActivationChecker::_isPersonalWorkspaceActive())
		{			
			return;
		}
		
		include_once "Services/DiskQuota/classes/class.ilDiskQuotaHandler.php";
		$usage = ilDiskQuotaHandler::getFilesizeByOwner($ilUser->getId());
						
		include_once "Services/WebDAV/classes/class.ilDiskQuotaChecker.php";	
		$quota = ilDiskQuotaChecker::_lookupPersonalWorkspaceDiskQuota($ilUser->getId());				
		$quota = $quota["disk_quota"];
		
		// administrator
		if(is_infinite($quota) || !(int)$quota)
		{
			return;
		}
					
		$lng->loadLanguageModule("file");
		return sprintf($lng->txt("personal_workspace_quota_status_legend"), 
				ilFormat::formatSize($usage), 
				ilFormat::formatSize($quota), 
				$quota ? round($usage/$quota*100) : 0);
	}
}

?>