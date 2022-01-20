<?php

use ILIAS\DI\LoggingServices;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @var ilLanguage|MockObject
     */
    protected ilLanguage $lng;
    /**
     * @var LoggingServices|MockObject
     */
    protected LoggingServices $logger;
    /**
     * @var ilLog|MockObject
     */
    protected ilLogger $log;

    public function __construct(TestCase $test_case, array $values = [])
    {
        parent::__construct($values);
        $this->lng = $test_case->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock();
        $this->logger =  $test_case->getMockBuilder(LoggingServices::class)->disableOriginalConstructor()->getMock();
        $this->log = $test_case->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return    ilLanguageMock
     */
    public function language() : ilLanguage
    {
        return $this->lng;
    }

    /**
     * @return    ilLanguageMock
     */
    public function logger() : \ILIAS\DI\LoggingServices
    {
        return $this->logger;
    }
}
