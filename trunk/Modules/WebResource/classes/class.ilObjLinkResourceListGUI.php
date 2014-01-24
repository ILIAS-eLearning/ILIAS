<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once "Services/Object/classes/class.ilObjectListGUI.php";
include_once('./Modules/WebResource/classes/class.ilObjLinkResourceAccess.php');

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
		if(ilObjLinkResourceAccess::_checkDirectLink($this->obj_id))
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
		global $ilSetting;
	
		if(ilObjLinkResourceAccess::_checkDirectLink($this->obj_id))
		{
			$this->__readLink();
			
			$desc = $this->link_data['description'];
			
			// #10682
			if($ilSetting->get("rep_shorten_description"))
			{
				$desc = ilUtil::shortenText($desc,
					$ilSetting->get("rep_shorten_description_length"), true);
			}
			
			return $desc;
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
		$this->copy_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->type = "webr";
		$this->gui_class_name = "ilobjlinkresourcegui";
		$this->info_screen_enabled = true;
		
		// general commands array
		$this->commands = ilObjLinkResourceAccess::_getCommands();
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
				if(ilObjLinkResourceAccess::_checkDirectLink($this->obj_id))
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
		if($_REQUEST["wsp_id"] || $_REQUEST["cmdClass"] == "ilpersonalworkspacegui")
		{
			if(ilObjLinkResourceAccess::_checkDirectLink($this->obj_id) && $a_cmd == '')
			{
				$a_cmd = "calldirectlink";
			}			
			$this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", "");
			$this->ctrl->setParameterByClass($this->gui_class_name, "wsp_id", $this->ref_id);
			return $this->ctrl->getLinkTargetByClass(array("ilpersonalworkspacegui", $this->gui_class_name), $a_cmd);
		}		
		else
		{
			// separate method for this line
			switch($a_cmd)
			{
				case '':
					if(ilObjLinkResourceAccess::_checkDirectLink($this->obj_id))
					{
						$this->__readLink();
						// $cmd_link = $this->link_data['target'];
						$cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=".$this->ref_id."&cmd=calldirectlink";
					}
					else
					{
						$cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=".$this->ref_id."&cmd=$a_cmd";
					}
					break;

				default:
					$cmd_link = "ilias.php?baseClass=ilLinkResourceHandlerGUI&ref_id=".$this->ref_id."&cmd=$a_cmd";
			}			
		}
		return $cmd_link;
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
