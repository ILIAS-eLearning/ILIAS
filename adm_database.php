<?PHP
/**
 * admin database
 * utils for updating the database and optimize it etc.
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

include_once("./classes/class.DBUpdate.php");

$myDB = new DBUpdate();
$tpl = new Template("tpl.adm_database.html", true, true);

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

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("database"));
$tpl->setVariable("TXT_VERSION", $lng->txt("version"));
$tpl->setVariable("TXT_DATABASE_VERSION", $lng->txt("database_version"));
$tpl->setVariable("DATABASE_VERSION", $myDB->currentVersion);
$tpl->setVariable("TXT_FILE_VERSION", $lng->txt("file_version"));
$tpl->setVariable("FILE_VERSION", $myDB->fileVersion);
$tpl->setVariable("TXT_DATABASE_VERSION_STATUS", $lng->txt($myDB->getDBVersionStatus()));

if ($myDB->getDBVersionStatus()=="database_needs_update")
{
	$tpl->setCurrentBlock("migrate");
	$tpl->setVariable("TXT_MIGRATE", $lng->txt("database_update"));
	$tpl->parseCurrentBlock();
}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>