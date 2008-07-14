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

/** @defgroup ServicesInit Services/Init
 */

/**
* ILIAS Initialisation Utility Class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Sascha Hofmann <shofmann@databay.de>

* @version $Id$
*
* @ingroup ServicesInit
*/
class ilInitialisation
{

	/**
	* Remove unsafe characters from GET
	*/
	function removeUnsafeCharacters()
	{
		// Remove unsafe characters from GET parameters.
		// We do not need this characters in any case, so it is
		// feasible to filter them everytime. POST parameters
		// need attention through ilUtil::stripSlashes() and similar functions)
		if (is_array($_GET))
		{
			foreach($_GET as $k => $v)
			{
				// \r\n used for IMAP MX Injection
				// ' used for SQL Injection
				$_GET[$k] = str_replace(array("\x00", "\n", "\r", "\\", "'", '"', "\x1a"), "", $v);

				// this one is for XSS of any kind
				$_GET[$k] = strip_tags($_GET[$k]);
			}
		}
	}

	/**
	 * get common include code files
	*/
	function requireCommonIncludes()
	{
		global $ilBench;

		// get pear
		require_once("include/inc.get_pear.php");
		require_once("include/inc.check_pear.php");

		//include class.util first to start StopWatch
		require_once "./Services/Utilities/classes/class.ilUtil.php";
		require_once "classes/class.ilBenchmark.php";
		$ilBench =& new ilBenchmark();
		$GLOBALS['ilBench'] =& $ilBench;

		// BEGIN Usability: Measure response time until footer is displayed on form
		// The stop statement is in class.ilTemplate.php function addILIASfooter()
		$ilBench->start("Core", "ElapsedTimeUntilFooter");
		// END Usability: Measure response time until footer is displayed on form

		$ilBench->start("Core", "HeaderInclude");

		// start the StopWatch
		$GLOBALS['t_pagestart'] = ilUtil::StopWatch();

		$ilBench->start("Core", "HeaderInclude_IncludeFiles");

		// Major PEAR Includes
		require_once "PEAR.php";
		//require_once "DB.php";
		require_once "Auth/Auth.php";

		// HTML_Template_IT support
		// (location changed with 4.3.2 & higher)
		@include_once "HTML/ITX.php";
		if (!class_exists("IntegratedTemplateExtension"))
		{
			include_once "HTML/Template/ITX.php";
			include_once "classes/class.ilTemplateHTMLITX.php";
		}
		else
		{
			include_once "classes/class.ilTemplateITX.php";
		}
		require_once "classes/class.ilTemplate.php";

		//include classes and function libraries
		require_once "include/inc.db_session_handler.php";
		require_once "classes/class.ilDBx.php";
		require_once "./Services/AuthShibboleth/classes/class.ilShibboleth.php";
		require_once "classes/class.ilias.php";
		require_once './Services/User/classes/class.ilObjUser.php';
		require_once "classes/class.ilFormat.php";
		require_once "./Services/Calendar/classes/class.ilDatePresentation.php";
		require_once "classes/class.ilSaxParser.php";
		require_once "./Services/Object/classes/class.ilObjectDefinition.php";
		require_once "./Services/Style/classes/class.ilStyleDefinition.php";
		require_once "./Services/Tree/classes/class.ilTree.php";
		require_once "./Services/Language/classes/class.ilLanguage.php";
		require_once "./Services/Logging/classes/class.ilLog.php";
		require_once "Services/Mail/classes/class.ilMailbox.php";
		require_once "classes/class.ilCtrl.php";
		require_once "classes/class.ilConditionHandler.php";
		require_once "classes/class.ilBrowser.php";
		require_once "classes/class.ilFrameTargetInfo.php";
		require_once "Services/Navigation/classes/class.ilNavigationHistory.php";
		require_once "Services/Help/classes/class.ilHelp.php";
		require_once "include/inc.ilias_version.php";

		//include role based access control system
		require_once "./Services/AccessControl/classes/class.ilAccessHandler.php";
		require_once "./Services/AccessControl/classes/class.ilRbacAdmin.php";
		require_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
		require_once "./Services/AccessControl/classes/class.ilRbacReview.php";

		// include object_data cache
		require_once "classes/class.ilObjectDataCache.php";
		require_once 'Services/Tracking/classes/class.ilOnlineTracking.php';

		// ### AA 03.10.29 added new LocatorGUI class ###
		//include LocatorGUI
		require_once "classes/class.ilLocatorGUI.php";

		// include error_handling
		require_once "classes/class.ilErrorHandling.php";

		// php5 downward complaince to php 4 dom xml and clone method
		if (version_compare(PHP_VERSION,'5','>='))
		{
			require_once("include/inc.xml5compliance.php");
			//require_once("Services/CAS/phpcas/source/CAS/domxml-php4-php5.php");

			require_once("include/inc.xsl5compliance.php");
			require_once("include/inc.php4compliance.php");
		}
		else
		{
			require_once("include/inc.php5compliance.php");
		}

		$ilBench->stop("Core", "HeaderInclude_IncludeFiles");
	}

