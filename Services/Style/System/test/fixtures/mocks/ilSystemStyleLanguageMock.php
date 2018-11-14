<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilSystemStylesLanguageMock
 */
class ilSystemStyleLanguageMock extends ilLanguage {

	/**
	 * @var array
	 */
	public $requested = [];


	/**
	 * ilLanguageMock constructor
	 */
	public function __construct() {
		parent::__construct("en");
	}


	/**
	 * @inheritdoc
	 */
	function txt($a_topic, $a_default_lang_fallback_mod = "") {
		$this->requested[] = $a_topic;

		return $a_topic;
	}
}
