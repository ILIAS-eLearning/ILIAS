<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

// needed for slow queries, etc.
if(!isset($GLOBALS['ilGlobalStartTime']) || !$GLOBALS['ilGlobalStartTime'])
{
	$GLOBALS['ilGlobalStartTime'] = microtime();
}

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
	protected static function removeUnsafeCharacters()
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
	protected static function requireCommonIncludes()
	{			
		// pear
		require_once("include/inc.get_pear.php");
		require_once("include/inc.check_pear.php");
		require_once "PEAR.php";
		
		// ilTemplate
		if(ilContext::usesTemplate())
		{
			// HTML_Template_IT support
			require_once "HTML/Template/ITX.php";
			require_once "./Services/UICore/classes/class.ilTemplateHTMLITX.php";
			require_once "./Services/UICore/classes/class.ilTemplate.php";
		}		
				
		// really always required?
		require_once "./Services/Utilities/classes/class.ilUtil.php";	
		require_once "./Services/Utilities/classes/class.ilFormat.php";
		require_once "./Services/Calendar/classes/class.ilDatePresentation.php";														
		require_once "include/inc.ilias_version.php";	
		
		self::initGlobal("ilBench", "ilBenchmark", "./Services/Utilities/classes/class.ilBenchmark.php");				
	}
	
	/**
	 * This is a hack for  authentication.
	 * 
	 * Since the phpCAS lib ships with its own compliance functions.
	 */
	protected static function includePhp5Compliance()
	{
		include_once 'Services/Authentication/classes/class.ilAuthFactory.php';
		if(ilAuthFactory::getContext() != ilAuthFactory::CONTEXT_CAS)
		{
			require_once("include/inc.xml5compliance.php");
		}
		require_once("include/inc.xsl5compliance.php");
	}

	/**
	 * This method provides a global instance of class ilIniFile for the
	 * ilias.ini.php file in variable $ilIliasIniFile.
	 *
	 * It initializes a lot of constants accordingly to the settings in
	 * the ilias.ini.php file.
	 */
	protected static function initIliasIniFile()
	{		
		require_once("./Services/Init/classes/class.ilIniFile.php");
		$ilIliasIniFile = new ilIniFile("./ilias.ini.php");				
		$ilIliasIniFile->read();
		self::initGlobal('ilIliasIniFile', $ilIliasIniFile);

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
		define ("PATH_TO_GHOSTSCRIPT",$ilIliasIniFile->readVariable("tools","ghostscript"));
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
		
		include_once './Services/Calendar/classes/class.ilTimeZone.php';
		$tz = ilTimeZone::initDefaultTimeZone($ilIliasIniFile);
		define ("IL_TIMEZONE", $tz);
	}

	/**
	 * builds http path
	 */
	protected static function buildHTTPPath()
	{
		include_once './Services/Http/classes/class.ilHTTPS.php';
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
	protected static function determineClient()
	{
		global $ilIliasIniFile;

		// check whether ini file object exists
		if (!is_object($ilIliasIniFile))
		{
			self::abortAndDie("Fatal Error: ilInitialisation::determineClient called without initialisation of ILIAS ini file object.");
		}
				
		// set to default client if empty
		if ($_GET["client_id"] != "")
		{
			$_GET["client_id"] = ilUtil::stripSlashes($_GET["client_id"]);
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
	protected static function initClientIniFile()
	{
		global $ilIliasIniFile;
		
		// check whether ILIAS_WEB_DIR is set.
		if (ILIAS_WEB_DIR == "")
		{
			self::abortAndDie("Fatal Error: ilInitialisation::initClientIniFile called without ILIAS_WEB_DIR.");
		}

		// check whether CLIENT_ID is set.
		if (CLIENT_ID == "")
		{
			self::abortAndDie("Fatal Error: ilInitialisation::initClientIniFile called without CLIENT_ID.");
		}

		$ini_file = "./".ILIAS_WEB_DIR."/".CLIENT_ID."/client.ini.php";

		// get settings from ini file
		require_once("./Services/Init/classes/class.ilIniFile.php");
		$ilClientIniFile = new ilIniFile($ini_file);		
		$ilClientIniFile->read();
		
		// invalid client id / client ini
		if ($ilClientIniFile->ERROR != "")
		{
			$c = $_COOKIE["ilClientId"];
			$default_client = $ilIliasIniFile->readVariable("clients","default");						
			ilUtil::setCookie("ilClientId", $default_client);
			if (CLIENT_ID != "" && CLIENT_ID != $default_client)
			{
				$mess = array("en" => "Client does not exist.",
						"de" => "Mandant ist ungültig.");
				self::redirect("index.php?client_id=".$default_client, null, $mess);							
			}
			else
			{
				self::abortAndDie("Invalid client");
			}
		}
		
		self::initGlobal("ilClientIniFile", $ilClientIniFile);

		// set constants
		define ("SESSION_REMINDER_LEADTIME", 30);
		define ("DEBUG",$ilClientIniFile->readVariable("system","DEBUG"));
		define ("DEVMODE",$ilClientIniFile->readVariable("system","DEVMODE"));
		define ("SHOWNOTICES",$ilClientIniFile->readVariable("system","SHOWNOTICES"));
		define ("DEBUGTOOLS",$ilClientIniFile->readVariable("system","DEBUGTOOLS"));
		define ("ROOT_FOLDER_ID",$ilClientIniFile->readVariable('system','ROOT_FOLDER_ID'));
		define ("SYSTEM_FOLDER_ID",$ilClientIniFile->readVariable('system','SYSTEM_FOLDER_ID'));
		define ("ROLE_FOLDER_ID",$ilClientIniFile->readVariable('system','ROLE_FOLDER_ID'));
		define ("MAIL_SETTINGS_ID",$ilClientIniFile->readVariable('system','MAIL_SETTINGS_ID'));
		$error_handler = $ilClientIniFile->readVariable('system', 'ERROR_HANDLER');
		define ("ERROR_HANDLER",$error_handler ? $error_handler : "PRETTY_PAGE");
		
		// this is for the online help installation, which sets OH_REF_ID to the
		// ref id of the online module
		define ("OH_REF_ID",$ilClientIniFile->readVariable("system","OH_REF_ID"));

		define ("SYSTEM_MAIL_ADDRESS",$ilClientIniFile->readVariable('system','MAIL_SENT_ADDRESS')); // Change SS
		define ("MAIL_REPLY_WARNING",$ilClientIniFile->readVariable('system','MAIL_REPLY_WARNING')); // Change SS

		// see ilObject::TITLE_LENGTH, ilObject::DESC_LENGTH
		// define ("MAXLENGTH_OBJ_TITLE",125);#$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_TITLE'));
		// define ("MAXLENGTH_OBJ_DESC",$ilClientIniFile->readVariable('system','MAXLENGTH_OBJ_DESC'));

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

		require_once('./Services/GlobalCache/classes/Settings/class.ilGlobalCacheSettings.php');
		$ilGlobalCacheSettings = new ilGlobalCacheSettings();
		$ilGlobalCacheSettings->readFromIniFile($ilClientIniFile);
		ilGlobalCache::setup($ilGlobalCacheSettings);
		
		return true;
	}

	/**
	 * handle maintenance mode
	 */
	protected static function handleMaintenanceMode()
	{
		global $ilClientIniFile;

		if (!$ilClientIniFile->readVariable("client","access"))
		{						
			$mess = array("en" => "The server is not available due to maintenance.".
					" We apologise for any inconvenience.",
				"de" => "Der Server ist aufgrund von Wartungsarbeiten nicht verfügbar.".
					" Wir bitten um Verständnis.");
			$mess_id = "init_error_maintenance";
			
			if (ilContext::hasHTML() && is_file("./maintenance.html"))
			{
				self::redirect("./maintenance.html", $mess_id, $mess);
			}
			else
			{					
				$mess = self::translateMessage($mess_id, $mess);
				self::abortAndDie($mess);
			}
		}
	}

	/**
	* initialise database object $ilDB
	*
	*/
	protected static function initDatabase()
	{
		// build dsn of database connection and connect
		require_once("./Services/Database/classes/class.ilDBWrapperFactory.php");				
		$ilDB = ilDBWrapperFactory::getWrapper(IL_DB_TYPE);
		$ilDB->initFromIniFile();
		$ilDB->connect();
		
		self::initGlobal("ilDB", $ilDB);		
	}

	/**
	 * set session handler to db
	 * 
	 * Used in Soap/CAS
	 */
	public static function setSessionHandler()
	{
		if(ini_get('session.save_handler') != 'user')
		{
			ini_set("session.save_handler", "user");
		}
		
		require_once "Services/Authentication/classes/class.ilSessionDBHandler.php";
		$db_session_handler = new ilSessionDBHandler();
		if (!$db_session_handler->setSaveHandler())
		{
			self::abortAndDie("Please turn off Safe mode OR set session.save_handler to \"user\" in your php.ini");
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
	protected static function setCookieParams()
	{
		global $ilSetting;
		
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
		
		include_once './Services/Http/classes/class.ilHTTPS.php';
		$cookie_secure = !$ilSetting->get('https', 0) && ilHTTPS::getInstance()->isDetected();
		
		define('IL_COOKIE_EXPIRE',0);
		define('IL_COOKIE_PATH',$cookie_path);
		define('IL_COOKIE_DOMAIN','');
		define('IL_COOKIE_SECURE', $cookie_secure); // Default Value

		define('IL_COOKIE_HTTPONLY',true); // Default Value
		session_set_cookie_params(
			IL_COOKIE_EXPIRE, IL_COOKIE_PATH, IL_COOKIE_DOMAIN, IL_COOKIE_SECURE, IL_COOKIE_HTTPONLY
		);
	}

	/**
	 * initialise $ilSettings object and define constants
	 * 
	 * Used in Soap
	 */
	protected static function initSettings()
	{
		global $ilSetting;

		self::initGlobal("ilSetting", "ilSetting", 
			"Services/Administration/classes/class.ilSetting.php");
				
		// check correct setup
		if (!$ilSetting->get("setup_ok"))
		{
			self::abortAndDie("Setup is not completed. Please run setup routine again.");
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
			self::buildHTTPPath();
		}

		// payment setting
		require_once('Services/Payment/classes/class.ilPaymentSettings.php');
		define('IS_PAYMENT_ENABLED', ilPaymentSettings::_isPaymentEnabled());		
	}

	/**
	 * provide $styleDefinition object
	 */
	protected static function initStyle()
	{
		global $styleDefinition, $ilPluginAdmin;

		// load style definitions
		self::initGlobal("styleDefinition", "ilStyleDefinition",
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
	 */
	public static function initUserAccount()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		// get user id
		if (!ilSession::get("AccountId"))
		{
			ilSession::set("AccountId", $ilUser->checkUserId());
			ilSession::set('orig_request_target', '');
			$ilUser->hasToAcceptTermsOfServiceInSession(true);
		}
		
		$uid = ilSession::get("AccountId");		
		if($uid)
		{
			$ilUser->setId($uid);	
			$ilUser->read();
			
			// init console log handler
			include_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
			ilLoggerFactory::getInstance()->initUser($ilUser->getLogin());
			ilLoggerFactory::getRootLogger()->debug('Using default timezone: '. IL_TIMEZONE);
		}
		else
		{
			if(is_object($GLOBALS['ilLog']))
			{
				$GLOBALS['ilLog']->logStack();
			}
			self::abortAndDie("Init user account failed");
		}
	}

	/**
	 * Init Locale
	 */
	protected static function initLocale()
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
							
				// #15347 - making sure that floats are not changed
				setlocale(LC_NUMERIC, "C"); 
				
				if (class_exists("Collator"))
				{
					$GLOBALS["ilCollator"] = new Collator($first);
				}				
			}
		}
	}
	
	/**
	 * go to public section
	 * 
	 * @param int $a_auth_stat
	 */
	public static function goToPublicSection($a_auth_stat = "")
	{
		global $ilAuth;
				
		if (ANONYMOUS_USER_ID == "")
		{
			self::abortAndDie("Public Section enabled, but no Anonymous user found.");
		}

		// logout and end previous session
		if($a_auth_stat == AUTH_EXPIRED ||
			$a_auth_stat == AUTH_IDLED)
		{
			ilSession::setClosingContext(ilSession::SESSION_CLOSE_EXPIRE);
		}
		else
		{
			ilSession::setClosingContext(ilSession::SESSION_CLOSE_PUBLIC);
		}
		$ilAuth->logout();
		session_unset();
		session_destroy();
		
		// new session and login as anonymous
		self::setSessionHandler();
		session_start();
		$_POST["username"] = "anonymous";
		$_POST["password"] = "anonymous";
		ilAuthUtils::_initAuth();
		
		// authenticate (anonymous)
		$oldSid = session_id();		
		$ilAuth->start();		
		if (IS_PAYMENT_ENABLED)
		{
			$newSid = session_id();
			if($oldSid != $newSid)
			{
				include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
				ilPaymentShoppingCart::_migrateShoppingCart($oldSid, $newSid);
			}
		}
		
		if (!$ilAuth->getAuth())
		{
			self::abortAndDie("ANONYMOUS user with the object_id ".ANONYMOUS_USER_ID." not found!");
		}
		
		self::initUserAccount();
		
		$mess_id = "init_error_authentication_fail";
		$mess = array("en" => "Authentication failed.",
			"de" => "Authentifizierung fehlgeschlagen.");
		
		// if target given, try to go there
		if ($_GET["target"] != "")
		{	
			// when we are already "inside" goto.php no redirect is needed
			$current_script = substr(strrchr($_SERVER["PHP_SELF"], "/"), 1);	
			if($current_script == "goto.php")
			{
				return;
			}		
			
			// goto will check if target is accessible or redirect to login
			self::redirect("goto.php?target=".$_GET["target"], $mess_id, $mess);			
		}
		
		// we do not know if ref_id of request is accesible, so redirecting to root
		$_GET["ref_id"] = ROOT_FOLDER_ID;
		$_GET["cmd"] = "frameset";
		self::redirect("ilias.php?baseClass=ilrepositorygui&reloadpublic=1&cmd=".
			$_GET["cmd"]."&ref_id=".$_GET["ref_id"], $mess_id, $mess);
	}

	/**
	 * go to login
	 * 
	 * @param int $a_auth_stat
	 */
	protected static function goToLogin($a_auth_stat = "")
	{		
		global $ilAuth;
		
		// close current session
		if($a_auth_stat == AUTH_EXPIRED ||
			$a_auth_stat == AUTH_IDLED)
		{
			ilSession::setClosingContext(ilSession::SESSION_CLOSE_EXPIRE);
		}
		else
		{
			ilSession::setClosingContext(ilSession::SESSION_CLOSE_LOGIN);
		}
		$ilAuth->logout();
		session_unset();
		session_destroy();

		$add = "";
		if ($_GET["soap_pw"] != "")
		{
			$add = "&soap_pw=".$_GET["soap_pw"]."&ext_uid=".$_GET["ext_uid"];
		}

		$script = "login.php?target=".$_GET["target"]."&client_id=".$_COOKIE["ilClientId"].
			"&auth_stat=".$a_auth_stat.$add;
					
		self::redirect($script, "init_error_authentication_fail",
			array("en" => "Authentication failed.",
				"de" => "Authentifizierung fehlgeschlagen."));
	}

	/**
	 * $lng initialisation
	 */
	protected static function initLanguage()
	{
		/**
		 * @var $rbacsystem ilRbacSystem
		 */
		global $rbacsystem;

		require_once 'Services/Language/classes/class.ilLanguage.php';
		self::initGlobal('lng', ilLanguage::getGlobalInstance());
		if(is_object($rbacsystem))
		{
			$rbacsystem->initMemberView();
		}
	}

	/**
	 * $ilAccess and $rbac... initialisation
	 */
	protected static function initAccessHandling()
	{				
		self::initGlobal("rbacreview", "ilRbacReview",
			"./Services/AccessControl/classes/class.ilRbacReview.php");
		
		require_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
		$rbacsystem = ilRbacSystem::getInstance();
		self::initGlobal("rbacsystem", $rbacsystem);
		
		self::initGlobal("rbacadmin", "ilRbacAdmin",
			 "./Services/AccessControl/classes/class.ilRbacAdmin.php");
		
		self::initGlobal("ilAccess", "ilAccessHandler", 
			 "./Services/AccessControl/classes/class.ilAccessHandler.php");
		
		require_once "./Services/AccessControl/classes/class.ilConditionHandler.php";
	}
	
	/**
	 * Init log instance 
	 */
	protected static function initLog() 
	{		
		include_once './Services/Logging/classes/public/class.ilLoggerFactory.php';
		$log = ilLoggerFactory::getRootLogger();
		
		self::initGlobal("ilLog", $log);
		// deprecated
		self::initGlobal("log", $log);
	}
	
	/**
	 * Initialize global instance
	 * 
	 * @param string $a_name
	 * @param string $a_class
	 * @param string $a_source_file 
	 */
	protected static function initGlobal($a_name, $a_class, $a_source_file = null)
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
	protected static function abortAndDie($a_message)
	{		
		if(is_object($GLOBALS['ilLog']))
		{
			$GLOBALS['ilLog']->write("Fatal Error: ilInitialisation - ".$a_message);
		}
		die($a_message);
	}
	
	/**
	 * Prepare developer tools	 
	 */
	protected static function handleDevMode()
	{
		if(defined(SHOWNOTICES) && SHOWNOTICES)
		{
			// no further differentiating of php version regarding to 5.4 neccessary
			// when the error reporting is set to E_ALL anyway
			
			// add notices to error reporting
			error_reporting(E_ALL);
		}
		
		if(defined('DEBUGTOOLS') && DEBUGTOOLS)
		{
			include_once "include/inc.debug.php";
		}
	}
	
	protected static $already_initialized;


	public static function reinitILIAS() {
		self::$already_initialized = false;
		self::initILIAS();
	}

	/**
	 * ilias initialisation
	 */
	public static function initILIAS()
	{
		if (self::$already_initialized) 
		{
			// workaround for bug #17990
			// big mess. we prevent double initialisations with ILIAS 5.1, which is good, but...
			// the style service uses $_GET["ref_id"] to determine
			// the context styles. $_GET["ref_id"] is "corrected" by the "goto" procedure and which calls
			// initILIAS again.
			// we need a mechanism that detemines our repository context and stores that in an information object
			// usable by the style component afterwars. This needs new concepts and a refactoring.
			if(ilContext::initClient())
			{
				global $tpl;
				if (is_object($tpl))
				{
					// load style sheet depending on user's settings
					$location_stylesheet = ilUtil::getStyleSheetLocation();
					$tpl->setVariable("LOCATION_STYLESHEET", $location_stylesheet);
				}
			}

			return;
		}

		self::$already_initialized = true;

		global $tree;
		
		self::initCore();
				
		if(ilContext::initClient())
		{
			self::initClient();

			if (ilContext::hasUser())
			{						
				self::initUser();
				
				if(ilContext::doAuthentication())
				{
					self::authenticate();
				}				
			}	

			// init after Auth otherwise breaks CAS
			self::includePhp5Compliance();
			
			// language may depend on user setting
			self::initLanguage();
			$tree->initLangCode();

			if(ilContext::hasHTML())
			{													
				include_once('./Services/WebServices/ECS/classes/class.ilECSTaskScheduler.php');
				ilECSTaskScheduler::start();					
				
				self::initHTML();		
			}							
		}					
	}
	
	/**
	 * Set error reporting level
	 */
	public static function handleErrorReporting()
	{		
		// remove notices from error reporting
		if (version_compare(PHP_VERSION, '5.4.0', '>='))
		{
			// Prior to PHP 5.4.0 E_ALL does not include E_STRICT.
			// With PHP 5.4.0 and above E_ALL >DOES< include E_STRICT.
			
			error_reporting(((ini_get("error_reporting") & ~E_NOTICE) & ~E_DEPRECATED) & ~E_STRICT);
		}
		else
		{
			error_reporting((ini_get("error_reporting") & ~E_NOTICE) & ~E_DEPRECATED);
		}
		
		// see handleDevMode() - error reporting might be overwritten again
		// but we need the client ini first
	}
	
	/**
	 * Init core objects (level 0)
	 */
	protected static function initCore()
	{
		global $ilErr;
		
		self::handleErrorReporting();
		
		// breaks CAS: must be included after CAS context isset in AuthUtils
		//self::includePhp5Compliance();

		self::requireCommonIncludes();
		
		
		// error handler 
		self::initGlobal("ilErr", "ilErrorHandling", 
			"./Services/Init/classes/class.ilErrorHandling.php");
		$ilErr->setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, 'errorHandler'));		
		
		// :TODO: obsolete?
		PEAR::setErrorHandling(PEAR_ERROR_CALLBACK, array($ilErr, "errorHandler"));
					
		// workaround: load old post variables if error handler 'message' was called
		include_once "Services/Authentication/classes/class.ilSession.php";
		if (ilSession::get("message"))
		{
			$_POST = ilSession::get("post_vars");
		}
					
		self::removeUnsafeCharacters();

		self::initIliasIniFile();

		define('IL_INITIAL_WD', getcwd());
		
		// deprecated
		self::initGlobal("ilias", "ILIAS", "./Services/Init/classes/class.ilias.php");				
	}
	
	/**
	 * Init client-based objects (level 1)
	 */
	protected static function initClient()
	{
		global $https, $ilias; 
		
		self::determineClient();

		self::initClientIniFile();
				
		
		// --- needs client ini		
		
		$ilias->client_id = CLIENT_ID;
		
		if (DEVMODE)
		{
			self::handleDevMode();
		}						
	

		self::handleMaintenanceMode();

		self::initDatabase();
		
		// moved after databases 
		self::initLog();		
		
		self::initGlobal("ilAppEventHandler", "ilAppEventHandler",
			"./Services/EventHandling/classes/class.ilAppEventHandler.php");

		// there are rare cases where initILIAS is called twice for a request
		// example goto.php is called and includes ilias.php later
		// we must prevent that ilPluginAdmin is initialized twice in
		// this case, since this won't get the values out of plugin.php the
		// second time properly
		if (!is_object($GLOBALS["ilPluginAdmin"]))
		{
			self::initGlobal("ilPluginAdmin", "ilPluginAdmin",
				"./Services/Component/classes/class.ilPluginAdmin.php");
		}

		self::setSessionHandler();

		self::initSettings();
		
		
		// --- needs settings	
		
		self::initLocale();				
						
		if(ilContext::usesHTTP())
		{
			// $https 
			self::initGlobal("https", "ilHTTPS", "./Services/Http/classes/class.ilHTTPS.php");
			$https->enableSecureCookies();
			$https->checkPort();	
		}		
		

		// --- object handling		
		
		self::initGlobal("ilObjDataCache", "ilObjectDataCache",
			"./Services/Object/classes/class.ilObjectDataCache.php");
												
		// needed in ilObjectDefinition
		require_once "./Services/Xml/classes/class.ilSaxParser.php";
		
		self::initGlobal("objDefinition", "ilObjectDefinition",
			"./Services/Object/classes/class.ilObjectDefinition.php");
		
		// $tree
		require_once "./Services/Tree/classes/class.ilTree.php";
		$tree = new ilTree(ROOT_FOLDER_ID);
		self::initGlobal("tree", $tree);
		unset($tree);

		self::initGlobal("ilCtrl", "ilCtrl",
				"./Services/UICore/classes/class.ilCtrl.php");

		self::setCookieParams();
	}
	
	/**
	 * Init user / authentification (level 2)
	 */
	protected static function initUser()
	{
		global $ilias, $ilAuth, $ilUser;
		
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

		// $ilUser 
		self::initGlobal("ilUser", "ilObjUser", 
			"./Services/User/classes/class.ilObjUser.php");
		$ilias->account =& $ilUser;
				
		self::initAccessHandling();

		
		// force login
		if ((isset($_GET["cmd"]) && $_GET["cmd"] == "force_login"))
		{			
			$ilAuth->logout();
			
			// we need to do this for the session statistics
			// could we use session_destroy() instead?
			// [this is done after every $ilAuth->logout() call elsewhere] 
			ilSession::_destroy(session_id(), ilSession::SESSION_CLOSE_LOGIN);

			// :TODO: keep session because of cart content?
			if(!isset($_GET['forceShoppingCartRedirect']))
			{
				$_SESSION = array();
			}
			else
			{
				ilSession::set("AccountId", "");	
			}
		}		
		
	}

	/**
	 * Try authentication
	 * 
	 * This will basically validate the current session
	 */
	public static function authenticate()
	{
		global $ilAuth, $ilias, $ilErr;
		
		$current_script = substr(strrchr($_SERVER["PHP_SELF"], "/"), 1);		
		
		if(self::blockedAuthentication($current_script))
		{
			return;
		}
						
		$oldSid = session_id();		
		
		$ilAuth->start();
		$ilias->setAuthError($ilErr->getLastError());
				
		if(IS_PAYMENT_ENABLED)
		{
			// cart is "attached" to session, has to be updated
			$newSid = session_id();
			if($oldSid != $newSid)
			{
				include_once './Services/Payment/classes/class.ilPaymentShoppingCart.php';
				ilPaymentShoppingCart::_migrateShoppingCart($oldSid, $newSid);
			}
		}					
		
		if($ilAuth->getAuth() && $ilAuth->getStatus() == '')
		{
			self::initUserAccount();
			
			self::handleAuthenticationSuccess();
		}			
		else 
		{									
			if (!self::showingLoginForm($current_script))
			{								
				// :TODO: should be moved to context?!
				$mandatory_auth = ($current_script != "shib_login.php"
						&& $current_script != "shib_logout.php"
						&& $current_script != "error.php"
						&& $current_script != "chat.php"
						&& $current_script != "wac.php"
						&& $current_script != "index.php"); // #10316
				
				if($mandatory_auth)
				{
					self::handleAuthenticationFail();
				}
			}		
		}					
	}

	/**
	 * @static
	 */
	protected static function handleAuthenticationSuccess()
	{
		/**
		 * @var $ilUser ilObjUser
		 */
		global $ilUser;

		require_once 'Services/Tracking/classes/class.ilOnlineTracking.php';
		ilOnlineTracking::updateAccess($ilUser);
	}

	/**
	 * @static
	 */
	protected static function handleAuthenticationFail()
	{
		/**
		 * @var $ilAuth    Auth
		 * @var $ilSetting ilSetting
		 */
		global $ilAuth, $ilSetting;
						
		// #10608
		if(ilContext::getType() == ilContext::CONTEXT_SOAP || ilContext::getType() == ilContext::CONTEXT_WAC)
		{
			throw new Exception("Authentication failed.");
		}				

		$status = $ilAuth->getStatus();

		if($ilSetting->get('pub_section') &&
			($status == '' || $status == AUTH_EXPIRED || $status == AUTH_IDLED) &&
			$_GET['reloadpublic'] != '1'
		)
		{
			self::goToPublicSection($status);
		}
		else
		{
			self::goToLogin($status);
		}
	}
	
	/**
	 * init HTML output (level 3)
	 */
	protected static function initHTML()
	{
		global $ilUser;
		
		if(ilContext::hasUser())
		{
			// load style definitions
			// use the init function with plugin hook here, too
			self::initStyle();
		}

		// $tpl
		$tpl = new ilTemplate("tpl.main.html", true, true);
		self::initGlobal("tpl", $tpl);
		if(ilContext::hasUser() && ilContext::doAuthentication())
		{
			/**
			 * @var $ilUser ilObjUser
			 * @var $ilCtrl ilCtrl
			 */
			global $ilUser, $ilCtrl;

			require_once 'Services/User/classes/class.ilUserRequestTargetAdjustment.php';
			$request_adjuster = new ilUserRequestTargetAdjustment($ilUser, $ilCtrl);
			$request_adjuster->adjust();
		}
		
		// load style sheet depending on user's settings
		$location_stylesheet = ilUtil::getStyleSheetLocation();
		$tpl->setVariable("LOCATION_STYLESHEET",$location_stylesheet);				
		
		require_once "./Services/UICore/classes/class.ilFrameTargetInfo.php";				
				
		self::initGlobal("ilNavigationHistory", "ilNavigationHistory",
				"Services/Navigation/classes/class.ilNavigationHistory.php");

		self::initGlobal("ilBrowser", "ilBrowser", 
			"./Services/Utilities/classes/class.ilBrowser.php");

		self::initGlobal("ilHelp", "ilHelpGUI", 
			"Services/Help/classes/class.ilHelpGUI.php");

		self::initGlobal("ilToolbar", "ilToolbarGUI", 
			"./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php");	

		self::initGlobal("ilLocator", "ilLocatorGUI", 
			"./Services/Locator/classes/class.ilLocatorGUI.php");

		self::initGlobal("ilTabs", "ilTabsGUI", 
			"./Services/UIComponent/Tabs/classes/class.ilTabsGUI.php");

		if(ilContext::hasUser())
		{
			// $ilMainMenu
			include_once './Services/MainMenu/classes/class.ilMainMenuGUI.php';
			$ilMainMenu = new ilMainMenuGUI("_top");
			self::initGlobal("ilMainMenu", $ilMainMenu);
			unset($ilMainMenu);
	

			// :TODO: tableGUI related

			// set hits per page for all lists using table module
			$_GET['limit'] = (int) $ilUser->getPref('hits_per_page');
			ilSession::set('tbl_limit', $_GET['limit']);

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
		else
		{
			// several code parts rely on ilObjUser being always included
			include_once "Services/User/classes/class.ilObjUser.php";
		}
	}
	
	/**
	 * Extract current cmd from request
	 * 
	 * @return string
	 */
	protected static function getCurrentCmd()
	{
		$cmd = $_REQUEST["cmd"];
		if(is_array($cmd))
		{
			return array_shift(array_keys($cmd));
		}
		else 
		{
			return $cmd;
		}
	}
	
	/**
	 * Block authentication based on current request
	 * 
	 * @return boolean 
	 */
	protected static function blockedAuthentication($a_current_script)
	{		
		if($a_current_script == "register.php" || 
			$a_current_script == "pwassist.php" ||
			$a_current_script == "confirmReg.php" ||
			$a_current_script == "il_securimage_play.php" ||
			$a_current_script == "il_securimage_show.php") 
		{
			return true;
		}
		
		if($_REQUEST["baseClass"] == "ilStartUpGUI")
		{
			$cmd_class = $_REQUEST["cmdClass"];
			
			if($cmd_class == "ilaccountregistrationgui" ||
				$cmd_class == "ilpasswordassistancegui")
			{
				return true;
			}
			
			$cmd = self::getCurrentCmd();
			if($cmd == "showTermsOfService" || $cmd == "showClientList" || 
				$cmd == 'showAccountMigration' || $cmd == 'migrateAccount' ||
				$cmd == 'processCode')
			{
				return true;
			}
		}
		
		// #12884
		if(($a_current_script == "goto.php" && $_GET["target"] == "impr_0") ||
			$_GET["baseClass"] == "ilImprintGUI")
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Is current view the login form?
	 * 
	 * @return boolean 
	 */
	protected static function showingLoginForm($a_current_script)
	{		
		if($a_current_script == "login.php")
		{
			return true;
		}
		
		if($_REQUEST["baseClass"] == "ilStartUpGUI" && 
			self::getCurrentCmd() == "showLogin")
		{	
			return true;					
		}
		
		return false;
	}
	
	/**
	 * Translate message if possible 
	 *
	 * @param string $a_message_id
	 * @param array $a_message_static
	 * @return string
	 */
	protected static function translateMessage($a_message_id, array $a_message_static = null)
	{
		global $ilDB, $lng, $ilSetting, $ilClientIniFile, $ilUser;
			
		// current language
		if(!$lng)
		{
			$lang = "en";
			if($ilUser)
			{
				$lang = $ilUser->getLanguage();
			}
			else if($_REQUEST["lang"])
			{
				$lang = (string)$_REQUEST["lang"];
			}
			else if($ilSetting)
			{
				$lang = $ilSetting->get("language");
			}
			else if($ilClientIniFile)
			{
				$lang = $ilClientIniFile->readVariable("language", "default");
			}			
		}
		else
		{
			$lang = $lng->getLangKey();
		}

		$message = "";
		if($ilDB && $a_message_id)
		{	
			if(!$lng)
			{
				require_once "./Services/Language/classes/class.ilLanguage.php";
				$lng = new ilLanguage($lang);
			}
					
			$lng->loadLanguageModule("init");
			$message = $lng->txt($a_message_id);	
		}
		else if(is_array($a_message_static))
		{
			if(!isset($a_message_static[$lang]))
			{
				$lang = "en";
			}
			$message = $a_message_static[$lang];
		}
	
		return utf8_decode($message);
	}
	
	/**
	 * Redirects to target url if context supports it
	 * 
	 * @param string $a_target
	 * @param string $a_message_id
	 * @param array $a_message_details
	 */
	protected static function redirect($a_target, $a_message_id, $a_message_static)
	{		
		// #12739
		if(defined("ILIAS_HTTP_PATH") &&
			!stristr($a_target, ILIAS_HTTP_PATH))
		{
			$a_target = ILIAS_HTTP_PATH."/".$a_target;
		}
		
		if(ilContext::supportsRedirects())
		{
			ilUtil::redirect($a_target);
		}		
		else
		{			
			$message = self::translateMessage($a_message_id, $a_message_static);
			
			// user-directed linked message
			if(ilContext::usesHTTP() && ilContext::hasHTML())
			{								
				$link = self::translateMessage("init_error_redirect_click", 
					array("en" => 'Please click to continue.',
						"de" => 'Bitte klicken um fortzufahren.'));				
				$mess = $message.
					'<br /><a href="'.$a_target.'">'.$link.'</a>';					
			}
			// plain text 
			else
			{									
				// not much we can do here
				$mess = $message;		
				
				if(!trim($mess))
				{
					$mess = self::translateMessage("init_error_redirect_info", 
						array("en" => 'Redirect not supported by context.',
							"de" => 'Weiterleitungen werden durch Kontext nicht unterstützt.')).
					' ('.$a_target.')';					
				}
			}
									
			self::abortAndDie($mess);			
		}
	}
}

?>