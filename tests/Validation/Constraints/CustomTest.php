<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the custom constraints
 *
 * @author Richard Klees <richard.klees@concepts-and-training.de>
 */
class ValidationConstraintsCustomTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Validation\Factory
	 */
	protected $f = null;

	public function test_use_txt() {
		$is_ok = function ($value) {
			return false;
		};
		$txt_id = "TXT_ID";
		$error = function (callable $txt, $value) use ($txt_id) {
			return $txt($txt_id, $value);
		};
		$lng = $this->createMock(\ilLanguage::class);
		$c = new Validation\Constraints\Custom($is_ok, $error, new Data\Factory(), $lng);

		$txt_out = "'%s'";
		$lng
			->expects($this->once())
			->method("txt")
			->with($txt_id)
			->willReturn($txt_out);

		$value = "VALUE";
		$problem = $c->problemWith($value);

		$this->assertEquals(sprintf($txt_out, $value), $problem);
	}

	public function test_exception_on_no_parameter() {
		}

	public function test_no_sprintf_on_one_parameter() {
	}
}
