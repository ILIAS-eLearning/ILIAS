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
* setup file for ilias
* 
* this file helps setting up ilias
* main purpose is writing the ilias.ini to the filesystem
* it can set up the database to if the settings are correct and the dbuser has the rights
*
* @author Peter Gabriel <pgabriel@databay.de> 
* @version $Id$
*
* @package ilias
*/
//include classes - later in the program it will be done by ilias.header.inc
require_once "include/inc.check_pear.php";
require_once "classes/class.ilSetup.php";
require_once "classes/class.ilLanguage.php";
require_once "classes/class.ilLog.php";

$OK = "<font color=\"green\"><strong>OK</strong></font>";
$FAILED = "<strong><font color=\"red\">FAILED</font></strong>";

//CVS - REVISION - DO NOT MODIFY
$REVISION = "$Revision$";
$VERSION = substr(substr($REVISION,2),0,-2);

// set ilias pathes
define ("ILIAS_HTTP_PATH","http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["REQUEST_URI"]));
define ("ILIAS_ABSOLUTE_PATH",dirname($_SERVER["PATH_TRANSLATED"]));

//instantiate template - later in the program please use own Templateclass
$tpl = new $tpl_class_name ("./templates/default");
$tpl->loadTemplatefile("tpl.setup.html", true, true);

// prepare file access to work with safe mode
umask(0117);

$log = new ilLog("", "ilias.log");

//instantiate setup-class
$mySetup = new ilSetup();

session_start();

// reload setupscript if $step is empty
if ($_GET["step"] == "")
{
	$_GET["step"] = "preliminaries";
}

// set language to english if $lang is empty
if ($_GET["lang"] == "")
{
	$_GET["lang"] = "en";
}

//instantiate language class
$lng = new ilLanguage($_GET["lang"]);

$languages = $mySetup->getLanguages($lng->lang_path);

foreach ($languages as $lang_key)
{
	$tpl->setCurrentBlock("languages");
	$tpl->setVariable("LINK_LANG", "./setup.php?step=".$_GET["step"]."&amp;lang=".$lang_key);
	$tpl->setVariable("LANG_NAME", $lng->txt("lang_".$lang_key));
	$tpl->setVariable("LANG", $lang_key);
	
	if ($lang_key == $_GET["lang"])
	{
		$vspace = 0;
		$border = 2;
	}
	else
	{
		$vspace = 0;
		$border = 0;
	}

	$tpl->setVariable("BORDER", $border);
	$tpl->setVariable("VSPACE", $vspace);
	$tpl->parseCurrentBlock();
}

// init
$ini_exists = $mySetup->readIniFile();
$db_exists = false;
$passwd_exists = false;
$logged_in = false;

if ($ini_exists)
{
	$db_exists = $mySetup->connect();
}

if ($db_exists)
{
	$passwd_exists = $mySetup->checkPasswordExists();
	include_once "./classes/class.ilDBUpdate.php";
}
else
{
	$setup_path = "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["REQUEST_URI"])."/setup/setup.php";
	echo "<p>This setup version is outdated. Please use new the new setup program is located in <a href=\"".$setup_path."\">".$setup_path."</a></p><p>Use your old setup password to login. If you haven't had set a password it is 'homer'.</p>";
	session_destroy();
	exit();
}

if ($_SESSION["auth_setup"] == "yes")
{
	$logged_in = true;
}

// show login screen
if (!$logged_in && $passwd_exists && $_GET["step"] != 9)
{
	$_GET["step"] = 8;
}

//main language texts
$tpl->setVariable("LANG", $_GET["lang"]);
$tpl->setVariable("TXT_SETUP", $lng->txt("setup"));
$tpl->setVariable("VERSION", $VERSION);
$tpl->setVariable("TXT_VERSION", $lng->txt("version"));

// switch start here!
switch ($_GET["step"])
{
	// LOGIN SCREEN
	// header to ilias login screen & switch language to english if needed
	case "login":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("login"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("login"));
		}
		else
		{
			session_destroy();
			
			$installed_langs = $mySetup->getInstalledLanguages();
			
			if (!in_array($_GET["lang"],$installed_langs))
			{
				$_GET["lang"] == "en";
			}
			
			header ("Location: login.php?lang=".$_GET["lang"]);
			exit();
		}
		break;

	// ENVIRONMENT & PRELIMINARIES
	case "preliminaries":
	
		$tpl->setVariable("TXT_SETUP_WELCOME", $lng->txt("setup_welcome"));
		$tpl->setVariable("TXT_SETUP_INIFILE_DESC", $lng->txt("setup_inifile_desc"));
		$tpl->setVariable("TXT_SETUP_DATABASE_DESC", $lng->txt("setup_database_desc"));
		$tpl->setVariable("TXT_SETUP_LANGUAGES_DESC", $lng->txt("setup_languages_desc"));
		$tpl->setVariable("TXT_SETUP_PASSWORD_DESC", $lng->txt("setup_password_desc"));		
	
		$server_os = php_uname();
		$server_web = $_SERVER["SERVER_SOFTWARE"];
		$environment = $lng->txt("env_using")." ".$server_os." <br/>".$lng->txt("with")." ".$server_web;

		if ((stristr($server_os,"linux") || stristr($server_os,"windows")) && stristr($server_web,"apache"))
		{
			$env_comment = $lng->txt("env_ok");		
		}
		else
		{
			$env_comment = "<font color=\"red\">".$lng->txt("env_warning")."</font>";
		}
			
		$tpl->setVariable("TXT_ENV_TITLE", $lng->txt("environment"));
		$tpl->setVariable("TXT_ENV_INTRO", $environment);
		$tpl->setVariable("TXT_ENV_COMMENT", $env_comment);	
		
		$tpl->setVariable("TXT_PRE_TITLE", $lng->txt("preliminaries"));
		$tpl->setVariable("TXT_PRE_INTRO", $lng->txt("pre_intro"));

		//get preliminaries	
		$arCheck = $mySetup->preliminaries();
		
		// display phpversion
		$tpl->setCurrentBlock("preliminary");
		$tpl->setVariable("TXT_PRE", $lng->txt("pre_php_version").": ".$arCheck["php"]["version"]);

		if ($arCheck["php"]["status"] == true)
		{
			$tpl->setVariable("STATUS_PRE", $OK);
		}
		else
		{
			$tpl->setVariable("STATUS_PRE", $FAILED);
			$tpl->setVariable("COMMENT_PRE", $arCheck["php"]["comment"]);
		}

		$tpl->parseCurrentBlock();
	
		// check if ilias3 folder is writable
		$tpl->setCurrentBlock("preliminary");
		$tpl->setVariable("TXT_PRE", $lng->txt("pre_folder_write"));

		if ($arCheck["root"]["status"] == true)
		{
			$tpl->setVariable("STATUS_PRE", $OK);
		}
		else
		{
			$tpl->setVariable("STATUS_PRE", $FAILED);
			$tpl->setVariable("COMMENT_PRE", $arCheck["root"]["comment"]);
		}
		$tpl->parseCurrentBlock();
		
		// check if ilias3 can create new folders
		$tpl->setCurrentBlock("preliminary");
		$tpl->setVariable("TXT_PRE", $lng->txt("pre_folder_create"));

		if ($arCheck["create"]["status"] == true)
		{
			$tpl->setVariable("STATUS_PRE", $OK);
			$tpl->setVariable("TXT_INSTALLATION", $lng->txt("installation"));	
		}
		else
		{
			$tpl->setVariable("STATUS_PRE", $FAILED);
			$tpl->setVariable("COMMENT_PRE", $arCheck["create"]["comment"]);
		}

		$tpl->parseCurrentBlock();
	
		// summary
		if ($arCheck["php"] == true && $arCheck["root"] == true && $arCheck["create"] == true)
		{
			$tpl->setCurrentBlock("all_ok");
			$tpl->setVariable("LINK_OVERVIEW", "setup.php?step=begin&lang=".$_GET["lang"]);
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("premessage");
			$tpl->setVariable("TXT_PRE_ERR", $lng->txt("pre_error"));
			$tpl->parseCurrentBlock();
		}
		break;

	// DISPLAY MAINMENU
	case "begin":
		$ok = true;
		
		//inifile
		if ($ini_exists)
		{
			$msg = $OK;
		}
		else
		{
			$msg = "";
			$ok = false;
		}
	
		$tpl->setCurrentBlock("link");
		$tpl->setVariable("NR", "1");
		$tpl->setVariable("TXT_LINK", $lng->txt("setup_inifile"));
		$tpl->setVariable("LINK", "setup.php?step=1&lang=".$_GET["lang"]);
		$tpl->setVariable("STATUS", $msg);
		$tpl->parseCurrentBlock();
		
		// database
		$num = 3;
		$txt_link = $lng->txt("setup_database");		

		if ($db_exists)
		{
			$myDB = new ilDBUpdate();
			
			if ($myDB->currentVersion > 60)
			{
				$setup_path = "http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["REQUEST_URI"])."/setup/setup.php";
				echo "<p>This setup version is outdated. Please use new the new setup program is located in <a href=\"".$setup_path."\">".$setup_path."</a></p><p>Use your old setup password to login. If you haven't had set a password it is 'homer'.</p>";
				session_destroy();
				exit();
			}

			if ($myDB->getDBVersionStatus())
			{
				$remark = $lng->txt("database_is_uptodate");			
				$msg = $OK;
			}
			else
			{
				$remark = "<font color=\"red\">".$lng->txt("database_needs_update").
						  " (".$lng->txt("database_version").": ".$myDB->currentVersion.
						  " ; ".$lng->txt("file_version").": ".$myDB->fileVersion.")</font>";
				$txt_link = $lng->txt("database_update");
				$num = 13;
				$msg = "";
				$ok = false;
			}
		}
		else
		{
			$msg = "";
			$ok = false;
		}
	
		$tpl->setCurrentBlock("link");
		$tpl->setVariable("NR", "2");
		$tpl->setVariable("TXT_LINK", $txt_link);
		$tpl->setVariable("LINK", "setup.php?step=".$num."&lang=".$_GET["lang"]);
		$tpl->setVariable("STATUS", $msg);
		$tpl->setVariable("TXT_REMARK", $remark);
		$tpl->parseCurrentBlock();
	
		// languages
		$msg = $OK;
	
		$tpl->setCurrentBlock("link");
		$tpl->setVariable("NR", "3");
		$tpl->setVariable("TXT_LINK", $lng->txt("setup_languages"));
		$tpl->setVariable("LINK", "setup.php?step=4&lang=".$_GET["lang"]);
		$tpl->setVariable("STATUS", $msg);
		$tpl->parseCurrentBlock();
	
		//password
		if ($logged_in)
		{
			$msg = $OK;
			$text = $lng->txt("change_password");
			$num = 11;
		}
		else
		{
			$msg = "";
			$ok = false;
			$text = $lng->txt("setup_password");
			$num = 6;
		}
	
		$tpl->setCurrentBlock("link");
		$tpl->setVariable("NR", "4");
		$tpl->setVariable("TXT_LINK", $text);
		$tpl->setVariable("LINK", "setup.php?step=".$num."&lang=".$_GET["lang"]);
		$tpl->setVariable("STATUS", $msg);
		$tpl->parseCurrentBlock();
		
		// login
		if ($ok)
		{
			$tpl->setCurrentBlock("ready");
			$tpl->setVariable("TXT_LOGIN", $lng->txt("login"));
			$tpl->setVariable("TXT_SETUP_READY", $lng->txt("setup_ready"));
			$tpl->setVariable("LINK_LOGIN", "setup.php?step=login&amp;lang=".$_GET["lang"]);
			$tpl->parseCurrentBlock();
		}	
		
		$tpl->setCurrentBlock("begininstallation");
		$tpl->setVariable("TXT_TITLE", $lng->txt("setup_mainmenu"));
		$tpl->setVariable("TXT_SETUP_INTRO_INSTALL", $lng->txt("intro_install"));
		$tpl->parseCurrentBlock();

		showMenulink();
		break;

	// SETUP INIFILE
	case "1":
		$mySetup->readIniFile();
		$dbhost = $_POST["dbhost"] ? $_POST["dbhost"] : $mySetup->dbHost;
		$dbname = $_POST["dbname"] ? $_POST["dbname"] : $mySetup->dbName;
		$dbuser = $_POST["dbuser"] ? $_POST["dbuser"] : $mySetup->dbUser;
		$dbpass = $_POST["dbpass"] ? $_POST["dbpass"] : $mySetup->dbPass;
		$dpath  = $_POST["dpath"]  ? $_POST["dpath"]  : $mySetup->data_path;



		//load defaults if neccessary
		if(!$_POST)
		{
			$mySetup->getDefaults();
			$dbhost = $mySetup->default["db"]["host"];
			$dbname = $mySetup->default["db"]["name"];
			$dbuser = $mySetup->default["db"]["user"];
			$dbpass = $mySetup->default["db"]["pass"];
		}

		//try to read the ini-file and build msg if error
		if (!$ini_exists)
		{
		    $msg = $lng->txt("inifile_firsttime")."<br />".$lng->txt("inifile_fill_all");
		}
		else
		{
		    $msg = $lng->txt("inifile_exists");
		}

		//check if ini-file is writable and build msg
		if (!$mySetup->checkWritable())
		{
			$msg = $lng->txt("inifile_cannot_write");
		}
		else
		{
		    $msg = "<br/>".$lng->txt("inifile_can_write");
		}

		//output message
		showMessage($msg,$lng->txt("setup_inifile"));

		//get the defaults from setup class
		$mySetup->getDefaults();

		//text output
		$tpl->setCurrentBlock("step1");
		$tpl->setVariable("TXT_DB_HOST", $lng->txt("db_host"));
		$tpl->setVariable("TXT_DB_NAME", $lng->txt("db_name"));
		$tpl->setVariable("TXT_DB_TYPE", $lng->txt("db_type"));
		$tpl->setVariable("TXT_DB_USER", $lng->txt("db_user"));
		$tpl->setVariable("TXT_DB_PASS", $lng->txt("db_pass"));
		$tpl->setVariable("TXT_DATA_PATH", $lng->txt("data_path")."<br>".$lng->txt("out_of_webspace"));
		$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
		$tpl->setVariable("TXT_RESET", $lng->txt("reset"));
		$tpl->setVariable("TXT_IMAGE_PATH", $lng->txt("webspace_dir"));
		//variable content output
		$tpl->setVariable("LANG", $_GET["lang"]);
		$tpl->setVariable("DB_HOST", $dbhost);
		$tpl->setVariable("DB_NAME", $dbname);
		$tpl->setVariable("DB_TYPE", "MySQL");
		$tpl->setVariable("DB_USER", $dbuser);
		$tpl->setVariable("DB_PASS", $dbpass);
		$tpl->setVariable("D_PATH", $dpath);

		$tpl->parseCurrentBlock();
		break;

	// GENERATE INIFILE
	case "2":
		if(!$_POST["dpath"] or @!file_exists($_POST["dpath"]))
		{
			// TODO: needs input checking of all vars here!!! and error message output
			header("location: setup.php?step=1&lang=en");
			exit;
		}

		$mySetup->setDbHost($_POST["dbhost"]);
		$mySetup->setDbName($_POST["dbname"]);
		$mySetup->setDbUser($_POST["dbuser"]);
		$mySetup->setDbPass($_POST["dbpass"]);
		$mySetup->setDataPath($_POST["dpath"]);

		//write the inifile if all things are okay
		if (!$mySetup->writeIniFile())
		{

			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("inifile_cannot_write"),$lng->txt("setup_inifile"));
		}
		else
		{
			// PREPARE DATA DIRECTORIES (OUTSIDE OF WEBSPACE)
			if(file_exists($mySetup->getDataPath()))
			{
				if(is_writeable($mySetup->getDataPath()))
				{
					// PREPARE MAIL DIRECTORY
					if(!@is_dir($mySetup->getDataPath().'/mail'))
					{
						mkdir($mySetup->getDataPath().'/mail');
					}

					// PREPARE LEARNING MODULE DATA DIRECTORY (outside wb)
					if(!@is_dir($mySetup->getDataPath().'/lm_data'))
					{
						mkdir($mySetup->getDataPath().'/lm_data');
					}

					// PREPARE FILE DATA DIRECTORY (outside wb)
					if(!@is_dir($mySetup->getDataPath().'/files'))
					{
						mkdir($mySetup->getDataPath().'/files');
					}

				}
				chmod($mySetup->getDataPath().'/mail',0755);
				chmod($mySetup->getDataPath().'/lm_data',0755);
				chmod($mySetup->getDataPath().'/files',0755);
			}

			// PREPARE WEBSPACE DIRECTORIES
			// removed


			$msg = $lng->txt("inifile_written")."<br />".$lng->txt("inifile_content");

			showMessage($msg,$lng->txt("setup_inifile"));

			$tpl->setCurrentBlock("step2");
			$tpl->setVariable("INIFILECONTENT", $mySetup->ini->show());
			$tpl->parseCurrentBlock();
		}
		break;

	// INSTALL DATABASE
	case "3":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("setup_database"));
		}
	
		//try to connect to the database
		//if connection is successful and database is present the user may advance to login
		elseif ($mySetup->installDatabase())
		{
			showMessage($lng->txt("database_ready"),$lng->txt("setup_database"));
		}
		else
		{
			showMessage($lng->txt($mySetup->error),$lng->txt("setup_database"));

		}
		break;

	// LANGUAGES
	case "4":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("setup_languages"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("setup_languages"));
		}
		else
		{
			showMessage($lng->txt("choose_languages"),$lng->txt("setup_languages"));

			$languages = $mySetup->getLanguages($lng->lang_path);
			$installed_langs = $mySetup->getInstalledLanguages();
			$tpl->setCurrentBlock("language_row");
	
			foreach ($languages as $lang_key)
			{
				if ($lang_key != "en")
				{		
					$tpl->setCurrentBlock("language_row");
					$tpl->setVariable("ARR_LANG_KEY", "id[".$lang_key."]");
					$tpl->setVariable("TXT_LANG", $lng->txt("lang_".$lang_key));
					if (in_array($lang_key,$installed_langs))
					{
						$tpl->setVariable("TXT_OK", ($OK));
						$tpl->setVariable("CHECKED", ("checked=\"checked\""));		
					}
					$tpl->setVariable("TXT_REMARK", $lang_remark);
					$tpl->parseCurrentBlock();
				}
			}
	
			// show menu link
			$tpl->setCurrentBlock("step4");
			$tpl->setVariable("LANG", $_GET["lang"]);
			$tpl->setVariable("TXT_LANG", $lng->txt("lang_en"));
			$tpl->setVariable("TXT_OK", $OK);
			$tpl->setVariable("TXT_REMARK", $lng->txt("en_by_default"));		
			$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
			$tpl->parseCurrentBlock();
		}
		break;

	// INSTALLING LANGUAGES
	case "5":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("setup_languages"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("setup_languages"));
		}
		else
		{
			if ($mySetup->installLanguages())
			{
			 $msg = $lng->txt("languages_installed");
			}
			else
			{
			 $msg = $lng->txt("lang_error_occurred!");
			}

			showMessage($msg,$lng->txt("setup_languages"));

		}
		break;

	// PASSWORD
	case "6":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("setup_password"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("setup_password"));
		}
		else
		{
			showMessage($lng->txt("password_info"),$lng->txt("setup_password"));
			
			$tpl->setCurrentBlock("step6");
			$tpl->setVariable("LANG", $_GET["lang"]);
			$tpl->setVariable("TXT_CHOOSE_PASSWD", $lng->txt("choose_password"));
			$tpl->parseCurrentBlock();
		}
		break;

	// WRITE PASSWORD
	case "7":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("setup_password"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("setup_password"));
		}
		elseif (empty($_POST["passwd"]))
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("password_empty"),$lng->txt("setup_password"),6);
		}
		else
		{
			showMessage($lng->txt("password_set"),$lng->txt("setup_password"));

			$mySetup->setPassword($_POST["passwd"]);
			$auth_setup = "yes";
			session_register("auth_setup");
		}
		break;

	// LOGIN SCREEN
	case "8":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("login"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("login"));
		}
		else
		{
			showTitle($lng->txt("login"));

			$tpl->setCurrentBlock("login");
			$tpl->setVariable("TXT_ENTER_PASSWD", $lng->txt("enter_password"));
			$tpl->setVariable("LANG", $_GET["lang"]);
			$tpl->parseCurrentBlock();
		}
		break;

	// CHECK PASSWD	
	case "9":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("login"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("login"));
		}
		elseif (!$mySetup->checkPassword($_POST["passwd"]))
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("wrong_password"),$lng->txt("authorization_failed"));
		}
		else
		{
			$auth_setup = "yes";
			session_register("auth_setup");
			
			header ("Location: setup.php?step=begin&lang=".$_GET["lang"]);
			exit();
		}
		break;

	// LOGOUT
	case "10":
		session_destroy();
		unset($_SESSION);
		showTitle($lng->txt("setup"));
		showText($lng->txt("logged_out"));
		break;

	// CHANGE PASSWORD FORM
	case "11":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("change_password"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("change_password"));
		}
		elseif (!$logged_in)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("log_in_first"),$lng->txt("change_password"));
		}
		else
		{

			showMessage("",$lng->txt("change_password"));

			// show menu link
			$tpl->setCurrentBlock("changepasswd");
			$tpl->setVariable("TXT_SET_OLDPASSWD", $lng->txt("set_oldpasswd"));
			$tpl->setVariable("TXT_SET_NEWPASSWD", $lng->txt("set_newpasswd"));
			$tpl->setVariable("LANG", $_GET["lang"]);
			$tpl->parseCurrentBlock();
		}
		break;

	// CONFIRM CHANGE PASSWORD
	case "12":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("change_password"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("change_password"));
		}
		elseif (!$logged_in)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("log_in_first"),$lng->txt("change_password"));
		}
		elseif (empty($_POST["passwdold"]) || empty($_POST["passwdnew"]))
		{
			showMessage($lng->txt("fill_both_fields"),$lng->txt("change_password"),11);
		}
		elseif (!$mySetup->checkPassword($_POST["passwdold"]))
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("wrong_password"),$lng->txt("change_password"),11);
		}
		else
		{
			$mySetup->setPassword($_POST["passwdnew"]);
			showMessage($lng->txt("password_changed"),$lng->txt("change_password"));
		}
		break;

	case "13":
		if (!$ini_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_ini_first"),$lng->txt("database_update"));
		}
		elseif (!$db_exists)
		{
			showMessage($lng->txt($mySetup->error)."<br/>".$lng->txt("setup_db_first"),$lng->txt("database_update"));
		}
		else
		{
			$myDB = new ilDBUpdate();		
			$myDB->applyUpdate();
			
			if ($myDB->updateMsg != "no_changes")
			{
				showTitle($lng->txt("database_update"));
		
				foreach ($myDB->updateMsg as $row)
				{
					showText($lng->txt($row["msg"]).": ".$row["nr"]);
					//$tpl->setCurrentBlock("versionmessage");
					//$tpl->setVariable("MSG", $row["msg"].": ".$row["nr"]);
					//$tpl->parseCurrentBlock();
				}
				
				showMenulink();
			}
			else
			{
				showMessage($lng->txt("no_changes"),$lng->txt("database_update"));
			}
		}
		break;

	case "14":
	
		break;

	case "15":
	
		break;

	default:
	
		break;
}

