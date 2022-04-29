<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class ilChatroomAbstractTest
 * @author Thomas Joußen <tjoussen@gmx.de>
 */
abstract class ilChatroomAbstractTest extends TestCase
{
    /** @var MockObject&ilChatroom */
    protected $ilChatroomMock;
    /** @var MockObject&ilChatroomUser */
    protected $ilChatroomUserMock;
    private ?Container $dic = null;

    protected function setUp() : void
    {
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        $this->setGlobalVariable(
            'tpl',
            $this->getMockBuilder(ilGlobalTemplateInterface::class)->getMock()
        );
        
        parent::setUp();
    }

    protected function tearDown() : void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    /**
     * @return ilChatroom&MockObject
     */
    protected function createIlChatroomMock() : ilChatroom
    {
        $this->ilChatroomMock = $this->getMockBuilder(ilChatroom::class)->disableOriginalConstructor()->onlyMethods(
            ['isOwnerOfPrivateRoom', 'clearMessages']
        )->getMock();

        return $this->ilChatroomMock;
    }

    /**
     * @return ilChatroomUser&MockObject
     */
    protected function createIlChatroomUserMock() : ilChatroomUser
    {
        $this->ilChatroomUserMock = $this->getMockBuilder(ilChatroomUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getUserId', 'getUsername']
        )->getMock();

        return $this->ilChatroomUserMock;
    }

    /**
     * @return ilDBInterface&MockObject
     */
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
