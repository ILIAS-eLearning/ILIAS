<?php

use PHPUnit\Framework\TestCase;

/**
 * Class ilWorkflowEngineBaseTest
 */
abstract class ilWorkflowEngineBaseTest extends TestCase
{
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
}
