<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilSearchSettings.php';

/**
* Search Auto Completion Application Class
*
*/
class ilSearchAutoComplete
{
	
	/**
	 * Performs better than standard like search on huge installations
	 */
	public static function getLuceneList($a_str)
	{
		include_once './Services/Search/classes/Lucene/class.ilLuceneQueryParser.php';
		$qp = new ilLuceneQueryParser('title:'.$a_str.'*');
		$qp->parse();
		
		include_once './Services/Search/classes/Lucene/class.ilLuceneSearcher.php';
		$searcher = ilLuceneSearcher::getInstance($qp);
		$searcher->setType(ilLuceneSearcher::TYPE_STANDARD);
		$searcher->search();
		
		$res = $searcher->getResult()->getCandidates();
		
		$max_entries = ilSearchSettings::getInstance()->getAutoCompleteLength() ?
			ilSearchSettings::getInstance()->getAutoCompleteLength() : 
			10;
		
		
		$list = array();
		$num_entries = 0;
		foreach($res as $res_obj_id)
		{
			if(self::checkObjectPermission($res_obj_id))
			{
				$list[] = ilObject::_lookupTitle($res_obj_id,true);
				$num_entries++;
			}
			if($num_entries >= $max_entries)
			{
				break;
			}
		}
		
		$i = 0;
		$result = array();
		foreach($list as $entry)
		{
			$result[$i] = new stdClass();
			$result[$i]->value = $entry;
			$i++;
		}
		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		return ilJsonUtil::encode($result);
	}
	
	
	
	/**
	* Get completion list
	*/
	static function getList($a_str)
	{
		global $ilDB;

		include_once './Services/Search/classes/class.ilSearchSettings.php';
		if(ilSearchSettings::getInstance()->isLuceneUserSearchEnabled())
		{
			return self::getLuceneList($a_str);
		}
		
		
		$a_str = str_replace('"', "", $a_str);
		
		$settings = new ilSearchSettings();
		
		$object_types = array('cat','dbk','crs','fold','frm','grp','lm','sahs','glo','mep','htlm','exc','file','qpl','tst','svy','spl',
			'chat','icrs','icla','webr','mcst','sess','pg','st','gdf','wiki');

		$set = $ilDB->query("SELECT title, obj_id FROM object_data WHERE "
			.$ilDB->like('title', 'text', $a_str."%")." AND "
			.$ilDB->in('type', $object_types, false, 'text')." ORDER BY title");
		$max = ($settings->getAutoCompleteLength() > 0)
			? $settings->getAutoCompleteLength()
			: 10;
		
		$cnt = 0;
		$list = array();
		$checked = array();
		$lim = "";
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			if (strpos($rec["title"], " ") > 0 || strpos($rec["title"], "-") > 0)
			{
				$rec["title"] = '"'.$rec["title"].'"';
			}
			if (!in_array($rec["title"], $list) && !in_array($rec["obj_id"], $checked))
			{
				if (ilSearchAutoComplete::checkObjectPermission($rec["obj_id"]))
				{
					$list[] = $lim.$rec["title"];
					$cnt++;
				}
				$checked[] = $rec["obj_id"];
			}
		}
		
		$set = $ilDB->query("SELECT rbac_id,obj_id,obj_type, keyword FROM il_meta_keyword WHERE "
			.$ilDB->like('keyword', 'text', $a_str."%")." AND "
			.$ilDB->in('obj_type', $object_types, false, 'text')." ORDER BY keyword");
		while (($rec = $ilDB->fetchAssoc($set)) && $cnt < $max)
		{
			if (strpos($rec["keyword"], " ") > 0)
			{
				$rec["keyword"] = '"'.$rec["keyword"].'"';
			}
			if (!in_array($rec["keyword"], $list) && !in_array($rec["rbac_id"], $checked))
			{
				if (ilSearchAutoComplete::checkObjectPermission($rec["rbac_id"]))
				{
					$list[] = $lim.$rec["keyword"];
					$cnt++;
				}
			}
			$checked[] = $rec["rbac_id"];
		}

		$i = 0;
		$result = array();
		foreach ($list as $l)
		{
			$result[$i] = new stdClass();
			$result[$i]->value = $l;
			$i++;
		}

		include_once './Services/JSON/classes/class.ilJsonUtil.php';
		return ilJsonUtil::encode($result);
	}
	
	/**
	* Checks read permission on obj id
	*/
	static function checkObjectPermission($a_obj_id)
	{
		global $ilAccess;
		
		$refs = ilObject::_getAllReferences($a_obj_id);
		foreach ($refs as $ref)
		{
			if ($ilAccess->checkAccess("read", "", $ref))
			{
				return true;
			}
		}
		return false;
	}
}
?>