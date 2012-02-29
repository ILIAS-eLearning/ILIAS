<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "Services/Context/classes/class.ilContext.php";

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
	protected function removeUnsafeCharacters()
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
	 * 
	 * used in SOAP
	 */
	public function requireCommonIncludes()
	{			
		// pear
		require_once("include/inc.get_pear.php");
		require_once("include/inc.check_pear.php");
		require_once "PEAR.php";
		
		// ilTemplate
		if(ilContext::usesTemplate())
		{
			// HTML_Template_IT support
			@include_once "HTML/Template/ITX.php";		// new implementation
			if (class_exists("HTML_Template_ITX"))
			{
				include_once "classes/class.ilTemplateHTMLITX.php";
			}
			else
			{
				include_once "HTML/ITX.php";		// old implementation
				include_once "classes/class.ilTemplateITX.php";
			}
			require_once "classes/class.ilTemplate.php";
		}		
				
		// really always required?
		require_once "./Services/Utilities/classes/class.ilUtil.php";	
		require_once "classes/class.ilFormat.php";
		require_once "./Services/Calendar/classes/class.ilDatePresentation.php";														
		require_once "include/inc.ilias_version.php";	
		
		$this->initGlobal("ilBench", "ilBenchmark", "classes/class.ilBenchmark.php");
	}
	
	/**
	 * This is a hack for CAS authentication.
	 * 
	 * Since the phpCAS lib ships with its own compliance functions.
	 */
	protected function includePhp5Compliance()
	{
		// php5 downward complaince to php 4 dom xml and clone method
		if (version_compare(PHP_VERSION,'5','>='))
		{
			include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
			if(ilAuthFactory::getContext() != ilAuthFactory::CONTEXT_CAS)
			{
				require_once("include/inc.xml5compliance.php");
			}
			require_once("include/inc.xsl5compliance.php");
		}
	}

	/**
	 * This method provides a global instance of class ilIniFile for the
	 * ilias.ini.php file in variable $ilIliasIniFile.
	 *
	 * It initializes a lot of constants accordingly to the settings in
	 * the ilias.ini.php file.
	 * 
	 * Used in SOAP
	 */
	public function initIliasIniFile()
	{		
		require_once("classes/class.ilIniFile.php");
		$ilIliasIniFile = new ilIniFile("./ilias.ini.php");				
		$ilIliasIniFile->read();
		$this->initGlobal('ilIliasIniFile', $ilIliasIniFile);

		// initialize constants
		define("ILIAS_DATA_DIR",$ilIliasIniFile->readVariable("clients","datadir"));
		define("ILIAS_WEB_DIR",$ilIliasIniFile->readVariable("clients","path"));
		define("ILIAS_ABSOLUTE_PATH",$ilIliasIniFile->readVariable('server','absolute_path'));

		// logging
		define ("ILIAS_LOG_DIR",$ilIliasIniFile->readVariable("log","path"));
		define ("ILIAS_LOG_FILE",$ilIliasIniFile->readVariable("log","file"));
		define ("ILIAS_LOG_ENABLED",$ilIliasIniFile->readVariable("log","enabled"));
		define ("ILIAS_LOG_LEVEL",$ilIliasIniFile->readVariable("log","level"));
		define ("SLOW_REQUEST_TIME",$ilIliasIniFile->readVariable("log","slow_request_time"));

		// read path + command for third party tools from ilias.ini
		define ("PATH_TO_CONVERT",$ilIliasIniFile->readVariable("tools","convert"));
		define ("PATH_TO_FFMPEG",$ilIliasIniFile->readVariable("tools","ffmpeg"));
		define ("PATH_TO_ZIP",$ilIliasIniFile->readVariable("tools","zip"));
		define ("PATH_TO_MKISOFS",$ilIliasIniFile->readVariable("tools","mkisofs"));
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
		
		$tz = $ilIliasIniFile->readVariable("server","timezone");
		if ($tz != "")
		{
			if (function_exists('date_default_timezone_set'))
			{
				date_default_timezone_set($tz);
			}
		}
		define ("IL_TIMEZONE", $ilIliasIniFile->readVariable("server","timezone"));
	}

	/**
	 * builds http path
	 * 
	 * this is also used by other classes now,
	 * e.g. in ilSoapAuthenticationCAS.php
	 */
	public function buildHTTPPath()
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

		$rq_uri = $_SERVER['REQUEST_URI'];

		// security fix: this failed, if the URI contained "?" and following "/"
		// -> we remove everything after "?"
		if (is_int($pos = strpos($rq_uri, "?")))
		{
			$rq_uri = substr($rq_uri, 0, $pos);
		}

		if(!defined('ILIAS_MODULE'))
		{
			$path = pathinfo($rq_uri);
			if(!$path['extension'])
			{
				$uri = $rq_uri;
			}
			else
			{
				$uri = dirname($rq_uri);
			}
		}
		else
		{
			// if in module remove module name from HTTP_PATH
			$path = dirname($rq_uri);

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
	protected function determineClient()
	{
		global $ilIliasIniFile;

		// check whether ini file object exists
		if (!is_object($ilIliasIniFile))
		{
			$this->abortAndDie("Fatal Error: ilInitialisation::determineClient called without initialisation of ILIAS ini file object.");
		}

		// set to default client if empty
		if ($_GET["client_id"] != "")
		{
			if (!defined("IL_PHPUNIT_TEST"))
			{
				ilUtil::setCookie("ilClientId", $_GET["client_id"]);
			}
		}
		else if (!$_COOKIE["ilClientId"])
		{
			// to do: ilias ini raus nehmen
			$client_id = $ilIliasIniFile->readVariable("clients","default");
			ilUtil::setCookie("ilClientId", $client_id);
		}
		if (!defined("IL_PHPUNIT_TEST"))
		{
			define ("CLIENT_ID", $_COOKIE["ilClientId"]);
		}
		else
		{
			define ("CLIENT_ID", $_GET["client_id"]);
		}
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
	protected function initClientIniFile()
	{
		global $ilIliasIniFile;
		
		// check whether ILIAS_WEB_DIR is set.
		if (ILIAS_WEB_DIR == "")
		{
			$this->abortAndDie("Fatal Error: ilInitialisation::initClientIniFile called without ILIAS_WEB_DIR.");
		}

		// check whether CLIENT_ID is set.
		if (CLIENT_ID == "")
		{
			$this->abortAndDie("Fatal Error: ilInitialisation::initClientIniFile called without CLIENT_ID.");
		}

		$ini_file = "./".ILIAS_WEB_DIR."/".CLIENT_ID."/client.ini.php";

		// get settings from ini file
		require_once("classes/class.ilIniFile.php");
		$ilClientIniFile = new ilIniFile($ini_file);		
		$ilClientIniFile->read();
		
		// invalid client id / client ini
		if ($ilClientIniFile->ERROR != "")
		{
			$c = $_COOKIE["ilClientId"];
			$default_client = $ilIliasIniFile->readVariable("clients","default");						
			ilUtil::setCookie("ilClientId", $default_client);
			if (CLIENT_ID != "" && CLIENT_ID != $default_client &&
				ilContext::supportsRedirects())
			{				
				ilUtil::redirect("index.php?client_id=".$default_client);							
			}
			else
			{
				$this->abortAndDie('Client '.$c.' does not exist. Please '.
					'<a href="./index.php">click here</a> to return to the default client.');
			}	
		}
		
		$this->initGlobal("ilClientIniFile", $ilClientIniFile);

		// set constants
		define ("SESSION_REMINDER_LEADTIME", 30);
		define ("DEBUG",$ilClientIniFile->readVariable("system","DEBUG"));
		define ("DEVMODE",$ilClientIniFile->readVariable("system","DEVMODE"));
		define ("SHOWNOTICES",$ilClientIniFile->readVariable("system","SHOWNOTICES"));
		define ("ROOT_FOLDER_ID",$ilClientIniFile->readVariable('system','ROOT_FOLDER_ID'));
		define ("SYSTEM_FOLDER_ID",$ilClientIniFile->readVariable('system','SYSTEM_FOLDER_ID'));
		define ("ROLE_FOLDER_ID",$ilClientIniFile->readVariable('system','ROLE_FOLDER_ID'));
		define ("MAIL_SETTINGS_ID",$ilClientIniFile->readVariable('system','MAIL_SETTINGS_ID'));
		
		// this is for the online help installation, which sets OH_REF_ID to the
		// ref id of the online module
		define ("OH_REF_ID",$ilClientIniFile->readVariable("system","OH_REF_ID"));

		define ("SYSTEM_MAIL_ADDRESS",$ilClientIniFile->readVariable('system','MAIL_SENT_ADDRESS')); // Change SS
		define ("MAIL_REPLY_WARNING",$ilClientIniFile->readVariable('system','MAIL_REPLY_WARNING')); // Change SS

		define ("MAXLENGTH_OBJ_TITLE",125);#$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_TITLE'));
		define ("MAXLENGTH_OBJ_DESC",$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_DESC'));

		define ("CLIENT_DATA_DIR",ILIAS_DATA_DIR."/".CLIENT_ID);
		define ("CLIENT_WEB_DIR",ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".CLIENT_ID);
		define ("CLIENT_NAME",$ilClientIniFile->readVariable('client','name')); // Change SS

		$val = $ilClientIniFile->readVariable("db","type");
		if ($val == "")
		{
			define ("IL_DB_TYPE", "mysql");
		}
		else
		{
			define ("IL_DB_TYPE", $val);
		}
		
		return true;
	}

	/**
	 * handle maintenance mode
	 */
	protected function handleMaintenanceMode()
	{
		global $ilClientIniFile;

		if (!$ilClientIniFile->readVariable("client","access"))
		{
			if (ilContext::hasHTML() && ilContext::supportsRedirects() &&
				is_file("./maintenance.html"))
			{
				ilUtil::redirect("./maintenance.html");
			}
			else
			{
				$mess = "The server is not available due to maintenance.".
					" We apologise for any inconvenience";
				
				// to do: include standard template here
				if(ilContext::hasHTML())
				{
					$mess = '<br /><p style="text-align:center;">'.$mess.'</p>';
				}
				
				$this->abortAndDie($mess);
			}
		}
	}

	/**
	* initialise database object $ilDB
	*
	*/
	protected function initDatabase()
	{
		// build dsn of database connection and connect
		require_once("./Services/Database/classes/class.ilDBWrapperFactory.php");				
		$ilDB = ilDBWrapperFactory::getWrapper(IL_DB_TYPE);
		$ilDB->initFromIniFile();
		$ilDB->connect();
		
		$this->initGlobal("ilDB", $ilDB);		
	}

	/**
	 * set session handler to db
	 * 
	 * Used in Soap
	 */
	public function setSessionHandler()
	{
		if(ini_get('session.save_handler') != 'user')
		{
			ini_set("session.save_handler", "user");
		}
		
		require_once "include/inc.db_session_handler.php";
		if (!db_set_save_handler())
		{
			$this->abortAndDie("Please turn off Safe mode OR set session.save_handler to \"user\" in your php.ini");
		}
						
		// Do not accept external session ids
		if (!ilSession::_exists(session_id()) && !defined('IL_PHPUNIT_TEST'))
		{
			session_regenerate_id();
		}				
	}
	
	/**
	 * set session cookie params for path, domain, etc.
	 */
	protected function setCookieParams()
	{
		include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
		if(ilAuthFactory::getContext() == ilAuthFactory::CONTEXT_HTTP) 
		{
			$cookie_path = '/';
		}
		elseif ($GLOBALS['COOKIE_PATH'])
		{
			// use a predefined cookie path from WebAccessChecker
	        $cookie_path = $GLOBALS['COOKIE_PATH'];
	    }
		else
		{
			$cookie_path = dirname( $_SERVER['PHP_SELF'] );
		}
		
		/* if ilias is called directly within the docroot $cookie_path
		is set to '/' expecting on servers running under windows..
		here it is set to '\'.
		in both cases a further '/' won't be appended due to the following regex
		*/
		$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";
		
		if($cookie_path == "\\") $cookie_path = '/';
		
		define('IL_COOKIE_EXPIRE',0);
		define('IL_COOKIE_PATH',$cookie_path);
		define('IL_COOKIE_DOMAIN','');
		define('IL_COOKIE_SECURE',false); // Default Value

		// session_set_cookie_params() supports 5th parameter
		// only for php version 5.2.0 and above
		if( version_compare(PHP_VERSION, '5.2.0', '>=') )
		{
			// PHP version >= 5.2.0
			define('IL_COOKIE_HTTPONLY',false); // Default Value
			session_set_cookie_params(
					IL_COOKIE_EXPIRE, IL_COOKIE_PATH, IL_COOKIE_DOMAIN, IL_COOKIE_SECURE, IL_COOKIE_HTTPONLY
			);
		}
		else
		{
			// PHP version < 5.2.0
			session_set_cookie_params(
					IL_COOKIE_EXPIRE, IL_COOKIE_PATH, IL_COOKIE_DOMAIN, IL_COOKIE_SECURE
			);
		}
	}

	/**
	 * initialise $ilSettings object and define constants
	 * 
	 * Used in Soap
	 */
	public function initSettings()
	{
		global $ilSetting;

		$this->initGlobal("ilSetting", "ilSetting", 
			"Services/Administration/classes/class.ilSetting.php");
				
		// check correct setup
		if (!$ilSetting->get("setup_ok"))
		{
			$this->abortAndDie("Setup is not completed. Please run setup routine again.");
		}

		// set anonymous user & role id and system role id
		define ("ANONYMOUS_USER_ID", $ilSetting->get("anonymous_user_id"));
		define ("ANONYMOUS_ROLE_ID", $ilSetting->get("anonymous_role_id"));
		define ("SYSTEM_USER_ID", $ilSetting->get("system_user_id"));
		define ("SYSTEM_ROLE_ID", $ilSetting->get("system_role_id"));
		define ("USER_FOLDER_ID", 7);

		// recovery folder
		define ("RECOVERY_FOLDER_ID", $ilSetting->get("recovery_folder_id"));

		// installation id
		define ("IL_INST_ID", $ilSetting->get("inst_id",0));

		// define default suffix replacements
		define ("SUFFIX_REPL_DEFAULT", "php,php3,php4,inc,lang,phtml,htaccess");
		define ("SUFFIX_REPL_ADDITIONAL", $ilSetting->get("suffix_repl_additional"));

		if(ilContext::usesHTTP())
		{
			$this->buildHTTPPath();
		}

		// payment setting
		require_once('Services/Payment/classes/class.ilPaymentSettings.php');
		define('IS_PAYMENT_ENABLED', ilPaymentSettings::_isPaymentEnabled());		
	}

	/**
	 * provide $styleDefinition object
	 */
	protected function initStyle()
	{
		global $styleDefinition, $ilPluginAdmin;

		// load style definitions
		$this->initGlobal("styleDefinition", "ilStyleDefinition",
			 "./Services/Style/classes/class.ilStyleDefinition.php");

		// add user interface hook for style initialisation
		$pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
		foreach ($pl_names as $pl)
		{
			$ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
			$gui_class = $ui_plugin->getUIClassInstance();
			$gui_class->modifyGUI("Services/Init", "init_style", array("styleDefinition" => $styleDefinition));
		}

		$styleDefinition->startParsing();
	}

	/**
	 * Init user with current account id
	 * 
	 * Used in ilStartupGUI
	 */
	public function initUserAccount()
	{
		global $ilUser;

		//get user id
		if (empty($_SESSION["AccountId"]))
		{
			$uid = $ilUser->checkUserId();
			$_SESSION["AccountId"] = $uid;
			if ($uid > 0)
			{
				$ilUser->setId($uid);
			}
			// TODO: do we need 'else' here?
		}
		else
		{
			// init user
			$ilUser->setId($_SESSION["AccountId"]);
		}

		// load account data of current user
		$ilUser->read();
	}
	
	/**
	 * Init Locale
	 */
	protected function initLocale()
	{
		global $ilSetting;
		
		if (trim($ilSetting->get("locale") != ""))
		{
			$larr = explode(",", trim($ilSetting->get("locale")));
			$ls = array();
			$first = $larr[0];
			foreach ($larr as $l)
			{
				if (trim($l) != "")
				{
					$ls[] = $l;
				}
			}
			if (count($ls) > 0)
			{
				setlocale(LC_ALL, $ls);
				if (class_exists("Collator"))
				{
					$GLOBALS["ilCollator"] = new Collator($first);
				}
			}
		}
	}
	
	/**
	 * Check current client ip against settings
	 */
	protected function checkUserClientIP()
	{
		global $ilUser, $ilLog, $ilAuth;

		// check client ip
		$clientip = $ilUser->getClientIP();
		if (trim($clientip) != "")
		{
			$clientip = preg_replace("/[^0-9.?*,:]+/","",$clientip);
			$clientip = str_replace(".","\\.",$clientip);
			$clientip = str_replace(Array("?","*",","), Array("[0-9]","[0-9]*","|"), $clientip);
			if (!preg_match("/^".$clientip."$/", $_SERVER["REMOTE_ADDR"]))
			{
				$ilLog->logError(1,
					$ilUser->getLogin().":".$_SERVER["REMOTE_ADDR"]);
				$ilAuth->logout();
				@session_destroy();
				
				if(ilContext::supportsRedirects())
				{
					ilUtil::redirect("login.php?wrong_ip=true");
				}
				else
				{
					$this->abortAndDie("Wrong IP");
				}
			}
		}
	}

	/**
	 * Check if user agreement is active and was accepted 
	 */
	protected function checkUserAgreement()
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
			$ilUser->getId() != ANONYMOUS_USER_ID &&
			$ilUser->checkTimeLimit())
		{
			if($ilAuth->supportsRedirects())
			{
				ilUtil::redirect("ilias.php?baseClass=ilStartUpGUI&cmdClass=ilstartupgui&target=".$_GET["target"]."&cmd=getAcceptance");
			}
			else
			{			
				// :TODO: abortAndDie() ?
			}
		}
	}

	/**
	 * go to public section
	 */
	protected function goToPublicSection()
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
		
		$oldSid = session_id();
		
		$ilAuth->start();
		
		if(IS_PAYMENT_ENABLED)
		{
			$newSid = session_id();
			if($oldSid != $newSid)
			{
				include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
				ilPaymentShoppingCart::_migrateShoppingCart($oldSid, $newSid);
			}
		}
		
		if (ANONYMOUS_USER_ID == "")
		{
			$this->abortAndDie("Public Section enabled, but no Anonymous user found.");
		}
		if (!$ilAuth->getAuth())
		{
			$this->abortAndDie("ANONYMOUS user with the object_id ".ANONYMOUS_USER_ID." not found!");
		}

		// if target given, try to go there
		if ($_GET["target"] != "")
		{
			$this->initUserAccount();

			// target is accessible -> goto target
			include_once("Services/Init/classes/class.ilStartUpGUI.php");
			if	(ilStartUpGUI::_checkGoto($_GET["target"]))
			{
				// :TODO: Disabled redirect for public section
				return true;
					// ilUtil::redirect(ILIAS_HTTP_PATH.
					//		"/goto.php?target=".$_GET["target"].$survey_parameter);
			}
			// target is not accessible -> login
			else	
			{
				$this->goToLogin($_GET['auth_stat']);
			}
		}

		$_GET["ref_id"] = ROOT_FOLDER_ID;
		$_GET["cmd"] = "frameset";

		$script = "ilias.php?baseClass=ilrepositorygui&reloadpublic=1&cmd=".$_GET["cmd"]."&ref_id=".$_GET["ref_id"];

		// todo do it better, if JS disabled
		//echo "<script language=\"Javascript\">\ntop.location.href = \"".$script."\";\n</script>\n";
		echo "<script language=\"Javascript\">\ntop.location.href = \"".$script."\";\n</script>\n".
			'Please click <a target="_top" href="'.$script.'">here</a> if you are not redirected automatically.';
		exit;
	}

	/**
	 * go to login
	 */
	protected function goToLogin($a_auth_stat = "")
	{		
		ilSession::setClosingContext(ilSession::SESSION_CLOSE_LOGIN);

		session_unset();
		session_destroy();

		$add = "";
		if ($_GET["soap_pw"] != "")
		{
			$add = "&soap_pw=".$_GET["soap_pw"]."&ext_uid=".$_GET["ext_uid"];
		}

		$script = "login.php?target=".$_GET["target"]."&client_id=".$_COOKIE["ilClientId"].
			"&auth_stat=".$a_auth_stat.$add;

		// todo do it better, if JS disabled
		// + this is, when session "ends", so
		// we should try to prevent some information about current
		// location
		//
		// check whether we are currently doing a goto call
		if (is_int(strpos($_SERVER["PHP_SELF"], "goto.php")) && $_GET["soap_pw"] == "" &&
			$_GET["reloadpublic"] != "1")
		{
			$script = "goto.php?target=".$_GET["target"]."&client_id=".CLIENT_ID.
				"&reloadpublic=1";
		}

		echo "<script language=\"Javascript\">\ntop.location.href = \"".$script."\";\n</script>\n".
			'Please click <a target="_top" href="'.$script.'">here</a> if you are not redirected automatically.';
		exit;
	}

	/**
	 * $lng initialisation
	 * 
	 * used in sessioncheck.php
	 */
	public function initLanguage()
	{
		global $ilUser, $ilSetting, $rbacsystem;
		
		if (!isset($_SESSION['lang']))
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

		if (isset($_POST['change_lang_to']) && $_POST['change_lang_to'] != "")
		{
			$_GET['lang'] = ilUtil::stripSlashes($_POST['change_lang_to']);
		}

		// prefer personal setting when coming from login screen
		// Added check for ilUser->getId > 0 because it is 0 when the language is changed and the user agreement should be displayes (Helmut Schottm��ller, 2006-10-14)
		if (is_object($ilUser) && $ilUser->getId() != ANONYMOUS_USER_ID && $ilUser->getId() > 0)
		{
			$_SESSION['lang'] = $ilUser->getPref("language");
		}

		$_SESSION['lang'] = (isset($_GET['lang']) && $_GET['lang']) ? $_GET['lang'] : $_SESSION['lang'];

		// check whether lang selection is valid
		require_once "./Services/Language/classes/class.ilLanguage.php";
		$langs = ilLanguage::getInstalledLanguages();
		if (!in_array($_SESSION['lang'], $langs))
		{
			if (is_object($ilSetting) && $ilSetting->get("language") != "")
			{
				$_SESSION['lang'] = $ilSetting->get("language");
			}
			else
			{
				$_SESSION['lang'] = $langs[0];
			}
		}
		$_GET['lang'] = $_SESSION['lang'];
						
		$lng = new ilLanguage($_SESSION['lang']);
		$this->initGlobal("lng", $lng);
		
		if(is_object($rbacsystem))
		{
			$rbacsystem->initMemberView();
		}
	}

	/**
	 * $ilAccess and $rbac... initialisation
	 * 
	 * Used in privfeed.php
	 */
	public function initAccessHandling()
	{				
		$this->initGlobal("rbacreview", "ilRbacReview",
			"./Services/AccessControl/classes/class.ilRbacReview.php");
		
		require_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
		$rbacsystem = ilRbacSystem::getInstance();
		$this->initGlobal("rbacsystem", $rbacsystem);
		
		$this->initGlobal("rbacadmin", "ilRbacAdmin",
			 "./Services/AccessControl/classes/class.ilRbacAdmin.php");
		
		$this->initGlobal("ilAccess", "ilAccessHandler", 
			 "./Services/AccessControl/classes/class.ilAccessHandler.php");
		
		require_once "./Services/AccessControl/classes/class.ilConditionHandler.php";
	}
	
	/**
	 * Init log instance 
	 */
	protected function initLog() 
	{		
		require_once "./Services/Logging/classes/class.ilLog.php";
		$log = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,CLIENT_ID,ILIAS_LOG_ENABLED,ILIAS_LOG_LEVEL);				
		$this->initGlobal("ilLog", $log);
		
		// deprecated
		$this->initGlobal("log", $log);
	}
	
	/**
	 * Initialize global instance
	 * 
	 * @param string $a_name
	 * @param string $a_class
	 * @param string $a_source_file 
	 */
	protected function initGlobal($a_name, $a_class, $a_source_file = null)
	{
		if($a_source_file)
		{
			include_once $a_source_file;
			$GLOBALS[$a_name] = new $a_class;
		}
		else
		{
			$GLOBALS[$a_name] = $a_class;
		}
	}
			
	/**
	 * Exit
	 * 
	 * @param string $a_message 
	 */
	protected function abortAndDie($a_message)
	{
		die($a_message);
	}
	
	/**
	 * Init core objects
	 */
	protected function initCore()
	{
		global $ilErr, $https;
		
		// error reporting
		if (DEVMODE && defined(SHOWNOTICES) && SHOWNOTICES)
		{
			// remove notices from error reporting
			if (version_compare(PHP_VERSION, '5.3.0', '>='))
			{
				error_reporting(E_ALL);
			}
			else
			{
				error_reporting(E_ALL);
			}
		}
		else
		{
			// remove notices from error reporting
			if (version_compare(PHP_VERSION, '5.3.0', '>='))
			{
				error_reporting((ini_get("error_reporting") & ~E_NOTICE) & ~E_DEPRECATED);
			}
			else
			{
				error_reporting(ini_get('error_reporting') & ~E_NOTICE);
			}
		}
		
		$this->includePhp5Compliance();

		$this->requireCommonIncludes();
		
		
		// error handler 
		$this->initGlobal("ilErr", "ilErrorHandling", 
			"classes/class.ilErrorHandling.php");
		$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, 'errorHandler'));		
		
		// :TODO: obsolete?
		PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, "errorHandler"));
					
		// workaround: load old post variables if error handler 'message' was called
		if (isset($_SESSION["message"]) && $_SESSION["message"])
		{
			$_POST = $_SESSION["post_vars"];
		}
		
			
		$this->removeUnsafeCharacters();
				
		$this->setCookieParams();

		
		$this->initIliasIniFile();
		
		$this->determineClient();

		$this->initClientIniFile();

		
		$this->handleMaintenanceMode();
		
		
		$this->initDatabase();

		// needs database
		$this->initGlobal("ilAppEventHandler", "ilAppEventHandler",
			"./Services/EventHandling/classes/class.ilAppEventHandler.php");

		$this->initGlobal("ilPluginAdmin", "ilPluginAdmin",
			"./Services/Component/classes/class.ilPluginAdmin.php");

		$this->setSessionHandler();
		
		$this->initSettings();

		$this->initLog();
			
		$this->initLocale();
				
		if(ilContext::usesHTTP())
		{
			// $https initialisation
			$this->initGlobal("https", "ilHTTPS", "./classes/class.ilHTTPS.php");
			$https->enableSecureCookies();
			$https->checkPort();	
		}
		
				
		$this->initGlobal("ilias", "ILIAS", "classes/class.ilias.php");
		
		
		//
		// ilObject / tree / ilCtrl 
		// 

		$this->initGlobal("ilObjDataCache", "ilObjectDataCache",
			"classes/class.ilObjectDataCache.php");
												
		// needed in ilObjectDefinition
		require_once "classes/class.ilSaxParser.php";
		
		$this->initGlobal("objDefinition", "ilObjectDefinition",
			"./Services/Object/classes/class.ilObjectDefinition.php");

		// $tree
		require_once "./Services/Tree/classes/class.ilTree.php";
		$tree = new ilTree(ROOT_FOLDER_ID);
		$this->initGlobal("tree", $tree);
		unset($tree);
				
		if(ilContext::hasHTML())
		{
			$this->initGlobal("ilCtrl", "ilCtrl",
				"./Services/UICore/classes/class.ilCtrl.php");		
			
			include_once('./Services/WebServices/ECS/classes/class.ilECSTaskScheduler.php');
			ilECSTaskScheduler::start();				
		}
	}

	/**
	 * ilias initialisation
	 */
	public function initILIAS()
	{
		global $ilUser, $ilErr, $ilias, $ilAuth, $tree, $ilCtrl;
		
		$this->initCore();
					
		if (ilContext::hasUser())
		{						
			if(ilContext::usesHTTP())
			{								
				// allow login by submitting user data
				// in query string when DEVMODE is enabled
				if( DEVMODE
					&& isset($_GET['username']) && strlen($_GET['username'])
					&& isset($_GET['password']) && strlen($_GET['password'])
				){
					$_POST['username'] = $_GET['username'];
					$_POST['password'] = $_GET['password'];
				}										
			}		

			// $ilAuth 
			require_once "Auth/Auth.php";
			require_once "./Services/AuthShibboleth/classes/class.ilShibboleth.php";		
			include_once("./Services/Authentication/classes/class.ilAuthUtils.php");
			ilAuthUtils::_initAuth();			
			$ilias->auth = $ilAuth;

			$this->initAccessHandling();
			
			$current_script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);				
			
			
			//
			// TRY AUTHENTICATION / FORCE LOGIN
			// 													

			// :TODO: ??? see below
			// $styleDefinition initialisation and style handling for login and co.
			// $this->initStyle();		
		
			$oldSid = session_id();
			
			$ilAuth->start();
			$ilias->setAuthError($ilErr->getLastError());
			
			if(IS_PAYMENT_ENABLED)
			{
				$newSid = session_id();
				if($oldSid != $newSid)
				{
					include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
					ilPaymentShoppingCart::_migrateShoppingCart($oldSid, $newSid);
				}
			}
		
			// workaround: force login		
			if ((isset($_GET["cmd"]) && $_GET["cmd"] == "force_login") || $current_script == "login.php")
			{
				ilSession::setClosingContext(ilSession::SESSION_CLOSE_LOGIN);

				$ilAuth->logout();

				if(!isset($_GET['forceShoppingCartRedirect']))
					$_SESSION = array();
				$_SESSION["AccountId"] = "";

				$ilAuth->start();
				$ilias->setAuthError($ilErr->getLastError());
			}
		
			// $ilUser 
			$this->initGlobal("ilUser", "ilObjUser", 
				"./Services/User/classes/class.ilObjUser.php");
			$ilias->account =& $ilUser;
		
			// handle ILIAS 2 imported users:
			// check ilias 2 password, if authentication failed
			// only if AUTH_LOCAL
			// DEPRECATED
			if (AUTH_CURRENT == AUTH_LOCAL && !$ilAuth->getAuth() && $current_script == "login.php" && $_POST["username"] != "")
			{
				if (ilObjUser::_lookupHasIlias2Password(ilUtil::stripSlashes($_POST["username"])))
				{
					if (ilObjUser::_switchToIlias3Password(
						ilUtil::stripSlashes($_POST["username"]),
						ilUtil::stripSlashes($_POST["password"])))
					{
						$ilAuth->start();
						$ilias->setAuthError($ilErr->getLastError());
						
						if(ilContext::supportsRedirects())
						{
							ilUtil::redirect("index.php");
						}
						else
						{
							// :TODO: abortAndDie() ?!
						}
					}
				}
			}
	

			//
			// PROCESS AUTHENTICATION STATUS
			// 

			if($ilAuth->getStatus() == '' &&
				$ilias->account->isCurrentUserActive() ||
				(defined("IL_PHPUNIT_TEST") && DEVMODE))
			{
				$has_just_logged_in = ($current_script == "login.php" ||
					$_GET["baseClass"] == "ilStartUpGUI");

				$this->handleAuthenticationSuccess($has_just_logged_in);
			}			
			else
			{
				$mandatory_auth = ($current_script != "login.php"
						&& $current_script != "shib_login.php"
						&& $current_script != "shib_logout.php"
						&& $current_script != "error.php"
						&& $current_script != "index.php"
						&& $current_script != "view_usr_agreement.php"
						&& $current_script != "register.php"
						&& $current_script != "chat.php"
						&& $current_script != "pwassist.php"
						&& $current_script != "confirmReg.php");

				$this->handleAuthenticationFail($mandatory_auth);
			}				
		}
		
		
		//
		// SUCCESSFUL AUTHENTICATED or NON-AUTH-AREA (Login, Registration, ...)
		//

		// language depends on user setting
		$this->initLanguage();
		$tree->initLangCode();
		
		if(ilContext::hasHTML() && !$ilCtrl->isAsynch())
		{						
			$this->initHTML();						
		}							
	}

	/**
	 * Handle successful authentication
	 * 
	 * @param bool $a_has_just_logged_in
	 */
	protected function handleAuthenticationSuccess($a_has_just_logged_in)
	{
		global $ilUser;
		
		$this->initUserAccount();

		$this->checkUserClientIP();

		// #5634
		$this->checkUserAgreement();
		
		require_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
		$security_settings = ilSecuritySettings::_getInstance();

		// update last_login date once the user logged in
		if ($a_has_just_logged_in)
		{
			// determine first login of user for setting an indicator
			// which still is available in PersonalDesktop, Repository, ...
			// (last login date is set to current date in next step)		
			if( $security_settings->isPasswordChangeOnFirstLoginEnabled() &&
				$ilUser->getLastLogin() == null )
			{
				$ilUser->resetLastPasswordChange();
			}

			$ilUser->refreshLogin();
		}

		// differentiate account security mode		
		if( $security_settings->getAccountSecurityMode() ==
			ilSecuritySettings::ACCOUNT_SECURITY_MODE_CUSTOMIZED )
		{
			// reset counter for failed logins
			ilObjUser::_resetLoginAttempts( $ilUser->getId() );
		}
				
		// Store online time of user		
		require_once 'Services/Tracking/classes/class.ilOnlineTracking.php';
		ilOnlineTracking::_updateAccess($ilUser->getId());	
	}
	
	/**
	 * Handle failed authentication
	 * 
	 * @param bool $a_mandatory_auth
	 */
	protected function handleAuthenticationFail($a_mandatory_auth)
	{
		global $ilAuth, $ilSetting, $ilUser;
		
		if ($a_mandatory_auth)
		{			    
			// jump to public section (to do: is this always the indended
			// behaviour, login could be another possibility (including
			// message)

			if ($_GET["baseClass"] != "ilStartUpGUI")
			{
				$this->initLanguage();
				
				// Do not redirect for Auth_SOAP Auth_CRON Auth_HTTP
				if($ilAuth->supportsRedirects() && ilContext::hasHTML())
				{				
					if ($ilSetting->get("pub_section") &&
						($ilAuth->getStatus() == "" || 
							$ilAuth->getStatus() == AUTH_EXPIRED ||
							$ilAuth->getStatus() == AUTH_IDLED) &&
						$_GET["reloadpublic"] != "1")
					{
						$this->goToPublicSection();
					}
					else
					{
						$this->goToLogin(($_GET['auth_stat'] && !$ilAuth->getStatus()) ? $_GET['auth_stat'] : $ilAuth->getStatus());
					} 			
				}
				else
				{
					// :TODO: raise exception?				
				}
			}						
		}
		
		//
		// FAILED AUTHENTICATION: NOT MANDATORY
		//
		
		else if(!$ilAuth->getAuth())
		{			
			// differentiate account security mode
			require_once('./Services/PrivacySecurity/classes/class.ilSecuritySettings.php');
			$security = ilSecuritySettings::_getInstance();
			if( $security->getAccountSecurityMode() ==
				ilSecuritySettings::ACCOUNT_SECURITY_MODE_CUSTOMIZED )
			{
				if(isset($_POST['username']) && $_POST['username'] && $ilUser->getId() == 0)
				{
					$username = ilUtil::stripSlashes( $_POST['username'] );
					$usr_id = ilObjUser::_lookupId( $username );

					if( $usr_id != ANONYMOUS_USER_ID )
					{
						ilObjUser::_incrementLoginAttempts($usr_id);

						$login_attempts = ilObjUser::_getLoginAttempts( $usr_id );
						$max_attempts = $security->getLoginMaxAttempts();

						if( $login_attempts >= $max_attempts &&
							$usr_id != SYSTEM_USER_ID &&
							$max_attempts > 0 )
						{
							ilObjUser::_setUserInactive( $usr_id );
						}
					}
				}
			}
		}
	}
	
	/**
	 * init HTML output
	 */
	protected function initHTML()
	{
		global $ilUser;
		
		// load style definitions
		// use the init function with plugin hook here, too
	    $this->initStyle();

		// $tpl
		$tpl = new ilTemplate("tpl.main.html", true, true);
		$this->initGlobal("tpl", $tpl);

		$this->initGlobal("ilLocator", "ilLocatorGUI", 
			"./Services/Locator/classes/class.ilLocatorGUI.php");
		
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);				
		
		// :TODO: ???
		require_once "classes/class.ilFrameTargetInfo.php";				

		$this->initGlobal("ilNavigationHistory", "ilNavigationHistory",
			"Services/Navigation/classes/class.ilNavigationHistory.php");
	
		$this->initGlobal("ilBrowser", "ilBrowser", 
			"classes/class.ilBrowser.php");
	
		$this->initGlobal("ilHelp", "ilHelpGUI", 
			"Services/Help/classes/class.ilHelpGUI.php");
		
		$this->initGlobal("ilTabs", "ilTabsGUI", 
			"./Services/UIComponent/Tabs/classes/class.ilTabsGUI.php");
		
		$this->initGlobal("ilToolbar", "ilToolbarGUI", 
			"./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php");	

		// $ilMainMenu
		include_once './Services/MainMenu/classes/class.ilMainMenuGUI.php';
		$ilMainMenu = new ilMainMenuGUI("_top");
		$this->initGlobal("ilMainMenu", $ilMainMenu);
		unset($ilMainMenu);
						
		
		// :TODO: tableGUI related
		
		// set hits per page for all lists using table module
		$_GET['limit'] = $_SESSION['tbl_limit'] = (int) $ilUser->getPref('hits_per_page');

		// the next line makes it impossible to save the offset somehow in a session for
		// a specific table (I tried it for the user administration).
		// its not posssible to distinguish whether it has been set to page 1 (=offset = 0)
		// or not set at all (then we want the last offset, e.g. being used from a session var).
		// So I added the wrapping if statement. Seems to work (hopefully).
		// Alex April 14th 2006
		if (isset($_GET['offset']) && $_GET['offset'] != "")							// added April 14th 2006
		{
			$_GET['offset'] = (int) $_GET['offset'];		// old code
		}
	}
}

?>