<?php
	require_once "classes/class.SurveyChart.php";
	$arr1 = unserialize(base64_decode($_GET["arr"]));
	$graphName = utf8_decode($_GET["grName"]);
	$b1 = new SurveyChart($_GET["type"],400,250,$graphName,$_GET["x"],$_GET["y"],$arr1);
?>
