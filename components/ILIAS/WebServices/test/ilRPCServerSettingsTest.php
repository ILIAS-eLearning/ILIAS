<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Unit tests for class ilRPCServerSettings
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesWebServices
 */
class ilRPCServerSettingsTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
    {
        $this->initDependencies();
        parent::setUp();
    }

    public function testConstruct(): void
    {
        $rpc_settings = ilRPCServerSettings::getInstance();
        $this->assertInstanceOf(ilRPCServerSettings::class, $rpc_settings);
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilSetting', $this->createMock(ilSetting::class));

        $logger = $this->getMockBuilder(ilLogger::class)
                       ->disableOriginalConstructor()
                       ->getMock();

        $logger_factory = $this->getMockBuilder(ilLoggerFactory::class)
                               ->disableOriginalConstructor()
                               ->onlyMethods(['getComponentLogger'])
                               ->getMock();
        $logger_factory->method('getComponentLogger')->willReturn($logger);
        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);
    }
}
