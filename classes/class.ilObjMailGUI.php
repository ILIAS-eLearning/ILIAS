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
* Class ilObjLanguageGUI
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @extends ilObject
* @package ilias-core
*/

require_once "class.ilObjectGUI.php";

class ilObjMailGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjMailGUI($a_data,$a_id,$a_call_by_reference)
	{
		$this->type = "mail";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference);
	}

	function viewObject()
	{
#		parent::editObject();
		
		$this->lng->loadLanguageModule("mail");

		$this->tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.mail_basicdata.html");
		$this->tpl->setCurrentBlock("systemsettings");

		$settings = $this->ilias->getAllSettings();
		if (isset($_POST["save_settings"]))  // formular sent
		{
			//init checking var
			$form_valid = true;

			if (!$form_valid)	//required fields not satisfied. Set formular to already fill in values
			{
				// mail server
				$settings["mail_server"] = $_POST["mail_server"];
				$settings["mail_port"] = $_POST["mail_port"];

				// internal mail
#				$settings["mail_intern_enable"] = $_POST["mail_intern_enable"];
				$settings["mail_maxsize_mail"] = $_POST["mail_maxsize_mail"];
				$settings["mail_maxsize_attach"] = $_POST["mail_maxsize_attach"];
				$settings["mail_maxsize_box"] = $_POST["mail_maxsize_box"];
				$settings["mail_maxtime_mail"] = $_POST["mail_maxtime_mail"];
				$settings["mail_maxtime_attach"] = $_POST["mail_maxtime_attach"];
			}
			else // all required fields ok
			{

		////////////////////////////////////////////////////////////
		// write new settings


				// mail server
				$this->ilias->setSetting("mail_server",$_POST["mail_server"]);
				$this->ilias->setSetting("mail_port",$_POST["mail_port"]);

				// internal mail
#				$this->ilias->setSetting("mail_intern_enable",$_POST["mail_intern_enable"]);
				$this->ilias->setSetting("mail_maxsize_mail",$_POST["mail_maxsize_mail"]);
				$this->ilias->setSetting("mail_maxsize_attach",$_POST["mail_maxsize_attach"]);
				$this->ilias->setSetting("mail_maxsize_box",$_POST["mail_maxsize_box"]);
				$this->ilias->setSetting("mail_maxtime_mail",$_POST["mail_maxtime_mail"]);
				$this->ilias->setSetting("mail_maxtime_attach",$_POST["mail_maxtime_attach"]);

				// write ini settings
				$this->ilias->ini->write();

				$settings = $this->ilias->getAllSettings();

				// feedback
				sendInfo($this->lng->txt("saved_successfully"));
			}
		}

		////////////////////////////////////////////////////////////
		// setting language vars


		// mail server
		$this->tpl->setVariable("TXT_MAIL_SMTP", $this->lng->txt("mail")." (".$this->lng->txt("smtp").")");
		$this->tpl->setVariable("TXT_MAIL_SERVER", $this->lng->txt("server"));
		$this->tpl->setVariable("TXT_MAIL_PORT", $this->lng->txt("port"));

		// internal mail
		$this->tpl->setVariable("TXT_MAIL_INTERN", $this->lng->txt("mail")." (".$this->lng->txt("internal_system").")");
#		$this->tpl->setVariable("TXT_MAIL_INTERN_ENABLE", $this->lng->txt("mail_intern_enable"));
		$this->tpl->setVariable("TXT_MAIL_MAXSIZE_MAIL", $this->lng->txt("mail_maxsize_mail"));
		$this->tpl->setVariable("TXT_MAIL_MAXSIZE_ATTACH", $this->lng->txt("mail_maxsize_attach"));
		$this->tpl->setVariable("TXT_MAIL_MAXSIZE_BOX", $this->lng->txt("mail_maxsize_box"));
		$this->tpl->setVariable("TXT_MAIL_MAXTIME_MAIL", $this->lng->txt("mail_maxtime_mail"));
		$this->tpl->setVariable("TXT_MAIL_MAXTIME_ATTACH", $this->lng->txt("mail_maxtime_attach"));
		$this->tpl->setVariable("TXT_MAIL_SAVE", $this->lng->txt("save"));


		///////////////////////////////////////////////////////////
		// display formula data


		// mail server
		$this->tpl->setVariable("MAIL_SERVER",$settings["mail_server"]);
		$this->tpl->setVariable("MAIL_PORT",$settings["mail_port"]);

		// internal mail
#		if ($settings["mail_intern_enable"] == "y")
#		{
#			$this->tpl->setVariable("MAIL_INTERN_ENABLE","checked=\"checked\"");
#		}

		$this->tpl->setVariable("MAIL_MAXSIZE_MAIL", $settings["mail_maxsize_mail"]);
		$this->tpl->setVariable("MAIL_MAXSIZE_ATTACH", $settings["mail_maxsize_attach"]);
		$this->tpl->setVariable("MAIL_MAXSIZE_BOX", $settings["mail_maxsize_box"]);
		$this->tpl->setVariable("MAIL_MAXTIME_MAIL", $settings["mail_maxtime_mail"]);
		$this->tpl->setVariable("MAIL_MAXTIME_ATTACH", $settings["mail_maxtime_attach"]);

	$this->tpl->parseCurrentBlock();
	}
		

} // END class.LanguageObjectOut
?>
