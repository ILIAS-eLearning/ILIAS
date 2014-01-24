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

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/** 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ingroup ModulesRemoteFile
*/
class ilObjRemoteFileListGUI extends ilObjectListGUI
{
	/**
	 * Constructor
	 *
	 * @access public
	 * 
	 */
	public function __construct()
	{
	 	parent::__construct();
	}
	
	/**
	 * init
	 *
	 * @access public
	 */
	public function init()
	{
		$this->copy_enabled = false;
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->info_screen_enabled = true;
		$this->type = 'rwik';
		$this->gui_class_name = 'ilobjremotefilegui';
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDSubstitution.php');
		$this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
		if($this->substitutions->isActive())
		{
			$this->substitutions_enabled = true;
		}
		
		// general commands array
		include_once('Modules/RemoteFile/classes/class.ilObjRemoteFileAccess.php');
		$this->commands = ilObjRemoteFileAccess::_getCommands();
		
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
	 * get properties (offline)
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function getProperties()
	{
		global $lng;

		include_once('Modules/RemoteFile/classes/class.ilObjRemoteFile.php');
				
		if($org = ilObjRemoteFile::_lookupOrganization($this->obj_id))
		{
			$this->addCustomProperty($lng->txt('organization'),$org,false,true);
		}
		
		$version = ilObjRemoteFile::_lookupVersionInfo($this->obj_id);
		if($version)
		{
			$this->addCustomProperty($lng->txt('version'),$version,false,true);
		}

		return array();
	}
	
	/**
	 * get command frame
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function getCommandFrame($a_cmd)
	{
		switch($a_cmd)
		{
			case 'show':
				include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
				include_once('./Services/WebServices/ECS/classes/class.ilECSImport.php');				
				if(ilECSExport::_isRemote(
					ilECSImport::lookupServerId($this->obj_id),
					ilECSImport::_lookupEContentId($this->obj_id)))
				{
					return '_blank';
				}
				
			default:
				return parent::getCommandFrame($a_cmd);
		}
	}

} // END class.ilObjRemoteFileListGUI
?>