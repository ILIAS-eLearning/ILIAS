<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("Services/Classification/classes/class.ilClassificationProvider.php");

/**
 * Tag classification provider
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesTagging
 */
class ilTaggingClassificationProvider extends ilClassificationProvider
{					
	protected $enable_all_users; // [bool]
	protected $selection; // [string]
	
	protected function init()
	{
		$tags_set = new ilSetting("tags");					
		$this->enable_all_users = (bool)$tags_set->get("enable_all_users", false);
	}
	
	public static function isActive($a_parent_ref_id, $a_parent_obj_id, $a_parent_obj_type)
	{				
		global $ilUser;
		
		// we currently only check for the parent object setting
		// might change later on (parent containers)
		include_once "Services/Object/classes/class.ilObjectServiceSettingsGUI.php";
		$valid = ilContainer::_lookupContainerSetting(
			$a_parent_obj_id,
			ilObjectServiceSettingsGUI::TAG_CLOUD,
			false
		);		
		
		if($valid)
		{
			$tags_set = new ilSetting("tags");					
			if(!$tags_set->get("enable_all_users", false) &&
				$ilUser->getId() == ANONYMOUS_USER_ID)
			{
				$valid = false;
			}
		}
		
		return $valid;
	}	
		
	public function render(array &$a_html, $a_parent_gui)
	{		
		global $lng;
		
		$all_tags = $this->getSubTreeTags();				
		if($all_tags)
		{						
			// see ilPDTaggingBlockGUI::getTagCloud();
			
		    $map = array(
				"personal" => $lng->txt("tagging_my_tags"),
				"other" =>  $lng->txt("tagging_other_users")
			);
			foreach($map as $type => $title)
			{
				$tags = $all_tags[$type];
				if($tags)
				{
					$max = 1;
					foreach($tags as $tag => $counter)
					{
						$max = max($counter, $max);
					}
					reset($tags);

					$tpl = new ilTemplate("tpl.tag_cloud_block.html", true, true, "Services/Tagging");

					$tpl->setCurrentBlock("tag_bl");
					foreach($tags as $tag => $counter)
					{						
						$tpl->setVariable("TAG_TYPE", $type);
						$tpl->setVariable("TAG_TITLE", $tag);
						$tpl->setVariable("TAG_CODE", md5($tag));
						$tpl->setVariable("REL_CLASS",
							ilTagging::getRelevanceClass($counter, $max));
						if(is_array($this->selection[$type]) &&
							in_array($tag, $this->selection[$type]))
						{
							$tpl->setVariable("HIGHL_CLASS", ' ilHighlighted');
						}

						$tpl->parseCurrentBlock();
					}

					$a_html[] = array(
						"title" => $title,
						"html" => $tpl->get()
					);
				}
			}
			
			/*
			if($this->selection)
			{
				$a_html[] = array(
						"title" => "Related Tags",
						"html" => ":TODO:"
					);
			}			 
			*/
		}
	}
	
	
	public function importPostData($a_saved = null)
	{		
		$type = trim($_REQUEST["tag_type"]);
		$tag_code = trim($_REQUEST["tag"]);	// using codes to avoid encoding issues
		if($type && $tag_code)
		{			
			// code to tag
			$found = null;
			foreach($this->getSubTreeTags() as $tags)
			{
				foreach(array_keys($tags) as $tag)
				{
					if(md5($tag) == $tag_code)
					{
						$found = $tag;
						break(2);
					}
				}
			}
			if($found)
			{
				/* single select
				if(is_array($a_saved[$type]) &&
					in_array($found, $a_saved[$type]))
				{
					return;
				}			
				return array($type=>array($found));			 
				*/			
				// multi select
				if(is_array($a_saved[$type]) &&
					in_array($found, $a_saved[$type]))
				{
					$key = array_search($found, $a_saved[$type]);
					unset($a_saved[$type][$key]);
					if(!sizeof($a_saved[$type]))
					{
						unset($a_saved[$type]);
					}
				}	
				else
				{
					$a_saved[$type][] = $found;
				}
			}
			return $a_saved;
		}
	}
	
	public function setSelection($a_value)
	{
		$this->selection = $a_value;
	}
	
	public function getFilteredObjects()
	{	
		global $ilUser;
		
		if(!$this->selection)
		{
			return;
		}
		
		include_once "Services/Tagging/classes/class.ilTagging.php"; 	
		
		$types = array("personal");
		if($this->enable_all_users)
		{
			$types[] = "other";
		}			
				
		$found = array();
		foreach($types as $type)
		{
			if(is_array($this->selection[$type]))
			{				
				$invert = ($type == "personal")
					? false
					: true;
				
				foreach($this->selection[$type] as $tag)
				{
					$found[$tag] = array_keys(ilTagging::_findObjectsByTag($tag, $ilUser->getId(), $invert));		
				}
			}
		}
						
		/* OR		
		$res = array();
		foreach($found as $tag => $ids)
		{
			$res = array_merge($res, $ids);
		}
		*/
		
		// AND
		$res = null;
		foreach($found as $tag => $ids)
		{
			if($res === null)
			{
				$res = $ids;
			}
			else
			{
				$res = array_intersect($res, $ids);
			}
		}
				
		if(sizeof($res))
		{
			return array_unique($res);
		}
	}
				
	protected function getSubTreeTags()
	{
		global $tree, $ilUser;
		
		$sub_ids = array();
		foreach($tree->getSubTree($tree->getNodeData($this->parent_ref_id)) as $sub_item)
		{
			if($sub_item["ref_id"] != $this->parent_ref_id &&
				$sub_item["type"] != "rolf" &&
				!$tree->isDeleted($sub_item["ref_id"]))
			{				
				$sub_ids[$sub_item["obj_id"]] = $sub_item["type"];
			}
		}
		
		if($sub_ids)
		{
			$only_user = $this->enable_all_users 
				? null
				: $ilUser->getId();
			
			include_once "Services/Tagging/classes/class.ilTagging.php"; 
			return ilTagging::_getTagCloudForObjects($sub_ids, $only_user, $ilUser->getId());						
		}
	}	
	
	public function initListGUI(ilObjectListGUI $a_list_gui)
	{
		$a_list_gui->enableTags(true);
	}
}