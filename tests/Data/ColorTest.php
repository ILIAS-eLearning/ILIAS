<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;

/**
 * Tests working with color data object
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ResultTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		$this->f = new Data\Factory();
	}

	protected function tearDown() {
		$this->f = null;
	}

	public function testFullHexValue() {
		$v = $this->f->color('#0fff2f');
		$expected = array(
			'#0fff2f',
			'rgb(15, 255, 47)',
			array(15, 255, 47)
		);
		$this->assertEquals($expected, array(
			$v->value(),
			$v->rgbstring(),
			$v->rgb()
		));
	}

	public function testShortHexValue() {
		$v = $this->f->color('#f0f');
		$expected = array(
			'#ff00ff',
			'rgb(255, 0, 255)',
			array(255, 0, 255)
		);
		$this->assertEquals($expected, array(
			$v->value(),
			$v->rgbstring(),
			$v->rgb()
		));
	}

	public function testRBGValue() {
		$v = $this->f->color(array(15,255,47));
		$expected = array(
			'#0fff2f',
			'rgb(15, 255, 47)',
			array(15, 255, 47)
		);
		$this->assertEquals($expected, array(
			$v->value(),
			$v->rgbstring(),
			$v->rgb()
		));
	}

	public function testWrongRBGValue() {
		$this->setExpectedException(UnexpectedValueException::class);
		$v = $this->f->color(array(-1,256,'123'));
	}
	public function testWrongRBGValue2() {
		$this->setExpectedException(UnexpectedValueException::class);
		$v = $this->f->color(array(1,1,'123'));
	}
	public function testWrongRBGValue3() {
		$this->setExpectedException(UnexpectedValueException::class);
		$v = $this->f->color(array());
	}
	public function testWrongHexValue() {
		$this->setExpectedException(UnexpectedValueException::class);
		$v = $this->f->color('ffffff');
	}
	public function testWrongHexValue2() {
		$this->setExpectedException(UnexpectedValueException::class);
		$v = $this->f->color('#ff');
	}
	public function testWrongHexValue4() {
		$this->setExpectedException(UnexpectedValueException::class);
		$v = $this->f->color('#gg0000');
	}

	public function testDarkness() {
		$v = $this->f->color('#6541f4');
		$this->assertEquals(true, $v->isDark());
	}

	public function testDarkness2() {
		$v = $this->f->color('#c1f441');
		$this->assertEquals(false, $v->isDark());
	}




}
