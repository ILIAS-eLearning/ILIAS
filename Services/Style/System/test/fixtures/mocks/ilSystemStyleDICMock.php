<?php

use ILIAS\DI\LoggingServices;

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
     * @var ilLanguage|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected ilLanguage $lng;
    /**
     * @var LoggingServices|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected LoggingServices $logger;
    /**
     * @var ilLog|\Mockery\LegacyMockInterface|\Mockery\MockInterface
     */
    protected ilLogger $log;

    public function __construct(array $values = [])
    {
        parent::__construct($values);
        $this->lng = Mockery::mock(ilLanguage::class);
        $this->lng->expects('txt')->atMost(5);
        $this->logger =  Mockery::mock(LoggingServices::class);
        $this->log = Mockery::mock(ilLogger::class);
        $this->logger->expects('root')->andReturn($this->log);
        $this->logger->expects('debug');
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
