<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/** @defgroup ServicesInit Services/Init
 */

/**
* ILIAS Initialisation Utility Class
* perform basic setup: init database handler, load configuration file,
* init user authentification & error handler, load object type definitions
*
* @author Alex Killing <alex.killing@gmx.de>
* @author Sascha Hofmann <shofmann@databay.de>
* @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
* @version $Id: class.ilInitialisation.php 26932 2010-12-09 16:26:46Z mjansen $
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
		require_once "./Services/Utilities/classes/class.ilBenchmark.php";
		$ilBench = new ilBenchmark();
		$GLOBALS['ilBench'] = $ilBench;

		// Major PEAR Includes
		require_once "PEAR.php";
		//require_once "DB.php";
		// require_once "Auth/Auth.php";

		

		//include classes and function libraries
		require_once "./Services/Database/classes/class.ilDB.php";
		// require_once "classes/class.ilias.php";
		// require_once './Services/User/classes/class.ilObjUser.php';
		require_once "./Services/Logging/classes/class.ilLog.php";
//		require_once "classes/class.ilObjectDataCache.php";
		require_once "./Services/Init/classes/class.ilErrorHandling.php";
		
		require_once "./Services/Administration/classes/class.ilSetting.php";
	}
	

	/**
	* This method provides a global instance of class ilIniFile for the
	* ilias.ini.php file in variable $ilIliasIniFile.
	*
	* It initializes a lot of constants accordingly to the settings in
	* the ilias.ini.php file.
	*/
	function initIliasIniFile() //VIELES KANN WEG
	{
		global $ilIliasIniFile;

		require_once("./Services/Init/classes/class.ilIniFile.php");
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
		if ($_GET["client_id"] != "") define ("CLIENT_ID", $_GET["client_id"]);
		else define ("CLIENT_ID", $ilIliasIniFile->readVariable("clients","default"));
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
		require_once("./Services/Init/classes/class.ilIniFile.php");
		$ilClientIniFile = new ilIniFile($ini_file);
		$GLOBALS['ilClientIniFile'] =& $ilClientIniFile;
		$ilClientIniFile->read();

		// if no ini-file found switch to setup routine
		if ($ilClientIniFile->ERROR != "")
		{
			throw new ilException('No Client INI File!! (ClientID "'.CLIENT_ID.'" / '.$ilClientIniFile->ERROR.')');
			return false;
		}

		// set constants
		define ("CLIENT_DATA_DIR",ILIAS_DATA_DIR."/".CLIENT_ID);
		define ("CLIENT_WEB_DIR",ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR."/".CLIENT_ID);
		define ("CLIENT_NAME",$ilClientIniFile->readVariable('client','name')); // Change SS

		define ("ROOT_FOLDER_ID",$ilClientIniFile->readVariable('system','ROOT_FOLDER_ID'));

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
	* initialise database object $ilDB
	*
	*/
	function initDatabase()
	{
		global $ilDB, $ilClientIniFile;

		// build dsn of database connection and connect
		require_once("./Services/Database/classes/class.ilDBWrapperFactory.php");
		$ilDB = ilDBWrapperFactory::getWrapper(IL_DB_TYPE);
		$ilDB->initFromIniFile();
		$ilDB->connect();
		$GLOBALS['ilDB'] = $ilDB;
		
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

		// error reporting
		// remove notices from error reporting
		if (version_compare(PHP_VERSION, '5.3.0', '>='))
		{
			error_reporting((ini_get("error_reporting") & ~E_NOTICE) & ~E_DEPRECATED);
		}
		else
		{
			error_reporting(ini_get('error_reporting') & ~E_NOTICE);
		}

		$ilUser = new ilObjUserMin();
		$GLOBALS['ilUser'] =& $ilUser;

		
		// include common code files
		$this->requireCommonIncludes();
		global $ilBench;
		// $ilIliasIniFile initialisation
		$this->initIliasIniFile();


		// CLIENT_ID determination
		$this->determineClient();

		// $ilAppEventHandler initialisation
		//$this->initEventHandling();


		// $ilClientIniFile initialisation
		if (!$this->initClientIniFile())
		{
			die("no client");
		}

		// $ilDB initialisation
		$this->initDatabase();
		$this->initLog();
		
		// settings
		$ilSetting = new ilSetting();
		$GLOBALS['ilSetting'] = & $ilSetting;

		// init tree
		require_once "./Services/Tree/classes/class.ilTree.php";
		$tree = new ilTree(ROOT_FOLDER_ID);
		$GLOBALS['tree'] =& $tree;

		
		if ((string) $_GET['do'] == "unload") {
			include_once './Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';
			ilSCORM2004StoreData::scormPlayerUnload(null, (int)$_GET['package_id']);
		}
		else $this->persistCMIData();
	}
	function initLog() {
		global $ilLog;
		$log = new ilLog(ILIAS_LOG_DIR,ILIAS_LOG_FILE,CLIENT_ID,ILIAS_LOG_ENABLED,ILIAS_LOG_LEVEL);
		$GLOBALS['log'] = $log;
		$ilLog = $log;
		$GLOBALS['ilLog'] = $ilLog;
	}

	public function persistCMIData()
	{
		global $ilLog, $ilDB, $ilUser;
		$packageId=(int)$_GET['package_id'];
		$lm_set = $ilDB->queryF('SELECT default_lesson_mode, interactions, objectives, comments FROM sahs_lm WHERE id = %s', 
			array('integer'),array($packageId));
		
		while($lm_rec = $ilDB->fetchAssoc($lm_set))
		{
			$defaultLessonMode=($lm_rec["default_lesson_mode"]);
			$interactions=(ilUtil::yn2tf($lm_rec["interactions"]));
			$objectives=(ilUtil::yn2tf($lm_rec["objectives"]));
			$comments=(ilUtil::yn2tf($lm_rec["comments"]));
		}
		$data = file_get_contents('php://input');
		$ilUser->setId($data->p);

		include_once './Modules/Scorm2004/classes/class.ilSCORM2004StoreData.php';
		ilSCORM2004StoreData::persistCMIData(null, $packageId, 
		$defaultLessonMode, $comments, 
		$interactions, $objectives,$data);
	}
}
class ilObjUserMin
{
	var $id;
	function getId()
	{
		return $this->id;
	}
	function setId($a_id)
	{
		$this->id = $a_id;
	}
	function getLanguage()
	{
		return 'en';
	}
}
?>
