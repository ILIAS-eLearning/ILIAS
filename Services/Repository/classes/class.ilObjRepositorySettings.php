<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObject.php");

/**
 * Class ilObjRepositorySettings
 * 
 * @author Stefan Meyer <meyer@leifos.com> 
 * @version $Id: class.ilObjSystemFolder.php 33501 2012-03-03 11:11:05Z akill $
 * 
 * @ingroup ServicesRepository
 */
class ilObjRepositorySettings extends ilObject
{
	/**
	* Constructor
	* @access	public
	* @param	integer	reference_id or object_id
	* @param	boolean	treat the id as reference_id (true) or object_id (false)
	*/
	function __construct($a_id,$a_call_by_reference = true)
	{
		$this->type = "reps";
		parent::__construct($a_id,$a_call_by_reference);
	}

	function delete()
	{
		// DISABLED
		return false;
	}
	
	public static function addNewItemGroup(array $a_titles)
	{
		global $ilDB;
		
		// append
		$pos = $ilDB->query("SELECT max(pos) mpos FROM il_new_item_grp");
		$pos = $ilDB->fetchAssoc($pos);
		$pos = (int)$pos["mpos"];
		$pos += 10;		
		
		$seq = $ilDB->nextID("il_new_item_grp");
		
		$ilDB->manipulate("INSERT INTO il_new_item_grp".
			" (id, titles, pos) VALUES (".
			$ilDB->quote($seq, "integer").
			", ".$ilDB->quote(serialize($a_titles), "text").
			", ".$ilDB->quote($pos, "integer").
			")");			
		return true;
	}
	
	public static function updateNewItemGroup($a_id, array $a_titles)
	{
		global $ilDB;
		
		$ilDB->manipulate("UPDATE il_new_item_grp".
			" SET titles = ".$ilDB->quote(serialize($a_titles), "text").
			" WHERE id = ".$ilDB->quote($a_id, "integer"));			
		return true;
	}
	
	public static function deleteNewItemGroup($a_id)
	{
		global $ilDB;
		
		// :TODO: remove object assignments
		
		$ilDB->manipulate("DELETE FROM il_new_item_grp".
			" WHERE id = ".$ilDB->quote($a_id, "integer"));				
		return true;
	}
	
	public static function getNewItemGroups()
	{
		global $ilDB, $lng, $ilUser;
		
		$def_lng = $lng->getDefaultLanguage();
		$usr_lng = $ilUser->getLanguage();
		
		$res = array();
		
		$set = $ilDB->query("SELECT * FROM il_new_item_grp ORDER BY pos");
		while($row = $ilDB->fetchAssoc($set))
		{
			$row["titles"] = unserialize($row["titles"]);
			
			$title = $row["titles"][$usr_lng];
			if(!$title)
			{
				$title = $row["titles"][$def_lng];
			}
			if(!$title)
			{
				$title = array_shift($row["titles"]);
			}
			$row["title"] = $title;
			
			$res[$row["id"]] = $row;			
		}
		
		return $res;
	}

	public static function updateNewItemGroupOrder(array $a_order)
	{
		global $ilDB;
		
		asort($a_order);
		$pos = 0;
		foreach($a_order as $id => $pos)
		{
			$pos += 10;
			
			$ilDB->manipulate("UPDATE il_new_item_grp".
				" SET pos = ".$ilDB->quote($pos, "integer").
				" WHERE id = ".$ilDB->quote($id, "integer"));			
		}	
	}
	
	public static function getNewItemGroupSubItems()
	{
		global $ilSetting, $objDefinition;
		
		$res = array();
		
		include_once("./Services/Component/classes/class.ilModule.php");
		foreach(ilModule::getAvailableCoreModules() as $mod)
		{					
			$has_repo = false;		
			$rep_types = $objDefinition->getRepositoryObjectTypesForComponent(IL_COMP_MODULE, $mod["subdir"]);
			if(sizeof($rep_types) > 0)
			{
				foreach($rep_types as $ridx => $rt)
				{
					// we only want to display repository modules
					if($rt["repository"])
					{
						$has_repo = true;							
					}
					else
					{
						unset($rep_types[$ridx]);
					}
				}										
			}				
			if($has_repo)
			{		
				foreach($rep_types as $rt)
				{									
					$pos_grp = $ilSetting->get("obj_add_new_pos_grp_".$rt["id"], 0);
					$res[$pos_grp][] = $rt["id"];
				}				
			}
		}
		
		return $res;
	}	
}
	
?>