<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* class ilObjectDataCache
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* This class caches some properties of the object_data table. Like title description owner obj_id
*
*/
class ilObjectDataCache
{
	var $db = null;
	var $reference_cache = array();
	var $object_data_cache = array();

	function ilObjectDataCache()
	{
		global $ilDB;

		$this->db =& $ilDB;
	}

	function deleteCachedEntry($a_obj_id)
	{
		unset($this->object_data_cache[$a_obj_id]);
	}

	function lookupObjId($a_ref_id)
	{
		if(!$this->__isReferenceCached($a_ref_id))
		{
//echo"-objidmissed-$a_ref_id-";
			$obj_id = $this->__storeReference($a_ref_id);
			$this->__storeObjectData($obj_id);
		}
		return (int) @$this->reference_cache[$a_ref_id];
	}

	function lookupTitle($a_obj_id)
	{
		if(!$this->__isObjectCached($a_obj_id))
		{
			$this->__storeObjectData($a_obj_id);
		}
		return @$this->object_data_cache[$a_obj_id]['title'];
	}

	function lookupType($a_obj_id)
	{
		if(!$this->__isObjectCached($a_obj_id))
		{
//echo"-typemissed-$a_obj_id-";
			$this->__storeObjectData($a_obj_id);
		}
		return @$this->object_data_cache[$a_obj_id]['type'];
	}

	function lookupOwner($a_obj_id)
	{
		if(!$this->__isObjectCached($a_obj_id))
		{
			$this->__storeObjectData($a_obj_id);
		}
		return @$this->object_data_cache[$a_obj_id]['owner'];
	}

	function lookupDescription($a_obj_id)
	{
		if(!$this->__isObjectCached($a_obj_id))
		{
			$this->__storeObjectData($a_obj_id);
		}
		return @$this->object_data_cache[$a_obj_id]['description'];
	}

	function lookupLastUpdate($a_obj_id)
	{
		if(!$this->__isObjectCached($a_obj_id))
		{
			$this->__storeObjectData($a_obj_id);
		}
		return @$this->object_data_cache[$a_obj_id]['last_update'];
	}
	// PRIVATE

	/**
	* checks whether an reference id is already in cache or not 
	*
	* @access	private
	* @param	int			$a_ref_id				reference id
	* @return	boolean
	*/
	function __isReferenceCached($a_ref_id)
	{
		#return false;
		#static $cached = 0;
		#static $not_cached = 0;

		if(@$this->reference_cache[$a_ref_id])
		{
			#echo "Reference ". ++$cached ."cached<br>";
			return true;
		}
		#echo "Reference ". ++$not_cached ." not cached<br>";
		return false;
		
	}

	/**
	* checks whether an object is aleady in cache or not 
	*
	* @access	private
	* @param	int			$a_obj_id				object id
	* @return	boolean
	*/
	function __isObjectCached($a_obj_id)
	{
		static $cached = 0;
		static $not_cached = 0;
			

		if(@$this->object_data_cache[$a_obj_id])
		{
			#echo "Object ". ++$cached ."cached<br>";
			return true;
		}
		#echo "Object ". ++$not_cached ." not cached<br>";
		return false;
	}


