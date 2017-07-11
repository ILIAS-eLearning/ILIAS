<?php
/**
 * This patch will move all the exercise instruction files from outside document root to inside.
 * I assume that all the files located in "ass_XXX" --> outside ilias /outside_data_directory/client_name/ilExercise/X/exc_XXX/ass_XXX/0
 * are all instruction files.
 * solution files are stored in feedb_xx/0/xxxx.xx
 */


require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();
include ("./include/inc.header.php");
global $DIC;

$db = $DIC->database();
$log = $DIC->logger();

$result = $db->query("SELECT id,exc_id FROM exc_assignment");

while($row = $db->fetchAssoc($result))
{
	include_once("./Modules/Exercise/classes/class.ilFSStorageExercise.php");
	$storage = new ilFSStorageExercise($row['exc_id'], $row['id']);

	$files = $storage->getFiles();
	if(!empty($files))
	{
		foreach ($files as $file)
		{
			$file_name = $file['name'];
			$file_full_path = $file['fullpath'];
			$file_relative_path = str_replace(ILIAS_DATA_DIR,"",$file_full_path);
			$directory_relative_path = str_replace($file_name, "",$file_relative_path);

			if (!is_dir(ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR.$directory_relative_path))
			{
				shell_exec("mkdir -p ".ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR.$directory_relative_path);
			}
			if (!file_exists("'".ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR.$file_relative_path."'"))
			{
				shell_exec("mv '".$file_full_path."' '".ILIAS_ABSOLUTE_PATH."/".ILIAS_WEB_DIR.$file_relative_path."'");
			}
		}
	}

}
