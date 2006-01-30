<?php
chdir("..");
include_once "./webservice/soap/include/inc.soap_functions.php";
$results = array();
foreach ($_POST as $key => $value)
{
	if (preg_match("/value_(\d+)_1/", $key, $matches))
	{
		array_push($results, $_POST["value_" . $matches[1] . "_1"]);
		array_push($results, $_POST["value_" . $matches[1] . "_2"]);
		array_push($results, $_POST["points_" . $matches[1]]);
	}
}
$res = saveQuestionResult($_POST["session_id"]."::".$_POST["client"],$_POST["user_id"], $_POST["test_id"], $_POST["question_id"], $_POST["pass"], $results);
if ($res === true)
{
	global $lng;
	$lng->loadLanguageModule("assessment");
	echo $lng->txt("javaapplet_successful_saved");
}
else
{
	echo $lng->txt("javaapplet_unsuccessful_saved");
	//echo print_r($res, true);
}
/*
chdir("..");
include_once "./include/inc.header.php";
if ($_POST["test_id"] > 0)
{
	global $ilDB;
	$query = sprintf("DELETE FROM tst_solutions WHERE user_fi = %s AND test_fi = %s AND question_fi = %s AND pass = %s",
		$ilDB->quote($_POST["user_id"] . ""),
		$ilDB->quote($_POST["test_id"] . ""),
		$ilDB->quote($_POST["question_id"] . ""),
		$ilDB->quote($_POST["pass"] . "")
	);
	$result = $ilDB->query($query);
	foreach ($_POST as $key => $value)
	{
		if (preg_match("/value_(\d+)_1/", $key, $matches))
		{
			$query = sprintf("INSERT INTO tst_solutions (solution_id, user_fi, test_fi, question_fi, value1, value2, points, pass, TIMESTAMP) VALUES (NULL, %s, %s, %s, %s, %s, %s, %s, NULL)",
				$ilDB->quote($_POST["user_id"] . ""),
				$ilDB->quote($_POST["test_id"] . ""),
				$ilDB->quote($_POST["question_id"] . ""),
				$ilDB->quote($_POST["value_" . $matches[1] . "_1"] . ""),
				$ilDB->quote($_POST["value_" . $matches[1] . "_2"] . ""),
				$ilDB->quote($_POST["points_" . $matches[1]] . ""),
				$ilDB->quote($_POST["pass"] . "")
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
*/
?>