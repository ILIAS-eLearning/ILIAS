<?php
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");



if($_GET["type"] == 0) {
	$bb = gevBuildingBlockUtils::getPossibleBuildingBlocksByTopicName($_GET["selected"]);
	// javascript expects building blocks in an array form.
	foreach ($bb as $key => $value) {
		$arr = array();
		foreach ($value as $k => $v) {
			$arr[] = array($k, $v);
		}
		$bb[$key] = $arr;
	}
	echo json_encode($bb);
}

if($_GET["type"] == 1) {
	$infos = gevBuildingBlockUtils::getBuildingBlockInfosById($_GET["selected"]);
	echo json_encode($infos);
}