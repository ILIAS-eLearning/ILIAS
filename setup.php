<?php

include_once("./classes/class.Setup.php");
include_once("./classes/class.Language.php");
include_once("HTML/IT.php");

/**
* setup file for ilias
* 
* this file helps setting up ilias
* 
* @version $Id$
* @package ilias
* @author Peter Gabriel <pgabriel@databay.de>
*/

$tpl = new IntegratedTemplate("./templates");
$tpl->loadTemplatefile("tpl.setup.html", false, true);

$mySetup = new Setup();

if ($step=="")
    header("location: setup.php?step=1");

$lng = new Language("en");


$tpl->setVariable("TXT_SETUP", $lng->txt("setup"));
$tpl->setVariable("TXT_SETUP_WELCOME", "This is the Install-routine of ILIAS.");


if ($step==2)
{
	$mySetup->setDbType($_POST["dbtype"]);
	$mySetup->setDbHost($_POST["dbhost"]);
	$mySetup->setDbName($_POST["dbname"]);
	$mySetup->setDbUser($_POST["dbuser"]);
	$mySetup->setDbPass($_POST["dbpass"]);
	
	if ($mySetup->writeIniFile()==false)
	{
		$step=1;
		$steperror=$mySetup->error;
	}
	$tpl->touchBlock("step2");
//	$tpl->parseCurrentBlock();
}


if ($step==1)
{
	$dbhost = $_POST["dbhost"];
	$dbname = $_POST["dbname"];
	$dbuser = $_POST["dbuser"];
	$dbpass = $_POST["dbpass"];

echo $steperror;

	//load defaults if neccessary
	if ($_POST["dbhost"]=="")
	{
		$mySetup->getDefaults();		
		$dbtype = $mySetup->default["db"]["type"];
		$dbhost = $mySetup->default["db"]["host"];
		$dbname = $mySetup->default["db"]["name"];
		$dbuser = $mySetup->default["db"]["user"];
		$dbpass = $mySetup->default["db"]["pass"];
	}
	
	if ($mySetup->readIniFile()==false)
	{
	    $msg = "It seems this is the first start of ILIAS. No IniFile present.";
	    $msg .= "<br>Please fill out all fields";
	}
	else
	{
	    $msg = "An ini-File exists. Note that this Installation Process deletes previous settings.";
	}
	
        if ($mySetup->checkIniFileWritable()==false)
        {
		$msg .= "<br>ILIAS Setup cannot write the ini-file. Please set the write-permissions first.";
        }
	else
	{
	    $msg .= "<br>ILIAS setup may write your Ini-File.";
	}
	
	$tpl->setCurrentBlock("message");
	$tpl->setVariable("TXT_ERR", $msg);
	$tpl->parseCurrentBlock();
	

		
	if ($mySetup->error != "")
	{
		echo "<p><font color=\"red\">";
		switch ($mySetup->error)
		{
			case "file_does_not_exist":
				$msg =  "It seems you just installed ILIAS. ILIAS needs a database connection to operate properly.";
				break;
			case "file_not_accessible":
				$msg =  "The Ini-File is not readable or writable. Please set permissions.";
				break;
			case "database_exists":
				$msg =  "The Database exists or your data is invalid. Please check all fields."; 
				break;
			case "data_invalid":
				$msg =  "Data is invalid. Please check the given data.<br>SQL Message: ".$mySetup->error_msg;
				break;
    			case "create_database_failed":
				$msg =  "The Connection to the database-host was successful<br> but cannot create Database on the Database-Host. Please Check. <br>SQL Message: ".$mySetup->error_msg;
				break;
			case "cannot_write":
				$msg = "Sorry, I cannot write to your ILIAS directory. Please make the dir webserver-writable";;
				break;
			default: 
				echo $mySetup->error;
		
		}
		echo "</font></p>";
	}

	
	$mySetup->getDefaults();
	reset ($mySetup->dbTypes);
	while (list($k,$v) = each($mySetup->dbTypes))
	{
	    $tpl->setCurrentBlock("seldbtype");
    	    $tpl->setVariable("DBTYPESHORT", $k);
    	    if ($mySetup->dbType == $k)
		$tpl->setVariable("SELDBSELECTED", " selected");
	    $tpl->setVariable("DBTYPE", $v);
	    $tpl->parseCurrentBlock();	
	    
	} 

	$tpl->setVariable("TXT_DB_HOST", $lng->txt("db_host"));
	$tpl->setVariable("TXT_DB_NAME", $lng->txt("db_name"));
	$tpl->setVariable("TXT_DB_TYPE", $lng->txt("db_type"));
	$tpl->setVariable("TXT_DB_USER", $lng->txt("db_user"));
	$tpl->setVariable("TXT_DB_PASS", $lng->txt("db_pass"));

	$tpl->setVariable("TXT_SUBMIT", $lng->txt("submit"));
	$tpl->setVariable("TXT_RESET", $lng->txt("reset"));

	$tpl->setVariable("DB_HOST", $dbhost);
	$tpl->setVariable("DB_NAME", $dbname);
	$tpl->setVariable("DB_TYPE", $dbtype);
	$tpl->setVariable("DB_USER", $dbuser);
    	$tpl->setVariable("DB_PASS", $dbpass);
}


$tpl->show();

?>