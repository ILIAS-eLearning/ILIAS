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


include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjGroupListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/
class ilObjGroupListGUI extends ilObjectListGUI
{

	/**
	* initialisation
	*
	* this method should be overwritten by derived classes
	*/
	function init()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->copy_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "grp";
		$this->gui_class_name = "ilobjgroupgui";

		// general commands array
		include_once('./Modules/Group/classes/class.ilObjGroupAccess.php');
		$this->commands = ilObjGroupAccess::_getCommands();
	}

	/**
	* Overwrite this method, if link target is not build by ctrl class
	* (e.g. "lm_presentation.php", "forum.php"). This is the case
	* for all links now, but bringing everything to ilCtrl should
	* be realised in the future.
	*
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{
		global $ilCtrl;
		
		switch($a_cmd)
		{
			// BEGIN WebDAV: Mount Webfolder.
			case 'mount_webfolder' :
				require_once ('Services/WebDAV/classes/class.ilDAVActivationChecker.php');
				if (ilDAVActivationChecker::_isActive())
				{
					require_once ('Services/WebDAV/classes/class.ilDAVServer.php');
					$davServer = ilDAVServer::getInstance();
					
					// XXX: The following is a very dirty, ugly trick. 
					//        To mount URI needs to be put into two attributes:
					//        href and folder. This hack returns both attributes
					//        like this:  http://...mount_uri..." folder="http://...folder_uri...
					$cmd_link = $davServer->getMountURI($this->ref_id).
								'" folder="'.$davServer->getFolderURI($this->ref_id);
				break;
				} // fall through if plugin is not active
			// END Mount Webfolder.

			case "edit":
			default:
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
				$cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				break;
		}

		return $cmd_link;
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
		global $lng, $rbacsystem,$ilUser;

		// BEGIN WebDAV get parent properties
		$props = parent::getProperties();
		// END WebDAV get parent properties
		
		include_once './Modules/Group/classes/class.ilObjGroupAccess.php';
		$info = ilObjGroupAccess::lookupRegistrationInfo($this->obj_id);
//var_dump($info);
		if($info['reg_info_list_prop'])
		{
			$props[] = array(
				'alert' => false,
				'newline' => true,
				'property' => $info['reg_info_list_prop']['property'],
				'value' => $info['reg_info_list_prop']['value']
			);
		}
		if($info['reg_info_list_prop_limit'])
		{
			
			$props[] = array(
				'alert' => false,
				'newline' => false,
				'property' => $info['reg_info_list_prop_limit']['property'],
				'propertyNameVisible' => strlen($info['reg_info_list_prop_limit']['property']) ? true : false,
				'value' => $info['reg_info_list_prop_limit']['value']
			);
		}
		
		
		
		// waiting list
		include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
		if(ilGroupWaitingList::_isOnList($ilUser->getId(),$this->obj_id))
		{
			$props[] = array(
				"alert" 	=> true,
				"property" 	=> $lng->txt('member_status'),
				"value"		=> $lng->txt('on_waiting_list')
			);
		}
		

		return $props;
	}

	// BEGIN WebDAV mount_webfolder in _blank frame
	/**
	* Get command target frame.
	*
	* Overwrite this method if link frame is not current frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		// begin-patch fm
		return parent::getCommandFrame($a_cmd);
		// end-patch fm
	}
	
	
	/**
	 * Workaround for course titles (linked if join or read permission is granted)
	 * @param type $a_permission
	 * @param type $a_cmd
	 * @param type $a_ref_id
	 * @param type $a_type
	 * @param type $a_obj_id
	 * @return type
	 */
	public function checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id = "")
	{
		if($a_permission == 'grp_linked')
		{
			return 
				parent::checkCommandAccess('read', '', $a_ref_id, $a_type, $a_obj_id) ||
				parent::checkCommandAccess('join', 'join', $a_ref_id, $a_type, $a_obj_id);
		}
		return parent::checkCommandAccess($a_permission, $a_cmd, $a_ref_id, $a_type, $a_obj_id);
	}
	
	// END WebDAV mount_webfolder in _blank frame
} // END class.ilObjGroupListGUI
?>