	/**
	* This method provides a global instance of class ilIniFile for the
	* ilias.ini.php file in variable $ilIliasIniFile.
	*
	* It initializes a lot of constants accordingly to the settings in
	* the ilias.ini.php file.
	*/
	function initIliasIniFile()
	{
		global $ilIliasIniFile;

		require_once("classes/class.ilIniFile.php");
		$ilIliasIniFile = new ilIniFile("./ilias.ini.php");
		$GLOBALS['ilIliasIniFile'] =& $ilIliasIniFile;
		$ilIliasIniFile->read();

		// initialize constants
		define("ILIAS_DATA_DIR",$ilIliasIniFile->readVariable("clients","datadir"));
		define("ILIAS_WEB_DIR",$ilIliasIniFile->readVariable("clients","path"));
		define("ILIAS_ABSOLUTE_PATH",$ilIliasIniFile->readVariable('server','absolute_path'));

		// logging
		define ("ILIAS_LOG_DIR",$ilIliasIniFile->readVariable("log","path"));
		define ("ILIAS_LOG_FILE",$ilIliasIniFile->readVariable("log","file"));
		define ("ILIAS_LOG_ENABLED",$ilIliasIniFile->readVariable("log","enabled"));
		define ("ILIAS_LOG_LEVEL",$ilIliasIniFile->readVariable("log","level"));

		// read path + command for third party tools from ilias.ini
		define ("PATH_TO_CONVERT",$ilIliasIniFile->readVariable("tools","convert"));
		define ("PATH_TO_ZIP",$ilIliasIniFile->readVariable("tools","zip"));
		define ("PATH_TO_UNZIP",$ilIliasIniFile->readVariable("tools","unzip"));
		define ("PATH_TO_JAVA",$ilIliasIniFile->readVariable("tools","java"));
		define ("PATH_TO_HTMLDOC",$ilIliasIniFile->readVariable("tools","htmldoc"));
		define ("URL_TO_LATEX",$ilIliasIniFile->readVariable("tools","latex"));
		define ("PATH_TO_FOP",$ilIliasIniFile->readVariable("tools","fop"));

		// read virus scanner settings
		switch ($ilIliasIniFile->readVariable("tools", "vscantype"))
		{
			case "sophos":
				define("IL_VIRUS_SCANNER", "Sophos");
				define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
				define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
				break;

			case "antivir":
				define("IL_VIRUS_SCANNER", "AntiVir");
				define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
				define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
				break;

			case "clamav":
				define("IL_VIRUS_SCANNER", "ClamAV");
				define("IL_VIRUS_SCAN_COMMAND", $ilIliasIniFile->readVariable("tools", "scancommand"));
				define("IL_VIRUS_CLEAN_COMMAND", $ilIliasIniFile->readVariable("tools", "cleancommand"));
				break;

			default:
				define("IL_VIRUS_SCANNER", "None");
				break;
		}

		//$this->buildHTTPPath();
	}

	/**
	* builds http path
	*
	* this is also used by other classes now,
	* e.g. in ilSoapAuthenticationCAS.php
	*/
	function buildHTTPPath()
	{
		include_once 'classes/class.ilHTTPS.php';
		$https = new ilHTTPS();

	    if($https->isDetected())
		{
			$protocol = 'https://';
		}
		else
		{
			$protocol = 'http://';
		}
		$host = $_SERVER['HTTP_HOST'];

		if(!defined('ILIAS_MODULE'))
		{
			$path = pathinfo($_SERVER['REQUEST_URI']);
			if(!$path['extension'])
			{
				$uri = $_SERVER['REQUEST_URI'];
			}
			else
			{
				$uri = dirname($_SERVER['REQUEST_URI']);
			}
		}
		else
		{
			// if in module remove module name from HTTP_PATH
			$path = dirname($_SERVER['REQUEST_URI']);

			// dirname cuts the last directory from a directory path e.g content/classes return content

			$module = ilUtil::removeTrailingPathSeparators(ILIAS_MODULE);

			$dirs = explode('/',$module);
			$uri = $path;
			foreach($dirs as $dir)
			{
				$uri = dirname($uri);
			}
		}
		return define('ILIAS_HTTP_PATH',ilUtil::removeTrailingPathSeparators($protocol.$host.$uri));
	}


	/**
	* This method determines the current client and sets the
	* constant CLIENT_ID.
	*/
	function determineClient()
	{
		global $ilIliasIniFile;

		// check whether ini file object exists
		if (!is_object($ilIliasIniFile))
		{
			die ("Fatal Error: ilInitialisation::determineClient called without initialisation of ILIAS ini file object.");
		}

		// set to default client if empty
		if ($_GET["client_id"] != "")
		{
			setcookie("ilClientId", $_GET["client_id"]);
			$_COOKIE["ilClientId"] = $_GET["client_id"];
		}
		else if (!$_COOKIE["ilClientId"])
		{
			// to do: ilias ini raus nehmen
			$client_id = $ilIliasIniFile->readVariable("clients","default");
			setcookie("ilClientId", $client_id);
			$_COOKIE["ilClientId"] = $client_id;
//echo "set cookie";
		}
//echo "-".$_COOKIE["ilClientId"]."-";
		define ("CLIENT_ID", $_COOKIE["ilClientId"]);
	}