// standard output
function showMessage ($a_msg,$a_title,$a_back = "")
{
	global $tpl, $lng, $logged_in;
	
	if ($logged_in)
	{
		$tpl->setCurrentBlock("logout");
		$tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
		$tpl->parseCurrentBlock();	
	}
	
	if (!empty($a_back))
	{
		$tpl->setCurrentBlock("backlink");
		$tpl->setVariable("TXT_BACK", $lng->txt("back"));
		$tpl->setVariable("BACK", $a_back);
		$tpl->setVariable("LANG", $_GET["lang"]);
		$tpl->parseCurrentBlock();	
	}

	$tpl->setCurrentBlock("title");
	$tpl->setVariable("TXT_TITLE", $a_title);
	$tpl->parseCurrentBlock();

	$tpl->setCurrentBlock("message");
	$tpl->setVariable("TXT_MSG", $a_msg);
	$tpl->parseCurrentBlock();
	
	$tpl->setCurrentBlock("menulink");
	$tpl->setVariable("TXT_MENU", $lng->txt("setup_menu"));
	$tpl->setVariable("LANG", $_GET["lang"]);
	$tpl->parseCurrentBlock();
}


// display title only
function showTitle($a_title)
{
	global $tpl;

	$tpl->setCurrentBlock("title");
	$tpl->setVariable("TXT_TITLE", $a_title);
	$tpl->parseCurrentBlock();
}


