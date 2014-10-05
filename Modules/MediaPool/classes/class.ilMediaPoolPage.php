<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObject.php");

/**
* Class ilMediaPoolPage
* 
* @author Alex Killing <alex.killing@gmx.de> 
* @version $Id$
*
* @ingroup ModulesMediaPool
*/
class ilMediaPoolPage extends ilPageObject
{
	/**
	 * Get parent type
	 *
	 * @return string parent type
	 */
	function getParentType()
	{
		return "mep";
	}	

	/**
	* update object data
	*
	* @access	public
	* @return	boolean
	*/
	function update($a_validate = true, $a_no_history = false)
	{
		global $ilDB;
				parent::update($a_validate, $a_no_history);

		return true;
	}
	
	/**
	* Read media_pool data
	*/
	function read()
	{
		global $ilDB;
		
		// get co page
		parent::read();
	}


	/**
	* delete media_pool page and al related data	
	*
	* @access	public
	*/
	function delete()
	{
		global $ilDB;
		

		// delete internal links information to this page
//		include_once("./Services/Link/classes/class.ilInternalLink.php");
//		ilInternalLink::_deleteAllLinksToTarget("mep", $this->getId());
				
		
		// delete co page
		parent::delete();

		return true;
	}

	/**
	* delete media pool page and al related data	
	*
	* @access	public
	*/
	static function deleteAllPagesOfMediaPool($a_media_pool_id)
	{
		global $ilDB;
		
// todo
/*
		$query = "SELECT * FROM il_media_pool_page".
			" WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer");
		$set = $ilDB->query($query);
		
		while($rec = $ilDB->fetchAssoc($set))
		{
			$mp_page = new ilMediaPoolPage($rec["id"]);
			$mp_page->delete();
		}
*/
	}
	
	/**
	* Checks whether a page with given title exists
	*/
	static function exists($a_media_pool_id, $a_title)
	{
		global $ilDB;

// todo
/*
		
		$query = "SELECT * FROM il_media_pool_page".
			" WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		if($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
*/		
		return false;
	}
	
	/**
	* Lookup title
	*/
	static function lookupTitle($a_page_id)
	{
		global $ilDB;
	
		include_once("./Modules/MediaPool/classes/class.ilMediaPoolItem.php");
		return ilMediaPoolItem::lookupTitle($a_page_id);
	}


	/**
	* Check whether page exists in media pool or not	
	*
	* @param	int		media pool id
	* @param	string	page name
	* @return	boolean	page exists true/false
	*/
	static function _mediaPoolPageExists($a_media_pool_id, $a_title)
	{
		global $ilDB;
// todo
/*		
		$query = "SELECT id FROM il_media_pool_page".
			" WHERE media_pool_id = ".$ilDB->quote($a_media_pool_id, "integer").
			" AND title = ".$ilDB->quote($a_title, "text");
		$set = $ilDB->query($query);
		
		$pages = array();
		if ($rec = $ilDB->fetchAssoc($set))
		{
			return true;
		}
*/		
		return false;
	}
	
	/**
	 * Check whether meida pool page exists
	 * 
	 * @param	int			page id
	 * @return	boolean		true/false
	 */
	static function _exists($a_id)
	{
		return ilPageObject::_exists("mep", $a_id);
	}
	
	/**
	* get all usages of current media object
	*/
	function getUsages($a_incl_hist = true)
	{
		return $this->lookupUsages($this->getId(), $a_incl_hist);
	}
	
	/**
	* Lookup usages of media object
	*
	* @todo: This should be all in one context -> mob id table
	*/
	function lookupUsages($a_id, $a_incl_hist = true)
	{
		global $ilDB;

		// get usages in pages
		$q = "SELECT * FROM page_pc_usage WHERE pc_id = ".
			$ilDB->quote($a_id, "integer").
			" AND pc_type = ".$ilDB->quote("incl", "text");
			
		if (!$a_incl_hist)
		{
			$q.= " AND usage_hist_nr = ".$ilDB->quote(0, "integer");
		}
			
		$us_set = $ilDB->query($q);
		$ret = array();
		while($us_rec = $ilDB->fetchAssoc($us_set))
		{
			$ut = "";
			if(is_int(strpos($us_rec["usage_type"], ":")))
			{
				$us_arr = explode(":", $us_rec["usage_type"]);
				$ut = $us_arr[1];
				$ct = $us_arr[0];
			}

			// check whether page exists
			$skip = false;
			if ($ut == "pg")
			{
				include_once("./Services/COPage/classes/class.ilPageObject.php");
				if (!ilPageObject::_exists($ct, $us_rec["usage_id"]))
				{
					$skip = true;
				}
			}
				
			if (!$skip)
			{
				$ret[] = array("type" => $us_rec["usage_type"],
					"id" => $us_rec["usage_id"],
					"hist_nr" => $us_rec["usage_hist_nr"]);
			}
		}

		// get usages in media pools
		$q = "SELECT DISTINCT mep_id FROM mep_tree JOIN mep_item ON (child = obj_id) WHERE mep_item.obj_id = ".
			$ilDB->quote($a_id, "integer")." AND mep_item.type = ".$ilDB->quote("pg", "text");
		$us_set = $ilDB->query($q);
		while($us_rec = $ilDB->fetchAssoc($us_set))
		{
			$ret[] = array("type" => "mep",
				"id" => $us_rec["mep_id"]);
		}
		
		return $ret;
	}
	
}
?>
