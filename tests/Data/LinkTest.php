<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");
use ILIAS\Data;

/**
 * Tests working with link data object
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class LinkTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Data\Factory();
	}

	protected function tearDown() {
		$this->f = null;
	}

	public function testContruction() {
		$l = $this->f->link('the label', 'http://www.ilias.de');

		$this->assertInstanceOf(Data\Link\Link::class, $l);
		$this->assertEquals('the label', $l->label());
		$this->assertEquals('http://www.ilias.de', $l->url());
	}

	public function testContructionWrongParams() {
		try {
			$v = $this->f->link('the label','');
			$this->assertFalse("This should not happen.");
		}
		catch (\InvalidArgumentException $e) {
			$this->assertTrue(true);
		}
	}

}
