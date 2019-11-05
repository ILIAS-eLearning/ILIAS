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

define ("IL_EXTRACT_ROLES", 1);
define ("IL_USER_IMPORT", 2);
define ("IL_VERIFY", 3);

define ("IL_FAIL_ON_CONFLICT", 1);
define ("IL_UPDATE_ON_CONFLICT", 2);
define ("IL_IGNORE_ON_CONFLICT", 3);

define ("IL_IMPORT_SUCCESS", 1);
define ("IL_IMPORT_WARNING", 2);
define ("IL_IMPORT_FAILURE", 3);

define ("IL_USER_MAPPING_LOGIN", 1);
define ("IL_USER_MAPPING_ID", 2);

require_once("./Services/Xml/classes/class.ilSaxParser.php");
require_once ('Services/User/classes/class.ilUserXMLWriter.php');

/**
* User Import Parser
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @extends ilSaxParser
*/
class ilUserImportParser extends ilSaxParser
{
	var $approve_date_set = false;
	var $time_limit_set = false;
	var $time_limit_owner_set = false;

	/**
	* boolean to determine if look and skin should be updated
	*/
	var $updateLookAndSkin = false;	
	var $folder_id;
	var $roles;
	/**
	 * The Action attribute determines what to do for the current User element.
     * This variable supports the following values: "Insert","Update","Delete".
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
	 * Conflict handling rule.
	 *
	 * Values:  IL_FAIL_ON_CONFLICT
	 *          IL_UPDATE_ON_CONFLICT
	 *          IL_IGNORE_ON_CONFLICT
	 */
	var $conflict_rule;


	/**
	 * send account notification
	 *
	 * @var boolean
	 */
	var $send_mail;

	/**
	 * This variable is used to report the error level of the validation process
	 * or the importing process.
     *
	 * Values:  IL_IMPORT_SUCCESS
	 *          IL_IMPORT_WARNING
	 *          IL_IMPORT_FAILURE
     *
     * Meaning of the values when in validation mode:
	 *          IL_IMPORT_WARNING
	 *					Some of the entity actions can not be processed
	 *                  as specified in the XML file. One or more of the
	 *                  following conflicts have occurred:
	 *                  -	An "Insert" action has been specified for a user
	 *						who is already in the database.
	 *                  -	An "Update" action has been specified for a user
	 *						who is not in the database.
	 *                  -	A "Delete" action has been specified for a user
     *					   who is not in the database.
	 *          IL_IMPORT_FAILURE
	 *					Some of the XML elements are invalid.
     *
     * Meaning of the values when in import mode:
	 *          IL_IMPORT_WARNING
	 *					Some of the entity actions have not beeen processed
	 *					as specified in the XML file.
     *
     *                  In IL_UPDATE_ON_CONFLICT mode, the following
	 *					 may have occured:
     *                  -	An "Insert" action has been replaced by a
	 *						"Update" action for a user who is already in the
	 *						database.
     *                   -	An "Update" action has been replaced by a
	 *						"Insert" action for a user who is not in the
	 *						database.
     *                  -	A "Delete" action has been replaced by a "Ignore"
	 *						action for a user who is not in the database.
	 *
     *                 In IL_IGNORE_ON_CONFLICT mode, the following
	 *					 may have occured:
     *                 -	An "Insert" action has been replaced by a
	 *						"Ignore" action for a user who is already in the
	 *						database.
     *                 -	An "Update" action has been replaced by a
	 *						"Ignore" action for a user who is not in the
	 *						database.
     *                  -	A "Delete" action has been replaced by a "Ignore"
	 *						action for a user who is not in the database.
     *
	 *          IL_IMPORT_FAILURE
	 *					The import could not be completed.
     *
	 *                       In IL_FAIL_ON_CONFLICT mode, the following
	 *						 may have occured:
	 *                       -	An "Insert" action has failed for a user who is
	 *							already in the database.
	 *                       -	An "Update" action has failed for a user who is
	 *							not in the database.
	 *                       -	A "Delete" action has failed for a user who is
	 *							not in the database.
	 */
	var $error_level;

	/**
	 * The password type of the current user.
	 */
	var $currPasswordType;
	/**
	 * The password of the current user.
	 */
	var $currPassword;

	/**
	 * The active state of the current user.
	*/
	var $currActive;
	/**
	* The count of user elements in the XML file
    	*/
	var $userCount;

	/**
	 * record user mappings for successful actions
	 *
	 * @var assoc array (key = user id, value= login)
	 */
	var $user_mapping;

	/**
	 *
	 * mapping mode is used for import process
	 *
	 * @var int
	 */
	var $mapping_mode;

	/**
	 * Cached local roles.
	 * This is used to speed up access to local roles.
	 * This is an associative array.
	 * The key is either a role_id  or  a role_id with the string "_courseMembersObject" appended.
	 * The value is a role object or  the course members object for which the role is defined
	 */
	var $localRoleCache;

	/**
	 * Cached personal picture of the actual user
	 * This is used because the ilObjUser object has no field for the personal picture
	 */
	var $personalPicture;

	/**
	 * Cached parent roles.
	 * This is used to speed up assignment to local roles with parents.
	 * This is an associative array.
	 * The key is a role_id .
	 * The value is an array of role_ids containing all parent roles.
	 */
	var $parentRolesCache;

	/**
	 * ILIAS skin
	 */
	var $skin;

	/**
	 * ILIAS style
	 */
	var $style;

	/**
	 * User assigned styles
	 */
	var $userStyles;

	/**
	 * Indicates if the skins are hidden
	 */
	var $hideSkin;

	/**
	 * Indicates if the skins are enabled
	 */
	var $disableSkin;

	/**
	 * current user id, used for updating the login
	 *
	 * @var unknown_type
	 */
	var $user_id;

	/**
	 * current User obj
	 * @var ilObjUser
	*/
	private $userObj;

