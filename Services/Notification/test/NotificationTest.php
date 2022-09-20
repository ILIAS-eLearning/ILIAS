<?php

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class NotificationTest extends TestCase
{
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function setUp(): void
    {
        parent::setUp();
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;
    }

    protected function tearDown(): void
    {
    }

    protected function initDBMock(): \PHPUnit\Framework\MockObject\MockObject
    {
        $db_mock = $this->createMock(ilDBInterface::class);
        $this->setGlobalVariable(
            "ilDB",
            $db_mock
        );
        return $db_mock;
    }

    public function testRemoveForUser(): void
    {
        $db_mock = $this->initDBMock();
        $db_mock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('DELETE FROM notification WHERE user_id ='));

        ilNotification::removeForUser(14);
    }
}
