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

include_once("./Services/Tagging/classes/class.ilTagging.php");

/**
* Class ilTaggingGUI. User interface class for tagging engine.
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesTagging
*/
class ilTaggingGUI
{
	/**
	* Set Object.
	*
	* @param	int			$a_obj_id			Object ID
	* @param	string		$a_obj_type			Object Type
	* @param	int			$a_sub_obj_id		Subobject ID
	* @param	string		$a_sub_obj_type		Subobject Type
	*/
	function setObject($a_obj_id, $a_obj_type, $a_sub_obj_id = 0, $a_sub_obj_type = "")
	{
		global $ilUser;

		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->sub_obj_id = $a_sub_obj_id;
		$this->sub_obj_type = $a_sub_obj_type;
		
		$this->setSaveCmd("saveTags");
		$this->setUserId($ilUser->getId());
		$this->setInputFieldName("il_tags");
	}
	
	/**
	* Set User ID.
	*
	* @param	int	$a_userid	User ID
	*/
	function setUserId($a_userid)
	{
		$this->userid = $a_userid;
	}

	/**
	* Get User ID.
	*
	* @return	int	User ID
	*/
	function getUserId()
	{
		return $this->userid;
	}

	/**
	* Set Save Command.
	*
	* @param	string	$a_savecmd	Save Command
	*/
	function setSaveCmd($a_savecmd)
	{
		$this->savecmd = $a_savecmd;
	}

	/**
	* Get Save Command.
	*
	* @return	string	Save Command
	*/
	function getSaveCmd()
	{
		return $this->savecmd;
	}

	/**
	* Set Input Field Name.
	*
	* @param	string	$a_inputfieldname	Input Field Name
	*/
	function setInputFieldName($a_inputfieldname)
	{
		$this->inputfieldname = $a_inputfieldname;
	}

	/**
	* Get Input Field Name.
	*
	* @return	string	Input Field Name
	*/
	function getInputFieldName()
	{
		return $this->inputfieldname;
	}

	/**
	* Get Input HTML for Tagging of an object (and a user)
	*/
	function getTaggingInputHTML()
	{
		global $lng, $ilCtrl;
		
		$ttpl = new ilTemplate("tpl.tags_input.html", true, true, "Services/Tagging");
		$tags = ilTagging::getTagsForUserAndObject($this->obj_id, $this->obj_type,
			$this->sub_obj_id, $this->sub_obj_type, $this->getUserId());
		$ttpl->setVariable("VAL_TAGS",
			ilUtil::prepareFormOutput(implode($tags, " ")));
		$ttpl->setVariable("TXT_SAVE", $lng->txt("save"));
		$ttpl->setVariable("CMD_SAVE", $this->savecmd);
		$ttpl->setVariable("NAME_TAGS", $this->getInputFieldName());
		
		return $ttpl->get();
	}
	
	/**
	* Save Input
	*/
	function saveInput()
	{
		$input = ilUtil::stripSlashes($_POST[$this->getInputFieldName()]);
		$itags = explode(" ", $input);
		$tags = array();
		foreach($itags as $itag)
		{
			$itag = trim($itag);
			if (!in_array($itag, $tags))
			{
				$tags[] = $itag;
			}
		}

		ilTagging::writeTagsForUserAndObject($this->obj_id, $this->obj_type,
			$this->sub_obj_id, $this->sub_obj_type, $this->getUserId(), $tags);
	}
	
	/**
	* Get Input HTML for Tagging of an object (and a user)
	*/
	function getAllUserTagsForObjectHTML()
	{
		global $lng, $ilCtrl;
		
		$ttpl = new ilTemplate("tpl.tag_cloud.html", true, true, "Services/Tagging");
		$tags = ilTagging::getTagsForObject($this->obj_id, $this->obj_type,
			$this->sub_obj_id, $this->sub_obj_type);
			
		$max = 1;
		foreach ($tags as $tag)
		{
			$max = max($max, $tag["cnt"]);
		}
		reset($tags);
		foreach ($tags as $tag)
		{
			$ttpl->setCurrentBlock("unlinked_tag");
			$ttpl->setVariable("FONT_SIZE", ilTagging::calculateFontSize($tag["cnt"], $max)."%");
			$ttpl->setVariable("TAG_TITLE", $tag["tag"]);
			$ttpl->parseCurrentBlock();
		}
		
		return $ttpl->get();
	}

}

?>
