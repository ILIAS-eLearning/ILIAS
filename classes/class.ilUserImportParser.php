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

define ("IL_EXTRACT_ROLES", 1);
define ("IL_USER_IMPORT", 2);

require_once("classes/class.ilSaxParser.php");

/**
* User Import Parser
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @extends ilSaxParser
* @package core
*/
class ilUserImportParser extends ilSaxParser
{
	var $folder_id;
	var $roles;

	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	* @param	int			$a_mode			IL_EXTRACT_ROLES | IL_USER_IMPORT
	*
	* @access	public
	*/
	function ilUserImportParser($a_xml_file, $a_mode = IL_USER_IMPORT)
	{
		global $lng, $tree;

		$this->roles = array();
		$this->mode = $a_mode;

		parent::ilSaxParser($a_xml_file);
	}

	/**
	* assign users to this folder (normally the usr_folder)
	* But if called from local admin => the ref_id of the category
	* @access	public
	*/
	function setFolderId($a_folder_id)
	{
		$this->folder_id = $a_folder_id;
	}
	function getFolderId()
	{
		return $this->folder_id;
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
	}

	/**
	* set import to local role assignemt
	*
	* @param	array		role assignment (key: import id; value: local role id)
	*/
	function setRoleAssignment($a_assign)
	{
		$this->role_assign = $a_assign;
	}

	/**
	* generate a tag with given name and attributes
	*
	* @param	string		"start" | "end" for starting or ending tag
	* @param	string		element/tag name
	* @param	array		array of attributes
	*/
	function buildTag ($type, $name, $attr="")
	{
		$tag = "<";

		if ($type == "end")
			$tag.= "/";

		$tag.= $name;

		if (is_array($attr))
		{
			while (list($k,$v) = each($attr))
				$tag.= " ".$k."=\"$v\"";
		}

		$tag.= ">";

		return $tag;
	}

	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{

		// extract roles mode
		if ($this->mode == IL_EXTRACT_ROLES)
		{
			switch($a_name)
			{
				case "Role":
					$this->current_role_id = $a_attribs["Id"];
					$this->current_role_type = $a_attribs["Type"];
					break;
			}
		}

		// extract roles mode
		if ($this->mode == IL_USER_IMPORT)
		{
			switch($a_name)
			{
				case "Role":
					$this->current_role_id = $a_attribs["Id"];
					$this->current_role_type = $a_attribs["Type"];
					break;

				case "User":
					$this->userObj = new ilObjUser();
					$this->userObj->setLanguage($a_attribs["Language"]);
					$this->userObj->setImportId($a_attribs["Id"]);
					break;

				case "Password":
					$this->currPasswordType = $a_attribs["Type"];
					break;
			}
		}

		$this->cdata = "";
	}


	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser, $a_name)
	{
		global $ilias, $rbacadmin, $ilUser;

		// extract roles mode
		if ($this->mode == IL_EXTRACT_ROLES)
		{
			switch($a_name)
			{
				case "Role":
					$this->roles[$this->current_role_id]["name"] = $this->cdata;
					$this->roles[$this->current_role_id]["type"] =
						$this->current_role_type;
					break;
			}
		}

		// user import mode
		if ($this->mode == IL_USER_IMPORT)
		{
			switch($a_name)
			{
				case "Role":
					$this->roles[$this->current_role_id]["name"] = $this->cdata;
					break;

				case "User":

					// check if login name doesn't exist
					if (ilObjUser::getUserIdByLogin($this->userObj->getLogin()) == 0)
					{
						// checks passed. save user
						$this->userObj->setTitle($this->userObj->getFullname());
						$this->userObj->setDescription($this->userObj->getEmail());

						// default time limit settings

						$this->userObj->setTimeLimitOwner($this->getFolderId());
						$this->userObj->setTimeLimitUnlimited($ilias->account->getTimeLimitUnlimited());
						$this->userObj->setTimeLimitFrom($ilias->account->getTimeLimitFrom());
						$this->userObj->setTimeLimitUntil($ilias->account->getTimeLimitUntil());
						$this->userObj->setActive(true , $ilUser->getId());
						
						$this->userObj->create();

						//insert user data in table user_data
						$this->userObj->saveAsNew(false);

						// set user preferences
						$this->userObj->setPref("skin",
							$ilias->ini->readVariable("layout","skin"));
						$this->userObj->setPref("style",
							$ilias->ini->readVariable("layout","style"));
						$this->userObj->writePrefs();

						//set role entries
						foreach($this->roles as $role_id => $role)
						{
							$rbacadmin->assignUser($this->role_assign[$role_id],
								$this->userObj->getId(), true);
						}
					}

					// init role array for next user
					$this->roles = array();
					break;

				case "Login":
					$this->userObj->setLogin($this->cdata);
					break;

				case "Password":
					switch ($this->currPasswordType)
					{
						case "ILIAS2":
							$this->userObj->setPasswd($this->cdata, IL_PASSWD_CRYPT);
							break;

						case "ILIAS3":
							$this->userObj->setPasswd($this->cdata, IL_PASSWD_MD5);
							break;
					}
					break;

				case "Firstname":
					$this->userObj->setFirstname($this->cdata);
					break;

				case "Lastname":
					$this->userObj->setLastname($this->cdata);
					$this->userObj->setFullname();
					break;

				case "Title":
					$this->userObj->setUTitle($this->cdata);
					break;

				case "Gender":
					$this->userObj->setGender($this->cdata);
					break;

				case "Email":
					$this->userObj->setEmail($this->cdata);
					break;

				case "Institution":
					$this->userObj->setInstitution($this->cdata);
					break;

				case "Street":
					$this->userObj->setStreet($this->cdata);
					break;

				case "City":
					$this->userObj->setCity($this->cdata);
					break;

				case "PostalCode":
					$this->userObj->setZipCode($this->cdata);
					break;

				case "Country":
					$this->userObj->setCountry($this->cdata);
					break;

				case "PhoneOffice":
					$this->userObj->setPhoneOffice($this->cdata);
					break;

				case "PhoneHome":
					$this->userObj->setPhoneHome($this->cdata);
					break;

				case "PhoneMobile":
					$this->userObj->setPhoneMobile($this->cdata);
					break;

				case "Fax":
					$this->userObj->setFax($this->cdata);
					break;

				case "Hobby":
					$this->userObj->setHobby($this->cdata);
					break;

                case "Referral_Comment":
                    $this->userObj->setComment($this->cdata);
                    break;

				case "Department":
					$this->userObj->setDepartment($this->cdata);
					break;
			}
		}
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

		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		$a_data = preg_replace("/\n/","",$a_data);
		$a_data = preg_replace("/\t+/","",$a_data);
		if(!empty($a_data))
		{
			$this->cdata .= $a_data;
		}
	}

	/**
	* get collected roles
	*/
	function getCollectedRoles()
	{
		return $this->roles;
	}
}
?>
