<?php
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
require_once "classes/class.Setup.php";
require_once "classes/class.Language.php";
require_once "classes/class.Log.php";
require_once "HTML/IT.php";

$OK = "<font color=\"green\"><strong>OK</strong></font>";
$FAILED = "<strong><font color=red>FAILED</font></strong>";

//CVS - REVISION - DO NOT MODIFY
$REVISION = "$Revision$";
$VERSION = substr(substr($REVISION,2),0,-2);

//instantiate template - later in the program please use own Templateclass
$tpl = new IntegratedTemplate("./templates/default");
$tpl->loadTemplatefile("tpl.setup.html", true, true);

// prepare file access to work with safe mode
umask(0117);

$log = new Log("ilias.log");

//instantiate setup-class
$mySetup = new Setup();

//reload setupscript if $step is empty
if ($_GET["step"] == "")
    $step = "preliminaries";

//instantiate language class
if ($_GET["lang"] == "")
	$lang = "en";

$lng = new Language($lang);

$languages = $mySetup->getLanguages($lng->lang_path);

foreach ($languages as $lang_key)
{
	$tpl->setCurrentBlock("languages");
	$tpl->setVariable("LINK_LANG", "./setup.php?lang=".$lang_key."&amp;step=".$_GET["step"]);
	$tpl->setVariable("LANG_DESC", strtoupper($lang_key));
	$tpl->parseCurrentBlock();
}

//main language texts
$tpl->setVariable("LANG", $lang);
$tpl->setVariable("TXT_SETUP", $lng->txt("setup"));
$tpl->setVariable("TXT_SETUP_WELCOME", "This is the Install-routine of ILIAS.");
$tpl->setVariable("VERSION", $VERSION);

// password
if ($step == 5)
{
	// show menu link
	$tpl->setCurrentBlock("step5");
	$tpl->setVariable("TXT_PASSWORD5", $lng->txt("password"));
	$tpl->setVariable("TXT_STEP5", $lng->txt("setup"));
	$tpl->setVariable("LINK_STEP5", "setup.php?step=begin&lang=".$lang);
	$tpl->parseCurrentBlock();
}


//languages
if ($step == 4)
{
	// show menu link
	$tpl->setCurrentBlock("step4");
	$tpl->setVariable("TXT_LANGUAGES4", $lng->txt("languages"));
	$tpl->setVariable("TXT_STEP4", $lng->txt("setup"));
	$tpl->setVariable("LINK_STEP4", "setup.php?step=begin&lang=".$lang);
	$tpl->parseCurrentBlock();
}

//third step of installation process
//install database
if ($step == 3)
{
		if (!$mySetup->readIniFile())
		{
			$msg = "<br/>Please set up your ini-file first.";
			$tpl->setCurrentBlock("message");
			$tpl->setVariable("TXT_ERR", $lng->txt($mySetup->error).$msg);
			$tpl->parseCurrentBlock();	
		}
		//output
		
		//try to connect to the database
		//if connection is successful and database is present the user may advance to login
		if ($mySetup->installDatabase() == true)
		{
			//user may now login
			$tpl->setCurrentBlock("message");
			$tpl->setVariable("TXT_ERR", $lng->txt("setup_ready"));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("step3");
			$tpl->setVariable("TXT_DATABASE3", $lng->txt("database"));
			$tpl->setVariable("TXT_STEP3", $lng->txt("setup"));
			$tpl->setVariable("LINK_STEP3", "setup.php?step=begin&amp;lang=".$lang);
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("message");
			$tpl->setVariable("TXT_ERR", $lng->txt($mySetup->error));
			$tpl->parseCurrentBlock();

			$tpl->setCurrentBlock("step3");
			$tpl->setVariable("TXT_DATABASE3", $lng->txt("database"));
			$tpl->setVariable("TXT_STEP3", $lng->txt("setup"));
			$tpl->setVariable("LINK_STEP3", "setup.php?step=begin&amp;lang=".$lang);
			$tpl->parseCurrentBlock();
		}

}

