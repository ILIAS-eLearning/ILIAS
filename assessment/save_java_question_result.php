<?php
define("ILIAS_MODULE", "assessment");
chdir("..");
require_once "./include/inc.header.php";
if ($_POST["test_id"] > 0)
{
	global $ilDB;
	foreach ($_POST as $key => $value)
	{
		if (preg_match("/value_(\d+)_1/", $key, $matches))
		{
			$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, points, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($_POST["user_id"]),
				$ilDB->quote($_POST["test_id"]),
				$ilDB->quote($_POST["question_id"]),
				$ilDB->quote($_POST["value_" . $matches[1] . "_1"]),
				$ilDB->quote($_POST["value_" . $matches[1] . "_2"]),
				$ilDB->quote($_POST["points_" . $matches[1]])
			);
			$result = $ilDB->query($query);
		}
	}
	global $lng;
	$lng->loadLanguageModule("assessment");
	echo $lng->txt("javaapplet_successful_saved");
}
else
{
	echo $lng->txt("javaapplet_unsuccessful_saved");
}

?>