	/**
	* This method provides a global instance of class ilIniFile for the
	* client.ini.php file in variable $ilClientIniFile.
	*
	* It initializes a lot of constants accordingly to the settings in
	* the client.ini.php file.
	*
	* Preconditions: ILIAS_WEB_DIR and CLIENT_ID must be set.
	*
	* @return	boolean		true, if no error occured with client init file
	*						otherwise false
	*/
	function initClientIniFile()
	{
		global $ilClientIniFile;

		// check whether ILIAS_WEB_DIR is set.
		if (ILIAS_WEB_DIR == "")
		{
			die ("Fatal Error: ilInitialisation::initClientIniFile called without ILIAS_WEB_DIR.");
		}

		// check whether CLIENT_ID is set.
		if (CLIENT_ID == "")
		{
			die ("Fatal Error: ilInitialisation::initClientIniFile called without CLIENT_ID.");
		}

		$ini_file = "./".ILIAS_WEB_DIR."/".CLIENT_ID."/client.ini.php";

		// get settings from ini file
		require_once("classes/class.ilIniFile.php");
		$ilClientIniFile = new ilIniFile($ini_file);
		$GLOBALS['ilClientIniFile'] =& $ilClientIniFile;
		$ilClientIniFile->read();

		// if no ini-file found switch to setup routine
		if ($ilClientIniFile->ERROR != "")
		{
			return false;
		}

		// set constants
		define ("DEBUG",$ilClientIniFile->readVariable("system","DEBUG"));
		define ("DEVMODE",$ilClientIniFile->readVariable("system","DEVMODE"));
		define ("ROOT_FOLDER_ID",$ilClientIniFile->readVariable('system','ROOT_FOLDER_ID'));
		define ("SYSTEM_FOLDER_ID",$ilClientIniFile->readVariable('system','SYSTEM_FOLDER_ID'));
		define ("ROLE_FOLDER_ID",$ilClientIniFile->readVariable('system','ROLE_FOLDER_ID'));
		define ("MAIL_SETTINGS_ID",$ilClientIniFile->readVariable('system','MAIL_SETTINGS_ID'));

		define ("SYSTEM_MAIL_ADDRESS",$ilClientIniFile->readVariable('system','MAIL_SENT_ADDRESS')); // Change SS
		define ("MAIL_REPLY_WARNING",$ilClientIniFile->readVariable('system','MAIL_REPLY_WARNING')); // Change SS

		define ("MAXLENGTH_OBJ_TITLE",125);#$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_TITLE'));
		define ("MAXLENGTH_OBJ_DESC",$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_DESC'));

		define ("CLIENT_DATA_DIR",ILIAS_DATA_DIR."/".CLIENT_ID);
		define ("CLIENT_WEB_DIR",ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".CLIENT_ID);
		define ("CLIENT_NAME",$ilClientIniFile->readVariable('client','name')); // Change SS

		// build dsn of database connection and connect
		define ("IL_DSN", $ilClientIniFile->readVariable("db","type").
					 "://".$ilClientIniFile->readVariable("db", "user").
					 ":".$ilClientIniFile->readVariable("db", "pass").
					 "@".$ilClientIniFile->readVariable("db", "host").
					 "/".$ilClientIniFile->readVariable("db", "name"));

		return true;
	}

	/**
	* handle maintenance mode
	*/
	function handleMaintenanceMode()
	{
		global $ilClientIniFile;

		if (!$ilClientIniFile->readVariable("client","access"))
		{
			if (is_file("./maintenance.html"))
			{
				ilUtil::redirect("./maintenance.html");
			}
			else
			{
				// to do: include standard template here
				die('<br /><p style="text-align:center;">The server is not '.
					'available due to maintenance. We apologise for any inconvenience.</p>');
			}
		}
	}

	/**
	* initialise database object $ilDB
	*
	* precondition: IL_DSN must be set
	*/
	function initDatabase()
	{
		global $ilDB;

		// check whether ILIAS_WEB_DIR is set.
		if (IL_DSN == "")
		{
			die ("Fatal Error: ilInitialisation::initDatabase called without IL_DSN.");
		}

		// build dsn of database connection and connect
		require_once("classes/class.ilDBx.php");
		$ilDB = new ilDBx(IL_DSN);
		$GLOBALS['ilDB'] =& $ilDB;
	}
	
	/**
	* initialise event handler ilAppEventHandler
	*/
	function initEventHandling()
	{
		global $ilAppEventHandler;

		// build dsn of database connection and connect
		require_once("./Services/EventHandling/classes/class.ilAppEventHandler.php");
		$ilAppEventHandler = new ilAppEventHandler();
		$GLOBALS['ilAppEventHandler'] =& $ilAppEventHandler;
	}

	/**
	* set session handler to db
	*/
	function setSessionHandler()
	{
		global $ilErr;

		// set session handler
		if(ini_get('session.save_handler') != 'user')
		{
			ini_set("session.save_handler", "user");
		}
		if (!db_set_save_handler())
		{
			die("Please turn off Safe mode OR set session.save_handler to \"user\" in your php.ini");
		}

	}

	/**
	* initialise $ilSettings object and define constants
	*/
	function initSettings()
	{
		global $ilSetting;

		require_once("Services/Administration/classes/class.ilSetting.php");
		$ilSetting = new ilSetting();
		$GLOBALS['ilSetting'] =& $ilSetting;

		// set anonymous user & role id and system role id
		define ("ANONYMOUS_USER_ID", $ilSetting->get("anonymous_user_id"));
		define ("ANONYMOUS_ROLE_ID", $ilSetting->get("anonymous_role_id"));
		define ("SYSTEM_USER_ID", $ilSetting->get("system_user_id"));
		define ("SYSTEM_ROLE_ID", $ilSetting->get("system_role_id"));

		// recovery folder
		define ("RECOVERY_FOLDER_ID", $ilSetting->get("recovery_folder_id"));

		// installation id
		define ("IL_INST_ID", $ilSetting->get("inst_id",0));

		// define default suffix replacements
		define ("SUFFIX_REPL_DEFAULT", "php,php3,php4,inc,lang,phtml,htaccess");
		define ("SUFFIX_REPL_ADDITIONAL", $ilSetting->get("suffix_repl_additional"));

		$this->buildHTTPPath();
	}


	/**
	* determine current script and path to main ILIAS directory
	*/
	function determineScriptAndUpDir()
	{
		$this->script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);
		$dirname = dirname($_SERVER["PHP_SELF"]);
		$ilurl = @parse_url(ILIAS_HTTP_PATH);
		$subdir = substr(strstr($dirname,$ilurl["path"]),strlen($ilurl["path"]));
		$updir = "";

		if ($subdir)
		{
			$num_subdirs = substr_count($subdir,"/");

			for ($i=1;$i<=$num_subdirs;$i++)
			{
				$updir .= "../";
			}
		}
		$this->updir = $updir;
	}

	/**
	* provide $styleDefinition object
	*/
	function initStyle()
	{
		global $ilBench, $styleDefinition;

		// load style definitions
		$ilBench->start("Core", "HeaderInclude_getStyleDefinitions");
		$styleDefinition = new ilStyleDefinition();
		$GLOBALS['styleDefinition'] =& $styleDefinition;
		$styleDefinition->startParsing();
		$ilBench->stop("Core", "HeaderInclude_getStyleDefinitions");
	}


	/**
	* set skin and style via $_GET parameters "skin" and "style"
	*/
	function handleStyle()
	{
		global $styleDefinition;

		if ($_GET['skin']  && $_GET['style'])
		{
			include_once("./Services/Style/classes/class.ilObjStyleSettings.php");
			if ($styleDefinition->styleExists($_GET['skin'], $_GET['style']) &&
				ilObjStyleSettings::_lookupActivatedStyle($_GET['skin'], $_GET['style']))
			{
				$_SESSION['skin'] = $_GET['skin'];
				$_SESSION['style'] = $_GET['style'];
			}
		}
		if ($_SESSION['skin'] && $_SESSION['style'])
		{
			include_once("./Services/Style/classes/class.ilObjStyleSettings.php");
			if ($styleDefinition->styleExists($_SESSION['skin'], $_SESSION['style']) &&
				ilObjStyleSettings::_lookupActivatedStyle($_SESSION['skin'], $_SESSION['style']))
			{
				$ilias->account->skin = $_SESSION['skin'];
				$ilias->account->prefs['style'] = $_SESSION['style'];
			}
		}
	}

	function initUserAccount()
	{
		global $ilUser, $ilLog, $ilAuth;

		//get user id
		if (empty($_SESSION["AccountId"]))
		{
			$_SESSION["AccountId"] = $ilUser->checkUserId();

			// assigned roles are stored in $_SESSION["RoleId"]
			// DISABLED smeyer 20070510
			#$rbacreview = new ilRbacReview();
			#$GLOBALS['rbacreview'] =& $rbacreview;
			#$_SESSION["RoleId"] = $rbacreview->assignedRoles($_SESSION["AccountId"]);
		} // TODO: do we need 'else' here?
		else
		{
			// init user
			$ilUser->setId($_SESSION["AccountId"]);
		}

		// load account data of current user
		$ilUser->read();
	}

	function checkUserClientIP()
	{
		global $ilUser, $ilLog, $ilAuth, $ilias;

		// check client ip
		$clientip = $ilUser->getClientIP();
		if (trim($clientip) != "")
		{
			$clientip = preg_replace("/[^0-9.?*,:]+/","",$clientip);
			$clientip = str_replace(".","\\.",$clientip);
			$clientip = str_replace(Array("?","*",","), Array("[0-9]","[0-9]*","|"), $clientip);
			if (!preg_match("/^".$clientip."$/", $_SERVER["REMOTE_ADDR"])) 
			{
				$ilLog ->logError(1,
				$ilias->account->getLogin().":".$_SERVER["REMOTE_ADDR"].":".$message);
				$ilAuth->logout();
				@session_destroy();
				ilUtil::redirect("login.php?wrong_ip=true");
			}
		}
	}

	function checkUserAgreement()
	{
		global $ilUser, $ilAuth;

		// are we currently in user agreement acceptance?
		$in_user_agreement = false;
		if (strtolower($_GET["cmdClass"]) == "ilstartupgui" &&
			(strtolower($_GET["cmd"]) == "getacceptance" ||
			(is_array($_POST["cmd"]) &&
			key($_POST["cmd"]) == "getAcceptance")))
		{
			$in_user_agreement = true;
		}

		// check wether user has accepted the user agreement
		//	echo "-".$script;
		if (!$ilUser->hasAcceptedUserAgreement() &&
			$ilAuth->getAuth() &&
			!$in_user_agreement &&
			$ilUser->getId() != ANONYMOUS_USER_ID)
		{
			ilUtil::redirect("ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&target=".$_GET["target"]."&cmd=getAcceptance");
		}
	}


	/**
	* go to public section
	*/
	function goToPublicSection()
	{
		global $ilAuth;

		// logout and end previous session
		$ilAuth->logout();
		session_unset();
		session_destroy();

		// new session and login as anonymous
		$this->setSessionHandler();
		session_start();
		$_POST["username"] = "anonymous";
		$_POST["password"] = "anonymous";
		ilAuthUtils::_initAuth();
		$ilAuth->start();

		if (ANONYMOUS_USER_ID == "")
		{
			die ("Public Section enabled, but no Anonymous user found.");
		}
		if (!$ilAuth->getAuth())
		{
			die("ANONYMOUS user with the object_id ".ANONYMOUS_USER_ID." not found!");
		}

		// BEGIN WebDAV: Don't do a redirect to the public area, if the user
		//             performs a get request.
		/*
		if (ilPlugin::isPluginActive('ilUsabilityPlugin'))
		{
			if ($_SERVER['REQUEST_METHOD'] == 'GET')
			{
				$_SESSION["AccountId"] = ANONYMOUS_USER_ID;
				$this->initUserAccount();
				return;
			}
		}
		*/
		// END WebDAV: Don't do a redirect to the public area, if the user

		// if target given, try to go there
		if ($_GET["target"] != "")
		{
			$this->initUserAccount();

			// target is accessible -> goto target
			include_once("Services/Init/classes/class.ilStartUpGUI.php");
			if	(ilStartUpGUI::_checkGoto($_GET["target"]))
			{
				// Disabled: GET parameter is kept, since no redirect. smeyer
				// additional parameter capturing for survey access codes
				/*
				$survey_parameter = "";
				if (array_key_exists("accesscode", $_GET))
				{
					$survey_parameter = "&accesscode=" . $_GET["accesscode"];
				}
				*/
				// Disabled redirect for public section
				return true;
				#ilUtil::redirect(ILIAS_HTTP_PATH.
				#	"/goto.php?target=".$_GET["target"].$survey_parameter);
			}
			else	// target is not accessible -> login
			{
				$this->goToLogin();
			}
		}

		$_GET["ref_id"] = ROOT_FOLDER_ID;

		$_GET["cmd"] = "frameset";
		$jump_script = "repository.php";
		
		$script = $this->updir.$jump_script."?reloadpublic=1&cmd=".$_GET["cmd"]."&ref_id=".$_GET["ref_id"];

		// todo do it better, if JS disabled
		//echo "<script language=\"Javascript\">\ntop.location.href = \"".$script."\";\n</script>\n";
		echo "<script language=\"Javascript\">\ntop.location.href = \"".$script."\";\n</script>\n".
			'Please click <a href="'.$script.'">here</a> if you are not redirected automatically.';
		exit;
	}


	/**
	* go to login
	*/
	function goToLogin($a_auth_stat = "")
	{
		global $PHP_SELF;

		session_unset();
		session_destroy();

		$add = "";
		if ($_GET["soap_pw"] != "")
		{
			$add = "&soap_pw=".$_GET["soap_pw"]."&ext_uid=".$_GET["ext_uid"];
		}

		$script = $this->updir."login.php?target=".$_GET["target"]."&client_id=".$_COOKIE["ilClientId"].
			"&auth_stat=".$a_auth_stat.$add;

		// todo do it better, if JS disabled
		// + this is, when session "ends", so
		// we should try to prevent some information about current
		// location
		//
		// check whether we are currently doing a goto call
		if (is_int(strpos($PHP_SELF, "goto.php")) && $_GET["soap_pw"] == "" &&
			$_GET["reloadpublic"] != "1")
		{
			$script = $this->updir."goto.php?target=".$_GET["target"]."&client_id=".CLIENT_ID.
				"&reloadpublic=1";
		}

		echo "<script language=\"Javascript\">\ntop.location.href = \"".$script."\";\n</script>\n".
			'Please click <a href="'.$script.'">here</a> if you are not redirected automatically.';

		exit;

	}

	/**
	* $lng initialisation
	*/
	function initLanguage()
	{
		global $ilBench, $lng, $ilUser;
		//init language
		$ilBench->start("Core", "HeaderInclude_initLanguage");

		if (is_null($_SESSION['lang']))
		{
			if ($_GET["lang"])
			{
				$_GET["lang"] = $_GET["lang"];
			}
			else
			{
				if (is_object($ilUser))
				{
					$_GET["lang"] = $ilUser->getPref("language");
				}
			}
		}
		if ($_POST['change_lang_to'] != "")
		{
			$_GET['lang'] = ilUtil::stripSlashes($_POST['change_lang_to']);
		}

		$_SESSION['lang'] = ($_GET['lang']) ? $_GET['lang'] : $_SESSION['lang'];

		// prefer personal setting when coming from login screen
		// Added check for ilUser->getId > 0 because it is 0 when the language is changed and the user agreement should be displayes (Helmut Schottm��ller, 2006-10-14)
		if (is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID && $ilUser->getId() > 0)
		{
			$_SESSION['lang'] = $ilUser->getPref("language");
		}

		$lng = new ilLanguage($_SESSION['lang']);
		$GLOBALS['lng'] =& $lng;
		$ilBench->stop("Core", "HeaderInclude_initLanguage");

	}

	/**
	* $ilAccess and $rbac... initialisation
	*/
	function initAccessHandling()
	{
		global $ilBench, $rbacsystem, $rbacadmin, $rbacreview;

		$ilBench->start("Core", "HeaderInclude_initRBAC");
		$rbacsystem = new ilRbacSystem();
		$GLOBALS['rbacsystem'] =& $rbacsystem;
		$rbacadmin = new ilRbacAdmin();
		$GLOBALS['rbacadmin'] =& $rbacadmin;
		$rbacreview = new ilRbacReview();
		$GLOBALS['rbacreview'] =& $rbacreview;
		$ilAccess =& new ilAccessHandler();
		$GLOBALS["ilAccess"] =& $ilAccess;
		$ilBench->stop("Core", "HeaderInclude_initRBAC");
	}


	/**
	* ilias initialisation
	* @param string $context this is used for circumvent redirects to the login page if called e.g. by soap	
	*/
	function initILIAS($context = "web")
	{
		global $ilDB, $ilUser, $ilLog, $ilErr, $ilClientIniFile, $ilIliasIniFile,
			$ilSetting, $ilias, $https, $ilObjDataCache,
			$ilLog, $objDefinition, $lng, $ilCtrl, $ilBrowser, $ilHelp,
			$ilTabs, $ilMainMenu, $rbacsystem, $ilNavigationHistory;

		// remove unsafe characters
		$this->removeUnsafeCharacters();

		// include common code files
		$this->requireCommonIncludes();
		global $ilBench;

		// set error handler (to do: check preconditions for error handler to work)
		$ilBench->start("Core", "HeaderInclude_GetErrorHandler");
		$ilErr = new ilErrorHandling();
		$GLOBALS['ilErr'] =& $ilErr;
		$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		$ilBench->stop("Core", "HeaderInclude_GetErrorHandler");


		// prepare file access to work with safe mode (has been done in class ilias before)
		umask(0117);


		// $ilIliasIniFile initialisation
		$this->initIliasIniFile();


		// CLIENT_ID determination
		$this->determineClient();

		// $ilAppEventHandler initialisation
		$this->initEventHandling();

		// $ilClientIniFile initialisation
		if (!$this->initClientIniFile())
		{
			$c = $_COOKIE["ilClientId"];
			setcookie("ilClientId", $ilIliasIniFile->readVariable("clients","default"));
			$_COOKIE["ilClientId"] = $ilIliasIniFile->readVariable("clients","default");
			if (CLIENT_ID != "" && CLIENT_ID != $ilIliasIniFile->readVariable("clients","default"))
			{
				ilUtil::redirect("index.php?client_id=".$ilIliasIniFile->readVariable("clients","default"));
			}
			else
			{
				echo ("Client $c does not exist. ".'Please <a href="./index.php">click here</a> to return to the default client.');
			}
			exit;
			//ilUtil::redirect("./setup/setup.php");	// to do: this could fail in subdirectories
													// this is also source of a bug (see mantis)
		}


		// maintenance mode
		$this->handleMaintenanceMode();

		// $ilDB initialisation
		$this->initDatabase();

		// init plugin admin class
		include_once("./Services/Component/classes/class.ilPluginAdmin.php");
		$ilPluginAdmin = new ilPluginAdmin();
		$GLOBALS['ilPluginAdmin'] = $ilPluginAdmin;

		// set session handler
		$this->setSessionHandler();

		// $ilSetting initialisation
		$this->initSettings();


		// $ilLog initialisation
		$this->initLog();

		// $ilAuth initialisation
		include_once("./Services/Authentication/classes/class.ilAuthUtils.php");
		ilAuthUtils::_initAuth();
		global $ilAuth;

		// Do not accept external session ids
		if ($_GET["PHPSESSID"] != "")
		{
			$_GET["PHPSESSID"] == "";
			session_regenerate_id();
		}

		// $ilias initialisation
			global $ilias, $ilBench;
		$ilBench->start("Core", "HeaderInclude_GetILIASObject");
		$ilias = & new ILIAS();
		$GLOBALS['ilias'] =& $ilias;
		$ilBench->stop("Core", "HeaderInclude_GetILIASObject");

		// test: trace function calls in debug mode
		if (DEVMODE)
		{
			if (function_exists("xdebug_start_trace"))
			{
				//xdebug_start_trace("/tmp/test.txt");
			}
		}


		// $https initialisation
		require_once './classes/class.ilHTTPS.php';
		$https =& new ilHTTPS();
		$GLOBALS['https'] =& $https;
		$https->checkPort();


		// $ilObjDataCache initialisation
		$ilObjDataCache = new ilObjectDataCache();
		$GLOBALS['ilObjDataCache'] =& $ilObjDataCache;

		// workaround: load old post variables if error handler 'message' was called
		if ($_SESSION["message"])
		{
			$_POST = $_SESSION["post_vars"];
		}


		// put debugging functions here
		require_once "include/inc.debug.php";


		// $objDefinition initialisation
		$ilBench->start("Core", "HeaderInclude_getObjectDefinitions");
		$objDefinition = new ilObjectDefinition();
		$GLOBALS['objDefinition'] =& $objDefinition;
//		$objDefinition->startParsing();
		$ilBench->stop("Core", "HeaderInclude_getObjectDefinitions");

		// $ilAccess and $rbac... initialisation
		$this->initAccessHandling();

		// init tree
		$tree = new ilTree(ROOT_FOLDER_ID);
		$GLOBALS['tree'] =& $tree;

		// authenticate & start session
		PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, "errorHandler"));
		$ilBench->start("Core", "HeaderInclude_Authentication");
//var_dump($_SESSION);
		$ilAuth->start();
//var_dump($_SESSION);
		$ilias->setAuthError($ilErr->getLastError());
		$ilBench->stop("Core", "HeaderInclude_Authentication");

