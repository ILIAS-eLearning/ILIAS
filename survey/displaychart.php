<?php
	require_once "classes/class.SurveyChart.php";
	$arr1 = unserialize(base64_decode($_GET["arr"]));
	$graphName = utf8_decode($_GET["grName"]);
	foreach ($arr1 as $key => $value)
	{
		foreach ($value as $key2 => $value2)
		{
			$arr1[$key][$key2] = utf8_decode($value2);
		}
	}
	$b1 = new SurveyChart($_GET["type"],400,250,$graphName,utf8_decode($_GET["x"]),utf8_decode($_GET["y"]),$arr1);
?>
