<?php declare(strict_types=1);

/* Copyright (c) 1998-2021 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;

/**
 * Class ilChatroomAbstractTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
abstract class ilChatroomAbstractTest extends TestCase
{
    /** @var PHPUnit\Framework\MockObject\MockObject|ilChatroom */
    protected $ilChatroomMock;

    /** @var PHPUnit\Framework\MockObject\MockObject|ilChatroomUser */
    protected $ilChatroomUserMock;

    protected function setUp() : void
    {
        global $DIC;
        $GLOBALS['DIC'] = $DIC = new Container();
        $DIC['tpl'] = $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock();
        
        parent::setUp();
    }

    protected function createIlChatroomMock() : ilChatroom
    {
        $this->ilChatroomMock = $this->getMockBuilder(ilChatroom::class)->disableOriginalConstructor()->onlyMethods(
            ['isOwnerOfPrivateRoom', 'clearMessages']
        )->getMock();

        return $this->ilChatroomMock;
    }

    protected function createIlChatroomUserMock() : ilChatroomUser
    {
        $this->ilChatroomUserMock = $this->getMockBuilder(ilChatroomUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getUserId', 'getUsername']
        )->getMock();

        return $this->ilChatroomUserMock;
    }

    protected function createGlobalIlDBMock() : ilDBInterface
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->method('quote')->willReturnCallback(static function ($arg) : string {
            return "'" . $arg . "'";
        });

        $this->setGlobalVariable('ilDB', $db);

        return $db;
    }

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        $DIC[$name] = static function (Container $c) use ($name) {
            return $GLOBALS[$name];
        };
    }
}
