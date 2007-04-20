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
* Class ilObjMailGUI
* for admin panel
*
* @author Stefan Meyer <smeyer@databay.de> 
* $Id$
* 
* @ilCtrl_Calls ilObjMailGUI: ilPermissionGUI
* 
* @extends ilObjectGUI
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
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference, false);
	}

	function viewObject()
	{
#		parent::editObject();
		
		$this->lng->loadLanguageModule("mail");

		$this->tpl->addBlockFile("SYSTEMSETTINGS", "systemsettings", "tpl.mail_basicdata.html", "Services/Mail");
		$this->tpl->setCurrentBlock("systemsettings");

		$settings = $this->ilias->getAllSettings();

		if (isset($_POST["save_settings"]))  // formular sent
		{
			//init checking var
			$form_valid = true;
			
			// put any checks here!!!

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
				$this->ilias->setSetting("mail_incoming_mail",$_POST["mail_incoming_mail"]);

#				$this->ilias->setSetting("mail_intern_enable",$_POST["mail_intern_enable"]);
				$this->ilias->setSetting("mail_maxsize_mail",$_POST["mail_maxsize_mail"]);
				$this->ilias->setSetting("mail_maxsize_attach",$_POST["mail_maxsize_attach"]);
				$this->ilias->setSetting("mail_maxsize_box",$_POST["mail_maxsize_box"]);
				$this->ilias->setSetting("mail_maxtime_mail",$_POST["mail_maxtime_mail"]);
				$this->ilias->setSetting("mail_maxtime_attach",$_POST["mail_maxtime_attach"]);
				$this->ilias->setSetting("pear_mail_enable",$_POST["pear_mail_enable"]);

				$settings = $this->ilias->getAllSettings();

				// feedback
				ilUtil::sendInfo($this->lng->txt("saved_successfully"));
			}
		}

		////////////////////////////////////////////////////////////
		// setting language vars

		// common
		$this->tpl->setVariable("TXT_DAYS",$this->lng->txt("days"));
		$this->tpl->setVariable("TXT_KB",$this->lng->txt("kb"));

		// mail server
		$this->tpl->setVariable("TXT_MAIL_SMTP", $this->lng->txt("mail")." (".$this->lng->txt("smtp").")");
		$this->tpl->setVariable("TXT_MAIL_SERVER", $this->lng->txt("server"));
		$this->tpl->setVariable("TXT_MAIL_PORT", $this->lng->txt("port"));

		// Pear Mail extension
		// Note: We use the include statement to determine whether PEAR MAIL is
		//      installed. We use the @ operator to prevent PHP from issuing a
		//      warning while we test for PEAR MAIL.
		$is_pear_mail_installed = @include 'Mail/RFC822.php';
		$this->tpl->setVariable("TXT_PEAR_MAIL", $this->lng->txt("mail_use_pear_mail"));
		if ($settings['pear_mail_enable'] && $is_pear_mail_installed) 
		{
			$this->tpl->setVariable("PEAR_MAIL_CHECKED", 'checked="checked"');
		}
		if ($is_pear_mail_installed)
		{
			$this->tpl->setVariable("TXT_PEAR_MAIL_INFO", 
				$this->lng->txt("mail_use_pear_mail_info")
			);
		}
		else
		{
			$this->tpl->setVariable("TXT_PEAR_MAIL_INFO", 
				$this->lng->txt("mail_use_pear_mail_info").'<br>'.
				$this->lng->txt("mail_pear_mail_needed")
			);
			$this->tpl->setVariable("PEAR_MAIL_DISABLED", 'disabled="disabled"');
		}

		// internal mail
		include_once "Services/Mail/classes/class.ilMailOptions.php";
		$this->tpl->setVariable("TXT_GENERAL_SETTINGS", $this->lng->txt("general_settings"));
		$this->tpl->setVariable("TXT_MAIL_INCOMING", $this->lng->txt("mail_incoming"));
		$types = array(
			array(
				"name" => $this->lng->txt("mail_incoming_local"),
				"value" => IL_MAIL_LOCAL
			),
			array(
				"name" => $this->lng->txt("mail_incoming_smtp"),
				"value" => IL_MAIL_EMAIL
			),
			array(
				"name" => $this->lng->txt("mail_incoming_both"),
				"value" => IL_MAIL_BOTH
			)
		);
		for ($i = 0; $i < count($types); $i++)
		{
			$this->tpl->setCurrentBlock("loop_incoming_mail");
			$this->tpl->setVariable("LOOP_INCOMING_MAIL_VALUE", $types[$i]["value"]);
			$this->tpl->setVariable("LOOP_INCOMING_MAIL_NAME", $types[$i]["name"]);
			if ($settings["mail_incoming_mail"] == $types[$i]["value"])
			{
				$this->tpl->setVariable("LOOP_INCOMING_MAIL_SELECTED", "selected");
			}
			$this->tpl->parseCurrentBlock("loop_incoming_mail");
		}

