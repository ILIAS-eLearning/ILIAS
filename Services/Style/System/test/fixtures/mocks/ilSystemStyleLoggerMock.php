<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

use ILIAS\DI\LoggingServices;

/**
 * Class ilSystemStyleLoggerMock
 *
 * @since 5.3
 */
class ilSystemStyleLoggerMock extends LoggingServices {

	/**
	 * ilSystemStyleLoggerMock constructor
	 *
	 * @param ilSystemStyleDICMock $container
	 */
	public function __construct(ilSystemStyleDICMock $container) {
		parent::__construct($container);
	}


	/**
	 * @inheritdoc
	 */
	public function root(): ilLogger {
		return new ilSystemStyleRootLoggerMock();
	}
}

/**
 * Class ilSystemStyleLoggerMock
 *
 * @since 5.3
 */
class ilSystemStyleRootLoggerMock extends ilLogger {

	/**
	 * ilSystemStyleLoggerMock constructor
	 */
	public function __construct() {
		parent::__construct(NULL);
	}


	/**
	 * @inheritdoc
	 */
	public function debug($a_message, $a_context = array()) {

	}
}
