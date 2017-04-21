<?php
/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

ini_set("assert.active", "1");
ini_set("assert.bail", "0");
ini_set("assert.warning", "1");

/**
 * Testing the faytory of result objects
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class ReadmeTests extends PHPUnit_Framework_TestCase {
	public function testReadme() {
		require_once(__DIR__."/../../src/Data/README.md");
		$this->assertTrue(true);
	}
}