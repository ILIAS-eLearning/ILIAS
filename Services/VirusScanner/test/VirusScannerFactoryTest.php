<?php

declare(strict_types=1);

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

require_once __DIR__ . '/bootstrap.php';

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class VirusScannerFactoryTest extends VirusScannerBaseTest
{
    public static ilLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();

        if (!defined('IL_VIRUS_SCAN_COMMAND')) {
            define("IL_VIRUS_SCAN_COMMAND", 'phpunitscan');
        }

        if (!defined('IL_VIRUS_CLEAN_COMMAND')) {
            define("IL_VIRUS_CLEAN_COMMAND", 'phpunitclean');
        }

        $logger = $this->getMockBuilder(ilLogger::class)->disableOriginalConstructor()->getMock();
        self::$logger = $logger;

        $logger_factory = new class () extends ilLoggerFactory {
            public function __construct()
            {
            }

            public static function getRootLogger(): ilLogger
            {
                return VirusScannerFactoryTest::$logger;
            }

            public function getComponentLogger(string $a_component_id): ilLogger
            {
                return VirusScannerFactoryTest::$logger;
            }
        };

        $this->setGlobalVariable('ilias', $this->getMockBuilder(ILIAS::class)->disableOriginalConstructor()->getMock());
        $this->setGlobalVariable(
            'lng',
            $this->getMockBuilder(ilLanguage::class)->disableOriginalConstructor()->getMock()
        );

        $this->setGlobalVariable('ilLoggerFactory', $logger_factory);

        $this->setGlobalVariable('ilErr', $this->getMockBuilder(ilErrorHandling::class)->disableOriginalConstructor()->getMock());
    }

    public function testSophosScanStrategyCanBeRetrievedAccordingToGlobalSetting(): void
    {
        define("IL_VIRUS_SCANNER", 'Sophos');

        self::assertInstanceOf(ilVirusScannerSophos::class, ilVirusScannerFactory::_getInstance());
    }

    public function testAntiVirScanStrategyCanBeRetrievedAccordingToGlobalSetting(): void
    {
        define("IL_VIRUS_SCANNER", 'AntiVir');

        self::assertInstanceOf(ilVirusScannerAntiVir::class, ilVirusScannerFactory::_getInstance());
    }

    public function testClamAvScanStrategyCanBeRetrievedAccordingToGlobalSetting(): void
    {
        define("IL_VIRUS_SCANNER", 'ClamAV');

        self::assertInstanceOf(ilVirusScannerClamAV::class, ilVirusScannerFactory::_getInstance());
    }

    public function testIcapClientScanStrategyCanBeRetrievedAccordingToGlobalSetting(): void
    {
        define("IL_VIRUS_SCANNER", 'icap');
        define("IL_ICAP_CLIENT", 'phpunit');

        self::assertInstanceOf(ilVirusScannerICapClient::class, ilVirusScannerFactory::_getInstance());
    }
}
