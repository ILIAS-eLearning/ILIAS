<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Unit tests for class ilADTFactory
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilADTFactoryTest extends TestCase
{
    protected $backupGlobals = false;

    protected Container $dic;

    protected function setUp() : void
    {
        $this->initDependencies();
        parent::setUp();
    }

    public function testConstruct() : void
    {
        $first = ilADTFactory::getInstance();
        $second = ilADTFactory::getInstance();
        $this->assertEquals($first, $second);
    }

    protected function setGlobalVariable(string $name, $value) : void
    {
        global $DIC;

        $GLOBALS[$name] = $value;
        unset($DIC[$name]);
        $DIC[$name] = static function (\ILIAS\DI\Container $c) use ($value) {
            return $value;
        };
    }

    protected function initDependencies() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;
    }
}