		// workaround: force login
		if ($_GET["cmd"] == "force_login" || $this->script == "login.php")
		{
			$ilAuth->logout();
			$_SESSION = array();
			$_SESSION["AccountId"] = "";
			$ilAuth->start();
			$ilias->setAuthError($ilErr->getLastError());
		}

		// check correct setup
		if (!$ilias->getSetting("setup_ok"))
		{
			die("Setup is not completed. Please run setup routine again.");
		}

		// $ilUser initialisation (1)
		$ilBench->start("Core", "HeaderInclude_getCurrentUser");
		$ilUser = new ilObjUser();
		$ilias->account =& $ilUser;
		$GLOBALS['ilUser'] =& $ilUser;
		$ilBench->stop("Core", "HeaderInclude_getCurrentUser");


		// $ilCtrl initialisation
		$ilCtrl = new ilCtrl();
		$GLOBALS['ilCtrl'] =& $ilCtrl;

		// determin current script and up-path to main directory
		// (sets $this->script and $this->updir)
		$this->determineScriptAndUpDir();

		// $styleDefinition initialisation and style handling for login and co.
		$this->initStyle();
		if (in_array($this->script,
			array("login.php", "register.php", "view_usr_agreement.php"))
			|| $_GET["baseClass"] == "ilStartUpGUI")
		{
			$this->handleStyle();
		}


