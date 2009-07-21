<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./setup/classes/class.ilSetup.php";

/**
 * Setup GUI class
 *
 * class to setup ILIAS first and maintain the ini-settings and the database
 *
 * @author   Sascha Hofmann <shofmann@databay.de>
 * @version  $Id$
 */
class ilSetupGUI
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

		$this->tpl = $tpl;
		$this->lng = $lng;

		// CVS - REVISION - DO NOT MODIFY
		$this->revision = '$Revision$';
		$this->version = "2 ".substr(substr($this->revision,1),0,-2);
		$this->lang = $this->lng->lang_key;

		// init setup
		$this->setup = new ilSetup($_SESSION["auth"],$_SESSION["access_mode"]);

		// init client object if exists
		$client_id = ($_GET["client_id"]) ? $_GET["client_id"] : $_SESSION["ClientId"];
		if ($_POST["client_id"] != "")
		{
			$client_id = $_POST["client_id"];
		}

/*if ($_POST["client_id"] == "")
{		
echo "<br>+".$_GET["client_id"];
echo "<br>+".$_POST["client_id"];
echo "<br>+".$_SESSION["ClientId"];
echo "<br>+".$client_id;
}*/
		// for security
		if (!$this->setup->isAdmin() and $client_id != $_SESSION["ClientId"])
		{
			$client_id = $_SESSION["ClientId"];
		}

		$this->client_id = $client_id;

		$this->setup->ini_client_exists = $this->setup->newClient($client_id);
		$this->setup->getClient()->status = $this->setup->getStatus($client_id);

		// determine command
		if (($this->cmd = $_GET["cmd"]) == "gateway")
		{
			// surpress warning if POST is not set
			@$this->cmd = key($_POST["cmd"]);
		}

		// determine display mode here
		// TODO: depending on previous setting (session)
		// OR switch to 'setup'-mode if someone logs in as client and client's setup wasn't finished (-> entry in settings table does not exist)
		if ($this->setup->isAuthenticated() and !$this->setup->getClient()->status["finish"]["status"] and $this->cmd != "clientlist" and $this->cmd != "")
		{
			$this->setDisplayMode("setup");
		}
		else
		{
			$this->setDisplayMode($_SESSION["display_mode"]);
		}

		// output starts here


		// main cmd handling
		if (!$this->setup->isAuthenticated() or !$this->setup->isInstalled())
		{
			// check for first time installation or migrate an old one first
			if (!$this->setup->isInstalled() or !($this->setup->ini->readVariable("clients","path")))
			{
				$this->cmdInstall();
			}
			else
			{
				if ($this->cmd == "performLogin" || $this->cmd == "performMLogin")
				{
					$cmd = $this->cmd;
					$this->$cmd();
				}
				else
				{
					$this->displayLogin();
				}
			}
		}
		else
		{
			if ($this->setup->isAdmin())
			{
				$this->cmdAdmin();
			}
			else
			{
				$this->cmdClient();
			}
		}

		// display header
		$this->displayHeader();

		if (DEBUG)
		{
			echo "cmd: ".$this->cmd." | access: ".$this->setup->access_mode." | display: ".$this->display_mode;
			var_dump($this->setup->getClient()->status);
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
		$cmd = $this->cmd;
		switch ($this->cmd)
		{
			case NULL:
			case "preliminaries":
				$this->setup->checkPreliminaries();
				$this->displayPreliminaries();
				break;

			case "install":
				$this->displayMasterSetup();
				break;
				
			case "determineToolsPathInstall":
				$this->determineToolsPathInstall();
				break;

			case "saveBasicSettings":
				$this->$cmd();
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
		$cmd = $this->cmd;

		switch ($this->cmd)
		{
			case NULL:
			case "clientlist":
				$this->setDisplayMode("view");
				$this->displayClientList();
				$this->active_tab = "clientlist";
				break;

			case "changepassword":
				$this->setDisplayMode("view");
				$this->changeMasterPassword();
				$this->active_tab = "password";
				break;

			case "mastersettings":
				$this->setDisplayMode("view");
				$this->changeMasterSettings();
				$this->active_tab = "basicsettings";
				break;
				
			case "determineToolsPath":
				$this->setDisplayMode("view");
				$this->determineToolsPath();
				break;

			case "changedefault":
				$this->changeDefaultClient();
				break;  

			case "newclient":
				$this->cmd = "selectdb";
				$this->setDisplayMode("setup");
				$this->setup->ini_client_exists = $this->setup->newClient();
				$this->selectDBType();
				break;  

			case "selectdbtype":
			case "displayIni":
				$this->cmd = "ini";
				$this->setDisplayMode("setup");
				//$this->setup->ini_client_exists = $this->setup->newClient($this->client_id);
				$this->displayIni();
				break;  

			case "startup":
				$this->setDisplayMode("setup");
				$this->setup->ini_client_exists = $this->setup->newClient();
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

			case "preliminaries":
				$this->setup->checkPreliminaries();
				$this->displayPreliminaries();
				$this->active_tab = "preliminaries";
				break;
				
			case "updateBasicSettings":
			case "performLogin":
			case "performMLogin":
				$this->$cmd();
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
		$cmd = $this->cmd;
		switch ($this->cmd)
		{
			case NULL:
			case "view":
				if ($this->setup->getClient()->db_installed)
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
				//if ($this->setup->getClient()->db_installed)
				//{
				//	$this->cmd = "db";
				//	$this->displayDatabase();
				//}
				//else
				//{
					$this->displayIni();
				//}
				break;
				
			case "db":
				$this->displayDatabase();
				break;
	
			case "lang":
				if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "lang" and $this->setup->error === true)
				{
					$this->jumpToFirstUnfinishedSetupStep();
				}
				else
				{
					$this->displayLanguages();
				}
				break;

			case "contact":
				if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "contact")
				{
					$this->jumpToFirstUnfinishedSetupStep();
				}
				else
				{
					$this->displayContactData();
				}
				break;
	
			case "nic":
				if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "nic")
				{
					$this->jumpToFirstUnfinishedSetupStep();
				}
				else
				{
					$this->displayNIC();
				}
				break;
	
			case "finish":
				if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "finish")
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
				ilUtil::redirect(ILIAS_HTTP_PATH."/login.php?client_id=".$this->setup->getClient()->getId());
				break;

			case "login_new":
				if ($this->setup->getClient()->ini->readVariable("client","access") != "1")
				{
					$this->setup->getClient()->ini->setVariable("client","access","1");
					$this->setup->getClient()->ini->write();
				}

				session_destroy();
				ilUtil::redirect(ILIAS_HTTP_PATH."/login.php?client_id=".$this->setup->getClient()->getId());
				break;

			case "tools":
				$this->displayTools();
				break;
				
			case "reloadStructure":
				$this->reloadControlStructure();
				break;

			case "saveClientIni":
			case "installDatabase":
			case "displayDatabase":
			case "updateDatabase":
			case "saveLanguages":
			case "saveContact":
			case "displayContactData":
			case "displayNIC":
			case "saveRegistration":
				$this->$cmd();
				break;

			default:
				$this->displayError($this->lng->txt("unknown_command"));
				break;
		}
	}

	// end cmd subsets 

	////
	//// GENERAL DISPLAY FUNCTIONS
	////
	
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

		if ($this->cmd != "logout" and $this->setup->isInstalled())
		{
			// add client link
			if ($this->setup->isAdmin())
			{
				if ($this->display_mode == "view" or $this->cmd == "clientlist" or $this->cmd == "changepassword" or $this->cmd == "mastersettings")
				{
					$this->tpl->setCurrentBlock("add_client");
					$this->tpl->setVariable("TXT_ADD_CLIENT",ucfirst($this->lng->txt("new_client")));
					$this->tpl->parseCurrentBlock();
				}

				// client list link
				$class = ($this->active_tab == "clientlist")
					? "ilSMActive"
					: "ilSMInactive";				
				$this->tpl->setCurrentBlock("display_list");
				$this->tpl->setVariable("TXT_LIST",ucfirst($this->lng->txt("list_clients")));
				$this->tpl->setVariable("TAB_CLASS", $class);
				$this->tpl->parseCurrentBlock();

				// edit paths link
				$class = ($this->active_tab == "basicsettings")
					? "ilSMActive"
					: "ilSMInactive";				
				$this->tpl->setCurrentBlock("edit_pathes");
				$this->tpl->setVariable("TXT_EDIT_PATHES",$this->lng->txt("basic_settings"));
				$this->tpl->setVariable("TAB_CLASS", $class);
				$this->tpl->parseCurrentBlock();

				// preliminaries
				$class = ($this->active_tab == "preliminaries")
					? "ilSMActive"
					: "ilSMInactive";
				$this->tpl->setCurrentBlock("preliminaries");
				$this->tpl->setVariable("TXT_PRELIMINARIES",$this->lng->txt("preliminaries"));
				$this->tpl->setVariable("TAB_CLASS", $class);
				$this->tpl->parseCurrentBlock();

				// change password link
				$class = ($this->active_tab == "password")
					? "ilSMActive"
					: "ilSMInactive";				
				$this->tpl->setCurrentBlock("change_password");
				$this->tpl->setVariable("TXT_CHANGE_PASSWORD",ucfirst($this->lng->txt("password")));
				$this->tpl->setVariable("TAB_CLASS", $class);
				$this->tpl->parseCurrentBlock();
			}

			// logout link
			if ($this->setup->isAuthenticated())
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
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET","./css/setup.css");
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
			if ($this->setup->ini_ilias_exists and $this->display_mode == "setup" and $this->setup->getClient()->getId() != "")
			{
				$this->tpl->setVariable("TXT_ACCESS_MODE","(".$this->lng->txt("client_id").": ".$this->setup->getClient()->getId().")");
			}
			elseif ($this->setup->isAdmin())
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
		
		$ntpl = new ilTemplate("tpl.navbuttons.html", true, true, "setup");
		//$this->tpl->addBlockFile("NAVBUTTONS","navbuttons","tpl.navbuttons.html", "setup");

		$ntpl->setVariable("FORMACTION_BUTTONS","setup.php?cmd=gateway");

		if ($this->btn_prev_on)
		{
			$ntpl->setCurrentBlock("btn_back");
			$ntpl->setVariable("TXT_PREV", $this->btn_prev_lng);   
			$ntpl->setVariable("CMD_PREV", $this->btn_prev_cmd);   
			$ntpl->parseCurrentBlock();
		}
		
		if ($this->btn_next_on)
		{
			$ntpl->setCurrentBlock("btn_forward");
			$ntpl->setVariable("TXT_NEXT", $this->btn_next_lng);
			$ntpl->setVariable("CMD_NEXT", $this->btn_next_cmd);   
			$ntpl->parseCurrentBlock();
		}
		
		$nav_html = $ntpl->get();
		$this->tpl->setVariable("NAVBUTTONS", $nav_html);
		if (!$this->no_second_nav)
		{
			$this->tpl->setVariable("NAVBUTTONS2", $nav_html);
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
		$this->btn_prev_lng = ($a_lng) ? $this->lng->txt($a_lng) : $this->lng->txt("prev");
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
		$this->btn_next_lng = ($a_lng) ? $this->lng->txt($a_lng) : $this->lng->txt("next");
	}

	////
	//// CLIENT OVERVIEW
	////
	
	/**
	 * display client overview panel 
	 */
	function displayClientOverview()
	{       
		$this->checkDisplayMode();

		// disable/enable button
		$btpl = new ilTemplate("tpl.buttons.html", true, true, "setup");
		$btpl->setCurrentBlock("btn");
		$btpl->setVariable("CMD", "changeaccess");
		$access_button = ($this->setup->getClient()->status["access"]["status"]) ? "disable" : "enable";
		$btpl->setVariable("TXT", $this->lng->txt($access_button));
		$btpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$btpl->parseCurrentBlock();
		$this->tpl->setVariable("BUTTONS", $btpl->get());

		$this->initClientOverviewForm();
		$this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());		

		$this->displayStatusPanel();
	}

	/**
	 * Init client overview form.
	 */
	public function initClientOverviewForm()
	{
		global $lng, $ilCtrl;
	
		$settings = $this->setup->getClient()->getAllSettings();
		
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		$this->form->setTitle($lng->txt("client_info"));
		
		// installation name
		$ne = new ilNonEditableValueGUI($lng->txt("inst_name"), "inst_name");
		$ne->setValue(($this->setup->getClient()->getName())
			? $this->setup->getClient()->getName()
			: "&lt;".$this->lng->txt("no_client_name")."&gt;");
		$ne->setInfo($this->setup->getClient()->getDescription());
		$this->form->addItem($ne);
	    
		// client id
		$ne = new ilNonEditableValueGUI($lng->txt("client_id"), "client_id");
		$ne->setValue($this->setup->getClient()->getId());
		$this->form->addItem($ne);
		
		// nic id
		$ne = new ilNonEditableValueGUI($lng->txt("ilias_nic_id"), "nic_id");
		$ne->setValue(($this->setup->getClient()->db_installed)
			? $settings["inst_id"]
			: $txt_no_database);
		$this->form->addItem($ne);
		
		// database version
		$ne = new ilNonEditableValueGUI($lng->txt("db_version"), "db_vers");
		$ne->setValue(($this->setup->getClient()->db_installed)
			? $settings["db_version"]
			: $txt_no_database);
		$this->form->addItem($ne);
		
		// access status
		$ne = new ilNonEditableValueGUI($lng->txt("access_status"), "status");
		//$access_link = "&nbsp;&nbsp;[<a href=\"setup.php?cmd=changeaccess&client_id=".$this->setup->getClient()->getId()."&back=view\">".$this->lng->txt($access_button)."</a>]";
		$access_status = ($this->setup->getClient()->status["access"]["status"]) ? "online" : "disabled";
		$ne->setValue($this->lng->txt($access_status).$access_link);
		$this->form->addItem($ne);
		
		// server information
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt("server_info"));
		$this->form->addItem($sh);
		
		// ilias version
		$ne = new ilNonEditableValueGUI($lng->txt("ilias_version"), "il_vers");
		$ne->setValue(ILIAS_VERSION);
		$this->form->addItem($ne);
		
		// host
		$ne = new ilNonEditableValueGUI($lng->txt("host"), "host");
		$ne->setValue($_SERVER["SERVER_NAME"]);
		$this->form->addItem($ne);
		
		// ip address and port
		$ne = new ilNonEditableValueGUI($lng->txt("ip_address")." & ".
			$lng->txt("port"));
		$ne->setValue($_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]);
		$this->form->addItem($ne);
		
		// server software
		$ne = new ilNonEditableValueGUI($lng->txt("server_software"), "server_softw");
		$ne->setValue($_SERVER["SERVER_SOFTWARE"]);
		$this->form->addItem($ne);

		// http path
		$ne = new ilNonEditableValueGUI($lng->txt("http_path"), "http_path");
		$ne->setValue(ILIAS_HTTP_PATH);
		$this->form->addItem($ne);

		// absolute path
		$ne = new ilNonEditableValueGUI($lng->txt("absolute_path"), "absolute_path");
		$ne->setValue(ILIAS_ABSOLUTE_PATH);
		$this->form->addItem($ne);
		
		// third party tools
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($this->lng->txt("3rd_party_software"));
		$this->form->addItem($sh);
		
		$tools = array("convert", "zip", "unzip", "java", "htmldoc", "mkisofs");
		
		foreach ($tools as $tool)
		{
			// tool
			$ne = new ilNonEditableValueGUI($lng->txt($tool."_path"), $tool."_path");
			$p = $this->setup->ini->readVariable("tools", $tool);
			$ne->setValue($p ? $p : $this->lng->txt("not_configured"));
			$this->form->addItem($ne);
		}

		// latex
		$ne = new ilNonEditableValueGUI($lng->txt("url_to_latex"), "latex_url");
		$p = $this->setup->ini->readVariable("tools", "latex_url");
		$ne->setValue($p ? $p : $this->lng->txt("not_configured"));
		$this->form->addItem($ne);
		
		// virus scanner
		$ne = new ilNonEditableValueGUI($lng->txt("virus_scanner"), "vscan");
		$ne->setValue($this->setup->ini->readVariable("tools","vscantype"));
		$this->form->addItem($ne);
		
		// scan command
		$ne = new ilNonEditableValueGUI($lng->txt("scan_command"), "scan");
		$p = $this->setup->ini->readVariable("tools","scancommand");
		$ne->setValue($p ? $p : $this->lng->txt("not_configured"));
		$this->form->addItem($ne);
		
		// clean command
		$ne = new ilNonEditableValueGUI($lng->txt("clean_command"), "clean");
		$p = $this->setup->ini->readVariable("tools","cleancommand");
		$ne->setValue($p ? $p : $this->lng->txt("not_configured"));
		$this->form->addItem($ne);
		
		$this->form->setFormAction("setup.php?cmd=gateway");
	}

	////
	//// PRELIMINARIES
	////

	/**
	 * display preliminaries page
	 */
	function displayPreliminaries()
	{
		$OK = "<font color=\"green\"><strong>OK</strong></font>";
		$FAILED = "<strong><font color=\"red\">FAILED</font></strong>";
		
		$this->tpl->addBlockFile("CONTENT","content","tpl.preliminaries.html", "setup");
		
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

		$preliminaries = array("php", "root", "folder_create",
			"cookies_enabled", "dom", "xsl", "gd", "memory");
		foreach ($preliminaries as $preliminary)
		{
			$this->tpl->setCurrentBlock("preliminary");
			$this->tpl->setVariable("TXT_PRE", $this->lng->txt("pre_".$preliminary));
			if ($this->setup->preliminaries_result[$preliminary]["status"] == true)
			{
				$this->tpl->setVariable("STATUS_PRE", $OK);
			}
			else
			{
				$this->tpl->setVariable("STATUS_PRE", $FAILED);
			}
			$this->tpl->setVariable("COMMENT_PRE", $this->setup->preliminaries_result[$preliminary]["comment"]);
			$this->tpl->parseCurrentBlock();
		}

		// summary
		if ($this->setup->preliminaries === true)
		{
			if ($this->setup->isInstalled())
			{
				$cmd = "mastersettings";
			}
			else
			{
				$cmd = "install";
			}
			$btn_text = ($this->cmd == "preliminaries") ? "" : "installation";
//echo "-".$this->display_mode."-";
			$this->setButtonNext($cmd,$btn_text);
		}
		else
		{
			$this->tpl->setCurrentBlock("premessage");
			$this->tpl->setVariable("TXT_PRE_ERR", sprintf($this->lng->txt("pre_error"),
				"http://www.ilias.de/docu/goto.php?target=pg_6531_367&client_id=docu"));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	////
	//// BASIC SETTINGS
	////

	/**
	 * display master setup form & process form input
	 */
	function displayMasterSetup($a_omit_init = false)
	{
		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html", "setup");
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("basic_settings"));
		$this->tpl->setVariable("TXT_INFO",
			$this->lng->txt("info_text_first_install")."<br/>".$this->lng->txt("info_text_pathes"));

		$this->setButtonPrev("preliminaries");

		if ($this->setup->isInstalled())
		{
			$this->setButtonNext("list");
		}

		if (!$a_omit_init)
		{
			$this->initBasicSettingsForm(true);
		}
		$this->tpl->setVariable("SETUP_CONTENT", "<br>".$this->form->getHTML()."<br>");		
	}
		
	/**
	 * display master settings and process form input
	 */
	function changeMasterSettings($a_omit_init = false)
	{			
		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html", "setup");
		$this->tpl->setVariable("TXT_HEADER", $this->lng->txt("basic_settings"));
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_pathes"));

		$this->btn_next_on = true;
		$this->btn_next_lng = $this->lng->txt("create_new_client")."...";
		$this->btn_next_cmd = "newclient";

		if (!$a_omit_init)
		{
			$this->initBasicSettingsForm();
			$this->getBasicSettingsValues();
		}
		$this->tpl->setVariable("SETUP_CONTENT", "<br>".$this->form->getHTML()."<br>");		
	}

	/**
	 * Init basic settings form.
	 */
	public function initBasicSettingsForm($a_install = false)
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// webspace dir	
		$ne = new ilNonEditableValueGUI($lng->txt("data_directory_in_ws"), "webspace_dir");
		if ($a_install)
		{
			$ne->setInfo($this->lng->txt("data_directory_in_ws_info"));
		}
		$ne->setValue(getcwd()."/data");
		$this->form->addItem($ne);
		
		// data dir
		if ($a_install)
		{
			$ti = new ilTextInputGUI($lng->txt("data_directory_outside_ws"), "datadir_path");
			$ti->setInfo($lng->txt("data_directory_info"));
			$ti->setRequired(true);
			$this->form->addItem($ti);
		}
		else
		{
			$ne = new ilNonEditableValueGUI($lng->txt("data_directory_outside_ws"), "data_dir");
			$this->form->addItem($ne);
		}
			
	
		// logging
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("logging"));
		$this->form->addItem($sh);
		
		// path to log file
		$ti = new ilTextInputGUI($lng->txt("log_path"), "log_path");
		$ti->setInfo($lng->txt("log_path_comment"));
		$this->form->addItem($ti);
		
		// disable logging 
		$cb = new ilCheckboxInputGUI($lng->txt("disable_logging"), "chk_log_status");
		$this->form->addItem($cb);
		
		// required 3rd party tools
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("3rd_party_software_req"));
		$this->form->addItem($sh);
		
		// convert path
		$ti = new ilTextInputGUI($lng->txt("convert_path"), "convert_path");
		$ti->setInfo($lng->txt("convert_path_comment"));
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// zip path
		$ti = new ilTextInputGUI($lng->txt("zip_path"), "zip_path");
		$ti->setInfo($lng->txt("zip_path_comment"));
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// unzip path
		$ti = new ilTextInputGUI($lng->txt("unzip_path"), "unzip_path");
		$ti->setInfo($lng->txt("unzip_path_comment"));
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// optional 3rd party tools
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("3rd_party_software_opt"));
		$this->form->addItem($sh);

		// java path
		$ti = new ilTextInputGUI($lng->txt("java_path"), "java_path");
		$ti->setInfo($lng->txt("java_path_comment"));
		$this->form->addItem($ti);

		// htmldoc path
		$ti = new ilTextInputGUI($lng->txt("htmldoc_path"), "htmldoc_path");
		$ti->setInfo($lng->txt("htmldoc_path_comment"));
		$this->form->addItem($ti);

		// mkisofs path
		$ti = new ilTextInputGUI($lng->txt("mkisofs_path"), "mkisofs_path");
		$ti->setInfo($lng->txt("mkisofs_path_comment"));
		$this->form->addItem($ti);

		// latex
		$ti = new ilTextInputGUI($lng->txt("url_to_latex"), "latex_url");
		$ti->setInfo($lng->txt("latex_url_comment"));
		$this->form->addItem($ti);
		
		// virus scanner
		$options = array(
			"none" => $lng->txt("none"),
			"sophos" => $lng->txt("sophos"),
			"antivir" => $lng->txt("antivir"),
			"clamav" => $lng->txt("clamav")
			);
		$si = new ilSelectInputGUI($lng->txt("virus_scanner"), "vscanner_type");
		$si->setOptions($options);
		$this->form->addItem($si);

		// scan command
		$ti = new ilTextInputGUI($lng->txt("scan_command"), "scan_command");
		$this->form->addItem($ti);

		// clean command
		$ti = new ilTextInputGUI($lng->txt("clean_command"), "clean_command");
		$this->form->addItem($ti);

		if ($a_install)
		{
			$sh = new ilFormSectionHeaderGUI();
			$sh->setTitle($lng->txt("master_password"));
			$this->form->addItem($sh);
			
			// password
			$pi = new ilPasswordInputGUI($lng->txt("password"), "password");
			$pi->setRequired(true);
			$pi->setSkipSyntaxCheck(true);
			$pi->setInfo($lng->txt("password_info"));
			$this->form->addItem($pi);
		}
	
		if ($a_install)
		{
			$this->form->addCommandButton("saveBasicSettings", $lng->txt("save"));
		}
		else
		{
			$this->form->addCommandButton("updateBasicSettings", $lng->txt("save"));
			$this->form->addCommandButton("determineToolsPath", $lng->txt("determine_tools_paths"));
		}
	                
		$this->form->setTitle($lng->txt("data_directories"));
		$this->form->setFormAction("setup.php?cmd=gateway");
		
		if ($a_install)
		{
			$det = $this->determineTools();
			$this->form->setValuesByArray($det);
		}
	 
	}
	
	/**
	 * Get current values for basic settings from 
	 */
	public function getBasicSettingsValues()
	{
		$values = array();
	
		$values["webspace_dir"] = getcwd()."/data";
		$values["data_dir"] = $this->setup->ini->readVariable("clients","datadir");
		$values["convert_path"] = $this->setup->ini->readVariable("tools","convert");
		$values["zip_path"] = $this->setup->ini->readVariable("tools","zip");
		$values["unzip_path"] = $this->setup->ini->readVariable("tools","unzip");
		$values["java_path"] = $this->setup->ini->readVariable("tools","java");
		$values["htmldoc_path"] = $this->setup->ini->readVariable("tools","htmldoc");
		$values["mkisofs_path"] = $this->setup->ini->readVariable("tools","mkisofs");
		$values["latex_url"] = $this->setup->ini->readVariable("tools","latex");
		$values["fop_path"] = $this->setup->ini->readVariable("tools","fop");
		$values["vscanner_type"] = $this->setup->ini->readVariable("tools", "vscantype");
		$values["scan_command"] = $this->setup->ini->readVariable("tools", "scancommand");
		$values["clean_command"] = $this->setup->ini->readVariable("tools", "cleancommand");
		$values["log_path"] = $this->setup->ini->readVariable("log","path")."/".
			$this->setup->ini->readVariable("log","file");
		$values["chk_log_status"] = !$this->setup->ini->readVariable("log","enabled");

		$this->form->setValuesByArray($values);
	}

	/**
	 * Save basic settings form
	 */
	public function saveBasicSettings()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initBasicSettingsForm(true);
		if ($this->form->checkInput())
		{
			$_POST["setup_pass"] = $_POST["password"];
			$_POST["setup_pass2"] = $_POST["password_retype"];
			if (!$this->setup->checkDataDirSetup($_POST))
			{
				$i = $this->form->getItemByPostVar("datadir_path");
				$i->setAlert($this->lng->txt($this->setup->getError()));				
				ilUtil::sendFailure($this->lng->txt("form_input_not_valid"),true);
			}
			else if (!$this->setup->checkLogSetup($_POST))
			{
				$i = $this->form->getItemByPostVar("log_path");
				$i->setAlert($this->lng->txt($this->setup->getError()));				
				ilUtil::sendFailure($this->lng->txt("form_input_not_valid"),true);
			}
			else if (!$this->setup->checkPasswordSetup($_POST))
			{
				ilUtil::sendFailure($this->lng->txt($this->setup->getError()),true);
			}
			else if (!$this->setup->saveMasterSetup($_POST))
			{
				ilUtil::sendFailure($this->lng->txt($this->setup->getError()),true);
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
				ilUtil::redirect("setup.php?cmd=mastersettings");
			}
		}
		
		$this->form->setValuesByPost();
		$this->displayMasterSetup(true);
	}
	
	/**
	 * Update basic settings form
	 */
	public function updateBasicSettings()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initBasicSettingsForm();
		if ($this->form->checkInput())
		{
			if (!$this->setup->checkLogSetup($_POST))
			{
				$i = $this->form->getItemByPostVar("log_path");
				$i->setAlert($this->lng->txt($this->setup->getError()));
				ilUtil::sendFailure($this->lng->txt("form_input_not_valid"),true);
			}
			else if (!$this->setup->updateMasterSettings($_POST))
			{
				ilUtil::sendFailure($this->lng->txt($this->setup->getError()),true);
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
				ilUtil::redirect("setup.php?cmd=mastersettings");
			}
		}
		
		$this->form->setValuesByPost();
		$this->changeMasterSettings(true);
	}
	
	////
	//// LOGIN
	////
	
	/**
	 * login to a client
	 */
	function loginClient()
	{
		session_destroy();
		
		ilUtil::redirect(ILIAS_HTTP_PATH."/login.php?client_id=".$this->setup->getClient()->getId());
	}
	
	/**
	 * display login form and process form
	 */
	function displayLogin($a_omit_minit = false, $a_omit_cinit = false)
	{
		global $lng;
		
		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html", "setup");

		if ($a_omit_minit)
		{
			$m_form = $this->form->getHTML();
		}
		if (!$a_omit_cinit)
		{
			$this->initClientLoginForm();
		}
		$cl_form = $this->form->getHTML();
		if (!$a_omit_minit)
		{
			$this->initMasterLoginForm();
			$m_form = $this->form->getHTML();
		}
		$this->tpl->setVariable("SETUP_CONTENT", $cl_form."<br>".$m_form);
		$this->tpl->setVariable("TXT_HEADER", $lng->txt("login"));
	}
	
	/**
	* Master Login
	*/
	public function performMLogin()
	{
		$this->initMasterLoginForm();
		if ($this->form->checkInput())
		{
			$i = $this->form->getItemByPostVar("mpassword");
			if (!$this->setup->loginAsAdmin($_POST["mpassword"]))
			{
				$i->setAlert($this->lng->txt("login_invalid"));
			}
			else
			{
				// everything ok -> we are authenticated
				ilUtil::redirect("setup.php");
			}
		}

		// something wrong -> display login again		
		$this->form->setValuesByPost();
		$this->displayLogin(true);
	}
	
	/**
	 * Login 
	 */	
	function performLogin()
	{
		$this->initClientLoginForm();
		if ($this->form->checkInput())
		{
			$i = $this->form->getItemByPostVar("password");
			if (!$this->setup->loginAsClient(
				array("client_id" => $_POST["client_id"],
				"username" => $_POST["username"], "password" => $_POST["password"])))
			{
				$i->setAlert($this->setup->getError());
			}
			else
			{
				// everything ok -> we are authenticated
				ilUtil::redirect("setup.php");
			}
		}

		// something wrong -> display login again		
		$this->form->setValuesByPost();
		$this->displayLogin(false, true);
	}
	
	/**
	* Init client login form.
	*/
	public function initClientLoginForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// client id
		$ti = new ilTextInputGUI($lng->txt("client_id"), "client_id");
		$ti->setMaxLength(32);
		$ti->setSize(20);
		$this->form->addItem($ti);
		
		// username
		$ti = new ilTextInputGUI($lng->txt("username"), "username");
		$ti->setSize(20);
		$this->form->addItem($ti);

		// password
		$pi = new ilPasswordInputGUI($lng->txt("password"), "password");
		$pi->setSize(20);
		$pi->setRetype(false);
		$pi->setSkipSyntaxCheck(true);
		$this->form->addItem($pi);		
	
		$this->form->addCommandButton("performLogin", $lng->txt("login"));
	                
		$this->form->setTitle($lng->txt("client_login"));
		$this->form->setFormAction("setup.php?cmd=gateway");
	}

	/**
	* Init master login form.
	*/
	public function initMasterLoginForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// password
		$pi = new ilPasswordInputGUI($lng->txt("password"), "mpassword");
		$pi->setSize(20);
		$pi->setRetype(false);
		$pi->setSkipSyntaxCheck(true);
		$this->form->addItem($pi);		
	
		$this->form->addCommandButton("performMLogin", $lng->txt("login"));
	                
		$this->form->setTitle($lng->txt("admin_login"));
		$this->form->setFormAction("setup.php?cmd=gateway");
	 
	}

	////
	//// CLIENT LIST
	////

	/**
	 * display client list and process form input
	 */
	function displayClientList()
	{
		$_SESSION["ClientId"] = "";
		
		$this->tpl->addBlockFile("CONTENT","content","tpl.clientlist.html", "setup");
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_list"));		
		ilUtil::sendInfo();

		// common
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("list_clients"));
		$this->tpl->setVariable("TXT_LISTSTATUS",($this->setup->ini->readVariable("clients","list")) ? $this->lng->txt("display_clientlist") : $this->lng->txt("hide_clientlist"));
		$this->tpl->setVariable("TXT_TOGGLELIST",($this->setup->ini->readVariable("clients","list")) ? $this->lng->txt("disable") : $this->lng->txt("enable"));
		
		include_once("./setup/classes/class.ilClientListTableGUI.php");
		$tab = new ilClientListTableGUI($this->setup);
		$this->tpl->setVariable("CLIENT_LIST", $tab->getHTML());

		// create new client button
		$this->btn_next_on = true;
		$this->btn_next_lng = $this->lng->txt("create_new_client")."...";
		$this->btn_next_cmd = "newclient";
	}

	/**
	* Determine tools paths
	*/
	function determineToolsPath()
	{
		$_POST = $this->determineTools($_POST);
		$this->updateBasicSettings();
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
	function determineTools($a_tools = "")
	{
		$tools = array("convert", "zip", "unzip", "java", "htmldoc","mkisofs");
		$dirs = array("/usr/local", "/usr/local/bin", "/usr/bin", "/bin", "/sw/bin", "/usr/bin");
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
	

	////
	//// NEW CLIENT STEP 1: SELECT DB TYPE
	////
	
	/**
	 * Select database type
	 *
	 */
	function selectDBType()
	{
		$this->checkDisplayMode("create_new_client");
		

if (true)
{
		$this->initDBSelectionForm();
		$this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
}
else
{
		// output

		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_select_db.html", "setup");
		
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save"));
		
		$this->tpl->setVariable("TXT_DB_TYPE", $this->lng->txt("db_type"));
		$this->tpl->setVariable("TXT_DB_SELECTION", $this->lng->txt("db_selection"));
}
		if ($this->setup->getClient()->status["ini"]["status"])
		{
			$this->setButtonNext("db");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	 * Init db selection form.
	 */
	public function initDBSelectionForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// db type
		$options = array(
			"mysql" => "MySQL 5.0.x or higher",
			"oracle" => "Oracle 10g or higher",
			);
		$si = new ilSelectInputGUI($lng->txt("db_type"), "db_type");
		$si->setOptions($options);
		$si->setInfo($lng->txt(""));
		$this->form->addItem($si);
	
		$this->form->addCommandButton("selectdbtype", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("db_selection"));
		$this->form->setFormAction("setup.php?cmd=gateway");
	}
	
	////
	//// NEW CLIENT STEP 2: SELECT DB TYPE
	////

	/**
	 * display setup in step
	 */
	function displayIni($a_omit_form_init = false)
	{
		$this->checkDisplayMode("create_new_client");
		
		if ($_POST["db_type"] != "")
		{
			$_SESSION["db_type"] = $_POST["db_type"];
		}
		else
		{
			$_POST["db_type"] = $_SESSION["db_type"];
		}
		
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_ini"));
		if (!$a_omit_form_init)
		{
			$this->initClientIniForm();
			$this->getClientIniValues();
		}
		$this->tpl->setVariable("SETUP_CONTENT",
			$this->form->getHTML());

		if ($this->setup->getClient()->status["ini"]["status"])
		{
			$this->setButtonNext("db");
		}
		
		$this->checkPanelMode();
	}

	/**
	 * Init client ini form.
	 */
	public function initClientIniForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		
		// client id
		if ($this->setup->ini_client_exists)
		{
			$hi = new ilHiddenInputGUI("client_id");
			$hi->setValue($this->client_id);
			$this->form->addItem($hi);
		
			$ne = new ilNonEditableValueGUI($lng->txt("client_id"), "hh");
			$ne->setValue($this->client_id);
			$this->form->addItem($ne);	
		}
		else
		{
			$ti = new ilTextInputGUI($lng->txt("client_id"), "client_id");
			$ti->setMaxLength(32);
			$ti->setRequired(true);
			$this->form->addItem($ti);
		}
		
		// database connection	
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("db_conn"));
		$this->form->addItem($sh);
		
		// db type
		$ne = new ilNonEditableValueGUI($lng->txt("db_type"), "dbt");
		$ne->setValue($lng->txt("db_".$_SESSION["db_type"]));
		$this->form->addItem($ne);
	
		// db host
		$ti = new ilTextInputGUI($lng->txt("db_host"), "db_host");
		$ti->setMaxLength(120);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// db name
		if ($_SESSION["db_type"] == "mysql")
		{
			$ti = new ilTextInputGUI($lng->txt("db_name"), "db_name");
		}
		else
		{
			$ti = new ilTextInputGUI($lng->txt("db_service_name"), "db_name");
		}
		$ti->setMaxLength(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// db user
		$ti = new ilTextInputGUI($lng->txt("db_user"), "db_user");
		$ti->setMaxLength(40);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// db password
		$ti = new ilTextInputGUI($lng->txt("db_pass"), "db_pass");
		$ti->setMaxLength(40);
		$this->form->addItem($ti);
		
		$this->form->addCommandButton("saveClientIni", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("inst_identification"));
		$this->form->setFormAction("setup.php?cmd=gateway");
	}
	
	/**
	 * Get current values for client ini from 
	 */
	public function getClientIniValues()
	{
		$values = array();
	
		$values["db_host"] = $this->setup->getClient()->getDbHost();
		$values["db_user"] = $this->setup->getClient()->getDbUser();
		$values["db_pass"] = $this->setup->getClient()->getDbPass();
		$values["db_name"] = $this->setup->getClient()->getDbName();
		$values["client_id"] = $this->setup->getClient()->getId();
	
		$this->form->setValuesByArray($values);
	}
	
	/**
	 * Save client ini form
	 */
	public function saveClientIni()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initClientIniForm();
		if ($this->form->checkInput())
		{
			if (strlen($_POST["client_id"]) != strlen(urlencode(($_POST["client_id"]))))
			{
				$i = $this->form->getItemByPostVar("client_id");
				$i->setAlert($this->lng->txt("ini_client_id_invalid"));
				ilUtil::sendFailure($this->lng->txt("ini_client_id_invalid"),true);
			}           
			else if (strlen($_POST["client_id"]) < 4)
			{
				$i = $this->form->getItemByPostVar("client_id");
				$i->setAlert($this->lng->txt("ini_client_id_too_short"));
				ilUtil::sendFailure($this->lng->txt("ini_client_id_too_short"),true);
			}
			else if (strlen($_POST["client_id"]) > 32)
			{
				$i = $this->form->getItemByPostVar("client_id");
				$i->setAlert($this->lng->txt("ini_client_id_too_long"));
				ilUtil::sendFailure($this->lng->txt("ini_client_id_too_long"),true);
			}
			else if (!$this->setup->ini_client_exists && file_exists(ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".$_POST["client_id"]))
			{
				$i = $this->form->getItemByPostVar("client_id");
				$i->setAlert($this->lng->txt("ini_client_id_exists"));
				ilUtil::sendFailure($this->lng->txt("ini_client_id_exists"),true);
			}
			else
			{
	
				// save some old values
				$old_db_name = $this->setup->getClient()->getDbName();
				$old_db_type = $this->setup->getClient()->getDbType();
				$old_client_id = $this->setup->getClient()->getId();            

				// create new client object if it does not exist
				if (!$this->setup->ini_client_exists)
				{
					$client_id = $_POST["client_id"];
					$this->setup->newClient($client_id);
				}

				// set client data 
				$this->setup->getClient()->setId($_POST["client_id"]);
				$this->setup->getClient()->setDbHost($_POST["db_host"]);
				$this->setup->getClient()->setDbName($_POST["db_name"]);
				$this->setup->getClient()->setDbUser($_POST["db_user"]);
				$this->setup->getClient()->setDbPass($_POST["db_pass"]);
				$this->setup->getClient()->setDbType($_SESSION["db_type"]);
				$this->setup->getClient()->setDSN();
				
				// try to connect to database
				if (!$this->setup->getClient()->checkDatabaseHost())
				{
					$i = $this->form->getItemByPostVar("db_host");
					$i->setAlert($this->lng->txt($this->setup->getClient()->getError()));
					ilUtil::sendFailure($this->setup->getClient()->getError(),true);
				}
				else
				{	
					// check if db exists
					$db_installed = $this->setup->getClient()->checkDatabaseExists();
	
					if ($db_installed and (!$this->setup->ini_ilias_exists or ($this->setup->getClient()->getDbName() != $old_db_name)))
					{
						$_POST["db_name"] = $old_db_name;
						$message = ucfirst($this->lng->txt("database"))." \"".$this->setup->getClient()->getDbName()."\" ".$this->lng->txt("ini_db_name_exists");
						$i = $this->form->getItemByPostVar("db_name");
						$i->setAlert($message);
						ilUtil::sendFailure($message, true);
					}
					else
					{
						// all ok. create client.ini and save posted data
						if (!$this->setup->ini_client_exists)
						{
							if ($this->setup->saveNewClient())
							{
								ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
								$this->setup->getClient()->status["ini"]["status"] = true;
								$_SESSION["ClientId"] = $client_id;
								ilUtil::redirect("setup.php?cmd=displayIni&client_id=".$client_id);
							}
							else
							{
								$err = $this->setup->getError();
								ilUtil::sendFailure($this->lng->txt("save_error").": ".$err, true);
								$this->setup->getClient()->status["ini"]["status"] = false;
								$this->setup->getClient()->status["ini"]["comment"] = $err;
							}
						}
						else
						{
							if ($this->setup->getClient()->ini->write())
							{
								ilUtil::sendSuccess($this->lng->txt("settings_changed"));
								$this->setup->getClient()->status["ini"]["status"] = true;
								ilUtil::redirect("setup.php?cmd=displayIni");
							}
							else
							{
								$err = $this->setup->getClient()->ini->getError();
								ilUtil::sendFailure($this->lng->txt("save_error").": ".$err, true);
								$this->setup->getClient()->status["ini"]["status"] = false;
								$this->setup->getClient()->status["ini"]["comment"] = $err;
							}
						}
					}
				}
			}			
		}
		
		$this->form->setValuesByPost();
		$this->displayIni(true);
	}
	
	/**
	 * display error page
	 * 
	 * @param    string  error message
	 */
	function displayError($a_message)
	{
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.error.html", "setup");
		
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
		$this->tpl->addBlockFile("CONTENT","content","tpl.logout.html", "setup");

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
		$steps = $this->setup->getStatus();
		
		// remove access step
		unset($steps["access"]);
		
		$steps["ini"]["text"]       = $this->lng->txt("setup_process_step_ini");
		$steps["db"]["text"]        = $this->lng->txt("setup_process_step_db");
		$steps["lang"]["text"]      = $this->lng->txt("setup_process_step_lang");
		$steps["contact"]["text"]   = $this->lng->txt("setup_process_step_contact");
		$steps["nic"]["text"]       = $this->lng->txt("setup_process_step_nic");
		$steps["finish"]["text"]    = $this->lng->txt("setup_process_step_finish");
		
		$stpl = new ilTemplate("tpl.process_panel.html", true, true, "setup");

		$num = 1;

		foreach ($steps as $key => $val)
		{
			$stpl->setCurrentBlock("menu_row");
			$stpl->setVariable("TXT_STEP",$this->lng->txt("step")." ".$num.": &nbsp;");
			$stpl->setVariable("TXT_ACTION",$val["text"]);
			$stpl->setVariable("IMG_ARROW", "spacer.gif");
			
			$num++;

			if ($this->cmd == $key and isset($this->cmd))
			{
				$stpl->setVariable("HIGHLIGHT", " style=\"font-weight:bold;\"");
				$stpl->setVariable("IMG_ARROW", "arrow_right.png");
			}
			
			$status = ($val["status"]) ? $OK : "";          
			
			$stpl->setVariable("TXT_STATUS",$status);
			$stpl->parseCurrentBlock();
		}
		
		$stpl->setVariable("TXT_SETUP_PROCESS_STATUS",$this->lng->txt("setup_process_status"));

		$this->tpl->setVariable("PROCESS_MENU", $stpl->get());
	}

	/**
	 * display status panel
	 */
	function displayStatusPanel()
	{
		$OK = "<font color=\"green\"><strong>OK</strong></font>";

		$this->tpl->addBlockFile("STATUS_PANEL","status_panel","tpl.status_panel.html", "setup");

		$this->tpl->setVariable("TXT_OVERALL_STATUS", $this->lng->txt("overall_status"));
		// display status
		if ($this->setup->getClient()->status)
		{
			foreach ($this->setup->getClient()->status as $key => $val)
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
				$this->tpl->addBlockFile("CONTENT","content","tpl.clientview.html", "setup");
				// display tabs
				include "./setup/include/inc.client_tabs.php";
				$client_name = ($this->setup->getClient()->getName()) ? $this->setup->getClient()->getName() : $this->lng->txt("no_client_name");
				$this->tpl->setVariable("TXT_HEADER",$client_name." (".$this->lng->txt("client_id").": ".$this->setup->getClient()->getId().")");       
				break;
			
			case "setup":
				$this->tpl->addBlockFile("CONTENT","content","tpl.clientsetup.html", "setup");
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
		$this->tpl->addBlockFile("CONTENT","content","tpl.clientsetup.html", "setup");
		
		$this->tpl->setVariable("TXT_INFO",$this->lng->txt("info_text_first_client"));
		$this->tpl->setVariable("TXT_HEADER",$this->lng->txt("setup_first_client"));
		
		$this->displayProcessPanel();
		
		$this->setButtonNext("ini");
	}

	////
	//// DISPLAY DATABASE
	////
	
	/**
	 * display database form and process form input
	 */
	function displayDatabase()
	{
		global $ilErr,$ilDB,$ilLog;

		$this->checkDisplayMode("setup_database");
		
		//$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_db.html", "setup");

		// database is intalled
		if ($this->setup->getClient()->db_installed)
		{
			$ilDB = $this->setup->getClient()->db;
			$this->lng->setDbHandler($ilDB);
			$dbupdate = new ilDBUpdate($ilDB);
			$db_status = $dbupdate->getDBVersionStatus();

			$this->initClientDbForm(false, $dbupdate, $db_status);
			$this->getClientDbFormValues($dbupdate);
			$this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

			if ($db_status)
			{
				$this->setButtonNext("lang");
			}
		}
		else	// database is not installed
		{
			$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_db")."<br />".
				"<p><code>CREATE DATABASE &lt;your_db&gt; CHARACTER SET utf8 COLLATE &lt;your_collation&gt;</code></p>".
				"<p><b>".$this->lng->txt("info_text_db2")."</b></p><br/>");
	
			$this->initClientDbForm();
			$this->getClientDbFormValues();
			$this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
			$this->setButtonPrev("ini");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	* Init client db form.
	*/
	public function initClientDbForm($a_install = true, $dbupdate = null, $db_status = false)
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// type
		$ne = new ilNonEditableValueGUI($lng->txt("db_type"), "db_type");
		$this->form->addItem($ne);

		// version
		if ($this->setup->getClient()->getDBType() == "mysql")
		{
			$ne = new ilNonEditableValueGUI($lng->txt("version"), "db_version");
			$ilDB = $this->setup->getClient()->db;
			$ne->setValue($ilDB->getDBVersion());
			$this->form->addItem($ne);
		}

		// host
		$ne = new ilNonEditableValueGUI($lng->txt("host"), "db_host");
		$this->form->addItem($ne);

		// name
		$ne = new ilNonEditableValueGUI($lng->txt("name"), "db_name");
		$this->form->addItem($ne);
	
		// user
		$ne = new ilNonEditableValueGUI($lng->txt("user"), "db_user");
		$this->form->addItem($ne);
		
		// creation / collation for mysql
		if ($this->setup->getClient()->getDBType() == "mysql" && $a_install)
		{
			// create database 
			$cb = new ilCheckboxInputGUI($lng->txt("database_create"), "chk_db_create");
			
				// collation
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
					$options[$collation] = $collation;
				}
				$si = new ilSelectInputGUI($lng->txt("collation"), "collation");
				$si->setOptions($options);
				$si->setInfo($this->lng->txt("info_text_db_collation2")." ".
					"<a target=\"_new\" href=\"http://dev.mysql.com/doc/mysql/en/charset-unicode-sets.html\">".
					" MySQL Reference Manual :: 10.11.1 Unicode Character Sets</a>");
				$cb->addSubItem($si);
				
			$this->form->addItem($cb);
		}

		if ($a_install)
		{
			$this->form->addCommandButton("installDatabase", $lng->txt("database_install"));
		}
		else
		{
			$ilDB = $this->setup->getClient()->db;
			$this->lng->setDbHandler($ilDB);
			$dbupdate = new ilDBUpdate($ilDB);
			
			// database version
			$ne = new ilNonEditableValueGUI($lng->txt("database_version"), "curv");
			$ne->setValue($dbupdate->currentVersion);
			$this->form->addItem($ne);
			
			// file version
			$ne = new ilNonEditableValueGUI($lng->txt("file_version"), "filev");
			$ne->setValue($dbupdate->fileVersion);
			$this->form->addItem($ne);
			
			if (!$db_status = $dbupdate->getDBVersionStatus())
			{
				// next update step
				$options = array();
				for ($i = $dbupdate->currentVersion + 1; $i <= $dbupdate->fileVersion; $i++)
				{
					$options[$i] = $i;
				}
				if (count($options) > 1)
				{
					$si = new ilSelectInputGUI($lng->txt("next_update_break"), "update_break");
					$si->setOptions($options);
					$si->setInfo($lng->txt("next_update_break_info"));
					$this->form->addItem($si);
				}
								
				ilUtil::sendFailure($this->lng->txt("database_needs_update"));
				$this->form->addCommandButton("updateDatabase", $lng->txt("database_update"));
			}
			else
			{
				ilUtil::sendSuccess($this->lng->txt("database_is_uptodate"));
			}
		}
	                
		$this->form->setTitle($lng->txt("database"));
		$this->form->setFormAction("setup.php?cmd=gateway");
	}
	
	/**
	* Get current values for client db from 
	*
	*/
	public function getClientDbFormValues($dbupdate = null)
	{
		global $lng;
		
		$values = array();
	
		$values["db_host"] = $this->setup->getClient()->getDbHost();
		$values["db_name"] = $this->setup->getClient()->getDbName();
		$values["db_user"] = $this->setup->getClient()->getDbUser();
		$values["db_type"] = $lng->txt("db_".$this->setup->getClient()->getDbType());
		if (is_object($dbupdate))
		{
			$values["update_break"] = $dbupdate->fileVersion;
			if (($dbupdate->fileVersion - $dbupdate->currentVersion) >= 200)
			{
				$values["update_break"] = $dbupdate->currentVersion + 200 -
					($dbupdate->currentVersion % 100);
			}
		}

		$this->form->setValuesByArray($values);
	}
	
	////
	//// INSTALL DATABASE
	////
	
	/**
	 * Install the database
	 *
	 * @param
	 * @return
	 */
	function installDatabase()
	{
		if (!$this->setup->getClient()->db_exists)
		{
			if ($_POST["chk_db_create"])
			{
				if (!$this->setup->createDatabase($_POST["collation"]))
				{
					ilUtil::sendFailure($this->lng->txt($this->setup->getError()), true);
					ilUtil::redirect("setup.php?cmd=displayDatabase");
				}
			}
			else
			{
				ilUtil::sendFailure($this->lng->txt("database_not_exists_create_first"), true);
				ilUtil::redirect("setup.php?cmd=displayDatabase");
			}
		}
		if (!$this->setup->installDatabase())
		{
			ilUtil::sendFailure($this->lng->txt($this->setup->getError()), true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("database_installed"), true);
		}
		ilUtil::redirect("setup.php?cmd=displayDatabase");
	}

	////
	//// UPDATE DATABASE
	////
	
	/**
	 * Update database
	 */
	function updateDatabase()
	{
		global $ilCtrlStructureReader;
		
		$ilCtrlStructureReader->setIniFile($this->setup->getClient()->ini);
		
		include_once "./Services/Database/classes/class.ilDBUpdate.php";
		include_once "./Services/AccessControl/classes/class.ilRbacAdmin.php";
		include_once "./Services/AccessControl/classes/class.ilRbacReview.php";
		include_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
		include_once "./Services/Tree/classes/class.ilTree.php";
		include_once "./classes/class.ilSaxParser.php";
		include_once "./Services/Object/classes/class.ilObjectDefinition.php";

		// referencing db handler in language class
		$ilDB = $this->setup->getClient()->db;
		$this->lng->setDbHandler($ilDB);

		// run dbupdate
		$dbupdate = new ilDBUpdate($ilDB);
		$dbupdate->applyUpdate((int) $_POST["update_break"]);
	
		if ($dbupdate->updateMsg == "no_changes")
		{
			$message = $this->lng->txt("no_changes").". ".$this->lng->txt("database_is_uptodate");
		}
		else
		{
			$sep = "";
			foreach ($dbupdate->updateMsg as $row)
			{
				if ($row["msg"] == "update_applied")
				{
					$a_message.= $sep.$row["nr"];
					$sep = ", ";
				}
				else
				{
					$e_message.= "<br/>".$this->lng->txt($row["msg"]).": ".$row["nr"];
				}
			}
			if ($a_message != "")
			{
				$a_message = $this->lng->txt("update_applied").": ".$a_message;
			}
		}
		
		ilUtil::sendInfo($a_message.$e_message, true);
		ilUtil::redirect("setup.php?cmd=displayDatabase");
	}
	
	////
	//// LANGUAGES
	////
	
	/**
	 * display language form and process form input
	 */
	function displayLanguages()
	{
		$this->checkDisplayMode("setup_languages");

		if (!$this->setup->getClient()->db_installed)
		{
			// program should never come to this place
			$message = "No database found! Please install database first.";
			ilUtil::sendFailure($message);
		}

		include_once("./setup/classes/class.ilSetupLanguagesTableGUI.php");
		$tab = new ilSetupLanguagesTableGUI($this->setup->getClient());
		$this->tpl->setVariable("SETUP_CONTENT", $tab->getHTML());

		$this->tpl->setVariable("TXT_SETUP_TITLE",ucfirst(trim($this->lng->txt("setup_languages"))));
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_lang"));
		
		$installed_langs = $this->lng->getInstalledLanguages();
		$lang_count = count($installed_langs);
		if ($lang_count > 0)
		{
			$this->setup->getClient()->status["lang"]["status"] = true;
			$this->setup->getClient()->status["lang"]["comment"] = $lang_count." ".$this->lng->txt("languages_installed");
		}
		else
		{
			$this->setup->getClient()->status["lang"]["status"] = false;
			$this->setup->getClient()->status["lang"]["comment"] = $this->lng->txt("lang_none_installed");
		}

		$this->setButtonPrev("db");
		
		if ($lang_count > 0)
		{
			$this->setButtonNext("contact");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	 * Save languages
	 *
	 * @param
	 * @return
	 */
	function saveLanguages()
	{
		if (empty($_POST["form"]["lang_id"]))
		{
			ilUtil::sendFailure($this->lng->txt("lang_min_one_language"), true);
			ilUtil::redirect("setup.php?cmd=lang");
		}
		
		if (!in_array($_POST["form"]["lang_default"],$_POST["form"]["lang_id"]))
		{
			ilUtil::sendFailure($this->lng->txt("lang_not_installed_default"), true);
			ilUtil::redirect("setup.php?cmd=lang");
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

		$this->setup->getClient()->setDefaultLanguage($_POST["form"]["lang_default"]);
		$message = $this->lng->txt("languages_installed");
		
		if ($result !== true)
		{
			$message .= "<br/>(".$this->lng->txt("langs_not_valid_not_installed").": ".$list.")";
		}
		ilUtil::sendInfo($message, true);
		ilUtil::redirect("setup.php?cmd=lang");
	}

	////
	//// CONTACT DATA
	////	
	
	/**
	 * display contact data form and process form input
	 */
	function displayContactData($a_omit_init = false)
	{
		$this->checkDisplayMode("setup_contact_data");
		$settings = $this->setup->getClient()->getAllSettings();

		if (!$a_omit_init)
		{
			$this->initContactDataForm();
			$this->getContactValues();
		}
		$this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_contact"));
		$this->setButtonPrev("lang");
		
		$check = $this->setup->checkClientContact($this->setup->client);

		$this->setup->getClient()->status["contact"]["status"] = $check["status"];
		$this->setup->getClient()->status["contact"]["comment"] = $check["comment"];

		if ($check["status"])
		{
			$this->setButtonNext("nic");
		}
		
		$this->checkPanelMode();
	}

	/**
	 * Init contact data form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initContactDataForm()
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// name
		$ti = new ilTextInputGUI($lng->txt("name"), "inst_name");
		$ti->setMaxLength(64);
		$ti->setSize(30);
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		// description
		$ti = new ilTextInputGUI($lng->txt("client_info"), "inst_info");
		$ti->setMaxLength(64);
		$ti->setSize(30);
		$this->form->addItem($ti);
		
		// institution
		$ti = new ilTextInputGUI($lng->txt("client_institution"), "inst_institution");
		$ti->setMaxLength(64);
		$ti->setSize(30);
		$this->form->addItem($ti);
		
		// contact data
		$sh = new ilFormSectionHeaderGUI();
		$sh->setTitle($lng->txt("contact_data"));
		$this->form->addItem($sh);
		
		// first name
		$ti = new ilTextInputGUI($lng->txt("firstname"), "admin_firstname");
		$ti->setMaxLength(64);
		$ti->setSize(30);
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// last name
		$ti = new ilTextInputGUI($lng->txt("lastname"), "admin_lastname");
		$ti->setMaxLength(64);
		$ti->setSize(30);
		$ti->setRequired(true);
		$this->form->addItem($ti);
	
		$fs = array (
			"title" => array("max" => 64, "size" => 30),
			"position" => array("max" => 64, "size" => 30),
			"institution" => array("max" => 200, "size" => 30),
			"street" => array("max" => 64, "size" => 30),
			"zipcode" => array("max" => 10, "size" => 5),
			"city" => array("max" => 64, "size" => 30),
			"country" => array("max" => 64, "size" => 30),
			"phone" => array("max" => 64, "size" => 30)
			);
		foreach ($fs as $f => $op)
		{
			// field
			$ti = new ilTextInputGUI($lng->txt($f), "admin_".$f);
			$ti->setMaxLength($op["max"]);
			$ti->setSize($op["size"]);
			$ti->setInfo($lng->txt(""));
			$this->form->addItem($ti);
		}

		// email
		$ti = new ilEmailInputGUI($lng->txt("email"), "admin_email");
		$ti->setRequired(true);
		$this->form->addItem($ti);
		
		$this->form->addCommandButton("saveContact", $lng->txt("save"));
	                
		$this->form->setTitle($lng->txt("client_data"));
		$this->form->setFormAction("setup.php?cmd=gateway");
	}
	
	/**
	 * Get current values for contact from 
	 */
	public function getContactValues()
	{
		
		$settings = $this->setup->getClient()->getAllSettings();
		
		$values = $settings;
		
		$values["inst_name"] = ($this->setup->getClient()->getName())
			? $this->setup->getClient()->getName()
			: $this->setup->getClient()->getId();
		$values["inst_info"] = $this->setup->getClient()->getDescription();
	
		$this->form->setValuesByArray($values);
	}

	/**
	 * Save contact form
	 */
	public function saveContact()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initContactDataForm();
		if ($this->form->checkInput())
		{
			$this->setup->getClient()->setSetting("admin_firstname", $_POST["admin_firstname"]);
			$this->setup->getClient()->setSetting("admin_lastname", $_POST["admin_lastname"]);
			$this->setup->getClient()->setSetting("admin_title", $_POST["admin_title"]);
			$this->setup->getClient()->setSetting("admin_position", $_POST["admin_position"]);
			$this->setup->getClient()->setSetting("admin_institution", $_POST["admin_institution"]);
			$this->setup->getClient()->setSetting("admin_street", $_POST["admin_street"]);
			$this->setup->getClient()->setSetting("admin_zipcode", $_POST["admin_zipcode"]);
			$this->setup->getClient()->setSetting("admin_city", $_POST["admin_city"]);
			$this->setup->getClient()->setSetting("admin_country", $_POST["admin_country"]);
			$this->setup->getClient()->setSetting("admin_phone", $_POST["admin_phone"]);
			$this->setup->getClient()->setSetting("admin_email", $_POST["admin_email"]);
			$this->setup->getClient()->setSetting("inst_institution", $_POST["inst_institution"]);
			$this->setup->getClient()->setSetting("inst_name", $_POST["inst_name"]);

			// update client.ini
			$this->setup->getClient()->setName($_POST["inst_name"]);
			$this->setup->getClient()->setDescription($_POST["inst_info"]);
			$this->setup->getClient()->ini->write();
						
			ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
			ilUtil::redirect("setup.php?cmd=displayContactData");
		}
		
		$this->form->setValuesByPost();
		$this->displayContactData(true);
	}

	//// 
	//// NIC Registration
	////  

	/**
	 * display nic registration form and process form input
	 */
	function displayNIC($a_omit_init = false)
	{
		$this->checkDisplayMode("nic_registration");
		$settings = $this->setup->getClient()->getAllSettings();
		$nic_key = $this->setup->getClient()->getNICkey();
		
		// reload settings
		$settings = $this->setup->getClient()->getAllSettings();
//var_dump($settings);
		if ($settings["nic_enabled"] == "1" && $settings["inst_id"] > 0)
		{
			$this->no_second_nav = true;
			$this->tpl->setVariable("TXT_INFO",$this->lng->txt("info_text_nic3")." ".$settings["inst_id"].".");
		}
		else
		{
			// reload settings
			$settings = $this->setup->getClient()->getAllSettings();
			
			$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_nic"));
			if (!$a_omit_init)
			{
				$this->initRegistrationForm();
				$this->getRegistrationValues();
			}
			$this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

			if (isset($settings["nic_enabled"]))
			{
				$this->setup->getClient()->status["nic"]["status"] = true;
			}

		}

		$this->setButtonPrev("contact");
		
		if ($this->setup->getClient()->status["nic"]["status"])
		{
			$this->setButtonNext("finish","finish");
		}
		
		$this->checkPanelMode();
	}
	
	/**
	 * Init registration form.
	 *
	 * @param        int        $a_mode        Edit Mode
	 */
	public function initRegistrationForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
	
		// registration type
		$radg = new ilRadioGroupInputGUI($lng->txt("nic_registration"), "register");
		$radg->setValue(1);
		$op1 = new ilRadioOption($lng->txt("nic_reg_online"), 1);
		$radg->addOption($op1);
		$op1 = new ilRadioOption($lng->txt("nic_reg_disable"), 0, $lng->txt("nic_reg_disable_info"));
		$radg->addOption($op1);
		$this->form->addItem($radg);	
	
		$this->form->addCommandButton("saveRegistration", $lng->txt("save"));
		$this->form->setFormAction("setup.php?cmd=gateway");
	}
	
	/**
	 * Get current values for registration from 
	 */
	public function getRegistrationValues()
	{
		$settings = $this->setup->getClient()->getAllSettings();
		$nic_key = $this->setup->getClient()->getNICkey();


		$values = array();

		if (!isset($settings["nic_enabled"]) or $settings["nic_enabled"] == "1")
		{
			$values["register"] = 1;
		}
		/*elseif ($settings["nic_enabled"] == "2")
		{
			$this->tpl->setVariable("EMAIL",$checked);
		}*/
		else
		{
			$values["register"] = 0;
		}
		
		$this->form->setValuesByArray($values);
	}
	
	/**
	 * Save registration form
	 */
	public function saveRegistration()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initRegistrationForm();
		if ($this->form->checkInput())
		{
			// check register option
			if ($_POST["register"] == 1)
			{
				// update nic
				$this->setup->getClient()->updateNIC($this->setup->ilias_nic_server);
//var_dump($this->setup->getClient()->nic_status);
				// online registration failed
				if (empty($this->setup->getClient()->nic_status[2]))
				{
					$this->setup->getClient()->setSetting("nic_enabled","-1");
					ilUtil::sendFailure($this->lng->txt("nic_reg_failed"), true);
					ilUtil::redirect("setup.php?cmd=displayNIC");
				}
				else
				{
					$this->setup->getClient()->setSetting("inst_id",$this->setup->getClient()->nic_status[2]);
					$this->setup->getClient()->setSetting("nic_enabled","1");
					$this->setup->getClient()->status["nic"]["status"] = true;      
					ilUtil::sendSuccess($this->lng->txt("nic_reg_enabled"), true);
					ilUtil::redirect("setup.php?cmd=displayNIC");
				}
			}
			/*elseif ($_POST["form"]["register"] == 2)
			{
				$nic_by_email = (int) $_POST["form"]["nic_id"];
				
				$checksum = md5($nic_key.$nic_by_email);
				
				if (!$nic_by_email or $_POST["form"]["nic_checksum"] != $checksum)
				{
					$message = $this->lng->txt("nic_reg_enter_correct_id");     
				}
				else
				{
					$this->setup->getClient()->setSetting("inst_id",$nic_by_email);
					$this->setup->getClient()->setSetting("nic_enabled","1");
					$message = $this->lng->txt("nic_reg_enabled");      
				}
			}*/
			else
			{
				$this->setup->getClient()->setSetting("inst_id","0");
				$this->setup->getClient()->setSetting("nic_enabled","0");
				ilUtil::sendSuccess($this->lng->txt("nic_reg_disabled"), true);
				ilUtil::redirect("setup.php?cmd=displayNIC");
			}
		}
		
		$this->form->setValuesByPost();
		$this->displayNIC(true);
	}
	
	////
	//// Tools
	////
	
	/**
	 * display tools
	 */
	function displayTools()
	{
		$this->checkDisplayMode();
		
		// output
		ilUtil::sendInfo();
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_tools.html", "setup");
		$this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
		$this->tpl->setVariable("TXT_TOOLS", $this->lng->txt("tools"));
		$this->tpl->setVariable("TXT_CTRL_STRUCTURE", $this->lng->txt("ctrl_structure"));
		$this->tpl->setVariable("TXT_RELOAD", $this->lng->txt("reload"));

		$ilDB = $this->setup->getClient()->db;
		$cset = $ilDB->query("SELECT count(*) as cnt FROM ctrl_calls");
		$crec = $ilDB->fetchAssoc($cset);

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
		
		if (!$this->setup->getClient()->db_installed)
		{
			ilUtil::sendInfo($this->lng->txt("no_db"), true);
			$this->displayTools();
			return;
		}

		// referencing does not work in dbupdate-script
		$GLOBALS["ilDB"] = $this->setup->getClient()->getDB();
// BEGIN WebDAV
		// read module and service information into db
		require_once "./setup/classes/class.ilModuleReader.php";
		require_once "./setup/classes/class.ilServiceReader.php";
		require_once "./setup/classes/class.ilCtrlStructureReader.php";

		require_once "./Services/Component/classes/class.ilModule.php";
		require_once "./Services/Component/classes/class.ilService.php";
		$modules = ilModule::getAvailableCoreModules();
		$services = ilService::getAvailableCoreServices();

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
		$this->tpl->addBlockFile("CONTENT","content","tpl.std_layout.html", "setup");
		
		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_password"));

		// formular sent
		if ($_POST["form"])
		{
			$pass_old = $this->setup->getPassword();

			if (empty($_POST["form"]["pass_old"]))
			{
				$message = $this->lng->txt("password_enter_old");
				$this->setup->raiseError($message,$this->setup->error_obj->MESSAGE);
			}
				
			if (md5($_POST["form"]["pass_old"]) != $pass_old)
			{
				$message = $this->lng->txt("password_old_wrong");
				$this->setup->raiseError($message,$this->setup->error_obj->MESSAGE);
			}
			
			if (empty($_POST["form"]["pass"]))
			{
				$message = $this->lng->txt("password_empty");
				$this->setup->raiseError($message,$this->setup->error_obj->MESSAGE);
			}
			
			if ($_POST["form"]["pass"] != $_POST["form"]["pass2"])
			{
				$message = $this->lng->txt("password_not_match");
				$this->setup->raiseError($message,$this->setup->error_obj->MESSAGE);
			}
			
			if (md5($_POST["form"]["pass"]) == $pass_old)
			{
				$message = $this->lng->txt("password_same");
				$this->setup->raiseError($message,$this->setup->error_obj->MESSAGE);
			}
			
			if (!$this->setup->setPassword($_POST["form"]["pass"]))
			{
				$message = $this->lng->txt("save_error");
				$this->setup->raiseError($message,$this->setup->error_obj->MESSAGE);
			}

			ilUtil::sendInfo($this->lng->txt("password_changed"),true);
			ilUtil::redirect("setup.php");
		}
		
		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.form_change_admin_password.html", "setup");

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
		$this->no_second_nav = true;
//echo "<b>1</b>";
		if ($this->validateSetup())
		{
			$txt_info = $this->lng->txt("info_text_finish1")."<br /><br />".
				"<p>".$this->lng->txt("user").": <b>root</b><br />".
				$this->lng->txt("password").": <b>homer</b></p>";
			$this->setButtonNext("login_new","login");
//echo "<b>2</b>";
			$this->setup->getClient()->reconnect();		// if this is not done, the writing of
											// the setup_ok fails (with MDB2 and a larger
											// client list), alex 17.1.2008
			$this->setup->getClient()->setSetting("setup_ok",1);
//$this->setup->getClient()->setSetting("zzz", "Z");
//echo "<b>3</b>";
			$this->setup->getClient()->status["finish"]["status"] = true;
//echo "<b>4</b>";
		}
		else
		{
			$txt_info = $this->lng->txt("info_text_finish2");
		}
		
//echo "<b>5</b>";
		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_finish.html", "setup");
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
			
			$msg = $this->setup->getClient()->delete($ini,$db,$files);

			ilUtil::sendInfo($this->lng->txt("client_deleted"),true);
			ilUtil::redirect("setup.php");
		}

		$this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_delete"));
		
		// output
		$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.form_delete_client.html", "setup");

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
		if ($this->setup->getClient()->status["finish"]["status"])
		{
			$val = ($this->setup->getClient()->ini->readVariable("client","access")) ? "0" : true;
			$this->setup->getClient()->ini->setVariable("client","access",$val);
			$this->setup->getClient()->ini->write();
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
			$client = new ilClient($_POST["form"]["default"], $this->setup->db_connections);

			if (!$client->init())
			{
				$this->setup->raiseError($this->lng->txt("no_valid_client_id"),$this->setup->error_obj->MESSAGE);
			}
			
			$status = $this->setup->getStatus($client);
		
			if ($status["finish"]["status"])
			{
				$this->setup->ini->setVariable("clients","default",$client->getId());
				$this->setup->ini->write();
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
		foreach ($this->setup->getClient()->status as $key => $val)
		{
			if ($key != "finish" and $key != "access")
			{
				if ($val["status"] != true)
				{
					return false;
				}
			}
		}
		
//$this->setup->getClient()->setSetting("zzz", "V");
		$clientlist = new ilClientList($this->setup->db_connections);
//$this->setup->getClient()->setSetting("zzz", "W");
		$list = $clientlist->getClients();
//$this->setup->getClient()->setSetting("zzz", "X");
		if (count($list) == 1)
		{
			$this->setup->ini->setVariable("clients","default",$this->setup->getClient()->getId());
			$this->setup->ini->write();

			$this->setup->getClient()->ini->setVariable("client","access",1);
			$this->setup->getClient()->ini->write();
		}
//$this->setup->getClient()->setSetting("zzz", "Y");
		return true;
	}
	
	/**
	 * if setting up a client was not finished, jump back to the first uncompleted setup step
	 */
	function jumpToFirstUnfinishedSetupStep()
	{
		if (!$this->setup->getClient()->status["db"]["status"])
		{
			$this->cmd = "db";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayDatabase();
		}
		elseif (!$this->setup->getClient()->status["lang"]["status"])
		{
			$this->cmd = "lang";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayLanguages();      
		}
		elseif (!$this->setup->getClient()->status["contact"]["status"])
		{
			$this->cmd = "contact";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayContactData();        
		}
		elseif (!$this->setup->getClient()->status["nic"]["status"])
		{
			$this->cmd = "nic";
			ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"),true);
			$this->displayNIC();        
		}
		elseif (!$this->setup->getClient()->status["finish"]["status"])
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
		if ($this->setup->ini->readVariable("clients","list"))
		{
			$this->setup->ini->setVariable("clients","list","0");
			$this->setup->ini->write();
			ilUtil::sendInfo($this->lng->txt("list_disabled"),true);
		}
		else
		{
			$this->setup->ini->setVariable("clients","list","1");
			$this->setup->ini->write();
			ilUtil::sendInfo($this->lng->txt("list_enabled"),true);             
		}
		
		ilUtil::redirect("setup.php");
	}
		
} // END class.ilSetupGUI
?>
