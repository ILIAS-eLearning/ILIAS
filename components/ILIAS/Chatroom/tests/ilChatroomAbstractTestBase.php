<?php

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

declare(strict_types=1);

use ILIAS\DI\Container;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

abstract class ilChatroomAbstractTestBase extends TestCase
{
    protected MockObject&ilChatroom $ilChatroomMock;
    protected MockObject&ilChatroomUser $ilChatroomUserMock;
    private ?Container $dic = null;

    protected function setUp(): void
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

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    protected function createIlChatroomUserMock(): ilChatroomUser&MockObject
    {
        $this->ilChatroomUserMock = $this->getMockBuilder(ilChatroomUser::class)->disableOriginalConstructor()->onlyMethods(
            ['getUserId', 'getUsername']
        )->getMock();

        return $this->ilChatroomUserMock;
    }

    protected function createGlobalIlDBMock(): ilDBInterface&MockObject
    {
        $db = $this->getMockBuilder(ilDBInterface::class)->getMock();
        $db->method('quote')->willReturnCallback(static fn($arg): string => "'" . $arg . "'");

        $this->setGlobalVariable('ilDB', $db);

        return $db;
    }

    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        $DIC[$name] = (static fn(Container $c) => $GLOBALS[$name]);
    }
}
