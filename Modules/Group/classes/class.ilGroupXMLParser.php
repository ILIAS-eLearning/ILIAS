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

require_once("./Services/Xml/classes/class.ilSaxParser.php");
require_once('./Services/User/classes/class.ilObjUser.php');
include_once('./Services/Calendar/classes/class.ilDateTime.php');
include_once('./Modules/Group/classes/class.ilGroupParticipants.php');


/**
 * Group Import Parser
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: class.ilGroupXMLParser.php 15678 2008-01-06 20:40:55Z akill $
 *
 * @extends ilSaxParser

 */
class ilGroupXMLParser extends ilSaxParser
{
	public static $CREATE = 1;
	public static $UPDATE = 2;
	
	private $participants = null;
	private $current_container_setting;

	var $group_data;
	var $group_obj;

	var $parent;
	var $counter;

	var $mode;
	var $grp;

	/**
	 * Constructor
	 *
	 * @param	string		$a_xml_file		xml file
	 *
	 * @access	public
	 */

	function ilGroupXMLParser($a_xml, $a_parent_id)
	{
		define('EXPORT_VERSION',2);

		parent::ilSaxParser(null);

		$this->mode =  ilGroupXMLParser::$CREATE;
		$this->grp = null;

		$this->setXMLContent($a_xml);

		// SET MEMBER VARIABLES
		$this->__pushParentId($a_parent_id);


	}

	function __pushParentId($a_id)
	{
		$this->parent[] = $a_id;
	}
	function __popParentId()
	{
		array_pop($this->parent);

		return true;
	}
	function __getParentId()
	{
		return $this->parent[count($this->parent) - 1];
	}

	/**
	 * set event handler
	 * should be overwritten by inherited class
	 * @access	private
	 */
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	 * start the parser
	 */
	function startParsing()
	{
		parent::startParsing();

		if ($this->mode == ilGroupXMLParser::$CREATE)
		{
			return is_object($this->group_obj) ? $this->group_obj->getRefId() : false;
		}
		else
		{
			return is_object($this->group_obj) ? $this->group_obj->update() : false;
		}
	}


	/**
	 * handler for begin of element
	 */
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		global $ilErr;

