<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;


/**
 * Unit tests for class ilCopyWizardOptions
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCopyWizard
 */
class ilCopyWizardOptionsTest extends TestCase
{
    protected $backupGlobals = false;

    protected Container $dic;

    protected function setUp() : void
    {
        $this->initCopyWizardDependencies();
        parent::setUp();
    }

    public function testSingleton() : void
    {
        $first = ilCopyWizardOptions::_getInstance(0);
        $second = ilCopyWizardOptions::_getInstance(0);
        $this->assertTrue($first === $second);
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
        $DIC[$name] = static function (Container $c) use ($value) {
            return $value;
        };
    }

    protected function initCopyWizardDependencies() : void
    {
        $this->dic = new Container();
        $GLOBALS['DIC'] = $this->dic;

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('tree', $this->createMock(ilTree::class));
    }
}
