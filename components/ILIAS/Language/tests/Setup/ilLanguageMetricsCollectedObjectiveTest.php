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

use ILIAS\Setup;
use PHPUnit\Framework\TestCase;

/**
 * collectFrom() hands the Setup database to ilSetupLanguage via
 * setDbHandler() before reading, and leaves the globals untouched.
 */
class ilLanguageMetricsCollectedObjectiveTest extends TestCase
{
    private function collect(ilSetupLanguage $setup_language, ?ilDBInterface $db): void
    {
        $objective = new ilLanguageMetricsCollectedObjective(
            $this->createStub(Setup\Metrics\Storage::class),
            $setup_language
        );
        $environment = new Setup\ArrayEnvironment([]);
        if ($db !== null) {
            $environment = $environment->withResource(Setup\Environment::RESOURCE_DATABASE, $db);
        }

        (new ReflectionMethod($objective, 'collectFrom'))
            ->invoke($objective, $environment, $this->createStub(Setup\Metrics\Storage::class));
    }

    public function testSetupDatabaseIsHandedToIlSetupLanguageBeforeAnyRead(): void
    {
        $db = $this->createStub(ilDBInterface::class);
        $calls = [];
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->expects(self::once())->method('setDbHandler')->with($db)
            ->willReturnCallback(static function () use (&$calls): bool {
                $calls[] = 'setDbHandler';
                return true;
            });
        $setup_language->method('getLocalLanguages')->willReturnCallback(static function () use (&$calls): array {
            $calls[] = 'getLocalLanguages';
            return [];
        });
        $setup_language->method('getInstalledLanguages')->willReturnCallback(static function () use (&$calls): array {
            $calls[] = 'getInstalledLanguages';
            return [];
        });

        $this->collect($setup_language, $db);

        self::assertSame('setDbHandler', $calls[0] ?? null);
        self::assertContains('getInstalledLanguages', $calls);
    }

    public function testGlobalsAreNotChanged(): void
    {
        $dic = new ILIAS\DI\Container();
        $GLOBALS['DIC'] = $dic;
        unset($GLOBALS['ilDB']);

        $setup_language = $this->createStub(ilSetupLanguage::class);
        $setup_language->method('getLocalLanguages')->willReturn([]);
        $setup_language->method('getInstalledLanguages')->willReturn([]);

        $this->collect($setup_language, $this->createStub(ilDBInterface::class));

        self::assertSame($dic, $GLOBALS['DIC']);
        self::assertFalse(isset($dic['ilDB']));
        self::assertArrayNotHasKey('ilDB', $GLOBALS);
    }

    public function testWithoutSetupDatabaseNothingIsRead(): void
    {
        $setup_language = $this->createMock(ilSetupLanguage::class);
        $setup_language->expects(self::never())->method('setDbHandler');
        $setup_language->expects(self::never())->method('getInstalledLanguages');

        $this->collect($setup_language, null);
    }
}