	/**
	* Stores Reference in cache.
	* Maybe it could be useful to find all references of that object andd store them also in the cache.
	* But this would be an extra query.
	*
	* @access	private
	* @param	int			$a_ref_id				reference id
	* @return	int			$obj_id
	*/
	function __storeReference($a_ref_id)
	{
		global $ilDB;
		
		$query = "SELECT obj_id FROM object_reference WHERE ref_id = ".$ilDB->quote($a_ref_id,'integer');
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->reference_cache[$a_ref_id] = $row['obj_id'];
		}
		return (int) @$this->reference_cache[$a_ref_id];
	}

	/**
	* Stores object data in cache
	*
	* @access	private
	* @param	int			$a_obj_id				object id
	* @return	bool
	*/
	function __storeObjectData($a_obj_id, $a_lang = "")
	{
		global $ilDB, $objDefinition, $ilUser;
		
		if (is_object($ilUser) && $a_lang == "")
		{
			$a_lang = $ilUser->getLanguage();
		}
		
		$query = "SELECT * FROM object_data WHERE obj_id = ".
			$ilDB->quote($a_obj_id ,'integer');
		$res = $this->db->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
			$this->object_data_cache[$a_obj_id]['title'] = $row->title;
			$this->object_data_cache[$a_obj_id]['description'] = $row->description;
			$this->object_data_cache[$a_obj_id]['type'] = $row->type;
			$this->object_data_cache[$a_obj_id]['owner'] = $row->owner;
			$this->object_data_cache[$a_obj_id]['last_update'] = $row->last_update;
			
			//$ilBench->start("Tree", "fetchNodeData_readDefinition");
			if (is_object($objDefinition))
			{
				$translation_type = $objDefinition->getTranslationType($row->type);
			}
			//$ilBench->stop("Tree", "fetchNodeData_readDefinition");

			if ($translation_type == "db")
			{
				//$ilBench->start("Tree", "fetchNodeData_getTranslation");
				$q = "SELECT title,description FROM object_translation ".
					 "WHERE obj_id = ".$ilDB->quote($a_obj_id,'integer')." ".
					 "AND lang_code = ".$ilDB->quote($a_lang,'text')." ".
					 "AND NOT lang_default = 1";
				$r = $ilDB->query($q);

				$row = $r->fetchRow(DB_FETCHMODE_OBJECT);
				if ($row)
				{
					$this->object_data_cache[$a_obj_id]['title'] = $row->title;
					$this->object_data_cache[$a_obj_id]['description'] = $row->description;
				}
				//$ilBench->stop("Tree", "fetchNodeData_getTranslation");
			}
		}
		
		return true;
	}
	
	/**
	* Stores object data in cache
	*
	* @access	private
	* @param	int			$a_obj_id				object id
	* @return	bool
	*/
	function preloadObjectCache($a_obj_ids, $a_lang = "")
	{
		global $ilDB, $objDefinition, $ilUser;
		
		if (is_object($ilUser) && $a_lang == "")
		{
			$a_lang = $ilUser->getLanguage();
		}
		
//echo "<br>-preloading-"; var_dump($a_obj_ids);
		if (!is_array($a_obj_ids)) return;
		if (count($a_obj_ids) == 0) return;
		
		
		$query = "SELECT * FROM object_data ".
			"WHERE ".$ilDB->in('obj_id',$a_obj_ids,false,'integer');
		$res = $ilDB->query($query);
		while($row = $res->fetchRow(DB_FETCHMODE_OBJECT))
		{
//echo "<br>store_obj-".$row->obj_id."-".$row->type."-".$row->title."-";
			$this->object_data_cache[$row->obj_id]['title'] = $row->title;
			$this->object_data_cache[$row->obj_id]['description'] = $row->description;
			$this->object_data_cache[$row->obj_id]['type'] = $row->type;
			$this->object_data_cache[$row->obj_id]['owner'] = $row->owner;
			$this->object_data_cache[$row->obj_id]['last_update'] = $row->last_update;

			if (is_object($objDefinition))
			{
				$translation_type = $objDefinition->getTranslationType($row->type);
			}
			//$ilBench->stop("Tree", "fetchNodeData_readDefinition");

			if ($translation_type == "db")
			{
				//$ilBench->start("Tree", "fetchNodeData_getTranslation");
				$q = "SELECT title,description FROM object_translation ".
					 "WHERE obj_id = ".$ilDB->quote($row->obj_id,'integer')." ".
					 "AND lang_code = ".$ilDB->quote($a_lang,'text')." ".
					 "AND NOT lang_default = 1";
				$r = $ilDB->query($q);

				$row2 = $r->fetchRow(DB_FETCHMODE_OBJECT);
				if ($row2)
				{
					$this->object_data_cache[$row->obj_id]['title'] = $row2->title;
					$this->object_data_cache[$row->obj_id]['description'] = $row2->description;
				}
				//$ilBench->stop("Tree", "fetchNodeData_getTranslation");
			}
		}
	}

	function preloadReferenceCache($a_ref_ids, $a_incl_obj = true)
	{
		global $ilDB;
		
		if (!is_array($a_ref_ids)) return;
		if (count($a_ref_ids) == 0) return;
		
		$query = "SELECT ref_id, obj_id FROM object_reference ".
			"WHERE ".$ilDB->in('ref_id',$a_ref_ids,false,'integer');
		$res = $ilDB->query($query);
		
		$obj_ids = array();
		while($row = $res->fetchRow(DB_FETCHMODE_ASSOC))
		{
			$this->reference_cache[$row['ref_id']] = $row['obj_id'];
//echo "<br>store_ref-".$row['ref_id']."-".$row['obj_id']."-";
			$obj_ids[] = $row['obj_id'];
		}
		if ($a_incl_obj)
		{
			$this->preloadObjectCache($obj_ids);
		}
	}

}
?>