		switch($a_name)
		{
			// GROUP DATA
			case "group":
				#if($a_attribs["exportVersion"] < EXPORT_VERSION)
				#{
				#	$ilErr->raiseError("!!! This export Version isn't supported, update your ILIAS 2 installation"
				#					   ,$ilErr->WARNING);
				#}
				// DEFAULT
				$this->group_data["admin"] = array();
				$this->group_data["member"] = array();

				$this->group_data["type"] = $a_attribs["type"];
				$this->group_data["id"] = $a_attribs["id"];

				break;

			case 'title':
				break;
				
			case "owner":
				$this->group_data["owner"] = $a_attribs["id"];
				break;

			case 'registration':
				$this->group_data['registration_type'] = $a_attribs['type'];
				$this->group_data['waiting_list_enabled'] = $a_attribs['waitingList'] == 'Yes' ? true : false;
				break;
				
			case 'maxMembers':
				$this->group_data['max_members_enabled'] = $a_attribs['enabled'] == 'Yes' ? true : false;
				break; 

			case "admin":
				if (!isset($a_attribs['action']) || $a_attribs['action'] == "Attach")
				{
					$this->group_data["admin"]["attach"][] = $a_attribs["id"];
				} 
				elseif (isset($a_attribs['action']) || $a_attribs['action'] == "Detach")
				{
					$this->group_data["admin"]["detach"][] = $a_attribs["id"];
				}
				
				if(isset($a_attribs['notification']) and $a_attribs['notification'] == 'Yes')
				{
					$this->group_data['notifications'][] = $a_attribs['id'];
				}
				
				break;

			case "member":
				if (!isset($a_attribs['action']) || $a_attribs['action'] == "Attach")
				{
					$GLOBALS['ilLog']->write(__METHOD__.': new member with id '.$a_attribs['id']);
					$this->group_data["member"]["attach"][] = $a_attribs["id"];
				} elseif (isset($a_attribs['action']) || $a_attribs['action'] == "Detach")
				{
					$GLOBALS['ilLog']->write(__METHOD__.': deprecated member with id '.$a_attribs['id']);
					$this->group_data["member"]["detach"][] = $a_attribs["id"];
				}

				break;

			case "folder":
				// NOW SAVE THE NEW OBJECT (if it hasn't been imported)
				$this->__save();
				break;

			case "file":
				// NOW SAVE THE NEW OBJECT (if it hasn't been imported)
				$this->__save();

				$this->file["fileName"] = $a_attribs["fileName"];
				$this->file["id"] = $a_attribs["id"];

				// SAVE IT
				$this->__saveFile();
				break;
			
			case 'ContainerSetting':
				$this->current_container_setting = $a_attribs['id'];				
				break;

			case 'Sort':
				// NOW SAVE THE NEW OBJECT (if it hasn't been imported)
				$this->__save();
				include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
				ilContainerSortingSettings::_importContainerSortingSettings($a_attribs, $this->group_obj->getId());
				break;
		}
	}


	function handlerEndTag($a_xml_parser, $a_name)
	{
		switch($a_name)
		{
			case "title":
				$this->group_data["title"] = trim($this->cdata);
				break;

			case "description":
				$this->group_data["description"] = trim($this->cdata);
				break;
				
			case 'information':
				$this->group_data['information'] = trim($this->cdata);
				break;

			case 'password':
				$this->group_data['password'] = trim($this->cdata);
				break;
				
			case 'maxMembers':
				$this->group_data['max_members'] = trim($this->cdata);
				break;

			case 'expiration':
				$this->group_data['expiration_end'] = trim($this->cdata);
				break;
				
			case 'start':
				$this->group_data['expiration_start'] = trim($this->cdata);
				break;
				
			case 'end':
				$this->group_data['expiration_end'] = trim($this->cdata);
				break;

			case "folder":
				$this->__popParentId();
				break;

			case "folderTitle":
				$this->folder = trim($this->cdata);
				$this->__saveFolder();
				break;

			case "group":
				// NOW SAVE THE NEW OBJECT (if it hasn't been imported)
				$this->__save();
				break;
			
			case 'ContainerSetting':
				if($this->current_container_setting)
				{
					ilContainer::_writeContainerSetting(
						$this->group_obj->getId(), 
						$this->current_container_setting, 
						$this->cdata);
				}
				break;
		}
		$this->cdata = '';
	}


	/**
	 * handler for character data
	 */
	function handlerCharacterData($a_xml_parser, $a_data)
	{
		// i don't know why this is necessary, but
		// the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
		// in character data, but we don't want that, because it's the
		// way we mask user html in our content, so we convert back...
		$a_data = str_replace("<","&lt;",$a_data);
		$a_data = str_replace(">","&gt;",$a_data);

		if(!empty($a_data))
		{
			$this->cdata .= $a_data;
		}
	}

	// PRIVATE
	function __save()
	{
		if($this->group_imported)
		{
			return true;
		}

		$this->__initGroupObject();
	
		$this->group_obj->setImportId($this->group_data["id"]);
		$this->group_obj->setTitle($this->group_data["title"]);
		$this->group_obj->setDescription($this->group_data["description"]);
		
		$ownerChanged = false;
		if (isset($this->group_data["owner"])) 
		{
			$owner = $this->group_data["owner"];
			if (!is_numeric($owner)) 
			{
				$owner = ilUtil::__extractId ($owner, IL_INST_ID);
			}
			if (is_numeric($owner) && $owner > 0) 
			{
				$this->group_obj->setOwner($owner);
				$ownerChanged = true;
			}
		}

		/**
		 * mode can be create or update
		 */
		if ($this->mode == ilGroupXMLParser::$CREATE)
		{
			$this->group_obj->create();
			$this->group_obj->createReference();
			$this->group_obj->putInTree($this->__getParentId());
			$this->group_obj->setPermissions($this->__getParentId());
			$this->group_obj->initGroupStatus($this->group_data["type"] == "open" ? GRP_TYPE_PUBLIC : GRP_TYPE_CLOSED);
		} 
		else
		{
			switch($this->group_data['type'])
			{
				case 'open':
					$grp_status = GRP_TYPE_PUBLIC;
					break;
					
				case 'closed':
					$grp_status = GRP_TYPE_CLOSED;
					break;
					
			}
			
			$this->group_obj->updateOwner();
			if($this->group_obj->getGroupStatus() != $grp_status)
			{
				$this->group_obj->setGroupType($grp_status);
				$this->group_obj->updateGroupType();
			}
		}

		// SET GROUP SPECIFIC DATA
		switch($this->group_data['registration_type'])
		{
			case 'direct':
			case 'enabled':
				$flag = GRP_REGISTRATION_DIRECT;
				break;

			case 'disabled':
				$flag = GRP_REGISTRATION_DEACTIVATED;
				break;

			case 'confirmation':
				$flag = GRP_REGISTRATION_REQUEST;
				break;

			case 'password':
				$flag = GRP_REGISTRATION_PASSWORD;
				break;

			default:
				$flag = GRP_REGISTRATION_DIRECT;
		}
		$this->group_obj->setRegistrationType($flag);
		
		$end = new ilDateTime(time(),IL_CAL_UNIX);
		if($this->group_data['expiration_end'])
		{
			$end = new ilDateTime($this->group_data['expiration_end'],IL_CAL_UNIX);
		}

		$start = clone $end;
		if($this->group_data['expiration_start'])
		{
			$start = new ilDateTime($this->group_data['expiration_start'],IL_CAL_UNIX);
		}

		$this->group_obj->setRegistrationStart($start);
		$this->group_obj->setRegistrationEnd($end);
		$this->group_obj->setPassword($this->group_data['password']);
		$this->group_obj->enableUnlimitedRegistration(!isset($this->group_data['expiration_end']));
		$this->group_obj->enableMembershipLimitation($this->group_data['max_members_enabled']);
		$this->group_obj->setMaxMembers($this->group_data['max_members'] ? $this->group_data['max_members'] : 0);
		$this->group_obj->enableWaitingList($this->group_data['waiting_list_enabled']);
		
		
		if ($this->mode == ilGroupXMLParser::$CREATE)
		{
			$this->group_obj->initGroupStatus($this->group_data["type"] == "open" ? 0 : 1);
		}

		$this->group_obj->update();

		// ASSIGN ADMINS/MEMBERS
		$this->__assignMembers();

		$this->__pushParentId($this->group_obj->getRefId());


		$this->group_imported = true;

		return true;
	}

	function __saveFolder()
	{
		$this->__initFolderObject();

		$this->folder_obj->setTitle($this->folder);
		$this->folder_obj->create();
		$this->folder_obj->createReference();
		$this->folder_obj->putInTree($this->__getParentId());
		
		$this->__pushParentId($this->folder_obj->getRefId());

		$this->__destroyFolderObject();

		return true;
	}

	function __saveFile()
	{
		$this->__initFileObject();

		$this->file_obj->setType("file");
		$this->file_obj->setTitle($this->file["fileName"]);
		$this->file_obj->setFileName($this->file["fileName"]);
		$this->file_obj->create();
		$this->file_obj->createReference();
		$this->file_obj->putInTree($this->__getParentId());
		$this->file_obj->setPermissions($this->__getParentId());

		// COPY FILE
		$this->file_obj->createDirectory();

		$this->__initImportFileObject();

		if($this->import_file_obj->findObjectFile($this->file["id"]))
		{
			$this->file_obj->copy($this->import_file_obj->getObjectFile(),$this->file["fileName"]);
		}

		unset($this->file_obj);
		unset($this->import_file_obj);

		return true;
	}

	function __assignMembers()
	{
		global $ilias,$ilUser;

		$this->participants = new ilGroupParticipants($this->group_obj->getId());
		$this->participants->add($ilUser->getId(),IL_GRP_ADMIN);
		$this->participants->updateNotification($ilUser->getId(),true);
		
		// attach ADMINs
		if (count($this->group_data["admin"]["attach"]))
		{
			foreach($this->group_data["admin"]["attach"] as $user)
			{
				if($id_data = $this->__parseId($user))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						$this->participants->add($id_data['usr_id'],IL_GRP_ADMIN);
						if(in_array($user,(array) $this->group_data['notifications']))
						{
							$this->participants->updateNotification($id_data['usr_id'],true);
						}							
					}
				}
			}
		}
		// detach ADMINs
		if (count($this->group_data["admin"]["detach"]))
		{
			foreach($this->group_data["admin"]["detach"] as $user)
			{
				if($id_data = $this->__parseId($user))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						if($this->participants->isAssigned($id_data['usr_id']))
						{
							$this->participants->delete($id_data['usr_id']);
						}
					}
				}
			}
		}
		// MEMBER
		if (count($this->group_data["member"]["attach"]))
		{
			foreach($this->group_data["member"]["attach"] as $user)
			{
				if($id_data = $this->__parseId($user))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						$this->participants->add($id_data['usr_id'],IL_GRP_MEMBER);
					}
				}
			}
		}

		if (count($this->group_data["member"]["detach"]))
		{
			foreach($this->group_data["member"]["detach"] as $user)
			{
				if($id_data = $this->__parseId($user))
				{
					if($id_data['local'] or $id_data['imported'])
					{
						if($this->participants->isAssigned($id_data['usr_id']))
						{
							$this->participants->delete($id_data['usr_id']);
						}
					}
				}
			}
		}
		return true;
	}

	function __initGroupObject()
	{
		include_once "./Modules/Group/classes/class.ilObjGroup.php";

		if ($this->mode == ilGroupXMLParser::$CREATE)
		{
			$this->group_obj =& new ilObjGroup();
		} elseif ($this->mode == ilGroupXMLParser::$UPDATE) {
			$this->group_obj = $this->grp;
		}

		return true;
	}

	function __initFolderObject()
	{
		include_once "./Modules/Folder/classes/class.ilObjFolder.php";

		$this->folder_obj =& new ilObjFolder();

		return true;
	}

	function __initImportFileObject()
	{
		include_once "./Modules/Group/classes/class.ilFileDataImportGroup.php";

		$this->import_file_obj =& new ilFileDataImportGroup();

		return true;
	}

	function __initFileObject()
	{
		include_once "./Modules/File/classes/class.ilObjFile.php";

		$this->file_obj =& new ilObjFile();

		return true;
	}

	function __destroyFolderObject()
	{
		unset($this->folder_obj);
	}

	function __parseId($a_id)
	{
		global $ilias;

		$fields = explode('_',$a_id);

		if(!is_array($fields) or
		   $fields[0] != 'il' or
		   !is_numeric($fields[1]) or
		   $fields[2] != 'usr' or
		   !is_numeric($fields[3]))
		{
			return false;
		}
		if($id = ilObjUser::_getImportedUserId($a_id))
		{
			return array('imported' => true,
						 'local' => false,
						 'usr_id' => $id);
		}
		if(($fields[1] == $ilias->getSetting('inst_id',0)) and ($user = ilObjUser::_lookupName($fields[3])))
		{
			if(strlen($user['login']))
			{
				return array('imported' => false,
							 'local' => true,
							 'usr_id' => $fields[3]);
			}
		}
		$GLOBALS['ilLog']->write(__METHOD__.' Parsing id failed: '.$a_id);
		return false;
	}


	public function setMode($mode) {
		$this->mode = $mode;
	}

	public function setGroup(& $grp) {
		$this->grp = $grp;
	}
}
?>