		// handle ILIAS 2 imported users:
		// check ilias 2 password, if authentication failed
		// only if AUTH_LOCAL
//echo "A";
		if (AUTH_CURRENT == AUTH_LOCAL && !$ilAuth->getAuth() && $this->script == "login.php" && $_POST["username"] != "")
		{
			if (ilObjUser::_lookupHasIlias2Password(ilUtil::stripSlashes($_POST["username"])))
			{
				if (ilObjUser::_switchToIlias3Password(
					ilUtil::stripSlashes($_POST["username"]),
					ilUtil::stripSlashes($_POST["password"])))
				{
					$ilAuth->start();
					$ilias->setAuthError($ilErr->getLastError());
					ilUtil::redirect("index.php");
				}
			}
		}

//echo $_POST; exit;
		//
		// SUCCESSFUL AUTHENTICATION
		//
//echo "<br>B-".$ilAuth->getAuth()."-".$ilAuth->_sessionName."-";
//var_dump ($session[_authsession]);
		if ($ilAuth->getAuth() && $ilias->account->isCurrentUserActive())
		{
//echo "C"; exit;
			$ilBench->start("Core", "HeaderInclude_getCurrentUserAccountData");
//var_dump($_SESSION);
			// get user data
			$this->initUserAccount();
//var_dump($_SESSION);
			// check client IP of user
			$this->checkUserClientIP();

			// check user agreement
			$this->checkUserAgreement();

			// update last_login date once the user logged in
			if ($this->script == "login.php" ||
				$_GET["baseClass"] == "ilStartUpGUI")
			{
				$ilUser->refreshLogin();
			}

			// set hits per page for all lists using table module
			$_GET['limit'] = $_SESSION['tbl_limit'] = (int) $ilUser->getPref('hits_per_page');

			// the next line makes it impossible to save the offset somehow in a session for
			// a specific table (I tried it for the user administration).
			// its not posssible to distinguish whether it has been set to page 1 (=offset = 0)
			// or not set at all (then we want the last offset, e.g. being used from a session var).
			// So I added the wrapping if statement. Seems to work (hopefully).
			// Alex April 14th 2006
			if ($_GET['offset'] != "")							// added April 14th 2006
			{
				$_GET['offset'] = (int) $_GET['offset'];		// old code
			}

			$ilBench->stop("Core", "HeaderInclude_getCurrentUserAccountData");
		}
		elseif (
					$this->script != "login.php"
					and $this->script != "shib_login.php"
					and $this->script != "error.php"
					and $this->script != "index.php"
					and $this->script != "view_usr_agreement.php"
					and $this->script != "register.php"
					and $this->script != "chat.php"
					and $this->script != "pwassist.php"
				)
		{
			//
			// AUTHENTICATION FAILED
			//

			// authentication failed due to inactive user?
			if ($ilAuth->getAuth() && !$ilUser->isCurrentUserActive())
			{
				$inactive = true;
			}

			// jump to public section (to do: is this always the indended
			// behaviour, login could be another possibility (including
			// message)
//echo "-".$_GET["baseClass"]."-";
			if ($_GET["baseClass"] != "ilStartUpGUI")
			{
				// $lng initialisation
				$this->initLanguage();

				if ($ilSetting->get("pub_section") &&
					($ilAuth->getStatus() == "" || $ilAuth->getStatus() == AUTH_EXPIRED ||
						$ilAuth->getStatus() == AUTH_IDLED) &&
					$_GET["reloadpublic"] != "1")
				{
					$this->goToPublicSection();
				}
				else
				{
					if ($context == "web")
					{
						// normal access by webinterface
						$this->goToLogin($ilAuth->getStatus());
						exit;
					} else {
						// called by soapAuthenticationLdap
						return;
					}
					
				}
				// we should not get here => public section needs no redirect smeyer
				// exit;
			}
		}

