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
 * Setup GUI class
 *
 * class to setup ILIAS first and maintain the ini-settings and the database
 *
 * @author   Sascha Hofmann <shofmann@databay.de>
 * @version  $Id$
 */

require_once "class.ilSetup.php";

class ilSetupGUI extends ilSetup
{
	var $tpl;       // template object
	var $lng;       // language objet
	var $log;       // log object
	
	var $btn_prev_on = false;   // toggle previous button on/off
	var $btn_prev_cmd;          // command processed when previous button was clicked
	var $btn_prev_lng;          // previous button label

	var $btn_next_on = false;   // toggle NEXT button on/off
	var $btn_next_cmd;          // command processed when next button was clicked
	var $btn_next_lng;          // next button label

	var $revision;              // cvs revision of this script
	var $version;               // cvs version of this script
	var $lang;                  // current language (lang_key)

	var $cmd;                       // command variable
	var $display_mode = "view";     // view mode (setup or details)

	/**
	 * Constructor
	 *  
	 */
	function ilSetupGUI()
	{
		global $tpl, $lng;

		$this->tpl =& $tpl;
		$this->lng =& $lng;

		// CVS - REVISION - DO NOT MODIFY
		$this->revision = '$Revision$';
		$this->version = "2 ".substr(substr($this->revision,1),0,-2);
		$this->lang = $this->lng->lang_key;

		// init setup
		$this->ilSetup($_SESSION["auth"],$_SESSION["access_mode"]);

		// init client object if exists
		$client_id = ($_GET["client_id"]) ? $_GET["client_id"] : $_SESSION["ClientId"];

		// for security
		if (!$this->isAdmin() and $client_id != $_SESSION["ClientId"])
		{
			$client_id = $_SESSION["ClientId"];
		}

		$this->ini_client_exists = $this->newClient($client_id);
		$this->client->status = $this->getStatus();

		// determine command
		if (($this->cmd = $_GET["cmd"]) == "gateway")
		{
			// surpress warning if POST is not set
			@$this->cmd = key($_POST["cmd"]);
		}

		// determine display mode here
		// TODO: depending on previous setting (session)
		// OR switch to 'setup'-mode if someone logs in as client and client's setup wasn't finished (-> entry in settings table does not exist)
		if ($this->isAuthenticated() and !$this->client->status["finish"]["status"] and $this->cmd != "clientlist" and $this->cmd != "")
		{
			$this->setDisplayMode("setup");
		}
		else
		{
			$this->setDisplayMode($_SESSION["display_mode"]);
		}

		// output starts here

		// display header
		$this->displayHeader();

		// main cmd handling
		if (!$this->isAuthenticated() or !$this->isInstalled())
		{
			// check for first time installation or migrate an old one first
			if (!$this->isInstalled() or !($this->ini->readVariable("clients","path")))
			{
				$this->cmdInstall();
			}
			else
			{
				$this->displayLogin();
			}
		}
		else
		{
			if ($this->isAdmin())
			{
				$this->cmdAdmin();
			}
			else
			{
				$this->cmdClient();
			}
		}

		if (DEBUG)
		{
			echo "cmd: ".$this->cmd." | access: ".$this->access_mode." | display: ".$this->display_mode;
			var_dump($this->client->status);
		}

		// display footer
		$this->displayFooter();

		// end output

	}  // end constructor

	// cmd subsets

	/**
	 * process valid commands for pre-installation status
	 */
	function cmdInstall()
	{
		switch ($this->cmd)
		{
			case NULL:
			case "preliminaries":
				$this->checkPreliminaries();
				$this->displayPreliminaries();
				break;

			case "install":
				$this->displayMasterSetup();
				break;
				
			case "determineToolsPathInstall":
				$this->determineToolsPathInstall();
				break;

			default:
				$this->displayError($this->lng->txt("unknown_command"));
				break;
		}
	}

	/**
	 * process valid commands for admins
	 */
	function cmdAdmin()
	{
		switch ($this->cmd)
		{
			case NULL:
			case "clientlist":
				$this->setDisplayMode("view");
				$this->displayClientList();
				break;

			case "changepassword":
				$this->setDisplayMode("view");
				$this->changeMasterPassword();
				break;

			case "mastersettings":
				$this->setDisplayMode("view");
				$this->changeMasterSettings();
				break;
				
			case "determineToolsPath":
				$this->setDisplayMode("view");
				$this->determineToolsPath();
				break;

			case "changedefault":
				$this->changeDefaultClient();
				break;  

			case "newclient":
				$this->cmd = "ini";
				$this->setDisplayMode("setup");
				$this->ini_client_exists = $this->newClient();
				$this->displayIni();
				break;  

			case "startup":
				$this->setDisplayMode("setup");
				$this->ini_client_exists = $this->newClient();
				$this->displayStartup();
				break;

			case "delete":
				$this->setDisplayMode("view");
				$this->displayDeleteConfirmation();
				break;

			case "togglelist":
				$this->setDisplayMode("view");
				$this->toggleClientList();
				break;

			default:
				$this->cmdClient();
				break;
		}
	}
	
	/**
	 * process valid commands for all clients
	 */
	function cmdClient()
	{
		switch ($this->cmd)
		{
			case NULL:
			case "view":
				if ($this->client->db_installed)
				{
					$this->setDisplayMode("view"); 
					$this->displayClientOverview();
				}
				else
				{
					$this->cmd = "db";
					$this->displayDatabase();
				}
				break;
				
			case "ini":
				// only allow access to ini if db does not exist yet
				if ($this->client->db_installed)
				{
					$this->cmd = "db";
					$this->displayDatabase();
				}
				else
				{
					$this->displayIni();
				}
				break;
				
			case "db":
				$this->displayDatabase();
				break;
	
			case "lang":
				if (!isset($_GET["lang"]) and !$this->client->status["finish"]["status"] and $_GET["cmd"] == "lang" and $this->error === true)
				{
					$this->jumpToFirstUnfinishedSetupStep();
				}
				else
				{
					$this->displayLanguages();
				}
				break;

			case "contact":
				if (!isset($_GET["lang"]) and !$this->client->status["finish"]["status"] and $_GET["cmd"] == "contact")
				{
					$this->jumpToFirstUnfinishedSetupStep();
				}
				else
				{
					$this->displayContactData();
				}
				break;
	
			case "nic":
				if (!isset($_GET["lang"]) and !$this->client->status["finish"]["status"] and $_GET["cmd"] == "nic")
				{
					$this->jumpToFirstUnfinishedSetupStep();
				}
				else
				{
					$this->displayNIC();
				}
				break;
	
			case "finish":
				if (!isset($_GET["lang"]) and !$this->client->status["finish"]["status"] and $_GET["cmd"] == "finish")
				{
					$this->jumpToFirstUnfinishedSetupStep();
				}
				else
				{
					$this->displayFinishSetup();
				}
				break;

			case "changeaccess":
				$this->changeAccessMode($_GET["back"]);
				break;

			case "logout":
				$this->displayLogout();
				break;

			case "login":
				session_destroy();
				ilUtil::redirect(ILIAS_HTTP_PATH."/login.php?client_id=".$this->client->getId());
				break;

			case "login_new":
				if ($this->client->ini->readVariable("client","access") != "1")
				{
					$this->client->ini->setVariable("client","access","1");
					$this->client->ini->write();
				}

				session_destroy();
				ilUtil::redirect(ILIAS_HTTP_PATH."/login.php?client_id=".$this->client->getId());
				break;

			case "tools":
				$this->displayTools();
				break;
				
			case "reloadStructure":
				$this->reloadControlStructure();
				break;

			default:
				$this->displayError($this->lng->txt("unknown_command"));
				break;
		}
	}

	// end cmd subsets 

	/**
	 * display client overview panel 
	 */
	function displayClientOverview()
	{       
		$this->checkDisplayMode();
	
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.client_overview.html");

		if ($this->client->db_installed)
		{
			$settings = $this->client->getAllSettings();
		}
		
		$txt_no_database = $this->lng->txt("no_database");

		$access_status = ($this->client->status["access"]["status"]) ? "online" : "disabled";
		$access_button = ($this->client->status["access"]["status"]) ? "disable" : "enable";
		$access_link = "&nbsp;&nbsp;[<a href=\"setup.php?cmd=changeaccess&client_id=".$this->client->getId()."&back=view\">".$this->lng->txt($access_button)."</a>]";
		
		// basic data
		$this->tpl->setVariable("TXT_BASIC_DATA", $this->lng->txt("client_info"));
		$this->tpl->setVariable("TXT_INST_NAME", $this->lng->txt("inst_name"));
		$this->tpl->setVariable("TXT_INST_ID", $this->lng->txt("ilias_nic_id"));
		$this->tpl->setVariable("TXT_CLIENT_ID2", $this->lng->txt("client_id"));
		$this->tpl->setVariable("TXT_DB_VERSION", $this->lng->txt("db_version"));
		$this->tpl->setVariable("TXT_ACCESS_STATUS", $this->lng->txt("access_status"));
		
		$this->tpl->setVariable("TXT_SERVER_DATA", $this->lng->txt("server_info"));
		$this->tpl->setVariable("TXT_ILIAS_VERSION", $this->lng->txt("ilias_version"));
		$this->tpl->setVariable("TXT_HOSTNAME", $this->lng->txt("host"));
		$this->tpl->setVariable("TXT_IP_ADDRESS", $this->lng->txt("ip_address"));
		$this->tpl->setVariable("TXT_SERVER_PORT", $this->lng->txt("port"));
		$this->tpl->setVariable("TXT_SERVER_SOFTWARE", $this->lng->txt("server_software"));
		$this->tpl->setVariable("TXT_HTTP_PATH", $this->lng->txt("http_path"));
		$this->tpl->setVariable("TXT_ABSOLUTE_PATH", $this->lng->txt("absolute_path"));
		$this->tpl->setVariable("TXT_DEFAULT_LANGUAGE", $this->lng->txt("default_language"));
		$this->tpl->setVariable("TXT_FEEDBACK_RECIPIENT", $this->lng->txt("feedback_recipient"));
		$this->tpl->setVariable("TXT_ERROR_RECIPIENT", $this->lng->txt("error_recipient"));

		// paths
		$this->tpl->setVariable("TXT_SOFTWARE", $this->lng->txt("3rd_party_software"));
		$this->tpl->setVariable("TXT_CONVERT_PATH", $this->lng->txt("path_to_convert"));
		$this->tpl->setVariable("TXT_ZIP_PATH", $this->lng->txt("path_to_zip"));
		$this->tpl->setVariable("TXT_UNZIP_PATH", $this->lng->txt("path_to_unzip"));
		$this->tpl->setVariable("TXT_JAVA_PATH", $this->lng->txt("path_to_java"));
		$this->tpl->setVariable("TXT_HTMLDOC_PATH", $this->lng->txt("path_to_htmldoc"));
		$this->tpl->setVariable("TXT_LATEX_URL", $this->lng->txt("url_to_latex"));
		$this->tpl->setVariable("TXT_VIRUS_SCANNER", $this->lng->txt("virus_scanner"));
		$this->tpl->setVariable("TXT_SCAN_COMMAND", $this->lng->txt("scan_command"));
		$this->tpl->setVariable("TXT_CLEAN_COMMAND", $this->lng->txt("clean_command"));

		// display formula data

		// client data
		$this->tpl->setVariable("INST_ID",($this->client->db_installed) ? $settings["inst_id"] : $txt_no_database);
		$this->tpl->setVariable("CLIENT_ID2",$this->client->getId());
		$this->tpl->setVariable("INST_NAME",($this->client->getName()) ? $this->client->getName() : "&lt;".$this->lng->txt("no_client_name")."&gt;");
		$this->tpl->setVariable("INST_INFO",$this->client->getDescription());
		$this->tpl->setVariable("DB_VERSION",($this->client->db_installed) ? $settings["db_version"] : $txt_no_database);
		$this->tpl->setVariable("ACCESS_STATUS",$this->lng->txt($access_status).$access_link);

		// server data
		$this->tpl->setVariable("HTTP_PATH",ILIAS_HTTP_PATH);
		$this->tpl->setVariable("ABSOLUTE_PATH",ILIAS_ABSOLUTE_PATH);
		$this->tpl->setVariable("HOSTNAME", $_SERVER["SERVER_NAME"]);
		$this->tpl->setVariable("SERVER_PORT", $_SERVER["SERVER_PORT"]);
		$this->tpl->setVariable("SERVER_ADMIN", $_SERVER["SERVER_ADMIN"]);  // not used
		$this->tpl->setVariable("SERVER_SOFTWARE", $_SERVER["SERVER_SOFTWARE"]);
		$this->tpl->setVariable("IP_ADDRESS", $_SERVER["SERVER_ADDR"]);
		$this->tpl->setVariable("ILIAS_VERSION", ILIAS_VERSION);

		$this->tpl->setVariable("FEEDBACK_RECIPIENT",($this->client->db_installed) ? $settings["feedback_recipient"] : $txt_no_database);
		$this->tpl->setVariable("ERROR_RECIPIENT",($this->client->db_installed) ? $settings["error_recipient"] : $txt_no_database);

		// paths to tools
		$not_set = $this->lng->txt("path_not_set");
				
		$convert = $this->ini->readVariable("tools","convert");
		$zip = $this->ini->readVariable("tools","zip");
		$unzip = $this->ini->readVariable("tools","unzip");
		$java = $this->ini->readVariable("tools","java");
		$htmldoc = $this->ini->readVariable("tools","htmldoc");
		$latex = $this->ini->readVariable("tools", "latex");
		$vscan = $this->ini->readVariable("tools","vscantype");
		$scancomm = $this->ini->readVariable("tools","scancommand");
		$cleancomm = $this->ini->readVariable("tools","cleancommand");
		
		$this->tpl->setVariable("CONVERT_PATH",($convert) ? $convert : $not_set);
		$this->tpl->setVariable("ZIP_PATH",($zip) ? $zip : $not_set);
		$this->tpl->setVariable("UNZIP_PATH",($unzip) ? $unzip : $not_set);
		$this->tpl->setVariable("JAVA_PATH",($java) ? $java : $not_set);
		$this->tpl->setVariable("HTMLDOC_PATH",($htmldoc) ? $htmldoc : $not_set);
		$this->tpl->setVariable("LATEX_URL",($latex) ? $latex : $not_set);
		$this->tpl->setVariable("VAL_SCAN_COMMAND",($scancomm) ? $scancomm : $not_set);
		$this->tpl->setVariable("VAL_CLEAN_COMMAND",($cleancomm) ? $cleancomm : $not_set);
		$this->tpl->setVariable("VAL_VIRUS_SCANNER",($vscan) ? $vscan : $not_set);

		$this->tpl->parseCurrentBlock();

		$this->displayStatusPanel();
	}

