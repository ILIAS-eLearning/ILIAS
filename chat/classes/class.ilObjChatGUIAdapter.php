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
	| along with this program; if not, write to the Free Software        
         |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


/**
* Class ilObjChatGUIAdapter
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjChatGUIAdapter.php,v 1.2 2005/07/29 13:03:16 smeyer Exp $
* 
* @extends ilObjectGUI
*/

require_once "./classes/class.ilObjectGUIAdapter.php";

class ilObjChatGUIAdapter extends ilObjectGUIAdapter
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjChatGUIAdapter($a_ref_id,$a_cmd = '')
	{
		define("ILIAS_MODULE","chat");

		parent::ilObjectGUIAdapter($a_ref_id,true,false,$a_cmd);
		$this->gui_obj->setTabTargetScript("chat_rep.php");
		$this->gui_obj->setInModule(true);
		$this->gui_obj->setTargetScript("chat_rep.php");
		$this->__setStyleSheetLocation();
		$this->__setReturnLocation();
		$this->__setFormAction();
		$this->__prepareOutput();

		// FINALLY PERFORM ACTION
		$this->performAction();
	}

	
	// PRIVATE METHODS
	function __prepareOutput()
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		$title = $this->gui_obj->object->getTitle();

		// catch feedback message
		sendInfo();

		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->setAdminTabs();
		$this->__showLocator();
	}

	function __setReturnLocation()
	{
		
		$this->gui_obj->setReturnLocation("permSave","chat_rep.php?cmd=perm&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("addRole","chat_rep.php?cmd=perm&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("addRole","chat_rep.php?cmd=perm&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("cancel","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("update","chat_rep.php?cmd=edit&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("uploadFile","chat_rep.php?cmd=edit&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("members","chat_rep.php?cmd=members&ref_id=".$this->getId());

		$this->gui_obj->setReturnLocation("view","chat_rep.php?ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("save","chat_rep.php?cmd=view&ref_id=".$current_ref_id);
		$this->gui_obj->setReturnLocation("cut","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("clear","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("copy","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("link","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("paste","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("cancelDelete","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("confirmedDelete","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("removeFromSystem","chat_rep.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("undelete","chat_rep.php?cmd=view&ref_id=".$this->getId());
	}

	function __setFormAction()
	{
		$this->gui_obj->setFormAction("update","chat_rep.php?cmd=gateway&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("gateway","chat_rep.php?cmd=gateway&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("permSave","chat_rep.php?cmd=permSave&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("addRole","chat_rep.php?cmd=addRole&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("updateMembers","chat_rep.php?cmd=updateMembers&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("newMembers","chat_rep.php?cmd=newMembers&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("downloadFile","chat_rep.php?cmd=downloadFile&ref_id=".$this->getId());
	}
	function __showLocator()
	{
		$path_info = $this->gui_obj->tree->getPathFull($this->getId());

		$this->tpl->addBlockFile("LOCATOR","locator","tpl.locator.html");

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("LINK_ITEM","../repository.php");
		$this->tpl->setVariable("ITEM",$this->lng->txt("repository"));
		$this->tpl->parseCurrentBlock();

		$repository_link = true;
		for($i = 1; $i < count($path_info); ++$i)
		{
			if($path_info[$i]["child"] == $this->getId())
			{
				$repository_link = false;
			}
			$this->tpl->touchBlock("locator_separator_prefix");
			$this->tpl->setCurrentBlock("locator_item");
			if($repository_link)
			{
				$this->tpl->setVariable("LINK_ITEM","../repository.php?ref_id=".$path_info[$i]["child"]);
			}
			else
			{
				$this->tpl->setVariable("LINK_ITEM","./chat_rep.php?ref_id=".$path_info[$i]["child"]);
			}
			$this->tpl->setVariable("ITEM",$path_info[$i]["title"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}

	function __setStyleSheetLocation()
	{
		$this->tpl->setCurrentBlock("ChatStyle");
		$this->tpl->setVariable("LOCATION_CHAT_STYLESHEET",ilUtil::getStyleSheetLocation());
		$this->tpl->parseCurrentBlock();
	}
		
} // END class.ilChatGUIAdapter
?>