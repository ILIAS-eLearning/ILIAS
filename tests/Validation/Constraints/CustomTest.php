<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */
require_once("libs/composer/vendor/autoload.php");

use ILIAS\Validation;
use ILIAS\Data;

/**
 * TestCase for the custom constraints
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
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
		$error = function (callable $txt, $value) {
		};
		$c = new Validation\Constraints\Custom(
	}
}
