<?php

/**
* setup file for ilias
* 
* this file helps setting up ilias
* main purpose is writing the ilias.ini to the filesystem
* it can set up the database to if the settings are correct and the dbuser has the rights
* 
* @version $Id$
* @package ilias
* @author Peter Gabriel <pgabriel@databay.de>
*/

//include classes - later in the program it will be done by ilias.header.inc
include_once("./classes/class.Setup.php");
include_once("./classes/class.Language.php");
include_once("./classes/class.Log.php");
include_once("HTML/IT.php");

$OK = "<font color=\"green\"><strong>OK</strong></font>";
$FAILED = "<strong><font color=red>FAILED</font></strong>";

//CVS - REVISION - DO NOT MODIFY
$REVISION = "$Revision$";
$VERSION = substr(substr($REVISION,2),0,-2);

//instantiate template - later in the program please use own Templateclass
$tpl = new IntegratedTemplate("./templates");
$tpl->loadTemplatefile("tpl.setup.html", false, true);

$log = new Log("ilias.log");

//instantiate setup-class
$mySetup = new Setup();

//reload setupscript if $step is empty
if ($_GET["step"] == "")
    $step = "preliminaries";

//instantiate language class
if ($_GET[$lang] == "")
	$lang = "en";

$lng = new Language($lang);

$langs = $lng->getAllLanguages();

foreach ($langs as $row)
{
	$tpl->setCurrentBlock("languages");
	$tpl->setVariable("LINK_LANG", "./setup.php?lang=".$row["id"]."&amp;step=".$_GET["step"]);
	$tpl->setVariable("LANG_DESC", strtoupper($row["id"]));
	$tpl->parseCurrentBlock();
}

//main language texts
$tpl->setVariable("TXT_SETUP", $lng->txt("setup"));
$tpl->setVariable("TXT_SETUP_WELCOME", "This is the Install-routine of ILIAS.");
$tpl->setVariable("VERSION", $VERSION);


//languages
if ($step == 4)
{

}

//third step of installation process
//install database
if ($step == 3)
{
		
		$mySetup->readIniFile();
		//output
		
		//try to connect to the database
		//if connection is successful and database is present the user may advance to login
		if ($mySetup->installDatabase()==true)
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
	if ($mySetup->readIniFile()==false)
	{
	    $msg = "It seems this is the first start of ILIAS. No IniFile present.";
	    $msg .= "<br>Please fill out all fields";
	}
	else
	{
	    $msg = "An ini-File exists. Note that this Installation Process deletes previous settings.";
	}
	
	//check if ini-file is writable and build msg
	if ($mySetup->checkRootWritable()==false)
	{
		$msg .= "<br>ILIAS Setup cannot write the ini-file. Please set the write-permissions first.";
	}
	else
	{
	    $msg .= "<br>ILIAS setup can write your Ini-File.";
	}
	
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
	if ($mySetup->checkDatabaseExists() == true)
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
	$check = $lng->getAllLanguages();
	if (count($check) > 0)
		$msg = $OK.", ".count($check). " ".$lng->txt("languages");
	else
	{
		$msg = "";
		$ok = false;
	}

	$tpl->setCurrentBlock("link");
	$tpl->setVariable("NR", "3");
	$tpl->setVariable("TXT_LINK", $lng->txt("setup")." ".$lng->txt("languages"));
	$tpl->setVariable("LINK", "setup.php?step=4&lang=".$lang);
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


if ($step == "preliminaries")
{
	//get preliminaries	
	$arCheck = $mySetup->preliminaries();
	
	//writable folder
	$tpl->setCurrentBlock("preliminary");
	$tpl->setVariable("TXT_PRE", "Writable ILIAS3 folder");
	if ($arCheck["root"] == true)
		$tpl->setVariable("STATUS_PRE", $OK);
	else
		$tpl->setVariable("STATUS_PRE", $FAILED);
	$tpl->parseCurrentBlock();

	//languages
	$tpl->setCurrentBlock("preliminary");
	$tpl->setVariable("TXT_PRE", "Writable language folder");
	if ($arCheck["lang"] == true)
		$tpl->setVariable("STATUS_PRE", $OK);
	else
		$tpl->setVariable("STATUS_PRE", $FAILED);
	$tpl->parseCurrentBlock();

	//summary
	if ($arCheck["root"] == true && $arCheck["lang"] == true)
	{
		$tpl->setCurrentBlock("all_ok");
		$tpl->setVariable("LINK_OVERVIEW", "setup.php?step=begin&lang=".$lang);
		$tpl->parseCurrentBlock();
	}
	else
	{
		$tpl->setCurrentBlock("premessage");
		$tpl->setVariable("TXT_PREERR", "One or two things failed. Please correct the problems first or you cannot setup ILIAS.");
		$tpl->parseCurrentBlock();
	}
}

//display output
$tpl->show();

?>