//second step of installation process
//write ini file or if this fails, display content of ini-file on screen
if ($step == 2)
{
	$mySetup->setDbType($_POST["dbtype"]);
	$mySetup->setDbHost($_POST["dbhost"]);
	$mySetup->setDbName($_POST["dbname"]);
	$mySetup->setDbUser($_POST["dbuser"]);
	$mySetup->setDbPass($_POST["dbpass"]);
	
	//write the inifile if all things are okay
	if ($mySetup->writeIniFile() == false)
	{
		$tpl->setCurrentBlock("message");
		$tpl->setVariable("TXT_ERR", "ERROR");
		$tpl->parseCurrentBlock();
		
		$tpl->setCurrentBlock("step2");
		$tpl->setVariable("TXT_INIFILE2", $lng->txt("inifile"));
		$tpl->setVariable("TXT_STEP2", $lng->txt("step")." 1: ".$lng->txt("setup")." ".$lng->txt("inifile"));
		$tpl->setVariable("LINK_STEP2", "setup.php?step=1&lang=".$lang);
		$tpl->parseCurrentBlock();
	}
	else
	{
		$tpl->setCurrentBlock("step2_success");
		$tpl->setVariable("INIFILECONTENT", $mySetup->ini->show());
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("step2");
		$tpl->setVariable("TXT_INIFILE2", $lng->txt("inifile"));
		$tpl->setVariable("TXT_STEP2", $lng->txt("setup"));
		$tpl->setVariable("LINK_STEP2", "setup.php?step=begin&lang=".$lang);
		$tpl->parseCurrentBlock();
	}


}

//first step of installation process
//give basic data of inifile:
//database data
if ($step == 1)
{
	$dbhost = $_POST["dbhost"];
	$dbname = $_POST["dbname"];
	$dbuser = $_POST["dbuser"];
	$dbpass = $_POST["dbpass"];
	
	//load defaults if neccessary
	if ($_POST["dbhost"] == "")
	{
		$mySetup->getDefaults();		
		$dbtype = $mySetup->default["db"]["type"];
		$dbhost = $mySetup->default["db"]["host"];
		$dbname = $mySetup->default["db"]["name"];
		$dbuser = $mySetup->default["db"]["user"];
		$dbpass = $mySetup->default["db"]["pass"];
	}
	
	//try to read the ini-file and build msg if error
	if ($mySetup->readIniFile() == false)
	{
	    $msg = "It seems this is the first start of ILIAS3. No ini-file present.";
	    $msg .= "<br>Please fill out all fields";
	}
	else
	{
	    $msg = "An ini-file exists. Note that this Installation process deletes previous settings.";
	}
	
	//check if ini-file is writable and build msg
	if ($mySetup->checkWritable() == false)
	{
		$msg .= "<br>ILIAS3 setup cannot write the ini-file. Please set the write permissions first.";
	}
	else
	{
	    $msg .= "<br>ILIAS3 setup can write your ini-file.";
	}
	
	// show menu link
	$tpl->setCurrentBlock("step2");
	$tpl->setVariable("TXT_STEP2", $lng->txt("setup"));
	$tpl->setVariable("LINK_STEP2", "setup.php?step=begin&lang=".$lang);
	$tpl->parseCurrentBlock();
	
	//output message
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("TXT_ERR", $msg);
	$tpl->parseCurrentBlock();
	
	//get the defaults from setup class
	$mySetup->getDefaults();
	reset ($mySetup->dbTypes);
	
	//go through database-types and output them
	while (list($k,$v) = each($mySetup->dbTypes))
	{
		//select box in template
		$tpl->setCurrentBlock("seldbtype");
		$tpl->setVariable("DBTYPESHORT", $k);
		if ($mySetup->dbType == $k)
			$tpl->setVariable("SELDBSELECTED", " selected");
		$tpl->setVariable("DBTYPE", $v);
		$tpl->parseCurrentBlock();
	} 

	//text output
	$tpl->setVariable("TXT_INIFILE1", $lng->txt("inifile"));
	$tpl->setVariable("TXT_DB_HOST", $lng->txt("db_host"));
	$tpl->setVariable("TXT_DB_NAME", $lng->txt("db_name"));
	$tpl->setVariable("TXT_DB_TYPE", $lng->txt("db_type"));
	$tpl->setVariable("TXT_DB_USER", $lng->txt("db_user"));
	$tpl->setVariable("TXT_DB_PASS", $lng->txt("db_pass"));
	$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
	$tpl->setVariable("TXT_RESET", $lng->txt("reset"));

	//variable content output
	$tpl->setVariable("LANG1", $lang);
	$tpl->setVariable("DB_HOST", $dbhost);
	$tpl->setVariable("DB_NAME", $dbname);
	$tpl->setVariable("DB_TYPE", $dbtype);
	$tpl->setVariable("DB_USER", $dbuser);
	$tpl->setVariable("DB_PASS", $dbpass);
}