	/**
	 * current messenger type
	 *
	 * @var String
	 */
	private $current_messenger_type;

	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	* @param	int			$a_mode			IL_EXTRACT_ROLES | IL_USER_IMPORT | IL_VERIFY
	* @param	int			$a_conflict_rue	IL_FAIL_ON_CONFLICT | IL_UPDATE_ON_CONFLICT | IL_IGNORE_ON_CONFLICT
	*
	* @access	public
	*/
	function __construct($a_xml_file = '', $a_mode = IL_USER_IMPORT, $a_conflict_rule = IL_FAIL_ON_CONFLICT)
	{
		global $DIC;

		$global_settings = $DIC->settings();

		$this->roles = array();
		$this->mode = $a_mode;
		$this->conflict_rule = $a_conflict_rule;
		$this->error_level = IL_IMPORT_SUCCESS;
		$this->protocol = array();
		$this->logins = array();
		$this->userCount = 0;
		$this->localRoleCache = array();
		$this->parentRolesCache = array();
		$this->send_mail = false;
		$this->mapping_mode = IL_USER_MAPPING_LOGIN;
		
		// get all active style  instead of only assigned ones -> cannot transfer all to another otherwise
		$this->userStyles = array();
		include_once './Services/Style/System/classes/class.ilStyleDefinition.php';
		$skins = ilStyleDefinition::getAllSkins();

		if (is_array($skins))
		{

			foreach($skins as $skin)
			{
				foreach($skin->getStyles() as $style)
				{
					include_once("./Services/Style/System/classes/class.ilSystemStyleSettings.php");
					if (!ilSystemStyleSettings::_lookupActivatedStyle($skin->getId(),$style->getId()))
					{
						continue;
					}
					$this->userStyles [] = $skin->getId().":".$style->getId();
				}
			}
		}

		$settings = $global_settings->getAll();
		if ($settings["usr_settings_hide_skin_style"] == 1)
		{
			$this->hideSkin = TRUE;
		}
		else
		{
			$this->hideSkin = FALSE;
		}
		if ($settings["usr_settings_disable_skin_style"] == 1)
		{
			$this->disableSkin = TRUE;
		}
		else
		{
			$this->disableSkin = FALSE;
		}

		include_once("Services/Mail/classes/class.ilAccountMail.php");
		$this->acc_mail = new ilAccountMail();
		$this->acc_mail->setAttachConfiguredFiles(true);
		$this->acc_mail->useLangVariablesAsFallback(true);

		parent::__construct($a_xml_file);
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
			foreach ($attr as $k => $v) {
				$tag .= " " . $k . "=\"$v\"";
			}
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
				// detect numeric, ilias id (then extract role id) or alphanumeric
				$this->current_role_id = $a_attribs["Id"]; 
				if ($internal_id = ilUtil::__extractId($this->current_role_id, IL_INST_ID)) 
				{
					 $this->current_role_id = $internal_id;
				}
				$this->current_role_type = $a_attribs["Type"];							

				break;
		}
	}
	/**
	* handler for begin of element in user import mode
	*/
	function importBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		global $DIC;

		$ilias = $DIC['ilias'];
		$lng = $DIC['lng'];

		switch($a_name)
		{
			case "Role":
				$this->current_role_id = $a_attribs["Id"];
				if ($internal_id = ilUtil::__extractId($this->current_role_id, IL_INST_ID)) 
				{
					 $this->current_role_id = $internal_id;
				}			
				$this->current_role_type = $a_attribs["Type"];
				$this->current_role_action = (is_null($a_attribs["Action"])) ? "Assign" : $a_attribs["Action"];
				break;

			case "PersonalPicture":
				$this->personalPicture = array(
					"encoding" => $a_attribs["encoding"],
					"imagetype" => $a_attribs["imagetype"],
					"content" => ""
				);
				break;

			case "Look":
				$this->skin = $a_attribs["Skin"];
				$this->style = $a_attribs["Style"];
				break;

			case "User":
				$this->acc_mail->reset();
				$this->prefs = array();
				$this->currentPrefKey = null;
				$this->auth_mode_set = false;
				$this->approve_date_set = false;
				$this->time_limit_set = false;
				$this->time_limit_owner_set = false;	
				$this->updateLookAndSkin = false;
				$this->skin = "";
				$this->style = "";
				$this->personalPicture = null;
				$this->userCount++;
				$this->userObj = new ilObjUser();

				// user defined fields
				$this->udf_data = array();

				// if we have an object id, store it
				$this->user_id = -1;
				if (!is_null($a_attribs["Id"]) && $this->getUserMappingMode() == IL_USER_MAPPING_ID)
				{
				    if (is_numeric($a_attribs["Id"]))
				    {
				        $this->user_id = $a_attribs["Id"];
				    }
				    elseif ($id = ilUtil::__extractId ($a_attribs["Id"], IL_INST_ID))
				    {
				        $this->user_id = $id;
				    }
				}

				$this->userObj->setPref("skin",
					$ilias->ini->readVariable("layout","skin"));
				$this->userObj->setPref("style",
					$ilias->ini->readVariable("layout","style"));
				
				$this->userObj->setLanguage($a_attribs["Language"]);
				$this->userObj->setImportId($a_attribs["Id"]);
				$this->action = (is_null($a_attribs["Action"])) ? "Insert" : $a_attribs["Action"];
				$this->currPassword     = null;
				$this->currPasswordType = null;
				$this->currActive = null;				
				$this->multi_values = array();
				break;

			case 'Password':
				$this->currPasswordType = $a_attribs['Type'];
				break;
			case "AuthMode":
				if (array_key_exists("type", $a_attribs))
				{
					switch ($a_attribs["type"])
					{
						case "saml":
						case "ldap":
							if(strcmp('saml', $a_attribs['type']) === 0)
							{
								require_once './Services/Saml/classes/class.ilSamlIdp.php';
								$list = ilSamlIdp::getActiveIdpList();
								if(count($list) == 1)
								{
									$this->auth_mode_set = true;
									$ldap_id = current($list);
									$this->userObj->setAuthMode('saml_' . $ldap_id);
								}
								break;
							}
							if(strcmp('ldap', $a_attribs['type']) === 0)
							{
								// no server id provided => use default server
								include_once './Services/LDAP/classes/class.ilLDAPServer.php';
								$list = ilLDAPServer::_getActiveServerList();
								if(count($list) == 1)
								{
									$this->auth_mode_set = true;
									$ldap_id = current($list);
									$this->userObj->setAuthMode('ldap_'.$ldap_id);
								}
							}
							break;

						case "default":
						case "local":
						case "radius":
						case "shibboleth":
						case "script":
						case "cas":
						case "soap":
						case "openid":
						// begin-patch auth_plugin
						default:
							$this->auth_mode_set = true;
							$this->userObj->setAuthMode($a_attribs["type"]);
							break;
						/*
							$this->logFailure($this->userObj->getLogin(),
											  sprintf($lng->txt("usrimport_xml_element_inapplicable"),"AuthMode",$a_attribs["type"]));
							break;
						 * 
						 */
					}
				}
				else
				{
					$this->logFailure($this->userObj->getLogin(),
									  sprintf($lng->txt("usrimport_xml_element_inapplicable"),"AuthMode",$a_attribs["type"]));
				}
				break;

			case 'UserDefinedField':
				$this->tmp_udf_id = $a_attribs['Id'];
				$this->tmp_udf_name = $a_attribs['Name'];
				break;

			case 'AccountInfo':
				$this->current_messenger_type = strtolower($a_attribs["Type"]);
				break;
			case 'GMapInfo':
				$this->userObj->setLatitude($a_attribs["latitude"]);
				$this->userObj->setLongitude($a_attribs["longitude"]);
				$this->userObj->setLocationZoom($a_attribs["zoom"]);
				break;
			case 'Pref':
				$this->currentPrefKey = $a_attribs["key"];
				break;			
		}
	}
	/**
	* handler for begin of element
	*/
	function verifyBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		global $DIC;

		$lng = $DIC['lng'];

		switch($a_name)
		{
			case "Role":
				if (is_null($a_attribs['Id'])
				|| $a_attribs['Id'] == "")
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_missing"),"Role","Id"));
				}
				$this->current_role_id = $a_attribs["Id"];
				$this->current_role_type = $a_attribs["Type"];
				if ($this->current_role_type != 'Global'
				&& $this->current_role_type != 'Local')
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_missing"),"Role","Type"));
				}
				$this->current_role_action = (is_null($a_attribs["Action"])) ? "Assign" : $a_attribs["Action"];
				if ($this->current_role_action != "Assign"
				&& $this->current_role_action != "AssignWithParents"
				&& $this->current_role_action != "Detach")
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"Role","Action",$a_attribs["Action"]));
				}
				if ($this->action == "Insert"
				&& $this->current_role_action == "Detach")
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_inapplicable"),"Role","Action",$this->current_role_action,$this->action));
				}
				if ($this->action == "Delete")
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_inapplicable"),"Role","Delete"));
				}
				break;

			case "User":
				$this->userCount++;
				$this->userObj = new ilObjUser();
				$this->userObj->setLanguage($a_attribs["Language"]);
				$this->userObj->setImportId($a_attribs["Id"]);
				$this->currentPrefKey = null;
				// if we have an object id, store it
				$this->user_id = -1;

                if (!is_null($a_attribs["Id"]) && $this->getUserMappingMode() == IL_USER_MAPPING_ID)
				{
				    if (is_numeric($a_attribs["Id"]))
				    {
				        $this->user_id = $a_attribs["Id"];
				    }
				    elseif ($id = ilUtil::__extractId ($a_attribs["Id"], IL_INST_ID))
				    {
				        $this->user_id = $id;
				    }
				}

				$this->action = (is_null($a_attribs["Action"])) ? "Insert" : $a_attribs["Action"];
				if ($this->action != "Insert"
				&& $this->action != "Update"
				&& $this->action != "Delete")
				{
					$this->logFailure($this->userObj->getImportId(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"User","Action",$a_attribs["Action"]));
				}
				$this->currPassword     = null;
				$this->currPasswordType = null;
				break;

			case 'Password':
				$this->currPasswordType = $a_attribs['Type'];
				break;
			case "AuthMode":
				if (array_key_exists("type", $a_attribs))
				{
					switch($a_attribs["type"])
					{
						case "saml":
						case "ldap":
							if(strcmp('saml', $a_attribs['type']) === 0)
							{
								require_once './Services/Saml/classes/class.ilSamlIdp.php';
								$list = ilSamlIdp::getActiveIdpList();
								if(count($list) != 1)
								{
									$this->logFailure(
										$this->userObj->getImportId(),
										sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"AuthMode","type",$a_attribs['type']));
								}
								break;
							}
							if(strcmp('ldap', $a_attribs['type']) === 0)
							{
								// no server id provided
								include_once './Services/LDAP/classes/class.ilLDAPServer.php';
								$list = ilLDAPServer::_getActiveServerList();
								if(count($list) != 1)
								{
									$this->logFailure(
										$this->userObj->getImportId(), 
										sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"AuthMode","type",$a_attribs['type']));
								}
							}
							break;

						case "default":
						case "local":
						case "radius":
						case "shibboleth":
						case "script":
						case "cas":
						case "soap":
						case "openid":
						// begin-patch auth_plugin
						default:
							$this->userObj->setAuthMode($a_attribs["type"]);
							break;
						/*
						default:
							$this->logFailure($this->userObj->getImportId(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"AuthMode","type",$a_attribs["type"]));
							break;
						 *
						 */
					}
				}
				else
				{
					$this->logFailure($this->userObj->getImportId(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"AuthMode","type",""));
				}
				break;
			case 'Pref':
				$this->currentPrefKey = $a_attribs["key"];
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
	 * Returns the parent object of the role folder object which contains the specified role.
	 */
	function getRoleObject($a_role_id)
	{
		if (array_key_exists($a_role_id, $this->localRoleCache))
		{
			return $this->localRoleCache[$a_role_id];
		}
		else
		{
			$role_obj = new ilObjRole($a_role_id, false);
			$role_obj->read();
			$this->localRoleCache[$a_role_id] = $role_obj;
			return $role_obj;
		}

	}
	/**
	 * Returns the parent object of the role folder object which contains the specified role.
	 */
	function getCourseMembersObjectForRole($a_role_id)
	{
		global $DIC;

		$rbacreview = $DIC['rbacreview'];
		$rbacadmin = $DIC['rbacadmin'];
		$tree = $DIC['tree'];

		if (array_key_exists($a_role_id.'_courseMembersObject', $this->localRoleCache))
		{
			return $this->localRoleCache[$a_role_id.'_courseMembersObject'];
		}
		else
		{
			require_once("Modules/Course/classes/class.ilObjCourse.php");
			require_once("Modules/Course/classes/class.ilCourseParticipants.php");
			$course_refs = $rbacreview->getFoldersAssignedToRole($a_role_id, true);
			$course_ref = $course_refs[0];
			$course_obj = new ilObjCourse($course_ref, true);
			$crsmembers_obj = ilCourseParticipants::_getInstanceByObjId($course_obj->getId());
			$this->localRoleCache[$a_role_id.'_courseMembersObject'] = $crsmembers_obj;
			return $crsmembers_obj;
		}

	}

	/**
	 * Assigns a user to a role.
         */
	function assignToRole($a_user_obj, $a_role_id)
	{
		require_once "./Services/AccessControl/classes/class.ilObjRole.php";
		include_once('./Services/Object/classes/class.ilObject.php');
		#require_once "Modules/Course/classes/class.ilObjCourse.php";
		#require_once "Modules/Course/classes/class.ilCourseParticipants.php";

		global $DIC;

		$rbacreview = $DIC['rbacreview'];
		$rbacadmin = $DIC['rbacadmin'];
		$tree = $DIC['tree'];

		// Do nothing, if the user is already assigned to the role.
                // Specifically, we do not want to put a course object or
                // group object on the personal desktop again, if a user
                // has removed it from the personal desktop.
		if ($rbacreview->isAssigned($a_user_obj->getId(), $a_role_id))
		{
			return;
		}
                
		// If it is a course role, use the ilCourseMember object to assign
		// the user to the role
		
		$rbacadmin->assignUser($a_role_id, $a_user_obj->getId(), true);
		$obj_id = $rbacreview->getObjectOfRole($a_role_id);
		switch($type = ilObject::_lookupType($obj_id))
		{
			case 'grp':
			case 'crs':
				$ref_ids = ilObject::_getAllReferences($obj_id);
				$ref_id = current((array) $ref_ids);
				if($ref_id)
				{
					ilObjUser::_addDesktopItem($a_user_obj->getId(),$ref_id,$type);
				}
				break;
			default:
				break;
		}
	}
	/**
	 * Get array of parent role ids from cache.
	 * If necessary, create a new cache entry.
	 */
	function getParentRoleIds($a_role_id)
	{
		global $DIC;

		$rbacreview = $DIC['rbacreview'];
	
		if (! array_key_exists($a_role_id, $this->parentRolesCache))
		{
			$parent_role_ids = array();
			
			$role_obj = $this->getRoleObject($a_role_id);
			$short_role_title = substr($role_obj->getTitle(),0,12);
			$folders = $rbacreview->getFoldersAssignedToRole($a_role_id, true);
			if (count($folders) > 0)
			{
				$all_parent_role_ids = $rbacreview->getParentRoleIds($folders[0]);
				foreach ($all_parent_role_ids as $parent_role_id => $parent_role_data)
				{
					if ($parent_role_id != $a_role_id)
					{
						switch (substr($parent_role_data['title'],0,12))
						{
							case 'il_crs_admin' :
							case 'il_grp_admin' :
								if ($short_role_title == 'il_crs_admin' || $short_role_title == 'il_grp_admin')
								{
									$parent_role_ids[] = $parent_role_id;
								}
								break;
							case 'il_crs_tutor' :
							case 'il_grp_tutor' :
								if ($short_role_title == 'il_crs_tutor' || $short_role_title == 'il_grp_tutor')
								{
									$parent_role_ids[] = $parent_role_id;
								}
								break;
							case 'il_crs_membe' :
							case 'il_grp_membe' :
								if ($short_role_title == 'il_crs_membe' || $short_role_title == 'il_grp_membe')
								{
									$parent_role_ids[] = $parent_role_id;
								}
								break;
							default :
								break;
						}
					}
				}
			}
			$this->parentRolesCache[$a_role_id] = $parent_role_ids;
		}
		return $this->parentRolesCache[$a_role_id];
	}
	/**
	 * Assigns a user to a role and to all parent roles.
     */
	function assignToRoleWithParents($a_user_obj, $a_role_id)
	{
		$this->assignToRole($a_user_obj, $a_role_id);
		
		$parent_role_ids = $this->getParentRoleIds($a_role_id);
		foreach ($parent_role_ids as $parent_role_id)
		{
			$this->assignToRole($a_user_obj, $parent_role_id);
		}
	}
	/**
	 * Detachs a user from a role.
     */
	function detachFromRole($a_user_obj, $a_role_id)
	{
		global $DIC;

		$rbacreview = $DIC['rbacreview'];
		$rbacadmin = $DIC['rbacadmin'];
		$tree = $DIC['tree'];

		$rbacadmin->deassignUser($a_role_id, $a_user_obj->getId());
		
		if (substr(ilObject::_lookupTitle($a_role_id),0,6) == 'il_crs' or
			substr(ilObject::_lookupTitle($a_role_id),0,6) == 'il_grp')
		{
			$obj = $rbacreview->getObjectOfRole($a_role_id);
			$ref = ilObject::_getAllReferences($obj);
			$ref_id = end($ref);
			ilObjUser::_dropDesktopItem($a_user_obj->getId(), $ref_id, ilObject::_lookupType($obj));
		}
}

	/**
	* handler for end of element when in import user mode.
	*/
	function importEndTag($a_xml_parser, $a_name)
	{
		global $DIC;

		$ilias = $DIC['ilias'];
		$rbacadmin = $DIC['rbacadmin'];
		$rbacreview = $DIC['rbacreview'];
		$ilUser = $DIC['ilUser'];
		$lng = $DIC['lng'];
		$ilSetting = $DIC['ilSetting'];

		switch($a_name)
		{
			case "Role":
				$this->roles[$this->current_role_id]["name"] = $this->cdata;
				$this->roles[$this->current_role_id]["type"] = $this->current_role_type;
				$this->roles[$this->current_role_id]["action"] = $this->current_role_action;
				break;

			case "PersonalPicture":
				switch ($this->personalPicture["encoding"])
				{
					case "Base64":
						$this->personalPicture["content"] = base64_decode($this->cdata);
						break;
					case "UUEncode":
    					$this->personalPicture["content"] = convert_uudecode($this->cdata);
						break;
				}
				break;

			case "User":
				$this->userObj->setFullname();
				// Fetch the user_id from the database, if we didn't have it in xml file
				// fetch as well, if we are trying to insert -> recognize duplicates!
				if ($this->user_id == -1 || $this->action=="Insert")
					$user_id = ilObjUser::getUserIdByLogin($this->userObj->getLogin());
				else
					$user_id = $this->user_id;

                //echo $user_id.":".$this->userObj->getLogin();

				// Handle conflicts
				switch ($this->conflict_rule)
				{
					case IL_FAIL_ON_CONFLICT :
						// do not change action
						break;
					case IL_UPDATE_ON_CONFLICT :
						switch ($this->action)
						{
							case "Insert" :
								if ($user_id)
								{
									$this->logWarning($this->userObj->getLogin(),sprintf($lng->txt("usrimport_action_replaced"),"Insert","Update"));
									$this->action = "Update";
								}
								break;
							case "Update" :
								if (! $user_id)
								{
									$this->logWarning($this->userObj->getLogin(),sprintf($lng->txt("usrimport_action_replaced"),"Update","Insert"));
									$this->action = "Insert";
								}
								break;
							case "Delete" :
								if (! $user_id)
								{
									$this->logWarning($this->userObj->getLogin(),sprintf($lng->txt("usrimport_action_ignored"),"Delete"));
									$this->action = "Ignore";
								}
								break;
						}
						break;
					case IL_IGNORE_ON_CONFLICT :
						switch ($this->action)
						{
							case "Insert" :
								if ($user_id)
								{
									$this->logWarning($this->userObj->getLogin(),sprintf($lng->txt("usrimport_action_ignored"),"Insert"));
									$this->action = "Ignore";
								}
								break;
							case "Update" :
								if (! $user_id)
								{
									$this->logWarning($this->userObj->getLogin(),sprintf($lng->txt("usrimport_action_ignored"),"Update"));
									$this->action = "Ignore";
								}
								break;
							case "Delete" :
								if (! $user_id)
								{
									$this->logWarning($this->userObj->getLogin(),sprintf($lng->txt("usrimport_action_ignored"),"Delete"));
									$this->action = "Ignore";
								}
								break;
						}
						break;
				}

				// check external account conflict (if external account is already used)
				// note: we cannot apply conflict rules in the same manner as to logins here
				// so we ignore records with already existing external accounts.
				//echo $this->userObj->getAuthMode().'h';
				$am = ($this->userObj->getAuthMode() == "default" || $this->userObj->getAuthMode() == "")
					? ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode'))
					: $this->userObj->getAuthMode();
				$loginForExternalAccount = ($this->userObj->getExternalAccount() == "")
					? ""
					: ilObjUser::_checkExternalAuthAccount($am, $this->userObj->getExternalAccount());
				switch ($this->action)
				{
					case "Insert" :
						if ($loginForExternalAccount != "")
						{
							$this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_no_insert_ext_account_exists")." (".$this->userObj->getExternalAccount().")");
							$this->action = "Ignore";
						}
						break;
						
					case "Update" :
						// this variable describes the ILIAS login which belongs to the given external account!!!
						// it is NOT nescessarily the ILIAS login of the current user record !!
						// so if we found an ILIAS login according to the authentication method
						// check if the ILIAS login belongs to the current user record, otherwise somebody else is using it!
						if ($loginForExternalAccount != "") 
						{
							// check if we changed the value!
							$externalAccountHasChanged = $this->userObj->getExternalAccount() != ilObjUser::_lookupExternalAccount($this->user_id);
							// if it has changed and the external login 
							if ($externalAccountHasChanged && trim($loginForExternalAccount) != trim($this->userObj->getLogin()))
							{
								$this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_no_update_ext_account_exists")." (".$this->userObj->getExternalAccount().")");
								$this->action = "Ignore";
							}							
						}
						break;
				}
				
				if(sizeof($this->multi_values))
				{
					if(isset($this->multi_values["GeneralInterest"]))
					{
						$this->userObj->setGeneralInterests($this->multi_values["GeneralInterest"]);
					}
					if(isset($this->multi_values["OfferingHelp"]))
					{
						$this->userObj->setOfferingHelp($this->multi_values["OfferingHelp"]);
					}
					if(isset($this->multi_values["LookingForHelp"]))
					{
						$this->userObj->setLookingForHelp($this->multi_values["LookingForHelp"]);
					}
				}

				// Perform the action
				switch ($this->action)
				{
					case "Insert" :
						if ($user_id)
						{
							$this->logFailure($this->userObj->getLogin(),$lng->txt("usrimport_cant_insert"));
						}
						else
						{

							if(!strlen($this->currPassword) == 0)
							{
								switch(strtoupper($this->currPasswordType))
								{
									case "BCRYPT":
										$this->userObj->setPasswd($this->currPassword, IL_PASSWD_CRYPTED);
										$this->userObj->setPasswordEncodingType('bcryptphp');
										$this->userObj->setPasswordSalt(null);
										break;

									case "PLAIN":
										$this->userObj->setPasswd($this->currPassword, IL_PASSWD_PLAIN);
										$this->acc_mail->setUserPassword($this->currPassword);
										break;

									default:
										$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"Type","Password",$this->currPasswordType));
										break;

								}
							}
							else
							{
								// this does the trick for empty passwords
								// since a MD5 string has always 32 characters,
								// no hashed password combination will ever equal to
								// an empty string
								$this->userObj->setPasswd("", IL_PASSWD_CRYPTED);

							}

							$this->userObj->setTitle($this->userObj->getFullname());
							$this->userObj->setDescription($this->userObj->getEmail());
							
							if(!$this->time_limit_owner_set)
							{
								$this->userObj->setTimeLimitOwner($this->getFolderId());
							}

							// default time limit settings
							if(!$this->time_limit_set)
							{
								$this->userObj->setTimeLimitUnlimited(1);
								$this->userObj->setTimeLimitMessage(0);

								if (! $this->approve_date_set)
								{
									$this->userObj->setApproveDate(date("Y-m-d H:i:s"));
								}
							}


							$this->userObj->setActive($this->currActive == 'true' || is_null($this->currActive));

							// Finally before saving new user.
							// Check if profile is incomplete
							
							// #8759
							if(count($this->udf_data))
							{
								$this->userObj->setUserDefinedData($this->udf_data);								
							}
							
							$this->userObj->setProfileIncomplete($this->checkProfileIncomplete($this->userObj));
							$this->userObj->create();

							//insert user data in table user_data
							$this->userObj->saveAsNew(false);
							
							// Set default prefs						
							$this->userObj->setPref('hits_per_page',$ilSetting->get('hits_per_page',30));
							//$this->userObj->setPref('show_users_online',$ilSetting->get('show_users_online','y'));

							if (count ($this->prefs)) 
							{
								foreach ($this->prefs as $key => $value)
								{
									if ($key != "mail_incoming_type" && 
									    $key != "mail_signature" &&
									    $key != "mail_linebreak"
									)
									{
									   $this->userObj->setPref($key, $value);
									}
								}
							}

							if(!is_array($this->prefs) || array_search('chat_osc_accept_msg', $this->prefs) === false)
							{
								$this->userObj->setPref('chat_osc_accept_msg', $ilSetting->get('chat_osc_accept_msg', 'n'));
							}
							if(!is_array($this->prefs) || array_search('bs_allow_to_contact_me', $this->prefs) === false)
							{
								$this->userObj->setPref('bs_allow_to_contact_me', $ilSetting->get('bs_allow_to_contact_me', 'n'));
							}

							$this->userObj->writePrefs();

							// update mail preferences, to be extended
							$this->updateMailPreferences($this->userObj->getId());
							
							if (is_array($this->personalPicture))
							{
								if (strlen($this->personalPicture["content"]))
								{
									$extension = "jpg";
									if (preg_match("/.*(png|jpg|gif|jpeg)$/", $this->personalPicture["imagetype"], $matches))
									{
										$extension = $matches[1];
									}
									$tmp_name = $this->saveTempImage($this->personalPicture["content"], ".$extension");
									if (strlen($tmp_name))
									{
										ilObjUser::_uploadPersonalPicture($tmp_name, $this->userObj->getId());
										unlink($tmp_name);
									}
								}
							}

							//set role entries
							foreach($this->roles as $role_id => $role)
							{
								if ($this->role_assign[$role_id])
								{
									$this->assignToRole($this->userObj, $this->role_assign[$role_id]);
								}
							}

							if(count($this->udf_data))
							{
								include_once './Services/User/classes/class.ilUserDefinedData.php';
								$udd = new ilUserDefinedData($this->userObj->getId());
								foreach($this->udf_data as $field => $value)
								{
									$udd->set("f_".$field,$value);
								}
								$udd->update();
							}

							$this->sendAccountMail();
							$this->logSuccess($this->userObj->getLogin(),$this->userObj->getId(), "Insert");
							// reset account mail object
							$this->acc_mail->reset();
						}
						break;

					case "Update" :
						if (! $user_id)
						{
							$this->logFailure($this->userObj->getLogin(),$lng->txt("usrimport_cant_update"));
						}
						else
						{
							$updateUser = new ilObjUser($user_id);
							$updateUser->read();
							$updateUser->readPrefs();
							if ($this->currPassword != null)
							{
								switch(strtoupper($this->currPasswordType))
								{
									case "BCRYPT":
										$updateUser->setPasswd($this->currPassword, IL_PASSWD_CRYPTED);
										$updateUser->setPasswordEncodingType('bcryptphp');
										$updateUser->setPasswordSalt(null);
										break;

									case "PLAIN":
										$updateUser->setPasswd($this->currPassword, IL_PASSWD_PLAIN);
										$this->acc_mail->setUserPassword($this->currPassword);
										break;

									default:
										$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"Type","Password",$this->currPasswordType));
										break;
								}
							}
							if (! is_null($this->userObj->getFirstname())) $updateUser->setFirstname($this->userObj->getFirstname());
							if (! is_null($this->userObj->getLastname())) $updateUser->setLastname($this->userObj->getLastname());
							if (! is_null($this->userObj->getUTitle())) $updateUser->setUTitle($this->userObj->getUTitle());
							if (! is_null($this->userObj->getGender())) $updateUser->setGender($this->userObj->getGender());
							if (! is_null($this->userObj->getEmail())) $updateUser->setEmail($this->userObj->getEmail());
							if (! is_null($this->userObj->getSecondEmail())) $updateUser->setSecondEmail($this->userObj->getSecondEmail());
							if (! is_null($this->userObj->getBirthday())) $updateUser->setBirthday($this->userObj->getBirthday());
							if (! is_null($this->userObj->getInstitution())) $updateUser->setInstitution($this->userObj->getInstitution());
							if (! is_null($this->userObj->getStreet())) $updateUser->setStreet($this->userObj->getStreet());
							if (! is_null($this->userObj->getCity())) $updateUser->setCity($this->userObj->getCity());
							if (! is_null($this->userObj->getZipCode())) $updateUser->setZipCode($this->userObj->getZipCode());
							if (! is_null($this->userObj->getCountry())) $updateUser->setCountry($this->userObj->getCountry());
							if (! is_null($this->userObj->getSelectedCountry())) $updateUser->setSelectedCountry($this->userObj->getSelectedCountry());
							if (! is_null($this->userObj->getPhoneOffice())) $updateUser->setPhoneOffice($this->userObj->getPhoneOffice());
							if (! is_null($this->userObj->getPhoneHome())) $updateUser->setPhoneHome($this->userObj->getPhoneHome());
							if (! is_null($this->userObj->getPhoneMobile())) $updateUser->setPhoneMobile($this->userObj->getPhoneMobile());
							if (! is_null($this->userObj->getFax())) $updateUser->setFax($this->userObj->getFax());
							if (! is_null($this->userObj->getHobby())) $updateUser->setHobby($this->userObj->getHobby());
							if (! is_null($this->userObj->getGeneralInterests())) $updateUser->setGeneralInterests($this->userObj->getGeneralInterests());
							if (! is_null($this->userObj->getOfferingHelp())) $updateUser->setOfferingHelp($this->userObj->getOfferingHelp());
							if (! is_null($this->userObj->getLookingForHelp())) $updateUser->setLookingForHelp($this->userObj->getLookingForHelp());
							if (! is_null($this->userObj->getComment())) $updateUser->setComment($this->userObj->getComment());
							if (! is_null($this->userObj->getDepartment())) $updateUser->setDepartment($this->userObj->getDepartment());
							if (! is_null($this->userObj->getMatriculation())) $updateUser->setMatriculation($this->userObj->getMatriculation());
							if (! is_null($this->currActive)) $updateUser->setActive($this->currActive == "true", is_object($ilUser) ? $ilUser->getId() : 0);
							if (! is_null($this->userObj->getClientIP())) $updateUser->setClientIP($this->userObj->getClientIP());
							if (! is_null($this->userObj->getTimeLimitUnlimited())) $updateUser->setTimeLimitUnlimited($this->userObj->getTimeLimitUnlimited());
							if (! is_null($this->userObj->getTimeLimitFrom())) $updateUser->setTimeLimitFrom($this->userObj->getTimeLimitFrom());
							if (! is_null($this->userObj->getTimeLimitUntil())) $updateUser->setTimeLimitUntil($this->userObj->getTimeLimitUntil());
							if (! is_null($this->userObj->getTimeLimitMessage())) $updateUser->setTimeLimitMessage($this->userObj->getTimeLimitMessage());
							if (! is_null($this->userObj->getApproveDate())) $updateUser->setApproveDate($this->userObj->getApproveDate());
							if (! is_null($this->userObj->getAgreeDate())) $updateUser->setAgreeDate($this->userObj->getAgreeDate());
							if (! is_null($this->userObj->getLanguage())) $updateUser->setLanguage($this->userObj->getLanguage());
							if (! is_null($this->userObj->getExternalAccount())) $updateUser->setExternalAccount($this->userObj->getExternalAccount());
							
							// Fixed: if auth_mode is not set, it was always overwritten with auth_default
							#if (! is_null($this->userObj->getAuthMode())) $updateUser->setAuthMode($this->userObj->getAuthMode());
							if($this->auth_mode_set)
								$updateUser->setAuthMode($this->userObj->getAuthMode());
							
							// Special handlin since it defaults to 7 (USER_FOLDER_ID)
							if($this->time_limit_owner_set)
							{
								$updateUser->setTimeLimitOwner($this->userObj->getTimeLimitOwner());
							}							

							
							if (count ($this->prefs)) 
							{
								foreach ($this->prefs as $key => $value)
								{
									if ($key != "mail_incoming_type" && 
									    $key != "mail_signature" &&
									    $key != "mail_linebreak"
									){
									    $updateUser->setPref($key, $value);
									}
								}
							}
							
							// save user preferences (skin and style)
							if ($this->updateLookAndSkin)
							{
								$updateUser->setPref("skin", $this->userObj->getPref("skin"));
								$updateUser->setPref("style", $this->userObj->getPref("style"));
							}
							
																					
							$updateUser->writePrefs();
							
							// update mail preferences, to be extended
							$this->updateMailPreferences($updateUser->getId());
							
							// #8759
							if(count($this->udf_data))
							{
								$updateUser->setUserDefinedData($this->udf_data);								
							}
							
							$updateUser->setProfileIncomplete($this->checkProfileIncomplete($updateUser));
							$updateUser->setFullname();
							$updateUser->setTitle($updateUser->getFullname());
							$updateUser->setDescription($updateUser->getEmail());
							$updateUser->update();

							if(count($this->udf_data))
							{
								include_once './Services/User/classes/class.ilUserDefinedData.php';
								$udd = new ilUserDefinedData($updateUser->getId());
								foreach($this->udf_data as $field => $value)
								{
									$udd->set("f_".$field,$value);
								}
								$udd->update();
							}

							// update login
							if (!is_null($this->userObj->getLogin()) && $this->user_id != -1)
							{							
								try 
								{
									$updateUser->updateLogin($this->userObj->getLogin());
								}
								catch (ilUserException $e)
								{									
								}
							}
								

						    // if language has changed

							if (is_array($this->personalPicture))
							{
								if (strlen($this->personalPicture["content"]))
								{
									$extension = "jpg";
									if (preg_match("/.*(png|jpg|gif|jpeg)$/", $this->personalPicture["imagetype"], $matches))
									{
										$extension = $matches[1];
									}
									$tmp_name = $this->saveTempImage($this->personalPicture["content"], ".$extension");
									if (strlen($tmp_name))
									{
										ilObjUser::_uploadPersonalPicture($tmp_name, $this->userObj->getId());
										unlink($tmp_name);
									}
								}
							}


							//update role entries
							//-------------------
							foreach ($this->roles as $role_id => $role)
							{
								if ($this->role_assign[$role_id])
								{
									switch ($role["action"])
									{
										case "Assign" :
											$this->assignToRole($updateUser, $this->role_assign[$role_id]);
											break;
										case "AssignWithParents" :
											$this->assignToRoleWithParents($updateUser, $this->role_assign[$role_id]);
											break;
										case "Detach" :
											$this->detachFromRole($updateUser, $this->role_assign[$role_id]);
											break;
									}
								}
							}
    						$this->logSuccess($updateUser->getLogin(), $user_id, "Update");
						}
						break;
					case "Delete" :
						if (! $user_id)
						{
							$this->logFailure($this->userObj->getLogin(),$lng->txt("usrimport_cant_delete"));
						}
						else
						{
							$deleteUser = new ilObjUser($user_id);
							$deleteUser->delete();

							$this->logSuccess($this->userObj->getLogin(),$user_id, "Delete");
						}
						break;
				}

				// init role array for next user
				$this->roles = array();
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
			case "SecondEmail":
				$this->userObj->setSecondEmail($this->cdata);
				break;
			case "Birthday":
				$timestamp = strtotime($this->cdata);
				if ($timestamp !== false)
				{
					$this->userObj->setBirthday($this->cdata);
				}				
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

			case "SelCountry":
				$this->userObj->setSelectedCountry($this->cdata);
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
			
			case "GeneralInterest":
			case "OfferingHelp":
			case "LookingForHelp":
				$this->multi_values[$a_name][] = $this->cdata;				
				break;			

			case "Comment":
				$this->userObj->setComment($this->cdata);
				break;

			case "Department":
				$this->userObj->setDepartment($this->cdata);
				break;

			case "Matriculation":
				$this->userObj->setMatriculation($this->cdata);
				break;

			case "Active":
				$this->currActive = $this->cdata;
				break;

			case "ClientIP":
				$this->userObj->setClientIP($this->cdata);
				break;

			case "TimeLimitOwner":
				$this->time_limit_owner_set = true;
				$this->userObj->setTimeLimitOwner($this->cdata);
				break;

			case "TimeLimitUnlimited":
				$this->time_limit_set = true;
				$this->userObj->setTimeLimitUnlimited($this->cdata);
				break;

			case "TimeLimitFrom":
				if (is_numeric($this->cdata))
				{
					// Treat cdata as a unix timestamp
					$this->userObj->setTimeLimitFrom($this->cdata);
				}
				else
				{
					// Try to convert cdata into unix timestamp, or ignore it
					$timestamp = strtotime($this->cdata);
					if ($timestamp !== false && trim($this->cdata) != "0000-00-00 00:00:00")
					{
						$this->userObj->setTimeLimitFrom($timestamp);
					}
					elseif ($this->cdata == "0000-00-00 00:00:00") 
					{
					    $this->userObj->setTimeLimitFrom(null);
					}

				}
				break;

			case "TimeLimitUntil":
				if (is_numeric($this->cdata))
				{
					// Treat cdata as a unix timestamp
					$this->userObj->setTimeLimitUntil($this->cdata);
				}
				else
				{
					// Try to convert cdata into unix timestamp, or ignore it
					$timestamp = strtotime($this->cdata);
					if ($timestamp !== false && trim($this->cdata) != "0000-00-00 00:00:00")
					{
						$this->userObj->setTimeLimitUntil($timestamp);
					}
					elseif ($this->cdata == "0000-00-00 00:00:00") 
					{
					    $this->userObj->setTimeLimitUntil(null);
					}
				}
				break;

			case "TimeLimitMessage":
				$this->userObj->setTimeLimitMessage($this->cdata);
				break;

			case "ApproveDate":
				$this->approve_date_set = true;
				if (is_numeric($this->cdata))
				{
					// Treat cdata as a unix timestamp
					$tmp_date = new ilDateTime($this->cdata,IL_CAL_UNIX);
					$this->userObj->setApproveDate($tmp_date->get(IL_CAL_DATETIME));
				}
				else
				{
					// Try to convert cdata into unix timestamp, or ignore it
					$timestamp = strtotime($this->cdata);
					if ($timestamp !== false && trim($this->cdata) != "0000-00-00 00:00:00")
					{
						$tmp_date = new ilDateTime($timestamp,IL_CAL_UNIX);
						$this->userObj->setApproveDate($tmp_date->get(IL_CAL_DATETIME));
					}
					elseif ($this->cdata == "0000-00-00 00:00:00") 
					{
					    $this->userObj->setApproveDate(null);
					}
				}
				break;

			case "AgreeDate":
				if (is_numeric($this->cdata))
				{
					// Treat cdata as a unix timestamp
					$tmp_date = new ilDateTime($this->cdata,IL_CAL_UNIX);
					$this->userObj->setAgreeDate($tmp_date->get(IL_CAL_DATETIME));
				}
				else
				{
					// Try to convert cdata into unix timestamp, or ignore it
					$timestamp = strtotime($this->cdata);
					if ($timestamp !== false && trim($this->cdata) != "0000-00-00 00:00:00")
					{
						$tmp_date = new ilDateTime($timestamp,IL_CAL_UNIX);
						$this->userObj->setAgreeDate($tmp_date->get(IL_CAL_DATETIME));
					} 
					elseif ($this->cdata == "0000-00-00 00:00:00") 
					{
					    $this->userObj->setAgreeDate(null);
					}
				}
				break;

			case "ExternalAccount":
				$this->userObj->setExternalAccount($this->cdata);
				break;

			case "Look":
				$this->updateLookAndSkin = false;
				if (!$this->hideSkin)
				{
					// TODO: what to do with disabled skins? is it possible to change the skin via import?
					if ((strlen($this->skin) > 0) && (strlen($this->style) > 0))
					{
						if (is_array($this->userStyles))
						{
							if (in_array($this->skin . ":" . $this->style, $this->userStyles))
							{
								$this->userObj->setPref("skin", $this->skin);
								$this->userObj->setPref("style", $this->style);
								$this->updateLookAndSkin = true;
							}
						}
					}
				}
				break;
				
			case 'UserDefinedField':
				include_once './Services/User/classes/class.ilUserDefinedFields.php';
				$udf = ilUserDefinedFields::_getInstance();
				if($field_id = $udf->fetchFieldIdFromImportId($this->tmp_udf_id))
				{
					$this->udf_data[$field_id] = $this->cdata;
				}
				elseif($field_id = $udf->fetchFieldIdFromName($this->tmp_udf_name))
				{
					$this->udf_data[$field_id] = $this->cdata;
				}
				break;
			case 'AccountInfo':
				if($this->current_messenger_type =="external")
				{
					$this->userObj->setExternalAccount($this->cdata);
				}
				break;
			case 'Pref':
				if ($this->currentPrefKey != null && strlen(trim($this->cdata)) > 0 
					&& ilUserXMLWriter::isPrefExportable($this->currentPrefKey))
					$this->prefs[$this->currentPrefKey] = trim($this->cdata);
				$this->currentPrefKey = null;
				break;
		}
	}

	/**
	* Saves binary image data to a temporary image file and returns
	* the name of the image file on success.
	*/
	function saveTempImage($image_data, $filename)
	{
		$tempname = ilUtil::ilTempnam() . $filename;
		$fh = fopen($tempname, "wb");
		if ($fh == false)
		{
			return "";
		}
		$imagefile = fwrite($fh, $image_data);
		fclose($fh);
		return $tempname;
	}

	/**
	* handler for end of element when in verify mode.
	*/
	function verifyEndTag($a_xml_parser, $a_name)
	{
		global $DIC;

		$lng = $DIC['lng'];
		$ilAccess = $DIC['ilAccess'];
		$ilSetting = $DIC['ilSetting'];
		$ilObjDataCache = $DIC['ilObjDataCache'];

		switch($a_name)
		{
			case "Role":
				$this->roles[$this->current_role_id]["name"] = $this->cdata;
				$this->roles[$this->current_role_id]["type"] = $this->current_role_type;
				$this->roles[$this->current_role_id]["action"] = $this->current_role_action;
				break;

			case "User":
				$this->userObj->setFullname();
				if ($this->user_id != -1 && ($this->action == "Update" || $this->action == "Delete"))
				    $user_exists = !is_null(ilObjUser::_lookupLogin($this->user_id));
			    else
			        $user_exists = ilObjUser::getUserIdByLogin($this->userObj->getLogin()) != 0;

				if (is_null($this->userObj->getLogin()))
				{
					$this->logFailure("---",sprintf($lng->txt("usrimport_xml_element_for_action_required"),"Login", "Insert"));
				}

				switch ($this->action)
				{
					case "Insert" :
						if ($user_exists and $this->conflict_rule == IL_FAIL_ON_CONFLICT)
						{
							$this->logWarning($this->userObj->getLogin(),$lng->txt("usrimport_cant_insert"));
						}
						if (is_null($this->userObj->getGender()) && $this->isFieldRequired("gender"))
						{
							$this->logFailure($this->userObj->getLogin(),sprintf($lng->txt("usrimport_xml_element_for_action_required"),"Gender", "Insert"));
						}
						if (is_null($this->userObj->getFirstname()))
						{
							$this->logFailure($this->userObj->getLogin(),sprintf($lng->txt("usrimport_xml_element_for_action_required"),"Firstname", "Insert"));
						}
						if (is_null($this->userObj->getLastname()))
						{
							$this->logFailure($this->userObj->getLogin(),sprintf($lng->txt("usrimport_xml_element_for_action_required"),"Lastname", "Insert"));
						}
						if (count($this->roles) == 0)
						{
							$this->logFailure($this->userObj->getLogin(),sprintf($lng->txt("usrimport_xml_element_for_action_required"),"Role", "Insert"));
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
								$this->logFailure($this->userObj->getLogin(),sprintf($lng->txt("usrimport_global_role_for_action_required"),"Insert"));
							}
						}
						break;
					case "Update" :
						if(!$user_exists)
						{
							$this->logWarning($this->userObj->getLogin(),$lng->txt("usrimport_cant_update"));
						} 
						elseif($this->user_id != -1 && !is_null($this->userObj->getLogin()))
							// check if someone owns the new login name!
                        {
                            $someonesId = ilObjUser::_lookupId($this->userObj->getLogin());

                            if (is_numeric($someonesId ) && $someonesId != $this->user_id) {
               			          $this->logFailure($this->userObj->getLogin(), $lng->txt("usrimport_login_is_not_unique"));
                            }
                        }
						break;
					case "Delete" :
						if(!$user_exists)
						{
							$this->logWarning($this->userObj->getLogin(),$lng->txt("usrimport_cant_delete"));
						}
						break;
				}

				// init role array for next user
				$this->roles = array();
				break;

			case "Login":
				if (array_key_exists($this->cdata, $this->logins))
				{
					$this->logWarning($this->cdata, $lng->txt("usrimport_login_is_not_unique"));
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
					case "BCRYPT":
						$this->userObj->setPasswd($this->cdata, IL_PASSWD_CRYPTED);
						$this->userObj->setPasswordEncodingType('bcryptphp');
						$this->userObj->setPasswordSalt(null);
						break;

					case "PLAIN":
						$this->userObj->setPasswd($this->cdata, IL_PASSWD_PLAIN);
						$this->acc_mail->setUserPassword($this->currPassword);
						break;

					default :
						$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_attribute_value_illegal"),"Type","Password",$this->currPasswordType));
						break;
				}
				break;

			case "Firstname":
				$this->userObj->setFirstname($this->cdata);
				break;

			case "Lastname":
				$this->userObj->setLastname($this->cdata);
				break;

			case "Title":
				$this->userObj->setUTitle($this->cdata);
				break;

			case "Gender":
				if (!in_array(strtolower($this->cdata), ['n', 'm', 'f'])) {
					$this->logFailure(
						$this->userObj->getLogin(), 
						sprintf($lng->txt("usrimport_xml_element_content_illegal"),"Gender",$this->cdata)
					);
				}
				$this->userObj->setGender($this->cdata);
				break;

			case "Email":
				$this->userObj->setEmail($this->cdata);
				break;
			case "SecondEmail":
				$this->userObj->setSecondEmail($this->cdata);
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

			case "SelCountry":
				$this->userObj->setSelectedCountry($this->cdata);
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
			
			case "GeneralInterest":
			case "OfferingHelp":
			case "LookingForHelp":
				$this->multi_values[$a_name][] = $this->cdata;				
				break;				

			case "Comment":
				$this->userObj->setComment($this->cdata);
				break;

			case "Department":
				$this->userObj->setDepartment($this->cdata);
				break;

			case "Matriculation":
				$this->userObj->setMatriculation($this->cdata);
				break;

			case "ExternalAccount":
//echo "-".$this->userObj->getAuthMode()."-".$this->userObj->getLogin()."-";
				$am = ($this->userObj->getAuthMode() == "default" || $this->userObj->getAuthMode() == "")
					? ilAuthUtils::_getAuthModeName($ilSetting->get('auth_mode'))
					: $this->userObj->getAuthMode();
				$loginForExternalAccount = (trim($this->cdata) == "")
					? ""
					: ilObjUser::_checkExternalAuthAccount($am, trim($this->cdata));
				switch ($this->action)
				{
					case "Insert" :
						if ($loginForExternalAccount != "")
						{
							$this->logWarning($this->userObj->getLogin(), $lng->txt("usrimport_no_insert_ext_account_exists")." (".$this->cdata.")");
						}
						break;
						
					case "Update" :
						if ($loginForExternalAccount != "")
						{
							$externalAccountHasChanged = trim($this->cdata) != ilObjUser::_lookupExternalAccount($this->user_id);
							if ($externalAccountHasChanged && trim($loginForExternalAccount) != trim($this->userObj->getLogin()))
							{
								$this->logWarning($this->userObj->getLogin(),
									$lng->txt("usrimport_no_update_ext_account_exists")." (".$this->cdata." for ".$loginForExternalAccount.")");
							}
						}
						break;
						
				}
				if ($externalAccountHasChanged)
					$this->userObj->setExternalAccount(trim($this->cdata));
				break;
				
			case "Active":
				if ($this->cdata != "true"
				&& $this->cdata != "false")
				{
					$this->logFailure($this->userObj->getLogin(),
									  sprintf($lng->txt("usrimport_xml_element_content_illegal"),"Active",$this->cdata));
				}
				$this->currActive = $this->cdata;
				break;
			case "TimeLimitOwner":
				if (!preg_match("/\d+/", $this->cdata))
				{
					$this->logFailure($this->userObj->getLogin(),
									  sprintf($lng->txt("usrimport_xml_element_content_illegal"),"TimeLimitOwner",$this->cdata));
				} 
				elseif(!$ilAccess->checkAccess('cat_administrate_users','',$this->cdata))
				{
					$this->logFailure($this->userObj->getLogin(),
									  sprintf($lng->txt("usrimport_xml_element_content_illegal"),"TimeLimitOwner",$this->cdata));
				}
				elseif($ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($this->cdata)) != 'cat' && !(int) $this->cdata == USER_FOLDER_ID)
				{
					$this->logFailure($this->userObj->getLogin(),
									  sprintf($lng->txt("usrimport_xml_element_content_illegal"),"TimeLimitOwner",$this->cdata));
					
				}
				$this->userObj->setTimeLimitOwner($this->cdata);
				break;
			case "TimeLimitUnlimited":
				switch (strtolower($this->cdata))
				{
					case "true":
					case "1":
						$this->userObj->setTimeLimitUnlimited(1);
						break;
					case "false":
					case "0":
						$this->userObj->setTimeLimitUnlimited(0);
						break;
					default:
						$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"),"TimeLimitUnlimited",$this->cdata));
						break;
				}
				break;
			case "TimeLimitFrom":
				// Accept datetime or Unix timestamp
				if (strtotime($this->cdata) === false && ! is_numeric($this->cdata))
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"),"TimeLimitFrom",$this->cdata));
				}
				$this->userObj->setTimeLimitFrom($this->cdata);
				break;
			case "TimeLimitUntil":
				// Accept datetime or Unix timestamp
				if (strtotime($this->cdata) === false && ! is_numeric($this->cdata))
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"),"TimeLimitUntil",$this->cdata));
				}
				$this->userObj->setTimeLimitUntil($this->cdata);
				break;
			case "TimeLimitMessage":
				switch (strtolower($this->cdata))
				{
					case "1":
						$this->userObj->setTimeLimitMessage(1);
						break;
					case "0":
						$this->userObj->setTimeLimitMessage(0);
						break;
					default:
						$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"),"TimeLimitMessage",$this->cdata));
						break;
				}
				break;
			case "ApproveDate":
				// Accept datetime or Unix timestamp
				if (strtotime($this->cdata) === false && ! is_numeric($this->cdata) && !$this->cdata == "0000-00-00 00:00:00")
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"),"ApproveDate",$this->cdata));
				}
				break;
			case "AgreeDate":
				// Accept datetime or Unix timestamp
				if (strtotime($this->cdata) === false && ! is_numeric($this->cdata) && !$this->cdata == "0000-00-00 00:00:00")
				{
					$this->logFailure($this->userObj->getLogin(), sprintf($lng->txt("usrimport_xml_element_content_illegal"),"AgreeDate",$this->cdata));
				}
				break;
			case "Pref":				
				if ($this->currentPrefKey != null)
					$this->verifyPref($this->currentPrefKey, $this->cdata);
				$this->currentPrefKey == null;
		}
	}

	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser, $a_data)
	{
		// DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
		// TODO: Mit Alex klären, ob das noch benötigt wird $a_data = preg_replace("/\n/","",$a_data);
		// TODO: Mit Alex klären, ob das noch benötigt wird $a_data = preg_replace("/\t+/","",$a_data);
		if($a_data != "\n") $a_data = preg_replace("/\t+/"," ",$a_data);

		if(strlen($a_data) > 0)
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
	* get count of User elements
	*/
	function getUserCount()
	{
		return $this->userCount;
	}

	/**
     * Writes a warning log message to the protocol.
	 *
	* @param	string		login
	* @param	string		message
	 */
	function logWarning($aLogin, $aMessage)
	{
		if (! array_key_exists($aLogin, $this->protocol))
		{
			$this->protocol[$aLogin] = array();
		}
		if ($aMessage)
		{
			$this->protocol[$aLogin][] = $aMessage;
		}
		if ($this->error_level == IL_IMPORT_SUCCESS)
		{
			$this->error_level = IL_IMPORT_WARNING;
		}
	}
	/**
     * Writes a failure log message to the protocol.
	 *
	* @param	string		login
	* @param	string		message
	 */
	function logFailure($aLogin, $aMessage)
	{
		if (! array_key_exists($aLogin, $this->protocol))
		{
			$this->protocol[$aLogin] = array();
		}
		if ($aMessage)
		{
			$this->protocol[$aLogin][] = $aMessage;
		}
		$this->error_level = IL_IMPORT_FAILURE;
	}

	/**
     * Writes a success log message to the protocol.
	 *
	 * @param	string		login
	 * @param	string		userid
	 * @param   string      action
	 */
	function logSuccess($aLogin, $userid, $action)
	{
	    $this->user_mapping[$userid] = array("login" => $aLogin, "action" => $action, "message" => "successful");
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
	function getProtocolAsHTML($a_log_title)
	{
		global $DIC;

		$lng = $DIC['lng'];

		$block = new ilTemplate("tpl.usr_import_log_block.html", true, true, "Services/User");
		$block->setVariable("TXT_LOG_TITLE", $a_log_title);
		$block->setVariable("TXT_MESSAGE_ID", $lng->txt("login"));
		$block->setVariable("TXT_MESSAGE_TEXT", $lng->txt("message"));
		foreach ($this->getProtocol() as $login => $messages)
		{
			$block->setCurrentBlock("log_row");
			$reason = "";
			foreach ($messages as $message)
			{
				if ($reason == "")
				{
					$reason = $message;
				}
				else
				{
					$reason = $reason."<br>".$message;
				}
			}
			$block->setVariable("MESSAGE_ID", $login);
			$block->setVariable("MESSAGE_TEXT", $reason);
			$block->parseCurrentBlock();
		}
		return $block->get();
	}

	/**
     * Returns true, if the import was successful.
	 */
	function isSuccess()
	{
		return $this->error_level == IL_IMPORT_SUCCESS;
	}

	/**
     * Returns the error level.
	 * @return IL_IMPORT_SUCCESS | IL_IMPORT_WARNING | IL_IMPORT_FAILURE
	 */
	function getErrorLevel()
	{
		return $this->error_level;
	}

	/**
	 * returns a map user_id <=> login
	 *
	 * @return assoc array, with user_id as key and login as value
	 */
	function getUserMapping() {
	    return $this->user_mapping;
	}

	/**
	* send account mail
	*/
	function sendAccountMail()
	{
		if($_POST["send_mail"] != "" ||
			($this->isSendMail() && $this->userObj->getEmail() != ""))
		{
			$this->acc_mail->setUser($this->userObj);
			$this->acc_mail->send();
		}
	}

	/**
	 * write access to property send mail
	 *
	 * @param mixed $value
	 */
	function setSendMail ($value) {
	    $this->send_mail = $value ? true: false;
	}

	/**
	 * read access to property send mail
	 *
	 * @return boolean
	 */
	function isSendMail () {
	    return $this->send_mail;
	}

	/**
	 * write access to user mapping mode
	 *
	 * @param int $value must be one of IL_USER_MAPPING_ID or IL_USER_MAPPING_LOGIN, die otherwise
	 */
	function setUserMappingMode($value)
	{
	    if ($value == IL_USER_MAPPING_ID || $value == IL_USER_MAPPING_LOGIN)
	       $this->mapping_mode = $value;
	    else die ("wrong argument using methode setUserMappingMethod in ".__FILE__);
	}

	/**
	 * read access to user mapping mode
	 *
	 * @return int one of IL_USER_MAPPING_ID or IL_USER_MAPPING_LOGIN
	 */
	function getUserMappingMode()
	{
	    return $this->mapping_mode;
	}

	/**
	 * read required fields
	 *
	 * @access private
	 *
	 */
	private function readRequiredFields()
	{
		global $DIC;

		$ilSetting = $DIC['ilSetting'];

	 	if(is_array($this->required_fields))
	 	{
	 		return $this->required_fields;
	 	}
	 	foreach($ilSetting->getAll() as $field => $value)
	 	{
	 		if(substr($field,0,8) == 'require_' and $value == 1)
	 		{
	 			$value = substr($field,8);
	 			$this->required_fields[$value] = $value;
	 		}
	 	}
	 	return $this->required_fields ? $this->required_fields : array();
	}

	/**
	 * Check if profile is incomplete
	 * Will set the usr_data field profile_incomplete if any required field is missing
	 *
	 *
	 * @access private
	 *
	 */
	private function checkProfileIncomplete($user_obj)
	{
		include_once "Services/User/classes/class.ilUserProfile.php";
		return ilUserProfile::isProfileIncomplete($user_obj);
	}
	
	/**
	*	determine if a field $fieldname is to a required field (global setting)
	*
	* @param	$fieldname	string value of fieldname, e.g. gender
	* @return true, if field of required fields contains fieldname as key, false otherwise.
	**/
	protected function isFieldRequired ($fieldname) 
	{
		$requiredFields = $this->readRequiredFields();
		$fieldname = strtolower(trim($fieldname));
		return array_key_exists($fieldname, $requiredFields);
	}
	
	private function verifyPref ($key, $value) {
		switch ($key) {
		    case 'mail_linebreak':
			case 'hits_per_page': 
				if (!is_numeric($value) || $value < 0)
					$this->logFailure("---", "Wrong value '$value': Positiv numeric value expected for preference $key.");
				break;			
			case 'language': 				
			case 'skin': 
			case 'style': 
			case 'ilPageEditor_HTMLMode': 
			case 'ilPageEditor_JavaScript': 
			case 'ilPageEditor_MediaMode':
			case 'tst_javascript': 
			case 'tst_lastquestiontype': 
			case 'tst_multiline_answers':
			case 'tst_use_previous_answers':
			case 'graphicalAnswerSetting': 
			case 'priv_feed_pass': 
				$this->logFailure("---", "Preference $key is not supported.");				
				break;				
			case 'public_city': 
			case 'public_country':
			case 'public_department':
			case 'public_email':
			case 'public_second_email':
			case 'public_fax':
			case 'public_hobby':
			case 'public_institution':
			case 'public_matriculation':
			case 'public_phone':
			case 'public_phone_home':
			case 'public_phone_mobile':
			case 'public_phone_office':
			case 'public_street':
			case 'public_upload':
			case 'public_zip':		
			case 'public_interests_general':
			case 'public_interests_help_offered':
			case 'public_interests_help_looking':
			case 'send_info_mails':
			case 'hide_own_online_status':
				if (!in_array($value, array('y', 'n')))
					$this->logFailure("---", "Wrong value '$value': Value 'y' or 'n' expected for preference $key.");				
				break;
			case 'bs_allow_to_contact_me':
				if(!in_array($value, array('y', 'n')))
				{
					$this->logFailure("---", "Wrong value '$value': Value 'y' or 'n' expected for preference $key.");
				}
				break;
			case 'chat_osc_accept_msg':
				if(!in_array($value, array('y', 'n')))
				{
					$this->logFailure("---", "Wrong value '$value': Value 'y' or 'n' expected for preference $key.");
				}
				break;
			case 'public_profile':
				if (!in_array($value, array('y', 'n', 'g')))
					$this->logFailure("---", "Wrong value '$value': Value 'y', 'g' or 'n' expected for preference $key.");				
				break;
			case 'show_users_online':
				if (!in_array($value, array('y', 'n', 'associated')))
				 	$this->logFailure("---", "Wrong value '$value': Value 'y' or 'n' or 'associated' expected for preference $key.");
				break;
			case 'mail_incoming_type':
			    if (!in_array((int) $value, array("0","1","2")))
			        $this->logFailure("---", "Wrong value '$value': Value \"0\" (LOCAL),\"1\" (EMAIL) or \"2\" (BOTH) expected for preference $key.");
				break;
			case 'weekstart':
			    if (!in_array($value, array ("0","1")))
			        $this->logFailure("---", "Wrong value '$value': Value \"0\" (Sunday) or \"1\" (Monday) expected for preference $key.");
				break;
				
			case 'mail_signature':
			    break;
			case 'user_tz': 
				include_once('Services/Calendar/classes/class.ilTimeZone.php');
				try {
					$tz = ilTimeZone::_getInstance($value);
					return true;
				} catch (ilTimeZoneException $tze) {
					$this->logFailure("---", "Wrong value '$value': Invalid timezone $value detected for preference $key.");					
				}
				break;
			default:
				if (!ilUserXMLWriter::isPrefExportable($key))
			    	$this->logFailure("---", "Preference $key is not supported.");				
				break;	
		}
	}
	
	private function updateMailPreferences ($usr_id) {
	    if (array_key_exists("mail_incoming_type", $this->prefs) || 
	        array_key_exists("mail_signature", $this->prefs) ||
	        array_key_exists("mail_linebreak", $this->prefs)
	        )
	    {
	        include_once("Services/Mail/classes/class.ilMailOptions.php"); 
	        $mailOptions = new ilMailOptions($usr_id);

			$mailOptions->setLinebreak(array_key_exists("mail_linebreak", $this->prefs) ? $this->prefs["mail_linebreak"] : $mailOptions->getLinebreak());
			$mailOptions->setSignature(array_key_exists("mail_signature", $this->prefs) ? $this->prefs["mail_signature"] : $mailOptions->getSignature());
			$mailOptions->setIncomingType(array_key_exists("mail_incoming_type", $this->prefs) ? $this->prefs["mail_incoming_type"] : $mailOptions->getIncomingType());
			$mailOptions->updateOptions();
	    }
	}

}
?>