	/**
	 * set display mode to 'view' or 'setup'
	 * 'setup' -> show status panel and (prev/next) navigation buttons 
	 * 'view' -> show overall status and tabs under title bar
	 * 
	 * @param    string      display mode
	 * @return   boolean     true if display mode was successfully set 
	 */
	function setDisplayMode($a_mode)
	{
		// security
		if ($a_mode != "view" and $a_mode != "setup")
		{
			return false;
		}

		$this->display_mode = $a_mode;
		$_SESSION["display_mode"] = $this->display_mode;
		
		return true;
	}
	
	/**
	 * display header with admin links and language flags
	 */
	function displayHeader()
	{
		$languages = $this->lng->getLanguages();

		$count = (int) round(count($languages) / 2);
		$num = 1;

		foreach ($languages as $lang_key)
		{
			if ($num === $count)
			{
				$this->tpl->touchBlock("lng_new_row");
			}

			$this->tpl->setCurrentBlock("languages");
			$this->tpl->setVariable("LINK_LANG", "./setup.php?cmd=".$this->cmd."&amp;lang=".$lang_key);
			$this->tpl->setVariable("LANG_NAME", $this->lng->txt("meta_l_".$lang_key));
			$this->tpl->setVariable("LANG_ICON", $lang_key);
			$this->tpl->setVariable("LANG_KEY", $lang_key);
			$this->tpl->setVariable("BORDER", 0);
			$this->tpl->setVariable("VSPACE", 0);
			$this->tpl->parseCurrentBlock();

			$num++;
		}

		if (count($languages) % 2)
		{
			$this->tpl->touchBlock("lng_empty_cell");
		}

		if ($this->cmd != "logout" and $this->isInstalled())
		{
			// add client link
			if ($this->isAdmin())
			{
				if ($this->display_mode == "view" or $this->cmd == "clientlist" or $this->cmd == "changepassword" or $this->cmd == "mastersettings")
				{
					$this->tpl->setCurrentBlock("add_client");
					$this->tpl->setVariable("TXT_ADD_CLIENT",ucfirst($this->lng->txt("new_client")));
					$this->tpl->parseCurrentBlock();
				}

				// client list link
				$this->tpl->setCurrentBlock("display_list");
				$this->tpl->setVariable("TXT_LIST",ucfirst($this->lng->txt("list_clients")));
				$this->tpl->parseCurrentBlock();

				// edit paths link
				$this->tpl->setCurrentBlock("edit_pathes");
				$this->tpl->setVariable("TXT_EDIT_PATHES",$this->lng->txt("basic_settings"));
				$this->tpl->parseCurrentBlock();

				// change password link
				$this->tpl->setCurrentBlock("change_password");
				$this->tpl->setVariable("TXT_CHANGE_PASSWORD",ucfirst($this->lng->txt("password")));
				$this->tpl->parseCurrentBlock();
			}

			// logout link
			if ($this->isAuthenticated())
			{
				$this->tpl->setCurrentBlock("logout");
				$this->tpl->setVariable("TXT_LOGOUT",$this->lng->txt("logout"));
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setVariable("VAL_CMD", $_GET["cmd"]);
		$this->tpl->setVariable("TXT_OK",$this->lng->txt("change"));
		$this->tpl->setVariable("TXT_CHOOSE_LANGUAGE",$this->lng->txt("choose_language"));
		$this->tpl->setVariable("PAGETITLE","Setup");
		//$this->tpl->setVariable("LOCATION_STYLESHEET","./templates/blueshadow.css");
		$this->tpl->setVariable("LOCATION_STYLESHEET","../templates/default/delos.css");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET","../templates/default/delos_cont.css");
		$this->tpl->setVariable("TXT_ILIAS_VERSION", "ILIAS ".ILIAS_VERSION);
		$this->tpl->setVariable("TXT_SETUP",$this->lng->txt("setup"));
		$this->tpl->setVariable("VERSION", $this->version);
		$this->tpl->setVariable("TXT_VERSION", $this->lng->txt("version"));
		$this->tpl->setVariable("LANG", $this->lang);
	}

	/**
	 * page output and set title
	 */
	function displayFooter()
	{
		// footer (not really)
		if ($this->cmd != "logout")
		{
			if ($this->ini_ilias_exists and $this->display_mode == "setup" and $this->client->getId() != "")
			{
				$this->tpl->setVariable("TXT_ACCESS_MODE","(".$this->lng->txt("client_id").": ".$this->client->getId().")");
			}
			elseif ($this->isAdmin())
			{
				$this->tpl->setVariable("TXT_ACCESS_MODE","(".$this->lng->txt("root_access").")");
			}
		
			$this->displayNavButtons();
		}
		
		$this->tpl->show();
	}

	/**
	 * display navigation buttons
	 * 
	 * @return   boolean     false if both buttons are deactivated
	 */
	function displayNavButtons()
	{
		if (!$this->btn_prev_on and !$this->btn_next_on)
		{
			return false;
		}
		
		$this->tpl->addBlockFile("NAVBUTTONS","navbuttons","tpl.navbuttons.html");

		$this->tpl->setVariable("FORMACTION_BUTTONS","setup.php?cmd=gateway");

		if ($this->btn_prev_on)
		{
			$this->tpl->setCurrentBlock("btn_back");
			$this->tpl->setVariable("TXT_PREV", $this->btn_prev_lng);   
			$this->tpl->setVariable("CMD_PREV", $this->btn_prev_cmd);   
			$this->tpl->parseCurrentBlock();
		}
		
		if ($this->btn_next_on)
		{
			$this->tpl->setCurrentBlock("btn_forward");
			$this->tpl->setVariable("TXT_NEXT", $this->btn_next_lng);
			$this->tpl->setVariable("CMD_NEXT", $this->btn_next_cmd);   
			$this->tpl->parseCurrentBlock();
		}
		
		return true;
	}

	/**
	 * set previous navigation button
	 * 
	 * @param    string      command to process on click
	 * @param    string      button label
	 */
	function SetButtonPrev($a_cmd = 0,$a_lng = 0)
	{
		$this->btn_prev_on = true;
		$this->btn_prev_cmd = ($a_cmd) ? $a_cmd : "gateway";
		$this->btn_prev_lng = ($a_lng) ? $this->lng->txt($a_lng) : "<<&nbsp;&nbsp;&nbsp;".$this->lng->txt("prev");
	}

	/**
	 * set next navigation button
	 * 
	 * @param    string      command to process on click
	 * @param    string      button label
	 */
	function SetButtonNext($a_cmd,$a_lng = 0)
	{
		$this->btn_next_on = true;
		$this->btn_next_cmd = ($a_cmd) ? $a_cmd : "gateway";
		$this->btn_next_lng = ($a_lng) ? $this->lng->txt($a_lng) : $this->lng->txt("next")."&nbsp;&nbsp;&nbsp;>>";
	}

	/**
	 * display preliminaries page
	 */
	function displayPreliminaries()
	{
		$OK = "<font color=\"green\"><strong>OK</strong></font>";
		$FAILED = "<strong><font color=\"red\">FAILED</font></strong>";
		
		$this->tpl->addBlockFile("CONTENT","content","tpl.preliminaries.html");
		
		$this->tpl->setVariable("TXT_SETUP_TITLE",$this->lng->txt("ilias_setup"));
		$this->tpl->setVariable("TXT_SETUP_WELCOME", $this->lng->txt("setup_welcome"));
		$this->tpl->setVariable("TXT_SETUP_INIFILE_DESC", $this->lng->txt("setup_inifile_desc"));
		$this->tpl->setVariable("TXT_SETUP_DATABASE_DESC", $this->lng->txt("setup_database_desc"));
		$this->tpl->setVariable("TXT_SETUP_LANGUAGES_DESC", $this->lng->txt("setup_languages_desc"));
		$this->tpl->setVariable("TXT_SETUP_PASSWORD_DESC", $this->lng->txt("setup_password_desc"));     
		$this->tpl->setVariable("TXT_SETUP_NIC_DESC", $this->lng->txt("setup_nic_desc"));   
	
		$server_os = php_uname();
		$server_web = $_SERVER["SERVER_SOFTWARE"];
		$environment = $this->lng->txt("env_using")." ".$server_os." <br/>".$this->lng->txt("with")." ".$server_web;
		
		if ((stristr($server_os,"linux") || stristr($server_os,"windows")) && stristr($server_web,"apache"))
		{
			$env_comment = $this->lng->txt("env_ok");       
		}
		else
		{
			$env_comment = "<font color=\"red\">".$this->lng->txt("env_warning")."</font>";
		}
			
		$this->tpl->setVariable("TXT_ENV_TITLE", $this->lng->txt("environment"));
		$this->tpl->setVariable("TXT_ENV_INTRO", $environment);
		$this->tpl->setVariable("TXT_ENV_COMMENT", $env_comment);   
		
		$this->tpl->setVariable("TXT_PRE_TITLE", $this->lng->txt("preliminaries"));
		$this->tpl->setVariable("TXT_PRE_INTRO", $this->lng->txt("pre_intro"));

		// display phpversion
		$this->tpl->setCurrentBlock("preliminary");
		$this->tpl->setVariable("TXT_PRE", $this->lng->txt("pre_php_version").": ".$arCheck["php"]["version"]);

		if ($this->preliminaries_result["php"]["status"] == true)
		{
			$this->tpl->setVariable("STATUS_PRE", $OK);
		}
		else
		{
			$this->tpl->setVariable("STATUS_PRE", $FAILED);
			$this->tpl->setVariable("COMMENT_PRE", $this->preliminaries_result["php"]["comment"]);
		}

		$this->tpl->parseCurrentBlock();
	
		// check if ilias3 folder is writable
		$this->tpl->setCurrentBlock("preliminary");
		$this->tpl->setVariable("TXT_PRE", $this->lng->txt("pre_folder_write"));

		if ($this->preliminaries_result["root"]["status"] == true)
		{
			$this->tpl->setVariable("STATUS_PRE", $OK);
		}
		else
		{
			$this->tpl->setVariable("STATUS_PRE", $FAILED);
			$this->tpl->setVariable("COMMENT_PRE", $this->preliminaries_result["root"]["comment"]);
		}
		$this->tpl->parseCurrentBlock();
		
		// check if ilias3 can create new folders
		$this->tpl->setCurrentBlock("preliminary");
		$this->tpl->setVariable("TXT_PRE", $this->lng->txt("pre_folder_create"));

		if ($this->preliminaries_result["create"]["status"] == true)
		{
			$this->tpl->setVariable("STATUS_PRE", $OK);
		}
		else
		{
			$this->tpl->setVariable("STATUS_PRE", $FAILED);
			$this->tpl->setVariable("COMMENT_PRE", $this->preliminaries_result["create"]["comment"]);
		}

		$this->tpl->parseCurrentBlock();

		// check cookies
		$this->tpl->setCurrentBlock("preliminary");
		$this->tpl->setVariable("TXT_PRE", $this->lng->txt("pre_cookies_enabled"));

		if ($this->preliminaries_result["cookies"]["status"] == true)
		{
			$this->tpl->setVariable("STATUS_PRE", $OK);
		}
		else
		{
			$this->tpl->setVariable("STATUS_PRE", $FAILED);
			$this->tpl->setVariable("COMMENT_PRE", $this->preliminaries_result["cookies"]["comment"]);
		}

		$this->tpl->parseCurrentBlock();

		// check javascript
		$this->tpl->setCurrentBlock("preliminary_js");
		$this->tpl->setVariable("TXT_PRE", $this->lng->txt("pre_javascript_enabled"));
		$this->tpl->setVariable("STATUS_PRE", $FAILED);
		$this->tpl->setVariable("COMMENT_PRE", $this->lng->txt("pre_javascript_comment"));
		$this->tpl->parseCurrentBlock();

		// summary
		if ($this->preliminaries === true)
		{
			$cmd = "install";

			$btn_text = ($this->cmd == "preliminaries") ? "" : "installation";
			$this->setButtonNext($cmd,$btn_text);
		}
		else
		{
			$this->tpl->setCurrentBlock("premessage");
			$this->tpl->setVariable("TXT_PRE_ERR", $this->lng->txt("pre_error"));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * display master setup form & process form input
	 */
	function displayMasterSetup($a_det = false)
	{
		if ($_POST["form"])
		{
			if (!$this->checkDataDirSetup($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}
	
			if (!$this->checkLogSetup($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}

			if ($a_det)
			{
				$_POST["form"] = $this->determineTools($_POST["form"]);
			}
			
			/*if (!$this->checkToolsSetup($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}*/
			
			if (!$this->checkPasswordSetup($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}

			if (!$this->saveMasterSetup($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}           
			
			ilUtil::sendInfo($this->lng->txt("settings_saved"),true);
			
			ilUtil::redirect("setup.php?cmd=mastersettings");
		}

		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html");

		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.form_mastersetup.html");

		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		
		// for checkboxes & radio buttons
		$checked = "checked=\"checked\"";

		// general
		$this->tpl->setVariable("TXT_ENTER_DIR_AND_FILENAME", $this->lng->txt("dsfsdave"));
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("setup_basic_settings"));
		$this->tpl->setVariable("SUBMIT_CMD", "install");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_ENTER_DIR_AND_FILENAME", $this->lng->txt("enter_dir_and_filename"));
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_first_install")."<br/>".$this->lng->txt("info_text_pathes"));
		$this->tpl->setVariable("TXT_DET_TOOLS_PATH", $this->lng->txt("determine_tools_paths"));
		$this->tpl->setVariable("CMD_DET_TOOLS_PATH", "determineToolsPathInstall");

		
		if ($this->safe_mode)
		{
			$this->tpl->setVariable("SAFE_MODE_STYLE", " class=\"message\" ");
			$this->tpl->setVariable("TXT_SAFE_MODE_INFO", $this->lng->txt("safe_mode_enabled"));
		}
		else
		{
			$this->tpl->setVariable("TXT_SAFE_MODE_INFO", "");
		}

		// datadir
		$this->tpl->setCurrentBlock("setup_datadir");
		$this->tpl->setVariable("TXT_DATADIR_TITLE", $this->lng->txt("main_datadir_outside_webspace"));
		$this->tpl->setVariable("TXT_DATADIR_PATH", $this->lng->txt("datadir_path"));
		$this->tpl->setVariable("TXT_DATADIR_COMMENT1", $this->lng->txt("datadir_path_comment1"));
		$this->tpl->setVariable("TXT_CREATE", $this->lng->txt("create_directory"));
		// values
		//echo $this->ini->readVariable($this->ini->readVariable("server","presetting"),"data_dir");
		if ($_SESSION["error_post_vars"]["form"])
		{
			$this->tpl->setVariable("DATADIR_PATH", $_SESSION["error_post_vars"]["form"]["datadir_path"]);
		}
		elseif ($this->ini->readVariable("server","presetting") != "")
		{
			$this->tpl->setVariable("DATADIR_PATH", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"data_dir"));
		}


			
		//$chk_datadir_path = ($_SESSION["error_post_vars"]["form"]["chk_datadir_path"]) ? "CHK_DATADIR_PATH_TARGET" : "CHK_DATADIR_PATH_CREATE";
		$chk_datadir_path = ($_SESSION["error_post_vars"]["form"]["chk_datadir_path"]) ? $checked : "";
		$this->tpl->setVariable("CHK_DATADIR_PATH",$chk_datadir_path);
		$this->tpl->parseCurrentBlock();
		
		// logging
		$this->tpl->setCurrentBlock("setup_log");
		$this->tpl->setVariable("TXT_LOG_TITLE", $this->lng->txt("logging"));
		$this->tpl->setVariable("TXT_LOG_PATH", $this->lng->txt("log_path"));
		$this->tpl->setVariable("TXT_LOG_COMMENT", $this->lng->txt("log_path_comment"));
		$this->tpl->setVariable("TXT_DISABLE_LOGGING", $this->lng->txt("disable_logging"));

		// values
		if ($_SESSION["error_post_vars"]["form"])
		{
			$this->tpl->setVariable("LOG_PATH", $_SESSION["error_post_vars"]["form"]["log_path"]);
		}
		elseif ($this->ini->readVariable("server","presetting") != "")
		{
			$this->tpl->setVariable("LOG_PATH", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"log"));
		}

		$chk_log_path = ($_SESSION["error_post_vars"]["form"]["chk_log_status"]) ? $checked : "";
		$this->tpl->setVariable("CHK_LOG_STATUS",$chk_log_path);
		$this->tpl->parseCurrentBlock();

		// tools
		$this->tpl->setCurrentBlock("setup_tools");
		$this->tpl->setVariable("TXT_DISABLE_CHECK", $this->lng->txt("disable_check"));
		$this->tpl->setVariable("TXT_TOOLS_TITLE", $this->lng->txt("3rd_party_software"));
		$this->tpl->setVariable("TXT_CONVERT_PATH", $this->lng->txt("convert_path"));
		$this->tpl->setVariable("TXT_ZIP_PATH", $this->lng->txt("zip_path"));
		$this->tpl->setVariable("TXT_UNZIP_PATH", $this->lng->txt("unzip_path"));
		$this->tpl->setVariable("TXT_JAVA_PATH", $this->lng->txt("java_path"));
		$this->tpl->setVariable("TXT_HTMLDOC_PATH", $this->lng->txt("htmldoc_path"));
		$this->tpl->setVariable("TXT_LATEX_URL", $this->lng->txt("url_to_latex"));

		$this->tpl->setVariable("TXT_CONVERT_COMMENT", $this->lng->txt("convert_path_comment"));
		$this->tpl->setVariable("TXT_ZIP_COMMENT", $this->lng->txt("zip_path_comment"));
		$this->tpl->setVariable("TXT_UNZIP_COMMENT", $this->lng->txt("unzip_path_comment"));
		$this->tpl->setVariable("TXT_JAVA_COMMENT", $this->lng->txt("java_path_comment"));
		$this->tpl->setVariable("TXT_HTMLDOC_COMMENT", $this->lng->txt("htmldoc_path_comment"));
		$this->tpl->setVariable("TXT_LATEX_URL_COMMENT", $this->lng->txt("latex_url_comment"));

		// values
		if ($_SESSION["error_post_vars"]["form"])
		{
			$this->tpl->setVariable("CONVERT_PATH", $_SESSION["error_post_vars"]["form"]["convert_path"]);
			$this->tpl->setVariable("ZIP_PATH", $_SESSION["error_post_vars"]["form"]["zip_path"]);
			$this->tpl->setVariable("UNZIP_PATH", $_SESSION["error_post_vars"]["form"]["unzip_path"]);
			$this->tpl->setVariable("JAVA_PATH", $_SESSION["error_post_vars"]["form"]["java_path"]);
			$this->tpl->setVariable("HTMLDOC_PATH", $_SESSION["error_post_vars"]["form"]["htmldoc_path"]);
			$this->tpl->setVariable("LATEX_URL", $_SESSION["error_post_vars"]["form"]["latex_url"]);
		}
		elseif ($this->ini->readVariable("server","presetting") != "")
		{
			$this->tpl->setVariable("CONVERT_PATH", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"convert"));
			$this->tpl->setVariable("ZIP_PATH", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"zip"));
			$this->tpl->setVariable("UNZIP_PATH", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"unzip"));
			$this->tpl->setVariable("JAVA_PATH", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"java"));
			$this->tpl->setVariable("HTMLDOC_PATH", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"htmldoc"));
			$this->tpl->setVariable("LATEX_URL", $this->ini->readVariable($this->ini->readVariable("server","presetting"),"latex"));
		}
								
		$this->tpl->setVariable("TXT_VIRUS_SCANNER", $this->lng->txt("virus_scanner"));
		$this->tpl->setVariable("TXT_NONE", $this->lng->txt("none"));
		$this->tpl->setVariable("TXT_SOPHOS", $this->lng->txt("sophos"));
		$this->tpl->setVariable("TXT_ANTIVIR", $this->lng->txt("antivir"));
		$this->tpl->setVariable("TXT_CLAMAV", $this->lng->txt("clamav"));
		$this->tpl->setVariable("TXT_SCAN_COMMAND", $this->lng->txt("scan_command"));
		$this->tpl->setVariable("TXT_CLEAN_COMMAND", $this->lng->txt("clean_command"));


		$chk_convert_path = ($_SESSION["error_post_vars"]["form"]["chk_convert_path"]) ? $checked : "";
		$chk_zip_path = ($_SESSION["error_post_vars"]["form"]["chk_zip_path"]) ? $checked : "";
		$chk_unzip_path = ($_SESSION["error_post_vars"]["form"]["chk_unzip_path"]) ? $checked : "";
		$chk_java_path = ($_SESSION["error_post_vars"]["form"]["chk_java_path"]) ? $checked : "";
		$chk_htmldoc_path = ($_SESSION["error_post_vars"]["form"]["chk_htmldoc_path"]) ? $checked : "";
		$chk_latex_url = ($_SESSION["error_post_vars"]["form"]["chk_latex_url"]) ? $checked : "";

		$this->tpl->setVariable("CHK_CONVERT_PATH", $chk_convert_path);
		$this->tpl->setVariable("CHK_ZIP_PATH", $chk_zip_path);
		$this->tpl->setVariable("CHK_UNZIP_PATH", $chk_unzip_path);
		$this->tpl->setVariable("CHK_JAVA_PATH", $chk_java_path);
		$this->tpl->setVariable("CHK_HTMLDOC_PATH", $chk_htmldoc_path);
		$this->tpl->setVariable("CHK_LATEX_URL", $chk_latex_url);
		$this->tpl->parseCurrentBlock();
		
		// setup password
		$this->tpl->setCurrentBlock("setup_pass");
		$this->tpl->setVariable("TXT_SETUP_PASS_TITLE", $this->lng->txt("setup_pass_title"));
		$this->tpl->setVariable("TXT_SETUP_PASS_COMMENT", $this->lng->txt("password_info"));
		$this->tpl->setVariable("TXT_SETUP_PASS", $this->lng->txt("setup_pass"));
		$this->tpl->setVariable("TXT_SETUP_PASS2", $this->lng->txt("setup_pass2"));
		// values
		$this->tpl->setVariable("SETUP_PASS", $_SESSION["error_post_vars"]["form"]["setup_pass"]);
		$this->tpl->setVariable("SETUP_PASS2", $_SESSION["error_post_vars"]["form"]["setup_pass2"]);
		$this->tpl->parseCurrentBlock();
		
		$this->setButtonPrev("preliminaries");

		if ($this->isInstalled())
		{
			$this->setButtonNext("list");
		}
	}
	
