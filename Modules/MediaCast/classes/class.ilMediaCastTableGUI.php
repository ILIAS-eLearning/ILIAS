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

include_once("Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for table NewsForContext
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesNews
*/
class ilMediaCastTableGUI extends ilTable2GUI
{
	protected $downloadable = false;
	function ilMediaCastTableGUI($a_parent_obj, $a_parent_cmd = "")
	{
		global $ilCtrl, $lng;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		// Check whether download-buttons will be displayed
		$mediacast = new ilObjMediaCast($a_parent_obj->id);
		$this->downloadable = $mediacast->getDownloadable();

		$this->addColumn("", "f", "1");
		$this->addColumn($lng->txt("mcst_entry"), "", "33%");
		$this->addColumn("", "", "33%");
		$this->addColumn("", "", "34%");
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_media_cast_row.html",
			"Modules/MediaCast");
		$this->setDefaultOrderField("creation_date");
		$this->setDefaultOrderDirection("desc");

	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilAccess;
		
		include_once("./Services/MediaObjects/classes/class.ilObjMediaObject.php");
		
		$news_set = new ilSetting("news");
		$enable_internal_rss = $news_set->get("enable_rss_for_internal");

		// edit link
		$ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", $a_set["id"]);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$this->tpl->setCurrentBlock("edit");
			$this->tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
			$this->tpl->setVariable("CMD_EDIT",
				$ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "editCastItem"));
			$this->tpl->setVariable("TXT_DET_PLAYTIME", $lng->txt("mcst_det_playtime"));
			$this->tpl->setVariable("CMD_DET_PLAYTIME",
				$ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "determinePlaytime"));
			$this->tpl->parseCurrentBlock();
			$this->tpl->setCurrentBlock("edit_checkbox");
			$this->tpl->setVariable("VAL_ID", $a_set["id"]);
			$this->tpl->parseCurrentBlock();
		}

			
		// access
		if ($enable_internal_rss)
		{
			$this->tpl->setCurrentBlock("access");
			$this->tpl->setVariable("TXT_ACCESS", $lng->txt("news_news_item_visibility"));
			if ($a_set["visibility"] == NEWS_PUBLIC)
			{
				$this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_public"));
			}
			else
			{
				$this->tpl->setVariable("VAL_ACCESS", $lng->txt("news_visibility_users"));
			}
			$this->tpl->parseCurrentBlock();
		}
		
		$ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", "");

		if (ilObject::_exists($a_set["mob_id"]))
		{
			if ($a_set["update_date"] != "")
			{
				$this->tpl->setCurrentBlock("last_update");
				$this->tpl->setVariable("TXT_LAST_UPDATE",
					$lng->txt("last_update"));
				$this->tpl->setVariable("VAL_LAST_UPDATE",
					ilDatePresentation::formatDate(new ilDateTime($a_set["update_date"], IL_CAL_DATETIME)));
				$this->tpl->parseCurrentBlock();
			}
			
			$mob = new ilObjMediaObject($a_set["mob_id"]);
			$med = $mob->getMediaItem("Standard");
			
			$this->tpl->setVariable("VAL_TITLE",
				$a_set["title"]);
			$this->tpl->setVariable("VAL_DESCRIPTION",
				$a_set["content"]);
			$this->tpl->setVariable("TXT_FILENAME",
				$lng->txt("filename"));
			$this->tpl->setVariable("VAL_FILENAME",
				$mob->getTitle());
			$this->tpl->setVariable("TXT_CREATED",
				$lng->txt("created"));
			$this->tpl->setVariable("VAL_CREATED",
				ilDatePresentation::formatDate(new ilDateTime($a_set["creation_date"], IL_CAL_DATETIME)));
			$this->tpl->setVariable("TXT_DURATION",
				$lng->txt("mcst_play_time"));
			$this->tpl->setVariable("VAL_DURATION",
				$a_set["playtime"]);
			if ($this->downloadable) {
				$ilCtrl->setParameterByClass("ilobjmediacastgui", "item_id", $a_set["id"]);
				// to keep always the order of the purposes
				// iterate through purposes and display the according mediaitems 				
				foreach (ilObjMediaCast::$purposes as $purpose) 
				{
    				$a_mob = $mob->getMediaItem($purpose);
    				if (!is_object($a_mob))
    				    continue;
  					$ilCtrl->setParameterByClass("ilobjmediacastgui", "purpose", $a_mob->getPurpose());
					$file = ilObjMediaObject::_lookupItemPath($a_mob->getMobId(), false, false, $a_mob->getPurpose());
					if (is_file($file))
					{
						$size = filesize($file);
						$size = sprintf("%.1f MB",$size/1024/1024);
					}
					$format = ($a_mob->getFormat()!= "")?$a_mob->getFormat():"audio/mpeg";					
   					$this->tpl->setCurrentBlock("downloadable");
   					$this->tpl->setVariable("TXT_DOWNLOAD", $lng->txt("mcst_download_" . strtolower($a_mob->getPurpose())));
   					$this->tpl->setVariable("CMD_DOWNLOAD", $ilCtrl->getLinkTargetByClass("ilobjmediacastgui", "downloadItem"));
   					$this->tpl->setVariable("TITLE_DOWNLOAD", "(".$format.", ".$size.")");
   					$this->tpl->parseCurrentBlock();
				}
			}
				
			include_once("./Services/MediaObjects/classes/class.ilMediaPlayerGUI.php");
			$mpl = new ilMediaPlayerGUI();
			if (is_object($med))
			{
			    if (strcasecmp("Reference", $med->getLocationType()) == 0)
			        $mpl->setFile($med->getLocation());
			    else
			       $mpl->setFile(ilObjMediaObject::_getURL($mob->getId())."/".$med->getLocation());
			    $mpl->setMimeType ($med->getFormat());
			    $mpl->setDisplayHeight($med->getHeight());
			}

			$this->tpl->setVariable("PLAYER", $mpl->getMp3PlayerHtml());

		}
		
	}	

}
?>
