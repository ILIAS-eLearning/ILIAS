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


/**
* Class ilForumLocatorGUI
* core export functions for forum
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForumLocatorGUI
{
	var $mode;
	var $temp_var;
	var $tree;
	var $obj;
	var $lng;
	var $tpl;
	var $frm;
	var $thread_id;
	var $thread_subject;
	var $show_user;

	function ilForumLocatorGUI()
	{
		global $lng, $tpl, $tree;

		$this->tree =& $tree;
		$this->mode = "std";
		$this->temp_var = "LOCATOR";
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->show_user = false;
	}


	function setTemplateVariable($a_temp_var)
	{
		$this->temp_var = $a_temp_var;
	}

	function setRefId($a_ref_id)
	{
		$this->ref_id = $a_ref_id;
	}

	function setThread($a_id, $a_subject)
	{
		$this->thread_id = $a_id;
		$this->thread_subject = $a_subject;
	}

	function setForum(&$a_frm)
	{
		$this->frm =& $a_frm;
	}

	function showUser($a_show)
	{
		$this->show_user = $a_show;
	}

	/**
	* display locator
	*/
	function display()
	{
		global $lng;

		$this->tpl->addBlockFile($this->temp_var, "locator", "tpl.locator.html");

		$path = $this->tree->getPathFull($this->ref_id);

		$modifier = 1;
		
		//$this->tpl->touchBlock("locator_separator");
		//$this->tpl->touchBlock("locator_item");

		foreach ($path as $key => $row)
		{
			if ($this->ref_id == $row["child"] && is_object($this->frm))
			{
				if (!is_array($topicData = $this->frm->getOneTopic()))
				{
					continue;
				}
			}

			/*
			if ($row["child"] == $this->tree->getRootId())
			{
				continue;
			}*/

			//if (($key < count($path)-$modifier) || (!empty($this->thread_id))
			//	|| $this->show_user)
			if (($key < count($path)-$modifier))
			{
				$this->tpl->touchBlock("locator_separator");
				//$this->tpl->touchBlock("locator_item");
			}
			
			if ($row["child"] > 0)
			{
				$icon_path = ilObject::_getIcon($row['obj_id'], 'tiny', $row['type']);
				
				$this->tpl->setCurrentBlock("locator_img");
				$this->tpl->setVariable("IMG_SRC", $icon_path);
				#$this->tpl->setVariable("IMG_SRC", ilUtil::getImagePath("icon_".$row["type"]."_s.gif"));
				$this->tpl->setVariable("IMG_ALT", $lng->txt("obj_".$row['type']));
				$this->tpl->parseCurrentBlock();
			}

			
			$this->tpl->setCurrentBlock("locator_item");
			if ($row["child"] == $this->tree->getRootId())
			{
				$title = $this->lng->txt("repository");
				$link = "repository.php?ref_id=".$row["child"]
					."&amp;cmd=frameset";
			}
			else if (($this->ref_id == $row["child"]) && (is_object($this->frm)))
			{
				$title = $row["title"];
				#$link = "forums_threads_liste.php?ref_id=".$row["child"];
				$link = "repository.php?ref_id=".$row["child"];
			}
			else
			{
				$title = $row["title"];
				if ($row["type"] == "frm")
				{
					$link = "repository.php?ref_id=".$row["child"];
				}
				else
				{
					$link = "repository.php?ref_id=".$row["child"]
						."&amp;cmd=frameset";
				}
			}
			$this->tpl->setVariable("ITEM", $title);
			$this->tpl->setVariable("LINK_ITEM", $link);
			$t_frame = ilFrameTargetInfo::_getFrame("MainContent");
			$this->tpl->setVariable("LINK_TARGET", "target=\"$t_frame\"");
			$this->tpl->parseCurrentBlock();
		}

		/*
		if (!empty($this->thread_id))
		{
			if ($this->show_user)
			{
				$this->tpl->touchBlock("locator_separator");
			}
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $this->thread_subject);
			$this->tpl->setVariable("LINK_ITEM", "repository.php?cmd=viewThread&cmdClass=ilobjforumgui&thr_pk=".
				$this->thread_id."&ref_id=".$this->ref_id);
			$this->tpl->parseCurrentBlock();
		}*/

		/*
		if ($this->show_user)
		{
			$this->tpl->setCurrentBlock("locator_item");
			$this->tpl->setVariable("ITEM", $lng->txt("userdata"));
			$this->tpl->setVariable("LINK_ITEM", "");
			//$t_frame = ilFrameTargetInfo::_getFrame("RepositoryContent", "frm");
			//$this->tpl->setVariable("LINK_TARGET","target=\"$t_frame\"");
			$this->tpl->parseCurrentBlock();
		}*/

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}


} // END class ilForumLocatorGUI
