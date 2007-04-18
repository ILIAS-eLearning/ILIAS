<?php
// this script is called from the integrated flashapplication. The Flashapplication calls this script with the command getURL()
// The Header is set to No Content - therefore no window will be loaded
header('HTTP/1.1 204 No Content');

	chdir("..");
	require_once "./include/inc.header.php";
	include_once "./Services/Utilities/classes/class.ilUtil.php";

	// calculate directory of temporary file
	// ILAS_ROOT/data directory from the current Ilias Client
	$dirname = ilUtil::removeTrailingPathSeparators(CLIENT_WEB_DIR);
	
	// success of deleting the temporary data file
	$success;

	// splits the String from the GET Parameter
	$tmp_file = substr($_GET[tmp_file],strpos($_GET[tmp_file], 'assessment' ));


	// remove special path symbols from the file name to prevent security hacks
	$tmp_file = str_replace("%", "", $tmp_file);
	$tmp_file = str_replace("\\", "", $tmp_file);
	$tmp_file = str_replace("..", "", $tmp_file);
	$tmp_file = $dirname . "/" . $tmp_file;
	
	//echo "new tmp_file: ".$tmp_file."<br/>";

if (file_exists($tmp_file))
{
	//echo "file exists";
			if (unlink($tmp_file))
			{
			$success ="Löschen des tmp Files war erfolgreich!";
			}
			else
			{
			$success ="Löschen des tmp Files war nicht erfolgreich!";
			}
}
?>