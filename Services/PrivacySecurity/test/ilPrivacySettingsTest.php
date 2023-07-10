<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ILIAS\DI\Container;

/**
 * Unit tests for class ilPrivacySettingsTest
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesCopyWizard
 */
class ilPrivacySettingsTest extends TestCase
{
    protected ?Container $dic = null;

    protected function setUp(): void
    {
        $this->initDependencies();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        global $DIC;

        $DIC = $this->dic;

        parent::tearDown();
    }

    public function testConstruct(): void
    {
        $settings = ilPrivacySettings::getInstance();
        $this->assertInstanceOf(ilPrivacySettings::class, $settings);
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
        global $DIC;

        $this->dic = is_object($DIC) ? clone $DIC : $DIC;

        $DIC = new Container();

        if (!defined('SYSTEM_FOLDER_ID')) {
            define('SYSTEM_FOLDER_ID', 9);
        }

        $this->setGlobalVariable('ilDB', $this->createMock(ilDBInterface::class));
        $this->setGlobalVariable('ilSetting', $this->getMockBuilder(ilSetting::class)->disableOriginalConstructor()->getMock());
        $this->setGlobalVariable('ilUser', $this->getMockBuilder(ilObjUser::class)->disableOriginalConstructor()->getMock());
    }
}
