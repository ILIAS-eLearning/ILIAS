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
* XML writer class
*
* Class to simplify manual writing of xml documents.
* It only supports writing xml sequentially, because the xml document
* is saved in a string with no additional structure information.
* The author is responsible for well-formedness and validity
* of the xml document.
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id: class.ilObjectXMLWriter.php,v 1.3 2005/11/04 12:50:24 smeyer Exp $
*/

include_once "./classes/class.ilXmlWriter.php";
include_once "./classes/class.ilObjUserFolder.php";

class ilSoapUserObjectXMLWriter extends ilXmlWriter
{
	var $ilias;
	var $xml;
	var $users;
	var $user_id = 0;
	var $attachRoles = false;

	/**
	* constructor
	* @param	string	xml version
	* @param	string	output encoding
	* @param	string	input encoding
	* @access	public
	*/
	function ilSoapUserObjectXMLWriter()
	{
		global $ilias,$ilUser;

		parent::ilXmlWriter();

		$this->ilias =& $ilias;
		$this->user_id = $ilUser->getId();
		$this->attachRoles = false;
	}

	function setAttachRoles ($value)
	{
		$this->attachRoles = $value == 1? true : false;
	}

	function setObjects(&  $users)
	{
		$this->users = & $users;
	}


	function start()
	{
		if (!is_array($this->users))
			return false;

		$this->__buildHeader();

		foreach ($this->users as $user)
		{

			$this->__handleUser ($user);

		}

		$this->__buildFooter();

		return true;
	}

	function getXML()
	{
		return $this->xmlDumpMem(FALSE);
	}


	function __buildHeader()
	{
		$this->xmlSetDtdDef("<!DOCTYPE Users PUBLIC \"-//ILIAS//DTD UserImport//EN\" \"".ILIAS_HTTP_PATH."/xml/ilias_user_3_8.dtd\">");
		$this->xmlSetGenCmt("User of ilias system");
		$this->xmlHeader();

		$this->xmlStartTag('Users');

		return true;
	}

	function __buildFooter()
	{
		$this->xmlEndTag('Users');
	}

	function __handleUser ($row)
	{
		global $ilDB;

		if (strlen($row["language"]) == 0) $row["language"] = "en";

		$attrs = array ('Id' => $row["usr_id"],'Language' => $row["language"], 'Action' => "Update");

		$this->xmlStartTag("User", $attrs);

		$this->xmlElement("Login", null, $row["login"]);

		if ($this->attachRoles == TRUE)
		{
			include_once './classes/class.ilObjRole.php';

			$query = sprintf("SELECT object_data.title, object_data.description,  rbac_fa.*
			                  FROM object_data, rbac_ua, rbac_fa WHERE rbac_ua.usr_id = %s AND rbac_ua.rol_id = rbac_fa.rol_id AND object_data.obj_id = rbac_fa.rol_id",
					$ilDB->quote($row["usr_id"])
			);
			$rbacresult = $ilDB->query($query);

			while ($rbacrow = $rbacresult->fetchRow(DB_FETCHMODE_ASSOC))
			{
					if ($rbacrow["assign"] != "y")
						continue;

					$type = "";

					if ($rbacrow["parent"] == ROLE_FOLDER_ID)
					{
						$type = "Global";
					}
					else
					{
						$type = "Local";
					}
					if (strlen($type))
					{
						$this->xmlElement("Role",
							array ("Id" => $rbacrow["rol_id"], "Type" => $type),
							$rbacrow["title"]);
					}

			}
		}

		 /**
		  * never export passwords!
		  */

		$this->__addElement ("Firstname", $row["firstname"]);
		$this->__addElement ("Lastname", $row["lastname"]);
		$this->__addElement ("Title", $row["title"]);
		$this->__addElement ("Gender", $row["gender"]);
		$this->__addElement ("Email", $row["email"]);
		$this->__addElement ("Institution", $row["institution"]);
		$this->__addElement ("Street", $row["street"]);
		$this->__addElement ("City", $row["city"]);
		$this->__addElement ("PostalCode", $row["zipcode"], null, "zipcode");
		$this->__addElement ("Country", $row["country"]);
		$this->__addElement ("PhoneOffice", $row["phone_office"], null, "phone_office");
		$this->__addElement ("PhoneHome", $row["phone_home"], null, "phone_home");
		$this->__addElement ("PhoneMobile", $row["phone_mobile"],  null, "phone_mobile");
		$this->__addElement ("Fax", $row["fax"]);
		$this->__addElement ("Department", $row["department"]);
		$this->__addElement ("Comment", $row["referral_comment"], null, "referral_comment");
		$this->__addElement ("Matriculation", $row["matriculation"]);
		$this->__addElement ("Active", $row["active"] ? "true":"false" );
		$this->__addElement ("ClientIP", $row["client_ip"], null, "client_ip");
		$this->__addElement ("TimeLimitOwner", $row["time_limit_owner"], null, "time_limit_owner");
		$this->__addElement ("TimeLimitUnlimited", $row["time_limit_unlimited"], null, "time_limit_unlimited");
		$this->__addElement ("TimeLimitFrom", $row["time_limit_from"], null, "time_limit_from");
		$this->__addElement ("TimeLimitUntil", $row["time_limit_until"], null, "time_limit_until");
		$this->__addElement ("TimeLimitMessage", $row["time_limit_message"], null, "time_limit_message");
		$this->__addElement ("ApproveDate", $row["approve_date"], null, "client_ip");
		$this->__addElement ("AgreeDate", $row["agree_date"], null, "agree_date");

		if ((int) $row["ilinc_id"] !=0) {
				$this->__addElement ("iLincID", $row["ilinc_id"], "ilinc_id");
				$this->__addElement ("iLincUser", $row["ilinc_user"], "ilinc_user");
				$this->__addElement ("iLincPasswd", $row["ilinc_passwd"], "ilinc_passwd");
		}

		$this->__addElement ("AuthMode", null, array ("type" => $row["auth_mode"]));

		$this->__addElement ("LastUpdate", $row["last_update"]);

		$this->xmlEndTag('User');
	}


	function __addElement ($tagname, $value, $attrs = null, $settingsname = null)
	{

	    $this->xmlElement ($tagname, $attrs, $value);

	}

}


?>
