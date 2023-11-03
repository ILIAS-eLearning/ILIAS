<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * @author  Stefan Meyer <meyer@leifos.de>
 * @ingroup ServicesTree
 */
class ilTrackingCollectionTest extends TestCase
{
    protected Container $dic;

    protected function setUp(): void
    {
        parent::setUp();
        $this->initDependencies();
    }

    public function testCollectionInstance(): void
    {
        $objectives = ilLPCollection::getInstanceByMode(
            0,
            ilLPObjSettings::LP_MODE_OBJECTIVES
        );
        $this->assertInstanceOf(
            ilLPCollectionOfObjectives::class,
            $objectives
        );
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;
        $this->setGlobalVariable(
            'ilDB',
            $this->createMock(ilDBInterface::class)
        );
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
