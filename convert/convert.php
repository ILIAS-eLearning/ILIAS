<?php

/**
* convert ...
*
* @author Matthias Rulinski <matthias.rulinski@mi.uni-koeln.de>
* @version $Id$
*/

//include files
require_once "class.ILIAS2To3Converter.php";

// Sicherheitsabfragen fehlen ***
if ($_REQUEST["ok"] == "ok")
{
	// connection data
	$user = $_REQUEST["user"];
	$pass = $_REQUEST["pass"];
	$host = $_REQUEST["host"];
	$dbname = $_REQUEST["dbname"];
	
	// Learnunit id, source directory, target directory, filename
	$luId = (integer) $_REQUEST["luId"];
	$iliasDir = $_REQUEST["iliasDir"];
	$sDir = $_REQUEST["sDir"];
	$tDir = $_REQUEST["tDir"];
	$file = $_REQUEST["file"];
	
	// test run ***
	if (is_integer($luId) and
		is_string($iliasDir) and
		is_string($sDir) and
		is_string($tDir) and
		is_string($file))
	{
		$exp = new ILIAS2To3Converter($user, $pass, $host, $dbname);
		// *** in den Konstruktor damit. ...
		$exp->luId = $luId;
		$exp->iliasDir = $iliasDir;
		$exp->sourceDir = $sDir;
		$exp->targetDir = $tDir;
		$exp->file = $file;
		$exp->dumpFile();
	}
	else
	{
		echo "Fill all fields, please.";
	}
}
else
{
	echo "<html>\n".
			"<head>\n".
				"<title>ILIAS2export (experimental)</title>\n".
			"</head>\n".
			"<body>\n".
				"Export of ILIAS 2 Learningunits to ILIAS 3 LearningModules (experimental)<br /><br />\n".
				"<form action=\"".$_SERVER["PHP_SELF"]."\" method=\"post\" enctype=\"multipart/form-data\">\n".
					"ILIAS 2 Databaseconnection:<br /><br />\n".
					"user:<br /><input type=\"text\" name=\"user\" maxlengh=\"30\" size=\"20\" value=\"mysql\"><br />\n".
					"pass:<br /><input type=\"password\" name=\"pass\" maxlengh=\"30\" size=\"20\" value=\"\"><br />\n".
					"host:<br /><input type=\"text\" name=\"host\" maxlengh=\"30\" size=\"20\" value=\"localhost\"><br />\n".
					"dbname:<br /><input type=\"text\" name=\"dbname\" maxlengh=\"30\" size=\"20\" value=\"ilias\"><br /><br />\n".
					"Id of the Learningunit to be exported:<br /><br />\n".
					"<input type=\"text\" name=\"luId\" maxlengh=\"10\" size=\"10\" value=\"5\"><br /><br />\n".
					"Full path of the ILIAS 2 base directory:<br /><br />\n".
					"<input type=\"text\" name=\"iliasDir\" maxlengh=\"50\" size=\"40\" value=\"\"><br /><br />\n".
					"Full path of the source directory containing the raw data files:<br /><br />\n".
					"<input type=\"text\" name=\"sDir\" maxlengh=\"50\" size=\"40\" value=\"\"><br /><br />\n".
					"Full path of the target directory to copy the XML file and  the raw data files to:<br /><br />\n".
					"<input type=\"text\" name=\"tDir\" maxlengh=\"50\" size=\"40\" value=\"\"><br /><br />\n".
					"Filename for the generated XML file:<br /><br />\n".
					"<input type=\"text\" name=\"file\" maxlengh=\"50\" size=\"40\" value=\"lo.xml\"><br /><br />\n".
					"<input type=\"submit\" name=\"ok\" value=\"ok\">\n".
				"</form>\n".
			"</body>\n".
		"</html>\n";
}

?>