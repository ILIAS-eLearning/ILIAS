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
* Class ilObjLdapGUI
* for admin panel
*
* @author Sascha Hofmann <shofmann@databay.de>
* $Id$
* 
* @extends ilObjectGUI
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjLdapGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjLdapGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "ldap";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	function viewObject()
	{
		$this->lng->loadLanguageModule("ldap");

		$this->tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.ldap_basicdata.html");
		$this->tpl->setCurrentBlock("systemsettings");

		$settings = $this->ilias->getAllSettings();

		if (isset($_POST["save_settings"]))  // formular sent
		{
			//init checking var
			$form_valid = true;
			
			// do checks here!!!!

			if (!$form_valid)	//required fields not satisfied. Set formular to already fill in values
			{
				// ldap
				$settings["ldap_enable"] = $_POST["ldap_enable"];
				$settings["ldap_server"] = $_POST["ldap_server"];
				$settings["ldap_port"] = $_POST["ldap_port"];
				$settings["ldap_basedn"] = $_POST["ldap_basedn"];
			}
			else // all required fields ok
			{

		////////////////////////////////////////////////////////////
		// write new settings

				// ldap
				$this->ilias->setSetting("ldap_enable",$_POST["ldap_enable"]);
				$this->ilias->setSetting("ldap_server",$_POST["ldap_server"]);
				$this->ilias->setSetting("ldap_port",$_POST["ldap_port"]);
				$this->ilias->setSetting("ldap_basedn",$_POST["ldap_basedn"]);

				$settings = $this->ilias->getAllSettings();

				// feedback
				sendInfo($this->lng->txt("saved_successfully"));
			}
		}

		////////////////////////////////////////////////////////////
		// setting language vars

		// ldap
		$this->tpl->setVariable("TXT_LDAP", $this->lng->txt("ldap"));
		$this->tpl->setVariable("TXT_LDAP_ENABLE", $this->lng->txt("enable"));
		$this->tpl->setVariable("TXT_LDAP_SERVER", $this->lng->txt("server"));
		$this->tpl->setVariable("TXT_LDAP_PORT", $this->lng->txt("port"));
		$this->tpl->setVariable("TXT_LDAP_BASEDN", $this->lng->txt("basedn"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

		///////////////////////////////////////////////////////////
		// display formula data

		// ldap
		if ($settings["ldap_enable"])
		{
			$this->tpl->setVariable("LDAP_ENABLE","checked=\"checked\"");
		}

		$this->tpl->setVariable("LDAP_SERVER",$settings["ldap_server"]);
		$this->tpl->setVariable("LDAP_PORT",$settings["ldap_port"]);
		$this->tpl->setVariable("LDAP_BASEDN",$settings["ldap_basedn"]);

		$this->tpl->parseCurrentBlock();
	}
} // END class.ilObjLdapGUI
?>
