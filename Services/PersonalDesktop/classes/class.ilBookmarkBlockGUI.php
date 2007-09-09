<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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
* BlockGUI class for Bookmarks block
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_IsCalledBy ilBookmarkBlockGUI: ilColumnGUI
*/
class ilBookmarkBlockGUI extends ilBlockGUI
{
	static $block_type = "pdbookm";
	
	/**
	* Constructor
	*/
	function ilBookmarkBlockGUI()
	{
		global $ilCtrl, $lng, $ilUser;
		
		parent::ilBlockGUI();
		
		$this->setImage(ilUtil::getImagePath("icon_bm_s.gif"));
		$this->setTitle($lng->txt("my_bms"));
		$this->setEnableNumInfo(false);
		$this->setLimit(99999);
		$this->setAvailableDetailLevels(3);
		
		$this->id = (empty($_GET["bmf_id"]))
			? $bmf_id = 1
			: $_GET["bmf_id"];
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
		
		include_once("./Services/PersonalDesktop/classes/class.ilBookmarkFolder.php");
		$bm_items = ilBookmarkFolder::_getNumberOfObjects();
		$this->num_bookmarks = $bm_items["bookmarks"];
		$this->num_folders = $bm_items["folders"];

		if ($this->getCurrentDetailLevel() > 1 &&
			($this->num_bookmarks > 0 || $this->num_folders > 0))
		{
			if ($ilUser->getPref("il_pd_bkm_mode") == 'tree')
			{
				$this->setDataSection($this->getPDBookmarkListHTMLTree());
			}
			else
			{
				$this->setRowTemplate("tpl.bookmark_pd_list.html", "Services/PersonalDesktop");
				$this->getListRowData();
				$this->setColSpan(2);
				parent::fillDataSection();
			}
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
	function getPDBookmarkListHTMLTree()
	{
		global $ilCtrl, $ilUser;
		
		include_once("./Services/PersonalDesktop/classes/class.ilBookmarkExplorer.php");
		
		$showdetails = ($this->getCurrentDetailLevel() > 2);
		$tpl = new ilTemplate("tpl.bookmark_pd_tree.html", true, true,
			"Services/PersonalDesktop");

		$exp = new ilBookmarkExplorer($ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "show"),
			$_SESSION["AccountId"]);
		$exp->setAllowedTypes(array('dum','bmf','bm'));
		$exp->setEnableSmallMode(true);
		$exp->setTargetGet("bmf_id");
		$exp->setSessionExpandVariable('mexpand');
		$ilCtrl->setParameter($this, "bmf_id", $this->id);
		$exp->setExpandTarget($ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "show"));
		if ($_GET["mexpand"] == "")
		{
			$expanded = $this->id;
		}
		else
		{
			$expanded = $_GET["mexpand"];
		}
		$exp->setExpand($expanded);
		$exp->setShowDetails($showdetails);

		// build html-output
		$exp->setOutput(0);
		return $exp->getOutput();
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
	}

	/**
	* Get list data (for flat list).
	*/
	function getListRowData()
	{
		global $ilUser, $lng, $ilCtrl;
		
		include_once("./Services/PersonalDesktop/classes/class.ilBookmarkFolder.php");

		$data = array();
		
		$bm_items = ilBookmarkFolder::getObjects($_SESSION["ilCurBMFolder"]);

		if (!ilBookmarkFolder::isRootFolder($_SESSION["ilCurBMFolder"])
			&& !empty($_SESSION["ilCurBMFolder"]))
		{			
			$ilCtrl->setParameter($this, "curBMFolder",
				ilBookmarkFolder::_getParentId($_SESSION["ilCurBMFolder"]));

			$data[] = array(
				"img" => ilUtil::getImagePath("icon_cat_s.gif"),
				"alt" => $lng->txt("bmf"),
				"title" => "..",
				"link" => $ilCtrl->getLinkTarget($this, "setCurrentBookmarkFolder"));

			$this->setTitle($this->getTitle().": ".ilBookmarkFolder::_lookupTitle($_SESSION["ilCurBMFolder"]));
		}

		foreach ($bm_items as $bm_item)
		{
			switch ($bm_item["type"])
			{
				case "bmf":
					$ilCtrl->setParameter($this, "curBMFolder", $bm_item["obj_id"]);
					$data[] = array(
						"img" => ilUtil::getImagePath("icon_cat_s.gif"),
						"alt" => $lng->txt("bmf"),
						"title" => ilUtil::prepareFormOutput($bm_item["title"]),
						"desc" => ilUtil::prepareFormOutput($bm_item["desc"]),
						"link" => $ilCtrl->getLinkTarget($this,
							"setCurrentBookmarkFolder"),
						"target" => "");
					break;

				case "bm":
					$data[] = array(
						"img" => ilUtil::getImagePath("spacer.gif"),
						"alt" => $lng->txt("bm"),
						"title" => ilUtil::prepareFormOutput($bm_item["title"]),
						"desc" => ilUtil::prepareFormOutput($bm_item["desc"]),
						"link" => ilUtil::prepareFormOutput($bm_item["target"]),
						"target" => "_blank");
					break;
			}
		}
		
		$this->setData($data);
	}
	
	/**
	* get flat bookmark list for personal desktop
	*/
	function fillRow($a_set)
	{
		global $ilUser;
		
		$this->tpl->setVariable("IMG_BM", $a_set["img"]);
		$this->tpl->setVariable("IMG_ALT", $a_set["alt"]);
		$this->tpl->setVariable("BM_TITLE", $a_set["title"]);
		$this->tpl->setVariable("BM_LINK", $a_set["link"]);
		$this->tpl->setVariable("BM_TARGET", $a_set["target"]);

		if ($this->getCurrentDetailLevel() > 2)
		{
			$this->tpl->setVariable("BM_DESCRIPTION", ilUtil::prepareFormOutput($a_set["desc"]));
		}
		else
		{
			$this->tpl->setVariable("BM_TOOLTIP", ilUtil::prepareFormOutput($a_set["desc"]));
		}
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

	/**
	* set current desktop view mode to flat
	*/
	function setPdFlatMode()
	{
		global $ilCtrl, $ilUser;

		$ilUser->writePref("il_pd_bkm_mode", 'flat');
		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
		}
	}

	/**
	* set current desktop view mode to tree
	*/
	function setPdTreeMode()
	{
		global $ilCtrl, $ilUser;
		
		$ilUser->writePref("il_pd_bkm_mode", 'tree');
		if ($ilCtrl->isAsynch())
		{
			echo $this->getHTML();
			exit;
		}
		else
		{
			$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
		}
	}

	/**
	* set current bookmarkfolder on personal desktop
	*/
	function setCurrentBookmarkFolder()
	{
		global $ilCtrl;
		
		$_SESSION["ilCurBMFolder"] = $_GET["curBMFolder"];
		$ilCtrl->redirectByClass("ilpersonaldesktopgui", "show");
	}

}

?>