#		$this->tpl->setVariable("TXT_MAIL_INTERN", $this->lng->txt("mail")." (".$this->lng->txt("internal_system").")");
		$this->tpl->setVariable("TXT_MAIL_INTERN", $this->lng->txt("internal_system"));
#		$this->tpl->setVariable("TXT_MAIL_INTERN_ENABLE", $this->lng->txt("mail_intern_enable"));
		$this->tpl->setVariable("TXT_MAIL_MAXSIZE_MAIL", $this->lng->txt("mail_maxsize_mail"));
		$this->tpl->setVariable("TXT_MAIL_MAXSIZE_ATTACH", $this->lng->txt("mail_maxsize_attach"));
		$this->tpl->setVariable("TXT_MAIL_MAXSIZE_BOX", $this->lng->txt("mail_maxsize_box"));
		$this->tpl->setVariable("TXT_MAIL_MAXTIME_MAIL", $this->lng->txt("mail_maxtime_mail"));
		$this->tpl->setVariable("TXT_MAIL_MAXTIME_ATTACH", $this->lng->txt("mail_maxtime_attach"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));

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

	function importObject()
	{
		global $rbacsystem,$lng;

		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		#$this->getTemplateFile("import");
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.mail_import.html", "Services/Mail");

		// GET ALREADY CREATED UPLOADED XML FILE
		$this->__initFileObject();
		if($this->file_obj->findXMLFile())
		{
			$this->tpl->setVariable("TXT_IMPORTED_FILE",$lng->txt("checked_files"));
			$this->tpl->setVariable("XML_FILE",basename($this->file_obj->getXMLFile()));

			$this->tpl->setVariable("BTN_IMPORT",$this->lng->txt("import"));
		}

		$this->tpl->setVariable("FORMACTION",
			$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_IMPORT_MAIL",$this->lng->txt("table_mail_import"));
		$this->tpl->setVariable("TXT_IMPORT_FILE",$this->lng->txt("mail_import_file"));
		$this->tpl->setVariable("BTN_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("BTN_UPLOAD",$this->lng->txt("upload"));

		return true;
	}

	function performImportObject()
	{
		global $rbacsystem,$lng;

		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		$this->__initFileObject();
		$this->file_obj->findXMLFile();
		$this->__initParserObject($this->file_obj->getXMLFile(),"import");
		$this->parser_obj->startParsing();
		$number = $this->parser_obj->getCountImported();
		ilUtil::sendInfo($lng->txt("import_finished")." ".$number,true);
		
		$this->ctrl->redirect($this, "import");
	}
	
	

	function uploadObject()
	{
		global $rbacsystem,$lng;

		if (!$rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->WARNING);
		}
		
		$this->__initFileObject();
		if(!$this->file_obj->storeUploadedFile($_FILES["importFile"]))	// STEP 1 save file in ...import/mail
		{
			$this->message = $lng->txt("import_file_not_valid"); 
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->unzip())
		{
			$this->message = $lng->txt("cannot_unzip_file");					// STEP 2 unzip uplaoded file
			$this->file_obj->unlinkLast();
		}
		else if(!$this->file_obj->findXMLFile())						// STEP 3 getXMLFile
		{
			$this->message = $lng->txt("cannot_find_xml");
			$this->file_obj->unlinkLast();
		}
		else if(!$this->__initParserObject($this->file_obj->getXMLFile(),"check"))
		{
			$this->message = $lng->txt("error_parser");				// STEP 4 init sax parser
		}
		else if(!$this->parser_obj->startParsing())
		{
			$this->message = $lng->txt("users_not_imported").":<br/>"; // STEP 5 start parsing
			$this->message .= $this->parser_obj->getNotAssignableUsers();
		}
		// FINALLY CHECK ERROR
		if(!$this->message)
		{
			$this->message = $lng->txt("uploaded_and_checked");
		}
		ilUtil::sendInfo($this->message,true);
		
		$this->ctrl->redirect($this, "import");
	}

	// PRIVATE
	function __initFileObject()
	{
		include_once "./classes/class.ilFileDataImportMail.php";

		$this->file_obj =& new ilFileDataImportMail();

		return true;
	}
	function __initParserObject($a_xml,$a_mode)
	{
		include_once "Services/Mail/classes/class.ilMailImportParser.php";

		if(!$a_xml)
		{
			return false;
		}

		$this->parser_obj =& new ilMailImportParser($a_xml,$a_mode);
		
		return true;
	}
	
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				include_once("./classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd)
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();

				break;
		}
		return true;
	}
	
	function getAdminTabs(&$tabs_gui)
	{
		$this->getTabs($tabs_gui);
	}
	
	/**
	* get tabs
	* @access	public
	* @param	object	tabs gui object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "view"), array("view",""), "", "");
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}
		
		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("import",
				$this->ctrl->getLinkTarget($this, "import"), "import", "", "");
		}
	}
} // END class.ilObjMailGUI
?>
