<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\DI\HTTPServices;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class assBaseTestCase
 */
abstract class assBaseTestCase extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();

        parent::setUp();
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    protected function setGlobalVariable($name, $value)
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = $GLOBALS[$name];
    }

    /**
     * @return \ilTemplate|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getGlobalTemplateMock()
    {
        return $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return HTTPServices&MockObject|MockObject
     */
    protected function getHttpMock()
    {
        return $this->getMockBuilder(HTTPServices::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \ilDBInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getDatabaseMock()
    {
        return $this->getMockBuilder(\ilDBInterface::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return \ILIAS|PHPUnit_Framework_MockObject_MockObject
     */
    protected function getIliasMock()
    {
        $mock = $this->getMockBuilder(\ILIAS::class)->disableOriginalConstructor()->getMock();

        $account = new stdClass();
        $account->id = 6;
        $account->fullname = 'Esther Tester';

        $mock->account = $account;

        return $mock;
    }
}