if ($step == "begin")
{
	$ok = true;
	
	//inifile
	if ($mySetup->checkIniFileExists() == true)
		$msg = $OK;
	else
	{
		$msg = "";
		$ok = false;
	}

	$tpl->setCurrentBlock("link");
	$tpl->setVariable("NR", "1");
	$tpl->setVariable("TXT_LINK", $lng->txt("setup")." ".$lng->txt("inifile"));
	$tpl->setVariable("LINK", "setup.php?step=1&lang=".$lang);
	$tpl->setVariable("STATUS", $msg);
	$tpl->parseCurrentBlock();
	
	//database
	$mySetup->readIniFile();
	$db_status = $mySetup->checkDatabaseExists();
	if ($db_status["status"] == true)
		$msg = $OK;
	else
	{
		$msg = "";
		$ok = false;
	}

	$tpl->setCurrentBlock("link");
	$tpl->setVariable("NR", "2");
	$tpl->setVariable("TXT_LINK", $lng->txt("setup")." ".$lng->txt("database"));
	$tpl->setVariable("LINK", "setup.php?step=3&lang=".$lang);
	$tpl->setVariable("STATUS", $msg);
	$tpl->parseCurrentBlock();

	//languages
	
	$msg = "";

	$tpl->setCurrentBlock("link");
	$tpl->setVariable("NR", "3");
	$tpl->setVariable("TXT_LINK", $lng->txt("setup")." ".$lng->txt("languages"));
	$tpl->setVariable("LINK", "setup.php?step=4&lang=".$lang);
	$tpl->setVariable("STATUS", $msg);
	$tpl->parseCurrentBlock();

	//password
	//if (count($langs) > 0)
	//	$msg = $OK.", ".count($langs). " ".$lng->txt("languages");
	//else
	//{
	//	$msg = "";
	//	$ok = false;
	//}

	$tpl->setCurrentBlock("link");
	$tpl->setVariable("NR", "4");
	$tpl->setVariable("TXT_LINK", $lng->txt("setup")." ".$lng->txt("password"));
	$tpl->setVariable("LINK", "setup.php?step=5&lang=".$lang);
	$tpl->setVariable("STATUS", $msg);
	$tpl->parseCurrentBlock();
	
	//login
	if ($ok == true)
	{
		$tpl->setCurrentBlock("ready");
		$tpl->setVariable("TXT_LOGIN", $lng->txt("login"));
		$tpl->setVariable("LINK_LOGIN", "login.php?lang=".$lang);
		
		$tpl->parseCurrentBlock();
	}	
	
	$tpl->setCurrentBlock("begininstallation");
	$tpl->parseCurrentBlock();
}

// ENVIRONMENT & PRELIMINARIES
if ($step == "preliminaries")
{
	$server_os = php_uname();
	$server_web = $_SERVER["SERVER_SOFTWARE"];
	$environment = "ILIAS3 will be running on ".$server_os." <br/>with ".$server_web.".<br/><br/>OK. Sounds good.";
		
	$tpl->setVariable("TXT_ENV", $environment);

	//get preliminaries	
	$arCheck = $mySetup->preliminaries();
	
	// phpversion
	$tpl->setCurrentBlock("preliminary");
	$tpl->setVariable("TXT_PRE", "PHP version: ".$arCheck["php"]["version"]);
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

	// writable folder
	$tpl->setCurrentBlock("preliminary");
	$tpl->setVariable("TXT_PRE", "Writable ILIAS3 folder");
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
	
	// Can creating new foldersr
	$tpl->setCurrentBlock("preliminary");
	$tpl->setVariable("TXT_PRE", "New folders creatable in ILIAS3 folder");
	if ($arCheck["create"]["status"] == true)
	{
		$tpl->setVariable("STATUS_PRE", $OK);
	}
	else
	{
		$tpl->setVariable("STATUS_PRE", $FAILED);
		$tpl->setVariable("COMMENT_PRE", $arCheck["create"]["comment"]);
	}
	$tpl->parseCurrentBlock();

	//summary
	if ($arCheck["php"] == true && $arCheck["root"] == true && $arCheck["create"] == true)
	{
		$tpl->setCurrentBlock("all_ok");
		$tpl->setVariable("LINK_OVERVIEW", "setup.php?step=begin&lang=".$lang);
		$tpl->parseCurrentBlock();
	}
	else
	{
		$tpl->setCurrentBlock("premessage");
		$tpl->setVariable("TXT_PREERR", "One or two things failed. Please correct the problems first or you cannot setup ILIAS3.");
		$tpl->parseCurrentBlock();
	}
}

$tpl->show();
?>