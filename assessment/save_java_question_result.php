<?php
define("ILIAS_MODULE", "assessment");
chdir("..");
require_once "./include/inc.header.php";
if ($_GET["test_id"] > 0)
{
	global $ilDB;
	$query = sprintf("INSERT INTO tst_solution (solution_id, user_fi, test_fi, question_fi, value1, value2, points, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, NULL)",
		$ilDB->quote($_GET["user_id"]),
		$ilDB->quote($_GET["test_id"]),
		$ilDB->quote($_GET["question_id"]),
		"NULL",
		"NULL",
		$ilDB->quoute($_GET["points_max"])
	);
	$result = $ilDB->query($query);
}
?>