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
		
	public function render(array &$a_html, $a_parent_gui, $a_parent_cmd, $a_target_gui, $a_target_cmd)
	{		
		global $ilCtrl, $lng;
		
		$all_tags = $this->getSubTreeTags();				
		if($all_tags)
		{						
			// see ilPDTaggingBlockGUI::getTagCloud();
			
		    $map = array(
				"personal" => $lng->txt("tagging_my_tags"),
				"other" =>  $lng->txt("tagging_all_users")
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
						$ilCtrl->setParameterByClass($a_target_gui, "tag", rawurlencode($tag));
						$tpl->setVariable("HREF_TAG",
							$ilCtrl->getLinkTargetByClass($a_target_gui, $a_target_cmd));
						$tpl->setVariable("TAG_TITLE", $tag);
						$tpl->setVariable("FONT_SIZE",
							ilTagging::calculateFontSize($counter, $max)."%");

						if($this->selection == $tag)
						{
							$tpl->setVariable("TAG_CLASS", ' class="ilHighlighted"');
						}

						$tpl->parseCurrentBlock();
					}
					$tpl->setVariable("CLOUD_STYLE", ' class="small"');

					$a_html[] = array(
						"title" => $title,
						"html" => $tpl->get()
					);
				}
			}
			
			$ilCtrl->setParameterByClass($a_target_gui, "tag", "");
		}
	}
	
	
	public function importPostData()
	{
		return $_REQUEST["tag"];
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
				
		$only_user = null;		
		if(!$this->enable_all_users)
		{
			$only_user = $ilUser->getId();
		}		
		
		include_once "Services/Tagging/classes/class.ilTagging.php"; 		
		return array_keys(ilTagging::_findObjectsByTag($this->selection, $only_user));		
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
}