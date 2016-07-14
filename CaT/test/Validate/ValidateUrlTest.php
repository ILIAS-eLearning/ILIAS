<?php

/* Copyright (c) 2016 Stefan Hecken, Extended GPL, see docs/LICENSE */

class ValidateUrlTest extends PHPUnit_Framework_TestCase {
	public function setUp() {
		$this->validate_url = new \CaT\Validate\ValidateUrl;
	}

	/**
	 * @dataProvider urlProvider
	 */
	public function test_valid_url_prefix($url, $valid) {
		$this->assertEquals($valid, $this->validate_url->validUrlPrefix($url));
	}

	public function urlProvider() {
		return array(array("http://www.ard.de", true)
			, array("https://www.ard.de", true)
			, array("htt://www.ard.de", false)
			, array("www.ard.de", false)
			);
	}
}