		//
		// SUCCESSFUL AUTHENTICATED or NON-AUTH-AREA (Login, Registration, ...)
		//

		// $lng initialisation
		$this->initLanguage();

		// store user language in tree
		$GLOBALS['tree']->initLangCode();

		// instantiate main template
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$GLOBALS['tpl'] =& $tpl;


		// ### AA 03.10.29 added new LocatorGUI class ###
		// when locator data array does not exist, initialise
		if ( !isset($_SESSION["locator_level"]) )
		{
			$_SESSION["locator_data"] = array();
			$_SESSION["locator_level"] = -1;
		}
		// initialise global ilias_locator object
		$ilias_locator = new ilLocatorGUI();			// deprecated
		$ilLocator = new ilLocatorGUI();
		$GLOBALS['ilias_locator'] =& $ilias_locator;	// deprecated
		$GLOBALS['ilLocator'] =& $ilLocator;

		// load style definitions
		$ilBench->start("Core", "HeaderInclude_getStyleDefinitions");
		$styleDefinition = new ilStyleDefinition();
		$GLOBALS['styleDefinition'] =& $styleDefinition;
		$styleDefinition->startParsing();
		$ilBench->stop("Core", "HeaderInclude_getStyleDefinitions");

		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);

		// Init Navigation History
		$ilNavigationHistory = new ilNavigationHistory();
		$GLOBALS['ilNavigationHistory'] =& $ilNavigationHistory;

		// init infopanel

		// provide global browser information
		$ilBrowser = new ilBrowser();
		$GLOBALS['ilBrowser'] =& $ilBrowser;

		// provide global help object
		$ilHelp = new ilHelp();
		$GLOBALS['ilHelp'] =& $ilHelp;

		// main tabs gui
		include_once 'classes/class.ilTabsGUI.php';
		$ilTabs = new ilTabsGUI();
		$GLOBALS['ilTabs'] =& $ilTabs;

		// main menu
		include_once 'classes/class.ilMainMenuGUI.php';
		$ilMainMenu = new ilMainMenuGUI("_top");
		$GLOBALS['ilMainMenu'] =& $ilMainMenu;

		// Store online time of user
		ilOnlineTracking::_updateAccess($ilUser->getId());

		$ilBench->stop("Core", "HeaderInclude");
		$ilBench->save();

	}

	/**
	* Initialisation for feed.php
	*/
	function initFeed()
	{
		global $ilDB, $ilUser, $ilLog, $ilErr, $ilClientIniFile, $ilIliasIniFile,
			$ilSetting, $ilias, $https, $ilObjDataCache,
			$ilLog, $objDefinition, $lng, $ilCtrl, $ilBrowser, $ilHelp,
			$ilTabs, $ilMainMenu, $rbacsystem, $ilNavigationHistory;

		// remove unsafe characters
		$this->removeUnsafeCharacters();

		// include common code files
		$this->requireCommonIncludes();
		global $ilBench;

		// $ilAppEventHandler initialisation
		$this->initEventHandling();

		// set error handler (to do: check preconditions for error handler to work)
		$ilBench->start("Core", "HeaderInclude_GetErrorHandler");
		$ilErr = new ilErrorHandling();
		$GLOBALS['ilErr'] =& $ilErr;
		$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK,array($ilErr,'errorHandler'));
		$ilBench->stop("Core", "HeaderInclude_GetErrorHandler");

		// prepare file access to work with safe mode (has been done in class ilias before)
		umask(0117);

		// $ilIliasIniFile initialisation
		$this->initIliasIniFile();

		// CLIENT_ID determination
		$this->determineClient();

		// $ilClientIniFile initialisation
		if (!$this->initClientIniFile())
		{
			$c = $_COOKIE["ilClientId"];
			setcookie("ilClientId", $ilIliasIniFile->readVariable("clients","default"));
			$_COOKIE["ilClientId"] = $ilIliasIniFile->readVariable("clients","default");
			echo ("Client $c does not exist. Please reload this page to return to the default client.");
			exit;
		}

		// maintenance mode
		$this->handleMaintenanceMode();

		// $ilDB initialisation
		$this->initDatabase();
		
		// init plugin admin class
		include_once("./Services/Component/classes/class.ilPluginAdmin.php");
		$ilPluginAdmin = new ilPluginAdmin();
		$GLOBALS['ilPluginAdmin'] = $ilPluginAdmin;

		// $ilObjDataCache initialisation
		$ilObjDataCache = new ilObjectDataCache();
		$GLOBALS['ilObjDataCache'] =& $ilObjDataCache;

		// init settings
		$this->initSettings();

		// init tree
		$tree = new ilTree(ROOT_FOLDER_ID);
		$GLOBALS['tree'] =& $tree;

		// init language
		$lng = new ilLanguage($ilClientIniFile->readVariable("language","default"));
		$GLOBALS['lng'] =& $lng;

	}

	function initLog() {
		global $ilLog;
		$log = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,CLIENT_ID,ILIAS_LOG_ENABLED,ILIAS_LOG_LEVEL);
		$GLOBALS['log'] =& $log;
		$ilLog =& $log;
		$GLOBALS['ilLog'] =& $ilLog;
	}

	function initILIASObject() {
		global $ilias, $ilBench;
		$ilBench->start("Core", "HeaderInclude_GetILIASObject");
		$ilias = & new ILIAS();
		$GLOBALS['ilias'] =& $ilias;
		$ilBench->stop("Core", "HeaderInclude_GetILIASObject");
//var_dump($_SESSION);
	}
}
?>