	/**
	 * login to a client
	 */
	function loginClient()
	{
		session_destroy();
		
		ilUtil::redirect(ILIAS_HTTP_PATH."/login.php?client_id=".$this->client->getId());
	}
	
	/**
	 * display login form and process form
	 */
	function displayLogin()
	{
		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html");

		if ($_POST["form"])
		{
			// first check client login
			if (empty($_POST["form"]["admin_password"]))
			{
				if (!$this->loginAsClient($_POST["form"]))
				{
					if ($error_msg = $this->getError())
					{
						$this->raiseError($this->lng->txt($error_msg),$this->error_obj->MESSAGE);
					}
				}
			}
			else
			{
				if (!$this->loginAsAdmin($_POST["form"]["admin_password"]))
				{
					$this->raiseError($this->lng->txt("login_invalid"),$this->error_obj->MESSAGE);
				}
			}

			ilUtil::redirect("setup.php");
		}

		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.form_login.html");
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("setup_login"));

		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_login"));

		$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_CLIENT_LOGIN",$this->lng->txt("client_login"));
		$this->tpl->setVariable("TXT_CLIENT_ID",$this->lng->txt("client_id"));
		$this->tpl->setVariable("TXT_USERNAME",ucfirst($this->lng->txt("username")));
		$this->tpl->setVariable("TXT_PASSWORD",ucfirst($this->lng->txt("password")));
		$this->tpl->setVariable("TXT_OR",strtoupper($this->lng->txt("or")));
		$this->tpl->setVariable("TXT_ADMIN_LOGIN",$this->lng->txt("admin_login"));
		$this->tpl->setVariable("TXT_ADMIN_PASSWORD",ucfirst($this->lng->txt("password")));
		$this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("submit"));
	}

	/**
	 * display client list and process form input
	 */
	function displayClientList()
	{
		$_SESSION["ClientId"] = "";
		
		$_GET["sort_by"] = ($_GET["sort_by"]) ? $_GET["sort_by"] : "name";

		$clientlist = new ilClientList($this->db_connections);
		$list = $clientlist->getClients();

		if (count($list) == 0)
		{
			ilUtil::sendInfo($this->lng->txt("no_clients_available"),true);
		}
		
		// prepare clientlist
		$data = array();
		$data["data"] = array();
		$data["ctrl"] = array();
		$data["cols"] = array("","name","id","login","details","status","access");

		foreach ($list as $key => $client)
		{
			// check status 
			$status_arr = $this->getStatus($client);

			if (!$status_arr["db"]["status"])
			{
				$status = $status_arr["db"]["comment"];
			}
			elseif (!$status_arr["finish"]["status"])
			{
				$status = $this->lng->txt("setup_not_finished");
			}
			else
			{
				$status = "<font color=\"green\"><strong>OK</strong></font>";
			}
			
			if ($status_arr["access"]["status"])
			{
				$access = "online";
			}
			else
			{
				$access = "disabled";
			}
			
			if ($key == $this->default_client)
			{
				$default = " checked=\"checked\"";
			}
			else
			{
				$default = "";
			}
			
			if ($status_arr["finish"]["status"] and $status_arr["access"]["status"])
			{
				$login = "<a href=\"../login.php?client_id=".$key."\">Login</a>";
			}
			else
			{
				$login = "&nbsp;";
			}

			$access_html = "<a href=\"setup.php?cmd=changeaccess&client_id=".$key."&back=clientlist\">".$this->lng->txt($access)."</a>";
			
			$client_name = ($client->getName()) ? $client->getName() : "&lt;".$this->lng->txt("no_client_name")."&gt;";
			
			// visible data part
			$data["data"][] = array(
							"default"       => "<input type=\"radio\" name=\"form[default]\" value=\"".$key."\"".$default."/>",
							"name"          => $client_name."#separator#".$client->getDescription(),
							"id"            => $key,
							"login"         => $login,
							"details"       => "<a href=\"setup.php?cmd=view&client_id=".$key."\">Details</a>",
							"status"        => $status,
							"access_html"   => $access_html
							);

		}

		$this->maxcount = count($data["data"]);

		// sorting array
		$data["data"] = ilUtil::sortArray($data["data"],$_GET["sort_by"],$_GET["sort_order"]);

		$this->tpl->addBlockFile("CONTENT","content","tpl.clientlist.html");
		
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_list"));
		
		ilUtil::sendInfo();

		// load template for table
		$this->tpl->addBlockfile("CLIENT_LIST", "client_list", "tpl.table.html");
		// load template for table content data
		$this->tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

		// common
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("available_clients"));
		$this->tpl->setVariable("TXT_LISTSTATUS",($this->ini->readVariable("clients","list")) ? $this->lng->txt("display_clientlist") : $this->lng->txt("hide_clientlist"));
		$this->tpl->setVariable("TXT_TOGGLELIST",($this->ini->readVariable("clients","list")) ? $this->lng->txt("disable") : $this->lng->txt("enable"));

		$this->tpl->setVariable("FORMACTION","setup.php?cmd=gateway");

		// build table
		include_once "../Services/Table/classes/class.ilTableGUI.php";
		$tbl = new ilTableGUI();
		$tbl->disable("sort");
		//$tbl->enable("header");

		$num = 0;

		// title & header columns
		$tbl->setTitle(ucfirst($this->lng->txt("select_client")));

		foreach ($data["cols"] as $val)
		{
			$header_names[] = ucfirst($this->lng->txt($val));
		}
		$tbl->setHeaderNames($header_names);
		$tbl->setHeaderVars($data["cols"],$header_params);
		$tbl->setColumnWidth(array("5%","30%","10%","10%","10%","20%","15%"));
		
		// control
		$tbl->setOrderColumn($_GET["sort_by"],"name");
		$tbl->setOrderDirection($_GET["sort_order"],"asc");
		$tbl->setLimit(0);
		$tbl->setOffset(0);
		$tbl->setMaxCount($maxcount);
		
		// show valid actions
		$this->tpl->setVariable("COLUMN_COUNTS",count($data["cols"]));
		
		// footer
		//$tbl->setFooter("tbl_footer");
		
		$tbl->disable("footer");
		$tbl->disable("icon");
		$tbl->disable("numinfo");
		
		// render table
		$tbl->render();

		if (is_array($data["data"][0]))
		{
			// table cell
			for ($i=0; $i < count($data["data"]); $i++)
			{
				$data2 = $data["data"][$i];
				$ctrl = $data["ctrl"][$i];

				// color changing
				$css_row = ilUtil::switchColor($i+1,"tblrow1","tblrow2");

				$this->tpl->setCurrentBlock("table_cell");
				$this->tpl->setVariable("CELLSTYLE", "tblrow1");
				$this->tpl->parseCurrentBlock();

				foreach ($data2 as $key => $val)
				{
					$this->tpl->setCurrentBlock("text");
					
					if ($key == "name")
					{
						$name_field = explode("#separator#",$val);
						$val = $name_field[0]."<br/><span class=\"subtitle\">".$name_field[1]."</span>";
					}

					$this->tpl->setVariable("TEXT_CONTENT", $val);                  
					$this->tpl->parseCurrentBlock();

					$this->tpl->setCurrentBlock("table_cell");
					$this->tpl->parseCurrentBlock();

				} //end foreach

				$this->tpl->setCurrentBlock("tbl_content");
				$this->tpl->setVariable("CSS_ROW", $css_row);
				$this->tpl->parseCurrentBlock();
			}

			$this->tpl->setCurrentBlock("tbl_action_btn");
			$this->tpl->setVariable("TPLPATH",TPLPATH);         
			$this->tpl->setVariable("BTN_NAME","changedefault");
			$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("set_default_client"));
			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("tbl_action_row");
			$this->tpl->setVariable("TPLPATH",TPLPATH);         
			$this->tpl->setVariable("COLUMN_COUNTS","7");
			$this->tpl->parseCurrentBlock();

		}
	}

	/**
	* Determine tools paths
	*/
	function determineToolsPath()
	{
		$this->changeMasterSettings(true);
	}
	
	/**
	* Determine tools paths
	*/
	function determineToolsPathInstall()
	{
		$this->displayMasterSetup(true);
	}
	
	/**
	* Determine Tools
	*/
	function determineTools($a_tools)
	{
		$tools = array("convert", "zip", "unzip", "java", "htmldoc");
		$dirs = array("/usr/local", "/usr/local/bin", "/usr/bin", "/bin", "/sw/bin");
		foreach($tools as $tool)
		{
			// try which command
			unset($ret);
			@exec("which ".$tool, $ret);
			if (substr($ret[0], 0, 3) != "no " && substr($ret[0], 0, 1) == "/")
			{
				$a_tools[$tool."_path"] = $ret[0];
				continue;
			}
			
			// try common directories
			foreach($dirs as $dir)
			{
				if (is_file($dir."/".$tool))
				{
					$a_tools[$tool."_path"] = $dir."/".$tool;
					continue;
				}
			}
		}
		return $a_tools;
	}
	
	/**
	 * display master settings and process form input
	 */
	function changeMasterSettings($a_det = false)
	{
		if ($_POST["form"])
		{
			if (!$this->checkLogSetup($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}

			/*if (!$this->checkToolsSetup($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}*/

			if ($a_det)
			{
				$_POST["form"] = $this->determineTools($_POST["form"]);
			}
			
			if (!$this->updateMasterSettings($_POST["form"]))
			{
				$this->raiseError($this->lng->txt($this->getError()),$this->error_obj->MESSAGE);
			}

			ilUtil::sendInfo($this->lng->txt("settings_saved"),true);
			ilUtil::redirect("setup.php?cmd=mastersettings");
		}

		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html");

		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.form_mastersetup.html");

		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");

		// for checkboxes & radio buttons
		$checked = "checked=\"checked\"";

		// general
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("change_basic_settings"));
		$this->tpl->setVariable("SUBMIT_CMD", "mastersettings");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		$this->tpl->setVariable("TXT_ENTER_DIR_AND_FILENAME", $this->lng->txt("enter_dir_and_filename"));
		$this->tpl->setVariable("TXT_DET_TOOLS_PATH", $this->lng->txt("determine_tools_paths"));
		$this->tpl->setVariable("CMD_DET_TOOLS_PATH", "determineToolsPath");
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_pathes"));
		
		if ($this->safe_mode)
		{
			$this->tpl->setVariable("SAFE_MODE_STYLE", " class=\"message\" ");
			$this->tpl->setVariable("TXT_SAFE_MODE_INFO", $this->lng->txt("safe_mode_enabled"));
		}
		else
		{
			$this->tpl->setVariable("TXT_SAFE_MODE_INFO", "");
		}
		// datadir
		$this->tpl->setCurrentBlock("display_datadir");
		$this->tpl->setVariable("TXT_DATADIR_TITLE", $this->lng->txt("main_datadir_outside_webspace"));
		$this->tpl->setVariable("TXT_DATADIR_PATH", $this->lng->txt("datadir_path"));
		$this->tpl->setVariable("DATADIR_PATH", $this->ini->readVariable("clients","datadir"));
		$this->tpl->setVariable("TXT_DATADIR_COMMENT2", $this->lng->txt("datadir_path_comment2"));
		$this->tpl->parseCurrentBlock();

		// logging
		$this->tpl->setCurrentBlock("setup_log");
		$this->tpl->setVariable("TXT_LOG_TITLE", $this->lng->txt("logging"));
		$this->tpl->setVariable("TXT_LOG_PATH", $this->lng->txt("log_path"));
		$this->tpl->setVariable("TXT_LOG_COMMENT", $this->lng->txt("log_path_comment"));
		$this->tpl->setVariable("TXT_DISABLE_LOGGING", $this->lng->txt("disable_logging"));
		// values
		if ($_SESSION["error_post_vars"])
		{
			$this->tpl->setVariable("LOG_PATH", $_SESSION["error_post_vars"]["form"]["log_path"]);
			$chk_log_status = ($_SESSION["error_post_vars"]["form"]["chk_log_status"]) ? $checked : "";
		}
		else
		{
			$this->tpl->setVariable("LOG_PATH",$this->ini->readVariable("log","path")."/".$this->ini->readVariable("log","file"));
			$chk_log_status = ($this->ini->readVariable("log","enabled")) ? "" : $checked;

		}

		$this->tpl->setVariable("CHK_LOG_STATUS",$chk_log_status);
		$this->tpl->parseCurrentBlock();

		// tools
		$this->tpl->setCurrentBlock("setup_tools");
		$this->tpl->setVariable("TXT_DISABLE_CHECK", $this->lng->txt("disable_check"));
		$this->tpl->setVariable("TXT_TOOLS_TITLE", $this->lng->txt("3rd_party_software"));
		$this->tpl->setVariable("TXT_CONVERT_PATH", $this->lng->txt("convert_path"));
		$this->tpl->setVariable("TXT_ZIP_PATH", $this->lng->txt("zip_path"));
		$this->tpl->setVariable("TXT_UNZIP_PATH", $this->lng->txt("unzip_path"));
		$this->tpl->setVariable("TXT_JAVA_PATH", $this->lng->txt("java_path"));
		$this->tpl->setVariable("TXT_HTMLDOC_PATH", $this->lng->txt("htmldoc_path"));
		$this->tpl->setVariable("TXT_LATEX_URL", $this->lng->txt("url_to_latex"));
		$this->tpl->setVariable("TXT_FOP_PATH", $this->lng->txt("fop_path"));
		
		$this->tpl->setVariable("TXT_VIRUS_SCANNER", $this->lng->txt("virus_scanner"));
		$this->tpl->setVariable("TXT_NONE", $this->lng->txt("none"));
		$this->tpl->setVariable("TXT_SOPHOS", $this->lng->txt("sophos"));
		$this->tpl->setVariable("TXT_ANTIVIR", $this->lng->txt("antivir"));
		$this->tpl->setVariable("TXT_CLAMAV", $this->lng->txt("clamav"));
		$this->tpl->setVariable("TXT_SCAN_COMMAND", $this->lng->txt("scan_command"));
		$this->tpl->setVariable("TXT_CLEAN_COMMAND", $this->lng->txt("clean_command"));

		$this->tpl->setVariable("TXT_CONVERT_COMMENT", $this->lng->txt("convert_path_comment"));
		$this->tpl->setVariable("TXT_ZIP_COMMENT", $this->lng->txt("zip_path_comment"));
		$this->tpl->setVariable("TXT_UNZIP_COMMENT", $this->lng->txt("unzip_path_comment"));
		$this->tpl->setVariable("TXT_JAVA_COMMENT", $this->lng->txt("java_path_comment"));
		$this->tpl->setVariable("TXT_HTMLDOC_COMMENT", $this->lng->txt("htmldoc_path_comment"));
		$this->tpl->setVariable("TXT_LATEX_URL_COMMENT", $this->lng->txt("latex_url_comment"));
		$this->tpl->setVariable("TXT_FOP_COMMENT", $this->lng->txt("fop_path_comment"));
		// values
		if ($_SESSION["error_post_vars"])
		{
			$vals = $_SESSION["error_post_vars"]["form"];
		}
		else
		{
			$vals["convert_path"] = $this->ini->readVariable("tools","convert");
			$vals["zip_path"] = $this->ini->readVariable("tools","zip");
			$vals["unzip_path"] = $this->ini->readVariable("tools","unzip");
			$vals["java_path"] = $this->ini->readVariable("tools","java");
			$vals["htmldoc_path"] = $this->ini->readVariable("tools","htmldoc");
			$vals["latex_url"] = $this->ini->readVariable("tools","latex");
			$vals["fop_path"] = $this->ini->readVariable("tools","fop");
			$vals["vscanner_type"] = $this->ini->readVariable("tools", "vscantype");
			$vals["scan_command"] = $this->ini->readVariable("tools", "scancommand");
			$vals["clean_command"] = $this->ini->readVariable("tools", "cleancommand");
		}
		
		$tools = array("convert" => "testConvert", "zip" => "testZip",
			"unzip" => "testUnzip", "java" => "testJava", "htmldoc" => "testHtmldoc",
			"latex" => "testLatex");
		foreach ($tools as $tool => $func)
		{
			$end = ($tool == "latex")
				? "url"
				: "path";
			if (($err = $this->$func($vals[$tool."_".$end])) != "")
			{
				$this->tpl->setCurrentBlock("warning_".$tool);
				$this->tpl->setVariable("TXT_WARNING_".strtoupper($tool), $this->lng->txt($err));
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setVariable("CONVERT_PATH", $vals["convert_path"]);
		$this->tpl->setVariable("ZIP_PATH", $vals["zip_path"]);
		$this->tpl->setVariable("UNZIP_PATH", $vals["unzip_path"]);
		$this->tpl->setVariable("JAVA_PATH", $vals["java_path"]);
		$this->tpl->setVariable("HTMLDOC_PATH", $vals["htmldoc_path"]);
		$this->tpl->setVariable("LATEX_URL", $vals["latex_url"]);
		$this->tpl->setVariable("FOP_PATH", $vals["fop_path"]);
		$this->tpl->setVariable("STYPE_".
			strtoupper($vals["vscanner_type"]), " selected=\"1\" ");
		$this->tpl->setVariable("SCAN_COMMAND", $vals["scan_command"]);
		$this->tpl->setVariable("CLEAN_COMMAND", $vals["clean_command"]);
		
		$chk_convert_path = ($_SESSION["error_post_vars"]["form"]["chk_convert_path"]) ? $checked : "";
		$chk_zip_path = ($_SESSION["error_post_vars"]["form"]["chk_zip_path"]) ? $checked : "";
		$chk_unzip_path = ($_SESSION["error_post_vars"]["form"]["chk_unzip_path"]) ? $checked : "";
		$chk_java_path = ($_SESSION["error_post_vars"]["form"]["chk_java_path"]) ? $checked : "";
		$chk_htmldoc_path = ($_SESSION["error_post_vars"]["form"]["chk_htmldoc_path"]) ? $checked : "";
		$chk_latex_url = ($_SESSION["error_post_vars"]["form"]["chk_latex_url"]) ? $checked : "";
		$chk_fop_path = ($_SESSION["error_post_vars"]["form"]["chk_fop_path"]) ? $checked : "";

		$this->tpl->setVariable("CHK_LOG_STATUS", $chk_log_stauts);
		$this->tpl->setVariable("CHK_CONVERT_PATH", $chk_convert_path);
		$this->tpl->setVariable("CHK_ZIP_PATH", $chk_zip_path);
		$this->tpl->setVariable("CHK_UNZIP_PATH", $chk_unzip_path);
		$this->tpl->setVariable("CHK_JAVA_PATH", $chk_java_path);
		$this->tpl->setVariable("CHK_HTMLDOC_PATH", $chk_htmldoc_path);
		$this->tpl->setVariable("CHK_LATEX_URL", $chk_latex_url);
		$this->tpl->setVariable("CHK_FOP_PATH", $chk_fop_path);
		$this->tpl->parseCurrentBlock();
		
		$this->btn_next_on = true;
		$this->btn_next_lng = $this->lng->txt("create_new_client")." >>";
		$this->btn_next_cmd = "newclient";

	}

	/**
	 * display setup in step
	 */
	function displayIni()
	{
		$this->checkDisplayMode("create_new_client");
		
		// checkings
		if ($_POST["form"])
		{
			// check client name
			if (!$_POST["form"]["client_id"])
			{
				$this->raiseError($this->lng->txt("ini_no_client_id"),$this->error_obj->MESSAGE);
			}

			if (strlen($_POST["form"]["client_id"]) != strlen(urlencode(($_POST["form"]["client_id"]))))
			{
				$this->raiseError($this->lng->txt("ini_client_id_invalid"),$this->error_obj->MESSAGE);
			}           

			if (strlen($_POST["form"]["client_id"]) < 4)
			{
				$this->raiseError($this->lng->txt("ini_client_id_too_short"),$this->error_obj->MESSAGE);
			}

			if (strlen($_POST["form"]["client_id"]) > 32)
			{
				$this->raiseError($this->lng->txt("ini_client_id_too_long"),$this->error_obj->MESSAGE);
			}

			// check database
			if (!$_POST["form"]["db_host"])
			{
				$this->raiseError($this->lng->txt("ini_no_db_host"),$this->error_obj->MESSAGE);
			}

			if (!$_POST["form"]["db_name"])
			{
				$this->raiseError($this->lng->txt("ini_no_db_name"),$this->error_obj->MESSAGE);
			}
			
			if (!$_POST["form"]["db_user"])
			{
				$this->raiseError($this->lng->txt("ini_no_db_user"),$this->error_obj->MESSAGE);
			}

			// create new client object if it does not exist
			if (!$this->ini_client_exists)
			{
				$client_id = $_POST["form"]["client_id"];
				
				// check for existing client dir (only for newly created clients not renaming)
				if (!$this->ini_client_exists and file_exists(ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$client_id))
				{
					$this->raiseError($this->lng->txt("ini_client_id_exists"),$this->error_obj->MESSAGE);
				}

				$this->newClient($client_id);
			}

			// save some old values
			$old_db_name = $this->client->getDbName();
			$old_client_id = $this->client->getId();            
			// set client data 
			$this->client->setId($_POST["form"]["client_id"]);
			$this->client->setDbHost($_POST["form"]["db_host"]);
			$this->client->setDbName($_POST["form"]["db_name"]);
			$this->client->setDbUser($_POST["form"]["db_user"]);
			$this->client->setDbPass($_POST["form"]["db_pass"]);
			$this->client->setDSN();
			
			// try to connect to database
			if (!$this->client->checkDatabaseHost())
			{
				$this->raiseError($this->lng->txt($this->client->getError()),$this->error_obj->MESSAGE);
			}
			
			// check if db exists
			$db_installed = $this->client->checkDatabaseExists();

			if ($db_installed and (!$this->ini_ilias_exists or ($this->client->getDbName() != $old_db_name)))
			{
				$_POST["form"]["db_name"] = $old_db_name;
				$message = ucfirst($this->lng->txt("database"))." \"".$this->client->getDbName()."\" ".$this->lng->txt("ini_db_name_exists");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}
			
			if ($this->ini_client_exists and $old_client_id != $this->client->getId())
			{
				$message = $this->lng->txt("ini_client_id_no_change");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}

			// all ok. create client.ini and save posted data
			if (!$this->ini_client_exists)
			{
				if ($this->saveNewClient())
				{
					ilUtil::sendInfo($this->lng->txt("settings_saved"));
					$this->client->status["ini"]["status"] = true;
				}
				else
				{
					$err = $this->getError();
					ilUtil::sendInfo($this->lng->txt("save_error").": ".$err);
					$this->client->status["ini"]["status"] = false;
					$this->client->status["ini"]["comment"] = $err;
				}
			}
			else
			{
				if ($this->client->ini->write())
				{
					ilUtil::sendInfo($this->lng->txt("settings_changed"));
					$this->client->status["ini"]["status"] = true;
				}
				else
				{
					$err = $this->client->ini->getError();
					ilUtil::sendInfo($this->lng->txt("save_error").": ".$err);
					$this->client->status["ini"]["status"] = false;
					$this->client->status["ini"]["comment"] = $err;
				}
			}
		}

		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_ini.html");
		
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_ini"));
		
		// display default values, loaded valus or saved error values
		if ($_SESSION["error_post_vars"]["form"])
		{
			$this->tpl->setVariable("CLIENT_ID", $_SESSION["error_post_vars"]["form"]["client_id"]);
			$this->tpl->setVariable("DB_HOST", $_SESSION["error_post_vars"]["form"]["db_host"]);    
			$this->tpl->setVariable("DB_NAME", $_SESSION["error_post_vars"]["form"]["db_name"]);        
			$this->tpl->setVariable("DB_USER", $_SESSION["error_post_vars"]["form"]["db_user"]);        
			$this->tpl->setVariable("DB_PASS", $_SESSION["error_post_vars"]["form"]["db_pass"]);
		}
		else
		{
			$this->tpl->setVariable("CLIENT_ID", $this->client->getId());
			$this->tpl->setVariable("DB_HOST", $this->client->getDbHost()); 
			$this->tpl->setVariable("DB_NAME", $this->client->getDbName());     
			$this->tpl->setVariable("DB_USER", $this->client->getDbUser());     
			$this->tpl->setVariable("DB_PASS", $this->client->getDbPass());     
		}

		$this->tpl->setVariable("TXT_CLIENT_HEADER", $this->lng->txt("inst_identification"));
		$this->tpl->setVariable("TXT_CLIENT_ID", $this->lng->txt("client_id"));
		$this->tpl->setVariable("TXT_DB_HEADER", $this->lng->txt("db_conn"));
		$this->tpl->setVariable("TXT_DB_TYPE", $this->lng->txt("db_type"));
		$this->tpl->setVariable("TXT_DB_HOST", $this->lng->txt("db_host"));
		$this->tpl->setVariable("TXT_DB_NAME", $this->lng->txt("db_name")); 
		$this->tpl->setVariable("TXT_DB_USER", $this->lng->txt("db_user"));
		$this->tpl->setVariable("TXT_DB_PASS", $this->lng->txt("db_pass"));

		if ($this->client->status["ini"]["status"])
		{
			$this->setButtonNext("db");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	 * display error page
	 * 
	 * @param    string  error message
	 */
	function displayError($a_message)
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.error.html");
		
		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("FORMACTION", $_SESSION["referer"]);
		$this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
		$this->tpl->setVariable("ERROR_MESSAGE",($a_message));
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->show();
		exit();
	}

	/**
	 * display logout page
	 */
	function displayLogout()
	{
		$this->tpl->addBlockFile("CONTENT","content","tpl.logout.html");

		session_destroy();

		$this->logged_out = true;
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("logged_out"));        
		$this->tpl->setCurrentBlock("home_link");
		$this->tpl->setVariable("TXT_INDEX",$this->lng->txt("ilias_homepage"));
		$this->tpl->setVariable("LNK_INDEX",ILIAS_HTTP_PATH."/index.php");
		$this->tpl->parseCurrentBlock();
	}

	/**
	 * display process panel
	 */
	function displayProcessPanel()
	{
		$OK = "<font color=\"green\"><strong>OK</strong></font>";
		
		$steps = array();
		$steps = $this->getStatus();
		
		// remove access step
		unset($steps["access"]);
		
		$steps["ini"]["text"]       = $this->lng->txt("setup_process_step_ini");
		$steps["db"]["text"]        = $this->lng->txt("setup_process_step_db");
		$steps["lang"]["text"]      = $this->lng->txt("setup_process_step_lang");
		$steps["contact"]["text"]   = $this->lng->txt("setup_process_step_contact");
		$steps["nic"]["text"]       = $this->lng->txt("setup_process_step_nic");
		$steps["finish"]["text"]    = $this->lng->txt("setup_process_step_finish");
		
		$this->tpl->addBlockFile("PROCESS_MENU","process_menu","tpl.process_panel.html");

		$this->tpl->setVariable("TXT_SETUP_PROCESS_STATUS",$this->lng->txt("setup_process_status"));

		$num = 1;

		foreach ($steps as $key => $val)
		{
			$this->tpl->setCurrentBlock("menu_row");
			$this->tpl->setVariable("TXT_STEP",$this->lng->txt("step")." ".$num.": &nbsp;");
			$this->tpl->setVariable("TXT_ACTION",$val["text"]);
			$this->tpl->setVariable("IMG_ARROW", "spacer.gif");
			
			$num++;

			if ($this->cmd == $key and isset($this->cmd))
			{
				$this->tpl->setVariable("HIGHLIGHT", " style=\"font-weight:bold;\"");
				$this->tpl->setVariable("IMG_ARROW", "arrow_right.png");
			}
			
			$status = ($val["status"]) ? $OK : "";          
			
			$this->tpl->setVariable("TXT_STATUS",$status);
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	 * display status panel
	 */
	function displayStatusPanel()
	{
		$OK = "<font color=\"green\"><strong>OK</strong></font>";

		$this->tpl->addBlockFile("STATUS_PANEL","status_panel","tpl.status_panel.html");

		$this->tpl->setVariable("TXT_OVERALL_STATUS", $this->lng->txt("overall_status"));
		// display status
		if ($this->client->status)
		{
			foreach ($this->client->status as $key => $val)
			{
				$status = ($val["status"]) ? $OK : "&nbsp;";
				$this->tpl->setCurrentBlock("status_row");
				$this->tpl->setVariable("TXT_STEP", $this->lng->txt("step_".$key));
				$this->tpl->setVariable("TXT_STATUS",$status);
				$this->tpl->setVariable("TXT_COMMENT",$val["comment"]);
				$this->tpl->parseCurrentBlock();
			}
		}
	}
	
	/**
	 * determine display mode and load according html layout 
	 * 
	 * @param    string  set title for display mode 'setup' 
	 */
	function checkDisplayMode($a_title = "")
	{
		switch ($this->display_mode)
		{
			case "view":
				$this->tpl->addBlockFile("CONTENT","content","tpl.clientview.html");
				// display tabs
				include "./include/inc.client_tabs.php";
				$client_name = ($this->client->getName()) ? $this->client->getName() : $this->lng->txt("no_client_name");
				$this->tpl->setVariable("TXT_HEADER",$client_name." (".$this->lng->txt("client_id").": ".$this->client->getId().")");       
				break;
			
			case "setup":
				$this->tpl->addBlockFile("CONTENT","content","tpl.clientsetup.html");
				$this->tpl->setVariable("TXT_HEADER",$this->lng->txt($a_title));        
				break;

			default:
				$this->displayError($this->lng->txt("unknown_display_mode"));
				exit();
				break;
		}
	}

	/**
	 * determine display mode and load correct panel
	 */
	function checkPanelMode()
	{
		switch ($this->display_mode)
		{
			case "view":
				$this->displayStatusPanel();                
				break;
			
			case "setup":
				$this->displayProcessPanel();
				break;
		}
	}

	/**
	 * display intro page for the first client installation
	 */
	function displayStartup()
	{
		$this->tpl->addBlockFile("CONTENT","content","tpl.clientsetup.html");
		
		$this->tpl->setVariable("TXT_INFO",$this->lng->txt("info_text_first_client"));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("setup_first_client"));
		
		$this->displayProcessPanel();
		
		$this->setButtonNext("ini");
	}

	/**
	 * display database form and process form input
	 */
	function displayDatabase()
	{
		global $ilErr,$ilDB,$ilLog;

		$this->checkDisplayMode("setup_database");

		// checkings
		if ($_POST["form"]["db_flag"] == 1)
		{
			$message = "";
			
			if (!$this->client->db_installed)
			{
				if (!$this->client->db_exists)
				{
					if ($_POST["form"]["chk_db_create"])
					{
						if (!$this->createDatabase($_POST["collation"]))
						{
							$message = $this->lng->txt($this->getError());
							$this->raiseError($message,$this->error_obj->MESSAGE);
						}
					}
					else
					{
						$message = $this->lng->txt("database_not_exists_create_first");
						$this->raiseError($message,$this->error_obj->MESSAGE);                  
					}
				}
				
				if (!$this->installDatabase())
				{
					$message = $this->lng->txt($this->getError());
					$this->client->status["db"]["status"] = false;
					$this->client->status["db"]["comment"] = "install_error";
				}
				else
				{
					$message = $this->lng->txt("database_installed");
				}
			}
			else
			{
				include_once "../classes/class.ilDBUpdate.php";
				include_once "../Services/AccessControl/classes/class.ilRbacAdmin.php";
				include_once "../Services/AccessControl/classes/class.ilRbacReview.php";
				include_once "../Services/AccessControl/classes/class.ilRbacSystem.php";
				include_once "../Services/Tree/classes/class.ilTree.php";
				include_once "../classes/class.ilSaxParser.php";
				include_once "../Services/Object/classes/class.ilObjectDefinition.php";

				// referencing db handler in language class
				$this->lng->setDbHandler($this->client->db);

				// referencing does not work in dbupdate-script
				$ilDB = new ilDbx($this->client->dsn);

				// run dbupdate
				$dbupdate = new ilDBUpdate($ilDB);
				$dbupdate->applyUpdate();
			
				if ($dbupdate->updateMsg == "no_changes")
				{
					$message = $this->lng->txt("no_changes").". ".$this->lng->txt("database_is_uptodate");
				}
				else
				{
					foreach ($dbupdate->updateMsg as $row)
					{
						$message .= $this->lng->txt($row["msg"]).": ".$row["nr"]."<br/>";
					}
				}
			}
		}

		ilUtil::sendInfo($message);

		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_db.html");
		
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("DB_HOST", $this->client->getDbHost());
		$this->tpl->setVariable("DB_NAME", $this->client->getDbName());     
		$this->tpl->setVariable("DB_USER", $this->client->getDbUser());     
		$this->tpl->setVariable("DB_PASS", $this->client->getDbPass());

		if ($this->client->db_installed)
		{
			// referencing db handler in language class
			$this->lng->setDbHandler($this->client->db);

			include_once "../classes/class.ilDBUpdate.php";
			$ilDB = new ilDbx($this->client->dsn);
			$dbupdate = new ilDBUpdate($ilDB);

			if (!$db_status = $dbupdate->getDBVersionStatus())
			{
				$remark = "<font color=\"red\">".$this->lng->txt("database_needs_update").
								  " (".$this->lng->txt("database_version").": ".$dbupdate->currentVersion.
								  " ; ".$this->lng->txt("file_version").": ".$dbupdate->fileVersion.")</font>";
				$this->tpl->setVariable("TXT_INFO", $remark);
				
				$this->tpl->setCurrentBlock("btn_submit");
				$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("database_update"));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setVariable("TXT_INFO",$this->lng->txt("database_is_uptodate"));
				$this->client->status["db"]["status"] = true;
				$this->client->status["db"]["comment"] = "version ".$dbupdate->getCurrentVersion();
			}
			
			$this->tpl->setVariable("TXT_DB_VERSION", $this->lng->txt("version"));
			$this->tpl->setVariable("VAL_DB_VERSION", $ilDB->getMySQLVersion());
			$this->tpl->setVariable("TXT_DB_MODE", $this->lng->txt("ilias_db_mode"));
			
			if ($ilDB->isMySQL4_1OrHigher())
			{
				$this->tpl->setVariable("VAL_DB_MODE", $this->lng->txt("mysql_4_1_x_or_higher_mode"));
			}
			else
			{
				$this->tpl->setVariable("VAL_DB_MODE", $this->lng->txt("mysql_4_0_x_or_lower_mode"));
			}
			$this->tpl->setVariable("TXT_CHECK_VERSIONS", $this->lng->txt("check_db_versions"));
		}
		else
		{
			$checked = "";

			if ($_SESSION["error_post_vars"]["form"]["chk_db_create"])
			{
				$checked = "checked=\"checked\"";
			}

			$this->tpl->setCurrentBlock("option_db_create");
			$this->tpl->setVariable("TXT_DB_CREATE", $this->lng->txt("database_create"));
			$this->tpl->setVariable("DB_CREATE_CHECK",$checked);
			$this->tpl->parseCurrentBlock();

			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("database_install"));
			$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_db"));
			
			// output version
			$ilDB = new ilDbx($this->client->dsn_host);
			$this->tpl->setVariable("TXT_DB_VERSION", $this->lng->txt("version"));
			$this->tpl->setVariable("VAL_DB_VERSION", $ilDB->getMySQLVersion());
			$this->tpl->setVariable("TXT_DB_MODE", $this->lng->txt("ilias_db_mode"));
			
			if ($ilDB->isMySQL4_1OrHigher())
			{
				$this->tpl->setVariable("VAL_DB_MODE", $this->lng->txt("mysql_4_1_x_or_higher_mode"));
			}
			else
			{
				$this->tpl->setVariable("VAL_DB_MODE", $this->lng->txt("mysql_4_0_x_or_lower_mode"));
			}
			$this->tpl->setVariable("TXT_CHECK_VERSIONS", $this->lng->txt("check_db_versions"));
			
			// collation selection ( see utf8 collations at
			// http://dev.mysql.com/doc/mysql/en/charset-unicode-sets.html )
			if ($ilDB->isMySQL4_1OrHigher())
			{
				$collations = array
				(
					"utf8_unicode_ci",
					"utf8_general_ci",
					"utf8_czech_ci",
					"utf8_danish_ci",
					"utf8_estonian_ci",
					"utf8_icelandic_ci",
					"utf8_latvian_ci",
					"utf8_lithuanian_ci",
					"utf8_persian_ci",
					"utf8_polish_ci",
					"utf8_roman_ci",
					"utf8_romanian_ci",
					"utf8_slovak_ci",
					"utf8_slovenian_ci",
					"utf8_spanish2_ci",
					"utf8_spanish_ci",
					"utf8_swedish_ci",
					"utf8_turkish_ci"
				);
				foreach($collations as $collation)
				{
					$this->tpl->setCurrentBlock("collation_item");
					$this->tpl->setVariable("VAL_COLLATION_ITEM", $collation);
					$this->tpl->setVariable("TXT_COLLATION_ITEM", $collation);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("collation_selection");
				$this->tpl->setVariable("TXT_COLLATION", $this->lng->txt("collation"));
				$this->tpl->parseCurrentBlock();
				//$this->tpl->setCurrentBlock("setup_content");
				$this->tpl->setVariable("COLLATION_INFO1", $this->lng->txt("info_text_db_collation1"));
				$this->tpl->setVariable("COLLATION_EXAMPLE",
					"<br /><br />".$this->lng->txt("example").": CREATE DATABASE ilias3 CHARACTER SET utf8 COLLATE utf8_unicode_ci");
				$this->tpl->setVariable("COLLATION_INFO2", "<br /><br />".$this->lng->txt("info_text_db_collation2")." ".
					"<a target=\"_new\" href=\"http://dev.mysql.com/doc/mysql/en/charset-unicode-sets.html\">".
					" MySQL Reference Manual :: 10.11.1 Unicode Character Sets</a>");
			}
		}
		
		$this->tpl->parseCurrentBlock();
		
		$this->tpl->setVariable("TXT_SETUP_TITLE", $this->lng->txt("setup_database"));
		$this->tpl->setVariable("TXT_DB_HEADER", $this->lng->txt("db_conn"));
		$this->tpl->setVariable("TXT_DB_TYPE", $this->lng->txt("db_type"));
		$this->tpl->setVariable("TXT_DB_HOST", $this->lng->txt("db_host"));
		$this->tpl->setVariable("TXT_DB_NAME", $this->lng->txt("db_name")); 
		$this->tpl->setVariable("TXT_DB_USER", $this->lng->txt("db_user"));
		$this->tpl->setVariable("TXT_DB_PASS", $this->lng->txt("db_pass"));
		
		// only allow to return to ini if db does not exist yet
		if (!$this->client->db_installed)
		{
			$this->setButtonPrev("ini");
		}
		
		if ($this->client->db_installed and $db_status)
		{
			$this->setButtonNext("lang");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	 * display language form and process form input
	 */
	function displayLanguages()
	{
		$this->checkDisplayMode("setup_languages");

		if (!$this->client->db_installed)
		{
			// program should never come to this place
			$message = "No database found! Please install database first.";
			ilUtil::sendInfo($message);
		}
	
		// checkings
		if ($_POST["form"])
		{
			if (empty($_POST["form"]["lang_id"]))
			{
				$message = $this->lng->txt("lang_min_one_language");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}
			
			if (!in_array($_POST["form"]["lang_default"],$_POST["form"]["lang_id"]))
			{
				$message = $this->lng->txt("lang_not_installed_default");
				$this->error = true;
				$this->raiseError($message,$this->error_obj->MESSAGE);

			}
			
			$result = $this->lng->installLanguages($_POST["form"]["lang_id"], $_POST["form"]["lang_local"]);
			
			if (is_array($result))
			{
				$count = count($result);
				$txt = "tet";
				
				foreach ($result as $key => $lang_key)
				{
					$list .= $this->lng->txt("lang_".$lang_key);
					
					if ($count > $key + 1)
					{
						$list .= ", ";
					}
				}
			}

			$this->client->setDefaultLanguage($_POST["form"]["lang_default"]);
			$message = $this->lng->txt("languages_installed");
			
			if ($result !== true)
			{
				$message .= "<br/>(".$this->lng->txt("langs_not_valid_not_installed").": ".$list.")";
			}
			ilUtil::sendInfo($message);
		}

		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_lang.html");

		$languages = $this->lng->getInstallableLanguages();
		$installed_langs = $this->lng->getInstalledLanguages();
		$installed_local_langs = $this->lng->getInstalledLocalLanguages();
		$local_langs = $this->lng->getLocalLanguages();
		$default_lang = $this->client->getDefaultLanguage();
		
		$lang_count = count($installed_langs);
		
		$this->tpl->setVariable("TXT_LANG_HEADER", ucwords($this->lng->txt("available_languages")));
		$this->tpl->setVariable("TXT_LANGUAGE", ucwords($this->lng->txt("language")));
		$this->tpl->setVariable("TXT_INSTALLED", ucwords($this->lng->txt("installed")));
		$this->tpl->setVariable("TXT_INCLUDE_LOCAL", ucwords($this->lng->txt("include_local")));
		$this->tpl->setVariable("TXT_DEFAULT", ucwords($this->lng->txt("default")));

		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		
		$this->tpl->setVariable("TXT_SETUP_TITLE",ucfirst(trim($this->lng->txt("setup_languages"))));
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_lang"));
		
		if ($lang_count > 0)
		{
			$this->client->status["lang"]["status"] = true;
			$this->client->status["lang"]["comment"] = $lang_count." ".$this->lng->txt("languages_installed");
		}
		else
		{
			$this->client->status["lang"]["status"] = false;
			$this->client->status["lang"]["comment"] = $this->lng->txt("lang_none_installed");
		}

		foreach ($languages as $lang_key)
		{
			$this->tpl->setCurrentBlock("language_row");
			$this->tpl->setVariable("LANG_KEY", $lang_key);
			$this->tpl->setVariable("TXT_LANG", $this->lng->txt("lang_".$lang_key));
			$this->tpl->setVariable("BORDER", 0);
			$this->tpl->setVariable("VSPACE", 0);

			if (in_array($lang_key,$installed_langs))
			{
				$this->tpl->setVariable("CHECKED", ("checked=\"checked\""));
			}

			if (!in_array($lang_key,$local_langs))
			{
				$this->tpl->setVariable("LOCAL", ("disabled=\"disabled\""));        
			}
			else if (in_array($lang_key,$installed_local_langs))
			{
				$this->tpl->setVariable("LOCAL", ("checked=\"checked\""));
			}

			if ($lang_key == $default_lang)
			{
				$this->tpl->setVariable("DEFAULT", ("checked=\"checked\""));
			}

			$this->tpl->parseCurrentBlock();
		}
		
		$this->setButtonPrev("db");
		
		if ($lang_count > 0)
		{
			$this->setButtonNext("contact");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	 * display contact data form and process form input
	 */
	function displayContactData()
	{
		$this->checkDisplayMode("setup_contact_data");
	
		$settings = $this->client->getAllSettings();

		// formular sent
		if ($_POST["form"])
		{
			// init checking var
			$form_valid = true;

			// check required fields
			if (empty($_POST["form"]["admin_firstname"]) or empty($_POST["form"]["admin_lastname"])
				or empty($_POST["form"]["admin_street"]) or empty($_POST["form"]["admin_zipcode"])
				or empty($_POST["form"]["admin_country"]) or empty($_POST["form"]["admin_city"])
				or empty($_POST["form"]["admin_phone"]) or empty($_POST["form"]["admin_email"])
				or empty($_POST["form"]["inst_name"]) or empty($_POST["form"]["inst_info"]))
			{
				$form_valid = false;
				$message = $this->lng->txt("fill_out_required_fields");
				//$this->raiseError($message,$this->error_obj->MESSAGE);
				ilUtil::sendInfo($message);
			}
			
			// admin email
			if (!ilUtil::is_email($_POST["form"]["admin_email"]) and $form_valid)
			{
				$form_valid = false;
				$message = $this->lng->txt("input_error").": '".$this->lng->txt("email")."'<br/>".$this->lng->txt("email_not_valid");
				ilUtil::sendInfo($message);
				//$this->raiseError($message,$this->error_obj->MESSAGE);
			}

			if (!$form_valid)   //required fields not satisfied. Set formular to already fill in values
			{
				// load user modified settings again
				// contact
				$settings["admin_firstname"] = ilUtil::prepareFormOutput($_POST["form"]["admin_firstname"],true);
				$settings["admin_lastname"] = ilUtil::prepareFormOutput($_POST["form"]["admin_lastname"],true);
				$settings["admin_title"] = ilUtil::prepareFormOutput($_POST["form"]["admin_title"],true);
				$settings["admin_position"] = ilUtil::prepareFormOutput($_POST["form"]["admin_position"],true);
				$settings["admin_institution"] = ilUtil::prepareFormOutput($_POST["form"]["admin_institution"],true);
				$settings["admin_street"] = ilUtil::prepareFormOutput($_POST["form"]["admin_street"],true);
				$settings["admin_zipcode"] = ilUtil::prepareFormOutput($_POST["form"]["admin_zipcode"],true);
				$settings["admin_city"] = ilUtil::prepareFormOutput($_POST["form"]["admin_city"],true);
				$settings["admin_country"] = ilUtil::prepareFormOutput($_POST["form"]["admin_country"],true);
				$settings["admin_phone"] = ilUtil::prepareFormOutput($_POST["form"]["admin_phone"],true);
				$settings["admin_email"] = ilUtil::prepareFormOutput($_POST["form"]["admin_email"],true);

				// client
				$settings["inst_name"] = ilUtil::prepareFormOutput($_POST["form"]["inst_name"],true);
				$settings["inst_info"] = ilUtil::prepareFormOutput($_POST["form"]["inst_info"],true);
				$settings["inst_institution"] = ilUtil::prepareFormOutput($_POST["form"]["inst_institution"],true);
			}
			else // all required fields ok
			{

				// write new settings
				// contact
				$this->client->setSetting("admin_firstname",ilUtil::stripSlashes($_POST["form"]["admin_firstname"]));
				$this->client->setSetting("admin_lastname",ilUtil::stripSlashes($_POST["form"]["admin_lastname"]));
				$this->client->setSetting("admin_title",ilUtil::stripSlashes($_POST["form"]["admin_title"]));
				$this->client->setSetting("admin_position",ilUtil::stripSlashes($_POST["form"]["admin_position"]));
				$this->client->setSetting("admin_institution",ilUtil::stripSlashes($_POST["form"]["admin_institution"]));
				$this->client->setSetting("admin_street",ilUtil::stripSlashes($_POST["form"]["admin_street"]));
				$this->client->setSetting("admin_zipcode",ilUtil::stripSlashes($_POST["form"]["admin_zipcode"]));
				$this->client->setSetting("admin_city",ilUtil::stripSlashes($_POST["form"]["admin_city"]));
				$this->client->setSetting("admin_country",ilUtil::stripSlashes($_POST["form"]["admin_country"]));
				$this->client->setSetting("admin_phone",ilUtil::stripSlashes($_POST["form"]["admin_phone"]));
				$this->client->setSetting("admin_email",ilUtil::stripSlashes($_POST["form"]["admin_email"]));
				$this->client->setSetting("inst_institution",ilUtil::stripSlashes($_POST["form"]["inst_institution"]));
				$this->client->setSetting("inst_name",ilUtil::stripSlashes($_POST["form"]["inst_name"]));

				// update client.ini
				$this->client->setName(ilUtil::stripSlashes($_POST["form"]["inst_name"]));
				$this->client->setDescription(ilUtil::stripSlashes($_POST["form"]["inst_info"]));
				$this->client->ini->write();

				// reload settings
				$settings = $this->client->getAllSettings();
				// feedback
				ilUtil::sendInfo($this->lng->txt("saved_successfully"));
			}
		}

		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_contact.html");

		// client values
		$this->tpl->setVariable("INST_NAME",ilUtil::prepareFormOutput(($this->client->getName()) ? $this->client->getName() : $this->client->getId()));
		$this->tpl->setVariable("INST_INFO",ilUtil::prepareFormOutput($this->client->getDescription()));
		$this->tpl->setVariable("INST_INSTITUTION",ilUtil::prepareFormOutput($settings["inst_institution"]));

		// contact values
		$this->tpl->setVariable("ADMIN_FIRSTNAME",ilUtil::prepareFormOutput($settings["admin_firstname"]));
		$this->tpl->setVariable("ADMIN_LASTNAME",ilUtil::prepareFormOutput($settings["admin_lastname"]));
		$this->tpl->setVariable("ADMIN_TITLE",ilUtil::prepareFormOutput($settings["admin_title"]));
		$this->tpl->setVariable("ADMIN_POSITION",ilUtil::prepareFormOutput($settings["admin_position"]));
		$this->tpl->setVariable("ADMIN_INSTITUTION",ilUtil::prepareFormOutput($settings["admin_institution"]));
		$this->tpl->setVariable("ADMIN_STREET",ilUtil::prepareFormOutput($settings["admin_street"]));
		$this->tpl->setVariable("ADMIN_ZIPCODE",ilUtil::prepareFormOutput($settings["admin_zipcode"]));
		$this->tpl->setVariable("ADMIN_CITY",ilUtil::prepareFormOutput($settings["admin_city"]));
		$this->tpl->setVariable("ADMIN_COUNTRY",ilUtil::prepareFormOutput($settings["admin_country"]));
		$this->tpl->setVariable("ADMIN_PHONE",ilUtil::prepareFormOutput($settings["admin_phone"]));
		$this->tpl->setVariable("ADMIN_EMAIL",ilUtil::prepareFormOutput($settings["admin_email"]));
		
		// client text
		$this->tpl->setVariable("TXT_INST_DATA", $this->lng->txt("client_data"));
		$this->tpl->setVariable("TXT_INST_NAME", $this->lng->txt("client_name"));
		$this->tpl->setVariable("TXT_INST_INFO", $this->lng->txt("client_info"));
		$this->tpl->setVariable("TXT_INST_INSTITUTION", $this->lng->txt("client_institution"));

		// contact text
		$this->tpl->setVariable("TXT_CONTACT_DATA", $this->lng->txt("contact_data"));
		$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_FIRSTNAME", $this->lng->txt("firstname"));
		$this->tpl->setVariable("TXT_LASTNAME", $this->lng->txt("lastname"));
		$this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
		$this->tpl->setVariable("TXT_POSITION", $this->lng->txt("position"));
		$this->tpl->setVariable("TXT_INSTITUTION", $this->lng->txt("institution"));
		$this->tpl->setVariable("TXT_STREET", $this->lng->txt("street"));
		$this->tpl->setVariable("TXT_ZIPCODE", $this->lng->txt("zipcode"));
		$this->tpl->setVariable("TXT_CITY", $this->lng->txt("city"));
		$this->tpl->setVariable("TXT_COUNTRY", $this->lng->txt("country"));
		$this->tpl->setVariable("TXT_PHONE", $this->lng->txt("phone"));
		$this->tpl->setVariable("TXT_EMAIL", $this->lng->txt("email"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_SETUP_TITLE","contact information & client data");
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_contact"));
		
		$this->setButtonPrev("lang");
		
		$check = $this->checkClientContact($this->client);

		$this->client->status["contact"]["status"] = $check["status"];
		$this->client->status["contact"]["comment"] = $check["comment"];

		if ($check["status"])
		{
			$this->setButtonNext("nic");
		}
		
		$this->checkPanelMode();
	}

	/**
	 * display nic registration form and process form input
	 */
	function displayNIC()
	{
		$this->checkDisplayMode("nic_registration");
		$settings = $this->client->getAllSettings();
		$nic_key = $this->client->getNICkey();
		
		// formular sent
		if ($_POST["form"])
		{
			// check register option
			if ($_POST["form"]["register"] == 1)
			{
				// update nic
				$this->client->updateNIC($this->ilias_nic_server);
				
				// online registration failed
				if (empty($this->client->nic_status[2]))
				{
					$this->client->setSetting("nic_enabled","-1");
					$message = $this->lng->txt("nic_reg_failed");               
				}
				else
				{
					$this->client->setSetting("inst_id",$this->client->nic_status[2]);
					$this->client->setSetting("nic_enabled","1");
					$this->client->status["nic"]["status"] = true;
					$message = $this->lng->txt("nic_reg_enabled");      
				}
			}
			elseif ($_POST["form"]["register"] == 2)
			{
				$nic_by_email = (int) $_POST["form"]["nic_id"];
				
				$checksum = md5($nic_key.$nic_by_email);
				
				if (!$nic_by_email or $_POST["form"]["nic_checksum"] != $checksum)
				{
					$message = $this->lng->txt("nic_reg_enter_correct_id");     
				}
				else
				{
					$this->client->setSetting("inst_id",$nic_by_email);
					$this->client->setSetting("nic_enabled","1");
					$message = $this->lng->txt("nic_reg_enabled");      
				}
			}
			else
			{
				$this->client->setSetting("inst_id","0");
				$this->client->setSetting("nic_enabled","0");
				$message = $this->lng->txt("nic_reg_disabled");
			}

			ilUtil::sendInfo($message);
		}
		
		// reload settings
		$settings = $this->client->getAllSettings();
		
		if ($settings["nic_enabled"] == "1" && $settings["inst_id"] > 0)
		{
			$this->tpl->setVariable("TXT_INFO",$this->lng->txt("info_text_nic3")." ".$settings["inst_id"].".");
		}
		else
		{
			// reload settings
			$settings = $this->client->getAllSettings();
			
			$email_subject = rawurlencode("NIC registration request");
			$email_body = base64_encode($this->client->getURLStringForNIC($this->ilias_nic_server));
			$email_link = "<a href=\"mailto:ilias-nic@uni-koeln.de?subject=".$email_subject."&body=".$email_body."\">".$this->lng->txt("email")."</a>";

			$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_nic1")." ".$email_link." ".$this->lng->txt("info_text_nic2"));

			// output
			$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_nic.html");
	
			$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	
			// register form
			$this->tpl->setVariable("TXT_NIC_ENTER_ID",$this->lng->txt("nic_reg_enter_id"));
			$this->tpl->setVariable("TXT_NIC_ENTER_CHECKSUM",$this->lng->txt("nic_reg_enter_checksum"));
			$this->tpl->setVariable("TXT_NIC_REGISTER",$this->lng->txt("nic_registration"));
			$this->tpl->setVariable("TXT_NIC_ENABLE",$this->lng->txt("nic_reg_online"));
			$this->tpl->setVariable("TXT_NIC_EMAIL",$this->lng->txt("nic_reg_email"));
			$this->tpl->setVariable("TXT_NIC_DISABLE",$this->lng->txt("nic_reg_disable")." <span class=\"subtitle\">".$this->lng->txt("nic_reg_disable_comment")."</span>");
	
			$checked = "checked=\"checked\"";
			
			if (!isset($settings["nic_enabled"]) or $settings["nic_enabled"] == "1")
			{
				$this->tpl->setVariable("ENABLED",$checked);
			}
			elseif ($settings["nic_enabled"] == "2")
			{
				$this->tpl->setVariable("EMAIL",$checked);
			}
			else
			{
				$this->tpl->setVariable("DISABLED",$checked);
			}
	
			if (isset($settings["nic_enabled"]))
			{
				$this->client->status["nic"]["status"] = true;
			}
		}

		$this->setButtonPrev("contact");
		
		if ($this->client->status["nic"]["status"])
		{
			$this->setButtonNext("finish","finish");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	 * display tools
	 */
	function displayTools()
	{
		$this->checkDisplayMode();
		
		// output
		ilUtil::sendInfo();
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_tools.html");
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_TOOLS", $this->lng->txt("tools"));
		$this->tpl->setVariable("TXT_CTRL_STRUCTURE", $this->lng->txt("ctrl_structure"));
		$this->tpl->setVariable("TXT_RELOAD", $this->lng->txt("reload"));

		$q = "SELECT count(*) as cnt FROM ctrl_calls";
		$cset = $this->client->db->query($q);
		$crec = $cset->fetchRow(DB_FETCHMODE_ASSOC);
		if ($crec["cnt"] == 0)
		{
			$this->tpl->setVariable("TXT_CTRL_STRUCTURE_DESC",
				$this->lng->txt("ctrl_missing_desc"));
		}
		else
		{
			$this->tpl->setVariable("TXT_CTRL_STRUCTURE_DESC",
				$this->lng->txt("ctrl_structure_desc"));
		}

		$this->tpl->parseCurrentBlock();
		
		//$this->checkPanelMode();
	}

	/**
	* reload control structure
	*/
	function reloadControlStructure()
	{
		global $ilCtrlStructureReader;
		
		if (!$this->client->db_installed)
		{
			ilUtil::sendInfo($this->lng->txt("no_db"), true);
			$this->displayTools();
			return;
		}

		// referencing does not work in dbupdate-script
		$GLOBALS["ilDB"] = new ilDbx($this->client->dsn);
// BEGIN WebDAV
		// read module and service information into db
		require_once "./classes/class.ilModuleReader.php";
		require_once "./classes/class.ilServiceReader.php";
		require_once "./classes/class.ilCtrlStructureReader.php";

		chdir("..");
		require_once "./Services/Component/classes/class.ilModule.php";
		require_once "./Services/Component/classes/class.ilService.php";
		$modules = ilModule::getAvailableCoreModules();
		$services = ilService::getAvailableCoreServices();
		chdir("./setup");

		ilModuleReader::clearTables();
		foreach($modules as $module)
		{
			$mr = new ilModuleReader(ILIAS_ABSOLUTE_PATH."/Modules/".$module["subdir"]."/module.xml",
				$module["subdir"], "Modules");
			$mr->getModules();
			unset($mr);
		}

		ilServiceReader::clearTables();
		foreach($services as $service)
		{
			$sr = new ilServiceReader(ILIAS_ABSOLUTE_PATH."/Services/".$service["subdir"]."/service.xml",
				$service["subdir"], "Services");
			$sr->getServices();
			unset($sr);
		}
// END WebDAV

		$ilCtrlStructureReader->readStructure(true);
		ilUtil::sendInfo($this->lng->txt("ctrl_structure_reloaded"), true);
		$this->displayTools();
	}


	
	/**
	 * display change password form and process form input
	 */
	function changeMasterPassword()
	{
		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html");
		
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_password"));

		// formular sent
		if ($_POST["form"])
		{
			$pass_old = $this->getPassword();

			if (empty($_POST["form"]["pass_old"]))
			{
				$message = $this->lng->txt("password_enter_old");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}
				
			if (md5($_POST["form"]["pass_old"]) != $pass_old)
			{
				$message = $this->lng->txt("password_old_wrong");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}
			
			if (empty($_POST["form"]["pass"]))
			{
				$message = $this->lng->txt("password_empty");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}
			
			if ($_POST["form"]["pass"] != $_POST["form"]["pass2"])
			{
				$message = $this->lng->txt("password_not_match");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}
			
			if (md5($_POST["form"]["pass"]) == $pass_old)
			{
				$message = $this->lng->txt("password_same");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}
			
			if (!$this->setPassword($_POST["form"]["pass"]))
			{
				$message = $this->lng->txt("save_error");
				$this->raiseError($message,$this->error_obj->MESSAGE);
			}

			ilUtil::sendInfo($this->lng->txt("password_changed"),true);
			ilUtil::redirect("setup.php");
		}
		
		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.form_change_admin_password.html");

		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("password_new_master"));

		// pass form
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_REQUIRED_FIELDS", $this->lng->txt("required_field"));
		$this->tpl->setVariable("TXT_PASS_TITLE",$this->lng->txt("change_password"));
		$this->tpl->setVariable("TXT_PASS_OLD",$this->lng->txt("set_oldpasswd"));
		$this->tpl->setVariable("TXT_PASS",$this->lng->txt("set_newpasswd"));
		$this->tpl->setVariable("TXT_PASS2",$this->lng->txt("password_retype"));
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
	}

	/**
	 * display finish setup page
	 */
	function displayFinishSetup()
	{
		$this->checkDisplayMode("finish_setup");

//echo "<b>1</b>";
		if ($this->validateSetup())
		{
			$txt_info = $this->lng->txt("info_text_finish1");
			$this->setButtonNext("login_new","login");
//echo "<b>2</b>";
			$this->client->reconnect();		// if this is not done, the writing of
											// the setup_ok fails (with MDB2 and a larger
											// client list), alex 17.1.2008
			$this->client->setSetting("setup_ok",1);
//$this->client->setSetting("zzz", "Z");
//echo "<b>3</b>";
			$this->client->status["finish"]["status"] = true;
//echo "<b>4</b>";
		}
		else
		{
			$txt_info = $this->lng->txt("info_text_finish2");
		}
		
//echo "<b>5</b>";
		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_finish.html");
		$this->tpl->setVariable("TXT_INFO",$txt_info);
		
		$this->setButtonPrev("nic");
//echo "<b>6</b>";
		$this->checkPanelMode();
//echo "<b>7</b>";
	}
	
	/**
	 * display delete client confirmation form and process form input
	 */
	function displayDeleteConfirmation()
	{
		$this->checkDisplayMode();

		// formular sent
		if ($_POST["form"]["delete"])
		{
			$ini = true;
			$db = false;
			$files = false;
		
			/* disabled
			switch ($_POST["form"]["delete"])
			{
				case 1:
					$ini = true;
					break;
			
				case 2:
					$ini = true;
					$db = true;
					break;

				case 3:
					$ini = true;
					$db = true;
					$files = true;
					break;      
			}
			*/
			
			$msg = $this->client->delete($ini,$db,$files);

			ilUtil::sendInfo($this->lng->txt("client_deleted"),true);
			ilUtil::redirect("setup.php");
		}

		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_delete"));
		
		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.form_delete_client.html");

		// delete panel
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
		$this->tpl->setVariable("TXT_DELETE_CONFIRM", $this->lng->txt("delete_confirm"));
		$this->tpl->setVariable("TXT_DELETE_INFO", $this->lng->txt("delete_info"));

		$this->checkPanelMode();
	}
	
	/**
	 * enable/disable access to a client
	 * 
	 * @param    string  jump back to this script
	 */
	function changeAccessMode($a_back)
	{
		if ($this->client->status["finish"]["status"])
		{
			$val = ($this->client->ini->readVariable("client","access")) ? "0" : true;
			$this->client->ini->setVariable("client","access",$val);
			$this->client->ini->write();
			$message = "client_access_mode_changed";
		}
		else
		{
			$message = "client_setup_not_finished";
		}
		
		ilUtil::sendInfo($this->lng->txt($message),true);
		
		ilUtil::redirect("setup.php?cmd=".$a_back);
	}
	
	/**
	 * set defualt client
	 */
	function changeDefaultClient()
	{
		if ($_POST["form"])
		{
			$client = new ilClient($_POST["form"]["default"], $this->db_connections);

			if (!$client->init())
			{
				$this->raiseError($this->lng->txt("no_valid_client_id"),$this->error_obj->MESSAGE);
			}
			
			$status = $this->getStatus($client);
		
			if ($status["finish"]["status"])
			{
				$this->ini->setVariable("clients","default",$client->getId());
				$this->ini->write();
				$message = "default_client_changed";
			}
			else
			{
				$message = "client_setup_not_finished";
			}
		}
		
		ilUtil::sendInfo($this->lng->txt($message),true);
		
		ilUtil::redirect("setup.php");
	}

	/**
	 * validatesetup status again
	 * and set access mode of the first client to online
	 */
	function validateSetup()
	{
		foreach ($this->client->status as $key => $val)
		{
			if ($key != "finish" and $key != "access")
			{
				if ($val["status"] != true)
				{
					return false;
				}
			}
		}
		
//$this->client->setSetting("zzz", "V");
		$clientlist = new ilClientList($this->db_connections);
//$this->client->setSetting("zzz", "W");
		$list = $clientlist->getClients();
//$this->client->setSetting("zzz", "X");
		if (count($list) == 1)
		{
			$this->ini->setVariable("clients","default",$this->client->getId());
			$this->ini->write();

			$this->client->ini->setVariable("client","access",1);
			$this->client->ini->write();
		}
//$this->client->setSetting("zzz", "Y");
		return true;
	}
	
	/**
	 * if setting up a client was not finished, jump back to the first uncompleted setup step
	 */
	function jumpToFirstUnfinishedSetupStep()
	{
		if (!$this->client->status["db"]["status"])
		{
			$this->cmd = "db";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayDatabase();
		}
		elseif (!$this->client->status["lang"]["status"])
		{
			$this->cmd = "lang";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayLanguages();      
		}
		elseif (!$this->client->status["contact"]["status"])
		{
			$this->cmd = "contact";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayContactData();        
		}
		elseif (!$this->client->status["nic"]["status"])
		{
			$this->cmd = "nic";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayNIC();        
		}
		elseif (!$this->client->status["finish"]["status"])
		{
			$this->cmd = "finish";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayFinishSetup();        
		}
		else
		{
			return false;
		}
	}

	/**
	 * enable/disable client list on index page
	 */
	function toggleClientList()
	{
		if ($this->ini->readVariable("clients","list"))
		{
			$this->ini->setVariable("clients","list","0");
			$this->ini->write();
			ilUtil::sendInfo($this->lng->txt("list_disabled"),true);
		}
		else
		{
			$this->ini->setVariable("clients","list","1");
			$this->ini->write();
			ilUtil::sendInfo($this->lng->txt("list_enabled"),true);             
		}
		
		ilUtil::redirect("setup.php");
	}
	
	/**
	* FORM: Init form.
	*
	* @param        int        $a_mode        Form Edit Mode ("edit")
	*/
	/*
	public function initBasicSettingsForm($a_mode = "edit")
	{
		global $lng;
		
		chdir ("..");
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		chdir ("./setup");
		
		$this->form_gui = new ilPropertyFormGUI();
		
		$this->form_gui->setTitle($this->lng->txt("change_basic_settings"));
		
		// Property Title
		$text_input = new ilTextInputGUI($lng->txt("block_feed_block_title"), "block_title");
		$text_input->setInfo("");
		$text_input->setRequired(true);
		$text_input->setMaxLength(200);
		$this->form_gui->addItem($text_input);
		
		// Property FeedUrl
		$text_input = new ilTextInputGUI($lng->txt("block_feed_block_feed_url"), "block_feed_url");
		$text_input->setInfo($lng->txt("block_feed_block_feed_url_info"));
		$text_input->setRequired(true);
		$text_input->setMaxLength(250);
		$this->form_gui->addItem($text_input);
		
		
		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form_gui->addCommandButton("save", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelSave", $lng->txt("cancel"));
		}
		else
		{
			$this->form_gui->addCommandButton("update", $lng->txt("save"));
			$this->form_gui->addCommandButton("cancelUpdate", $lng->txt("cancel"));
		}
		
		$this->form_gui->setTitle($lng->txt("block_feed_block_head"));
		$this->form_gui->setFormAction("setup.php?cmd=gateway");
	}*/
	
} // END class.ilSetupGUI
?>
