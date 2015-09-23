<?php
require_once("./Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

require_once("Services/GEV/Utils/classes/class.gevBuildingBlockUtils.php");



if($_GET["type"] == 0) {
	$bb = gevBuildingBlockUtils::getPossibleBuildingBlocksByTopicName($_GET["selected"]);
	$json = "{";

	foreach ($bb as $key => $value) {
		$json .= '"'.$key.'":[';
		//$json .= '{"BLOCKS":[';
		foreach ($value as $key => $block) {
			$json .= '["'.$key.'","'.$block.'"],';
		}
		$json = rtrim($json,",");
		$json .= "],";
	}
	$json = rtrim($json,",");
	$json .= "}";
	$fh = fopen("possible_bulding_blocks.json","w+");
	fwrite($fh,$json);
	fclose($fh);
}

if($_GET["type"] == 1) {
	$infos = gevBuildingBlockUtils::getBuildingBlockInfosById($_GET["selected"]);
	$json = "{";

	$content = preg_replace('/[\r]/', '', $infos["content"]);
	$content = preg_replace('/[\n]/', '#:#', $content);
	$content = preg_replace('/["]/', '\\"', $content);

	$learning_dest = preg_replace('/[\r]/', '', $infos["learning_dest"]);
	$learning_dest = preg_replace('/[\n]/', '#:#', $learning_dest);

	$json .= '"content":"'.$content.'",';
	$json .= '"target":"'.$learning_dest.'",';
	$json .= '"wp":"'.$infos["wp"].'"';
	$json .= "}";
	$fh = fopen("bulding_block_infos.json","w+");
	fwrite($fh,$json);
	fclose($fh);
}