// display text only
function showText($a_msg)
{
	global $tpl;

	$tpl->setCurrentBlock("message");
	$tpl->setVariable("TXT_MSG", $a_msg);
	$tpl->parseCurrentBlock();
}

// display menulink only
function showMenulink()
{
	global $tpl, $lng, $logged_in;

	if ($logged_in)
	{
		$tpl->setCurrentBlock("logout");
		$tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
		$tpl->setVariable("LANG", $_GET["lang"]);
		$tpl->parseCurrentBlock();	
	}

	if ($_GET["step"] != "begin")
	{
		$tpl->setCurrentBlock("menulink");
		$tpl->setVariable("TXT_MENU", $lng->txt("setup_menu"));
		$tpl->setVariable("LANG", $_GET["lang"]);
		$tpl->parseCurrentBlock();
	}
}

$tpl->setVariable("LANG", $_GET["lang"]);
header('Content-type: text/html; charset=UTF-8');
$tpl->show();

				/*if ($myDB->currentVersion == "61")
				{
					$setup_path = substr("http://".$_SERVER["HTTP_HOST"].dirname($_SERVER["REQUEST_URI"]),0,-6)."/setup/setup.php";
					echo "<p>This setup version is outdated. Please use new the new setup program is located in <a href=\"".$setup_path."\">".$setup_path."</a></p><p>Your setup password is the same. If you haven't had set a password it is 'homer'.</p>";
					exit();
				}*/
?>
