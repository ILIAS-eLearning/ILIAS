<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once "libs/composer/vendor/autoload.php";

require_once "ilSystemStyleLanguageMock.php";
require_once "ilSystemStyleLoggerMock.php";

use ILIAS\DI\Container;
use ILIAS\DI\LoggingServices;

/**
 * Class ilLanguageMock
 *
 * @since 5.2
 */
class ilSystemStyleDICMock extends Container {

	/**
	 * @return ilLanguageMock
	 */
	public function language(): ilLanguage {
		return new ilSystemStyleLanguageMock();
	}


	/**
	 * @return ilLanguageMock
	 */
	public function logger(): LoggingServices {
		return new ilSystemStyleLoggerMock($this);
	}
}
