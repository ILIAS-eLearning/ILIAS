<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("Services/Block/classes/class.ilBlockGUI.php");

/**
* ilPDTaggingBlockGUI displays personal tag cloud on personal desktop.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilPDTaggingBlockGUI: ilColumnGUI
*/
class ilPDTaggingBlockGUI extends ilBlockGUI
{
	static $block_type = "pdtag";
	
	/**
	* Constructor
	*/
	function ilPDTaggingBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		
		parent::ilBlockGUI();
		
		$lng->loadLanguageModule("tagging");
		$this->setImage(ilUtil::getImagePath("icon_tag_s.gif"));
		$this->setTitle($lng->txt("tagging_my_tags"));
		$this->setEnableNumInfo(false);
		$this->setLimit(99999);
		$this->setAvailableDetailLevels(3);
	}
	
	/**
	* Get block type
	*
	* @return	string	Block type.
	*/
	static function getBlockType()
	{
		return self::$block_type;
	}
	
	/**
	* Is block used in repository object?
	*
	* @return	string	Block type.
	*/
	static function isRepositoryObject()
	{
		return false;
	}

	
	/**
	* Get Screen Mode for current command.
	*/
	static function getScreenMode()
	{
		switch($_GET["cmd"])
		{
			case "showResourcesForTag":
				return IL_SCREEN_CENTER;
				break;

			default:
				return IL_SCREEN_SIDE;
				break;
		}
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $ilCtrl;

		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd("getHTML");

		return $this->$cmd();
	}

	function getHTML()
	{
		// workaround to show details row
		$this->setData(array("dummy"));

		if ($this->getCurrentDetailLevel() == 0)
		{
			return "";
		}
		else
		{
			return parent::getHTML();
		}
	}
	
	/**
	* Fill data section
	*/
	function fillDataSection()
	{
		global $ilUser;
		
		include_once("./Services/Tagging/classes/class.ilTagging.php");
		$this->tags = ilTagging::getTagsForUser($ilUser->getId());

		if ($this->getCurrentDetailLevel() > 1 && ($this->tags > 0))
		{
			$this->setDataSection($this->getTagCloud());
		}
		else
		{
			if ($this->num_bookmarks == 0 && $this->num_folders == 0)
			{
				$this->setEnableDetailRow(false);
			}
			$this->setDataSection($this->getOverview());
		}
	}
	
	/**
	* get tree bookmark list for personal desktop
	*/
	function getTagCloud()
	{
		global $ilCtrl, $ilUser;
		
		$showdetails = ($this->getCurrentDetailLevel() > 2);
		$tpl = new ilTemplate("tpl.tag_cloud.html", true, true,
			"Services/Tagging");
		$max = 1;
		foreach($this->tags as $tag)
		{
			$max = max($tag["cnt"], $max);
		}
		reset($this->tags);

		foreach($this->tags as $tag)
		{
			$tpl->setCurrentBlock("linked_tag");
			$ilCtrl->setParameter($this, "tag", rawurlencode($tag["tag"]));
			$tpl->setVariable("HREF_TAG",
				$ilCtrl->getLinkTarget($this, "showResourcesForTag"));
			$tpl->setVariable("TAG_TITLE", $tag["tag"]);
			$tpl->setVariable("FONT_SIZE",
				ilTagging::calculateFontSize($tag["cnt"], $max)."%");
			$tpl->parseCurrentBlock();
		}
		$tpl->setVariable("CLOUD_STYLE", ' class="small" ');
		return $tpl->get();
	}
	
	/**
	* List resources for tag
	*/
	function showResourcesForTag()
	{
		global $lng, $ilCtrl, $ilUser, $objDefinition;
		
		$tpl = new ilTemplate("tpl.resources_for_tag.html", true, true, "Services/Tagging");
		include_once("./Services/PersonalDesktop/classes/class.ilPDContentBlockGUI.php");
		$content_block = new ilPDContentBlockGUI();
		$content_block->setColSpan(2);
		$content_block->setTitle(sprintf($lng->txt("tagging_resources_for_tag"),
			"<i>".$_GET["tag"]."</i>"));
		$content_block->setImage(ilUtil::getImagePath("icon_tag.gif"));
		$content_block->addHeaderCommand($ilCtrl->getParentReturn($this),
			$lng->txt("selected_items_back"));
			
		// get resources
		include_once("./Services/Tagging/classes/class.ilTagging.php");
		$objs = ilTagging::getObjectsForTagAndUser($ilUser->getId(), $_GET["tag"]);

		foreach($objs as $key => $obj)
		{
			$ref_ids = ilObject::_getAllReferences($obj["obj_id"]);
			foreach($ref_ids as $ref_id)
			{
				$type = $obj["obj_type"];
				
				if ($type == "") continue;
				
				// get list gui class for each object type
				if (empty($this->item_list_gui[$type]))
				{
					$class = $objDefinition->getClassName($type);
					$location = $objDefinition->getLocation($type);
			
					$full_class = "ilObj".$class."ListGUI";
			
					include_once($location."/class.".$full_class.".php");
					$this->item_list_gui[$type] = new $full_class();
					$this->item_list_gui[$type]->enableDelete(false);
					$this->item_list_gui[$type]->enablePath(true);
					$this->item_list_gui[$type]->enableCut(false);
					$this->item_list_gui[$type]->enableSubscribe(false);
					$this->item_list_gui[$type]->enablePayment(false);
					$this->item_list_gui[$type]->enableLink(false);
				}
				$html = $this->item_list_gui[$type]->getListItemHTML(
					$ref_id,
					$obj["obj_id"], 
					ilObject::_lookupTitle($obj["obj_id"]),
					ilObject::_lookupDescription($obj["obj_id"]));
					
				if ($html != "")
				{
					$css = ($css != "tblrow1") ? "tblrow1" : "tblrow2";
						
					$tpl->setCurrentBlock("res_row");
					$tpl->setVariable("ROWCLASS", $css);
					$tpl->setVariable("RESOURCE_HTML", $html);
					$tpl->setVariable("ALT_TYPE", $lng->txt("obj_".$type));
					$tpl->setVariable("IMG_TYPE",
						ilUtil::getImagePath("icon_".$type.".gif"));
					$tpl->parseCurrentBlock();
				}
			}
		}
		$content_block->setContent($tpl->get());
		//$content_block->setContent("test");

		return $content_block->getHTML();
	}

	/**
	* block footer
	*/
	function fillFooter()
	{
		global $ilCtrl, $lng, $ilUser;

		$this->setFooterLinks();
		$this->fillFooterLinks();
		$this->tpl->setVariable("FCOLSPAN", $this->getColSpan());
		if ($this->tpl->blockExists("block_footer"))
		{
			$this->tpl->setCurrentBlock("block_footer");
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* Set footer links.
	*/
	function setFooterLinks()
	{
		global $ilUser, $ilCtrl, $lng;
return;
/*
		if ($this->num_bookmarks == 0 && $this->num_folders == 0)
		{
			return;
		}
		
		// flat
		if ($ilUser->getPref("il_pd_bkm_mode") == 'tree')
		{
			$this->addFooterLink( $lng->txt("flatview"),
				$ilCtrl->getLinkTarget($this, "setPdFlatMode"),
				$ilCtrl->getLinkTarget($this, "setPdFlatMode",
				"", true),
				"block_".$this->getBlockType()."_".$this->block_id);
		}
		else
		{
			$this->addFooterLink($lng->txt("flatview"));
		}

		// as tree
		if ($ilUser->getPref("il_pd_bkm_mode") == 'tree')
		{
			$this->addFooterLink($lng->txt("treeview"));
		}
		else
		{
			$this->addFooterLink($lng->txt("treeview"),
				$ilCtrl->getLinkTarget($this,
					"setPdTreeMode"),
				$ilCtrl->getLinkTarget($this,
					"setPdTreeMode", "", true),
				"block_".$this->getBlockType()."_".$this->block_id
				);
		}
*/
	}


	/**
	* Get overview.
	*/
	function getOverview()
	{
		global $ilUser, $lng, $ilCtrl;
				
		return '<div class="small">'.$this->num_bookmarks." ".$lng->txt("bm_num_bookmarks").", ".
			$this->num_folders." ".$lng->txt("bm_num_bookmark_folders")."</div>";
	}

}

?>
