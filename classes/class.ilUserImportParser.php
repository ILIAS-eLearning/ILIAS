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
	 * The Action element determines what to do for the current User element.
     * This variable supports the following values: "insert","update","delete".
	 */
	var $action;
	/**
	 * The variable holds the protocol of the import.
     * This variable is an associative array.
	 * - Keys are login names of users or "missing login", if the login name is 
	 *   missing.
	 * - Values are an array of error messages associated with the login.
	 *   If the value array is empty, then the user was imported successfully.
	 */
	var $protocol;
	/**
	 * This variable is used to collect each login that we encounter in the 
	 * import data.
	 * This variable is needed to detect duplicate logins in the import data.
	 * The variable is an associative array. (I would prefer using a set, but PHP
	 * does not appear to support sets.)
	 * Keys are logins.
	 * Values are logins.
	 */
	var $logins;

	/**
	 * This variable is used to report whether importing was successful.
	 */
	var $success;

	/**
	 * The password type of the current user.
	 */
	var $currPasswordType;
	/**
	 * The password of the current user.
	 */
	var $currPassword;

	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	* @param	int			$a_mode			IL_EXTRACT_ROLES | IL_USER_IMPORT | IL_VERIFY
	*
	* @access	public
	*/
	function ilUserImportParser($a_xml_file, $a_mode = IL_USER_IMPORT)
	{
		global $lng, $tree;

		$this->roles = array();
		$this->mode = $a_mode;
		$this->success = true;
		$this->protocol = array();
		$this->logins = array();
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
		switch ($this->mode) 
		{
			case IL_EXTRACT_ROLES :
				$this->extractRolesBeginTag($a_xml_parser, $a_name, $a_attribs);
				break;
			case IL_USER_IMPORT :
				$this->importBeginTag($a_xml_parser, $a_name, $a_attribs);
				break;
			case IL_VERIFY :
				$this->verifyBeginTag($a_xml_parser, $a_name, $a_attribs);
				break;
		}

		$this->cdata = "";
	}

	/**
	* handler for begin of element in extract roles mode
	*/
	function extractRolesBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		switch($a_name)
		{
			case "Role":
				$this->current_role_id = $a_attribs["Id"];
				$this->current_role_type = $a_attribs["Type"];
				break;
		}
	}
	/**
	* handler for begin of element in user import mode
	*/
	function importBeginTag($a_xml_parser, $a_name, $a_attribs)
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
				$this->action = "insert";
				$this->currPassword = null;
				$this->currPasswordType = null;
				break;

			case "Password":
				$this->currPasswordType = $a_attribs["Type"];
				break;
		}
	}
	/**
	* handler for begin of element
	*/
	function verifyBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		switch($a_name)
		{
			case "Role":
				if (is_null($a_attribs['Id'])
				|| $a_attribs['Id'] == "")
				{
					$this->log($this->userObj->getLogin(), "'Id' attribute in 'Role' element must be specified.");
					$this->success = false;
				}
				if ($a_attribs['Type'] != 'Global'
				&& $a_attribs['Type'] != 'Local')
				{
					$this->log($this->userObj->getLogin(), "'Type' attribute in 'Role' element must be specified with value 'Global' or 'Local'.");
					$this->success = false;
				}
				$this->current_role_id = $a_attribs["Id"];
				$this->current_role_type = $a_attribs["Type"];
				break;

			case "User":
				$this->userObj = new ilObjUser();
				$this->userObj->setLanguage($a_attribs["Language"]);
				$this->userObj->setImportId($a_attribs["Id"]);
				$this->action = "insert";
				$this->currPassword = null;
				$this->currPasswordType = null;
				break;

			case "Password":
				$this->currPasswordType = $a_attribs["Type"];
				break;
		}
	}

	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser, $a_name)
	{
		switch ($this->mode) 
		{
			case IL_EXTRACT_ROLES :
				$this->extractRolesEndTag($a_xml_parser, $a_name);
				break;
			case IL_USER_IMPORT :
				$this->importEndTag($a_xml_parser, $a_name);
				break;
			case IL_VERIFY :
				$this->verifyEndTag($a_xml_parser, $a_name);
				break;
		}
	}

	/**
	* handler for end of element when in extract roles mode.
	*/
	function extractRolesEndTag($a_xml_parser, $a_name)
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

	/**
	* handler for end of element when in import user mode.
	*/
	function importEndTag($a_xml_parser, $a_name)
	{
		global $ilias, $rbacadmin, $rbacreview, $ilUser;

		switch($a_name)
		{
			case "Role":
				$this->roles[$this->current_role_id]["name"] = $this->cdata;
				$this->roles[$this->current_role_id]["type"] =
					$this->current_role_type;
				break;

			case "User":
				$user_id = ilObjUser::getUserIdByLogin($this->userObj->getLogin());
				switch ($this->action)
				{
					case "insert" :
						if ($user_id) 
						{
							$this->log($this->userObj->getLogin(),"Can't perform Action 'insert', user is already in database.");
							$this->success = false;
						}
						else
						{
							switch ($this->currPasswordType)
							{
								case "ILIAS2":
									$this->userObj->setPasswd($this->currPassword, IL_PASSWD_CRYPT);
									break;

								case "ILIAS3":
									$this->userObj->setPasswd($this->currPassword, IL_PASSWD_MD5);
									break;
							}

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
						break;

					case "update" :
						if (! $user_id) 
						{
							$this->log($this->userObj->getLogin(),"Can't perform Action 'update', no such user in database.");
							$this->success = false;
						}
						else
						{
							$updateUser = new ilObjUser($user_id);
							$updateUser->read();
							$updateUser->readPrefs();
							if ($this->currPassword != null)
							{
								switch ($this->currPasswordType)
								{
									case "ILIAS2":
										$updateUser->setPasswd($this->currPassword, IL_PASSWD_CRYPT);
										break;

									case "ILIAS3":
										$updateUser->setPasswd($this->currPassword, IL_PASSWD_MD5);
										break;
								}
							}
							if (! is_null($this->userObj->getFirstname())) $updateUser->setFirstname($this->userObj->getFirstname());
							if (! is_null($this->userObj->getLastname())) $updateUser->setLastname($this->userObj->getLastname());
							if (! is_null($this->userObj->getUTitle())) $updateUser->setUTitle($this->userObj->getUTitle());
							if (! is_null($this->userObj->getGender())) $updateUser->setGender($this->userObj->getGender());
							if (! is_null($this->userObj->getEmail())) $updateUser->setEmail($this->userObj->getEmail());
							if (! is_null($this->userObj->getInstitution())) $updateUser->setInstitution($this->userObj->getInstitution());
							if (! is_null($this->userObj->getStreet())) $updateUser->setStreet($this->userObj->getStreet());
							if (! is_null($this->userObj->getCity())) $updateUser->setCity($this->userObj->getCity());
							if (! is_null($this->userObj->getZipCode())) $updateUser->setZipCode($this->userObj->getZipCode());
							if (! is_null($this->userObj->getCountry())) $updateUser->setCountry($this->userObj->getCountry());
							if (! is_null($this->userObj->getPhoneOffice())) $updateUser->setPhoneOffice($this->userObj->getPhoneOffice());
							if (! is_null($this->userObj->getPhoneHome())) $updateUser->setPhoneHome($this->userObj->getPhoneHome());
							if (! is_null($this->userObj->getPhoneMobile())) $updateUser->setPhoneMobile($this->userObj->getPhoneMobile());
							if (! is_null($this->userObj->getFax())) $updateUser->setFax($this->userObj->getFax());
							if (! is_null($this->userObj->getHobby())) $updateUser->setHobby($this->userObj->getHobby());
							if (! is_null($this->userObj->getComment())) $updateUser->setComment($this->userObj->getComment());
							if (! is_null($this->userObj->getDepartment())) $updateUser->setDepartment($this->userObj->getDepartment());

							$updateUser->update();

							//update role entries
							//-------------------
							//If a global role is in the import data, we deassign
							//the user from all other global roles.
							$is_update_global_role = false;
							foreach ($this->roles as $role_id => $role)
							{
								if ($role["type"] == "Global")
								{
									$is_update_global_role = true;
									break;
								}
							}
							if ($is_update_global_role)
							{
								foreach ($rbacreview->getGlobalRoles() as $role_id)
								{
									$rbacadmin->deassignUser($role_id, $updateUser->getId());
								}
							}
							// Assign the user to the roles
							foreach ($this->roles as $role_id => $role)
							{
								$rbacadmin->assignUser($this->role_assign[$role_id],
									$updateUser->getId(), true);
							}
						}
						break;
					case "delete" :
						if (! $user_id) 
						{
							$this->log($this->userObj->getLogin(),"Can't perform Action 'delete', no such user in database.");
							$this->success = false;
						}
						else
						{
							$deleteUser = new ilObjUser($user_id);
							$deleteUser->delete();
						}
						break;
				}

				// init role array for next user
				$this->roles = array();
				break;

			case "Action":
				$this->action = $this->cdata;
				break;

			case "Login":
				$this->userObj->setLogin($this->cdata);
				break;

			case "Password":
				$this->currPassword = $this->cdata;
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

			case "Comment":
				$this->userObj->setComment($this->cdata);
				break;

			case "Department":
				$this->userObj->setDepartment($this->cdata);
				break;
		}
	}

	/**
	* handler for end of element when in verify mode.
	*/
	function verifyEndTag($a_xml_parser, $a_name)
	{
		switch($a_name)
		{
			case "Role":
				$this->roles[$this->current_role_id]["name"] = $this->cdata;
				$this->roles[$this->current_role_id]["type"] = $this->current_role_type;
				break;

			case "User":
				$user_exists = ilObjUser::getUserIdByLogin($this->userObj->getLogin()) != 0;

				switch ($this->action)
				{
					case "insert" :
						if ($user_exists) 
						{
							$this->log($this->userObj->getLogin(),"Can't perform Action 'insert'. User is already in database.");
							$this->success = false;
						}
						if (is_null($this->userObj->getGender()))
						{	
							$this->log($this->userObj->getLogin(),"Gender element must be specified for Action 'insert'.");
							$this->success = false;
						}
						if (count($this->roles) == 0)
						{	
							$this->log($this->userObj->getLogin(),"Role element must be specified for Action 'insert'.");
							$this->success = false;
						} 
						else 
						{
							$has_global_role = false;
							foreach ($this->roles as $role)
							{
								if ($role['type'] == 'Global')
								{
									$has_global_role = true;
									break;
								}
							}
							if (! $has_global_role)
							{
							$this->log($this->userObj->getLogin(),"At least one global Role must be specified for Action 'insert'.");
							$this->success = false;
							}
						}
						break;
					case "update" :
						if (! $user_exists) 
						{
							$this->log($this->userObj->getLogin(),"Can't perform Action 'update'. No such user in database.");
							$this->success = false;
						}
						break;
					case "delete" :
						if (! $user_exists) 
						{
							$this->log($this->userObj->getLogin(),"Can't perform Action 'delete'. No such user in database.");
							$this->success = false;
						}
						break;
				}

				// init role array for next user
				$this->roles = array();
				break;

			case "Action":
				if ($this->cdata != "insert"
				&& $this->cdata != "update"
				&& $this->cdata != "delete")
				{
					$this->log($this->userObj->getLogin(), "Unsupported action '".$this_cdata."'. Content of Action element must be 'insert', 'delete' or 'update'.");
					$this->success = false;
				}
				$this->action = $this->cdata;
				break;

			case "Login":
				if (array_key_exists($this->cdata, $this->logins))
				{
					$this->log($this->cdata, "Login must be unique in import data.");
					$this->success = false;
				} 
				else
				{
					$this->logins[$this->cdata] = $this->cdata;
				}
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

					default :
						$this->log($this->userObj->getLogin(), "Illegal password type '".$this->currPasswordType."'. Password type must be 'ILIAS2' or 'ILIAS3'.");
						$this->success = false;
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
				if ($this->cdata != "m"
				&& $this->cdata != "f")
				{
					$this->log($this->userObj->getLogin(), "Illegal gender '".$this->cdata."'. Gender must be 'm' or 'f'.");
					$this->success = false;
				}
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

			case "Comment":
				$this->userObj->setComment($this->cdata);
				break;

			case "Department":
				$this->userObj->setDepartment($this->cdata);
				break;
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

	/**
     * Writes a log message to the protocol a message.
	 *
	* @param	string		login
	* @param	string		message
	 */
	function log($aLogin, $aMessage) 
	{
		if (! array_key_exists($aLogin, $this->protocol))
		{
			$this->protocol[$aLogin] = array();
		}
		if ($aMessage)
		{
			$this->protocol[$aLogin][] = $aMessage;
		}
	}


	/**
     * Returns the protocol.
	 *
	 * The protocol is an associative array.
	 * Keys are login names.
	 * Values are non-associative arrays. Each array element contains an error
	 * message.
	 */
	function getProtocol() 
	{
		return $this->protocol;
	}
	/**
     * Returns the protocol as a HTML table.
	 */
	function getProtocolAsHTML() 
	{
		$html = "<table>";
		$html = $html."<tr><td><b>Login</b></td><td><b>Reason</b></td></tr>";
		foreach ($this->protocol as $login => $messages)
		{
			$html = $html."<tr>";
			$html = $html.'<td valign="top">'.$login.'</td><td valign="top"><ul>';
			foreach ($messages as $message)
			{
				$html = $html."<li>".$message."</li>";
			}
			$html = $html."</ul></td></tr>";
		}
		$html = $html."</table>";
		return $html;
	}

	/**
     * Returns true, if the import was successful.
	 */
	function isSuccess() 
	{
		return $this->success;
	}
}
?>
