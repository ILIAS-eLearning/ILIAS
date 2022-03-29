<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Unit tests for class ilPrivacySettingsTest
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCopyWizard
 */
class ilPrivacySettingsTest extends TestCase
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
        $settings = ilPrivacySettings::getInstance();
        $this->assertInstanceOf(ilPrivacySettings::class, $settings);
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

        if (!defined('SYSTEM_FOLDER_ID')) {
            define('SYSTEM_FOLDER_ID' , 9);
        }

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilSetting', $this->createMock(ilSetting::class));
    }
}
