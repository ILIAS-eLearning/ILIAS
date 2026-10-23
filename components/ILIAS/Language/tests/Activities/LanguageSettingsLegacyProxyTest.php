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

namespace ILIAS\Language\Tests\Activities;

use ILIAS\Language\Activities\LanguageSettingsLegacyProxy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LanguageSettingsLegacyProxyTest extends TestCase
{
    private mixed $dic_backup = null;

    protected function setUp(): void
    {
        $this->dic_backup = $GLOBALS['DIC'] ?? null;
    }

    protected function tearDown(): void
    {
        $GLOBALS['DIC'] = $this->dic_backup;
    }

    private function withSettings(\ilSetting $settings): LanguageSettingsLegacyProxy
    {
        $dic = new \ILIAS\DI\Container();
        $dic['ilSetting'] = static fn(): \ilSetting => $settings;
        $GLOBALS['DIC'] = $dic;

        return new LanguageSettingsLegacyProxy();
    }

    public function testDoesNotTouchTheLegacyContainerOnConstruction(): void
    {
        unset($GLOBALS['DIC']);

        $this->assertInstanceOf(LanguageSettingsLegacyProxy::class, new LanguageSettingsLegacyProxy());
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function storedFlagProvider(): array
    {
        return ['"1"' => ['1', true], '"0"' => ['0', false], 'empty string' => ['', false]];
    }

    /** Falsy cast of the stored string, like ilObjLanguageAccess::_checkTranslate(), under the per-language key. */
    #[DataProvider('storedFlagProvider')]
    public function testIsPageTranslationEnabledCastsTheStoredValueUnderThePerLanguageKey(string $stored, bool $expected): void
    {
        $settings = $this->createMock(\ilSetting::class);
        $settings->expects($this->once())->method('get')->with('lang_translate_fr', '0')->willReturn($stored);

        $this->assertSame($expected, $this->withSettings($settings)->isPageTranslationEnabled('fr'));
    }

    public function testSettingsAreWrittenAsOneAndZeroUnderTheirKeys(): void
    {
        $written = [];
        $settings = $this->createMock(\ilSetting::class);
        $settings->method('set')->willReturnCallback(
            static function (string $key, string $value) use (&$written): void {
                $written[] = [$key, $value];
            }
        );

        $proxy = $this->withSettings($settings);
        $proxy->setLanguageDetectionEnabled(true);
        $proxy->setLanguageDetectionEnabled(false);
        $proxy->setPageTranslationEnabled('de', true);
        $proxy->setPageTranslationEnabled('de', false);

        $this->assertSame(
            [['lang_detection', '1'], ['lang_detection', '0'], ['lang_translate_de', '1'], ['lang_translate_de', '0']],
            $written
        );
    }
}
