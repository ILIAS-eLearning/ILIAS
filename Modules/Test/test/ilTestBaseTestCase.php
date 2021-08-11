<?php declare(strict_types=1);

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ILIAS\DI\Container;

/**
 * Class ilTestBaseClass
 * @author Marvin Beym <mbeym@databay.de>
 */
class ilTestBaseTestCase extends TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp() : void
    {
        $GLOBALS['DIC'] = new Container();

        parent::setUp();
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = $GLOBALS[$name];
    }

    /**
     * @return ilTemplate|mixed|MockObject
     */
    protected function getGlobalTemplateMock()
    {
        return $this->getMockBuilder(\ilTemplate::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ilDBInterface|mixed|MockObject
     */
    protected function getDatabaseMock()
    {
        return $this->getMockBuilder(\ilDBInterface::class)->disableOriginalConstructor()->getMock();
    }

    /**
     * @return ILIAS|mixed|MockObject
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