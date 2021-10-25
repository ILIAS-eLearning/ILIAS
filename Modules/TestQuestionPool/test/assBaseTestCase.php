<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\Refinery\Random\Group as RandomGroup;

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
        global $DIC;
        $GLOBALS['DIC'] = new \ILIAS\DI\Container();


        require_once './Services/Language/classes/class.ilLanguage.php';
        $lng_mock = $this->createMock('ilLanguage', array('txt'), array(), '', false);
        $lng_mock->expects($this->any())->method('txt')->will($this->returnValue('Test'));
        unset($DIC['lng']);
        $DIC['lng'] = $lng_mock;
        $GLOBALS['lng'] = $DIC['lng'];

        $dataCache_mock = $this->createMock('ilObjectDataCache', array(), array(), '', false);
        $DIC['ilObjDataCache'] = $dataCache_mock;
        $GLOBALS['ilObjDataCache'] = $DIC['ilObjDataCache'];

        $access_mock = $this->createMock('ilAccess', array(), array(), '', false);
        $DIC['ilAccess'] = $access_mock;
        $GLOBALS['ilAccess'] = $DIC['ilAccess'];

        $help_mock = $this->createMock('ilHelpGUI', array(), array(), '', false);
        $DIC['ilHelp'] = $help_mock;
        $GLOBALS['ilHelp'] = $help_mock;

        $user_mock = $this->createMock('ilObjUser', array(), array(), '', false);
        $DIC['ilUser'] = $user_mock;
        $GLOBALS['ilUser'] = $user_mock;

        $tabs_mock = $this->createMock('ilTabsGUI', array(), array(), '', false);
        $DIC['ilTabs'] = $tabs_mock;
        $GLOBALS['ilTabs'] = $tabs_mock;

        $rbacsystem_mock = $this->createMock('ilRbacSystem', array(), array(), '', false);
        $DIC['rbacsystem'] = $rbacsystem_mock;
        $GLOBALS['rbacsystem'] = $rbacsystem_mock;

        $refineryMock = $this->getMockBuilder(RefineryFactory::class)->disableOriginalConstructor()->getMock();
        $refineryMock->expects(self::any())->method('random')->willReturn($this->getMockBuilder(RandomGroup::class)->getMock());
        $DIC['refinery'] = $refineryMock;

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
        return $this->getMockBuilder(\ilGlobalPageTemplate::class)->disableOriginalConstructor()->getMock();
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
