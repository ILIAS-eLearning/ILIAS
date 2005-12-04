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
* Class ilObjExerciseGUIAdapter
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$Id: class.ilObjExerciseGUIAdapter.php,v 1.5 2005/11/21 17:12:08 shofmann Exp $
* 
* @extends ilObjectGUIAdapter
* @package ilias-core
*/

require_once "class.ilObjectGUIAdapter.php";

class ilObjExerciseGUIAdapter extends ilObjectGUIAdapter
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjExerciseGUIAdapter($a_ref_id,$a_cmd = '')
	{
		parent::ilObjectGUIAdapter($a_ref_id,true,false,$a_cmd);
		//$this->gui_obj->setTabTargetScript("exercise.php");
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

		$this->tpl->setCurrentBlock("header_image");
		$this->tpl->setVariable("IMG_HEADER", ilUtil::getImagePath("icon_exc_b.gif"));
		$this->tpl->parseCurrentBlock();
		if (!empty($title))
		{
			$this->tpl->setVariable("HEADER", $title);
		}

		$this->gui_obj->setTabs();
		//$this->setAdminTabs();
		$this->__showLocator();
	}

	function __setReturnLocation()
	{
		
		$this->gui_obj->setReturnLocation("permSave","exercise.php?cmd=perm&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("addRole","exercise.php?cmd=perm&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("addRole","exercise.php?cmd=perm&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("cancel","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("update","exercise.php?cmd=edit&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("uploadFile","exercise.php?cmd=edit&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("members","exercise.php?cmd=members&ref_id=".$this->getId());

		$this->gui_obj->setReturnLocation("save","exercise.php?cmd=view&ref_id=".$current_ref_id);
		$this->gui_obj->setReturnLocation("cut","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("clear","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("copy","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("link","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("paste","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("cancelDelete","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("confirmedDelete","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("removeFromSystem","exercise.php?cmd=view&ref_id=".$this->getId());
		$this->gui_obj->setReturnLocation("undelete","exercise.php?cmd=view&ref_id=".$this->getId());
	}

	function __setFormAction()
	{
		$this->gui_obj->setFormAction("permSave","exercise.php?cmd=permSave&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("addRole","exercise.php?cmd=addRole&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("gateway","exercise.php?cmd=gateway&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("updateMembers","exercise.php?cmd=updateMembers&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("newMembers","exercise.php?cmd=newMembers&ref_id=".$this->getId());
		$this->gui_obj->setFormAction("downloadFile","exercise.php?cmd=downloadFile&ref_id=".$this->getId());
	}
	function __showLocator()
	{
		$path_info = $this->gui_obj->tree->getPathFull($this->getId());

		$this->tpl->addBlockFile("LOCATOR","locator","tpl.locator.html");

		$this->tpl->setCurrentBlock("locator_item");
		$this->tpl->setVariable("LINK_ITEM","./repository.php");
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
				$this->tpl->setVariable("LINK_ITEM","./repository.php?ref_id=".$path_info[$i]["child"]);
			}
			else
			{
				$this->tpl->setVariable("LINK_ITEM","./exercise.php?ref_id=".$path_info[$i]["child"]);
			}
			$this->tpl->setVariable("ITEM",$path_info[$i]["title"]);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock("locator");
		$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}
		
} // END class.ilExerciseGUIAdapter
?>