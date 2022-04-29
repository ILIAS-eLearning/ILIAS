<?php

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilWorkflowEngineBaseTest
 */
abstract class ilWorkflowEngineBaseTest extends TestCase
{
    private ?Container $dic = null;

    protected function setGlobalVariable(string $name, $value)
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = function ($c) use ($name) {
            return $GLOBALS[$name];
        };
    }

    /**
     *
     */
    protected function setUp() : void
    {
        parent::setUp();

        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $this->setGlobalVariable('ilDB', $this->getMockBuilder(ilDBInterface::class)->getMock());

        $this->setGlobalVariable(
            'ilAppEventHandler',
            $this->getMockBuilder(ilAppEventHandler::class)->disableOriginalConstructor()->onlyMethods(array('raise'))->getMock()
        );

        $this->setGlobalVariable(
            'ilSetting',
            $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->onlyMethods(array('delete', 'get', 'set'))->getMock()
        );
    }

    protected function tearDown() : void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }
}
