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

include_once "classes/class.ilObjectListGUI.php";

/**
* Class ilObjLinkResourceListGUI
*
* @author 		Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesWebResource
*/
class ilObjLinkResourceListGUI extends ilObjectListGUI
{
	var $single_link = null;
	var $link_data = array();

	/**
	* constructor
	*
	*/
	function ilObjLinkResourceListGUI()
	{
		$this->ilObjectListGUI();

	}

	/**
	* overwritten from base class
	*/
	function getTitle()
	{
		if($this->__checkDirectLink())
		{
			$this->__readLink();
			
			return $this->link_data['title'];
		}
		return parent::getTitle();
	}
	/**
	* overwritten from base class
	*/
	function getDescription()
	{
		if($this->__checkDirectLink())
		{
			$this->__readLink();
			
			return $this->link_data['description'];
		}
		return parent::getDescription();
	}

	/**
	* initialisation
	*/
	function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->type = "webr";
		$this->gui_class_name = "ilobjlinkresourcegui";
		$this->info_screen_enabled = true;
		
		// general commands array
		include_once('class.ilObjLinkResourceAccess.php');
		$this->commands = ilObjLinkResourceAccess::_getCommands();
	}


	/**
	* inititialize new item
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
	}


	/**
	* Get command target frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		switch($a_cmd)
		{
			case "":
				if($this->__checkDirectLink())
				{
					$frame = '_blank';
				}
				else
				{
					$frame = ilFrameTargetInfo::_getFrame("RepositoryContent");
				}
				break;

			default:
		}

		return $frame;
	}



	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $ilUser;

		$props = array();

		return $props;
	}


	/**
	* Get command link url.
	*
	* @param	int			$a_ref_id		reference id
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{

		// separate method for this line
		switch($a_cmd)
		{
			case '':
				if($this->__checkDirectLink())
				{
					$this->__readLink();
					$cmd_link = $this->link_data['target'];
				}
				else
				{
					$cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=".$this->ref_id."&cmd=$a_cmd";
				}
				break;

			default:
				$cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=".$this->ref_id."&cmd=$a_cmd";
		}
		return $cmd_link;
	}


	/**
	* Check whether there is only one active link in the web resource.
	* In this case this link is shown in a new browser window
	*
	*/
	function __checkDirectLink()
	{
		if(isset($this->single_link[$this->obj_id]))
		{
			return $this->single_link[$this->obj_id];
		}
		include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
		return $this->single_link[$this->obj_id] = ilLinkResourceItems::_isSingular($this->obj_id);
	}

	/**
	* Get data of first active link resource
	*
	* @return array link data array
	*/
	function __readLink()
	{
		include_once './Modules/WebResource/classes/class.ilLinkResourceItems.php';
		include_once './Modules/WebResource/classes/class.ilParameterAppender.php';

		if(ilParameterAppender::_isEnabled())
		{
			return $this->link_data = ilParameterAppender::_append($tmp =& ilLinkResourceItems::_getFirstLink($this->obj_id));
		}
		return $this->link_data = ilLinkResourceItems::_getFirstLink($this->obj_id);
	}
} // END class.ilObjTestListGUI
?>
