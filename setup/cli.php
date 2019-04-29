<?php

/* Copyright (c) 2019 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../libs/composer/vendor/autoload.php");

$c = build_container_for_setup();
$app = $c["app"];
$app->run();

function build_container_for_setup() {
	$c = new \Pimple\Container;

	$c["app"] =  function($c) {
		return new \ILIAS\Setup\CLI\App();
	};

	return $c;
}

