<?php
include_once("./classes/class.Setup.php");
/**
* setup file for ilias
* 
* this file helps setting up ilias
* 
* @version $Id$
* @package ilias
* @author Peter Gabriel <pgabriel@databay.de>
*/

$mySetup = new Setup();

/**
* html start
*/
function html_start()
{
?>

	<html>
	<head>
		<title>ILIAS - Installation</title>
		<link rel="stylesheet" type="text/css" href="ilias.css">
	</head>	
	<body>
		<table width="100%">
		<tr class="head">
			<td>ILIAS - Installation</td>
			<td>Setup Version 0.9</td>
		</tr>
		</table>
<?php
}

/**
* html end
*/
function html_end()
{
?>
	</body>
	</html>
<?php
}



/**
* step 1
*/
function step1()
{
  global $mySetup;
 
	$dbhost = $_POST["dbhost"];
	$dbname = $_POST["dbname"];
	$dbuser = $_POST["dbuser"];
	$dbpass = $_POST["dbpass"];

	if ($dbhost=="")
		$dbhost = $mySetup->dbHost;
	if ($dbname=="")
		$dbname = $mySetup->dbName;
	if ($dbuser=="")
		$dbuser = $mySetup->dbUser;

	echo "<p>Welcome to the setup of ILIAS.</p>\n";
	echo "<p>To make Ilias operatable please fill out the following fields.</p>";
	echo "<p>ILIAS will install the database with the given parameters after pressing &lt;submit&gt;.</p>\n";
	
	if ($mySetup->error != "")
	{
		echo "<p><font color=\"red\">";
		switch ($mySetup->error)
		{
			case "file_does_not_exist":
				echo "It seems you just installed ILIAS. ILIAS needs a database connection to operate properly.";
				break;
			case "file_not_accessible":
				echo "The Ini-File is not readable or writable. Please set permissions.";
				break;
			case "database_exists":
				echo "The Database exists or your data is invalid. Please check all fields."; 
				break;
			case "data_invalid":
				echo "Data is invalid. Please check the given data.<br>SQL Message: ".$mySetup->error_msg;
				break;
			case "create_database_failed":
				echo "The Connection to the database-host was successful<br> but cannot create Database on the Database-Host. Please Check. <br>SQL Message: ".$mySetup->error_msg;
				break;
			case "cannot_write":
				echo "Sorry, I cannot write to your ILIAS directory. Please make the dir webserver-writable";;
				break;
			default: 
				echo $mySetup->error;
		
		}
		echo "</font></p>";
	}
	echo "<form method=\"post\" name=\"form\" action=\"setup.php\">\n";
	echo "<input type=\"hidden\" name=\"step\" value=\"2\">";
	echo "<table>";
	echo "<tr>";
	echo "<td>Database-Type</td>";
	echo "<td><select name=\"dbtype\">";

	//
	reset ($mySetup->dbTypes);
	while (list($k,$v) = each($mySetup->dbTypes))
	{
		echo "<option value=\"$k\"";
		if ($mySetup->dbType == $k)
			echo " selected";
		echo ">$v</option>";
	} 
	echo "</select></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Database-Host</td>";
	echo "<td><input type=\"text\" size=40 name=\"dbhost\" value=\"".$dbhost."\"></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Database-Name</td>";
	echo "<td><input type=\"text\" size=40 name=\"dbname\" value=\"".$dbname."\"></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>User</td>";
	echo "<td><input type=\"text\" name=\"dbuser\" value=\"".$dbuser."\"></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>Password</td>";
	echo "<td><input type=\"text\" name=\"dbpass\" value=\"".$dbpass."\"></td>";
	echo "</tr>";
	echo "</table>";
	echo "<input type=\"submit\" name=\"butSubmit\" value=\"submit\">&nbsp;";
	echo "<input type=\"reset\" name=\"butReset\" value=\"reset\">";
	echo "</form>\n";
}

/**
* step 2
* install database
*/
function step2()
{
	global $error_msg, $mySetup;

	$mySetup->setDbType($_POST["dbtype"]);
	$mySetup->setDbHost($_POST["dbhost"]);
	$mySetup->setDbName($_POST["dbname"]);
	$mySetup->setDbUser($_POST["dbuser"]);
	$mySetup->setDbPass($_POST["dbpass"]);

	if ($mySetup->installDatabase()==true)
		return true;
	else
		return false;
}
// ***************************************************************************
// main program
// ***************************************************************************
if (empty($step))
	$step = $_POST["step"];
if (empty($step))
	$step = 1;
	

//check submitted values and check database
if ($step == 2)
{
	if (step2()==false)
		$step=1;
	else
	{
		//html output
		html_start();
		echo "<p>Database setup completed</p>";
		echo "<p>You may now login at <a href=\"index.php\">index.php</a> with login 'root' and password 'ilias'.</p>";
		html_end();
	}
}

if ($step == 1)
{
	//html output
	html_start();
	step1();		
	html_end();
}

?>