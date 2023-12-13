<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Class ilExportOptionsTest
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilExportOptionsTest extends TestCase
{
    protected $backupGlobals = false;

    protected Container $dic;

    protected function setUp(): void
    {
        $this->initDependencies();
        parent::setUp();
    }

    public function testConstruct(): void
    {
        $options = ilExportOptions::getInstance();
        $this->assertNull($options);

        $options = ilExportOptions::newInstance(0);
        $this->assertTrue($options instanceof ilExportOptions);

        $options_ref = ilExportOptions::getInstance();
        $this->assertEquals($options, $options_ref);
    }


    protected function setGlobalVariable(string $name, $value): void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies(): void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
    }
}
