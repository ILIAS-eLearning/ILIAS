<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

/**
 * Test clipboard repository
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class CategoryReferenceTest extends TestCase
{
    protected function setUp(): void
    {
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;

        parent::setUp();

        $this->setGlobalVariable(
            "ilAccess",
            $this->createConfiguredMock(
                ilAccess::class,
                [
                    "checkAccess" => true
                ]
            )
        );
    }

    /**
     * @param string $name
     * @param mixed  $value
     */
    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;

        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function tearDown(): void
    {
    }

    /**
     * Test commands
     */
    public function testCommands(): void
    {
        $commands = ilObjCategoryReferenceAccess::_getCommands(10);
        $this->assertIsArray($commands);
    }
}
