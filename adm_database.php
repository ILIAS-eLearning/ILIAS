<?PHP
/**
 * admin database
 * utils for updating the database and optimize it etc.
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @version $Id$
 *
 * @package ilias
 */
require_once "./include/ilias_header.inc";
require_once "./classes/class.DBUpdate.php";

$myDB = new DBUpdate();

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_database.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

if ($_GET["cmd"]=="migrate")
{
	$myDB->applyUpdate();

	if ($myDB->updateMsg != "no_changes")
	{
		foreach ($myDB->updateMsg as $row)
		{
			$tpl->setCurrentBlock("versionmessage");
			$tpl->setVariable("MSG", $row["msg"].": ".$row["nr"]);
			$tpl->parseCurrentBlock();
		}
	}
}

if ($_POST["cmd"] != "")
{
	$msg = $myDB->optimizeTables($key);
	foreach ($msg as $row)
	{
		$tpl->setCurrentBlock("versionmessage");
		$tpl->setVariable("MSG", $row);
		$tpl->parseCurrentBlock();
	}
}

$tpl->setVariable("TXT_DATABASE", $lng->txt("database"));
$tpl->setVariable("TXT_VERSION", $lng->txt("version"));
$tpl->setVariable("TXT_DATABASE_VERSION", $lng->txt("database_version"));
$tpl->setVariable("DATABASE_VERSION", $myDB->currentVersion);
$tpl->setVariable("TXT_FILE_VERSION", $lng->txt("file_version"));
$tpl->setVariable("FILE_VERSION", $myDB->fileVersion);
$tpl->setVariable("DATABASE_VERSION_STATUS", $lng->txt($myDB->getDBVersionStatus()));

if ($myDB->getDBVersionStatus()=="database_needs_update")
{
	$tpl->setCurrentBlock("migrate");
	$tpl->setVariable("TXT_MIGRATE", $lng->txt("database_update"));
	$tpl->parseCurrentBlock();
}


// optimization
$dbtables = $myDB->getTables();
$i = 0;
foreach ($dbtables as $row)
{
	$tpl->setCurrentBlock("optrow");
	$tpl->setVariable("TABLE", $row["name"].":".$row["status"]);
	$tpl->setVariable("TABLEID", $row["table"]);
	$tpl->setVariable("ROWCOL", "tblrow".(($i%2)+1));
	$tpl->parseCurrentBlock();
	$i++;
}

$myDB->getTableStatus("rbac_fa");

$tpl->setVariable("TXT_OPTIMIZE", $lng->txt("optimize"));

$tpl->show();

?>