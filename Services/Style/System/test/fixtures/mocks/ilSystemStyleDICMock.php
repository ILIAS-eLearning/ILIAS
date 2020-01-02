<?php
require_once("libs/composer/vendor/autoload.php");

require_once("ilSystemStyleLanguageMock.php");
require_once("ilSystemStyleLoggerMock.php");



/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

/**
 * Class ilLanguageMock
 */
class ilSystemStyleDICMock extends ILIAS\DI\Container
{
    /**
     * @return	ilLanguageMock
     */
    public function language()
    {
        return new ilSystemStyleLanguageMock();
    }

    /**
     * @return	ilLanguageMock
     */
    public function logger()
    {
        return new ilSystemStyleLoggerMock();
    }
}
